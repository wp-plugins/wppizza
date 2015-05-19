=== WPPizza ===
Contributors: ollybach
Donate link: http://www.wp-pizza.com/
Author URI: http://www.wp-pizza.com
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Tags: pizza, restaurant, pizzaria, pizzeria, restaurant menu, ecommerce, e-commerce, commerce, wordpress ecommerce, store, shop, sales, shopping, cart, order online, cash on delivery, multilingual, checkout, configurable, variable, widgets, shipping, tax, wpml
Requires at least: PHP 5.3+, WP 3.3+ 
Tested up to: 4.2.2
Stable tag: 2.11.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Restaurant Plugin (not only for Pizza). Maintain your Menu (sizes, prices, categories). Accept COD orders. Multisite, Multilingual, WPML compatible.



== Description ==

- **Conceived for Pizza Delivery Businesses, but flexible enough to serve any restaurant type.**

- Maintain your restaurant menu online and accept cash on delivery orders.

- Set categories, multiple prices per item and descriptions.

- Multilingual Frontend (just update labels in admin settings page and/or widget as required). WPML compatible.

- Multisite enabled

- Keeps track of your online orders.

- Shortcode enabled. (see <a href='http://wordpress.org/extend/plugins/wppizza/faq/' >FAQ</a> for details)


**To see the plugin in action with different themes try it at <a href="https://www.wp-pizza.com/">www.wp-pizza.com</a>**

**if you wish to allow your customers to add additional ingredients to any given menu item, have a look at the premium <a href='https://www.wp-pizza.com/'>"WPPizza Add Ingredients"</a> extension**

**gateways available to process credit card payments  instead of just cash on delivery at <a href='https://www.wp-pizza.com/'>www.wp-pizza.com</a>** 


== Installation ==

**Install**

1. Download the plugin and upload the entire `wppizza` folder to the `/wp-content/plugins/` directory.  
Alternatively you can download and install WPPizza using the built in WordPress plugin installer.  
2. Activate the plugin through the 'Plugins' menu in WordPress.  
3. You will find all configuration and menu options in your administration sidebar  


**Things to do on first install**

for consistency this document has now moved to the following location :   
<a href='https://www.wp-pizza.com/topic/things-to-do-on-first-install/'>https://www.wp-pizza.com/topic/things-to-do-on-first-install/</a>  
** I strongly encourage you to read it **  


**Uninstall**

Please note:  
although all options, menu items and menu categories get deleted from the database along with the table that holds any orders you may have received, you will manually have to delete any additional pages (such as the order page for example) that have been created as i have no way of knowing if you are using this page elsewhere or have changed the content/name of it.  
the same goes for the 3 example icons that come with this plugin as you might have used them elsewhere.



== Upgrade Notice ==

Please update to version 2.5.7+ asap as metadata might get lost when using *quickedit* in wppizza custom post type
(if you are currently using wppizza <2.4 you should definitely update to the latest version as there were also some security flaws in version <2.4)

PLEASE UPDATE IF YOU ARE USING 2.10.4.5 and payment gateway as the redirection was broken for some of them  


== Screenshots ==

1. frontend example
2. administration - widget
3. administration - categories
4. administration - menu item
5. administration - order settings (one of many option screens)
  

== Other Notes ==

= Translations provided by: =

