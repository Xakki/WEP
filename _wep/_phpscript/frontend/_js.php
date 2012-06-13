<?php
	if(!$_CFG['_PATH']['wep'] or !$_CFG['_PATH']['path']) die('ERROR');
	global $_tpl;
	$GLOBALS['_RESULT'] = array('html' => '','html2' => '','onload'=>'');
	$html=$html2='';

	if(!isset($_GET['noajax']))
		require_once($_CFG['_PATH']['wep_phpscript'].'lib/jquery_getjson.php');
	require_once($_CFG['_PATH']['core'].'html.php');
	if(isset($_GET['noajax']))
		headerssent();

	session_go();

	$DATA  = array();

	if(isset($_GET['_fn']) and $_GET['_fn']) {

		if(!isset($_GET['_design']))
			$_GET['_design'] = $_CFG['wep']['design'];

		$HTML = new html('_design/',$_GET['_design'],(isset($_GET['_template'])?true:false));// упрощённый режим

		if(_new_class($_GET['_modul'],$MODUL) and isset($MODUL->_AllowAjaxFn[$_GET['_fn']])) {
			eval('$GLOBALS["_RESULT"]=$MODUL->'.$_GET['_fn'].'();');
		} else
			$GLOBALS['_RESULT']['text'] = 'Вызов функции не разрешён модулем.';


		if(isset($_GET['_template'])) {
			$_tpl = $GLOBALS['_RESULT'];
			$HTML->_templates = $_GET['_template'];
		}

	}
	else {
		if(!isset($_GET['_view']))
			$_GET['_view'] = '';

		if($_GET['_view']=='exit') {
			$_tpl['onload'] = 'alert("TODO: Замена на ф. wep.exit();");';
			static_main::userExit();
			$_tpl['onload'] = 'window.location.href=window.location.href;';
		}
		elseif($_GET['_view']=='login') {
			$_tpl['onload'] = 'alert("TODO: Замена на json");';
			$res=array('',0);
			if(count($_POST) and isset($_POST['login']))
			{
				$res = static_main::userAuth($_POST['login'],$_POST['pass']);// повесить обработчик xml
				if($res[1]) {//alert('Поздравляем! Вы успешно авторизованы!');  
					$_tpl['onload'] .= "window.location.reload();";
				}
			}
			if(!$res[1]) {
				if(count($_POST)) {
					$html2 = '<div style="width:200px;font-size:12px;color:red;white-space:normal;">'.$res[0].'</div>';
					$_tpl['onload'] = 'clearTimeout(timerid2);fShowload(1,result.html2,0,"loginblock .messlogin");$("#loginblock>div.layerblock").show();'.$_tpl['onload'];
					$html='';
				}
			}
			
		}elseif($_GET['_view']=='rating') {
			$_tpl['onload'] = 'alert("TODO:Переделать!");';
			_new_class('ugroup',$UGROUP);
			$html = $UGROUP->setRating($_GET['_modul'],$_GET['mid'],$_GET['rating']);
		}else
			$html='ERrOR';

		$GLOBALS['_RESULT'] = array("html" => $html,"html2" => $html2,'onload'=>$_tpl['onload']);
	}
	/*
	include($_CFG['_PATH']['core'].'/includesrc.php');
	fileInclude($_CFG['fileIncludeOption']);
	arraySrcToFunc();
	*/

	if(isset($_GET['noajax']) and !isset($_GET['_template'])) {
		header('Content-type: text/html; charset=utf-8');
		print_r($GLOBALS['_RESULT']);
	}
