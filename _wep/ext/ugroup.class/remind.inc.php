<?php
/**
 * Напоминание пароля
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = 'messages';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 48;

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон для сообщений'),
			'1'=>array('type'=>'int','caption'=>'Время действия ссылки', 'comment'=>'В часах'),
		);
		return $form;
	}

	global $UGROUP,$USERS, $HTML;
	if(!$UGROUP) _new_class('ugroup', $UGROUP);
	if(!$USERS) $USERS = &$UGROUP->childs['users'];
	
	$PARAM = array('timer'=>$FUNCPARAM[1]);

	$html='';
	if(count($_GET) and isset($_GET['id']) and $_GET['id'] and $_GET['t']!='' and $_GET['hash']!='') {
		$PARAM['get'] = $_GET;
		if(isset($_POST['fpass']))
			$PARAM['pass'] = $_POST['fpass'];
		$PARAM['re_pass'] = (isset($_POST['re_fpass'])?$_POST['re_fpass']:'');
		list($flag,$DATA) = $USERS->remindSET($PARAM);
		$DATA = array($FUNCPARAM[0]=>$DATA);
		$html .= $HTML->transformPHP($DATA,$FUNCPARAM[0]);
		if(!$flag) {
			$html .= '<br/>
			<div class="cform" style="width:540px;"><form action="" method="post" name="newpass">
				<div>Введите пароль</div> <input type="password" onkeyup="checkPass(\'fpass\')" maxlength="32" value="" name="fpass" class="accept"/>
				<div>Повторите пароль</div><input type="password" onkeyup="checkPass(\'fpass\')" maxlength="32" value="" name="re_fpass" class="reject"/>
				<div></div><input class="submit" type="submit" name="enter" value="Отправить" disabled="disabled"/>
			</form>
			</div>';
		}
	} else {
		$flag = 0;
		if(count($_POST) and $_POST['mail']!='') {
			$PARAM['post'] = $_POST;
			list($flag,$DATA) = $USERS->remindSEND($PARAM);
			$DATA = array($FUNCPARAM[0]=>$DATA);
			$html .= $HTML->transformPHP($DATA,$FUNCPARAM[0]);
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


