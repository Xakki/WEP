<?php
ini_set("display_errors","0");
if(isset($_GET['url']) and $_GET['url']!=''){
	if(strpos($_GET['url'],'.')===false)
		$url = base64_decode($_GET['url']);
	else
		$url = str_replace("&amp;" , "&", $_GET['url']);
	if(substr($url,0,7)!='http://')
		$url = 'http://'.$url;
}
else
	$url = 'http://'.$_SERVER['HTTP_HOST'];
header('Location: '.$url);