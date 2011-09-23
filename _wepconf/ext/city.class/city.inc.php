<?php
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
		_new_class('city',$CITY);
		if(isset($this->pageParam[0]) and $CITY->citySelect($this->pageParam[0])) {
			$loc = 'Location: http://'.$CITY->domen.'.'.$_SERVER['HTTP_HOST2'];
			header("HTTP/1.0 301");
			header($loc);
			die($loc);
		}
		$html='';
		$html = $HTML->transform('<main>'.$CITY->cityDisplay().'</main>','citymain');
		return $html;

