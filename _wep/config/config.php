<?

	//date_default_timezone_set('Asia/Yekaterinburg');
	date_default_timezone_set('Europe/Moscow');
	error_reporting(E_ALL ^ E_NOTICE);

$_CFG['time'] = time();
$_CFG['getdate'] = getdate();
$_CFG['remember_expire'] = $_CFG['time']+1728000; // 20дней ,по умолчанию
$_CFG['logs']['sql'] = array(); // - массив SQL запросов

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
	'log'=>0);

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
	'sessiontype'=>1 //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
);

$_CFG['site'] = array( // для сайта
	'msp'=>'paginator' // постраничнка
);

  /****************/
 /*$_CFG['_PATH']*/
/****************/
/*Полные пути по файловым системам для ядра*/
	if(!isset($_CFG['_PATH']['wepconf'])) //если  путь не был задан
		$_CFG['_PATH']['wepconf'] = dirname(dirname(__FILE__)); // файл-путь к конфигам
	if(!isset($_CFG['_PATH']['path']))
		$_CFG['_PATH']['path'] = dirname($_CFG['_PATH']['wepconf']);
	if(!isset($_CFG['_PATH']['wep'])) //если  путь не был задан
		$_CFG['_PATH']['wep'] = $_CFG['_PATH']['path'].'_wep'; // файл-путь  Корень админки
	
	$_SERVER['_DR_'] = $_CFG['_PATH']['path'] = $_CFG['_PATH']['path'].'/'; // корень сайта, основной путь к проекту
	$_CFG['_PATH']['extcore'] = $_CFG['_PATH']['wep'].'/extcore/'; // путь к системным модулям
	$_CFG['_PATH']['core'] = $_CFG['_PATH']['wep'].'/core/'; // путь к ядру
	$_CFG['_PATH']['phpscript'] = $_CFG['_PATH']['wep'].'/_phpscript';
	$_CFG['_PATH']['ctext'] = $_CFG['_PATH']['wep'].'/pagetext/'; // путь к обработчикам блоков страниц
	$_CFG['_PATH']['cdesign'] = $_CFG['_PATH']['wep'].'/cdesign/'; // дизайн админки
	$_CFG['_PATH']['locallang'] = $_CFG['_PATH']['wep'].'/locallang/'; // язык

/*пути для фаилов пользовательских модулей*/
	$_CFG['_PATH']['ptext'] = $_CFG['_PATH']['wepconf'].'/pagetext/'; // путь к обработчикам блоков страниц
	$_CFG['_PATH']['ext'] = $_CFG['_PATH']['wepconf'].'/ext/'; // путь к пользовательским модулям
	$_CFG['_PATH']['config'] = $_CFG['_PATH']['wepconf'].'/config/'; // конфиги
	$_CFG['_PATH']['ulocallang'] = $_CFG['_PATH']['wepconf'].'/locallang/'; // язык
	$_CFG['_PATH']['cron'] = $_CFG['_PATH']['wepconf'].'/cron/'; // кроны
	$_CFG['_PATH']['temp'] = $_CFG['_PATH']['wepconf'].'/temp/'; // путь к папке для хранения временных фаилов
	$_CFG['_PATH']['log'] = $_CFG['_PATH']['wepconf'].'/log/'; 
	$_CFG['_PATH']['HASH_KEY'] = $_CFG['_PATH']['config'].'hash.key';

/*пути для фаилов дизайна страниц*/
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
	$_CFG['PATH']['wepconfname'] = basename($_CFG['_PATH']['wepconf']); // базовое имя пользовательских фаилов
	$_CFG['PATH']['cdesign'] = $_CFG['PATH']['wepname'].'/cdesign/'; // дизайн админки
	$_CFG['PATH']['WSWG'] = '_wysiwyg/';
	$_CFG['PATH']['HASH_KEY'] = $_CFG['PATH']['wepconfname'].'/config/hash.key';

