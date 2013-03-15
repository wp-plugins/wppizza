jQuery(document).ready(function($){
	/*******************************
	*	[add to cart / remove from cart]
	*******************************/
	if ($(".wppizza-open").length > 0){//first check if shopping cart exists on page and that we are open
		$(document).on('click', '.wppizza-add-to-cart,.wppizza-remove-from-cart,.wppizza-cart-refresh', function(e){
			e.preventDefault();

		var self=$(this);
		if(self.hasClass('wppizza-add-to-cart')){type='add';}
		if(self.hasClass('wppizza-remove-from-cart')){type='remove';}
		if(self.hasClass('wppizza-cart-refresh')){type='refresh';}
			self.fadeOut(100).fadeIn(400);
			$('.wppizza-order').prepend('<div id="wppizza-loading"></div>');
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':type,'id':self.attr('id')}}, function(response) {

				/*show items in cart*/
				$('.wppizza-order').html(response.itemsajax);
				/*button*/
				$('.wppizza-cart-button').html(response.button);
				/*minimum order not reached*/
				$('.wppizza-cart-nocheckout').html(response.nocheckout);
				/*order summary*/
				$('.wppizza-cart-total-items-label').html(response.order_value.total_price_items.lbl);
				$('.wppizza-cart-total-items-value').html(response.order_value.total_price_items.val);
				if(response.nocheckout==''){
					$('.wppizza-cart-discount-label').html(response.order_value.discount.lbl);
					$('.wppizza-cart-discount-value').html(response.order_value.discount.val);
					$('.wppizza-cart-delivery-charges-label').html(response.order_value.delivery_charges.lbl);
					$('.wppizza-cart-delivery-charges-value').html(response.order_value.delivery_charges.val);
					$('.wppizza-cart-total-label').html(response.order_value.total.lbl);
					$('.wppizza-cart-total-value').html(response.order_value.total.val);
				}
				if(response.nocheckout!='' || response.items.length==0){
					$('.wppizza-cart-discount-label').html('');
					$('.wppizza-cart-discount-value').html('');
					$('.wppizza-cart-delivery-charges-label').html('');
					$('.wppizza-cart-delivery-charges-value').html('');
					$('.wppizza-cart-total-label').html('');
					$('.wppizza-cart-total-value').html('');
				}
				if(response.items.length==0){
				$('.wppizza-cart-total-items-label').html('');
				$('.wppizza-cart-total-items-value').html('');
				}
			},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);console.log(jqXHR.responseText);$('.wppizza-order #wppizza-loading').remove();});
		});
	}
	/***********************************************
	*
	*	[if there's a shopping cart on the page
	*	but we are currently closed, display alert]
	*
	***********************************************/
	if ($(".wppizza-open").length == 0 &&  $(".wppizza-cart").length > 0){
		$(document).on('click', '.wppizza-add-to-cart', function(e){
			alert(wppizza.msg.closed);
		});
	}
	/*******************************
	*	[validate and submit order page]
	*******************************/
	$.validator.setDefaults({
		submitHandler: function() {
			var self=$('#wppizza-send-order');
			 self.prepend('<div id="wppizza-loading">"loading"</div>');
			 jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'sendorder','data':self.serialize()}}, function(response) {
				$('#wppizza-send-order #wppizza-loading').remove();
				self.html('<div id="wppizza-order-received">'+response+'</div>');
			},'html').error(function(jqXHR, textStatus, errorThrown) {$('#wppizza-send-order #wppizza-loading').remove();alert("error : " + errorThrown);console.log(jqXHR.responseText);});
		}
	});
	$("#wppizza-send-order").validate();
	
	/******************************
	* set error messages
	*******************************/
	jQuery.extend(jQuery.validator.messages, {
    	required: wppizza.validate_error.required,
    	email: wppizza.validate_error.email,
	});
})