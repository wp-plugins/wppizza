<?php
 /*
 *
 *	Template: WPPizza Gateway "Thank you" page Order Details
 *
 *	if enabled, this will display the order details on the thank you page
 *	after a transaction has been successfuly processed
 *	classes are the same as used in the order page (except trasaction id / time , as they doent exist in the order page before processing), so instead of using a custom version of this,
 *	MAYBE you want to just adjust the css so the layout is equal to the order page
 *
 *
 *	$order=>array(transaction_id,transaction_date_time,currency,currencyiso)
 *	$items=>array of tems ordered including (name,quantity,price,pricetotal,additionalInfo)
 *	$summary=>summary of all items (total_price_items,discount,delivery_charges,item_tax,total)
 *	$customer=> array customdetails
 *	$customerlbl=> array label for customer details
 *  $orderlbl=> array of localized vars
 */
?>
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_before');
?>
<div id="wppizza-cart-contents" class="wppizza-cart-thankyou">

<?php	/**header with time and transaction id**/ ?>
	<div id="wppizza-transaction-head"><?php echo $orderlbl['your_order'] ?> - <span id="wppizza-transaction-id">[<?php echo $order['transaction_id'] ?>]</span><?php do_action('wppizza_show_order_head');/*do something*/ ?></div>
	<div id="wppizza-transaction-time"><?php echo $order['transaction_date_time'] ?><br/><br/></div>



<?php	/**customer details**/ 	?>
<?php if(isset($customer) && is_array($customer)){ ?>
	<ul id="wppizza-customer-details">
	<?php foreach($customer as $k=>$v){ if (isset($customerlbl[$k])) {?>
		<li><label><?php echo $customerlbl[$k] ?></label> <?php echo $v ?></li>
	<?php }} ?>
	</ul>
<?php } ?>


<?php	/**order items details**/ 	?>
	<ul id="wppizza-item-details">
	<?php foreach($items as $k=>$item){ ?>
		<li><?php echo''.$item['quantity'].'x '.$item['name'].' '.$item['size'].' ['.$order['currency'].' '.$item['price'].']' ?> <span><?php echo''.$order['currency'].' '.$item['pricetotal'].''; ?></span>
		<?php if(isset($item['additionalInfo']) && $item['additionalInfo']!=''){?>
			<div class="wppizza-item-additionalinfo">
				<span><?php echo $item['additionalInfo'] ?></span>
			</div>
		<?php } ?>
		</li>
	<?php } ?>
	</ul>


<?php	/**order summary**/ 	?>
	<ul id="wppizza-cart-subtotals">

			<li class="wppizza-order-total-items"><?php echo $orderlbl['order_items'] ?><span><?php echo $order['currency'].' '.$summary['total_price_items']; ?></span></li>

		<?php if($summary['discount']>0){/*discount applies*/?>
			<li class="wppizza-order-discount"><?php echo $orderlbl['discount'] ?><span><span class="wppizza-minus"></span><?php echo $order['currency'].' '.$summary['discount']; ?></span></li>
		<?php } ?>

		<?php if($summary['item_tax']>0){/*tax*/ ?>
			<li class="wppizza-order-item-tax"><?php echo $orderlbl['item_tax_total'] ?><span><?php echo $order['currency'].' '.$summary['item_tax']; ?></span></li>
		<?php } ?>

		<?php if(!isset($summary['selfPickup']) ||  $summary['selfPickup']==0){ /*no self pickup enabled or chosen :conditional  NEW IN VERSION  v1.4.1*/ ?>
			<?php if($summary['delivery_charges']!='' ){/*delivery charges if any*/?>
				<li class="wppizza-order-pickup"><?php echo $orderlbl['delivery_charges'] ?><span><?php echo $order['currency'].' '.$summary['delivery_charges']; ?></span></li>
			<?php }else{ ?>
				<li class="wppizza-order-pickup"><?php echo $orderlbl['delivery_charges'] ?><span><?php echo $orderlbl['free_delivery'] ?></span></li>
			<?php } ?>
		<?php } ?>

			<li id="wppizza-cart-total"><?php echo $orderlbl['order_total'] ?><span><?php echo $order['currency'].' '.$summary['total']; ?></span></li>

		<?php if(isset($summary['selfPickup']) &&  $summary['selfPickup']==1){ /*self pickup conditional-> no delivery charges : NEW IN VERSION 1.4.1**/ ?>
			<li id="wppizza-self-pickup"><?php echo $orderlbl['order_page_self_pickup'] ?></li>
		<?php } ?>
	</ul>
</div>
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_after');
?>