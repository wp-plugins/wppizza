<?php
	$gateways=$this->wppizza_get_registered_gateways();
	if(count($gateways)>0){
	asort($gateways);
	foreach($gateways as $k=>$gw){
		$gwIdent=strtolower($gw['ident']);

		print"<div class='wppizza-gateway'>";

			/***gateway enabled on  frontend**/
			print"<div class='wppizza-gateway-head button'>";
			print"<label>".$gw['gatewayName']."</label>";
			print"<span class='wppizza-gateway-enable'>";
			print"<input name='".$this->pluginSlug."[".$field."][gateway_selected][".$gw['ident']."]' type='checkbox'  value='1' ".checked($gw['enabled'],true,false)." /> ";
			print"".__('enabled Y/N',$this->pluginLocale)."";
			print"</span>";
			print"<span class='wppizza-gateway-show-options button'>".__('show options',$this->pluginLocale)."</span>";
			print"</div>";

			print"<div class='wppizza-gateway-settings'>";

			/***additional info**/
			if(isset($gw['gatewayAdditionalInfo']) && $gw['gatewayAdditionalInfo']!=''){
				print"<div>";
					print"".$gw['gatewayAdditionalInfo'];
				print"</div>";
			}
			/***gateway order on  frontend**/
			if(count($gateways)>1){
				print"<div>";
				print"<label>".__('Frontend display order',$this->pluginLocale)."</label>";
				print"<input name='".$this->pluginSlug."[".$field."][gateway_order][".$gw['ident']."]' type='text' size='2' value='".$gw['sort']."'  /> ";
				print"</div>";
			}else{
				print"<input name='".$this->pluginSlug."[".$field."][gateway_order][".$gw['ident']."]' type='hidden' value='".$gw['sort']."'  /> ";
			}

			/***gateway label on  frontend (if empty uses gatewayName**/
			print"<div>";
			print"<label>".__('Frontend Label',$this->pluginLocale)."</label>";
			print"<input name='".$this->pluginSlug."[gateways][wppizza_gateway_".$gwIdent."][gateway_label]' type='text' size='40' value='".$gw['gatewayOptions']['gateway_label']."' /> ";
			print"[".__('displays',$this->pluginLocale)." '".$gw['gatewayName']."' ".__('if Empty',$this->pluginLocale)."]";
			print"<br/>[".__('only displayed if more than one gateway installed, activated and enabled',$this->pluginLocale)."]";
			print"</div>";

			/***gateway additional info on  frontend - can be left empty**/
				print"<div>";
				print"<label>".__('Frontend: Additional Plugin Information',$this->pluginLocale)."</label>";
				print"<textarea name='".$this->pluginSlug."[gateways][wppizza_gateway_".$gwIdent."][gateway_info]' />".$gw['gatewayOptions']['gateway_info']."</textarea>";
				print"<br/>[".__('only displayed if more than one gateway installed, activated and enabled',$this->pluginLocale)."]";
				print"</div>";

			/***gateway specific elements**/
			foreach($gw['gatewaySettings'] as $key=>$val){
				print"<div>";
					print"<label>".$val['label']."</label>";
					wppizza_echo_formfield($val['type'],$val['key'],$this->pluginSlug."[gateways][".$gw['gatewayOptionsName']."][".$val['key']."]",$val['value'],$val['placeholder'],$val['options']);
					print"<br/>".$val['descr']."";
				print"</div>";
			}

		print"</div>";
		print"</div>";
	}}
?>