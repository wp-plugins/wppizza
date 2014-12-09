<?php
/****************************************************************************************
*
*
*	WPPizza - Category Loop Template
*	get/set variables 
*	
*
****************************************************************************************/
/******************************************
	if we are trying to get the loop from a shortcode/widget in another page
	not related to the wppizza custom post type we will have a different post type,
	so we explicitly set it here to generate the right classes etc
*******************************************/ 
	$post_type=WPPIZZA_POST_TYPE;
	
	/*********** plugin options **************/
	$currency=$options['order']['currency_symbol'];//put currency into a shorter variable. just easier to deal with further down
	$optionsDecimals=$options['layout']['hide_decimals'];
	$txt=$options['localization'];/**put localization vars into a shorter variable
	
	/**check if we have set the headers to be suppressed in wppizza->settings->layout**/
	if($options['layout']['suppress_loop_headers']){
		$noheader=1;	
	}
	/*check if we are showing prices*/
	if($options['layout']['hide_prices']){$hidePrices=1;}
	/*check currency to left of price*/
	if($options['layout']['currency_symbol_left']){$currencyLeft=1;}
	/*check if we are hiding pricetiers if there's only one*/
	if($options['layout']['hide_single_pricetier']){$hidePricetier=1;}
	/*check if we are showing cart icon*/
	if($options['layout']['hide_cart_icon']){$hideCartIcon=' '.$post_type.'-no-cart';}else{$hideCartIcon='';}
	/*check if we are showing currency next to item*/
	if($options['layout']['hide_item_currency_symbol']){$hideCurrencySymbol=1;}	
	/*check if online order has been enabled and if so , remove title and classes to disable adding to cart js*/
	if($options['layout']['disable_online_order']){$priceClass=''; $priceTitle='';}else{$priceClass=' '.$post_type.'-add-to-cart'; $priceTitle=' title="'.$txt['add_to_cart']['lbl'].'"';}
	/**add trigger adding to cart from title (h2) when there's only one pricetier***/
	if($options['layout']['add_to_cart_on_title_click']){$clickTrigger=1;}
	/**check if we want to sort and add by category to emails**/
	if($options['layout']['items_group_sort_print_by_category']){$groupItemByCat=1;}



/****************************************************************
	also, if we are retrieving the loop via a shortcode/widget 
	not on a page with this custom post type
	we will have set set the query variable explicitly to this category
	otherwise just get the one we have and get term descriptions, id's etc
*****************************************************************/
if(!is_single()){
	if(isset($query_var)){
		$termSlug=$query_var;
		$termDetails=$query;
		$categoryId=$query->term_id;
	}else{		
		$termSlug=get_query_var( WPPIZZA_TAXONOMY );		
		$termDetails = get_term_by( 'slug', $termSlug, WPPIZZA_TAXONOMY);
		$categoryId=$termDetails->term_id;
	}
}
/********************************************************
	single posts might be in more than one category, 
	however if we  have no idea here which one it should be , 
	we just take the first one unless set by GET[c]
********************************************************/
if(is_single() && get_post_type()==$post_type){
	$termDetails = wp_get_post_terms( $post->ID, WPPIZZA_TAXONOMY);
	$termSlug='';/*ini as unknown*/
	$categoryId=0;/*ini as unknown*/

	if ($termDetails && ! is_wp_error($termDetails)){
		if(isset($_GET['c'])){/*if we know which category this is apply, otherwsise use first available**/
			$taxonomies = wp_list_pluck( $termDetails, 'term_id', 'slug');
			$termSlug=$_GET['c'];			
			$categoryId=$taxonomies[$_GET['c']];			
		}else{
			$termSlug=$termDetails[0]->slug;
			$categoryId=$termDetails[0]->term_id;			
		}
	}
}
/**************************************************************************
	for people that cannot read or insist nevertheless on putting 
	the shortcode on a post or other custom post type although 
	- AS THE DOCUMENTAION SAYS - IT BELONGS ON PAGES ONLY 
	

	NOTE: as we cannot use the current post id (as it's not a
	wppizza post) we'll do our best to get and set the appropriate vars
	
	
	ADVISE: THE BELOW IS REALLY NOT SUPPORTED AS SUCH.
	IT MIGHT OR MIGHT NOT WORK DEPENDING ON POST (TYPE) USED.
	just saying.....
**************************************************************************/
if(is_single() && get_post_type()!=$post_type){
	/*ignore all of the below if there's no query var to start off with*/
	if(isset($query_var)){
		
		$terms = get_terms(WPPIZZA_TAXONOMY);/**get all wppizza categories*/
		$taxonomies = wp_list_pluck( $terms, 'term_id', 'slug');/**array slug->id*/					
		$termSlug='';/*ini as unknown*/
		$categoryId=0;/*ini as unknown*/		
		$termDetails=array();/*ini as unknown*/		
		
	
		/**lets see if we can find one**/
		if(isset($taxonomies[$query_var])){
			$termSlug=$query_var;
			$categoryId=$taxonomies[$query_var];
			
			$tDetails=array();
			foreach($terms as $term){
				if($term->term_id==$categoryId){
					$tDetails=$term;
					break;
				}
			}
			/*get details from cat id**/
			$termDetails[0] = $tDetails;
			$termDetails[0]->filter = 'raw'; 
		}
	}
}
/*************************************************************	
	build and run the query
*************************************************************/
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
		'post_type' => ''.WPPIZZA_POST_TYPE.'',
		'posts_per_page' => $options['layout']['items_per_loop'],
		'paged' => $paged ,
		'post__not_in' => !empty($exclude) ? $exclude : '' , /*ADDED in v.2.7.3*/
		'post__in' => !empty($include) ? $include : '' , /*ADDED in v.2.8.9.7*/
		'tax_query' => array(
			array(
				'taxonomy' => ''.WPPIZZA_TAXONOMY.'',
				'field' => 'slug',
				'terms' => $termSlug,
				'include_children' => false
			)
		),
		'orderby'=>'menu_order',
		'order'=>'ASC'
	);
	/**new in version 2.5. currenly used to display single posts***/
	$args = apply_filters('wppizza_filter_loop', $args);

	/**execute query**/	
	$the_query = new WP_Query( $args );
?>