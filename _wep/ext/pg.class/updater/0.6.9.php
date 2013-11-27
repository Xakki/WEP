<?php
//$MODUL - сам главный модуль
//$rDATA[$MODUL->_cl] - массив данных по обновлению

if (isset($_POST['sbmt'])) {
	if (isset($_POST['query_pg']['Обновление@newquery'])) {

		$MODUL->SQL->execSQL('UPDATE ' . $MODUL->childs['content']->tablename . ' SET styles=replace(styles, \'../default/style/\', \'#themes#\'), script=replace(script, \'../default/script/\', \'#themes#\')');
	}
}
else {

	$rDATA[$MODUL->_cl]['Обновление']['@newquery'] = 'Установить обновление v0.6.9';
	$rDATA[$MODUL->_cl]['@value'] = array('Обновление@newquery' => true);
}
