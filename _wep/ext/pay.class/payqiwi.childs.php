<?php
class payqiwi_class extends kernel_extends
{

	const STATUS_NOYET = 50;
	const STATUS_ONWAY = 52;
	const STATUS_OK = 60;
	const STATUS_CANCEL = 100;
	const STATUS_ERROR_SYS = 150;
	const STATUS_NOMONEY = 151;
	const STATUS_CANCE_BY_USER = 160;
	const STATUS_CANCEL_BY_TIMEOUT = 161;

	public $API_HREF = 'http://ishop.qiwi.ru/xml';

	function _create_conf()
	{ /*CONFIG*/
		//parent::_create_conf();

		$this->config['qiwi_login'] = '';
		$this->config['qiwi_password'] = '';
		$this->config['qiwi_txn-prefix'] = '';
		$this->config['qiwi_create-agt'] = 1;
		$this->config['qiwi_alarm-sms'] = 0;
		$this->config['qiwi_alarm-call'] = 0;
		$this->config['minpay'] = 10;
		$this->config['maxpay'] = 15000;
		$this->config['lifetime'] = 1080;

		$this->config_form['qiwi_info'] = array('type' => 'info', 'caption' => '<h3>QIWI</h3><p>На сайте необходимо разместить логотип и описание(<a href="http://ishopnew.qiwi.ru/docs.html" target="_blank">материалы QIWI для сайта</a>)</p>');
		$this->config_form['qiwi_login'] = array('type' => 'text', 'caption' => 'Логин', 'comment' => '');
		$this->config_form['qiwi_password'] = array('type' => 'password', 'md5' => false, 'caption' => 'Пароль', 'mask' => array('password' => 'change'));
		$this->config_form['qiwi_txn-prefix'] = array('type' => 'text', 'caption' => 'Префикс в номере счёта', 'comment' => '');
		$this->config_form['qiwi_create-agt'] = array('type' => 'checkbox', 'caption' => 'Разрешать не клиентам QIWI', 'comment' => 'Если вкл. то при выставлении счёта создается пользователь в системе QIWI. При этом оплатить счёт можно в терминале наличными без ввода ПИН-кода.');
		$this->config_form['qiwi_alarm-sms'] = array('type' => 'text', 'caption' => 'alarm-sms', 'comment' => '1 - включит СМС оповещение (СМС платно)');
		$this->config_form['qiwi_alarm-call'] = array('type' => 'text', 'caption' => 'alarm-call', 'comment' => '1 - включит звонок (платно)');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут', 'comment' => 'Время жизни счёта по умолчанию. Задается в часах. Максимум 1080 часов (45 суток)', 'style' => 'background-color:#F60;');
	}

	function init()
	{
		parent::init();
		$this->caption = 'QIWI';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->lang['add_name'] = 'Пополнение кошелька из QIWI';
		$this->lang['add_err'] = 'Ошибка выставление счёта. Обратитесь к администратору сайта.';
		// TODO - ввывод сообщения реализовать через конфиг
		$this->lang['add'] = 'Счёт на оплату отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action" target="_blank" id="tempoQiwi">QIWI</a> в раздел "Счета".<script>$("#tempoQiwi").click();</script>';
		//$this->lang['add'] = 'Счёт на пополнение кошелька отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action">QIWI</a> и в течении 5ти минут после оплаты, сумма поступит на ваш баланс.';
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		//$this->showinowner = false;

		$this->ver = '0.2';
		$this->pay_systems = true; // Это модуль платёжной системы
		$this->pay_formType = 'https://w.qiwi.ru/orders.action';

		$this->_enum['statuses'] = array(
			self::STATUS_NOYET => 'Неоплаченный счёт',
			self::STATUS_ONWAY => 'Проводится',
			self::STATUS_OK => 'Оплаченный счёт',
			self::STATUS_ERROR_SYS => 'Отменен (ошибка на терминале)',
			self::STATUS_NOMONEY => 'Отменен (ошибка авторизации: недостаточно средств на балансе, отклонен абонентом при оплате с лицевого счета оператора сотовой связи и т.п.).',
			self::STATUS_CANCE_BY_USER => 'Отменен',
			self::STATUS_CANCEL_BY_TIMEOUT => 'Отменен (Истекло время)',
		);
		/*
		Возможны иные статусы счетов.
		Счета со статусом менее либо равным 50 трактуются как выставленные, но еще не оплаченные
		счета.
		Cчета с 51 по 59 трактуются как счета в процессе проведения (могут перейти в статус 60).
		Cчета со статусом большим или равным 100 трактуются как отмененные счет
		*/

		$this->_enum['errors'] = array(
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
			510 => 'Ошибка проверки оплаты qiwi, счёт не найден в базе',
			520 => 'Ошибка при получении данных от QIWI',
		);

		$this->cron[] = array('modul' => $this->_cl, 'function' => 'checkBill()', 'active' => 1, 'time' => 300);

	}

