<?php

error_reporting(-1);
ini_set('display_errors', -1);

/*
 * версия ядра
 *  нумерация отличает от других версией
 * 1 - структурной не совместимостью, различия в хранении данных и в исполняемых функциях, вызывающие критические ошибки в коде
 * 2 - добавленн новый функционал, расширен и измененн меющиеся функции -
 * 3 - Номер ревизии , исправленны ошибки
 */
$_CFG['info'] = array(//информация о СМС
	'version' => '2.8.22',
	'email' => 'wep@xakki.ru',
	'icq' => '222392984'
);

/* MAIN_CFG */

$_CFG['sql'] = array(// SQL
	'type' => 'sqlmyi',
	'host' => 'localhost',
	'login' => 'wepmysqluser',
	'password' => 'defaultpass',
	'port' => '3306',
	'database' => 'wepbd',
	'setnames' => 'utf8',
	'dbpref' => '',
	'engine' => 'MyISAM',
	'log' => 0, // логирование запросов в фаил
	'longquery' => 1 // запись в баг запросы которые выполняются дольше указанного времени в сек
);
$_CFG['wep'] = array(// для ядра и админки
	'access' => 1, // 1 - вкл доступ по модулю пользователей, 0 - вкл доступ по дефолтному паролю
	'login' => 'root',
	'password' => 'rootpass',
	'charset' => 'utf-8',
	'setlocale'=>'ru_RU.UTF-8',
	'locale' => 'default',
	'timezone' => 'Europe/Moscow',
	'dateformat' => 'Y-m-d',
	'lang' => 'default',
	'design' => 'default',
	'md5' => 'change_me',
	'def_filesize' => 200,
	'chmod'=> 0777,
	'sessiontype' => 1, //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
	'bug_hunter' => array ( 0 => '0', 1 => '1', 4 => '4', 16 => '16', 64 => '64', 256 => '256', 4096 => '4096', 2 => '2', 32 => '32', 128 => '128', 512 => '512', 2048 => '2048'), // какие ошибки отлавливать
	'catch_bug' => 1, // Системная - укзаывает на элемент в массиве $GLOBALS['_ERR'] в котором отлавливаются ошибки
	'error_reporting' => -1, // заменить на multiselect
	'debugmode' => 2, //0- ничего не показывать обычным юзерам, 1 -паказывать только сообщение что произошла ошибка, 2 - паказать ошибку
	'_showerror'=>'_showerror', // для GET запросов
	'_showallinfo'=>'_showallinfo', // для GET запросов
	'cron'=>array(),// Скоро удалю
);

$_CFG['site'] = array(// для сайта
	'www' => '',
	'rf' => 0, // для рускояз доменов
	'worktime' => false, // 1 - включает отображение страницы "Технический перерыв"
	'work_title' => 'Технический перерыв',
	'work_text' => 'Технический перерыв',
	'redirectPlugin' => 0,
);
$_CFG['memcache'] = array(
	'host' => '127.0.0.1',
	'port' => 11211,
);

/* END_MAIN_CFG */

$_CFG['require_modul'] = array(
	'modulprm' => true,
	'ugroup' => true,
	'bug' => true,
	'mail' => true,
	'pg' => true,
);
$_CFG['singleton'] = array(); // Массив объектов которые не клонируются
$_CFG['hook'] = array(); // События
$_CFG['cron'] = array(); // cron

$_CFG['logs'] = array(
	'sql' => array(),
	'mess' => array(),
); // - массив SQL запросов
$GLOBALS['_ERR'] = array(); //текс ошибок

$_CFG['fileIncludeOption'] = array(); //автоподключение SCRIPT & STYLE
$_CFG['returnFormat'] = 'html';
//json
//''

/* * PATH_CFG* */

/* Полные пути по файловым системам для ядра */
if(!isset($_CFG['_PATH']['wep'])) //если  путь не был задан
	$_CFG['_PATH']['wep'] = dirname(dirname(__FILE__)).'/'; // файл-путь к ядру, Корень админки
if(!isset($_CFG['_PATH']['path']))
	$_CFG['_PATH']['path'] = dirname($_CFG['_PATH']['wep']).'/'; // корень сайта
