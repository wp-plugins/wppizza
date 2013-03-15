<?php
	function wppizza_output_format_float($str,$type='price'){
		if($type=='price'){$str=sprintf('%01.2f',$str);}
		return $str;
	}
	
	function wppizza_currencies($selected='',$returnValue=null){
		$items['USD']='$';
		$items['GBP']='£';
		$items['EUR']='€';
		$items['CAD']='$';
		$items['CHF']='CHF';
		$items['CRC']='¢';
		if(!$returnValue){
		ksort($items);
	    foreach($items as $key=>$val){
	    	if($key==$selected){$d=' selected="selected"';}else{$d='';}
			$options[]=array('selected'=>''.$d.'','value'=>''.$val.'','id'=>''.$key.'');
	    }}
	    if($selected!='' && $returnValue){
	    	$options=array('key'=>$selected,'val'=>$items[$selected]);	
	    }
		return $options;
	}	
	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
		a bit hackish mind you, but thank's any way to the website
		that provided the original if i find it again, i insert the address here...
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/
	function wppizza_frontendOpeningTimes($options){
		$str='';
		/**group identical opening times**/
		foreach($options['opening_times_standard'] as $k=>$v){			
			if($k==0){$k=7;}/*for sorting reasons , set sunday temporarily to 7 here**/
			if(!isset($times[''.$v['open'].'|'.$v['close'].''])){
				$times[''.$v['open'].'|'.$v['close'].'']=array();
				$times[''.$v['open'].'|'.$v['close'].''][]=$k;
			}else{
				$times[''.$v['open'].'|'.$v['close'].''][]=$k;
			}
		}
		foreach($times as $k=>$arr){
			/*to have sundays last when sorting, set it to 7*/
			asort($arr);
			$grouped[$k]=array('firstday'=>$arr[0],'days'=>$arr,'consecutivedays'=>wpizza_days_concat($arr));
		}
		/**sort by first day in array so we start with a monday regardless**/
		asort($grouped);
		foreach($grouped as $k=>$v){	
			foreach(explode(",",$v['consecutivedays']) as $b=>$c){
				$consec=explode("-",$c);
				$open=explode("|",$k);
				if(count($consec)==2){
					if(($consec[0]+1)==$consec[1]){$seperator=',';}else{$seperator='-';}
					$str.=' '.wpizza_format_weekday($consec[0],'%a').''.$seperator.''.wpizza_format_weekday($consec[1],'%a').'';
				}
				if(count($consec)==1){
					$str.=' '.wpizza_format_weekday($consec[0],'%a').'';
				}
				if($open[0]==$open[1]){	
					$str.=' '.$options['localization']['closed']['lbl'].'';
				}else{
					$str.=' '.ltrim($open[0],0).'-'.ltrim($open[1],0).'';//loose leading zeros
				}
			}
		}
		return trim($str);
	}
	
	/**makes a text representation out of int**/
	function wpizza_format_weekday($int,$format ){	
		/*let's use static timestamps, no need to use the overhead of a function to generate really **/
		$day[1]=946900800;//mon (3rd jan 2000 12:00)
		$day[2]=946987200;//tue (4th jan 2000 12:00)
		$day[3]=947073600;//wed (5th jan 2000 12:00)
		$day[4]=947160000;//thu (6th jan 2000 12:00)
		$day[5]=947246400;//fri (7th jan 2000 12:00)
		$day[6]=947332800;//sat (8th jan 2000 12:00)
		$day[7]=947419200;//sun (9th jan 2000 12:00) if using 7 as sunday
		$day[0]=947419200;//sun (9th jan 2000 12:00) if using 0 as sunday
		
		$wDayFormatted=strftime($format,$day[$int]);
	
		return $wDayFormatted;
	}
function wpizza_days_concat( Array $days ){

    // Define all days of the week, st sun(0) to 7 
    static $all_days = array('1', '2', '3', '4', '5', '6','7');

    // prepare our output
    $output = array();

    // loop through all days of the week
    foreach ( $all_days as $i => $day ){
        // if it is included, 
        if ( in_array( $day, $days ) ){
            $output[] = $day;
        }else{/*if not*/
            $output[] = '#';
        }
    }

    // clean everything up
    $output = implode( '#', $output );
    $output = trim( $output, '#' );

    // two or more consecutive hashes = days that are two or more apart
    $output = preg_split( '/##+/', $output, NULL, PREG_SPLIT_NO_EMPTY );

    // turn consecutive days into dashed days
    foreach ( $output as $i => $value ){
    	$output[ $i ] = preg_replace( '/#(\w+#)*/', '-', $value );
    }
    // format with commas
    $output = implode( ',', $output );

return $output;
}  	

