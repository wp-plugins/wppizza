<?php
/*
Plugin Name: WPPizza
Description: Maintain your restaurant menu online and accept cash on delivery orders. Set categories, multiple prices per item and descriptions. Conceived for Pizza Delivery Businesses, but flexible enough to serve any type of restaurant.
Author: ollybach
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Author URI: http://www.wp-pizza.com
Version: 1.2.2.1
License:

  Copyright 2012 ollybach (dev@wp-pizza.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**set the following as  constants so we can use it throughout*/
define('WPPIZZA_CLASS', 'WPPizza');
define('WPPIZZA_NAME', 'WPPizza');
define('WPPIZZA_SLUG', 'wppizza');
define('WPPIZZA_LOCALE', ''.WPPIZZA_SLUG.'-locale');
define('WPPIZZA_POST_TYPE', ''.WPPIZZA_SLUG.'');
define('WPPIZZA_TAXONOMY', ''.WPPIZZA_POST_TYPE.'_menu');
define('WPPIZZA_PATH', plugin_dir_path(__FILE__) );
define('WPPIZZA_URL', plugin_dir_url(__FILE__) );

add_action('widgets_init', create_function('', 'register_widget("'.WPPIZZA_CLASS.'");'));
register_uninstall_hook( __FILE__, 'wppizza_uninstall' );