if(!isset($_CFG['_PATH']['wepconf'])) //если  путь не был задан
	$_CFG['_PATH']['wepconf'] = $_CFG['_PATH']['path'] . '_wepconf/'; // файл-путь  к конфигу

$_SERVER['_DR_'] = $_CFG['_PATH']['path'] = $_CFG['_PATH']['path']; // корень сайта, основной путь к проекту
$_CFG['_PATH']['_path'] = dirname(dirname(dirname(__FILE__))). '/';
$_CFG['_PATH']['core'] = $_CFG['_PATH']['wep'] . 'core/'; // путь к ядру
$_CFG['_PATH']['cdesign'] = $_CFG['_PATH']['wep'] . 'cdesign/'; // backend админки (контролеры и шаблоны)
$_CFG['_PATH']['wep_ext'] = $_CFG['_PATH']['wep'] . 'ext/'; // путь к системным модулям
$_CFG['_PATH']['wep_phpscript'] = $_CFG['_PATH']['wep'] . '_phpscript/';
$_CFG['_PATH']['wep_inc'] = $_CFG['_PATH']['wep'] . 'inc/'; // путь к обработчикам блоков страниц
$_CFG['_PATH']['wep_locallang'] = $_CFG['_PATH']['wep'] . 'locallang/'; // язык
$_CFG['_PATH']['wep_config'] = $_CFG['_PATH']['wep'] . 'config/'; // конфиги
$_CFG['_FILE']['wep_config'] = $_CFG['_PATH']['wep'].'config/config.php';
$_CFG['_FILE']['wep_config_form'] = $_CFG['_PATH']['wep'].'config/config_form.php';

/* пути для файлов пользовательских модулей */
$_CFG['_PATH']['phpscript'] = $_CFG['_PATH']['wepconf'] . '_phpscript/';
$_CFG['_PATH']['inc'] = $_CFG['_PATH']['wepconf'] . 'inc/'; // путь к обработчикам блоков страниц
$_CFG['_PATH']['ext'] = $_CFG['_PATH']['wepconf'] . 'ext/'; // путь к пользовательским модулям
$_CFG['_PATH']['config'] = $_CFG['_PATH']['wepconf'] . 'config/'; // конфиги
$_CFG['_FILE']['config'] = $_CFG['_PATH']['config'].'config.php';
$_CFG['_FILE']['cron'] = $_CFG['_PATH']['config'] . 'configcron.php';
$_CFG['_FILE']['HASH_KEY'] = $_CFG['_PATH']['config'] . 'hash.key';

$_CFG['_PATH']['locallang'] = $_CFG['_PATH']['wepconf'] . 'locallang/'; // язык
$_CFG['_PATH']['cron'] = $_CFG['_PATH']['wepconf'] . 'cron/'; // кроны
$_CFG['_PATH']['weptemp'] = $_CFG['_PATH']['wepconf'] . 'temp/'; // путь к папке для хранения временных файлов
$_CFG['_PATH']['temp'] = $_CFG['_PATH']['path'] . '_content/temp/'; // путь к папке для хранения временных файлов системы
$_CFG['_PATH']['log'] = $_CFG['_PATH']['wepconf'] . 'log/';

/* пути для файлов дизайна страниц */
$_CFG['_PATH']['design'] = $_CFG['_PATH']['path'] . '_design/'; // дизайн сайта
$_CFG['_PATH']['_style'] = $_CFG['_PATH']['path'] . '_design/_style/'; // дизайн стили
$_CFG['_PATH']['_script'] = $_CFG['_PATH']['path'] . '_design/_script/'; // дизайн стили

