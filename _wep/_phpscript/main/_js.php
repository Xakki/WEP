<?php
	if(!$_CFG['_PATH']['wep'] or !$_CFG['_PATH']['path']) die('ERROR');

	$GLOBALS['_RESULT']	= array();
	$_tpl['onload']=$html=$html2='';
	if(!isset($_GET['_view']))
		$_GET['_view'] = '';

	if(!isset($_GET['noajax']))
		require_once($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');
	require_once($_CFG['_PATH']['core'].'/html.php');
	if(isset($_GET['noajax']))
		headerssent();

	session_go();

	$DATA  = array();
	if(isset($_GET['_fn']) and $_GET['_fn']) {
		session_go();
		if(!isset($_GET['_design']))
			$_GET['_design'] = 'default';
		$HTML = new html('_design/',$_GET['_design'],true);
		if(_new_class($_GET['_modul'],$MODUL) and isset($MODUL->_AllowAjaxFn[$_GET['_fn']])) {
			eval('$_tpl=$MODUL->'.$_GET['_fn'].'();');
		} else
			$_tpl['text'] = 'Вызов функции не разрешён модулем.';
		if(!isset($_GET['_template']))
			$HTML->_templates = 'default';
		else
			$HTML->_templates = $_GET['_template'];
	}
	else {
		
		if($_GET['_view']=='exit') {
			static_main::userExit();
			$_tpl['onload'] = 'window.location.href=window.location.href;';
		}
		elseif($_GET['_view']=='login') {
			$res=array('',0);
			if(count($_POST) and isset($_POST['login']))
			{
				$res = static_main::userAuth($_POST['login'],$_POST['pass']);// повесить обработчик xml
				if($res[1]) {//alert('Поздравляем! Вы успешно авторизованы!');  
					$_tpl['onload'] .= "window.location.href=window.location.href;";
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
			_new_class('ugroup',$UGROUP);
			$html = $UGROUP->setRating($_GET['_modul'],$_GET['mid'],$_GET['rating']);
		}
		$GLOBALS['_RESULT']['html'] = $html;
		$GLOBALS['_RESULT']['html2'] = $html2;
		$GLOBALS['_RESULT']['eval'] = $_tpl['onload'];
	}