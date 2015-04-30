<?php
error_reporting(0);
if(!defined('DOING_AJAX') || !DOING_AJAX){
	header('HTTP/1.0 400 Bad Request', true, 400);
	print"you cannot call this script directly";
  exit; //just for good measure
}
/**testing variables ****************************/
//sleep(2);//when testing jquery fadeins etc
/******************************************/
/**********set header********************/
//header('Content-type: application/json');
$options=$this->pluginOptions;
$optionSizes=wppizza_sizes_available($options['sizes']);//outputs an array $arr=array(['lbl']=>array(),['prices']=>array());

$output='';

/*****************************************************************************************************************
*
*
*
*
*
*****************************************************************************************************************/
	/*****************************************************
		[adding new additive]
	*****************************************************/
	if($_POST['vars']['field']=='additives' && $_POST['vars']['id']>=0){
		$output=$this->wppizza_admin_section_additives($_POST['vars']['field'],$_POST['vars']['id'],'');
	}
	/*****************************************************
		[adding new custom opening time]
	*****************************************************/
	if($_POST['vars']['field']=='opening_times_custom'){
		$output=$this->wppizza_admin_section_opening_times_custom($_POST['vars']['field']);
	}
	/*****************************************************
		[adding new times closed]
	*****************************************************/
	if($_POST['vars']['field']=='times_closed_standard'){
		$output=$this->wppizza_admin_section_times_closed_standard($_POST['vars']['field']);
	}
	/*****************************************************
		[adding new size selection options]
	*****************************************************/
		if($_POST['vars']['field']=='sizes' && $_POST['vars']['id']>=0 && isset($_POST['vars']['newFields']) && $_POST['vars']['newFields']>0){
			$output=$this->wppizza_admin_section_sizes($_POST['vars']['field'],$_POST['vars']['id'],$_POST['vars']['newFields']);
		}

	/*****************************************************
		[order history -> delete abandoned orders]
	*****************************************************/
	if($_POST['vars']['field']=='delete_abandoned_orders'){
		global $wpdb;
		$days=0;
		if((int)$_POST['vars']['days']>=1){
			$days=(int)$_POST['vars']['days'];
		}
		/**do or dont delete all non completed orders**/
			$pStatusQuery=" IN ('INITIALIZED','CANCELLED')";
		if($_POST['vars']['failed']=='true'){
			$pStatusQuery=" NOT IN ('COMPLETED','PENDING','REFUNDED','CAPTURED','COD','AUTHORIZED')";
		}
		$sql="DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE order_date < TIMESTAMPADD(DAY,-".$days.",NOW()) AND payment_status ".$pStatusQuery."";
		$res=$wpdb->query( $wpdb->prepare($sql));
		$output.="".__('Done', $this->pluginLocale)."";
	}

	/******************************************************
		[save sorted categories]
	******************************************************/
	if($_POST['vars']['field']=='cat_sort'){

		$order = explode(',', $_POST['vars']['order']);
		$sorter = 0;
		/**we want all saved ones first as we might not display all on the screen**/
		$newOptions['layout']['category_sort']=$options['layout']['category_sort'];
		/**first parent categories**/
		foreach ($order as $id) {
			$key=(int)str_replace("tag-","",$id);
			$category = get_term_by( 'id', $key, $this->pluginSlugCategoryTaxonomy);
			/*only saving the sort of the parent categories**/
			if($category->parent==0){
				$newOptions['layout']['category_sort'][(int)$key]=$sorter;
				$sorter++;
			}
		}
		/**the child categories, maybe we will need them at some point **/
		foreach ($order as $id) {
			$key=(int)str_replace("tag-","",$id);
			$category = get_term_by( 'id', $key, $this->pluginSlugCategoryTaxonomy);
			/*only saving the sort of the parent categories**/
			if($category->parent>0){
				$newOptions['layout']['category_sort'][(int)$key]=$sorter;
				$sorter++;
			}
		}


		/***update full hierarchy too make sure we are now using the right updated order***/
		$newOptions['layout']['category_sort_hierarchy']=$this->wppizza_complete_sorted_hierarchy($newOptions['layout']['category_sort']);


		update_option( $this->pluginSlug, $newOptions );
	die(1);
	}
	/******************************************************
		[adding a new meal category->add column selection]
	******************************************************/
	if($_POST['vars']['field']=='meals' && !isset($_POST['vars']['item']) && $_POST['vars']['id']>=0){
		$output=$this->wppizza_admin_section_category($_POST['vars']['field'],$_POST['vars']['id']);
	}
	/******************************************************
		[adding a new meal to category]
	******************************************************/
	if($_POST['vars']['field']=='meals' && isset($_POST['vars']['item']) && $_POST['vars']['id']>=0 && $_POST['vars']['newKey']>=0){
		$output=$this->wppizza_admin_section_category_item($_POST['vars']['field'],$_POST['vars']['id'],false,$_POST['vars']['newKey'],false,$options);
	}
	/******************************************************
		[prize tier selection has been changed->add relevant price options input fields]
	******************************************************/
	if($_POST['vars']['field']=='sizeschanged' && $_POST['vars']['id']!='' && isset($_POST['vars']['inpname']) &&  $_POST['vars']['inpname']!=''){
		$output='';
		if(is_array($options['sizes'][$_POST['vars']['id']])){
			foreach($options['sizes'][$_POST['vars']['id']] as $a=>$b){
				/*if we change the ingredient pricetire, do not use default prices , but just empty**/
				if(isset($_POST['vars']['classId']) && $_POST['vars']['classId']=='ingredients'){$price='';}else{$price=$b['price'];}
				$output.="<input name='".$_POST['vars']['inpname']."[prices][]' type='text' size='5' value='".$price."'>";
		}}
		
		print"".$output."";
		exit();
	}	


	/******************************************************
		[get php info]
	******************************************************/
	if($_POST['vars']['field']=='get-php-vars'){
		ob_start();
		phpinfo(INFO_CONFIGURATION);
		//phpinfo(INFO_GENERAL);
		//phpinfo(INFO_ENVIRONMENT);
		//phpinfo(INFO_VARIABLES);
		
		preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);
		
		# $matches [1]; # Style information
		# $matches [2]; # Body information
		
		echo "<div class='phpinfodisplay'><style type='text/css'>\n",
		    join( "\n",
		        array_map(
		            create_function(
		                '$i',
		                'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'
		                ),
		            preg_split( '/\n/', $matches[1] )
		            )
		        ),
		    "</style>\n",
		    $matches[2],
		    "\n</div>\n";
		exit();
	}

