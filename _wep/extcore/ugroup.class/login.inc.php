<?
	//$FUNCPARAM = explode('&',$FUNCPARAM);
/**HELP
$FUNCPARAM : 1 - OPENid вкл, else - OPENid выкл
HELP**/

	$result = array();
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='' and !strstr($_REQUEST['ref'],'login') and !strstr($_REQUEST['ref'],'remind')) 
		$ref= $_REQUEST['ref'];
	elseif($_SERVER['HTTP_REFERER']!='' and strpos($_SERVER['HTTP_REFERER'], '.html')) {
		$pos = strripos($_SERVER['HTTP_REFERER'], '/');
		$rest = substr($_SERVER['HTTP_REFERER'], ($pos+1), 5);
		if(!strpos($this->dataCash[$rest]['ugroup'], 'anonim'))
			$ref= $_SERVER['HTTP_REFERER'];
		else 
			$ref= $_CFG['_HREF']['BH'];		
		}
	else 
		$ref= $_CFG['_HREF']['BH'];

$mess = $form = '';

	if($FUNCPARAM && $_POST['token']) {
		if(!$UGROUP)
			_new_class('ugroup',$UGROUP);
		$html =  $UGROUP->childs['users']->loginzaReg();
		return $html;
	}
	elseif(count($_POST) and isset($_POST['login'])) {
		$result = static_main::userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			@header("Location: ".$ref);
			die();
		}
	}
	elseif(isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok") {
		static_main::userExit();
		$mess=$_CFG['_MESS']['exitok'];
		$ref = $_CFG['_HREF']['BH'];
	}
	elseif(isset($_COOKIE['remember']) and $result = static_main::userAuth() and $result[1]) {
		@header("Location: ".$ref);
		die();
	}
	$form = '<div class="cform" style="">
			<form action="" method="post">
					<input type="hidden" name="ref" value="'.$ref.'"/>
					<div>Логин:</div><input type="text" name="login" tabindex="1"/>
					<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
					<div>Запомнить?<input type="checkbox" style="border:medium none; width:30px;" tabindex="3" name="remember" value="1"/></div>
					<input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
				</form>
				<a href="'.$_CFG['_HREF']['BH'].'remind.html">Забыли пароль?</a>
			 <div style="clear:both;"></div>
		 </div>';
	if($FUNCPARAM) {
		$_tpl['script']['loginza'] = array('http://loginza.ru/js/widget.js');
		$form = '<div class="layerblock" style="width:620px;background:none;border:none;margin:20px auto 0;"><iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url='.urlencode('http://'.$_SERVER['HTTP_HOST'].'/login.html').'&providers_set=yandex,google,rambler,mailruapi,myopenid,openid,loginza" style="width:359px;height:180px;float:left;" scrolling="no" frameborder="no" id="loginzaiframe"></iframe>'.$form.'</div>';
	}


	if($result[0]) $result[0] = '<div style="color:red;">'.$result[0].'</div>';
	$html = '<div style="height:100%;">
		<div class="messhead" style="text-align: center;">'.$result[0].$mess.'</div>
		'.$form.'
	</div>';
	return $html;
?>
