<?php
if (!class_exists( 'WPPizza' ) ) {return;}

	class WPPIZZA_SEND_ORDER_EMAILS extends WPPIZZA_ACTIONS {

		function __construct() {
			parent::__construct();

			$this->wppizza_order_emails_extend();

			/**blog charset*/
			$this->blogCharset=get_bloginfo('charset');
			/**timestamp the order*/
			$this->currentTime= current_time('timestamp');
			//$this->orderTimestamp =date("d-M-Y H:i:s", current_time('timestamp'));
			$this->orderTimestamp ="".date_i18n(get_option('date_format'),$this->currentTime)." ".date_i18n(get_option('time_format'),$this->currentTime)."";			

			/**set shop name and email*/
			$this->orderShopName 	='';
			$this->orderShopEmail 	=$this->pluginOptions['order']['order_email_to'][0];

			/**who to bcc the order to*/
			$this->orderShopBcc 	=$this->pluginOptions['order']['order_email_bcc'];

			/**name and email of whoever is ordering*/
			$this->orderClientName 	='';
			$this->orderClientEmail ='';

			/** post variables from order form **/
			$this->orderPostVars	=$_POST;

			/**subject vars for email subject**/
			$this->subjectPrefix 	=	''.get_bloginfo().': ';
			$this->subject 			=	''.$this->pluginOptions['localization']['your_order']['lbl'].' ';
			$this->subjectSuffix 	=	''.$this->orderTimestamp.'';

			/* we also need any overrides by extensions in the mmain class to be used here**/
			$this->wppizza_extend();
		}
		/***************************************************************
			[allow some extension classes to allow to modify variables]
			class must start with 'WPPIZZA_ORDER_EMAILS_EXTEND_'
		***************************************************************/
		function wppizza_order_emails_extend(){
			$allClasses=get_declared_classes();
			$wppizzaOrderExtend=array();
			foreach ($allClasses AS $oe=>$class){
				$chkStr=substr($class,0,28);
				if($chkStr=='WPPIZZA_ORDER_EMAILS_EXTEND_'){
					$wppizzaOrderExtend[$oe]=new $class;
					foreach($wppizzaOrderExtend[$oe] as $k=>$v){
						$this->$k=$v;
					}
				}
			}
			return ;
		}

		/*****************************************************************************************************************************
		*
		*	[construct plaintext and html email from fields in db]
		*
		*****************************************************************************************************************************/
		function wppizza_wordwrap_indent($str){
			$str=str_replace(PHP_EOL,"".PHP_EOL."     ",$str);
			return $str;

		}

		function wppizza_order_email($orderid){
			global $wpdb;
			$res = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id='".$orderid."' ");

			/*initialize vars**/
			$email=array();
			$email['plaintext']='';
			$email['html']=array();

			if($res){
				/*********************************************************************
				*
				*
				*	customer details : posted and stored variables from order page
				*
				*
				**********************************************************************/
				$pOptions=$this->pluginOptions;
				$cDetails=maybe_unserialize($res->customer_ini);

				/*********************************************
				*
				*	[posted input fields of this plugin]
				*
				*********************************************/
				$email['plaintext']['customer_details']="";//".PHP_EOL.PHP_EOL
				/**protect these keys, so no other extension uses it*/
				$protectedKeys=array();
				foreach($this->pluginOptions['order_form'] as $k=>$v){
					$protectedKeys[$v['key']]=$v;
				}

				$i=0;
				foreach($cDetails as $k=>$v){
					/*****default input fields of this plugin*****/
					if(isset($protectedKeys[$k])){
						/**plaintext**/
						if($protectedKeys[$k]['type']!='textarea'){/* non-textareas*/
							$strPartLeft=''.$protectedKeys[$k]['lbl'].'';
							$spaces=45-strlen($strPartLeft);
							$strPartRight=''.wp_kses($cDetails[$k],array());
							$email['plaintext']['customer_details'].=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
						}else{
							$email['plaintext']['customer_details'].=''.$protectedKeys[$k]['lbl'].PHP_EOL;
							$email['plaintext']['customer_details'].='     '.$this->wppizza_wordwrap_indent(wordwrap(wp_kses($cDetails[$k],array()), 72, PHP_EOL, true));
							$email['plaintext']['customer_details'].=PHP_EOL.PHP_EOL;

						}
						/**html -> stick into array for use in html emails**/
						if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
							if($protectedKeys[$k]['type']!='textarea'){/* non-textareas*/
								$cDetValHtml=wppizza_email_html_entities($cDetails[$k]);
							}else{
								$cDetValHtml='<div class="wppizza-order-textarea">'.nl2br(wppizza_email_html_entities($cDetails[$k])).'</div>';
							}
							$email['html']['customer_details'][]=array('label'=>wppizza_email_html_entities($protectedKeys[$k]['lbl']),'value'=>$cDetValHtml);
						}
					}

					/*********************************************************************
						if another plugin/extension wants to add field value pairs, make sure
						its an array having [label] and [value] to display in email
						i.e:
						<input type="hidden" name="distinct_name[label]" value="some value"/>';
						<input type="text" name="distinct_name[value]" value="some value"/>';
						(make sure there are no clashes with other input fields)
						ought to make this "classable" at some point anyway
					**********************************************************************/
					if(!isset($protectedKeys[$k])){
						if(is_array($v) && isset($v['label']) && isset($v['value']) && !isset($protectedKeys[$k]) ){
							//if($i==0){
							//	$email['plaintext']['customer_details'] .=PHP_EOL;/*add one empty line for readabilities sake*/
							//}
							$strPartLeft=''.$v['label'].'';
							$spaces=45-strlen($strPartLeft);
							$strPartRight=''.wp_kses($v['value'],array());

							$email['plaintext']['customer_details'].=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;

							/**html emails**/
							/**stick into array for use in html emails**/
							if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
								$email['html']['customer_details'][]=array('label'=>wppizza_email_decode_entities($v['label'],$this->blogCharset),'value'=>wppizza_email_decode_entities($strPartRight,$this->blogCharset));
							}
						$i++;
						}
					}
				}
				/*****************************************************************************************
				*
				*
				*	[order details]
				*
				*
				****************************************************************************************/
				$oDetails=maybe_unserialize($res->order_ini);
				$email['plaintext']['order']=PHP_EOL."===========".$this->pluginOptions['localization']['order_details']['lbl']."============".PHP_EOL.PHP_EOL;
				$email['plaintext']['order'].=$this->orderTimestamp."".PHP_EOL;
				$email['plaintext']['order'].="".$this->pluginOptions['localization']['order_paid_by']['lbl']." ".$res->initiator." (".$res->transaction_id.") ".PHP_EOL;
				$email['plaintext']['order'].=PHP_EOL.PHP_EOL;

				foreach($oDetails['item'] as $k=>$v){
					/*tabs dont seem to work reliably, so lets try to put some even space between order item and total**/
					$strPartLeft=''.$v['quantity'].'x '.$v['name'].' '.$v['size'].'  '.$oDetails['currency'].' '.wppizza_output_format_price(wppizza_output_format_float($v['price']),$this->pluginOptions['layout']['hide_decimals']).'';
					$spaces=55-strlen($strPartLeft);
					$strPartRight=''.$oDetails['currency'].' '.wppizza_output_format_price(wppizza_output_format_float($v['pricetotal']),$this->pluginOptions['layout']['hide_decimals']).'';
					$email['plaintext']['order'].=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
					
					$addInfo=array();
					/*if its a (non empty) array**/
					if(isset($v['additionalInfo']) && is_array($v['additionalInfo']) && count($v['additionalInfo'])>0){
						foreach($v['additionalInfo'] as $additionalInfo){
							$addInfo[]=''.$additionalInfo.'';
						}
					}
					/*if its a (non empty) string**/
					if(isset($v['additionalInfo']) && !is_array($v['additionalInfo']) && $v['additionalInfo']!=''){
						$addInfo[]=''.$v['additionalInfo'].'';
					}
					
					/**append additional info to item if set**/
					if(count($addInfo)>0){
						$email['plaintext']['order'].="   ".implode(", ",$addInfo);
						$email['plaintext']['order'].=PHP_EOL.PHP_EOL;
					}
					/**html emails**/
					if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
						if(count($addInfo)>0){
							$addInfoHtml=wppizza_email_html_entities(implode(", ",$addInfo));
						}else{
							$addInfoHtml='';
						}
						$email['html']['order_items'][]=array('label'=>wppizza_email_html_entities($strPartLeft),'value'=>wppizza_email_html_entities($strPartRight),'additional_info'=>$addInfoHtml);
					}

				}
				$email['plaintext']['order'].=PHP_EOL.PHP_EOL;
				$email['plaintext']['order'].=''.$pOptions['localization']['order_items']['lbl'].': '.$oDetails['currency'].' '.$oDetails['total_price_items'].PHP_EOL;

				/**html emails**/
				if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
					$email['html']['order_summary']['cartitems']=array('label'=>wppizza_email_html_entities($pOptions['localization']['order_items']['lbl']),'price'=>$oDetails['total_price_items'],'currency'=>$oDetails['currency'] );
				}

				if($oDetails['discount']>0){
					$email['plaintext']['order'].=''.$pOptions['localization']['discount']['lbl'].': - '.$oDetails['currency'].' '.($oDetails['discount']).PHP_EOL;
					/**html emails**/
					if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
						$email['html']['order_summary']['discount']=array('label'=>wppizza_email_html_entities($pOptions['localization']['discount']['lbl']),'price'=>$oDetails['discount'],'currency'=>$oDetails['currency'] );
					}
				}
				/**********************************************************
				*
				*	[item tax]
				*
				**********************************************************/
				if($oDetails['item_tax']>0){
					$email['plaintext']['order'].=$pOptions['localization']['item_tax_total']['lbl'].': '.$oDetails['currency'].' '.($oDetails['item_tax']).PHP_EOL;
						/**html emails**/
						if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
							$email['html']['order_summary']['item_tax']=array('label'=>wppizza_email_html_entities($pOptions['localization']['item_tax_total']['lbl']),'price'=>$oDetails['item_tax'],'currency'=>$oDetails['currency'] );
						}
				}
				/**********************************************************
				*
				*	[delivery charges - no self pickup enabled or selected]
				*
				**********************************************************/
				if(!isset($oDetails['selfPickup']) || $oDetails['selfPickup']==0){
					if($oDetails['delivery_charges']!=''){
						$email['plaintext']['order'].=$pOptions['localization']['delivery_charges']['lbl'].': '.$oDetails['currency'].' '.($oDetails['delivery_charges']).PHP_EOL;
						/**html emails**/
						if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
							$email['html']['order_summary']['delivery']=array('label'=>wppizza_email_html_entities($pOptions['localization']['delivery_charges']['lbl']),'price'=>$oDetails['delivery_charges'],'currency'=>$oDetails['currency'] );
						}
					}else{
						$email['plaintext']['order'].=$pOptions['localization']['free_delivery']['lbl'].PHP_EOL;
						/**html emails**/
						if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
							$email['html']['order_summary']['delivery']=array('label'=>wppizza_email_html_entities($pOptions['localization']['free_delivery']['lbl']),'price'=>'','currency'=>'' );
						}
					}
				}
				/**********************************************************
					[order total]
				**********************************************************/
				$email['plaintext']['order'].=PHP_EOL;
				$email['plaintext']['order'].=$pOptions['localization']['order_total']['lbl'].': '.$oDetails['currency'].' '.($oDetails['total']).PHP_EOL;
				/**html emails**/
				if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
					$email['html']['order_summary']['total']=array('label'=>wppizza_email_html_entities($pOptions['localization']['order_total']['lbl']),'price'=>$oDetails['total'],'currency'=>$oDetails['currency'] );
				}
				$email['plaintext']['order'].=PHP_EOL;

				/****************************************************
					[self pickup - enabled and selected]
				****************************************************/
				if(isset($oDetails['selfPickup']) && $oDetails['selfPickup']==1){
					$email['plaintext']['order'].=PHP_EOL.wordwrap(strip_tags($pOptions['localization']['order_page_self_pickup']['lbl']), 72, "\n", true).PHP_EOL;
					/**html emails**/
					if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
						$email['html']['order_summary']['self_pickup']=array('label'=>wppizza_email_html_entities($pOptions['localization']['order_page_self_pickup']['lbl']),'price'=>'','currency'=>'' );
					}
				}

				$email['plaintext']['order'].=PHP_EOL."====================================================".PHP_EOL;
				$email['plaintext']['order'].=PHP_EOL."".$pOptions['localization']['order_email_footer']['lbl']."".PHP_EOL;

				/**now decode any funny entities**/
				$email['plaintext']['order']=wppizza_email_decode_entities($email['plaintext']['order'],$this->blogCharset);

				/***********************************************************************************************
				*
				*
				*	[now lets set the relevant class vars]
				*
				*
				***********************************************************************************************/

					/**********************************************
						[all db vals - maybe useful at some point in the future]
					************************************************/
					$this->orderResults=$res;
					/***********************************************
						[set currency etc]
					************************************************/
					$this->orderCurrency=$oDetails['currency'];
					$this->orderTransactionId=$res->transaction_id;
					$this->orderGatewayUsed=$res->initiator;
					/***********************************************
						[set plaintext emails]
					************************************************/
					$this->orderMessage['plaintext']="".PHP_EOL.PHP_EOL.$email['plaintext']['customer_details'].PHP_EOL.$email['plaintext']['order'].PHP_EOL ;
					/***********************************************
						[set html emails if set ]
					************************************************/
					$this->orderMessage['html']=$email['html'];

					/***********************************************
						[customer and order details to be saved in db and displayed in history]
					************************************************/
					$this->customerDetails=$email['plaintext']['customer_details'];
					$this->orderDetails=$email['plaintext']['order'];
					/***********************************************************
						[set name and email of the the person that is ordering]
					***********************************************************/
					$recipientName =!empty($cDetails['cname']) ? wppizza_validate_string($cDetails['cname']) : '';
					$fromEmails=!empty($cDetails['cemail']) ? wppizza_validate_email_array($cDetails['cemail']) : '';
					$this->orderClientName=$recipientName;
					$this->orderClientEmail=$fromEmails[0];

					/***********************************************
						[overwrite subject vars for email subject]
					************************************************/
					if (file_exists( get_template_directory() . '/wppizza-order-email-subject.php')){
						/**copy to template directory to keep settings**/
						include(get_template_directory() . '/wppizza-order-email-subject.php');
					}else{
						include(WPPIZZA_PATH.'templates/wppizza-order-email-subject.php');
					}
					
					/**update db entry with the current time timestamp of when the order was actually send**/
					$orderDate=date('Y-m-d H:i:s',$this->currentTime);
					$order_ini=$oDetails;
					$order_ini['time']=$this->currentTime;
					
					$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET order_date='".$orderDate."',order_ini='".serialize($order_ini)."' WHERE id='".$orderid."' ");					
					
					
			}
			return;
		}

		/*****************************************************************************************************************************
		*
		*	[choose function to send email]
		*	[send order email]
		*	[return status -> "true" if successful, "false" if not]
		*	[if false, will return error message and var[mailer] to indicate which function was used]
		*
		*****************************************************************************************************************************/
		function wppizza_order_send_email($orderid=false){

			/***create/set email html and plaintext strings***/
			$this->wppizza_order_email($orderid);

			$phpVersion=phpversion();

			/**send order using mail**/
			if($this->pluginOptions['plugin_data']['mail_type']=='mail'){
				/************set headers*************/
				$header = '';
				if($this->orderClientEmail!=''){
					$header .= 'From: '.$this->orderClientName.'<'.$this->orderClientEmail.'>' . PHP_EOL.
					'Reply-To: '.$this->orderClientEmail.'' . PHP_EOL .
					'X-Mailer: PHP/' . $phpVersion;
					$header .= PHP_EOL;
					$header .= 'Cc: '.$this->orderClientEmail.'' . PHP_EOL;
				}else{
					$header .= 'From: --------<>' . PHP_EOL.
					'Reply-To: '.$this->orderClientEmail.'' . PHP_EOL .
					'X-Mailer: PHP/' . $phpVersion;
					$header .= PHP_EOL;
				}
				if(count($this->orderShopBcc)>0){
					$bccs=implode(",",$this->orderShopBcc);/*trying to get rid of strict errors->passed by reference*/
					$header .= 'Bcc: '.$bccs.'' . PHP_EOL;
				}
				$header .= 'MIME-Version: 1.0' . PHP_EOL;
				$header .= 'Content-type: text/plain; charset='.$this->blogCharset.'' . PHP_EOL;
				/************send mail**************/
				if( @mail( $this->orderShopEmail, $this->subjectPrefix.$this->subject.$this->subjectSuffix, $this->orderMessage['plaintext'], $header)) {
					$sendMail['status']=true;
				}else{
					$sendMail['status']=false;
					$sendMail['error']= error_get_last();
				}
				/**ident to identify mail function*/
				$sendMail['mailer']='mail';
			}
			/**send order using wp_mail**/
			if($this->pluginOptions['plugin_data']['mail_type']=='wp_mail'){
				/************set headers*************/
				$wpMailHeaders=array();
				if($this->orderClientEmail!=''){
					$wpMailHeaders[] = 'From: '.$this->orderClientName.'<'.$this->orderClientEmail.'>';
					$wpMailHeaders[] = 'Cc: '.$this->orderClientEmail.'';
				}else{
					$wpMailHeaders[] = 'From: --------<>';
				}
				if(count($this->orderShopBcc)>0){
					$bccs=implode(",",$this->orderShopBcc);/*trying to get rid of strict errors->passed by reference*/
					$wpMailHeaders[]= 'Bcc: '.$bccs.'';
				}
				$wpMailHeaders[] = 'Reply-To: '.$this->orderClientEmail.'';

				/************send mail**************/
				if(@wp_mail($this->orderShopEmail, $this->subjectPrefix.$this->subject.$this->subjectSuffix, $this->orderMessage['plaintext'], $wpMailHeaders)) {
					$sendMail['status']=true;
				}else{
					$sendMail['status']=false;
					$sendMail['error']=error_get_last();
				}
				/**ident to identify mail function*/
				$sendMail['mailer']='wp_mail';
			}


			/**send order using phpmailer**/
			if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				$options=$this->pluginOptions;

				/************************************************************************
				*	for legacy reasons, in case templates and settings from
				*	earlier versions of this plugin, were copied to the theme folder
				*	we create some static vars here which
				*	also makes it a bit easier/obvious to edit the template
				***********************************************************************/
				$mail = new PHPMailer(true);
				/**to be used in html template**/
				$nowdate=$this->orderTimestamp;
				$transactionId=$this->orderTransactionId;
				$gatewayUsed=$this->orderGatewayUsed;
				$customer_details_array=$this->orderMessage['html']['customer_details'];
				$order_items=$this->orderMessage['html']['order_items'];
				$order_summary=$this->orderMessage['html']['order_summary'];
				$currency=$this->orderCurrency;
				/***get localization vars**/
				foreach($options['localization'] as $k=>$v){
					$orderLabel[$k]=$v['lbl'];	
					
				}

				/*return $orderHtml*/
				$orderHtml='';
				/*for legacy reasons, someone might use an old template in their theme directory***/
				if (file_exists( get_template_directory() . '/wppizza-order-html-email.php')){
					require_once(get_template_directory_uri().'/wppizza-order-html-email.php');
				}
				elseif(file_exists( get_template_directory() . '/wppizza-order-email-html.php')){
					ob_start();
					require_once(get_template_directory_uri().'/wppizza-order-email-html.php');
					$orderHtml = ob_get_clean();
				}else{
					ob_start();
					require_once(WPPIZZA_PATH.'templates/wppizza-order-email-html.php');
					$orderHtml = ob_get_clean();
				}
				/**set phpmailer settings**/
				if (file_exists( get_template_directory() . '/wppizza-phpmailer-settings.php')){
					require_once(get_template_directory_uri().'/wppizza-phpmailer-settings.php');
				}else{
					require_once(WPPIZZA_PATH.'templates/wppizza-phpmailer-settings.php');
				}
			}
			/************return status (success or fail - with error messages if any) **************/
			return $sendMail;
		}
		/********************************************************************************************
		*
		*
		*	[ORDER BY COD/Ajax]
		*
		*
		********************************************************************************************/
		function wppizza_order_results($mailResults,$orderId){
			$output='';
			/***successfully sent***/
			if(isset($mailResults['status'])){
				$output="<div class='mailSuccess'><h1>".$this->pluginOptions['localization']['thank_you']['lbl']."</h1>".nl2br($this->pluginOptions['localization']['thank_you_p']['lbl'])."</div>";
				$output.=$this->gateway_order_on_thankyou($orderId);
				$this->wppizza_unset_cart();
			}
			/***mail sending error or transaction already processes -> show error***/
			if(!isset($mailResults['status'])){
				$output="<p class='mailError'>".$mailResults['mailer'].": ".print_r($mailResults['error'],true)."</p>";
			}
		return $output;
		}


	/******************************************************************
	*
	*	[show order details to thank you page if set ]
	*	[$order = object or id]
	******************************************************************/
	function gateway_order_on_thankyou($res){
		$output='';
		/**check if we are displaying the order on the thank you page**/
		if($this->pluginOptions['gateways']['gateway_showorder_on_thankyou']){
			/*if we are only passing the id, try and get the order from the db first**/
			if(!is_object($res) && is_numeric($res)){
				global $wpdb;
				$res = $wpdb->get_row("SELECT id,transaction_id,order_ini,customer_ini,initiator FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id='".$res."' ");
			}

			$thisOrderTransactionId=$res->transaction_id;
			$thisOrderDetails=unserialize($res->order_ini);
			$thisCustomerDetails=unserialize($res->customer_ini);

			/*organize vars to make them easier to use in template**/
			$order=array();
			/*transaction id*/
			$order['transaction_id']=$thisOrderTransactionId;
			$order['transaction_date_time']="".date_i18n(get_option('date_format'),$thisOrderDetails['time'])." ".date_i18n(get_option('time_format'),$thisOrderDetails['time'])."";
			/**paid by**/
			$order['gatewayUsed']=$res->initiator;

			/*order globals*/
			$order['currency']=$thisOrderDetails['currency'];
			$order['currencyiso']=$thisOrderDetails['currencyiso'];

			/*order items*/
			$items=$thisOrderDetails['item'];

			/*order summary*/
			$summary['total_price_items']=$thisOrderDetails['total_price_items'];
			$summary['discount']=$thisOrderDetails['discount'];
			$summary['item_tax']=$thisOrderDetails['item_tax'];
			$summary['delivery_charges']=$thisOrderDetails['delivery_charges'];
			$summary['total_price_items']=$thisOrderDetails['total_price_items'];
			$summary['selfPickup']=$thisOrderDetails['selfPickup'];
			$summary['total']=$thisOrderDetails['total'];

			/**customer details**/
			$customer=array();
			$customerlbl=array();
			$protectedKeys=array();
			foreach($this->pluginOptions['order_form'] as $k=>$v){
				$protectedKeys[$v['key']]=$v;
			}
			foreach($thisCustomerDetails as $k=>$v){
				/*****default input fields of this plugin*****/
				if(isset($protectedKeys[$k])){
					$customerlbl[$k]=$protectedKeys[$k]['lbl'];
					if($protectedKeys[$k]['type']!='textarea'){
						$customer[$k]=$v;
					}else{
						$customer[$k]='<div class="wppizza-order-textarea">'.nl2br($v).'</div>';
					}
				}
				if(!isset($protectedKeys[$k]) && is_array($v) && isset($v['label']) && isset($v['value'])){
					$customer[''.$v['label'].'']=$v['value'];
					$customerlbl[''.$v['label'].'']=$v['label'];
				}
			}
			$orderlbl=array();
			foreach($this->pluginOptions['localization'] as $k=>$v){
				$orderlbl[$k]=$v['lbl'];
			}
			/*if template copied to theme directory , use that one otherwise use default**/
			ob_start();
			if (file_exists( get_template_directory() . '/wppizza-show-order.php')){
				include(get_template_directory() . '/wppizza-show-order.php');
			}else{
				include(WPPIZZA_PATH.'templates/wppizza-show-order.php');
			}
			$output .= ob_get_clean();
		}

		return $output;
	}
}
?>