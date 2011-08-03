<?
	$_CFG['_PATH']['wep'] = dirname(dirname(dirname(__FILE__))).'/_wep';

	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require_once($_CFG['_PATH']['core'].'/html.php');
	$HTML = new html('_design/',$_CFG['wep']['design'],false);// упрощённый режим

$c=0;
	_new_class('subscribe',$SUBSCRIBE);

	$listfields = array('*');
	$clause = 'WHERE active=1 and UNIX_TIMESTAMP()>(date+period-43200) LIMIT 100';
	$SUBSCRIBE->data = $SUBSCRIBE->_query($listfields,$clause,'id');
	if(count($SUBSCRIBE->data)) {
		_new_class('mail',$MAIL);
		_new_class('rubric',$RUBRIC);
		_new_class('city',$CITY);
		_new_class('board',$BOARD);
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
			$_GET['city'] = $r['city'];
			$CITY->citySelect($_GET['city']);
			$xml = $BOARD->fListDisplay($rid,$param);
			if($xml!='') {
				$datamail['from']='robot@unidoski.ru';
				$datamail['mail_to']=$r['email'];
				$datamail['subject']='Подписка на рассылки объявлений UNIDOSKI.RU';
				$datamail['text']='
<html>
<body>'
.$HTML->transform('<main><url>http://'.$_CFG['_HREF']['BH'].'/</url>'.$xml.'</main>','boardsubscribe')
.'<hr/>
<body><html>';
				$MAIL->reply = 0;
				if(!$MAIL->Send($datamail)){
					trigger_error('Подписка на объявл. - '.$this->_CFG['_MESS']['mailerr'], E_USER_WARNING);
				}
				$c++;
			}
		}
		$SUBSCRIBE->SQL->execSQL('update '.$SUBSCRIBE->tablename.' SET date='.time().' WHERE id IN ('.implode(',',array_keys($SUBSCRIBE->data)).')');
	}
	echo 'Отослано '.$c.' писем';

