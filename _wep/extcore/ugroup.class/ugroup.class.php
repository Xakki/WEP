<?

class ugroup_class extends kernel_class
{

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config["mailto"] = 'info@unidoski.ru';
		$this->config["mailrobot"] = 'robot@unidoski.ru';
		$this->config["mailconfirm"] = '';
		$this->config["mailremind"] = '';
		$this->config["mailbottom"] = '';
		$this->config["reg"] = 1;
		$this->config["noreggroup"] = 4;
		$this->config["reggroup"] = 4;
		$this->config["rememberday"] = 20;

		$this->config_form["mailto"] = array("type" => "text", 'mask' =>array('min'=>1,"name"=>'email'), "caption" => "Адрес службы поддержки");
		$this->config_form["mailrobot"] = array("type" => "text", 'mask' =>array('min'=>1,"name"=>'email'), "caption" => "Адрес Робота");
		$this->config_form['mailconfirm'] = array(
			'type' => 'ckedit', 
			'caption' => 'Текст письма для подтверждения', 
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['mailremind'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст письма востановления пароля', 
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['mailbottom'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст прикрепляемый в конце письма', 
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form["reg"] = array("type" => "checkbox", "caption" => "Включить регистрацию?");
		$this->config_form["noreggroup"] = array("type" => "list", "listname"=>"list", "caption" => "Не подтвердившие регистрацию");
		$this->config_form["reggroup"] = array("type" => "list", "listname"=>"list", "caption" => "Регистрировать по умолчинию");
		$this->config_form["rememberday"] = array("type" => "int", 'mask' =>array('min'=>1), "caption" => 'Дней запоминания авторизации');
	}

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_actctrl = true;
		$this->caption = "Группы";
		return 0;
	}

	function _create() {
		parent::_create();

		$this->_vf_list = 'concat(name,"[",level,"]")';// агрегатная ф мускл
		
		$this->_enum['level'] = array(
			0=>'Полный доступ (абсолютный)',
			1=>'Полный доступ (проверка привелегий)',
			2=>'Доступ к модулям',
			5=>'нет доступа');

		$this->fields["name"] = array("type" => "varchar", "width" =>128, "attr" => "NOT NULL");
		$this->fields["wep"] = array("type" => "bool", "attr" => "NOT NULL default 0");
		$this->fields["level"] = array("type" => "tinyint", "width" =>2, "attr" => "NOT NULL default 1");
		$this->fields["filesize"] = array("type" => "int", "width" =>5, "attr" => "NOT NULL DEFAULT 0");
		$this->fields["paramboard"] = array("type" => "int", "width" =>3, "attr" => "NOT NULL DEFAULT 5");
		$this->fields["paramsubsc"] = array("type" => "int", "width" =>3, "attr" => "NOT NULL DEFAULT 1");
		$this->fields["design"] = array("type" => "varchar", "width" =>128, "attr" => "NOT NULL");

		$this->fields_form["name"] = array("type" => "text", 'mask' =>array('min'=>1), "caption" => "Название группы");
		$this->fields_form["wep"] = array("type" => "checkbox", "caption" => "Разрешить вход в админку?");
		$this->fields_form["level"] = array("type" => "list", "listname"=>"level", "caption" => "Доступ в CMS");
		$this->fields_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизаин личного кабинета');
		$this->fields_form["filesize"] = array("type" => "int", "caption" => "Доступный размер диска", "comment" => "Значение в мегабайтах, 0 - запрет",'mask'=>array("max"=>1000));
		$this->fields_form["paramboard"] = array("type" => "int", "caption" => "Объявления", "comment" => "Разрешённое число объявлений в день");
		$this->fields_form["paramsubsc"] = array("type" => "int", "caption" => "Подписки", "comment" => "Разрешённое число подписок на объявления");
		$this->fields_form["active"] = array("type" => "checkbox", "caption" => "Активность");

		$this->def_records[] = array('name'=>'Администраторы','level'=>0,'filesize'=>'100','active'=>1,'id'=>1,'wep'=>1);

		$this->create_child("users");
		//$this->create_child("weppages");
	}

	function _getlist($listname) {
		$data = array();
		if ($listname == "glist") {
			$result = $this->SQL->execSQL("SELECT id, name FROM ".$this->tablename);
			if ($result->err) return $this->_message($result->err);
			while ($row = $result->fetch_array())
				$data[$row['id']] = $row['name'];
			return $data;
		}
		elseif ($listname == "mdesign") {
			$dir = dir($this->_CFG['_PATH']['cdesign']);
			while ($entry = $dir->read()) {
				if ($entry!= "." and $entry!= "..") {
					$data[$entry] = $entry;
				}
			}
			$dir->close();
			return $data;
		}
		else return parent::_getlist($listname);
	}
	function _UpdItemModul($param) {
		$ret = parent::_UpdItemModul($param);
		//if($ret[1] and $_SESSION['user'] and $_SESSION['user']['owner_id']==$this->id)
		//	session_unset();
		return $ret;
	}
}

class users_class extends kernel_class {

