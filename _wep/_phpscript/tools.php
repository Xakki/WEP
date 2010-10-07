<?exit();
	if(!isset($_ADMIN))
		$_ADMIN = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
	require_once($_ADMIN.'/system/config.ini.php');
	require_once($_ADMIN."/system/sql.class.php");
header("Content-type:text/html;charset=utf-8");

	$SQL = new class_sql(true);
	$ugroup = new ugroup_class($SQL);
	$data = array();
	$result = $SQL->execSQL('SELECT * FROM users WHERE reg_hash!="1"');
	if(!$result->err)  {
		while ($row = $result->fetch_array()) {
			$arr['vars']['owner_id']=$this->owner->config["noreggroup"];
			$arr['vars']['active']=0;
			$arr['vars']['creater_id']=$arr['vars']['id'];
			$arr['vars']['reg_hash']=md5(time().$arr['vars']['id'].$arr['vars']['name']);
			$pass=$arr['vars']['pass'];
			$arr['vars']['pass']=md5($this->_CFG['wep']['md5'].$arr['vars']['pass']);
			$_SESSION['user']['id'] = $arr['vars']['id'];
			if(!$this->_add_item($arr['vars'])) {
				$MAIL = new mail_class($SQL);
				$datamail['from']=$this->owner->config["mailrobot"];
				$datamail['mailTo']=$arr['vars']['email'];
				$datamail['subject']='Подтвердите регистрацию на '.strtoupper($_SERVER['HTTP_HOST']);
				$href = '?confirm='.$arr['vars']['id'].'&amp;hash='.$arr['vars']['reg_hash'];
				$datamail['text']=str_replace(array('%pass%','%login%','%href%'),array($pass,$arr['vars']['id'],$href),$this->owner->config["mailconfirm"]);
				$MAIL->reply = 0;
				if($MAIL->Send($datamail)) {
					$flag=1;
					$arr['mess']  = $_MESS['regok'];
				}else {
					$this->_delete();
					$arr['mess']  = $_MESS['mailerr'].$_MESS['regerr'];
				}
			} 

		}
	}else exit('<hr>error 1');

	$data_insert = array();
print_r('<hr>Обработка '.count($data).' данных');

	foreach($data as $rub=>$row) {
		$cH=0;$cI=0;$cI4=0;$cL=0;$cT=0;
		foreach($row as $k=>$r) {
			$cls= array();
			$tabl = '';
			$prefC ='';
			if($r['type2']=='checkbox') {
				$prefC =''.$cH;
				$tabl = 'btynyint';
				$cH++;
			}
			elseif($r['type2']=='intu10') {
				$prefC ='2'.$cI;
				$tabl = 'bint';
				$cI++;
			}
			elseif($r['type2']=='intu4') {
				$prefC ='1'.$cI4;
				$tabl = 'bint';
				$cI4++;
			}
			elseif($r['type2']=='list') {
				$prefC ='5'.$cL;
				$tabl = 'bvarchar';
				$cL++;
			}
			elseif($r['type2']=='text254') {
				$prefC ='7'.$cT;
				$tabl = 'bvarchar';
				$cT++;
			}
			$cls[]='type='.$prefC;


			$result = $SQL->execSQL('SELECT * FROM '.$tabl.' where owner_id='.$k);
			if(!$result->err)  {
				while ($temprow = $result->fetch_array()) {
					$data_insert[$temprow['board']]['name'.$prefC] = $temprow['name'];
				}
			}else exit('<hr>error 2 = '.$tabl);

			$result = $SQL->execSQL('UPDATE param SET '.implode(' , ',$cls).' WHERE id='.$k);
		}
		
	}

	print_r('<hr>Записывается '.count($data_insert).' данных');
	foreach($data_insert as $k=>$r) {
		$result = $SQL->execSQL('INSERT INTO paramb (owner_id,'.implode(',',array_keys($r)).')
		VALUES ("'.$k.'", "'.implode('","',$r).'")');
		if($result->err) exit('<hr>error 3 ');
	}
?>