/**are we currntly open ?*/
function wpizza_are_we_open($standard,$custom){
	$currentlyOpen=0;//initialize as closed
	$todayWday=date("w");
	$d=date("d");
	$m=date("m");
	$Y=date("Y");	
	/**make sunday 7 instead of 0 to aid sorting**/
	if($todayWday==0){$yesterdayWday=6;}else{$yesterdayWday=($todayWday-1);}
	/**get the opening times today, as well as the spillover from yesterday
	in case its very early in the morning and we dont close until after midnight on the previous day**/
	$todayTimes=$standard[$todayWday];
	$yesterdayTimes=$standard[$yesterdayWday];
	$todayStartTime	= mktime(0, 0, 0, $m , $d, $Y);
	$todayEndTime	= mktime(23, 59, 59, $m , $d, $Y);
	$todayDate	= ''.$Y.'-'.$m.'-'.$d.'';
	$yesterdayDate	= date("Y-m-d",mktime(12, 0, 0, $m , $d-1, $Y));
	
	/**now we first check if these dates have custom dates opening times****/
	if(count($custom)>0){
		$yesterdayCustom=array_search($yesterdayDate,$custom['date']);
		$todayCustom=array_search($todayDate,$custom['date']);
	}
	/*if we have found dates in custom dates array,make start and end and use these**/
	if(isset($yesterdayCustom) && $yesterdayCustom!==false){		
		$t=wpizza_get_opening_times($custom['open'][$yesterdayCustom],$custom['close'][$yesterdayCustom],$d,$m,$Y,'yesterday');
		if($t){
			$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
		}
	}else{//use times from standard opening times
		$t=wpizza_get_opening_times($standard[$yesterdayWday]['open'],$standard[$yesterdayWday]['close'],$d,$m,$Y,'yesterday');
		if($t){
			$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
		}		
	}
	if(isset($todayCustom) && $todayCustom!==false){
		$t=wpizza_get_opening_times($custom['open'][$todayCustom],$custom['close'][$todayCustom],$d,$m,$Y,'today');
			$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
	}else{//use times from standard opening times
		$t=wpizza_get_opening_times($standard[$todayWday]['open'],$standard[$todayWday]['close'],$d,$m,$Y,'today');
		if($t){
			$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
		}		
	}	
	/********now check if current time is in the $openToday array between start and end***/
	foreach($openToday as $k=>$times){
		if(time() >= $times['start'] && time() <= $times['end']){
			$currentlyOpen=1;
		return $currentlyOpen;	
		}	
	}

	return $currentlyOpen;
	
}

/* takes 01:45, 3:45 format, no seconds as currently not needed**/
function wpizza_get_opening_times($starttime,$endtime,$d,$m,$Y,$day='today'){
 $openingtime=false;//initilize
 $start=explode(':',$starttime);
 $end=explode(':',$endtime);
 /***if both times are the same , we are closed**/
  if($starttime==$endtime) {
	$openingtime=false;
 }
 
 /*for easier comparison, change 00 in endtime hour to 24 if thats the case*/
 if((int)$end[0]==0){$end24=24;}else{$end24=$end[0];}
 /*compare. if start hour>end hour OR starthour==endhour AND startminute>endminute , opening times are crossing midnight**/
 if($start[0]>$end24 || ($start[0]==$end24 && $start[1]>$end[1])) {
 	$openingTimesCrossMidnight=1;
 }
 if(isset($openingTimesCrossMidnight)){
 	if($day=='today'){
 		$openingtime['start']=mktime($start[0],$start[1],0,$m,$d,$Y);
 		$openingtime['end']=mktime(23,59,59,$m,$d,$Y);
 	}
 	if($day=='yesterday'){
 		$openingtime['start']=mktime(0,0,0,$m,$d,$Y);
 		$openingtime['end']=mktime($end[0],$end[1],0,$m,$d,$Y);
 	}
 }else{
  	if($day=='today'){
 		$openingtime['start']=mktime($start[0],$start[1],0,$m,$d,$Y);
 		$openingtime['end']=mktime($end[0],$end[1],0,$m,$d,$Y);
 	}
/*won't happen...well, shouldn't**/
// 	if($day=='yesterday'){
// 		$openingtime['start']=mktime(23,0,0,$m,$d,$Y);
// 		$openingtime['end']=mktime(23,0,0,$m,$d,$Y);
// 	}	
 }

	return $openingtime;
}

