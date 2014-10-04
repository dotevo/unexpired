<?php
session_start();
include("config.php");

if(isset($_GET['oauth_token'])&&isset($_SESSION['secret'])){
	$oauth = new OAuth(OSM_OAUTH_KEY,OSM_OAUTH_SEC,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
	$oauth->enableDebug();

	$oauth->setToken($_GET['oauth_token'], $_SESSION['secret']);
	$access_token_info = $oauth->getAccessToken(OSM_ACCURL);
	$_SESSION['token'] = strval($access_token_info['oauth_token']);
	$_SESSION['secret'] = strval($access_token_info['oauth_token_secret']);
	$oauth->setToken($_SESSION['token'], $_SESSION['secret']);
	$oauth->fetch(OSM_APIURL."user/details");
	$user_details = $oauth->getLastResponse();
	$xml = simplexml_load_string($user_details);
	$_SESSION['osm_id'] = strval ($xml->user['id']);
	$_SESSION['osm_user'] = strval($xml->user['display_name']);
	header("Location: success.html");
}
?>
