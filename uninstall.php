<?php
/*
* WordPress Plugin Uninstall
* 
* Uses the wpPluginJanitor class for standardized & secure database cleanup.
*
* @package    wpPluginFramework <https://github.com/sscovil/wpPluginFramework>
* @author     Shaun Scovil <sscovil@gmail.com>
* @version    1.0
*/
if(!defined('WP_UNINSTALL_PLUGIN') ){
    exit();
}
global $wpdb;
// Define plugin options, custom post types and custom taxonomies to remove.
$opt = array('wppizza','wppizza_gateway_cod','widget_wppizza');
$cpt = array('wppizza');
$tax = array(array('taxonomy'=>'wppizza_menu', 'object_type'=>'wppizza'));

// Register wpPluginJanitor class only if it does not already exist.
if( !class_exists( 'wpPluginJanitor' ) ){
	
	if ( is_multisite() ) {
 	   	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
 	   		if ($blogs) {
        	foreach($blogs as $blog) {
           		switch_to_blog($blog['blog_id']);	
		  		include_once( 'inc/admin.plugin.uninstall.janitor.php' );
				// Call uninstall cleanup method.
				wpPluginJanitor::cleanup( $opt, $cpt, $tax );
				/*delete wppizza order table**/
				$table = $wpdb->prefix."wppizza_orders";
				$wpdb->query("DROP TABLE IF EXISTS $table");
			}
			restore_current_blog();		
 	   		}
	}else{
		
  		include_once( 'inc/admin.plugin.uninstall.janitor.php' );
		// Call uninstall cleanup method.
		wpPluginJanitor::cleanup( $opt, $cpt, $tax );
		/*delete wppizza order table**/
		$table = $wpdb->prefix."wppizza_orders";
		$wpdb->query("DROP TABLE IF EXISTS $table");				
	}
}
?>
