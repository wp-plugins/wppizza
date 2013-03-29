=== WPPizza ===
Contributors: ollybach
Donate link: http://www.wp-pizza.com/
Author URI: http://www.wp-pizza.com
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Tags: pizza, restaurant, order online, cash on delivery, multilingual
Requires at least: PHP 5.2, WP 3.3 
Tested up to: 3.5.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Restaurant Plugin (not only for Pizza). Maintain your Menu (multiple sizes, prices, categories). Accept COD online orders. Multilingual.



== Description ==

- **Conceived for Pizza Delivery Businesses, but flexible enough to serve any restaurant type.**

- Maintain your restaurant menu online and accept cash on delivery orders.

- Set categories, multiple prices per item and descriptions.

- Multilingual Frontend (just update labels in admin settings page and/or widget as required)

- Keeps track of your online orders.

- Shortcode enabled. (see <a href='http://wordpress.org/extend/plugins/wppizza/faq/' >FAQ</a> for details)


**To see the plugin in action with different themes try it at <a href="http://www.wp-pizza.com/">www.wp-pizza.com</a>**

**if you wish to allow your customers to add additional ingredients to any given menu item, have a look at the premium <a href='http://www.wp-pizza.com/'>"WPPizza Add Ingredients"</a> extension**

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
	
	to be able to add items to your shopping cart make sure that ALL pages that display your menu items have the shopping cart displayed somewhere, so:
	- display the shoppingcart somewhere on those pages (typically in a sidebar) using the wppizza widget (using type:cart) or the following shortcode: [wppizza type='cart']
	- if the cart displays "currently closed" adjust your opening times in wppizza->settings->openingtimes
	- make sure the navigation to those pages gets displayed somewhere (this will normally already be the case - usually by some pagelist or menu ).

	that should be it.......

	NOTE: **some themes (custom community for example) require that you distinctly save the automatically inserted pages again (i.e "order page", etc) to generate the right markup and display correctly. So if the layout is messed up on initial install, try to just save those pages once and see if that fixes things.**

- **Option 2** - using templates:
	
	- copy your themes 'page.php' file as 'wppizza-wrapper.php' into your theme directory(if your theme does not have a page.php file, look for archive.php or even index.php)
	- open /wppizza/templates/wppizza-wrapper.php and copy everything between 	[copy from here .....] to [...........copy to here]
	- place this snippet in the 'wppizza-wrapper.php' file we created above in your theme directory, REPLACING the original loop (including the while ( have_posts() ) : the_post(); or similar part) 
	- display the navigation by either using the widget (type:navigation) or a shortcode [wppizza type='navigation']
	- ensure you still have an order page that includes the following shortcode [wppizza type='orderpage'] and wppizza->settings->order settings: 'order page' is set to use this page
	- if you wish , you can now delete all wppizza default pages EXCEPT THE ORDER PAGE. However,if using permalinks, you might want to keep the parent page (default: Our Menu) and set the permalinks in wppizza->settings to this page. If you do, make sure to update permalinks structure once.

	Now you do not have to maintain any wppizza category pages, or navigation when adding new categories or menu items to wppizza as it's all taken care of automagically.

**PS**: you might have to adjust the css to work within your theme.  
see <a href='http://wordpress.org/extend/plugins/wppizza/faq/'>FAQ: "Can I edit the css ?"</a> for details  


**Uninstall**

Please note:  
although all options, menue items and menue categories get deleted from the database along with the table that holds any orders you may have received, you will manually have to delete any additional pages (such as the order page for example) that have been created as i have no way of knowing if you are using this page elsewhere or have changed the content/name of it.  
the same goes for the 3 example icons that come with this plugin as you might have used them elsewhere.



== Upgrade Notice ==

Please Upgrade asap, as there was a bug that displayed default prices as opposed to the actual price per itme

== Screenshots ==

1. frontend example
2. administration - widget
3. administration - categories
4. administration - menu item
5. administration - order settings (one of many option screens)


== Other Notes ==

please note that the icons used in the demo installation are <a href="http://www.iconarchive.com/show/desktop-buffet-icons-by-aha-soft.html">iconarchive.com</a> icons and not for commercial use.  
if you do wish to use any icon from this set commercially, please follow <a href="http://www.desktop-icon.com/stock-icons/desktop-buffet-icons.htm">this link</a> to purchase it.



== Changelog ==

