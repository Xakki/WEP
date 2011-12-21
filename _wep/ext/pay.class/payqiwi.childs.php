<?php
class payqiwi_class extends kernel_extends {

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config['login'] = '';
		$this->config['password'] = '';
		$this->config['txn-prefix'] = '';
		$this->config['create-agt'] = 1;
		$this->config['lifetime'] = 0;
		$this->config['alarm-sms'] = 0;
		$this->config['alarm-call'] = 0;
		$this->config['minpay'] = 10;
		$this->config['maxpay'] = 15000;

		$this->config_form['login'] = array('type' => 'text', 'caption' => 'Логин','comment'=>'');
		$this->config_form['password'] = array('type' => 'password', 'caption' => 'Пароль');
		$this->config_form['txn-prefix'] = array('type' => 'text', 'caption' => 'Префикс в номере счёта','comment'=>'');
		//$this->config_form['create-agt'] = array('type' => 'text', 'caption' => 'Логин','comment'=>'Если 1 то при выставлении счёта создается пользователь в системе QIWI. При этом оплатить счёт можно в терминале наличными без ввода ПИН-кода.');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут','comment'=>'Время жизни счёта по умолчанию. Задается в часах. Если 0 , то будетмаксимум (45 суток)');
		$this->config_form['alarm-sms'] = array('type' => 'text', 'caption' => 'alarm-sms','comment'=>'1 - включит СМС оповещение (СМС платно)');
		$this->config_form['alarm-call'] = array('type' => 'text', 'caption' => 'alarm-call','comment'=>'1 - включит звонок (платно)');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта');
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'QIWI';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->lang['add_name'] = 'Пополнение кошелька из QIWI';
		$this->lang['_saveclose'] = 'Выписать счёт';
		$this->lang['add'] = 'Счёт на пополнение кошелька отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action">QIWI</a> и в течении 5ти минут после оплаты, сумма поступит на ваш баланс.';
		$this->default_access = '|0|';
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->_href = 'http://ishop.qiwi.ru/xml';
		$this->ver = '0.1';
		$this->pay_systems = true; // Это модуль платёжной системы

		$this->_enum['statuses'] = array(
			50 => 'Неоплаченный счёт',
			60 => 'Оплаченный счёт',
			150 => 'Счёт отклонён'
		);

