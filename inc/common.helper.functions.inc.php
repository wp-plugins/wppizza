<?php
/*******************************************************************
*
*	[helper function to make and return a hash and original string to check against ]
*
*******************************************************************/
	function wppizza_mkHash($array){
		$tohash=serialize($array);
		/*try sha256 first if that's an error, use md5*/
		$hash=''.hash("sha256","".AUTH_SALT."".$tohash."".NONCE_SALT."").'';
		if(!$hash || $hash==false || strlen($hash)<64){
		$hash='['.md5("".AUTH_SALT."".$tohash."".NONCE_SALT."").']';	
		}
		$ret['hash']=$hash;
		$ret['order_ini']=$tohash;
	return $ret;
	}

/*******************************************************************
*
*	[always round up - $precision => no of decimals]
*
*******************************************************************/	
function wppizza_round_up ( $value, $precision ) { 
    $pow = pow ( 10, $precision ); 
    return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow; 
} 	
	
	
/******************************* find path to wp-load.php or any other wp file above current directory under document root****************************/
/** this is probably not very useful ever as one will need to find the path before this has been loaded****/
function wppizza_get_wp_config_path($file){
    $base = dirname(__FILE__);
    $base = str_replace("\\", "/", $base);
    $docRoot = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
	$httpRoot= str_replace($docRoot, "", $base);
	$chunkPath=explode("/",$httpRoot);
	$wpbase=$docRoot;
	foreach($chunkPath as $k=>$v){
		$filePath=$wpbase.''.$file.'';
		if(file_exists($filePath)){
			return $filePath;
		}		
	}
}	
?>