/* * ************* */
/* $_CFG['PATH'] */
/* * ************* */
// относительные пути
$_CFG['PATH']['WSWG'] = '_wysiwyg/';
$_CFG['PATH']['content'] = '_content/';
$_CFG['PATH']['userfile'] = $_CFG['PATH']['content'].'_userfile/'; // файлы пользователя
$_CFG['PATH']['wepname'] = basename($_CFG['_PATH']['wep']); // базовое имя админки
$_CFG['PATH']['wepconfname'] = basename($_CFG['_PATH']['wepconf']); // базовое имя пользовательских файлов
$_CFG['PATH']['cdesign'] = $_CFG['PATH']['wepname'] . '/cdesign/'; // дизайн админки
$_CFG['FILE']['HASH_KEY'] = $_CFG['PATH']['wepconfname'] . '/config/hash.key';
$_CFG['PATH']['weptemp'] = $_CFG['PATH']['wepconfname'] . '/temp/'; // путь к папке для хранения временных файлов
$_CFG['PATH']['temp'] = $_CFG['PATH']['content'].'temp/'; // путь к папке для хранения временных файлов


/* * *************** */
/* $_CFG['_MASK']** */
/* * *************** */
$_CFG['_MASK'] = array(
	'all' => '',
	'login' => '/[^0-9A-Za-z]/',// Default nomatch
	'name' => '/[^0-9A-Za-zА-ЯЁёа-я\-]/u',
	'name2' => '/[^0-9A-Za-zА-ЯЁёа-я\- ]/u',
	'text' => '/[^\/\(\)\!\+\:\;\?\"\'\`\№\#\,\.0-9A-Za-zЁёА-Яа-я \-\=\_\%\n\r\t\|\*\@\&\$\\\]\[\{\}\>\<]/u',
	'html' => '/[^\/\(\)\!\+\:\;\?\"\'\`\№\#\,\.0-9A-Za-zЁёА-Яа-я \-\=\_\%\n\r\t\|\*\@\&\$\\\]\[\{\}\>\<]/u',
	'int' => '/[^0-9\-]/',
	'float' => '/[^\.0-9]/',
	'alpha' => '/[^A-Za-z]/',
	'alphaint' => '/[^A-Za-z0-9]/',
	'date' => '/[^0-9\.\-\: ]/',
	'phone2' => '/^((([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2})|(\([0-9]{5}\)[0-9]{1}))-[0-9]{2}-[0-9]{2})((, )(([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2}))-[0-9]{2}-[0-9]{2}){0,3}$/',
	'phone' => '/^((([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2}))-[0-9]{2}-[0-9]{2})$/',
	'phone3' => array(
		'eval' => 'static_form::_phoneReplace($value)'
	),
	'email' => array(
		'eval' => 'mb_strtolower($value)', //$value = EVAL;
		'match' => '/^[0-9a-z_\-\.]+@[0-9a-z_\.\-]+\.[a-z]{2,5}$/u',
		'nomatch' => '/[^0-9a-z_\-\.\@]/u',
	//'comment'=>'',
	),
	'www' => array(
		'match' => '/^(http:\/\/)?[0-9A-Za-zЁёА-Яа-я\-\_\.]+\.[A-Za-zЁёА-Яа-я]{2,6}[\/]?$/u',
		'nomatch' => '/[^0-9A-Za-zЁёА-Яа-я\:\/\.\-\_]/u',
		'comment' => 'http://xakki.ru или xakki.ru',
	),
	'wwwq' => array(
		'match' => '/^(http:\/\/)?[0-9A-Za-zЁёА-Яа-я\-\_\.]+\.[A-Za-zЁёА-Яа-я]{2,6}(\/[A-Za-zЁёА-Яа-я\-\_\.\?\&]+)?$/u',
		'nomatch' => '/[^0-9A-Za-zЁёА-Яа-я:\/\.\-\_\=\?\&\#]/u',
	//'comment'=>'',
	),
	'token' => '/{[^}]*}/',);

$_CFG['_repl'] = array(
	'name' => '/[^0-9A-Za-zА-Яа-я\- \,\.@_]+/u',
	'href' => '/(http:\/\/|https:\/\/|www\.)[0-9A-Za-zА-Яа-я\/\.\_\-\=\?\&\;]*/u',
	'alphaint' => '/[^A-Za-z0-9]+/u',);

