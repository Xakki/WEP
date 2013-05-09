<?php
//$MODUL - сам главный модуль
//$rDATA[$MODUL->_cl] - массив данных по обновлению

if(isset($_POST['sbmt'])) {
	global $_CFG;
	eval('$datac=' . file_get_contents($_CFG['_PATH']['configDir'] .'payqiwi_class.cfg') . ';');
	foreach($datac as $ck=>$cr)
		$MODUL->config['qiwi_'.$ck] = $cr;
	static_tools::_save_config($MODUL->config, $MODUL->_file_cfg);
}

