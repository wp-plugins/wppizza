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
		[order history -> delete order]
	*****************************************************/
	if($_POST['vars']['field']=='delete_orders'){
		global $wpdb;
		$res=$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id=%s ",$_POST['vars']['ordId']));
		$output.="".__('order deleted', $this->pluginLocale)."";
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
	/*****************************************************
		[order history]
	*****************************************************/
	/**show get orders**/
	if($_POST['vars']['field']=='get_orders'){
		$output='';
		$totalPriceOfShown=0;
		global $wpdb;
		if($_POST['vars']['limit']>0){$limit=' limit 0,'.$_POST['vars']['limit'].'';}else{$limit='';}
		
		if($_POST['vars']['orderstatus']!=''){$orderstatus=' AND order_status="'.$_POST['vars']['orderstatus'].'" ';}else{$orderstatus='';}
		
		$allOrders = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED','REFUNDED') ".$orderstatus." ORDER BY id DESC ".$limit." ");
		
		
		
		if(is_array($allOrders) && count($allOrders)>0){
			/*admin only*/
			if (current_user_can('wppizza_cap_delete_order')){
				$output.="<div>".__('Note: deleting an order will <b>ONLY</b> delete it from the database table. It will <b>NOT</b> issue any refunds, cancel the order, send emails etc.', $this->pluginLocale)."</div>";
			}
			$output.="<div style='color:red'>".__('"Status" is solely for your internal reference. Updating/changing the value will have no other effects but might help you to identify which orders have not been processed.', $this->pluginLocale)."</div>";
			$output.="<table>";
				/****************************************************************************
					[header row]
				****************************************************************************/
				$output.="<tr class='wppizza-orders-head'>";
					
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
				
				/**allow filtering**/	
				$header= apply_filters('wppizza_filter_orderhistory_header', $header );	
				$output.=implode('',$header);
					
					
					
				$output.="</tr>";


				$customOrderStatus=wppizza_custom_order_status();
				$customOrderStatusGetTxt=wppizza_order_status_default();
				foreach ( $allOrders as $orders ){
					/**add to total ordered amount of shown items**/
					$customerDet=maybe_unserialize($orders->customer_ini);
					$orderDet=maybe_unserialize($orders->order_ini);
					$totalPriceOfShown+=(float)$orderDet['total'];
					/*******************************************/
					
					$output.="<tr class='wppizza-ord-status-".strtolower($orders->order_status)."'>";
						
						
						/****************************************************************************
							[first column, order info (id, transaction id etc)]
						****************************************************************************/
						$orderinfo=array();/*reset*/
						
						$orderinfo['tdopen']="<td style='white-space:nowrap'>";
							$orderinfo['date']= date("d-M-Y H:i:s",strtotime($orders->order_date));
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
								$orderinfo['hiddeninput_payment']="<input type='hidden' id='wppizza_order_initiator_".$orders->id."' value='".__('Payment By', $this->pluginLocale).": ". $gwIdent ."' />";
								$orderinfo['hiddeninput_payment'].="<input type='hidden' id='wppizza_order_initiator_ident_".$orders->id."' value='". $gwIdent ."' />";
								
								$orderinfo['payment']="<br/>".__('Payment By', $this->pluginLocale).": ". $gwIdent ."";
							}
							if($orders->transaction_id!=''){
								$orders->transaction_id = apply_filters('wppizza_filter_transaction_id', $orders->transaction_id, $orders->id );
								$orderinfo['hiddeninput_txid']="<input type='hidden' id='wppizza_order_transaction_id_".$orders->id."' value='ID: ". $orders->transaction_id ."' />";
								$orderinfo['transaction_id']="<br/>ID: ". $orders->transaction_id . "";
							}
							$orderinfo['status']="<br/>";
							$orderinfo['status'].="<label>".__('Status', $this->pluginLocale)."";
							$orderinfo['status'].="<select id='wppizza_order_status-".$orders->id."' name='wppizza_order_status-".$orders->id."' class='wppizza_order_status'>";
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

							//$orderinfo[]="<br/>";
							//$orderinfo[]="<a href='javascript:void()' id='wppizza_order_reject'>".__('Reject with email to customer', $this->pluginLocale)."</a>";
							//$orderinfo[]="<span id='wppizza_order_rejected-".$orders->id."'>";
							//$orderinfo[]="</span>";

							$orderinfo['last_update']="<br/>";
							$orderinfo['last_update'].="".__('Last Status Update', $this->pluginLocale).":<br />";
							$orderinfo['last_update'].="<span id='wppizza_order_update-".$orders->id."'>";
							if($orders->order_update!='0000-00-00 00:00:00'){
								$orderinfo['last_update'].= date("d-M-Y H:i:s",strtotime($orders->order_update));
							}else{
								$orderinfo['last_update'].= date("d-M-Y H:i:s",strtotime($orders->order_date));
							}
							$orderinfo['last_update'].="</span>";
						$orderinfo['tdclose']="</td>";
						
						/**allow filtering**/	
						$orderinfo= apply_filters('wppizza_filter_orderhistory_order_info', $orderinfo, $orders->id, $customerDet, $orderDet);	
						$output.=implode('',$orderinfo);						
						
						
						/****************************************************************************
							[second column -> customer details
						****************************************************************************/						
						$customer_details=array();/*reset*/
						$customer_details[]="<td>";
							$customer_details[]="<textarea id='wppizza_order_customer_details_".$orders->id."' class='wppizza_order_customer_details'>". $orders->customer_details ."</textarea>";
						$customer_details[]="</td>";
						/**allow filtering**/	
						$customer_details= apply_filters('wppizza_filter_orderhistory_customer_details', $customer_details, $orders->id, $customerDet, $orderDet);	
						$output.=implode('',$customer_details);						

						/****************************************************************************
							[third column -> order details
						****************************************************************************/	
						$order_details=array();/*reset*/
						$order_details[]="<td>";
							$order_details[]="<textarea id='wppizza_order_details_".$orders->id."' class='wppizza_order_details' >". $orders->order_details ."</textarea>";
						$order_details[]="</td>";
						/**allow filtering**/	
						$order_details= apply_filters('wppizza_filter_orderhistory_order_details', $order_details, $orders->id, $customerDet, $orderDet);	
						$output.=implode('',$order_details);							
						
						/****************************************************************************
							[fourth column -> delete, print, add notes
						****************************************************************************/						
						$actions=array();/*reset*/
						$actions['tdopen']="<td>";
							/*admin only*/
							if (current_user_can('wppizza_cap_delete_order')){
								$actions['delete']="<a href='#' id='wppizza_order_".$orders->id."' class='wppizza_order_delete'>".__('delete', $this->pluginLocale)."</a>";
								$actions['deletebr']="<br/>";
							}
							/*print order*/
							$actions['print']="<a href='javascript:void(0);'  id='wppizza-print-order-".$orders->id."' class='wppizza-print-order button'>".__('print order', $this->pluginLocale)."</a>";
							/*add edit notes*/
								$actions['printbr']="<br/>";
								if(trim($orders->notes)==''){
									$nbtrClass='wppizza-order-notes-tr';$notesBtnSty='block;';
								}else{
									$nbtrClass='wppizza-order-has-notes-tr';$notesBtnSty='none';
								}
								$actions['notes']="<a href='javascript:void(0);'  id='wppizza-order-add-notes-".$orders->id."' class='wppizza-order-add-notes button' style='display:".$notesBtnSty."'>".__('add notes', $this->pluginLocale)."</a>";
						$actions['tdclose']="</td>";
						/**allow filtering**/	
						$actions= apply_filters('wppizza_filter_orderhistory_actions', $actions, $orders->id, $customerDet, $orderDet );	
						$output.=implode('',$actions);							
					
					
					
					$output.="</tr>";


					/****************************************************************************
						[second row -> order notes
					****************************************************************************/					
					$notes=array();/*reset*/
					$notes['tropen']="<tr id='".$nbtrClass."-".$orders->id."' class='".$nbtrClass."'>";
						$notes['tdopen']="<td colspan='4'>";
							$notes['textarea_notes']="<textarea id='wppizza-order-notes-".$orders->id."' class='wppizza-order-notes' placeholder='".__('notes:', $this->pluginLocale)."'>".$orders->notes."</textarea>";
							$notes['textarea_notes_ok']="<a href='javascript:void(0);'  id='wppizza-order-do-notes-".$orders->id."' class='wppizza-order-do-notes button'>".__('ok', $this->pluginLocale)."</a>";
						$notes['tdclose']="</td>";
					$notes['trclose']="</tr>";
					
					/**allow filtering**/	
					$notes= apply_filters('wppizza_filter_orderhistory_notes', $notes, $orders->id, $customerDet, $orderDet );	
					$output.=implode('',$notes);						
					
					
				}
			$output.="</table>";
		}else{
			$output.="<h1 style='text-align:center'>".__('no orders yet :(', $this->pluginLocale)."</h1>";
		}
		
		$obj['orders']=$output;
		
		$obj['totals']=__('Total of shown orders', $this->pluginLocale).': '.$this->pluginOptions['order']['currency_symbol'].' '.wppizza_output_format_price($totalPriceOfShown).'';
		$obj['totals'].='<br /><a href="javascript:void(0)" id="wppizza_history_totals_getall">'.__('show total of all orders', $this->pluginLocale).'</a>';
		
		print"".json_encode($obj)."";
	exit();		
	}
	/*****************************************************
		[order history get totals]
	*****************************************************/
	/**show get orders**/
	if($_POST['vars']['field']=='get_orders_total'){
		$totalPriceAll=0;
		global $wpdb;
		$allOrders = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED') ORDER BY id DESC ");	
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
		[order history -> update order status]
	********************************************/
	if($_POST['vars']['field']=='orderstatuschange' && isset($_POST['vars']['id']) && $_POST['vars']['id']>=0){
		global $wpdb;
		
		/****update if set to refunded***/
		$order_status=$_POST['vars']['selVal'];
		if($order_status=='REFUNDED'){
			$payment_status='REFUNDED';
		}else{
		/**set back payment status if not refunded to what it was**/
			$initiator=$_POST['vars']['initiator'];
			if($initiator=='COD'){
				$payment_status='COD';
			}else{
				$payment_status='COMPLETED';
			}
		}
		$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET order_status='".$_POST['vars']['selVal']."',payment_status='".$payment_status."',order_update=NULL WHERE id=".$_POST['vars']['id']." ");
		$thisOrder = $wpdb->get_results("SELECT order_update FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id=".$_POST['vars']['id']."");

		$output= date("d-M-Y H:i:s",strtotime($thisOrder[0]->order_update));
	}
	/********************************************
		[order history -> update notes]
	********************************************/
	if($_POST['vars']['field']=='ordernoteschange' && isset($_POST['vars']['id']) && $_POST['vars']['id']>=0){
		global $wpdb;
		/*add notes to db*/
		$notes=wppizza_validate_string($_POST['vars']['selVal']);
		$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET notes='".$notes."' WHERE id=".$_POST['vars']['id']." ");
		$output=strlen($notes);

		/*we probably do not want to update the order status date/time otherwsie use the below instead and use the response insetad of 'ok' in the js */
		//$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET notes='".wppizza_validate_string($_POST['vars']['selVal'])."',order_update=NULL WHERE id=".$_POST['vars']['id']." ");
		//$thisOrder = $wpdb->get_results("SELECT order_update FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id=".$_POST['vars']['id']."");
		//$output= date("d-M-Y H:i:s",strtotime($thisOrder[0]->order_update));
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