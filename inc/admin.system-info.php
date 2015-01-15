<?php
/******************************************************************
*
*
*	System Info
*	[in large parts borrowed from Easy Digital Downloads plugin]
*
*
*******************************************************************/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
	global $wpdb;
	/*mysql info**/
	$con=mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	// Check connection
	if (mysqli_connect_errno()){
	 $dbInfo="Failed to connect to MySQL: " . mysqli_connect_error();
	}
	$dbInfo=mysqli_get_server_info($con);
	mysqli_close($con);


	/*theme**/
	if ( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
	}
	/**remote post**/
	$params = array(
		'sslverify'		=> false,
		'timeout'		=> 60,
		'user-agent'	=> 'WPPIZZA-SYSINFO/' . $this->pluginVersion,
	);
	
	$response = wp_remote_post( 'https://www.wp-pizza.com', $params );
	
	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST =  'wp_remote_post() works' . "\n";
	} else {
		$WP_REMOTE_POST =  'wp_remote_post() does not work' . "\n";
	}		
?>	
<textarea readonly="readonly"  onclick="this.focus();this.select();" style="width:100%;height:400px">
### SYSTEM INFO ###

Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $theme . "\n"; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo $dbInfo . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

WordPress Memory Limit:   <?php echo WP_MEMORY_LIMIT; ?><?php echo "\n"; ?>
PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; ?>
PHP Arg Separator:        <?php echo ini_get( 'arg_separator.output' ) . "\n"; ?>
PHP Allow URL File Open:  <?php echo ini_get( 'allow_url_fopen' ) ? "Yes" : "No\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>
Page For Posts:           <?php $id = get_option( 'page_for_posts' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>

WP Remote Post:           <?php echo $WP_REMOTE_POST; ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>


DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? 'fsockopen supported.' : 'fsockopen NOT supported.'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'cURL supported.' : 'cURL NOT supported.'; ?><?php echo "\n"; ?>
SOAP Client:              <?php echo ( class_exists( 'SoapClient' ) ) ? 'SOAP Client enabled.' : 'SOAP Client NOT enabled.'; ?><?php echo "\n"; ?>
SUHOSIN:                  <?php echo ( extension_loaded( 'suhosin' ) ) ? 'SUHOSIN installed.' : 'SUHOSIN NOT installed.'; ?><?php echo "\n"; ?>
<?php if(extension_loaded( 'suhosin' )){ ?>
suhosin.post.max_vars:    <?php echo esc_html( ini_get( 'suhosin.post.max_vars' ) ); ?><?php echo "\n"; ?>
suhosin.request.max_vars: <?php echo esc_html( ini_get( 'suhosin.request.max_vars' ) ); ?><?php echo "\n"; ?>
<?php } ?>


ACTIVE PLUGINS:

<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
	// only show active plugins
	if ( ! in_array( $plugin_path, $active_plugins ) )
		continue;

	echo $plugin['Name'] . ': ' . $plugin['Version'] . PHP_EOL;
}

if ( is_multisite() ) {
?>

NETWORK ACTIVE PLUGINS:

<?php
$plugins = wp_get_active_network_plugins();
$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

foreach ( $plugins as $plugin_path ) {
	$plugin_base = plugin_basename( $plugin_path );

	// only show active plugins
	if ( ! array_key_exists( $plugin_base, $active_plugins ) )
		continue;

	$plugin = get_plugin_data( $plugin_path );

	echo $plugin['Name'] . ' :' . $plugin['Version'] . PHP_EOL;
}
?>
<?php } ?>

WPPIZZA VARIABLES

Order Page:               <?php echo !empty( $this->pluginOptions['order']['orderpage'] ) ? get_permalink( $this->pluginOptions['order']['orderpage'] ) . "\n" : "\n" ?>
Mail Type:                <?php echo $this->pluginOptions['plugin_data']['mail_type'] . PHP_EOL ?>
Using Cache:              <?php echo !empty( $this->pluginOptions['plugin_data']['using_cache_plugin']) ? 'Yes'. PHP_EOL:'No' . PHP_EOL ?>
Dequeue scripts:          <?php echo !empty( $this->pluginOptions['plugin_data']['dequeue_scripts']) ? $this->pluginOptions['plugin_data']['dequeue_scripts'] . PHP_EOL : '---' . PHP_EOL ?>
Style:                    <?php echo $this->pluginOptions['layout']['style'] . PHP_EOL ?>
Style Enabled:            <?php echo !empty( $this->pluginOptions['layout']['include_css']) ? 'Yes'. PHP_EOL :'No' . PHP_EOL ?>
Confirmation Form:        <?php echo !empty( $this->pluginOptions['order_form']['confirmation_form_enabled']) ? 'Yes' . PHP_EOL :'No' . PHP_EOL ?>
Multisite Session:        <?php echo !empty( $this->pluginOptions['plugin_data']['wp_multisite_session_per_site']) ? 'Yes (Default)'. PHP_EOL :'No' . PHP_EOL ?>
Multisite Reports:        <?php echo !empty( $this->pluginOptions['plugin_data']['wp_multisite_reports_all_sites']) ? 'Yes'. PHP_EOL :'No (Default)' . PHP_EOL ?>
Multisite History:        <?php echo !empty( $this->pluginOptions['plugin_data']['wp_multisite_order_history_all_sites']) ? 'Yes'. PHP_EOL :'No (Default)' . PHP_EOL ?>

Single Item Tpl:          <?php echo !empty( $this->pluginOptions['plugin_data']['post_single_template']) ? $this->pluginOptions['plugin_data']['post_single_template']. PHP_EOL :'--' . PHP_EOL ?>
Single Item Permalink:    <?php echo $this->pluginOptions['plugin_data']['single_item_permalink_rewrite'] . PHP_EOL ?>


CUSTOMISED WPPIZZA TEMPLATES (IF ANY):
<?php
// Show templates that have been customised in their own directory
$tplDir = get_stylesheet_directory() . DIRECTORY_SEPARATOR.WPPIZZA_SLUG;
if(is_dir($tplDir)){
	$tplFiles = $tplDir.DIRECTORY_SEPARATOR.'*';
	if (!empty($tplFiles)){
		foreach ( glob( $tplFiles ) as $file ) {
			echo "Filename: " .WPPIZZA_SLUG.'/'. basename( $file ) . PHP_EOL;
		}
	}
}else{
	//in case theu have not been copied to their own directory
	$tplDir = get_stylesheet_directory();

	$wppizzaTplDir = WPPIZZA_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'*';
	foreach ( glob( $wppizzaTplDir ) as $file ) {
		$fileSearch=$tplDir.DIRECTORY_SEPARATOR.basename( $file );		
		if(is_file($fileSearch)){
			echo "Filename: " . basename( $file ) . PHP_EOL;
		}
	}
}
?>
</textarea>