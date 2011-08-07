<?
	global $BOARD, $RUBRIC;
	if(!$BOARD) _new_class('board',$BOARD);
	if(!$RUBRIC) _new_class('rubric',$RUBRIC);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	$RUBRIC->simpleRubricCache();
	return '<div class="blockhead">Новые объявления</div><div class="hrb"></div>'.$HTML->transform($BOARD->fNewDisplay(10),'boardnew');
