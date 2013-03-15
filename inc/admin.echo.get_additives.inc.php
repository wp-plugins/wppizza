<?php
	$str='';
	$str.="<span class='wppizza_option'>";
	$str.="<input id='wppizza_".$field."_".$k."' name='".$this->pluginSlug."[".$field."][".$k."]' size='30' class='wppizza-getkey' type='text' value='".$v."' />";
	/**if not in use or just added via js***/
	if(!isset($optionInUse) || (isset($optionInUse) && !in_array($k,$optionInUse['additives']))){
		$str.="<a href='#' class='wppizza-delete button' title='".__('delete', $this->pluginLocale)."'> [X] </a>";
	}else{
		$str.="".__('in use', $this->pluginLocale)."";
	}
	$str.="</span>";
?>