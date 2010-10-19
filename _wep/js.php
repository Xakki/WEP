<?
	$GLOBALS['_RESULT']	= $DATA = array();
	$_tpl['onload']=$html=$html2='';

	$_CFG['_PATH']['wepconf'] = dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/_wepconf';
	require_once($_CFG['_PATH']['wepconf'].'/config/config.php');
	require($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');

/*Запуск сессии*/
	/*if($_REQUEST['_view']=='pagenum'){
		$_GET['_modul'] = preg_replace($_CFG['_repl']['name'],'',$_GET['_modul']);
		$_REQUEST['mop'] = (int)$_REQUEST['mop'];
		if(!isset($_COOKIE[$_GET['_modul'].'_mop']) or $_COOKIE[$_GET['_modul'].'_mop']!=$_REQUEST['mop'])
			setcookie($_GET['_modul'].'_mop',$_REQUEST['mop']);
		print_r('<pre>');print_r($_COOKIE);
		$_tpl['onload'] .= 'window.location.reload();';
	}
	else*/

	if($_SERVER['robot']) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['deniedrobot'];
		exit($_CFG['_MESS']['deniedrobot']);
	}
	elseif(!isset($_COOKIE[$_CFG['session_name']])) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['denieda'];
		exit($_CFG['_MESS']['denieda']);
	}

	require_once($_CFG['_PATH']['core'].'html.php');
	require_once($_CFG['_PATH']['core'].'sql.php');
	$SQL = new sql();

	$result = userAuth(); // запскает сессию и проверяет авторизацию
	if(!$result[1]) {
		//header('Location: login.php?ref='.base64_encode($_SERVER['REQUEST_URI']));
		$GLOBALS['_RESULT']['html'] = 'Вы не авторизованы , либо доступ закрыт.';
		exit($GLOBALS['_RESULT']['html']);
	}

	if($_CFG['wep']['access'] and (!isset($_SESSION['user']) or $_SESSION['user']['level']>=5)) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['denied'];
		exit($_CFG['_MESS']['denied']);
		//$_tpl['onload']='window.location="login.php?mess=Недостаточно прав доступа."';
	}
	elseif(!$_GET['_modul'] or !$_SESSION['user']['wep']) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['errdata'];
		exit($_CFG['_MESS']['errdata']);
		//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Параметры заданны неверно!</div>\',1);fSwin1();';
	}

	if(!_new_class($_GET['_modul'],$MODUL))
		exit(' Модуль '.$_GET['_modul'].' не установлен');
		//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Модуль '.$_GET['_modul'].' не установлен</div>\',1);fSwin1();';

	if(!_prmModul($_GET['_modul'],array(1,2)))  // Проверка доступа к модулю
		exit('Доступ к модулю '.$_GET['_modul'].' запрещён администратором');
		//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>\',1);fSwin1();';



	if($_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif($_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];
	$_design = 'default';
	$HTML = new html($_CFG['PATH']['cdesign'],$_design,false);// упрощённый режим

	if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
	if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
	if($_GET['_id']!='') $MODUL->id = $_GET['_id'];
		
	$DATA = array();
	if($_GET['_view']=='boardlist' and $_GET['_rid']=(int)$_GET['_rid']) {
		$MODUL->RUBRIC = new rubric_class($SQL);
		if($MODUL->ParamFieldsForm((int)$_GET['_id'],$_GET['_rid'])) {
			if($MODUL->kFields2FormFields($MODUL->form)) {
				$DATA['form'] = &$BOARD->form;
				$html = $HTML->transformPHP($DATA,'form');
			}
			$_tpl['onload'] .= 'rclaim(\'type\');$(\'loadform\').style.display=\'\';';
		}
		else {
			$html = '&#160;';
			$_tpl['onload'] .= 'GetId(\'loadform\').style.display=\'none\';';
		}
	}
	elseif($_GET['_view']=='list'){
		$param = array('fhref'=>'_view=list&amp;_modul='.$_GET['_modul'].'&amp;','ajax'=>1);
		list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);
		$mess = '';
			if($_GET['_type']=="add" or $_GET['_type']=="edit") {
				if($flag==1) {
					end($HTML->path);prev($HTML->path);
					//$_SESSION['mess']=$xml;
					//header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
					//$_tpl['onload'] = 'if(confirm("'.$HTML->transform('<formblock>'.$xml.'</formblock>','formcreat').'")) alert("'.key($HTML->path).'");';
					$_tpl['onload'] = 'alert(\''.$HTML->transformPHP($DATA['formcreat'],'messages').'\');';
				}
				else {
					$DATA['formcreat']['path'] = $HTML->path;
					$html = $HTML->transformPHP($DATA,'formcreat');
					$_tpl['onload'] .= 'JSFR("form");';
				}
			}elseif($flag!=3) {
				end($HTML->path);
				$_tpl['onload'] = 'alert(\''.$HTML->transformPHP($DATA['superlist'],'messages').'\');load_href("'.str_replace('&amp;','&',key($HTML->path)).'");';
			}else {
				$DATA['superlist']['path'] = $HTML->path;
				$html = $HTML->transformPHP($DATA,'superlist');
			}

		$_tpl['onload'] .= 'fSwin1(1);';
	}
	elseif($_GET['_type']=='modulstree'){
		$html = $HTML->transform($MODUL->fXmlModuls($_GET['_modul']),'modulstree');
		$_tpl['onload'] .= '$(\'#\'+_win1).animate({width:\'show\'},500);';
		if($MODUL->mf_ordctrl) $_tpl['onload'] .= 'iSortable();';
	}
	elseif($_GET['_type']=='modulschild'){
		$html = $HTML->transform($MODUL->fXmlModulsTree($_GET['_modul'],$MODUL->id),'modulstree');
		$_tpl['onload'] .= '$(\'#\'+id).slideDown(\'fast\');';
		if($MODUL->mf_ordctrl) $_tpl['onload'] .= 'iSortable();';
	}
	elseif($_GET['_type']=='reinstall'){
		$DATA['formtools'] = $MODUL->confirmReinstall();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '_win2=\'tools_block\';JSFR("#form_tools_reinstal");';
	}
	elseif($_GET['_type']=='config'){
		$DATA['formtools'] = $MODUL->confirmConfigmodul();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '_win2=\'tools_block\';JSFR("#form_tools_config");';
	}
	elseif($_GET['_type']=='reindex'){
		$DATA['formtools'] = $MODUL->confirmReindex();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '_win2=\'tools_block\';JSFR(\'#form_tools_reindex\');';
	}
	elseif($_GET['_type']=='checkmodul'){
		$DATA['formtools'] = $MODUL->confirmCheckmodul();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '_win2=\'tools_block\';JSFR(\'#form_tools_checkmodul\');';
	}
	elseif($_GET['_type']=='stats'){
		$htmleval = $MODUL->statisticModule((int)$_GET['_oid']);
		$html = '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>'.$htmleval[0];
		$_tpl['onload'] .= $htmleval[1];
	}
	elseif($_GET['_type']=='item'){
		$_owner_id=$MODUL->owner_id;
		$xml = $MODUL->_UpdItemModul(array('ajax'=>1));
		$html = $HTML->transform('<formblock>'.$xml[0].'</formblock>','formcreat');
		if($xml[1]==1) {
			if($MODUL->parent_id) {
				if($MODUL->data[$MODUL->id]['parent_id']!=$MODUL->fld_data['parent_id']) 
					$_tpl['onload'] .= '$("#'.$MODUL->_cl.'_'.$MODUL->id.'").remove();';
				$_tpl['onload'] .= 'JSHR("_'.$MODUL->_cl.'_'.$MODUL->parent_id.'","'.$_CFG['_HREF']['JS'].'",{_type:"modulschild",_modul:"'.$MODUL->_cl.'",_id:"'.$MODUL->parent_id.'"});';
			}
			elseif($MODUL->owner and $_owner_id) {
				$own = substr(get_class($MODUL->owner),0,-6);
				$_tpl['onload'] .= 'JSHR("_'.$own.'_'.$_owner_id.'","'.$_CFG['_HREF']['JS'].'",{_type:"modulschild",_modul:"'.$own.'",_id:"'.$_owner_id.'"});';
			}
			else
				$_tpl['onload'] .= 'JSHR(_win1,"'.$_CFG['_HREF']['JS'].'",{_type:"modulstree",_modul:"'.$_GET['_modul'].'"});';
		}elseif($xml[1]==-1){
			$_tpl['onload'] .= 'clearTimeout(timerid2);fShowload(1,result.html2);'.$_tpl['onload'];//$_tpl['onload'] = 'GetId("messages").innerHTML=result.html2;'.$_tpl['onload'];
			$html2="<div class='blockhead'>Внимание. Некоректно заполнены поля.</div><div class='hrb'>&#160;</div>".$html;$html='';
		}else
			$_tpl['onload'] .= 'JSFR("form");';
		$_tpl['onload'] .= '_selid("'.$_GET['_modul'].'_'.$_GET['_id'].'");iSortable();';
		if($_SESSION['user']['design']=='users' and $xml[1]==1) $_tpl['onload'] ='';
	}
	elseif($_GET['_type']=='sort'){
		if($MODUL->mf_ordctrl and _prmModul($MODUL->_cl,array(10))) {
			$_GET['_obj']=str_replace(array('\\\\\\"','\\\\"','\\"'),'"',$_GET['_obj']);
			$pq = unserialize($_GET['_obj']);
			if($MODUL->_sorting($pq)) 
				$_tpl['onload'] .= 'alert("Ошибка сортировки");';
			else {
				$html = 'Данные успешно отсортированы';
			}
			$_tpl['onload'] .= '_arr_sort["'.$MODUL->nick.'"]= new Object();checkOrd("'.$MODUL->nick.'");';
		} else $_tpl['onload'] .= 'alert("Сортировка не доступна!");';
	}
	elseif($_GET['_type']=='delete'){
		list($xml,$result) = $MODUL->_Del(array());
		if($result) $_tpl['onload'] .= 'alert("Ошибка удаления");';
		else {
			unset($_SESSION['user']);
			$_tpl['onload'] .= 'alert("Все ваши данные на сервере успешно удалены!");window.location = "http://"+window.location.host;';
		}
	}
	elseif($_GET['_type']=='del'){
		list($xml,$result) = $MODUL->_Del(array());
		if($result) $_tpl['onload'] .= 'alert("Ошибка удаления");';
		else {
			$html = 'Данные успешно удалены!';
			foreach($MODUL->id as $dr)
				$_tpl['onload'] .= '$("#'.$MODUL->_cl.'_'.$dr.'").remove();';
		}
		$_tpl['onload'] .= '';
	}
	/**
		*Форма фильтра
	**/
	elseif($_REQUEST['_type']=='formfilter') {
		$DATA['filter'] = $MODUL->filtrForm();
		$html = '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>'.$HTML->transformPHP($DATA,'filter');
		$_tpl['onload'] .= 'JSFR(\'#form_filter\');';
	}
	/**
		* очистка фильтра
	**/
	elseif($_REQUEST['_type']=='filter' && isset($_REQUEST['f_clear_sbmt'])){
		$show_footer = 0;
		unset($_SESSION['filter'][$_GET['_modul']]);
	
		$_tpl['onload'] .= 'window.location.href = window.location.href;';
	}
	
	/**
		* задаются параметры фильтра
	**/
	elseif($_REQUEST['_type']=='filter'){
		$show_footer = 0;

		foreach($MODUL->fields_form as $k=>$row)
		{
			if(isset($_REQUEST['f_'.$k]) && $_REQUEST['f_'.$k]!='' && isset($MODUL->fields_form[$k]['mask']['filter']))
			{
				$is_int = 0 ;
				if (!is_array($_REQUEST['f_'.$k])) {
					$_SESSION['filter'][$_GET['_modul']][$k] = mysql_real_escape_string($_REQUEST['f_'.$k]);
					if(isset($_REQUEST['f_'.$k.'_2']))
						$_SESSION['filter'][$_GET['_modul']][$k.'_2'] = mysql_real_escape_string($_REQUEST['f_'.$k.'_2']);
				} else {
					$_SESSION['filter'][$_GET['_modul']][$k] = array();
					if($is_int)
						foreach($_REQUEST['f_'.$k] as $row)
							$_SESSION['filter'][$_GET['_modul']][$k][] = (int)$row;
					else
						foreach($_REQUEST['f_'.$k] as $row)
							$_SESSION['filter'][$_GET['_modul']][$k][] = mysql_real_escape_string($row);
				}
				if($_REQUEST['exc_'.$k]) 
					$_SESSION['filter'][$_GET['_modul']]['exc_'.$k] = 1;
				else
					unset($_SESSION['filter'][$_GET['_modul']]['exc_'.$k]);
			}else
				unset($_SESSION['filter'][$_GET['_modul']][$k]);
			
		 }

			$_tpl['onload'] .= 'window.location.href = window.location.href;';
	}
	else
		$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Параметры заданны неверно!</div>\',1);';
	//$log = fDisplLogs();
	//$_tpl['onload'] .= (count($log)?'fLog(\''.$log[0].'\',\''.$log[1].'\');':'');
	$_tpl['onload'] .= 'GetId(\'inftime\').innerHTML=\'
	<div style="color:blue;">Обработка страницы '.(getmicrotime()-$_time_start).' c.</div>
	<div style="color:green;">Пaмять '. intval(memory_get_usage()/1024).'/'. intval(memory_get_peak_usage()/1024).' кб</div>
	<div style="color:yellow;">Кол-во SQL запросов "'.count($_CFG['logs']['sql']).'"</div>\';';

	$GLOBALS['_RESULT'] = array("html" => $html,"html2" => $html2,'eval'=>$_tpl['onload']);

?>