// WYSIWYG 
$_CFG['ckedit']['toolbar']['Full'] = "'Full'";
$_CFG['ckedit']['toolbar']['Page'] = "[
	['Source','-','Save','NewPage','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Maximize', 'ShowBlocks','-','About'],
	['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
	['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Timestamp','Code'],
	['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	['TextColor','BGColor'],[ 'UIColor' ],
	['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	['Link','Unlink','Anchor'],
	['Styles','Format','Font','FontSize']
]";

$_CFG['ckedit']['toolbar']['Board'] = "[ 
	['PasteText'], ['Undo','Redo','-','RemoveFormat'], ['Bold','Italic','Underline','Superscript'], 
	['NumberedList','BulletedList'], ['JustifyLeft','JustifyCenter','JustifyBlock'] ]";

$_CFG['_imgquality'] = 80; // качество картинки
$_CFG['_imgwater'] = '_design/_img/watermark.png'; //водяной знак

$_CFG['form'] = array(
	'imgFormat' => array('gif' => 1, 'jpg' => 1, 'jpeg' => 1, 'png' => 1),
	'flashFormat' => array('swf' => 1),
	'dateFormat' => 'd-m-Y-H-i-s'
);


if (isset($_POST) && count($_POST) && get_magic_quotes_gpc()) {
	stripSlashesOnArray($_POST);
}

function stripSlashesOnArray(array &$theArray) {
	foreach ($theArray as &$value) {
		if (is_array($value)) {
			stripSlashesOnArray($value);
		} else {
			$value = stripslashes($value);
		}
	}
	unset($value);
	reset($theArray);
}

//ERRORS
$_CFG['_error'] = array(
	0 => array(
		'type' => '[@]',
		'color' => 'black',
		'prior' => 5,
		'debug' => 0
	),
	E_ERROR => array(//1
		'type' => '[Fatal Error]',
		'color' => 'red',
		'prior' => 0,
		'debug' => 0
	),
	E_PARSE => array(//4
		'type' => '[Parse Error]',
		'color' => 'red',
		'prior' => 0,
		'debug' => 0
	),
	E_CORE_ERROR => array(//16
		'type' => '[Fatal Core Error]',
		'color' => 'red',
		'prior' => 0,
		'debug' => 0
	),
	E_COMPILE_ERROR => array(//64
		'type' => '[Compilation Error]',
		'color' => 'red',
		'prior' => 0,
		'debug' => 0
	),
	E_USER_ERROR => array(//256
		'type' => '[Triggered Error]',
		'color' => 'red',
		'prior' => 2,
		'debug' => 1
	),
	E_RECOVERABLE_ERROR => array(//4096
		'type' => '[Catchable Fatal Error]',
		'color' => 'red',
		'prior' => 1,
		'debug' => 1
	),

	E_WARNING => array(//2
		'type' => '[Warning]',
		'color' => '#F18890',
		'prior' => 1,
		'debug' => 1
	),
	E_CORE_WARNING => array(//32
		'type' => '[Core Warning]',
		'color' => '#F18890',
		'prior' => 1,
		'debug' => 1
	),
	E_COMPILE_WARNING => array(//128
		'type' => '[Compilation Warning]',
		'color' => '#F18890',
		'prior' => 1,
		'debug' => 0
	),
	E_USER_WARNING => array(//512
		'type' => '[Triggered Warning]',
		'color' => '#F18890',
		'prior' => 3,
		'debug' => 1
	),

	E_STRICT => array(//2048
		'type' => '[Deprecation Notice]',
		'color' => 'brown',
		'prior' => 4,
		'debug' => 0
	),

	E_NOTICE => array(//8
		'type' => '[Notice]',
		'color' => '#858585',
		'prior' => 5,
		'debug' => 0
	),
	E_USER_NOTICE => array(//1024
		'type' => '[Triggered Notice]',
		'color' => '#858585',
		'prior' => 5,
		'debug' => 0
	),
);

//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
/* INCLUDE USER CONF */
if(file_exists($_CFG['_FILE']['config']))
	include($_CFG['_FILE']['config']);
elseif(!isset($INSTALL)) {
	static_main::redirect('/'.$_CFG['PATH']['wepname'].'/install.php');
}
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////




//Настройка для Nginx
if (isset($_SERVER['HTTP_X_REAL_IP']) and $_SERVER['HTTP_X_REAL_IP'])
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
if (isset($_SERVER['HTTP_X_REAL_PORT']) and $_SERVER['HTTP_X_REAL_PORT'])
	$_SERVER['SERVER_PORT'] = $_SERVER['HTTP_X_REAL_PORT'];

/* http пути */
$port = '';
if (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] != 80)
	$port = ':' . $_SERVER['SERVER_PORT'];
