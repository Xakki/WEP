<?php

	$_tpl['meta'] = '
		<base href="'.$_CFG['_HREF']['BH'].'"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		';
		//<!--<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>-->

	$_tpl['script']['jquery'] = 1;
	$_tpl['script']['include'] = 1;
	$_tpl['script']['wep'] = 1;
	$_tpl['script']['script.jquery/form'] = 1;

	$_tpl['script'][$_tpl['design'].'script/main.js'] = 1;

	$_tpl['styles']['button'] = 1;
	$_tpl['styles']['main'] = 1;
	$_tpl['styles'][$_tpl['design'].'style/main.css'] = 1;

	$_tpl['modulstree']=$eval='';

	$DATA = array('adminmenu'=>fAdminMenu($_GET['_modul'])); $_tpl['adminmenu'] = $HTML->transformPHP($DATA,'adminmenu');

	if(!$_GET['_modul'] or !(isset($_GET['_view']) or isset($_GET['_type']))) {
		$html = '<div style="position:absolute;top:50%;left:50%;"><div style="width:200px;height:100px;position:absolute;top:-50px;left:-100px;"><img src="'.$_tpl['design'].'img/login.gif" width="250" alt="LOGO"/></div></div>';
	}
	else {
		/*if(count($_GET)==2)
			$SQL->_iFlag = TRUE;*/
		if($_GET['_view']=='list' and $_GET['_modul']=='_tools') {
			if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0)
				$html = include($_CFG['_PATH']['wep_phpscript'].'/tools.php');
			else
				$html = '<div style="color:red;">Доступ только ОДМИНУ</div>';
		}
		elseif(!_new_class($_GET['_modul'],$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль '.$_GET['_modul'].' не установлен</div>';
		}
		else {

			if(isset($_GET['_oid']) and $_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if(isset($_GET['_pid']) and $_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if(isset($_GET['_id']) and $_GET['_id']!='') $MODUL->id = $_GET['_id'];
			if(!isset($_GET['_type'])) $_GET['_type'] = '';

			if(static_main::_prmModul($_GET['_modul'],array(1,2))) {
				if($_GET['_view']=='list') {
					$param = array(
						'sbmt_save'=>true, 
						'sbmt_close'=>true ,
						'sbmt_del'=>true,
						'firstpath'=> '/'.$_CFG['PATH']['wepname'] . '/index.php?_view=list&'
					);
//$tt = array();$summ = 0;for($j = 1; $j <= 5; $j++) { $tt[$j] = getmicrotime(); for($i = 1; $i <= 20; $i++) {
					$MODUL->setFilter(1);
					list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

					// Adept path
					$path = array();
					foreach($DATA['path'] as $r) {
						$temp = $DATA['firstpath'];
						foreach($r['path'] as $kp=>$rp)
							$temp .= $kp.'='.$rp.'&';
						$path[$temp] = $r['name'];
					}
					$DATA['path'] = $path;

					if($MODUL->ver!=$_CFG['modulprm'][$MODUL->_cl]['ver']) {
						$html = 'Версия модуля '.$MODUL->caption.'['.$MODUL->_cl.'] ('.$MODUL->ver.') отличается от версии ('.$_CFG['modulprm'][$MODUL->_cl]['ver'].') сконфигурированного для этого сайта. Обновите здесь поля таблицы.';
					}
					end($DATA['path']);prev($DATA['path']);
					$prevhref = $_CFG['_HREF']['BH'].str_replace('&amp;', '&', key($DATA['path']));
					if(isset($DATA['formcreat']['form']['_*features*_'])) {
						$DATA['formcreat']['form']['_*features*_']['prevhref'] = $prevhref;
					}

					if(isset($DATA['formcreat']['form']) and $flag==1 and !count($DATA['formcreat']['form'])) {
						$_SESSION['mess']=$DATA['formcreat']['messages'];
						/*if($_SERVER['HTTP_REFERER'])
							static_main::redirect($_SERVER['HTTP_REFERER']);
						else*/
							static_main::redirect($prevhref);
					}
					elseif(!isset($DATA['formcreat']) and $flag!=3) {
						// После успешного удаления
						$_SESSION['mess']=$DATA['messages'];
						end($DATA['path']);
						if($_SERVER['HTTP_REFERER'])
							static_main::redirect($_SERVER['HTTP_REFERER']);
						else
							static_main::redirect($_CFG['_HREF']['BH'].str_replace("&amp;", "&", key($DATA['path'])));
					}
					else {
						if(!isset($_SESSION['mess']) or !is_array($_SESSION['mess']))
							$_SESSION['mess']= array();
						elseif(count($_SESSION['mess']))
							$DATA['messages'] += $_SESSION['mess'];
						$DATA = array('superlist'=>$DATA);
						$html = $HTML->transformPHP($DATA,'superlist');
						$_SESSION['mess'] = array();
					}

//} $tt[$j] = getmicrotime()-$tt[$j]; $summ += $tt[$j]; } echo 'Среднее время = "'.($summ/5).'" ';echo $tt;
				}

			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>';
		}
	}
	$_tpl['modulsforms'] = $html;

	//$_tpl['script']['script.localisation/jquery.localisation-min'] = 1;

//$_CFG['fileIncludeOption']['fancybox']

