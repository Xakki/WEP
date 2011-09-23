<?php
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) _new_class('board',$BOARD);
	if(!$RUBRIC) _new_class('rubric',$RUBRIC);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;
	$html = '';
	/*if(static_main::_prmUserCheck(1)) {
		if(isset($_GET['reloads'])) {
			$tt  = getmicrotime();
			$BOARD->servUpdate();
			$html .= ' -= Обновлено! '.(getmicrotime()-$tt).'mc=- ';
		}
		if(isset($_GET['clear'])) {
			$tt  = getmicrotime();
			$BOARD->servClear();
			$html .= ' -= Ощищено! '.(getmicrotime()-$tt).'mc=- ';
		}
	}*/

	$html .= $HTML->transform($RUBRIC->MainRubricDisplay(),'rubricmain');

	return $html;
