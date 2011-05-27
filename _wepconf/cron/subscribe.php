<?
	require("../system/config.ini.php");
	require("../system/sql.class.php");
	$SQL = new class_sql();
	$HTML = new class_html('design/',$_CFG['wep']['design'],false);// упрощённый режим
$c=0;
	$SUBSCRIBE = new subscribe_class($SQL);

	$listfields = array('*');
	$clause = 'WHERE active=1 and UNIX_TIMESTAMP()>(date+period-43200) LIMIT 100';
	$SUBSCRIBE->_query($listfields,$clause,'id');
	if(count($SUBSCRIBE->data)) {
		$MAIL = new mail_class($SQL);
		$RUBRIC = new rubric_class($SQL);
		$CITY = new city_class($SQL);
		$BOARD = new board_class($SQL);
		$BOARD->RUBRIC = &$RUBRIC;
		$RUBRIC->simpleRubricCache();
		foreach($SUBSCRIBE->data as $k=>$r) {
			$r['city'];
			$r['email'];
			$r['date'];
			$param = array();
			$temp = explode('&',$r['param']);
			foreach($temp as $row) {
				$temp2 = explode('=',$row);
				if($temp2[0] and $temp2[1])
					$param[$temp2[0]] = $temp2[1];
			}
			$param['datea'] = $r['date'];
			$rid = (int)$param['rubric'];
			$_GET["city"] = $r['city'];
			$CITY->citySelect($_GET["city"]);
			$xml = $BOARD->fListDisplay($rid,$param);
			if($xml!='') {
				$datamail['from']='robot@unidoski.ru';
				$datamail['mailTo']=$r['email'];
				$datamail['subject']='Подписка на рассылки объявлений UNIDOSKI.RU';
				$datamail['text']='
<html>
<body>'
.$HTML->transform('<main><url>http://'.$_SERVER['CITY_HOST'].'/</url>'.$xml.'</main>','boardsubscribe')
.'<hr/>
<body><html>';
				$MAIL->reply = 0;
				$MAIL->Send($datamail);
				$c++;
			}
		}
		$SQL->execSQL('update '.$SUBSCRIBE->tablename.' SET date='.time().' WHERE id IN ('.implode(',',array_keys($SUBSCRIBE->data)).')');
	}
	echo 'Отослано '.$c.' писем';

