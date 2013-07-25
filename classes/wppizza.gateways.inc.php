<?php
if (!class_exists( 'WPPizza' ) ) {return ;}
class WPPIZZA_GATEWAYS extends WPPIZZA {

	protected $pluginGateways;

	function __construct() {
    	parent::__construct();

		add_action('init', array( $this, 'wppizza_add_default_gateways'));/*add default COD gateway*/
		/************************************************************************
			[runs only for frontend]
		*************************************************************************/
		if(!is_admin()){
			add_action('init', array( $this, 'wppizza_instanciate_gateways_frontend'));
			add_action('init', array( $this, 'wppizza_do_gateways'));/**output available gateway choices on order page**/
			add_action('init', array($this,'wppizza_gateway_initialize_order'));/*initialize oder into db */
		}
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
		/**get the selected gateway and associated classname*/
		$wppizzaGateway=array();
		foreach($this->pluginOptions['gateways']['gateway_selected'] as $gw=>$enbld){
			if($enbld){/**only add enabled gateways**/
				$gatewayClass="WPPIZZA_GATEWAY_".strtoupper($gw);
				$wppizzaGateway[$gw]=new $gatewayClass;
			}
		}
		$this->pluginGateways=$wppizzaGateway;
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
							print"<option value='".$key."' />";
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
							print"<div id='wppizza-gw-".$key."' class='wppizza-gw-button button'>";
								print"<label>";
								print"<input type='radio' name='wppizza-gateway' id='wppizza-gateway-".$key."' value='".$key."' ".checked($i,0,false)."/> ";
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
					/**add hidden value so the ajax call knows whether its cod or anything else*/
					print"<input type='hidden' name='wppizza-gateway' id='wppizza-gateway-".$key."' value='".$key."' /> ";
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
		$standardButton='<input class="submit wppizza-ordernow" type="submit" style="display:block" value="'.$this->pluginOptions['localization']['send_order']['lbl'].'" />';
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
		$cartDetails=wppizza_order_summary($_SESSION[$this->pluginSession],$this->pluginOptions);

		$gatewayOrder['currencyiso']=$cartDetails['currencyiso'];
		$gatewayOrder['currency']=$cartDetails['currency'];
		foreach($cartDetails['items'] as $k=>$v){
			$gatewayOrder['item'][$k]['name']=''.$v['name'].'';
			$gatewayOrder['item'][$k]['size']=''.$v['size'].'';
			$gatewayOrder['item'][$k]['quantity']=''.$v['count'].'';
			$gatewayOrder['item'][$k]['price']=''.$v['price'].'';
			$gatewayOrder['item'][$k]['pricetotal']=''.$v['pricetotal'].'';
			/**add any additional info to name*/
			$addInfo=array();
			if(is_array($v['additionalinfo']) && count($v['additionalinfo'])>0){foreach($v['additionalinfo'] as $additionalInfo){
				$addInfo[]=''.$additionalInfo.'';
			}}
			$gatewayOrder['item'][$k]['additionalInfo']=implode(", ",$addInfo);
		}

		$gatewayOrder['total_price_items']=$cartDetails['order_value']['total_price_items']['val'];
		$gatewayOrder['discount']=$cartDetails['order_value']['discount']['val'];
		$gatewayOrder['item_tax']=$cartDetails['order_value']['item_tax']['val'];
		$gatewayOrder['delivery_charges']=$cartDetails['order_value']['delivery_charges']['val'];
		$gatewayOrder['selfPickup']=!empty($cartDetails['selfPickup']) ? 1 : 0;
		$gatewayOrder['total']=$cartDetails['order_value']['total']['val'];


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
		first install anyway or old version anyway]
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
			add_action('wppizza_choose_gateway', array($this,'gateway_set_order_details'),1,10);
			add_action('wppizza_choose_gateway', array($this,'gateway_db_initialize_order'),1,11);
			add_action('wppizza_choose_gateway', array($this, 'gateway_form_fields'),1,12);
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
	}
}
?>