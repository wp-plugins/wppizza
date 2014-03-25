<?php
if (!class_exists( 'WPPizza' ) ) {return ;}
class WPPIZZA_GATEWAYS extends WPPIZZA {
	protected $pluginGateways;
	private $gatewayOrderDetails;
	private $gatewayOrderId;
	function __construct() {
    	parent::__construct();

		add_action('init', array( $this, 'wppizza_add_default_gateways'));/*add default COD gateway*/
		/************************************************************************
			[runs only for frontend]
		*************************************************************************/
		if(!is_admin()){
			add_action('init', array( $this, 'wppizza_instanciate_gateways_frontend'));
			add_action('init', array( $this, 'wppizza_do_gateways'));/**output available gateway choices on order page**/
			add_action('init', array( $this,'wppizza_gateway_initialize_order'));/*initialize oder into db */
		}

		/************************************************************************
			[load wpml from parent. used in ajax call, so must be available front and backend ]
		*************************************************************************/
		add_action('init', array( $this, 'wppizza_wpml_localization'),99);

		/************************************************************************
			[runs only in backend]
		*************************************************************************/
		add_action('admin_init', array( $this, 'wppizza_available_gateways'),1);/**check if a gateways has been (un)installed and if so, update option**/
		add_action('admin_init', array( $this, 'wppizza_load_gateways_admin'));

	}

	function wppizza_do_gateways() {
		add_action('wppizza_choose_gateway', array( $this, 'wppizza_choose_gateway'));
	}

	function wppizza_load_gateways_admin() {
		$allClasses=get_declared_classes();
		foreach ($allClasses AS $class){
			$chkStr=substr($class,0,16);
			if($chkStr=='WPPIZZA_GATEWAY_'){
				$c=new $class;
			}
		}
	}

