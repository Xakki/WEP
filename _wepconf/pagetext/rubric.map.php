<?
	//$keyPG - id страницы
	global $BOARD, $RUBRIC, $CITY;
	_new_class('city',$CITY);
	_new_class('board',$BOARD);
	_new_class('rubric',$RUBRIC);
	$BOARD->RUBRIC = &$RUBRIC;

	$datamap = array();
	if(!$RUBRIC->data3) $RUBRIC->RubricCache();

	function rubGetMap(&$data,$id,$kPG) {
		global $RUBRIC, $CITY;
		$s = array();
		if (isset($data[$id]) and is_array($data[$id]) and count($data[$id]))
			foreach ($data[$id] as $key => $value)
			{
				$s[$key] = $value;
				$s[$key]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$RUBRIC->data2[$key]['lname'].'/'.$kPG.'.html';
				if ($key!=$id and isset($data[$key]) and count($data[$key]) and is_array($data[$key])) {
					if(!$CITY->id) 
						$s[$key]['hidechild'] =1;
					$s[$key]['#item#'] = rubGetMap($data,$key,$kPG);
				}
			}

		return $s;
	}
	$datamap = rubGetMap($RUBRIC->data3,0,$keyPG);
	if(!$CITY->id) 
		$DATA_PG[$keyPG]['hidechild'] =1;
	return $datamap;

