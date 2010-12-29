<?

	$DATA = array();
	if($_GET['_view']=='list'){
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
			$_tpl['onload'] .= 'clearTimeout(timerid2);fShowload(1,result.html2);'.$_tpl['onload'];//$_tpl['onload'] = '$("#messages").html(result.html2);'.$_tpl['onload'];
			$html2="<div class='blockhead'>Внимание. Некоректно заполнены поля.</div><div class='hrb'>&#160;</div>".$html;$html='';
		}else
			$_tpl['onload'] .= 'JSFR("form");';
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
	elseif($_GET['_type']=='reinstall'){
		$DATA['formtools'] = $MODUL->confirmReinstall();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '$(\'#form_tools_reinstal\').bind(\'submit\',function(e){return JSFRWin(\'#form_tools_reinstal\',\'#tools_block\');});';
	}
	elseif($_GET['_type']=='config'){
		$DATA['formtools'] = $MODUL->confirmConfigmodul();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '$(\'#form_tools_config\').bind(\'submit\',function(e){return JSFRWin(\'#form_tools_config\',\'#tools_block\');});';
	}
	elseif($_GET['_type']=='reindex'){
		$DATA['formtools'] = $MODUL->confirmReindex();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '$(\'#form_tools_reindex\').bind(\'submit\',function(e){return JSFRWin(\'#form_tools_reindex\',\'#tools_block\');});';
	}
	elseif($_GET['_type']=='checkmodul'){
		$DATA['formtools'] = $MODUL->confirmCheckmodul();
		$html = $HTML->transformPHP($DATA,'formtools');
		$_tpl['onload'] .= '$(\'#form_tools_checkmodul\').bind(\'submit\',function(e){return JSFRWin(\'#form_tools_checkmodul\',\'#tools_block\');});';
	}
	elseif($_GET['_type']=='stats'){
		$htmleval = $MODUL->statisticModule((int)$_GET['_oid']);
		$html = '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>'.$htmleval[0];
		$_tpl['onload'] .= $htmleval[1];
	}
	/**
		*Форма фильтра
	**/
	elseif($_REQUEST['_type']=='formfilter') {
		$DATA['filter'] = $MODUL->filtrForm();
		$html = '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>'.$HTML->transformPHP($DATA,'filter');
		$_tpl['onload'] .= '$(\'#form_filter\').bind(\'submit\',function(e){return JSFRWin(\'#form_filter\');});';
	}
	/**
		* очистка фильтра
	**/
	elseif($_REQUEST['_type']=='filter' && isset($_REQUEST['f_clear_sbmt'])){
		unset($_SESSION['filter'][$_GET['_modul']]);
		$_tpl['onload'] .= 'window.location.href = \''.$_SERVER['HTTP_REFERER'].'\';';
	}
	
	/**
		* задаются параметры фильтра
	**/
	elseif($_REQUEST['_type']=='filter') {
		$MODUL->setFilter();
		$_tpl['onload'] .= 'window.location.href = \''.$_SERVER['HTTP_REFERER'].'\';';
	}
	else
		$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Параметры заданны неверно!</div>\',1);';


	include($_CFG['_PATH']['core'].'/includesrc.php');
	fileInclude($_CFG['fileIncludeOption']);
	arraySrcToFunc();
	

?>