<?
	$result = array();
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='' and !strstr($_REQUEST['ref'],'login')) 
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
//qwertyffgfff
$mess = $form = '';
/*
	require_once $_CFG['_PATH']['extcore']."ugroup.class/openid.php";

	if(isset($_GET['openid_identifier']))
	{
		require_once ($_CFG['_PATH']['extcore']."ugroup.class/try_auth.php");
		list($flag,$mess) = openid_auth();
		if(!$flag) 
			$mess = '<div class="err">'.$mess.'</div>';
		else {
			$form = '<div class="cform">'.$mess.'</div>';
			$mess = '';
		}
	}
	elseif(isset($_GET['janrain_nonce']))
	{
		require_once ($_CFG['_PATH']['extcore']."ugroup.class/finish_auth.php");
		list($flag,$mess) = openid_confirm();
		if(!$flag) 
			$mess = '<div class="err">'.$mess.'</div>';
		elseif($flag==2)
			$mess = '<div class="alert">'.$mess.'</div>';
		else
			$mess = '<div class="err">'.$mess.'</div>';
	}
	else*/

	if(count($_POST) and isset($_POST['login'])) {
		$result = userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			@header("Location: ".$ref);
			die();
		}
	}
	elseif(isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok") {
		userExit();
		$mess=$_CFG['_MESS']['exitok'];
		$ref = $_CFG['_HREF']['BH'];
	}
	elseif(isset($_COOKIE['remember']) and $result = userAuth() and $result[1]) {
		@header("Location: ".$ref);
		die();
	}

		$form = '<div class="cform">
			<form action="" method="post">
					<input type="hidden" name="ref" value="'.$ref.'"/>
					<div>Логин:</div><input type="text" name="login" tabindex="1"/>
					<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
					<div>Запомнить?<input type="checkbox" style="border:medium none; width:30px;" tabindex="3" name="remember" value="1"/></div>
					<input class="submit" type="submit" name="enter" value="Войти" tabindex="3"/>
				</form>
				<a href="'.$_CFG['_HREF']['BH'].'/remind.html">Забыли пароль?</a>
			
			 <div style="clear:both;"></div>
		 </div>';

/*
<hr/><hr/><hr/>
			 <div id="verify-form">
				<form method="get" action="">
				  Ваш OpenID:
				  <input type="hidden" name="action" value="verify" />
				  <input type="text" name="openid_identifier" size="40" value="" />
				  <input type="submit" value="Войти" />
				</form>
			 </div>
*/
	if($result[0]) $result[0] = '<div style="color:red;">'.$result[0].'</div>';
	$html = '<div style="height:100%;">
		<div class="messhead" style="text-align: center;">'.$result[0].$mess.'</div>
		'.$form.'
	</div>';
	return $html;
?>