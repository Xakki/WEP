<?
	if(!$_CFG['_PATH']['wep'] or !$_CFG['_PATH']['path']) die('ERROR');

	$GLOBALS['_RESULT']	= array();
	$_tpl['onload']=$html=$html2='';

	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require_once($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');
	require_once($_CFG['_PATH']['core'].'/html.php');	/**отправляет header и печатает страничку*/
	require_once($_CFG['_PATH']['core'].'/sql.php');
	$SQL = new sql();
	
	session_go();

	//$HTML = new html('_design/',$_CFG['wep']['design'],false);// упрощённый режим
	$DATA  = array();

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
				$_tpl['onload'] .= "alert('Поздравляем! Вы успешно авторизованы!');  window.location.href=window.location.href;";
			}
		}
		if(!$res[1]) {
			if(count($_POST)) {
				$html2 = '<div style="width:200px;font-size:12px;color:red;white-space:normal;">'.$res[0].'</div>';
				$_tpl['onload'] = 'clearTimeout(timerid2);fShowload(1,result.html2,0,"loginblock .messlogin");$("#loginblock>div.layerblock").show();'.$_tpl['onload'];
				$html='';
			}
		}
		
	}
	$GLOBALS['_RESULT'] = array("html" => $html,"html2" => $html2,'eval'=>$_tpl['onload']);

?>