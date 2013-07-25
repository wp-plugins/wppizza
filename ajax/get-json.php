<?php
error_reporting(0);
if(!defined('DOING_AJAX') || !DOING_AJAX){
	header('HTTP/1.0 400 Bad Request', true, 400);
	print"you cannot call this script directly";
  exit; //just for good measure
}
/**testing variables ****************************/
//unset($_SESSION[$this->pluginSession]);
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
		$_SESSION[$this->pluginSession]['items'][$groupId][]=array('sortname'=>strtolower($itemName),'size'=>$itemVars[3],'price'=>$itemSizePrice,'sizename'=>$itemSizeName,'printname'=>$itemName,'id'=>$itemVars[1],'additionalinfo'=>'');
	}
	/**remove from cart -> just unset**/
	if($_POST['vars']['type']=='remove'){
		/**explode and get last in array (the id)**/
		$groupId=end(explode("-",$_POST['vars']['id']));
		end($_SESSION[$this->pluginSession]['items'][$groupId]);
		$last=key($_SESSION[$this->pluginSession]['items'][$groupId]);
		unset($_SESSION[$this->pluginSession]['items'][$groupId][$last]);
		/*if there are 0x this ingredient, unset completely**/
		if(count($_SESSION[$this->pluginSession]['items'][$groupId])==0){
			unset($_SESSION[$this->pluginSession]['items'][$groupId]);
		}
	}

	/*total price*/
	foreach($_SESSION[$this->pluginSession]['items'] as $group){
		foreach($group as $v){
			$itemprice[]=$v['price'];
		}
	}

	$totalitemprice=array_sum($itemprice);

	/**total tax on all items -> currently not used as we will be calculating tax AFTER any discounts**/
	$_SESSION[$this->pluginSession]['total_items_tax']=0;
	if($this->pluginOptions['order']['item_tax']>0){
		$_SESSION[$this->pluginSession]['total_items_tax']=$totalitemprice/100*$this->pluginOptions['order']['item_tax'];
	}


	$_SESSION[$this->pluginSession]['total_price_items']=$totalitemprice;
	print"".json_encode(wppizza_order_summary($_SESSION[$this->pluginSession],$options,true))."";
	exit();
}
/***************************************************************
*
*
*	[set self pickup]
*
*
***************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='order-pickup'){
	if($_POST['vars']['value']=='true'){
		$_SESSION[$this->pluginSession]['selfPickup']=1;
	}else{
		if(isset($_SESSION[$this->pluginSession]['selfPickup'])){
			unset($_SESSION[$this->pluginSession]['selfPickup']);
		}
	}
exit();
}
/************************************************************************************************
*
*
*	[send the order by email and update db]
*
*
************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='sendorder'){
	/**********set header********************/
	header('Content-type: text/plain');

	/*****************************************
		[get and parse all post variables
	*****************************************/
	$params = array();
	parse_str($_POST['vars']['data'], $params);
	/*****************************************
		[get the order]
	*****************************************/
	global $wpdb;
	$orderId=false;
	$order = $wpdb->get_row("SELECT id,payment_status FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE hash='".$params['wppizza_hash']."' ");
	if($order){
		$orderId=$order->id;
		$orderStatus=$order->payment_status;
	}


	/***********************************************************
		[set name and email of the the person that is ordering]
	***********************************************************/
	$recipientName =wppizza_validate_string($params['cname']);
	$fromEmails=wppizza_validate_email_array($params['cemail']);

	/********************************************
		[new send order email class]
	********************************************/
	$sendEmail=new WPPIZZA_SEND_ORDER_EMAILS;
	/** set email and name of the person that is ordering*/
	$sendEmail->orderClientName=$recipientName;
	$sendEmail->orderClientEmail=$fromEmails[0];
	/***get/set posted variables****/
	$sendEmail->orderPostVars=$params;
	/***create html and plaintext emails ****/
	$sendEmail->orderMessage=$sendEmail->wppizza_order_construct_email();


	/************************************************************
	*	[provided we have a valid order id AND its set
	*	to INITIALIZE send the emails and update db]
	*	returns array
	*	['status']->true/false
	*	['error']->''/msg
	*	['mailer']->mail function name
	*
	************************************************************/
	/*default errors**/
	$mailResults['error']=__('Sorry, we could not find this order.',$this->pluginLocale);
	$mailResults['mailer']='Error'; /**just so we dont have an abandoned colon**/
	if($orderId){
		if($orderStatus=='INITIALIZED'){
			$updateDb=true;
			$mailResults=$sendEmail->wppizza_order_send_email();
		}else{
			$mailResults['error']=__('This order has already been processed',$this->pluginLocale);
		}
	}
	/***update order******/
	if(isset($updateDb)){
		$now=time();
		$thisOrderTransactionId='COD'.$now.$orderId.'';
		$orderDetails=$sendEmail->orderMessage;
		if($mailResults['status']){
			$mailSent='Y';
			$mailError='';
			$paymentStatus='COMPLETED';
			$transactionDetails=''.serialize($orderDetails).'';
		}else{
			$mailSent='N';
			$mailError=serialize($mailResults['error']);
			$paymentStatus='FAILED';
			$transactionDetails=__('FAILED: Sending of Mail Failed',$this->pluginLocale);
		}
		/*update order**/
		$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET
		transaction_id='".$thisOrderTransactionId."',
		transaction_details='".$transactionDetails."',
		customer_details='".$orderDetails['customer_details']."',
		order_details='".$orderDetails['order_details']."',
		customer_ini='".serialize($params)."',
		mail_construct='".serialize($sendEmail->orderMessage)."',
		payment_status='".$paymentStatus."',
		mail_sent='".$mailSent."',
		mail_error='".$mailError."'
		WHERE id='".$orderId."' ");
	}


	/********************************************************************
	*
	*	[depending on $mailResults['status'],
	*	will insert order into db and output "thankyou"
	*	or justs displays error]
	*
	********************************************************************/
	$output=$sendEmail->wppizza_order_results($mailResults,$orderId);

	print"".$output."";
exit();
}
exit();
?>