	function _set_features()
	{
		if (parent::_set_features()) return 1;
		$this->mf_actctrl = true;
		$this->mf_use_charid = true;
		$this->caption = "Пользователи";
		return 0;
	}

	function _create()
	{
		parent::_create();
		$this->_unique['email']='email';

		$this->fields["id"] = array("type" => "VARCHAR", "width" => 32, "attr" => "NOT NULL");
		$this->fields["name"] = array("type" => "VARCHAR", "width" => 32,"attr" => "NOT NULL");
		//$this->fields["sname"] = array("type" => "VARCHAR", "width" => 32,"attr" => "NOT NULL");
		//$this->fields["tname"] = array("type" => "VARCHAR", "width" => 32,"attr" => "NOT NULL");
		$this->fields["pass"] = array("type" => "VARCHAR", "width" => 32, "attr" => "NOT NULL");
		//$this->fields["address"] = array("type" => "VARCHAR", "width" => 127,"attr" => "NOT NULL");
		$this->fields["phone"] = array("type" => "VARCHAR", "width" => 127,"attr" => "NOT NULL");
		$this->fields["email"] =  array("type" => "VARCHAR", "width" => 32, "attr" => "NOT NULL");
		$this->fields["www"] =  array("type" => "VARCHAR", "width" => 32, "attr" => "NOT NULL");
		//$this->fields["description"] =  array("type" => "VARCHAR", "width" => 254, "attr" => "NOT NULL");

		// service field
		$this->fields["reg_date"] = array("type" => "int", "attr" => "NOT NULL");
		$this->fields["reg_ip"] = array("type" => "VARCHAR", "width" => 128,"attr" => "NOT NULL DEFAULT '127.0.0.1'");
		$this->fields["reg_hash"] = array("type" => "VARCHAR", "width" => 128);
		$this->fields["up_date"] = array("type" => "TIMESTAMP", "attr" => "NOT NULL");

		// ----
		if($this->mf_use_charid){
			$this->fields_form['id'] =	array('type' => 'text', 'caption' => 'Логин','mask'=>array('name'=>'login','min' => '4','sort'=>1),'comment'=>'Логин должен состоять только из латинских букв и цифр.');
			if(_prmUserCheck())  // Запрет поля на редактирование
				$this->fields_form['id']['readonly']=true;
		}
		
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Имя','mask'=>array('name'=>'name','usercheck'=>1)); // Вывод поля при редактировании

		//$this->fields_form['sname'] = array('type' => 'text', 'caption' => 'Фамилия','mask'=>array('name'=>'name'));
		//$this->fields_form['tname'] = array('type' => 'text', 'caption' => 'Отчество','mask'=>array('name'=>'name'));
		//$this->fields_form['address'] = array('type' => 'text', 'caption' => 'Адрес');

		if(_prmUserCheck(1)) // Вывод поля генерации пароля если админ
			$this->fields_form['pass'] = array('type' => 'password2', 'caption' => 'Пароль','md5'=>$this->_CFG['wep']['md5'], 'mask'=>array('min' => '6','fview'=>1));
		elseif(!_prmUserCheck())
			$this->fields_form['pass'] = array('type' => 'password_new', 'caption' => 'Пароль','mask'=>array('min' => '6','fview'=>1));

		$this->fields_form['phone'] = array(
			'type' => 'text', 
			'caption' => 'Телефон',
			'mask'=>array('usercheck'=>1,'name'=>'phone','onetd'=>'Контакт'));// Вывод поля при редактировании
		$this->fields_form['www'] = array(
			'type' => 'text', 
			'caption' => 'WWW',
			'mask'=>array('usercheck'=>1,'name'=>'www','onetd'=>'none'));// Вывод поля при редактировании

		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'E-mail','mask'=>array('name'=>'email','min' => '7','onetd'=>'close'));

