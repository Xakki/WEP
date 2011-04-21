<?
	global $CITY;
/*
	if($_GET['go']=='1dsdsdsdsd') {
		$dc= array();
		$CITY = new city_class($SQL);
		$q = "SELECT * FROM ".$CITY->tablename;
		$cityresult = $SQL->execSQL($q);
		if($result->err) return 'ошибка запроса';

		include_once($_CFG['_PATH']['phpscript'].'/translit.php');

		while ($row = $cityresult->fetch_array()) {
			$dc[$row['id']] = latrus($row['name']);
		}
		foreach($dc as $k=>$r) {
			$q = "UPDATE ".$CITY->tablename.' SET name="'.$r.'"  WHERE id='.$k;
			$SQL->execSQL($q);
		}
		return 'Успешно';
	}
*/
		$html='';
		if(!$CITY) _new_class('city',$CITY);
		$html = $HTML->transform('<main>'.$CITY->cityDisplay().'</main>','citymain');
		return $html;

?>