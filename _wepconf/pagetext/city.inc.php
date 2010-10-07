<?
	global $CITY;
/*
	if($_GET['go']=='1dsdsdsdsd') {
		$dc= array();
		$CITY = new city_class($SQL);
		$q = "SELECT * FROM ".$CITY->tablename;
		$result = $SQL->execSQL($q);
		if($result->err) return 'ошибка запроса';

		include_once($_CFG['_PATH']['phpscript'].'translit.php');

		while ($row = $result->fetch_array()) {
			$dc[$row['id']] = latrus($row['name']);
		}
		foreach($dc as $k=>$r) {
			$q = "UPDATE ".$CITY->tablename.' SET name="'.$r.'"  WHERE id='.$k;
			$result = $SQL->execSQL($q);
		}
		return 'Успешно';
	}
	*/
		$html='';
		if(!$CITY) $CITY = new city_class($SQL);
		$html = $HTML->transform('<main>'.$CITY->cityDisplay().'</main>','citymain');
		return $html;

?>