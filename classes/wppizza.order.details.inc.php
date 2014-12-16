<?php
/********************************************************************************************

	!!!!! NOT YET FOR PRODUCTION AND THEREFORE NOT USED !!!!
	
	
	not used in main plugin, but can be called externally to get 
	all details of a specific order with labels etc in an array
	
	usage
	require_once(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
	$orderdetails=new WPPIZZA_ORDER_DETAILS();
	$orderdetails->setOrderId($id);
	
	
	!!!!! NOT YET FOR PRODUCTION AND THEREFORE NOT USED !!!!

********************************************************************************************/
if (!class_exists( 'WPPizza' ) ) {return ;}

if (!class_exists('WPPIZZA_ORDER_DETAILS')) {
	class WPPIZZA_ORDER_DETAILS extends WPPIZZA {
	
		public $orderId=false;
		public $blogId=false;
	
		function __construct() {
			parent::__construct();
		}
	
		function setOrderId($orderId){
			$this->orderId=$orderId;
		}
	
		function setBlogId($blogid){
			$this->blogId=$blogid;
		}
	
		function getOrder(){
			global $wpdb;
			/**select the right blog table if set **/
			if($this->blogId && (int)$this->blogId>1){
				$wpdb->prefix=$wpdb->base_prefix . $this->blogId.'_';
			}
			$orderDetails = $wpdb->get_row("SELECT id, wp_user_id, order_date,  order_update, customer_ini, order_ini, transaction_id, initiator, payment_status, notes FROM " .$wpdb->prefix . "wppizza_orders WHERE id='".(int)$this->orderId."' ");
			if(!is_object($orderDetails)){$orderDetails=false; return $orderDetails;}
			/**mk timestamp*/
			$oDateTimeStamp=strtotime($orderDetails->order_date);
			$uDateTimeStamp=strtotime($orderDetails->order_update);
			/**unserialize**/
			$oOrder=maybe_unserialize($orderDetails->order_ini);
			$oCustomer=maybe_unserialize($orderDetails->customer_ini);
			
			
			/**single vars**/
			$order['id']=$orderDetails->id;
			$order['wp_user_id']=$orderDetails->wp_user_id;
			$order['order_date']="".date_i18n(get_option('date_format'),$oDateTimeStamp)." ".date_i18n(get_option('time_format'),$oDateTimeStamp)."";//format by set wordpress date/time
			$order['order_update']="".date_i18n(get_option('date_format'),$uDateTimeStamp)." ".date_i18n(get_option('time_format'),$uDateTimeStamp)."";//format by set wordpress date/time
			$order['transaction_id']=$orderDetails->transaction_id;
			$order['payment_status']=$orderDetails->payment_status;
			$order['initiator']=$orderDetails->initiator;
			$order['notes']=$orderDetails->notes;
			$order['currency']=$oOrder['currency'];
			$order['currencyiso']=$oOrder['currencyiso'];	
			/**items**/
			$order['items']=$this->getItemDetails($oOrder['item'],$oOrder['currency']);
			/**customer**/
			$order['customer']=$this->getCustomerDetails($oCustomer);
			/**summary**/
			$order['summary']=$this->getSummaryDetails($oOrder);
			
			/**filter if required**/
			$order = apply_filters('wppizza_order_details_filter_order', $order);			
			return $order;
		}
		/**********************************************************************************************
		*
		*
		*	[customer details]
		*
		*
		*********************************************************************************************/		
		private function getCustomerDetails($oCustomer){

			/*get enabled plugin order form fields*/
			$setFields=array();
			foreach($this->pluginOptions['order_form'] as $k=>$v){
				$setFields[$v['key']]=$v;
			}
			/*apply any filters used*/
			$setFields = apply_filters('wppizza_filter_order_form_fields', $setFields);
			
						
			$customerDetails=array();			
			foreach($oCustomer as $k=>$v){
				/*array  field labels, values and type of post values*/
				if(isset($setFields[$k])){
					$customerDetails['post'][$k]=array('label'=>$setFields[$k]['lbl'],'value'=>$oCustomer[$k],'type'=>$setFields[$k]['type']);
				}
				/*array any other saves values*/
				if(!isset($setFields[$k])){
					$customerDetails['other'][$k]=$v;//=array('label'=>$setFields[$k]['lbl'],'value'=>$oCustomer[$k],'type'=>$setFields[$k]['type']);
				}
			}
			
			/**unset some irrelevant vars**/
			unset($customerDetails['other']['wppizza-gateway']);
			unset($customerDetails['other']['wppizza_hash']);
			
			
			
			//**filter if required - probably overkill as there is already a filter above**/
			//$customer = apply_filters('wppizza_order_details_filter_customer', $customer);
			return $customerDetails;
		}
		/**********************************************************************************************
		*
		*
		*	[order items]
		*
		*
		*********************************************************************************************/		
		private function getItemDetails($oItems,$currency){
				
			$items=array();
			foreach($oItems as $itemKey=>$item){
				$items[$itemKey]['postId']			=$item['postId'];
				$items[$itemKey]['name']			=$item['name'];
				$items[$itemKey]['size']			=$item['size'];
				$items[$itemKey]['quantity']		=$item['quantity'];				
				$items[$itemKey]['price']			=$item['price'];
				$items[$itemKey]['pricetotal']		=$item['pricetotal'];
				$items[$itemKey]['catIdSelected']	=$item['catIdSelected'];
				$items[$itemKey]['categories']		=$item['categories'];
				$items[$itemKey]['extenddata']		=$item['extenddata'];
				$items[$itemKey]['currency']		=$currency;
				
				
				/**entire array**/
				//$items[$itemKey]['all']				=$item;
				
				
				//if(isset($item['additional_info']) && trim($item['additional_info'])!=''){
				//$items[$itemKey]['additionalinfo']	=$item['additional_info'];
				//}				
			}
			/**filter old legacy additional info keys**/
			//$items = apply_filters('wppizza_filter_order_additional_info', $items);
			/**filter new/current extend additional info keys**/
			$items = apply_filters('wppizza_filter_order_extend', $items);
			return $items;
		}
		
		
		
		/**********************************************************************************************
		*
		*
		*	[order summary]
		*	--we can probably loose all the currencies here. oh well. who knows maybe useful one day
		*
		*********************************************************************************************/		
		private function getSummaryDetails($oDetails){
			
//			/****************************************************************
//				include wpml to also send store/emails translated.
//				will not affect items (they will always be the translated one's
//				or - more accurately - be the ones that were put in the cart
//				don't use require once
//			****************************************************************/
//			/**set appropriate language. as this can be a language agnostic ipn request, set it specifically depending on what was stored in the db**/
//			if(function_exists('icl_translate') && isset($cDetails['wppizza_wpml_lang']) && $cDetails['wppizza_wpml_lang']!=''){
//					global $sitepress;
//					$sitepress->switch_lang($cDetails['wppizza_wpml_lang']);
//					require(WPPIZZA_PATH .'inc/wpml.inc.php');
//					require(WPPIZZA_PATH .'inc/wpml.gateways.inc.php');
//			}
			/***get (possibly wpml'ed) options**/
			$pOptions=$this->pluginOptions;			

			$summary=array();
			/**********************************************************
			*	[cart items
			**********************************************************/
				$summary['cartitems']=array('label'=>($pOptions['localization']['order_items']['lbl']),'price'=>$oDetails['total_price_items'],'currency'=>$oDetails['currency'] );
			/**********************************************************
			*	[discount]
			**********************************************************/
			if($oDetails['discount']>0){
				$summary['discount']=array('label'=>($pOptions['localization']['discount']['lbl']),'price'=>$oDetails['discount'],'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[item tax - tax applied to items only]
			**********************************************************/
			if($oDetails['item_tax']>0 && !($pOptions['order']['shipping_tax'])){
				$summary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>$oDetails['item_tax'],'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[delivery charges - no self pickup enabled or selected]
			**********************************************************/
			if($pOptions['order']['delivery_selected']!='no_delivery'){/*delivery disabled*/
			if(!isset($oDetails['selfPickup']) || $oDetails['selfPickup']==0){
				if($oDetails['delivery_charges']!=''){
					$summary['delivery']=array('label'=>($pOptions['localization']['delivery_charges']['lbl']),'price'=>$oDetails['delivery_charges'],'currency'=>$oDetails['currency'] );
				}else{
					$summary['delivery']=array('label'=>($pOptions['localization']['free_delivery']['lbl']),'price'=>'','currency'=>'' );
				}
			}
			}
			/**********************************************************
			*	[item tax - tax applied to items only]
			**********************************************************/
			if($oDetails['item_tax']>0 && $pOptions['order']['shipping_tax']){
				$summary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>$oDetails['item_tax'],'currency'=>$oDetails['currency'] );
			}

			/**********************************************************
			*	[taxes included]
			**********************************************************/
			if($oDetails['taxes_included']>0 && $pOptions['order']['taxes_included']){
				$summary['taxes_included']=array('label'=>sprintf(''.$pOptions['localization']['taxes_included']['lbl'].'',$pOptions['order']['item_tax']),'price'=>$oDetails['taxes_included'],'currency'=>$oDetails['currency'] );
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
				$summary['total']=array('label'=>($pOptions['localization']['order_total']['lbl']),'price'=>$oDetails['total'],'currency'=>$oDetails['currency'] );
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
			****************************************************/
			$summary = apply_filters('wppizza_filter_order_summary_parameters_emails', $summary, $oDetails);        
			return $summary;
		}

		
	}
}
?>