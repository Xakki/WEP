<?
	$result = array();
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='' and !strstr($_REQUEST['ref'],'login')) 
		$ref= $_REQUEST['ref'];
	elseif($_SERVER['HTTP_REFERER']!='' and !strstr($_SERVER['HTTP_REFERER'],'login'))
		$ref= $_SERVER['HTTP_REFERER'];
	else 
		$ref= '/index.php';

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
	if(count($_POST) and isset($_POST['login']))
	{
		$result = userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			header("Location: ".$ref);
			exit();
		}
	}
	elseif($result = userAuth() and $result[1]) {
		header("Location: ".$ref);
		exit();
	}
	else {
		$form = '<div class="cform">
			<form action="" method="post">
					<input type="hidden" name="ref" value="'.$ref.'"/>
					<div>Логин:</div><input type="text" name="login" tabindex="1"/>
					<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
					<div>Запомнить?<input type="checkbox" style="border:medium none; width:30px;" tabindex="3" name="remember" value="1"/></div>
					<input class="submit" type="submit" name="enter" value="Войти" tabindex="3"/>
				</form>
				<a href="/remind.html">Забыли пароль?</a>
			
			 <div style="clear:both;"></div>
		 </div>';
	}
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
	$html = '<div style="height:100%;">
		'.$form.'
		<div class="messhead">'.$result[0].$mess.'</div>
	</div>';
	return $html;
?>