	protected function _create()
	{
		parent::_create();
		//$this->fields['name'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');
		$this->fields['phone'] = array('type' => 'bigint', 'width' => 13, 'attr' => 'unsigned NOT NULL');
		$this->fields['email'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL'); // в коппейках
		$this->fields['statuses'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['errors'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['phone'] = array('type' => 'int', 'caption' => 'Номер телефона', 'readonly' => 1, 'comment' => '10 значный номер мобильного, <b>без 8ки</b>. <br/>Пример: 9271234567', 'mask' => array('min' => 10, 'max' => 10), 'maxlength' => 10);
		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'Email');
		$this->fields_form['cost'] = array('type' => 'decimal', 'caption' => 'Сумма (руб)', 'readonly' => 1, 'comment' => 'Минимум ' . $this->config['minpay'] . 'р, максимум ' . $this->config['maxpay'] . 'р', 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		//$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Комментарий', 'mask'=>array('name'=>'all'));
		$this->fields_form['statuses'] = array('type' => 'list', 'listname' => 'statuses', 'readonly' => 1, 'caption' => 'Статус', 'mask' => array());
		$this->fields_form['errors'] = array('type' => 'list', 'listname' => 'errors', 'readonly' => 1, 'caption' => 'Ошибка', 'mask' => array());
	}

	/*
	* Создание счёта
	*/
	public function billingForm($summ, $comm, $data = array())
	{
		$this->prm_add = true;
		$param = array('showform' => 1, 'savePost' => true, 'setAutoSubmit' => true);

		$this->owner->setPostData('phone', $data);
		$this->owner->setPostData('email', $data);

		$argForm = array();
		$argForm['email'] = array('type' => 'email', 'caption' => 'Email', 'mask' => array('min' => 5));
		$argForm['phone'] = array('type' => 'int', 'caption' => 'Номер телефона', 'comment' => '10 значный номер мобильного, <b>без 8ки</b>. <br/>Пример: 9271234567', 'mask' => array('min' => 10, 'max' => 10), 'maxlength' => 10);
		if (isset($_POST['phone']) and $_POST['phone']) {
			$tmp = preg_replace('/[^0-9]/', '', $_POST['phone']);
			if ($tmp[0] != '9') {
				$tmp = mb_substr($tmp, 1);
			}
			if ($tmp and strlen($tmp) == 10)
				$_POST['phone'] = $tmp;
		}
		$argForm['name'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $comm)); // иначе name не попадает в БД
		if ($summ > 0)
			$argForm['cost'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $summ, 'min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		else
			$argForm['cost'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment' => 'Минимум ' . $this->config['minpay'] . 'р, максимум ' . $this->config['maxpay'] . 'р', 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		$this->lang['Save and close'] = 'Оплатить через ' . $this->caption;
		return $this->_UpdItemModul($param, $argForm);
	}


	// INFO
	public function statusForm($data)
	{
		global $_tpl;
		//$data['child']
		$result = array('showStatus' => true, 'messages' => array());
		if (count($data) and $data['status'] < 2) {
			$result['messages'][] = array('logoPayStatus qiwiPayStatus', '<div>Чтобы оплатить счёт, перейдите на сайт</div><a href="' . $this->pay_formType . '" target="_blank" title="QIWI" id="goQiwiClick">QIWI</a>');
			$result['messages'][] = array('autoClick', '<a title="Отменить" id="autoClick">Автоматический переход через <i>5</i> сек.</a><script>wep.timerFunction(function(){window.open($(".qiwiPayStatus a").attr("href"), "_blank");}, "#autoClick", "#goQiwiClick");</script>>');
			// $_tpl['onload'] - deprecated
		}
		return $result;
	}


	/**
	 * При обновлении статуса
	 */
	/*function _update($data=array(),$where=null,$flag_select=true) {
		$result = parent::_update($data,$where,$flag_select);
		return $result;
	}*/
	public function _add($data = array(), $flag_select = true, $flag_update = false)
	{
		$data2 = array(
			'phone' => $data['phone'],
			'email' => $data['email'],
			'cost' => $data['cost'],
			'statuses' => self::STATUS_NOYET
		);

		$result = parent::_add($data2, true, $flag_update);
		if ($result) {
			if (!isset($data['name']) or !$data['name'])
				$data['name'] = 'Счёт №' . $this->config['qiwi_txn-prefix'] . $this->id;
			$options = array(
				'phone' => $this->data[$this->id]['phone'],
				'amount' => $this->data[$this->id]['cost'],
				'comment' => $data['name']
			);
			$_SESSION['user']['phone'] = $this->data[$this->id]['phone']; // @WTF - сомнительно
			$err = $this->createBill($options);
			if ($err === 0) {
				$this->_update(array('name' => $data['name']));
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
	private function createBill($options)
	{
		$defaults = array(
			'create-agt' => $this->config['qiwi_create-agt'],
			'lifetime' => $this->config['lifetime'],
			'alarm-sms' => $this->config['qiwi_alarm-sms'],
			'alarm-call' => $this->config['qiwi_alarm-call'],
			'txn-prefix' => $this->config['qiwi_txn-prefix'],
			'comment' => 'Пополнение кошелька',
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
		if ($options['create-agt'])
			$x .= '<extra name="create-agt">1</extra>';
		$x .= '<extra name="ltime">' . $options['lifetime'] . '</extra>';
		$x .= '<extra name="ALARM_SMS">' . $options['alarm-sms'] . '</extra>';
		$x .= '<extra name="ACCEPT_CALL">' . $options['alarm-call'] . '</extra>';
		$x .= '</request>';

		$param = array(
			'POST' => $x
		);

		$result = static_tools::_http($this->API_HREF, $param);
		$err = $this->check_response($result['text'], 'send');
		return $err;
	}

	function check_response($xml, $flag = 'send')
	{
		if (!$xml) return 520;
		$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>' . $xml);
		$rc = $xml->{'result-code'};
		$fatality = $rc['fatal'];
		$err = (int)$rc;
		if ($err !== 0) {
			trigger_error('Ошибка создания счета QIWI. - [' . $err . ']' . $this->_enum['errors'][$err], E_USER_WARNING);
			if ($this->id)
				$this->_update(array('errors' => $rc), false, false);
		}
		if ($fatality == 'true') {
			return $err;
		}

		if ($flag == 'check') {
			$billlist = $xml->{'bills-list'};
			if ($billlist) {
				foreach ($billlist->children() as $bill) {
					$upd = array(
						'statuses' => (int)$bill['status'],
						'cost' => floatval($bill['sum'])
					);
					// *$upd['statuses']=self::STATUS_OK; //TEST - успешная оплата
					if ($this->config['qiwi_txn-prefix'])
						$this->id = (int)str_replace($this->config['qiwi_txn-prefix'], '', $bill['id']);
					else
						$this->id = (int)$bill['id'];
					$this->_update($upd);

					if ($upd['statuses'] == self::STATUS_OK)
						$status = PAY_PAID;
					elseif ($upd['statuses'] >= 100)
						$status = PAY_USERCANCEL;
					else
						$status = PAY_NOPAID;

					if ($this->id and $this->data[$this->id])
						$this->owner->payTransaction($this->data[$this->id]['owner_id'], $status);
					else {
						$err = 501;
						trigger_error('Ошибка проверки оплаты qiwi: счёт ' . $bill['id'] . ' не найден в базе', E_USER_WARNING);
					}
				};
			}
		}
		return $err;
	}


	/// CRON
	function checkBill()
	{
		$this->clearOldData();

		$bills = $this->_query('*', 'WHERE statuses<60');
		if (!count($bills)) return '-нет выставленных счетов-';

		$x = '<?xml version="1.0" encoding="utf-8"?><request>';
		$x .= '<protocol-version>4.00</protocol-version>';
		$x .= '<request-type>33</request-type>';
		$x .= '<extra name="password">' . $this->config['qiwi_password'] . '</extra>';
		$x .= '<terminal-id>' . $this->config['qiwi_login'] . '</terminal-id>';
		$x .= '<bills-list>';
		foreach ($bills as $txnID) {
			$x .= '<bill txn-id="' . $this->config['qiwi_txn-prefix'] . $txnID['id'] . '"/>';
		}
		$x .= '</bills-list>';
		$x .= '</request>';

		$param = array(
			'POST' => $x
		);

		$result = static_tools::_http($this->API_HREF, $param);

		$err = $this->check_response($result['text'], 'check');
		if ($err === 0)
			return '-Успешно-';
		else {
			trigger_error('Ошибка запроса QIWI `' . $this->_enum['errors'][$err] . '` <pre>' . _e(var_dump($result, true)) . '</pre>', E_USER_WARNING);
			return '-Ошибка-' . $this->_enum['errors'][$err];
		}
	}

	/**
	 * Сервис служба очистки данных
	 * Отключает неоплаченные платежи
	 * @param $M - модуль платежной системы
	 * @param $leftTime - в секундах
	 */
	function clearOldData()
	{
		if (!$this->config['lifetime']) $this->config['lifetime'] = 1080;
		$leftTime = ($this->config['lifetime'] * 3600);

		$this->_update(array('statuses' => '161', $this->mf_actctrl => 0), 'WHERE statuses<60 and ' . $this->mf_timecr . '<"' . (time() - $leftTime) . '"');

		$this->owner->clearOldData($this->_cl, $leftTime);
	}
}


