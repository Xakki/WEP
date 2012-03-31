<?php
class payqiwi_class extends kernel_extends {

	function _create_conf2(&$obj) {/*CONFIG*/
		//parent::_create_conf();

		$obj->config['qiwi_login'] = '';
		$obj->config['qiwi_password'] = '';
		$obj->config['qiwi_txn-prefix'] = '';
		$obj->config['qiwi_create-agt'] = 1;
		$obj->config['qiwi_lifetime'] = 1080;
		$obj->config['qiwi_alarm-sms'] = 0;
		$obj->config['qiwi_alarm-call'] = 0;
		$obj->config['qiwi_minpay'] = 10;
		$obj->config['qiwi_maxpay'] = 15000;

		$obj->config_form['qiwi_info'] = array('type' => 'info', 'caption'=>'<h3>QIWI</h3><p>На сайте необходимо разместить логотип и описание(<a href="http://ishopnew.qiwi.ru/docs.html" target="_blank">материалы QIWI для сайта</a>)</p>');
		$obj->config_form['qiwi_login'] = array('type' => 'text', 'caption' => 'Логин', 'comment'=>'', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_password'] = array('type' => 'password', 'md5'=>false, 'caption' => 'Пароль', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_txn-prefix'] = array('type' => 'text', 'caption' => 'Префикс в номере счёта','comment'=>'', 'style'=>'background-color:#2ab7ec;');
		//$this->owner->config_form['qiwi_create-agt'] = array('type' => 'text', 'caption' => 'Логин','comment'=>'Если 1 то при выставлении счёта создается пользователь в системе QIWI. При этом оплатить счёт можно в терминале наличными без ввода ПИН-кода.', 'style'=>'background-color:gray;');
		$obj->config_form['qiwi_lifetime'] = array('type' => 'text', 'caption' => 'Таймаут','comment'=>'Время жизни счёта по умолчанию. Задается в часах. Максимум 45 суток (1080 часов)', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_alarm-sms'] = array('type' => 'text', 'caption' => 'alarm-sms','comment'=>'1 - включит СМС оповещение (СМС платно)', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_alarm-call'] = array('type' => 'text', 'caption' => 'alarm-call','comment'=>'1 - включит звонок (платно)', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2ab7ec;');
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'QIWI';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->lang['add_name'] = 'Пополнение кошелька из QIWI';
		$this->lang['Save and close'] = 'Выписать счёт';
		$this->lang['add_err'] = 'Ошибка выставление счёта. Обратитесь к администратору сайта.';
		$this->lang['add'] = 'Счёт на оплату отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action" target="_blank">QIWI</a> в раздел "Счета".';
		//$this->lang['add'] = 'Счёт на пополнение кошелька отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action">QIWI</a> и в течении 5ти минут после оплаты, сумма поступит на ваш баланс.';
		$this->default_access = '|9|';
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->API_HREF = 'http://ishop.qiwi.ru/xml';
		$this->ver = '0.2';
		$this->pay_systems = true; // Это модуль платёжной системы
		$this->pay_formType = 'https://w.qiwi.ru/orders.action';

		$this->_enum['statuses'] = array(
			50 => 'Неоплаченный счёт',
			52 => 'Проводится',
			60 => 'Оплаченный счёт',
			150 => 'Отменен (ошибка на терминале)',
			151 => 'Отменен (ошибка авторизации: недостаточно средств на балансе, отклонен абонентом при оплате с лицевого счета оператора сотовой связи и т.п.).',
			160 => 'Отменен',
			161 => 'Отменен (Истекло время)',
		);
/*
Возможны иные статусы счетов.
Счета со статусом менее либо равным 50 трактуются как выставленные, но еще не оплаченные 
счета.
Cчета с 51 по 59 трактуются как счета в процессе проведения (могут перейти в статус 60).
Cчета со статусом большим или равным 100 трактуются как отмененные счет
*/

		$this->_enum['errors'] =array(
			0 => ' - ',
			13 => 'Сервер занят, повторите запрос позже',
			150 => 'Ошибка авторизации (неверный логин/пароль)',
			210 => 'Счет не найден',
			215 => 'Счет с таким txn-id уже существует',
			241 => 'Сумма слишком мала',
			242 => 'Превышена максимальная сумма платежа – 15 000р',
			278 => 'Превышение максимального интервала получения списка счетов',
			298 => 'Номер телефона введён неверный либо не существует в системе',
			300 => 'Неизвестная ошибка',
			330 => 'Ошибка шифрования на сервере',
			339 => 'Не пройден контроль IP-адреса для сервера',
			370 => 'Превышено максимальное кол-во одновременно выполняемых запросов, повторите запрос позже',
			510=> 'Ошибка проверки оплаты qiwi, счёт не найден в базе',
			520=> 'Ошибка при получении данных от QIWI',
		);

		$this->cron[] = array('modul'=>$this->_cl,'function'=>'checkBill()','active'=>1,'time'=>300);

		return true;
	}

	protected function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');
		$this->fields['phone'] = array('type' => 'bigint', 'width' => 13,'attr' => 'unsigned NOT NULL');
		$this->fields['cost'] = array('type' => 'float', 'width' => '11,4','attr' => 'NOT NULL'); // в коппейках
		$this->fields['statuses'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL');
		$this->fields['errors'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL','default'=>0);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['phone'] = array('type' => 'int', 'caption' => 'Номер телефона', 'comment'=>'10 значный номер мобильного. Пример: 9271234567', 'mask'=>array('min'=>10,'max'=>10));
		if($form and !$this->id and isset($_SESSION['user']['phone'])) {
			$this->fields_form['phone']['default'] = preg_replace('/[^0-9]/','',$_SESSION['user']['phone']);
			if($this->fields_form['phone']['default'][0]!='9'){
				$this->fields_form['phone']['default'] = mb_substr($this->fields_form['phone']['default'],1);
			}
		}
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->owner->config['qiwi_minpay'].'р, максимум '.$this->owner->config['qiwi_maxpay'].'р', 'default'=>100, 'mask'=>array('minint'=>$this->owner->config['qiwi_minpay'],'maxint'=>$this->owner->config['qiwi_maxpay']));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Комментарий', 'mask'=>array('name'=>'all'));
		$this->fields_form['statuses'] = array('type' => 'list', 'listname'=>'statuses', 'readonly'=>1, 'caption' => 'Статус', 'mask'=>array());
		$this->fields_form['errors'] = array('type' => 'list', 'listname'=>'errors', 'readonly'=>1, 'caption' => 'Ошибка', 'mask'=>array());
	}


	/*
	* При добавлении делаем запрос XML
	*/
	function billingFrom($summ, $comm, $data=array()) {
		$this->prm_add = true;
		$this->getFieldsForm(1);
		$argForm = $this->fields_form;
		$argForm['cost']['mask']['evala'] = $summ;
		$argForm['cost']['readonly'] = true;
		$argForm['name']['mask']['evala'] = '"'.addcslashes($comm,'"').'"';
		$argForm['name']['readonly'] = true;
		return $this->_UpdItemModul(array('showform'=>1),$argForm);
	}

	/**
	* При обновлении статуса
	*/
	/*function _update($data=array(),$where=false,$flag_select=true) {
		$result = parent::_update($data,$where,$flag_select);
		return $result;
	}*/
	function _add($data=array(),$flag_select=true) {
		$data2 = array(
			'phone'=>$data['phone'],
			'cost'=>$data['cost'],
			'statuses'=>50
		);

		$result = parent::_add($data2,true);
		if($result) {
			$data['name'] .= ' (Счёт №'.$this->owner->config['qiwi_txn-prefix'].$this->id.')';
			$options = array(
				'phone'=>$this->data[$this->id]['phone'],
				'amount'=>$this->data[$this->id]['cost'],
				'comment'=>$data['name']
			);
			$err = $this->createBill($options);
			if($err===0) {
				$this->_update(array('name'=>$data['name']));
			}
			else {
				$this->_delete();
				$this->lang['add_err'] = $this->_enum['errors'][$err];
				$result = false;
			}
		}
		return $result;
	}

	/*
	* XML запрос на выписку счёта
	*/
	private function createBill($options) {
		$defaults = array(
			'create-agt' => $this->owner->config['qiwi_create-agt'],
			'lifetime' => $this->owner->config['qiwi_lifetime'],
			'alarm-sms' => $this->owner->config['qiwi_alarm-sms'],
			'alarm-call' => $this->owner->config['qiwi_alarm-call'],
			'txn-prefix' => $this->owner->config['qiwi_txn-prefix'],
			'comment'=>'Пополнение кошелька',
		);
		$options = array_merge($defaults, $options);

		$x = '<?xml version="1.0" encoding="utf-8"?><request>';
		$x .= '<protocol-version>4.00</protocol-version>';
		$x .= '<request-type>30</request-type>';
		$x .= '<extra name="password">' . $this->owner->config['qiwi_password'] . '</extra>';
		$x .= '<terminal-id>' . $this->owner->config['qiwi_login'] . '</terminal-id>';
		$x .= '<extra name="txn-id">' . $options['txn-prefix'] . $this->id . '</extra>';
		$x .= '<extra name="to-account">' . $options['phone'] . '</extra>';
		$x .= '<extra name="amount">' . (int)$options['amount'] . '</extra>';
		$x .= '<extra name="comment">' . $options['comment'] . '</extra>';
		//$x .= '<extra name="create-agt">' . $options['create-agt'] . '</extra>';
		$x .= '<extra name="ltime">' . $options['lifetime'] . '</extra>';
		$x .= '<extra name="ALARM_SMS">' . $options['alarm-sms'] . '</extra>';
		$x .= '<extra name="ACCEPT_CALL">' . $options['alarm-call'] . '</extra>';
		$x .= '</request>';

		$param = array(
			'POST'=>$x
		);

		$result = $this->_http($this->API_HREF,$param);
		$err = $this->check_response($result['text'],'send');
		return $err;
	}

	function check_response($xml,$flag='send') {
		if(!$xml) return 520;
		$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>'.$xml);
		$rc = $xml->{'result-code'};
		$fatality = $rc['fatal'];
		$err = (int)$rc;
		if($err!==0) {
			if($this->id)
				$this->_update(array('errors'=>$rc),false,false);
		}
		if($fatality=='true') {
			return $err;
		}
		if($flag=='check') {
			$billlist = $xml->{'bills-list'};
			if($billlist) {
				foreach ($billlist->children() as $bill) {

					$upd = array(
						'statuses' => (int)$bill['status'],
						'cost' => floatval($bill['sum'])
					);
					if($this->owner->config['qiwi_txn-prefix'])
						$this->id = (int)str_replace($this->owner->config['qiwi_txn-prefix'],'',$bill['id']);
					else
						$this->id = (int)$bill['id'];
					$this->_update($upd);

					if($upd['statuses']==60)
						$status = 1;
					elseif($upd['statuses']>=100)
						$status = 2;
					else
						$status = 0;
					if($this->id and $this->data[$this->id])
						$this->owner->PayTransaction($status,$this->data[$this->id]['cost'],$this->data[$this->id]['owner_id']);
					else {
						$err = 501;
						trigger_error('Ошибка проверки оплаты qiwi: счёт '.$bill['id'].' не найден в базе', E_USER_WARNING);
					}
				};
			}
		}
		return $err;
	}


	/// CRON
	function checkBill() {
		$bills = $this->_query('*','WHERE statuses<60');
		if(!count($bills)) return '-нет выставленных счетов-';

		$x = '<?xml version="1.0" encoding="utf-8"?><request>';
		$x .= '<protocol-version>4.00</protocol-version>';
		$x .= '<request-type>33</request-type>';
		$x .= '<extra name="password">' . $this->owner->config['qiwi_password'] . '</extra>';
		$x .= '<terminal-id>' . $this->owner->config['qiwi_login'] . '</terminal-id>';
		$x .= '<bills-list>';
		foreach($bills as $txnID) {
			$x .= '<bill txn-id="' . $this->owner->config['qiwi_txn-prefix'] . $txnID['id'] . '"/>';
		}
		$x .= '</bills-list>';
		$x .= '</request>';

		$param = array(
			'POST'=>$x
		);

		$result = $this->_http($this->API_HREF,$param);
		$err = $this->check_response($result['text'],'check');
		if($err===0)
			return '-Успешно-';
		else{
			trigger_error('Ошибка запроса QIWI `'.$this->_enum['errors'][$err].'`', E_USER_WARNING);
			return '-Ошибка-'.$this->_enum['errors'][$err];
		}
	}

}


