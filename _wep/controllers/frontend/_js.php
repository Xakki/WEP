<?php
	if(!$_CFG['_PATH']['wep'] or !$_CFG['_PATH']['path']) die('ERROR');
	global $_tpl;

	session_go();
	$DATA  = array();
	
	/**
	*
	*/
	if(isset($_GET['_fn']) and $_GET['_fn']) {

		if(!isset($_GET['_design']))
			$_GET['_design'] = $_CFG['wep']['design'];

		if(_new_class($_GET['_modul'],$MODUL) and isset($MODUL->_AllowAjaxFn[$_GET['_fn']])) 
		{
			eval('$data=$MODUL->'.$_GET['_fn'].'();');
			if( is_array($data) )
			{
				if(!isset($data['tpl']) or !$data['tpl'])
					$data['tpl'] = '#pg#formcreat';
				$_tpl['text'] = transformPHP($data, $data['tpl'] );
				$_tpl['onload'] = $_tpl['onload'];
			}
		} 
		else
			$_tpl['text'] = 'Вызов функции не разрешён модулем.';


		if(isset($_GET['_template'])) {
			setTemplate($_GET['_template']);
		}

	}
	/**
	*
	*/
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
			$_tpl['data'] = array();
			while($row = $result->fetch())
				$_tpl['data'][] = array($row['id'],$row['name']);
		}else
			print('NO VALID DATA');
		/*
		if($field['type']=='ajaxlist') {
			if($field['listname']['where']) $field['listname']['where'] .= ' and '.$_GET['nameField'].' LIKE "%'.$SQL->SqlEsc($_GET['_value']).'%"';
			$field['listname']['ordfield'] .= ' LIMIT 25';
			$md= $MODUL->_getCashedList($field['listname']);
			unset($md[0]);
			foreach ($md as $k=>$r) {
				$_tpl['data'][$k] = $r;
			}
		}*/
	}
	/**
	*
	*/
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

			$_tpl['title'] = $PGLIST->pageinfo['name'];

			/*foreach($_REQUEST as $k=>$r) {
				if($k=='onload') {
					$_tpl['onload'] .= $_tpl[$k];
				}
				else if($k=='styles') {
					$_tpl['styles'] = $_tpl[$k];
				}
				else if($k=='script') {
					$_tpl['script'] = $_tpl[$k];
				}
				else if(isset($_tpl[$k])) {
					$_tpl['pg_'.$k] = $_tpl[$k];
				}
			};*/
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
	/**
	*
	*/
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
					$_tpl['swf_uploader'] = array(
						'name' => $temp_name,
						'path' => $_CFG['PATH']['temp'],
						'mime_type' => $ext_list[$ext],
					);
				}
			}
			else {
				$_tpl['error'] = 'Неверный тип файла';
			}
			
		}
	}
	/**
	*
	*/
	else {
		if(!isset($_GET['_view']))
			$_GET['_view'] = '';

		if($_GET['_view']=='exit') {
			static_main::userExit();
			$_tpl['onload'] = 'window.location.href=window.location.href;';
		}
		elseif($_GET['_view']=='login') {
			$res=array('',0);
			if(count($_POST) and isset($_POST['login']))
			{
				$res = static_main::userAuth($_POST['login'],$_POST['pass']);// повесить обработчик xml
				if($res[1]) {
					$_tpl['onload'] .= "window.location.reload();";
				}
			}
			if(!$res[1]) {
				if(count($_POST)) {
					$_tpl['text'] = '<div style="font-size:12px;color:red;white-space:normal;">'.$res[0].'</div>';
					//$_tpl['onload'] = 'clearTimeout(timerid2); fShowload(1,result.html,0,"loginblock"); jQuery("#loginblock>div.layerblock").show(); '.$_tpl['onload'];
					$_tpl['onload'] = 'clearTimeout(timerid2);jQuery(\'div.messlogin\').hide().html(result.html).show(\'slow\');'.$_tpl['onload'];
					$html='';
				}
			}
			
		}
		elseif($_GET['_view']=='rating') {
			$_tpl['onload'] = 'alert("TODO:Переделать!");';
			_new_class('ugroup',$UGROUP);
			$html = $UGROUP->setRating($_GET['_modul'],$_GET['mid'],$_GET['rating']);
		}
		else
			$html='ERrOR';

	}
