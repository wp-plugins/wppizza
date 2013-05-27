jQuery(document).ready(function($){
	/*******************************
	*	[add to cart / remove from cart]
	*******************************/
	if ($(".wppizza-open").length > 0){//first check if shopping cart exists on page and that we are open
		$(document).on('click touchstart', '.wppizza-add-to-cart,.wppizza-remove-from-cart,.wppizza-cart-refresh', function(e){
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
				$('.wppizza-cart-total-items-value').html(response.currency+' '+response.order_value.total_price_items.val);
				if(response.nocheckout==''){
					$('.wppizza-cart-discount-label').html(response.order_value.discount.lbl);
					
					/*addcurrency if discount applies**/
					if(response.order_value.discount.val!=''){
						$('.wppizza-cart-discount-value').html(response.currency+' '+response.order_value.discount.val);
					}else{
						$('.wppizza-cart-discount-value').html(response.order_value.discount.val);	
					}
					$('.wppizza-cart-delivery-charges-label').html(response.order_value.delivery_charges.lbl);
					/*addcurrency if its not free delivery**/
					if(response.order_value.delivery_charges.val!=''){
					$('.wppizza-cart-delivery-charges-value').html(response.currency+' '+response.order_value.delivery_charges.val);
					}else{
					$('.wppizza-cart-delivery-charges-value').html(response.order_value.delivery_charges.val);
					}
					$('.wppizza-cart-total-label').html(response.order_value.total.lbl);
					$('.wppizza-cart-total-value').html(response.currency+' '+response.order_value.total.val);
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
		$(document).on('click touchstart', '.wppizza-add-to-cart', function(e){
			alert(wppizza.msg.closed);
		});
	}
	/***********************************************
	*
	*	[customer selects self pickup , session gets set via ajax
	*	reload page to reflect delivery charges....
	*	only relevant if there's a shoppingcart on page]
	*
	***********************************************/	
	if ($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0){
		$(document).on('click touchstart', '#wppizza-order-pickup-sel,#wppizza-order-pickup-js', function(e){
			var self=$(this);
			var selfValue=self.is(':checked');			
			/*js alert if enabled*/
			if(self.attr('id')=='wppizza-order-pickup-js' && selfValue==true){
				alert(wppizza.msg.pickup);
			}
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'order-pickup','value':selfValue}}, function(response) { 
	
//console.log(response);
				window.location.href=window.location.href;/*make sure page gest reloaded without confirm*/
				
			},'text').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);console.log(jqXHR.responseText);});
		});	
	}
	/***********************************************
	*
	*	[if we are trying to add to cart by clicking on the title
	*	but there's mor than one size to choose from, display alert]
	*	[provided  there's a cart on page and we are open]
	***********************************************/
	if ($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0){
		/*more than one size->choose alert*/
		$(document).on('click touchstart', '.wppizza-trigger-choose', function(e){
			alert(wppizza.msg.choosesize);
		});
		/*only one size, trigger click*/
		$(document).on('click touchstart', '.wppizza-trigger-click', function(e){
			/*just loose wppizza-article- from id*/
			var ArticleId=this.id.substr(16);
			/**make target id*/
			target=$('#wppizza-'+ArticleId+'');
			/*trigger*/
			target.trigger('click');
		});	
	}
	/***********************************************
	*
	*	[expand image on hover]
	*
	***********************************************/

	/*******************************
	*	[validate and submit order page]
	*******************************/
	$.validator.setDefaults({
		submitHandler: function() {
			var self=$('#wppizza-send-order');
			 self.prepend('<div id="wppizza-loading"></div>');
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
    	email: wppizza.validate_error.email
	});

	/******************************
	* Let's make IE7 IE8 happy
	*******************************/
	$(document).on('click touchstart', '.wppizza-cart-button>a', function(e){
		e.preventDefault(); e.stopPropagation();
        var url=jQuery(this).attr("href");
        window.location.href = url;
	})

})