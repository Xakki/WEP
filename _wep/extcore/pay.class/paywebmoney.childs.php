<?

class paywebmoney_class extends kernel_extends {
	
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Платежи WebMoney';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->mf_timecr = true; // создать поле хранящще время создания поля
		$this->mf_timeup = true; // создать поле хранящще время обновления поля
		$this->mf_timeoff = true; // создать поле хранящще время отключения поля (active=0)
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->cf_childs = true;
		$this->ver = '0.1';
		return true;
	}
	
	protected function _create_conf() {
		parent::_create_conf();

		$this->config['WMR'] = 'R385104050920';
		$this->config['WMZ'] = 'Z750445485014';
		$this->config['WMU'] = 'U879987000333';
		$this->config['WME'] = 'E841076953303';
		
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
			'WMR' => array(
				'caption' => 'webmoney R',
				'icon' => 'wmr.gif',
			),
			
		);
	}
	
	
	// возвращает массив с формой, которая отправит пользователя на сайт платежной системы
	function get_pay_form($system, $amount, $inv_id) {
		
		$desc = $this->owner->config['desc'];
		$lmi_payee_purse = $this->config[$_POST['payment_system']];
		$caption = $this->pay_systems[$_POST['payment_system']]['caption'];
		
		$crc = md5($this->config['mrh_login'].':'.$amount.':'.$inv_id.':'.$this->config['mrh_pass1']);
		
		$data['text_before'] = 'Пополнение баланса через систему '.$caption.'<br/>После оплаты, на Ваш счет поступит '.$amount.' рублей.';
		$data['text_after'] = '';
	
	
		$data['formcreat']['form'] = array(
			'_*features*_' => array(
				'method' => 'post',
				'name' => 'pay',
				'action' => 'https://merchant.webmoney.ru/lmi/payment.asp',
				'enctype' => '',
			),
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
	
	function add_payment($amount, $status) {
		return $this->owner->add_payment($amount, $status, $this->_cl);
	}
	
	
}


