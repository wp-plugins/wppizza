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
?>