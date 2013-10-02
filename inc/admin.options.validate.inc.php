<?php
	/**get previously saved options**/
	$options = $this->pluginOptions;


		/**lets not forget static, uneditable options **/
		$options['plugin_data']['version'] = $this->pluginVersion;
		$options['plugin_data']['nag_notice'] = isset($input['plugin_data']['nag_notice']) ? $input['plugin_data']['nag_notice'] : $options['plugin_data']['nag_notice'];
		$options['plugin_data']['empty_category_and_items'] = false;/*we dont really want to save these settings, just execute when true*/


		if(isset($input['plugin_data']['empty_category_and_items']) && $input['plugin_data']['empty_category_and_items']==1){
			$this->wppizza_empty_taxonomy(!empty($input['plugin_data']['delete_attachments']) ? true : false, !empty($input['plugin_data']['truncate_orders']) ? true : false);
		}

		/**maybe in the future**/
//		if(isset($input['plugin_data']['install_sample_data'])){
			//include(WPPIZZA_PATH .'inc/admin.sample.data.inc.php');
			//$this->wppizza_empty_taxonomy(!empty($input['plugin_data']['delete_attachments']) ? true : false, !empty($input['plugin_data']['truncate_orders']) ? true : false);
//		}

		/**validate global settings***/
		if(isset($_POST[''.$this->pluginSlug.'_global'])){
			/**submitted options -> validate***/
			$options['plugin_data']['js_in_footer'] = !empty($input['plugin_data']['js_in_footer']) ? true : false;
			$options['plugin_data']['wp_multisite_session_per_site'] = !empty($input['plugin_data']['wp_multisite_session_per_site']) ? true : false;
			$options['plugin_data']['mail_type'] = wppizza_validate_alpha_only($input['plugin_data']['mail_type']);
		}

		/**sets custom menu as child of a parent page**/
		if(isset($input['plugin_data']['category_parent_page'])){
			$options['plugin_data']['category_parent_page'] = !empty($input['plugin_data']['category_parent_page']) ? (int)$input['plugin_data']['category_parent_page'] : '';
			//$options['plugin_data']['category_parent_page'] = wppizza_validate_alpha_only($input['plugin_data']['category_parent_page']);


		}
		if(isset($input['layout']['category_sort'])){
			$options['layout']['category_sort']=$input['layout']['category_sort'];
		}
		/*set number of items per loop. must be >= get_option('posts_per_page ')*/
		if(isset($input['layout']['items_per_loop'])){
			/*if minus=>set to -1**/
			if(substr($input['layout']['items_per_loop'],0,1)=='-'){
				$set='-1';
			}else{/*else mk int**/
				if((int)$input['layout']['items_per_loop']>=get_option('posts_per_page ')){
					$set=(int)$input['layout']['items_per_loop'];
				}else{
					$set=get_option('posts_per_page ');
				}
			}

			$options['layout']['items_per_loop']=$set;
		}


		if(isset($_POST[''.$this->pluginSlug.'_layout'])){
			$options['layout']['include_css'] = !empty($input['layout']['include_css']) ? true : false;
			$options['layout']['css_priority'] = wppizza_validate_int_only($input['layout']['css_priority']);
			$options['layout']['hide_decimals'] = !empty($input['layout']['hide_decimals']) ? true : false;
			$options['layout']['style'] = wppizza_validate_alpha_only($input['layout']['style']);
			$options['layout']['placeholder_img'] = !empty($input['layout']['placeholder_img']) ? true : false;
			$options['layout']['suppress_loop_headers'] = !empty($input['layout']['suppress_loop_headers']) ? true : false;
			$options['layout']['hide_cart_icon'] = !empty($input['layout']['hide_cart_icon']) ? true : false;
			$options['layout']['hide_item_currency_symbol'] = !empty($input['layout']['hide_item_currency_symbol']) ? true : false;
			$options['layout']['hide_single_pricetier'] = !empty($input['layout']['hide_single_pricetier']) ? true : false;
			$options['layout']['hide_prices'] = !empty($input['layout']['hide_prices']) ? true : false;
			$options['layout']['disable_online_order'] = !empty($input['layout']['disable_online_order']) ? true : false;
			$options['layout']['add_to_cart_on_title_click'] = !empty($input['layout']['add_to_cart_on_title_click']) ? true : false;
			$options['layout']['currency_symbol_left'] = !empty($input['layout']['currency_symbol_left']) ? true : false;
			$options['layout']['show_currency_with_price'] = wppizza_validate_int_only($input['layout']['show_currency_with_price']);
			$options['layout']['cart_increase'] = !empty($input['layout']['cart_increase']) ? true : false;

			$options['opening_times_format']['hour']=wppizza_validate_string($input['opening_times_format']['hour']);
			$options['opening_times_format']['separator']=wppizza_validate_string($input['opening_times_format']['separator']);
			$options['opening_times_format']['minute']=wppizza_validate_string($input['opening_times_format']['minute']);
			$options['opening_times_format']['ampm']=wppizza_validate_string($input['opening_times_format']['ampm']);

		}
		/**validate opening_times settings***/
		if(isset($_POST[''.$this->pluginSlug.'_opening_times'])){
			$options['opening_times_standard'] = array();//initialize array
			ksort($input['opening_times_standard']);//just for consistency. not really necessary though
			foreach($input['opening_times_standard'] as $k=>$v){
				foreach($v as $l=>$m){
				$options['opening_times_standard'][$k][$l]=wppizza_validate_24hourtime($m);
				}
			}

			$options['opening_times_custom'] = array();//initialize array
			if(isset($input['opening_times_custom'])){
			foreach($input['opening_times_custom'] as $k=>$v){
				foreach($v as $l=>$m){
					if($k=='date'){
						$options['opening_times_custom'][$k][$l]=wppizza_validate_date($m,'Y-m-d');
					}else{
						$options['opening_times_custom'][$k][$l]=wppizza_validate_24hourtime($m);
					}
				}
			}}

			$options['times_closed_standard'] = array();//initialize array
			if(isset($input['times_closed_standard'])){
				foreach($input['times_closed_standard'] as $k=>$v){
					foreach($v as $l=>$m){
						if($k=='day'){
							$options['times_closed_standard'][$l][$k]=(int)$m;
						}else{
							$options['times_closed_standard'][$l][$k]=wppizza_validate_24hourtime($m);
						}
					}
				}
			}
		}
		/**validate order settings***/
		if(isset($_POST[''.$this->pluginSlug.'_order'])){
			$options['order'] = array();//initialize array
			$options['order']['currency'] = strtoupper(wppizza_validate_letters_only($input['order']['currency'],3));//validation a bit overkill, but then again, why not
				$displayCurrency=wppizza_currencies($input['order']['currency'],true);
			$options['order']['currency_symbol'] = $displayCurrency['val'];
			$options['order']['orderpage'] = !empty($input['order']['orderpage']) ? (int)$input['order']['orderpage'] : false;
			$options['order']['orderpage_exclude']=!empty($input['order']['orderpage_exclude']) ? true : false;
			$options['order']['order_pickup']=!empty($input['order']['order_pickup']) ? true : false;
			$options['order']['order_pickup_alert']=!empty($input['order']['order_pickup_alert']) ? true : false;
			$options['order']['order_pickup_discount']=wppizza_validate_float_only($input['order']['order_pickup_discount']);

			$options['order']['order_pickup_display_location'] = wppizza_validate_int_only($input['order']['order_pickup_display_location']);

			$options['order']['delivery_selected'] = wppizza_validate_alpha_only($input['order']['delivery_selected']);
			$options['order']['discount_selected'] = wppizza_validate_alpha_only($input['order']['discount_selected']);
			$options['order']['delivery'] = array();
			foreach($input['order']['delivery'] as $k=>$v){
				foreach($v as $l=>$m){
					if($l!='deliver_below_total'){
						$options['order']['delivery'][$k][$l]=wppizza_validate_float_only($m,2);
					}
				}
				if($k=='minimum_total'){
					$options['order']['delivery'][$k]['deliver_below_total']=!empty($input['order']['delivery'][$k]['deliver_below_total']) ? true : false;
				}
			}
			$options['order']['discounts'] = array();//initialize array
			$options['order']['discounts']['none'] = array();//add distinctly as it has no array associated with it
			foreach($input['order']['discounts'] as $a=>$b){
				foreach($b as $c=>$d){
					foreach($d as $e=>$f){
						foreach($f as $g=>$h){
							$options['order']['discounts'][$a][$c][$e][$g]=wppizza_validate_float_only($h,2);
						}
					}
				}
			}

			$options['order']['item_tax']=wppizza_validate_float_only($input['order']['item_tax']);

			$options['order']['order_email_to'] = wppizza_validate_email_array($input['order']['order_email_to']);
			$options['order']['order_email_bcc'] = wppizza_validate_email_array($input['order']['order_email_bcc']);
			$emailFrom=wppizza_validate_email_array($input['order']['order_email_from']);/*validated as array but we only store the first value as string*/
			$options['order']['order_email_from'] = !empty($emailFrom[0]) ? ''.$emailFrom[0].'' : '' ;
			$options['order']['order_email_from_name'] = wppizza_validate_string($input['order']['order_email_from_name']);
		}
		/**validate order form***/
		if(isset($_POST[''.$this->pluginSlug.'_order_form'])){
			foreach($input['order_form'] as $a=>$b){
				$options['order_form'][$a]['sort'] = (int)($input['order_form'][$a]['sort']);
				$options['order_form'][$a]['key'] = ($options['order_form'][$a]['key']);
				$options['order_form'][$a]['lbl'] = wppizza_validate_string($input['order_form'][$a]['lbl']);
				$options['order_form'][$a]['value'] = wppizza_strtoarray($input['order_form'][$a]['value']);
				$options['order_form'][$a]['type'] = wppizza_validate_letters_only($input['order_form'][$a]['type']);
				$options['order_form'][$a]['enabled'] = !empty($input['order_form'][$a]['enabled']) ? true : false;
				$options['order_form'][$a]['required'] = !empty($input['order_form'][$a]['required']) ? true : false;
			}
		}
		/**validate sizes settings***/
		if(isset($_POST[''.$this->pluginSlug.'_sizes'])){
			$options['sizes'] = array();//initialize array
			if(isset($input['sizes'])){
			foreach($input['sizes'] as $a=>$b){
				foreach($b as $c=>$d){
					$options['sizes'][$a][$c]['lbl']=wppizza_validate_string($d['lbl']);
					$options['sizes'][$a][$c]['price']=wppizza_validate_float_only($d['price'],2);
				}
			}}
		}
		/**validate additives ***/
		if(isset($_POST[''.$this->pluginSlug.'_additives'])){
			$options['additives'] = array();//initialize array
			if(isset($input['additives'])){
			foreach($input['additives'] as $a=>$b){
				$options['additives'][$a]=wppizza_validate_string($b);
			}}
		}
		/**validate localization ***/
		if(isset($_POST[''.$this->pluginSlug.'_localization'])){
			if(isset($input['localization'])){
			$allowHtml=array('thank_you_p');/*array of items to allow html (such as tinymce textareas) */
			foreach($input['localization'] as $a=>$b){
				/*add new value , but keep desciption (as its not editable on frontend)*/
				if(in_array($a,$allowHtml)){$html=1;}else{$html=false;}
				$options['localization'][$a]=array('lbl'=>wppizza_validate_string($b,$html));
			}}
		}

		/**update gateways**/
		if(isset($_POST[''.$this->pluginSlug.'_gateways'])){

			$options['gateways']['gateway_select_as_dropdown']=!empty($input['gateways']['gateway_select_as_dropdown'])? true : false;
			$options['gateways']['gateway_showorder_on_thankyou']=!empty($input['gateways']['gateway_showorder_on_thankyou'])? true : false;
			$options['gateways']['gateway_select_label']=wppizza_validate_string($input['gateways']['gateway_select_label']);

			/**sort selected gateway*/
			asort($input['gateways']['gateway_order']);

			$options['gateways']['gateway_selected']=array();
			foreach($input['gateways']['gateway_order'] as $gw=>$sort){
				$options['gateways']['gateway_selected'][$gw]=!empty($input['gateways']['gateway_selected'][$gw])? true : false;
			}

			/**selected gateway*/
			$gateways=$this->wppizza_get_registered_gateways();

			foreach($gateways as $k=>$v){
			//if(is_array($v['gatewaySettings']) && count($v['gatewaySettings'])>0)
				$updateGatewayOptions=array();
				foreach($v['gatewaySettings'] as $l=>$m){
					/*validate value according to callback*/
					if(isset($input['gateways'][$v['gatewayOptionsName']][$m['key']])){
						if($m['validateCallback']!=''){
							if(is_array($m['validateCallback'])){
								$val=$m['validateCallback'][0]($input['gateways'][$v['gatewayOptionsName']][$m['key']],$m['validateCallback'][1]);
							}else{
								$val=$m['validateCallback']($input['gateways'][$v['gatewayOptionsName']][$m['key']]);
							}
						}else{
							/*no callback defined*/
							$val=$input['gateways'][$v['gatewayOptionsName']][$m['key']];
						}
					}else{
						$val='';
					}
					$updateGatewayOptions[$m['key']]=$val;
				}

					$lbl=wppizza_validate_string($input['gateways'][$v['gatewayOptionsName']]['gateway_label']);
				$updateGatewayOptions['gateway_label']=!empty($lbl) ? $lbl : $v['gatewayName'];
				$updateGatewayOptions['gateway_info']=wppizza_validate_string($input['gateways'][$v['gatewayOptionsName']]['gateway_info']);

				update_option($v['gatewayOptionsName'],$updateGatewayOptions);
			}
		}
?>