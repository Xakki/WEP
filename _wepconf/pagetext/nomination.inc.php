<?
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) _new_class('board',$BOARD);
	if(!$RUBRIC) _new_class('rubric',$RUBRIC);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	$RUBRIC->simpleRubricCache();

	if(!count($RUBRIC->data2)) 
		return '';

	$html .= 'Номинации ';
	if($_GET['period']=='lastyear' or $_GET['period']=='month')
		$html .= '<a href="topcool.html?">'.date("Y").' года</a>';
	else
		$html .= '<b>'.date("Y").' года</b>';
	if($_GET['period']=='lastyear')
		$html .= ', <b>'.(date("Y")-1).' года</b>';
	else
		$html .= ', <a href="topcool.html?period=lastyear">'.(date("Y")-1).' года</a>';
	if($_GET['period']=='month')
		$html .= ', <b>прошедшего месяца</b>';
	else
		$html .= ', <a href="topcool.html?period=month">прошедшего месяца</a>';

	$i = 1;
	while(isset($BOARD->config['nomination'.$i])) {
		if($BOARD->config['nomination'.$i]!='') {
			$html .= '<h2>'.$BOARD->config['nomination'.$i].'</h2>';
			//
			$filter = array('nomination'.$i=>1,'nomination'.$i.'_2'=>0);

			if($_GET['period']=='month') 
				$filter['datea'] = mktime(0, 0, 0,date("m")-1, 1, date("Y"));
			elseif($_GET['period']=='lastyear') {
				$filter['datea'] = mktime(0, 0, 0,1, 1, date("Y")-1);
				$filter['datea_2'] = mktime(0, 0, 0,1, 1, date("Y"));
			}
			else
				$filter['datea'] = mktime(0, 0, 0,1, 1, date("Y"));
			$html .= $HTML->transform('<main>'.$BOARD->fListDisplay(0,$filter,0,'t1.nomination'.$i,3).'<i>'.$i.'</i></main>','nomination');
		}
		$i++;
	}

	return $html;