* Italien:  Silvia Palandri  
* Hebrew:  Yair10 [&#1492;&#1500;&#1489;&#32;&#1489;&#1504;&#1497;&#1497;&#1514;&#32;&#1488;&#1514;&#1512;&#1497;&#1501;&#32;]  
* Dutch:  Jelmer  
* Spanish:  Andrew Kurtis at <a href="http://www.webhostinghub.com/">WebHostingHub</a>  
* German:  Franz Rufnak  

Many, many thanks guys and girls.  

Note: As the plugin gets updated over time and has some other strings and features added, the translations above (and future ones) will probably have a few strings not yet translated. If you wish, feel free to provide any of those missing and I will update the translations accordingly.  

If you want to contribute your own translation, feel free to send me your files and I will be more than happy to include them.  


= Demo Icons: =
please note that the icons used in the demo installation are <a href="http://www.iconarchive.com/show/desktop-buffet-icons-by-aha-soft.html">iconarchive.com</a> icons and not for commercial use.  
if you do wish to use any icon from this set commercially, please follow <a href="http://www.desktop-icon.com/stock-icons/desktop-buffet-icons.htm">this link</a> to purchase it.  


== Changelog ==

2.11.9  
* updated: chosen js library  
* updated: timepicker js library    
* updated: jquery validation js library    
* updated: flot js library    
19th May 2015  

2.11.8.18  
* fix/security: update pretty photo library to v3.1.6  
19th May 2015  

2.11.8.17  
* fix: abs path error in grid css  
19th May 2015  


2.11.8.16  
* added: [currently experimental] grid based layout option  
* added: wppizza_is_current_businessday function to determine if a timestamp is between open and closing times of current business day  
* tweak: 'key' to passed customer detail email variables  
19th May 2015  

2.11.8.15  
* internal: updated EDD updater (should make some external gateway update notifications more reliable)   
14th May 2015  


2.11.8.14  
* added: class (WPPIZZA_USER_DETAILS) to get order details for a specific logged in user (as yet unused)  
* added: filter for gateways selection (in frontend) to allow to conditionally disable perhaps   
* added: sortorder of menu items option (wppizza->layout)  
* tweak: added prices to list of menu items (in admin)  
* internal: added updated EDD class  
* internal: also return order_date when using wppizza_gateway_get_order_details function  
* internal/security: although the recently flagged/widespread XSS vulnerability should not be an issue in any wppizza version, "add_query_arg" functions are now being escaped nevertheless  
11th May 2015  


2.11.8.13  
* tweak: clear session data when updating plugin to eliminate possible php notices or errors for already initialized sessions   
* tweak: changed to a language agnostic menu item placeholder image  
* tweak: log some possible ajax error to console, instead of alerting them  
* tweak: order history polling failing silently for up to 5 times before throwing alert  
* tweak: added some missing "!defined( 'ABSPATH' )" sanity checks (just to be doubly safe. though should not really be any issue not having it as in previous versions)  
* added: [wppizza type="additives"] shortcode to display all additives somewhere if required. (no styling applied , but plenty of css classes available) 
30th April 2015  


2.11.8.12  
* internal: also return transaction_details when using wppizza_gateway_get_order_details function  
* tweak: set css max-width on ctips input field  
* tweak: do not submit form when hitting enter in tips field, but apply tip instead    
15th April 2015  

2.11.8.11  
* fix: removed some more possible php notices under certain circumstances  
* fix: admin print order history -> summary prices error when hiding decimals (hide decimals function erroneously applied 2x)  
12th April 2015  

2.11.8.10  
* added: option to not install default menu items, categories and/or pages  by defining WPPIZZA_NO_DEFAULTS (no menu items, categories, pages), WPPIZZA_NO_DEFAULT_ITEMS (no menu items, categories) and/or WPPIZZA_NO_DEFAULT_PAGES (no pages) constants in wp-config.php  
* added: more filter hooks in cart in various places  
* fix: unclosed div element in wppizza-order.php  
* fix: removed some more possible php notices under certain circumstances  
* tweak: some minor css tweaks in cart  
* tweak: some minor js tweaks (plus adding id to wrapper span) when using add_item_to_cart_button shortcode  
* tweak: added spans/classes around item name in cart and on order page    
* tweak: gateways using overlays instead of redirects will now also save user session data entered in order page  
7th April 2015  

2.11.8.9  
* fix : erroneously used "minimum order for delivery" (as opposed to "minimum order for self-pickup") value when set to "No delivery offered / pickup only"    
* tweak: capture/display (hopefully) more meaningful mail errors when using mail() or wp_mail()  
28th March 2015  

2.11.8.8  
* fix : fixed bug introduced in 2.11.7.8 that stopped button to redirect to order page   
25th March 2015  

2.11.8.7  
* tweak : added distinct window.location.relaod(true) to scripts where appropriate  
* tweak : very minor cart css adjustments  
* tweak : added filter after cart icons  
25th March 2015  

2.11.8.6  
* tweak : customer session data got unnecessarily re-saved every time when switching from delivery to self-pickup even if not on order page   
* added: make formatted price output filterable (wppizza_filter_output_format_price)  
19th March 2015  

2.11.8.5  
* added : further options for minicart (paddings, viewcart button etc)  
17th March 2015  

2.11.8.4  
* fix: [admin print order] -> added missing "doctype=" to meta tag, plus a couple of minor tweaks to now also pass w3c validation.  
14th March 2015  

2.11.8.3  
* fix: [html email template] -> added missing "doctype=" to meta tag, plus a couple of minor tweaks to now also pass w3c validation.  
14th March 2015  

2.11.8.2  
* fix: when set to "prices entered include tax" , tax was not always calculated accurately in some non-english languages and latest language packs  
12th March 2015  

2.11.8.1  
* tweak: option to enable minicart/small cart only for a set maximum bowser size  
* fix: minicart/small cart display did not always behave accurately on page load  
11th March 2015  

2.11.8  
* multisite (parent site only - order history print): allow header of "print order" to use child sites blogname instead of parent site's name [wppizza->settings]. only applicable if displaying all orders of all sites in order history.  
* multisite (parent site only - order history print): allow display of site name *where order was made* .only applicable if displaying all orders of all sites in order history and header is set to show info of parent site.  
* added: automatically scroll to missing required input fields (if not already in view) on order and confirmation pages  
* added: automatically scroll to top on thank you page if required  
* added: option to dynamically add a minicart to top of page if main cart is not in view (enable in widget or per shortcode attribute minicart=1)  
* tweak: minor css adjustments in cart  
* tweak: wrapped customr details in html emails into their own table tag to not affect td widths in other elements    
10th March 2015  


2.11   
* changelogs <=2.11.7 can be found in /logs  


== Frequently Asked Questions ==

= General Faq's =

for consistency and manageability the faq's have been moved to <a href='https://www.wp-pizza.com/forum/faqs/'>https://www.wp-pizza.com/forum/faqs/</a>

= Shortcodes = 

please see here <a href='https://www.wp-pizza.com/topic/wppizza-shortcodes/'>https://www.wp-pizza.com/topic/wppizza-shortcodes/</a>


= How can I submit a bug, ask for help or request a new feature? =

- leave a message on the <a href="http://wordpress.org/support/plugin/wppizza">wordpress forum</a> and I'll respond asap.  
- send an email to dev[at]wp-pizza.com with as much info as you can give me or 
- use the <a href="https://www.wp-pizza.com/contact/">"contact" form</a>, <a href="https://www.wp-pizza.com/forum/feature-requests/">"feature request" page </a> or <a href="http://www.wp-pizza.com/support/">support forum</a> on <a href="http://www.wp-pizza.com/">www.wp-pizza.com</a>


	
	>**additional premium add-ons can be found at <a href="https://www.wp-pizza.com/">www.wp-pizza.com</a>**  
	>

