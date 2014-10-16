=== WPPizza ===
Contributors: ollybach
Donate link: http://www.wp-pizza.com/
Author URI: http://www.wp-pizza.com
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Tags: pizza, restaurant, restaurant menu, ecommerce, e-commerce, commerce, wordpress ecommerce, store, shop, sales, shopping, cart, order online, cash on delivery, multilingual, checkout, configurable, variable, widgets, shipping, tax
Requires at least: PHP 5.3+, WP 3.3+ 
Tested up to: 4.0
Stable tag: 2.11.2.3
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


2.10.4.6  
* BUGFIX: 2.10.4.5 broke redirection of some gatways  , PLEASE UPDATE IF YOU ARE USING 2.10.4.5  
5th September 2014  

4th September 2014  
2.10.4.5  
* checked for compatibility with WP 4.0 (nothing to do :))  
* doublecheck when submitting order that shop is still open (in case someone stayed on the orderpage for ages without doing anything)  
* added filter in admin order history to be able to customise output if required  
* added action hooks to admin-get-json.php and get-json.php    
* added missing class to wrapper div of menu item text and pricetiers in responsive css  
* bugfix: closingtimes between 00:00 and 00:59 were not recognised properly (surprised noone noticed this before)  
* updated readme  
4th September 2014  


2.10.4.4  
* fix: dashboard sales widget was available/visible to all when it should only have been visible to users with "reports" access  
* updated readme  
2nd September 2014  


2.10.4.3  
* Maintenence: also delete (if any) wpml string translations from wpml db when uninstalling plugin to avoid orphaned/redundant db entries  
* added missing filter to also allow other plugins to update/save user meta values when checking "update my details" on order page  
* added some variables to do_action hooks in show order template  
1st September 2014  

