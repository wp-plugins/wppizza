<?php
		register_setting($this->pluginSlug,$this->pluginSlug, array( $this, 'wppizza_admin_options_validate') );

		/**global settings**/
		add_settings_section('global', __('Global Settings', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'global');
		add_settings_field('version', '<b>'.__('Plugin Version:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'global', 'global', 'version' );
		add_settings_field('js_in_footer', '<b>'.__('Javascript in Footer:', $this->pluginLocale).'</b> '.__('[combines all jsVars in one tidy place, but requires wp_footer in theme]', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'global', 'global', 'js_in_footer' );
		add_settings_field('empty_category_and_items', '<b>'.__('Empty/Delete ALL WPPizza Categories and Items:<br/><span style="color:red">use with care<br/>if you select "delete images too", all featured images used for any wppizza menu items will be deleted too.<br/>if you use these images elsewhere, you should not select this !</span>', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'global', 'global', 'empty_category_and_items' );
//		add_settings_field('install_sample_data', '<b>'.__('Install Sample data:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'global', 'global', 'install_sample_data' );
		add_settings_field('category_parent_page', ''.__('<b>Permalinks:<br/>(only used and relevant when using widget or shortcode to display wppizza category navigation !!!)<br/><span style="color:red">when changing this setting, you MUST re-save your permalink settings</span></b><br/>(page cannot be used as static post page (wp settings) or have any children', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'global', 'global', 'category_parent_page' );

		/**layout settings**/
		add_settings_section('layout', __('Layout Settings', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'layout');
		add_settings_field('include_css', '<b>'.__('Include CSS:', $this->pluginLocale).'</b><br/>'.__('include frontend css that came with this plugin (untick if you want to provide your own styles somewhere else)', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'include_css' );
		add_settings_field('items_per_loop', '<b>'.__('Menu Items per page:', $this->pluginLocale).'</b><br/>'.__('how many menu items per category page (displays pagination, if there are more menu items for the selected category)<br/>[options: -1=all, >1=items per page]<br/><span style="color:red">if not set to -1, it must be >= wordpress settings->reading->Blog pages show at most</span>', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'items_per_loop' );
		add_settings_field('style', '<b>'.__('Which style to use (if enabled above):', $this->pluginLocale).'</b><br/>'.__('there is currently only one style available, but I may add more in the future', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'style' );
		add_settings_field('placeholder_img', '<b>'.__('Display placeholder image when no image associated with meal item:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'placeholder_img' );
		add_settings_field('suppress_loop_headers', '<b>'.__('Globally suppress headers above list of menu items:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'suppress_loop_headers' );
		add_settings_field('hide_cart_icon', '<b>'.__('Hide cart icon next to prices:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'hide_cart_icon' );
		add_settings_field('hide_prices', '<b>'.__('Hide prices altogether:', $this->pluginLocale).'</b><br/><span style="color:red">'.__('this will disable the adding of any item to the shoppingcart.', $this->pluginLocale).'</span><br/>'.__('Really only useful if you want to display your menu without offering online orders', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'hide_prices' );
		add_settings_field('disable_online_order', '<b>'.__('Completely disable online orders:', $this->pluginLocale).'</b><br/><span style="color:red">'.__('this will still display prices (unless set to be hidden above), but will disable shoppingcart and orderpage', $this->pluginLocale).'</span><br/>'.__('Useful if you want to display your menu and prices but without offering online orders.', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'layout', 'layout', 'disable_online_order' );
		
		/**opening times**/
		add_settings_section('opening_times', __('Opening Times', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'opening_times');
		add_settings_field('opening_times_standard', '<b>'.__('Standard opening times:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'opening_times', 'opening_times', 'opening_times_standard' );
		add_settings_field('opening_times_custom', '<b>'.__('Any dates/days where opening times differ from the standard times above (such as christmas etc).', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'opening_times', 'opening_times', 'opening_times_custom' );

		/**order**/
		add_settings_section('order', __('Order Settings', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'order');
		add_settings_field('currency', '<b>'.__('Currency:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'order', 'order', 'currency' );
		add_settings_field('orderpage', '<b>'.__('Order Page:', $this->pluginLocale).'</b><br/>'.__('(ensure the page includes [wppizza type="orderpage"] or the widget equivalent', $this->pluginLocale).'', array( $this, 'wppizza_admin_settings_input'), 'order', 'order', 'orderpage' );
		add_settings_field('delivery', '<b>'.__('Delivery Charges:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'order', 'order', 'delivery' );
		add_settings_field('discounts', '<b>'.__('Discounts:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'order', 'order', 'discounts' );
		add_settings_field('order_email_to', '<b>'.__('Which email address should any orders be sent to [separated by comma if multiple]:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'order', 'order', 'order_email_to' );
		add_settings_field('order_email_bcc', '<b>'.__('If you would like to BCC order emails add these here [separated by comma if multiple]:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'order', 'order', 'order_email_bcc' );

		/**order form**/
		add_settings_section('order_form', __('Order Form', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'order_form');
		add_settings_field('order_form', '<b>'.__('Form Fields:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'order_form', 'order_form', 'order_form' );

		/**size options**/
		add_settings_section('sizes', __('Size Options Available', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'sizes');
		add_settings_field('sizes', '<b>'.__('As meals and beverages can come in different sizes, please add/edit the options you want to offer your customers. You will then be able to offer these options on a per item basis:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'sizes', 'sizes', 'sizes' );

		/**additives**/
		add_settings_section('additives', __('Additives', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'additives');
		add_settings_field('additives', '<b>'.__('Some meals and beverages may contain additives. Add any possible additves here and select them at any meal/beravage that contains these additives. This in turn will add a footnote to pages denoting which item contains what additives:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'additives', 'additives', 'additives' );

		/**localization**/
		add_settings_section('localization', __('Frontened Localization', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'localization');
		add_settings_field('localization', '<b>'.__('Edit as required:', $this->pluginLocale).'</b>', array( $this, 'wppizza_admin_settings_input'), 'localization', 'localization', 'localization' );
	
		/**order history**/
		add_settings_section('history', __('Order History', $this->pluginLocale),  array( $this, 'wppizza_admin_page_text_header'), 'history');
		add_settings_field('history', '', array( $this, 'wppizza_admin_settings_input'), 'history', 'history', 'history' );
?>