//addpath
$PHP_SELF = explode('/', $_SERVER['PHP_SELF']);
if (!$PHP_SELF[0])
	array_shift($PHP_SELF);
array_pop($PHP_SELF);
$addpath = '';
$k = 0;
while (isset($PHP_SELF[$k]) and $PHP_SELF[$k] != $_CFG['PATH']['wepname']) {
	$addpath .= $PHP_SELF[$k] . '/';
	$k++;
}
/* $_CFG['_HREF'] */
if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = $_CFG['site']['www'];

if (strpos($_SERVER['HTTP_HOST'], 'xn--') !== false) {
	require_once($_CFG['_PATH']['wep_phpscript'] . '/idna_convert.class.php');
	$IDN = new idna_convert();
	$_SERVER['HTTP_HOST'] = $IDN->decode($_SERVER['HTTP_HOST']);
	$_CFG['site']['rf'] = 1;
}
$_CFG['_HREF']['BH'] = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $addpath; // www-путь сайта




$_CFG['_HREF']['wepJS'] = $_CFG['_HREF']['BH'] . $_CFG['PATH']['wepname'] . '/js.php';
$_CFG['_HREF']['siteJS'] = $_CFG['_HREF']['BH'] . '_js.php';
$_CFG['_HREF']['siteAJAX'] = $_CFG['_HREF']['BH'] . '_json.php';
$_CFG['_HREF']['captcha'] = $_CFG['_HREF']['BH'] . '_captcha.php';
$_CFG['_HREF']['WSWG'] = $_CFG['_HREF']['BH'] . $_CFG['PATH']['WSWG'];
$_CFG['_HREF']['_style'] = '_design/_style/'; // дизайн стили
$_CFG['_HREF']['_script'] = '_design/_script/'; // дизайн стили
$_CFG['_HREF']['arrayHOST'] = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

if (strstr($_SERVER['PHP_SELF'], '/' . $_CFG['PATH']['wepname'] . '/'))
	$_CFG['_F']['adminpage'] = true;
else
	$_CFG['_F']['adminpage'] = false;



/* * *************** */
/* $_CFG['session'] */
/* * *************** */

$_CFG['session']['name'] = 'wepID';
$_CFG['session']['path'] = '/';
$_CFG['session']['secure'] = 0;
$_CFG['session']['domain'] = '';
$_CFG['session']['multidomain'] = 0;
$hostcnt = count($_CFG['_HREF']['arrayHOST']);
// никто не будет использовать домен 4го уровня, а значит это IP
if ($hostcnt==1 or ($hostcnt == 4 and ip2long($_SERVER['HTTP_HOST'])!==false) ) { //учитываем localhost и ИПИ
	$_SERVER['HTTP_HOST2'] = $_SERVER['HTTP_HOST'];
	//$_CFG['session']['domain'] = '';//$_SERVER['HTTP_HOST2'];
} else {
	$temp = strpos($_CFG['_HREF']['arrayHOST'][0],':');
	if($temp!==false) {
		$_CFG['_HREF']['arrayHOST'][0] = substr($_CFG['_HREF']['arrayHOST'][0],0,$temp);
	}
	$_SERVER['HTTP_HOST2'] = $_CFG['_HREF']['arrayHOST'][1] . '.' . $_CFG['_HREF']['arrayHOST'][0];
	if ($_CFG['site']['rf'])
		$_CFG['session']['domain'] = $IDN->encode($_SERVER['HTTP_HOST2']);
	else
		$_CFG['session']['domain'] = $_SERVER['HTTP_HOST2'];
}

/* INCLUDE LANG */
include_once($_CFG['_PATH']['wep_locallang'] . $_CFG['wep']['lang'] . '.php');
if (file_exists($_CFG['_PATH']['locallang'] . $_CFG['wep']['lang'] . '.php'))
	include_once($_CFG['_PATH']['locallang'] . $_CFG['wep']['lang'] . '.php');


