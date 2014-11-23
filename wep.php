<?php

if (!defined('SITE') || !defined('WEP') || !defined('WEPCONF') || !defined('WEP_CONFIG') || !defined('WEP_ADMIN')) {
	die('Not defined constants');
}

if (!isset($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = '';
}

if (!isset($_SERVER['HTTP_PROTO'])) {
    $_SERVER['HTTP_PROTO'] = 'http://';
}

require_once(WEP . 'config/config.php');

//FIX URL
if (isset($_SERVER['REQUEST_URI'])) {
    $REQUEST_URI = preg_replace('/\/+/', '/', $_SERVER['REQUEST_URI']);
    if ($REQUEST_URI != $_SERVER['REQUEST_URI']) {
        static_main::redirect($REQUEST_URI, 301);
    }
}
$temp = strpos($_SERVER['REQUEST_URI'], '?');
$URI = ($temp ? substr($_SERVER['REQUEST_URI'], 0, $temp) : $_SERVER['REQUEST_URI']);
$_REQUEST['pageParam'] = $_GET['pageParam'] = $URI = ltrim($URI, '/');

$_GET['_php'] = '';
//ROUTE
// Тут роутинг для самостоятельных скриптов
	if ($URI == 'robots.txt') {
        if (file_exists(SITE . 'robots.txt'))
            echo file_get_contents(SITE . 'robots.txt');
		elseif (file_exists($_CFG['_PATH']['controllers'] . 'robotstxt.php'))
			require_once($_CFG['_PATH']['controllers'] . 'robotstxt.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/robotstxt.php');
        return true;
    }
    elseif(strpos($URI, '.php')!==false) {
        $php = $_GET['_php'] = mb_substr($URI, 0, -4);
        if ($php == '_redirect') {
            if (file_exists($_CFG['_PATH']['controllers'] . '_redirect.php'))
                require_once($_CFG['_PATH']['controllers'] . '_redirect.php');
            else
                require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/_redirect.php');
            return true;
        }
        elseif ($php == '_captcha') {
            if (file_exists($_CFG['_PATH']['controllers'] . '_captcha.php'))
                require_once($_CFG['_PATH']['controllers'] . '_captcha.php');
            else
                require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/_captcha.php');
            return true;
        }
        /**
         * Загрузка пхп фаилов
         */
        elseif (static_main::phpAllowVendors($php . '.php')) {
            setNeverShowAllInfo();

            if (static_main::phpAllowVendorsSession($php . '.php')) {
                session_go();
            }

            if (static_main::phpAllowVendorsUnregisterAutoload($php . '.php')) {
                static_main::autoload_unregister();
            }

            set_include_path(get_include_path()
                . PATH_SEPARATOR
                . dirname($_SERVER['_DR_'] . $php . '.php'));

            require $_SERVER['_DR_'] . $php . '.php';

            return true;
        }
    }

/////////////////////////////

require_once($_CFG['_PATH']['core'] . 'weperr.php');

if (isset($_SERVER['argv']) and $_SERVER['argv'][1] === 'cron' and $_SERVER['SHELL']) {
	require_once(WEP . 'controllers/cron.php');
    return true;
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