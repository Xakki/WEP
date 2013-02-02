<?php
//namespace WEP;

/*
 * версия ядра
 *  нумерация отличает от других версией
 * 1 - структурной не совместимостью, различия в хранении данных и в исполняемых функциях, вызывающие критические ошибки в коде
 * 2 - добавленн новый функционал, расширен и измененн меющиеся функции -
 * 3 - Номер ревизии , исправленны ошибки
 */
$_CFG['info'] = array(//информация о СМС
	'version' => '2.15.43',
	'email' => 'wep@xakki.ru',
	'icq' => '222392984'
);

/* MAIN_CFG */

$_CFG['sql'] = array(// SQL
	'type' => 'sqlmyi',
	'host' => 'localhost',
	'login' => '',
	'password' => '',
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
	'login' => 'root', // дефолтны логин (для запуска инициализации сайта)
	'password' => 'root', // дефолтный пароль (для запуска инициализации сайта)
	'charset' => 'utf-8',
	'setlocale'=>'ru_RU.UTF-8',
	'locale' => 'default',
	'timezone' => 'Europe/Moscow',
	'dateformat' => 'd F Yг.',
	'timeformat' => 'd F Yг. H:i:s',
	'lang' => 'default',
	'design' => 'default',
	'md5' => 'change_me',
	'def_filesize' => 200,
	'chmod'=> 0777,
	'sessiontype' => 1, //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
	'bug_hunter' => array ( 0 => '0', 1 => '1', 4 => '4', 16 => '16', 64 => '64', 256 => '256', 4096 => '4096', 2 => '2', 32 => '32', 128 => '128', 512 => '512', 2048 => '2048'), // какие ошибки отлавливать
	'catch_bug' => 1, // Системная - укзаывает на элемент в массиве $GLOBALS['_ERR'] в котором отлавливаются ошибки
	'error_reporting' => -1, // заменить на multiselect
	'debugmode' => 1, //0- ничего не показывать обычным юзерам, 1 -паказывать только сообщение что произошла ошибка, 2 - паказать ошибку
	'_showerror'=>'_showerror', // для GET запросов TODO: генерировать при установке
	'_showallinfo'=>'_showallinfo', // для GET запросов TODO: генерировать при установке
	'guestid'=>3, // TODO: так не пойдет, нужно что нибудь придумать
);