2.10.4.2  
* fixed error when using bestseller shortcode as items with the same number of sales only displayed the last one    
* fixed time discrepancy in reports if php.ini timezone settings are - for some reason - different to wordpress timezone settings  
* maintenance: now always using a straight forward timestamp associated with the time of order when initializing/adding order to the db (instead of attempting to use microtime if available - as it's just overkill)  
19th August 2014  

2.10.4.1  
* eliminated a (one time and inconsequential) php notice on updating plugin regarding single_item_permalink_rewrite   
18th August 2014  
  
2.10.4  
* increased varchar for transaction_id in wppizza_orders table from 32 to 48 chars as some gateways have longer transaction ids  
* fixed issue where currencies defined as hex values where not displayed properly next to menu items in plaintext emails  
* added option to set single menu item permalink to something other than "wppizza"    
18th August 2014  


2.10.3  
* added admin dashboard overview widget  
* added missing symbol/currency for indian rupee  
* added lost password link under login on order page  
11th August 2014  

2.10.2.1  
* added: allow form fields to be required only on pickup and not on delivery (and still vice versa)  
* some minor changes/additions to gateway functions to aid development of future additional gateways  
11th August 2014  

2.10.2  
* added: allow change of quantities in order page      
* added: some more filters in order page, confirmation page, thank you page, order history and email (html/plaintext) templates to allow output filtering of items display (for example: don't display single item prices) without having to edit the templates directly (see wp-pizza.com support->codesnippets)  
* added: some more action hooks in template pages  
* minor css tweaks  
* some tidy up in places and php notices/warning eliminations    
9th August 2014  


2.10.1  
* added possible adding (via filters) of placeholders in order form form fields  
* added various filters to add addiional fields to registration and profile pages   
* TWEAK: set distinct css text color when displaying categories in cart above menu items (as some dark themes might have a light text color there on a light background which would make this unreadable)  
* BUGFIX: added "shop closed" display and disable sending of order in orderpage when shop is closed (previously it was still possible send order at a later date/time when menu items still existed in cart/session even though shop was closed)  
* BUGFIX: eliminated some more possible php notices  
5th August 2014  

2.10.0.1  
* revert to using uksort($array, 'strnatcmp') when sorting additives as ksort($array,SORT_NATURAL); is only available for php 5.4+  
5th August 2014  


2.10  
* added optional alternative tax rate (to be set on a per item basis)  
* added dedicated shipping tax rate  
* added option to set distinct minimum order value on pickup  
* added shortcode for bestsellers (see faq's -> shortcodes)  
* added shortcode for single menu items (see faq's -> shortcodes)  
* added ability to include menu items in general search results [wppizza->settings]  
* added dedicated search widget/shortcode for customisation of search parameters  
* added filter (wppizza_filter_email_subject_prefix, wppizza_filter_email_subject, wppizza_filter_email_subject_suffix) to enable filter/customisation of email subject   
* added span tag to opening times around times themselves as well as classes to aid formatting/styling if required  
* added "end" (key 35) to also confirm changed quantity in shoppingcart (when textbox is enabled for quantities)  
* added filter (wppizza_filter_order_form_fields) to enable addon plugins (such as "delivery by postcode" and the forthcoming "preorder" plugin) to add and add other form fields more easily and consistently to order page and confirmation page (if used)  
* minor alterations to frontend css (set lineheight for additives at bottom of page so list of additives that break over 2 or more lines are not that squished)  
* minor alterations to admin css  
* BUGFIX: wppizza stopped inbuilt WP search from working properly  
* BUGFIX: additives sorting when using id's did sort by string instead of number/natural  
* BUGFIX: added missing minus sign in order history before any applied discounts values  
* BUGFIX: got rid of some strict notices when uninstalling the plugin  
02nd August 2014  

1.0 - 2.9.4.6  
* previous changes cen be found in changelog-1.0-2.9.4.6.txt  



== Frequently Asked Questions ==


= Can I edit the css ? =

although the css has been written so that it works with many themes out of the box (see www.wp-pizza.com - all themes use the same default stylesheet) you might want to adjust some things here and there to work with your theme (especially if you want to support older browsers).  
if you do, copy the wppizza/css/wppizza-default.source.css (or wppizza/css/wppizza-responsive.source.css if using the responsive style) as wppizza-default.css (or wppizza-responsive.css)  to your theme directory (so it does not get overwritten by future updates of the plugin) and edit as required.  

**alternatively *(and possibly better as any future updates to the main css will still be reflected)*, just copy wppizza-custom.css to your theme directory (or child theme directory if used as of v2.8.9.7) and only overwrite the styles you need to override.**  

(this file will be read AFTER the main default.css). "Include css" has to be enabled for this to apply   

PS: Optionally as of version 2.8.9.7 - you could use a subdirectory called wppizza for all your customised wppizza templates.   


= Can I use this plugin when I am not using english on my site ? =

of course you can.  
although the administration backend is currently only available in a few other languages apart from english (albeit translation ready, contact me if you want to help me translating it into a different language), 
you can have the frontend in whatever language you want. just go to wppizza->settings->localization and edit the variables as required. Furthermore, WPPizza is WPML compatible.


= How do I sort the categories ? =

go to: wppizza->categories in the administration and drag and drop. If you wish to have subcategories, edit the required category and select the appropriate parent.


= How do I add a menu item ? =

go to: wppizza->add new  
add title, description, any preservatives the item might have (editable from wppizza->settings->additives), select pricetier (editable from wppizza->settings->meal sizes) and add prices as required.  
make sure to also select at least one category.  
if you wish to add an image, either set a featured image for this menu item (if available in your theme) or add an image as normal in your description.  
the size of the featured image displayed will depend on your thumbnail size set in settings->media: thumbnail size
if your theme does not allow featured images, and you are adding pictures directly into the description, you might want to turn off "display placeholder image" in wppizza->settings->layout  
alternatively, add " add_theme_support( 'post-thumbnails'); " (without the quotes) to your themes function.php file  

= How do I sort the menu items ? =

just use the order attribute (which btw is also available in quick edit). if you cannot see it in individual posts/menu items, you might have to click on "screen options" and check "attributes" first  
there is also a variety of drag and drop (custom) post type plugins available on wordpress if you prefer to sort the order that way  


= Can I just display the menu without offering online order  ? =

Sure.  
There are various options/possibilities depending what you want to achieve:
	
* Just don't display the shoppingcart anywhere. If you choose to do this, you might also want to delete any orderpage you might have (as there's nothing to order). 	
* If you still want to show the cart (to show combined prices/taxes/discounts) but want to disable ordering, go to wppizza->order settings and do not select an order page 
* you can also go to wppizza->layout and check "Completely disable online order"  
* and/or goto wppizza->gateways and disable all gateways 

if necessary - depending how you want this setup exactly - you might also want to consider deleting your orderpage entirely if it's not needed.  


= I'm using the plugin with xyz theme and it's all messed up  =

first thing to try (if you haven't done so already): re-save the pages WPPizza has created   
(I'm NOT talking about the WPPizza Categories and Menu Item Pages, but the pages that display a list of your item - typically a page that has somthing like [wppizza -some attributes-] on it)

make sure your theme (or one of your plugins) does not use an old version of jQuery (some themes/plugins override the version that comes bundled with wordpress - no, i don't know either why they would do that)  

If that still doesn't help, you might have to adjust the CSS (see 'Can I edit the css ?' above)

If you have problems, send me a link to your site I'll have a look...


= can i send html emails ? =  

yup.  
go to wppizza->settings and change "Select Type of Mail Delivery" to "HTML and plaintext"  
if you do , you probably want to edit the html template. To do so, move "wppizza-order-html-email.php" from the wppizza template directory to your theme folder and edit as required  
if you want to use smtp, do the same with "wppizza-phpmailer-settings.php"  and edit as needed  


= What are the available shortcodes ? =  

in case where you cannot or do not want to use a widget, here are the corresponding shortcodes:
	
- **display orderpage**

	attributes:  
	- type='orderpage' 		(required [str])  
	
	example: 		[wppizza type='orderpage']	


- **display items** in category 

	attributes:  
	- category='pizza' 		(optional: (string|array|!all). single slug, comma separated slugs or !all to display all (in conjunction with !all, you can also exclude categories by prefixing with "-" [minus]).  if omitted entirely, will display the first category)  
	- noheader='1' 			(optional: 'anything'. omit attribute to show header. will suppress header (category title and description) in wppizza-loop.php. you can globally hide all category headers by setting "suppress headers" in wppizza->settings->layout.)  
	- showadditives='0' 	(optional: '0' or '1'. if omitted, a list of additives will be displayed if any of the category items has additives added. if set (0 or 1): force to display/hide additives list. useful when displaying more than 1 category on a page)  
	- exclude='6,5,8' 		(optional [comma separated menu item id's]): exclude some id's  
	- include='6,5,8' 		(optional [comma separated menu item id's]): include only these id's (overrides exclude)  
	- note: if you want to edit the category loop and/or headers, copy wppizza-loop.php from the plugins template directory into your theme directory and edit it there.  
	
	example: 		[wppizza] =>will display all items in first category that has more than zero items   
	example: 		[wppizza category='pizza' noheader='1' showadditives='0' exclude='6,5,8'] => will display all items in category with slug "pizza", not showing headers or additives list and  excluding items 6,5 and 8  
	example: 		[wppizza category='pizza,pasta'] => will display all items in category with slug "pizza" or "pasta"  
	example: 		[wppizza category='!all' exclude='6,5,8'] => will display all items in all categories but excluding items with id 6,5,8  
	example: 		[wppizza category='!all,-pizza,-drinks'] => will display all categories except "pizza" and "drinks".     

- **display single menu item **   

	attributes:  
	- single='11' 		(required [str] - id of menu item to display)  	

	example: 		[wppizza single='11']  


- **display bestsellers** (from ALL categories, sorted by number of sales descending. only items with sales >=1 are included )  

	attributes:  
	- bestsellers='10' 		(required: integer of how many items to display). 
	- showadditives='0' 	(optional: '0' or '1'. if omitted, a list of additives will be displayed if any of the items has additives added. if set (0 or 1): force to display/hide additives list. useful when displaying more than 1 category on a page)  
	- include='6,5,8' 		(optional [comma separated menu item id's]): ADDITIONALLY include these id's to the bestsellers already displayed    
	- ifempty='5,9,8,11'		(optional [comma separated menu item id's]): if no sales have been made yet and no include have been defined - mainly for new installations - set the menu item id's you want to have displayed instead - ignores include attribute of appicable)  
	
	example: 		[wppizza bestsellers='10' ifempty='5,9,8,11' showadditives='0' include='6,5,8']
	


- **display openingtimes** (returns grouped opening times in a string):

	attributes:  
	- type='openingtimes' 	(required [str])  

	example: 		[wppizza type='openingtimes']


- **display shopping cart**

	attributes:  
	- type='cart' 			(required [str])  
 	- stickycart='1' 		(optional[bool]: anything. if its defined the cart will always be in viewpoint even when scrolling)  
 	- openingtimes='1' 		(optional[bool]: anything. if its defined openingtimes get displayed above cart)  
 	- orderinfo=1				(optional[bool]: anything. if its defined order info (discounts free delivery etc) get displayed below cart)  
 	- width='200px' 			(optional[str]: value in px or % -> defaults to 100%) (although < 150px is probably bad)  
 	- height='200' 			(optional[str]: value in px -> defaults to 250px)  
	
	example: 		[wppizza type='cart' openingtimes='1' orderinfo='1' width='90%' height='350']  


- **display the navigation** (when using template files , see What to do on first install ? -> option 2):
	
	attributes:  
	- type='navigation' 		(required [str])  
	- title='some title' 		(optional[str]: will render as h2 as first element in cart element if set)  
	- parent='slug-name' 		(optionsl [str]): only show child categories of this slug  
	- exclude='6,5,8' 			(optional [comma separated category id's]): exclude some id's  

	example: 		[wppizza type='navigation' title='some title' parent='slug-name' exclude='6,5,8']  



- **display the users order history** (displays login form if not logged in, "anyone can register" has to be enabled):  
	
	attributes:  
	- type='orderhistory' 		(required [str])  	
	- maxpp='5' 				(optional [int]: how many orders to display per page - default=10)  
	- multisite='1' 			(optional [str|int]: display orders from all blogs/sites - will be ignored in non-multiste setups.  )  
	- sitetitle='1' 			(optional [str|int]: display site/blog title for identification purposes next to order date on history page - will be ignored in non-multiste setups.)  
	
		
	example: 		[wppizza type='orderhistory']  
	example: 		[wppizza type='orderhistory' maxpp='5'] => will display 5 orders per page  
	example: 		[wppizza type='orderhistory' multisite='1'] => will display all orders from all blogs for this user in a multisite setup  
	example: 		[wppizza type='orderhistory' multisite='1' sitetitle='1'] => will display all orders from all blogs for this user in a multisite setup and will display relevant blog/site title next to order date   

	note: you would probably want to disable comments etc on that page  


- **display a searchbox **   

	attributes:  
	- type='search' 		(required [str])  	
	- include='wppizza,post,page,attachment,revision,nav_menu_item' (optional[str]: include menu items, posts, pages and/or other cpts respectively)  
	- loggedinonly='1' (optional[bool]: anything. if defined searchform only gets displayed for logged in users)  

	example: 		[wppizza type='search'  include='wppizza,post,page' loggedinonly='1']

	

= Shortcodes do not work in widgets ? =  

Some Themes do not have shortcode support enabled in text widgets.  
To enable, either add this line to the functions.php of your theme;

	add_filter('widget_text', 'do_shortcode');

or use one of the wordpress plugins available here for example, that do this  
http://wordpress.org/plugins/search.php?q=shortcode+in+text+widget  



= Can I edit the templates ? =

Sure, if you want.  
Just make sure you copy the relevant template from wppizza/templates/ to your theme directory (or as of version 2.8.9.7 child theme directory if used) and edit those, so you don't loose your edits when the plugin gets updated. Make sure to read the comments in the relevant files.

**Preferably though - if at all possible - I would suggest to use css and/or some of the action hooks provided  to be able to take advantage of any possible future updates or improvements of those templates**


= How do the orders get to my restaurant ? =

by email.  
if you need them to be sent by fax for example you will have to look into integrating your orders with a fax2email gateway. (search for it on your favourite search engine)


= Where do I set the images for any particular menu item? =

just use the "featured image" for a menu item  
if you do not have this options it's either hidden (go to "screen options" at the top of the page to enable/show it) or your theme does not have thumbnails enabled (tell the author about it. not my fault)
if the auther doesnt want to or cannot do anything about it, you can also try just to put the following code at the bottom of your themes function file (before the closing ?> if any )
	
	add_theme_support('post-thumbnails');

if you do *not* want to display *any* images, delete any featured images that may be associated with your menu items and uncheck "Display placeholder image when no image associated with meal item:" in wppizza->layout  


= My shop doesn't do Pizzas, how do I change the name and icon in the administration panel ? =

* to change the name (i.e WPPizza) just add "define('WPPIZZA_NAME', 'The Name You Want');" to your wp-config.php 
* to change the WPPizza Menu Icon in Admin Panel next to the Name just add "define('WPPIZZA_MENU_ICON', 'http://path/to/icon.png');" to your wp-config.php 
* to change the urls to something more suitable go to wppizza->settings and change the permalinks as appropriate (if linking to single items, make sure you read the notes below and in the plugin) 
	  

= single wppizza menu items display =  

if you did 

* NOT enable "Include wppizza menu items in regular search results" (wppizza->settings),  
* are NOT using the wppizza search widget (selectable in the wppizza widget)  
* or - if using the search widget - have NOT enabled "wppizza menu items" in the widget  
* have NOT edited the templates to link to single menu items  
* and are NOT using a sitemap generation plugin that automatically indexes single posts/menu items (or are excluding those single menu items from the sitemap)  

the *following is irrelevant*.


*however*, if any of the above is true, please read on with regards to the "how to display single wppizza menu items" settings/template/page to use in wppizza->settings->Include wppizza menu items in regular search results .

assuming you *are* including wppizza menu items in your search results for example, you will get a list of results with links (typically the title, but it depends on the theme you are using) to that particular menu item alongside excerpts/content amd/or an associated featured image.  
clicking on said link will display the single menu item in its own page according to how your theme's templates display normal blogposts  

if you would like this single item to display like all your other wppizza menu items, there are two choices:
	
**preferred (and really the right way to do this when working with wordpress)**  
- set/leave the selected option in "wppizza->settings->Include wppizza menu items in regular search results" at "default or custom template [single-wppizza.php if exists]"
- your theme has (should have) a file called single.php  
- make a copy of this file in the same directory, renaming it to single-wppizza.php  
- this file is likely to have something like the following (between some coding like "while ( have_posts() ) : the_post(); .... endwhile "):
	

	get_template_part( 'content', get_post_format() );


or similar 

REPLACE this bit of coding (leaving the while/endwhile intact) with :  

	if ($template_file = locate_template( array ('wppizza-loop.php' ))){  
		include_once($template_file);  
	}else{  
		include_once(''.WPPIZZA_PATH.'templates/wppizza-loop.php');  
	}  

and your single menu items should now display as any other menu item  
(if you are using the responsive style - "wppizza->layout->Which style to use" -  use wppizza-loop-responsive.php instead of wppizza-loop.php in the code above )  

in short:  

* make sure option is set to "default or custom template [single-wppizza.php if exists]"  
* copy your single.php as single-wppizza.php  
* edit/replace the loop code in that single-wppizza.php file as described above  
* save  


additionally , you could of course make any other edits to that file (like removing comment templates if added by your theme etc ) as you wish  
	


**alternative (in case you are not able to or are comfortable with editing/copying theme templates)**  
set a page you want to use from the dropdown list of pages provided (still talking about wppizza->settings->Include wppizza menu items in regular search results : "how to display single wppizza menu items")  

this in turn will "hijack" this page and it's contents when displaying single wppizza menu items while keeping any other formatting, sidebars etc you are using for your pages   

however, the chances are / caveat is , that your theme will probably still display at least the page title somewhere, so this really is just an added option on not really the way this sort of thing should be done with wordpress  

you can of course also create a new, empty page and use that one (the above still aplies though and you would probably / via a plugin or some such, want to exclude this page from your normal wordpress page menu as it would be blank if it does not display a single menu item coming from a link of a search result) 




as ever, if you have any questions or need help with this, leave a message in the support forum here or on wp-pizza.com  



= using a cache plugin =  

* if you are using a cache plugin, you MUST exclude your order page from being cached  
* you will probably also want to enable "I am using a caching plugin" in wppizza->settings  
	  


= How can I submit a bug, ask for help or request a new feature? =

- leave a message on the <a href="http://wordpress.org/support/plugin/wppizza">wordpress forum</a> and I'll respond asap.  
- send an email to dev[at]wp-pizza.com with as much info as you can give me or 
- use the "contact us" or "feature request" page on <a href="http://www.wp-pizza.com/">www.wp-pizza.com</a>