/* Acept config */

//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors',-1);
error_reporting($_CFG['wep']['error_reporting']);
mb_internal_encoding($_CFG["wep"]["charset"]);
date_default_timezone_set($_CFG['wep']['timezone']);
setlocale(LC_CTYPE, $_CFG['wep']['locale']);
$_CFG['modulinc'] = array(
	0 => array('path' => $_CFG['_PATH']['wep_ext'], 'name' => 'Ядро - '),
	3 => array('path' => $_CFG['_PATH']['ext'], 'name' => 'Плагины - ')
);
$_CFG['time'] = time();
$_CFG['getdate'] = getdate();
$_CFG['remember_expire'] = $_CFG['session']['expire'] = $_CFG['time'] + 1728000; // 20дней ,по умолчанию
if($_CFG['session']['multidomain'])
	$_CFG['session']['domain'] = '.' . $_CFG['session']['domain'];
if($_CFG['wep']['sessiontype']===1)
	$_CFG['require_modul']['session'] = true;
session_name($_CFG['session']['name']);
session_set_cookie_params($_CFG['session']['expire'], $_CFG['session']['path'], $_CFG['session']['domain'], $_CFG['session']['secure']);
ini_set('session.cookie_domain', $_CFG['session']['domain']);
register_shutdown_function('shutdown_function'); // Запускается первым при завершении скрипта

include $_CFG['_PATH']['core'] . 'observer.php';

/*
  Функция завершения работы скрипта
 */

function shutdown_function() {
	observer::notify_observers('shutdown_function');
}

/* SESSION */

function session_go($force=false) { //$force=true - открывает сесиию для не авторизованного пользователя
	if(isset($_SESSION)) return true;
	global $_CFG, $SESSION_GOGO;
	if (!$_CFG['robot'] and (isset($_COOKIE[$_CFG['session']['name']]) or $force)) {
		if($_CFG['wep']['sessiontype'] == 1) {
			if(!$SESSION_GOGO)
				$SESSION_GOGO = new session_class();
			$SESSION_GOGO->start($force);
		}else {
			session_start();
		}
		return true;
	}
	return false;
}

function _setcookie($name, $value='', $expire='', $path='', $domain='', $secure='') {
	global $_CFG;
	if ($expire == '')
		$expire = $_CFG['session']['expire'];
	if ($path == '')
		$path = $_CFG['session']['path'];
	if ($domain == '')
		$domain = $_CFG['session']['domain'];
	if ($secure == '')
		$secure = $_CFG['session']['secure'];
	setcookie($name, $value, $expire, $path, $domain, $secure);
}

/**
 * Инициализация модулей
 */
function _new_class($name, &$MODUL, $force = false) {
	global $_CFG;
	$MODUL = NULL;
	static_main::_prmModulLoad();
	$name = _getExtMod($name);
		
	if(!isset($_CFG['singleton'][$name]) and $_CFG['modulprm'][$name]['pid']) {
		// кастыль: при обращении к дочерним классам , находяться родители и от него дается ссылка на класс.
		_new_class($_CFG['modulprm'][$name]['pid'], $MODUL2);
		$MODUL = $MODUL2->childs[$name];
		return true;
	}

	if (isset($_CFG['singleton'][$name])) {
		$MODUL = $_CFG['singleton'][$name];
		return true;
	}
	else {
		$class_name = $name . "_class";

			if(!class_exists($class_name,false)) {
				if((isset($_CFG['modulprm'][$name]) or $force) and $file = _modulExists($class_name)) {
					// !$_CFG['modulprm'][$name]['pid']) 
					require_once($file);
				}
			}

			if(class_exists($class_name,false)) {
				$getparam = array_slice(func_get_args(), 2);
				$obj = new ReflectionClass($class_name);
				//$pClass = $obj->getParentClass();
				$MODUL = $obj->newInstanceArgs($getparam);
				/* extract($getparam,EXTR_PREFIX_ALL,'param');
				  if(count($getparam)) {
				  $p = '$param'.implode(',$param',array_keys($getparam)).'';
				  } else $p = '';
				  eval('$MODUL = new '.$class_name.'('.$p.');'); */
				if ($MODUL)
					return true;
			}
			elseif (isset($_CFG['modulprm'][$name]) and $_CFG['modulprm'][$name]['pid']) {
				$moduls = array($name);
				while ($_CFG['modulprm'][$name]['pid'])
				{
					$moduls[] = $_CFG['modulprm'][$name]['pid'];
					$name = $_CFG['modulprm'][$name]['pid'];
				}

				$cnt = count($moduls);

				_new_class($moduls[$cnt-1], $MODUL);

				for ($i=$cnt-2; $i>=0; $i--)
				{
					$MODUL = $MODUL->childs[$moduls[$i]];
				}
				return true;
			}
			else
				trigger_error('Can`t init `' . $class_name . '` modul ', E_USER_WARNING);
	}
	return false;
}

