<?
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set('display_errors',-1);
	//универсальный для русского языка
	setlocale(LC_CTYPE, 'ru_RU.UTF-8');

$_CFG['logs']['sql'] = array(); // - массив SQL запросов
$_CFG['timezone'] = 'Europe/Moscow';
$_CFG['info'] = array( //информация
	'version'=>'2.2',
	'email'=>'info@xakki.ru',
	'icq'=>'222392984');

$_CFG['sql'] = array( // SQL
	'host'=>'localhost',
	'login'=>'default',
	'password'=>'defaultpass',
	'database'=>'defaultbd',
	'setnames'=>'utf8',
	'dbpref'=>'',
	'log'=>0,// логирование запросов в фаил
	'longquery'=>1 // запись в баг запросы которые выполняются дольше указанного времени в сек
);
$_CFG['memcache'] = array(
	'host'=>'127.0.0.1',
	'port'=>11211,
);
$_CFG['wep'] = array( // для админки
	'charset'=>'utf-8',
	'access'=>1, // 1 - вкл доступ по модулю пользователей, 0 - вкл доступ по дефолтному паролю
	'locallang'=>'default',
	'login'=>'root',
	'password'=>'default',
	'design'=>'default',
	'msp'=>'paginator',
	'md5'=>'d3dEegf6EH',
	'def_filesize'=>200,
	'sessiontype'=>1, //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
	'bug_hunter' => 1,
);

$_CFG['site'] = array( // для сайта
	'msp'=>'paginator', // постраничнка
	'rf' => 0, // для рускояз доменов
	'bug_hunter' => 1, // логирование ошибок
	'show_error' => 1, //0- ничего не показывать обычным юзерам, 1 -паказывать только сообщение что произошла ошибка, 2 - паказать ошибку
);

$_CFG['singleton'] = array();

  /****************/
 /*$_CFG['_PATH']*/
/****************/
/*Полные пути по файловым системам для ядра*/
	if(!isset($_CFG['_PATH']['wep'])) //если  путь не был задан
		$_CFG['_PATH']['wep'] = dirname(dirname(__FILE__)); // файл-путь к ядру, Корень админки
	if(!isset($_CFG['_PATH']['path']))
		$_CFG['_PATH']['path'] = dirname($_CFG['_PATH']['wep']); // корень сайта
	if(!isset($_CFG['_PATH']['wepconf'])) //если  путь не был задан
		$_CFG['_PATH']['wepconf'] = $_CFG['_PATH']['path'].'/_wepconf'; // файл-путь  к конфигу 
	
	$_SERVER['_DR_'] = $_CFG['_PATH']['path'] = $_CFG['_PATH']['path'].'/'; // корень сайта, основной путь к проекту
	$_CFG['_PATH']['extcore'] = $_CFG['_PATH']['wep'].'/extcore/'; // путь к системным модулям
	$_CFG['_PATH']['core'] = $_CFG['_PATH']['wep'].'/core/'; // путь к ядру
	$_CFG['_PATH']['phpscript'] = $_CFG['_PATH']['wep'].'/_phpscript';
	$_CFG['_PATH']['ctext'] = $_CFG['_PATH']['wep'].'/pagetext/'; // путь к обработчикам блоков страниц
	$_CFG['_PATH']['cdesign'] = $_CFG['_PATH']['wep'].'/cdesign/'; // дизайн админки
	$_CFG['_PATH']['locallang'] = $_CFG['_PATH']['wep'].'/locallang/'; // язык

/*пути для файлов пользовательских модулей*/
	$_CFG['_PATH']['phpscript2'] = $_CFG['_PATH']['wepconf'].'/_phpscript';
	$_CFG['_PATH']['ptext'] = $_CFG['_PATH']['wepconf'].'/pagetext/'; // путь к обработчикам блоков страниц
	$_CFG['_PATH']['ext'] = $_CFG['_PATH']['wepconf'].'/ext/'; // путь к пользовательским модулям
	$_CFG['_PATH']['config'] = $_CFG['_PATH']['wepconf'].'/config/'; // конфиги
	$_CFG['_PATH']['ulocallang'] = $_CFG['_PATH']['wepconf'].'/locallang/'; // язык
	$_CFG['_PATH']['cron'] = $_CFG['_PATH']['wepconf'].'/cron/'; // кроны
	$_CFG['_PATH']['temp'] = $_CFG['_PATH']['wepconf'].'/temp/'; // путь к папке для хранения временных файлов
	$_CFG['_PATH']['log'] = $_CFG['_PATH']['wepconf'].'/log/'; 
	$_CFG['_PATH']['HASH_KEY'] = $_CFG['_PATH']['config'].'hash.key';

