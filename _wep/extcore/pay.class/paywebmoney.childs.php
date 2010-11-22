<?

class paywebmoney_class extends kernel_class {
	
	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->caption = 'Платежи WebMoney';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->mf_timecr = true; // создать поле хранящще время создания поля
		$this->mf_timeup = true; // создать поле хранящще время обновления поля
		$this->mf_timeoff = true; // создать поле хранящще время отключения поля (active=0)
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->cf_childs = true;
		$this->ver = '0.1';
		return 0;
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
			'webmoneyR' => array(
				'caption' => 'webmoneyR',
				'icon' => 'wmr.gif',
			),
		);
	}
	
	function _pay() {
		global $_tpl;
		
		$_tpl['onload'] = 'document.getElementById(\'pay\').submit();';
		
		$html = 'Сейчас Вы будете перенаправлены на страницу оплаты';
		$html .= '<form id="pay" method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp">'."\n";
		$html .= '<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="'.$_POST['payment_amount'].'">';
		$html .= '<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="'.base64_encode('Пополнение пользовательского счета partner.ru').'">'."\n";
		$html .= '<input type="hidden" name="LMI_PAYEE_PURSE" value="R159322342129">'."\n";
		$html .= '<input type="hidden" name="LMI_MODE" value="1">'."\n";
		$html .= '<input type="hidden" name="LMI_SIM_MODE" value="0">'."\n";
		$html .= '<input type="submit" style="display:none;">';
		
		return $html;
	}	
	
	
}

?>