/********************************************************************************************************************************************************************
*
*
*
*
*	[order history things]
*
*
*
*
********************************************************************************************************************************************************************/

	/*************************************************************************************
	*
	*
	*
	*	[show/get orders wppizza->order history]
	*
	*
	*	
	***********************************************************************************/
	if($_POST['vars']['field']=='get_orders'){
		/*get some global wp vars**/
		global $wpdb,$blog_id;
		/*ini markup string*/
		$markup='';
		/**ini output array*/
		$output=array();
			
		/*ini total price*/
		$totalPriceOfShown=0;
		/*get selected limit*/ 
		if($_POST['vars']['limit']>0){$limit=' limit 0,'.(int)$_POST['vars']['limit'].'';}else{$limit='';}
		/*get selected order status to show */ 
		if($_POST['vars']['orderstatus']!=''){$orderstatus=' AND order_status="'.$_POST['vars']['orderstatus'].'" ';}else{$orderstatus='';}


		/****************************************************
		*
		*	[multisite only and if enabled in wppizza->settings] 
		*	get *all* subsites orders (in parent site only)]
		*
		***************************************************/
		if ( is_multisite() && $blog_id==BLOG_ID_CURRENT_SITE && $options['plugin_data']['wp_multisite_order_history_all_sites']) {
			/*ini array*/
			$allOrders=array();
	 	   	/*get all and loop through blogs*/
	 	   	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	 	   		if ($blogs) {
	        	foreach($blogs as $blog) {
	        		switch_to_blog($blog['blog_id']);
	        			/*make sure plugin is active*/
	        			if(is_plugin_active('wppizza/wppizza.php')){
	        				/* 
	        					get blogid and name to add to object
	        					if blogid==1 omit to be able to select right table
	        					as it won't have the 1_ prefix
	        				*/
							$blogId=$blog['blog_id']==1 ? '' : $blog['blog_id'] ;
							$blogName=get_bloginfo('name');
							/************************
								[make and run query]
								dont bother with "order by" here
								as we have to resort on order date anyway
							*************************/
							$allOrdersQuery = "SELECT order_date, id, wp_user_id, order_update, customer_details, order_details, order_status, hash, order_ini, customer_ini, payment_status, transaction_id, transaction_details, transaction_errors, initiator, notes FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED','REFUNDED') ".$orderstatus." ".$limit." ";
							$theseOrders = $wpdb->get_results($allOrdersQuery);
							$blogOrders=array();
							if(is_array($theseOrders)){
							foreach($theseOrders as $key=>$order){
								$blogOrders[$key]=$order;
								/**add blog id and blog name to object*/
								$blogOrders[$key]->blogId=$blogId;
								$blogOrders[$key]->blogName=$blogName;
							}}
							/**merge array**/
							$allOrders=array_merge($allOrders,$blogOrders);

	        			}
	        		restore_current_blog();
	        	}}
				/**sort by date in reverse (by order date) and truncate to $limit set*/
				arsort($allOrders);
				$allOrders = array_slice($allOrders, 0, (int)$_POST['vars']['limit']);		
		
		}
		/****************************************************
		*
		*	[standard, single site or if multisite has NOT enabled  
		*	"History all subsites" in wppizza->settings]
		*
		***************************************************/
		else{
			$allOrdersQuery = "SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED','REFUNDED') ".$orderstatus." ORDER BY id DESC ".$limit." ";
			$allOrders = $wpdb->get_results($allOrdersQuery);
		}

		/****************************************************
		*
		*	check if there are orders and if so do loop 
		*
		***************************************************/
		/********************
			if we have orders
		********************/
		if(is_array($allOrders) && count($allOrders)>0){
			
			
			/*get any -perhaps filtered - customised order status */
			$customOrderStatus=wppizza_custom_order_status();
			$customOrderStatusGetTxt=wppizza_order_status_default();
			
			
			/*admin only notice if able to delete order*/
			if (current_user_can('wppizza_cap_delete_order')){
				$output['notice_delete']="<div>".__('Note: deleting an order will <b>ONLY</b> delete it from the database table. It will <b>NOT</b> issue any refunds, cancel the order, send emails etc.', $this->pluginLocale)."</div>";
			}
			/*notice regarding status change*/
			$output['notice_info']="<div style='color:red'>".__('"Status" is solely for your internal reference. Updating/changing the value will have no other effects but might help you to identify which orders have not been processed.', $this->pluginLocale)."</div>";
			
			
			
			/********************************************************************************************
			*
			*	[TABLE OPEN]
			*
			********************************************************************************************/			
			$output['table_open']="<table>";
				/****************************************************************************
					[header row]
				****************************************************************************/
				$output['header']="<tr class='wppizza-orders-head'>";

					$header['column_order']="<td>";
						$header['column_order'].="".__('Order', $this->pluginLocale)."";
					$header['column_order'].="</td>";

					$header['column_customer']="<td>";
						$header['column_customer'].="".__('Customer Details', $this->pluginLocale)."";
					$header['column_customer'].="</td>";

					$header['column_details']="<td>";
						$header['column_details'].="".__('Order Details', $this->pluginLocale)."";
					$header['column_details'].="</td>";

					$header['column_empty']="<td>";
						$header['column_empty'].="";
					$header['column_empty'].="</td>";

					/**allow header filtering**/
					$header= apply_filters('wppizza_filter_orderhistory_header', $header );
					$output['header'].=implode('',$header);

				$output['header'].="</tr>";
				
				/********************
					loop 
				********************/				
				foreach ( $allOrders as $oKey=>$orders ){
					/**ini array*/
					$thisOrder=array();
					/*unserialized customer data*/
					$customerDet=maybe_unserialize($orders->customer_ini);
					/*unserialized order data*/
					$orderDet=maybe_unserialize($orders->order_ini);
					/*order status*/
					$orderStatus=strtolower($orders->order_status);
					
					/**add to total ordered amount of shown items**/
					$totalPriceOfShown+=(float)$orderDet['total'];
					/**
						create unique id/key (if using multisite) 
						as id's could be the same if pulled from 2 blogs
					**/
					$uoKey='';
					$blogid='';
					if(isset($orders->blogId)){
						$blogid=$orders->blogId;
						$uoKey.=''.$orders->blogId.'_';	
					}
					$uoKey.=$orders->id;
					

					/****************************************************************************
					*
					*	[start first row -> regular order info]
					*	
					****************************************************************************/

					$thisOrder['main_tr_open']="<tr  id='wppizza-order-tr-".$uoKey."' class='wppizza-order-tr wppizza-ord-status-".$orderStatus."'>";


						/***************************************************************
						*
						*	first row, first column, 
						*	order info (id, transaction id etc)
						*
						****************************************************************/
						$orderinfo=array();/*reset*/
						$orderinfo['tdopen']="<td style='white-space:nowrap'>";
							
							/************************
							*
							*	add some hidden inputs to be able to correctly 
							*	identify id's etc via js if required
							*
							************************/
								/**order id**/
								$orderinfo['hiddeninput_orderid']="<input type='hidden' id='wppizza_order_id_".$uoKey."' value='".$orders->id ."' />";
								/**blog id, blog name**/
								if(isset($orders->blogName)){
								$orderinfo['hiddeninput_blogid']="<input type='hidden' id='wppizza_order_blogid_".$uoKey."' value='".$orders->blogId ."' />";
								$orderinfo['hiddeninput_blogname']="<input type='hidden' id='wppizza_order_blogname_".$uoKey."' value='".$orders->blogName ."' />";
								}

							/************************
							*
							*	output 
							*
							************************/
								/**multisite, blog info if exists, appropriate*/
								if(isset($orders->blogName)){
									$orderinfo['blogname']= '<b>'.$orders->blogName.'</b><br />';
								}
								
								/** order date**/
									$orderinfo['date']= date("d-M-Y H:i:s",strtotime($orders->order_date));
							
								/** 
									get used gateway label
									hidden variables only used for old style 
									order printing
								**/
								if($orders->initiator!=''){
									/**get label from gateway class**/
									$gwIdent=$orders->initiator;
									$gatewayClassname='WPPIZZA_GATEWAY_'.$orders->initiator;
									if (class_exists(''.$gatewayClassname.'')) {
										$gw=new $gatewayClassname;
										if($gw->gatewayOptions['gateway_label']!=''){
										$gwIdent=$gw->gatewayOptions['gateway_label'];
										}
									}
									/*old style order printing**/
									if($options['plugin_data']['use_old_admin_order_print']){
										$orderinfo['hiddeninput_payment']="<input type='hidden' id='wppizza_order_initiator_".$uoKey."' value='".__('Payment By', $this->pluginLocale).": ". $gwIdent ."' />";
										$orderinfo['hiddeninput_payment'].="<input type='hidden' id='wppizza_order_initiator_ident_".$uoKey."' value='". $gwIdent ."' />";
									}
									$orderinfo['payment']="<br />".__('Payment By', $this->pluginLocale).": ". $gwIdent ."";
								}
							
								/** 
									print transaction id
									hidden variables only used for old style 
									order printing
								**/							
								if($orders->transaction_id!=''){
									$orders->transaction_id = apply_filters('wppizza_filter_transaction_id', $orders->transaction_id, $orders->id );
									/*old style order printing**/
									if($options['plugin_data']['use_old_admin_order_print']){								
										$orderinfo['hiddeninput_txid']="<input type='hidden' id='wppizza_order_transaction_id_".$uoKey."' value='ID: ". $orders->transaction_id ."' />";
									}
									$orderinfo['transaction_id']="<br/>ID: ". $orders->transaction_id . "";
								}
								
								/** 
									print order status dropdown
								**/								
								$orderinfo['status']="<br />";
								$orderinfo['status'].="<label>".__('Status', $this->pluginLocale)."";
								$orderinfo['status'].="<select id='wppizza_order_status-".$uoKey."' name='wppizza_order_status-".$uoKey."' class='wppizza_order_status'>";
								foreach($customOrderStatus as $s){
									if(isset($customOrderStatusGetTxt[$s])){/*get translation if we have any*/
										$lbl=$customOrderStatusGetTxt[$s];
									}else{
										$lbl=$s;
									}
									$orderinfo['status'].="<option value='".$s."' ".selected($orders->order_status,$s,false).">".$lbl."</option>";
								}
								$orderinfo['status'].="</select>";
								$orderinfo['status'].="</label>";

								/** 
									print last update
									or order date if not set
								**/	
								$orderinfo['last_update']="<br />";
								$orderinfo['last_update'].="".__('Last Status Update', $this->pluginLocale).":<br />";
								$orderinfo['last_update'].="<span id='wppizza_order_update-".$uoKey."'>";
								if($orders->order_update!='0000-00-00 00:00:00'){
									$orderinfo['last_update'].= date("d-M-Y H:i:s",strtotime($orders->order_update));
								}else{
									$orderinfo['last_update'].= date("d-M-Y H:i:s",strtotime($orders->order_date));
								}
								$orderinfo['last_update'].="</span>";
								
								
						$orderinfo['tdclose']="</td>";

						/**allow filtering**/
						$orderinfo= apply_filters('wppizza_filter_orderhistory_order_info', $orderinfo, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus);
						$thisOrder['orderinfo']=implode('',$orderinfo);


						/***************************************************************
							first row, second column, 
							customer details
						****************************************************************/						
						$customer_details=array();/*reset*/
						$customer_details[]="<td>";
							$customer_details[]="<textarea id='wppizza_order_customer_details_".$uoKey."' class='wppizza_order_customer_details'>". $orders->customer_details ."</textarea>";
						$customer_details[]="</td>";
						/**allow filtering**/
						$customer_details= apply_filters('wppizza_filter_orderhistory_customer_details', $customer_details, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus);
						$thisOrder['customer_details']=implode('',$customer_details);

						/***************************************************************
							first row, third column, 
							order details
						****************************************************************/						
						$order_details=array();/*reset*/
						$order_details[]="<td>";
							$order_details[]="<textarea id='wppizza_order_details_".$uoKey."' class='wppizza_order_details' >". $orders->order_details ."</textarea>";
						$order_details[]="</td>";
						/**allow filtering**/
						$order_details= apply_filters('wppizza_filter_orderhistory_order_details', $order_details, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus);
						$thisOrder['order_details']=implode('',$order_details);


						/***************************************************************
							first row, fourth column, 
							delete, print, add notes
						****************************************************************/						
						$actions=array();/*reset*/
						$actions['tdopen']="<td>";
							
							
							/******************************
							*
							*	delete order button [admin only]
							*	
							******************************/
							if (current_user_can('wppizza_cap_delete_order')){
								$actions['delete']="<a href='#' id='wppizza-delete-order-".$uoKey."' class='wppizza_order_delete'>".__('delete', $this->pluginLocale)."</a>";
								$actions['deletebr']="<br/>";
							}
							/************************
							*
							*	print order button
							*
							************************/
							/*current version*/
							if(!$options['plugin_data']['use_old_admin_order_print']){
								$actions['print']="<a href='javascript:void(0);'  id='wppizza-print-order-".$uoKey."' class='wppizza-print-order button'>".__('print order', $this->pluginLocale)."</a>";
							}
							/*old style order printing using just the fields/textareas shown*/							
							if($options['plugin_data']['use_old_admin_order_print']){
								$actions['print']="<a href='javascript:void(0);'  id='wppizza-print-order-".$uoKey."' class='wppizza-print-order-prev button'>".__('print order', $this->pluginLocale)."</a>";
							}

							/************************
							*
							*	add/edit notes button
							*
							************************/							
								$actions['printbr']="<br />";
								/*set visibility*/
								if(trim($orders->notes)==''){$notesBtnSty='block;';}else{$notesBtnSty='none';}
								
								$actions['notes']="<a href='javascript:void(0);'  id='wppizza-order-add-notes-".$uoKey."' class='wppizza-order-add-notes button' style='display:".$notesBtnSty."'>".__('add notes', $this->pluginLocale)."</a>";
						
						$actions['tdclose']="</td>";
						/**allow filtering**/
						$actions= apply_filters('wppizza_filter_orderhistory_actions', $actions, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus );
						$thisOrder['actions']=implode('',$actions);

					$thisOrder['main_tr_close']="</tr>";


					/****************************************************************************
					*
					*
					*	[do second row -> order notes]
					*
					*	
					****************************************************************************/
					$notes=array();/*reset*/
					/*set appropriate class*/
					if(trim($orders->notes)==''){$nbtrClass='wppizza-order-notes-tr';}else{$nbtrClass='wppizza-order-has-notes-tr';}
					
					$notes['tropen']="<tr id='".$nbtrClass."-".$uoKey."' class='".$nbtrClass."'>";
						$notes['tdopen']="<td colspan='4'>";
							$notes['textarea_notes']="<textarea id='wppizza-order-notes-".$uoKey."' class='wppizza-order-notes' placeholder='".__('notes:', $this->pluginLocale)."'>".$orders->notes."</textarea>";
							$notes['textarea_notes_ok']="<a href='javascript:void(0);'  id='wppizza-order-do-notes-".$uoKey."' class='wppizza-order-do-notes button'>".__('ok', $this->pluginLocale)."</a>";
						$notes['tdclose']="</td>";
					$notes['trclose']="</tr>";

					/**allow filtering of notes **/
					$notes= apply_filters('wppizza_filter_orderhistory_notes', $notes, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus  );
					/**add notes tr to output**/
					$thisOrder['notes']=implode('',$notes);





					/**********************************************
						allow filter of output parts
						in loop
					**********************************************/
					$thisOrder= apply_filters('wppizza_filter_orderhistory_loop_parts', $thisOrder , $orders->id, $customerDet, $orderDet, $blogid, $orderStatus  );
					$output['order_'.$uoKey]=implode('',$thisOrder);
				
				}
				/***********************************************************************************
				*
				*	[END LOOP]
				*
				***********************************************************************************/				
				
			$output['table_close']="</table>";
			
			
			
			/******************************************
				allow filter of output all output parts
			********************************************/
			$output= apply_filters('wppizza_filter_orderhistory_table', $output);
			$output=implode('',$output);
			/**add to markup*/
			$markup.=$output;
			
			/********************************************************************************************
			*
			*	[TABLE CLOSE]
			*
			********************************************************************************************/			
		}
		/**we have no orders to display*/
		else{
			$markup.="<h1 style='text-align:center'>".__('no orders yet :(', $this->pluginLocale)."</h1>";
		}

		/*************************************************************************************************
		*
		*	[array of vars to return to js
		*
		*************************************************************************************************/
		/*orders html*/
		$obj['orders']=$markup;
		/*total value of DISPLAYED orders*/
		$obj['totals']=__('Total of shown orders', $this->pluginLocale).': '.$this->pluginOptions['order']['currency_symbol'].' '.wppizza_output_format_price($totalPriceOfShown).'';
		$obj['totals'].='<br /><a href="javascript:void(0)" id="wppizza_history_totals_getall">'.__('show total of all orders', $this->pluginLocale).'</a>';

		print"".json_encode($obj)."";
	exit();
	}
