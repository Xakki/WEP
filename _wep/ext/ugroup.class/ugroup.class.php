<?php

class ugroup_class extends kernel_extends
{

	function _create_conf()
	{ /*CONFIG*/
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
		$this->config['uniq_email'] = 1;
		$this->config['istree'] = 0;
		$this->config['offerta'] = ''; //Я принимаю Условия использования.

		$this->config_form['mail_to'] = array('type' => 'text', 'mask' => array('min' => 1, 'name' => 'email'), 'caption' => 'Адрес службы поддержки');
		$this->config_form['mailrobot'] = array('type' => 'text', 'mask' => array('min' => 1, 'name' => 'email'), 'caption' => 'Адрес Робота');
		$this->config_form['mailinfo'] = array(
			'type' => 'ckedit',
			'caption' => 'Инфа о реге',
			'paramedit' => array(
				'CKFinder' => 1,
				'height' => 350,
				'fullPage' => 'true',
				'toolbarStartupExpanded' => 'false'));
		$this->config_form['mailconfirm'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст письма для подтверждения',
			'paramedit' => array(
				'CKFinder' => 1,
				'height' => 350,
				'fullPage' => 'true',
				'toolbarStartupExpanded' => 'false'));
		$this->config_form['mailremind'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст письма востановления пароля',
			'paramedit' => array(
				'CKFinder' => 1,
				'height' => 350,
				'fullPage' => 'true',
				'toolbarStartupExpanded' => 'false'));
		$this->config_form['reg'] = array('type' => 'checkbox', 'caption' => 'Включить регистрацию?');
		$this->config_form['payon'] = array('type' => 'text', 'caption' => 'Включить платежную систему? Введите название денежной единицы. [руб,евро итп]');
		$this->config_form['premoderation'] = array('type' => 'checkbox', 'caption' => 'Использовать премодерацию?');
		$this->config_form['noreggroup'] = array('type' => 'list', 'listname' => 'list', 'caption' => 'Неподтвердившие регистрацию');
		$this->config_form['reggroup'] = array('type' => 'list', 'listname' => 'list', 'caption' => 'Регистрировать по умолчанию');
		$this->config_form['modergroup'] = array('type' => 'list', 'listname' => 'list', 'caption' => 'Ожидающие проверку модератором');
		$this->config_form['rememberday'] = array('type' => 'int', 'mask' => array('min' => 1), 'caption' => 'Дней запоминания авторизации');
		$this->config_form['karma'] = array('type' => 'checkbox', 'caption' => 'Включить систему рейтингов?', 'style' => 'background:green;');
		$this->config_form['userpic'] = array('type' => 'text', 'mask' => array(), 'caption' => 'Дефолтная фотка пользователя');
		$this->config_form['invite'] = array('type' => 'checkbox', 'caption' => 'Включить систему инвайтов?', 'style' => 'background:gray;');
		$this->config_form['uniq_email'] = array('type' => 'checkbox', 'caption' => 'Уникальный Email?');
		$this->config_form['istree'] = array('type' => 'checkbox', 'caption' => 'Включить подгруппы?');
		$this->config_form['offerta'] = array(
			'type' => 'ckedit',
			'caption' => 'Пользовательское соглашение',
			'comment' => 'Чтобы включить эту опцию, нужно прописать в этом поле короткий текст который будет в профиле пользователя напротив чекбокса. текст может содержать ссылки. Пример "Я принимаю Условия использования."',
			'paramedit' => array(
				'height' => 80,
				//'fullPage'=>'true',
				'toolbarStartupExpanded' => 'false'));

	}

	protected function init()
	{
		parent::init();

		$this->mf_actctrl = true;
		$this->mf_istree = false;
		$this->mf_treelevel = 1;
		$this->caption = 'Группы';
		$this->singleton = true;
		$this->tablename = 'ugroup';
		$this->ver = '0.2.3';
		$this->default_access = '|0|';
	}

