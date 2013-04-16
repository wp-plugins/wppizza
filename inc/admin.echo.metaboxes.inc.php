<?php
	$options = $this->pluginOptions;
	$optionsSizes =wppizza_sizes_available($options['sizes']);
	$optionsCurrency =$options['order']['currency_symbol'];
	$meta_values = get_post_meta($meta_options->ID, $this->pluginSlug);
	$meta_values = $meta_values[0];

	$str='';
	
	/****  pricetiers and prices ***/
	$str.="<div class='".$this->pluginSlug."_option'>";
	$str.="<div class='wppizza-meta-label'>".__('pricetier and prices', $this->pluginLocale).":</div> ";
	$str.="<select name='".$this->pluginSlug."[sizes]' class='wppizza_pricetier_select'>";
	foreach($optionsSizes as $l=>$m){
		if($l==$meta_values['sizes']){$sel=" selected='selected'";}else{$sel='';}
		$str.="<option value='".$l."'".$sel.">".implode(", ",$m['lbl'])."</option>";
	}
	$str.="</select>";
	$str.="<span class='wppizza_pricetiers'>";
		foreach($meta_values['prices'] as $k=>$v){
			$str.="<input name='".$this->pluginSlug."[prices][]' size='5' type='text' value='".wppizza_output_format_price($v)."' />".$optionsCurrency."";
	}
	$str.="</span>";
	$str.="</div>";
		
	/*->*** which additives in item ***/
	$str.="<div class='".$this->pluginSlug."_option'>";
	$str.="<div class='wppizza-meta-label'>".__('contains additives', $this->pluginLocale).":</div> ";
	asort($options['additives']);//sort but keep index
	foreach($options['additives']  as $s=>$o){
		$str.="<label class='button'>";
		$str.="<input name='".$this->pluginSlug."[additives][".$s."]' size='5' type='checkbox' ". checked(in_array($s,$meta_values['additives']),true,false)." value='".$s."' /> ".$o."";
		$str.="</label>";			
	}
	$str.="</div>";

/*--> moved to add ingredients plugin ************/
	/*->***  enable addition of ingredients by customer on/off***/
//	$str.="<div class='".$this->pluginSlug."_option'>";
//	$str.="<label>";
//	$str.="".__('allow customers to add additional ingredients to this item ?', $this->pluginLocale).": ";
//	$str.="<span class='button'>";
//	$str.="<input name='".$this->pluginSlug."[add_ingredients]' type='checkbox' ". checked($meta_values['add_ingredients'],true,false)." value='1' />";				
//	$str.="</span>";
//	$str.="</label>";
//	$str.="</div>";
/*--> moved to add ingredients plugin end************/
	print"".$str;
?>