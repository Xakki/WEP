<?
	if(!$_CFG['_PATH']['wep']) die('ERROR');

	$GLOBALS['_RESULT']	= array();

	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	if (!isset($_GET['html']))
		require_once($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');
	require_once($_CFG['_PATH']['core'].'/sql.php');	/**отправляет header и печатает страничку*/
	$SQL = new sql($_CFG['sql']);

	if($_GET['_fn']) {
		session_go();
		if(_getChildModul($_GET['_modul'],$MODUL) and isset($MODUL->_AllowAjaxFn[$_GET['_fn']])) {
			eval('$GLOBALS[\'_RESULT\']=$MODUL->'.$_GET['_fn'].'();');
		} else
			$GLOBALS['_RESULT']['text'] = 'Вызов функции не разрешён модулем.';
		
	} 
	elseif($_GET['_view']=='ajaxlist' and $_GET['_srlz']=stripslashes($_GET['_srlz']) and $_GET['_hsh']==md5($_GET['_srlz'].$_CFG['wep']['md5'])) {
		$listname = unserialize($_GET['_srlz']);
		if(!isset($listname['tablename']) and isset($listname['class']) and $listname['class'])
			$listname['tablename'] = $_CFG['sql']['dbpref'].$listname['class'];
		

		if(!isset($listname['idField'])) 
			$listname['idField'] = 'tx.id';
		if(!isset($listname['nameField'])) 
			$listname['nameField'] = 'tx.name';

		$clause = 'SELECT '.$listname['idField'].' as id,'.$listname['nameField'].' as name';
		$clause .= ' FROM `'.$listname['tablename'].'` tx ';
		if(isset($listname['join'])) {
			$clause .= $listname['join'];
		}
		$clause .= ' WHERE '.$listname['nameField'].' LIKE "%'.mysql_real_escape_string($_GET['_value']).'%" ';
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
			print('NO VALID DATA');
		/*
		if($field['type']=='ajaxlist') {
			if($field['listname']['where']) $field['listname']['where'] .= ' and '.$_GET['nameField'].' LIKE "%'.mysql_real_escape_string($_GET['_value']).'%"';
			$field['listname']['ordfield'] .= ' LIMIT 25';
			$md= $MODUL->_getCashedList($field['listname']);
			unset($md[0]);
			foreach ($md as $k=>$r) {
				$GLOBALS['_RESULT']['data'][$k] = $r;
			}
		}*/
	}
	elseif($_REQUEST['_view']=='loadpage') {
		require_once($_CFG['_PATH']['core'].'/html.php');
		$_COOKIE['_showallinfo'] = 0;
		$_COOKIE['_showerror'] = 0;
		$DATA  = array();
		session_go();
		_new_class('pg',$PGLIST);
		$_tpl = array();
		$PGLIST->id = $_REQUEST['pg'];
		$PGLIST->display(false);
		//$_tpl['logs'] = '';
		//print_r('<pre>');print_r($_tpl);
		//$GLOBALS['_RESULT']['html2'] = $_tpl;
		foreach($_POST as $k=>$r) {
			if(isset($_tpl[$k])) {
				$GLOBALS['_RESULT']['pg_'.$k] = $_tpl[$k];
				if(is_array($r)) {
					foreach($r as $vk=>$vr)
						$GLOBALS['_RESULT']['eval'] .= 'jQuery(\''.$vr.'\').'.$vk.'(result.pg_'.$k.');';
				} else//replaceWith
					$GLOBALS['_RESULT']['eval'] .= 'jQuery(\''.$r.'\').html(result.pg_'.$k.');';
			}
		}
		//print_r('<pre>');print_r($GLOBALS['_RESULT']);
	}
	else {
		print('NO VALID DATA');
	}