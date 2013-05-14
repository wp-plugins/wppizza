<?php
	/****************************************************
	*
	*	[insert new default options into options table]
	*	[delete obsolte options from options table]
	*
	*****************************************************/

	/**********************************************
		[check and add newly added default options]
	***********************************************/
	$added_options=wppizza_compare_options($defaultOptions,$options);
	/*get array of new default options when updating plugin*/
	$update_options=wppizza_merge_options($added_options,$options);

	/*distinctly set plugin version*/
	$update_options['plugin_data']['version']=$this->pluginVersion;
	/*add delivery charges per item if it does not exist yet (as wppizza_merge_options -> wppizza_traverse_list only looks for first dimansion in array. should maybe be made recursive one day though.....)*/
	if(!isset($options['order']['delivery']['per_item'])){
		$update_options['order']['delivery']['per_item']=$defaultOptions['order']['delivery']['per_item'];
	}
	/****************************************************************************
	$update_options now holds the old options plus the added new defaults options
	*****************************************************************************/

	/**********************************************
		[now lets remove obsolete options]
	***********************************************/
	$removed_options=wppizza_compare_options($options,$defaultOptions);/*get obsolete options**/
	/*ini array*/
	$arr1_flat = array();
	$arr2_flat = array();
	/*flatten*/
	$arr1_flat = wppizza_flatten($update_options);
	$arr2_flat = wppizza_flatten($removed_options);
	/*get difference*/
	$ret = array_diff_assoc($arr1_flat, $arr2_flat);

	/**unflatten->final options**/
	$update_options = wppizza_inflate($ret);
	/******************************************************************************************************
	*
	* $update_options now holds old options plus the added new defaults options minus the removed options
	*
	******************************************************************************************************/


	/**override some options as we do NOT want to remove them*/
	/*although these arrays are set in defaultOptions, the default values are empty, as the dimensions of it are user generated and we do not want to loose these values when comparing / updating**/
	/*if we however ever not use any of these options above, we can delete the relevant one here**/
	if(isset($options['times_closed_standard'])){
		$update_options['times_closed_standard']=$options['times_closed_standard'];
	}
	if(isset($options['opening_times_custom'])){
		$update_options['opening_times_custom']=$options['opening_times_custom'];
	}
	if(isset($options['order']['order_email_to'])){
		$update_options['order']['order_email_to']=$options['order']['order_email_to'];
	}
	if(isset($options['order']['order_email_bcc'])){
		$update_options['order']['order_email_bcc']=$options['order']['order_email_bcc'];
	}
	if(isset($options['order_form'])){
		$update_options['order_form']=$options['order_form'];
	}
	if(isset($options['plugin_data']['category_parent_page'])){
		$update_options['plugin_data']['category_parent_page']=$options['plugin_data']['category_parent_page'];
	}
	/*this will always be an empty array (discounts set to "none"), so distinctly set it here as the comparison function above will strip it as it's empty**/
	$update_options['order']['discounts']['none']=array();
	ksort($update_options['order']['discounts']);/*to keep a consistant order*/

?>