<?
$_SESSION['step'] = 1;
if(!isset($var_const))
	$var_const = array(
		'mess'=>array(),
		'sbmt'=>'Сохранить'
	);
//Подключение к БД и доп параметры
$edit_cfg = array(
	'sql' => true,
	'memcache' => true,
	'wep' => true,
	'site' => true,
);


$mess = array();
if (isset($_POST['sbmt'])) {
	$_POST['wep']['bug_hunter'] = array_combine($_POST['wep']['bug_hunter'],$_POST['wep']['bug_hunter']);
	list($fl,$mess) = static_tools::saveUserCFG($_POST,$TEMP_CFG);
	//Записать в конфиг все данные которые отличаются от данных по умолчанию
	if ($fl) {
		$mess[] = $var_const['mess'];
		$DATA['messages'] = $mess;
		$_SESSION['step'] = 2;
		return $html = $HTML->transformPHP($DATA, 'messages');
		//@header('Location: install.php?step=' . ($_GET['step'] + 1));
		//die('<a href="install.php?step=' . ($_GET['step'] + 1) . '">Следующий шаг</a>');
	}
} else {
	$DEF_CFG = static_tools::getFdata($_CFG['_PATH']['wep'] . '/config/config.php', '/* MAIN_CFG */', '/* END_MAIN_CFG */');
	$USER_CFG = static_tools::getFdata($_CFG['_PATH']['wepconf'] . '/config/config.php', '', '', $DEF_CFG);// Текущая полная конфигурация
	$mess[] = array('name' => 'ok', 'value' => 'Будте осторожны при вводе этих настроек.');
}

$DATA = array('_*features*_' => array('method' => 'POST', 'name' => 'step0'));

/* SQL */
foreach ($USER_CFG['sql'] as $k => $r) {
	if(is_bool($r))
		$type = 'checkbox';
	else
		$type = 'text';
	$comm = '';
	$cap = $k;
	switch ($k) {
		case 'host':
			$cap = 'Хост подключения к БД';
			break;
		case 'login':
			$cap = 'Логин БД';
			break;
		case 'password':
			$cap = 'Пароль БД';
			$type = 'password_new';
			break;
		case 'database':
			$cap = 'Название БД';
			break;
		case 'setnames':
			$cap = 'Кодировка БД';
			break;
		case 'dbpref':
			$cap = 'Префикс в названии таблиц';
			break;
		case 'log':
			$cap = 'Логирование всех запросов в фаил';
			$type = 'checkbox';
			break;
		case 'longquery':
			$cap = 'Логировать долгие запросы к БД';
			$comm = 'запись в баг запросы которые выполняются дольше указанного времени в сек';
			break;
	}

	$DATA['sql[' . $k.']'] = array(
		'caption' => $cap,
		'comment' => $comm,
		'type' => $type,
		'value' => $r
	);
}