/*************************************************************************************************
*
*
*	[order history get totals ALL orders 
*	(not just displayed ones)]
*
*
*************************************************************************************************/
	/**show get orders**/
	if($_POST['vars']['field']=='get_orders_total'){
		$totalPriceAll=0;
		global $wpdb;
		global $blog_id;
		/************************************************************************
			multisite install
			all orders of all sites (blogs)
			but only for master blog and if enabled (settings)
		************************************************************************/
		if ( is_multisite() && $blog_id==BLOG_ID_CURRENT_SITE && $options['plugin_data']['wp_multisite_order_history_all_sites']) {
			$allOrders=array();
	 	   	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	 	   		if ($blogs) {
	        	foreach($blogs as $blog) {
	        		switch_to_blog($blog['blog_id']);
	        			/*make sure plugin is active*/
	        			if(is_plugin_active('wppizza/wppizza.php')){
							/************************
								[make and run query]
							*************************/
							$allOrdersQuery = $wpdb->get_results("SELECT order_ini FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status='COMPLETED' ");
							/**merge array**/
							$allOrders=array_merge($allOrdersQuery,$allOrders);
	        			}
					restore_current_blog();
	        	}}
		}else{
			$allOrders = $wpdb->get_results("SELECT order_ini FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status='COMPLETED' ");
		}
	
		if(is_array($allOrders) && count($allOrders)>0){
			foreach ( $allOrders as $orders ){
				/**add to total ordered amount of shown items**/
				$orderDet=maybe_unserialize($orders->order_ini);
				$totalPriceAll+=(float)$orderDet['total'];
				/*******************************************/
			}
		}

		$obj['totals']=__('total all orders', $this->pluginLocale).': '.$this->pluginOptions['order']['currency_symbol'].' '.wppizza_output_format_price($totalPriceAll).'';

		print"".json_encode($obj)."";
	exit();
	}
