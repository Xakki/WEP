<?

	//date_default_timezone_set('Asia/Yekaterinburg');
	//date_default_timezone_set('Europe/Moscow');

/*Полные пути по файловым системам для ядра*/
	$_CFG['_PATH']['wepconf'] = dirname(dirname(__FILE__)); // файл-путь к конфигам
	if(!isset($_CFG['_PATH']['path']))
		$_CFG['_PATH']['path'] = dirname($_CFG['_PATH']['wepconf']);
	if(!isset($_CFG['_PATH']['wep'])) //если  путь не был задан
		$_CFG['_PATH']['wep'] = $_CFG['_PATH']['path'].'/_wep'; // файл-путь  Корень админки

	include($_CFG['_PATH']['wep'].'/config/config.php');

$_CFG['sql'] = array( // SQL
	'host'=>'localhost',
	'login'=>'core_wep',
	'password'=>'sF45DfpFddt3',
	'database'=>'core_wep',
	'setnames'=>'utf8',
	'dbpref'=>'',
	'log'=>0);

$_CFG['wep'] = array( // для админки
	'charset'=>'utf-8',
	'access'=>1, // 1 - вкл доступ по модулю пользователей, 0 - вкл доступ по дефолтному паролю
	'locallang'=>'default',
	'login'=>'root',
	'password'=>'core_wep',
	'design'=>'default',
	'msp'=>'paginator',
	'md5'=>'dfHH2Lss',
	'def_filesize'=>200,
	'sessiontype'=>1 //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
);

$_CFG['site'] = array( // для сайта
	'msp'=>'paginator' // постраничнка
	);


?>
