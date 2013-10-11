<?php
if (!class_exists( 'WPPizza' ) ) {return ;}
class WPPIZZA_ACTIONS extends WPPIZZA {
      function __construct() {
    	parent::__construct();

		/************************************************************************
			[runs in front AND backend]
		*************************************************************************/
			/*sort categories**/
			add_filter('get_terms', array(&$this,'wppizza_do_sort_custom_posts_category'), 10, 2);
	    	add_action('init', array( $this, 'wppizza_require_common_input_validation_functions'));/*include input validation functions**/
	    	add_action('init', array( $this, 'wppizza_require_common_output_formatting_functions'));/*include output formatting functions**/
	    	add_action('init', array( $this, 'wppizza_require_common_helper_functions'));/*include output formatting functions**/
	    	add_action('init', array( $this, 'wppizza_register_custom_posttypes'));/*register custom posttype*/
			add_action('init', array( $this, 'wppizza_register_custom_taxonomies'));/*register taxonomies*/
			add_action('init', array(&$this,'wppizza_init_sessions'));/*needed for admin AND frontend***/			/**add sessions to keep track of shippingcart***/
			add_shortcode($this->pluginSlug, array($this, 'wppizza_add_shortcode'));//used in ajax request for cart contents so must be available when ajax and on front AND backend!

			/*class to send order emails used via ajax too so must be avialable from bckend too*/
			add_action('init', array( $this, 'wppizza_send_order_emails'));

			/***************
				[make user defined localizations strings wpml compatible]
			***************/
    		add_action('init', array( $this, 'wppizza_wpml_localization'),15);
			/***************
				[filters]
			***************/
			/**sanitize db input vars**/
			add_filter('wppizza_filter_sanitize_order', array( $this, 'wppizza_filter_sanitize_order'),10,1);
			add_filter('wppizza_filter_sanitize_post_vars', array( $this, 'wppizza_filter_sanitize_post_vars'),10,1);
			add_filter('wppizza_filter_order_summary', array( $this, 'wppizza_filter_order_summary_legacy'),10,1);


		/************************************************************************
			[runs only for frontend]
		*************************************************************************/
		if(!is_admin()){
			/***enqueue frontend scripts and styles***/
			add_action('wp_enqueue_scripts', array( $this, 'wppizza_register_scripts_and_styles'),$this->pluginOptions['layout']['css_priority']);

			/***************
				[filters]
			***************/
			/*include template**/
			add_filter('template_include', array( $this,'wppizza_include_template'), 1 );
			/***use loop for single post***/
			add_filter('wppizza_filter_loop', array( $this, 'wppizza_filter_loop'));
			/***exclude selected order page from navigation */
			add_filter('get_pages', array(&$this,'wppizza_exclude_order_page_from_navigation'));
		}
		/************************************************************************
			[runs only for admin]
		*************************************************************************/
		if(is_admin()){
			/**check requirements*/
			add_action('admin_init', array( $this, 'wppizza_check_plugin_requirements'));/*check if we have the relevant php version etc**/
			add_action('admin_init', array( $this, 'wppizza_admin_options_init'));/*if necessary, add the db option table and fill with defaults**/
    		add_action('admin_menu', array( $this, 'register_admin_menu_pages' ) );
    		add_action('admin_init', array( $this, 'wppizza_admin_pages_init' ) );
    		add_action('admin_init', array( $this, 'wppizza_admin_metaboxes') );
			add_action('admin_init', array( $this, 'wppizza_do_admin_notice'));/*if necessary,show admin info screens**/
			/***enqueue backend scripts and styles***/
			add_action('admin_enqueue_scripts', array( $this, 'wppizza_register_scripts_and_styles_admin'));
			/*when deleting or creating categories*/
			add_filter('delete_'.$this->pluginSlugCategoryTaxonomy.'', array(&$this,'wppizza_save_sorted_custom_category'));
			add_action('create_'.$this->pluginSlugCategoryTaxonomy.'', array(&$this,'wppizza_save_sorted_custom_category'));//runs as ajax call
			/*when saving custom post*/
			add_action('save_post', array( $this, 'wppizza_admin_save_metaboxes'), 10, 2 );
			/**sort menu item column in admin by name**/
			add_filter( 'request', array( $this, 'wppizza_items_sort') );

		}
		/************************************************************************
			[ajax]
		*************************************************************************/
		add_action('wp_ajax_wppizza_admin_json', array(&$this,'wppizza_admin_json') );
		add_action('wp_ajax_wppizza_json', array(&$this,'wppizza_json') );// non logged in users
		add_action('wp_ajax_nopriv_wppizza_json', array(&$this,'wppizza_json') );
      }
/***********************************************************************************************
*
*
*	[check requirements, start session, initialize options, register custom post type, metaboxes]
*
*
***********************************************************************************************/
	/********************************************************
		[PHP 5.2 (json_decode) required ,
		so if PHP version is lower then 5.2,
		display an error message and deactivate the plugin]
	********************************************************/
	function wppizza_check_plugin_requirements(){
		if( version_compare( PHP_VERSION, '5.2', '<' )) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
			deactivate_plugins($this->pluginPath);
			wp_die(  __('WPPizza requires the server on which your site resides to be running PHP 5.2 or higher. As of version 3.2, WordPress itself will also <a href="http://wordpress.org/news/2010/07/eol-for-php4-and-mysql4">have this requirement</a>. You should get in touch with your web hosting provider and ask them to update PHP.<br /><br /><a href="' . admin_url( 'plugins.php' ) . '">Back to Plugins</a>', $this->pluginLocale) );
		}
	}

	/*******************************************************
		[insert options and defaults on first install]
	******************************************************/
	public function wppizza_admin_options_init(){

		$options = $this->pluginOptions;
		if($options==0){/*no options db entry->do stuff*/
			$install_options=1;
			/****set nag notice to 1 as its first install*******/
			$this->pluginNagNotice=1;
			/**include and insert default options***/
			require_once(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');
			/*insert $options;*/
			update_option($this->pluginSlug, $defaultOptions );
			/*create order table*/
			require_once(WPPIZZA_PATH .'inc/admin.create.order.table.inc.php');
		}else{
			/****************************************************************************************
				@forceUpdate
				[in case we want  to force update without upgrading version, uncomment below
				 - DEVELOPMENT PURPOSES ONLY when adding/deleting default options.
			**************************************************************************************/
			//$forceUpdate=1;
			//$install_options=1;
			/**update  options if installed version < current version***/
			if( version_compare( $options['plugin_data']['version'], 	$this->pluginVersion, '<' ) || isset($forceUpdate)) {

				/**get default options***/
				require_once(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');
				/**compare table options against default options and delete/add as required***/
				require_once(WPPIZZA_PATH .'inc/admin.update.options.inc.php');

				/**compare currently installed options vs this vesrion**/
				if(	version_compare( $options['plugin_data']['version'], '2.0', '<' )){$update_options['plugin_data']['nag_notice']='2.1';}

				/**update options**/
				update_option($this->pluginSlug, $update_options );

				/*update order table*/
				require_once(WPPIZZA_PATH .'inc/admin.create.order.table.inc.php');
			}
		}
	}
	/************************************************************************************
		[admin notices: show and dismiss]
	************************************************************************************/
    function wppizza_do_admin_notice() {/*check if we need to show any notices i.e when set to 1 or on first install*/
		if($this->pluginOptions['plugin_data']['nag_notice']!=0 || $this->pluginOptions==0){
			add_action('admin_notices', array( $this, 'wppizza_install_notice') );
			add_action('admin_head', array($this, 'wppizza_dismiss_notice_js') );
			add_action('wp_ajax_wppizza_dismiss_notice', array($this, 'wppizza_dismiss_notice'));
    	}
  	}

	/* plugin admin screen notices/nags */
    function wppizza_install_notice() {
			/**get url to info screens**/
   			$pluginInfoInstallationUrl = admin_url( 'plugin-install.php?tab=plugin-information&plugin='.WPPIZZA_SLUG.'&section=installation&TB_iframe=true&width=600&height=800');
			$pluginInfoFaqUrl = admin_url( 'plugin-install.php?tab=plugin-information&plugin='.WPPIZZA_SLUG.'&section=faq&TB_iframe=true&width=600&height=800');

			$pluginUpdatedNotice='';
			$pluginUpdatedNotice.='<div id="message" class="updated wppizza_admin_notice" style="padding:20px;">';
			/*set text depending on notice number*/
			if($this->pluginOptions['plugin_data']['nag_notice']=='1' || $this->pluginNagNotice==1){
				$pluginUpdatedNotice.='<b>'.$this->pluginName.' Installed</b><br/><br/>';
				$pluginUpdatedNotice.='Thank you for installing '.WPPIZZA_NAME.' <br/>';
				$pluginUpdatedNotice.='Please make sure to read the <a href="'.$pluginInfoInstallationUrl.'" class="thickbox">"Installation Instructions"</a> and <a href="'.$pluginInfoFaqUrl.'" class="thickbox">"FAQ"</a> ';
				$pluginUpdatedNotice.='<br/>';
			}


			if($this->pluginOptions['plugin_data']['nag_notice']=='2.1'){
				$pluginUpdatedNotice.='<b>Update Notice '.WPPIZZA_NAME.' '.$this->pluginVersion.':</b><br/><br/>';
				$pluginUpdatedNotice.='To aid the further development of new options and extensions now and in the future as well as being able to take advantage of various new options added to this plugin, the way the plugin handles various things was changed quite significantly.';
				$pluginUpdatedNotice.='<br/><br/>';
				$pluginUpdatedNotice.='<b>IMPORTANT: if you have customised and copied any of the following templates/files to your theme directory, read on below, as you MIGHT have to update them !!!</b>';
				$pluginUpdatedNotice.='<br/><span style="color:red"><b>(IF you are not using any customised files you should be just fine and can ignore all of this)</b></span>';
				$pluginUpdatedNotice.='<br/><blockquote>templates/wppizza-order.php<br/>templates/wppizza-phpmailer-settings.php<br/>templates/wppizza-cart.php<br/>templates/wppizza-order-html-email.php<br/>css/wppizza-default.css</blockquote><br/>';
				$pluginUpdatedNotice.='<span style="color:red"><b>IF</b></span> you are using customised versions of the following files (i.e you have copied any of these files to your theme directory) <b>you will have to</b> update them as marked below (changes are marked in the relevant files).';
				$pluginUpdatedNotice.='<br/><br/>';
				$pluginUpdatedNotice.='<b>Affected Files and priorty of having to change your customised versions</b> <span style="color:red">(only if you are using customised versions of these files in your theme directory of course)</span>: ';
				$pluginUpdatedNotice.='<blockquote>';
					$pluginUpdatedNotice.='<b>templates/wppizza-order.php</b> [UPDATE REQUIRED]:<br/>you <b>MUST</b> update any customised version (if used). Changes in this file are marked with "NEW IN VERSION" ';
						$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>templates/wppizza-phpmailer-settings.php</b> [UPDATE REQUIRED IF USING PHPMAILER]:<br/><b>IF</b> you are using a customised version of this <b>AND</b> are using phpmailer to send the emails this plugin generates, you MUST update your customised version according to the file find in the templates/ directory ';
				$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>templates/wppizza-cart.php</b> [OPTIONAL].<br/><b>However</b>, if you want to take advantage of charging tax on items or enable self pickup (with or without offering discounts) etc , you\'ll have to update your customised version. (search for "CONDITIONAL ADDED/CHANGED" in the file to find any changes made)';
				$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>templates/wppizza-order-html-email.php.</b>[OPTIONAL].<br/><b>However</b>, if you want to take advantage of charging tax on items or enable self pickup (with or without offering discounts) etc , you\'ll have to update your customised version. (search for "NEW IN VERSION " to see changes)';
				$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>css/wppizza-default.css</b><br/>[not strictly required, <b>but probably a good idea</b>]. changes are marked with "NEW IN VERSION"';
				$pluginUpdatedNotice.='</blockquote>';
				$pluginUpdatedNotice.='with regards to all of the above, I would recommend you first make a backup of you customisation so you can refer to it';
				$pluginUpdatedNotice.='<br/><br/>';
				$pluginUpdatedNotice.='<b>Sorry about all this. Just had to be done. I hope the above is clear, but if you have any questions mail me at dev[at]wp-pizza.com</b>';
				$pluginUpdatedNotice.='<br/>';
			}

			$pluginUpdatedNotice.='<br/><a href="#" onclick="wppizza_dismiss_notice(); return false;" class="button-primary">dismiss</a>';
			$pluginUpdatedNotice.='</div>';
			$pluginUpdatedNotice=__($pluginUpdatedNotice, $this->pluginLocale);
			print"".$pluginUpdatedNotice."";
    }
    function wppizza_dismiss_notice_js () {
        $js="";
        $js.="<script type='text/javascript' >".PHP_EOL."";
        $js.="jQuery(document).ready(function($) {".PHP_EOL."";
            $js.="wppizza_dismiss_notice = function () {".PHP_EOL."";
	        	$js.="var data = {action: 'wppizza_dismiss_notice'};".PHP_EOL."";
	        	// since wp2.8 ajaxurl is defined in admin header pointing to admin-ajax.php
	        	$js.="jQuery.post(ajaxurl, data, function(response) {".PHP_EOL."";
			        $js.="$('.wppizza_admin_notice').hide('slow');".PHP_EOL."";
	        	$js.="});".PHP_EOL."";
	        $js.="};".PHP_EOL."";
        $js.="});".PHP_EOL."";
        $js.="</script>".PHP_EOL."";
        print"".$js;
    }
    public function wppizza_dismiss_notice() {
    	$options = $this->pluginOptions;
    	$options['plugin_data']['nag_notice']=0;
    	update_option($this->pluginSlug,$options);
        die();
    }

	/*****************************************************
 	 [sort admin column by title]
 	*****************************************************/
	function wppizza_items_sort( $request ) {
		if(isset ($request['post_type']) && $request['post_type']==''.WPPIZZA_POST_TYPE.''){
			if ( !isset( $request['orderby'] ) || ( isset( $request['orderby'] ) &&  $request['orderby']=='title' ) ) {
				$request = array_merge( $request, array('orderby' => 'title'));
			}
			if ( !isset( $request['order'] )) {
				$request = array_merge( $request, array('order' => 'asc'));
			}
		}
	return $request;
	}

	/*****************************************************
		[save sortorder when creating or deleting, categories]
	*****************************************************/
	function wppizza_save_sorted_custom_category( $column ) {

		/**bypass when activating plugin as we are installing
		already sorted default items via wp_insert_post()**/
		if(!isset($_GET['activate'])){
			$prevOptions=$this->pluginOptions;
			$currentSort=$prevOptions['layout']['category_sort'];

			$newSort['layout']['category_sort']=array();//initialize new
			$i=0;
			/**if its not set yet, we are actually adding a new category as first***/
			if(!isset($currentSort[$column])){
				$newSort['layout']['category_sort'][$column]=0;
			$i++;
			}
			/** if it IS set, we are deleting, so lets unset**/
			if(isset($currentSort[$column])){
				unset($currentSort[$column]);//unset current
			}

			/*if we are adding a new cat, the old ones get appended in order, starting with 1**/
			/*otherwise we are deleting, we will have unset old one and reorder starting from 0 */
			if(is_array($currentSort)){
			asort($currentSort);/*sort*/
			foreach($currentSort as $k=>$v){
				$newSort['layout']['category_sort'][$k]=$i;
				$i++;
			}}
			asort($newSort['layout']['category_sort']);	//probably superflous
			update_option( $this->pluginSlug, $newSort );
		}
	}

/******************************************************************
	[meta boxes , render , save on creation/update of post]
*******************************************************************/
	function wppizza_admin_metaboxes() {
    	add_meta_box( $this->pluginSlug,__('Set Item Options', $this->pluginLocale),array($this,'wppizza_admin_render_metaboxes'),$this->pluginSlug, 'normal', 'high');
	}
	function wppizza_admin_render_metaboxes( $meta_options ) {
		require_once(WPPIZZA_PATH .'inc/admin.echo.metaboxes.inc.php');
	}
	function wppizza_admin_save_metaboxes($item_id, $item_details ) {
		/**bypass the below when activating plugin as we are installing the default items on first activation via wp_insert_post()**/
		if(!isset($_GET['activate'])){
			/***as this function gets called when creating a new page, we will also insert some default values (as $_POST will be empty)**/
			// Check post type first
		    if(isset($item_details->post_type) && $item_details->post_type == $this->pluginSlug ){
		    	//**additives array**//
		    	$itemMeta['additives']=array();
		    	if(isset($_POST[$this->pluginSlug]['additives'])){
		    	foreach($_POST[$this->pluginSlug]['additives'] as $k=>$v){
		    		$itemMeta['additives'][$k]				= (int)$_POST[$this->pluginSlug]['additives'][$k];
		    	}}

		    	/**set some default values (namely sizes and prices) when adding new page**/
		    	if(!isset($_POST[$this->pluginSlug]['sizes'])){
					$options = $this->pluginOptions;
					$optionsSizes =wppizza_sizes_available($options['sizes']);
					/**get no of price input fields of first available size option**/
					reset($optionsSizes);
					$first_key = key($optionsSizes);
					$_POST[$this->pluginSlug]['sizes']=$first_key;
					if(isset($optionsSizes[$first_key]['price'])){
					$_POST[$this->pluginSlug]['prices']=$optionsSizes[$first_key]['price'];
					}
		    	}

				//**sizes**//
				$itemMeta['sizes']							= (int)$_POST[$this->pluginSlug]['sizes'];

		    	//**prices array**//
		    	$itemMeta['prices']=array();
		    	if(isset($_POST[$this->pluginSlug]['prices'])){
		    	foreach($_POST[$this->pluginSlug]['prices'] as $k=>$v){
		    		$itemMeta['prices'][$k]					= wppizza_validate_float_only($_POST[$this->pluginSlug]['prices'][$k],2);
		    	}}

		    	update_post_meta($item_id,$this->pluginSlug,$itemMeta);
			}
		}
	}
/***********************************************************************************************
*
*
*	[Admin output, settings and options]
*
*
************************************************************************************************/
public function register_admin_menu_pages() {
	// Check if user hass access to the plugin settings
//	if (current_user_can('administrator')){
		require_once(WPPIZZA_PATH .'inc/admin.echo.register.submenu.pages.inc.php');
//	}
}
function wppizza_admin_pages_init(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.settings.sections.inc.php');
}
function wppizza_admin_page_text_header($v) {
	require_once(WPPIZZA_PATH .'inc/admin.echo.settings.text.header.inc.php');
}
public function admin_manage_additives(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_additives.inc.php');
}
public function admin_manage_opening_times(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_opening_times.inc.php');
}
public function admin_manage_meal_sizes(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_meal_sizes.inc.php');
}
public function admin_manage_order_history(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_order_history.inc.php');
}
public function admin_manage_tools(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_tools.inc.php');
}
public function admin_manage_access_Rights(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_access_rights.inc.php');
}
public function admin_manage_layout(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_layout.inc.php');
}
public function admin_manage_order_settings(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_order_settings.inc.php');
}
public function admin_manage_order_form(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_order_form.inc.php');
}
public function admin_manage_localization(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_localization.inc.php');
}
public function admin_manage_settings(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_settings.inc.php');
}
public function admin_manage_gateways(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_gateways.inc.php');
}
public function wppizza_admin_settings_input($field='') {
	require(WPPIZZA_PATH .'inc/admin.echo.settings.input.fields.inc.php');
}
/*********************************************************
*
*		[array of wp capabilities]
*
*********************************************************/
function wppizza_set_capabilities($get_user_caps=false){
	$tabs['settings']=array('name'=>__('Settings',$this->pluginLocale),'cap'=>'wppizza_cap_settings');
	$tabs['order-settings']=array('name'=>__('Order Settings',$this->pluginLocale),'cap'=>'wppizza_cap_order_settings');
	$tabs['gateways']=array('name'=>__('Gateways',$this->pluginLocale),'cap'=>'wppizza_cap_gateways');
	$tabs['order-form-settings']=array('name'=>__('Order Form Settings',$this->pluginLocale),'cap'=>'wppizza_cap_order_form_settings');
	$tabs['opening-times']=array('name'=>__('Opening Times',$this->pluginLocale),'cap'=>'wppizza_cap_opening_times');
	$tabs['meal-sizes']=array('name'=>__('Meal Sizes',$this->pluginLocale),'cap'=>'wppizza_cap_meal_sizes');
	$tabs['additives']=array('name'=>__('Additives',$this->pluginLocale),'cap'=>'wppizza_cap_additives');
	$tabs['layout']=array('name'=>__('Layout',$this->pluginLocale),'cap'=>'wppizza_cap_layout');
	$tabs['localization']=array('name'=>__('Localization',$this->pluginLocale),'cap'=>'wppizza_cap_localization');
	$tabs['order-history']=array('name'=>__('Order History',$this->pluginLocale),'cap'=>'wppizza_cap_order_history');
	$tabs['access']=array('name'=>__('Access Rights',$this->pluginLocale),'cap'=>'wppizza_cap_access');
	$tabs['tools']=array('name'=>__('Tools',$this->pluginLocale),'cap'=>'wppizza_cap_tools');
	$tabs['delete-order']=array('name'=>__('Delete Orders',$this->pluginLocale),'cap'=>'wppizza_cap_delete_order');


	if($get_user_caps){
		global $current_user;
		$usercaps=array();
		$capUnique=array();/*dont need to have the same thing multiple times*/
		/*user can have more than one role**/
		foreach($current_user->roles as $roleName){
			$userRole = get_role($roleName);
			foreach($tabs as $tab=>$v){
				if(isset($userRole->capabilities[$v['cap']]) && !isset($capUnique[$v['cap']])){
					$usercaps[]=array('tab'=>$tab,'cap'=>$v['cap'],'name'=>$v['name']);
					$capUnique[$v['cap']]=1;
				}
			}
		}
		return $usercaps;
	}

	return $tabs;
}
/*********************************************************
*
*	[admin output print functions -
*	to consitantly add admin output sections,
*	whether or not added via ajax]
*********************************************************/
/*********************************************************
		[opening times]
*********************************************************/
private function wppizza_admin_section_opening_times_custom($field,$k=null,$options=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_openingtimes.inc.php');
	return $str;
}
/*********************************************************
		[times closed]
*********************************************************/
private function wppizza_admin_section_times_closed_standard($field,$k=null,$options=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_timesclosed.inc.php');
	return $str;
}
/*********************************************************
		[additives]
*********************************************************/
private function wppizza_admin_section_additives($field,$k,$v,$optionInUse=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_additives.inc.php');
	return $str;
}
/*********************************************************
		[gateways]
*********************************************************/
private function wppizza_admin_section_gateways($field,$options){
	require(WPPIZZA_PATH .'inc/admin.echo.get_gateways.inc.php');
}
/**************************************************************
	[get registered gateways. must start with WPPIZZA_GATEWAY_]
**************************************************************/
function wppizza_get_registered_gateways() {
	$paymentGateways = array();
	$gatewayDetails=$this->pluginOptions['gateways']['gateway_selected'];

	/*set sort order depending on the order they are stored in the db (by key),
		true/false declares whether gateway is enabled in frontend
	**/
	/*bit convoluted maybe, but saves me some db entry**/
	$i=0;
	foreach($gatewayDetails as $k=>$v){
		$gatewayOrder[$k]=$i;
	 $i++;
	}

	$allClasses=get_declared_classes();
	foreach ($allClasses AS $class){
		$chkStr=substr($class,0,16);
		if($chkStr=='WPPIZZA_GATEWAY_'){
			//print_r($class);
			$iDent=substr($class,16);
			$c=new $class;
			$c->gateway_settings();
				$paymentGateways[] =array(
					'sort'=>!empty($gatewayOrder[$iDent]) ? $gatewayOrder[$iDent] : '0',
					'enabled'=>!empty($gatewayDetails[$iDent]) ? $gatewayDetails[$iDent] : false,
					'ident'=>$iDent,
					'gatewayName'=>$c->gatewayName,
					'gatewayDescription'=>$c->gatewayDescription,
					'gatewayAdditionalInfo'=>$c->gatewayAdditionalInfo,
					'gatewayOptionsName'=>$c->gatewayOptionsName,
					'gatewayOptions'=>$c->gatewayOptions,
					'gatewaySettings'=>$c->gateway_settings()
				);
		}
	}
	return $paymentGateways;
}

/*********************************************************
		[available sizes of meal items]
*********************************************************/
private function wppizza_admin_section_sizes($field,$k,$v=null,$optionInUse=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_mealsizes.inc.php');
	return $str;
}
/***********************************************************************************************
*
*
* 	[ajax calls]
*
*
***********************************************************************************************/
	/******************
     [admin ajax call]
    *******************/
	public function wppizza_admin_json(){
		require(WPPIZZA_PATH.'ajax/admin-get-json.php');
		die();
	}
	/*******************
     [frontend ajax call]
    ********************/
	public function wppizza_json(){
		require(WPPIZZA_PATH.'ajax/get-json.php');
		die();
	}
/***********************************************************************************************
*
* 	[shortcode functions]
*	[ensure shortcodes are enabled - DOH]
*	[to use shortcodes in text widgets add  "add_filter('widget_text','do_shortcode')" to theme function file
*	or use any suitable plugin]
*
************************************************************************************************/
    /*****************************************************
     * Generates shortcode output utilising templates
     * @atts    The array of shortcode attributes
     ******************************************************/
	public function wppizza_add_shortcode($atts){
		$markup='';
		include(WPPIZZA_PATH.'views/shortcode.php');
		return $markup;
		die();//needed !!!
	}

    /*****************************************************
    * include relevant template depending on shortcode
    * [see header of template for details]
    ******************************************************/
	public function wppizza_include_shortcode_template($type,$atts=null){
		/***************************************
			[include category loop template]
		***************************************/
		if($type=='category'){
		static $countCategory=0;$countCategory++;
			$options = $this->pluginOptions;
			/*select first category if none selected->used when using shortcode without category*/
			if(!isset($atts['category'])){
				$termSort=$options['layout']['category_sort'];
				asort($termSort);
				reset($termSort);
				$firstTermId=key($termSort);
				/*get slug and taxonomy from id*/
				$query=get_term_by('id',$firstTermId,$this->pluginSlugCategoryTaxonomy);
			}
			/*a category has been selected*/
			if(isset($atts['category'])){
				/*get slug and taxonomy from slug*/
				$query=get_term_by('slug',$atts['category'],$this->pluginSlugCategoryTaxonomy);
			}
			/*exclude header*/
			if(isset($atts['noheader']) || $options['layout']['suppress_loop_headers']){
				$noheader=1;
			}
			/*show.hide additives at bottom of loop*/
			if(isset($atts['showadditives'])){
				$showadditives=$atts['showadditives'];
			}

			if($query){
			$query_var=''.$query->slug.'';
			}
			/*set template style if !default*/
			$loStyle='';
			if($options['layout']['style']!='default'){
				$loStyle='-'.$options['layout']['style'].''	;
			}
			/*include template from theme if exists*/
			if ($template_file = locate_template( array ('wppizza-loop'.$loStyle.'.php' ))){
				include($template_file);
				return;
			}
			/*if template not in theme, fallback to template in plugin*/
			/* it really should BE there */
			if (is_file(''.WPPIZZA_PATH.'templates/wppizza-loop'.$loStyle.'.php' )){
				$template_file=''.WPPIZZA_PATH.'templates/wppizza-loop'.$loStyle.'.php';
				include($template_file);
				return;
			}
		}
		/***************************************
			[include navigation template]
		***************************************/
		if($type=='navigation'){
			extract(shortcode_atts(array('title' => ''), $atts));
			$child_of=0;
			if(isset($atts['parent'])){
				$query=get_term_by('slug',$atts['parent'],$this->pluginSlugCategoryTaxonomy);
				if($query){
					$child_of=$query->term_id;
				}
			}
			$post_type=$this->pluginSlug;
			$args = array(
			  'taxonomy'     => $this->pluginSlugCategoryTaxonomy,
			  'orderby'      => 'name',
			  'show_count'   => 0,      // 1 for yes, 0 for no
			  'pad_counts'   => 0,      // 1 for yes, 0 for no
			  'hierarchical' => 1,      // 1 for yes, 0 for no
			  'title_li'     => $title,
			  'depth '     	 => 0,
			  'child_of'     => $child_of,
			  'show_option_none'   => __('Nothing here'),
			  'hide_empty'   => 1,
			  'echo'   => 0				// keep as variable
			);//'walker'        => new wppizza_walker_nav_menu,


			/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
			if ($template_file = locate_template( array ('wppizza-navigation.php'))){
				include($template_file);
				return;
			}
			/*check if it exists in plugin directory (it should really BE there), otherwise we will have to serve defaults**/
			if (is_file(''.WPPIZZA_PATH.'templates/wppizza-navigation.php')){
				$template_file=''.WPPIZZA_PATH.'templates/wppizza-navigation.php';
				include($template_file);
				return;
			}
		}
		/***************************************
			[include shopping cart template]
		***************************************/
		if($type=='cart'){
			/**if request is ajax , return formatted tems**/
			if(isset($atts['request'])){
			$request=$atts['request'];
			}
			/**variables to use in template**/
			$options = $this->pluginOptions;
			$cart=wppizza_order_summary($_SESSION[$this->pluginSession],$options);
			$cart = apply_filters('wppizza_filter_order_summary', $cart);
			/**txt variables from settings->localization*/
			$txt = $options['localization'];/*put all text varibles into something easier to deal with**/

			/*check if we set width,height**/
			$style=array();
			if(isset($atts['width']) && $atts['width']!=''){$style['width']='width:'.esc_html($atts['width']).'';}
			if(isset($atts['height']) && $atts['height']!=''){$style['height']='height:'.(int)($atts['height']).'px';}
			if(count($style)>0){$cartStyle['cart']=' style="'.implode(";",$style).'"';}
			if(isset($style['width'])){
				$cartStyle['width']=' style="'.$style['width'].'"';
			}
			/**dsiaply order info like discounts and delivery costs**/
			if(isset($atts['orderinfo'])){
				$orderinfo=true;
			}
			/**display openingtimes above - depending on template - cart? **/
			if(isset($atts['openingtimes'])){
				$openingTimes=wppizza_frontendOpeningTimes($options);
			}
			/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
			if ($template_file = locate_template( array (''.$this->pluginSlug.'-cart.php'))){
				include($template_file);
				return;
			}
			/*check if it exists in plugin directory, otherwise we will have to serve defaults**/
			if (is_file(''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-cart.php')){
				$template_file=''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-cart.php';
				include($template_file);
				return;
			}
		}
		/***************************************
			[include order page template]
		***************************************/
		if($type=='orderpage'){
			/*******get the variables***/
			$options = $this->pluginOptions;
			$cart=wppizza_order_summary($_SESSION[$this->pluginSession],$options);
			$cart = apply_filters('wppizza_filter_order_summary', $cart);
			/**txt variables from settings->localization*/
			$txt = $options['localization'];
			/**formelements from settings->order form*/
			$formelements=$options['order_form'];
			sort($formelements);
				/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
				if ($template_file = locate_template( array (''.$this->pluginSlug.'-order.php' ))){
				include($template_file);
					return;
				}
				/*check if it exists in plugin directory, otherwise we will have to serve defaults**/
				if (is_file(''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-order.php')){
					$template_file =''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-order.php';
					include($template_file);
					return;
				}
		}
}
/*********************************************************
*
*		[include validation function]
*
*********************************************************/
public function wppizza_require_common_input_validation_functions(){
	require_once(WPPIZZA_PATH .'inc/common.input.validation.functions.inc.php');
}
/*********************************************************
*
*	[output formatting functions]
*
*********************************************************/
	public function wppizza_require_common_output_formatting_functions(){
		require_once(WPPIZZA_PATH .'inc/common.output.formatting.functions.inc.php');
	}

/*********************************************************
*
*	[common helper functions]
*
*********************************************************/
	public function wppizza_require_common_helper_functions(){
		require_once(WPPIZZA_PATH .'inc/common.helper.functions.inc.php');
	}
	/*******************************************************
		[register custom post type]
	******************************************************/
	public function wppizza_register_custom_posttypes(){
		$labels = array(
			'name'               => __( 'Menu Items', $this->pluginLocale),
			'singular_name'      => __( 'WPPizza Menu Item', $this->pluginLocale ),
			'add_new'            => __( 'Add New',  $this->pluginLocale ),
			'add_new_item'       => __( 'Add New Menu Item',$this->pluginLocale ),
			'edit'				 => __( 'Edit', $this->pluginLocale ),
			'edit_item'          => __( 'Edit Menu Item',$this->pluginLocale ),
			'new_item'           => __( 'New Menu Item',$this->pluginLocale ),
			'all_items'          => __( 'All Menu Items',$this->pluginLocale ),
			'view'               => __( 'View', $this->pluginLocale ),
			'view_item'          => __( 'View Menu Items',$this->pluginLocale ),
			'search_items'       => __( 'Search Menu Items',$this->pluginLocale ),
			'not_found'          => __( 'No items found',$this->pluginLocale ),
			'not_found_in_trash' => __( 'No items found in the Trash',$this->pluginLocale ),
			'parent_item_colon'  => '',
			'menu_name'          => ''.$this->pluginName.''
		);
			$args = array(
				'labels'        => $labels,
				'description'   => __( 'Holds '.$this->pluginName.' menu items data', $this->pluginLocale),
				'show_ui'		=> true,
				'public'        => true,
				'menu_position' => 100,
				'menu_icon'		=> plugins_url( 'img/pizza_16.png', $this->pluginPath ),
				'has_archive'   => false,
				'hierarchical'	=> false,
				'supports'      => array( 'title', 'editor', 'author','thumbnail','page-attributes','comments'),
				'taxonomies'    => array('')
			);
			register_post_type( $this->pluginSlug, $args );
	}
	/*******************************************************
		[register taxonomy + taxonomy related functions]
	******************************************************/
	public function wppizza_register_custom_taxonomies(){
		$options = $this->pluginOptions;

		/**********************
			when using permalinks, we can either set the
			parent to be a dedicated page (admin->settings)
			.........
		***********************/
		$sel_category_parent=get_post($options['plugin_data']['category_parent_page'],ARRAY_A);
		/**********************
		........or use/set a default
		(required as other pages wont work without it when permalinked
		**********************/
		if($sel_category_parent['post_name']==''){
			$sel_category_parent['post_name']=$this->pluginSlugCategoryTaxonomy;
		}

		  // Add new taxonomy, make it hierarchical (like categories)
		  $labels = array(
		    'name' => _x( 'WPPizza Categories', 'taxonomy general name' ),
		    'singular_name' => _x( 'Category', 'taxonomy singular name' ),
		    'search_items' =>  __( 'Search Categories' ),
		    'all_items' => __( 'All Categories' ),
		    'parent_item' => __( 'Parent Category' ),
		    'parent_item_colon' => __( 'Parent Category:' ),
		    'edit_item' => __( 'Edit Category' ),
		    'update_item' => __( 'Update Category' ),
		    'add_new_item' => __( 'Add New Category' ),
		    'new_item_name' => __( 'New Category Name' ),
		    'menu_name' => __( 'Categories' )
		  );
		  register_taxonomy($this->pluginSlugCategoryTaxonomy,array($this->pluginSlug), array(
		    'hierarchical' => true,
		    'labels' => $labels,
		    'show_ui' => true,
		    'show_admin_column' => true,
		    'query_var' => true,
		    'rewrite' => array( 'slug' => ''.$sel_category_parent['post_name'].'','hierarchical'=>true )
		  ));
	}


	/*******************************************************
		[start session]
	******************************************************/
	function wppizza_init_sessions() {
	    if (!session_id()) {session_start();}
	    /*initialize if not set*/
	    if(!isset($_SESSION[$this->pluginSession])){
	    	/*holds items in cart*/
	    	$_SESSION[$this->pluginSession]['items']=array();
	    	/*gross sum of all items in cart,before discounts etc*/
	    	$_SESSION[$this->pluginSession]['total_price_items']=0;
	    }
	}
	/*******************************************************
		[empty cart session]
	******************************************************/
	function wppizza_unset_cart() {
	 	if (!session_id()) {session_start();}
	    /*holds items in cart*/
	    $_SESSION[$this->pluginSession]['items']=array();
	    /*gross sum of all items in cart,before discounts etc*/
	    $_SESSION[$this->pluginSession]['total_price_items']=0;
	}
/*********************************************************
*
*	[include send order emails class]
*
*********************************************************/
	function wppizza_send_order_emails() {
		require_once(WPPIZZA_PATH .'classes/wppizza.send-order-emails.inc.php');
	}
/***********************************************************************************************
*
* 	[template functions - include the relevant templates depending on shortcode/widget type and atts]
*	[if there's a copy of the template in the current theme folder use that one,
*	otherwise use the one in plugin template directory]
*
************************************************************************************************/

    /*****************************************************
     * Wrapper template when displying items in custom post type category
     * [see header of templates/wppizza-wrapper.php for details]
     ******************************************************/
	public function wppizza_include_template($template_path){
		/******list of all items in this particular taxonomy category(term)******/
		if ( get_post_type() == $this->pluginSlug ) {
			$post_type=get_post_type();
			$options = $this->pluginOptions;
			/*exclude header*/
			if($options['layout']['suppress_loop_headers']){
				$noheader=1;
			}
			if ( !is_single() ) {
				/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
				if ($theme_file = locate_template( array ('wppizza-wrapper.php' ))){
					include($theme_file);
					return;
				}
				/*check if it exists in plugin directory, otherwise we will have to serve defaults**/
				if (is_file(''.WPPIZZA_PATH.'templates/wppizza-wrapper.php' )){
					$theme_file=''.WPPIZZA_PATH.'templates/wppizza-wrapper.php';
					include($theme_file);
					return;
				}
			}
		}
		return $template_path;
	}
    /*****************************************************
     * Use loop template when displying SINGLE ITEMS in custom post type category
     * [see header of templates/wppizza-single.php for details]
     ******************************************************/
	function wppizza_filter_loop($args){
		global $post;
		if(is_single()){
			$catTerms = get_the_terms($post->ID, WPPIZZA_TAXONOMY);
			if ( $catTerms && ! is_wp_error( $catTerms ) ){
				$firstCat=reset($catTerms);
				$args['tax_query'][0]['terms']=$firstCat->slug;
			}
			$args['posts_per_page']=1;
			$args['max_num_pages']=-1;
		}
		return $args;
	}
/***********************************************************************************************
*
*
*	[Register and Enqueue scripts and styles]
*
*
************************************************************************************************/
    /**************
     	[Admin]
	***************/
    public function wppizza_register_scripts_and_styles_admin($hook) {
        if(is_admin()) {// && ($hook=='settings_page_'.$this->pluginSlug || $hook=='widgets.php')
            /**css**/
            	if (file_exists( get_template_directory() . '/wppizza-admin.css')){
					/**copy stylesheet to template directory to keep settings**/
					wp_register_style($this->pluginSlug.'-admin', get_template_directory_uri().'/wppizza-admin.css', array(), $this->pluginVersion);
            	}else{
					wp_register_style($this->pluginSlug.'-admin', plugins_url( 'css/styles-admin.css',$this->pluginPath), array(), $this->pluginVersion);
            	}
				/**if we want to keep all the original css (including future changes) but only want to overwrite some lines , add wppizza-admin-custom.css to your template directory*/
				if (file_exists( get_template_directory() . '/wppizza-admin-custom.css')){
					wp_register_style($this->pluginSlug.'-admin-custom', get_template_directory_uri().'/wppizza-admin-custom.css', array(''.$this->pluginSlug.'-admin'), $this->pluginVersion);
					wp_enqueue_style($this->pluginSlug.'-admin-custom');
				}
				/**for timepicker etc*/
				wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/smoothness/jquery-ui.css');
 				wp_enqueue_style($this->pluginSlug.'-admin');

      		/**js***/
      			wp_enqueue_script('jquery-ui-sortable');
            	wp_enqueue_script('jquery-ui-datepicker');
            	wp_register_script($this->pluginSlug, plugins_url( 'js/scripts.admin.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);
            	wp_register_script($this->pluginSlug.'-timepick', plugins_url( 'js/jquery.ui.timepicker.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);
            	wp_enqueue_script($this->pluginSlug);
            	wp_enqueue_script($this->pluginSlug.'-timepick');
        }
    }
    /**************
     	[Frontend]
	***************/
    public function wppizza_register_scripts_and_styles($hook) {
		$options = $this->pluginOptions;
    	/**************
    		css
    	**************/
		if($options['layout']['include_css']){
			if (file_exists( get_template_directory() . '/wppizza-'.$options['layout']['style'].'.css')){
			/**copy stylesheet to template directory to keep settings**/
			wp_register_style($this->pluginSlug, get_template_directory_uri().'/wppizza-'.$options['layout']['style'].'.css', array(), $this->pluginVersion);
			}else{
			wp_register_style($this->pluginSlug, plugins_url( 'css/wppizza-'.$options['layout']['style'].'.css', $this->pluginPath ), array(), $this->pluginVersion);
			}
			wp_enqueue_style($this->pluginSlug);

			/**if we want to keep all the original css (including future changes) but only want to overwrite some lines , add wppizza-custom.css to your template directory*/
			if (file_exists( get_template_directory() . '/wppizza-custom.css')){
				wp_register_style($this->pluginSlug.'-custom', get_template_directory_uri().'/wppizza-custom.css', array(''.$this->pluginSlug.''), $this->pluginVersion);
				wp_enqueue_style($this->pluginSlug.'-custom');
			}
		}

		/****************
			js
		****************/
    	wp_register_script($this->pluginSlug.'-validate', plugins_url( 'js/jquery.validate.min.js', $this->pluginPath ), array($this->pluginSlug), $this->pluginVersion ,true);
    	wp_enqueue_script($this->pluginSlug.'-validate');
    	wp_register_script($this->pluginSlug, plugins_url( 'js/scripts.min.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,$options['plugin_data']['js_in_footer']);
    	wp_enqueue_script($this->pluginSlug);

    	/**localized js***/
		wp_enqueue_script( $this->pluginSlug );
		$jsMessages=array();
		$jsMessages['closed']=''.$options['localization']['alert_closed']['lbl'].'';
		if($options['layout']['add_to_cart_on_title_click']){
			$jsMessages['choosesize']=''.$options['localization']['alert_choose_size']['lbl'].'';
		}
		if($options['order']['order_pickup'] && $options['order']['order_pickup_alert'] ){
			$jsMessages['pickup']=''.$options['localization']['order_self_pickup_cart_js']['lbl'].'';
		}
		/*add functions (names) to run when cart has been refreshed**/
		$jsCartRefreshCompleteFunctions['functionsCartRefresh']=array();
		$jsCartRefreshCompleteFunctions['functionsCartRefresh'] = apply_filters('wppizza_filter_js_cart_refresh_functions', $jsCartRefreshCompleteFunctions['functionsCartRefresh']);
		/**allow adding of veriables for extending plugins**/
		$jsExtend['jsExtend']=array();
		$jsExtend['jsExtend'] = apply_filters('wppizza_filter_js_extend', $jsExtend['jsExtend']);

		$localized_array = array( 'ajaxurl' =>admin_url('admin-ajax.php'),'validate_error'=>array('email'=>''.$options['localization']['required_field']['lbl'].'','required'=>''.$options['localization']['required_field']['lbl'].''),'msg'=>$jsMessages,'funcCartRefr'=>$jsCartRefreshCompleteFunctions['functionsCartRefresh'],'extend'=>$jsExtend['jsExtend']);
		wp_localize_script( $this->pluginSlug,$this->pluginSlug, $localized_array );

    }
/*********************************************************
*
*		[add filter functions]
*
*********************************************************/
	public function wppizza_exclude_order_page_from_navigation($pages) {
		if($this->pluginOptions['order']['orderpage_exclude']){
			$pageCount = count($pages);
			for ( $i=0; $i<$pageCount; $i++ ) {
				$page = & $pages[$i];
				if ($page->ID==$this->pluginOptions['order']['orderpage']) {
					unset( $pages[$i] );/*unset the order page*/
				}
			}
			if ( ! is_array( $pages ) ) $pages = (array) $pages;
			$pages = array_values( $pages );
		}
		return $pages;
	}
	/*****************************************************
		[display categories in order saved in options]
	*****************************************************/
	function wppizza_do_sort_custom_posts_category($terms,$taxonomy) {
		/*should be bypassed when creating/deleting categories (i.e when there's a post[action])**/
		if(isset($_GET['taxonomy']) && $_GET['taxonomy']==$this->pluginSlugCategoryTaxonomy && in_array($this->pluginSlugCategoryTaxonomy,$taxonomy)){
			$options = $this->pluginOptions;
			$termArray=array();
			foreach($terms as $k=>$term){
				if(is_object($term)){
				$key=$options['layout']['category_sort'][$term->term_id];
				$termArray[$key]=$term;
				}
			}
			ksort($termArray);
			return	$termArray;
		}else{
			return	$terms;
		}
	}
/*********************************************************
*
*		[admin options validation]
*
*********************************************************/
    public function wppizza_admin_options_validate($input){
		/*do not use require_once here as it may be used more than once .doh!**/
		require(WPPIZZA_PATH .'inc/admin.options.validate.inc.php');
	return $options;
    }
/*********************************************************
*
*		[empty custom posts]
*
*********************************************************/
	public function wppizza_empty_taxonomy($deleteAttachments=false,$truncateOrders=false){
		require_once(WPPIZZA_PATH .'inc/admin.empty.taxonomy.data.php');
	}

/*********************************************************************************
*
*	[changes wppizza custom sort order query to display category navigation in the right order]
*
*********************************************************************************/
	function wppizza_term_filter($pieces){
		$cat=$this->pluginOptions['layout']['category_sort'];
		asort($cat);
		$sort=implode(",",array_keys($cat));
		/*customise order by clause*/
		$pieces['orderby'] = 'ORDER BY FIELD(t.term_id,'.$sort.')';
	return $pieces;
	}
/*********************************************************************************
*
*	[filter legacy: as we might still be using additional info in templates]
*	make extend array into addinfo array
*
*********************************************************************************/
	function wppizza_filter_order_summary_legacy($orderItems){
		foreach($orderItems['items'] as $k=>$oItem){
			/**extend key has priority over additional info (legacy)*/
			if(isset($oItem['extend']) && count($oItem['extend'])>0){
				$orderItems['items'][$k]['additionalinfo']=$oItem['extend'];
				unset($orderItems['items'][$k]['extend']);
			}


		}
		return $orderItems;
	}

/*********************************************************************************
*
*	[filter: sanitize whats going into the db]
*
*********************************************************************************/
	function wppizza_filter_sanitize_order_recursive(&$val,$key){
		/**let's first decode all already encode ones to not double encode**/
		$val=wppizza_email_decode_entities($val,$this->blogCharset);
		/*now entitize the lot again*/
		$val=wppizza_email_html_entities($val);
	}
	function wppizza_filter_sanitize_order($arr){
		array_walk_recursive($arr,array($this,'wppizza_filter_sanitize_order_recursive'));
		return $arr;
	}


	function wppizza_filter_sanitize_post_vars_recursive(&$val,$key){
		$val=stripslashes($val);
		/**let's first decode all already encode ones to not double encode**/
		$val=wppizza_email_decode_entities($val,$this->blogCharset);
		/**strip things**/
		$val=wp_kses($val,array());
		/*now entitize the lot again*/
		$val=wppizza_email_html_entities($val);
	}

	function wppizza_filter_sanitize_post_vars($arr){
		if(is_array($arr)){
			array_walk_recursive($arr,array($this,'wppizza_filter_sanitize_post_vars_recursive'));
		}
		return $arr;
	}
/*********************************************************************************
*
*	[filter: order details returned from db]
*
*********************************************************************************/
/*seems to run 2x ?! need to find out why that is reasonably soon*/

	function wppizza_filter_order_db_return($oDetails){
		$orderDetails=$oDetails;
		//$orderDetails['items']=array();
		foreach($oDetails['item'] as $k=>$v){
			$orderDetails['item'][$k]['postId']=$v['postId'];
			$orderDetails['item'][$k]['count']=$v['count'];
			$orderDetails['item'][$k]['quantity']=$v['count'];/*legacy*/
			$orderDetails['item'][$k]['name']=$v['name'];
			$orderDetails['item'][$k]['size']=$v['size'];
			$orderDetails['item'][$k]['price']=wppizza_output_format_price($v['price'],$this->pluginOptions['layout']['hide_decimals']);
			$orderDetails['item'][$k]['pricetotal']=wppizza_output_format_price($v['pricetotal'],$this->pluginOptions['layout']['hide_decimals']);
			$orderDetails['item'][$k]['categories']=$v['categories'];
			$orderDetails['item'][$k]['additionalinfo']=$v['additionalinfo'];
			$orderDetails['item'][$k]['extend']=$v['extend'];
		}


		$orderDetails['total_price_items']=wppizza_output_format_price($oDetails['total_price_items'],$this->pluginOptions['layout']['hide_decimals']);
		$orderDetails['discount']=wppizza_output_format_price($oDetails['discount'],$this->pluginOptions['layout']['hide_decimals']);
		$orderDetails['item_tax']=wppizza_output_format_price($oDetails['item_tax'],$this->pluginOptions['layout']['hide_decimals']);

		$orderDetails['delivery_charges']=!empty($oDetails['delivery_charges']) ? wppizza_output_format_price($oDetails['delivery_charges'],$this->pluginOptions['layout']['hide_decimals']) : '';
		$orderDetails['selfPickup']=!empty($oDetails['selfPickup']) ? 1 : 0;
		$orderDetails['total']=wppizza_output_format_price($oDetails['total'],$this->pluginOptions['layout']['hide_decimals']);

	return $orderDetails;
	}
/*********************************************************************************
*
*	[filter plaintext customer / order details converting array to string when returned from db]
*	[as everything that gets stored in the db is entitized, we need to un-entitize stuff
*	that gets returned from the db and make a string out of it]
*********************************************************************************/
/*seems to run 2x too ?! need to find out why that is reasonably soon*/
	function wppizza_filter_customer_details_to_plaintext($customerDetails){
		if(is_array($customerDetails)){
				$pad=74;
				$customerDetailsString='';
				foreach($customerDetails as $k=>$v){
					/*non-textarea*/
					if($v['type']!='textarea'){
						$strPartLeft=''.wppizza_email_decode_entities($v['label'],$this->blogCharset).'';
						$spaces=$pad-strlen($strPartLeft);
						$strPartRight=''.wppizza_email_decode_entities($v['value'],$this->blogCharset);
						/**add to string**/
						$customerDetailsString.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
					}
					/**textareas, nl and indent*/
					if($v['type']=='textarea'){
						/**add to string**/
						$customerDetailsString.=''.wppizza_email_decode_entities($v['label'],$this->blogCharset).PHP_EOL;
						if($v['value']!=''){
							$customerDetailsString.='     '.wppizza_wordwrap_indent(wordwrap(wppizza_email_decode_entities($v['value'],$this->blogCharset), $pad, PHP_EOL, true));
							$customerDetailsString.=PHP_EOL.PHP_EOL;
						}
					}
				}
			return $customerDetailsString;
		}else{
			return $customerDetails;
		}
	}


	function wppizza_filter_order_items_to_plaintext($orderItems){
		if(is_array($orderItems)){
			$pad=74;
			/**set originals**/
			$oItems=$orderItems;

			/*override/entitydecode individual keys as required**/
			foreach($orderItems as $k=>$v){
				$oItems[$k]['name']=wppizza_email_decode_entities($v['name'],$this->blogCharset);
				$oItems[$k]['label']=wppizza_email_decode_entities($v['label'],$this->blogCharset);
				$oItems[$k]['value']=wppizza_email_decode_entities($v['value'],$this->blogCharset);
				if(isset($v['categories']) && is_array($v['categories'])){
				foreach($v['categories'] as $catId=>$cat){
					$oItems[$k]['categories'][$catId]['name']=wppizza_email_decode_entities($cat['name'],$this->blogCharset);
					$oItems[$k]['categories'][$catId]['description']=wppizza_email_decode_entities($cat['description'],$this->blogCharset);
				}}
				/**decoded string add spaces to front***/
				$oItems[$k]['additional_info']='   '.wppizza_email_decode_entities(''.$v['addinfo']['txt'].'',$this->blogCharset).PHP_EOL;

				/**now unset vars we dont need anymore**/
				unset($oItems[$k]['additionalinfo']);
				unset($oItems[$k]['extend']);
				unset($oItems[$k]['additionalInfo']);
				unset($oItems[$k]['addinfo']);


			}
			return $oItems;
		}
	}


	function wppizza_filter_order_items_html($orderItems,$returnKey){

		/**set originals**/
		$oItems=$orderItems;
		if(isset($orderItems) && is_array($orderItems)){
		foreach($orderItems as $k=>$v){
			/**unset vars we dont need anymore**/
			unset($oItems[$k]['additionalinfo']);
			unset($oItems[$k]['extend']);
			unset($oItems[$k]['additionalInfo']);
			/**return additional info html with set returnKey (whatever is used in template*/
			$oItems[$k][''.$returnKey.'']=''.$v['addinfo']['html'].'';
			/**now unset addinfo var as we dont need it anymore**/
			unset($oItems[$k]['addinfo']);
		}}
		return $oItems;
	}
	
	
	
	
	function wppizza_filter_customer_details_html($cDetails){
		if(isset($cDetails) && is_array($cDetails)){
		foreach($cDetails as $k=>$v){
			if($v['type']=='textarea'){
				$cDetails[$k]['value']='<div class="wppizza-order-textarea">'.nl2br($v['value']).'</div>';
			
			}
		}}
		return	$cDetails;
	}	
	
	function wppizza_filter_order_summary_to_plaintext($orderSummary){
		if(is_array($orderSummary)){
				$pad=74;
				$orderSummaryString='';
				foreach($orderSummary as $k=>$v){
						if($k=='self_pickup'){
							$strPartLeft=PHP_EOL.wordwrap(strip_tags(wppizza_email_decode_entities($v['label'],$this->blogCharset)), $pad, "\n", true).PHP_EOL;
						}else{
							$strPartLeft=''.wppizza_email_decode_entities($v['label'],$this->blogCharset).'';
						}
						$spaces=$pad-strlen($strPartLeft);
						$strPartRight=wppizza_email_decode_entities(''.$v['currency'].' '.$v['price'].'',$this->blogCharset);
						/**add to string**/
						$orderSummaryString.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
				}
				/**decode entities for plaintext**/
			return $orderSummaryString;
		}else{
			return $orderSummary;
		}
	}

	function wppizza_filter_order_additional_info($orderItems){
		foreach($orderItems as $k=>$oItems){
			/*legacy => will be deprecated. it should all be in [extend] as array***/
			if(isset($oItems['additionalinfo']) && is_array($oItems['additionalinfo'])){
				$orderItems[$k]['addinfo']['html']=implode(", ",$oItems['additionalinfo']);
				$orderItems[$k]['addinfo']['txt']=wppizza_email_decode_entities(implode(", ",$oItems['additionalinfo']),$this->blogCharset);
			}
		}
		return $orderItems;
	}

/*******************************************************
*
*	[WPML : make user defined strings wpml compatible]
*
******************************************************/
	function wppizza_wpml_localization(){
		if(function_exists('icl_translate')) {
			/**localization**/
			foreach($this->pluginOptions['localization'] as $k=>$arr){
				$this->pluginOptions['localization'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,''. $k.'', $arr['lbl']);
			}
			/**additives**/
			foreach($this->pluginOptions['additives'] as $k=>$str){
				$this->pluginOptions['additives'][$k] = icl_translate(WPPIZZA_SLUG,'additives_'. $k.'', $str);
			}
			/**sizes**/
			foreach($this->pluginOptions['sizes'] as $k=>$arr){
				foreach($arr as $sKey=>$sArr){
					$this->pluginOptions['sizes'][$k][$sKey]['lbl'] = icl_translate(WPPIZZA_SLUG,'sizes_'. $k.'_'.$sKey.'', $sArr['lbl']);
				}
			}
			/**gateway**/
			$this->pluginOptions['gateways']['gateway_select_label'] = icl_translate(WPPIZZA_SLUG,'gateway_select_label', $this->pluginOptions['gateways']['gateway_select_label']);
			$registerdGateways=$this->wppizza_get_registered_gateways();
			foreach($registerdGateways as $k=>$regGw){
				$regGw['gatewayName'] = icl_translate(WPPIZZA_SLUG.'_gateways',''.strtolower($regGw['ident']).'_ident', $regGw['gatewayName']);
				if(isset($regGw['gatewayOptions']) && is_array($regGw['gatewayOptions'])){
					foreach($regGw['gatewayOptions'] as $g=>$gwOption){
						if(is_string($gwOption) && $gwOption!=''){
							$regGw['gatewayOptions'][$g] =__($gwOption, $this->pluginLocale);
							$regGw['gatewayOptions'][$g] = icl_translate(WPPIZZA_SLUG.'_gateways',''.strtolower($regGw['ident']).'_'. $g.'', $regGw['gatewayOptions'][$g]);
						}
					}
				}
			}
			/**order_form**/
			foreach($this->pluginOptions['order_form'] as $k=>$arr){
				$this->pluginOptions['order_form'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,'order_form_'. $k.'', $arr['lbl']);
			}
			/**order**/
			$this->pluginOptions['order']['order_email_from'] = icl_translate(WPPIZZA_SLUG,'order_email_from', $this->pluginOptions['order']['order_email_from']);
			$this->pluginOptions['order']['order_email_from_name'] = icl_translate(WPPIZZA_SLUG,'order_email_from_name', $this->pluginOptions['order']['order_email_from_name']);
			/**order email to **/
			foreach($this->pluginOptions['order']['order_email_to'] as $k=>$arr){
				$this->pluginOptions['order']['order_email_to'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_to_'.$k.'', $arr);
			}
			/**order email bcc **/
			foreach($this->pluginOptions['order']['order_email_bcc'] as $k=>$arr){
				$this->pluginOptions['order']['order_email_bcc'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_bcc_'. $k.'', $arr);
			}
		}
	}
}
?>