<?php

	$html='';
	$_GET['_showallinfo']=0;
	$_CFG['site']['show_error'] = 0;

	require_once($_CFG['_PATH']['path'].'/_wep/config/config.php');
	require_once($_CFG['_PATH']['core'].'/html.php');	/**отправляет header и печатает страничку*/
	$_COOKIE['_showerror'] = 0;

	_new_class('city',$CITY);
	$CITY->citySelect((int)$_GET['city']);
	_new_class('rubric',$RUBRIC);
	_new_class('board',$BOARD);

	$BOARD->messages_on_page = 40;
	$BOARD->RUBRIC = &$RUBRIC;

	$RUBRIC->simpleRubricCache();
	//if(!count($RUBRIC->data2)) return 'Нет данных';
	$rid= (int)$_GET['rubric'];
	$html = $BOARD->fListDisplay($rid,$_GET,1,'t1.datea',20);
	//}
	if(isset($BOARD->data) and is_array($BOARD->data) and count($BOARD->data)) {
		reset($BOARD->data);
		$dt = current($BOARD->data);
		$dt = date('r',$temp['datea']);
	}else
		$dt = date('r');
	if($rid) {
		$title = 'УниДоски.ру - '.$RUBRIC->data2[$rid]['name'];
	}
	else
		$title = 'УниДоски.ру';
	header("content-type: application/rss+xml");
	echo '<?phpxml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<title>'.$title.'</title>
<link>http://'.$_SERVER['HTTP_HOST'].'</link>
<description>Универсальная доска объявлений. Лучшее средство для поиска и размещения объявления.</description>
<language>ru-ru</language>
<lastBuildDate>'.$dt.'</lastBuildDate>
<generator>WebEngineOnPHP</generator>
<managingEditor>editor@unidoski.ru</managingEditor>
<webMaster>webmaster@unidoski.ru</webMaster>
'.
$html
.'</channel></rss>';


