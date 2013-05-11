<?php
 /*
 	WPPizza Shopping Cart
 */
 /******************************************************
 	to keep things consistent when adding an item via ajax
 	we build the cart contents here to insert below when
 	loading page, or returning when getting via ajax
 *******************************************************/
$cartContents='';
if(isset($cart['innercartinfo'])){
	$cartContents='<p>'.$cart['innercartinfo'].'</p>';	
}else{
	$cartContents='<ul class="wppizza-cart-contents">';
	foreach($cart['items'] as $k=>$item){
		$cartContents.='<li class="wppizza-cart-item">';
		$cartContents.='<span id="wppizza-cart-'.$k.'" class="wppizza-remove-from-cart" title="'.$txt['remove_from_cart']['lbl'].'">x</span>'.$item['count'].'x '.$item['name'].' ';
		if($item['size']!=''){
		$cartContents.='<span class="wppizza-cart-item-size">'.$item['size'].'</span> ';
		}
		$cartContents.='<span class="wppizza-cart-item-price">'.wppizza_output_format_price($item['pricetotal'],$options['layout']['hide_decimals']).' '.$cart['currency'].'</span>';
		if(is_array($item['additionalinfo']) && count($item['additionalinfo'])>0){
			$cartContents.='<div class="wppizza-item-additional-info"><div class="wppizza-item-additional-info-icon"></div><div class="wppizza-item-additional-info-pad">'; 
			foreach($item['additionalinfo'] as $addItem){
				$cartContents.='<span>'.$addItem.'</span>';	
			}
			$cartContents.='</div></div>';
		}
		$cartContents.='</li>';
	}
	$cartContents.='</ul>';	
}
/******************************************************
	[request was made via ajax, when adding item, so only return item html] 
******************************************************/
if(isset($request) && $request=='ajax'){
	echo $cartContents;
return;
}
?>
<?php if(isset($openingTimes)){ ?>
	<div class="wppizza-opening-hours"<?php if(isset($cartStyle['width'])){echo $cartStyle['width'];}?>><?php echo $openingTimes ?></div>
<?php } ?>
<div class="wppizza-cart"<?php if(isset($cartStyle['cart'])){echo $cartStyle['cart'];}?>>
	<?php if($cart['shopopen']==1){ /*make sure that we are open*/ ?>
	<input type='hidden' class='wppizza-open' name='wppizza-open' />
	<?php } ?>
	<div class="wppizza-order">
	<?php echo $cartContents ?>
	</div>
	<div class="wppizza-cart-info">
			<div class="wppizza-cart-nocheckout"><?php echo $cart['nocheckout'] ?></div>
			<span>
				<span class="wppizza-cart-total-items-label">
					<?php if(count($cart['items'])>0){?>
						<?php echo $cart['order_value']['total_price_items']['lbl'] ?>
					<?php } ?>
				</span>
				<span class="wppizza-cart-total-items-value wppizza-cart-info-price">
					<?php if(count($cart['items'])>0){?>
						<?php if($cart['order_value']['total_price_items']['val']>0){echo $cart['currency'];} ?> <?php echo ($cart['order_value']['total_price_items']['val']) ?>
					<?php } ?>
				</span>
			</span>
			<span>
				<span class="wppizza-cart-discount-label">
					<?php if($cart['nocheckout']=='' && count($cart['items'])>0){?>
					<?php echo $cart['order_value']['discount']['lbl'] ?>
					<?php } ?>
				</span>
				<span class="wppizza-cart-discount-value wppizza-cart-info-price">
					<?php if($cart['nocheckout']=='' && count($cart['items'])>0){?>
					<?php if($cart['order_value']['discount']['val']>0){echo $cart['currency'];?> <?php echo ($cart['order_value']['discount']['val']) ?>
					<?php }} ?>
				</span>
			</span>
			<span>
				<span class="wppizza-cart-delivery-charges-label">
					<?php if($cart['nocheckout']=='' && count($cart['items'])>0){?>
					<?php echo $cart['order_value']['delivery_charges']['lbl'] ?>
					<?php } ?>
				</span>
				<span class="wppizza-cart-delivery-charges-value wppizza-cart-info-price">
					<?php if($cart['nocheckout']=='' && count($cart['items'])>0){?>
					<?php if($cart['order_value']['delivery_charges']['val']!=''){echo $cart['currency']; ?> <?php echo ($cart['order_value']['delivery_charges']['val']) ?>
					<?php }} ?>
				</span>
			</span>
			<span class="wppizza-cart-total">
				<span class="wppizza-cart-total-label">
					<?php if($cart['nocheckout']=='' && count($cart['items'])>0){?>
					<?php echo $cart['order_value']['total']['lbl'] ?>
					<?php } ?>
				</span>
				<span class="wppizza-cart-total-value wppizza-cart-info-price">
					<?php if($cart['nocheckout']=='' && count($cart['items'])>0){?>
					<?php if($cart['order_value']['total']['val']>0){echo $cart['currency'];} ?> <?php echo ($cart['order_value']['total']['val']) ?>
					<?php } ?>
				</span>
			</span>
	
		<div class="wppizza-cart-button"><?php echo $cart['button'] ?></div>
	
	</div>
</div>
<?php if(isset($orderinfo)){ ?>
	<ul id="wppizza-orders-info" <?php if(isset($cartStyle['width'])){echo $cartStyle['width'];}?>>	
		<?php if(isset($cart['pricing_discounts'])){foreach($cart['pricing_discounts'] as $discounts){?>
			<li><?php echo $discounts ?></li>
		<?php }}?>
		<?php if(isset($cart['pricing_delivery'])){?>
			<li><?php echo $cart['pricing_delivery'] ?></li>
			<?php if(isset($cart['pricing_delivery_per_item_free'])){?>
			<li><?php echo $cart['pricing_delivery_per_item_free'] ?></li>
			<?php }?>
		<?php }?>
	</ul>
<?php } ?>