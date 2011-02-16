<?

class ugroup_extend extends kernel_class
{

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config['mailto'] = 'info@xakki.ru';
		$this->config['mailrobot'] = 'robot@xakki.ru';
		$this->config['mailconfirm'] = '';
		$this->config['mailinfo'] = '';
		$this->config['mailremind'] = '';
		$this->config['reg'] = 1;
		$this->config['noreggroup'] = 4;
		$this->config['reggroup'] = 4;
		$this->config['rememberday'] = 20;
		$this->config['payon'] = 0;
		$this->config['premoderation'] = 0;
		$this->config['modergroup'] = 4;

		$this->config_form['mailto'] = array('type' => 'text', 'mask' =>array('min'=>1,'name'=>'email'), 'caption' => 'Адрес службы поддержки');
		$this->config_form['mailrobot'] = array('type' => 'text', 'mask' =>array('min'=>1,'name'=>'email'), 'caption' => 'Адрес Робота');
		$this->config_form['mailinfo'] = array(
			'type' => 'ckedit', 
			'caption' => 'Инфа о реге', 
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['mailconfirm'] = array(
			'type' => 'textarea', 
			'caption' => 'Текст письма для подтверждения', 
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['mailremind'] = array(
			'type' => 'textarea',
			'caption' => 'Текст письма востановления пароля');
		$this->config_form['reg'] = array('type' => 'checkbox', 'caption' => 'Включить регистрацию?');
		$this->config_form['payon'] = array('type' => 'checkbox', 'caption' => 'Включить платежную систему?');
		$this->config_form['premoderation'] = array('type' => 'checkbox', 'caption' => 'Использовать премодерацию?');
		$this->config_form['noreggroup'] = array('type' => 'list', 'listname'=>'list', 'caption' => 'Неподтвердившие регистрацию');
		$this->config_form['reggroup'] = array('type' => 'list', 'listname'=>'list', 'caption' => 'Регистрировать по умолчанию');
		$this->config_form['modergroup'] = array('type' => 'list', 'listname'=>'list', 'caption' => 'Непрошедшие проверку');
		$this->config_form['rememberday'] = array('type' => 'int', 'mask' =>array('min'=>1), 'caption' => 'Дней запоминания авторизации');
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->caption = 'Группы';
		$this->singleton = true;
		
		$this->ver = '0.2.1';
		return true;
	}

	function _create() {
		parent::_create();

		$this->_vf_list = 'concat(name,"[",level,"]")';// агрегатная ф мускл
		
		$this->_enum['level'] = array(
			0=>'Полный доступ (абсолютный)',
			1=>'Полный доступ (проверка привелегий)',
			2=>'Доступ к модулям',
			5=>'нет доступа');

		$this->fields['name'] = array('type' => 'varchar', 'width' =>128, 'attr' => 'NOT NULL');
		$this->fields['wep'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['level'] = array('type' => 'tinyint', 'width' =>2, 'attr' => 'NOT NULL',  'default' => 1);
		$this->fields['filesize'] = array('type' => 'int', 'width' =>5, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['design'] = array('type' => 'varchar', 'width' =>128, 'attr' => 'NOT NULL');
		
		$this->fields_form['name'] = array('type' => 'text', 'mask' =>array('min'=>1), 'caption' => 'Название группы');
		$this->fields_form['wep'] = array('type' => 'checkbox', 'caption' => 'Разрешить вход в админку?');
		$this->fields_form['level'] = array('type' => 'list', 'listname'=>'level', 'caption' => 'Доступ в CMS');
		$this->fields_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизаин личного кабинета');
		$this->fields_form['filesize'] = array('type' => 'int', 'caption' => 'Доступный размер диска', 'comment' => 'Значение в мегабайтах, 0 - запрет','mask'=>array('max'=>1000));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

		$this->def_records[] = array('name'=>'Администраторы','level'=>0,'filesize'=>'100','active'=>1,'id'=>1,'wep'=>1);
		$this->def_records[] = array('name'=>'Анонимы','level'=>5,'filesize'=>'0','active'=>1,'id'=>0,'wep'=>0);

		$this->create_child('users');
		if($this->_CFG['_F']['adminpage']) { // для админки , чтобы удобно можно было задавать права доступа
			include_once($this->_CFG['_PATH']['extcore'].'modulprm.class/modulprm.class.php');
			$this->create_child('modulgrp');
			$this->childs['modulgrp']->owner_name = 'ugroup_id';//теперь родитель в этом поле привязан
			unset($this->childs['modulgrp']->fields_form['ugroup_id']);//отклю список групп
			$this->childs['modulgrp']->fields_form['owner_id'] = array('type' => 'list', 'readonly' => 1, 'listname'=>array('tablename'=>$this->_CFG['sql']['dbpref'].'modulprm'), 'caption' => 'Модуль');//и включаем модули
			$this->childs['modulgrp']->fields['owner_id'] = array(); // чтобы  не ругался модчекструкт, тк это поле может задаваться по умолчанию от родителя
		}
	}

	/*function super_inc($param=array(),$ftype='') {
		return parent::
	}*/

	function _getlist($listname,$value=0) {
		$data = array();
		if ($listname == 'glist') {
			$result = $this->SQL->execSQL('SELECT id, name FROM '.$this->tablename);
			if(!$result->err)
				while ($row = $result->fetch_array())
					$data[$row['id']] = $row['name'];
			return $data;
		}
		elseif ($listname == 'mdesign') {
			$dir = dir($this->_CFG['_PATH']['cdesign']);
			while ($entry = $dir->read()) {
				if ($entry!= '.' and $entry!= '..') {
					$data[$entry] = $entry;
				}
			}
			$dir->close();
			return $data;
		}
		else return parent::_getlist($listname,$value);
	}

	/*function _UpdItemModul($param) {
		$ret = parent::_UpdItemModul($param);
		//if($ret[1] and $_SESSION['user'] and $_SESSION['user']['owner_id']==$this->id)
		//	session_unset();
		return $ret;
	}*/
}


class users_extend extends kernel_class {

	function _set_features()
	{
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->fn_login = 'id';
		$this->fn_pass = 'pass';
		if(!$this->fn_login or $this->fn_login=='id')
			$this->mf_use_charid = true;
		else
			$this->mf_use_charid = false;
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->caption = 'Пользователи';
		return true;
	}

	function _create()
	{
		parent::_create();
		$this->unique_fields['email']='email';

		if($this->fn_login!='email')
			$this->fields[$this->fn_login] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		$this->fields['name'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields['fullname'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL', 'default'=>'');
		$this->fields[$this->fn_pass] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		$this->fields['address'] = array('type' => 'varchar', 'width' => 127,'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['dob'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['gender'] = array('type' => 'tinyint', 'width' => 2, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['phone'] = array('type' => 'varchar', 'width' => 127,'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['email'] =  array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['www'] =  array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['loginza_token'] =  array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['loginza_provider'] =  array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['loginza_data'] =  array('type' => 'text', 'attr' => 'NOT NULL');
		// service field
		$this->fields['reg_hash'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['balance'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);


		// FORM FIELDS
		if($this->mf_use_charid){
			$this->fields_form[$this->fn_login] =	array('type' => 'text', 'caption' => 'Логин','mask'=>array('name'=>'login','min' => '4','sort'=>1),'comment'=>'Логин должен состоять только из латинских букв и цифр.');
			if(_prmUserCheck(1))  // Запрет поля на редактирование
				$this->fields_form[$this->fn_login]['readonly']=true;
		}
		
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Имя','mask'=>array('name'=>'name','usercheck'=>1)); // Вывод поля при редактировании

		//$this->fields_form['sname'] = array('type' => 'text', 'caption' => 'Фамилия','mask'=>array('name'=>'name'));
		//$this->fields_form['tname'] = array('type' => 'text', 'caption' => 'Отчество','mask'=>array('name'=>'name'));
		//$this->fields_form['address'] = array('type' => 'text', 'caption' => 'Адрес');

		if(_prmUserCheck(1)) // Вывод поля генерации пароля если админ
			$this->fields_form[$this->fn_pass] = array('type' => 'password2', 'caption' => 'Пароль','md5'=>$this->_CFG['wep']['md5'], 'mask'=>array('min' => '6','fview'=>1));
		elseif(!_prmUserCheck())
			$this->fields_form[$this->fn_pass] = array('type' => 'password_new', 'caption' => 'Пароль','mask'=>array('min' => '6','fview'=>1));

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

		$this->fields_form['mf_ipcreate'] =	array(
			'type' => 'text',
			'readonly' => 1, 
			'caption' => 'IP-пользователя',
			'mask'=>array('usercheck'=>1));
		$this->fields_form['mf_timecr'] =	array('type' => 'date','readonly' => 1, 'caption' => 'Дата регистрации','mask'=>array('sort'=>1));
		$this->fields_form['reg_hash'] = array('type' => 'hidden',  'caption' => 'Хэш','mask'=>array('eval'=>1,'fview'=>1,'usercheck'=>1));
		/*$this->fields_form['cntdec'] = array(
			'type' => 'list', 
			'listname'=>array('class'=>'board','field'=>'count(tx.id) as cntdec', 'leftjoin'=>'t1.id=tx.creater_id'), 
			'readonly'=>1,
			'caption' => 'Объявл.',
			'mask' =>array('usercheck'=>1,'sort'=>''));*/
		if($this->config['payon'])
			$this->fields_form['balance'] =	array(
				'type' => 'text',
				'readonly' => 1, 
				'caption' => 'Счет(руб)',
				'mask'=>array('sort'=>1));
		$this->fields_form['owner_id'] = array('type' => 'list', 'listname'=>'ownerlist', 'caption' => 'Группа', 'mask' =>array('usercheck'=>1,'fview'=>1));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Пользователь активен', 'mask' =>array('usercheck'=>1));

		$this->def_records[] = array(
			$this->fn_login => $this->_CFG['wep']['login'],
			'name'=>'Главный',
			$this->fn_pass => md5($this->_CFG['wep']['md5'].$this->_CFG['wep']['password']), 
			'active'=>1,
			'mf_timecr'=>time(),
			'owner_id'=>1,
			'reg_hash'=>1);

		$this->ordfield = 'mf_timecr DESC';

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
		if($login!='' && $pass!='')
		{
			if ($this->mf_use_charid and !preg_match('/^[0-9A-Za-z]+$/', $login))
				 return array('Поле `Логин` введено не корректно. Допустим ввод только латинских букв и цифр.',0);
			elseif (!$this->mf_use_charid and !preg_match('/^[0-9A-Za-z\.\-\@]+$/', $login))
				 return array('Поле `Email` введено не корректно. Допустим ввод только латинских букв,цифр, точки, тире и @',0);
			else
			{
				$this->listfields = array('t2.*,t2.active as gact,t2.name as gname,t1.*');
				$this->clause = 't1 Join '.$this->owner->tablename.' t2 on t1.'.$this->owner_name.'=t2.id where t1.'.$this->fn_login.' = \''.$login.'\' and t1.'.$this->fn_pass.' =\''.md5($this->_CFG['wep']['md5'].$pass).'\'';
				if(!$this->_list()) {
					header('Location: '.$this->_CFG['_HREF']['BH'].$this->_CFG['PATH']['wepname'].'/login.php?install');die();}
				if(count($this->data))
				{
					unset($_SESSION['user']);
					if(_strlen($this->data[0]['reg_hash'])>5)
						return array($this->_CFG['_MESS']['authnoconf'],0);
					elseif($this->data[0]['reg_hash']=='0' && !$this->data[0]['activ'])
						return array($this->_CFG['_MESS']['auth_notcheck'],0);
					elseif(!$this->data[0]['gact'])
						return array($_CFG['_MESS']['auth_bangroup'],0);
					elseif(!$this->data[0]['active'])
						return array($this->_CFG['_MESS']['auth_banuser'],0);
					elseif($this->data[0]['level']>=5)
						return array($this->_CFG['_MESS']['denied'],0);
					else
					{
						if($_POST['remember']=='1'){
							_setcookie('remember', md5($this->data[0][$this->fn_pass]).'_'.$this->data[0]['id'], (time()+(86400*$this->owner->config['rememberday'])));
						}
						$this->setUserSession();
						_prmModulLoad();
						return array($this->_CFG['_MESS']['authok'],1);
					}
				}
				else
					return array($this->_CFG['_MESS']['autherr'],0);
			}
		}
		else 
			return array('Поля `Логин` и `Пароль` - обязательные! Необходимо ввести свой `Логин` и `Пароль` чтобы авторизоваться.',0);
	}

	
	function cookieAuthorization() {
		if(!isset($_SESSION['user']) and isset($_COOKIE['remember']))
		{
			if (preg_match("/^[0-9A-Za-z\_]+$/",$_COOKIE['remember']))
			{
				$pos = strpos($_COOKIE['remember'],'_');
				$this->listfields = array('t2.*,t2.active as gact,t2.name as gname,t1.*');
				$this->clause = 't1 Join '.$this->owner->tablename.' t2 on t1.'.$this->owner_name.'=t2.id where t1.id = \''.substr($_COOKIE['remember'],($pos+1)).'\' and md5(t1.'.$this->fn_pass.') =\''.substr($_COOKIE['remember'],0,$pos).'\'';
				$this->_list();
				if(count($this->data))
				{
					unset($_SESSION['user']);
					if(!$this->data[0]['active'])
						return array('Ваш аккаунт заблокирован. За дополнительной информацией обращайтесь к Администратору сайта.',0);
					elseif(!$this->data[0]['gact'])
						return array('Ваша группа заблокирована. За дополнительной информацией обращайтесь к Администратору сайта.',0);
					elseif(_strlen($this->data[0]['reg_hash'])>5)
						return array('Вы не подтвердили регистрацию.',0);
					elseif($this->data[0]['level']>=5)
						return array('Доступ закрыт.',0);
					else
					{
						_setcookie('remember', md5($this->data[0][$this->fn_pass]).'_'.$this->data[0]['id'], (time()+(86400*$this->owner->config['rememberday'])));
						$this->setUserSession();
						_prmModulLoad();
						return array($this->_CFG['_MESS']['authok'],1);
					}
				}
			}
		}

		return array('',0);
	}

	function setUserSession($data='') {
		if($data==='') $data = current($this->data);
		$_SESSION['user'] = $data;
		$_SESSION['user']['owner_id'] = $data[$this->owner_name];
		$_SESSION['FckEditorUserFilesUrl'] = $this->_CFG['_HREF']['BH'].$this->_CFG['PATH']['userfile'].$_SESSION['user']['id'].'/';
		$_SESSION['FckEditorUserFilesPath'] = $this->_CFG['_PATH']['path'].$this->_CFG['PATH']['userfile'].$_SESSION['user']['id'].'/';
		if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0)
			_setcookie('_showerror',1);
		return 0;
	}

	function regForm(){
		global $_MESS,$_tpl;
		// добавлены настройки на форму регистрации
		$this->fields_form[$this->fn_login]['readonly']=false;
		//$this->fields_form['info'] = array('type' => 'checkbox', 'caption' => 'Пользователь активен');
		unset($this->fields_form['sett1']);
		if(!$this->owner->config['reg']) return array(array('messages'=>array(array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedreg']))),1);
		if(_prmUserCheck()) return array(array('messages'=>array(array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedu']))),1);
		$flag=0;// 0 - показывает форму, 1 - нет
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();
		$this->fields_form['_info']= array('type'=>'info','caption'=>'Регистрация пользователя','css'=>'caption');
		if(count($_POST) and $_POST['sbmt']) {
			if($this->fn_pass!='pass') $_POST[$this->fn_pass] = $_POST['pass'];
			if($this->fn_pass!='login') $_POST[$this->fn_login] = $_POST['login'];
			$this->kPreFields($_POST,$param);
			$arr = $this->fFormCheck($_POST,$param,$this->fields_form);
			if(!count($arr['mess'])){

				$this->listfields = array('LOWER(t1.'.$this->fn_login.') as lgn');
				$this->clause = 't1 where t1.'.$this->fn_login.' = \''.$arr['vars'][$this->fn_login].'\' or t1.email = \''.$arr['vars']['email'].'\'';
				$this->_list('lgn');
				if($this->mf_use_charid and isset($this->data[strtolower($arr['vars'][$this->fn_login])])) 
					$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['notlogin']);
				elseif(count($this->data)) 
					$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['notemail']);
				else {
					$arr['vars']['owner_id']=$this->owner->config['noreggroup'];
					$arr['vars']['active']=0;
					if(!$arr['vars']['name'])
						$arr['vars']['name'] = $arr['vars'][$this->fn_login];
					$arr['vars'][$this->mf_createrid]=$arr['vars']['id'];
					$arr['vars']['reg_hash']=md5(time().$arr['vars']['id']);
					$pass=$arr['vars'][$this->fn_pass];
					$arr['vars'][$this->fn_pass]=md5($this->_CFG['wep']['md5'].$arr['vars'][$this->fn_pass]);
					//$_SESSION['user']['id'] = $arr['vars']['id'];
					if($this->_add_item($arr['vars'])) {
						$MAIL = new mail_class($this->SQL);
						$datamail['from']=$this->owner->config['mailrobot'];
						$datamail['mailTo']=$arr['vars']['email'];
						$datamail['subject']='Подтвердите регистрацию на '.strtoupper($_SERVER['HTTP_HOST']);
						$href = '?confirm='.$arr['vars']['id'].'&hash='.$arr['vars']['reg_hash'];
						
						$datamail['text']=str_replace(array('%pass%','%login%','%href%','%host%'),array($pass,$arr['vars'][$this->fn_login],$href,$_SERVER['HTTP_HOST']),$this->owner->config['mailconfirm']);
						$MAIL->reply = 0;
						if($MAIL->Send($datamail)) {
							$flag=1;
							$arr['mess'][] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['regok']);
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
		$this->form['sbmt']['value']='Я согласен с правилами и хочу зарегистрироваться';
		$this->form['rulesinfo'] = array('type'=>'info','caption'=>'<a href="'.$this->_CFG['_HREF']['BH'].'terms.html" target="_blank">Правила пользования сайтом</a>','css'=>'rulelink');

		return Array(Array('messages'=>($mess+$arr['mess']), 'form'=>(!$flag?$this->form:array()), 'class'=>'regform'), $flag);
	}

	function regConfirm() {
		global $_MESS;
		
		$_GET['hash'] = preg_replace("/[^0-9a-f]+/",'',$_GET['hash']);
		if(!$this->owner->config['reg'])
			$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedreg']);
		elseif(!isset($_GET['confirm']) or !isset($_GET['hash']) or _strlen($_GET['hash'])!=32)
			$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['errdata']);
		else {
			$this->listfields = array('t1.id,t1.reg_hash');
			$this->clause = 't1 where t1.id = \''.preg_replace("/[^0-9a-z]+/",'',$_GET['confirm']).'\'';
			$this->_list();
			if(count($this->data) and _strlen($this->data[0]['reg_hash'])<5)
				$mess[] = array('name'=>'alert', 'value'=>$this->_CFG['_MESS']['confno']);
			elseif(count($this->data) and $this->data[0]['reg_hash']==$_GET['hash']){
				$this->id = $this->data[0]['id'];
				$this->fld_data['reg_hash']= 1;
				if($this->owner->config['premoderation']) {
					$this->fld_data['active']= 0;
					$this->fld_data['owner_id']= $this->owner->config['modergroup'];
				}
				else {
					$this->fld_data['active']= 1;
					$this->fld_data['owner_id']= $this->owner->config['reggroup'];
				}
				
				if($this->_update())
					$mess[] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['confok']);
				else
					$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['conferr']);
			}
			else
				$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['errdata']);
		}

		return Array(array('messages'=>$mess),$flag);
	}
	function loginzaReg() {
		$mess = $dt = array();
		$flag = false;
		$authdata = file_get_contents('http://loginza.ru/api/authinfo?token='.$_POST['token']);
		$dt['loginza_data'] = $authdata;
		$authdata = json_decode($authdata,TRUE);
		if($authdata['error_type']) {
			$mess[] = array('name'=>'error', 'value'=>$authdata['error_type'].':'.$authdata['error_message']); 
		} else {
			$dt['loginza_token'] = $_POST['token'];
			$dt['loginza_provider'] = $authdata['provider'];
			$dt[$this->fn_login] = md5($authdata['identity']);
			if($authdata['provider']=='http://openid.yandex.ru/server/') {
				$dt['name'] = substr(substr($authdata['identity'],24),0,-1);
				$dt['email'] = substr(substr($authdata['identity'],24),0,-1).'@ya.ru';
			}
			elseif($authdata['provider']=='http://mail.ru/') {
				$dt['name'] = $authdata['name']['first_name'];
				$temp = substr(substr($authdata['identity'],18),0,-1);
				$temp = explode('/',$temp);
				$dt['email'] = $temp[1].'@'.$temp[0].'.ru';
			}
			/*elseif($authdata['provider']=='http://vkontakte.ru/') {
				$dt['name'] = $authdata['name']['last_name'].' '.$authdata['name']['first_name'];
				$dt['email'] = $authdata['email'];
			}*/
			elseif(isset($authdata['email'])) {
				$dt['email'] = $authdata['email'];
				if(is_array($authdata['name'])) {
					if($authdata['name']['full_name'])
						$dt['name'] = $authdata['name']['full_name'];
					elseif($authdata['name']['first_name'])
						$dt['name'] = $authdata['name']['first_name'];
				}else 
					$dt['name'] = $authdata['name'];
			}
		}

//print_r('<pre>');print_r($authdata);print_r($dt);
		if(!count($mess)) {
			if(!$dt['email']) {
				$mess[] = array('name'=>'error', 'value'=>'Данный провайдер не сообщил ваш Email, который необходим для авторизации на нашем сайте. Возможно в настройках провайдера вашего аккаунта есть опция позволяющая передавать Email. В любом случае вы можете воспользоваться стандартной регистрацие в нашем сайте , это не займет много времени.');
			} else {
				session_go(1);
				if($data = $this->_query('t2.*,t2.active as gact,t2.name as gname,t1.*','t1 Join '.$this->owner->tablename.' t2 on t1.'.$this->owner_name.'=t2.id where t1.email=\''.mysql_real_escape_string($dt['email']).'\'') and count($data)) {
					$data = current($data);
					if($data['reg_hash']==2)
						$flag = true;
					else
						$mess[] = array('name'=>'error', 'value'=>'Поскольку Вы('.$dt['email'].') зарегистрированы на нашем сайте стандартным способом, то для авторизайции также следуют воспользоваться стандартной формой авторизации.');
				} else {
					$data =  $dt;
					if(!$data['name']) $data['name'] = $dt['email'];
					$data['owner_id']=$this->owner->config['reggroup'];
					$data['active']=1;
					$data['reg_hash'] = 2; // отметка  о том что регестрируются через LOGINZA
					$pass = substr($data[$this->fn_login],10);
					$data[$this->fn_pass]=md5($this->_CFG['wep']['md5'].$pass);
					//$_SESSION['user']['id'] = $data[$this->fn_login];
					$this->fld_data = $data;
					$this->fld_data['loginza_data'] = '';
					if($this->_add($data)) {
						$data = current($this->_query('t2.*,t2.active as gact,t2.name as gname,t1.*','t1 Join '.$this->owner->tablename.' t2 on t1.'.$this->owner_name.'=t2.id where t1.email=\''.mysql_real_escape_string($dt['email']).'\''));
						$flag = true;
						/*
						global $MAIL;
						if(!$MAIL) _new_class('mail',$MAIL);
						$datamail['from']=$this->owner->config['mailrobot'];
						$datamail['mailTo']=$data['email'];
						$datamail['subject']='Вы зарегестрированы на сайте '.strtoupper($_SERVER['HTTP_HOST']);
						
						$datamail['text']=str_replace(array('%pass%','%login%','%host%'),array($pass,$data['id'],$_SERVER['HTTP_HOST']),$this->owner->config['mailinfo']);
						$MAIL->reply = 0;
						if(!$MAIL->Send($datamail)) {
							$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['mailerr']);
						}*/
					} else
						$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['regerr']);
				}
				if($flag) {
					$mess[] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['authok']);
					$mess[] = array('name'=>'ok', 'value'=>'<a href="/add.html">Перейти на страницу добавления объявления</a>');
					$this->setUserSession($data);
//print_r($data);
					_prmModulLoad();
				}
			}
		} 
		global $HTML;
		$mess = array('messages'=>$mess);
		return $HTML->transformPHP($mess,'messages');
	}

	function remind() {
		global $HTML;
		$form=1;$html='';
		if(count($_GET) and $_GET['id']!='' and $_GET['t']!='' and $_GET['hash']!='') {
			$this->listfields = array('t1.*');
			$this->clause = 't1 where t1.id = \''.$_GET['id'].'\'';
			$this->_list();
			$datau=$this->data[0];
			if(count($this->data)==1 and $datau['active']==1 and $_GET['hash']==(md5($datau[$this->fn_pass].$_GET['t'].$datau['email']).'h')) {
				
				$form=0;
				if(count($_POST)) {
					if($_POST['pass']==$_POST['re_pass']){
						if(preg_match($this->_CFG['_MASK']['name'],$_POST['pass']) and _strlen($_POST['pass'])>=6) {
							$this->SQL->execSQL('UPDATE '.$this->tablename.' SET '.$this->fn_pass.'="'.md5($this->_CFG['wep']['md5'].$_POST['pass']).'" where email="'.$datau['email'].'"');
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
			if($html) $html = '<div class="messages">'.$html.'</div>';
			if(!$form) {
				$html .= '<div class="messages"><div class="ok">Введите новый пароль для пользователя с email-ом '.$datau['email'].'. Пароль должен состоять из букв руского и английского алфавита, цифр и тире, и не менее 6ти символов.</div></div>
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

				$this->listfields = array('t1.*');
				$this->clause = 't1 where t1.email = \''.$_POST['mail'].'\'';
				$this->_list();
				$datau=$this->data[0];
				if(count($this->data)==1 and $datau['active']==1) {
					$form=0;
					$time=time();
					$hash =md5($datau[$this->fn_pass].$time.$datau['email']).'h';
					$MAIL = new mail_class($this->SQL);
					$datamail['from']=$this->owner->config['mailrobot'];
					$datamail['mailTo']=$datau['email'];
					$datamail['subject']='Востановление пароля на '.strtoupper($_SERVER['HTTP_HOST']);
					$href = '?id='.$datau['id'].'&t='.$time.'&hash='.$hash;
					$datamail['text']=str_replace(
							array('%email%','%login%','%href%','%time%','%host%'),
							array($datau['email'],$datau[$this->fn_login],$href,date('Y-m-d H:i:s',($time+3600*24*2)),$_SERVER['HTTP_HOST']),
							$this->owner->config['mailremind']);
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
?>
