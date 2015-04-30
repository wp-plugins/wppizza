<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	/*no need to run this when no wpml or on wppizza first install*/
	if($this->pluginOptions!=0) {
		/************************************************************************************************************
		*
		*
		*	gateways -> string translation forntend (registration of strings automatic in backend
		*
		*
		************************************************************************************************************/
		if ( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
			if(isset($this->pluginOptions['gateways']['gateway_selected']) && is_array($this->pluginOptions['gateways']['gateway_selected'])){
				/**loop through gateways and check if enabled**/
				foreach($this->pluginOptions['gateways']['gateway_selected'] as $gwIdent=>$enabled){ 
					if(!empty($enabled) && is_object($this->pluginGateways[$gwIdent])){
						$this->wppizza_gateway_translate_wpml_variables($gwIdent, $this->pluginGateways[$gwIdent]); 
					}
				}
			}
		}
	}
?>