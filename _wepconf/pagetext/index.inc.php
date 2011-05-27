<?
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) $BOARD = new board_class($SQL);
	if(!$RUBRIC) $RUBRIC = new rubric_class($SQL);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	if(isset($_GET['reloads'])) $BOARD->servUpdate();

	$html = $HTML->transform($RUBRIC->MainRubricDisplay(),'rubricmain');

	return $html;
