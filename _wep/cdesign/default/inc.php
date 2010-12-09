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

				if($_GET['_view']=='list') {
					$MODUL->_clp = '_view=list&amp;_modul='.$MODUL->_cl.'&amp;';
					$param = array('sbmtsave'=>1,'close'=>1);
//$tt = array();$summ = 0;for($j = 1; $j <= 5; $j++) { $tt[$j] = getmicrotime(); for($i = 1; $i <= 20; $i++) {
							$MODUL->setFilter(1);
							list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

							if($_GET['_type']=="add" or $_GET['_type']=="edit") {
								if(isset($DATA['formcreat']) and isset($DATA['formcreat']['form']) and count($DATA['formcreat']['form'])) {
									$DATA['formcreat']['path'] = $HTML->path;
									$html = $HTML->transformPHP($DATA,'formcreat');
									//$_tpl['onload'] .= 'var tmp = $(\'#form_'.$_GET['_modul'].'\').attr(\'action\');$(\'#form_'.$_GET['_modul'].'\').attr(\'action\',tmp.replace(\'index.php\',\'js.php\'));JSFR(\'#form_'.$_GET['_modul'].'\');';
								}
								elseif($flag==1){
									end($HTML->path);prev($HTML->path);
									$_SESSION['mess']=$DATA['formcreat']['messages'];
									header('Location: '.$_CFG['_HREF']['BH'].str_replace("&amp;", "&", key($HTML->path)));
									die();
								}
								else {
									//$DATA['formcreat']['messages'] = $_SESSION['mess'];
									$DATA['formcreat']['path'] = $HTML->path;
									$html = $HTML->transformPHP($DATA,'formcreat');
									//$_tpl['onload'] .= 'var tmp = $(\'#form_'.$_GET['_modul'].'\').attr(\'action\');$(\'#form_'.$_GET['_modul'].'\').attr(\'action\',tmp.replace(\'index.php\',\'js.php\'));JSFR(\'#form_'.$_GET['_modul'].'\');';
									if($MODUL->includeJStoFORM) {
										if(!is_array($MODUL->config['scriptIncludeToForm']))
											$MODUL->config['scriptIncludeToForm'] = explode('|',$MODUL->config['scriptIncludeToForm']);
										if(count($MODUL->config['scriptIncludeToForm']))
											foreach($MODUL->config['scriptIncludeToForm'] as $sr)
												$_tpl['script'][$sr] = 1;
									}
									if($MODUL->includeCSStoFORM) {
										if(!is_array($MODUL->config['cssIncludeToForm']))
											$MODUL->config['cssIncludeToForm'] = explode('|',$MODUL->config['cssIncludeToForm']);
										if(count($MODUL->config['cssIncludeToForm']))
											foreach($MODUL->config['cssIncludeToForm'] as $sr)
												$_tpl['styles'][$sr] = 1;
									}
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

//} $tt[$j] = getmicrotime()-$tt[$j]; $summ += $tt[$j]; } echo 'Среднее время = "'.($summ/5).'" ';echo $tt;

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

	$_tpl['styles']['style'] = 1;

	unset($_tpl['script']['jquery']);
	$_tpl['script']['jquery.form'] = 1;
	$_tpl['script']['utils'] = 1;
	$_tpl['script']['script'] = 1;
	//$_tpl['script']['script.localisation/jquery.localisation-min'] = 1;
	include($_CFG['_PATH']['core'].'/includesrc.php');
	fileInclude($_CFG['fileIncludeOption']);
	arraySrcToStr();


//$_CFG['fileIncludeOption']['fancybox']
?>
