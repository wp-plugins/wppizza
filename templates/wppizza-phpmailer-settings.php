<?
/*************************************************************************************************
*
*	WPPizza PHPMailer Settings
*	[settings to send emails with phpmailer]
*	[if you wish to edit these, copy this file to your template directory 
*	and edit it there so it does not get overwritten in future upgrades]
*
*
**************************************************************************************************/

//$mail->IsSMTP(); // uncomment if you want the class to use SMTP and set accordingly below

try {
	$mail->CharSet = ''.$blogCharset.'';
	/*if you want to use smtp set as required below**/
	/*settings here are for using gmail . adjust as needed **/
	
  	//$mail->Host       = "mail.yourdomain.com"; // SMTP server
  	//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
  	//$mail->SMTPAuth   = true;                  // enable SMTP authentication
  	//$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
  	//$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
  	//$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
  	//$mail->Username   = "yourusername@gmail.com";  // GMAIL username
  	//$mail->Password   = "yourpassword";            // GMAIL password
	
	/**who to send the order to**/
	foreach($options['order']['order_email_to'] as $k=>$v){
		$mail->AddAddress(''.$v.'', '');
	}
	/**any bcc's set in options ?**/
	if(count($options['order']['order_email_bcc'])>0){
		$mail->AddBCC("".implode(",",$options['order']['order_email_bcc'])."");
	}
	
	/**who the order is from. (if given)**/
	if($fromEmails[0]!=''){
		$fromName=wppizza_validate_string($params['cname']);
		if($fromName==''){$fromName='----';}	//if we want, we could set $fromName=get_bloginfo() for example if no name was given...
		$mail->SetFrom(''.$fromEmails[0].'',''.$fromName.'');
		$mail->AddReplyTo(''.$fromEmails[0].'', ''.$fromName.'');
	}
	/**the subject**/
	//$subject prefix defaults to  ''.get_bloginfo().': ';
	$mail->Subject = '' . $subjectPrefix . $subject . '';
	
	
	/**the html**/
	$mail->MsgHTML($orderHtml);
	$mail->AltBody = $order; // optional - MsgHTML will create an alternate automatically
	

	$mail->Send();
	$mailSent='phpmailer';

} catch (phpmailerException $e) {
	$mailError=$e->errorMessage();//Pretty error messages from PHPMailer
} catch (Exception $e) {
	$mailError=$e->getMessage();//Boring error messages from anything else!
}
?>