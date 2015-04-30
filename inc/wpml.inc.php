<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	/*no need to run this when no wpml or on wppizza first install*/
	if($this->pluginOptions!=0) {

		/**localization**/
		if(isset($this->pluginOptions['localization']) && is_array($this->pluginOptions['localization'])){
		foreach($this->pluginOptions['localization'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,''. $k.'', $arr['lbl']);//register in admin
			}else{
				$this->pluginOptions['localization'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,''. $k.'', $arr['lbl']);
			}
		}}
		
		/**additives**/
		if(isset($this->pluginOptions['additives']) && is_array($this->pluginOptions['additives'])){
		foreach($this->pluginOptions['additives'] as $k=>$str){
			
			if(is_array($str) && isset($str['name'])){$str=$str['name'];}/*legacy*/
			
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'additives_'. $k.'', $str);//register in admin
			}else{			
				$this->pluginOptions['additives'][$k] = icl_translate(WPPIZZA_SLUG,'additives_'. $k.'', $str);
			}
		}}
		
		/**sizes**/
		if(isset($this->pluginOptions['sizes']) && is_array($this->pluginOptions['sizes'])){
		foreach($this->pluginOptions['sizes'] as $k=>$arr){
			foreach($arr as $sKey=>$sArr){
				if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
					icl_register_string(WPPIZZA_SLUG,'sizes_'. $k.'_'.$sKey.'', $sArr['lbl']);//register in admin
				}else{				
					$this->pluginOptions['sizes'][$k][$sKey]['lbl'] = icl_translate(WPPIZZA_SLUG,'sizes_'. $k.'_'.$sKey.'', $sArr['lbl']);
				}
			}
		}}
		
		/**order_form**/
		if(isset($this->pluginOptions['order_form']) && is_array($this->pluginOptions['order_form'])){
		foreach($this->pluginOptions['order_form'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'order_form_'. $k.'', $arr['lbl']);//register in admin
			}else{
				$this->pluginOptions['order_form'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,'order_form_'. $k.'', $arr['lbl']);
			}
		}}
		
		/**confirmation_form**/
		if(isset($this->pluginOptions['confirmation_form']) && is_array($this->pluginOptions['confirmation_form'])){
		foreach($this->pluginOptions['confirmation_form'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'confirmation_form_'. $k.'', $arr['lbl']);//register in admin
			}else{
				$this->pluginOptions['confirmation_form'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,'confirmation_form_'. $k.'', $arr['lbl']);
			}
		}}

		/**localization_confirmation_form**/
		if(isset($this->pluginOptions['localization_confirmation_form']) && is_array($this->pluginOptions['localization_confirmation_form'])){
		foreach($this->pluginOptions['localization_confirmation_form'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'confirmation_'. $k.'', $arr['lbl']);//register in admin
			}else{
				$this->pluginOptions['localization_confirmation_form'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,'confirmation_'. $k.'', $arr['lbl']);
			}
		}}
		
			
		/**order**/
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'order_email_from',$this->pluginOptions['order']['order_email_from']);//register in admin
				icl_register_string(WPPIZZA_SLUG,'order_email_from_name', $this->pluginOptions['order']['order_email_from_name']);//register in admin
			}else{
				$this->pluginOptions['order']['order_email_from'] = icl_translate(WPPIZZA_SLUG,'order_email_from', $this->pluginOptions['order']['order_email_from']);
				$this->pluginOptions['order']['order_email_from_name'] = icl_translate(WPPIZZA_SLUG,'order_email_from_name', $this->pluginOptions['order']['order_email_from_name']);
			}
		/**order email to **/
		if(isset($this->pluginOptions['order']['order_email_to']) && is_array($this->pluginOptions['order']['order_email_to'])){
		foreach($this->pluginOptions['order']['order_email_to'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'order_email_to_'. $k.'', $arr);//register in admin
			}else{
				$this->pluginOptions['order']['order_email_to'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_to_'.$k.'', $arr);
			}
		}}
		/**order email bcc **/
		if(isset($this->pluginOptions['order']['order_email_bcc']) && is_array($this->pluginOptions['order']['order_email_bcc'])){
		foreach($this->pluginOptions['order']['order_email_bcc'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'order_email_bcc_'. $k.'', $arr);//register in admin
			}else{			
				$this->pluginOptions['order']['order_email_bcc'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_bcc_'. $k.'', $arr);
			}
		}}
		/**order email attachments **/
		if(isset($this->pluginOptions['order']['order_email_attachments']) && is_array($this->pluginOptions['order']['order_email_attachments'])){
		foreach($this->pluginOptions['order']['order_email_attachments'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,'order_email_attachments_'. $k.'', $arr);//register in admin
			}else{			
				$this->pluginOptions['order']['order_email_attachments'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_attachments_'. $k.'', $arr);
			}
		}}		


		/**single item permalink**/
		if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
			icl_register_string(WPPIZZA_SLUG,'single_item_permalink_rewrite', $this->pluginOptions['plugin_data']['single_item_permalink_rewrite']);//register in admin
		}else{	
			$this->pluginOptions['plugin_data']['single_item_permalink_rewrite'] = icl_translate(WPPIZZA_SLUG,'single_item_permalink_rewrite', $this->pluginOptions['plugin_data']['single_item_permalink_rewrite']);
		}
	
		/**gateways select label**/
		if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
			icl_register_string(WPPIZZA_SLUG,'gateway_select_label', $this->pluginOptions['gateways']['gateway_select_label']);//register in admin
		}else{	
			$this->pluginOptions['gateways']['gateway_select_label'] = icl_translate(WPPIZZA_SLUG,'gateway_select_label', $this->pluginOptions['gateways']['gateway_select_label']);
		}
	}
?>