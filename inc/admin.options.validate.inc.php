<?php

	/**get previously saved options**/
	$options = $this->pluginOptions;
	
		/**lets not forget static, uneditable options **/
		$options['plugin_data']['version'] = $this->pluginVersion;
		$options['plugin_data']['nag_notice'] = $this->pluginNagNotice;
		$options['plugin_data']['empty_category_and_items'] = false;/*we dont really want to save these settings, just execute when true*/
		

		if(isset($input['plugin_data']['empty_category_and_items'])){
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
			$options['layout']['style'] = wppizza_validate_alpha_only($input['layout']['style']);
			$options['layout']['placeholder_img'] = !empty($input['layout']['placeholder_img']) ? true : false;
			$options['layout']['hide_cart_icon'] = !empty($input['layout']['hide_cart_icon']) ? true : false;
			$options['layout']['hide_prices'] = !empty($input['layout']['hide_prices']) ? true : false;
			$options['layout']['disable_online_order'] = !empty($input['layout']['disable_online_order']) ? true : false;
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
		}
		/**validate order settings***/
		if(isset($_POST[''.$this->pluginSlug.'_order'])){
			$options['order'] = array();//initialize array
			$options['order']['currency'] = strtoupper(wppizza_validate_letters_only($input['order']['currency'],3));//validation a bit overkill, but then again, why not
				$displayCurrency=wppizza_currencies($options['order']['currency'],true);
			$options['order']['currency_symbol'] = $displayCurrency['val'];
			$options['order']['orderpage'] = !empty($input['order']['orderpage']) ? (int)$input['order']['orderpage'] : false;
			$options['order']['orderpage_exclude']=!empty($input['order']['orderpage_exclude']) ? true : false;
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
			$options['order']['order_email_to'] = wppizza_validate_email_array($input['order']['order_email_to']);
			$options['order']['order_email_bcc'] = wppizza_validate_email_array($input['order']['order_email_bcc']);
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
			foreach($input['localization'] as $a=>$b){
				/*add new value , but keep desciption (as its not editable on frontend)*/
				$options['localization'][$a]=array('descr'=>$options['localization'][$a]['descr'],'lbl'=>wppizza_validate_string($b));
			}}			
		}
?>