<?php
	if(!$_CFG['_PATH']['wep']) die('ERROR');

	$GLOBALS['_RESULT']	= array('html'=>'','eval'=>'');
	$_tpl['onload']=$html=$html2='';

	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	if (!isset($_GET['html']))
		require_once($_CFG['_PATH']['wep_phpscript'].'/jquery_getjson.php');
	require_once($_CFG['_PATH']['core'].'/sql.php');	/**отправляет header и печатает страничку*/
	$SQL = new sql($_CFG['sql']);

	if(isset($_GET['_fn']) and $_GET['_fn']) {
		session_go();
		if(_new_class($_GET['_modul'],$MODUL) and isset($MODUL->_AllowAjaxFn[$_GET['_fn']])) {
			eval('$GLOBALS[\'_RESULT\']=$MODUL->'.$_GET['_fn'].'();');
		} else
			$GLOBALS['_RESULT']['text'] = 'Вызов функции не разрешён модулем.';
		
	} 
	elseif(isset($_GET['_view']) && $_GET['_view']=='ajaxlist' and $_GET['_srlz']=stripslashes($_GET['_srlz']) and $_GET['_hsh']==md5($_GET['_srlz'].$_CFG['wep']['md5'])) {
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
			$GLOBALS['_RESULT']['data'] = array();
			while($row = $result->fetch_array())
				$GLOBALS['_RESULT']['data'][] = array($row['id'],$row['name']);
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
	elseif(isset($_REQUEST['_view']) && $_REQUEST['_view']=='loadpage') {
		require_once($_CFG['_PATH']['core'].'/html.php');
		$_COOKIE['_showallinfo'] = 0;
		$_COOKIE['_showerror'] = 0;
		$DATA  = array();
		session_go();
		_new_class('pg',$PGLIST);
		$_tpl = array();
		$PGLIST->id = $_REQUEST['pgId'];
		$PGLIST->display(false);
		//$_tpl['logs'] = '';
		//print_r('<pre>');print_r($_tpl);
		//$GLOBALS['_RESULT']['html2'] = $_tpl;
		foreach($_REQUEST as $k=>$r) {
			if($k=='onload') {
				$GLOBALS['_RESULT']['eval'] .= $_tpl[$k];
			}
			else if(isset($_tpl[$k])) {
				$GLOBALS['_RESULT']['pg_'.$k] = $_tpl[$k];
				if(!isset($_REQUEST['onlyget'])) {
					if(is_array($r)) {
						foreach($r as $vk=>$vr)
							$GLOBALS['_RESULT']['eval'] = 'jQuery(\''.$vr.'\').'.$vk.'(result.pg_'.$k.');'.$GLOBALS['_RESULT']['eval'];
					} else//replaceWith
						$GLOBALS['_RESULT']['eval'] = 'jQuery(\''.$r.'\').html(result.pg_'.$k.');'.$GLOBALS['_RESULT']['eval'];
				}
			}
		}
		//print_r('<pre>');print_r($GLOBALS['_RESULT']);
	}
	elseif (isset($_POST['wepID'])) {
		if (isset($_FILES['Filedata']) && $_FILES['Filedata']['error'] == 0) {
//			session_id(mysql_real_escape_string((string)$_POST['wepID']));
//			session_go();
//			file_put_contents($_CFG['_PATH']['path'].'test.txt', 'aaa '.var_export($_SESSION, true));

			$ext_list = array(
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png',
			);
			$parts = explode('.', $_FILES['Filedata']['name']);
			$ext = end($parts);
			
			if (isset($ext_list[$ext]))	{
				$temp_name = substr(md5(getmicrotime()),16) . '.' . $ext;
				$temp_path = $_CFG['_PATH']['temp'].$temp_name;
				static_tools::_checkdir($_CFG['_PATH']['temp']);
				if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $temp_path)){
					$_FILES['Filedata']['tmp_name']= $temp_name;
					$GLOBALS['_RESULT']['swf_uploader'] = array(
						'name' => $temp_name,
						'path' => $_CFG['PATH']['temp'],
						'mime_type' => $ext_list[$ext],
					);
				}
			}
			else {
				$GLOBALS['_RESULT']['error'] = 'Неверный тип файла';
			}
			


//			file_put_contents($_CFG['_PATH']['path'].'test.txt', 'aaa '.var_export($_FILES, true));
			
			
//			$_SESSION['swf_upload_file'][] = $_FILES['Filedata'];
		}
	}
	else {
		print('NO VALID DATA');
	}
	
