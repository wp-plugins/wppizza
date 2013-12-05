<?php
/******************************************************************************************************************
*
*	EDD_SL: http://wordpress.org/plugins/easy-digital-downloads/
*	Class to allow to integrate with EDD if used in additional extensions outside the WP repository.
*	Can be used to allow automatic update notifications of extensions to WPPizza in the WP Dashboard via EDD_SL.
*	Not used in the main WPPizza plugin (as any updates of WPPizza will automatically be available).
*	requires WPPIZZA_EDD_SL_PLUGIN_UPDATER in classes/wppizza.edd.plugin.updater.inc.php
*
******************************************************************************************************************/
if (!class_exists( 'WPPizza' ) ) {return ;}
class WPPIZZA_EDD_SL{
	function __construct() {
		//parent::__construct();
	}
	
	function echo_edd_settings($slug,$fieldName,$license,$status){
		echo"<input name='".$fieldName."' type='text' placeholder='".__('Enter your license key')."' size='30' class='regular-text' value='".$license."' />";
		echo' '.__('License Key', WPPIZZA_LOCALE).'<br />';
		
		/**print activate or de-activate button**/
		if( $status !== false && $status == 'valid' ) {	
			echo"<label class='button-secondary'><input name='".$slug."[license][action]' type='checkbox' value='deactivate' /> ".__('De-Activate License', WPPIZZA_LOCALE)."</label>";
		}else{
			echo"<label class='button-secondary'><input name='".$slug."[license][action]' type='checkbox' value='activate' /> ".__('Activate License', WPPIZZA_LOCALE)."</label>";	
		}
		/**print status info**/
		if( $status !== false && $status == 'valid' ) {	
			echo'<span style="color:green;"> '. __('License active', WPPIZZA_LOCALE).'</span>';
		}		
		if( $status !== false && $status !='' && $status != 'valid' ) {	
			echo'<span style="color:red;"> '. __('License in-active', WPPIZZA_LOCALE).' ['.$status.']</span>';
		}
		echo'<br/>'.__('Please note: entering and activating the license is optional, but if you choose not to do so, you will not be informed of any future bugfixes and/or updates.', WPPIZZA_LOCALE).'<br />';		
	}
	
	function edd_toggle($current,$licenseNew,$action,$eddName,$eddUrl){
		$licenseCurrent=$current['key'] ;/*current license number**/
		$statusCurrent=$current['status'] ;/*current status**/
		$errorCurrent=$current['error'] ;/*current / last error**/
		
		
		/**defaults/previously set vars,  if no action taken**/	
		$edd['error']=false;
		$edd['status']=$statusCurrent;
		
		/***deactivate currently set license first, if it was not '' anyway and new one is different***/
		if($licenseCurrent!='' && $licenseNew!=$licenseCurrent && $statusCurrent=='valid'){
			$edd=$this->edd_action('deactivate_license',$licenseCurrent,$eddName,$eddUrl);
		}
		
		if($licenseNew==''){
			$edd['error']=false;
			$edd['status']='';
		}
		
		/***if new different license has been set and we had no error (otherwise we'll just keep the original settings and desplay error****/
		if($licenseNew!='' && !$edd['error']){
			/**its a new key, so lets reset  the status***/
			if($licenseNew!=$licenseCurrent){
				$edd['status']='';	
			}
			/**if we are activating**/
			if($action=='activate'){
				$edd=$this->edd_action('activate_license',$licenseNew,$eddName,$eddUrl);
			}
			/**if we are de-activating**/
			if($action=='deactivate'){
				$edd=$this->edd_action('deactivate_license',$licenseNew,$eddName,$eddUrl);
			}
		}
		/**set the new license option vars***/
		if(!$edd['error']){$edd['key']=$licenseNew;}else{$edd['key']=$licenseCurrent;}
	
	return $edd;
	}
	
	function edd_action($action,$license,$eddName,$eddUrl){
		$api_params = array(
			'edd_action'=> $action,
			'license' 	=> $license,
			'item_name' => urlencode( $eddName ) // the name of our product in EDD
		);
		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, $eddUrl ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		$edd['error']=false;
		if ( is_wp_error( $response ) ){
				$edd['error']=true;
		}else{
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			$edd['status']=wppizza_validate_string($license_data->license);
		}
	return $edd;
	}
}
?>