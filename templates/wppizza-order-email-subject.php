<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/********************************************************************************************************
*
*
*	[WPPizza - Set Email Subject Line]
*	[email subject is made up of 3 parts: subjectPrefix . subject . subjectSuffix. 
*	below are the defaults  used.
*	[copy this file to your theme directory]
*	[overwrite/edit your subject line  as required, delete individual parts to use defaults]
*
*	$this->orderTimestamp : returns date/time according to your wordpress settings.
*	$this->pluginOptions['localization']['your_order']['lbl'] : returns what you have set in your localization options of the plugin
*	$this->currentTime ==  unix timestamp if you want to do something with that instead
*	$this->orderTransactionId : returns transaction id of this order.
*	$this->orderGatewayUsed : returns whichever gateway was used (COD, Paypal, Omnikassa etc etc).
*	$this->orderResults: the whole kitchen sink as object if none of the above is sufficient. use as needed.
********************************************************************************************************/    	
/*
	$this->subjectPrefix 	=	''.get_bloginfo().': ';
	$this->subject 		= 	''.$this->pluginOptions['localization']['your_order']['lbl'].' ';
	$this->subjectSuffix 	=	''.$this->orderTimestamp.'';
*/
?>