/* MEMCACHE */
$DATA['showparam1'] = array('type' => 'info', 'caption' => '
	<div class="showparam" onclick="show_fblock(this,\'.hmemcache\')"> 
		<span class="shbg"></span>
		<span class="sh1">Показать</span>
		<span class="sh2">Скрыть</span> настройки Memcache
	</div>');

foreach ($USER_CFG['memcache'] as $k => $r) {
	if(is_bool($r))
		$type = 'checkbox';
	else
		$type = 'text';
	$comm = '';
	$cap = $k;
	switch ($k) {
		case 'host':
			$cap = 'Хост подключения к Memcache';
			break;
		case 'port':
			$cap = 'Порт подключения к Memcache';
			break;
	}

	$DATA['memcache[' . $k.']'] = array(
		'caption' => $cap,
		'comment' => $comm,
		'type' => $type,
		'value' => $r,
		'css' => 'fblock hmemcache',
		'style' => 'display:none;'
	);
}

/* WEP */
$DATA['showparam2'] = array('type' => 'info', 'caption' => '
	<div class="showparam" onclick="show_fblock(this,\'.hwep\')"> 
		<span class="shbg"></span>
		<span class="sh1">Показать</span>
		<span class="sh2">Скрыть</span> настройки ядра и админки сайта
	</div>');

foreach ($USER_CFG['wep'] as $k => $r) {
	if(is_bool($r))
		$type = 'checkbox';
	else
		$type = 'text';
	if($k=='cron') continue;
	if(is_array($r)) {
		if(is_array(current($r)))
			continue;
		$type = 'list';
	}
	$comm = '';
	$cap = $k;
	switch ($k) {
		case 'charset':
			$cap = 'Кодировка сайта';
		break;
		case 'locale':
			$cap = 'Кодировка locale';
		break;
		case 'timezone':
			$cap = 'Временная зона';
			$type = 'list';
			$valuelist = array();
			$timezone_identifiers = DateTimeZone::listAbbreviations();
			foreach($timezone_identifiers as $tt=>$td) {
				if($td[0]['timezone_id']) {
					$ofset = $td[0]['offset']/3600;
					if($ofset>0) $ofset = '+'.$ofset;
					$ofset = (int)$ofset;
					$valuelist[$td[0]['offset']] = array('#id#' => $td[0]['timezone_id'], '#name#' => $ofset.'ч '.(int)(($td[0]['offset']/60)-$ofset*60).' мин GMT','#checked#'=>0);
					foreach($td as $tt2=>$td2) 
						$valuelist[$td[0]['offset']]['#item#'][] = array('#id#' => $td2['timezone_id'], '#name#' => $td2['timezone_id']);
				}
			}

			ksort($valuelist,SORT_NUMERIC );
		break;
		case 'access':
			$cap = 'Тип доступа в админку';
			$type = 'list';
			$valuelist = array(
				array('#id#' => 0, '#name#' => 'Не используя БД, по паролю указанным ниже'),
				array('#id#' => 1, '#name#' => 'Используя БД и привелегиий доступа')
			);
		break;
		case 'locallang':
			$cap = 'Локализация';
			$type = 'list';
			$valuelist = array();
			$dir = dir($_CFG['_PATH']['locallang']);
			while (false !== ($entry = $dir->read())) {
				if ($entry != '.' && $entry != '..' && strpos($entry, '.php')) {
					$entry = substr($entry, 0, -4);
					$valuelist[] = array('#id#' => $entry, '#name#' => $entry);
				}
			}
		break;
		case 'login':
			$cap = 'Босс-логин';
			break;
		case 'password':
			$cap = 'Босс-пароль';
			$type = 'password_new';
		break;
		case 'design':
			$cap = 'Дизайн админки';
			$type = 'list';
			$valuelist = array();
			$dir = dir($_CFG['_PATH']['cdesign']);
			while (false !== ($entry = $dir->read())) {
				if ($entry != '.' && $entry != '..') {
					$valuelist[] = array('#id#' => $entry, '#name#' => $entry);
				}
			}
		break;
		case 'msp':
			$cap = 'Модуль вывода постраничной навигации';
			$type = 'list';
			$valuelist = array(
				array('#id#' => '', '#name#' => 'Стандартное'),
				array('#id#' => 'paginator', '#name#' => 'paginator')
			);
		break;
		case 'md5':
			$cap = 'Соль для паролей';
		break;
		case 'def_filesize':
			$cap = 'Размер фаилового хранилища, Мб';
		break;
		case 'sessiontype':
			$cap = 'Тип хранени сессий пользователей';
			$type = 'list';
			$valuelist = array(
				array('#id#' => 0, '#name#' => 'Стандартное'),
				array('#id#' => 1, '#name#' => 'В базе данных')
			);
		break;
		case 'bug_hunter':
			$cap = 'Ловец жуков';
			$comm = 'Отлов ошибок';
			$type = 'list';
			foreach ($_CFG['_error'] as $ke=>$re) {
				$valuelist[] = array('#id#' => $ke, '#name#' => $re['type']);
			}
		break;
		case 'catch_bug':
			$cap = 'Основной сборщик ошибок';
			$comm = 'Системная - указывает на элемент в массиве $GLOBALS["_ERR"] в котором отлавливаются ошибки';
		break;
		case 'stop_fatal_error':
			$cap = 'Останавливать скрипт на фатальной ошибке?';
			$type = 'checkbox';
		break;
	}

	$DATA['wep[' . $k.']'] = array(
		'caption' => $cap,
		'comment' => $comm,
		'type' => $type,
		'value' => $r,
		'valuelist' => $valuelist,
		'css' => 'fblock hwep',
		'style' => 'display:none;'
	);
	if($k=='bug_hunter') {
		$DATA['wep[' . $k.']']['multiple'] = 1;
	}
	$valuelist = array();
}


/* SITE */
$DATA['showparam3'] = array('type' => 'info', 'caption' => '
	<div class="showparam" onclick="show_fblock(this,\'.hsite\')"> 
		<span class="shbg"></span>
		<span class="sh1">Показать</span>
		<span class="sh2">Скрыть</span> настройки всего сайта
	</div>');

foreach ($USER_CFG['site'] as $k => $r) {
	$type = 'text';
	$comm = '';
	$cap = $k;
	switch ($k) {
		case 'msp':
			$cap = 'Модуль вывода постраничной навигации';
			$type = 'list';
			$valuelist = array(
				array('#id#' => '', '#name#' => 'Стандартное'),
				array('#id#' => 'paginator', '#name#' => 'paginator')
			);
			break;
		case 'show_error':
			$cap = 'Показ ошибок';
			$type = 'list';
			$valuelist = array(
				array('#id#' => 0, '#name#' => 'ничего не показывать обычным юзерам'),
				array('#id#' => 1, '#name#' => 'паказывать только сообщение что произошла ошибка'),
				array('#id#' => 2, '#name#' => 'паказать ашипку :)')
			);
			break;
		case 'rf':
			$cap = 'Рускоязычный домен';
			$type = 'checkbox';
			break;
		case 'worktime':
			$type = 'checkbox';
			$cap = 'Включить режим "Технический перерыв"';
			break;
		case 'work_title':
			$cap = 'Заголовок для режима "Технический перерыв"';
			break;
		case 'work_text':
			$cap = 'Текст для режима "Технический перерыв"';
			break;
	}

	$DATA['site[' . $k.']'] = array(
		'caption' => $cap,
		'comment' => $comm,
		'type' => $type,
		'value' => $r,
		'valuelist' => $valuelist,
		'css' => 'fblock hsite',
		'style' => 'display:none;'
	);
	$valuelist = array();
}

$DATA['sbmt'] = array(
	'type' => 'submit',
	'value' => $var_const['sbmt']);

$DATA['formcreat'] = array('form' => $DATA);
$DATA['formcreat']['messages'] = $mess;
$html = $HTML->transformPHP($DATA, 'formcreat');
return $html;