$_CFG['site'] = array(// для сайта
	'www' => '',
	'email' => '',
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
$_CFG['ReflectedClass'] = array(); // cron

$_CFG['logs'] = array(
	'sql' => array(),
	'mess' => array(),
); // - массив SQL запросов
$GLOBALS['_ERR'] = array(); //текс ошибок

$_CFG['fileIncludeOption'] = array(); //автоподключение SCRIPT & STYLE
$_CFG['returnFormat'] = 'html';
//json
//''


$_CFG['header'] = array(
	'modif'=> time(),
	'expires'=> time()-864000,
);

/* * PATH_CFG* */

/* Полные пути по файловым системам для ядра */
if(!isset($_CFG['_PATH']['wep'])) //если  путь не был задан
	$_CFG['_PATH']['wep'] = dirname(dirname(__FILE__)).'/'; // файл-путь к ядру, Корень админки
if(!isset($_CFG['_PATH']['path']))
	$_CFG['_PATH']['path'] = dirname($_CFG['_PATH']['wep']).'/'; // корень сайта
if(!isset($_CFG['_PATH']['wepconf'])) //если  путь не был задан
	$_CFG['_PATH']['wepconf'] = $_CFG['_PATH']['path'] . '_wepconf/'; // файл-путь  к конфигу

$_SERVER['_DR_'] = $_CFG['_PATH']['path']; // корень сайта, основной путь к проекту
$_CFG['_PATH']['_path'] = dirname(dirname(dirname(__FILE__))). '/';
$_CFG['_PATH']['core'] = $_CFG['_PATH']['wep'] . 'core/'; // путь к ядру
$_CFG['_PATH']['cdesign'] = $_CFG['_PATH']['path'] . '_design/'; // backend админки (контролеры и шаблоны)
$_CFG['_PATH']['wep_ext'] = $_CFG['_PATH']['wep'] . 'ext/'; // путь к системным модулям
$_CFG['_PATH']['wep_phpscript'] = $_CFG['_PATH']['wep'] . '_phpscript/';
$_CFG['_PATH']['backend'] = $_CFG['_PATH']['wep'] . '_phpscript/backend/';
$_CFG['_PATH']['frontend'] = $_CFG['_PATH']['wep'] . '_phpscript/frontend/';
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
$_CFG['_PATH']['weptemp'] = $_CFG['_PATH']['wepconf'] . 'temp/'; // путь к папке для хранения временных файлов
$_CFG['_PATH']['temp'] = $_CFG['_PATH']['path'] . '_content/temp/'; // путь к папке для хранения временных файлов системы
$_CFG['_PATH']['content'] = $_CFG['_PATH']['path'] . '_content/'; // путь к папке для хранения  файлов системы
$_CFG['_PATH']['log'] = $_CFG['_PATH']['wepconf'] . 'log/';

/* пути для файлов дизайна страниц */
$_CFG['_PATH']['_design'] = $_CFG['_PATH']['path'] . '_design/'; //  дизайн ядра
$_CFG['_PATH']['_style'] = $_CFG['_PATH']['path'] . '_design/_style/'; // дизайн стили ядра
$_CFG['_PATH']['_script'] = $_CFG['_PATH']['path'] . '_design/_script/'; // дизайн стили ядра

$_CFG['_PATH']['themes'] = $_CFG['_PATH']['path'] . '_themes/'; // дизайн сайта
/* * ************* */
/* $_CFG['PATH'] */
/* * ************* */
// относительные пути
$_CFG['PATH']['admin'] = '_wepadmin/';
$_CFG['PATH']['WSWG'] = '_wysiwyg/';
$_CFG['PATH']['themes'] = '_themes/';
$_CFG['PATH']['content'] = '_content/';
$_CFG['PATH']['userfile'] = $_CFG['PATH']['content'].'_userfile/'; // файлы пользователя
$_CFG['PATH']['wepconfname'] = basename($_CFG['_PATH']['wepconf']); // базовое имя пользовательских файлов
$_CFG['PATH']['cdesign'] = '_design/'; // дизайн админки
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
		'match' => '/^[0-9A-Za-z_\-\.]+@[0-9A-Za-z_\.\-]+\.[A-Za-z]{2,5}$/u',
		'nomatch' => '/[^0-9A-Za-z_\-\.\@]/u',
	//'comment'=>'',
	),
	'www' => array(
		'match' => '/^(http:\/\/)?[0-9A-Za-zЁёА-Яа-я\-\_\.]+\.[A-Za-zЁёА-Яа-я]{2,6}[\/]?$/u',
		'nomatch' => '/[^0-9A-Za-zЁёА-Яа-я\:\/\.\-\_]/u',
		'comment' => 'http://xakki.ru или xakki.ru',
	),
	'wwwq' => array(
		'match' => '/^(http:\/\/)?[0-9A-Za-zЁёА-Яа-я\-\_\.]+\.[A-Za-zЁёА-Яа-я]{2,6}([0-9A-Za-zЁёА-Яа-я\/\-\_\.\?\&]+)?$/u',
		'nomatch' => '/[^0-9A-Za-zЁёА-Яа-я:\/\.\-\_\=\?\&\#]/u',
	//'comment'=>'',
	),
	'token' => '/{[^}]*}/',);

$_CFG['_repl'] = array(
	'name' => '/[^0-9A-Za-zА-Яа-я\- \,\.@_]+/u',
	'href' => '/(http:\/\/|https:\/\/|www\.)[0-9A-Za-zА-Яа-я\/\.\_\-\=\?\&\;]*/u',
	'alphaint' => '/[^A-Za-z0-9]+/u',);

$_CFG['_striptag'] = '<table><td><tr><p><span><center><div><a><b><strong><em><u><i><ul><ol><li><br>';