	function wppizza_instanciate_gateways_frontend() {

		/**display surcharges pre order**/
		$this->showSurchageBeforOrder=false;
		
		/**get the selected gateway and associated classname*/
		$wppizzaGatewayCount=0;
		$wppizzaGateway=array();
		$wppizzaGatewayOptions=array();
		if(isset($this->pluginOptions['gateways']['gateway_selected']) && is_array($this->pluginOptions['gateways']['gateway_selected'])){
		foreach($this->pluginOptions['gateways']['gateway_selected'] as $gw=>$enbld){
			if($enbld){/**only add enabled gateways**/
				$gatewayClass="WPPIZZA_GATEWAY_".strtoupper($gw);
				$wppizzaGateway[$gw]=new $gatewayClass;
				$wppizzaGatewayOptions[$gw]=$wppizzaGateway[$gw];
				
				
				/***  to display surcharges in orderpage prior to ordering****/
				/**set first gateway**/
				if($wppizzaGatewayCount==0){
					$this->pluginGatewaySelected=$gw;
				}
				/***check if surcharges are calculated by the gateway rather than set by admin**/
				//$wppizzaGateway[$gw]->gatewaySurchargeAtCheckout=false;
				if(isset($wppizzaGateway[$gw]->gatewaySurchargeAtCheckout)){
					$this->showSurchageBeforOrder=true;	
				}
				

				/***check (for legacy reasons) if we have the relevant vars in gateway plugin to calculate surcharges on order page **/
				if(isset($wppizzaGateway[$gw]->gatewaySurchargePercent) && isset($wppizzaGateway[$gw]->gatewaySurchargeFixed)){
					$wppizzaGateway[$gw]->surchargePc=$wppizzaGateway[$gw]->gatewayOptions[$wppizzaGateway[$gw]->gatewaySurchargePercent];
					$wppizzaGateway[$gw]->surchargeFixed=$wppizzaGateway[$gw]->gatewayOptions[$wppizzaGateway[$gw]->gatewaySurchargeFixed];
					/**check if any of the values are >0 **/
					if($wppizzaGateway[$gw]->gatewayOptions[$wppizzaGateway[$gw]->gatewaySurchargePercent]>0 || $wppizzaGateway[$gw]->gatewayOptions[$wppizzaGateway[$gw]->gatewaySurchargeFixed]>0 ){
						$this->showSurchageBeforOrder=true;/*if any gateway has surcharges>0 calculate and display**/
					}
				}else{
						$wppizzaGateway[$gw]->surchargePc=0;
						$wppizzaGateway[$gw]->surchargeFixed=0;
				}				
			$wppizzaGatewayCount++;
			}
		}}

		/**if we have not yet set a gateway , use the first one **/
		if(!isset($_SESSION[$this->pluginSessionGlobal]['userdata']['gateway'])){
			$_SESSION[$this->pluginSession]['gateway-selected']['gw']=strtolower($this->pluginGatewaySelected);
			$_SESSION[$this->pluginSession]['gateway-selected']['surchargePc']=$wppizzaGateway[$this->pluginGatewaySelected]->surchargePc;
			$_SESSION[$this->pluginSession]['gateway-selected']['surchargeFixed']=$wppizzaGateway[$this->pluginGatewaySelected]->surchargeFixed;
			$_SESSION[$this->pluginSession]['gateway-selected']['surchargeAtCheckout']=!empty($wppizzaGateway[$this->pluginGatewaySelected]->gatewaySurchargeAtCheckout) ? true:false;
		}	
		/***switch gw via ajax and reload page*****/
		if(isset($_SESSION[$this->pluginSessionGlobal]['userdata']['gateway'])){
			$selGw=$_SESSION[$this->pluginSessionGlobal]['userdata']['gateway'];
			/**lets just make double sure this exists in case soemone feels the need to mess around with the html values**/
			if(isset($wppizzaGateway[$selGw])){
				if(isset($_SESSION[$this->pluginSession]['gateway-selected'])){
					unset($_SESSION[$this->pluginSession]['gateway-selected']);
				}
				$_SESSION[$this->pluginSession]['gateway-selected']['gw']=strtolower($selGw);
				$_SESSION[$this->pluginSession]['gateway-selected']['surchargePc']=$wppizzaGateway[$selGw]->surchargePc;
				$_SESSION[$this->pluginSession]['gateway-selected']['surchargeFixed']=$wppizzaGateway[$selGw]->surchargeFixed;
				$_SESSION[$this->pluginSession]['gateway-selected']['surchargeAtCheckout']=!empty($wppizzaGateway[$selGw]->gatewaySurchargeAtCheckout) ? true:false;
			}
		}
			
		/**add a hidden flag in frontend to display surcharges on gateway change and set initial session**/
		if($this->showSurchageBeforOrder){
			add_action('wppizza_choose_gateway',array($this,'wppizza_recalculate_handling'));
		}

		$this->pluginGateways=$wppizzaGateway;

	return $wppizzaGatewayOptions;
	}

/****************************************************************************
*
*	[Gateway Recalc Handling Charge]
*	[check if we need to recalculate handling charges on order page]
****************************************************************************/
	function wppizza_recalculate_handling(){
		print"<input type='hidden' id='wppizza_calc_handling' />";	
	}
/***********************************************
	[include default COD "gateway"]
***********************************************/
	function wppizza_add_default_gateways() {
		require_once(WPPIZZA_PATH .'classes/wppizza-gateway-cod.php');
	}

