<?php
if (!defined('SITE') || !defined('WEP') || !defined('WEPCONF') || !defined('WEP_CONFIG')) {
	die('Not defined constants');
}


require_once(WEP . 'config/config.php');

//FIX URL
$REQUEST_URI = preg_replace('/\/+/', '/', $_SERVER['REQUEST_URI']);
if ($REQUEST_URI != $_SERVER['REQUEST_URI']) {
	static_main::redirect($REQUEST_URI, 301);
}

if (isset($_GET['_php'])) {
	if ($_GET['_php'] == 'robotstxt') {
		if (file_exists($_CFG['_PATH']['controllers'] . 'robotstxt.php'))
			require_once($_CFG['_PATH']['controllers'] . 'robotstxt.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/robotstxt.php');
		exit();
	}
	elseif (isset($_GET['_php']) and $_GET['_php'] == '_redirect') {
		if (file_exists($_CFG['_PATH']['controllers'] . '_redirect.php'))
			require_once($_CFG['_PATH']['controllers'] . '_redirect.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/_redirect.php');
		exit();
	}
	elseif ($_GET['_php'] == '_captcha') {
		if (file_exists($_CFG['_PATH']['controllers'] . '_captcha.php'))
			require_once($_CFG['_PATH']['controllers'] . '_captcha.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/_captcha.php');
		exit();
	}
	/**
	 * Загрузка пхп фаилов
	 */
	elseif (static_main::phpAllowVendors($_GET['_php'] . '.php')) {
		setNeverShowAllInfo();

		if (static_main::phpAllowVendorsSession($_GET['_php'] . '.php')) {
			session_go();
		}

		if (static_main::phpAllowVendorsUnregisterAutoload($_GET['_php'] . '.php')) {
			static_main::autoload_unregister();
		}

		set_include_path(get_include_path()
			. PATH_SEPARATOR
			. dirname($_SERVER['_DR_'] . $_GET['_php'] . '.php'));

		require $_SERVER['_DR_'] . $_GET['_php'] . '.php';

		return true;
	}
}
/////////////////////////////

require_once($_CFG['_PATH']['core'] . 'weperr.php');

if (isset($_SERVER['argv']) and $_SERVER['argv'][1] === 'cron' and $_SERVER['SHELL']) {
	require_once(WEP . 'controllers/cron.php');
	exit();
}

if (isAjax()) {
	require_once($_CFG['_PATH']['core'] . 'output/ajax.php');
    $WEPOUT = new wepajax();
}
else {
    require_once($_CFG['_PATH']['core'] . 'output/html.php');
    $WEPOUT = new wephtml();
}

require_once($_CFG['_PATH']['core'] . 'transform/transformPHP.php');
require_once($_CFG['_PATH']['core'] . 'transform/transformXSL.php');

require_once($_CFG['_PATH']['wep_controllers'] . 'main.php');