		//$this->fields_form['description'] =  array('type' => 'textarea', 'caption' => 'Дополнительная информация','mask'=>array('max' => 2048));

		$this->fields_form['reg_ip'] =	array(
			'type' => 'text',
			'readonly' => 1, 
			'caption' => 'IP-пользователя',
			'mask'=>array('evala'=>'$_SERVER["REMOTE_ADDR"]','usercheck'=>1));
		$this->fields_form['reg_date'] =	array('type' => 'date','readonly' => 1, 'caption' => 'Дата регистрации','mask'=>array('evala'=>'time()','sort'=>1));
		$this->fields_form['reg_hash'] = array('type' => 'hidden',  'caption' => 'Хэш','mask'=>array('eval'=>1,'fview'=>1,'usercheck'=>1));
		$this->fields_form['cntdec'] = array(
			'type' => 'list', 
			'listname'=>array('class'=>'board','field'=>'count(tx.id) as cntdec', 'leftjoin'=>'t1.id=tx.creater_id'), 
			'readonly'=>1,
			'caption' => 'Объявл.',
			'mask' =>array('usercheck'=>1,'sort'=>''));
		$this->fields_form['owner_id'] = array('type' => 'list', 'listname'=>'ownerlist', 'caption' => 'Группа', 'mask' =>array('usercheck'=>1,'fview'=>1));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Пользователь активен', 'mask' =>array('usercheck'=>1));

		$this->def_records[] = array(
			'id'=>$this->_CFG['wep']['login'],
			'name'=>'Главный',
			'pass'=>md5($this->_CFG['wep']['md5'].$this->_CFG['wep']['password']), 
			'active'=>1,
			'reg_date'=>time(),
			'owner_id'=>1,
			'reg_hash'=>1);
		//$this->cookieAuthorization();
	}

	function _UpdItemModul($param) {
		$ret = parent::_UpdItemModul($param);
		if($ret[1] and $_SESSION['user'] and $_SESSION['user']['id']==$this->id) {
			$this->_select();
			/** Обновление сессии пользователя если он сам обновил*/
			$_SESSION['user'] = array_merge($_SESSION['user'],$this->data[$this->id]);
		}
		return $ret;
	}

