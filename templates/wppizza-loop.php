<?php
 /****************************************************************************************
 *
 *
 *	WPPizza - Category Loop Template
 *	IF YOU MUST EDIT THIS, READ THE COMMENTS
 *	
 *
 ****************************************************************************************/
	/******************************************
	if we are trying to get the loop from a shortcode/widget in another page
	not related to the wppizza custom post type we will have a different post type,
	so we explicitly set it here to generate the right classes etc
	*******************************************/ 
	$post_type=WPPIZZA_POST_TYPE;
	
	/***********get plugin options **************/
	$options=get_option(WPPIZZA_SLUG);
	$currency=$options['order']['currency_symbol'];//put currency into a shorter variable. just easier to deal with further down
	$txt=$options['localization'];/**put localization vars into a shorter variable
	
	/**check if we have set the headers to be suppressed in wppizza->settings->layout**/
	if($options['layout']['suppress_loop_headers']){
		$noheader=1;	
	}
	/*check if we are showing prices*/
	if($options['layout']['hide_prices']){$hidePrices=1;}
	/*check if we are showing cart icon*/
	if($options['layout']['hide_cart_icon']){$hideCartIcon=' '.$post_type.'-no-cart';}else{$hideCartIcon='';}
	/*check if online order has been enabled and if so , remove title and classes to disable adding to cart js*/
	if($options['layout']['disable_online_order']){$priceClass=''; $priceTitle='';}else{$priceClass=' '.$post_type.'-add-to-cart'; $priceTitle=' title="'.$txt['add_to_cart']['lbl'].'"';}
	



	/****************************************************************
	also, if we are retrieving the loop via a shortcode/widget 
	not on a page with this custom post type
	we will have set set the query variable explicitly to this category
	otherwise just get the one we have
	*****************************************************************/
	if(isset($query_var)){
		$termSlug=$query_var;
	}else{
		$termSlug=get_query_var( WPPIZZA_TAXONOMY );
	}

	/*************************************************************
	now lets get term descriptions , names etc
	only needs to run when noheader is not set though
	*************************************************************/
	if(!isset($noheader)){
		$termDetails = get_term_by( 'slug', $termSlug, WPPIZZA_TAXONOMY);
	}
	/*************************************************************	
	build and run the query
	*************************************************************/
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
		'post_type' => ''.WPPIZZA_POST_TYPE.'',
		'posts_per_page' => $options['layout']['items_per_loop'],
		'paged' => $paged ,
		'tax_query' => array(
			array(
				'taxonomy' => ''.WPPIZZA_TAXONOMY.'',
				'field' => 'slug',
				'terms' => ''.$termSlug.'',
				'include_children' => false
			)
		),
		'orderby'=>'menu_order',
		'order'=>'ASC'
	);
	$the_query = new WP_Query( $args );
?>
<?php
/********************************************
 *
 *	[OUTPUT HEADER]
 *	[edit or even delete if you want]
 *	[alternatively - and better - set "noheader" in shortcode, "Suppress Category Header" in widget
 *	or just suppress all headers above looped items  in wppizza settings->layout]
 * 
 ********************************************/
?>
<?php if(!isset($noheader)){ /*exclude header if set*/?>
	<header class="page-header entry-header <?php echo $post_type ?>-header">
		<h1 class="page-title entry-title <?php echo $post_type ?>-title"><?php echo $termDetails->name ?></h1>
		<?php if ( $termDetails->description!='' ) :?>
		<div class="entry-meta <?php echo $post_type ?>-header-meta"><?php echo $termDetails->description; ?></div>
		<?php endif; ?>
	</header>
<?php } ?>
<?php
/********************************************
*
*
*	[OUTPUT LOOP - edit if you want/must]
*	WARNING: be careful not to change or delete any classes or id's (especially in the prices section), as the ajax functionality when adding items to cart depends on them
*	Furthermore, the used CSS has - obviously - been setup with those in min.
*	You should be able to add additional classes though. Make sure you test things.
*
*
 ********************************************/
	/* Start the Loop */
	while ( $the_query->have_posts() ) : $the_query->the_post();
	/*get meta data for this post**/
	$meta=get_post_meta(get_the_ID(), $post_type, true );
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(array(''.$post_type.'-article','entry-content')); ?>>
<?php
/*********************************************
			[thumbnails]
**********************************************/
?>
		<?php if(has_post_thumbnail()) {?>
			<div class="<?php echo $post_type ?>-article-img">
			<?php the_post_thumbnail( 'thumbnail', array('class' => ''.$post_type.'-article-img-thumb')); ?>
			</div>
		<?php
			}else{
				if($options['layout']['placeholder_img']){//display placeholder
		?>
			<div class="<?php echo $post_type ?>-article-img">
				<div class="<?php echo $post_type ?>-article-img-placeholder"></div>
			</div>
		<?php } } ?>
<?php
/*********************************************
		[prices]
**********************************************/
	if(!isset($hidePrices)){
?>
		<div id="<?php echo $post_type ?>-article-tiers-<?php echo get_the_ID()?>" class="<?php echo $post_type ?>-article-tiers">
	   	<?php foreach($options['sizes'][$meta['sizes']] as $k=>$v){?>
	   		<span id='<? echo $post_type."-".get_the_ID()."-".$meta['sizes']."-".$k ?>' class='<?php echo $post_type ?>-article-price <?php echo $priceClass ?>' <?php echo $priceTitle ?>'>
	    		<span><? echo wppizza_output_format_float($meta['prices'][$k],'price')?></span>
	    		<div class='<?php echo $post_type ?>-article-price-lbl<?php echo $hideCartIcon?>'><?php echo $v['lbl']?></div>
	   		</span>
	   	<?php } ?>
	   		<span class='<?php echo $post_type ?>-article-price-currency'><?php echo $currency?></span>
		</div>
<?php
	}
/*********************************************
		[title, additives info and description]
**********************************************/
?>
		<div class="<?php echo $post_type ?>-article-info">
		<h2>
			<?php the_title(); ?>
			<?php if(count($meta['additives'])>0){?>
				<sup class='<?php echo $post_type ?>-article-additives' title='<? echo $txt['contains_additives']['lbl'] ?>'>*
	    		<?php foreach($meta['additives'] as $k=>$v){ $additivesOnPage=true; ?>
	    			(<?php echo $k ?>)
	    		<?php } ?>
				</sup>
			<?php } ?>
		</h2>
		<?php the_content();?>
		</div>
	</article>
<?php endwhile;	?>
<?php
/********************************************
 *
 *	[if any of the items have additives, display idents here]
 *	[if showadditives is distinctly set force/hide display]
 *
 ********************************************/
if(!isset($showadditives) || $showadditives!=0){
if(isset($additivesOnPage) || (isset($showadditives) && $showadditives==1)){
?>
	<div class='<?php echo $post_type ?>-contains-additives'>
	<?php foreach($options['additives'] as $k=>$v){?>
		<span><sup>(<?php echo $k ?>)</sup><?php echo $v ?></span>
	<?php } ?>
	</div>
<?php }} ?>
<?php
/*************************************************
	[pagination - no need to display empty divs]
**************************************************/
if($the_query->max_num_pages>1){
?>
<div class="navigation">
  <div class="alignleft"><?php previous_posts_link(''.$txt['previous']['lbl'].'') ?></div>
  <div class="alignright"><?php next_posts_link(''.$txt['next']['lbl'].'',$the_query->max_num_pages) ?></div>
</div>
<?php } ?>