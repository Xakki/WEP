<?php
	defined('WEP') or define('WEP', dirname(__FILE__).'/_wep/');
	defined('WEP_CONFIG') or define('WEP_CONFIG', dirname(__FILE__).'/_wepconf/config/main.php');
	// remove the following lines when in production mode
	defined('WEP_DEBUG') or define('WEP_DEBUG',true);
	// specify how many levels of call stack should be shown in each log message
	defined('WEP_TRACE_LEVEL') or define('WEP_TRACE_LEVEL',2);

	require_once(WEP.'wep.php');