	protected function _create()
	{
		$this->mf_istree = (bool)$this->config['istree']; // Выполнять до parent::_create();, тк там BOOL значение преобразуется в назв поля
		parent::_create();

		if ($this->config['payon']) { // Включается зависимость модуля
			$this->_dependClass = array('pay');
		}

		$this->_vf_list = 'concat(name,"[",level,"]")'; // агрегатная ф мускл

		$this->_enum['level'] = array(
			0 => 'Администраторы',
			1 => 'Модераторы',
			2 => 'Пользователи',
			5 => 'Гости');

		$this->fields[$this->mf_namefields] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
		$this->fields['wep'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['level'] = array('type' => 'tinyint', 'width' => 2, 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['negative'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['filesize'] = array('type' => 'int', 'width' => 5, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['design'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['minkarma'] = array('type' => 'int', 'width' => 8, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['maxkarma'] = array('type' => 'int', 'width' => 8, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['defkratio'] = array('type' => 'decimal', 'width' => '8,2', 'attr' => 'NOT NULL', 'default' => '0.00');

	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['id'] = array('type' => 'text', 'mask' => array(), 'caption' => 'ID');
		$this->fields_form[$this->mf_namefields] = array('type' => 'text', 'mask' => array('min' => 1), 'caption' => 'Название группы');
		$this->fields_form['wep'] = array('type' => 'checkbox', 'caption' => 'Админка', 'comment' => 'Разрешить вход в админку?');
		$this->fields_form['level'] = array('type' => 'list', 'listname' => 'level', 'caption' => 'Доступ в CMS', 'default' => 2);
		$this->fields_form['design'] = array('type' => 'list', 'listname' => 'cdesign', 'caption' => 'Дизайн', 'comment' => 'Дизаин личного кабинета', 'default' => 'default');
		$this->fields_form['filesize'] = array('type' => 'int', 'caption' => 'Share', 'comment' => 'Доступный размер диска. Значение в мегабайтах, 0 - запрет', 'mask' => array('max' => 1000));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

		if ($this->config['karma']) {
			$this->fields_form['minkarma'] = array('type' => 'text', 'caption' => 'Мин. карма', 'mask' => array());
			$this->fields_form['maxkarma'] = array('type' => 'text', 'caption' => 'Макс. карма', 'mask' => array('fview' => 1));
			$this->fields_form['defkratio'] = array('type' => 'text', 'caption' => 'Коэфициент по умол.', 'mask' => array('fview' => 1));
		}
		if ($this->config['payon']) {
			$this->fields_form['negative'] = array('type' => 'int', 'caption' => 'Разрешенный отрицательный баланс');
		}
	}

	function _setDefaultRecords()
	{
		parent::_setDefaultRecords();
		$this->def_records[1] = array('id' => 1, $this->mf_namefields => 'Администраторы', 'level' => 0, 'filesize' => '100', 'active' => 1, 'design' => 'default', 'wep' => 1);
		$this->def_records[2] = array('id' => 2, $this->mf_namefields => 'Пользователи', 'level' => 2, 'filesize' => '0', 'active' => 1, 'design' => 'default', 'wep' => 0);
		$this->def_records[3] = array('id' => 3, $this->mf_namefields => 'Гости', 'level' => 5, 'filesize' => '0', 'active' => 1, 'design' => 'default', 'wep' => 0);
		return true;
	}

	function _childs()
	{
		$this->create_child('users');
		if (isBackend()) { // для админки , чтобы удобно можно было задавать права доступа
			/*$this->create_child('modulgrp');
			$this->childs['modulgrp']->owner_name = 'ugroup_id';//теперь родитель в этом поле привязан
			unset($this->childs['modulgrp']->fields_form['ugroup_id']);//отклю список групп
			$this->childs['modulgrp']->fields_form['owner_id'] = array('type' => 'list', 'readonly' => 1, 'listname'=>array('tablename'=>$this->SQL_CFG['dbpref'].'modulprm'), 'caption' => 'Модуль');//и включаем модули
			$this->childs['modulgrp']->fields['owner_id'] = array(); // чтобы  не ругался модчекструкт, тк это поле может задаваться по умолчанию от родителя
			*/
		}
	}

	function _getlist($listname, $value = 0)
	{
		$data = array();
		if ($listname == 'glist') {
			$result = $this->SQL->execSQL('SELECT id, ' . $this->mf_namefields . ' FROM ' . $this->tablename);
			if (!$result->err)
				while ($row = $result->fetch())
					$data[$row['id']] = $row[$this->mf_namefields];
			return $data;
		}
		elseif ($listname == 'cdesign') {
			$dir = dir($this->_CFG['_PATH']['cdesign']);
			while ($entry = $dir->read()) {
				if ($entry != '.' and $entry != '..' && $entry{0} != '_') {
					$data[$entry] = $entry;
				}
			}
			$dir->close();
			return $data;
		}
		else return parent::_getlist($listname, $value);
	}

	function authorization($login, $pass)
	{
		return $this->childs['users']->authorization($login, $pass);
	}

	function cookieAuthorization()
	{
		return $this->childs['users']->cookieAuthorization();
	}

	function regForm($param = array(), $argForm = null)
	{
		return $this->childs['users']->regForm($param, $argForm);
	}

	function regConfirm()
	{
		return $this->childs['users']->regConfirm();
	}

	public function getUserData($id)
	{
		if (!$id) {
			trigger_error('Error get user data', E_USER_WARNING);
			return array();
		}
		$listfields = array('t1.*,t1.id as gid,t1.active as gact,t1.name as gname,t2.*');
		$clause = 't1 Join ' . $this->childs['users']->tablename . ' t2 on t2.' . $this->childs['users']->owner_name . '=t1.id where t2.id = ' . (int)$id . '';
		$this->data = $this->_query($listfields, $clause);
		if (!count($this->data)) {
			trigger_error('Not found data for user id=' . $id, E_USER_WARNING);
			return array();
		}
		$this->data[0]['FckEditorUserFilesUrl'] = MY_BH . $this->_CFG['PATH']['userfile'] . $id . '/';
		$this->data[0]['FckEditorUserFilesPath'] = SITE . $this->_CFG['PATH']['userfile'] . $id . '/';
		return $this->data[0];
	}

	function mailNotif()
	{
		$mess = '';
		if ($this->config['mail_to'] and $this->config['premoderation']) {
			$data = $this->childs['users']->_query('id,name,email', 'WHERE active=-1 and owner_id=' . $this->config['modergroup']);
			if (count($data)) {
				$txt = '<table border="1"><tr><td>ID</td><td>Name</td><td>email</td></tr>';
				foreach ($data as $k => $r) {
					$txt .= '<tr>
						<td><a href="' . ADMIN_BH . '?_view=list&_modul=ugroupom&ugroupom_id=' . $this->config['modergroup'] . '&ugroupom_ch=usersom&usersom_id=' . $r['id'] . '&_type=update">' . $r['id'] . '</a></td>
						<td>' . $r['name'] . '</td>
						<td>' . $r['email'] . '</td></tr>';
				}
				$txt .= '</table>';
				_new_class('mail', $MAIL);
				$datamail = array(
					'creater_id' => 0,
					'mail_to' => $this->config['mail_to'],
					'subject' => strtoupper($_SERVER['HTTP_HOST']) . ' Оповещение: Ожидают проверки ' . count($data) . ' зарегистрированных пользователя',
					'text' => '<p>Список пользователей ожидающие одобрения.</p>' . $txt,

				);
				$MAIL->reply = 0;
				$MAIL->config['mailcron'] = 0;
				if ($MAIL->Send($datamail)) {
					$mess = 'Оповещение: ' . count($data) . ' пользователей ожидают одобрения.';
				}
				else {
					trigger_error('Оповещение - ' . static_main::m('mailerr', $this), E_USER_WARNING);
				}
			}
		}
		return $mess;
	}

	/**
	 *
	 */
	public function needApplyOfferta(&$form)
	{
		if ($this->config['offerta'] and static_main::_prmUserCheck() and !$_SESSION['user']['offerta']) {
			if (isset($_POST['user_offerta']) and $_POST['user_offerta']) {
				$this->childs['users']->id = $_SESSION['user']['id'];
				return $this->childs['users']->_update(array('offerta' => 1));
			}
			else
				$form['user_offerta'] = array('type' => 'checkbox', 'caption' => $this->config['offerta'], 'mask' => array('min' => 1));
			//, 'comment'=>'Для продолжения, необходимо дать согласие, отметив данный пункт'
			return true;
		}
		return false;
	}

}


class users_class extends kernel_extends
{

	function init()
	{
		parent::init();
		$this->mf_actctrl = true;
		$this->fn_login = 'email'; //login or email
		$this->fn_pass = 'pass';
		$this->mf_use_charid = false;
		$this->cf_fields = true; // Разрешить добавлять добавлять дополнительные поля в таблицу
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_ipcreate = true; //IP адрес пользователя с котрого была добавлена запись
		$this->caption = 'Пользователи';
		$this->lang['title_regme'] = 'Регистрация пользователя';
		//$this->lang['update_name'] =
		$this->lang['title_profile'] = 'Редактирование профиля';
		$this->lang['Save and close'] = 'Зарегистрировать';
		$this->default_access = '|0|';
		$this->userCach = array();
		$this->tablename = 'users';
	}

	protected function _create_conf()
	{ /*CONFIG*/
		parent::_create_conf();

		$this->config['temp_olden'] = 0;
		$this->config['cf_fields'] = array();
		$this->config_form['temp_olden'] = array('type' => 'checkbox', 'caption' => 'Включить дополнительные поля');
	}

	function _create()
	{
		if ($this->config['temp_olden'])
			$this->config['cf_fields'] = array(
				'cf1' => array(
					'type' => 'varchar',
					'width' => 32,
					'attr' => 'NOT NULL',
					'default' => '',
					'caption' => 'Телефон',
					'mask' => array('name' => 'phone3'),
				),
				'cf2' => array(
					'type' => 'varchar',
					'width' => 32,
					'attr' => 'NOT NULL',
					'default' => '',
					'caption' => 'Адрес',
				),
			);

		parent::_create();
		$this->ordfield = 'mf_timecr DESC';
		$this->unique_fields[$this->fn_login] = $this->fn_login;
		$this->unique_fields['email'] = 'email';
		$this->index_fields[$this->fn_pass] = $this->fn_pass;

		if ($this->fn_login != 'email') {
			$this->fields[$this->fn_login] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		}
		$this->fields['email'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields[$this->mf_namefields] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		$this->fields[$this->fn_pass] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		// service field

		$this->fields['reg_hash'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['balance'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL', 'default' => '0.00');
		$this->fields['lastvisit'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['karma'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['kratio'] = array('type' => 'decimal', 'width' => '8,2', 'attr' => 'NOT NULL', 'default' => '0.00');
		$this->fields['offerta'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);

		$this->attaches['userpic'] = array('mime' => array('image/pjpeg' => 'jpg', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/png' => 'png'), 'thumb' => array(array('type' => 'resize', 'w' => '800', 'h' => '600', 'pref' => 'orign_'), array('type' => 'resizecrop', 'w' => 85, 'h' => 85)), 'maxsize' => 1000, 'path' => '');
		if (static_main::_prmUserCheck() and isset($this->_CFG['modulprm_ext']) and !is_null($this->_CFG['modulprm_ext'])) {
			$params = array(
				'obj' => &$this,
				'func' => 'updateLastVisit',
			);
			observer::register_observer($params, 'shutdown_function');
		}
		$this->cron[] = array('modul' => $this->_cl, 'function' => 'deleteNoConfirmUser()', 'active' => 0, 'time' => 86400);
	}

	function _childs()
	{
		parent::_childs();
		if ($this->owner->config['invite'])
			$this->create_child('invite');
	}

	function _setDefaultRecords()
	{
		parent::_setDefaultRecords();
		$this->def_records[1] = array(
			$this->fn_login => $this->_CFG['wep']['login'],
			$this->mf_namefields => 'Администратор',
			$this->fn_pass => md5($this->_CFG['wep']['md5'] . $this->_CFG['wep']['password']),
			'active' => 1,
			'mf_timecr' => time(),
			'owner_id' => 1,
			'reg_hash' => 1);
		return true;
	}

	function updateLastVisit()
	{
		if (isset($_SESSION['user']['id']) and isset($_SESSION['user']['lastvisit']) and (time() - $_SESSION['user']['lastvisit']) > 300) {
			$this->SQL->execSQL('UPDATE `' . $this->tablename . '` SET lastvisit=' . time() . ' WHERE id=' . (int)$_SESSION['user']['id'] . '');
		}
	}

	// FORM FIELDS
	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form = array();
		$this->fields_form['owner_id'] = array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'Группа', 'mask' => array('usercheck' => 1, 'fview' => 1));
		$this->fields_form[$this->fn_login] = array('type' => 'text', 'caption' => 'Логин', 'mask' => array('name' => 'login', 'min' => '4', 'sort' => 1), 'comment' => 'Логин должен состоять только из латинских букв и цифр.');

		// if($this->id) {
		/*Такое лучше не открывать не кому , лучше пользоваться востановлением пароля*/
		// if(static_main::_prmUserCheck(1)) // Вывод поля генерации пароля если админ
		// $this->fields_form[$this->fn_pass] = array('type' => 'password', 'caption' => 'Пароль', 'mask'=>array('password'=>'re', 'min' => '6','fview'=>1));
		//$this->fields_form[$this->fn_pass] = array('type' => 'password', 'caption' => 'Пароль','md5'=>$this->_CFG['wep']['md5'], 'mask'=>array('password'=>'hash', 'min' => '6','fview'=>1));
		//else
		/*if(isset($_POST[$this->fn_pass]) and !$_POST[$this->fn_pass])
			unset($this->fields_form[$this->fn_pass]);unset($_POST[$this->fn_pass]);
		$this->fields_form[$this->fn_login]['readonly']=true;*/
		// } else {
		$this->fields_form[$this->fn_pass] = array('type' => 'password', 'caption' => 'Пароль', 'mask' => array('min' => '6', 'fview' => 1));
		//$this->fields_form[$this->fn_login]['readonly']=false;
		//$this->fields_form[$this->fn_pass] = array('type' => 'password', 'caption' => 'Пароль','mask'=>array('min' => '6','fview'=>1));
		// }

		$this->fields_form['email'] = array('type' => 'email', 'caption' => 'E-mail', 'mask' => array('name' => 'email', 'min' => '7'));
		$this->fields_form[$this->mf_namefields] = array('type' => 'text', 'caption' => 'Имя', 'mask' => array('name' => 'name2')); // Вывод поля при редактировании
		if ($this->owner->config['karma']) {
			$this->fields_form['karma'] = array('type' => 'text', 'caption' => 'Карма', 'readonly' => true, 'mask' => array('usercheck' => 1));
			$this->fields_form['kratio'] = array('type' => 'decimal', 'caption' => 'Коэф. значимости', 'readonly' => true, 'mask' => array('usercheck' => 1));
			if (static_main::_prmUserCheck(1)) {
				unset($this->fields_form['karma']['readonly']);
				unset($this->fields_form['kratio']['readonly']);
			}
		}
		$this->fields_form['userpic'] = array('type' => 'file', 'caption' => 'Аватар', 'del' => 1, 'mask' => array('fview' => 1, 'width' => 85, 'height' => 85, 'thumb' => 0), 'default' => $this->owner->config['userpic']);
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => true, 'caption' => 'IP-пользователя', 'mask' => array('usercheck' => 1));
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => true, 'caption' => 'Дата регистрации', 'mask' => array('sort' => 1));
		$this->fields_form['reg_hash'] = array('type' => 'hidden', 'caption' => 'Хэш', 'mask' => array('eval' => 1, 'fview' => 1, 'usercheck' => 1));
		if ($this->owner->config['payon'] or static_main::_prmUserCheck(1))
			$this->fields_form['balance'] = array(
				'type' => 'text',
				'readonly' => true,
				'caption' => 'Счет(' . $this->owner->config['payon'] . ')',
				'mask' => array());
		if ($this->owner->config['offerta']) {
			$this->fields_form['offerta'] = array('type' => 'checkbox', 'caption' => $this->owner->config['offerta'], 'mask' => array('min' => 1)); // 'comment'=>'Для продолжения, необходимо дать согласие, отметив данный пункт'
		}
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Пользователь активен', 'mask' => array('usercheck' => 1));

		/*if(static_main::_prmUserCheck() and !static_main::_prmUserCheck(1)) {  // Запрет поля на редактирование
			$this->fields_form[$this->fn_login]['readonly']=true;
			//$this->fields_form[$this->fn_login]['email']=true;
		}*/

	}

	function _update($data = array(), $where = null, $flag_select = true)
	{
		$id = $this->id;
		$res = parent::_update($data, $where, $flag_select);
		if ($res) {
			global $SESSION_GOGO;
			if ($SESSION_GOGO) {
				if (isset($this->fld_data[$this->mf_actctrl]) and !$this->fld_data[$this->mf_actctrl])
					$SESSION_GOGO->delUser($id);
				else
					$SESSION_GOGO->updateUser($id, $this);
			}
		}
		return $res;
	}

	function _delete()
	{
		$id = $this->id;
		$res = parent::_delete();
		if ($res) {
			global $SESSION_GOGO;
			if ($SESSION_GOGO)
				$SESSION_GOGO->delUser($id);
		}
		return $res;
	}

	function authorization($login, $pass)
	{
		if ($login != '' && $pass != '') {
			if ($this->fn_login == 'email' and !preg_match('/^[0-9A-Za-z_\-\.\@]+$/', $login))
				return array('Поле `Email` введено не корректно. Допустим ввод только латинских букв,цифр, точки, тире, подчёркивание и @', 0);
			elseif ($this->fn_login != 'email' and !preg_match('/^[0-9A-Za-z]+$/', $login))
				return array('Поле `Логин` введено не корректно. Допустим ввод только латинских букв и цифр.', 0);
			else {
				$listfields = array('t2.id as gid, t2.active as gact, t2.name as gname, t2.level, t1.reg_hash,t1.active, t1.id, t1.' . $this->fn_pass);
				$clause = 't1 Join ' . $this->owner->tablename . ' t2 on t1.' . $this->owner_name . '=t2.id where t1.' . $this->fn_login . ' = \'' . $login . '\' and t1.' . $this->fn_pass . ' =\'' . md5($this->_CFG['wep']['md5'] . $pass) . '\'';
				$this->data = $this->_query($listfields, $clause);
				if (count($this->data)) {
					if (isset($_SESSION['user']))
						unset($_SESSION['user']);
					if (_strlen($this->data[0]['reg_hash']) == 32)
						return array(static_main::m('authnoconf', $this), 0);
					elseif ($this->data[0]['reg_hash'] == '0' && $this->data[0]['active'] == 0)
						return array(static_main::m('auth_notcheck', $this), 0);
					elseif (!$this->data[0]['gact'])
						return array(static_main::m('auth_bangroup', $this), 0);
					elseif ($this->data[0]['active'] == 0)
						return array(static_main::m('auth_banuser', $this), 0);
					elseif ($this->data[0]['level'] >= 5)
						return array(static_main::m('denied', $this), 0);
					else {
						if (isset($_POST['remember']) and $_POST['remember'] == '1') {
							_setcookie('remember', md5($this->data[0][$this->fn_pass]) . '_' . $this->data[0]['id'], (time() + (86400 * $this->owner->config['rememberday'])));
						}
						$this->setUserSession($this->data[0]['id']);
						static_main::_prmModulLoad();
						return array(static_main::m('authok', $this), 1);
					}
				}
				else
					return array(static_main::m('autherr', $this), 0);
			}
		}
		else
			return array('Поля `Логин` и `Пароль` - обязательные!', 0);
	}


	function cookieAuthorization()
	{
		$mess = '';
		if (!static_main::_prmUserCheck() and isset($_COOKIE['remember'])) {
			if (preg_match("/^[0-9A-Za-z\_]+$/", $_COOKIE['remember'])) {
				$pos = strpos($_COOKIE['remember'], '_');
				$listfields = array('t2.active as gact, t2.level, t1.active, t1.reg_hash, t1.id, t1.' . $this->fn_pass);
				$clause = 't1 Join ' . $this->owner->tablename . ' t2 on t1.' . $this->owner_name . '=t2.id where t1.id = \'' . substr($_COOKIE['remember'], ($pos + 1)) . '\' and md5(t1.' . $this->fn_pass . ') =\'' . substr($_COOKIE['remember'], 0, $pos) . '\'';
				$this->data = $this->_query($listfields, $clause);
				if (count($this->data)) {
					if (isset($_SESSION['user']))
						unset($_SESSION['user']);
					if ($this->data[0]['active'] != 1)
						$mess = 'Ваш аккаунт заблокирован. За дополнительной информацией обращайтесь к Администратору сайта.';
					elseif (!$this->data[0]['gact'])
						$mess = 'Ваша группа заблокирована. За дополнительной информацией обращайтесь к Администратору сайта.';
					elseif (_strlen($this->data[0]['reg_hash']) == 32)
						$mess = 'Вы не подтвердили регистрацию.';
					elseif ($this->data[0]['level'] >= 5)
						$mess = 'Доступ закрыт.';
					else {
						_setcookie('remember', md5($this->data[0][$this->fn_pass]) . '_' . $this->data[0]['id'], (time() + (86400 * $this->owner->config['rememberday'])));
						$this->setUserSession($this->data[0]['id']);
						static_main::_prmModulLoad();
						return array(static_main::m('authok', $this), 1);
					}
				}
			}
			_setcookie('remember', 0, -10000);
		}
		return array($mess, 0);
	}

	function regForm($param = array(), $argForm = null)
	{
		global $_MESS, $_tpl;
		$flag = 0; // 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1; // 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess' => array(), 'vars' => array());
		$mess = $DATA = array();

		if ($this->id) {
			//$this->id = (int)$_SESSION['user']['id'];
			$this->data = $this->_select();
			$DATA = current($this->data);
		}
		else {
			// добавлены настройки на форму регистрации
			if (!$this->owner->config['reg']) {
				return array(
					array(
						'messages' => array(
							static_main::am('error', 'deniedreg')
						)
					),
					1
				);
			}
			$DATA = $_POST;
			$this->id = null;
			if (count($_POST) and isset($_POST['sbmt']) and isset($_SESSION['user']))
				unset($_SESSION['user']);
		}

		if (is_null($argForm))
			$argForm = $this->fields_form;

		if (count($_POST) and isset($_POST['sbmt'])) {
			$flag = -1;
			//if($this->fn_pass!='pass') $_POST[$this->fn_pass] = $_POST['pass'];
			//if($this->fn_pass!='login') $_POST[$this->fn_login] = $_POST['login'];
			$this->kPreFields($_POST, $param, $argForm);
			$pass = $_POST[$this->fn_pass];
			$arr = $this->fFormCheck($_POST, $param, $argForm);
			if (!count($arr['mess'])) {
				$clause = 't1 where (t1.' . $this->fn_login . ' = \'' . $this->SqlEsc($arr['vars'][$this->fn_login]) . '\'';
				if ($this->fn_login != 'email' and $arr['vars']['email']) {
					$clause .= ' or t1.email = \'' . $this->SqlEsc($arr['vars']['email']) . '\'';
				}
				$clause .= ' )';
				if ($this->id) $clause .= ' and id!=' . $this->id;
				$datach = $this->_query('LOWER(t1.' . $this->fn_login . ') as lgn', $clause);
				if (count($datach) and $datach[0]['lgn'] == mb_strtolower($arr['vars'][$this->fn_login]))
					$arr['mess'][] = static_main::am('error', 'notlogin');
				elseif (isset($datach[0]))
					$arr['mess'][] = static_main::am('error', 'notemail');
				else {
					if (!$this->id) { // регистрация
						$arr['vars'][$this->owner_name] = $this->owner->config['noreggroup'];
						$arr['vars']['active'] = 0;
						if (!isset($arr['vars'][$this->mf_namefields]) or !$arr['vars'][$this->mf_namefields])
							$arr['vars'][$this->mf_namefields] = $arr['vars'][$this->fn_login];
						$arr['vars']['reg_hash'] = md5(time() . $arr['vars'][$this->fn_login]);

						//$_SESSION['user'] = $arr['vars']['id'];
						if (isset($param['owner_id'])) {
							$arr['vars']['owner_id'] = $param['owner_id'];
						}
						elseif ($this->owner->config['premoderation']) {
							$arr['vars']['owner_id'] = $this->owner->config['modergroup'];
						}
						else {
							$arr['vars']['owner_id'] = $this->owner->config['reggroup'];
						}

						if ($this->_add($arr['vars'])) {
							$this->SQL->execSQL('UPDATE ' . $this->tablename . ' SET ' . $this->mf_createrid . '="' . $this->id . '" where id="' . $this->id . '"');
							$this->sendRegMail($this->data[$this->id], $pass);
							$flag = 1;
							$arr['mess'][] = static_main::am('ok', 'regok');
						}
						else
							$arr['mess'][] = static_main::am('error', 'regerr');
					}
					else { // профиль
						if ($this->id == $_SESSION['user']['id'])
							unset($arr['vars']['active']);
						if ($this->_update($arr['vars'])) {
							$arr['mess'][] = static_main::am('ok', 'update');
							if ($formflag) // кастыль
								$mess = $this->kPreFields($this->data[$this->id], $param, $argForm);
							$this->setUserSession($this->id);
							$flag = 1;
						}
						else
							$arr['mess'][] = static_main::am('error', 'update_err');
					}
				}
			}
		}
		else {
			$mess = $this->kPreFields($DATA, $param, $argForm);
		}

		if (isset($argForm['captcha']))
			static_form::setCaptcha($argForm['captcha']['mask']);

		if (isset($param['formflag']))
			$formflag = $param['formflag'];
		elseif ($flag == 0)
			$formflag = 1;
		elseif (isset($_POST['sbmt']) and $_POST['sbmt'] and $flag == 1)
			$formflag = 0;
		elseif (isset($_POST['sbmt_save']) and $_POST['sbmt_save'])
			$formflag = 1;
		elseif (isset($param['ajax']))
			$formflag = 0;
		if ($formflag) // показывать форму
			$formflag = $this->kFields2Form($param, $argForm);

		if (static_main::_prmUserCheck())
			$argForm['_info'] = array('type' => 'info', 'caption' => static_main::m('title_profile', $this), 'css' => 'caption');
		else
			$argForm['_info'] = array('type' => 'info', 'caption' => static_main::m('title_regme', $this), 'css' => 'caption');

		return Array(
			Array(
				'messages' => ($mess + $arr['mess']),
				'form' => ($formflag ? $argForm : array()),
				'class' => 'regform',
				'options' => $this->getFormOptions()
			),
			$flag
		);
	}

	function sendRegMail($vars, $pass = '', $subject = '', $flagInfo = false)
	{
		_new_class('mail', $MAIL);
		$MAIL->config['mailcron'] = 0;
		$datamail = array('creater_id' => -1);
		$datamail['mail_to'] = $vars['email'];
		$datamail['user_to'] = $vars['id'];
		if ($subject)
			$datamail['subject'] = $subject;
		else
			$datamail['subject'] = 'Подтвердите регистрацию на ' . strtoupper($_SERVER['HTTP_HOST']);
		$href = '?confirm=' . $vars[$this->fn_login] . '&hash=' . $vars['reg_hash'];

		$datamail['text'] = str_replace(
			array('%pass%', '%login%', '%href%', '%host%'),
			array($pass, $vars[$this->fn_login], $href, $_SERVER['HTTP_HOST']),
			($flagInfo ? $this->owner->config['mailinfo'] : $this->owner->config['mailconfirm'])
		);
		$MAIL->reply = 0;
		if ($MAIL->Send($datamail)) {
			// иногда сервер говорит что ошибка, а сам всеравно письма отсылает
			return true;
		}
		else {
			trigger_error('Регистрация - ' . static_main::m('mailerr', $this), E_USER_WARNING);
			//$this->_delete();
			//$arr['mess'][] = array('name'=>'error', 'value'=>static_main::m('mailerr',$this));
			//$arr['mess'][] = array('name'=>'error', 'value'=>static_main::m('regerr',$this));
			return false;
		}
	}

	function regConfirm()
	{
		global $_MESS;
		$flag = false;

		$_GET['hash'] = preg_replace("/[^0-9a-f]+/", '', $_GET['hash']);
		if (!$this->owner->config['reg'])
			$mess[] = static_main::am('error', 'deniedreg');
		elseif (!isset($_GET['confirm']) or !isset($_GET['hash']) or _strlen($_GET['hash']) != 32)
			$mess[] = static_main::am('error', 'errdata');
		else {
			$data = $this->_query('t1.id,t1.reg_hash', 't1 where t1.`' . $this->fn_login . '` = \'' . preg_replace("/[^0-9a-z@\-\_\.]+/u", '', $_GET['confirm']) . '\'');
			if (count($data) and _strlen($data[0]['reg_hash']) < 32)
				$mess[] = static_main::am('alert', 'confno');
			elseif (count($data) and $data[0]['reg_hash'] == $_GET['hash']) {
				$this->id = $data[0]['id'];
				$DATA = array();
				$DATA['reg_hash'] = 1;
				if ($this->owner->config['premoderation']) {
					$DATA['active'] = -1;
				}
				else {
					$DATA['active'] = 1;
				}

				if ($this->_update($DATA)) {
					$mess[] = static_main::am('ok', 'confok');
					$this->setUserSession($this->id);
					static_main::_prmModulLoad();
					$flag = true;
				}
				else
					$mess[] = static_main::am('error', 'conferr');
			}
			else
				$mess[] = static_main::am('error', 'errdata');
		}

		return Array(array('messages' => $mess), $flag);
	}

	function remindSET($_DATA)
	{
		$mess = array();
		$flag = -1;
		$listfields = array('t1.*');
		$this->id = $_DATA['get']['id'];
		$this->data = $this->_select();
		$datau = current($this->data);
		if ($_DATA['get']['t'] < (time() - 3600 * $_DATA['timer']))
			$mess[] = array('error', 'Срок действия ссылки "востановления пароля" истёк.');
		elseif (count($this->data) == 1 and $datau['active'] == 1 and $_DATA['get']['hash'] == (md5($datau[$this->fn_pass] . $_DATA['get']['t'] . $datau['email']) . 'h')) {

			$flag = 0;
			if (isset($_DATA['pass']) and $_DATA['pass']) {
				if (!isset($_DATA['re_pass']) or $_DATA['pass'] == $_DATA['re_pass']) {
					if (_strlen($_DATA['pass']) >= 6) {
						$this->SQL->execSQL('UPDATE ' . $this->tablename . ' SET ' . $this->fn_pass . '="' . md5($this->_CFG['wep']['md5'] . $_DATA['pass']) . '" where email="' . $datau['email'] . '"');
						$mess[] = array('ok', 'Ура! Ваш пароль был успешно изменён.');
						$flag = 1;
					}
					else
						$mess[] = array('error', 'Пароль должен быть длинее 6ти символов.');
				}
				else
					$mess[] = array('error', 'Неверно повторен пароль.');
			}

		}
		elseif (count($this->data) == 1 and $this->data[$this->id]['active'] != 1) {
			$mess[] = array('error', 'Ваш профиль отключен или не подтверждён. Обратитесь в <a href="/mail.html?feedback=1">службу поддержки сайта</a>, если не сможете решить проблему.');
		}
		else {
			$mess[] = array('error', 'Не верные параметры данных.<br/> Возможно вы уже воспользовались данной ссылкой.');
		}
		if ($flag === 0)
			$mess[] = array('ok', 'Введите новый пароль для пользователя с email-ом ' . $datau['email'] . '. Пароль должен быть не менее 6ти символов. Используйте различные комбинации из спецсимволов, цифр, больших и маленьких букв.');
		return array($flag, $mess);
	}

	function remindSEND($_DATA)
	{
		$mess = array();
		$flag = 0;
		$listfields = array('t1.*');
		$clause = 't1 where t1.email = \'' . $this->SqlEsc($_DATA['post']['mail']) . '\'';
		$this->data = $this->_query($listfields, $clause);
		if (count($this->data) == 1 and $this->data[0]['active'] == 1) {
			$datau = $this->data[0];
			$time = time();
			$hash = md5($datau[$this->fn_pass] . $time . $datau['email']) . 'h';
			_new_class('mail', $MAIL);
			$MAIL->config['mailcron'] = 0;
			$datamail = array('creater_id' => -1);
			$datamail['mail_to'] = $datau['email'];
			$datamail['user_to'] = $datau['id'];
			$datamail['subject'] = 'Востановление пароля на ' . strtoupper($_SERVER['HTTP_HOST']);
			$href = '?id=' . $datau['id'] . '&t=' . $time . '&hash=' . $hash;
			$datamail['text'] = str_replace(
				array('%email%', '%login%', '%href%', '%time%', '%host%'),
				array($datau['email'], $datau[$this->fn_login], $href, date('Y-m-d H:i', ($time + 3600 * $_DATA['timer'])), $_SERVER['HTTP_HOST']),
				$this->owner->config['mailremind']);
			$MAIL->reply = 0;
			if ($MAIL->Send($datamail)) {
				$mess[] = array('ok', 'На ваш E-mail отправлено письмо с секретной ссылкой на форму для установки нового пароля.<br/> Ссылка действительна в течении 2х суток с момента отправки данной формы.');
				$flag = 1;
			}
			else {
				trigger_error('Напоминание пароля - ' . static_main::m('mailerr', $this), E_USER_WARNING);
				$mess[] = static_main::am('error', 'mailerr');
				$flag = 0;
			}
		}
		elseif (count($this->data) == 1) {
			$flag = -1;
			$mess[] = array('error', 'Ваш профиль отключен или не подтверждён. Обратитесь в <a href="/mail.html?feedback=1">службу поддержки сайта</a>');
		}
		else {
			$flag = -2;
			$mess[] = array('error', 'Такой адрес на сайте не зарегистрирован.');
		}
		return array($flag, $mess);
	}

	function setUserSession($id)
	{
		$data = $this->owner->getUserData($id);
		if (!count($data) or !$data['id']) return false;
		session_go(true);
		$_SESSION['user'] = $data;
		//if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0)
		//	_setcookie($this->_CFG['wep']['_showerror'],2);
		unset($this->_CFG['modulprm']);
		static_main::_prmModulLoad();
		return true;
	}

	function UserInfo($ID, $fld = 'id')
	{
		$ID = $this->SqlEsc($ID);
		if (isset($this->userCach[$ID]))
			return $this->userCach[$ID];
		$DATA = $this->_query('t2.name as gname,t1.*', ' t1 JOIN ' . $this->owner->tablename . ' t2 ON t1.owner_id=t2.id WHERE t1.' . $fld . '="' . $this->SqlEsc($ID) . '"');
		if (count($DATA)) {
			$DATA = $DATA[0];
			$this->userCach[$ID] = $DATA;
		}
		return $DATA;
	}

	function displayList($data, $field = false)
	{
		$DATA = array();
		if ($field === false)
			$field = array('creater_id');
		foreach ($data as $r) {
			foreach ($field as $ur)
				$DATA[$r[$ur]] = $this->SqlEsc($r[$ur]);
		}
		if (count($DATA))
			$DATA = $this->_query('t2.name as gname,t1.*', ' t1 JOIN ' . $this->owner->tablename . ' t2 ON t1.owner_id=t2.id WHERE t1.id IN (' . implode(',', $DATA) . ')', 'id');
		return $DATA;
	}

	// Удаление пользователей не подтвердивших авторизацию
	function deleteNoConfirmUser($day = 7)
	{
		if ($this->owner->config['reg'] and $this->owner->config['noreggroup']) {
			$this->SQL->execSQL('DELETE FROM ' . $this->tablename . ' WHERE active=0 and owner_id=' . $this->owner->config['noreggroup'] . ' and ' . $this->mf_timecr . '<' . ($this->_CFG['time'] - ($day * 24 * 3600)));
		}
	}
}

