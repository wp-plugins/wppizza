<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
/**********************************************************************************************************************************************************************
*
*
*
*	WPPizza - Print Order Template
*	template used when printing order from admin order history screen
*
*	NOTE: THIS TEMPLATE IS NOT *YET* EDITABLE OTHER THAN USING FILTERS AND ACTIONS (AND EVEN THOSE MIGHT CHANGE YET)
*	(Well, of course it's editable, but you will loose your changes in the next update/s of the plugin)
*	
*	WHY: as it's a new addition I would expect a few  things to change depending on user feedback 
*
*	To support as many printers as possible it is also deliberately using tables as opposed to divs (for now anyway)
*
*
**********************************************************************************************************************************************************************/
/****************************************************************
*
*
*	allow filtering of miscellaneous  variables and css styles
*	through a filter rather than editing this template directly
*
*
*****************************************************************/
	/*************************
		misellaneous variables
		[not really in use yet]
	*************************/
	$vars['title']='';
	/*filter if required*/
	$vars = apply_filters('wppizza_filter_print_order_variables', $vars);
	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add your own title ( might not make too much sense when printing though):
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_variables','myprefix_function_add_title');
		myprefix_function_add_title($vars){
			$vars['title']='my title';
			return $vars;
		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
	/****************
		css
	****************/
	/*globals*/
	$style['global']='html,body,table,tbody,tr,td,th{margin:0;padding:0;font-size:12px;text-align:left}';
	$style['table']='table{width:100%;margin:0 0 10px 0;}';
	$style['th']='th{padding:5px;}';
	$style['td']='td{padding:0 5px;}';
	/*header*/
	$style['header']='#header{margin:0}';
	$style['header_td']='#header #head td{font-size:200%;text-align:center;}';
	$style['address']='#header #address td{white-space:nowrap;font-size:130%;text-align:center;padding-bottom:5px;}';
	/*overview*/
	$style['overview_th']='#overview{margin:0}';
	$style['overview_th']='#overview th{border-top:1px solid;border-bottom:1px solid;font-size:120%;text-align:center}';
	$style['overview_td']='#overview tbody>tr>td{width:50%;white-space:nowrap;}';
	$style['overview_td1']='#overview tbody>tr>td:first-child{text-align:right}';
	$style['overview_td2']='#overview tbody>tr>td:last-child{text-align:left}';

	$style['overview_order_id']='#overview #order_id td{font-size:180%}';
	$style['payment_due']='#overview #payment_due td{font-size:180%}';
	$style['pickup_delivery']='#overview #pickup_delivery td{font-size:180%;text-align:center}';

	/*customer*/
	$style['customer_th']='#customer th{border-top:1px solid;border-bottom:1px solid;white-space:nowrap;font-size:120%;text-align:center}';
	/*items*/
	$style['items_th']='#items th{border-top:1px solid;border-bottom:1px solid;white-space:nowrap;}';
	$style['items_th_widths']='#items th:first-child,#items th:last-child{width:20px}';
	$style['items_tds']='#items .item td{padding-top:5px}';
	$style['items_td_1']='#items .item td:first-child{text-align:center}';
	$style['items_td_2']='#items .item td:last-child{text-align:right}';
	$style['items_divider_hr']='#items tbody > tr.divider > td > hr {border:none;border-top:1px dotted #AAAAAA;}';
	/*summary*/
	$style['summary']='#summary {border-top:1px solid;border-bottom:1px solid;}';
	$style['summary_td']='#summary tbody > tr > td{text-align:right}';
	$style['summary_td_last']='#summary tbody > tr > td:last-child{width:100px}';

	/*filter css if required*/
	$style = apply_filters('wppizza_filter_print_order_css', $style);
	/*implode css for output */
	$style = implode('', $style);

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add your own css:
		in your theme's functions.php:

		add_filter('wppizza_filter_print_order_css','myprefix_function_add_css');
		myprefix_function_add_css($style){

			// *** to add new ***  //
			$style['custom']='// add some custom css declaration //';

			// *** to remove exiting ['table']  for instance ***  //
			unset($style['table']);

			// *** to change exiting  ['table']  for instance ***  //
			$style['table']='table{// use your own css declaration //}';

			return $style;
		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
?>
<?php
/*****************************************************************
*
*
*	[start output ->  doctype/html/title/styles/body etc]
*
*
******************************************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title><?php echo $vars['title'] ?></title>
		<meta http-equiv="Content-Type" content="text/html;<?php echo get_option('blog_charset'); ?>" />
		<style><?php echo $style ?></style>
	</head>
<body>
<?php
/****************************************************************************
*
*	[header: restaurant name and address for example]
*
****************************************************************************/
	/***********************
	*
	*	[create output]
	*
	***********************/
	$hTable['tableOpen']='<table id="header">';

		/*header*/
		$hTable['tableHeader']='';

		/*footer*/
		$hTable['tableFooter']='';

		/*body*/
		$hTable['tableBodyOpen']='<tbody>';
			$hTable['tableBodyHeader']='<tr id="head"><td>'.$txt['header_order_print_header'].'</td></tr>';
			$hTable['tableBodyAddress']='<tr id="address"><td>'.$txt['header_order_print_shop_address'].'</td></tr>';
		$hTable['tableBodyClose']='</tbody>';

	$hTable['tableClose']='</table>';
	/**************************
		allow filtering and
		implode for output
	***************************/
	$hTable = apply_filters('wppizza_filter_print_order_address_output', $hTable, $txt);
	$hTable = implode(PHP_EOL, array_filter($hTable));

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['header']=$hTable;

?>
<?php
/****************************************************************************
*
*
*	[overview of order details: date, transactionId , gateway used etc etc ]
*
*
****************************************************************************/
	/***********************
	*
	*	[create output]
	*
	***********************/
	$oTable['tableOpen']='<table id="overview">';

		/*header*/
		$oTable['tableHeader']='<thead><tr><th colspan="2">'.$orderDetails['order_date']['value'].'</th></thead>';

		/*footer*/
		$oTable['tableFooter']='';

		/*body*/
		$oTable['tableBodyOpen']='<tbody>';

			$oTable['order_id']='<tr id="order_id"><td>'.$orderDetails['order_id']['label'].'</td><td>'.$orderDetails['order_id']['value'].'</td></tr>';
			$oTable['payment_due']='<tr id="payment_due"><td>'.$orderDetails['payment_due']['label'].'</td><td>'.$orderDetails['payment_due']['value'].'</td></tr>';
			$oTable['pickup_delivery']='<tr id="pickup_delivery"><td colspan="2">'.$orderDetails['pickup_delivery']['value'].'</td></tr>';

			$oTable['payment_type']='<tr id="payment_type"><td>'.$orderDetails['payment_type']['label'].'</td><td>'.$orderDetails['payment_type']['value'].'</td></tr>';
			$oTable['payment_method']='<tr id="payment_method"><td>'.$orderDetails['payment_method']['label'].'</td><td>'.$orderDetails['payment_method']['value'].'</td></tr>';
			$oTable['transaction_id']='<tr id="transaction_id"><td>'.$orderDetails['transaction_id']['label'].'</td><td>'.$orderDetails['transaction_id']['value'].'</td></tr>';

		$oTable['tableBodyClose']='</tbody>';

	$oTable['tableClose']='</table>';

	/**************************
		allow filtering and
		implode for output
	***************************/
	$oTable = apply_filters('wppizza_filter_print_order_overview_output', $oTable, $order);
	$oTable = implode(PHP_EOL,$oTable);


	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['overview']=$oTable;
?>
<?php
/****************************************************************************
*
*
*	[customer details: whatever fields where enabled on order page]
*
*
****************************************************************************/
	/***********************
	*
	*	[customer details]
	*
	***********************/
	$customer=array();
	foreach($customerDetails as $key=>$array){
		$customer[$key] ='<tr><td>'.$array['label'].'</td><td>'.$array['value'].'</td></tr>';
	}
	/**allow filtering**/
	$customer = apply_filters('wppizza_filter_print_order_customer', $customer);
	/*implode for output below*/
	$customer = implode(PHP_EOL,$customer);

	/***********************
	*
	*	[create output]
	*
	***********************/
	$cTable['tableOpen']='<table id="customer">';

		/*header*/
		$cTable['tableHeader']='<thead><tr><th colspan="2">'.$txt['header_order_print_customer_label'].'</th></thead>';

		/*footer*/
		$cTable['tableFooter']='';

		/*body*/
		$cTable['tableBodyOpen']='<tbody>';
			$cTable['tableBodyCustomer']=''.$customer.'';
		$cTable['tableBodyClose']='</tbody>';

	$cTable['tableClose']='</table>';

	/**************************
		allow filtering and
		implode for output
	***************************/
	$cTable = apply_filters('wppizza_filter_print_order_customer_output', $cTable, $order);
	$cTable = implode(PHP_EOL,$cTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['customers']=$cTable;
?>
<?php
/****************************************************************************
*
*
*	[order/item details]
*
*
****************************************************************************/
	/***********************
	*
	*	[items]
	*
	***********************/
	$items=array();
	foreach($cartitems as $key=>$array){

		/**construct item <tr> by array to make it more easily filterable**/
		$items[$key]['tropen'] ='<tr class="item">';

			$items[$key]['td1open'] ='<td>';
				$items[$key]['quantity'] =''.$array['quantity'].'';
			$items[$key]['td1close'] ='</td>';

			$items[$key]['td2open'] ='<td>';
				$items[$key]['name'] =''.$array['name'].'';
				$items[$key]['pricesingle'] =' '.$array['value'].'';
			$items[$key]['td2close'] ='</td>';

			$items[$key]['td3open'] ='<td>';
				$items[$key]['pricetotal'] =''.$array['valuetotal'].'';
			$items[$key]['td3close'] ='</td>';

		$items[$key]['trclose'] ='</tr>';

		/**additional info other plugins might add**/
		$items[$key]['addinfo'] ='<tr class="itemaddinfo"><td></td><td>'.$array['addinfo'].'</td><td></td></tr>';

		/**a divider tr /  hr ****/
		$items[$key]['devider'] ='<tr class="divider"><td colspan="3"><hr /></td></tr>';

		/**allow filtering individual item**/
		$items[$key] = apply_filters('wppizza_filter_print_order_single_item', $items[$key]);
		$items[$key] = implode(PHP_EOL,$items[$key]);
	}
	/**allow filtering all items**/
	$items = apply_filters('wppizza_filter_print_order_items', $items);
	/*implode for output below*/
	$items = implode(PHP_EOL,$items);

	/***********************
	*
	*	[create output]
	*
	***********************/
	$iTable['tableOpen']='<table id="items">';

		/*header markup */
		$headerMarkup=array();
		$headerMarkup['tableHeadOpen']='<thead><tr>';
			$headerMarkup['quantity']='<th>'.$txt['header_order_print_itemised_quantity'].'</th>';
			$headerMarkup['arcticle']='<th>'.$txt['header_order_print_itemised_article'].'</th>';
			$headerMarkup['total']='<th>'.$txt['header_order_print_itemised_price'].' '.$orderDetails['currency']['value'].'</th>';
		$headerMarkup['tableHeadClose']='</tr></thead>';
		/**filter if necessary*/
		$headerMarkup = apply_filters('wppizza_filter_print_order_item_header', $headerMarkup, $txt);

		/**add to array markup**/
		$iTable['tableHeader']=implode("",$headerMarkup).'';


		/*footer*/
		$iTable['tableFooter']='';

		/*body*/
		$iTable['tableBodyOpen']='<tbody>';
			$iTable['tableBodyItems']=''.$items.'';
		$iTable['tableBodyClose']='</tbody>';

	$iTable['tableClose']='</table>';
	/**************************
		allow filtering and
		implode for output
	***************************/
	$iTable = apply_filters('wppizza_filter_print_order_items_output', $iTable, $order);
	$iTable = implode(PHP_EOL,$iTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['items']=$iTable;
?>
<?php
/************************************************************************************************
*
*
*	[order summary: price/tax/discount/delivery options etc]
*
*
************************************************************************************************/
	/***********************
	*
	*	[details]
	*
	***********************/
	$summary=array();
	foreach($orderSummary as $key=>$array){
		$summary[$key] ='<tr><td>'.$array['label'].'</td><td>'.$array['value'].'</td></tr>';
		/**allow filtering per line**/
		$summary[$key] = apply_filters('wppizza_filter_print_order_summary_item', $summary[$key]);
	}
	/**allow filtering all items**/
	$summary = apply_filters('wppizza_filter_print_order_items', $summary);
	/*implode for output below*/
	$summary = implode(PHP_EOL,$summary);

	/***********************
	*
	*	[create output]
	*
	***********************/
	$sTable['tableOpen']='<table id="summary">';

		/*header*/
		$sTable['tableHeader']='<thead><tr><th colspan="2"></th></thead>';

		/*footer*/
		$sTable['tableFooter']='';

		/*body*/
		$sTable['tableBodyOpen']='<tbody>';
			$sTable['tableBodySummary']=''.$summary.'';
		$sTable['tableBodyClose']='</tbody>';

	$sTable['tableClose']='</table>';

	/**************************
		allow filtering and
		implode for output
	***************************/
	$sTable = apply_filters('wppizza_filter_print_order_customer_output', $sTable, $order);
	$sTable = implode(PHP_EOL,$sTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['summary']=$sTable;
?>
<?php
/****************************************************************************
*
*
*	[allow filter (to change order of blocks for example)
*	, then implode and actually output]
*
*
****************************************************************************/
	$output=apply_filters('wppizza_filter_print_order_output', $output, $order);
	$output=implode(PHP_EOL,$output);
	echo $output;
?>
<?php
/*****************************************************************
*
*	[end  -> close body/html]
*
******************************************************************/
?>
</body></html>