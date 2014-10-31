=== WPPizza ===
Contributors: ollybach
Donate link: http://www.wp-pizza.com/
Author URI: http://www.wp-pizza.com
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Tags: pizza, restaurant, restaurant menu, ecommerce, e-commerce, commerce, wordpress ecommerce, store, shop, sales, shopping, cart, order online, cash on delivery, multilingual, checkout, configurable, variable, widgets, shipping, tax
Requires at least: PHP 5.3+, WP 3.3+ 
Tested up to: 4.0
Stable tag: 2.11.5.1
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

2.11.5.1  
* added attribute (itemcount=[left|right]) to wppizza type=totals shortcode  
* fix: loop template was loaded 2x when using shortcode attribute !all AND having a customised version in theme directory  
* fix: some php notices eliminated  
* improved mobile devices usability somewhat when allowing quantities to be changed via input field  
* internal: now also passing cart variables to js cart refresh function  
31st October 2014  

2.11.5  
* fix: wrong submit button label in confirmation page  
* admin: added exclude categories/items when calculating discounts  
* admin: added exclude categories when calculating delivery charges  
* admin: minor admin text changes  
* admin: added chosen.js library for multiselects  
* added filter for delivery charges     
* added missing translations for order history status (admin)  
* stopped accidental submitting order when changing quantities and hitting enter key. this will now trigger update of quantities instead.  
* readme.txt tidyup  
29th October 2014  

2.11.4    
* updated language files  
* replaced erroneously hardcoded WPPizza with WPPIZZA_NAME constant in a couple of places  
* added: optional text (set in localization) that can be displayed on initial order page (before submitting) prior to other order details  
* some admin css tweaks  
* added shortcode to just display totals (see faq->shortcodes)  
27th October 2014  


2.11.3    
* added paymentstatus_userid(payment_status,wp_user_id) index on orders table  
* added some more action/filter hooks to order, confirmation, history , email , opening times and cart templates  
* added filter (wppizza_filter_order_ini_add_vars) to be able to add additional parameters to wppizza_orders order_ini field  
* added system info to wppizza->tools to aid debuggging  
* added enabling of wp_cron schedule to clean up orders table periodically (wppizza->tools)   
23rd October 2014


2.11.2.3  
* added action to order page (wppizza_order_after_field_'.$elmKey.'')  
* minor css tweaks to menu item title in loop when using default layout  (i.e changed display:inline;display:inline-block; to just display:inline;)  
* general maintenance  
16th October 2014  


2.11.2.2  
* added german translation  
* stopped exiting of dashboard functions when php <5.3 (athough it clearly states a min version of php 5.3 required)  
* some minor css tweaks  
13th October 2014  


2.11.2.1    
* MAINTENANCE - nothing dramatic  
* added missing option variable to wppizza_filter_plaintextemail_item_markup to avoid some phpnotices    
* added option to select order by hash when setting a payment to failed in method wppizza_gateway_order_payment_failed to allow us to save some unnecessary db selects in payment gateways (if required)  
* added conditional to not display header either in loop if found_post<=0  
9th October 2014  


2.11.2    
* added more shortcode options to order history display (see shortcodes in faqs)  
* added more shortcode options for category display to - for example - select all or comma separated list of categories in a single shortcode (see shortcodes in faqs)  
* added title attribute on additives in loop  
* added and using permalinks filter  to be able to add item more reliably to the correct category when "group by category" has been enabled and item belongs to more than one category  
* added hidden input (used when group by category has been enabled) via action hook instead of it being hardcoded into loop templates  
* added more parameters (slug, categoryid, options) to all action hooks in loop templates  
* added notice when using php <5.3 in reports 
* reformatted opening hours widget/shortcode output a little bit to also account for sunday weekstart in a somewhat more sensible manner   
* prevent accidental double click on order submit button  
* tidy up in loop templates (using include/require for consistency)  
* set "*" before additives in loop to be css controlled instead of hardcoded  
* minor css tweaks  
7th October 2014  



2.11.1.2    
* fix: javascript error when switching from self pickup to delivery (and vice versa)  
30th September 2014  

2.11.1.1    
* fix: emails could not be sent if setting a fixed from address but not enabling email field to be present on order form when using phpmailer  
* added distinct error message for email formfield (as opposed to just "this is a required field")  
23th September 2014  


2.11.1    
JUST SOME GENERAL INTERNAL FILTERS, METHODS AND ACTION HOOKS ADDITIONS (USER REQUESTS) - NOTHING MAJOR
* added filter - wppizza_filter_order_summary_exclude_item_from_count -  to order summary function (affecting cart and order etc) that lets the total count of items (used for calculating delivery charges on a per item basis ) be filtered  
* added action hook - wppizza_gateway_button_append_{gatewayIdent} - that could be used to print stuff/formfields after a specific gateway button (if not using dropdown for gateway selection) 
* added method to gateway class  - wppizza_gateway_append_formfields - to output/append a passed formfields array suitably html formatted (labels/required/input etc) somewhere (for example i conjunction with  wppizza_gateway_button_append_{gatewayIdent} hook  
* maintenence: load gateway classes and variables conditionally in admin and frontend  
19th September 2014  
 
2.11    
* added 'EXPIRED' ENUM value to wppizza_orders table payment_status field   
* added filter to payment method fieldset "wppizza_filter_paymentmethod_confirmation" on confirmation page that could be used if required  
* added action (wppizza_pickup_toggle,true/false) that runs after selection of pickup/no-pickup selection changes  
* added (missing) filter for menu item meta data in ajax requests "wppizza_filter_loop_meta_ajax,$meta,$id" that - most probably - would be used in conjunction with "wppizza_filter_loop_meta,$meta,$id"  
* added some more gateway functions for ease of use in future gateway development    
* added option that forces the user to confirm or cancel (rather than just clicking ok) when changing from pickup to delivery and vice versa  
* added third argument ($items) to relevant do_action hooks in "wppizza-show-order.php" template  
* maintenance/internal: as per gettext/wp guidelines loading textdomain on init as opposed to in __construct  
* more consistent saving of userdata sessions  
* made gateway that use overlays for example as opposed to redirect also work when using confirmation page  
* maintenance/internal: some possible phpnotices on first gateway activations/installations fixed  
* WPML: added missing confirmation form strings (on confirmation page if used) to WPML string translation  
* WPML: fixed spinner (updating quantities on order page) not displaying in secondary languages when using WPML  
* WPML: customer value labels (telephone, address etc for example ) are also now being sent/shown in the right language in emails and on thankyou pages etc  
* WPML: automatically register all relevant gateway extensions settings in wpml string translation  
* WPML: maintenance/internal: eliminated (wpml related) variable that more or less duplicated all the option data (should help memory consumption somewhat)  
* WPML: maintenance/internal: changed registration and translation of wpml strings to automatically register any gateway strings and only load those on order page (saves a few unnecessary queries on some pages)   
* WPML: maintenance/internal: using if(function_exists('icl_translate')) BEFORE including wpml related files to avoid unnecessary file access calls  
* WPML: maintenance/internal: using require_once (as opposed to require) if possible/appropriate for wpml includes (mainly admin registration of wpml strings) to reduce database queries  
* WPML: Note: this whole WPML thing is getting a bit unwieldy/messy now and should at some point be re-factured (However, that's quite a big job, so no ETA yet, as it all seems to work for the time being)  
15th September 2014  


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


