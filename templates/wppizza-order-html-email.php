<?php
/***********************************************

	[general header and opening tags]

************************************************/
$orderHtml.='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;'.get_option('blog_charset').'" />
	</head>
	<body style="margin: 0px; background-color: #FFFFFF;" text="#444444" bgcolor="#FFFFFF" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
		<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" border="0" style="margin:10px 0">
			<tr>
				<td>
					<center>
						<table width="550" cellpadding="0" bgcolor="#F4F3F4" cellspacing="0" align="center" style="border:1px dotted #CECECE" style="padding:2px 15px;text-align:center;background:#21759B;font-size:12px;color:#FFFFFF;font-family:Verdana, Helvetica, Arial, sans-serif">';


/***********************************************

	[add some header]

************************************************/
		$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:30px;text-align:center;background:#21759B;"><h1 style="font-size:160%;color:#FFFFFF;">'.get_bloginfo().'</h1></td></tr>';


/***********************************************

	[add label and date]

************************************************/
		$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:20px 0 0 15px">'.$options['localization']['order_details']['lbl'].'</td></tr>';
		$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:2px 0 0 15px;">'.$nowdate.'</td></tr>';

		/*add devider**/
		$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:0;border-top:1px dotted #CECECE;">&nbsp;</td></tr>';

/**********************************************************

	[customer details loop of all submittecd fields]

***********************************************************/
	foreach($customer_details_array as $k=>$v){
		$orderHtml.=PHP_EOL.'				<tr><td style="padding:2px 15px;">'.$v['label'].'</td><td>'.$v['value'].'<td></tr>';
	}
	/*add devider**/
		$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:0;border-top:1px dotted #CECECE;">&nbsp;</td></tr>';



/**********************************************************

	[order details loop name/price/additional info]

***********************************************************/
	foreach($order_items as $k=>$v){
		$orderHtml.=PHP_EOL.'				<tr><td style="padding:2px 15px">'.$v['label'].'</td><td>'.$v['value'].'</td></tr>';
		/*only print if there's somethiong to print*/
		if($v['additional_info']!=''){
		$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:2px 15px 2px 30px">'.$v['additional_info'].'</td></tr>';
		}
	}
	/*add devider**/
	$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:0;border-top:1px dotted #CECECE;">&nbsp;</td></tr>';

/**********************************************************

	[order summaries - sum of items, discount, delivery, total]

***********************************************************/

		/**cart items summary**/
		$orderHtml.=PHP_EOL.'				<tr><td style="padding:0 5px;">'.$order_summary['cartitems']['label'].'</td><td>'.$order_summary['cartitems']['currency'].' '.$order_summary['cartitems']['price'].'</td></tr>';

		/**if discounts applied**/
		if(isset($order_summary['discount'])){
		$orderHtml.=PHP_EOL.'				<tr><td style="padding:0 5px;">'.$order_summary['discount']['label'].'</td><td>'.$order_summary['discount']['currency'].' '.$order_summary['discount']['price'].'</td></tr>';
		}

		/**delivery costs (if free, price and currency will be empty)**/
		if(isset($order_summary['delivery'])){
		$orderHtml.=PHP_EOL.'				<tr><td style="padding:0 5px;">'.$order_summary['delivery']['label'].'</td><td>'.$order_summary['delivery']['currency'].' '.$order_summary['delivery']['price'].'</td></tr>';
		}

		/*add devider**/
		$orderHtml.=PHP_EOL.'				<tr><td colspan="2" style="padding:0;border-bottom:1px dotted #CECECE;">&nbsp;</td></tr>';

		/**total**/
		$orderHtml.=PHP_EOL.'				<tr><td style="padding:5px;font-weight:600">'.$order_summary['total']['label'].'</td><td style="font-weight:600">'.$order_summary['total']['currency'].' '.$order_summary['total']['price'].'</td></tr>';





/**********************************************************

	[general body table,html closing tags]

***********************************************************/
$orderHtml.='
						</table>
					</center>
				</td>
			</tr>
		</table>
	</body>
</html>';
/**make sure there's NO space/linebreak/whatever after the closing php tag*/
?>