<?php
error_reporting(0);
if(!defined('DOING_AJAX') || !DOING_AJAX){
	header('HTTP/1.0 400 Bad Request', true, 400);
	print"you cannot call this script directly";
  exit; //just for good measure
}
/**testing variables ****************************/
//sleep(2);//when testing jquery fadeins etc
/******************************************/
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
	$allOrders = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED') ORDER BY id DESC ".$limit." ");
	if(is_array($allOrders) && count($allOrders)>0){
		$output.="<div>".__('Note: deleting an order will <b>ONLY</b> delete it from the database table. It will <b>NOT</b> issue any refunds, cancel the order, send emails etc.', $this->pluginLocale)."</div>";
		$output.="<div style='color:red'>".__('"Status" is solely for your internal reference. Updating/changing the value will have no other effects but might help you to identify which orders have not been processed.', $this->pluginLocale)."</div>";
		$output.="<table>";
			$output.="<tr class='wppizza-orders-head'>";
				$output.="<td>";
					$output.="".__('Order', $this->pluginLocale)."";
				$output.="</td>";
				$output.="<td>";
					$output.="".__('Customer Details', $this->pluginLocale)."";
				$output.="</td>";
				$output.="<td>";
					$output.="".__('Order Details', $this->pluginLocale)."";
				$output.="</td>";
				$output.="<td>";
					$output.="";
				$output.="</td>";
			$output.="</tr>";


			$customOrderStatus=wppizza_custom_order_status();
			foreach ( $allOrders as $orders ){
				$output.="<tr class='wppizza-ord-status-".strtolower($orders->order_status)."'>";
					$output.="<td style='white-space:nowrap'>";
						$output.= date("d-M-Y H:i:s",strtotime($orders->order_date));
						if($orders->initiator!=''){
							$output.="<br/>Payment By: ". $orders->initiator ."";
						}
						if($orders->transaction_id!=''){
							$output.="<br/>ID: ". $orders->transaction_id ."";
						}
						$output.="<br/>";
						$output.="".__('Status', $this->pluginLocale)."";
						$output.="<select id='wppizza_order_status-".$orders->id."' name='wppizza_order_status-".$orders->id."' class='wppizza_order_status'>";
							foreach($customOrderStatus as $s){
								$output.="<option value='".$s."' ".selected($orders->order_status,$s,false).">".__($s, $this->pluginLocale)."</option>";	
							}
						$output.="</select>";	
					$output.="</td>";
					$output.="<td>";
						$output.="<textarea class='wppizza_order_customer_details'>". $orders->customer_details ."</textarea>";
					$output.="</td>";
					$output.="<td>";
						$output.="<textarea class='wppizza_order_details'>". $orders->order_details ."</textarea>";
					$output.="</td>";
					$output.="<td>";
						$output.="<a href='#' id='wppizza_order_".$orders->id."' class='wppizza_order_delete'>".__('delete', $this->pluginLocale)."</a>";
					$output.="</td>";
				$output.="</tr>";
			}
		$output.="</table>";
	}else{
		$output.="<h1 style='text-align:center'>".__('no orders yet :(', $this->pluginLocale)."</h1>";
	}
}
/**update order status**/
if($_POST['vars']['field']=='orderstatuschange' && isset($_POST['vars']['id']) && $_POST['vars']['id']>=0){
	global $wpdb;
	$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET order_status='".$_POST['vars']['selVal']."' WHERE id=".$_POST['vars']['id']." ");
	$output="".print_r($res,true)."";
}
/**delete order**/
if($_POST['vars']['field']=='delete_orders'){
	global $wpdb;
	$res=$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id=%s ",$_POST['vars']['ordId']));
	$output.="".__('order deleted', $this->pluginLocale)."";
}

/**delete abandoned  orders**/
if($_POST['vars']['field']=='delete_abandoned_orders'){
	global $wpdb;
	$days=0;
	if((int)$_POST['vars']['days']>=1){
		$days=(int)$_POST['vars']['days'];
	}
	/**do or dont delete all non completed orders**/
		$pStatusQuery=" IN ('INITIALIZED','CANCELLED')";
	if($_POST['vars']['failed']=='true'){
		$pStatusQuery=" NOT IN ('COMPLETED','PENDING')";
	}
	$sql="DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE order_date < TIMESTAMPADD(DAY,-".$days.",NOW()) AND payment_status ".$pStatusQuery."";
	$res=$wpdb->query( $wpdb->prepare($sql));
	$output.="".__('Done', $this->pluginLocale)."";

}
print"".$output."";
exit();
?>