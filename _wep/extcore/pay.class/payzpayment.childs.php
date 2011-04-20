<?

class payzpayment_class extends kernel_extends {
	
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Платежи Z-payment';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->mf_timecr = true; // создать поле хранящще время создания поля
		$this->mf_timeup = true; // создать поле хранящще время обновления поля
		$this->mf_timeoff = true; // создать поле хранящще время отключения поля (active=0)
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->cf_childs = true;
		$this->ver = '0.1';
		return true;
	}

	protected function _create() {
		parent::_create();
		$this->fields['user_id'] = array('type' => 'int', 'width' => 8,'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL'); // в коппейках

		$this->fields_form['user_id'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Юзер', 'mask'=>array());
		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Цена (кооп.)', 'mask'=>array());
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата', 'mask'=>array());
		//$this->fields_form['mf_timeup'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата обновления', 'mask'=>array('fview'=>2));
		//$this->fields_form['mf_timeoff'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата отключения', 'mask'=>array('fview'=>2));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text','readonly'=>1, 'caption' => 'Дата', 'mask'=>array('fview'=>2));
		
		$this->pay_systems = array(
			'qiwi' => array(
				'caption' => 'Турминалы оплаты QIWI',
				'icon' => 'qiwi.gif',
			),
		);
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

?>
