<?php
 exit('Example');
	// Путь к корню сайта
	defined('SITE') or define('SITE', dirname(__FILE__).'/');
	// путь к ядру движка
	defined('WEP') or define('WEP', SITE.'_wep/');
	// путь к папке проекта м конфигами
	defined('WEPCONF') or define('WEPCONF', SITE.'_wepconf/');
	// фаил конфига проекта
	defined('WEP_CONFIG') or define('WEP_CONFIG', WEPCONF.'config/main.php');

    defined('WEP_ADMIN') or define('WEP_ADMIN', '_wepadmin/');

// remove the following lines when in production mode
	defined('WEP_DEBUG') or define('WEP_DEBUG',true);
	// specify how many levels of call stack should be shown in each log message
	defined('WEP_TRACE_LEVEL') or define('WEP_TRACE_LEVEL',2);

	require_once(WEP.'wep.php');