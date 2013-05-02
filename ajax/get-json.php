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
	$timestamp=current_time('timestamp');
	$nowdate=date("d-M-Y H:i:s",$timestamp);
	/**get charset**/
	$blogCharset=get_bloginfo('charset');



		/*********************************************************************
			customer details
		**********************************************************************/
		$customer_details ="".PHP_EOL.PHP_EOL;
		$customer_details_array =array();
		$protectedKeys=array();
		ksort($options['order_form']);
		foreach($options['order_form'] as $k=>$v){
			if(($v['enabled'])){
				$protectedKeys[$v['key']]=1;

				if($v['type']!='textarea'){/*pad non-textareas*/
					$strPartLeft=''.$v['lbl'].'';
					$spaces=45-strlen($strPartLeft);
					$strPartRight=''.strip_tags($params[$v['key']]);

					$customer_details.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
				}else{
					$customer_details.=PHP_EOL;
					$customer_details.=''.$v['lbl'].PHP_EOL;
					$customer_details.= wordwrap(strip_tags($params[$v['key']]), 72, "\n", true);
					$customer_details.=PHP_EOL;
				}
/**html emails**/
/**stick into array for use in html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	$customer_details_array[]=array('label'=>wppizza_email_html_entities($v['lbl']),'value'=>wppizza_email_html_entities($params[$v['key']]));
}

			}
		}

		/*********************************************************************
			if another plugin/extension wants to add field value pairs, make sure
			its an array having [label] and [value] to display in email
			i.e:
			<input type="hidden" name="distinct_name[label]" value="some value"/>';
			<input type="text" name="distinct_name[value]" value="some value"/>';
			(make sure there are no clashes with other input fields)
			ought to make this "classable" at some point anyway
		**********************************************************************/
		$i=0;
		foreach($params as $k=>$v){
			if(is_array($v) && isset($v['label']) && isset($v['value']) && !isset($protectedKeys[$k]) ){
				if($i==0){$customer_details .=PHP_EOL;}/*add one empty line for readabilities sake*/
				$strPartLeft=''.$v['label'].'';
				$spaces=45-strlen($strPartLeft);
				$strPartRight=''.strip_tags($v['value']);

				$customer_details.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;

/**html emails**/
/**stick into array for use in html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	$customer_details_array[]=array('label'=>wppizza_email_decode_entities($v['label'],$blogCharset),'value'=>wppizza_email_decode_entities($strPartRight,$blogCharset));
}
			$i++;
			}
		}


		/*****************************************************************************************
			order details
		****************************************************************************************/
		$order=PHP_EOL."===============".$options['localization']['order_details']['lbl']."===========================".PHP_EOL;
		$order.=PHP_EOL.$nowdate."".PHP_EOL.PHP_EOL;
		$order_items =array();
		$order_summary =array();

		foreach($cartContents['items'] as $k=>$v){
			/*tabs dont seem to work reliably, so lets try to put some even space between order item and total**/
			$strPartLeft=''.$v['count'].'x '.$v['name'].' '.$v['size'].'  '.$currency.' '.wppizza_output_format_price(wppizza_output_format_float($v['price']),$options['layout']['hide_decimals']).'';
			$spaces=55-strlen($strPartLeft);
			$strPartRight=''.$currency.' '.wppizza_output_format_price(wppizza_output_format_float($v['pricetotal']),$options['layout']['hide_decimals']).'';
			$order.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
			if(isset($v['additionalinfo']) && is_array($v['additionalinfo'])){
				$addInfo=array();
				foreach($v['additionalinfo'] as $additionalInfo){
					$addInfo[]=''.$additionalInfo.'';
				}
				$order.="   ".implode(", ",$addInfo);
				$order.=PHP_EOL.PHP_EOL;
			}
/**html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	if(isset($v['additionalinfo']) && is_array($v['additionalinfo'])){
		$addInfoHtml=wppizza_email_html_entities(implode(", ",$addInfo));
	}else{
		$addInfoHtml='';
	}
	$order_items[]=array('label'=>wppizza_email_html_entities($strPartLeft),'value'=>wppizza_email_html_entities($strPartRight),'additional_info'=>$addInfoHtml);
}

		}
		$order.=PHP_EOL.PHP_EOL;
		$order.=''.$cartContents['order_value']['total_price_items']['lbl'].': '.$currency.' '.($cartContents['order_value']['total_price_items']['val']).PHP_EOL;
/**html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	$order_summary['cartitems']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['total_price_items']['lbl']),'price'=>$cartContents['order_value']['total_price_items']['val'],'currency'=>$currency );
}

		if($cartContents['order_value']['discount']['val']>0){
			$order.=''.$cartContents['order_value']['discount']['lbl'].': '.$currency.' '.($cartContents['order_value']['discount']['val']).PHP_EOL;
/**html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	$order_summary['discount']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['discount']['lbl']),'price'=>$cartContents['order_value']['discount']['val'],'currency'=>$currency );
}
		}

		if($cartContents['order_value']['delivery_charges']['val']!=''){
			$order.=$cartContents['order_value']['delivery_charges']['lbl'].': '.$currency.' '.($cartContents['order_value']['delivery_charges']['val']).PHP_EOL;
/**html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	$order_summary['delivery']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['delivery_charges']['lbl']),'price'=>$cartContents['order_value']['delivery_charges']['val'],'currency'=>$currency );
}

		}else{
			$order.=$cartContents['order_value']['delivery_charges']['lbl'].PHP_EOL;
/**html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	$order_summary['delivery']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['delivery_charges']['lbl']),'price'=>'','currency'=>'' );
}
		}
		$order.=PHP_EOL;
		$order.=$cartContents['order_value']['total']['lbl'].': '.$currency.' '.($cartContents['order_value']['total']['val']).PHP_EOL;

/**html emails**/
if($options['plugin_data']['mail_type']=='phpmailer'){
	$order_summary['total']=array('label'=>wppizza_email_html_entities($cartContents['order_value']['total']['lbl']),'price'=>$cartContents['order_value']['total']['val'],'currency'=>$currency );
}


		$order.=PHP_EOL;
		$order.=PHP_EOL."=====================================================".PHP_EOL;

		/**now decode any funny entities**/
		$order=wppizza_email_decode_entities($order,$blogCharset);


		/**********************************************************************************************************
			[send email]
		**********************************************************************************************************/
		$recipient = implode(",",$options['order']['order_email_to']);
		$subjectPrefix = ''.get_bloginfo().': ';
		$subject = ''.$options['localization']['your_order']['lbl'].' '.$nowdate.'';
		$message ="".$customer_details.PHP_EOL.$order.PHP_EOL ;


		/************use default mail function****************/
		if($options['plugin_data']['mail_type']=='mail'){
			$header = 'From: '.wppizza_validate_string($params['cname']).'<'.$fromEmails[0].'>' . PHP_EOL.
			'Reply-To: '.$fromEmails[0].'' . PHP_EOL .
			'X-Mailer: PHP/' . phpversion();
			$header .= PHP_EOL;
			$header .= 'Cc: '.$fromEmails[0].'' . PHP_EOL;
			if(count($options['order']['order_email_bcc'])>0){
			$header .= 'Bcc: '.implode(",",$options['order']['order_email_bcc']).'' . PHP_EOL;
			}

			$header .= 'MIME-Version: 1.0' . PHP_EOL;
			$header .= 'Content-type: text/plain; charset='.$blogCharset.'' . PHP_EOL;

			if(@mail($recipient, $subjectPrefix.$subject, $message, $header)) {
				$mailSent='mail';
			}else{
				$mailError = error_get_last();
				$mailError['sender']='mail';
			}
		}

		/************use wp mail****************/
		if($options['plugin_data']['mail_type']=='wp_mail'){
			$wpMailHeaders=array();
			$wpMailHeaders[] = 'From: '.wppizza_validate_string($params['cname']).'<'.$fromEmails[0].'>';
			$wpMailHeaders[] = 'Cc: '.$fromEmails[0].'';
			if(count($options['order']['order_email_bcc'])>0){
				$wpMailHeaders[]= 'Bcc: '.implode(",",$options['order']['order_email_bcc']).'';
			}
			$wpMailHeaders[] = 'Reply-To: '.$fromEmails[0].'';
			if(@wp_mail($recipient, $subjectPrefix.$subject, $message, $wpMailHeaders)) {
				$mailSent='wp_mail';
			}else{
				$mailError=error_get_last();
				$mailError['sender']='wp_mail';
			}
		}

		/************use phpmailer mail function****************/
		if($options['plugin_data']['mail_type']=='phpmailer'){
			require_once (WPPIZZA_PATH.'classes/phpmailer/class.phpmailer.php');
			$mail = new PHPMailer(true);
			/*returns $orderHtml*/
			$orderHtml='';
			if (file_exists( get_template_directory() . '/wppizza-order-html-email.php')){
				require_once(get_template_directory_uri().'/wppizza-order-html-email.php');
			}else{
				require_once(WPPIZZA_PATH.'templates/wppizza-order-html-email.php');
			}


			if (file_exists( get_template_directory() . '/wppizza-phpmailer-settings.php')){
				require_once(get_template_directory_uri().'/wppizza-phpmailer-settings.php');
			}else{
				require_once(WPPIZZA_PATH.'templates/wppizza-phpmailer-settings.php');
			}
		}

		/***successfully sent-> insert order into db and empty session***/
		if(isset($mailSent)){
			global $wpdb;
			$wpdb->hide_errors();
			//$wpdb->print_error();
			$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix . $this->pluginOrderTable." ( customer_details, order_details )VALUES ( %s, %s )", array($customer_details, $order)));
			print"<div class='mailSuccess'><h1>".$options['localization']['thank_you']['lbl']."</h1>".nl2br($options['localization']['thank_you_p']['lbl'])."</div>";
			unset($_SESSION[$this->pluginSlug]);
		}
		/***mail sending error -> show error***/
		if(isset($mailError)){
			print"<p class='mailError'>".print_r($mailError,true)."</p>";
		}


exit();
}
exit();
?>