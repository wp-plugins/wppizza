var wppizzaClickEvent='click';

jQuery(document).ready(function($){
	/*******************************************************
	*	[detect browser supported events
	*	and use touchstart if click is not supported
	*	avoids double trigger as using 'click touchstart'
	*	appears to trigger twice in Android devices
	*******************************************************/	

	var wppizzaCheckEventSupport = function(eventName){
    	
    	/*iSomething understands click, but doesnt want to do things with it so lets force touchstsrt*/
    	if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPad/i)) || (navigator.userAgent.match(/iPod/i))) {
   			var bindEvent='touchstart';/*default to touchstart for iCrap*/
   			return bindEvent;
		}

    	var el = document.createElement('div');
    	eventName = 'on' + eventName;
    	var isSupported = (eventName in el);
    	if (!isSupported) {
      		el.setAttribute(eventName, 'return;');
      		isSupported = typeof el[eventName] == 'function';
    	}
    	el = null;
    	var bindEvent='click';/*default touchstart*/
    	if(!isSupported){
    		bindEvent='touchstart';	/*if browser does not support touchstart, use click*/
    	}
    	return bindEvent;
  	}
  	wppizzaClickEvent=wppizzaCheckEventSupport("click");


	/*******************************
	*	[keep cart static on page when scrolling]
	*******************************/
    var wppizzaCartStickyElm=$('.wppizza-cart-sticky');
    if(wppizzaCartStickyElm.length>0){
    	$.each(wppizzaCartStickyElm,function(i,v){
	    	var self=$(this);
    		var wppizzaCartOffset = self.offset(); 
    		var wppizzaCartWidth = self.width(); 
    		var wppizzaCartParentWidth = self.parent().width();    
    		$(window).scroll(function () {    
		        var scrollTop = $(window).scrollTop(); 
        		// check the visible top of the browser     
        		if (wppizzaCartOffset.top<scrollTop) {
            		self.width(wppizzaCartWidth).addClass('wppizza-cart-fixed');            		
        		} else {
		            self.width(wppizzaCartWidth).removeClass('wppizza-cart-fixed');   
        		}
    		}); 
	    });
    }

	/*******************************
	*	[add to cart / remove from cart]
	*******************************/
	/**only allow integers in cart increase/decrease**/
	$(document).on('keyup', '.wppizza-cart-incr', function(e){
		this.value = this.value.replace(/[^0-9]/g,'');
		/**when using textbox in cart to incr/decr allow enter as well as clicking on button */
		if(e.keyCode == 13){
			$(this).closest('li').find('.wppizza-cart-increment').trigger(''+wppizzaClickEvent+'');
		}
	})
	

	/**run defined functions after cart refresh**/
	var wppizzaCartRefreshed = (function(functionArray) {
		if(functionArray.length>0){
			for(i=0;i<functionArray.length;i++){
				var func = new Function("term", "return " + functionArray[i] + "(term);");
				func();
			}
		}
	});
	
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-add-to-cart,.wppizza-remove-from-cart,.wppizza-cart-refresh,.wppizza-cart-increment,.wppizza-empty-cart-button', function(e){
		if ($(".wppizza-open").length > 0){//first check if shopping cart exists on page and that we are open
			e.preventDefault();
			e.stopPropagation();

		var self=$(this);
		var selfId=self.attr('id');
		var cartButton=$('.wppizza-cart-button input,.wppizza-cart-button>a,.wppizza-empty-cart-button');
		cartButton.attr("disabled", "true");/*disable place order button to stop trying to order whilst stuff is being added to the cart*/

		var itemCount=1;		
		
		if(self.hasClass('wppizza-add-to-cart')){type='add';}
		if(self.hasClass('wppizza-remove-from-cart')){type='remove';}
		if(self.hasClass('wppizza-empty-cart-button')){type='removeall';selfId=0;}
		if(self.hasClass('wppizza-cart-refresh')){type='refresh';}
		if(self.hasClass('wppizza-cart-increment')){
			var itemCount=self.closest('li').find('.wppizza-cart-incr').val();
			if(itemCount==0){
				type='remove';
			}else{
				type='increment';  
			}
		}
			if(type!='removeall'){
				self.fadeOut(100).fadeIn(400);
			}
			$('.wppizza-order').prepend('<div id="wppizza-loading"></div>');
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':type,'id':selfId,'itemCount':itemCount}}, function(response) {

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
	*	but there's more than one size to choose from, display alert]
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
			 var ArticleId=this.id.split("-");
			ArticleId=ArticleId.splice(2);
			ArticleId = ArticleId.join("-");
			/**make target id*/
			target=$('#wppizza-'+ArticleId+'');
			/*trigger*/
			target.trigger(''+wppizzaClickEvent+'');
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
				var hasClassAjax=false;
				
				if($("input[name='wppizza-gateway']").length>0){
					var elm = $("input[name='wppizza-gateway']");
					if(elm.is(':radio')){
						var selected = $("input[name='wppizza-gateway']:checked");
					}else{
						var selected = elm;
					}
					hasClassAjax=selected.hasClass("wppizzaGwAjaxSubmit");
				}else{
					var selected = $("select[name='wppizza-gateway']");
					hasClassAjax=$("select[name='wppizza-gateway'] option:selected").hasClass("wppizzaGwAjaxSubmit")
				}
				var self=$('#wppizza-send-order');
				var currVal = selected.val();
				var profileUpdate=$("#wppizza_profile_update").is(':checked');
				if(profileUpdate==true){
					jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'profile_update','data':self.serialize()}}, function(response) {
						//console.log(response);
					},'html');
				}
				/**cod->transmit form via ajax if cod or forced by gw settings (i.e $this->gatewayTypeSubmit = 'ajax')*/
				if(currVal=='cod' || hasClassAjax){				
					self.prepend('<div id="wppizza-loading"></div>');
					jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'sendorder','data':self.serialize()}}, function(response) {
						$('#wppizza-send-order #wppizza-loading').remove();
						self.html('<div id="wppizza-order-received">'+response+'</div>');
					},'html').error(function(jqXHR, textStatus, errorThrown) {$('#wppizza-send-order #wppizza-loading').remove();alert("error : " + errorThrown);console.log(jqXHR.responseText);});
					return false;
				}else{
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
	* Let's make IE7 IE8 happy and stop submitting while other stuff is going on such as adding items etc
	*******************************/
	$(document).on(''+wppizzaClickEvent+'', '.wppizza-cart-button>a', function(e){
		e.preventDefault(); e.stopPropagation();
		var attr = $(this).attr('disabled');
		if (typeof attr !== 'undefined' && attr !== false){}else{
        	var url=jQuery(this).attr("href");
	        window.location.href = url;
		}
	})

})