		$this->_enum['errors'] =array(
			300 => 'Неизвестная ошибка',
			13 => 'Сервер занят. Повторите запрос позже',
			150 => 'Неверный логин или пароль',
			215 => 'Счёт с таким номером уже существует',
			278 => 'Превышение максимального интервала получения списка счетов',
			298 => 'Агент не существует в системе',
			330 => 'Ошибка шифрования',
			370 => 'Превышено макс. кол-во одновременно выполняемых запросов',
			0 => 'OK'
		);
		return true;
	}

	protected function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL');
		$this->fields['phone'] = array('type' => 'bigint', 'width' => 11,'attr' => 'unsigned NOT NULL');
		$this->fields['cost'] = array('type' => 'float', 'width' => '11,4','attr' => 'NOT NULL'); // в коппейках
		$this->fields['statuses'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL');
		$this->fields['errors'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL','default'=>0);

		$this->cron[] = array('modul'=>$this->_cl,'function'=>'checkBill()','active'=>1,'time'=>300);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['phone'] = array('type' => 'int', 'caption' => 'Номер телефона', 'comment'=>'10ти значный номер вашего мобильного телефона', 'mask'=>array('min'=>10,'max'=>10));
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['minpay'].'р, максимум '.$this->config['maxpay'].'р', 'mask'=>array('minint'=>$this->config['minpay'],'maxint'=>$this->config['maxpay']));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Комментарий', 'mask'=>array('name'=>'all'));
		$this->fields_form['statuses'] = array('type' => 'list', 'listname'=>'statuses', 'readonly'=>1, 'caption' => 'Статус', 'mask'=>array());
		$this->fields_form['errors'] = array('type' => 'list', 'listname'=>'errors', 'readonly'=>1, 'caption' => 'Ошибка', 'mask'=>array());
	}
	/**
	* При обновлении статуса
	*/
	/*function _update($data=array(),$where=false,$flag_select=true) {
		$result = parent::_update($data,$where,$flag_select);
		return $result;
	}*/

	/*
	* При добавлении делаем запрос XML
	*/
	function _add($data=array(),$flag_select=true) {
		$data2 = array(
			'phone'=>$data['phone'],
			'cost'=>$data['cost'],
			'statuses'=>50
		);

		$result = parent::_add($data2,true);
		if($result) {
			$data['name'] .= ' (Счёт №'.$this->config['txn-prefix'].$this->id.')';
			$options = array(
				'phone'=>$this->data[$this->id]['phone'],
				'amount'=>$this->data[$this->id]['cost'],
				'comment'=>$data['name']
			);
			$result = $this->createBill($options);
			if(!$result)
				$this->_delete();
			else
				$this->_update(array('name'=>$data['name']));
		}
		return $result;
	}

	/*
	* XML запрос на выписку счёта
	*/
	private function createBill($options) {
		$defaults = array(
			'create-agt' => $this->config['create-agt'],
			'lifetime' => $this->config['lifetime'],
			'alarm-sms' => $this->config['alarm-sms'],
			'alarm-call' => $this->config['alarm-call'],
			'txn-prefix' => $this->config['txn-prefix'],
			'comment'=>'Пополнение кошелька',
		);
		$options = array_merge($defaults, $options);

		$x = '<?xml version="1.0" encoding="utf-8"?><request>';
		$x .= '<protocol-version>4.00</protocol-version>';
		$x .= '<request-type>30</request-type>';
		$x .= '<extra name="password">' . $this->config['password'] . '</extra>';
		$x .= '<terminal-id>' . $this->config['login'] . '</terminal-id>';
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
			'body'=>$x
		);

		$result = $this->_http($this->_href,$param);

		return $this->check_response($result['text'],'send');
	}

	function checkBill() {
		$bills = $this->_query('*','WHERE statuses=50');
		if(!count($bills)) return true;

		$x = '<?xml version="1.0" encoding="utf-8"?><request>';
		$x .= '<protocol-version>4.00</protocol-version>';
		$x .= '<request-type>33</request-type>';
		$x .= '<extra name="password">' . $this->config['password'] . '</extra>';
		$x .= '<terminal-id>' . $this->config['login'] . '</terminal-id>';
		$x .= '<bills-list>';
		foreach($bills as $txnID) {
			$x .= '<bill txn-id="' . $this->config['txn-prefix'] . $txnID['id'] . '"/>';
		}
		$x .= '</bills-list>';
		$x .= '</request>';

		$param = array(
			'body'=>$x
		);

		$result = $this->_http($this->_href,$param);

		return $this->check_response($result['text'],'check');
	}

	function check_response($xml,$flag='send') {
		$flag = false;
		if($xml) {
			$flag = true;
			$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>'.$xml);
			$rc = $xml->{'result-code'};
			$fatality = $rc['fatal'];
			if($rc!='0') {
				$flag = false;
				if($this->id)
					$this->_update(array('errors'=>$rc),false,false);
			}
			if($fatality=='true') {
				trigger_error('Ошибка запроса QIWI '.$xml, E_USER_WARNING);
				return false;
			}
			if($flag=='check') {
				$billlist = $xml->{'bills-list'};
				if($billlist) {
					foreach ($billlist->children() as $bill) {
						$upd = array(
							'statuses' => (int)$bill['status'],
							'cost' => preg_replace('/[^0-9\.]/','',(string)$bill['sum'])
						);
						if($this->config['txn-prefix'])
							$upd['id'] = str_replace($this->config['txn-prefix'],'',$bill['id']);
						$this->id = NULL;
						$this->_update($upd);

						if($bill['status']==60)
							$status = 1;
						elseif($bill['status']==150)
							$status = 2;
						else
							$status = 0;

						$this->owner->PayTransaction($status,$this->data[$this->id]['cost'],$this->data[$this->id]['owner_id']);
					};
				}
			}
		}
		return $flag;
	}
}


