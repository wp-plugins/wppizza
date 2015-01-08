<?php
/**************************************************************************************************************************************

	CLASS - WPPIZZA_ORDER_DETAILS

	gets all order details as array of a specific order by passing it an order id

	currently this is only used for printing the order in the admin order history

	however, over time this will expand to replace the somewhat convoluted coding the plugin uses in different places
	to get these details (when sending/creating emails for example or displaying orders in order history etc

	FUTURE/TODO: get the variables not only from existing order, but also from session data before order has been made/stored


	usage
	require_once(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
	$orderdetails=new WPPIZZA_ORDER_DETAILS();
	$orderdetails->setOrderId($id);


**************************************************************************************************************************************/
if (!class_exists( 'WPPizza' ) ) {return ;}

if (!class_exists('WPPIZZA_ORDER_DETAILS')) {
	class WPPIZZA_ORDER_DETAILS extends WPPIZZA_ACTIONS {

	public $orderId=false;
	public $blogId=false;
	public $decode=false;
	public $plaintext=false;
	public $rtl=false;
	public $translate=false;
	public $session=false;

/**********************************************************************************************
*
*
*	[construct]
*
*
*********************************************************************************************/
		function __construct() {
			parent::__construct();
			/************************
				[add filters]
			************************/
			/**filter order items when returned from db as its all stored in a array**/
			add_filter('wppizza_filter_order_details_db_return', array( $this, 'wppizza_filter_order_db_return'),10,1);
			/**filter transaction id appending db id if so required**/
			add_filter('wppizza_filter_order_details_transaction_id', array( $this, 'wppizza_filter_transaction_id'),10,2);
			/**filter items to sort by category**/
			add_filter('wppizza_filter_print_order_items', array( $this, 'wppizza_filter_items_by_category'),10,2);

		}
/**********************************************************************************************
*
*
*	[public methods]
*
*
*********************************************************************************************/
		/*set  the order id we need to get values for*/
		function setOrderId($orderId){
			$this->orderId=$orderId;
		}
		/**set blog id (might be required in multisite setups - currently not in use )*/
		function setBlogId($blogid){
			$this->blogId=$blogid;
		}
		/*return plaintext instead of html variables if true*/
		function setPlaintext($plaintext){
			$this->plaintext=$plaintext;
		}
		/*decode html encoded things - currently not used but might come in handy at some point*/
		function setDecode($decode){
			$this->decode=$decode;
		}
		/*set right to left - currently not used but might come in handy at some point*/
		function setRtl($rtl){
			$this->rtl=$rtl;
		}
		/*set use translated txt vars - currently not used but might come in handy at some point*/
		function setTranslate($translate){
			$this->translate=$translate;
		}
		/* use session instead of order id  - currently not used but will allow - in future - session data to be used instead of order id (before order has been executed/stored)*/
		function setSession($session){
			$this->session=$session;
		}
		/**********************************
		*
		*	get all order details as array
		*
		**********************************/
		function getOrder(){
			global $wpdb;
			/**select the right blog table if set **/
			if($this->blogId && (int)$this->blogId>1){
				$wpdb->prefix=$wpdb->base_prefix . $this->blogId.'_';
			}
			$orderDetails = $wpdb->get_row("SELECT id, wp_user_id, order_date,  order_update, customer_ini, order_ini, transaction_id, initiator, payment_status, notes FROM " .$wpdb->prefix . "wppizza_orders WHERE id='".(int)$this->orderId."' ");
			if(!is_object($orderDetails)){$orderDetails=false; return $orderDetails;}

			/**unserialize and filter order data**/
			$oOrder = apply_filters('wppizza_filter_order_details_db_return', maybe_unserialize($orderDetails->order_ini));

			/**unserialize customer data**/
			$oCustomer=maybe_unserialize($orderDetails->customer_ini);

			/****************************************************************
				include wpml to use translated localization variables.
				will not affect items (they will always be the translated one's
				or - more accurately - be the ones that were put in the cart
				don't use require once
			****************************************************************/
			if($this->translate && function_exists('icl_translate') && isset($cDetails['wppizza_wpml_lang']) && $cDetails['wppizza_wpml_lang']!=''){
//					global $sitepress;
//					$sitepress->switch_lang($oCustomer['wppizza_wpml_lang']);
//					require(WPPIZZA_PATH .'inc/wpml.inc.php');
//					require(WPPIZZA_PATH .'inc/wpml.gateways.inc.php');
			}
			/***get (possibly wpml'ed) options**/
			$pOptions=$this->pluginOptions;

			/*****************************
				get currency and position
				used in private methods
			******************************/
			$currencydecode=wppizza_email_decode_entities($oOrder['currency'],$this->blogCharset);
			$currency['currency']=$currencydecode;
			$currency['currencyiso']=$oOrder['currencyiso'];
			$currency['left']=$currencydecode.' ';
			$currency['right']='';
			if($pOptions['layout']['currency_symbol_position']=='right'){/*right aligned*/
				$currency['left']='';
				$currency['right']=' '.$currencydecode;
			}


			/**************************************************************************
			*
			*	group variables to use in output
			*
			***************************************************************************/

				/******************************************
					customer
				******************************************/
				$order['customer']=$this->getCustomerDetails($oCustomer,$pOptions);
				/*******************************
					items
				******************************************/
				$order['items']=$this->getItemDetails($oOrder,$currency);
				/******************************************
					summary
				******************************************/
				$order['summary']=$this->getSummaryDetails($oOrder,$currency,$pOptions);
				/******************************************
					localization
				******************************************/
				$order['localization']=$this->getLocalization($pOptions);
				/****************************************
					other order vars (date/payment type/notes  etc)
				*****************************************/
				$order['ordervars']=$this->getOrderDetails($orderDetails,$oOrder,$pOptions,$currency);


			/**allow filter if required - not yet enabled**/
			//$order = apply_filters('wppizza_order_details_filter_order', $order, $pOptions);
			return $order;
		}

/**********************************************************************************************
*
*
*	[private methods]
*
*
*********************************************************************************************/
		/**********************************************************************************************
		*
		*	[order items - private]
		*
		*********************************************************************************************/
		private function getItemDetails($oOrder,$currency){

			$oItems=array();
			foreach($oOrder['item'] as $itemKey=>$item){
				$oItems[$itemKey]=$item;
				/**for convenience, we concat vars into label and value and add them to array */
				$oItems[$itemKey]['label']=''.$item['quantity'].'x '.$item['name'].' '.$item['size'].' ['.$currency['left'].''.$item['price'].''.$currency['right'].']';
			}
			/**filter new/current extend additional info keys - to be run by external plugin if required**/
			$oItems = apply_filters('wppizza_filter_order_extend', $oItems);

			/****************************************************
				[simplify array of item details]
			****************************************************/
			$items=array();
			foreach($oItems as $itemKey=>$item){
				/*single item vars*/
				$items[$itemKey]['postId']			=$item['postId'];
				$items[$itemKey]['name']			=$item['name'];
				$items[$itemKey]['size']			=$item['size'];
				$items[$itemKey]['quantity']		=$item['quantity'];
				$items[$itemKey]['price']			=$currency['left'].$item['price'].$currency['right'];
				$items[$itemKey]['pricetotal']		=$currency['left'].$item['pricetotal'].$currency['right'];
				$items[$itemKey]['value']			=$item['price'];
				$items[$itemKey]['valuetotal']		=$item['pricetotal'];
				$items[$itemKey]['categories']		=$item['categories'];
				$items[$itemKey]['catIdSelected']	=$item['catIdSelected'];
				$items[$itemKey]['currency']		=$currency['currency'];

				/*extended vars*/
				if(!$this->plaintext){/*as html*/
					$items[$itemKey]['addinfo']			=!empty($item['addinfo']) ? $item['addinfo']['html'] : '' ;
				}
				if($this->plaintext){/*as plaintext*/
					$items[$itemKey]['addinfo']			=!empty($item['addinfo']) ? $item['addinfo']['txt'] : '' ;
				}

				/*add above concat vars too*/
				$items[$itemKey]['label']			=$item['label'];

				/**all  vars not used for now**/
				//$items[$itemKey]['all']				=$item;
			}

			/*
			below filters perhaps needed in future for backwards compatibility :
			wppizza_filter_items_by_category
			wppizza_emailplaintext_filter_items
			wppizza_filter_order_items_to_plaintext //if so add filter above in construct (see WPPIZZA_ACTIONS and wppizza.send-order-emails.inc.php)
			wppizza_filter_order_items_html//if so add filter above in construct (see WPPIZZA_ACTIONS and wppizza.send-order-emails.inc.php)
			*/

			return $items;
		}


		/**********************************************************************************************
		*
		*	[customer details - private]
		*
		*********************************************************************************************/
		private function getCustomerDetails($oCustomer,$pOptions){

			/*get enabled plugin order form fields*/
			$setFields=array();
			foreach($pOptions['order_form'] as $k=>$v){
				$setFields[$v['key']]=$v;
			}
			/*apply any filters used*/
			$setFields = apply_filters('wppizza_filter_order_form_fields', $setFields);


			$customer=array();
			$customer['post']=array();
			$customer['other']=array();
			foreach($oCustomer as $k=>$v){
				/*array  field labels, values and type of post values*/
				if(isset($setFields[$k])){
					$customer['post'][$k]=array('label'=>$setFields[$k]['lbl'],'value'=>$oCustomer[$k],'type'=>$setFields[$k]['type']);
				}
				/*array any other saves values*/
				if(!isset($setFields[$k])){
					$customer['other'][$k]=$v;//=array('label'=>$setFields[$k]['lbl'],'value'=>$oCustomer[$k],'type'=>$setFields[$k]['type']);
				}
			}

			/**unset some irrelevant vars**/
			unset($customer['other']['wppizza-gateway']);
			unset($customer['other']['wppizza_hash']);

			/**
				filter if required - probably overkill as there is already a filter above
				but perhaps needed in future for backwards compatibility :

				$customer = apply_filters('wppizza_order_details_filter_customer', $customer);
			**/

			/****************************************************
				[simplify array]
			****************************************************/
			$customerDetails=array();
			foreach($customer['post'] as $key=>$values){
				$customerDetails['post'][$key]['label']=$values['label'];
				$customerDetails['post'][$key]['value']=$values['value'];
				$customerDetails['post'][$key]['type']=$values['type'];
			}
			/*might have been added by other plugins*/
			foreach($customer['other'] as $key=>$values){
				$customerDetails['other'][$key]['label']=$values;
			}

			return $customerDetails;
		}



		/**********************************************************************************************
		*
		*	[order summary - private]
		*	we can probably loose all the currencies here. oh well, who knows maybe useful one day
		*
		*********************************************************************************************/
		private function getSummaryDetails($oDetails,$currency,$pOptions){

			/**********************************************************
			*	[initialize array
			**********************************************************/
			$summary=array();
			/**********************************************************
			*	[cart items
			**********************************************************/
				$summary['cartitems']=array('label'=>($pOptions['localization']['order_items']['lbl']),'price'=>wppizza_output_format_price($oDetails['total_price_items'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			/**********************************************************
			*	[discount]
			**********************************************************/
			if($oDetails['discount']>0){
				$summary['discount']=array('label'=>($pOptions['localization']['discount']['lbl']),'price'=>wppizza_output_format_price($oDetails['discount'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[item tax - tax applied to items only]
			**********************************************************/
			if($oDetails['item_tax']>0 && !($pOptions['order']['shipping_tax'])){
				$summary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>wppizza_output_format_price($oDetails['item_tax'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[delivery charges - no self pickup enabled or selected]
			**********************************************************/
			if($pOptions['order']['delivery_selected']!='no_delivery'){/*delivery disabled*/
			if(!isset($oDetails['selfPickup']) || $oDetails['selfPickup']==0){
				if($oDetails['delivery_charges']!=''){
					$summary['delivery']=array('label'=>($pOptions['localization']['delivery_charges']['lbl']),'price'=>wppizza_output_format_price($oDetails['delivery_charges'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
				}else{
					$summary['delivery']=array('label'=>($pOptions['localization']['free_delivery']['lbl']),'price'=>'','currency'=>'' );
				}
			}
			}
			/**********************************************************
			*	[item tax - tax applied to items only]
			**********************************************************/
			if($oDetails['item_tax']>0 && $pOptions['order']['shipping_tax']){
				$summary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>wppizza_output_format_price($oDetails['item_tax'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			}

			/**********************************************************
			*	[taxes included]
			**********************************************************/
			if($oDetails['taxes_included']>0 && $pOptions['order']['taxes_included']){
				$summary['taxes_included']=array('label'=>sprintf(''.$pOptions['localization']['taxes_included']['lbl'].'',$pOptions['order']['item_tax']),'price'=>wppizza_output_format_price($oDetails['taxes_included'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			}

			/**********************************************************
			*	[handling charges - (most likely to be used for vv payment)]
			**********************************************************/
			if(isset($oDetails['handling_charge']) && $oDetails['handling_charge']>0){
				$summary['handling_charge']=array('label'=>($pOptions['localization']['order_page_handling']['lbl']),'price'=>wppizza_output_format_price($oDetails['handling_charge'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[tips )]
			**********************************************************/
			if(isset($oDetails['tips']) && $oDetails['tips']>0){
				$summary['tips']=array('label'=>($pOptions['localization']['tips']['lbl']),'price'=>wppizza_output_format_price($oDetails['tips'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
				[order total]
			**********************************************************/
				$summary['total']=array('label'=>($pOptions['localization']['order_total']['lbl']),'price'=>wppizza_output_format_price($oDetails['total'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
			/****************************************************
				[self pickup (enabled and selected) / no delivery offered ]
			****************************************************/
			if(isset($oDetails['selfPickup']) && $oDetails['selfPickup']>=1){
				if($oDetails['selfPickup']==1){
					$summary['self_pickup']=array('label'=>($pOptions['localization']['order_page_self_pickup']['lbl']),'price'=>'','currency'=>'' );
				}
				if($oDetails['selfPickup']==2){
					$summary['self_pickup']=array('label'=>($pOptions['localization']['order_page_no_delivery']['lbl']),'price'=>'','currency'=>'' );
				}
			}

			/****************************************************
				[allow filtering of summary]
				currently disabled but perhaps needed in future for backwards compatibility :
				$summary = apply_filters('wppizza_filter_order_summary_parameters_emails', $summary, $oDetails);
			****************************************************/

			/****************************************************
				[simplify array taking account of currency left/right etc]
			****************************************************/
			$summaryDetails=array();
			foreach($summary as $key=>$values){
				$summaryDetails[$key]['label']=$values['label'];
				$summaryDetails[$key]['value']=!empty($values['currency']) ? $currency['left'].$values['price'].$currency['right'] : $values['price'];
			}
			return $summaryDetails;
		}
		/**********************************************************************************************
		*
		*	[miscellaneous single order details - private]
		*
		*********************************************************************************************/
		private function getOrderDetails($orderFields, $oDetails, $pOptions, $currency){

			/***********************
				format some variables
			***********************/
			/**mk timestamp*/
			$oDateTimeStamp=strtotime($orderFields->order_date);
			$uDateTimeStamp=strtotime($orderFields->order_update);
			/***********************
				format dates
			***********************/
			$order_date="".date_i18n(get_option('date_format'),$oDateTimeStamp)." ".date_i18n(get_option('time_format'),$oDateTimeStamp)."";//format by set wordpress date/time
			$order_update="".date_i18n(get_option('date_format'),$uDateTimeStamp)." ".date_i18n(get_option('time_format'),$uDateTimeStamp)."";//format by set wordpress date/time


			/***********************
				add gateway name/label as payment_type to single order vars
			***********************/
			$gateways=new WPPIZZA_GATEWAYS();
			$gateways->wppizza_instanciate_gateways_frontend();
			$gateways->wppizza_wpml_localization_gateways();
			$gwIni=strtoupper($orderFields->initiator);
			/*payment method*/
			$gw_payment_method='CC';/*ini as cc payment*/
			if($gwIni=='COD'){/*COD is always cash*/
				$gw_payment_method='CASH';
			}
			/**ini just using the simple initiator value*/
			$payment_type=$orderFields->initiator;

			/**use full label if exists*/
			if(isset($gateways->pluginGateways[$gwIni])){
				$payment_type=!empty($gateways->pluginGateways[$gwIni]->gatewayOptions['gateway_label']) ? $gateways->pluginGateways[$gwIni]->gatewayOptions['gateway_label'] : $orderFields->initiator;

				/*in case a customised non CC gateway was added, set back to Cash*/
				if($gateways->pluginGateways[$gwIni]->gatewayTypeSubmit=='ajax'){
					$gw_payment_method='CASH';
				}
			}
			/**get string for paymnt method used*/
			$payment_method=$pOptions['localization']['common_value_order_credit_card']['lbl'];
			if($gw_payment_method=='CASH'){
				$payment_method=$pOptions['localization']['common_value_order_cash']['lbl'];
			}
			/***********************
				payment due (if credit card->0)
			***********************/
			$payment_due=$currency['left'].wppizza_output_format_price(0,$pOptions['layout']['hide_decimals']).$currency['right'];
			if($gw_payment_method=='CASH'){
				$payment_due=$currency['left'].wppizza_output_format_price($oDetails['total'],$pOptions['layout']['hide_decimals']).$currency['right'];
			}

			/***********************
				pickup or delivery
			***********************/
			$pickup_delivery=$pOptions['localization']['common_value_order_delivery']['lbl'];
			if(isset($oDetails['selfPickup']) && $oDetails['selfPickup']>=1){
			$pickup_delivery=$pOptions['localization']['common_value_order_pickup']['lbl'];
			}


			/***********************
				return array
			***********************/
			$orderDetails=array();/*ini*/

			/*wp user id - currently unused*/
			$orderDetails['wp_user_id']=array('label'=>$pOptions['localization']['common_label_order_wp_user_id']['lbl'],'value'=>$orderFields->wp_user_id);

			/**order id*/
			$orderDetails['order_id']=array('label'=>$pOptions['localization']['common_label_order_order_id']['lbl'],'value'=>$orderFields->id);

			/**transaction_id*/
			$orderFields->transaction_id = apply_filters('wppizza_filter_order_details_transaction_id', $orderFields->transaction_id, $orderFields->id );
			$orderDetails['transaction_id']=array('label'=>$pOptions['localization']['common_label_order_transaction_id']['lbl'],'value'=>$orderFields->transaction_id);

			/**order_date*/
			$orderDetails['order_date']=array('label'=>$pOptions['localization']['common_label_order_order_date']['lbl'],'value'=>$order_date);

			/**payment_type*/
			$orderDetails['payment_type']=array('label'=>$pOptions['localization']['common_label_order_payment_type']['lbl'],'value'=>$payment_type);

			/**payment_status*/
			$orderDetails['payment_method']=array('label'=>$pOptions['localization']['common_label_order_payment_method']['lbl'],'value'=>$payment_method);

			/**payment_due*/
			$orderDetails['payment_due']=array('label'=>$pOptions['localization']['common_label_order_payment_outstanding']['lbl'],'value'=>$payment_due);

			/**currency - in use but without label*/
			$orderDetails['currency']=array('label'=>$pOptions['localization']['common_label_order_currency']['lbl'],'value'=>$currency['currency']);
			$orderDetails['currencyiso']=array('label'=>$pOptions['localization']['common_label_order_currency']['lbl'],'value'=>$currency['currencyiso']);

			/**pickup/delivery**/
			$orderDetails['pickup_delivery']=array('label'=>$pOptions['localization']['common_label_order_delivery_type']['lbl'],'value'=>$pickup_delivery);

			/*total add here too, might be useful*/
			$orderDetails['total']=array('label'=>$pOptions['localization']['order_total']['lbl'],'value'=>$currency['left'].wppizza_output_format_price($oDetails['total'],$pOptions['layout']['hide_decimals']).$currency['right']);


			/*
				currently unused variables without labels defined yet in localization
				might come in useful somewhere one day
			*/
			//notes
			$orderDetails['notes']=array('label'=>'','value'=>$orderFields->notes);

			//payment_status
			$orderDetails['payment_status']=array('label'=>'','value'=>$orderFields->payment_status);

			//order_update
			$orderDetails['order_update']=array('label'=>'','value'=>$order_update);

			//initiator
			$orderDetails['initiator']=array('label'=>'','value'=>$orderFields->initiator);

			return $orderDetails;
		}
		/**********************************************************************************************
		*
		*	[localization variables - private]
		*
		*********************************************************************************************/
		private function getLocalization($pOptions){
			/**txt variables from settings->localization additional vars > localization_confirmation_form*/
			$localize = array_merge($pOptions['localization'],$pOptions['localization_confirmation_form']);
			$txt=array();
			foreach($localize as $k=>$v){
				if(!$this->plaintext){/*as html*/
					$txt[$k]=$v['lbl'];
				}
				if($this->plaintext){/*as plaintext*/
					$txt[$k]=wppizza_email_decode_entities($v['lbl'],$this->blogCharset);
				}
			}
			return $txt;
		}
	}
}
?>