/********************************************
*
*
*	[order history -> update order status]
*
*
********************************************/
	if($_POST['vars']['field']=='orderstatuschange' && isset($_POST['vars']['id']) && $_POST['vars']['id']>=0){
		global $wpdb;
		/**distinct blogid set**/
		if($_POST['vars']['blogid']!=''){
			$wpdb->prefix=$wpdb->prefix.$_POST['vars']['blogid'].'_';
		}
		/****oder status***/
		$order_status=esc_sql($_POST['vars']['selVal']);
		/****update payment status too if set to refunded***/
		if($order_status=='REFUNDED'){
			$payment_status='REFUNDED';
		}else{
			/**set back payment status if not refunded to what it was**/
			$payment_status='COMPLETED';
		}
		
		/**update db including setting current time as order_update */
		$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET order_status='".$order_status."',payment_status='".$payment_status."',order_update=NULL WHERE id=".(int)$_POST['vars']['id']." ");
		$thisOrder = $wpdb->get_results("SELECT order_update FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id=".$_POST['vars']['id']."");

		$output= date("d-M-Y H:i:s",strtotime($thisOrder[0]->order_update));

		print"".$output."";
		exit();
	}
/*****************************************************
*
*
*	[order history -> delete order]
*
*
*****************************************************/
	if($_POST['vars']['field']=='delete_order'){
		global $wpdb;
		/**distinct blogid set**/
		if($_POST['vars']['blogid']!=''){
			$wpdb->prefix=$wpdb->prefix.$_POST['vars']['blogid'].'_';
		}
		$res=$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id=%s ",(int)$_POST['vars']['ordId']));
		$output.="".__('order deleted', $this->pluginLocale)."";
	}	
