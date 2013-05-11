<?php
$options = $this->pluginOptions;
	if($options!=0){

	$optionInUse=wppizza_options_in_use();//outputs an array $arr=array(['sizes']=>array(),['additives']=>array());
	$optionSizes=wppizza_sizes_available($options['sizes']);//outputs an array $arr=array(['lbl']=>array(),['prices']=>array());
	$optionsDecimals=$options['layout']['hide_decimals'];

			if($field=='version'){
				echo "{$options['plugin_data'][$field]}";
			}
			if($field=='js_in_footer'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
			}
			if($field=='wp_multisite_session_per_site'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
			}
			if($field=='mail_type'){
				//echo "<input id='".$field."' name='".$this->pluginSlug."[".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo "<select name='".$this->pluginSlug."[plugin_data][".$field."]' />";
					echo"<option value='mail' ".selected($options['plugin_data'][$field],"mail",false).">".__('default [uses mail]', $this->pluginLocale)."</option>";
					echo"<option value='wp_mail' ".selected($options['plugin_data'][$field],"wp_mail",false).">".__('Wordpress Mail Function [uses wp_mail]', $this->pluginLocale)."</option>";
					echo"<option value='phpmailer' ".selected($options['plugin_data'][$field],"phpmailer",false).">".__('HTML and Plaintext [uses PHPMailer]', $this->pluginLocale)."</option>";
				echo "</select>";
			}
			if($field=='category_parent_page'){
				/*get all pages**/
				$pages=get_pages(array('post_type'=> 'page','echo'=>0,'title_li'=>''));

				/**check which pages have children (so we can exclude from dropdown as otherwise children pages will not be accessible*/
				$exclude=array();
				foreach($pages as $k=>$v){
					$children = get_pages('child_of='.$v->ID);
					if( count( $children ) != 0 ) {$exclude[]=$v->ID;}
				}
				$exclude[]=get_option('page_for_posts');/*also exclude page thats set for default posts*/

				echo "<select name='".$this->pluginSlug."[plugin_data][".$field."]' />";
					echo"<option value=''>".__('no parent [default]', $this->pluginLocale)."</option>";
				foreach($pages as $k=>$v){
					if(in_array($v->ID,$exclude)){
						echo"<option value='' style='color:red'>".$v->post_title." ".__('[not selectable]', $this->pluginLocale)."</option>";
					}else{
						if($options['plugin_data'][$field]==$v->ID){$sel=' selected="selected"';}else{$sel='';}
						echo"<option value='".$v->ID."' ".$sel.">".$v->post_title."</option>";
					}
				}
				echo "</select>";
			}
			if($field=='empty_category_and_items'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  value='1' />";
				echo" ".__('delete images too ?', $this->pluginLocale)."";
				echo "<input id='".$field."_delete_attachments' name='".$this->pluginSlug."[plugin_data][delete_attachments]' type='checkbox'  value='1' />";
				echo" ".__('empty order table ?', $this->pluginLocale)."";
				echo "<input id='".$field."_truncate_orders' name='".$this->pluginSlug."[plugin_data][truncate_orders]' type='checkbox'  value='1' />";
			}
			if($field=='include_css' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_decimals' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='show_currency_with_price'){
				echo "".__('do not show', $this->pluginLocale)."<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],0,false)." value='0' /> ";
				echo "".__('on left', $this->pluginLocale)."<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],1,false)." value='1' />";
				echo "".__('on right', $this->pluginLocale)."<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],2,false)." value='2' />";
			}

			if($field=='currency_symbol_left'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}

			if($field=='items_per_loop' ){
				echo "<input name='".$this->pluginSlug."[layout][".$field."]' size='2' type='text'  value='{$options['layout'][$field]}' />";
			}
			if($field=='style'){
				echo "<select name='".$this->pluginSlug."[layout][".$field."]' />";
					foreach(wppizza_public_styles($options['layout'][$field]) as $k=>$v){
						echo"<option value='".$v['id']."' ".$v['selected'].">".$v['value']."</option>";
					}
				echo "</select>";
			}


			if($field=='add_to_cart_on_title_click' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}

			if($field=='opening_times_format'){
				echo"<span class='wppizza_label'>".__('Hours', $this->pluginLocale)."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][hour]' />";
						echo"<option value='G' ".selected($options[$field]['hour'],"G",false).">".__('24-hour format without leading zeros', $this->pluginLocale)."</option>";
						echo"<option value='g' ".selected($options[$field]['hour'],"g",false).">".__('12-hour format without leading zeros', $this->pluginLocale)."</option>";
						echo"<option value='H' ".selected($options[$field]['hour'],"H",false).">".__('24-hour format with leading zeros', $this->pluginLocale)."</option>";
						echo"<option value='h' ".selected($options[$field]['hour'],"h",false).">".__('12-hour format with leading zeros', $this->pluginLocale)."</option>";
				echo "</select>";
				echo "<br/>";
				echo"<span class='wppizza_label'>".__('Separator', $this->pluginLocale)."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][separator]' />";
						echo"<option value='' ".selected($options[$field]['separator'],"",false).">".__('no seperator', $this->pluginLocale)."</option>";
						echo"<option value='&nbsp;' ".selected($options[$field]['separator'],"&nbsp;",false).">".__('space', $this->pluginLocale)."</option>";
						echo"<option value=':' ".selected($options[$field]['separator'],":",false).">:</option>";
						echo"<option value='.' ".selected($options[$field]['separator'],".",false).">.</option>";
						echo"<option value='-' ".selected($options[$field]['separator'],"-",false).">-</option>";
						echo"<option value=';' ".selected($options[$field]['separator'],";",false).">;</option>";
				echo "</select>";
				echo "<br/>";
				echo"<span class='wppizza_label'>".__('Minutes', $this->pluginLocale)."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][minute]' />";
						echo"<option value='' ".selected($options[$field]['minute'],"",false).">".__('hide minutes', $this->pluginLocale)."</option>";
						echo"<option value='i' ".selected($options[$field]['minute'],"i",false).">".__('show minutes', $this->pluginLocale)."</option>";
				echo "</select>";
				echo "<br/>";
				echo"<span class='wppizza_label'>".__('Show AM/PM ?', $this->pluginLocale)."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][ampm]' />";
						echo"<option value='' ".selected($options[$field]['ampm'],"",false).">".__('do not show', $this->pluginLocale)."</option>";
						echo"<option value='a' ".selected($options[$field]['ampm'],"a",false).">".__('lowercase', $this->pluginLocale)."</option>";
						echo"<option value='A' ".selected($options[$field]['ampm'],"A",false).">".__('UPPERCASE', $this->pluginLocale)."</option>";
						echo"<option value=' a' ".selected($options[$field]['ampm']," a",false).">".__('lowercase (with leading space)', $this->pluginLocale)."</option>";
						echo"<option value=' A' ".selected($options[$field]['ampm']," A",false).">".__('UPPERCASE (width leading space)', $this->pluginLocale)."</option>";
				echo "</select>";
			}


			if($field=='placeholder_img'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_cart_icon'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_item_currency_symbol'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_single_pricetier'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_prices'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='disable_online_order'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='suppress_loop_headers'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='opening_times_standard'){
				echo"<div id='wppizza_".$field."'>";
				foreach(wppizza_days() as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo"<span class='wppizza_weekday'>".$v.":</span> ".__('open from', $this->pluginLocale).":";
					echo "<input name='".$this->pluginSlug."[".$field."][".$k."][open]' size='2' type='text' class='wppizza-time-select' value='{$options[$field][$k]['open']}' />";
					echo"".__('to', $this->pluginLocale).":";
					echo "<input name='".$this->pluginSlug."[".$field."][".$k."][close]' size='2' type='text' class='wppizza-time-select' value='{$options[$field][$k]['close']}' />";
					echo"</span>";
				}
				echo"</div>";
			}

			if($field=='opening_times_custom'){
				echo"<div id='wppizza_".$field."' >";
				echo"<div id='wppizza_".$field."_options'>";
				if(isset($options[$field]['date'])){
				foreach($options[$field]['date'] as $k=>$v){
					echo"".$this->wppizza_admin_section_opening_times_custom($field,$k,$options[$field]);
				}}
				echo"</div>";
				echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', $this->pluginLocale)."</a>";
				echo"</div>";
			}


			if($field=='times_closed_standard'){
				echo"<div id='wppizza_".$field."' >";
				echo"<div id='wppizza_".$field."_options'>";
				if(isset($options[$field]) && is_array($options[$field])){
				foreach($options[$field] as $k=>$v){
					echo"".$this->wppizza_admin_section_times_closed_standard($field,$k,$v);
				}}
				echo"</div>";
				echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', $this->pluginLocale)."</a>";
				echo"</div>";
			}


			if($field=='currency'){
				echo "<select name='".$this->pluginSlug."[order][".$field."]'>";
				foreach(wppizza_currencies($options['order'][$field]) as $l=>$m){
					echo "<option value='".$m['id']."' ".$m['selected'].">[".$m['id']."] - ".$m['value']."</option>";
				}
				echo "</select>";
			}

			if($field=='orderpage'){
				wp_dropdown_pages('name='.$this->pluginSlug.'[order]['.$field.']&selected='.$options['order'][$field].'&show_option_none='.__('select your orderpage', $this->pluginLocale).'');
				echo " ".__('Exclude from Navigation ?', $this->pluginLocale)."<input id='orderpage_exclude' name='".$this->pluginSlug."[order][orderpage_exclude]' type='checkbox'  ". checked($options['order']['orderpage_exclude'],true,false)." value='1' />";
			}

			if($field=='order_form'){
				sort($options[$field]);
				echo"<table>";
					echo"<tr><td>".__('Sort', $this->pluginLocale)."</td><td>".__('Label', $this->pluginLocale)."</td><td>".__('Enabled', $this->pluginLocale)."</td><td>".__('Required', $this->pluginLocale)."</td><td>".__('Type', $this->pluginLocale)."</td></tr>";
				foreach($options[$field] as $k=>$v){
					echo"<tr>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][sort]' size='1' type='text' value='".$v['sort']."' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][lbl]' size='15' type='text' value='".$v['lbl']."' /></td>";

					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][enabled]' type='checkbox' ". checked($v['enabled'],true,false)." value='1' /></td>";

					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][required]' type='checkbox' ". checked($v['required'],true,false)." value='1' /></td>";

					echo "<td>";
						echo "<select id='".$this->pluginSlug."_".$field."_type_".$k."' class='".$this->pluginSlug."_".$field."_type' name='".$this->pluginSlug."[".$field."][".$k."][type]' />";
							echo'<option value="text" '.selected($v['type'],"text",false).'>text</option>';
							echo'<option value="email" '.selected($v['type'],"email",false).'>email</option>';
							echo'<option value="textarea" '.selected($v['type'],"textarea",false).'>textarea</option>';
							echo'<option value="select" '.selected($v['type'],"select",false).'>select</option>';
						echo "</select>";
						if($v['type']!='select'){$display=' style="display:none"';$val='';}else{$display='';$val=''.implode(",",$v['value']).'';}
						echo "<span class='".$this->pluginSlug."_".$field."_select'".$display.">";
							echo "<input name='".$this->pluginSlug."[".$field."][".$k."][value]' type='text' value='".$val."' />";
						echo "</span>";
						echo "<span class='".$this->pluginSlug."_".$field."_select'".$display.">";
							echo "".__('seperate multiple with comma', $this->pluginLocale)."";
						echo "</span>";
					echo"</td>";
					echo"</tr>";
				}
				echo"</table>";
			}


			if($field=='delivery'){
				foreach($options['order'][$field] as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo "<input name='".$this->pluginSlug."[order][delivery_selected]' type='radio' ". checked($options['order']['delivery_selected']==$k,true,false)." value='".$k."' />";
					if($k=='minimum_total'){
						echo"".__('Free delivery when total order value reaches', $this->pluginLocale).":";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][min_total]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field]['minimum_total']['min_total'],$optionsDecimals)."' />";
						echo"<br />";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][deliver_below_total]' type='checkbox' ". checked($v['deliver_below_total'],true,false)." value='1' />";
						echo"".__('Deliver even when total order value is below minimum (the difference between total and "Minimum Total" above will be added to the Total as "Delivery Charges")', $this->pluginLocale)."";
						echo"<br />";
						echo"".__('(If this is not selected and the total order is below the set value above, the customer will not be able to submit the order to you)', $this->pluginLocale)."";
					}
					if($k=='standard'){
						echo"".__('Fixed Delivery Charges [added to order total]', $this->pluginLocale).":";
						echo "<input name='".$this->pluginSlug."[order][".$field."][standard][delivery_charge]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field]['standard']['delivery_charge'],$optionsDecimals)."' />";
					}
					if($k=='per_item'){
						echo"".__('Delivery Charges per item', $this->pluginLocale).":";
						echo "<input name='".$this->pluginSlug."[order][".$field."][per_item][delivery_charge_per_item]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field]['per_item']['delivery_charge_per_item'],$optionsDecimals)."' />";
						echo" ".__('Do not apply delivery charges when total order value reaches ', $this->pluginLocale).":";
						echo"<input name='".$this->pluginSlug."[order][".$field."][per_item][delivery_per_item_free]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field]['per_item']['delivery_per_item_free'],$optionsDecimals)."' />";
						echo" ".__('[set to 0 to always apply charges per item]', $this->pluginLocale)."";
					}
					echo "</span>";
				}
			}
			if($field=='delivery_per_item'){
				echo"<input name='".$this->pluginSlug."[order][".$field."]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field],$optionsDecimals)."' />";
			}
			if($field=='discounts'){
				foreach($options['order'][$field] as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo "<input name='".$this->pluginSlug."[order][discount_selected]' type='radio' ". checked($options['order']['discount_selected']==$k,true,false)." value='".$k."' />";
					if($k=='none'){
						echo"".__('No Discounts', $this->pluginLocale)."";
					}
					if($k=='percentage'){
						echo"".__('Percentage Discount', $this->pluginLocale).":";
						echo"<br />";
						foreach($v['discounts'] as $l=>$m){
							echo"".__('If order total >', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['min_total'],$optionsDecimals)."' />";
							echo"".__('discount', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['discount'],$optionsDecimals)."' />";
							echo"".__('percent', $this->pluginLocale)."";
							echo"<br />";
						}
					}
					if($k=='standard'){
						echo"".__('Standard Discount [money off]', $this->pluginLocale).":";
						echo"<br />";
						foreach($v['discounts'] as $l=>$m){
							echo"".__('If order total >', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['min_total'],$optionsDecimals)."' />";
							echo"".__('get', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['discount'],$optionsDecimals)."' />";
							echo"".__('off', $this->pluginLocale)."";
							echo"<br />";
						}
					}
					echo "</span>";
				}
			}

			if($field=='order_email_to' || $field=='order_email_cc' || $field=='order_email_bcc'){//$field==order_sms => not implemented
				if(is_array($options['order'][$field])){$val=implode(",",$options['order'][$field]);}else{$val='';}
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$val."' />";
			}
			if($field=='sizes'){
				echo"<div id='wppizza_".$field."'>";
				echo"<div id='wppizza_".$field."_options'>";
				foreach($options[$field] as $k=>$v){
					echo"".$this->wppizza_admin_section_sizes($field,$k,$v,$optionInUse);
				}
				echo"</div>";
				echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', $this->pluginLocale)."</a>";
				echo "<input id='wppizza_add_".$field."_fields' size='1' type='text' value='1' />".__('how many size option fields ?', $this->pluginLocale)."";
				echo"</div>";
			}
			if($field=='additives'){
				echo"<div id='wppizza_".$field."'>";
					echo"<div id='wppizza_".$field."_options'>";
					asort($options[$field]);//sort but keep index
					foreach($options[$field] as $k=>$v){
						echo"".$this->wppizza_admin_section_additives($field,$k,$options[$field][$k],$optionInUse);
					}
					echo"</div>";
					echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', $this->pluginLocale)."</a>";
				echo"</div>";
			}
			if($field=='localization'){
				$textArea=array('thank_you_p');/*tinymce textareas*/
				echo"<div id='wppizza_".$field."'>";
					echo"<div id='wppizza_".$field."_options'>";
					asort($options[$field]);
					foreach($options[$field] as $k=>$v){
						if(in_array($k,$textArea)){
							$editorId="".$this->pluginSlug."[".$field."][".$k."]";
							echo"<br/>".$v['descr']."";
							echo"<div style='width:500px;'>";
							wp_editor( $v['lbl'], $editorId,array('teeny'=>1,'wpautop'=>false) );
							echo"<br/></div>";
							echo"<br/>";
						//echo "<textarea name='".$this->pluginSlug."[".$field."][".$k."]' style='width:185px;height:150px'>".$v['lbl']."</textarea>";
						}else{
						echo "<input name='".$this->pluginSlug."[".$field."][".$k."]' size='30' type='text' value='".$v['lbl']."' />";
						echo"".$v['descr']."<br/>";
						}
					}
					echo"</div>";
				echo"</div>";
			}
			if($field=='history'){
				echo"<div id='wppizza_".$field."'>";
					echo "<a href='#' id='".$field."_get_orders'>".__('show most recent orders', $this->pluginLocale)."</a>";
					echo " ".__('maximum results [0 to show all]', $this->pluginLocale)."<input id='".$field."_orders_limit' size='5' type='text' value='20' />";
				echo"</div>";
				echo"<div id='wppizza_".$field."_orders'></div>";
			}
	}
?>