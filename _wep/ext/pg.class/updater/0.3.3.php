<?php
//$MODUL - сам главный модуль
//$rDATA[$MODUL->_cl] - массив данных по обновлению

if(isset($_POST['sbmt'])) {
	if(isset($_POST['query_pg']['Обновление@newquery'])) {
		$MODUL->SQL->execSQL($rDATA[$MODUL->childs['content']->_cl]['keywords']['@newquery']);
		$MODUL->SQL->execSQL($rDATA[$MODUL->childs['content']->_cl]['description']['@newquery']);

		$temp = $MODUL->qs('id,keywords,description','WHERE keywords!="" or description!=""');
		foreach($temp as $r) {
			$dataup = array();
			if($r['keywords'])
				$dataup['keywords'] = $r['keywords'];
			if($r['description'])
				$dataup['description'] = $r['description'];
			$MODUL->childs['content']->qu($dataup,'owner_id='.$r['id'].' LIMIT 1');
		}
		$MODUL->SQL->execSQL($rDATA[$MODUL->_cl]['keywords']['@newquery']);
		$MODUL->SQL->execSQL($rDATA[$MODUL->_cl]['description']['@newquery']);
	}
}
else {

	$rDATA[$MODUL->_cl]['Обновление']['@newquery'] = 'Установить обновление v0.3.3';
	$rDATA[$MODUL->_cl]['@value'] = array('Обновление@newquery'=>true);
}

unset($rDATA[$MODUL->_cl]['keywords']['@newquery']);
unset($rDATA[$MODUL->_cl]['description']['@newquery']);

unset($rDATA[$MODUL->childs['content']->_cl]['keywords']['@newquery']);
unset($rDATA[$MODUL->childs['content']->_cl]['description']['@newquery']);

