<?php
$options = $this->pluginOptions;
	if($options!=0){

	$optionInUse=wppizza_options_in_use();//outputs an array $arr=array(['sizes']=>array(),['additives']=>array());
	$optionSizes=wppizza_sizes_available($options['sizes']);//outputs an array $arr=array(['lbl']=>array(),['prices']=>array());

			if($field=='version'){
				echo "{$options['plugin_data'][$field]}";
			}
			if($field=='js_in_footer'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
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
			if($field=='placeholder_img'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_cart_icon'){
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

			if($field=='currency'){
				echo "<select name='".$this->pluginSlug."[order][".$field."]'>";
				foreach(wppizza_currencies($options['order'][$field]) as $l=>$m){
					echo "<option value='".$m['id']."' ".$m['selected'].">".$m['value']." - [".$m['id']."]</option>";
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
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][min_total]' size='2' type='text' value='".wppizza_output_format_float($options['order'][$field]['minimum_total']['min_total'],'price')."' />";
						echo"<br />";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][deliver_below_total]' type='checkbox' ". checked($v['deliver_below_total'],true,false)." value='1' />";
						echo"".__('Deliver even when total order value is below minimum (the difference between total and "Minimum Total" above will be added to the Total as "Delivery Charges")', $this->pluginLocale)."";
						echo"<br />";
						echo"".__('(If this is not selected and the total order is below the set value above, the customer will not be able to submit the order to you)', $this->pluginLocale)."";
					}
					if($k=='standard'){
						echo"".__('Fixed Delivery Charges [added to order total]', $this->pluginLocale).":";
						echo "<input name='".$this->pluginSlug."[order][".$field."][standard][delivery_charge]' size='2' type='text' value='".wppizza_output_format_float($options['order'][$field]['standard']['delivery_charge'],'price')."' />";
					}
					echo "</span>";
				}
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
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='2' type='text' value='".wppizza_output_format_float($options['order'][$field][$k]['discounts'][$l]['min_total'],'price')."' />";
							echo"".__('discount', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='2' type='text' value='".wppizza_output_format_float($options['order'][$field][$k]['discounts'][$l]['discount'],'price')."' />";
							echo"".__('percent', $this->pluginLocale)."";
							echo"<br />";
						}
					}
					if($k=='standard'){
						echo"".__('Standard Discount [money off]', $this->pluginLocale).":";
						echo"<br />";
						foreach($v['discounts'] as $l=>$m){
							echo"".__('If order total >', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='2' type='text' value='".wppizza_output_format_float($options['order'][$field][$k]['discounts'][$l]['min_total'],'price')."' />";
							echo"".__('get', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='2' type='text' value='".wppizza_output_format_float($options['order'][$field][$k]['discounts'][$l]['discount'],'price')."' />";
							echo"".__('off', $this->pluginLocale)."";
							echo"<br />";
						}
					}
					echo "</span>";
				}
			}

			if($field=='order_email_to' || $field=='order_email_cc' || $field=='order_email_bcc'){//$field==order_sms => not implemented
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".implode(",",$options['order'][$field])."' />";
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
				echo"<div id='wppizza_".$field."'>";
					echo"<div id='wppizza_".$field."_options'>";
					asort($options[$field]);
					foreach($options[$field] as $k=>$v){
						echo "<input name='".$this->pluginSlug."[".$field."][".$k."]' size='30' type='text' value='".$v['lbl']."' />";
						echo"".$v['descr']."<br/>";
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