<?php
//error_reporting(0);
if(!defined('DOING_AJAX') || !DOING_AJAX){
	header('HTTP/1.0 400 Bad Request', true, 400);
	print"you cannot call this script directly";
  exit; //just for good measure
}
/**********set header********************/
//header('Content-type: application/json');
$options=$this->pluginOptions;
$optionSizes=wppizza_sizes_available($options['sizes']);//outputs an array $arr=array(['lbl']=>array(),['prices']=>array());

$output='';

/**save sorted categories**/ 
if($_POST['vars']['field']=='cat_sort'){

	$order = explode(',', $_POST['vars']['order']);
	$sorter = 0;
	/**we want all saved ones first as we might not display all on the screen**/
	$newOptions['layout']['category_sort']=$options['layout']['category_sort'];
	/**first parent categories**/
	foreach ($order as $id) {
		$key=(int)str_replace("tag-","",$id);
		$category = get_term_by( 'id', $key, $this->pluginSlugCategoryTaxonomy);		
		/*only saving the sort of the parent categories**/
		if($category->parent==0){
			$newOptions['layout']['category_sort'][(int)$key]=$sorter;
			$sorter++;
		}
	}
	/**the child categories, maybe we will need them at some point **/
	foreach ($order as $id) {
		$key=(int)str_replace("tag-","",$id);
		$category = get_term_by( 'id', $key, $this->pluginSlugCategoryTaxonomy);		
		/*only saving the sort of the parent categories**/
		if($category->parent>0){
			$newOptions['layout']['category_sort'][(int)$key]=$sorter;
			$sorter++;
		}
	}
	update_option( $this->pluginSlug, $newOptions );	
die(1);
}


/**adding a new meal category->add column selection**/ 
if($_POST['vars']['field']=='meals' && !isset($_POST['vars']['item']) && $_POST['vars']['id']>=0){
	$output=$this->wppizza_admin_section_category($_POST['vars']['field'],$_POST['vars']['id']);	
}
/**adding a new meal to category**/ 
if($_POST['vars']['field']=='meals' && isset($_POST['vars']['item']) && $_POST['vars']['id']>=0 && $_POST['vars']['newKey']>=0){
	$output=$this->wppizza_admin_section_category_item($_POST['vars']['field'],$_POST['vars']['id'],false,$_POST['vars']['newKey'],false,$options);
}

/**adding new size selection options**/ 
if($_POST['vars']['field']=='sizes' && $_POST['vars']['id']>=0 && isset($_POST['vars']['newFields']) && $_POST['vars']['newFields']>0){	
	$output=$this->wppizza_admin_section_sizes($_POST['vars']['field'],$_POST['vars']['id'],$_POST['vars']['newFields']);
}
/**prize tier selection has been changed->add relevant price options input fields**/ 
if($_POST['vars']['field']=='sizeschanged' && $_POST['vars']['id']!='' && isset($_POST['vars']['inpname']) &&  $_POST['vars']['inpname']!=''){
	$output='';
	if(is_array($options['sizes'][$_POST['vars']['id']])){
		foreach($options['sizes'][$_POST['vars']['id']] as $a=>$b){
			/*if we change the ingredient pricetire, do not use default prices , but just empty**/
			if(isset($_POST['vars']['classId']) && $_POST['vars']['classId']=='ingredients'){$price='';}else{$price=$b['price'];}			
			$output.="<input name='".$_POST['vars']['inpname']."[prices][]' type='text' size='5' value='".$price."'>";
	}}
}
/**adding new additive**/ 
if($_POST['vars']['field']=='additives' && $_POST['vars']['id']>=0){	
	$output=$this->wppizza_admin_section_additives($_POST['vars']['field'],$_POST['vars']['id'],'');
}
/**adding new custom opening time**/ 
if($_POST['vars']['field']=='opening_times_custom'){
	$output=$this->wppizza_admin_section_opening_times_custom($_POST['vars']['field']);
}
/**adding new times closed**/ 
if($_POST['vars']['field']=='times_closed_standard'){
	$output=$this->wppizza_admin_section_times_closed_standard($_POST['vars']['field']);
}
/**get orders**/ 
if($_POST['vars']['field']=='get_orders'){
	$output='';
	global $wpdb;
	if($_POST['vars']['limit']>0){$limit=' limit 0,'.$_POST['vars']['limit'].'';}else{$limit='';}
	$allOrders = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." ORDER BY id DESC ".$limit." ");
	if(is_array($allOrders) && count($allOrders)>0){
		$output.="<table>";
			$output.="<tr>";
				$output.="<td>";
					$output.="".__('Date', $this->pluginLocale)."";
				$output.="</td>";
				$output.="<td>";
					$output.="".__('Customer Details', $this->pluginLocale)."";
				$output.="</td>";
				$output.="<td>";
					$output.="".__('Order Details', $this->pluginLocale)."";
				$output.="</td>";
			$output.="</tr>";

			foreach ( $allOrders as $orders ){
				$output.="<tr>";
					$output.="<td>";
						$output.= date("d-M-Y H:i:s",strtotime($orders->order_date));
					$output.="</td>";
					$output.="<td>";
						$output.="<textarea class='wppizza_order_details'>". $orders->customer_details ."</textarea>";
					$output.="</td>";
					$output.="<td>";
						$output.="<textarea class='wppizza_order_details'>". $orders->order_details ."</textarea>";
					$output.="</td>";
				$output.="</tr>";
			}
		$output.="</table>";
		$output.="</div>";
		$output.="</div>";
	}else{
		$output.="<h1 style='text-align:center'>".__('no orders yet :(', $this->pluginLocale)."</h1>";
	}
}
print"".$output."";
exit();
?>