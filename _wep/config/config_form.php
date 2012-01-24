<?php

$_CFGFORM['sql'] = array(// SQL
	'host' => array('type'=>'text','caption'=>'Хост подключения к БД', 'mask'=>array('min'=>9)),
	'login' =>  array('type'=>'text','caption'=>'Логин БД', 'mask'=>array('min'=>2)),
	'password' =>  array('type'=>'password','caption'=>'Пароль БД', 'mask'=>array('min'=>6)),
	'database' =>  array('type'=>'text','caption'=>'Название БД', 'mask'=>array('min'=>2)),
	'showparam1'=>array('type' => 'info', 'caption' => '
	<div class="showparam" onclick="show_fblock(this,\'.hsql\')"> 
		<span class="shbg"></span>
		<span class="sh1">Показать</span>
		<span class="sh2">Скрыть</span> ещё настройки БД
	</div>'),
	'setnames' =>  array('type'=>'text','caption'=>'Кодировка БД', 'css' => 'fblock hsql', 'style' => 'display:none;'),
	'dbpref' =>  array('type'=>'text','caption'=>'Префикс в названии таблиц', 'css' => 'fblock hsql', 'style' => 'display:none;'),
	'log' =>  array('type'=>'checkbox','caption'=>'Логирование всех запросов в фаил', 'css' => 'fblock hsql', 'style' => 'display:none;'),
	'longquery' =>  array('type'=>'int','caption'=>'Логировать долгие запросы к БД','comment'=>'запись в баг запросы которые выполняются дольше указанного времени в сек', 'css' => 'fblock hsql', 'style' => 'display:none;'),
);

$_CFGFORM['wep'] = array(// для ядра и админки
	'login' => array('type'=>'text','caption'=>'Мастер-логин', 'mask'=>array('min'=>4)),
	'password' => array('type'=>'password','caption'=>'Мастер-пароль', 'mask'=>array('min'=>8)),
	'md5' => array('type'=>'text','caption'=>'Соль для паролей', 'mask'=>array('min'=>5)),
	'showparam2'=>array('type' => 'info', 'caption' => '
	<div class="showparam" onclick="show_fblock(this,\'.hwep\')"> 
		<span class="shbg"></span>
		<span class="sh1">Показать</span>
		<span class="sh2">Скрыть</span> ещё настройки ядра
	</div>'),
	'charset' => array('type'=>'text','caption'=>'Кодировка сайта', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'setlocale' => array('type'=>'text','caption'=>'Локаль ПХП', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'locale' => array('type'=>'list', 'caption'=>'Локализация сайта', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'timezone' => array('type'=>'list', 'caption'=>'Временная зона', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'dateformat' => array('type'=>'text','caption'=>'Формат Даты', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'access' => array('type'=>'list', 'caption'=>'Система авторизации на сайте', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'design' => array('type'=>'list','caption'=>'Дизайн админки', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'lang' => array('type'=>'text','caption'=>'Язык', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'def_filesize' => array('type'=>'int','caption'=>'Размер фаилового хранилища, Мб', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'chmod'=> array('type'=>'text','caption'=>'chmod', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'sessiontype' => array('type'=>'list','caption'=>'Тип хранени сессий пользователей', 'css' => 'fblock hwep', 'style' => 'display:none;'),

	'bug_hunter' => array('type'=>'list','multiple'=>1, 'caption'=>'Ловец жуков', 'comment'=>'Отлов ошибок', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	//'catch_bug' => array('type'=>'text','caption'=>'Основной сборщик ошибок', 'comment'=>'Системная - указывает на элемент в массиве $GLOBALS["_ERR"] в котором отлавливаются ошибки', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'error_reporting' => array('type'=>'text','caption'=>'php error_reporting', 'comment'=>'битовая маска, -1 выводит всё виды ошибок', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	//'stop_fatal_error' => array('type'=>'checkbox','caption'=>'Останавливать скрипт на фатальной ошибке?', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	//'show_error' => array('type'=>'list', 'caption'=>'Показывать ошибки всем?', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'debugmode' => array('type'=>'list', 'caption'=>'DEBUG MODE', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'_showerror' => array('type'=>'text','caption'=>'GET[_showerror] - для вывода ошибок', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	'_showallinfo' => array('type'=>'text','caption'=>'GET[_showallinfo] - для вывода инфы', 'css' => 'fblock hwep', 'style' => 'display:none;'),
	//'cron'=> array('type'=>'text','caption'=>'cron', 'css' => 'fblock hwep', 'style' => 'display:none;'),
);

$_CFGFORM['site'] = array(// для сайта
	'www' => array('type'=>'text','caption'=>'HTTP_HOST'),
	'showparam3'=>array('type' => 'info', 'caption' => '
	<div class="showparam" onclick="show_fblock(this,\'.hsite\')"> 
		<span class="shbg"></span>
		<span class="sh1">Показать</span>
		<span class="sh2">Скрыть</span> настройки всего сайта
	</div>'),
	//'rf' => array('type'=>'checkbox','caption'=>'Рускоязычный домен', 'css' => 'fblock hsite', 'style' => 'display:none;'),
	'worktime' => array('type'=>'checkbox','caption'=>'Включить режим "Технический перерыв"', 'css' => 'fblock hsite', 'style' => 'display:none;'),
	'work_title' => array('type'=>'text','caption'=>'Заголовок для режима "Технический перерыв"', 'css' => 'fblock hsite', 'style' => 'display:none;'),
	'work_text' => array('type'=>'text','caption'=>'Текст для режима "Технический перерыв"', 'css' => 'fblock hsite', 'style' => 'display:none;'),
	//'redirectForRobots' => array('type'=>'checkbox','caption'=>'Делать ссылки для Ботов', 'css' => 'fblock hsite', 'style' => 'display:none;'),
);

$_CFGFORM['memcache'] = array(
	'showparam1'=>array('type' => 'info', 'caption' => '
	<div class="showparam" onclick="show_fblock(this,\'.hmemcache\')"> 
		<span class="shbg"></span>
		<span class="sh1">Показать</span>
		<span class="sh2">Скрыть</span> настройки Memcache
	</div>'),
	'host' => array('type'=>'text','caption'=>'Хост подключения к Memcache', 'css' => 'fblock hmemcache', 'style' => 'display:none;'),
	'port' => array('type'=>'text','caption'=>'Порт подключения к Memcache', 'css' => 'fblock hmemcache', 'style' => 'display:none;'),
);

	$_CFGFORM['wep']['timezone']['valuelist'] = array();
	$timezone_identifiers = DateTimeZone::listAbbreviations();
	foreach($timezone_identifiers as $tt=>$td) {
		if($td[0]['timezone_id']) {
			$ofset = $td[0]['offset']/3600;
			if($ofset>0) $ofset = '+'.$ofset;
			$ofset = (int)$ofset;
			$_CFGFORM['wep']['timezone']['valuelist'][$td[0]['offset']] = array('#id#' => $td[0]['timezone_id'], '#name#' => $ofset.'ч '.(int)(($td[0]['offset']/60)-$ofset*60).' мин GMT','#checked#'=>0);
			foreach($td as $tt2=>$td2) 
				$_CFGFORM['wep']['timezone']['valuelist'][$td[0]['offset']]['#item#'][] = array('#id#' => $td2['timezone_id'], '#name#' => $td2['timezone_id']);
		}
	}
	ksort($_CFGFORM['wep']['timezone']['valuelist'],SORT_NUMERIC );

	$_CFGFORM['wep']['access']['valuelist'] = array(
		array('#id#' => 0, '#name#' => 'Не используя БД, по ROOT-логину и ROOT-паролю'),
		array('#id#' => 1, '#name#' => 'Используя БД и привелегиий доступа')
	);

	$_CFGFORM['wep']['locale']['valuelist'] = array();
	$dir = dir($_CFG['_PATH']['wep_locallang']);
	while (false !== ($entry = $dir->read())) {
		if ($entry != '.' && $entry != '..' && strpos($entry, '.php')) {
			$entry = substr($entry, 0, -4);
			$_CFGFORM['wep']['locale']['valuelist'][] = array('#id#' => $entry, '#name#' => $entry);
		}
	}

	$_CFGFORM['wep']['design']['valuelist'] = array();
	$dir = dir($_CFG['_PATH']['cdesign']);
	while (false !== ($entry = $dir->read())) {
		if ($entry != '.' && $entry != '..') {
			$_CFGFORM['wep']['design']['valuelist'][] = array('#id#' => $entry, '#name#' => $entry);
		}
	}

	$_CFGFORM['wep']['sessiontype']['valuelist'] = array(
		array('#id#' => 0, '#name#' => 'Стандартное'),
		array('#id#' => 1, '#name#' => 'В базе данных')
	);

	$_CFGFORM['wep']['bug_hunter']['valuelist'] = array();
	foreach ($_CFG['_error'] as $ke=>$re) {
		$_CFGFORM['wep']['bug_hunter']['valuelist'][] = array('#id#' => $ke, '#name#' => $re['type']);
	}

	$_CFGFORM['wep']['debugmode']['valuelist'] = array(
		array('#id#' => 0, '#name#' => 'OFF (ошибки не показывать)'),
		array('#id#' => 1, '#name#' => 'ON (только о том что была ошибка)'),
		array('#id#' => 2, '#name#' => 'Full ON (все видят ошибки)')
	);

?>