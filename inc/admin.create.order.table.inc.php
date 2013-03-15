<?php
global $wpdb;
/*no backticks or apostrophies please**/
/** see http://codex.wordpress.org/Creating_Tables_with_Plugins **/
$sql="CREATE TABLE ".$wpdb->prefix . $this->pluginOrderTable." (
	id INT(10) NOT NULL AUTO_INCREMENT,
	order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	customer_details TEXT NULL DEFAULT NULL,
	order_details TEXT NULL DEFAULT NULL,
	PRIMARY KEY  (id)
)";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
?>