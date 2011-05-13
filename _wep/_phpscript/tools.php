<?
function tools_worktime() {
	global $_CFG;
	$file = $_CFG['_PATH']['wep'].'/_phpscript/main/index.php';
	$FF = file($file);
	print_r(strlen($FF[0]));
	if(strlen($FF[0])>3) {
		$FF[0] = '<?'."\n";
		$result = '<h3 style="color:gray;">Режим "технические работы" - отключён</h3>';
	} else {
		$FF[0] = '<?if(!isset($_GET["_worktime"])) {echo(file_get_contents($_CFG[\'_PATH\'][\'wep\'].\'/_phpscript/main/work.html\'));exit();}'."\n";
		$result = '<h3 style="color:green;">Режим "технические работы" - включён</h3>';
	}
	file_put_contents($file,trim(implode('',$FF)," \n\t\r"));
	return $result;
}

function tools_sendReg() {
	return 'Функция отключена.';
	global $SQL;
	_new_class('ugroup', $UGROUP);
	$data = array();
	$result = $SQL->execSQL('SELECT * FROM users WHERE reg_hash!="1"');
	if(!$result->err)  {
		while ($row = $result->fetch_array()) {
			$arr['vars']['owner_id']=$UGROUP->config["noreggroup"];
			$arr['vars']['active']=0;
			$arr['vars'][$this->mf_createrid]=$arr['vars']['id'];
			$arr['vars']['reg_hash']=md5(time().$arr['vars']['id'].$arr['vars']['name']);
			$pass=$arr['vars']['pass'];
			$arr['vars']['pass']=md5($this->_CFG['wep']['md5'].$arr['vars']['pass']);
			//$_SESSION['user']['id'] = $arr['vars']['id'];
			if(!$UGROUP->child['user']->_add_item($arr['vars'])) {
				_new_class('mail', $MAIL);
				$datamail['from']=$UGROUP->config["mailrobot"];
				$datamail['mailTo']=$arr['vars']['email'];
				$datamail['subject']='Подтвердите регистрацию на '.strtoupper($_SERVER['HTTP_HOST']);
				$href = '?confirm='.$arr['vars']['id'].'&amp;hash='.$arr['vars']['reg_hash'];
				$datamail['text']=str_replace(array('%pass%','%login%','%href%'),array($pass,$arr['vars']['id'],$href),$this->owner->config["mailconfirm"]);
				$MAIL->reply = 0;
				if($MAIL->Send($datamail)) {
					$flag=1;
					$arr['mess']  = $_MESS['regok'];
				}else {
					$UGROUP->child['user']->_delete();
					$arr['mess']  = $_MESS['mailerr'].$_MESS['regerr'];
				}
			} 

		}
	}
}

$dataF = array(
	'tools_worktime'=>'Режим "технические работы"',
	'phpinfo'=>'phpinfo'
);

if(file_exists($_CFG['_PATH']['phpscript2'].'/tools.php'))
	include($_CFG['_PATH']['phpscript2'].'/tools.php');

$html = '<div>Выбирите функцию для запуска</div><hr><ul>';
foreach($dataF as $kk=>$rr) {
	if($_GET['tfunc']==$kk) {
		$html .= '<li><a style="font-weight:bold;" href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&_modul=_tools&tfunc='.$kk.'">'.$rr.'</a>';
		$html .= ' <fieldset><legend>Результат выполнения функции '.$kk.'()</legend>';
		eval('$html .= '.$kk.'();');
		$html .= '</fieldset></li>';
	} else
		$html .= '<li><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&_modul=_tools&tfunc='.$kk.'">'.$rr.'</a></li>';
}
$html .= '</ul>';
$HTML->_templates = 'nologs';
return $html;
?>