function _getChildModul($name, &$MODUL) {
	global $_CFG;

	static_main::_prmModulLoad();
	if (isset($_CFG['modulprm'][$name]['pid']) && $_CFG['modulprm'][$name]['pid'] != '')
	{
		$moduls = array($name);
		while (isset($_CFG['modulprm'][$name]['pid']) && $_CFG['modulprm'][$name]['pid'] != '')
		{
			$moduls[] = $_CFG['modulprm'][$name]['pid'];
			$name = $_CFG['modulprm'][$name]['pid'];
		}

		$cnt = count($moduls);

		_new_class($moduls[$cnt-1], $MODUL);
		for ($i=$cnt-2; $i>=0; $i--)
		{
			$MODUL = $MODUL->childs[$moduls[$i]];
		}
	}
	else
	{
		_new_class($name, $MODUL);
	}
	if ($MODUL)
		return true;
}

function _getExtMod($name) {
	global $_CFG;
	//$this->mf_actctrl
	if (isset($_CFG['modulprm_ext'][$name]) && isset($_CFG['modulprm'][$name]) && !$_CFG['modulprm'][$name]['active'])
		$name = $_CFG['modulprm_ext'][$name][0];
	return $name;
}
/*
  Автозагрузка модулей
 */

function __autoload($class_name) { //автозагрузка модулей
	if ($file = _modulExists($class_name)) {
		require_once($file);
	}
	if(!class_exists($class_name,false))
		trigger_error('Can`t init `'.$class_name.'` modul ', E_USER_WARNING);
		//throw new Exception('Can`t init `' . $class_name . '` modul ');
}

/**
 * Проверка существ модуля
 *
 * Осторожно! Тут хитрая-оптимизированная логика
 * @global array $_CFG
 * @param string $class_name
 * @return string
 */
function _modulExists($class_name) {
	global $_CFG;
	$class_name = explode('_', $class_name);

	if (isset($_CFG['modulprm'][$class_name[0]])) {
		$file = $_CFG['modulprm'][$class_name[0]]['path'];
		if ($file and file_exists($file))
			return $file;
	}
	
	$file = $_CFG['_PATH']['core'] . $class_name[0] . (isset($class_name[1]) ? '.' . $class_name[1] : '') . '.php';
	if (file_exists($file))
		return $file;

	$ret = static_main::includeModulFile($class_name[0]);
	return $ret['file'];
}


	/**
	* Нахождение фаила содержащего класс модуля
	* @Mid - модуль
	*/
	function includeModulFile($Mid, &$OWN=NULL) {
		global $_CFG;
		$Pid = NULL;
		$ret = array('type' => 0, 'path' => '', 'file' => false);
		foreach ($_CFG['modulinc'] as $k => $r) {
			$ret['type'] = $k;
			$ret['path'] = $Mid . '.class/' . $Mid . '.class.php';
			$ret['file'] = $r['path'] . $ret['path'];

			if (is_file($ret['file'])) {
				$ret['path'] = $k . ':' . $ret['path'];
				//include_once($ret['file']);
				return $ret;
			}
			$tempOWN = &$OWN;
			while ($tempOWN and $tempOWN->_cl) {
				$Pid = $tempOWN->_cl;
				$ret['type'] = 5;

				$ret['path'] = $Pid . '.class/' . $Mid . '.childs.php';
				$ret['file'] = $r['path'] . $ret['path'];
				if (is_file($ret['file'])) {
					$ret['path'] = $k . ':' . $ret['path'];
					//include_once($ret['file']);
					return $ret;
				}

				$ret['path'] = $Pid . '.class/' . $Pid . '.childs.php';
				$ret['file'] = $r['path'] . $ret['path'];
				if (is_file($ret['file'])) {
					$ret['path'] = $k . ':' . $ret['path'];
					//include_once($ret['file']);
					return $ret;
				}

				$ret['path'] = $Pid . '.class/' . $Pid . '.class.php';
				$ret['file'] = $r['path'] . $ret['path'];
				if (is_file($ret['file'])) {
					$ret['path'] = $k . ':' . $ret['path'];
					//include_once($ret['file']);
					return $ret;
				}
				$tempOWN = &$tempOWN->owner;
			}
		}
		return array('type' => false, 'path' => false, 'file' => false);
	}


