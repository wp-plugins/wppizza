<?php
/*
*
*	 WPPizza Order Page
*
*/
/*********************************************************************************************************
*
*	[get cart contents with some variables]
*
*	[$cart['items'] = contains cart contents, grouped and sorted]
*	[$cart['order_value'] = contains subtotal, deliver charges, discounts and grand total]
*	[$txt array= localized variables from settings->localization]
*	[$formelements = form elements from settings->order form]
*	
*********************************************************************************************************/
/**if the user is logged in , pre-enter the info we have -> NEW IN VERSION 2.0***/
global $current_user;
get_currentuserinfo();
?>

<?php
	/*NEW IN VERSION 2.0 => FOR FUTURE USE*/
	do_action('wppizza_order_form_before');
?>
<form id='wppizza-send-order' method='post' action='' accept-charset="<?php echo get_bloginfo('charset') /*accept charset NEW IN VERSION 2.0 */ ?>">
	<fieldset id="wppizza-cart-contents">
		<legend><?php echo $txt['your_order']['lbl'] ?></legend>
		<?php if(count($cart['items'])>0){/*make sure there's stuff to order***/?>
			<ul id="wppizza-item-details">
			<?php foreach($cart['items'] as $item){ ?>
				<li><?php echo''.$item['count'].'x '.$item['name'].' '.$item['size'].' ['.$cart['currency'].' '.$item['price'].']' ?> <span><?php echo''.$cart['currency'].' '.$item['pricetotal'].''; ?></span>
					<?php if(is_array($item['additionalinfo']) && count($item['additionalinfo'])>0){?>
					<div class="wppizza-item-additionalinfo">
						<?php foreach($item['additionalinfo'] as $additionalInfo){?>
						<span><?php echo $additionalInfo ?></span>
						<?php } ?>
					</div>
					<?php } ?>
				</li>
			<?php } ?>
			</ul>

			<ul id="wppizza-cart-subtotals">

				<li class="wppizza-order-total-items"><?php echo $txt['order_items']['lbl'] ?><span><?php echo $cart['currency'].' '.$cart['order_value']['total_price_items']['val']; ?></span></li>

			<?php if($cart['order_value']['discount']['val']>0){/*discount applies*/?>
				<li class="wppizza-order-discount"><?php echo $txt['discount']['lbl'] ?><span><span class="wppizza-minus"></span><?php echo $cart['currency'].' '.$cart['order_value']['discount']['val']; ?></span></li>
			<?php } ?>

			<?php if($cart['order_value']['item_tax']['val']>0){/*item/sales tax applies  NEW IN VERSION 2.0*/ ?>
				<li class="wppizza-order-item-tax"><?php echo $txt['item_tax_total']['lbl'] ?><span><?php echo $cart['currency'].' '.$cart['order_value']['item_tax']['val']; ?></span></li>
			<?php } ?>
			
			<?php if(!isset($cart['self_pickup_enabled']) ||  $cart['selfPickup']==0){ /*no self pickup enabled or chosen :conditional  NEW IN VERSION  v1.4.1*/ ?>
				<?php if($cart['order_value']['delivery_charges']['val']!='' ){/*delivery charges if any*/?>
					<li class="wppizza-order-pickup"><?php echo $txt['delivery_charges']['lbl'] ?><span><?php echo $cart['currency'].' '.$cart['order_value']['delivery_charges']['val']; ?></span></li>
				<?php }else{ ?>
					<li class="wppizza-order-pickup"><?php echo $txt['delivery_charges']['lbl'] ?><span><?php echo $txt['free_delivery']['lbl'] ?></span></li>
				<?php } ?>
			<?php } ?>
				<li id="wppizza-cart-total"><?php echo $txt['order_total']['lbl'] ?><span><?php echo $cart['currency'].' '.$cart['order_value']['total']['val']; ?></span></li>

			<?php if(isset($cart['self_pickup_enabled']) &&  $cart['selfPickup']==1){ /*self pickup conditional-> no delivery charges : NEW IN VERSION 1.4.1**/ ?>
				<li id="wppizza-self-pickup"><?php echo $txt['order_page_self_pickup']['lbl'] ?></li>
			<?php } ?>
			</ul>

			<?php if(isset($cart['self_pickup_enabled']) && isset($cart['self_pickup_order_page'])){ /*allow self pickup and display on order page: NEW IN VERSION 1.4.1**/ ?>
				<div class="wppizza-order-pickup-choice">
					<label><input type='checkbox' id='<?php echo $cart['selfPickupId'] ?>' name='wppizza-order-pickup' value='1' <?php checked($cart['selfPickup'],1,true) ?> /><?php echo $cart['order_self_pickup'] ?></label>
				</div>
			<?php } ?>

		<?php }else{ ?>
			<p><?php echo $txt['cart_is_empty']['lbl'] ?></p>
		<?php } ?>
	</fieldset>

	<?php if(count($cart['items'])>0){/*make sure there's stuff to order***/?>
	<fieldset>
		<legend><?php echo $txt['order_form_legend']['lbl'] ?></legend>
		<?php foreach($formelements as $elm){if($elm['enabled']){?>
			<label for="<?php echo $elm['key'] ?>"><?php echo $elm['lbl'] ?><?php echo !empty($elm['required'])?'*':'' ?></label>
			<?php if($elm['type']=='text'){ ?>
				<input id="<?php echo $elm['key'] ?>" name="<?php echo $elm['key'] ?>" type="text" value="<?php echo $elm['key']=='cname' ? $current_user->user_firstname : '' /*NEW IN VERSION 2.0*/ ?> <?php echo $elm['key']=='cname' ? $current_user->user_lastname : '' /*NEW IN VERSION 2.0*/ ?>" <?php echo !empty($elm['required'])?'required':'' ?>/>
			<?php } ?>
			<?php if($elm['type']=='email'){?>
				<input id="<?php echo $elm['key'] ?>" name="<?php echo $elm['key'] ?>" type="email" value="<?php echo $current_user->user_email /*NEW IN VERSION 2.0*/ ?>" <?php echo !empty($elm['required'])?'required':'' ?>/>
			<?php } ?>
			<?php if($elm['type']=='textarea'){?>
				<textarea id="<?php echo $elm['key'] ?>" name="<?php echo $elm['key'] ?>" <?php echo !empty($elm['required'])?'required':'' ?>></textarea>
			<?php } ?>
			<?php if($elm['type']=='select'){?>
				<select id="<?php echo $elm['key'] ?>" name="<?php echo $elm['key'] ?>" <?php echo !empty($elm['required'])?'required':'' ?>>
					<option value="">--------</option>
					<?php foreach($elm['value'] as $a=>$b){?>
					<option value="<?php echo wppizza_validate_string($b) ?>"><?php echo $b ?></option>
					<?php } ?>
				</select>
			<?php } ?>
		<?php }}?>
		<?php
			/*NEW IN VERSION 2.0 => FOR FUTURE USE*/
			do_action('wppizza_gateway_choice_before');
		?>			
		<?php
			/*output the buy now/send order button depending on gateway*/
			/*NEW IN VERSION 2.0 IMPORTANT*/
			do_action('wppizza_choose_gateway');
			/* previously hardcoded submit button deleted*/
		?>
	</fieldset>
		<?php
			/*NEW IN VERSION 2.0 => FOR FUTURE USE*/
			do_action('wppizza_gateway_choice_after');
		?>	
	<?php } ?>
</form>
<?php
	/*NEW IN VERSION 2.0 => FOR FUTURE USE*/
	do_action('wppizza_order_form_after');
?>