	/**display choices***/
	function wppizza_choose_gateway(){
		$displayAsDropdown=$this->pluginOptions['gateways']['gateway_select_as_dropdown'];
		$selectLabel=$this->pluginOptions['gateways']['gateway_select_label'];
		$enabledGateways=$this->pluginGateways;
		if(count($enabledGateways)>0){
			/**display choice of more than one**/
			if(count($enabledGateways)>1){
				print"<div class='wppizza-gateways'>";
					/**as dropdown**/
					if($displayAsDropdown){
						print"<label class='wppizza-gw-label'>".$selectLabel."</label>";
						print"<select name='wppizza-gateway' /> ";
						$i=0;
						foreach($enabledGateways as $key=>$gw){
							$key=strtolower($key);
							/*******************************************************************************
								if we want to submit directly via ajax (other than cod which does this anyway),
								without sending to any gateway (lets say bacs or something)
								check if $this->gatewaySubmit isset and set to ajax and add class as required
								so we can identify this
							********************************************************************************/
							$gwAddClass='';
							if(isset($gw->gatewayTypeSubmit) && $gw->gatewayTypeSubmit=='ajax'){
								$gwAddClass=' class="wppizzaGwAjaxSubmit"';
							}
							if(isset($gw->gatewayTypeSubmit) && $gw->gatewayTypeSubmit=='custom'){/*customised*/
								$gwAddClass=' class="wppizzaGwCustom"';
							}
							print"<option value='".$key."' ".$gwAddClass." ".selected($_SESSION[$this->pluginSession]['gateway-selected']['gw'],$key,false)." />";
								print"".empty($gw->gatewayOptions['gateway_label']) ? $gw->gatewayName : $gw->gatewayOptions['gateway_label'] ." ";
							print"</option>";

						$i++;
						}
						print"</select>";
					}else{
						print"<label class='wppizza-gw-label'>".$selectLabel."</label>";
						$i=0;
						foreach($enabledGateways as $key=>$gw){
							$key=strtolower($key);
							/*******************************************************************************
								if we want to submit directly via ajax (other than cod which does this anyway),
								without sending to any gateway (lets say bacs or something)
								check if $this->gatewaySubmit isset and set to ajax and add class as required
								so we can identify this
							********************************************************************************/
							$gwAddClass='';
							if(isset($gw->gatewayTypeSubmit) && $gw->gatewayTypeSubmit=='ajax'){
								$gwAddClass=' class="wppizzaGwAjaxSubmit"';
							}
							if(isset($gw->gatewayTypeSubmit) && $gw->gatewayTypeSubmit=='custom'){/*customised*/
								$gwAddClass=' class="wppizzaGwCustom"';
							}						
							print"<div id='wppizza-gw-".$key."' class='wppizza-gw-button button'>";
									print"<label>";
									print"<input type='radio' name='wppizza-gateway' id='wppizza-gateway-".$key."' ".$gwAddClass." value='".$key."' ".checked($_SESSION[$this->pluginSession]['gateway-selected']['gw'],$key,false)."/> ";
									print"".!empty($gw->gatewayImage) ? $gw->gatewayImage : '' ." ";
									print"".empty($gw->gatewayOptions['gateway_label']) ? $gw->gatewayName : $gw->gatewayOptions['gateway_label'] ." ";
									print"</label>";
									print"".!empty($gw->gatewayOptions['gateway_info']) ? '<span class="wppizza-gateway-addinfo">'.$gw->gatewayOptions['gateway_info'].'</span>' : '' ." ";
							print"</div>";
						$i++;
						}
					}
				print"</div>";
				echo $this->wppizza_gateway_standard_button();
			}

			/**only one gateway just display button and add hidden field**/
			if(count($enabledGateways)==1){
				foreach($enabledGateways as $key=>$gw){
					$key=strtolower($key);
					/*******************************************************************************
						if we want to submit directly via ajax (other than cod which does this anyway),
						without sending to any gateway (lets say bacs or something)
						check if $this->gatewaySubmit isset and set to ajax and add class as required
						so we can identify this
					********************************************************************************/
					$gwAddClass='';
					if(isset($gw->gatewayTypeSubmit) && $gw->gatewayTypeSubmit=='ajax'){
						$gwAddClass=' class="wppizzaGwAjaxSubmit"';
					}
					if(isset($gw->gatewayTypeSubmit) && $gw->gatewayTypeSubmit=='custom'){/*customised*/
								$gwAddClass=' class="wppizzaGwCustom"';
					}					
					/**add hidden value so the ajax call knows whether its cod or anything else*/
					print"<input type='hidden' name='wppizza-gateway' id='wppizza-gateway-".$key."' ".$gwAddClass." value='".$key."' /> ";
					/**if this method has not been defined in class or is empty, display standard button**/
					if(method_exists($gw,'gateway_button') && $gw->gateway_button()!=''){
						echo $gw->gateway_button();
					}else{
						echo $this->wppizza_gateway_standard_button();
					}
				}
			}
		}
	}
	/***********************************************
	[standard "send order" button if not defined or empty in class]
	 MUST have class=wppizza-ordernow]
	***********************************************/
	function wppizza_gateway_standard_button() {
		$cart=wppizza_order_summary($_SESSION[$this->pluginSession],$this->pluginOptions , 'gatewaybuttons' );
		if($cart['nocheckout']!=''){
			$standardButton='<div class="wppizza-order-nocheckout">'.$cart['nocheckout'].'</div>';
		}else{
			$standardButton='<input class="submit wppizza-ordernow" type="submit" style="display:block" value="'.$this->pluginOptions['localization']['send_order']['lbl'].'" />';
		}
		return $standardButton;
	}
	/***********************************************
	*
	*	[array of items, tax, delivery charges etc]
	*	[(can be) used when creating/storing hash etc in
	*	in db for paymant gatways]
	*
	***********************************************/
	function wppizza_gateway_order_details($addVars=array()) {
		$gatewayOrder=array();
		$cartDetails=wppizza_order_summary($_SESSION[$this->pluginSession],$this->pluginOptions, 'orderdetails');
		$gatewayOrder['currencyiso']=$cartDetails['currencyiso'];
		$gatewayOrder['currency']=$cartDetails['currency'];
		foreach($cartDetails['items'] as $k=>$v){
			$gatewayOrder['item'][$k]['postId']=''.$v['postId'].'';
			$gatewayOrder['item'][$k]['name']=''.$v['name'].'';
			$gatewayOrder['item'][$k]['size']=''.$v['size'].'';
			$gatewayOrder['item'][$k]['count']=''.$v['count'].'';
			$gatewayOrder['item'][$k]['quantity']=''.$v['count'].'';/*legacy some customised templates may use this*/
			$gatewayOrder['item'][$k]['price']=''.wppizza_validate_float_only($v['price']).'';
			$gatewayOrder['item'][$k]['pricetotal']=''.wppizza_validate_float_only($v['pricetotal']).'';
			$gatewayOrder['item'][$k]['categories']=$v['categories'];
			/**add any additional info to name*/
			$addInfo=array();
			if(is_array($v['additionalinfo']) && count($v['additionalinfo'])>0){foreach($v['additionalinfo'] as $additionalInfo){
				$addInfo[]=''.$additionalInfo.'';
			}}
			//$gatewayOrder['item'][$k]['additionalinfo']=implode("",$addInfo);
			$gatewayOrder['item'][$k]['additionalinfo']=$addInfo;
			$gatewayOrder['item'][$k]['additionalInfo']=implode(" ",$addInfo);/*legacy paypal and order thank you page (note upper case I)*/
			$gatewayOrder['item'][$k]['extend']=$v['extend'];
			$gatewayOrder['item'][$k]['extenddata']=$v['extenddata'];/**to store data (keys, id's count, prices  etc) in the db to maybe retrieve later, put it in this key**/
			$gatewayOrder['item'][$k]['catIdSelected']=wppizza_validate_int_only($v['catIdSelected']);/**store selected category.**/
		}

		$gatewayOrder['total_price_items']=wppizza_validate_float_only($cartDetails['order_value']['total_price_items']['val']);
		$gatewayOrder['discount']=wppizza_validate_float_only($cartDetails['order_value']['discount']['val']);
		$gatewayOrder['taxrate']=wppizza_validate_float_only($cartDetails['taxrate']);
		$gatewayOrder['item_tax']=wppizza_validate_float_only($cartDetails['order_value']['item_tax']['val']);
		$gatewayOrder['taxes_included']=wppizza_validate_float_only($cartDetails['order_value']['taxes_included']['val']);

		$gatewayOrder['delivery_charges']=!empty($cartDetails['order_value']['delivery_charges']['val']) ? wppizza_validate_float_only($cartDetails['order_value']['delivery_charges']['val']) : '';
		$gatewayOrder['tips']=!empty($cartDetails['tips']['val']) ? wppizza_validate_float_only($cartDetails['tips']['val']) : '';

		$gatewayOrder['selfPickup']=!empty($cartDetails['selfPickup']) ? wppizza_validate_int_only($cartDetails['selfPickup']) : 0;
		$gatewayOrder['total']=wppizza_validate_float_only($cartDetails['order_value']['total']['val']);

		/**add any additional variables are set we want to pass/hash*/
		foreach($addVars as $k=>$v){
			$gatewayOrder[$k]=$v;
		}

		/*****created and return checkable hash**/
		$cartHash=wppizza_mkHash($gatewayOrder);/*make unique hash*/
		$gatewayOrder['hash']=$cartHash['hash'];/*add hash to array*/
		$gatewayOrder['order_ini']=$cartHash['order_ini'];/*add orig hash string to array*/

		return $gatewayOrder;
	}
	/***********************************************
		[check if gateways have changed/been (de)activated
		and update option accordingly (provided its not
		first install or old version anyway]
	***********************************************/
	function wppizza_available_gateways() {
		if(isset($this->pluginOptions['gateways']['gateway_selected'])){
		/**variable to store current gateways option**/
		$currentGateways=$this->pluginOptions['gateways']['gateway_selected'];

		/*get available gateway classes, **/
		$availableGateways=array();
		foreach (get_declared_classes() AS $class){
			$chkStr=substr($class,0,16);
			$iDent=substr($class,16);
			if($chkStr=='WPPIZZA_GATEWAY_'){
					$availableGateways[$iDent]=false;
			}
		}

		/**unset gateway from option if it's not in available classes****/
		foreach($this->pluginOptions['gateways']['gateway_selected'] as $k=>$v){
			if(!in_array($k,array_keys($availableGateways))){
				unset($this->pluginOptions['gateways']['gateway_selected'][$k]);
			}
		}

		/**get all additionally enabled gateways and initialise as disabled**/
		foreach($availableGateways as $k=>$v){
			if(!in_array($k,array_keys($currentGateways))){
				$this->pluginOptions['gateways']['gateway_selected'][$k]=false;
			}
		}

		/**gateway array has changed-> update option*/
		if($currentGateways!=$this->pluginOptions['gateways']['gateway_selected']){
			$updateOptions=$this->pluginOptions;
			/**overwrite gateway selected with new array**/
			$updateOptions['gateways']['gateway_selected']=$this->pluginOptions['gateways']['gateway_selected'];
			/**update options**/
			update_option($this->pluginSlug, $updateOptions );
		}

	}}
	/************************************************************************************************
	*
	*	[initialize an order on order page with hash etc so we can later compare via ipn,
	*	provided at least one gateway is available and we are not just returning to site via cancel ]
	*
	************************************************************************************************/
	function wppizza_gateway_initialize_order(){
		if(count($this->pluginGateways)>0){
			add_action('wppizza_choose_gateway', array($this,'gateway_set_order_details'));
			add_action('wppizza_choose_gateway', array($this,'gateway_db_initialize_order'));
			add_action('wppizza_choose_gateway', array($this, 'gateway_form_fields'));
			/**add a do action hook that has order id and details which can be used elsewhere **/
			add_action('wppizza_order_form_after', array($this, 'gateway_order_details_hook'));
		}
	}
	/********************************************************************
	*
	*	[returns order details and a hash made from those details,
	*	to store and check against later.
	*	additional variables added by sending array to this function
	*	(in this case  a timestamp) to make the order unique]
	*
	********************************************************************/
	function gateway_set_order_details(){
		/*In case microtime is available use it*/
		if(function_exists('microtime')){
			$timestamp=microtime(true);
		}else{
			$timestamp=time();
		}
		$this->gatewayOrderDetails=$this->wppizza_gateway_order_details(array('time'=>$timestamp));
	}
	/******************************************************************
	*
	*	[initialize/insert order in db when going to order page ]
	*
	******************************************************************/
	function gateway_db_initialize_order() {
		global $wpdb,$current_user;
		get_currentuserinfo();
		$wpdb->hide_errors();
		$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix . $this->pluginOrderTable." ( wp_user_id, hash, order_ini, payment_status )VALUES ( %s, %s, %s , %s )", array($current_user->ID, $this->gatewayOrderDetails['hash'], $this->gatewayOrderDetails['order_ini'],'INITIALIZED')));
		$this->gatewayOrderId=$wpdb->insert_id;
	}
	/*some action hooks to do somthing with the order details that have been  inserted into db**/
	function gateway_order_details_hook(){
		do_action('wppizza_gateway_do_order_details', $this->gatewayOrderDetails, $this->gatewayOrderId);
	}
	/******************************************************************
	*
	*	[add hash formfield to check against when sending to gateway]
	*
	******************************************************************/
	function gateway_form_fields(){
		$formFields='';
		$formFields='<input type="hidden" name="wppizza_hash" value="'.$this->gatewayOrderDetails['hash'].'" />';
		print $formFields;
	}
	/******************************************************************
	*
	*	[output order on thank you page]
	*
	******************************************************************/
	function gateway_order_on_thankyou($id){
		$orderEmails=new WPPIZZA_SEND_ORDER_EMAILS;
		$orderDetails=$orderEmails->gateway_order_on_thankyou($id);
		return	$orderDetails;
	}
	/******************************************************************
	*
	*	[output order on thank you page]
	*
	******************************************************************/
	function gateway_unset_cart() {
	 	if (!session_id()) {session_start();}
	    /*holds items in cart*/
	    $_SESSION[$this->pluginSession]['items']=array();
	    /*gross sum of all items in cart,before discounts etc*/
	    $_SESSION[$this->pluginSession]['total_price_items']=0;
	    /**gratuities**/
	    $_SESSION[$this->pluginSession]['tips']=0;
	}
}
?>