<?
	global $UGROUP,$USERS, $HTML;
	if(!$UGROUP) _new_class('ugroup', $UGROUP);
	if(!$USERS) $USERS = &$UGROUP->childs['users'];


	$html='';
	if(count($_GET) and $_GET['id']!='' and $_GET['t']!='' and $_GET['hash']!='') {
		list($flag,$DATA) = $USERS->remindSET($_GET,$_POST['fpass'],$_POST['re_fpass']);
		$DATA = array('messages'=>$DATA);
		$html .= $HTML->transformPHP($DATA,'messages');
		if(!$flag) {
			$html .= '<br/>
			<div class="cform" style="width:540px;"><form action="" method="post" name="newpass">
				<div>Введите пароль</div> <input type="password" onkeyup="checkPass(\'fpass\')" maxlength="32" value="" name="fpass" class="accept"/>
				<div>Повторите пароль</div><input type="password" onkeyup="checkPass(\'fpass\')" maxlength="32" value="" name="re_fpass" class="reject"/>
				<div></div><input class="submit" type="submit" name="enter" value="Отправить" disabled="disabled"/>
			</form>
			</div>';
		}
	}else {
		$flag = 0;
		if(count($_POST) and $_POST['mail']!='') {
			list($flag,$DATA) = $USERS->remindSEND($_POST);
			$DATA = array('messages'=>$DATA);
			$html .= $HTML->transformPHP($DATA,'messages');
		}

		if($flag<1) {
			$html .= '<div class="messages"><div class="ok">Введите ваш E-mail, указанный при регистрации.<br/>
			На даный почтовый ящик будет выслано письмо со ссылкой для смены пароля.<br/>
			Ссылка на смену пароля будет действовать в течении 2х суток с момента отправки данной формы.</div></div>
			<br/>
			<div class="cform" style="width:540px;"><form action="" method="post" name="remind">
				Введите свой E-mail<br/>
				<input type="text" name="mail"/>
				<div></div><input class="submit" type="submit" name="enter" value="Запрос смены пароля"/>
			</form>
			</div>';
		}
	}

	return '<div align="center">'.$html.'</div>';


