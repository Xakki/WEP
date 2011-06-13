<?

$_CFG['_PATH']['wep'] = dirname($_SERVER['SCRIPT_FILENAME']);
require_once($_CFG['_PATH']['wep'] . '/config/config.php');
require_once($_CFG['_PATH']['core'] . 'html.php');
require_once($_CFG['_PATH']['core'] . 'sql.php');
$TEMP_CFG= array();
$TEMP_CFG['wep']['access'] = $_CFG['wep']['access'] = 0; // авторизация только по главному паролю
$TEMP_CFG['wep']['sessiontype'] = $_CFG['wep']['sessiontype'] = 0; // запускаем сессию стандартно
$TEMP_CFG['site']['bug_hunter'] = $_CFG['site']['bug_hunter'] = array(); // откл запись в баг
$TEMP_CFG['sql']['log'] = $_CFG['sql']['log'] = 0;
$TEMP_CFG['site']['show_error'] = $_CFG['site']['show_error'] = 2;
$TEMP_CFG['wep']['stop_fatal_error'] = $_CFG['wep']['stop_fatal_error'] = false;

session_go(1);
$HTML = new html($_CFG['PATH']['cdesign']);

$_tpl['title'] = 'Установка WEP';

$flag = false;
$mess = '<div style="color:green;">Введите основной логин и пароль для запуска установки.</div>';
if(!isset($_SESSION['step']))
	$_SESSION['step'] = 1;

if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] === 0) {
	//проверяем если уже автоизовался
	$flag = true;
} elseif (count($_POST) and isset($_POST['login']) and $_POST['pass']) {
	$result = static_main::userAuth($_POST['login'], $_POST['pass']);
	if ($result[1]) {
		//успешная авторизация
		$flag = true;
	}else
		$mess = '<div style="color:red;">' . $result[0] . '</div>';
}

if ($flag) {
	$HTML->_templates = 'install';
	$stp = array(
		1 => array('name' => 'Шаг первый', 'css' => '', 'comment' => 'Подключение к БД и настройка дополнительных параметров'),
		2 => array('name' => 'Шаг второй', 'css' => '', 'comment' => 'Проверка структуры сайта'),
		3 => array('name' => 'Шаг третий', 'css' => '', 'comment' => 'Установка модулей и удаление.'),
		999 => array('name' => 'Завершение', 'css' => '', 'comment' => '')
	);
	if (!isset($_GET['step']))
		$_GET['step'] = 1;
	else
		$_GET['step'] = (int) $_GET['step'];

	$file = $_CFG['_PATH']['phpscript'] . '/install/step' . $_GET['step'] . '.php';
	if (file_exists($file)) {
		$var_const = array(
			'mess'=>array('name' => 'ok', 'value' => 'Пора перейти к <a href="'.$_CFG['PATH']['wepname'].'/install.php?step=' . ($_GET['step'] + 1) . '">следующему шагу №' . ($_GET['step'] + 1) . '</a>'),
			'sbmt'=>'Сохранить и перейти на следующий шаг'
		);
		if($_SESSION['step']<$_GET['step'])
			$_tpl['text'] =  'Как ты попал сюда? Вернитесь на <a href="'.$_CFG['PATH']['wepname'].'/install.php?step=' . $_SESSION['step'] . '">Шаг №'.$_SESSION['step'].'</a>.';
		else
			$_tpl['text'] = require($file);
	} 
	elseif($_SESSION['step']>3 and $_GET['step']==$_SESSION['step']) {
		$_tpl['text'] = '<h2>Установка завершена</h2><br/>
			<a href="/index.html">Перейти на сайт</a><br/>
			<a href="'.$_CFG['PATH']['wepname'].'/login.php">Перейти в админку</a>';
	} 
	else {
		$_tpl['text'] = '<h2>Ошибка.</h2><br/>
			<a href="'.$_CFG['PATH']['wepname'].'/install.php">Перейти на начало установки</a><br/>
			<a href="'.$_CFG['PATH']['wepname'].'/login.php">Перейти в админку</a>';
	}

	$_tpl['step'] = '';
	if(isset($stp[$_GET['step']]))
		$stp[$_GET['step']]['css'] = ' selstep';
	foreach ($stp as $k => $r) {
		$_tpl['step'] .= '<a ';
		if($k<=$_GET['step'])
			$_tpl['step'] .= ' href="'.$_CFG['PATH']['wepname'].'/install.php?step='.$k.'"';
		$_tpl['step'] .= 'class="stepitem' . $r['css'] . '"><div class="name">' . $r['name'] . '</div></a>';
	}
	$_tpl['step'] .= '<div class="stepcomment">' . $stp[$_GET['step']]['comment'] . '</div>';
	$_tpl['onload'] = '';
	/* 	$_tpl['ref'] = $ref;
	  $_tpl['action'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['wepname'].'/login.php'.(isset($_GET['install'])?'?install':'');
	  if($result[0]) $result[0] = '<div style="color:red;">'.$result[0].'</div>';
	  elseif(isset($_GET['install'])) $result[0] = '<div style="color:red;">Установка недостающих данных</div>';
	  $_tpl['mess'] = '<div class="messhead">'.$result[0].'</div>'; */
} else {
	$HTML->_templates = 'login';
	$_tpl['text'] = '';
	$_tpl['mess'] = '<div class="messhead">' . $mess . '</div>';
}