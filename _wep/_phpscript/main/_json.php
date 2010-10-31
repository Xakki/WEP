<?
	if(!$_CFG['_PATH']['path'] or !$_CFG['_PATH']['wep']) die('ERROR');

	$GLOBALS['_RESULT']	= array();


	require_once($_CFG['_PATH']['path'].'/_wepconf/config/config.php');
	require_once($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');
	//require_once($_CFG['_PATH']['core'].'/html.php');	/**отправляет header и печатает страничку*/
	require_once($_CFG['_PATH']['core']."/sql.php");
	$SQL = new sql();

	if($_GET['_view']=='ajaxlist' and $_GET['_srlz']=stripslashes($_GET['_srlz']) and $_GET['_hsh']==md5($_GET['_srlz'].$_CFG['wep']['md5'])) {
		$listname = unserialize($_GET['_srlz']);
		if(isset($listname['class']) and $listname['class'])
			$listname['tablename'] = $_CFG['sql']['dbpref'].$listname['class'];
		

		if(!isset($listname['tx.id'])) 
			$listname['tx.id'] = 'tx.id';
		if(!isset($listname['tx.name'])) 
			$listname['tx.name'] = 'tx.name';

		$clause = 'SELECT '.$listname['tx.id'].' as id,'.$listname['tx.name'].' as name';
		$clause .= ' FROM `'.$listname['tablename'].'` tx ';
		if(isset($listname['join'])) {
			$clause .= $listname['join'];
		}
		$clause .= ' WHERE '.$listname['tx.name'].' LIKE "%'.mysql_real_escape_string($_GET['_value']).'%" ';
		if(isset($listname['where']) and is_array($listname['where']))
			$clause .= ' and '.implode(' and ',$listname['where']);
		elseif(isset($listname['where']) and $listname['where']!='')
			$clause .= ' and '.$listname['where'];

		if ($listname['ordfield'])
			$clause .= ' ORDER BY '.$listname['ordfield'];
		else
			$clause .= ' ORDER BY name';

		if (isset($listname['limit']))
			$clause .= ' LIMIT '.$listname['limit'];
		else
			$clause .= ' LIMIT 25';
		$result = $SQL->execSQL($clause);
		if(!$result->err) {
			while($row = $result->fetch_array())
				$GLOBALS['_RESULT']['data'][$row['id']] = $row['name'];
		}else
			print_r('NO VALID DATA');
		/*
		if($field['type']=='ajaxlist') {
			if($field['listname']['where']) $field['listname']['where'] .= ' and '.$_GET['tx.name'].' LIKE "%'.mysql_real_escape_string($_GET['_value']).'%"';
			$field['listname']['ordfield'] .= ' LIMIT 25';
			$md= $MODUL->_getCashedList($field['listname']);
			unset($md[0]);print_r($md);
			foreach ($md as $k=>$r) {
				$GLOBALS['_RESULT']['data'][$k] = $r;
			}
		}*/
	}
	else {
		print_r('NO VALID DATA');
	}
?>