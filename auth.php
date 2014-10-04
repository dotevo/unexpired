<?php
ini_set('session.gc_maxlifetime', 60*60*24*30);
session_start();
include ("config.php");

unset($_SESSION['secret']);
if(!isset($_SESSION['secret'])){
	try {
		$oauth = new OAuth(OSM_OAUTH_KEY,OSM_OAUTH_SEC,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
		$request_token_info = $oauth->getRequestToken(OSM_REQURL);
		$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
		header("Location: ".OSM_AUTHURL."?oauth_token=".$request_token_info['oauth_token']);
		exit;
	}
	catch(OAuthException $E) {
		print_r($E);
	}
}else{
	header("Location: ".OSM_AUTHURL."?oauth_token=".$_SESSION['secret']);
	exit;
}
?>