// WYSIWYG 
$_CFG['ckedit']['toolbar']['Full'] = "'Full'";
$_CFG['ckedit']['toolbar']['Page'] = "[
	['Source','-','Preview','-','Templates'],
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

$_CFG['shutdown_function_flag'] = false;

include $_CFG['_PATH']['core'] . 'static.main.php';

$_CFG['robot'] = SpiderDetect();


//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
/* INCLUDE USER CONF */
$_NEED_INSTALL = false;
if(file_exists($_CFG['_FILE']['config']))
	include($_CFG['_FILE']['config']);
else {
	$_NEED_INSTALL = true;
}
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////




//Настройка для Nginx
if (isset($_SERVER['HTTP_X_REAL_IP']) and $_SERVER['HTTP_X_REAL_IP'])
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
if(!isset($_SERVER['REMOTE_ADDR']))
	$_SERVER['REMOTE_ADDR'] = 'localhost';
if (isset($_SERVER['HTTP_X_REAL_PORT']) and $_SERVER['HTTP_X_REAL_PORT'])
	$_SERVER['SERVER_PORT'] = $_SERVER['HTTP_X_REAL_PORT'];

/* http пути */
$port = '';
if (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] != 80)
	$port = ':' . $_SERVER['SERVER_PORT'];

//addpath
$addpath = '';
/*
$PHP_SELF = explode('/', $_SERVER['PHP_SELF']);
if (!$PHP_SELF[0])
	array_shift($PHP_SELF);
array_pop($PHP_SELF);

$k = 0;
while (isset($PHP_SELF[$k]) and $PHP_SELF[$k] != $_CFG['PATH']['admin']) {
	$addpath .= $PHP_SELF[$k] . '/';
	$k++;
}
*/

$_SERVER['HTTP_PROTO'] = 'http://'; // TODO - определение протокола

/* $_CFG['_HREF'] */
if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = $_CFG['site']['www'];

if (strpos($_SERVER['HTTP_HOST'], 'xn--') !== false) {
	require_once($_CFG['_PATH']['wep_phpscript'] . '/lib/idna_convert.class.php');
	$IDN = new idna_convert();
	$_SERVER['HTTP_HOST'] = $IDN->decode($_SERVER['HTTP_HOST']);
	$_CFG['site']['rf'] = 1;
}
$_CFG['_HREF']['_BH'] = $_SERVER['HTTP_HOST'] . '/' . $addpath; // www-путь сайта
$_CFG['_HREF']['BH'] = $_SERVER['HTTP_PROTO'] . $_CFG['_HREF']['_BH']; 
define('MY_BH', $_CFG['_HREF']['BH']);

if($_CFG['site']['redirectPlugin'])
	$_CFG['require_modul']['redirect'] = true;

$_CFG['PATH']['admin'] = $_CFG['_HREF']['BH'] . $_CFG['PATH']['admin'];
$_CFG['_HREF']['wepJS'] = $_CFG['PATH']['admin'] . 'js.php';
$_CFG['_HREF']['siteJS'] = $_CFG['_HREF']['BH'] . '_js.php';
$_CFG['_HREF']['captcha'] = $_CFG['_HREF']['BH'] . '_captcha.php';
$_CFG['_HREF']['WSWG'] = $_CFG['_HREF']['BH'] . $_CFG['PATH']['WSWG'];
$_CFG['_HREF']['_style'] = '_design/_style/'; // дизайн стили
$_CFG['_HREF']['_script'] = '_design/_script/'; // дизайн стили
$_CFG['_HREF']['arrayHOST'] = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

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

setlocale(LC_ALL, $_CFG['wep']['locale']);
//setlocale(LC_ALL, 'ru_RU.UTF-8', 'rus_RUS.UTF-8', 'Russian_Russia.UTF-8');

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


if(!isset($_COOKIE['wep123456'])) {
	if(!isset($_SERVER['HTTP_REFERER']))
		$_SERVER['HTTP_REFERER'] = '';
	_setcookie('wep123456',base64encode($_SERVER['HTTP_REFERER']),(time() + 86400));
}
