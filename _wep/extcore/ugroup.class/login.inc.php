<?
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

	if($_POST['token']) {
		$err = $dt = array();
		$authdata = file_get_contents('http://loginza.ru/api/authinfo?token='.$_POST['token']);
		$dt['loginza_data'] = $authdata;
		$authdata = json_decode($authdata,TRUE);
		if($authdata['error_type']) {
			$err[] = $authdata['error_type'].':'.$authdata['error_message']; 
		} else {
			$dt['loginza_token'] = $_POST['token'];
			$dt['loginza_provider'] = $authdata['provider'];
			$dt['id'] = md5($authdata['identity']);
			if($authdata['provider']=='http://openid.yandex.ru/server/') {
				$dt['name'] = substr(substr($authdata['identity'],24),0,-1);
				$dt['email'] = substr(substr($authdata['identity'],24),0,-1).'@ya.ru';
			}
			elseif($authdata['provider']=='http://mail.ru/') {
				$dt['name'] = $authdata['name']['first_name'];
				$temp = substr(substr($authdata['identity'],18),0,-1);
				$temp = explode('/',$temp);
				$dt['email'] = $temp[1].'@'.$temp[0].'.ru';
			}
			elseif($authdata['provider']=='http://vkontakte.ru/') {
				$dt['name'] = $authdata['name']['last_name'].' '.$authdata['name']['first_name'];
			}
			elseif(isset($authdata['email']) and isset($authdata['name'])) {
				$dt['email'] = $authdata['email'];
				if(is_array($authdata['name'])) {
					if($authdata['name']['full_name'])
						$dt['name'] = $authdata['name']['full_name'];
					elseif($authdata['name']['first_name'])
						$dt['name'] = $authdata['name']['first_name'];
				}else 
					$dt['name'] = $authdata['name'];
			}

		}
		print_r('<pre>');print_r($authdata);print_r($dt);
		if(!count($err)) {
			return 'OK';
		} else {
			return 'Ошибка авторизации<br/><div class="err">'.implode('</div><div class="err">',$err).'</div>';
		}
	}
	elseif(count($_POST) and isset($_POST['login'])) {
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
				<a href="'.$_CFG['_HREF']['BH'].'remind.html">Забыли пароль?</a>
			
			 <div style="clear:both;"></div>
		 </div>';

	if($result[0]) $result[0] = '<div style="color:red;">'.$result[0].'</div>';
	$html = '<div style="height:100%;">
		<div class="messhead" style="text-align: center;">'.$result[0].$mess.'</div>
		'.$form.'
	</div>';
	return $html;
?>