	function authorization($login,$pass) {
		if(isset($_POST['login']) && isset($_POST['pass']) && $_POST['login']!='' && $_POST['pass']!='')
		{
			$login = $_POST['login'];
			$pass = $_POST['pass'];
			if ($this->mf_use_charid and !preg_match('/^[0-9A-Za-z]+$/', $login))
				 return array('Поле `Логин` введено не корректно. Допустим ввод только латинских букв и цифр.',0);
			elseif (!$this->mf_use_charid and !preg_match('/^[0-9A-Za-z\.\-\@]+$/', $login))
				 return array('Поле `Email` введено не корректно. Допустим ввод только латинских букв,цифр, точки, тире и @',0);
			else
			{
				$this->listfields = array('t2.*,t2.active as gact,t2.name as gname,t1.*');
				$this->clause = "t1 Join {$this->owner->tablename} t2 on t1.".$this->owner_name."=t2.id where t1.".($this->mf_use_charid?'id':'email')." = '".$login."' and t1.pass ='".md5($this->_CFG['wep']['md5'].$pass)."'";
				$this->_list();
				if(count($this->data))
				{
					unset($_SESSION['user']);
					if(!$this->data[0]["active"])
						return array($this->_CFG['_MESS']['auth_banuser'],0);
					elseif(!$this->data[0]["gact"])
						return array($_CFG['_MESS']['auth_bangroup'],0);
					elseif(_strlen($this->data[0]["reg_hash"])>5)
						return array($this->_CFG['_MESS']['authnoconf'],0);
					elseif($this->data[0]["level"]>=5)
						return array($this->_CFG['_MESS']['denied'],0);
					else
					{
						if($_POST['remember']=='1'){
							setcookie('remember', md5($this->data[0]['pass']).'_'.$this->data[0]['id'], (time()+(86400*$this->owner->config['rememberday'])), '/', $_SERVER['HTTP_HOST2']);
						}
						return $this->setUserSession();
					}
				}
				else
					return array($this->_CFG['_MESS']['autherr'],0);
			}
		}
		else
			return array("Поля `Логин` и `Пароль` - обязательные! Необходимо ввести свой `Логин` и `Пароль` чтобы авторизоваться.",0);
	}

	
	function cookieAuthorization() {
		if(!isset($_SESSION['user']) and isset($_COOKIE['remember']))
		{
			if (preg_match("/^[0-9A-Za-z\_]+$/",$_COOKIE['remember']))
			{
				$pos = strpos($_COOKIE['remember'],'_');
				$this->listfields = array("t2.*,t2.active as gact,t2.name as gname,t1.*");
				$this->clause = "t1 Join {$this->owner->tablename} t2 on t1.".$this->owner_name."=t2.id where t1.id = '".substr($_COOKIE['remember'],($pos+1))."' and md5(t1.pass) ='".substr($_COOKIE['remember'],0,$pos)."'";
				$this->_list();
				if(count($this->data))
				{
					unset($_SESSION['user']);
					if(!$this->data[0]["active"])
						return array("Ваш аккаунт заблокирован. За дополнительной информацией обращайтесь к Администратору сайта.",0);
					elseif(!$this->data[0]["gact"])
						return array("Ваша группа заблокирована. За дополнительной информацией обращайтесь к Администратору сайта.",0);
					elseif(_strlen($this->data[0]["reg_hash"])>5)
						return array("Вы не подтвердили регистрацию.",0);
					elseif($this->data[0]["level"]>=5)
						return array("Доступ закрыт.",0);
					else
					{
						setcookie('remember', md5($this->data[0]['pass']).'_'.$this->data[0]['id'], (time()+(86400*$this->owner->config['rememberday'])), '/', $_SERVER['HTTP_HOST2']);
						return $this->setUserSession();
					}
				}
			}
		}

		return array('',0);
	}

	function setUserSession() {
		$_SESSION['user'] = $this->data[0];
		$_SESSION['user']['owner_id'] = $this->data[0][$this->owner_name];
		$MODULs = new  modulprm_class($this->SQL);
		$_SESSION['modulprm'] = $MODULs->userPrm($this->data[0][$this->owner_name]);
		$_SESSION['FckEditorUserFilesUrl'] = $this->_CFG['_HREF']['BH'].$this->_CFG['PATH']['userfile'].$_SESSION['user']['id'].'/';
		$_SESSION['FckEditorUserFilesPath'] = $this->_CFG['_PATH']['path'].$this->_CFG['PATH']['userfile'].$_SESSION['user']['id'].'/';
		if(_prmUserCheck(2)) {
			setcookie('_showerror',1);
			$_COOKIE['_showerror']=1;
		}
		return array($this->_CFG['_MESS']['authok'],1);
	}

