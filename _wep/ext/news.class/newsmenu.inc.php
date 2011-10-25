<?php
	$FUNCPARAM = explode('&',$FUNCPARAM);
	//$FUNCPARAM[0] - группировка [ndate, active]
	//$FUNCPARAM[1] - php template

		if(!_new_class('news',$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль news не установлен</div>';
		}
		else {
			if(!$FUNCPARAM[0]) $FUNCPARAM[0] = 'ndate';
			if(!$FUNCPARAM[1]) $FUNCPARAM[1] = 'newsmenu';
			$DATA = $NEWS->fMenuNews($FUNCPARAM[0]);
			$html = $HTML->transformPHP($DATA,$FUNCPARAM[1]);
		}
	return $html;
