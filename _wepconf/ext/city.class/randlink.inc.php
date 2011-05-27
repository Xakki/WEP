<?
	global $CITY;
	$html = '';
	if($CITY->parent_id) {
		$html = '<div class="copyright">';
		//if(!$CITY) _new_class('city',$CITY);
		/*$resultc = $CITY->SQL->execSQL('SELECT MAX(id) AS maxid FROM '.$CITY->tablename);
		if(!$resultc->err and $row = $resultc->fetch_array())
			$max = $row['maxid'];
		$ls = array();
		for($i = 1; $i <= 10; $i++) 
			$ls[] = rand(1,$max);*/
	//and id in ('.implode(',',$ls).')
		if($CITY->parent_id)
			$cls = 'and parent_id='.$CITY->parent_id;
		else
			$cls = '';

		$clause = 'SELECT name,'.($CITY->_CFG['site']['rf']?'domen_rf':'domen').' as domen, parent_id,id FROM '.$CITY->tablename.' WHERE active=1 '.$cls.' ORDER BY RAND() LIMIT 10';
		$resultc = $CITY->SQL->execSQL($clause);
		if(!$resultc->err)
			while ($row = $resultc->fetch_array()) {
				$html .= '<a href="http://'.$row['domen'].'.'.$_SERVER['HTTP_HOST2'].'">'.$row['name'].'</a>';
			}
		$html .= '</div>';
	}
	return $html;
