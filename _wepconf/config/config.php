<?

	//date_default_timezone_set('Asia/Yekaterinburg');
	date_default_timezone_set('Europe/Moscow');
	error_reporting(E_ALL ^ E_NOTICE);

/*Полные пути по файловым системам для ядра*/
	if(!isset($_CFG['_PATH']['wepconf'])) //если  путь не был задан
		$_CFG['_PATH']['wepconf'] = dirname(dirname(__FILE__)); // файл-путь к конфигам
	if(!isset($_CFG['_PATH']['path']))
		$_CFG['_PATH']['path'] = dirname($_CFG['_PATH']['wepconf']);
	$_SERVER['_DR_'] = $_CFG['_PATH']['path'] = $_CFG['_PATH']['path'].'/'; // корень сайта, основной путь к проекту
	if(!isset($_CFG['_PATH']['wep'])) //если  путь не был задан
		$_CFG['_PATH']['wep'] = $_CFG['_PATH']['path'].'_wep'; // файл-путь  Корень админки
	$_CFG['_PATH']['extcore'] = $_CFG['_PATH']['wep'].'/extcore/'; // путь к системным модулям
	$_CFG['_PATH']['core'] = $_CFG['_PATH']['wep'].'/core/'; // путь к ядру
	$_CFG['_PATH']['phpscript'] = $_CFG['_PATH']['wep'].'/_phpscript';
	$_CFG['_PATH']['ctext'] = $_CFG['_PATH']['wep'].'/pagetext/'; // путь к обработчикам блоков страниц
	$_CFG['_PATH']['cdesign'] = $_CFG['_PATH']['wep'].'/cdesign/'; // дизайн админки

/*пути для фаилов пользовательских модулей*/
	$_CFG['_PATH']['ptext'] = $_CFG['_PATH']['wepconf'].'/pagetext/'; // путь к обработчикам блоков страниц
	$_CFG['_PATH']['ext'] = $_CFG['_PATH']['wepconf'].'/ext/'; // путь к пользовательским модулям
	$_CFG['_PATH']['config'] = $_CFG['_PATH']['wepconf'].'/config/'; // конфиги
	$_CFG['_PATH']['locallang'] = $_CFG['_PATH']['wepconf'].'/locallang/'; // язык
	$_CFG['_PATH']['cron'] = $_CFG['_PATH']['wepconf'].'/cron/'; // кроны
	$_CFG['_PATH']['temp'] = $_CFG['_PATH']['wepconf'].'/temp/'; // путь к папке для хранения временных фаилов
	$_CFG['_PATH']['log'] = $_CFG['_PATH']['wepconf'].'/log/'; 
	$_CFG['_PATH']['HASH_KEY'] = $_CFG['_PATH']['config'].'hash.key';

/*пути для фаилов дизайна страниц*/
	$_CFG['_PATH']['design'] = $_CFG['_PATH']['path'].'_design/'; // дизайн сайта
	$_CFG['_PATH']['_style'] = $_CFG['_PATH']['path'].'_design/_style/'; // дизайн стили
	$_CFG['_PATH']['_script'] = $_CFG['_PATH']['path'].'_design/_script/'; // дизайн стили

// относительные пути
	$_CFG['PATH']['content'] = '_content/';
	$_CFG['PATH']['userfile'] = '_content/_userfile/'; // файлы пользователя
	$_CFG['PATH']['wepname'] = basename($_CFG['_PATH']['wep']); // базовое имя админки
	$_CFG['PATH']['wepconfname'] = basename($_CFG['_PATH']['wepconf']); // базовое имя пользовательских фаилов
	$_CFG['PATH']['cdesign'] = $_CFG['PATH']['wepname'].'/cdesign/'; // дизайн админки
	$_CFG['PATH']['WSWG'] = '_wysiwyg/';
	$_CFG['PATH']['HASH_KEY'] = $_CFG['PATH']['wepconfname'].'/config/hash.key';

/*http пути*/
	$_CFG['_HREF']['BH'] = 'http://'.$_SERVER['HTTP_HOST'].'/'; // www-путь сайта
	$_CFG['_HREF']['arrayHOST'] = array_reverse(explode('.',$_SERVER['HTTP_HOST']));
	$_SERVER['HTTP_HOST2'] = $_CFG['_HREF']['arrayHOST'][1].'.'.$_CFG['_HREF']['arrayHOST'][0];
	$_CFG['_HREF']['JS'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['wepname'].'/js.php';
	$_CFG['_HREF']['siteJS'] = $_CFG['_HREF']['BH'].'_js.php';
	$_CFG['_HREF']['captcha'] = $_CFG['_HREF']['BH'].'_captcha.php';
	$_CFG['_HREF']['WSWG'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['WSWG'];
	$_CFG['_HREF']['_style'] = $_CFG['_HREF']['BH'].'_design/_style/'; // дизайн стили
	$_CFG['_HREF']['_script'] = $_CFG['_HREF']['BH'].'_design/_script/'; // дизайн стили

	$_CFG['time'] = time();
	$_CFG['getdate'] = getdate();
	$_CFG['remember_expire'] = 1728000; // 20дней ,по умолчанию
	$_CFG['session_expire'] = 86400; // 1 день ,по умолчанию
	$_CFG['logs']['sql'] = array(); // - массив SQL запросов
	$_CFG['session_name'] = 'wepID';

	//session_start();
	session_name($_CFG['session_name']);
	session_set_cookie_params($_CFG['session_expire'],'/', $_SERVER['HTTP_HOST2']);

	if(strstr($_SERVER['PHP_SELF'],'/'.$_CFG['PATH']['wepname'].'/'))
		$_CFG['_F']['adminpage'] = true;
	else
		$_CFG['_F']['adminpage'] = false;

//Маски
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

$_CFG['sql'] = array( // SQL
	'host'=>'localhost',
	//'login'=>'xakkiorg_unid',
	//'password'=>'graber402',
	//'database'=>'xakkiorg_unidoski',
	'login'=>'xakki_02',
	'password'=>'sdFDfpFd4th',
	'database'=>'xakki_02',
	'setnames'=>'utf8',
	'dbpref'=>'',
	'log'=>0);

$_CFG['info'] = array( //информация
	'version'=>'2.2',
	'email'=>'info@xakki.ru',
	'icq'=>'222392984',
	'onShape'=>0);

$_CFG['wep'] = array( // для админки
	'charset'=>'utf-8',
	'access'=>1, // 1 - вкл доступ по модулю пользователей, 0 - вкл доступ по дефолтному паролю
	'locallang'=>'default',
	'login'=>'root',
	'password'=>'myadmin',
	'prm_table'=>'_',
	'design'=>'default',
	'msp'=>'paginator',
	'md5'=>'FoS2Lss',
	'def_filesize'=>200,
	'sessiontype'=>1 //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
);

$_CFG['site'] = array( // для сайта
	'msp'=>'paginator' // постраничнка
	);

	include_once($_CFG['_PATH']['locallang'].$_CFG['wep']['locallang'].'.php');


?>
