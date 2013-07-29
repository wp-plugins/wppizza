<?php
if (!class_exists( 'WPPizza' ) ) {return;}

	class WPPIZZA_SEND_ORDER_EMAILS extends WPPIZZA_ACTIONS {

		function __construct() {
			parent::__construct();

			$this->wppizza_order_emails_extend();

			/**blog charset*/
			$this->blogCharset=get_bloginfo('charset');
			/**timestamp the order*/
			$currentTime= current_time('timestamp');
			//$this->orderTimestamp =date("d-M-Y H:i:s", current_time('timestamp'));
			$this->orderTimestamp ="".date_i18n(get_option('date_format'),$currentTime)." ".date_i18n(get_option('time_format'),$currentTime)."";
			
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

			/** overwrite subject vars for email subject**/
			if (file_exists( get_template_directory() . '/wppizza-order-email-subject.php')){
				/**copy to template directory to keep settings**/
				include(get_template_directory() . '/wppizza-order-email-subject.php');
			}else{
				include(WPPIZZA_PATH.'templates/wppizza-order-email-subject.php');
			}

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


		function wppizza_order_construct_email(){

			/**cart contents**/
			$cartContents=wppizza_order_summary($_SESSION[$this->pluginSession],$this->pluginOptions);
			/**easier to handle if we put this into its own var**/
			$currency=$cartContents['currency'];

			$email['plaintext']='';
			$email['html']=array();

			/*********************************************************************
				customer details from order page
			**********************************************************************/
			$customer_details ="".PHP_EOL.PHP_EOL;
			$customer_details_array =array();
			$protectedKeys=array();
			ksort($this->pluginOptions['order_form']);
			foreach($this->pluginOptions['order_form'] as $k=>$v){
				if(($v['enabled'])){
					/**protect this key, so no other extension uses it below*/
					$protectedKeys[$v['key']]=1;

					if($v['type']!='textarea'){/*pad non-textareas*/
						$strPartLeft=''.$v['lbl'].'';
						$spaces=45-strlen($strPartLeft);
						$strPartRight=''.strip_tags($this->orderPostVars[$v['key']]);

						$customer_details.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
					}else{
						$customer_details.=PHP_EOL;
						$customer_details.=''.$v['lbl'].PHP_EOL;
						$customer_details.= wordwrap(strip_tags($this->orderPostVars[$v['key']]), 72, "\n", true);
						$customer_details.=PHP_EOL;
					}
				/**html emails**/
				/**stick into array for use in html emails**/
				if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
					$customer_details_array[]=array('label'=>wppizza_email_html_entities($v['lbl']),'value'=>wppizza_email_html_entities($this->orderPostVars[$v['key']]));
				}
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
			$i=0;
			foreach($this->orderPostVars as $k=>$v){
				if(is_array($v) && isset($v['label']) && isset($v['value']) && !isset($protectedKeys[$k]) ){
					if($i==0){$customer_details .=PHP_EOL;}/*add one empty line for readabilities sake*/
					$strPartLeft=''.$v['label'].'';
					$spaces=45-strlen($strPartLeft);
					$strPartRight=''.strip_tags($v['value']);

					$customer_details.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;

					/**html emails**/
					/**stick into array for use in html emails**/
					if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
						$customer_details_array[]=array('label'=>wppizza_email_decode_entities($v['label'],$this->blogCharset),'value'=>wppizza_email_decode_entities($strPartRight,$this->blogCharset));
					}
				$i++;
				}
			}

			/*****************************************************************************************
				order details
			****************************************************************************************/
			$order=PHP_EOL."==============".$this->pluginOptions['localization']['order_details']['lbl']."=========================".PHP_EOL;
			$order.=PHP_EOL.$this->orderTimestamp."".PHP_EOL.PHP_EOL;
			$order_items =array();
			$order_summary =array();

			foreach($cartContents['items'] as $k=>$v){
				/*tabs dont seem to work reliably, so lets try to put some even space between order item and total**/
				$strPartLeft=''.$v['count'].'x '.$v['name'].' '.$v['size'].'  '.$currency.' '.wppizza_output_format_price(wppizza_output_format_float($v['price']),$this->pluginOptions['layout']['hide_decimals']).'';
				$spaces=55-strlen($strPartLeft);
				$strPartRight=''.$currency.' '.wppizza_output_format_price(wppizza_output_format_float($v['pricetotal']),$this->pluginOptions['layout']['hide_decimals']).'';
				$order.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
				if(isset($v['additionalinfo']) && is_array($v['additionalinfo'])){
					$addInfo=array();
					foreach($v['additionalinfo'] as $additionalInfo){
						$addInfo[]=''.$additionalInfo.'';
					}
					$order.="   ".implode(", ",$addInfo);
					$order.=PHP_EOL.PHP_EOL;
				}
				/**html emails**/
				if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
					if(isset($v['additionalinfo']) && is_array($v['additionalinfo'])){
						$addInfoHtml=wppizza_email_html_entities(implode(", ",$addInfo));
					}else{
						$addInfoHtml='';
					}
					$order_items[]=array('label'=>wppizza_email_html_entities($strPartLeft),'value'=>wppizza_email_html_entities($strPartRight),'additional_info'=>$addInfoHtml);
				}

			}
			$order.=PHP_EOL.PHP_EOL;
			$order.=''.$cartContents['order_value']['total_price_items']['lbl'].': '.$currency.' '.($cartContents['order_value']['total_price_items']['val']).PHP_EOL;

			/**html emails**/
			if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
				$order_summary['cartitems']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['total_price_items']['lbl']),'price'=>$cartContents['order_value']['total_price_items']['val'],'currency'=>$currency );
			}

			if($cartContents['order_value']['discount']['val']>0){
				$order.=''.$cartContents['order_value']['discount']['lbl'].': - '.$currency.' '.($cartContents['order_value']['discount']['val']).PHP_EOL;
				/**html emails**/
				if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
					$order_summary['discount']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['discount']['lbl']),'price'=>$cartContents['order_value']['discount']['val'],'currency'=>$currency );
				}
			}

			/**********************************************************
				[item tax]
			**********************************************************/
			if($cartContents['order_value']['item_tax']['val']>0){
				$order.=$cartContents['order_value']['item_tax']['lbl'].': '.$currency.' '.($cartContents['order_value']['item_tax']['val']).PHP_EOL;
					/**html emails**/
					if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
						$order_summary['item_tax']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['item_tax']['lbl']),'price'=>$cartContents['order_value']['item_tax']['val'],'currency'=>$currency );
					}
			}

			/**********************************************************
				[delivery charges - no self pickup enabled or selected]
			**********************************************************/
			if(!isset($cartContents['selfPickup']) || $cartContents['selfPickup']==0){
				if($cartContents['order_value']['delivery_charges']['val']!=''){
					$order.=$cartContents['order_value']['delivery_charges']['lbl'].': '.$currency.' '.($cartContents['order_value']['delivery_charges']['val']).PHP_EOL;
					/**html emails**/
					if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
						$order_summary['delivery']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['delivery_charges']['lbl']),'price'=>$cartContents['order_value']['delivery_charges']['val'],'currency'=>$currency );
					}
				}else{
					$order.=$cartContents['order_value']['delivery_charges']['lbl'].PHP_EOL;
					/**html emails**/
					if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
						$order_summary['delivery']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['delivery_charges']['lbl']),'price'=>'','currency'=>'' );
					}
				}
			}

			/**********************************************************
				[order total]
			**********************************************************/
			$order.=PHP_EOL;
			$order.=$cartContents['order_value']['total']['lbl'].': '.$currency.' '.($cartContents['order_value']['total']['val']).PHP_EOL;
			/**html emails**/
			if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
				$order_summary['total']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['total']['lbl']),'price'=>$cartContents['order_value']['total']['val'],'currency'=>$currency );
			}
			$order.=PHP_EOL;


			/****************************************************
				[self pickup - enabled and selected]
			****************************************************/
			if(isset($cartContents['selfPickup']) && $cartContents['selfPickup']==1){
				$order.=PHP_EOL.wordwrap(strip_tags($cartContents['order_page_self_pickup']), 72, "\n", true).PHP_EOL;
				/**html emails**/
				if($this->pluginOptions['plugin_data']['mail_type']=='phpmailer'){
					$order_summary['self_pickup']=array('label'=>wppizza_email_html_entities($cartContents['order_page_self_pickup']),'price'=>'','currency'=>'' );
				}
			}


			$order.=PHP_EOL."====================================================".PHP_EOL;

			/**now decode any funny entities**/
			$order=wppizza_email_decode_entities($order,$this->blogCharset);


			$email['plaintext']="".$customer_details.PHP_EOL.$order.PHP_EOL ;
			$email['customer_details']=$customer_details;
			$email['order_details']=$order;

			$email['html']['customer_details']=$customer_details_array;
			$email['html']['order_items']=$order_items;
			$email['html']['order_summary']=$order_summary;
			$email['currency']=$currency;

			return $email;
		}

		/*****************************************************************************************************************************
		*
		*	[choose function to send email]
		*	[send order email]
		*	[return status -> "true" if successful, "false" if not]
		*	[if false, will return error message and var[mailer] to indicate which function was used]
		*
		*****************************************************************************************************************************/
		function wppizza_order_send_email(){
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
				$nowdate=$this->orderTimestamp;

				$mail = new PHPMailer(true);
				/**to be used in html template**/
				$customer_details_array=$this->orderMessage['html']['customer_details'];
				$order_items=$this->orderMessage['html']['order_items'];
				$order_summary=$this->orderMessage['html']['order_summary'];
				$order_summary=$this->orderMessage['html']['order_summary'];
				$currency=$this->orderMessage['currency'];

				/*return $orderHtml*/
				$orderHtml='';
				if (file_exists( get_template_directory() . '/wppizza-order-html-email.php')){
					require_once(get_template_directory_uri().'/wppizza-order-html-email.php');
				}else{
					require_once(WPPIZZA_PATH.'templates/wppizza-order-html-email.php');
				}
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
				$res = $wpdb->get_row("SELECT id,transaction_id,order_ini,customer_ini FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id='".$res."' ");
			}

			$thisOrderTransactionId=$res->transaction_id;
			$thisOrderDetails=unserialize($res->order_ini);
			$thisCustomerDetails=unserialize($res->customer_ini);

			/*organize vars to make them easier to use in template**/
			$order=array();
			/*transaction id*/
			$order['transaction_id']=$thisOrderTransactionId;
			$order['transaction_date_time']="".date_i18n(get_option('date_format'),$thisOrderDetails['time'])." ".date_i18n(get_option('time_format'),$thisOrderDetails['time'])."";

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

			/*customer details*/
			//print_r($thisCustomerDetails);
			$customer=$thisCustomerDetails;
			$customerlbl=array();
			foreach($this->pluginOptions['order_form'] as $k=>$v){
				$customerlbl[$v['key']]=$v['lbl'];
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