	function regForm(){
		global $_MESS,$_tpl;
		if(!$this->owner->config["reg"]) return array(array('messages'=>array(array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedreg']))),1);
		if(_prmUserCheck()) return array(array('messages'=>array(array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedu']))),1);
		$flag=0;// 0 - показывает форму, 1 - нет
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();
		$this->fields_form['_info']= array("type"=>"info",'caption'=>'Регистрация пользователя','css'=>'caption');
		if(count($_POST) and $_POST['sbmt']) {
			$this->kPreFields($_POST,$param);
			$arr = $this->fFormCheck($_POST);
			if(!count($arr['mess'])){

				$this->listfields = array("LOWER(t1.id) as id");
				$this->clause = "t1 where ".($this->mf_use_charid?"t1.id = '".$arr['vars']['id']."' or":"")." t1.email = '".$arr['vars']['email']."'";
				$this->_list('id');
				if($this->mf_use_charid and isset($this->data[strtolower($arr['vars']['id'])])) 
					$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['notlogin']);
				elseif(count($this->data)) 
					$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['notemail']);
				else {
					$arr['vars']['owner_id']=$this->owner->config["noreggroup"];
					$arr['vars']['active']=0;
					if(!$arr['vars']['name'])
						$arr['vars']['name'] = $arr['vars']['id'];
					$arr['vars']['creater_id']=$arr['vars']['id'];
					$arr['vars']['reg_hash']=md5(time().$arr['vars']['id']);
					$pass=$arr['vars']['pass'];
					$arr['vars']['pass']=md5($this->_CFG['wep']['md5'].$arr['vars']['pass']);
					$_SESSION['user']['id'] = $arr['vars']['id'];
					if(!$this->_add_item($arr['vars'])) {
						$MAIL = new mail_class($this->SQL);
						$datamail['from']=$this->owner->config["mailrobot"];
						$datamail['mailTo']=$arr['vars']['email'];
						$datamail['subject']='Подтвердите регистрацию на '.strtoupper($_SERVER['HTTP_HOST']);
						$href = '?confirm='.$arr['vars']['id'].'&amp;hash='.$arr['vars']['reg_hash'];
						$this->owner->config["mailbottom"] = str_replace('%YEAR%',date('Y'),$this->owner->config["mailbottom"]);
						$datamail['text']=str_replace(array('%pass%','%login%','%href%','%mailbottom%'),array($pass,$arr['vars']['id'],$href,$this->owner->config["mailbottom"]),$this->owner->config["mailconfirm"]);
						$MAIL->reply = 0;
						if($MAIL->Send($datamail)) {
							$flag=1;
							$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['regok']);
						}else {
							$this->_delete();
							$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['mailerr']);
							$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['regerr']);
						}
					} else
						$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['regerr']);
					unset($_SESSION['user']);
				}
			}
		} else $mess = $this->kPreFields($_POST,$param);

		$this->setCaptcha();
		$formflag = $this->kFields2Form($param);
		$this->form['sbmt']['value']='Я согласен с правилами и хочу зарегестрироваться';
		$this->form['rulesinfo'] = array('type'=>'info','caption'=>'<a href="/terms.html" target="_blank">Правила пользования сайтом</a>','css'=>'rulelink');

		return Array(Array('messages'=>($mess+$arr['mess']), 'form'=>(!$flag?$this->form:array()), 'class'=>'regform'), $flag);
	}

