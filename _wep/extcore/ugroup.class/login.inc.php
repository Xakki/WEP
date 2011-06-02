<?

	$result = array();
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='') {
		$ref= $_REQUEST['ref'];
		$pos = strripos($ref, '/');
		$rest = substr($ref, ($pos+1), 5);
		if(!strpos($this->dataCash[$rest]['ugroup'], 'anonim'))
			$ref= $ref;
		else 
			$ref= $_CFG['_HREF']['BH'];
	}
	elseif($_SERVER['HTTP_REFERER']!='' and strpos($_SERVER['HTTP_REFERER'], '.html')) {
		$ref= $_SERVER['HTTP_REFERER'];
		$pos = strripos($ref, '/');
		$rest = substr($ref, ($pos+1), 5);
		if(!strpos($this->dataCash[$rest]['ugroup'], 'anonim'))
			$ref= $ref;
		else 
			$ref= $_CFG['_HREF']['BH'];
	}
	else 
		$ref= $_CFG['_HREF']['BH'];	
	$mess = $form = '';

	if(count($_POST) and isset($_POST['login'])) {
		$result = static_main::userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			@header("Location: ".$ref);
			die();
		}
	}
	elseif(isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok") {
		static_main::userExit();
		$mess=$_CFG['_MESS']['exitok'];
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

	if(isset($result[0]) and $result[0]) $mess = '<div style="color:red;">'.$result[0].'</div>'.$mess;
	$html = '<div style="height:100%;">
		<div class="messhead" style="text-align: center;">'.$mess.'</div>
		'.$form.'
	</div>';
	return $html;

