<?php
//namespace WEP;
if (!defined('SITE') || !defined('WEP') || !defined('WEPCONF') || !defined('WEP_CONFIG')) {
    die('Not defined constants');
}

/*
 * версия ядра
 *  нумерация отличает от других версией
 * 1 - структурной не совместимостью, различия в хранении данных и в исполняемых функциях, вызывающие критические ошибки в коде
 * 2 - добавленн новый функционал, расширен и измененн меющиеся функции -
 * 3 - Номер ревизии , исправленны ошибки
 */
$_CFG['info'] = array( //информация о СМС
    'version' => '2.18.46',
    'email' => 'wep@xakki.ru',
    'icq' => '222392984'
);

/* MAIN_CFG */

$_CFG['sql'] = array( // SQL
    'type' => 'sqlmyi',
    'host' => '127.0.0.1',
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

$_CFG['wep'] = array( // для ядра и админки
    'access' => 1, // 1 - вкл доступ по модулю пользователей, 0 - вкл доступ по дефолтному паролю
    'login' => 'root', // дефолтны логин (для запуска инициализации сайта)
    'password' => 'root', // дефолтный пароль (для запуска инициализации сайта)
    'charset' => 'utf-8',
    'setlocale' => 'ru_RU.UTF-8',
    'locale' => 'default',
    'timezone' => 'Europe/Moscow',
    'dateformat' => 'd F Yг.',
    'timeformat' => 'd F Yг. H:i:s',
    'lang' => 'default',
    'design' => 'default',
    'md5' => 'change_me',
    'def_filesize' => 200,
    'chmod' => 0777,
    'sessiontype' => 1, //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
    'bug_hunter' => array(0 => '0', 1 => '1', 4 => '4', 16 => '16', 64 => '64', 256 => '256', 4096 => '4096', 2 => '2', 32 => '32', 128 => '128', 512 => '512', 2048 => '2048'), // какие ошибки отлавливать
    'catch_bug' => 1, // Системная - укзаывает на элемент в массиве $GLOBALS['_ERR'] в котором отлавливаются ошибки
    'error_reporting' => -1, // заменить на multiselect
    'debugmode' => 1, //0- ничего не показывать обычным юзерам, 1 -паказывать только сообщение что произошла ошибка, 2 - паказать ошибку
    '_showerror' => '_showerror', // для GET запросов TODO: генерировать при установке
    '_showallinfo' => '_showallinfo', // для GET запросов TODO: генерировать при установке
    'guestid' => 3, // TODO: так не пойдет, нужно что нибудь придумать
    'filedivider' => 10000,
);

$_CFG['site'] = array( // для сайта
    'www' => '',
    'email' => '',
    'rf' => 0, // для рускояз доменов
    'worktime' => false, // 1 - включает отображение страницы "Технический перерыв"
    'work_title' => 'Технический перерыв',
    'work_text' => 'Технический перерыв',
    'redirectPlugin' => 0,
    'theme' => 'default',
    'template' => 'default',
    'production' => false,
    'origin' => '',
    'usecdn' => true,
    'cdn' => array(
        'jquery' => '//yastatic.net/jquery/2.2.3/jquery.min.js',
        'script.jquery/jquery-ui' => '//yastatic.net/jquery-ui/1.11.2/jquery-ui.min.js',
        'highlight' => '//yandex.st/highlightjs/7.4/highlight.min.js',
        'bootstrap' => '//yastatic.net/bootstrap/3.3.6/js/bootstrap.min.js',
        'bootstrap.css' => '//yastatic.net/bootstrap/3.3.6/css/bootstrap.min.css',
        'style.jquery/smoothness/jquery-ui' => '//yandex.st/jquery-ui/1.11.2/themes/smoothness/jquery-ui.min.css',
    ),
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
    'sql' => [],
    'content' => [],
    'sqlTime' => 0,
    'mess' => [],
); // - массив SQL запросов
$GLOBALS['_ERR'] = array(); //текс ошибок

$_CFG['allowAutoIncludeCss'] = true; // Разрешаем автоподключение стилей
$_CFG['allowAutoIncludeScript'] = true; // Разрешаем автоподключение скриптов
//json
//''

$_CFG['header'] = array(
    'modif' => time(),
    'expires' => 0,
);

/* * PATH_CFG* */
// Локальные пути
$_SERVER['_DR_'] = SITE; // корень сайта, основной путь к проекту
//$_CFG['_PATH']['_path'] = dirname(dirname(dirname(__FILE__))). '/';
$_CFG['_PATH']['core'] = WEP . 'core/'; // путь к ядру
$_CFG['_PATH']['cdesign'] = SITE . '_design/'; // backend админки (шаблоны, скрипты, стили)
$_CFG['_PATH']['wep_ext'] = WEP . 'ext/'; // путь к системным модулям
$_CFG['_PATH']['wep_controllers'] = WEP . 'controllers/';
$_CFG['_PATH']['backend'] = WEP . 'controllers/backend/';
$_CFG['_PATH']['frontend'] = WEP . 'controllers/frontend/';
$_CFG['_PATH']['wep_inc'] = WEP . 'inc/'; // путь к обработчикам блоков страниц
$_CFG['_PATH']['wep_locallang'] = WEP . 'locallang/'; // язык
$_CFG['_PATH']['wep_config'] = WEP . 'config/'; // конфиги
$_CFG['_FILE']['wep_config'] = WEP . 'config/config.php';
$_CFG['_FILE']['wep_config_form'] = WEP . 'config/config_form.php';
$_CFG['_PATH']['wep_update'] = WEP . 'update/'; // папка с общими обновлениями

$_CFG['_PATH']['vendors'] = SITE . '_vendors/';

/* пути для файлов пользовательских модулей */
// WEP_CONFIG - конфиг 
$_CFG['_PATH']['configDir'] = dirname(WEP_CONFIG) . '/'; // конфиги
$_CFG['_PATH']['controllers'] = WEPCONF . 'controllers/';
$_CFG['_PATH']['inc'] = WEPCONF . 'inc/'; // путь к обработчикам блоков страниц
$_CFG['_PATH']['ext'] = WEPCONF . 'ext/'; // путь к пользовательским модулям
$_CFG['_PATH']['locallang'] = WEPCONF . 'locallang/'; // язык
$_CFG['_PATH']['weptemp'] = WEPCONF . 'temp/'; // путь к папке для хранения временных файлов
$_CFG['_PATH']['log'] = WEPCONF . 'log/';
$_CFG['_PATH']['update'] = WEPCONF . 'update/'; // папка с общими обновлениями

$_CFG['_FILE']['cron'] = $_CFG['_PATH']['configDir'] . 'configcron.php';
$_CFG['_FILE']['cronTask'] = $_CFG['_PATH']['configDir'] . 'cron.ini';
$_CFG['_FILE']['HASH_KEY'] = $_CFG['_PATH']['configDir'] . 'hash.key';

$_CFG['_PATH']['temp'] = SITE . '_content/temp/'; // путь к папке для хранения временных файлов системы
$_CFG['_PATH']['content'] = SITE . '_content/'; // путь к папке для хранения  файлов системы

/* пути для файлов дизайна страниц */
$_CFG['_PATH']['_design'] = SITE . '_design/'; //  дизайн ядра
$_CFG['_PATH']['_style'] = SITE . '_design/_style/'; // дизайн стили ядра
$_CFG['_PATH']['_script'] = SITE . '_design/_script/'; // дизайн стили ядра
$_CFG['_PATH']['themes'] = SITE . '_themes/'; // дизайн сайта
/* * ************* */
/* $_CFG['PATH'] */
/* * ************* */
// относительные пути
$_CFG['PATH']['vendors'] = '_vendors/';
$_CFG['PATH']['themes'] = '_themes/';
$_CFG['PATH']['content'] = '_content/';
$_CFG['PATH']['userfile'] = $_CFG['PATH']['content'] . '_userfile/'; // файлы пользователя
$_CFG['PATH']['wepconfname'] = basename(WEPCONF); // базовое имя пользовательских файлов
$_CFG['PATH']['cdesign'] = '_design/'; // дизайн админки
$_CFG['PATH']['weptemp'] = $_CFG['PATH']['wepconfname'] . '/temp/'; // путь к папке для хранения временных файлов
$_CFG['PATH']['temp'] = $_CFG['PATH']['content'] . 'temp/'; // путь к папке для хранения временных файлов


/* * *************** */
/* $_CFG['_MASK']** */
/* * *************** */
$_CFG['_MASK'] = array(
    'all' => '',
    'login' => '/[^0-9A-Za-z]/', // Default nomatch
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
        'eval' => 'static_form::_phoneReplace($value);'
    ),
    'email' => array(
        'eval' => 'mb_strtolower($value);', //$value = EVAL;
        'match' => '/^[0-9A-Za-z_\-\.]+@[0-9A-Za-z_\.\-]+\.[A-Za-z]{2,5}$/u',
        'nomatch' => '/[^0-9A-Za-z_\-\.\@]/u',
        //'comment'=>'',
    ),
    'www' => array(
        'match' => '/^((http:|https:)?\/\/)?[0-9A-Za-zЁёА-Яа-я\-\_\.]+\.[A-Za-zЁёА-Яа-я]{2,6}[\/]?$/u',
        'nomatch' => '/[^0-9A-Za-zЁёА-Яа-я\:\/\.\-\_]/u',
        'comment' => 'http://xakki.ru или xakki.ru',
    ),
    'wwwq' => array(
        'match' => '/^((http:|https:)?\/\/)?[0-9A-Za-zЁёА-Яа-я\-\_\.]+\.[A-Za-zЁёА-Яа-я]{2,6}([0-9A-Za-zЁёА-Яа-я\/\-\_\.\?\&]+)?$/u',
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
	['Source','Maximize','Preview','Templates'],
	['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
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

$_CFG['_imgquality'] = 85; // качество картинки
$_CFG['_imgwater'] = '_design/_img/watermark.png'; //водяной знак

$_CFG['form'] = array(
    'imgFormat' => array('gif' => 1, 'jpg' => 1, 'jpeg' => 1, 'png' => 1),
    'flashFormat' => array('swf' => 1),
    'dateFormat' => 'd-m-Y-H-i-s'
);


$_CFG['vendors'] = array(
    'ckfinder/core/connector/php/connector.php' => array(
        'session' => true,
        //'unregisterAutoload' => true,
    ),
);

// Вид списка формы с множественной выборкой
define('FORM_MULTIPLE_SIMPLE', 1);
define('FORM_MULTIPLE_JQUERY', 2);
define('FORM_MULTIPLE_KEY', 3);

define('FORM_STATUS_DEFAULT', 0);
define('FORM_STATUS_ERROR', -1);
define('FORM_STATUS_OK', 1);

// Source include position
define('POS_END', 0);
define('POS_BEGIN', 1);

define('QUOTES', '"');


define('SITE_MAP_LIMIT', 45000);

define('E_EXCEPTION_ERROR', 16384);
//ERRORS
$_CFG['_error'] = array(
    0 => array(
        'type' => '[@]',
        'color' => 'black',
        'prior' => 5,
        'debug' => 0
    ),
    E_ERROR => array( //1
        'type' => '[Fatal Error]',
        'color' => 'red',
        'prior' => 0,
        'debug' => 0
    ),
    E_PARSE => array( //4
        'type' => '[Parse Error]',
        'color' => 'red',
        'prior' => 0,
        'debug' => 0
    ),
    E_CORE_ERROR => array( //16
        'type' => '[Fatal Core Error]',
        'color' => 'red',
        'prior' => 0,
        'debug' => 0
    ),
    E_COMPILE_ERROR => array( //64
        'type' => '[Compilation Error]',
        'color' => 'red',
        'prior' => 0,
        'debug' => 0
    ),
    E_USER_ERROR => array( //256
        'type' => '[Triggered Error]',
        'color' => 'red',
        'prior' => 2,
        'debug' => 1
    ),
    E_RECOVERABLE_ERROR => array( //4096
        'type' => '[Catchable Fatal Error]',
        'color' => 'red',
        'prior' => 1,
        'debug' => 1
    ),

    E_WARNING => array( //2
        'type' => '[Warning]',
        'color' => '#F18890',
        'prior' => 1,
        'debug' => 1
    ),
    E_CORE_WARNING => array( //32
        'type' => '[Core Warning]',
        'color' => '#F18890',
        'prior' => 1,
        'debug' => 1
    ),
    E_COMPILE_WARNING => array( //128
        'type' => '[Compilation Warning]',
        'color' => '#F18890',
        'prior' => 1,
        'debug' => 0
    ),
    E_USER_WARNING => array( //512
        'type' => '[Triggered Warning]',
        'color' => '#F18890',
        'prior' => 3,
        'debug' => 1
    ),

    E_STRICT => array( //2048
        'type' => '[Deprecation Notice]',
        'color' => 'brown',
        'prior' => 4,
        'debug' => 0
    ),

    E_NOTICE => array( //8
        'type' => '[Notice]',
        'color' => '#858585',
        'prior' => 5,
        'debug' => 0
    ),
    E_USER_NOTICE => array( //1024
        'type' => '[Triggered Notice]',
        'color' => '#858585',
        'prior' => 5,
        'debug' => 0
    ),
    E_EXCEPTION_ERROR => array( // 16384
        'type' => '[Exception]',
        'color' => 'red',
        'prior' => 1,
        'debug' => 1
    ),
);


if (isset($_POST) && count($_POST) && get_magic_quotes_gpc()) {
    stripSlashesOnArray($_POST);
}

function stripSlashesOnArray(array &$theArray)
{
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

$_CFG['shutdown_function_flag'] = false;

require($_CFG['_PATH']['core'] . 'static.main.php');

$_CFG['robot'] = SpiderDetect();


//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
/* INCLUDE USER CONF */
$_NEED_INSTALL = false;
if (file_exists(WEP_CONFIG))
    include(WEP_CONFIG);
else {
    $_NEED_INSTALL = true;
}
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////


//Настройка для Nginx
if (isset($_SERVER['HTTP_X_REAL_IP']) and $_SERVER['HTTP_X_REAL_IP'])
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
if (!isset($_SERVER['REMOTE_ADDR']))
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
while (isset($PHP_SELF[$k]) and $PHP_SELF[$k] != WEP_ADMIN) {
	$addpath .= $PHP_SELF[$k] . '/';
	$k++;
}
*/

$_SERVER['HTTP_PROTO'] = 'http://'; // TODO - определение протокола

/* $_CFG['_HREF'] */
if (!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = $_CFG['site']['www'];

$_CFG['_HREF']['arrayHOST'] = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

if (strpos($_CFG['_HREF']['arrayHOST'][0], 'xn--') !== false) { // спец домен
    require_once($_CFG['_PATH']['wep_controllers'] . '/lib/idna_convert.class.php');
    $IDN = new idna_convert();
    $_SERVER['HTTP_HOST'] = $IDN->decode($_SERVER['HTTP_HOST']);
    $_CFG['site']['rf'] = 1;
    $_CFG['_HREF']['arrayHOST'] = array_reverse(explode('.', $_SERVER['HTTP_HOST']));
}

$_CFG['_HREF']['_BH'] = $_SERVER['HTTP_HOST'] . '/' . $addpath; // www-путь сайта
define('WEP_BH', $_CFG['_HREF']['_BH']);

$_CFG['_HREF']['BH'] = $_SERVER['HTTP_PROTO'] . $_CFG['_HREF']['_BH'];
define('MY_BH', $_CFG['_HREF']['BH']);

if ($_CFG['site']['redirectPlugin'])
    $_CFG['require_modul']['redirect'] = true;

$_CFG['_HREF']['admin'] = MY_BH . WEP_ADMIN;
define('ADMIN_BH', $_CFG['_HREF']['admin']);

$_CFG['_HREF']['wepJS'] = ADMIN_BH . 'js.php';
$_CFG['_HREF']['siteJS'] = MY_BH . '_js.php';
$_CFG['_HREF']['captcha'] = MY_BH . '_captcha.php';
$_CFG['_HREF']['_style'] = '_design/_style/'; // дизайн стили
$_CFG['_HREF']['_script'] = '_design/_script/'; // дизайн стили

$_CFG['_HREF']['vendors'] = MY_BH . '_vendors/';

$_CFG['_F']['adminpage'] = false;

define('CHARSET', $_CFG['wep']['charset']);
/*
  Используем эту ф вместо стандартной, для совместимости с UTF-8
 */
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding(CHARSET);
}

/********************/
/* $_CFG['session'] */
/********************/

$_CFG['session']['name'] = 'wepID';
$_CFG['session']['path'] = '/';
$_CFG['session']['secure'] = 0;
$_CFG['session']['domain'] = '';
$_CFG['session']['multidomain'] = 0;
$hostcnt = count($_CFG['_HREF']['arrayHOST']);
$_SERVER['HTTP_DOMAIN_3'] = '';
// никто не будет использовать домен 4го уровня, а значит это IP
if ($hostcnt == 1 or ($hostcnt == 4 and ip2long($_SERVER['HTTP_HOST']) !== false)) { //учитываем localhost и ИПИ
    $_SERVER['HTTP_HOST2'] = $_SERVER['HTTP_HOST'];
    //$_CFG['session']['domain'] = '';//$_SERVER['HTTP_HOST2'];
} else {
    $temp = strpos($_CFG['_HREF']['arrayHOST'][0], ':');
    if ($temp !== false) {
        $_CFG['_HREF']['arrayHOST'][0] = substr($_CFG['_HREF']['arrayHOST'][0], 0, $temp);
    }
    $_SERVER['HTTP_HOST2'] = $_CFG['_HREF']['arrayHOST'][1] . '.' . $_CFG['_HREF']['arrayHOST'][0];
    if ($_CFG['site']['rf'])
        $_CFG['session']['domain'] = $IDN->encode($_SERVER['HTTP_HOST2']);
    else
        $_CFG['session']['domain'] = $_SERVER['HTTP_HOST2'];

    if (isset($_CFG['_HREF']['arrayHOST'][2]) && $_CFG['_HREF']['arrayHOST'][2]) {
        $_SERVER['HTTP_DOMAIN_3'] = $_CFG['_HREF']['arrayHOST'][2];
    }
}

/********************/
/* INCLUDE LANG */
/********************/

include_once($_CFG['_PATH']['wep_locallang'] . $_CFG['wep']['lang'] . '.php');
if (file_exists($_CFG['_PATH']['locallang'] . $_CFG['wep']['lang'] . '.php'))
    include_once($_CFG['_PATH']['locallang'] . $_CFG['wep']['lang'] . '.php');


/********************/
/* Acept config */
/********************/

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
if ($_CFG['session']['multidomain'])
    $_CFG['session']['domain'] = '.' . $_CFG['session']['domain'];
if ($_CFG['wep']['sessiontype'] === 1)
    $_CFG['require_modul']['session'] = true;
session_name($_CFG['session']['name']);
session_set_cookie_params($_CFG['session']['expire'], $_CFG['session']['path'], $_CFG['session']['domain'], $_CFG['session']['secure']);
ini_set('session.cookie_domain', $_CFG['session']['domain']);


if (!isset($_COOKIE['wep123456'])) {
    _setcookie('wep123456', base64encode($_SERVER['HTTP_REFERER']), (time() + 86400));
}


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    $_CFG['requestType'] = 'ajax';
else
    $_CFG['requestType'] = 'default';
/********************/
/***** ЛОГИ ********/
/********************/

initShowAllInfo();

/*****************************/
/******* Показ ошибок ********/
/*****************************/

// or $_CFG['_F']['adminpage']
if (!$_CFG['robot']) {
    $se = $_CFG['wep']['_showerror'];
    if (isset($_GET[$se])) {
        $_COOKIE[$se] = (int)$_GET[$se];
        _setcookie($se, $_COOKIE[$se]);
    }
    /*elseif($_CFG['wep']['debugmode'] and !isset($_COOKIE[$se])) { // для localhost
        $_COOKIE[$se] = 1;
        _setcookie($se, 1);
    }*/
    if (isset($_COOKIE[$se])) {
        $_CFG['wep']['debugmode'] = $_COOKIE[$se];
    }
}
//else _setcookie($se, '', (time()-5000));


/*********************/
/******* _tpl ********/
/*********************/

$_tpl = array();
$_tpl['meta'] = $_tpl['logs'] = $_tpl['onload'] = $_tpl['title'] = $_tpl['text'] = $_tpl['time'] = $_tpl['onload'] = '';
$_tpl['script'] = $_tpl['styles'] = $_tpl['onloadArray'] = array();
$_tpl['YEAR'] = date('Y');
$_tpl['BH'] = rtrim(MY_BH, '/'); // OLD
if (isset($_SERVER['REQUEST_URI']))
    $_tpl['REQUEST_URI'] = $_SERVER['REQUEST_URI'];