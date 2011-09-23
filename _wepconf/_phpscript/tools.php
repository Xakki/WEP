<?php

function tools_rubricLatName() {
	return 'Функция отключена';
	global $SQL,$_CFG;
	include($_CFG['_PATH']['phpscript'].'/translit.php');
	$result = $SQL->execSQL('SELECT id,name FROM rubric');
	$data = array();
	if(!$result->err)  {
		while ($row = $result->fetch_array()) {
			$result2 = $SQL->execSQL('UPDATE rubric SET lname="'.ruslat($row['name']).'" WHERE id='.$row['id']);
			if($result->err)
				return 'Ошибка';
		}
	}else
		return 'Ошибка';
	return 'Транслит названия рубрик выполнен!';
}

function tools_boardLatName() {
	//return 'Функция отключена';
	global $SQL,$_CFG;
	_new_class('board',$BOARD);
	$result = $SQL->execSQL('SELECT id,text FROM board');
	$data = array();
	if(!$result->err)  {
		while ($row = $result->fetch_array()) {
			$row['text'] = $BOARD->getTranslitePatchFromText($row['text']);
			
			if(!$row['text']) continue;//return 'Ошибка2. Пустой path id='.$row['id'].' , text=<pre>'.htmlspecialchars($row['text'],ENT_COMPAT,'UTF-8').'</pre>';
			//return $ret.' * after='.htmlspecialchars($row['text'],ENT_COMPAT,'UTF-8');
			
			$result2 = $SQL->execSQL('UPDATE board SET path="'.$row['text'].'" WHERE id='.$row['id']);
			if($result->err)
				return 'Ошибка3';
		}
	}else
		return 'Ошибка';
	return 'Перекодирование выполнено';
}

function tools_getName() {
	global $SQL,$_CFG;
	$result = $SQL->execSQL('SELECT name FROM city WHERE center=1');
	$data = '|';
	if(!$result->err)  {
		while ($row = $result->fetch_array()) {
			$data .= $row['name'].'|';
		}
	}else
		return '';
	return $data;
}

function tools_clearPhone() {
	global $SQL,$_CFG;
	$result = $SQL->execSQL('SELECT id,phone FROM board WHERE phone!=""');
	if(!$result->err)  {
		while ($row = $result->fetch_array()) {
			$result2 = $SQL->execSQL('UPDATE board SET phone="'.static_form::_phoneReplace($row['phone']).'" WHERE id='.$row['id']);
			if($result->err)
				return 'Ошибка3';
		}
		return 'Обработано';
	} else
		return '';
}

function tools_userCheck() {
	global $SQL,$_CFG;
	$html = '';
	$textarea = '<hr/>Уважаемы пользователь. В связи со сбоем возникшим при отправке почты, мы повторно высылаем вам письмо с подтверждением регистрации. Перейдя по ссылке указанной выше вы автоматический авторизуетесь и получите доступ к дополнительным возможностям при подаче объявления:
		<ul>
			<li>публиковать объявления со сроком до 90 дней.</li>
			<li>отложенная публикация на 1,2,3,5...14 дней.</li>
			<li>добовлять 6ти фотографии , каждую размером до 3 Мб. Итоговое качество может достигать разрешения 1024х768px.</li>
			<li>добавлять до 10ти объявлений в день.</li>
			<li>Управлять своими объявлениями.</li>
			<li>просмотр статистики объявления (просмотры уникальным пользователем).</li>
			<li>Установить метку на карте, позволяющий вашим клиентам увидеть местоположение объекта продажи.</li>
			<li>подписка на RSS.</li>
		</ul>
		Если вы не подтвердите регистрацию, данные указанные вами при регистрации будут автоматический удалены 10го июля 2011г.';
	if(!isset($_POST['limit'])) {
		$html = 'Рассылка писем<br/><form method="post">
			<lable>Текст</lable><br/><textarea name="text" rows="9" cols="70" >'.htmlspecialchars($textarea,ENT_QUOTES,$_CFG['wep']['charset']).'</textarea><br/>
			<lable>Лимит</lable><br/><input type="text" name="limit" value="20"/>
			<br/><input type="submit" name="Пуск"/></form>';
	} else {
		$ttw  = getmicrotime();
		_new_class('mail',$MAIL);
		_new_class('ugroup',$UGROUP);
		$MAIL->reply = 0;
		$result = $SQL->execSQL('SELECT * FROM users WHERE active=0 and lastvisit=0 LIMIT '.$_POST['limit']);
		if(!$result->err)  {
			while ($row = $result->fetch_array()) {
				$datamail = array();
				$datamail['text'] = '';
				$datamail['from']=$UGROUP->config['mailrobot'];
				$datamail['mail_to']=$row['email'];
				$datamail['subject']='Подтвердите регистрацию на '.strtoupper($_SERVER['HTTP_HOST']);
				$href = '?confirm='.$row['login'].'&hash='.$row['reg_hash'];
				$datamail['text'] .= str_replace(array('%pass%','%login%','%href%','%host%'),array('Если вы забыли пароль, установленный вами ранее, то воспользуйтесь формой <a href="http://'.$_SERVER['HTTP_HOST'].'/remind.html">востановления пароля</a>.',$row['login'],$href,$_SERVER['HTTP_HOST']),$UGROUP->config['mailconfirm']);
				$datamail['text'] .= $_POST['text'];
				if($MAIL->Send($datamail)) {
					$SQL->execSQL('UPDATE users SET lastvisit=3 WHERE id='.$row['id']);
				} else {
					$html .='-';
					$SQL->execSQL('UPDATE users SET lastvisit=-3 WHERE id='.$row['id']);
				}
				
			}
			$html .= '<br/>Обработано';
		}
		$html .= '---- '.(getmicrotime()-$ttw).'mc -----';
	}
	return $html;
}

function tools_userBoard() {
	global $SQL,$_CFG;
	$html = '';
	if(!isset($_POST['go'])) {
		$result = $SQL->execSQL('SELECT count(t1.id) as cnt FROM board t1 JOIN users t2 ON t1.email=t2.email WHERE t1.email!="" and t1.creater_id=0');
		$html = 'Всё нормально';
		if(!$result->err and $row = $result->fetch_array())  {
			if($row['cnt'])
				$html = 'Обнаруженно '.$row['cnt'].' объяв не связанных с пользователем.<br/><form method="post"><input type="hidden" name="go" value="1"/><input type="submit" name="Пуск"/></form>';
		}
	} else {
		$result = $SQL->execSQL('SELECT t1.id,t2.id as creater_id FROM board t1 JOIN users t2 ON t1.email=t2.email WHERE t1.email!="" and t1.creater_id=0');
		if(!$result->err)  {
			while ($row = $result->fetch_array()) {
				$SQL->execSQL('UPDATE board SET creater_id='.$row['creater_id'].' WHERE id='.$row['id']);
			}
		}
		$html = 'Готово!';
	}
	return $html;
}

$dataF['tools_rubricLatName'] = 'Названия для рубрик транслитом';
$dataF['tools_boardLatName'] = 'Пути для объявлений транслитом';
$dataF['tools_getName'] = 'Показать названия городов';
$dataF['tools_clearPhone'] = 'Обновление телефонных номеров';
$dataF['tools_userCheck'] = 'Рассылка писем пользователю';
$dataF['tools_userBoard'] = 'Подключене объяв к пользователям, email которых совпадает';