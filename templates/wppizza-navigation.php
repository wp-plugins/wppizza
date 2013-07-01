<?php
/*
	Template for displaying WPPizza Category Navigation
	unless you want to wrap the whole thing in a div or something
	I would suggest you leave it alone......
*/
?>
<?php
/**set custom sort order**/
add_filter('terms_clauses', array($this,'wppizza_term_filter'), '', 1);
?>
<ul id="<?php echo $post_type ?>-categories" class="<?php echo $post_type ?>-categories">
<?php echo wp_list_categories( $args ); ?>
</ul>