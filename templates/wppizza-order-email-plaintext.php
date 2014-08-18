<?php
/****************************************************************************************************************
*
*	WPPizza - Plaintext Email Template
*
*	Note: do not use html tags. it will not work . Know what you are doing.
*
*	if you are sending html emails with phpmailer (wppizza->settings->Select Type of Mail Delivery)
*	you could also comment OUT
*	$mail->AltBody = $this->orderMessage['plaintext'];
*	in wppizza-phpmailer-settings.php as phpmailer will automatically generate plaintext emails from the html input
*	if you do this, changes to this file will not make any difference
*	make sure however, that you copy wppizza-phpmailer-settings.php to your theme directory
*	as otherwise your changes will be overwritten in the next update of this plugin. just saying.....
*
****************************************************************************************************************/
?>
<?php
/****************************************************************************
*
*	[header: date and time of order,gateway used,transactionid  etc]
*
****************************************************************************/
?>
===========<?php echo $orderLabel['order_details'] ?>============
<?php echo $nowdate ?>

<?php echo $orderLabel['order_paid_by'] ?> <?php echo $gatewayLabel ?> (<?php echo $transactionId ?>)



<?php
/****************************************************************************
*
*	[customer details: whatever fields where enabled on order page]
*
****************************************************************************/
echo $emailPlaintext['customer_details'];
?>

<?php
/****************************************************************************
*
*	[order items: list of items ordered]
*	to make thing reasonably pretty in plaintext emails , we pad with spaces
*	as required as tabs do not seem to want to work
*
****************************************************************************/
$output='';

/***allow filtering of items (sort, add categories and whatnot)****/
$emailPlaintext['items'] = apply_filters('wppizza_emailplaintext_filter_items', $emailPlaintext['items'], 'plaintextemail');

foreach($emailPlaintext['items'] as $itemKey=>$item){
	/***allow action per item - probably to use in conjunction with filter above****/
	$output = apply_filters('wppizza_emailplaintext_item', $item, $output);

	/**added 2.10.2*/
	/**construct the markup display of this item**/
	$itemMarkup=array();
	$itemMarkup['quantity']		=''.$item['quantity'].'x ';
	$itemMarkup['name']			=''.$item['name'].' ';
	$itemMarkup['size']			=''.$item['size'].' ';
	$itemMarkup['price']		='['.$currency_left.''.$item['price'].''.$currency_right.']';

	/**try to add some even spaces between things**/
	$spaces=75-strlen(implode("",$itemMarkup));
	$itemMarkup['spacer']=str_pad('', $spaces);

	$itemMarkup['price_total']	=''.$currency_left.''.$item['pricetotal'].''.$currency_right.'';

	$itemMarkup['linebreak']=PHP_EOL;

	if(isset($item['additional_info']) && trim($item['additional_info'])!=''){
		$itemMarkup['additionalinfo']=''.$item['additional_info'].''.PHP_EOL.'';
	}

	/**************************************************************************************************
		[added filter for customisation  v2.10.2]
		if you wish to customise the output, i would suggest you use the filter below in
		your functions.php instead of editing this file (or a copy thereof in your themes directory)
	/**************************************************************************************************/
	$itemMarkup = apply_filters('wppizza_filter_plaintextemail_item_markup', $itemMarkup, $item, $itemKey, $options['order']);
	/**output markup**/
	$output.=implode("",$itemMarkup);

	/**add additional line break as spacer between items**/
	//$output.=PHP_EOL;
}
/* print it */
echo''.$output.'';
/*****************************************************************************************************
	if you've changed the above and also want to store these changes in the order history->order details
	as opposed to just in plaintext emails uncomment the line below.
	YOU PROBABLY SHOULD DO A TEST ORDER AND CHECK HISTORY->ORDER DETAILS !!!
	IF IN DOUBT, LEAVE IT COMMENTED OUT
*****************************************************************************************************/
/* $emailPlaintext['db_items']=$output; */
?>


<?php
/************************************************************************************************
*
*	[order summary: price/tax/discount/delivery options etc]
*
************************************************************************************************/
echo $emailPlaintext['order_summary'];
?>

<?php
/****************************************************************************
*
*	[footer]
*
****************************************************************************/
?>
====================================================================

<?php
	echo $orderLabel['order_email_footer'];
?>