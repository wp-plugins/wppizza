<?php
/***********************************************

	[general header and opening tags]

************************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;<?php echo get_option('blog_charset'); ?>" />
	</head>
	<body style="margin: 0px; background-color: #FFFFFF;" text="#444444" bgcolor="#FFFFFF" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
		<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" border="0" style="margin:10px 0">
			<tr>
				<td>
					<center>
						<table width="550" cellpadding="0" bgcolor="#F4F3F4" cellspacing="0" align="center" style="border:1px dotted #CECECE" style="padding:2px 15px;text-align:center;background:#21759B;font-size:12px;color:#FFFFFF;font-family:Verdana, Helvetica, Arial, sans-serif">
<?php
/***********************************************

	[add some header]

************************************************/
?>
							<tr><td colspan="2" style="padding:30px;text-align:center;background:#21759B;"><h1 style="font-size:160%;color:#FFFFFF;"><?php echo get_bloginfo(); ?></h1></td></tr>
<?php
/***********************************************

	[add label, date transactionid, gateway info]

************************************************/
?>
							<tr><td colspan="2" style="padding:20px 0 0 15px"><?php echo $orderLabel['order_details']; ?></td></tr> 
							<tr><td colspan="2" style="padding:2px 0 0 15px;"><?php echo $nowdate; ?></td></tr>
							<tr><td colspan="2" style="padding:2px 0 0 15px;"><?php echo $orderLabel['order_paid_by']; ?> <?php echo $gatewayUsed; ?> (<?php echo $transactionId; ?>)</td></tr>
							<tr><td colspan="2" style="padding:0;border-top:1px dotted #CECECE;">&nbsp;</td></tr><?php /*add devider**/ ?>

<?php
/**********************************************************

	[customer details loop of all submittecd fields]

***********************************************************/
?>
<?php 	foreach($customer_details_array as $k=>$v){ ?>
							<tr><td style="padding:2px 15px;vertical-align:top"><?php echo $v['label']; ?></td><td><?php echo $v['value']; ?><td></tr>
<?php } ?>
							<tr><td colspan="2" style="padding:0;border-top:1px dotted #CECECE;">&nbsp;</td></tr><?php /*add devider**/ ?>

<?php
/**********************************************************

	[order details loop name/price/additional info]

***********************************************************/
?>
<?php 	foreach($order_items as $k=>$v){ ?>
							<tr><td style="padding:2px 15px"><?php echo $v['label']; ?></td><td><?php echo $v['value']; ?></td></tr>
		<?php if($v['additional_info']!=''){ /*only print if there's something to print*/ ?>
							<tr><td colspan="2" style="padding:0px 15px 15px 30px;font-size:90%"><?php echo $v['additional_info']; ?></td></tr>

		<?php } ?>
<?php } ?>
							<tr><td colspan="2" style="padding:0;border-top:1px dotted #CECECE;">&nbsp;</td></tr><?php /*add devider**/ ?>

<?php
/**********************************************************

	[order summaries - sum of items, discount, delivery, total, tax, self pickup if selected]

***********************************************************/
?>
		<?php /**cart items summary**/ ?>
							<tr><td style="padding:0 5px;"><?php echo $order_summary['cartitems']['label']; ?></td><td><?php echo $currency.' '.$order_summary['cartitems']['price']; ?></td></tr>

		<?php /**if discounts applied**/ ?>
		<?php if(isset($order_summary['discount'])){ ?>
							<tr><td style="padding:0 5px;"><?php echo $order_summary['discount']['label']; ?></td><td>-<?php echo $currency.' '.$order_summary['discount']['price']; ?></td></tr>
		<?php } ?>

		<?php /**if items are taxed -> NEW IN VERSION 2.0**/ ?>
		<?php if(isset($order_summary['item_tax'])){ ?>
							<tr><td style="padding:0 5px;"><?php echo $order_summary['item_tax']['label']; ?></td><td><?php echo $currency.' '.$order_summary['item_tax']['price']; ?></td></tr>
		<?php } ?>

		<?php /**delivery costs (if free or self pickup, price and currency will be empty)**/ ?>
		<?php if(isset($order_summary['delivery'])){ ?>
							<tr><td style="padding:0 5px;"><?php echo $order_summary['delivery']['label']; ?></td><td><?php echo $order_summary['delivery']['currency'].' '.$order_summary['delivery']['price']; ?></td></tr>
		<?php } ?>

		<?php /*add devider**/ ?>
							<tr><td colspan="2" style="padding:0;border-bottom:1px dotted #CECECE;">&nbsp;</td></tr>

		<?php /**total**/ ?>
							<tr><td style="padding:5px;font-weight:600"><?php echo $order_summary['total']['label']; ?></td><td style="font-weight:600"><?php echo $currency.' '.$order_summary['total']['price']; ?></td></tr>

		<?php /**self pickup if selected. NEW IN VERSION 1.4.1**/ ?>
		<?php if(isset($order_summary['self_pickup'])){?>
							<tr><td style="padding:10px 5px;font-weight:600;color:#ff3333" colspan="2"><?php echo $order_summary['self_pickup']['label']; ?></td></tr>
		<?php } ?>
		
<?php
/**********************************************************

	[some thank you text or whatever]

***********************************************************/
?>
<?php
if(trim($orderLabel['order_email_footer'])!=''){
?>		
		<?php /*add devider**/ ?>
							<tr><td colspan="2" style="padding:0;border-bottom:1px dotted #CECECE;">&nbsp;</td></tr>		
		<tr><td colspan="2" style="padding:2px 15px; font-size:90%"><?php echo $orderLabel['order_email_footer']; ?></td></tr>
<?php } ?>
<?php
/**********************************************************

	[general body table,html closing tags]

***********************************************************/
?>
						</table>
					</center>
				</td>
			</tr>
		</table>
	</body>
</html>