/*пути для файлов дизайна страниц*/
	$_CFG['_PATH']['design'] = $_CFG['_PATH']['path'].'_design/'; // дизайн сайта
	$_CFG['_PATH']['_style'] = $_CFG['_PATH']['path'].'_design/_style/'; // дизайн стили
	$_CFG['_PATH']['_script'] = $_CFG['_PATH']['path'].'_design/_script/'; // дизайн стили

  /****************/
 /*$_CFG['PATH']*/
/****************/
// относительные пути
	$_CFG['PATH']['content'] = '_content/';
	$_CFG['PATH']['userfile'] = '_content/_userfile/'; // файлы пользователя
	$_CFG['PATH']['wepname'] = basename($_CFG['_PATH']['wep']); // базовое имя админки
	$_CFG['PATH']['wepconfname'] = basename($_CFG['_PATH']['wepconf']); // базовое имя пользовательских файлов
	$_CFG['PATH']['cdesign'] = $_CFG['PATH']['wepname'].'/cdesign/'; // дизайн админки
	$_CFG['PATH']['WSWG'] = '_wysiwyg/';
	$_CFG['PATH']['HASH_KEY'] = $_CFG['PATH']['wepconfname'].'/config/hash.key';

//Настройка для Nginx
	if(isset($_SERVER['HTTP_X_REAL_IP']) and $_SERVER['HTTP_X_REAL_IP'])
		$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
	if(isset($_SERVER['HTTP_X_REAL_PORT']) and $_SERVER['HTTP_X_REAL_PORT'])
		$_SERVER['SERVER_PORT'] = $_SERVER['HTTP_X_REAL_PORT'];

/*http пути*/
	$port = '';
	if($_SERVER['SERVER_PORT']!=80)
		$port = ':'.$_SERVER['SERVER_PORT'];
	$PHP_SELF = explode('/',$_SERVER['PHP_SELF']);
	if(!$PHP_SELF[0]) array_shift($PHP_SELF);
	array_pop($PHP_SELF);

	$addpath = '';
	$k=0;
	while(isset($PHP_SELF[$k]) and $PHP_SELF[$k]!=$_CFG['PATH']['wepname']) {
		$addpath .= $PHP_SELF[$k].'/';
		$k++;
	}

  /****************/
 /*$_CFG['_HREF']*/
