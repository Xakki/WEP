<?php

	$GLOBALS['_RESULT']	= array();
	$_tpl['onload']=$html=$html2='';
	if(!isset($_GET['noajax']))
		require_once($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');

	require_once($_CFG['_PATH']['core'].'/html.php');
	if(isset($_GET['noajax']))
		headerssent();

	session_go();

	$HTML = new html('_design/',$_CFG['wep']['design'],false);// упрощённый режим
	$DATA  = array();

	if($_GET['_view']=='boardlist' and $_GET['_rid']=(int)$_GET['_rid']){
		_new_class('board',$BOARD);
		_new_class('rubric',$RUBRIC);
		$BOARD->RUBRIC = &$RUBRIC;
		$BOARD->fields_form = array();
		if($form = $BOARD->ParamFieldsForm((int)$_GET['_id'],$_GET['_rid'])) {
			if($BOARD->kFields2FormFields($form)) {
				$data['form'] = &$BOARD->form;
				$html = $HTML->transformPHP($data,'form');
			}
			$_tpl['onload'] .= 'rclaim(\'type\');';
		}
		else {
			$html = '&#160;';
			$_tpl['onload'] .= '';
		}
	}
	elseif($_GET['_view']=='boardexport' and $_GET['_region']=(int)$_GET['_region']){
		$htmldata = array('form'=>array());
		if($_GET['_region']) {
			// см. board.class/board.class.php [350]
			_new_class('exportboard',$EXPORTBOARD);
			list($DATA,$htmldata['form']) = $EXPORTBOARD->getListBoard($_GET['_region']);
			if(count($DATA)) {
				$html = $HTML->transformPHP($htmldata,'form');
				$_tpl['onload'] .= 'jQuery(\'#tr_boardexport\').show();';
			}
		}
	}
	elseif($_GET['_view']=='add' and isset($_GET['_modul']) and $_GET['_modul']=='boardcomments') {
		$MODUL = NULL;
		_new_class('board',$BOARD);
		$MODUL = &$BOARD->childs['boardcomments'];
		$MODUL->parent_id = (int)$_GET['_pid'];
		$MODUL->owner->id = (int)$_GET['_oid'];
		if($MODUL){
			if(count($_POST)) $_POST['sbmt'] = 1;
			list($DATA['formcreat'],$flag) = $MODUL->_UpdItemModul(array('ajax'=>1));
			$DATA['formcreat']['css'] = 'form_boardcomments';
			$html = $HTML->transformPHP($DATA,'formcreat');
			if($flag==1) {
				$_tpl['onload'] .= 'clearTimeout(timerid2);fShowload(1,result.html2);jQuery(\'div.blockclose\').attr(\'onclick\',\'\').click(function(){location.reload();});';
				//commanswer(0);jQuery(\'#form_boardcomments input,#form_boardcomments textarea\').val(\'\');

				$html2=$html;
				$html='';
			}
			elseif($flag==-1) {
				//$_tpl['onload'] = 'GetId("messages").innerHTML=result.html2;'.$_tpl['onload'];
				$_tpl['onload'] = 'jQuery(\'.caption_error\').remove();'.$_tpl['onload'].'clearTimeout(timerid2);fShowload(1,result.html2);';
				$html2='<div class=\'blockhead\' style=\'padding-right:25px;color:red;\'>Внимание.</div><div class=\'hrb\'>&#160;</div>'.$html;
				//$html='';
				//$html2='<div class=\'blockhead\' style=\'padding-right:25px;color:red;\'>Внимание. Некоректно заполнены поля.</div><div class=\'hrb\'>&#160;</div>';$html='';
			}
			else {
				$_tpl['onload'] .= 'jQuery(\'#form_'.$_GET['_modul'].'\').bind(\'submit\',function(e){ return JSWin({\'type\':\'POST\', \'href\':\''.$_CFG['_HREF']['siteJS'].'?_view=add&_modul='.$_GET['_modul'].'\', \'data\':$(\'#form_'.$_GET['_modul'].'\').serialize()}); });';
				//$_tpl['onload'] .= 'clearTimeout(timerid2);fShowload(1,result.html2);';
			}
			if(!isset($_SESSION['user']['id']))
				$_tpl['onload'] .= 'reloadCaptcha(\'captcha\');jQuery(\'input.secret\').attr(\'value\',\'\');';
				//$_tpl['onload'] .= 'JSFR("form");';
			//if($xml[1]==0) 
			//	$html .= '<script type="text/javascript">jQuery(document).ready(function(){'.$_tpl['onload'].'setTimeout(function(){JSFR(\'form\');},400);});</script>';
		}
	}
	elseif($_GET['_view']=='add') {
		$MODUL = NULL;
		_new_class('board',$MODUL);
		_new_class('rubric',$RUBRIC);
		$MODUL->RUBRIC = &$RUBRIC;
		if($MODUL){
			if(count($_POST)) $_POST['sbmt'] = 1;
			list($DATA['formcreat'],$flag) = $MODUL->_UpdItemModul(array('ajax'=>1,'errMess'=>1));
			$html = $HTML->transformPHP($DATA,'formcreat');

			if($flag==1) {
				//$_tpl['onload'] .= 'if(confirm(\''.$MODUL->getMess('add').' Хотите просмотреть его?\')) location.href = \'board_'.$MODUL->id.'.html\';else location.href = location.href;';
				$_tpl['onload'] .= 'clearTimeout(timerid2);fShowload (1,result.html2,0,0,\'location.href = location.href;\');';
				$html2 = '<div class="blockhead">'.$MODUL->getMess('add').'</div><div class="hrb">&nbsp;</div>
				<div class="divform"><div class="messages">
				<div class="ok">Хотите <a href=\'board_'.$MODUL->id.'.html\'>просмотреть</a> своё объявление?</div>
				<div class="ok">Или же хотите <a href=\'javascript:location.href=location.href;\'>добавить</a> ещё объявление?</div>
				</div></div>';
				$html = '';
			}elseif($flag==-1){
				//$_tpl['onload'] = 'GetId("messages").innerHTML=result.html2;'.$_tpl['onload'];
				$_tpl['onload'] = 'jQuery(\'.caption_error\').remove();'.$_tpl['onload'].'clearTimeout(timerid2);fShowload(1,result.html2);';
				$html2="<div class='blockhead'>Внимание. Некоректно заполнены поля.</div><div class='hrb'>&#160;</div>".$html;$html='';
			}
			else{
				$_tpl['onload'] .= 'clearTimeout(timerid2);fShowload(1,result.html2);';
				$html2=$html;$html='';
			}
			if(!isset($_SESSION['user']['id']))
				$_tpl['onload'] .= 'reloadCaptcha(\'captcha\');jQuery(\'input.secret\').attr(\'value\',\'\');';
				//$_tpl['onload'] .= 'JSFR("form");';
			//if($xml[1]==0) 
			//	$html .= '<script type="text/javascript">jQuery(document).ready(function(){'.$_tpl['onload'].'setTimeout(function(){JSFR(\'form\');},400);});</script>';
		}
	}
	elseif($_GET['_view']=='boardvote' and $_CFG['robot']) {
		$_tpl['onload'] = 'alert("Ботам голосовать нельзя.");';
	}
	elseif($_GET['_view']=='boardvote') {
		/*
UPDATE board t1 SET 
t1.nomination1=(SELECT count(t2.id) FROM boardvote t2 WHERE t2.type=1 and t2.owner_id=t1.id), 
t1.nomination2=(SELECT count(t3.id) FROM boardvote t3 WHERE t3.type=2 and t3.owner_id=t1.id);
		*/
		_new_class('board',$BOARD);
		$result = $BOARD->SQL->execSQL('SELECT count(id) as cnt FROM `boardvote` WHERE mf_ipcreate=INET_ATON("'.$_SERVER["REMOTE_ADDR"].'") and mf_timecr>'.($_CFG['time']-2592000));//86400*30
		$row = $result->fetch_array(); 
		if($row['cnt']>60)
			$_tpl['onload'] = 'alert("С вашего IP('.$_SERVER["REMOTE_ADDR"].') проголосовали более 60 раз за месяц. Сейчас проголосовать не получится.");';
		else {
			$_GET['_id'] = (int)$_GET['_id'];
			$_GET['_type'] = (int)$_GET['_type'];
			$result = $BOARD->SQL->execSQL('SELECT * FROM `board` WHERE active=1 and id='.$_GET['_id']);
			if($row = $result->fetch_array()) {
				if(isset($row['nomination'.$_GET['_type']])) {
					$vote = $row['nomination'.$_GET['_type']];
					$clause = array();
					$clause[] = 'owner_id='.$_GET['_id'];
					$clause[] = 'type='.$_GET['_type'];
					if(static_main::_prmUserCheck())
						$clause[] = 'creater_id="'.$_SESSION['user']['id'].'"';
					else {
						$clause[] = 'mf_ipcreate=INET_ATON("'.$_SERVER["REMOTE_ADDR"].'")';
					}
					$result = $BOARD->SQL->execSQL('SELECT * FROM `boardvote` WHERE '.implode(' and ',$clause));
					if(!$row = $result->fetch_array()) {
						$clause[] = 'mf_timecr=UNIX_TIMESTAMP()';
						$clause[] = 'agent="'.mysql_real_escape_string(substr($_SERVER['HTTP_USER_AGENT'],0,254)).'"';
						if($result2 = $BOARD->SQL->execSQL('INSERT INTO `boardvote` SET '.implode(', ',$clause)) and !$result2->err) {
							if($result3 = $BOARD->SQL->execSQL('UPDATE `board` SET nomination'.$_GET['_type'].'=nomination'.$_GET['_type'].'+1 WHERE id='.$_GET['_id']) and !$result3->err) {
								$html2 = $BOARD->config['nomination'.$_GET['_type']].' ['.($vote+1).']';
								$_tpl['onload'] = 'jQuery(param[\'insertObj\']).text(result.html2).attr(\'onclick\',\'\').attr(\'class\',\'nomination-item navi1\');';
							}
						}
					}else
						$_tpl['onload'] = 'alert("Вы уже проголосовали за эту номинацию!");';
				}
			}
			//
		}
	}
	elseif($_GET['_view']=='city') {
		$html='';
		$CITY = NULL;
		_new_class('city',$CITY);
		$html = "<div class='blockhead'>Выбирете город</div><div class='hrb'>&#160;</div>".$HTML->transform('<mainjs>'.$CITY->cityDisplay().'</mainjs>','citymain');
	}
	elseif($_GET['_view']=='addcity') {
		$html='';
		$CITY = NULL;
		_new_class('city',$CITY);
		$html = "<div class='blockhead'>Выбирете город</div><div class='hrb'>&#160;</div>".$HTML->transform('<mainadd>'.$CITY->cityDisplay().'</mainadd>','citymain');
	}
	elseif(isset($_GET['_view2']) and $_GET['_view2']=='subscribeparam') {
		$html='';
		_new_class('rubric',$RUBRIC);
		_new_class('board',$BOARD);
		$BOARD->RUBRIC = &$RUBRIC;
		$RUBRIC->simpleRubricCache();
		if(!count($RUBRIC->data2)) return '';
		$formparam = array();
		$formparam['filter'] = $BOARD->boardFindForm((int)$_REQUEST['rubric'],0);
		$html = "<div class='blockhead'>Параметры поиска объявления &#160;&#160;&#160;&#160;</div><div class='hrb'>&#160;</div>".$HTML->transformPHP($formparam,'filter');
		$_tpl['onload'] .= 'JSFR("form");';
	}
	elseif($_GET['_view']=='exit') {
		static_main::userExit();
		$_tpl['onload'] = 'window.location.href=window.location.href;';
	}
	elseif($_GET['_view']=='login') {
		$res=array('',0);
		if(count($_POST) and isset($_POST['login']))
		{
			$res = static_main::userAuth($_POST['login'],$_POST['pass']);// повесить обработчик xml
			if($res[1]) {
				$_tpl['onload'] .= "alert('Поздравляем! Вы успешно авторизованы!');  window.location.href=window.location.href;";
			}
		}
		if(!$res[1]) {
			if(count($_POST)) {
				$html2 = '<div style="font-size:12px;color:red;white-space:normal;">'.$res[0].'</div>';
				//$_tpl['onload'] = 'clearTimeout(timerid2); fShowload(1,result.html2,0,"loginblock"); jQuery("#loginblock>div.layerblock").show(); '.$_tpl['onload'];
				$_tpl['onload'] = 'clearTimeout(timerid2);jQuery(\'div.messlogin\').hide().html(result.html2).show(\'slow\');'.$_tpl['onload'];
				$html='';
			}
		}
		
	}
	elseif($_GET['_view']=='mCBox') {
		_new_class('formlist',$MODUL);
		$DATA = array();
		$enumlist = array();
		$clause = 'SELECT t1.id,t1.owner_id,t1.parent_id,t1.name,t1.checked,t1.cntdec FROM '.$MODUL->childs['formlistitems']->tablename.' t1 WHERE t1.parent_id='.(int)$_GET['tval'].' and t1.active=1 ORDER BY t1.ordind';
		$result = $MODUL->SQL->execSQL($clause);

		if(!$result->err) {
			$templ = array();
			while ($row = $result->fetch_array()) {
				$enumlist[$row['id']] = array('#id#'=>$row['id'],'#name#'=>$row['name'],'#checked#'=>$row['checked']);
			}
		}
		$DATA['filter'] = array(
			substr($_GET['tname'],0,-2).'_'.$_GET['tval'] => array(
				'caption'=>$_GET['tcap'],
				'type'=>'checkbox',
				'multiple'=>1,
				'value'=>0,
				'css'=>'addparam',
				'valuelist'=>$enumlist,
			)						
		);
		//$_tpl['onload'] .= 'mCBoxVis(\''.$rr.'_'.$rrr.'\');';
		$html = $HTML->transformPHP($DATA,'filter');
	}
	elseif($_GET['_view']=='filterChange') {
		_new_class('board',$BOARD);
		_new_class('rubric',$RUBRIC);
		$BOARD->RUBRIC = &$RUBRIC;
		$cnt = 0;
		if(isset($_GET['rubric']) and $rid = (int)$_GET['rubric']) {
			$RUBRIC->simpleRubricCache();
			$BOARD->boardFindForm($rid);
			$CITY = NULL;
			if(_new_class('city',$CITY))
				$CITY->cityPosition();
			$cnt = $BOARD->fListDisplay($rid,$_GET,'cnt');
		}
		if($cnt)
			$html='<a href="" onclick="document.forms.form_tools_paramselect.submit();return false;">Найдено объвлений '.$cnt.'</a>';
		else
			$html='<span style="color:red;">Объвлений не найдено</span>';
		$_tpl['onload'] = 'if(typeof timerc!=\'undefined\') clearTimeout(timerc); jQuery(\'body div.shres\').removeClass(\'shresload\').stop(); timerc=setTimeout(function(){jQuery(\'body div.shres\').fadeOut(1000);},5000);';
	}

	$GLOBALS['_RESULT'] = array("html" => $html,"html2" => $html2,'eval'=>$_tpl['onload']);
	if(isset($_GET['noajax'])) {
		header('Content-type: text/html; charset=utf-8');
		var_export($GLOBALS['_RESULT']);
	}

