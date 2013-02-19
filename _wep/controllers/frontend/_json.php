<?php
	if(!$_CFG['_PATH']['wep']) die('ERROR');

	$GLOBALS['_RESULT']	= array('html'=>'','onload'=>'');
	$_tpl['onload']=$html=$html2='';


	if(isset($_GET['_fn']) and $_GET['_fn']) {
		session_go();
		if(_new_class($_GET['_modul'],$MODUL) and isset($MODUL->_AllowAjaxFn[$_GET['_fn']])) {
			eval('$GLOBALS[\'_RESULT\']=$MODUL->'.$_GET['_fn'].'();');
		} else
			$GLOBALS['_RESULT']['text'] = 'Вызов функции не разрешён модулем.';
		
	} 
	elseif(isset($_GET['_view']) && $_GET['_view']=='ajaxlist' and $_GET['_srlz']=stripslashes($_GET['_srlz']) and $_GET['_hsh']==md5($_GET['_srlz'].$_CFG['wep']['md5'])) {
		$SQL = new $_CFG['sql']['type']($_CFG['sql']);

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
		$clause .= ' WHERE '.$listname['nameField'].' LIKE "%'.$SQL->SqlEsc($_GET['_value']).'%" ';
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
			while($row = $result->fetch())
				$GLOBALS['_RESULT']['data'][] = array($row['id'],$row['name']);
		}else
			print('NO VALID DATA');
		/*
		if($field['type']=='ajaxlist') {
			if($field['listname']['where']) $field['listname']['where'] .= ' and '.$_GET['nameField'].' LIKE "%'.$SQL->SqlEsc($_GET['_value']).'%"';
			$field['listname']['ordfield'] .= ' LIMIT 25';
			$md= $MODUL->_getCashedList($field['listname']);
			unset($md[0]);
			foreach ($md as $k=>$r) {
				$GLOBALS['_RESULT']['data'][$k] = $r;
			}
		}*/
	}
	elseif(isset($_REQUEST['_view']) && $_REQUEST['_view']=='loadpage') 
	{

		$DATA  = array();
		session_go();
		_new_class('pg',$PGLIST);

		if(!isset($_GET['_design']))
			$_GET['_design'] = $_CFG['wep']['design'];

		if(isset($_REQUEST['_pgId'])) {
			$PGLIST->ajaxRequest = true;
			$PGLIST->id = (int)$_REQUEST['_pgId'];
			$PGLIST->display(false);

			$GLOBALS['_RESULT']['title'] = $PGLIST->pageinfo['name'];

			// Подключение функциональных скриптов
			include($_CFG['_PATH']['core'].'/includesrc.php');
			fileInclude($_CFG['fileIncludeOption']);

			foreach($_REQUEST as $k=>$r) {
				if($k=='onload') {
					$GLOBALS['_RESULT']['onload'] .= $_tpl[$k];
				}
				else if($k=='styles') {
					$GLOBALS['_RESULT']['styles'] = $_tpl[$k];
				}
				else if($k=='script') {
					$GLOBALS['_RESULT']['script'] = $_tpl[$k];
				}
				else if(isset($_tpl[$k])) {
					$GLOBALS['_RESULT']['pg_'.$k] = $_tpl[$k];
					/*if(!isset($_REQUEST['onlyget'])) {
						if(is_array($r)) {
							foreach($r as $vk=>$vr)
								$GLOBALS['_RESULT']['onload'] = 'jQuery(\''.$vr.'\').'.$vk.'(result.pg_'.$k.');'.$GLOBALS['_RESULT']['onload'];
						} else//replaceWith
							$GLOBALS['_RESULT']['onload'] = 'jQuery(\''.$r.'\').html(result.pg_'.$k.');'.$GLOBALS['_RESULT']['onload'];
					}*/
				}
			};
			/////////////////////////////////////
		}
		elseif(isset($_REQUEST['_ctId'])) {
			$PGLIST->display_inc((int)$_REQUEST['_ctId'], $_GET['_design']);
			$_tpl['text'] = '';
			foreach($_tpl as $k=>$r) {
				if($k!='styles' and $k!='script' and $k!='onload' and !is_array($r))
				{
					$_tpl['text'] .= $r;
				}
			}
		};
		
	}
	elseif (isset($_REQUEST['fileupload'])) {
		if (isset($_FILES['Filedata']) && $_FILES['Filedata']['error'] == 0) {

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
			
		}
	}
	elseif($_GET['_view']=='exit') {
		static_main::userExit();
		$GLOBALS['_RESULT']['onload'] = 'window.location.href=window.location.href;';
	}
	elseif($_GET['_view']=='login') {
		$res=array('',0);
		if(count($_POST) and isset($_POST['login']))
		{
			$res = static_main::userAuth($_POST['login'],$_POST['pass']);// повесить обработчик xml
			if($res[1]) {
				$GLOBALS['_RESULT']['onload'] .= "window.location.reload();";
			}
		}
		if(!$res[1]) {
			if(count($_POST)) {
				$GLOBALS['_RESULT']['html2'] = '<div style="font-size:12px;color:red;white-space:normal;">'.$res[0].'</div>';
				//$_tpl['onload'] = 'clearTimeout(timerid2); fShowload(1,result.html2,0,"loginblock"); jQuery("#loginblock>div.layerblock").show(); '.$_tpl['onload'];
				$GLOBALS['_RESULT']['onload'] = 'clearTimeout(timerid2);jQuery(\'div.messlogin\').hide().html(result.html2).show(\'slow\');'.$_tpl['onload'];
				$html='';
			}
		}
		
	}
	else {
		print('ErRoR');
	}
	
