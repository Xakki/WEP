<?

function tools_sendReg() {
	return 'Функция отключена.';
	global $SQL;
	$UGROUP = new ugroup_class($SQL);
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
			$_SESSION['user']['id'] = $arr['vars']['id'];
			if(!$UGROUP->child['user']->_add_item($arr['vars'])) {
				$MAIL = new mail_class($SQL);
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
	'tools_sendReg'=>'tools_sendReg'
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
return $html;
?>