	function regConfirm() {
		global $_MESS;
		$_GET['hash'] = preg_replace("/[^0-9a-f]+/","",$_GET['hash']);
		if(!$this->owner->config["reg"])
			$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedreg']);
		elseif(!isset($_GET['confirm']) or !isset($_GET['hash']) or _strlen($_GET['hash'])!=32)
			$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['errdata']);
		else {
			$this->listfields = array("t1.id,t1.reg_hash");
			$this->clause = "t1 where t1.id = '".preg_replace("/[^0-9a-z]+/","",$_GET['confirm'])."'";
			$this->_list();
			if(count($this->data) and _strlen($this->data[0]['reg_hash'])<5)
				$mess[] = array('name'=>'alert', 'value'=>$this->_CFG['_MESS']['confno']);
			elseif(count($this->data) and $this->data[0]['reg_hash']==$_GET['hash']){
				$this->id = $this->data[0]['id'];
				$this->fld_data['reg_hash']= 1;
				$this->fld_data['active']= 1;
				$this->fld_data['owner_id']= $this->owner->config["reggroup"];
				if(!$this->_update())
					$mess[] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['confok']);
				else
					$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['conferr']);
			}
			else
				$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['errdata']);
		}

		return Array(array('messages'=>$mess),$flag);
	}

	function remind() {
		global $HTML;
		$form=1;$html='';
		if(count($_GET) and $_GET['id']!='' and $_GET['t']!='' and $_GET['hash']!='') {
			$this->listfields = array("t1.*");
			$this->clause = "t1 where t1.id = '".$_GET['id']."'";
			$this->_list();
			$datau=$this->data[0];
			if(count($this->data)==1 and $datau['active']==1 and $_GET['hash']==(md5($datau['pass'].$_GET['t'].$datau['email']).'h')) {
				
				$form=0;
				if(count($_POST)) {
					if($_POST['pass']==$_POST['re_pass']){
						if(preg_match($this->_CFG['_MASK']['name'],$_POST['pass']) and _strlen($_POST['pass'])>=6) {
							$this->SQL->execSQL('UPDATE '.$this->tablename.' SET pass="'.md5($this->_CFG['wep']['md5'].$_POST['pass']).'" where email="'.$datau['email'].'"');
							$html = '<div class="ok">Новый пароль упешно записан. И вы можете авторизоваться прямо сейчас.</div>';
							$form=1;
						}else
							$html = '<div class="error">Неверно повторен пароль.</div>';
					}
					else
						$html = '<div class="error">Некоректные данные.</div>';
				}
				
			}
			elseif(count($this->data)==1 and $this->data[0]['active']!=1) {
				$html = '<div class="error">Ваш профиль отключен или не подтверждён. Обратитесь в <a href="/mail.html?feedback=1">службу поддержки сайта</a></div>';
			}
			else{
				$html = '<div class="error">Не верные входные данные.<br/> Возможно вы уже воспользовались данной ссылкой.</div>';
			}
			$html = '<div class="messages">'.$html.'</div>';
			if(!$form) {
				$html .= '<div class="messages"><div class="ok">Введите новый пароль для пользователя '.($HTML->_itype($datau['id'])=='string'?$datau['id']:'').' с email '.$datau['email'].'. Пароль должен состоять из букв руского и английского алфавита, цифр и тире, и не менее 6ти символов.</div></div>
				<br/>
				<div class="cform" style="width:540px;"><form action="" method="post" name="newpass">
					<div>Введите пароль</div> <input type="password" onkeyup="checkPass(\'pass\')" maxlength="32" value="" name="pass" class="accept"/>
					<div>Повторите пароль</div><input type="password" onkeyup="checkPass(\'pass\')" maxlength="32" value="" name="re_pass" class="reject"/>
					<div></div><input class="submit" type="submit" name="enter" value="Отправить" disabled="disabled"/>
				</form>
				</div>';
			}
		}else {

			if(count($_POST) and $_POST['mail']!='') {

				$this->listfields = array("t1.*");
				$this->clause = "t1 where t1.email = '".$_POST['mail']."'";
				$this->_list();
				$datau=$this->data[0];
				if(count($this->data)==1 and $datau['active']==1){
					$form=0;
					$time=time();
					$hash =md5($datau['pass'].$time.$datau['email']).'h';
					$MAIL = new mail_class($this->SQL);
					$datamail['from']=$this->owner->config["mailrobot"];
					$datamail['mailTo']=$datau['email'];
					$datamail['subject']='Востановление пароля на '.strtoupper($_SERVER['HTTP_HOST']);
					$href = '?id='.$datau['id'].'&t='.$time.'&hash='.$hash;
					$datamail['text']=str_replace(
							array('%email%','%login%','%href%','%time%','%mailbottom%'),
							array($datau['email'],$datau['id'],$href,date('Y-m-d H:i:s',($time+3600*24*2)),$this->owner->config["mailbottom"]),
							$this->owner->config["mailremind"]);
					$MAIL->reply = 0;
					if($MAIL->Send($datamail)) {
						$html = '<div class="ok">На ваш E-mail отправленно письмо с секретной ссылкой на форму для установки нового пароля.<br/>
						Ссылка действительна в течении 2х суток с момента отправки данной формы.<br/></div>';
					}else {
						$html  = $_MESS['mailerr'];
					}
				}
				elseif(count($this->data)==1) {
					$html = '<div class="error">Ваш профиль отключен или не подтверждён. Обратитесь в <a href="/mail.html?feedback=1">службу поддержки сайта</a></div>';
				}
				else{
					$html = '<div class="error">Такой адрес на сайте не зарегистрирован.</div>';
				}
				$html = '<div class="messages">'.$html.'</div>';

			}

			if($form) {
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
	}
}


class weppages_class extends kernel_class {

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Страницы в WEP';
		return 0;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['blank'] = array('type' => 'bool', 'attr' => 'NOT NULL DEFAULT 0');

		# fields
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Name');
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'HREF', 'mask' =>array('name'=>'all'));
		$this->fields_form['blank'] = array('type' => 'checkbox', 'caption' => '_BLANK', 'mask' =>array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
	}
}


?>