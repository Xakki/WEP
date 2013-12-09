<?php

class paywebmoney_class extends kernel_extends
{

	function _create_conf2(&$obj)
	{ /*CONFIG*/
		//parent::_create_conf();

		$obj->config['webmoney_WMR'] = 'R385104050920';
		$obj->config['webmoney_WMZ'] = 'Z750445485014';
		$obj->config['webmoney_WMU'] = 'U879987000333';
		$obj->config['webmoney_WME'] = 'E841076953303';

		$obj->config['webmoney_login'] = '';
		$obj->config['webmoney_password'] = '';
		$obj->config['webmoney_txn-prefix'] = '';
		$obj->config['webmoney_create-agt'] = 1;
		$obj->config['webmoney_lifetime'] = 1080;
		$obj->config['webmoney_alarm-webmoney'] = 0;
		$obj->config['webmoney_alarm-call'] = 0;
		$obj->config['webmoney_minpay'] = 10;
		$obj->config['webmoney_maxpay'] = 15000;

		$obj->config_form['webmoney_info'] = array('type' => 'info', 'caption' => '<h3>webmoney</h3><p>На сайте необходимо разместить логотип и описание(<a href="http://ishopnew.webmoney.ru/docs.html" target="_blank">материалы webmoney для сайта</a>)</p>');
		$obj->config_form['webmoney_login'] = array('type' => 'text', 'caption' => 'Логин', 'comment' => '', 'style' => 'background-color:#2ab7ec;');
		$obj->config_form['webmoney_password'] = array('type' => 'password', 'md5' => false, 'caption' => 'Пароль', 'style' => 'background-color:#2ab7ec;');
		$obj->config_form['webmoney_txn-prefix'] = array('type' => 'text', 'caption' => 'Префикс в номере счёта', 'comment' => '', 'style' => 'background-color:#2ab7ec;');
		//$this->owner->config_form['webmoney_create-agt'] = array('type' => 'text', 'caption' => 'Логин','comment'=>'Если 1 то при выставлении счёта создается пользователь в системе webmoney. При этом оплатить счёт можно в терминале наличными без ввода ПИН-кода.', 'style'=>'background-color:gray;');
		$obj->config_form['webmoney_lifetime'] = array('type' => 'text', 'caption' => 'Таймаут', 'comment' => 'Время жизни счёта по умолчанию. Задается в часах. Максимум 45 суток (1080 часов)', 'style' => 'background-color:#2ab7ec;');
		$obj->config_form['webmoney_alarm-webmoney'] = array('type' => 'text', 'caption' => 'alarm-webmoney', 'comment' => '1 - включит СМС оповещение (СМС платно)', 'style' => 'background-color:#2ab7ec;');
		$obj->config_form['webmoney_alarm-call'] = array('type' => 'text', 'caption' => 'alarm-call', 'comment' => '1 - включит звонок (платно)', 'style' => 'background-color:#2ab7ec;');
		$obj->config_form['webmoney_minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#2ab7ec;');
		$obj->config_form['webmoney_maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#2ab7ec;');
	}

	function init()
	{
		parent::init();
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		//$this->pay_systems = true; // Это модуль платёжной системы
		$this->showinowner = false;

		$this->caption = 'Платежи WebMoney';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->ver = '0.1';

	}

	protected function _create()
	{
		parent::_create();
		$this->fields['user_id'] = array('type' => 'int', 'width' => 8, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL'); // в коппейках

		$this->pay_systems = array(
			'WMR' => array(
				'caption' => 'webmoney R',
				'icon' => 'wmr.gif',
			),

		);
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);

		$this->fields_form['user_id'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Юзер', 'mask' => array());
		$this->fields_form['cost'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Цена (кооп.)', 'mask' => array());
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Дата', 'mask' => array());
		//$this->fields_form['mf_timeup'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата обновления', 'mask'=>array('fview'=>2));
		//$this->fields_form['mf_timeoff'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата отключения', 'mask'=>array('fview'=>2));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Дата', 'mask' => array('fview' => 2));
	}

	// возвращает массив с формой, которая отправит пользователя на сайт платежной системы
	function get_pay_form($system, $amount, $inv_id)
	{

		$desc = $this->owner->config['desc'];
		$lmi_payee_purse = $this->owner->config['webmoney_' . $_POST['payment_system']];
		$caption = $this->pay_systems[$_POST['payment_system']]['caption'];

		$crc = md5($this->owner->config['webmoney_login'] . ':' . $amount . ':' . $inv_id . ':' . $this->owner->config['webmoney_password']);

		$data['text_before'] = 'Пополнение баланса через систему ' . $caption . '<br/>После оплаты, на Ваш счет поступит ' . $amount . ' рублей.';
		$data['text_after'] = '';

		$data['options'] = array(
			'method' => 'post',
			'name' => 'pay',
			'action' => 'https://merchant.webmoney.ru/lmi/payment.asp',
			'enctype' => '',
		);
		$data['form'] = array(
			'LMI_PAYMENT_AMOUNT' => array(
				'value' => $amount,
				'type' => 'hidden',
			),
			'LMI_PAYMENT_DESC_BASE64' => array(
				'value' => base64_encode($desc),
				'type' => 'hidden',
			),
			'LMI_PAYEE_PURSE' => array(
				'value' => $lmi_payee_purse,
				'type' => 'hidden',
			),
			'LMI_MODE' => array(
				'value' => 1,
				'type' => 'hidden',
			),
			'LMI_SIM_MODE' => array(
				'value' => 0,
				'type' => 'hidden',
			),
			'submit' => array(
				'value' => 'Перейти к оплате',
				'type' => 'submit',
			),
		);


		return $data;
	}


}


