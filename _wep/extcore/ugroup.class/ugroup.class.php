<?php

class ugroup_class extends kernel_extends
{

	protected function _set_features() {
		parent::_set_features();

		$this->mf_actctrl = true;
		$this->mf_istree = true;
		$this->mf_treelevel = 1;
		$this->caption = 'Группы';
		$this->singleton = true;
		$this->ver = '0.2.1';
		$this->default_access = '|0|';
		return true;
	}

	function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config['mail_to'] = 'info@xakki.ru';
		$this->config['mailrobot'] = 'robot@xakki.ru';
		$this->config['mailinfo'] = '';
		$this->config['mailconfirm'] = '';
		$this->config['mailremind'] = '';
		$this->config['reg'] = 1;
		$this->config['noreggroup'] = 4;
		$this->config['reggroup'] = 4;
		$this->config['rememberday'] = 20;
		$this->config['payon'] = '';
		$this->config['premoderation'] = 0;
		$this->config['modergroup'] = 4;
		$this->config['karma'] = 0;
		$this->config['userpic'] = '';
		$this->config['invite'] = 0;

		$this->config_form['mail_to'] = array('type' => 'text', 'mask' =>array('min'=>1,'name'=>'email'), 'caption' => 'Адрес службы поддержки');
		$this->config_form['mailrobot'] = array('type' => 'text', 'mask' =>array('min'=>1,'name'=>'email'), 'caption' => 'Адрес Робота');
		$this->config_form['mailinfo'] = array(
			'type' => 'ckedit', 
			'caption' => 'Инфа о реге', 
			'paramedit'=>array(
				'CKFinder'=>1,
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['mailconfirm'] = array(
			'type' => 'ckedit', 
			'caption' => 'Текст письма для подтверждения', 
			'paramedit'=>array(
				'CKFinder'=>1,
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['mailremind'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст письма востановления пароля', 
			'paramedit'=>array(
				'CKFinder'=>1,
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['reg'] = array('type' => 'checkbox', 'caption' => 'Включить регистрацию?');
		$this->config_form['payon'] = array('type' => 'text', 'caption' => 'Включить платежную систему? Введите название денежной единицы. [руб,евро итп]');
		$this->config_form['premoderation'] = array('type' => 'checkbox', 'caption' => 'Использовать премодерацию?');
		$this->config_form['noreggroup'] = array('type' => 'list', 'listname'=>'list', 'caption' => 'Неподтвердившие регистрацию');
		$this->config_form['reggroup'] = array('type' => 'list', 'listname'=>'list', 'caption' => 'Регистрировать по умолчанию');
		$this->config_form['modergroup'] = array('type' => 'list', 'listname'=>'list', 'caption' => 'Ожидающие проверку модератором');
		$this->config_form['rememberday'] = array('type' => 'int', 'mask' =>array('min'=>1), 'caption' => 'Дней запоминания авторизации');
		$this->config_form['karma'] = array('type' => 'checkbox', 'caption' => 'Включить систему рейтингов?','style'=>'background:green;');
		$this->config_form['userpic'] = array('type' => 'text', 'mask' =>array(), 'caption' => 'Дефолтная фотка пользователя');
		$this->config_form['invite'] = array('type' => 'checkbox', 'caption' => 'Включить систему инвайтов?','style'=>'background:gray;');

	}

	function _create() {
		parent::_create();

		if($this->config['payon']) {// Включается зависимость модуля
			$this->_dependClass = array('pay');
		}

		$this->_vf_list = 'concat(name,"[",level,"]")';// агрегатная ф мускл
		
		$this->_enum['level'] = array(
			0=>'Администраторы',
			1=>'Модераторы',
			2=>'Пользователи',
			5=>'Гости');

		$this->fields[$this->mf_namefields] = array('type' => 'varchar', 'width' =>128, 'attr' => 'NOT NULL');
		$this->fields['wep'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['level'] = array('type' => 'tinyint', 'width' =>2, 'attr' => 'NOT NULL',  'default' => 1);
		$this->fields['negative'] = array('type' => 'tinyint', 'width' =>2, 'attr' => 'NOT NULL',  'default' => 0);
		$this->fields['filesize'] = array('type' => 'int', 'width' =>5, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['design'] = array('type' => 'varchar', 'width' =>128, 'attr' => 'NOT NULL', 'default' => '');

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['id'] = array('type' => 'text', 'mask' =>array(), 'caption' => 'ID');
		$this->fields_form[$this->mf_namefields] = array('type' => 'text', 'mask' =>array('min'=>1), 'caption' => 'Название группы');
		$this->fields_form['wep'] = array('type' => 'checkbox', 'caption'=>'Админка', 'comment' => 'Разрешить вход в админку?');
		$this->fields_form['level'] = array('type' => 'list', 'listname'=>'level', 'caption' => 'Доступ в CMS', 'default'=>2);
		$this->fields_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption'=>'Дизайн', 'comment' => 'Дизаин личного кабинета', 'default'=>'default');
		$this->fields_form['filesize'] = array('type' => 'int', 'caption' => 'Share', 'comment' => 'Доступный размер диска. Значение в мегабайтах, 0 - запрет','mask'=>array('max'=>1000));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

		if($this->config['karma']) {
			$this->fields['minkarma'] = array('type' => 'int', 'width' =>8, 'attr' => 'NOT NULL', 'default' => 0);
			$this->fields['maxkarma'] = array('type' => 'int', 'width' =>8, 'attr' => 'NOT NULL', 'default' => 0);
			$this->fields['defkratio'] = array('type' => 'float', 'width' => '8,2','attr' => 'NOT NULL', 'default'=>'0.00');

			$this->fields_form['minkarma'] = array('type' => 'text', 'caption' => 'Мин. карма', 'mask' =>array());
			$this->fields_form['maxkarma'] = array('type' => 'text', 'caption' => 'Макс. карма', 'mask' =>array('fview'=>1));
			$this->fields_form['defkratio'] = array('type' => 'text', 'caption' => 'Коэфициент по умол.', 'mask' =>array('fview'=>1));
		}
		if($this->config['payon']) {
			$this->fields_form['negative'] = array('type' => 'checkbox', 'caption' => 'Разрешить отрицательный баланс?');
		}
	}

	function setSystemFields() {
		$this->def_records[1] = array('id'=>1,$this->mf_namefields=>'Администраторы','level'=>0,'filesize'=>'100','active'=>1,'design'=>'default','wep'=>1);
		$this->def_records[2] = array('id'=>2,$this->mf_namefields=>'Пользователи','level'=>2,'filesize'=>'0','active'=>1,'design'=>'default','wep'=>0);
		$this->def_records[3] = array('id'=>3,$this->mf_namefields=>'Гости','level'=>5,'filesize'=>'0','active'=>1,'design'=>'default','wep'=>0);
		return parent::setSystemFields();
	}

	function _childs() {
		$this->create_child('users');
		if($this->_CFG['_F']['adminpage']) { // для админки , чтобы удобно можно было задавать права доступа
			/*$this->create_child('modulgrp');
			$this->childs['modulgrp']->owner_name = 'ugroup_id';//теперь родитель в этом поле привязан
			unset($this->childs['modulgrp']->fields_form['ugroup_id']);//отклю список групп
			$this->childs['modulgrp']->fields_form['owner_id'] = array('type' => 'list', 'readonly' => 1, 'listname'=>array('tablename'=>$this->_CFG['sql']['dbpref'].'modulprm'), 'caption' => 'Модуль');//и включаем модули
			$this->childs['modulgrp']->fields['owner_id'] = array(); // чтобы  не ругался модчекструкт, тк это поле может задаваться по умолчанию от родителя
			*/
		}
	}

	function _getlist(&$listname,$value=0) {
		$data = array();
		if ($listname == 'glist') {
			$result = $this->SQL->execSQL('SELECT id, '.$this->mf_namefields.' FROM '.$this->tablename);
			if(!$result->err)
				while ($row = $result->fetch_array())
					$data[$row['id']] = $row[$this->mf_namefields];
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

	function authorization($login,$pass) {
		return $this->childs['users']->authorization($login,$pass);
	}
	function cookieAuthorization() {
		return $this->childs['users']->cookieAuthorization();
	}
	function regForm($param=array()) {
		return $this->childs['users']->regForm($param);
	}
	function regConfirm() {
		return $this->childs['users']->regConfirm();
	}

	public function getUserData($id) {
		if(!$id) {
			trigger_error('Error get user data', E_USER_WARNING);
			return array();
		}
		$listfields = array('t1.*,t1.id as gid,t1.active as gact,t1.name as gname,t2.*');
		$clause = 't1 Join '.$this->childs['users']->tablename.' t2 on t2.'.$this->childs['users']->owner_name.'=t1.id where t2.id = '.(int)$id.''; 
		$this->data = $this->_query($listfields,$clause);
		if(!count($this->data)) {
			trigger_error('Not found data for user id='.$id, E_USER_WARNING);
			return array();
		}
		$this->data[0]['FckEditorUserFilesUrl'] = $this->_CFG['_HREF']['BH'].$this->_CFG['PATH']['userfile'].$id.'/';
		$this->data[0]['FckEditorUserFilesPath'] = $this->_CFG['_PATH']['path'].$this->_CFG['PATH']['userfile'].$id.'/';
		return $this->data[0];
	}

	function mailNotif() {
		$mess = '';
		if($this->config['mail_to'] and $this->config['premoderation']) {
			$data = $this->childs['users']->_query('id,name,email','WHERE active=-1 and owner_id='.$this->config['modergroup']);
			if(count($data)) {
				$txt = '<table border="1"><tr><td>ID</td><td>Name</td><td>email</td></tr>';
				foreach($data as $k=>$r) {
					$txt .= '<tr>
						<td><a href="http://'.$_SERVER['HTTP_HOST'].'/'.$this->_CFG['PATH']['wepname'].'/index.php?_view=list&_modul=ugroupom&ugroupom_id='.$this->config['modergroup'].'&ugroupom_ch=usersom&usersom_id='.$r['id'].'&_type=edit">'.$r['id'].'</a></td>
						<td>'.$r['name'].'</td>
						<td>'.$r['email'].'</td></tr>';
				}
				$txt .= '</table>';
				_new_class('mail',$MAIL);
				$datamail = array(
					'creater_id' => -1,
					'mail_to' => $this->config['mail_to'],
					'subject' => strtoupper($_SERVER['HTTP_HOST']).' Оповещение: Ожидают проверки '.count($data).' зарегистрированных пользователя',
					'text' => '<p>Список пользователей ожидающие одобрения.</p>'.$txt,

				);
				$MAIL->reply = 0;
				if($MAIL->Send($datamail)) {
					$mess = 'Оповещение: '.count($data).' пользователей ожидают одобрения.';
				} else {
					trigger_error('Оповещение - '.$this->_CFG['_MESS']['mailerr'], E_USER_WARNING);
				}
			}
		}
		return $mess;
	}
	
}


class users_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->fn_login = 'email';//login or email
		$this->fn_pass = 'pass';
		$this->mf_use_charid = false;
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->caption = 'Пользователи';
		$this->locallang['default']['title_regme'] = 'Регистрация пользователя';
		$this->locallang['default']['title_profile'] = 'Редактирование профиля';
		$this->locallang['default']['_saveclose'] = 'Готово';
		$this->default_access = '|0|';
		$this->userCach = array();
		return true;
	}

	function _create()
	{
		parent::_create();
		$this->unique_fields['email']='email';
		$this->ordfield = 'mf_timecr DESC';

		if($this->fn_login!='email')
			$this->fields[$this->fn_login] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		$this->fields['email'] =  array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields[$this->mf_namefields] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields[$this->fn_pass] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		// service field
		$this->fields['reg_hash'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['balance'] = array('type' => 'float', 'width' => '11,4', 'attr' => 'NOT NULL', 'default'=>'0.0000');
		$this->fields['lastvisit'] =  array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['karma'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['kratio'] = array('type' => 'float', 'width' => '8,2','attr' => 'NOT NULL', 'default'=>'0.00');

		$this->attaches['userpic'] = array('mime' => array('image/pjpeg'=>'jpg', 'image/jpeg'=>'jpg', 'image/gif'=>'gif', 'image/png'=>'png'), 'thumb'=>array(array('type'=>'resize', 'w'=>'800', 'h'=>'600','pref'=>'orign_'),array('type'=>'resizecrop', 'w'=>85, 'h'=>85)),'maxsize'=>1000,'path'=>'');
		if(static_main::_prmUserCheck() and !is_null($this->_CFG['modulprm_ext'])) {
			$params = array(
				'obj'=>&$this,
				'func' => 'updateLastVisit',
			);
			observer::register_observer($params, 'shutdown_function');
		}
	}

	function _childs() {
		parent::_childs();
		if($this->owner->config['invite'])
			$this->create_child('invite');
	}

	function setSystemFields() {
		$this->def_records[1] = array(
			$this->fn_login => $this->_CFG['wep']['login'],
			$this->mf_namefields=>'Администратор',
			$this->fn_pass => md5($this->_CFG['wep']['md5'].$this->_CFG['wep']['password']), 
			'active'=>1,
			'mf_timecr'=>time(),
			'owner_id'=>1,
			'reg_hash'=>1);
		return parent::setSystemFields();
	}

	function updateLastVisit() {
		if(isset($_SESSION['user']['id']) and (time()-$_SESSION['user']['lastvisit'])>300) {
			$this->SQL->execSQL('UPDATE `'.$this->tablename.'` SET lastvisit='.time().' WHERE id='.(int)$_SESSION['user']['id'].'');
		}
	}

	// FORM FIELDS
	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form = array();
		$this->fields_form['owner_id'] = array('type' => 'list', 'listname'=>'ownerlist', 'caption' => 'Группа', 'mask' =>array('usercheck'=>1,'fview'=>1));
		$this->fields_form[$this->fn_login] =	array('type' => 'text', 'caption' => 'Логин','mask'=>array('name'=>'login','min' => '4','sort'=>1),'comment'=>'Логин должен состоять только из латинских букв и цифр.');

		$this->fields_form[$this->fn_pass] = array('type' => 'password_new', 'caption' => 'Пароль','mask'=>array('min' => '6','fview'=>1));
		if($this->id) {
			if(static_main::_prmUserCheck(1)) // Вывод поля генерации пароля если админ
				$this->fields_form[$this->fn_pass] = array('type' => 'password2', 'caption' => 'Пароль','md5'=>$this->_CFG['wep']['md5'], 'mask'=>array('min' => '6','fview'=>1));
			elseif(isset($_POST[$this->fn_pass]) and !$_POST[$this->fn_pass])
				unset($this->fields_form[$this->fn_pass]);unset($_POST[$this->fn_pass]);
			$this->fields_form[$this->fn_login]['readonly']=true;
		}else {
			//$this->fields_form[$this->fn_login]['readonly']=false;
			//$this->fields_form[$this->fn_pass] = array('type' => 'password_new', 'caption' => 'Пароль','mask'=>array('min' => '6','fview'=>1));
		}

		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'E-mail', 'mask'=>array('name'=>'email','min' => '7'));
		$this->fields_form[$this->mf_namefields] = array('type' => 'text', 'caption' => 'Имя','mask'=>array('name'=>'name2')); // Вывод поля при редактировании
		if($this->owner->config['karma']) {
			$this->fields_form['karma'] = array('type' => 'text', 'caption' => 'Карма', 'readonly'=>true,'mask'=>array('usercheck'=>1));
			$this->fields_form['kratio'] = array('type' => 'text', 'caption' => 'Коэф. значимости','readonly'=>true,'mask'=>array('usercheck'=>1));
			if(static_main::_prmUserCheck(1)) {
				unset($this->fields_form['karma']['readonly']);
				unset($this->fields_form['kratio']['readonly']);
			}
		}
		$this->fields_form['userpic'] = array('type'=>'file','caption'=>'Юзерпик','del'=>1, 'mask'=>array('fview'=>1,'width'=>85,'height'=>85,'thumb'=>0), 'default'=>$this->owner->config['userpic']);
		$this->fields_form['mf_ipcreate'] =	array('type' => 'text','readonly' => true, 'caption' => 'IP-пользователя','mask'=>array('usercheck'=>1));
		$this->fields_form['mf_timecr'] =	array('type' => 'date','readonly' => true, 'caption' => 'Дата регистрации','mask'=>array('sort'=>1));
		$this->fields_form['reg_hash'] = array('type' => 'hidden',  'caption' => 'Хэш','mask'=>array('eval'=>1,'fview'=>1,'usercheck'=>1));
		if(isset($this->owner->config['payon']) && $this->owner->config['payon'])
			$this->fields_form['balance'] =	array(
				'type' => 'text',
				'readonly' => true, 
				'caption' => 'Счет('.$this->owner->config['payon'].')',
				'mask'=>array('sort'=>1));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Пользователь активен', 'mask' =>array('usercheck'=>1));

		/*if(static_main::_prmUserCheck() and !static_main::_prmUserCheck(1)) {  // Запрет поля на редактирование
			$this->fields_form[$this->fn_login]['readonly']=true;
			//$this->fields_form[$this->fn_login]['email']=true;	
		}*/
			
	}


	function _update($flag_select=true) {
		$id = $this->id;
		$res = parent::_update($flag_select);
		if($res) {
			global $SESSION_GOGO;
			if($SESSION_GOGO) {
				if(isset($this->fld_data[$this->mf_actctrl]) and !$this->fld_data[$this->mf_actctrl])
					$SESSION_GOGO->delUser($id);
				else
					$SESSION_GOGO->updateUser($id,$this);
			}
		}
		return $res;
	}

	function _delete() {
		$id = $this->id;
		$res = parent::_delete();
		if($res) {
			global $SESSION_GOGO;
			if($SESSION_GOGO)
				$SESSION_GOGO->delUser($id);
		}
		return $res;
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
				$listfields = array('t2.id as gid, t2.active as gact, t2.name as gname, t2.level, t1.reg_hash,t1.active, t1.id, t1.'.$this->fn_pass);
				$clause = 't1 Join '.$this->owner->tablename.' t2 on t1.'.$this->owner_name.'=t2.id where t1.'.$this->fn_login.' = \''.$login.'\' and t1.'.$this->fn_pass.' =\''.md5($this->_CFG['wep']['md5'].$pass).'\'';
				$this->data = $this->_query($listfields,$clause);
				if(count($this->data))
				{
					unset($_SESSION['user']);
					if(_strlen($this->data[0]['reg_hash'])>5)
						return array($this->_CFG['_MESS']['authnoconf'],0);
					elseif($this->data[0]['reg_hash']=='0' && $this->data[0]['active']==0)
						return array($this->_CFG['_MESS']['auth_notcheck'],0);
					elseif(!$this->data[0]['gact'])
						return array($_CFG['_MESS']['auth_bangroup'],0);
					elseif($this->data[0]['active']==0)
						return array($this->_CFG['_MESS']['auth_banuser'],0);
					elseif($this->data[0]['level']>=5)
						return array($this->_CFG['_MESS']['denied'],0);
					else
					{
						if(isset($_POST['remember']) and $_POST['remember']=='1'){
							_setcookie('remember', md5($this->data[0][$this->fn_pass]).'_'.$this->data[0]['id'], (time()+(86400*$this->owner->config['rememberday'])));
						}
						$this->setUserSession($this->data[0]['id']);
						static_main::_prmModulLoad();
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
		if(!isset($_SESSION['user']['id']) and isset($_COOKIE['remember']))
		{
			if (preg_match("/^[0-9A-Za-z\_]+$/",$_COOKIE['remember']))
			{
				$pos = strpos($_COOKIE['remember'],'_');
				$listfields = array('t2.active as gact, t2.level, t1.active, t1.reg_hash, t1.id, t1.'.$this->fn_pass);
				$clause = 't1 Join '.$this->owner->tablename.' t2 on t1.'.$this->owner_name.'=t2.id where t1.id = \''.substr($_COOKIE['remember'],($pos+1)).'\' and md5(t1.'.$this->fn_pass.') =\''.substr($_COOKIE['remember'],0,$pos).'\'';
				$this->data = $this->_query($listfields,$clause);
				if(count($this->data))
				{
					unset($_SESSION['user']);
					if($this->data[0]['active']!=1)
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
						$this->setUserSession($this->data[0]['id']);
						static_main::_prmModulLoad();
						return array($this->_CFG['_MESS']['authok'],1);
					}
				}
			}
		}

		return array('',0);
	}

	function regForm($param=array()) {
		global $_MESS,$_tpl;
		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = $DATA = array();
		if(static_main::_prmUserCheck()) {
			$this->id = $_SESSION['user']['id'];
			$this->data = $this->_select();
			$DATA = $this->data[$this->id];
		}
		else {
		// добавлены настройки на форму регистрации
			if(!$this->owner->config['reg']) 
				return array(array('messages'=>array(array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedreg']))),1);			
			$DATA = $_POST;
			$this->id = 0;
			if(count($_POST) and $_POST['sbmt'] and isset($_SESSION['user']))
				unset($_SESSION['user']);
		}

		if(count($_POST) and $_POST['sbmt']) {
			$flag=-1;
			//if($this->fn_pass!='pass') $_POST[$this->fn_pass] = $_POST['pass'];
			//if($this->fn_pass!='login') $_POST[$this->fn_login] = $_POST['login'];
			$this->kPreFields($_POST,$param);
			$arr = $this->fFormCheck($_POST,$param,$this->fields_form);
			if(!count($arr['mess'])){
				$clause = 't1 where (t1.'.$this->fn_login.' = \''.$arr['vars'][$this->fn_login].'\'';
				if($this->fn_login!='email' and $arr['vars']['email']) {
					$clause .= ' or t1.email = \''.$arr['vars']['email'].'\'';
				}
				$clause .= ' )';
				if($this->id) $clause .= ' and id!='.$this->id;
				$datach = $this->_query('LOWER(t1.'.$this->fn_login.') as lgn',$clause);
				if(count($datach) and $datach[0]['lgn']==mb_strtolower($arr['vars'][$this->fn_login]))
					$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['notlogin']);
				elseif(isset($datach[0]))
					$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['notemail']);
				else {
					if(!$this->id) { // регистрация
						$arr['vars']['owner_id']=$this->owner->config['noreggroup'];
						$arr['vars']['active']=0;
						if(!isset($arr['vars'][$this->mf_namefields]) or !$arr['vars'][$this->mf_namefields])
							$arr['vars'][$this->mf_namefields] = $arr['vars'][$this->fn_login];
						$arr['vars']['reg_hash']=md5(time().$arr['vars'][$this->fn_login]);
						$pass=$arr['vars'][$this->fn_pass];
						$arr['vars'][$this->fn_pass]=md5($this->_CFG['wep']['md5'].$arr['vars'][$this->fn_pass]);
						//$_SESSION['user'] = $arr['vars']['id'];
						if($this->_add_item($arr['vars'])) {
							$this->SQL->execSQL('UPDATE '.$this->tablename.' SET '.$this->mf_createrid.'="'.$this->id.'" where '.$this->fn_login.'="'.$arr['vars'][$this->fn_login].'"');
							_new_class('mail',$MAIL);
							$datamail = array('creater_id'=>-1);
							$datamail['mail_to']=$arr['vars']['email'];
							$datamail['user_to']=$this->id;
							$datamail['subject']='Подтвердите регистрацию на '.strtoupper($_SERVER['HTTP_HOST']);
							$href = '?confirm='.$arr['vars'][$this->fn_login].'&hash='.$arr['vars']['reg_hash'];
							
							$datamail['text']=str_replace(array('%pass%','%login%','%href%','%host%'),array($pass,$arr['vars'][$this->fn_login],$href,$_SERVER['HTTP_HOST']),$this->owner->config['mailconfirm']);
							$MAIL->reply = 0;
							if($MAIL->Send($datamail)) {
								// иногда сервер говорит что ошибка, а сам всеравно письма отсылает
							} else {
								trigger_error('Регистрация - '.$this->_CFG['_MESS']['mailerr'], E_USER_WARNING);
								//$this->_delete();
								//$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['mailerr']);
								//$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['regerr']);
							}
							$flag=1;
							$arr['mess'][] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['regok']);
						} else
							$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['regerr']);
					}else { // профиль
						if($this->id==$_SESSION['user']['id'])
							unset($arr['vars']['active']);
						if($this->_save_item($arr['vars'])) {
							$arr['mess'][] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['update']);
							if($formflag)// кастыль
								$mess = $this->kPreFields($this->data[$this->id],$param);
							$this->setUserSession($this->id);
							$flag=1;
						}
						else
							$arr['mess'][] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['update_err']);
					}
				}
			}
		} else  {
			$mess = $this->kPreFields($DATA,$param);
		}
		if(static_main::_prmUserCheck())
			$this->fields_form['_info']= array('type'=>'info','caption'=>$this->getMess('title_profile'),'css'=>'caption');
		else
			$this->fields_form['_info']= array('type'=>'info','caption'=>$this->getMess('title_regme'),'css'=>'caption');

		static_form::setCaptcha();
		if(isset($param['formflag']))
			$formflag = $param['formflag'];
		elseif($flag==0)
			$formflag = 1;
		elseif(isset($_POST['sbmt']) and $_POST['sbmt'] and $flag==1)
			$formflag = 0;
		elseif(isset($_POST['sbmt_save']) and $_POST['sbmt_save'])
			$formflag = 1;
		elseif(isset($param['ajax']))
			$formflag = 0;
		if($formflag) // показывать форму
			$formflag = $this->kFields2Form($param);

		return Array(Array('messages'=>($mess+$arr['mess']), 'form'=>($formflag?$this->form:array()), 'class'=>'regform'), $flag);
	}

	function regConfirm() {
		global $_MESS;
		$flag = false;
		
		$_GET['hash'] = preg_replace("/[^0-9a-f]+/",'',$_GET['hash']);
		if(!$this->owner->config['reg'])
			$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['deniedreg']);
		elseif(!isset($_GET['confirm']) or !isset($_GET['hash']) or _strlen($_GET['hash'])!=32)
			$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['errdata']);
		else {
			$data = $this->_query('t1.id,t1.reg_hash','t1 where t1.`'.$this->fn_login.'` = \''.preg_replace("/[^0-9a-z@\.]+/",'',$_GET['confirm']).'\'');
			if(count($data) and _strlen($data[0]['reg_hash'])<5)
				$mess[] = array('name'=>'alert', 'value'=>$this->_CFG['_MESS']['confno']);
			elseif(count($data) and $data[0]['reg_hash']==$_GET['hash']) {
				$this->id = $data[0]['id'];
				$this->fld_data['reg_hash']= 1;
				if($this->owner->config['premoderation']) {
					$this->fld_data['active']= -1;
					$this->fld_data['owner_id']= $this->owner->config['modergroup'];
				}
				else {
					$this->fld_data['active']= 1;
					$this->fld_data['owner_id']= $this->owner->config['reggroup'];
				}
				
				if($this->_update()) {
					$mess[] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['confok']);
					$this->setUserSession($this->id);
					static_main::_prmModulLoad();
					$flag = true;
				}
				else
					$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['conferr']);
			}
			else
				$mess[] = array('name'=>'error', 'value'=>$this->_CFG['_MESS']['errdata']);
		}

		return Array(array('messages'=>$mess),$flag);
	}

	function remindSET($_DATA) {
		$mess = array();
		$flag = -1;
		$listfields = array('t1.*');
		$clause = 't1 where t1.id = \''.$_DATA['get']['id'].'\'';
		$this->data = $this->_query($listfields,$clause);
		$datau=$this->data[0];
		if($_DATA['get']['t']<(time()-3600*$_DATA['timer']))
			$mess[]  = array('error','Срок действия ссылки "востановления пароля" истёк.');
		elseif(count($this->data)==1 and $datau['active']==1 and $_DATA['get']['hash']==(md5($datau[$this->fn_pass].$_DATA['get']['t'].$datau['email']).'h')) {
			
			$flag=0;
			if(isset($_DATA['pass']) and $_DATA['pass']) {
				if(!isset($_DATA['re_pass']) or $_DATA['pass']==$_DATA['re_pass']){
					if(_strlen($_DATA['pass'])>=6) {
						$this->SQL->execSQL('UPDATE '.$this->tablename.' SET '.$this->fn_pass.'="'.md5($this->_CFG['wep']['md5'].$_DATA['pass']).'" where email="'.$datau['email'].'"');
						$mess[]  = array('ok','Ура! Ваш пароль был успешно изменён.');
						$flag = 1;
					}else
						$mess[]  = array('error','Пароль должен быть длинее 6ти символов.');
				}
				else
					$mess[]  = array('error','Неверно повторен пароль.');
			}
			
		}
		elseif(count($this->data)==1 and $this->data[0]['active']!=1) {
			$mess[]  = array('error','Ваш профиль отключен или не подтверждён. Обратитесь в <a href="/mail.html?feedback=1">службу поддержки сайта</a>, если не сможете решить проблему.');
		}
		else{
			$mess[]  = array('error','Не верные параметры данных.<br/> Возможно вы уже воспользовались данной ссылкой.');
		}
		if($flag===0)
			$mess[]  = array('ok','Введите новый пароль для пользователя с email-ом '.$datau['email'].'. Пароль должен быть не менее 6ти символов. Используйте различные комбинации из спецсимволов, цифр, больших и маленьких букв.');
		return array($flag,$mess);
	}

	function remindSEND($_DATA) {
		$mess = array();
		$flag = 0;
		$listfields = array('t1.*');
		$clause = 't1 where t1.email = \''.$_DATA['post']['mail'].'\'';
		$this->data = $this->_query($listfields,$clause);
		if(count($this->data)==1 and $this->data[0]['active']==1) {
			$datau=$this->data[0];
			$time=time();
			$hash =md5($datau[$this->fn_pass].$time.$datau['email']).'h';
			_new_class('mail',$MAIL);
			$datamail = array('creater_id'=>-1);
			$datamail['mail_to']=$datau['email'];
			$datamail['user_to']=$datau['id'];
			$datamail['subject']='Востановление пароля на '.strtoupper($_SERVER['HTTP_HOST']);
			$href = '?id='.$datau['id'].'&t='.$time.'&hash='.$hash;
			$datamail['text']=str_replace(
					array('%email%','%login%','%href%','%time%','%host%'),
					array($datau['email'],$datau[$this->fn_login],$href,date('Y-m-d H:i',($time+3600*$_DATA['timer'])),$_SERVER['HTTP_HOST']),
					$this->owner->config['mailremind']);
			$MAIL->reply = 0;
			if($MAIL->Send($datamail)) {
				$mess[]  = array('ok','На ваш E-mail отправленно письмо с секретной ссылкой на форму для установки нового пароля.<br/> Ссылка действительна в течении 2х суток с момента отправки данной формы.');
				$flag = 1;
			}else {
				trigger_error('Напоминание пароля - '.$this->_CFG['_MESS']['mailerr'], E_USER_WARNING);
				$mess[]  = array('error',$this->_CFG['_MESS']['mailerr']);
				$flag = 0;
			}
		}
		elseif(count($this->data)==1) {
			$flag = -1;
			$mess[]  = array('error','Ваш профиль отключен или не подтверждён. Обратитесь в <a href="/mail.html?feedback=1">службу поддержки сайта</a>');
		}
		else{
			$flag = -2;
			$mess[]  = array('error','Такой адрес на сайте не зарегистрирован.');
		}
		return array($flag,$mess);
	}

	function setUserSession($id) {
		$data = $this->owner->getUserData($id);
		if(!count($data) or !$data['id']) return false;
		session_go(1);
		$_SESSION['user'] = $data;
		if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0)
			_setcookie('_showerror',1);
		return true;
	}

	function UserInfo($ID,$fld='id') {
		$ID = mysql_real_escape_string($ID);
		if(isset($this->userCach[$ID]))
			return $this->userCach[$ID];
		$DATA = $this->_query('t2.name as gname,t1.*',' t1 JOIN '.$this->owner->tablename.' t2 ON t1.owner_id=t2.id WHERE t1.'.$fld.'="'.$ID.'"');
		if(count($DATA)) {
			$DATA = $DATA[0];
			$this->userCach[$ID] = $DATA;
		}
		return $DATA;
	}

	function diplayList($data,$field=false) {
		$DATA = array();
		if($field===false)
			$field = array('creater_id');
		foreach($data as $r) {
			foreach($field as $ur) 
				$DATA[$r[$ur]] = $r[$ur];
		}
		if(count($DATA))
			$DATA = $this->_query('t2.name as gname,t1.*',' t1 JOIN '.$this->owner->tablename.' t2 ON t1.owner_id=t2.id WHERE t1.id IN ('.implode(',',$DATA).')','id');
		return $DATA;
	}

}

