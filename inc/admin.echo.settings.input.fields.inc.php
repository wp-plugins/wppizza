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
				echo" <input id='".$field."_delete_attachments' name='".$this->pluginSlug."[plugin_data][delete_attachments]' type='checkbox'  value='1' />";
				echo" ".__('empty order table ?', $this->pluginLocale)."";
				echo" <input id='".$field."_truncate_orders' name='".$this->pluginSlug."[plugin_data][truncate_orders]' type='checkbox'  value='1' />";
			}
			if($field=='include_css' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
				echo "<input name='".$this->pluginSlug."[layout][css_priority]' size='2' type='text'  value='{$options['layout']['css_priority']}' />";
				echo "".__('Stylesheet Priority', $this->pluginLocale)."";
				echo "<br/>".__('By default, the stylesheet will be loaded AFTER the main theme stylesheet (which should have a priority of "10"). If you experience strange behaviour or layout issues (in conjunction with other plugins for example), you can try adjusting this priority here (the bigger the number, the later it gets loaded).', $this->pluginLocale)."";

			}
			if($field=='hide_decimals' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='show_currency_with_price'){
				echo "".__('do not show', $this->pluginLocale)." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],0,false)." value='0' /> ";
				echo "".__('on left', $this->pluginLocale)." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],1,false)." value='1' />";
				echo "".__('on right', $this->pluginLocale)." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],2,false)." value='2' />";
			}

			if($field=='currency_symbol_left'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='cart_increase'){
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
			if($field=='prettyPhoto' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='prettyPhotoStyle'){
				echo "<select name='".$this->pluginSlug."[layout][".$field."]'>";
					echo "<option value='pp_default' ".selected($options['layout'][$field],"pp_default",false).">default</option>";
					echo "<option value='light_rounded' ".selected($options['layout'][$field],"light_rounded",false).">light rounded</option>";
					echo "<option value='dark_rounded' ".selected($options['layout'][$field],"dark_rounded",false).">dark rounded</option>";
					echo "<option value='light_square' ".selected($options['layout'][$field],"light_square",false).">light square</option>";
					echo "<option value='dark_square' ".selected($options['layout'][$field],"dark_square",false).">dark square</option>";
					echo "<option value='facebook' ".selected($options['layout'][$field],"facebook",false).">facebook</option>";
				echo "</select>";
				echo' '.__('see wppizza.prettyPhoto.custom.js.php if you want to adjust prettyPhoto options', $this->pluginLocale).'';
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
			if($field=='empty_cart_button'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='opening_times_standard'){
				echo"<div id='wppizza_".$field."'>";
				foreach(wppizza_days() as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo"<span class='wppizza_weekday'>".$v.":</span> ".__('open from', $this->pluginLocale).":";
					echo "<input name='".$this->pluginSlug."[".$field."][".$k."][open]' size='3' type='text' class='wppizza-time-select' value='{$options[$field][$k]['open']}' />";
					echo"".__('to', $this->pluginLocale).":";
					echo "<input name='".$this->pluginSlug."[".$field."][".$k."][close]' size='3' type='text' class='wppizza-time-select' value='{$options[$field][$k]['close']}' />";
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
				echo " ".__('Exclude from Navigation ?', $this->pluginLocale)." <input id='orderpage_exclude' name='".$this->pluginSlug."[order][orderpage_exclude]' type='checkbox'  ". checked($options['order']['orderpage_exclude'],true,false)." value='1' />";
			}

			if($field=='order_form'){

				asort($options[$field]);

				echo"<table id='wppizza_".$field."'>";
					echo"<tr><th>".__('Sort', $this->pluginLocale)."</th><th>".__('Label', $this->pluginLocale)."</th><th>".__('Enabled', $this->pluginLocale)."</th><th>".__('Required', $this->pluginLocale)."</th><th>".__('Prefill<br/>[if known]', $this->pluginLocale)."</th><th>".__('Use when<br/>Registering ?', $this->pluginLocale)."</th><th>".__('Type', $this->pluginLocale)."</th></tr>";
				foreach($options[$field] as $k=>$v){
					$disableRegister=false;$disablePrefill=false;$fixedType='';$fixedTypeLabel='';

					if($v['key']=='cemail'){$disableRegister=true;$fixedType='email';$fixedTypeLabel='email';}
					if($v['key']=='ctips'){$disableRegister=true;$disablePrefill=true;$fixedType='tips';$fixedTypeLabel='';}
					if($v['key']=='csurcharges'){$disableRegister=true;$disablePrefill=true;$fixedType='selectcustom';}


					if($v['key']=='cemail'){$style=' style="margin-bottom:0"';}else{$style='';}
					echo"<tr class='".$v['key']."'>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][sort]' size='1' type='text' value='".$v['sort']."' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][lbl]' size='15' type='text' value='".$v['lbl']."' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][enabled]' type='checkbox' ". checked($v['enabled'],true,false)." value='1' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][required]' type='checkbox' ". checked($v['required'],true,false)." value='1' /></td>";
					echo"<td>";
						if(!$disablePrefill){echo"<input name='".$this->pluginSlug."[".$field."][".$k."][prefill]' type='checkbox' ". checked($v['prefill'],true,false)." value='1' />";}else{echo"".__('N/A', $this->pluginLocale)."";}
					echo"</td>";
					echo"<td>";
						if(!$disableRegister){echo"<input name='".$this->pluginSlug."[".$field."][".$k."][onregister]' type='checkbox' ". checked($v['onregister'],true,false)." value='1' />";}else{echo"".__('N/A', $this->pluginLocale)."";}
					echo"</td>";

					echo "<td>";

						if($fixedType!=''){
							echo "<input type='hidden' id='".$this->pluginSlug."_".$field."_type_".$k."' name='".$this->pluginSlug."[".$field."][".$k."][type]' value='".$fixedType."' />";
							echo "".$fixedTypeLabel."";
						}else{
							echo "<select id='".$this->pluginSlug."_".$field."_type_".$k."' class='".$this->pluginSlug."_".$field."_type' name='".$this->pluginSlug."[".$field."][".$k."][type]' />";
								echo'<option value="text" '.selected($v['type'],"text",false).'>text</option>';
								echo'<option value="email" '.selected($v['type'],"email",false).'>email</option>';
								echo'<option value="textarea" '.selected($v['type'],"textarea",false).'>textarea</option>';
								echo'<option value="select" '.selected($v['type'],"select",false).'>select</option>';
							echo "</select>";
						}

						$display=' style="display:none"';$val='';

						if($v['type']=='select'){$display='';$val=''.implode(",",$v['value']).'';}
						if($v['type']=='selectcustom'){$display='';
							$valArr=array();
							foreach($v['value'] as $vKey=>$vVal){
								$valArr[]=''.$vKey.':'.$vVal.'';
							}
							$val=implode("|",$valArr);
						}


						echo "<span class='".$this->pluginSlug."_".$field."_select'".$display.">";
							echo "<input name='".$this->pluginSlug."[".$field."][".$k."][value]' type='text' value='".$val."' />";
						echo "</span>";
						echo "<span class='".$this->pluginSlug."_".$field."_select'".$display.">";
							if($v['type']!='selectcustom'){echo "".__('separate multiple with comma', $this->pluginLocale)."";}
							if($v['type']=='selectcustom'){echo "".__('enter required value pairs', $this->pluginLocale)."";}
						echo "</span>";
					echo"</td>";// ".$v['key']." ".$v['type']."
					echo"</tr>";

					if($v['key']=='cemail'){
						echo"<tr class='".$v['key']."'><td colspan='7' style='margin:0;padding:0 0 0 10px'>";
						echo"<span class='description'>".__('<b>Note:</b> only this field can and should be used to send email notifications of the order to the customer (if enabled).<br/>Furthermore, you cannot select this field to show on the registration form. (Wordpress already adds this field)', $this->pluginLocale)."</span>";
						echo"</td></tr>";
					}
					if($v['key']=='ctips'){



						echo"<tr class='".$v['key']."'><td colspan='7' style='margin:0;padding:0 0 0 10px'>";
						echo"<span class='description'>";
						echo"".__('<b>Tips/Gratuities:</b> allow the customer can enter a <b>numerical</b> amount to be used as tips/gratuities.<br/>This field will not be added to the users profile and can therefore not be pre-filled or used in the registration form.', $this->pluginLocale)."";
						/**the following notice can probably be removed in a few months**/
						if (class_exists( 'WPPIZZA_GATEWAY_PAYPAL') ) {
							$pluginPath=dirname(dirname(plugin_dir_path( __FILE__ )));
							$gwPaypalData=get_plugin_data($pluginPath.'/wppizza-gateway-paypal/wppizza-gateway-paypal.php', false, false );
							if( version_compare( $gwPaypalData['Version'], '2.1' , '<' )) {
								echo"<br/><span style='color:red'>If you want to enable this field you MUST update to Wppizza Paypal Gateway 2.1+.<br/>If your version of the paypal gateway is < 2.0 <a href='mailto:dev@wp-pizza.com'> contact me</a> with your purchase id for an update.<br/>If your version is >= 2.0 you should be able to update via your dashboard (provided you activated your license).<br/>This notice will disappear as soon as you have updated the Paypal Gateway. </span>";
							}
						}
						/*********************end of notice******************************/
						echo"</span>";
						echo"</td></tr>";
					}


					/***********************
					//currently not in use
					if($v['key']=='csurcharges'){
						echo"<tr class='".$v['key']."'><td colspan='7' style='margin:0;padding:0 0 0 10px'>";
						echo"<span class='description'>".__('<b>Surcharges:</b> generate a dropdown of applicable surcharges as comma/pipe delimited label/price pairs (% allowed).<br />Example: "CC:1.00|COD:0.75%|Other:5" etc. <b>Any value selected by the customer will be added to the delivery charges.</b><br/>This field will not be added to the users profile and can therefore not be pre-filled or used in the registration form.', $this->pluginLocale)."</span>";
						echo"</td></tr>";
					}
					********************/

				}
				//future use.....maybe
				//echo"<tr><th colspan='7' class='wppizza-order-form-footer' ><span id='wppizza-toggle-tgs' class='description'>".__('Show Tips/Gratuities', $this->pluginLocale)."<span></th></tr>";
				echo"</table>";
			}


			if($field=='delivery'){
				/****sort in a more sensible manner**/
				$options['order'][$field]=array('no_delivery'=>$options['order'][$field]['no_delivery'],'minimum_total'=>$options['order'][$field]['minimum_total'],'standard'=>$options['order'][$field]['standard'],'per_item'=>$options['order'][$field]['per_item']);
				/**end custom sort**/

				echo "<span id='wppizza-delivery-options-select'>";
				foreach($options['order'][$field] as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo "<input name='".$this->pluginSlug."[order][delivery_selected]' type='radio' ". checked($options['order']['delivery_selected']==$k,true,false)." value='".$k."' />";

					if($k=='no_delivery'){
						echo" ".__('No delivery offered / pickup only', $this->pluginLocale)."";
						echo"<br /><span class='description'>".__('removes any labels, text, charges, checkboxes etc associated with delivery options. You can still set a minimum order value below.', $this->pluginLocale)."</span>";

					}
					if($k=='minimum_total'){
						echo" ".__('Free delivery when total order value reaches', $this->pluginLocale).":";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][min_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['minimum_total']['min_total'],$optionsDecimals)."' />";
						echo"<div style='margin-left:20px'>";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][deliver_below_total]' type='checkbox' ". checked($v['deliver_below_total'],true,false)." value='1' />";
						echo" ".__('Deliver even when total order value is below minimum (the difference between total and "Minimum Total" above will be added to the Total as "Delivery Charges")', $this->pluginLocale)."";
						echo"<br />";
						echo"<span class='description'>".__('(If this is not selected and the total order is below the set value above, the customer will not be able to submit the order to you)', $this->pluginLocale)."</span>";
						echo"<br />";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][deliverycharges_below_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['minimum_total']['deliverycharges_below_total'],$optionsDecimals)."' />";
						echo" ".__('Fixed Delivery charges if order has not reached total for free delivery [0 to disable]', $this->pluginLocale)."";
						echo"<br />";
						echo" <em style='color:red'>(".__('if set (i.e. not 0) "Deliver even when total order value is below minimum" must be checked for this to have any effect', $this->pluginLocale).")</em>";

						echo"</div>";
					}
					if($k=='standard'){
						echo" ".__('Fixed Delivery Charges [added to order total]', $this->pluginLocale).":";
						echo "<input name='".$this->pluginSlug."[order][".$field."][standard][delivery_charge]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['standard']['delivery_charge'],$optionsDecimals)."' />";

					}
					if($k=='per_item'){
						echo" ".__('Delivery Charges per item', $this->pluginLocale).":";
						echo"<div style='margin-left:20px'>";
						echo "<input name='".$this->pluginSlug."[order][".$field."][per_item][delivery_charge_per_item]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['per_item']['delivery_charge_per_item'],$optionsDecimals)."' />";
						echo" ".__('Do not apply delivery charges when total order value reaches ', $this->pluginLocale).":";
						echo"<input name='".$this->pluginSlug."[order][".$field."][per_item][delivery_per_item_free]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['per_item']['delivery_per_item_free'],$optionsDecimals)."' />";
						echo" ".__('[set to 0 to always apply charges per item]', $this->pluginLocale)."";
						echo"</div>";
					}
					echo "</span>";
				}
				echo "</span>";

				/**min order for delivery**/
				echo "<span class='wppizza_option' style='margin-top:20px'>";
				echo"<input name='".$this->pluginSlug."[order][order_min_for_delivery]' size='3' type='text' value='".wppizza_output_format_price($options['order']['order_min_for_delivery'],$optionsDecimals)."' />";
				echo" ".__('minimum order value [will disable "place order" button in cart and order page until set order value (before any discounts etc) has been reached. 0 to disable.]', $this->pluginLocale)."<br />";
				echo" <span class='description'>".__('Customer can still choose "self-pickup" (if enabled / applicable).', $this->pluginLocale)."</span>";
				echo "</span>";

				/**Exclude following menu items when calculating if free delivery**/
				echo "<span class='wppizza_option' style='margin:20px 0'>";
					echo" ".__('<b>Exclude</b> following menu items when calculating if free delivery applies', $this->pluginLocale)." :<br/>";
					echo'<span class="description">'.__('For example: you might want to offer free delivery only when total order of *meals* exceeds the set free delivery amount. In this case, exclude all your *drinks and non-meals* by selecting those below.', $this->pluginLocale).'</span><br/>';
					echo"<select name='".$this->pluginSlug."[order][delivery_calculation_exclude_item][]' multiple='multiple' class='wppizza_delivery_calculation_exclude_item'>";
					$args = array('post_type' => ''.WPPIZZA_POST_TYPE.'','posts_per_page' => -1, 'orderby'=>'title' ,'order' => 'ASC');
					$query = new WP_Query( $args );
					foreach($query->posts as $pKey=>$pVal){
						echo"<option value='".$pVal->ID."' ";
							if(isset($options['order']['delivery_calculation_exclude_item']) && in_array($pVal->ID,$options['order']['delivery_calculation_exclude_item'])){
								echo" selected='selected'";
							}
						echo">".$pVal->post_title."</option>";
					}
					echo"</select>";
					echo'<br/>'.__('Ctrl+Click to select multiple', $this->pluginLocale).'';
				echo "</span>";

			}
			/**I don't think this actually in use anywhere ?!**/
			if($field=='delivery_per_item'){
				echo"<input name='".$this->pluginSlug."[order][".$field."]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field],$optionsDecimals)."' />";
			}

			if($field=='order_pickup'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='checkbox'  ". checked($options['order'][$field],true,false)." value='1' /> ".__('tick to enable', $this->pluginLocale)."";

				echo "<br/>".__('Discount for self-pickup ?', $this->pluginLocale)." <input id='order_pickup_discount' name='".$this->pluginSlug."[order][order_pickup_discount]' size='3' type='text' value='".wppizza_output_format_price($options['order']['order_pickup_discount'])."' /> ".__('in % - 0 to disable', $this->pluginLocale)."";

				echo "<br/><input id='order_pickup_alert' name='".$this->pluginSlug."[order][order_pickup_alert]' type='checkbox'  ". checked($options['order']['order_pickup_alert'],true,false)." value='1' /> ".__('enable javascript alert when user selects self pickup (set corresponding text in localization)', $this->pluginLocale)."";
			}
			if($field=='order_pickup_display_location'){
				echo "".__('under cart only', $this->pluginLocale)."<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='radio'  ".checked($options['order'][$field],1,false)." value='1' /> ";
				echo "".__('on order page only', $this->pluginLocale)."<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='radio'  ".checked($options['order'][$field],2,false)." value='2' /> ";
				echo "".__('both', $this->pluginLocale)."<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='radio'  ".checked($options['order'][$field],3,false)." value='3' />";
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
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['min_total'],$optionsDecimals)."' />";
							echo"".__('discount', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['discount'],$optionsDecimals)."' />";
							echo"".__('percent', $this->pluginLocale)."";
							echo"<br />";
						}
					}
					if($k=='standard'){
						echo"".__('Standard Discount [money off]', $this->pluginLocale).":";
						echo"<br />";
						foreach($v['discounts'] as $l=>$m){
							echo"".__('If order total >', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['min_total'],$optionsDecimals)."' />";
							echo"".__('get', $this->pluginLocale).":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['discount'],$optionsDecimals)."' />";
							echo"".__('off', $this->pluginLocale)."";
							echo"<br />";
						}
					}
					echo "</span>";
				}
			}

			if($field=='order_email_to' || $field=='order_email_cc' || $field=='order_email_bcc' ){//$field==order_sms => not implemented
				if(is_array($options['order'][$field])){$val=implode(",",$options['order'][$field]);}else{$val='';}
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$val."' />";
			}
			if($field=='order_email_attachments'){
				if(is_array($options['order'][$field])){$val=implode(",",$options['order'][$field]);}else{$val='';}
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$val."' placeholder='/absolute/path/to/your/file'/>";
				echo" <span class='description'>".__('if you wish to add an attachment to the order emails add the FULL ABSOLUTE PATH to the file(s) here', $this->pluginLocale)."</span>";
			}

			if($field=='order_email_from'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$options['order'][$field]."' />";
			}
			if($field=='order_email_from_name'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$options['order'][$field]."' />";
			}



			if($field=='item_tax'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='2' type='text' value='".wppizza_output_format_price($options['order'][$field])."' />%";
				echo"<br/>";
				echo"<input name='".$this->pluginSlug."[order][shipping_tax]' type='checkbox'  ". checked($options['order']['shipping_tax'],true,false)." value='1' />";
				echo" ".__('apply tax to delivery/shipping  too', $this->pluginLocale)."";
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
					if(isset($options[$field]) && is_array($options[$field])){
					asort($options[$field]);//sort but keep index
					foreach($options[$field] as $k=>$v){
						echo"".$this->wppizza_admin_section_additives($field,$k,$options[$field][$k],$optionInUse);
					}}
					echo"</div>";
					echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', $this->pluginLocale)."</a>";
				echo"</div>";
			}

			if($field=='gateways'){
				echo"<div id='wppizza_".$field."'>";

				echo"<div id='wppizza_".$field."_options'>";
					echo"<div>";
						echo"<input name='".$this->pluginSlug."[gateways][gateway_select_as_dropdown]' type='checkbox'  ". checked($options['gateways']['gateway_select_as_dropdown'],true,false)." value='1' />";
						echo" <b>".__('Display Gateway choices as dropdowns instead of buttons', $this->pluginLocale)."</b> ".__('[only applicable if more than one gateway installed, activated and enabled]', $this->pluginLocale)."";
					echo"</div>";

					echo"<div>";
						echo"<b>".__('Label:', $this->pluginLocale)."</b> ";
						echo"<input name='".$this->pluginSlug."[gateways][gateway_select_label]' type='text' size='50' value='". $options['gateways']['gateway_select_label']."' />";
						echo"<br/>".__('by default displayed above if choices are displayed as full width buttons, next to if dropdown. edit css as required', $this->pluginLocale)." ".__('[only applicable if more than one gateway installed, activated and enabled]', $this->pluginLocale)."";
					echo"</div>";

					echo"<div>";
						echo"<input name='".$this->pluginSlug."[gateways][gateway_showorder_on_thankyou]' type='checkbox'  ". checked($options['gateways']['gateway_showorder_on_thankyou'],true,false)." value='1' />";
						echo" <b>".__('Show Order Details on "Thank You" page (Y/N)', $this->pluginLocale)."</b> ".__('Will add any order details after your thank you text on successful order', $this->pluginLocale)."";
					echo"</div>";




						$this->wppizza_admin_section_gateways($field,$options[$field]);
					echo"</div>";
				echo"</div>";
			}
			if($field=='localization'){
				/**to get descriptions include default options. do not use require_once, as we need this more than once**/
				require(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');

				/**add description to array**/
				$localizeOptions=array();
				foreach($defaultOptions['localization'] as $k=>$v){
					$localizeOptions[$k]['descr']=$v['descr'];
					$localizeOptions[$k]['lbl']=$options[$field][$k]['lbl'];
				}

				$textArea=array('thank_you_p');/*tinymce textareas*/
				echo"<div id='wppizza_".$field."'>";
					echo"<div id='wppizza_".$field."_options'>";
					asort($localizeOptions);
					$lngOddEvenArray=__('0,5,11,12,15,29,36', $this->pluginLocale);
					$lngOddEvan=explode(",",$lngOddEvenArray);
					$bgStyle=$lngOddEvan;
					$i=0;
					foreach($localizeOptions as $k=>$v){
					if(in_array($i,$bgStyle)){echo'<div>';}
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
					$i++;
					if(in_array($i,$bgStyle)){echo'</div>';}
					}
					echo"</div>";
				echo"</div>";
			}


			if($field=='access'){
				global $current_user,$user_level,$wp_roles;
				echo"<div id='wppizza_".$field."'>";
					echo"<b>".__('Set the roles that are allowed to access these pages', $this->pluginLocale)."</b>";
					echo"<br />".__('Menu Items and Categories are accessible just like "normal" posts', $this->pluginLocale)."";
					$roles=get_editable_roles();/*only get roles user is allowed to edit**/

					/*do not display current users role (otherwise he can screw his own access) or levels higher than current*/
					if(is_array($current_user->roles)){
					foreach($current_user->roles as $curRoles){
						if(isset($roles[$curRoles])){
							unset($roles[$curRoles]);
						}
					}}

					$access=$this->wppizza_set_capabilities();
					foreach($roles as $roleName=>$v){

						$userRole = get_role($roleName);
							echo"<div class='wppizza-access'>";
							echo"<input type='hidden' name='".$this->pluginSlug."[admin_access_caps][".$roleName."]' value='".$roleName."'>";
							echo"<ul>";
							print"<li style='width:150px'><b>".$roleName.":</b></li>";
								foreach($access as $aKey=>$aArray){
									echo"<li><input name='".$this->pluginSlug."[admin_access_caps][".$roleName."][".$aArray['cap']."]' type='checkbox'  ". checked(isset($userRole->capabilities[$aArray['cap']]),true,false)." value='".$aArray['cap']."' /> ".$aArray['name']."<br/></li>";//". checked($options['plugin_data']['access_level'],true,false)."
								}
							echo"</ul>";
							echo"</div>";
					}
				echo"</div>";
			}


			if($field=='history'){
				echo"<div id='wppizza_".$field."'>";

					echo"<div id='wppizza_".$field."_search' class='button'>";
						echo "<span style='float:left'>";
						echo "<a href='#' id='".$field."_get_orders' class='button'>".__('show most recent *confirmed* orders', $this->pluginLocale)."</a>";
						echo " ".__('maximum results [0 to show all]', $this->pluginLocale)."<input id='".$field."_orders_limit' size='3' type='text' value='20' />";
						echo "</span>";
						echo "<span style='float:right;margin-right:50px'>";
						echo " ".__('poll for new orders every', $this->pluginLocale)."<input id='".$field."_orders_poll_interval' size='2' type='text' value='30' />".__('seconds', $this->pluginLocale)."";
						echo "<label class='button'><input id='".$field."_orders_poll_enabled' type='checkbox' value='1' />".__('on/off', $this->pluginLocale)."</span>";
								echo "<span id='wppizza-orders-polling'></span>";/*shows loading icon*/
						echo "</label>";
					echo"</div>";
					echo"<div id='wppizza_".$field."_orders'></div>";

				echo"</div>";

			}
			if($field=='tools'){
				echo"<div id='wppizza_".$field."'>";
					echo"<div id='wppizza_".$field."_clear'>";
						echo" <b>".__('Delete abandoned/cancelled orders from database older than', $this->pluginLocale)."</b> ";
						echo"<input id='wppizza_order_days_delete' type='text' size='2' value='7' />";
						echo" <b>".__('Days (minimum: 1)', $this->pluginLocale)."</b> ";
						echo"<input id='wppizza_order_failed_delete' type='checkbox' value='1' />";
						echo" <b>".__('delete failed, tampered or otherwise invalid entries too.', $this->pluginLocale)."</b> ";
						echo"<br/><span id='wppizza_order_abandoned_delete' class='button'>".__('go', $this->pluginLocale)."</span>";
						echo"<br/>".__('As soon as customers go to the order page an order will be initialized and stored in the db to be checked against when going through with the purchase to make sure nothing has been tampered with. However, not every customer will actually go through with the purchase which leaves this initialised order orphaned in the db.Click the "ok" button to clean your db of these entries (it will NOT affect any completed or pending orders)', $this->pluginLocale)."";
						echo"<br/><span style='color:red'>".__('Note: This will delete these entries PERMANENTLY from the db and is not reversable.', $this->pluginLocale)."</style>";
					echo"</div>";
				echo"</div>";
			}

	}
?>