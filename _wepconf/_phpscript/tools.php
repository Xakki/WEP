<?

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

function tools_sendMail() {
	global $SQL,$_CFG;
	_new_class('mail',$MAIL);

	$datamail = array('from'=>'robot@unidoski.ru');
	$datamail['mailTo']='xakki@ya.ru';
	$datamail['subject']='Тестовая отправка письма на '.strtoupper($_SERVER['HTTP_HOST']);
	
	$datamail['text'] = ' Куча всякого текста ивсяких <a href="http://xakki.ru">ссылок</a> <hr/><div>Трололо</div>';

	if($MAIL->Send($datamail)) {
		return 'Успешно отправлено на xakki@ya.ru';
	}else
		return 'Сервер сказал что ошибка отправки письма';
}


$dataF['tools_rubricLatName'] = 'Названия для рубрик транслитом';
$dataF['tools_boardLatName'] = 'Пути для объявлений транслитом';
$dataF['tools_getName'] = 'Показать названия городов';
$dataF['tools_sendMail'] = 'Тестовая отправка почты';


