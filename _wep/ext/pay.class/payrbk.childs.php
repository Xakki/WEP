<?php

class payrbk_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->pay_systems = true; // Это модуль платёжной системы
		$this->showinowner = false;

		$this->caption = 'RBK Money';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';

		$this->ver = '0.1';
		return true;
	}
	
	function _create_conf() {/*CONFIG*/
		//parent::_create_conf();

		$this->config['login'] = '';
		$this->config['password'] = '';
		$this->config['txn-prefix'] = '';
		$this->config['create-agt'] = 1;
		$this->config['lifetime'] = 1080;
		$this->config['minpay'] = 10;
		$this->config['maxpay'] = 15000;

		$this->config_form['info'] = array('type' => 'info', 'caption'=>'<h3>zpayment</h3><p>На сайте необходимо разместить логотип и описание(<a href="http://ishopnew.zpayment.ru/docs.html" target="_blank">материалы zpayment для сайта</a>)</p>');
		$this->config_form['login'] = array('type' => 'text', 'caption' => 'Логин', 'comment'=>'', 'style'=>'background-color:#2ab7ec;');
		$this->config_form['password'] = array('type' => 'password', 'md5'=>false, 'caption' => 'Пароль', 'style'=>'background-color:#2ab7ec;');
		$this->config_form['txn-prefix'] = array('type' => 'text', 'caption' => 'Префикс в номере счёта','comment'=>'', 'style'=>'background-color:#2ab7ec;');
		//$this->owner->config_form['create-agt'] = array('type' => 'text', 'caption' => 'Логин','comment'=>'Если 1 то при выставлении счёта создается пользователь в системе zpayment. При этом оплатить счёт можно в терминале наличными без ввода ПИН-кода.', 'style'=>'background-color:gray;');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут','comment'=>'Время жизни счёта по умолчанию. Задается в часах. Максимум 45 суток (1080 часов)', 'style'=>'background-color:#2ab7ec;');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2ab7ec;');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2ab7ec;');
	}

	protected function _create() {
		parent::_create();
		$this->fields['user_id'] = array('type' => 'int', 'width' => 8,'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL'); // в коппейках
		
		$this->pay_systems = array(
			'qiwi' => array(
				'caption' => 'Турминалы оплаты QIWI',
				'icon' => 'qiwi.gif',
			),
		);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['user_id'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Юзер', 'mask'=>array());
		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Цена (кооп.)', 'mask'=>array());
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата', 'mask'=>array());
		//$this->fields_form['mf_timeup'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата обновления', 'mask'=>array('fview'=>2));
		//$this->fields_form['mf_timeoff'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата отключения', 'mask'=>array('fview'=>2));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text','readonly'=>1, 'caption' => 'Дата', 'mask'=>array('fview'=>2));
	}

	function _pay() {
		global $_tpl;
		
//		$_tpl['onload'] = 'document.getElementById(\'pay\').submit();';
		
		$html = 'Пополнение баланса через платежную систему Z-payment<br/>После оплаты, на Ваш счет поступит '.$_POST['payment_amount'].' руб.';
		$html .= '<form id="pay" method="POST" action="https://z-payment.ru/merchant.php">'."\n";
		$html .= '<input name="LMI_PAYMENT_AMOUNT" type="hidden" value="'.$_POST['payment_amount'].'" size="10" maxlength="10">';
		$html .= '<input name="CLIENT_MAIL" type="hidden" value="">';
		$html .= '<input name="LMI_PAYMENT_DESC" type="hidden" value="Описание покупки">';
		$html .= '<input name="LMI_PAYEE_PURSE" type="hidden" value="">';

		$html .= '<input type="submit" value="Перейти к оплате">';
		
		return $html;
	}	
	
	function add_payment($amount, $status) {
		return $this->owner->add_payment($amount, $status, $this->_cl);
	}
	
	
}


