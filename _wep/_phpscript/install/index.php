<?

$_CFG['_PATH']['wep'] = dirname($_SERVER['SCRIPT_FILENAME']);
require_once($_CFG['_PATH']['wep'] . '/config/config.php');
require_once($_CFG['_PATH']['core'] . 'html.php');
require_once($_CFG['_PATH']['core'] . 'sql.php');

$_CFG['wep']['access'] = 0; // авторизация только по главному паролю
$_CFG['wep']['sessiontype'] = 0; // запускаем сессию стандартно
$_CFG['site']['bug_hunter'] = 0; // откл запись в баг
$_CFG['sql']['log'] = 0;
$_CFG['site']['show_error'] = 2;

session_go(1);
$HTML = new html($_CFG['PATH']['cdesign']);

$_tpl['title'] = 'Установка WEP';

$flag = false;
$mess = '<div style="color:green;">Введите основной логин и пароль для запуска установки.</div>';

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
		0 => array('name' => 'Шаг первый', 'css' => '', 'comment' => 'Подключение к БД и настройка дополнительных параметров'),
		1 => array('name' => 'Шаг второй', 'css' => '', 'comment' => 'Установка модулей и удаление, со всеми патрохами.'),
		2 => array('name' => 'Шаг третий', 'css' => '', 'comment' => 'Проверка структуры сайта'),
		999 => array('name' => 'Завершение', 'css' => '', 'comment' => '')
	);
	if (!isset($_GET['step']))
		$_GET['step'] = 0;
	else
		$_GET['step'] = (int) $_GET['step'];

	$file = $_CFG['_PATH']['phpscript'] . '/install/step' . $_GET['step'] . '.php';
	if (file_exists($file)) {
		$_tpl['text'] = require($file);
	} else {
		$_GET['step'] = 999;
		$_tpl['text'] = '<h2>Установка завершена</h2>
			<br/>
			<a href="/index.html">Перейти на сайт</a><br/>
			<a href="login.php">Перейти в админку</a>';
	}

	$_tpl['step'] = '';
	$stp[$_GET['step']]['css'] = ' selstep';
	foreach ($stp as $k => $r) {
		$_tpl['step'] .= '<div class="stepitem' . $r['css'] . '"><div class="name">' . $r['name'] . '</div></div>';
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
?>