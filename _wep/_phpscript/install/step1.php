<?
$_SESSION['step'] = 1;

//Подключение к БД и доп параметры
$edit_cfg = array(
	'sql' => true,
	'memcache' => true,
	'wep' => true,
	'site' => true,
);

/**
 * Сбор переменных хранящихся в фаиле
 * @param <type> $file Фаил из которого будут браться данные о перменных
 * @param <type> $start Не обязательная, указывает строку после которой начинается сбор полезных строк
 * @param <type> $end не обязательная, указывает строку до которой будет сбор строк
 * @param <type> $mData не обязательно, дефолтное значение отслеживаемой переменной
 * @return <type> Возвращает массив полученных данных $_CFG
 */
function getFdata($file, $start='', $end='', $mData = false) {
	$fc = '';
	if ($start == '' and $end == '') {
		$fc = file_get_contents($file);
	} else {
		$fc = false;
		$file = file($file);
		foreach ($file as $k => $r) {
			if ($fc === false and strpos($r, $start) !== false)
				$fc = '';
			elseif (strpos($r, $end) !== false)
				break;
			if ($fc !== false)
				$fc .= $r . "\n";
		}
	}
	$fc = trim($fc, "<?>\n");
	if ($mData !== false) {
		$_CFG = $mData;
	}

	if ($fc)
		eval($fc);
	else
		print_r('NO CFG');

	return $_CFG;
}

$mess = array();
$DEF_CFG = getFdata($_CFG['_PATH']['wep'] . '/config/config.php', '/* MAIN_CFG */', '/* END_MAIN_CFG */');
$USER_CFG = getFdata($_CFG['_PATH']['wepconf'] . '/config/config.php', '', '', $DEF_CFG);

if (isset($_POST['sbmt'])) {
	$fl = false;
	$NEW_USER_CFG = array();
	foreach ($edit_cfg as $k => $r) {
		foreach ($_CFG[$k] as $kk => $rr) {
			if (isset($_POST[$k . '_' . $kk]) and (!isset($DEF_CFG[$k][$kk]) or $_POST[$k . '_' . $kk] != $DEF_CFG[$k][$kk])) {
				$NEW_USER_CFG[$k . '_' . $kk] = '$_CFG["' . $k . '"]["' . $kk . '"] = \'' . addcslashes($_POST[$k . '_' . $kk], '\'') . '\';';
				$USER_CFG[$k][$kk] = $_POST[$k . '_' . $kk];
			}
		}
	}
	$_CFG = array_merge($_CFG, $USER_CFG);
	$_CFG['wep']['access'] = 0;
	$_CFG['wep']['sessiontype'] = 0;
	$_CFG['site']['bug_hunter'] = 0;
	$_CFG['sql']['log'] = 0;
	$_CFG['site']['show_error'] = 2;

	$SQL = new sql($USER_CFG['sql']); //пробуемподключиться к БД

	$putFile = "<?\n\t//create time " . date('Y-m-d H:i') . "\n\t";
	$putFile .= implode("\n\t", $NEW_USER_CFG);
	$putFile .= "\n?>";

	//Записать в конфиг все данные которые отличаются от данных по умолчанию
	if (!file_put_contents($_CFG['_PATH']['wepconf'] . '/config/config.php', $putFile)) {
		$mess[] = array('name' => 'error', 'value' => 'Ошибка записи настроек. Нет доступа к фаилу');
	} else {
		$mess[] = array('name' => 'ok', 'value' => 'Подключение к БД успешно.');
		$mess[] = array('name' => 'ok', 'value' => 'Конфигурация успешно сохранена.');
		$mess[] = array('name' => 'ok', 'value' => 'Пора перейти к <a href="'.$_CFG['PATH']['wepname'].'/install.php?step=' . ($_GET['step'] + 1) . '">следующему шагу №' . ($_GET['step'] + 1) . '</a>');
		$DATA['messages'] = $mess;
		$_SESSION['step'] = 2;
		return $html = $HTML->transformPHP($DATA, 'messages');
		//@header('Location: install.php?step=' . ($_GET['step'] + 1));
		//die('<a href="install.php?step=' . ($_GET['step'] + 1) . '">Следующий шаг</a>');
	}
} else {
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

	$DATA['sql_' . $k] = array(
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

	$DATA['memcache_' . $k] = array(
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
			$cap = 'Включить отлов ошибок';
			$type = 'checkbox';
			break;
	}

	$DATA['wep_' . $k] = array(
		'caption' => $cap,
		'comment' => $comm,
		'type' => $type,
		'value' => $r,
		'valuelist' => $valuelist,
		'css' => 'fblock hwep',
		'style' => 'display:none;'
	);
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
	$cap = '';
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
			$cap = 'Тип хранени сессий пользователей';
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
		case 'bug_hunter':
			$cap = 'Включить отлов ошибок';
			$type = 'checkbox';
			break;
	}

	$DATA['site_' . $k] = array(
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
	'value' => 'Сохранить и перейти на следующий шаг');

$DATA['formcreat'] = array('form' => $DATA);
$DATA['formcreat']['messages'] = $mess;
$html = $HTML->transformPHP($DATA, 'formcreat');
return $html;
?>