/********************************************
*
*
*		[order history -> update notes]
*
*
********************************************/
	if($_POST['vars']['field']=='ordernoteschange' && isset($_POST['vars']['id']) && $_POST['vars']['id']>=0){
		global $wpdb;
		/**distinct blogid set**/
		if($_POST['vars']['blogid']!=''){
			$wpdb->prefix=$wpdb->prefix.$_POST['vars']['blogid'].'_';
		}
		/*add notes to db*/
		$notes=wppizza_validate_string($_POST['vars']['selVal']);
		$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET notes='".$notes."' WHERE id=".(int)$_POST['vars']['id']." ");
		$output=strlen($notes);
		
		print"".$output."";
		exit();
	}
/*************************************************************************************
*
*
*	[order history -> print order]
*
*
*************************************************************************************/
	if($_POST['vars']['field']=='print-order' && $_POST['vars']['id']>=0){

		$orderId=(int)$_POST['vars']['id'];
		/*should never happen really*/
		if($orderId<=0){
			print"ERROR [ADMIN 1001]: invalid order id";
			exit();
		}
		/**********************************
			get order details
		**********************************/
		require(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
		$orderDetails=new WPPIZZA_ORDER_DETAILS();
		$orderDetails->setOrderId($orderId);
		$orderDetails->setBlogId($_POST['vars']['blogid']);
		$order=$orderDetails->getOrder();/**all order vars**/
		
		/********************************
		simplify vars to us in template
		********************************/
		$siteDetails=$order['site'];
		$multiSite=$order['multisite'];
		$orderDetails=$order['ordervars'];
		$txt=$order['localization'];
		$customerDetails=$order['customer']['post'];//omit ['others'] here
		$cartitems=$order['items'];
		$orderSummary=$order['summary'];


		/**get template**/
		$output='';
		if(file_exists( $this->pluginTemplateDir . '/wppizza-order-print.php')){
			ob_start();
			require_once($this->pluginTemplateDir.'/wppizza-order-print.php');
			$output = ob_get_clean();
		}else{
			ob_start();
			require_once(WPPIZZA_PATH.'templates/wppizza-order-print.php');
			$output = ob_get_clean();
		}

		print"".$output."";
		exit();
	}
	/************************************************************************************************
	*
	*	[in case one wants to do/add more things in functions.php]
	*
	************************************************************************************************/
	do_action('wppizza_ajax_action_admin',$_POST);

print"".$output."";
exit();
?>