=== WPPizza ===
Contributors: ollybach
Donate link: http://www.wp-pizza.com/
Author URI: http://www.wp-pizza.com
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Tags: pizza, restaurant, pizzaria, pizzeria, restaurant menu, ecommerce, e-commerce, commerce, wordpress ecommerce, store, shop, sales, shopping, cart, order online, cash on delivery, multilingual, checkout, configurable, variable, widgets, shipping, tax, wpml
Requires at least: PHP 5.3+, WP 3.3+ 
Tested up to: 4.1.1
Stable tag: 2.11.8.6
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

although some defaults will be installed and set when first installing this plugin, **you will have to configure some things** to make it play nicely in your theme  
Note: you might want to start with  Option 1 first, as you can always use Option 2 later

- **Option 1** - using normal pages: easier to setup initially, but more labour intensive to maintain in the long run

	To get you started quickly and to help you decide if you like this plugin, the following defaults are being created on first install:
	- some default categories
	- some default menu items per category
	- pages that display the items in any given category (including the required shortcode)  
	- an order page to send any orders to you via email (including the required shortcode)  
	- a default list of additives that a menu item might have 
	- a selection of available meal sizes
	- as well as default opening times, order settings etc.
	- display the shoppingcart somewhere on those pages (typically in a sidebar) using the wppizza widget (using type:cart) or the following shortcode: [wppizza type='cart']  
		
	>**to be able to add items to your shopping cart make sure that ALL pages that display your menu items have the shopping cart displayed somewhere**  
	>
	>**However, you will most likely NOT want to display the shopping cart on the final order page.**   
	>
	>If your theme does not allow to adjust this - via templates for example -  use one of the many WP plugins available that lets you choose which widget to display on which page  
	
	- if the cart displays "currently closed" adjust your opening times in wppizza->settings->openingtimes (and make sure your timezone settings are correct)
	- make sure the navigation to those pages gets displayed somewhere (this will normally already be the case - usually by some pagelist or menu ).

	that should be it.......

	NOTE: **some themes (custom community for example) require that you distinctly save the automatically inserted pages again (i.e "order page", etc) to generate the right markup and display correctly. So if the layout is messed up on initial install, try to just save those pages once and see if that fixes things.**

- **Option 2** - using templates:
	
	- copy your themes 'page.php' file as 'wppizza-wrapper.php' into your theme directory(if your theme does not have a page.php file, look for archive.php or even index.php)
	- open /wppizza/templates/wppizza-wrapper.php and copy everything between 	[copy from here .....] to [...........copy to here]
	- place this snippet in the 'wppizza-wrapper.php' file we created above in your theme directory, REPLACING the original loop (including the while ( have_posts() ) : the_post(); or similar part) 
	- display the navigation by either using the widget (type:navigation) or a shortcode [wppizza type='navigation']
	- **ensure you still have an order page that includes the following shortcode [wppizza type='orderpage'] and wppizza->settings->order settings: 'order page' is set to use this page (you might have to re-save your permalink settings)**  
	- **ensure - as outlined in Option 1 - that you are displaying your shopping cart**  
	- if you wish , you can now delete all wppizza default pages EXCEPT THE ORDER PAGE. However,if using permalinks, you might want to keep the parent page (default: Our Menu) and set the permalinks in wppizza->settings to this page. If you do, make sure to update permalinks structure once.

	Now you do not have to maintain any wppizza category pages, or navigation when adding new categories or menu items to wppizza as it's all taken care of automagically.

**PS**: you might have to adjust the css to work within your theme.  
see <a href='http://wordpress.org/extend/plugins/wppizza/faq/'>FAQ: "Can I edit the css ?"</a> for details  


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


2.11.7.13  
* added: more currencies / alternative currency displays  
* added: filter to currencies  
* fix: entities in item sizes were not decoded in plaintext emails   
1st March 2015  

2.11.7.12  
* added: optionally set order history polling to be active on page load (and allow timer to be other than 30 secs as default). [wppizza->settings : miscellaneous]  
* tweak: some cart css tweaks for more theme compatibility  
* added [internal]: additional methods in WPPIZZA_ORDER_DETAILS class (wppizza.order.details.inc.php)  
19th February 2015  

2.11.7.11
* fix: admin order history - accidentally inverted 2 variables in  wppizza.order.details.inc.php  
13th February 2015  


2.11.7.10
* multisite: minor cosmetics error introduced in 2.11.7.9 (wppizza->settings -> multisite showed description for non available fields when not in parent site)  
13th February 2015  

 
2.11.7.9 
* internal: added more methods to WPPIZZA_ORDER_DETAILS to get variables/keys without needing an orderid  
* multisite: "cart per site" (wppizza->settings) was only displayed for parent site when it should have been available for all sites  
13th February 2015  


2.11.7.8 
* fix : eliminated PHP Notice:  Undefined index: gateway-selected  
* tweak: moved "empty order table" and "delete wppizza posts and categories" to wppizza->tools allowing it to be used independently of each other  
* added: allow menu items - added to order page via shortcodes for example (eg upsells) - to be added to order, reloading page if necessary  
10th February 2015  
  
2.11.7.7 
* fix: stopped iOS (iPhone, iPad etc) from clicking/adding things when scrolling 
4th February 2015 
  
2.11.7.6 
MINOR UPDATES
* tweak: added experimantal js (for development purposes only) - off by default  
* added: a couple of filters for admin print order template (as per user request)  
2nd February 2015  

2.11.7.5 
MINOR UPDATES
* tweak: show some more system info variables (admin tools) 
* added: a couple of action hooks at end of email templates (as per user request)  
2nd February 2015  


2.11.7.4  
* WPML: admin order settings : make sure original order page settings id gets used when translation does not exist (mainly for compatibility reasons with other non WPML plugins)   
* WPML: some more method existance checking (namely "switch_lang") to perhaps get around some compatibility issues between WPML and other translation plugins (although non WPML are typically not really supported by the plugin, but worth the effort in a couple of places perhaps)
* internal: wppizza->tools : make sure wppizza vars shown are coming directly from db before having had the chance of being filtered somewhere   
* tweak: show admin order history on load without having to click button  
* tweak: option to set admin max order history results to other than 20   
* added: more filters added to admin order history as well as passing order status to filters 
31st January 2015  


2.11.7.3  
* WPML: eliminated some possible - legacy - phpnotices regarding additives  
* added: option to always load all css/js on all pages (to deal with certain layouts that do not pass along page id)  
19th January 2015  

2.11.7.2  
* multisite|internal: store and use blogid per item in session too for use if/when appropriate   
18th January 2015  

2.11.7.1  
* fixed (possible) session handling error introduced in v 2.11.7  
15th January 2015  

2.11.7  
* tweak: split admin global settings into sections  
* tweak: added general wppizza-optm class to opening times output spans  
* tweak: more meaningful filenames on reports export 
* added: wrapper function wpizzaOpeningtimes() if wppizza options are not available (in multisite blogloop for example)  
* multisite: remove multisite settings for single installs  
* multisite: allow reporting for all subsites (in parent site only)  
* multisite: allow order history for all subsites (in parent site only)  
* multisite: order history print adds sitename for easier identification (in parent site only)   
15th January 2015  

2.11   
* changelog 2.11 to <2.11.7 can be found in logs/changelog-2.11.txt  

2.10   
* changelog for version 2.10 can be found in logs/changelog-2.10.txt  

1.0 - 2.9.4.6  
* previous changes can be found in logs/changelog-1.0-2.9.4.6.txt  


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

