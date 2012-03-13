<?php
class payqiwi_class extends kernel_extends {

	function _create_conf2(&$obj) {/*CONFIG*/
		//parent::_create_conf();

		$obj->config['qiwi_login'] = '';
		$obj->config['qiwi_password'] = '';
		$obj->config['qiwi_txn-prefix'] = '';
		$obj->config['qiwi_create-agt'] = 1;
		$obj->config['qiwi_lifetime'] = 0;
		$obj->config['qiwi_alarm-sms'] = 0;
		$obj->config['qiwi_alarm-call'] = 0;
		$obj->config['qiwi_minpay'] = 10;
		$obj->config['qiwi_maxpay'] = 15000;

		$obj->config_form['qiwi_info'] = array('type' => 'info', 'caption'=>'<h3>QIWI</h3>');
		$obj->config_form['qiwi_login'] = array('type' => 'text', 'caption' => 'Логин', 'comment'=>'', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_password'] = array('type' => 'password', 'caption' => 'Пароль', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_txn-prefix'] = array('type' => 'text', 'caption' => 'Префикс в номере счёта','comment'=>'', 'style'=>'background-color:#2ab7ec;');
		//$this->config_form['qiwi_create-agt'] = array('type' => 'text', 'caption' => 'Логин','comment'=>'Если 1 то при выставлении счёта создается пользователь в системе QIWI. При этом оплатить счёт можно в терминале наличными без ввода ПИН-кода.', 'style'=>'background-color:gray;');
		$obj->config_form['qiwi_lifetime'] = array('type' => 'text', 'caption' => 'Таймаут','comment'=>'Время жизни счёта по умолчанию. Задается в часах. Если 0 , то будетмаксимум (45 суток)', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_alarm-sms'] = array('type' => 'text', 'caption' => 'alarm-sms','comment'=>'1 - включит СМС оповещение (СМС платно)', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_alarm-call'] = array('type' => 'text', 'caption' => 'alarm-call','comment'=>'1 - включит звонок (платно)', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2ab7ec;');
		$obj->config_form['qiwi_maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2ab7ec;');
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		$this->config = &$this->owner->config;
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'QIWI';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->lang['add_name'] = 'Пополнение кошелька из QIWI';
		$this->lang['Save and close'] = 'Выписать счёт';
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
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');
		$this->fields['phone'] = array('type' => 'bigint', 'width' => 11,'attr' => 'unsigned NOT NULL');
		$this->fields['cost'] = array('type' => 'float', 'width' => '11,4','attr' => 'NOT NULL'); // в коппейках
		$this->fields['statuses'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL');
		$this->fields['errors'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL','default'=>0);

		$this->cron[] = array('modul'=>$this->_cl,'function'=>'checkBill()','active'=>1,'time'=>300);
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
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['qiwi_minpay'].'р, максимум '.$this->config['qiwi_maxpay'].'р', 'default'=>100, 'mask'=>array('minint'=>$this->config['qiwi_minpay'],'maxint'=>$this->config['qiwi_maxpay']));
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
			$data['name'] .= ' (Счёт №'.$this->config['qiwi_txn-prefix'].$this->id.')';
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
			'create-agt' => $this->config['qiwi_create-agt'],
			'lifetime' => $this->config['qiwi_lifetime'],
			'alarm-sms' => $this->config['qiwi_alarm-sms'],
			'alarm-call' => $this->config['qiwi_alarm-call'],
			'txn-prefix' => $this->config['qiwi_txn-prefix'],
			'comment'=>'Пополнение кошелька',
		);
		$options = array_merge($defaults, $options);

		$x = '<?xml version="1.0" encoding="utf-8"?><request>';
		$x .= '<protocol-version>4.00</protocol-version>';
		$x .= '<request-type>30</request-type>';
		$x .= '<extra name="password">' . $this->config['qiwi_password'] . '</extra>';
		$x .= '<terminal-id>' . $this->config['qiwi_login'] . '</terminal-id>';
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
		$x .= '<extra name="password">' . $this->config['qiwi_password'] . '</extra>';
		$x .= '<terminal-id>' . $this->config['qiwi_login'] . '</terminal-id>';
		$x .= '<bills-list>';
		foreach($bills as $txnID) {
			$x .= '<bill txn-id="' . $this->config['qiwi_txn-prefix'] . $txnID['id'] . '"/>';
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
		if($xml) {print_r($xml);
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
						if($this->config['qiwi_txn-prefix'])
							$upd['id'] = str_replace($this->config['qiwi_txn-prefix'],'',$bill['id']);
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