/*********************************************************
	[which mealsizes are available]
*********************************************************/
function wppizza_sizes_available($options){
	$availableSizes=array();
	if(is_array($options)){
	foreach($options as $l=>$m){
		foreach($m as $r=>$s){
			$availableSizes[$l]['lbl'][$r]=$options[$l][$r]['lbl'];
			$availableSizes[$l]['price'][$r]=$options[$l][$r]['price'];
		}
	}}
	return $availableSizes;
}
/*********************************************************
	[days]
*********************************************************/
function wppizza_days(){
	$items['1']=__('Mondays', WPPIZZA_LOCALE);
	$items['2']=__('Tuesdays', WPPIZZA_LOCALE);
	$items['3']=__('Wednesdays', WPPIZZA_LOCALE);
	$items['4']=__('Thursdays', WPPIZZA_LOCALE);
	$items['5']=__('Fridays', WPPIZZA_LOCALE);
	$items['6']=__('Saturdays', WPPIZZA_LOCALE);
	$items['0']=__('Sundays', WPPIZZA_LOCALE);

		return $items;
}
/*********************************************************
	[chosen style options]
*********************************************************/
function wppizza_public_styles($selected=''){
	$items['default']=__('Default', WPPIZZA_LOCALE);
    foreach($items as $key=>$val){
    	if($key==$selected){$d=' selected="selected"';}else{$d='';}
		$options[]=array('selected'=>''.$d.'','value'=>''.$val.'','id'=>''.$key.'');
    }
    return $options;
}

/*********************************************************
	[which metabox (sizes,additives) options are being used]
*********************************************************/
function wppizza_options_in_use(){
	$usedAdditives=array();
	$usedSizes=array();
	//get your custom posts ids as an array
	$posts = get_posts(array(
	    'post_type'   => WPPIZZA_SLUG,
	    'fields' => 'ids',
	    'posts_per_page'=>-1
	    )
	);
	//loop over each post
	foreach($posts as $k=>$p){
		//get the meta you need form each post
		$itemMeta=get_post_meta($p,WPPIZZA_SLUG);
		$usedAdditives[] =implode(",",$itemMeta[0]['additives']);
		$usedSizes[] =$itemMeta[0]['sizes'];
	}
	$optionsInUse['sizes']=array_unique($usedSizes);
	$optionsInUse['additives']=array_filter(array_unique(explode(",",implode(",",$usedAdditives))), 'strlen');

	return $optionsInUse;
}

