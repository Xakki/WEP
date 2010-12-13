<?

$_CFG['sql'] = array( // SQL
	'host'=>'127.0.0.1',
	'login'=>'core_wep',
	'password'=>'D56FdpnD4th',
	'database'=>'core_wep',
	'setnames'=>'utf8',
	'dbpref'=>'',
	'log'=>0);

$_CFG['wep'] = array( // для админки
	'charset'=>'utf-8',
	'access'=>1, // 1 - вкл доступ по модулю пользователей, 0 - вкл доступ по дефолтному паролю
	'locallang'=>'default',
	'login'=>'root',
	'password'=>'coreadmin',
	'prm_table'=>'_',
	'design'=>'default',
	'msp'=>'paginator',
	'md5'=>'dSS2ffs',
	'def_filesize'=>100,
	'sessiontype'=>1, //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
	'bug_hunter' => 1
);

$_CFG['site'] = array( // для сайта
	'msp'=> 'paginator', // постраничнка
	//'rf' => $_CFG['site']['rf'], // для рускояз доменов
	'bug_hunter' => 1
);

$_CFG['session']['multidomain'] = 1;
?>
