<?php
error_reporting(0);
if(!defined('DOING_AJAX') || !DOING_AJAX){
	header('HTTP/1.0 400 Bad Request', true, 400);
	print"you cannot call this script directly";
  exit; //just for good measure
}
/**testing variables ****************************/
//unset($_SESSION[$this->pluginSlug]);
//sleep(2);//when testing jquery fadeins etc
/******************************************/
$options=$this->pluginOptions;

/***************************************************************
*
*
*	[add / remove item from cart session]
*
*
***************************************************************/
if(isset($_POST['vars']['type']) && (($_POST['vars']['type']=='add' || $_POST['vars']['type']=='remove') && $_POST['vars']['id']!='')||  $_POST['vars']['type']=='refresh'){

	/**initialize price array***/
	$itemprice=array();
	/**********set header********************/
	header('Content-type: application/json');
	/**add to cart**/
	if($_POST['vars']['type']=='add'){
		/*explode into item id and selected size***/
		$itemVars=explode("-",$_POST['vars']['id']);
		//$meta=get_post_meta($itemVars[1], $this->pluginSlug, true );
		$itemName=get_the_title($itemVars[1]);
		$groupId=$itemVars[1].'.'.$itemVars[3];//group items by id and size. ensure there's a seperator between (as 8 and 31 would otherwise be the same as 83 and 1. furthermore , dont use "-" as the js splits by this
		
		/*get item set meta values to get price for this size**/
		$meta_values = get_post_meta($itemVars[1],$this->pluginSlug,true);
		$itemSizePrice=$meta_values['prices'][$itemVars[3]];
		/**are we hiding pricetier name if only one available ?**/
		if(count($meta_values['prices'])<=1 && $this->pluginOptions['layout']['hide_single_pricetier']==1){
			$itemSizeName='';
		}else{
			$itemSizeName=$options['sizes'][$itemVars[2]][$itemVars[3]]['lbl'];
		}
		
		/*add item to session array. adding lowercase name first to simplify sorting with asort**/
		$_SESSION[$this->pluginSlug]['items'][$groupId][]=array('sortname'=>strtolower($itemName),'size'=>$itemVars[3],'price'=>$itemSizePrice,'sizename'=>$itemSizeName,'printname'=>$itemName,'id'=>$itemVars[1],'additionalinfo'=>'');
	}
	/**remove from cart -> just unset**/
	if($_POST['vars']['type']=='remove'){
		/**explode and get last in array (the id)**/
		$groupId=end(explode("-",$_POST['vars']['id']));
		end($_SESSION[$this->pluginSlug]['items'][$groupId]);
		$last=key($_SESSION[$this->pluginSlug]['items'][$groupId]);
		unset($_SESSION[$this->pluginSlug]['items'][$groupId][$last]);
		/*if there are 0x this ingredient, unset completely**/
		if(count($_SESSION[$this->pluginSlug]['items'][$groupId])==0){
			unset($_SESSION[$this->pluginSlug]['items'][$groupId]);
		}
	}

	/*total price*/
	foreach($_SESSION[$this->pluginSlug]['items'] as $group){
		foreach($group as $v){
			$itemprice[]=$v['price'];
		}
	}
	$_SESSION[$this->pluginSlug]['total_price_items']=array_sum($itemprice);
	print"".json_encode(wppizza_order_summary($_SESSION[$this->pluginSlug],$options,true))."";
	exit();
}
/***************************************************************
*
*
*	[send the order]
*
*
***************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='sendorder'){
	/**********set header********************/
	header('Content-type: text/plain');
	/*get post variables*********************/
	$params = array();
	parse_str($_POST['vars']['data'], $params);
	/*lets check the emails again and use only the first one below if someone tried to put an array in**/
	$fromEmails=wppizza_validate_email_array($params['cemail']);
	/**cart contents**/
	$cartContents=wppizza_order_summary($_SESSION[$this->pluginSlug],$options);
	/**currency***/
	$currency=$cartContents['currency'];
	/**now timespamp and date**/
	$timestamp=time();
	$nowdate=date("d-M-y H:i:s",$timestamp);



		/*********************************************************************
			customer details
		**********************************************************************/
		$customer_details ="";
		$protectedKeys=array();
		foreach($options['order_form'] as $k=>$v){
			if(($v['enabled'])){
				$protectedKeys[$v['key']]=1;
				$customer_details .="".$v['lbl']." ".(strip_tags($params[$v['key']]))."".PHP_EOL;
			}
		}

		/*********************************************************************
			if another plugin/extension wants to add field value pairs, make sure
			its an array having [label] and [value] to display in email
			i.e:
			<input type="hidden" name="distinct_name[label]" value="some value"/>';
			<input type="text" name="distinct_name[value]" value="some value"/>';
			(make sure there are no clashes with other input fields)
		**********************************************************************/
		$i=0;
		foreach($params as $k=>$v){
			if(is_array($v) && isset($v['label']) && isset($v['value']) && !isset($protectedKeys[$k]) ){
				if($i==0){$customer_details .=PHP_EOL;}/*add one empty line for readabilities sake*/
				$customer_details .="".$v['label']." ".(strip_tags($v['value']))."".PHP_EOL;
			$i++;
			}
		}


		/*****************************************************************************************
			order details
		****************************************************************************************/
		$order=PHP_EOL."===============".$options['localization']['order_details']['lbl']."=================".PHP_EOL;
		$order.="".$nowdate."".PHP_EOL.PHP_EOL;

		foreach($cartContents['items'] as $k=>$v){
			/*tabs dont seem to work reliably, so lets try to put some even space between order item and total**/
			$strPartLeft=''.$v['count'].'x '.$v['name'].' '.$v['size'].' @ '.$currency.''.wppizza_output_format_float($v['price']).'';
			$spaces=50-strlen($strPartLeft);
			$strPartRight=''.$currency.''.wppizza_output_format_float($v['pricetotal']).'';
			$order.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
			if(isset($v['additionalinfo']) && is_array($v['additionalinfo'])){
				$addInfo=array();
				foreach($v['additionalinfo'] as $additionalInfo){
					$addInfo[]=''.$additionalInfo.'';
				}
				$order.="   ".implode(", ",$addInfo);
				$order.=PHP_EOL.PHP_EOL;
			}
		}
		$order.=PHP_EOL.PHP_EOL;
		$order.=''.$cartContents['order_value']['total_price_items']['lbl'].': '.$currency.''.($cartContents['order_value']['total_price_items']['val']).PHP_EOL;
		if($cartContents['order_value']['discount']['val']>0){
			$order.=''.$cartContents['order_value']['discount']['lbl'].': '.$currency.''.($cartContents['order_value']['discount']['val']).PHP_EOL;
		}
		if($cartContents['order_value']['delivery_charges']['val']>0){
			$order.=$cartContents['order_value']['delivery_charges']['lbl'].': '.$currency.''.($cartContents['order_value']['delivery_charges']['val']).PHP_EOL;
		}else{
			$order.=$cartContents['order_value']['delivery_charges']['lbl'].PHP_EOL;
		}
		$order.=PHP_EOL;
		$order.=$cartContents['order_value']['total']['lbl'].': '.$currency.''.($cartContents['order_value']['total']['val']).PHP_EOL;


		$order.=PHP_EOL.PHP_EOL;
		$order.=PHP_EOL."===========================================".PHP_EOL;



		/**********************************************************************************************************
			[send email]
		**********************************************************************************************************/
		$recipient = implode(",",$options['order']['order_email_to']);
		$subject = ''.get_bloginfo().': '.$options['localization']['your_order']['lbl'].' '.$nowdate.'';
		$message ="".$customer_details.PHP_EOL.$order.PHP_EOL ;

		$header = 'From: '.wppizza_validate_string($params['cname']).'<'.$fromEmails[0].'>' . PHP_EOL.
		'Reply-To: '.$fromEmails[0].'' . PHP_EOL .
		'X-Mailer: PHP/' . phpversion();
		$header .= PHP_EOL;
		$header .= 'Cc: '.$fromEmails[0].'' . PHP_EOL;
		if(count($options['order']['order_email_bcc'])>0){
		$header .= 'Bcc: '.implode(",",$options['order']['order_email_bcc']).'' . PHP_EOL;
		}

		$header .= 'MIME-Version: 1.0' . PHP_EOL;
		$header .= 'Content-type: text/plain; charset='.get_bloginfo('charset').'' . PHP_EOL;

		if (@mail($recipient, $subject, $message, $header)) {
			global $wpdb;
			$wpdb->hide_errors();
			//$wpdb->print_error();
			$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix . $this->pluginOrderTable." ( customer_details, order_details )VALUES ( %s, %s )", array($customer_details, $order)));
			print"<div class='mailSuccess'><h1>".$options['localization']['thank_you']['lbl']."</h1>".nl2br($options['localization']['thank_you_p']['lbl'])."</div>";
			unset($_SESSION[$this->pluginSlug]);
		}else{
			$error = error_get_last();
			print"<p class='mailError'>".print_r($error["message"])."</p>";
		}
exit();
}
exit();
?>