if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

/*
  точное время в милисекундах
 */

function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float) $usec + (float) $sec);
}

/*
  Функция SpiderDetect - принимает $_SERVER['HTTP_USER_AGENT'] и возвращает имя кравлера поисковой системы или false.
 */

function SpiderDetect($USER_AGENT='') {
	if (!$USER_AGENT) {
		if(!isset($_SERVER['HTTP_USER_AGENT'])) {
			return '*';
		}
		$USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
	}
	$engines = array(
		array('Aport', 'Aport robot'),
		array('Google', 'Google'),
		array('msnbot', 'MSN'),
		array('Rambler', 'Rambler'),
		array('Yahoo', 'Yahoo'),
		array('AbachoBOT', 'AbachoBOT'),
		array('accoona', 'Accoona'),
		array('AcoiRobot', 'AcoiRobot'),
		array('ASPSeek', 'ASPSeek'),
		array('CrocCrawler', 'CrocCrawler'),
		array('Dumbot', 'Dumbot'),
		array('FAST-WebCrawler', 'FAST-WebCrawler'),
		array('GeonaBot', 'GeonaBot'),
		array('Gigabot', 'Gigabot'),
		array('Lycos', 'Lycos spider'),
		array('MSRBOT', 'MSRBOT'),
		array('Scooter', 'Altavista robot'),
		array('AltaVista', 'Altavista robot'),
		array('WebAlta', 'WebAlta'),
		array('IDBot', 'ID-Search Bot'),
		array('eStyle', 'eStyle Bot'),
		array('Mail.Ru', 'Mail.Ru Bot'),
		array('Scrubby', 'Scrubby robot'),
		array('Yandex', 'Yandex'),
		array('YaDirectBot', 'Yandex Direct'),
		array('Bot', 'Bot')
	);

	foreach ($engines as $engine) {
		if (stripos($USER_AGENT, $engine[0])!==false) {
			return $engine[1];
		}
	}

	return '';
}

$_CFG['robot'] = SpiderDetect();


/*
  Используем эту ф вместо стандартной, для совместимости с UTF-8
 */
if (function_exists('mb_internal_encoding'))
	mb_internal_encoding($_CFG['wep']['charset']);

function _strlen($val) {
	if (function_exists('mb_strlen'))
		return mb_strlen($val);
	else
		return strlen($val);
}

function _substr($s, $offset, $len = NULL) {
	if (is_null($len)){
		if (function_exists('mb_substr'))
			return mb_substr($s, $offset);
		else
			return substr($s, $offset);
	}
	else {
		if (function_exists('mb_substr'))
			return mb_substr($s, $offset, $len);
		else
			return substr($s, $offset, $len);
	}
}

function _strtolower($txt) {
	if (function_exists('mb_strtolower'))
		return mb_strtolower($txt);
	else
		return strtolower($txt);
}

function _strpos($haystack, $needle, $offset=0) {
	if (function_exists('mb_strpos'))
		return mb_strpos($haystack, $needle, $offset);
	else
		return strpos($haystack, $needle, $offset);
}