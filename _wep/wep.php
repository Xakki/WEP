<?php
if(!defined('WEP') || !defined('WEP_CONFIG')) die('Not defined WEP && WEP_CONFIG');
require_once(WEP.'config/config.php');

if(isset($_GET['_php']) and $_GET['_php']=='captcha') {
	if(file_exists($_CFG['_PATH']['wepconf'].'_phpscript/_captcha.php'))
		require_once($_CFG['_PATH']['wepconf'].'_phpscript/_captcha.php');
	else
		require_once($_CFG['_PATH']['wep_controllers'].'frontend/_captcha.php');
	exit();
}

require_once($_CFG['_PATH']['core'].'weperr.php');

if(isset($_SERVER['argv']) and $_SERVER['argv'][1]=='cron' and $_SERVER['SHELL']) 
{
	require_once(WEP.'controllers/cron.php');
}

if(isAjax())
    require_once($_CFG['_PATH']['core'].'output/ajax.php');
else
	require_once($_CFG['_PATH']['core'].'output/html.php');

require_once($_CFG['_PATH']['core'].'transform/transformPHP.php');
require_once($_CFG['_PATH']['core'].'transform/transformXSL.php');

require_once($_CFG['_PATH']['wep_controllers'].'main.php');