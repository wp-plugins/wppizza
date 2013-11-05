var wppizzaClickEvent='click';

jQuery(document).ready(function($){
	/*******************************************************
	*	[detect browser supported events
	*	and use touchstart if click is not supported
	*	avoids double trigger as using 'click touchstart'
	*	appears to trigger twice (at least in jQuery 1.10) 
	*******************************************************/	
	var wppizzaCheckEventSupport = function(eventName){
    	var el = document.createElement('div');
    	eventName = 'on' + eventName;
    	var isSupported = (eventName in el);
    	if (!isSupported) {
      		el.setAttribute(eventName, 'return;');
      		isSupported = typeof el[eventName] == 'function';
    	}
    	el = null;
    	var bindEvent='touchstart';/*default touchstart*/
    	if(!isSupported){
    		bindEvent='click';	/*if browser does not support touchstart, use click*/
    	}
    	return bindEvent;
  	}
  	wppizzaClickEvent=wppizzaCheckEventSupport("touchstart");
	/*******************************
	*	[add to cart / remove from cart]
	*******************************/
	/**only allow integers in cart increase/decrease**/
	$('.wppizza-cart-incr').keyup(function () { 
    	this.value = this.value.replace(/[^0-9]/g,'');
	});
	
	/**run defined functions after cart refresh**/
	var wppizzaCartRefreshed = (function(functionArray) {
		if(functionArray.length>0){
			for(i=0;i<functionArray.length;i++){
				var func = new Function("term", "return " + functionArray[i] + "(term);");
				func();
			}
		}
	});
	
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-add-to-cart,.wppizza-remove-from-cart,.wppizza-cart-refresh,.wppizza-cart-increment', function(e){
		if ($(".wppizza-open").length > 0){//first check if shopping cart exists on page and that we are open
			e.preventDefault();
			e.stopPropagation();

		var self=$(this);
		var cartButton=$('.wppizza-cart-button input,.wppizza-cart-button>a');
		cartButton.attr("disabled", "true");/*disable place order button*/
		var itemCount=1;		
		
		if(self.hasClass('wppizza-add-to-cart')){type='add';}
		if(self.hasClass('wppizza-remove-from-cart')){type='remove';}
		if(self.hasClass('wppizza-cart-refresh')){type='refresh';}
		if(self.hasClass('wppizza-cart-increment')){
			var itemCount=self.closest('li').find('.wppizza-cart-incr').val();
			if(itemCount==0){
				type='remove';
			}else{
				type='increment';  
			}
		}
		
			self.fadeOut(100).fadeIn(400);
			$('.wppizza-order').prepend('<div id="wppizza-loading"></div>');
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':type,'id':self.attr('id'),'itemCount':itemCount}}, function(response) {

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
						$('.wppizza-cart-discount-value').html('<span class="wppizza-minus"></span>'+response.currency+' '+response.order_value.discount.val);
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
					/**tax**/
					$('.wppizza-cart-tax-label').html(response.order_value.item_tax.lbl);
					$('.wppizza-cart-tax-value').html(response.currency+' '+response.order_value.item_tax.val);
					
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
					$('.wppizza-cart-tax-label').html('');
					$('.wppizza-cart-tax-value').html('');
					
				}
				if(response.items.length==0){
				$('.wppizza-cart-total-items-label').html('');
				$('.wppizza-cart-total-items-value').html('');
				}
				
				cartButton.removeAttr("disabled");/*re-enable place order button*/
				
				wppizzaCartRefreshed(wppizza.funcCartRefr);
				
			},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);console.log(jqXHR.responseText);$('.wppizza-order #wppizza-loading').remove();});
		}});

	/***********************************************
	*
	*	[if there's a shopping cart on the page
	*	but we are currently closed, display alert]
	*
	***********************************************/
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-add-to-cart', function(e){
		if ($(".wppizza-open").length == 0 &&  $(".wppizza-cart").length > 0){
			alert(wppizza.msg.closed);
	}});

	/***********************************************
	*
	*	[customer selects self pickup , session gets set via ajax
	*	reload page to reflect delivery charges....
	*	only relevant if there's a shoppingcart or orderpage on page]
	*
	***********************************************/
	$(document).on(''+wppizzaClickEvent+'', '#wppizza-order-pickup-sel,#wppizza-order-pickup-js', function(e){
		if (($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0) || $("#wppizza-send-order").length>0){
			var self=$(this);
			var selfValue=self.is(':checked');
			/*js alert if enabled*/
			if(self.attr('id')=='wppizza-order-pickup-js' && selfValue==true){
				alert(wppizza.msg.pickup);
			}
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'order-pickup','value':selfValue}}, function(response) {
				window.location.href=window.location.href;/*make sure page gest reloaded without confirm*/
			},'text').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);console.log(jqXHR.responseText);});
	}});
	/***********************************************
	*
	*	[if we are trying to add to cart by clicking on the title
	*	but there's mor than one size to choose from, display alert]
	*	[provided  there's a cart on page and we are open]
	***********************************************/
	/*more than one size->choose alert*/
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-trigger-choose', function(e){
		if ($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0){
			alert(wppizza.msg.choosesize);
	}});
	/*only one size, trigger click*/
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-trigger-click', function(e){
		if ($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0){
			/*just loose wppizza-article- from id*/
			var ArticleId=this.id.substr(16);
			/**make target id*/
			target=$('#wppizza-'+ArticleId+'');
			/*trigger*/
			target.trigger('click');
	}});

	/***********************************************
	*
	*	[expand image on hover - one day....]
	*
	***********************************************/

	/*******************************************
	*	[validate and submit order page]
	*	gateway could be either by dropdown,
	*	radio, or if only one, hidden elm
	*******************************************/
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-ordernow', function(e){		
		$("#wppizza-send-order").validate({
			submitHandler: function(form) {
				if($("input[name='wppizza-gateway']").length>0){
					var elm = $("input[name='wppizza-gateway']");
					if(elm.is(':radio')){
						var selected = $("input[name='wppizza-gateway']:checked");
					}else{
						var selected = elm;
					}
				}else{
					var selected = $("select[name='wppizza-gateway']");
				}
				var currVal = selected.val();
	
				/**cod->transmit form via ajax*/
				if(currVal=='cod'){
					var self=$('#wppizza-send-order');
					self.prepend('<div id="wppizza-loading"></div>');
					jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'sendorder','data':self.serialize()}}, function(response) {
						$('#wppizza-send-order #wppizza-loading').remove();
						self.html('<div id="wppizza-order-received">'+response+'</div>');
					},'html').error(function(jqXHR, textStatus, errorThrown) {$('#wppizza-send-order #wppizza-loading').remove();alert("error : " + errorThrown);console.log(jqXHR.responseText);});
					return false;
				}else{
					var self=$('#wppizza-send-order');
					self.prepend('<div id="wppizza-loading" style="opacity:0.8;"></div>');
					form.submit();					
				}
			}
		})
	});
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
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-cart-button>a', function(e){
		e.preventDefault(); e.stopPropagation();
        var url=jQuery(this).attr("href");
        window.location.href = url;
	})

})