//Настройка для Nginx
	if(isset($_SERVER['HTTP_X_REAL_IP']))
		$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
	if(isset($_SERVER['HTTP_X_REAL_PORT']))
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
	$_CFG['_HREF']['BH'] = 'http://'.$_SERVER['HTTP_HOST'].$port.'/'.$addpath; // www-путь сайта
	$_CFG['_HREF']['JS'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['wepname'].'/js.php';
	$_CFG['_HREF']['siteJS'] = $_CFG['_HREF']['BH'].'_js.php';
	$_CFG['_HREF']['captcha'] = $_CFG['_HREF']['BH'].'_captcha.php';
	$_CFG['_HREF']['WSWG'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['WSWG'];
	$_CFG['_HREF']['_style'] = '_design/_style/'; // дизайн стили
	$_CFG['_HREF']['_script'] = '_design/_script/'; // дизайн стили
	$_CFG['_HREF']['arrayHOST'] = array_reverse(explode('.',$_SERVER['HTTP_HOST']));
  
  /******************/
 /*$_CFG['session']*/
/******************/
	$_CFG['session']['domain'] = '';
	if(count($_CFG['_HREF']['arrayHOST'])<2 or (int)$_CFG['_HREF']['arrayHOST'][0]>0) //учитываем localhost и ИПИ
		$_SERVER['HTTP_HOST2'] = $_SERVER['HTTP_HOST'].$port;
	else {
		$_SERVER['HTTP_HOST2'] = $_CFG['_HREF']['arrayHOST'][1].'.'.$_CFG['_HREF']['arrayHOST'][0].$port;
		$_CFG['session']['domain'] = '.'.$_SERVER['HTTP_HOST2'];
	}
	$_CFG['session']['name'] = 'wepID';
	$_CFG['session']['expire'] = $_CFG['time']+86400;// 1 день ,по умолчанию
	$_CFG['session']['path'] = '/';
	$_CFG['session']['secure'] = 0;

	session_name($_CFG['session']['name']);
	session_set_cookie_params($_CFG['session']['expire'],$_CFG['session']['path'], $_CFG['session']['domain'],$_CFG['session']['secure']);


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
'name'=>"/^[0-9A-Za-z\x7F-\xFF\-]+$/",
"text"=>"/^[\/\(\)\!\+\:\;\?\"\'\№\,\.0-9A-z\x7F-\xFF \-\=\_\%\n\r\t\|\*\>]+$/",
"html"=>"/^[\/\(\)\!\+\:\;\?\"\'\№\,\.0-9A-z\x7F-\xFF \-\=\_\%\n\r\t\|\*\>\<\@\&\$]+$/",
"int"=>"/^[0-9]+$/",
"float"=>"/^[\.0-9]+$/",
"alpha"=>"/^[A-Za-z]+$/",
"alphaint"=>"/^[A-Za-z0-9]+$/",
"date"=>"/^[0-9]+$/",
"phone2"=>"/^((([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2})|(\([0-9]{5}\)[0-9]{1}))-[0-9]{2}-[0-9]{2})((, )(([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2}))-[0-9]{2}-[0-9]{2}){0,3}$/", 
"phone"=>"/^((([0-9]-[0-9]{3}-[0-9]{3})|([0-9]{2,3})|(\([0-9]{3}\)[0-9]{3})|(\([0-9]{4}\)[0-9]{2}))-[0-9]{2}-[0-9]{2})$/",
"email"=>"/^[A-Za-z_0-9\-\.]+@[a-z_0-9\.\-]+.[a-z]{2,3}$/",
"www"=>"/^(http:\/\/)?([A-Za-z]+\.)?[0-9A-Za-z\-]+\.[A-Za-z]+[\/0-9A-Za-z\.\_\=\?\&]*$/");

$_CFG['_repl'] = array(
	'name'=>'/[^0-9A-Za-z\x7F-\xFF\- \,\.@_]+/',
	'href'=>"/(http:\/\/|www\.)[0-9A-Za-z\/\.\_\-\=\?]*/");

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
	'flashFormat' => array('swf'=>1)
);
  
  /******************/
 /*$_CFG['enum']***/
/******************/

		$_CFG['enum']['active'] = array(
			0=>'Неактивное, новое', 
			1=>'Активное', 
			2=>'Неактивное, выключено пользователем', 
			3=>'Неактивное, отредактировано пользователем', 
			4=>'Удалено пользователем', 
			5=>'Неактивное, включено пользователем', 
			6=>'Неактивное, некорректное');

		$_CFG['enum']['yesno'] = array(
			0=>'НЕТ', 
			1=>'ДА');

		$_CFG['enum']['yesno2'] = array(
			0=>'НЕТ', 
			1=>'ЕСТЬ');

		$_CFG['enum']['_MOP'] = array(
			5=>5, 
			10=>10, 
			20=>20,
			30=>30,
			50=>50,
			100=>100,
			150=>150,
			200=>200);

		$_CFG['enum']['menu'] = array(
			0=>'',
			1=>'Меню №1',
			2=>'Меню №2',
			3=>'Меню №3');

		$_CFG['enum']['marker'] = array(
			'text'=>'text',
			'head'=>'head',
			'blockadd'=>'blockadd',
			'param'=>'param',
			'path'=>'path',
			'logs'=>'logs',
			'foot'=>'foot');
  /***********************/
 /***INCLUDE USER CONF***/
/***********************/
	include_once($_CFG['_PATH']['locallang'].$_CFG['wep']['locallang'].'.php');
	if(file_exists($_CFG['_PATH']['locallang'].$_CFG['wep']['locallang'].'.php'))
		include_once($_CFG['_PATH']['ulocallang'].$_CFG['wep']['locallang'].'.php');

	register_shutdown_function ('shutdown_function'); // Запускается первым при завершении скрипта

/*
Функция завершения работы скрипта
*/
	function shutdown_function() {
		//ob_end_flush();
		//print_r('shutdown_function');
		if(defined('SID'))
			session_write_close();
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
			}else {
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
?>
