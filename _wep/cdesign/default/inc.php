<?

	$_tpl['modulstree']=$eval='';

	if($_CFG['info']['email'])
		$_tpl['contact'] = '<div class="ctd1">e-mail:</div>	<div class="ctd2"><a href="mailto:'.$_CFG['info']['email'].'">'.$_CFG['info']['email'].'</a></div>';
	if($_CFG['info']['icq'])
		$_tpl['contact'] .= '<div class="ctd1">icq:</div><div class="ctd2">'.$_CFG['info']['icq'].'</div>';
	if(isset($_CFG['info']['phone']) and $_CFG['info']['phone'])
		$_tpl['contact'] .= '<div class="ctd1">телефон:</div><div class="ctd2">'.$_CFG['info']['phone'].'</div>';
	$DATA = fXmlSysconf(); $_tpl['sysconf'] = $HTML->transformPHP($DATA,'sysconf');
	$DATA = fXmlModulslist(); $_tpl['modulslist']=$HTML->transformPHP($DATA,'modulslist');

	if(!$_GET['_modul'] or !(isset($_GET['_view']) or isset($_GET['_type']))) {
		$html = '<div style="position:absolute;top:50%;left:50%;"><div style="width:200px;height:100px;position:absolute;top:-50px;left:-100px;"><img src="'.$_tpl['design'].'img/login.gif" width="250" alt="LOGO"/></div></div>';
	}
	else {
		/*if(count($_GET)==2)
			$SQL->_iFlag = TRUE;*/
		if(!_new_class($_GET['_modul'],$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль '.$_GET['_modul'].' не установлен</div>';
		}
		else {

			if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

			if(_prmModul($_GET['_modul'],array(1,2))) {

				if($_GET['_view']=='reinstall') {
					$xml = $MODUL->confirmReinstall();
					$html = $HTML->transform($xml[0],'formcreat');
				}
				elseif($_GET['_view']=='config') {
					$xml = $MODUL->_configModul();
					$html = $HTML->transform($xml[0],'formcreat');
				}
				elseif($_GET['_view']=='reindex') {
					if($MODUL->_reindex()) $html = "Ошибка";
					else $html = 'Модуль успешно переиндексирован!';
				}
				elseif($_GET['_view']=='list') {
					$param = array('fhref'=>'_view=list&amp;_modul='.$_GET['_modul'].'&amp;');

//$tt = array();$summ = 0;for($j = 1; $j <= 5; $j++) { $tt[$j] = getmicrotime(); for($i = 1; $i <= 20; $i++) {
							
							list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

							if($_GET['_type']=="add" or $_GET['_type']=="edit") {
								if($flag==1) {
									end($HTML->path);prev($HTML->path);
									$_SESSION['mess']=$DATA['formcreat']['messages'];
									header('Location: '.$_CFG['_HREF']['BH'].str_replace("&amp;", "&", key($HTML->path)));
									die();
								}
								else {
									//print_r('<pre>');print_r($DATA);exit();
									//$DATA['formcreat']['messages'] = $_SESSION['mess'];
									$DATA['formcreat']['path'] = $HTML->path;
									$html = $HTML->transformPHP($DATA,'formcreat');
									//$_tpl['onload'] .= 'var tmp = $(\'#form_'.$_GET['_modul'].'\').attr(\'action\');$(\'#form_'.$_GET['_modul'].'\').attr(\'action\',tmp.replace(\'index.php\',\'js.php\'));JSFR(\'#form_'.$_GET['_modul'].'\');';
								}
							} elseif($flag!=3) {
								end($HTML->path);
								$_SESSION['mess']=$DATA['superlist']['messages'];
								header('Location: '.$_CFG['_HREF']['BH'].str_replace("&amp;", "&", key($HTML->path)));
								die();
							} else {
								if(!$_SESSION['mess']) 
									$_SESSION['mess']= array();
								$DATA['superlist']['messages'] += $_SESSION['mess'];
								$DATA['superlist']['path'] = $HTML->path;
								$html = $HTML->transformPHP($DATA,'superlist');
								$_SESSION['mess'] = array();
							}

//} $tt[$j] = getmicrotime()-$tt[$j]; $summ += $tt[$j]; } print_r('Среднее время = "'.($summ/5).'" ');print_r($tt);

					$_tpl['onload'] .= "$('.fancyimg').fancybox();";
				}
				if($MODUL->ver!=$_CFG['modulprm'][$MODUL->_cl]['ver'])
					$_tpl['onload'] .= 'showHelp(\'.weptools.wepchecktable\',\'Версия модуля '.$MODUL->caption.'['.$MODUL->_cl.'] ('.$MODUL->ver.') отличается от версии ('.$_CFG['modulprm'][$MODUL->_cl]['ver'].') сконфигурированного для этого сайта. Обновите здесь поля таблицы.\',4000);$(\'.weptools.wepchecktable\').addClass(\'weptools_sel\');';

			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>';
		}
	}
	$_tpl['modulsforms'] = $html;
	if(_prmUserCheck(2)) 
		$_tpl['debug'] = '<span class="seldebug"><select>
<option onclick="window.location=\''.$_CFG['PATH']['wepname'].'/index.php?_showallinfo=0\'" '.(!$_COOKIE['_showallinfo']?'selected="selected"':'').'>Скрыть инфу</option>
<option onclick="window.location=\''.$_CFG['PATH']['wepname'].'/index.php?_showallinfo=1\'" '.($_COOKIE['_showallinfo']==1?'selected="selected"':'').'>Показать инфу</option>
<option onclick="window.location=\''.$_CFG['PATH']['wepname'].'/index.php?_showallinfo=2\'" '.($_COOKIE['_showallinfo']==2?'selected="selected"':'').'>Показать SQL запросы</option>
<option onclick="window.location=\''.$_CFG['PATH']['wepname'].'/index.php?_showallinfo=3\'" '.($_COOKIE['_showallinfo']==3?'selected="selected"':'').'>Показать все логи</option>
</select></span>';
	$_tpl['styles'][$_CFG['_HREF']['_style'].'style.css'] ='<link rel="stylesheet" href="'.$_CFG['_HREF']['_style'].'style.css" type="text/css"/>';
	$_tpl['styles'][$_CFG['_HREF']['_style'].'form.css'] ='<link rel="stylesheet" href="'.$_CFG['_HREF']['_style'].'form.css" type="text/css"/>';

	$_tpl['script']['md5.js'] = '<script type="text/javascript" src="_design/_script/md5.js"></script>';
	$_tpl['script']['jquery.ui.widget.min.js'] = '<script type="text/javascript" src="_design/_script/script.jquery.ui/jquery.ui.widget.min.js"></script>';
	$_tpl['styles']['jquery-ui-redmond.css'] = '<link type="text/css" href="_design/_style/jquery-ui-redmond.css" rel="stylesheet"/>';
	
	$_tpl['script']['jquery.ui.mouse.min.js'] = '<script type="text/javascript" src="_design/_script/script.jquery.ui/jquery.ui.mouse.min.js"></script>';
	$_tpl['script']['jquery.ui.sortable.min.js'] = '<script type="text/javascript" src="_design/_script/script.jquery.ui/jquery.ui.sortable.min.js"></script>';
	$_tpl['script']['jquery.ui.draggable.min.js'] = '<script type="text/javascript" src="_design/_script/script.jquery.ui/jquery.ui.draggable.min.js"></script>';

	$_tpl['script']['jquery.ui.multiselect.js'] = '<script type="text/javascript" src="_design/_script/script.jquery.ui/jquery.ui.multiselect.js"></script>';
	$_tpl['styles']['jquery.ui.multiselect.js'] = '<link type="text/css" href="_design/_style/jquery-ui.multiselect.css" rel="stylesheet"/>';


	if($_CFG['globalformoption']['multiple']) {
		$_tpl['onload'] .= '$.localise(\'ui-multiselect\', {language: \'ru\', path: \'_design/_script/script.localisation/\'});';
		$_tpl['onload'] .= '$(\'select.multiple\').multiselect();';
	}

	$_tpl['script']['jquery.localisation-min.js'] = '<script type="text/javascript" src="_design/_script/script.localisation/jquery.localisation-min.js"></script>';

	if($_tpl['styles'] and is_array($_tpl['styles']) and count($_tpl['styles']))
			$_tpl['styles'] = implode("\n",$_tpl['styles']);
	if($_tpl['script'] and is_array($_tpl['script']) and count($_tpl['script']))
			$_tpl['script'] = implode("\n",$_tpl['script']);

?>
