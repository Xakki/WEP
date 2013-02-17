<?php
if(!defined('WEP') || !defined('WEP_CONFIG')) die('Not defined WEP && WEP_CONFIG');
require_once(WEP.'config/config.php');

require_once($_CFG['_PATH']['core'].'weperr.php');

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    require_once($_CFG['_PATH']['core'].'output/ajax.php');
else
	require_once($_CFG['_PATH']['core'].'output/html.php');

require_once($_CFG['_PATH']['core'].'transform/transformPHP.php');

require_once($_CFG['_PATH']['wep_controllers'].'main.php');