if ( ! class_exists( ''.WPPIZZA_CLASS.'' ) ) {
class WPPizza extends WP_Widget {
	private $pluginVersion;
	private $pluginName;
	private $pluginSlug;
	private $pluginSlugCategoryTaxonomy;
	private $pluginOrderTable;
	private $pluginLocale;
	private $pluginOptions;
	private $pluginNagNotice;


	/********************************************************
	*
	*
    *	[Constructor]
	*
	*
	********************************************************/
     function __construct() {
		/**init constants***/
		$this->pluginVersion='1.2.2.1';//increment in line with stable tag in readme and version above
	 	$this->pluginName="".WPPIZZA_NAME."";
	 	$this->pluginSlug="".WPPIZZA_SLUG."";//set also in uninstall when deleting options
		$this->pluginSlugCategoryTaxonomy="".WPPIZZA_TAXONOMY."";//also on uninstall delete wppizza_children as well as widget
		$this->pluginOrderTable="".WPPIZZA_SLUG."_orders";
		$this->pluginLocale="".WPPIZZA_LOCALE."";
		$this->pluginOptions = get_option(WPPIZZA_SLUG,0);
		$this->pluginNagNotice=0;//default off->for use in updates to this plugin

    	//classname and description
        $widget_opts = array (
            'classname' => WPPIZZA_CLASS,
            'description' => __('A Pizza Restaurant Plugin', $this->pluginLocale)
        );

        $this->WP_Widget(false, $name=$this->pluginName, $widget_opts);
        load_plugin_textdomain($this->pluginLocale, false, dirname(plugin_basename( __FILE__ ) ) . '/lang' );

		/************************************************************************
			[runs in front AND backend]
		*************************************************************************/
			/*sort categories**/
			add_filter('get_terms', array(&$this,'wppizza_do_sort_custom_posts_category'), 10, 2);
	    	add_action('init', array( $this, 'wppizza_require_common_input_validation_functions'));/*include input validation functions**/
	    	add_action('init', array( $this, 'wppizza_require_common_output_formatting_functions'));/*include output formatting functions**/
	    	add_action('init', array( $this, 'wppizza_register_custom_posttypes'));/*register custom posttype*/
			add_action('init', array( $this, 'wppizza_register_custom_taxonomies'));/*register taxonomies*/
			add_action('init', array(&$this,'wppizza_init_sessions'));/*needed for admin AND frontend***/			/**add sessions to keep track of shippingcart***/
			add_shortcode($this->pluginSlug, array($this, 'wppizza_add_shortcode'));//used in ajax request for cart contents so must be available when ajax and on front AND backend!
		/************************************************************************
			[runs only for frontend]
		*************************************************************************/
		if(!is_admin()){
			/***enqueue frontend scripts and styles***/
			add_action('wp_enqueue_scripts', array( $this, 'wppizza_register_scripts_and_styles'));
			/*include template**/
			add_filter('template_include', array( $this,'wppizza_include_template'), 1 );
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
    		add_action('admin_menu', array( $this, "register_admin_menu_pages" ) );
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
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(  __('WPPizza requires the server on which your site resides to be running PHP 5.2 or higher. As of version 3.2, WordPress itself will also <a href="http://wordpress.org/news/2010/07/eol-for-php4-and-mysql4">have this requirement</a>. You should get in touch with your web hosting provider and ask them to update PHP.<br /><br /><a href="' . admin_url( 'plugins.php' ) . '">Back to Plugins</a>', $this->pluginLocale) );
		}
	}
	/*******************************************************
		[start session]
	******************************************************/
	function wppizza_init_sessions() {
	    if (!session_id()) {session_start();}
	    /*initialize if not set*/
	    if(!isset($_SESSION[$this->pluginSlug])){
	    	/*holds items in cart*/
	    	$_SESSION[$this->pluginSlug]['items']=array();
	    	/*gross sum of all items in cart,before discounts etc*/
	    	$_SESSION[$this->pluginSlug]['total_price_items']=0;
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
			/**update  options if installed version < current version***/
			if( version_compare( $options['plugin_data']['version'], 	$this->pluginVersion, '<' ) || isset($forceUpdate)) {
				/**get default options***/
				require_once(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');
				/**compare table options against default options and delete/add as required***/
				require_once(WPPIZZA_PATH .'inc/admin.update.options.inc.php');
				/**update options**/
				update_option($this->pluginSlug, $update_options );
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
			$pluginUpdatedNotice.='<b>'.$this->pluginName.' Installed</b><br/><br/>';
			/*set text depending on notice number*/
			if($this->pluginOptions['plugin_data']['nag_notice']=='1' || $this->pluginNagNotice==1){
				$pluginUpdatedNotice.='Thank you for installing '.WPPIZZA_NAME.' <br/>';
				$pluginUpdatedNotice.='Please make sure to read the <a href="'.$pluginInfoInstallationUrl.'" class="thickbox">"Installation Instructions"</a> and <a href="'.$pluginInfoFaqUrl.'" class="thickbox">"FAQ"</a> ';
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
	/*******************************************************
		[register custom post type]
	******************************************************/
	public function wppizza_register_custom_posttypes(){
		$labels = array(
			'name'               => _x( 'Menu Items', 'post type general name' ),
			'singular_name'      => _x( 'WPPizza Menu Item', 'post type singular name' ),
			'add_new'            => _x( 'Add New', ''.WPPIZZA_NAME.'' ),
			'add_new_item'       => __( 'Add New '.WPPIZZA_NAME.' Menu Item' ),
			'edit'				 => __( 'Edit' ),
			'edit_item'          => __( 'Edit Menu Item' ),
			'new_item'           => __( 'New Menu Item' ),
			'all_items'          => __( 'All Menu Items' ),
			'view'               => __( 'View' ),
			'view_item'          => __( 'View Menu Items' ),
			'search_items'       => __( 'Search '.WPPIZZA_NAME.' Menu Items' ),
			'not_found'          => __( 'No items found' ),
			'not_found_in_trash' => __( 'No items found in the Trash' ),
			'parent_item_colon'  => '',
			'menu_name'          => ''.$this->pluginName.''
		);
			$args = array(
				'labels'        => $labels,
				'description'   => __( 'Holds '.$this->pluginName.' menu items data'),
				'show_ui'		=> true,
				'public'        => true,
				'menu_position' => 100,
				'menu_icon'		=> plugins_url( 'img/pizza_16.png', __FILE__ ),
				'has_archive'   => false,
				'hierarchical'	=> false,
				'supports'      => array( 'title', 'editor', 'author','thumbnail','page-attributes'),
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
		[display categories in order saved in options]
	*****************************************************/
	function wppizza_do_sort_custom_posts_category($terms,$taxonomy) {
		/*should be bypassed when creating/deleting categories (i.e when there's a post[action])**/
		if(isset($_GET['taxonomy']) && $_GET['taxonomy']==$this->pluginSlugCategoryTaxonomy && in_array($this->pluginSlugCategoryTaxonomy,$taxonomy)){
			$options = $this->pluginOptions;
			$termArray=array();
			foreach($terms as $k=>$term){
				$key=$options['layout']['category_sort'][$term->term_id];
				$termArray[$key]=$term;
			}
			ksort($termArray);
			return	$termArray;
		}else{
			return	$terms;
		}
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
				wp_register_style($this->pluginSlug, plugins_url( 'css/styles-admin.css', __FILE__ ), array(), $this->pluginVersion);
				wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/smoothness/jquery-ui.css');
 				wp_enqueue_style($this->pluginSlug);
      		/**js***/
      			wp_enqueue_script('jquery-ui-sortable');
            	wp_enqueue_script('jquery-ui-datepicker');
            	wp_register_script($this->pluginSlug, plugins_url( 'js/scripts.admin.js', __FILE__ ), array('jquery'), $this->pluginVersion ,true);
            	wp_register_script($this->pluginSlug.'-timepick', plugins_url( 'js/jquery.ui.timepicker.js', __FILE__ ), array('jquery'), $this->pluginVersion ,true);
            	wp_enqueue_script($this->pluginSlug);
            	wp_enqueue_script($this->pluginSlug.'-timepick');
        }
    }
    /**************
     	[Frontend]
	***************/
    public function wppizza_register_scripts_and_styles($hook) {
		$options = $this->pluginOptions;
    	/**css**/
		if($options['layout']['include_css']){
			if (file_exists( get_template_directory() . '/wppizza-'.$options['layout']['style'].'.css')){
			/**copy stylesheet to template directory to keep settings**/
			wp_register_style($this->pluginSlug, get_template_directory_uri().'/wppizza-'.$options['layout']['style'].'.css', array(), $this->pluginVersion);
			}else{
			wp_register_style($this->pluginSlug, plugins_url( 'css/wppizza-'.$options['layout']['style'].'.css', __FILE__ ), array(), $this->pluginVersion);
			}
			wp_enqueue_style($this->pluginSlug);
		}

		/**js***/
    	wp_register_script($this->pluginSlug.'-validate', plugins_url( 'js/jquery.validate.min.js', __FILE__ ), array($this->pluginSlug), $this->pluginVersion ,true);
    	wp_enqueue_script($this->pluginSlug.'-validate');
    	wp_register_script($this->pluginSlug, plugins_url( 'js/scripts.min.js', __FILE__ ), array('jquery'), $this->pluginVersion ,$options['plugin_data']['js_in_footer']);
    	wp_enqueue_script($this->pluginSlug);

    /**localized js***/
		wp_enqueue_script( $this->pluginSlug );
		$jsMessages=array();
		$jsMessages['closed']=''.$options['localization']['alert_closed']['lbl'].'';
		if($options['layout']['add_to_cart_on_title_click']){
			$jsMessages['choosesize']=''.$options['localization']['alert_choose_size']['lbl'].'';
		}
		$localized_array = array( 'ajaxurl' =>admin_url('admin-ajax.php'),'validate_error'=>array('email'=>''.$options['localization']['required_field']['lbl'].'','required'=>''.$options['localization']['required_field']['lbl'].''),'msg'=>$jsMessages);
		wp_localize_script( $this->pluginSlug,$this->pluginSlug, $localized_array );
    }
/***********************************************************************************************
*
*
*	[Admin output, settings and options]
*
*
************************************************************************************************/
public function register_admin_menu_pages() {
	// Check if user can access to the plugin
	if (!current_user_can('administrator')){
		wp_die( __('You do not have sufficient permissions to access this page !') );
	}
	require_once(WPPIZZA_PATH .'inc/admin.echo.register.submenu.pages.inc.php');
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
public function wppizza_admin_settings_input($field='') {
	require(WPPIZZA_PATH .'inc/admin.echo.settings.input.fields.inc.php');
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
*
*	[widget functions]
*
*
************************************************************************************************/

    /*****************************************************
     * Generates the administration form for the widget.
     * @instance    The array of keys and values for the widget.
     ******************************************************/
    function form($instance) {
    	include(WPPIZZA_PATH.'views/widget-admin.php');
    }
    /*******************************************************
     * Outputs the content of the widget.
     * @args            The array of form elements
     * @instance
     ******************************************************/
    function widget($args, $instance) {
		require(WPPIZZA_PATH.'views/widget.php');
    }
    /*******************************************************
     *
     * set default and return options for widget
     *
     ******************************************************/
	private function wppizza_default_widget_settings(){
		 $defaults=array(
            'title' => __("Shoppingcart", $this->pluginLocale),
            'type' => 'cart',
            'suppresstitle' => '',
            'noheader' => '',
            'width' => '',
            'height' => '',
            'openingtimes' => 'checked="checked"',
            'orderinfo' => 'checked="checked"'
        );
		return $defaults;
	}
    /*******************************************************
     *
     * available main options to choose from in widget
     *
     ******************************************************/
	private function wppizza_type_options(){
			$items['category']=__('Category Page', $this->pluginLocale);
			$items['navigation']=__('Navigation', $this->pluginLocale);
			$items['cart']=__('Cart', $this->pluginLocale);
			$items['orderpage']=__('Orderpage', $this->pluginLocale);
			$items['openingtimes']=__('Openingtimes', $this->pluginLocale);
		return $items;
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


			$query_var=''.$query->slug.'';
			/*include template from theme if exists*/
			if ($template_file = locate_template( array ('wppizza-loop.php' ))){
				include($template_file);
				return;
			}
			/*if template not in theme, fallback to template in plugin*/
			/* it really should BE there */
			if (is_file(''.WPPIZZA_PATH.'templates/wppizza-loop.php' )){
				$template_file=''.WPPIZZA_PATH.'templates/wppizza-loop.php';
				include($template_file);
				return;
			}
		}
		/***************************************
			[include navigation template]
		***************************************/
		if($type=='navigation'){
			extract(shortcode_atts(array('title' => ''), $atts));
			$post_type=$this->pluginSlug;
			$args = array(
			  'taxonomy'     => $this->pluginSlugCategoryTaxonomy,
			  'orderby'      => 'name',
			  'show_count'   => 0,      // 1 for yes, 0 for no
			  'pad_counts'   => 0,      // 1 for yes, 0 for no
			  'hierarchical' => 1,      // 1 for yes, 0 for no
			  'title_li'     => $title,
			  'depth '     	 => 0,
			  'child_of'     => 0,
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
			$cart=wppizza_order_summary($_SESSION[$this->pluginSlug],$options);
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
			$cart=wppizza_order_summary($_SESSION[$this->pluginSlug],$options);
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
*	[output formatting functions]
*
*********************************************************/
	public function wppizza_require_common_output_formatting_functions(){
		require_once(WPPIZZA_PATH .'inc/common.output.formatting.functions.inc.php');
	}
/*********************************************************
*
*		[empty custom posts]
*
*********************************************************/
	public function wppizza_empty_taxonomy($deleteAttachments=false,$truncateOrders=false){
		require_once(WPPIZZA_PATH .'inc/admin.empty.taxonomy.data.php');
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

/*********************************************************************************
*
*	[changes wppizza custom sort order query to display category navigation in the right order]
*
*********************************************************************************/
	function wppizza_term_filter($pieces){
		$sort=implode(",",array_keys($this->pluginOptions['layout']['category_sort']));
		/*customise order by clause*/
		$pieces['orderby'] = 'ORDER BY FIELD(t.term_id,'.$sort.')';
	return $pieces;
	}

}}
?>