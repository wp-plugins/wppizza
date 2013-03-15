<?php
$str='';
	$str.="<span class='wppizza_option'>";
	$str.="<input id='wppizza_".$field."_".$k."' class='wppizza-getkey' type='hidden'>";
	$str.="<span class='wppizza_label'>".__('Label', $this->pluginLocale).":</span>";
	/*existing*/
	if(is_array($v)){
	foreach($v as $c=>$obj){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$c."][lbl]' size='10' type='text' value='".$obj['lbl']."' />";
	}}
	/*ajax*/
	if(isset($v) && !is_array($v)){
	for($i=0;$i<$v;$i++){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$i."][lbl]' size='10' type='text' value='' />";
	}}

	$str.="<br/>";
	$str.="<span class='wppizza_label'>".__('Default Prices', $this->pluginLocale).":</span>";
	/*existing*/
	if(is_array($v)){
	foreach($v as $c=>$obj){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$c."][price]' size='10' type='text' value='".$obj['price']."' />";
	}}
	/*ajax*/
	if(isset($v) && !is_array($v)){
	for($i=0;$i<$v;$i++){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$i."][price]' size='10' type='text' value='' />";
	}}

	if(!isset($optionInUse) || (isset($optionInUse) && !in_array($k,$optionInUse['sizes']))){
	$str.="<a href='#' class='wppizza-delete ".$field." button' title='".__('delete', $this->pluginLocale)."'> [X] </a>";
	}else{
	$str.="".__('in use', $this->pluginLocale)."";
	}
	$str.="<hr />";
	$str.="</span>";
?>