1.0.3  
* fixed bug that displayed default as opposed to actual prices
* updated google library for timepicker when setting opening times  
* readme.txt updates  
* removed some obsolete functions and stripped tags when submitting an order  
* removed "loading" string in div when waiting for order to be processed/sent (ajax spinner should be enough)  
* added padding to div to pretty up thank you page  
* added a lot more currencies (including not displaying any at all)  
* added the ability to hide currency symbol next to items only  
- 29th March 2013  

1.0.2  
* minor readme.txt updates  
* enable screen info when first installing plugin  
* moved screenshots out of plugin into repository assets directory  
* minor bugfixes  
 - 17th March 2013

1.0.1  
* minor readme.txt updates - 15th March 2013

1.0  
* Initial release - 15th March 2013



== Frequently Asked Questions ==


= Can I edit the css ? =

although the css has been written so that it works with many themes out of the box (see www.wp-pizza.com - all themes use the same default stylesheet) you might want to adjust some things here and there to work with your theme (especially if you want to support older browsers).  
if you do, copy the wppizza/css/wppizza-default.css to your theme directory (so it does not get overwritten by future updates of the plugin) and edit as required  


= Can I use this plugin when I am not using english on my site ? =

of course you can.  
although the administration backend is currently only available in english (albeit translation ready, contact me if you want to help me translating it into a different language), 
you can have the frontend in whatever language you want. just go to wppizza->settings->localization and edit the variables as required


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


= Can I just display the menu without offering online order  ? =

Sure.  
Just don't display the shoppingcart anywhere. If you choose to do this, you might also want to delete any orderpage you might have (as there's nothing to order).  



= I'm using the plugin with xyz theme and it's all messed up  =

first thing to try (if you haven't done so already): re-save the pages WPPizza has created   
(I'm NOT talking about the WPPizza Categories and Menu Item Pages, but the pages that display a list of your item - typically a page that has somthing like [wppizza -some attributes-] on it)

If that still doesn't help, you might have to adjust the CSS (see 'Can I edit the css ?' above)

If you have problems, let me know what theme you are using and I'll have a look..  



= What are the available shortcodes ? =

in case where you cannot or do not want to use a widget, here are the corresponding shortcodes:
	
- **display orderpage**

	attributes:  
	- type='orderpage' 		(required [str])  
	
	example: 		[wppizza type='orderpage']	


- **display items** in category 

	attributes:  
	- category='pizza' 		(optional: '[category-slug]'. if omitted, will display the first category)  
	- noheader='1' 			(optional: 'anything'. omit attribute to show header. will suppress header (category title and description) in wppizza-loop.php. you can globally hide all category headers by setting "suppress headers" in wppizza->settings->layout.)  
	- showadditives='0' 	(optional: '0' or '1'. if omitted, a list of additives will be displayed if any of the category items has additives added. if set (0 or 1): force to display/hide additives list. useful when displaying more than 1 category on a page)  
	- note: if you want to edit the category loop and/or headers, copy wppizza-loop.php from the plugins template directory into your theme directory and edit it there.  
	
	example: 		[wppizza category='pizza' noheader='1' showadditives='0']


- **display openingtimes** (returns grouped opening times in a string):

	attributes:  
	- type='openingtimes' 	(required [str])  

	example: 		[wppizza type='openingtimes']


- **display shopping cart**

	attributes:  
	- type='cart' 			(required [str])  
 	- openingtimes='1' 		(optional[bool]: anything. if its defined openingtimes get displayed above cart)  
 	- orderinfo=1				(optional[bool]: anything. if its defined order info (discounts free delivery etc) get displayed below cart)  
 	- width='200px' 			(optional[str]: value in px or % -> defaults to 100%) (although < 150px is probably bad)  
 	- height='200' 			(optional[str]: value in px -> defaults to 250px)  
	
	example: 		[wppizza type='cart' openingtimes='1' orderinfo='1' width='90%' height='350']  


- **display the navigation** (when using template files , see What to do on first install ? -> option 2):
	
	attributes:  
	- type='navigation' 		(required [str])  
	- title='some title' 		(optional[str]: will render as h2 as first element in cart element if set)  

	example: 		[wppizza type='navigation' title='some title']


= Can I edit the templates ? =

Sure, if you want.  
Just make sure you copy the relevant template from wppizza/templates/ to your theme directory and edit those so you dont loose your edits when the plugin gets updated. Make sure to read the comments in the relevant files.


= How can I submit a bug, ask for help or request a new feature? =

	- leave a message on the <a href="http://wordpress.org/support/plugin/wppizza">wordpress forum</a> and I'll respond asap.  
	- send an email to support[at]wp-pizza.com with as much info as you can give me or 
	- use the "contact us" or "feature request" page on <a href="http://www.wp-pizza.com/">www.wp-pizza.com</a>