/*********************************************************************************
*
*	[returns an array containing all order data (prices, discounts, currency etc]
*
*********************************************************************************/
function wppizza_order_summary($session,$options,$ajax=null){
	/****************************************************
		[get currency]
	****************************************************/
	$summary['currency']=$options['order']['currency_symbol'];

	/****************************************************
		[get cart items as grouped array]
	****************************************************/
	$cartItems=array();//ini array
	$groupedItems=array();//ini array
	$summary['items']=array();//ini array
	/**lets group items by id and sizes***/
	foreach($session['items'] as $groupid=>$groupitems){
		foreach($groupitems as $v){
			$cartItems[''.$groupid.''][]=array('sortname'=>$v['sortname'],'size'=>$v['size'],'sizename'=>$v['sizename'],'printname'=>$v['printname'],'price'=>$v['price'],'additionalinfo'=>$v['additionalinfo']);
		}
	}	
	foreach($cartItems as $k=>$v){
		$groupedItems[$k]=array('sortname'=>$cartItems[$k][0]['sortname'],'size'=>$cartItems[$k][0]['size'],'sizename'=>$cartItems[$k][0]['sizename'],'printname'=>$cartItems[$k][0]['printname'],'price'=>$cartItems[$k][0]['price'],'count'=>count($cartItems[$k]),'total'=>(count($cartItems[$k])*$cartItems[$k][0]['price']),'additionalinfo'=>$cartItems[$k][0]['additionalinfo'])	;
	}
	asort($groupedItems);
	/**output items sorted by name and size**/
	foreach($groupedItems as $k=>$v){
		$summary['items'][$k]=array('name'=>$v['printname'],'count'=>$v['count'],'size'=>$v['sizename'],'price'=>wppizza_output_format_float($v['price']),'pricetotal'=>wppizza_output_format_float($v['total']),'additionalinfo'=>$v['additionalinfo']);
	}
	/****************************************************
		[if ajax request get items from template to keep formatting consistent]
	****************************************************/
	if(($ajax)){
		$summary['itemsajax'] = do_shortcode('[wppizza type="cart" request="ajax"]');
	}
	/**********************************
		[discounts]
	**********************************/	
	/** no discount**/
		$discountLabel='';
		$discountValue='';
		if($options['order']['discount_selected']=='none'){
				$discountApply=0;
		}	
		/** percentage discount**/
		if($options['order']['discount_selected']=='percentage'){
			/**sort highest to lowest and check if it aplies, if it does, apply and stop loop (only want to appply one!**/
			$discountApply=0;
			/**get most relevant discount to apply to price***/
			rsort($options['order']['discounts']['percentage']['discounts']);
			foreach($options['order']['discounts']['percentage']['discounts'] as $k=>$v){
				if($session['total_price_items']>=$v['min_total']){
					$discountApply=round($session['total_price_items']/100*$v['discount'],2);
				break;
				}
			}
			/**get all available discounts to display***/
			sort($options['order']['discounts']['percentage']['discounts']);
			foreach($options['order']['discounts']['percentage']['discounts'] as $k=>$v){
				if($v['discount']>0){// && $v['min_total']>0
				$summary['pricing_discounts'][]="".$options['localization']['spend']['lbl']." <span>".$options['order']['currency_symbol']."".wppizza_output_format_float($v['min_total'])."</span> ".$options['localization']['save']['lbl']." <span>".($v['discount'])."%</span>";
				}
			}				
			
		}	
		/** value discount**/
		if($options['order']['discount_selected']=='standard'){
			/**sort highest to lowest and check if it aplies, if it does, apply and stop loop (only want to appply one!**/
			$discountApply=0;
			/**get most relevant discount to apply to price***/
			rsort($options['order']['discounts']['standard']['discounts']);
			foreach($options['order']['discounts']['standard']['discounts'] as $k=>$v){
				if($session['total_price_items']>=$v['min_total']){
					$discountApply=$v['discount'];
				break;
				}
			}
			/**get all available discounts to display***/				
			sort($options['order']['discounts']['standard']['discounts']);
			foreach($options['order']['discounts']['standard']['discounts'] as $k=>$v){
				if($v['discount']>0){//&& $v['min_total']>0
					$summary['pricing_discounts'][]="".$options['localization']['spend']['lbl']." <span>".$options['order']['currency_symbol']."".wppizza_output_format_float($v['min_total'])."</span> ".$options['localization']['save']['lbl']." <span>".wppizza_output_format_float($v['discount'])." ".$options['order']['currency_symbol']."</span>";
				}
			}				
		}
		if($discountApply>0){
			$discountLabel=$options['localization']['discount']['lbl'];
			$discountValue=wppizza_output_format_float($discountApply);	
		}

			/**********************************
				[delivery]
			**********************************/
			$deliveryLabel=$options['localization']['free_delivery']['lbl'];//initialize var
			$deliveryCharges='';
			
			if($options['order']['delivery_selected']=='standard'){//standard
				/**delivery settings to display with discount options somewhere*/
				if($options['order']['delivery']['standard']['delivery_charge']>0){
					$deliveryLabel=$options['localization']['delivery_charges']['lbl'];
					$deliveryCharges=wppizza_output_format_float($options['order']['delivery']['standard']['delivery_charge']);
				}
			}
			if($options['order']['delivery_selected']=='minimum_total'){//minimum total
				if($options['order']['delivery']['minimum_total']['deliver_below_total']){
					if($session['total_price_items']<$options['order']['delivery']['minimum_total']['min_total']){	
						$deliveryLabel=$options['localization']['delivery_charges']['lbl'];
						$deliveryCharges=wppizza_output_format_float($options['order']['delivery']['minimum_total']['min_total']-$session['total_price_items']);
					}
				}
				/**delivery settings to display with discount options somewhere*/
				if($options['order']['delivery']['minimum_total']['min_total']>0){
					$summary['pricing_delivery']="".$options['localization']['free_delivery_for_orders_of']['lbl']." <span>".$options['order']['currency_symbol']."".wppizza_output_format_float($options['order']['delivery']['minimum_total']['min_total'])."</span>";
				}else{
					$summary['pricing_delivery']="".$options['localization']['free_delivery']['lbl']."";	
				}				
			}


	
	/****************************************************
		[get total order value]
	****************************************************/
	$totalOrder=$session['total_price_items']-(float)$discountValue+(float)$deliveryCharges;
	$summary['order_value']=array('total_price_items'=>array('lbl'=>$options['localization']['order_items']['lbl'],'val'=>wppizza_output_format_float($session['total_price_items'])),'delivery_charges'=>array('lbl'=>$deliveryLabel,'val'=>$deliveryCharges),'discount'=>array('lbl'=>$discountLabel,'val'=>$discountValue),'total'=>array('lbl'=>$options['localization']['order_total']['lbl'],'val'=>wppizza_output_format_float($totalOrder)));
	//$summary['items_single']=$session['items'];


	/****************************************************
		[check if we are open]
	****************************************************/
	$isOpen=wpizza_are_we_open($options['opening_times_standard'],$options['opening_times_custom']);
	$summary['shopopen']=$isOpen;
	$summary['button']='';
	$summary['nocheckout']='';
	if($isOpen==0){//closed -> display closed in cart element
		$summary['innercartinfo']=$options['localization']['closed']['lbl'];
	}
	if($isOpen==1){//open
		if(count($summary['items'])<=0){//open but nothing in cart -> display 'cart is empty' in cart element
			$summary['innercartinfo']=$options['localization']['cart_is_empty']['lbl'];
		}
		if(count($summary['items'])>0){//open and stuff in cart -> check min value reached in do/dont display button and info
			/*deliver when set to always deliver,  minimum value has been reached, or fixed delivery charges*/
			if(	
				(
				$options['order']['delivery_selected']=='minimum_total' &&
					(
						$options['order']['delivery']['minimum_total']['deliver_below_total'] ||
						(!$options['order']['delivery']['minimum_total']['deliver_below_total'] && $session['total_price_items']>=$options['order']['delivery']['minimum_total']['min_total'])
					)
				
				) || 
				$options['order']['delivery_selected']=='standard'
	
			){
				if($options['order']['orderpage']){//go to page
					//$str['button']=get_page_link($options['order']['orderpage']);						
					$summary['button']='<a href="'.get_page_link($options['order']['orderpage']).'">';
					$summary['button'].='<input class="btn btn-primary" type="button" value="'.$options['localization']['place_your_order']['lbl'].'" />';
					$summary['button'].='</a>';			
				}
				/**too much hassle to make this work on every theme. better to make the user add [wppizza type=orderpage] to a dedicated page.**/
//				else{//open ajax
//					$summary['button']='<input type="button" value="'.$options['localization']['place_your_order']['lbl'].'" class="wppizza-order-page"/>';
//				}
			}else{
			/*minimum order not reached*/
				$summary['nocheckout']=''.$options['localization']['minimum_order']['lbl'].' '.wppizza_output_format_float($options['order']['delivery']['minimum_total']['min_total']).' '.$options['order']['currency_symbol'].'';	
			}
		
		}


	}


return $summary;
}
/***************************************************
	[sort multidimensional array
	[example: wppizza_array_multisort($array, array('sizes'=>SORT_ASC, 'item'=>SORT_ASC)); ]
****************************************************/
function wppizza_array_multisort($array, $cols){
	    $colarr = array();
	    foreach ($cols as $col => $order) {
	        $colarr[$col] = array();
	        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
	    }
	    $eval = 'array_multisort(';
	    foreach ($cols as $col => $order) {
	        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
	    }
	    $eval = substr($eval,0,-1).');';
	    eval($eval);
	    $ret = array();
	    foreach ($colarr as $col => $arr) {
	        foreach ($arr as $k => $v) {
	            $k = substr($k,1);
	            if (!isset($ret[$k])) $ret[$k] = $array[$k];
	            $ret[$k][$col] = $array[$k][$col];
	        }
	    }
	    return $ret;
}
?>