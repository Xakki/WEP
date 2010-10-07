<?
	global $BOARD, $RUBRIC;
	if(!$BOARD) $BOARD = new board_class($SQL);
	if(!$RUBRIC) $RUBRIC = new rubric_class($SQL);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	$RUBRIC->simpleRubricCache();
	return '<div class="blockhead">Новые объявления</div><div class="hrb"></div>'.$HTML->transform($BOARD->fNewDisplay(10),'boardnew');
?>