/****************/
	if(strpos($_SERVER['HTTP_HOST'],'xn--') !== false) {
		require_once($_CFG['_PATH']['phpscript'].'/idna_convert.class.php');
		$IDN = new idna_convert();
		$_SERVER['HTTP_HOST'] = $IDN->decode($_SERVER['HTTP_HOST']);
		$_CFG['site']['rf']=1;
	}
	$_CFG['_HREF']['BH'] = 'http://'.$_SERVER['HTTP_HOST'].$port.'/'.$addpath; // www-путь сайта
	$_CFG['_HREF']['wepJS'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['wepname'].'/js.php';
	$_CFG['_HREF']['siteJS'] = $_CFG['_HREF']['BH'].'_js.php';
	$_CFG['_HREF']['captcha'] = $_CFG['_HREF']['BH'].'_captcha.php';
	$_CFG['_HREF']['WSWG'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['WSWG'];
	$_CFG['_HREF']['_style'] = '_design/_style/'; // дизайн стили
	$_CFG['_HREF']['_script'] = '_design/_script/'; // дизайн стили
	$_CFG['_HREF']['arrayHOST'] = array_reverse(explode('.',$_SERVER['HTTP_HOST']));

	if(strstr($_SERVER['PHP_SELF'],'/'.$_CFG['PATH']['wepname'].'/'))
		$_CFG['_F']['adminpage'] = true;
	else
		$_CFG['_F']['adminpage'] = false;
  
  /******************/
 /*$_CFG['_MASK']***/
/******************/
	$_CFG['_MASK'] = array(
'all'=>"/^.*$/",
'login'=>"/^[0-9A-Za-z]+$/",
'name'=>"/^[0-9A-Za-zА-ЯЁёа-я\-]+$/u",
"text"=>"/^[\/\(\)\!\+\:\;\?\"\'\№\,\.0-9A-Za-zА-Яа-я \-\=\_\%\n\r\t\|\*\>]+$/u",
"html"=>"/^[\/\(\)\!\+\:\;\?\"\'\№\,\.0-9A-Za-zА-Яа-я \-\=\_\%\n\r\t\|\*\>\<\@\&\$]+$/u",
"int"=>"/^[0-9]+$/",
"float"=>"/^[\.0-9]+$/",
"alpha"=>"/^[A-Za-z]+$/",
"alphaint"=>"/^[A-Za-z0-9]+$/",
"date"=>"/^[0-9]+$/",
"phone2"=>"/^((([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2})|(\([0-9]{5}\)[0-9]{1}))-[0-9]{2}-[0-9]{2})((, )(([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2}))-[0-9]{2}-[0-9]{2}){0,3}$/", 
"phone"=>"/^((([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2}))-[0-9]{2}-[0-9]{2})$/",
"email"=>"/^[A-Za-z_0-9\-\.]+@[a-z_0-9\.\-]+.[a-z]{2,3}$/",
"www"=>"/^(http:\/\/)?([A-Za-zА-Яа-я]+\.)?[0-9A-Za-zА-Яа-я\-]+\.[A-Za-zА-Яа-я]+[\/0-9A-Za-zА-Яа-я\.\_\=\?\&]*$/u");

$_CFG['_repl'] = array(
	'name'=>'/[^0-9A-Za-zА-Яа-я\- \,\.@_]+/u',
	'href'=>"/(http:\/\/|www\.)[0-9A-Za-z\/\.\_\-\=\?]*/u");

// WYSIWYG 
$_CFG['ckedit']['toolbar']['Full'] = "'Full'";
$_CFG['ckedit']['toolbar']['Page'] = "[['Source','-','Save','NewPage','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
	'/',
	['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	['Link','Unlink','Anchor'],
	['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
	'/',
	['Styles','Format','Font','FontSize'],
	['TextColor','BGColor'],
	['Maximize', 'ShowBlocks','-','About'],[ 'UIColor' ]]";

$_CFG['ckedit']['toolbar']['Board'] = "[ 
	['PasteText'], ['Undo','Redo','-','RemoveFormat'], ['Bold','Italic','Underline','Superscript'], 
	['NumberedList','BulletedList'], ['JustifyLeft','JustifyCenter','JustifyBlock'] ]";

$_CFG['_imgquality']=80;// качество картинки
$_CFG['_imgwater']=$_CFG['_PATH']['path'].'_design/_img/watermark.png'; //водяной знак

$_CFG['form'] = array(
	'imgFormat' => array('gif'=>1,'jpg'=>1,'jpeg'=>1,'png'=>1),
	'flashFormat' => array('swf'=>1),
	'dateFormat' => 'd-m-Y-H-i-s'
);

	

//ERRORS
$_CFG['_error'] = array(
	0 => array(
		'type' => '[@]',
		'color' => 'black',
		'prior' => 6
	),
	E_ERROR => array(
		'type' => '[Fatal Error]',
		'color' => 'red',
		'prior' => 0
	),
	E_WARNING => array(
		'type' => '[Warning]',
		'color' => 'yellow',
		'prior' => 1
	),
	E_PARSE => array(
		'type' => '[Parse Error]',
		'color' => 'red',
		'prior' => 0
	),
	E_NOTICE => array(
		'type' => '[Notice]',
		'color' => 'black',
		'prior' => 5
	),
	E_CORE_ERROR => array(
		'type' => '[Fatal Core Error]',
		'color' => 'red',
		'prior' => 0
	),			
	E_CORE_WARNING => array(
		'type' => '[Core Warning]',
		'color' => 'yellow',
		'prior' => 1
	),			
	E_COMPILE_ERROR => array(
		'type' => '[Compilation Error]',
		'color' => 'red',
		'prior' => 0
	),
	E_COMPILE_WARNING => array(
		'type' => '[Compilation Warning]',
		'color' => 'yellow',
		'prior' => 1
	),			
	E_USER_ERROR => array(
		'type' => '[Triggered Error]',
		'color' => 'red',
		'prior' => 0
	),			
	E_USER_WARNING => array(
		'type' => '[Triggered Warning]',
		'color' => 'yellow',
		'prior' => 2
	),			
	E_USER_NOTICE => array(
		'type' => '[Triggered Notice]',
		'color' => 'black',
		'prior' => 3
	),			
	E_STRICT => array(
		'type' => '[Deprecation Notice]',
		'color' => 'pink',
		'prior' => 4
	),			
	E_RECOVERABLE_ERROR => array(
		'type' => '[Catchable Fatal Error]',
		'color' => 'red',
		'prior' => 0
	),
);
  
  /******************/
 /*$_CFG['session']*/
/******************/

	$_CFG['session']['name'] = 'wepID';
	$_CFG['session']['path'] = '/';
	$_CFG['session']['secure'] = 0;
	$_CFG['session']['domain'] = '';
	$_CFG['session']['multidomain'] = 0;
	$hostcnt = count($_CFG['_HREF']['arrayHOST']);
	// никто не будет использовать домен 4го уровня, а значит это IP
	if($hostcnt<2 or ($hostcnt==4)) { //учитываем localhost и ИПИ
		$_SERVER['HTTP_HOST2'] = $_SERVER['HTTP_HOST'].$port;
		//$_CFG['session']['domain'] = '';//$_SERVER['HTTP_HOST2'];
	}
	else {
		$_SERVER['HTTP_HOST2'] = $_CFG['_HREF']['arrayHOST'][1].'.'.$_CFG['_HREF']['arrayHOST'][0].$port;
		
		if($_CFG['site']['rf'])
			$_CFG['session']['domain'] = $IDN->encode($_SERVER['HTTP_HOST2']);
		else
			$_CFG['session']['domain'] = $_SERVER['HTTP_HOST2'];

	}


 /***INCLUDE LANG***/
	include_once($_CFG['_PATH']['locallang'].$_CFG['wep']['locallang'].'.php');
	if(file_exists($_CFG['_PATH']['locallang'].$_CFG['wep']['locallang'].'.php'))
		include_once($_CFG['_PATH']['ulocallang'].$_CFG['wep']['locallang'].'.php');


  /***********************/
 /***INCLUDE USER CONF***/
/***********************/

include($_CFG['_PATH']['wepconf'].'/config/config.php');

 /***SET SESSION***/
	date_default_timezone_set($_CFG['timezone']);
		$_CFG['time'] = time();
		$_CFG['getdate'] = getdate();
		$_CFG['remember_expire'] = $_CFG['session']['expire']= $_CFG['time']+1728000; // 20дней ,по умолчанию
		if($_CFG['session']['multidomain'])
			$_CFG['session']['domain'] = '.'.$_CFG['session']['domain'];
	session_name($_CFG['session']['name']);
	session_set_cookie_params($_CFG['session']['expire'],$_CFG['session']['path'], $_CFG['session']['domain'],$_CFG['session']['secure']);
	ini_set('session.cookie_domain', $_CFG['session']['domain']);
	register_shutdown_function ('shutdown_function'); // Запускается первым при завершении скрипта

	include $_CFG['_PATH']['core'].'observer.php';

/*
Функция завершения работы скрипта
*/
	function shutdown_function() {
		observer::notify_observers('shutdown_function');
	}
/*SESSION*/

	function session_go($force=0) {
		global $_CFG;
		if(!$_SERVER['robot'] and (isset($_COOKIE[$_CFG['session']['name']]) or $force) and !defined('SID')) {
			if($_CFG['wep']['sessiontype']==1) {
				if(!$SESSION_GOGO) {
					require_once($_CFG['_PATH']['core'].'/session.php');
					$SESSION_GOGO = new session_gogo();
				}
			} else {
				session_start();
			}
			return true;
		}
		return false;
	}
	
	function _setcookie($name,$value='',$expire='',$path='',$domain='',$secure='') {
		global $_CFG;
		if($expire=='')
			$expire = $_CFG['session']['expire'];
		if($path=='')
			$path = $_CFG['session']['path'];
		if($domain=='')
			$domain = $_CFG['session']['domain'];
		if($secure=='')
			$secure = $_CFG['session']['secure'];
		setcookie($name,$value,$expire,$path,$domain,$secure);
	}
/*
точное время в милисекундах
*/
	function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime()); return ((float)$usec + (float)$sec);
	}
?>
