<?


class payrobox_class extends kernel_extends {
	
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Платежи Робо кассы';
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

		$this->config['mrh_login'] = 'rbxch';
		$this->config['mrh_pass1'] = 'testing123';
		$this->config['mrh_pass2'] = 'testing456';
		
		$this->config['in_curr'] = 'PCR';
		$this->config['culture'] = 'ru';
		
	}

	protected function _create() {
		parent::_create();
		
		$this->pay_systems = array(
			'WMZ' => array(
				'caption' => 'webmoney Z',
				'icon' => 'wmz.gif',
			),
			'WMU' => array(
				'caption' => 'webmoney U',
				'icon' => 'wmu.gif',
			),
		);

	}
	
	// возвращает массив с формой, которая отправит пользователя на сайт платежной системы
	function get_pay_form($system, $amount, $inv_id) {
		
		$crc = md5($this->config['mrh_login'].':'.$amount.':'.$inv_id.':'.$this->config['mrh_pass1']);
		
		$data['text_before'] = 'Пополнение баланса через Робокассу<br/>После оплаты, на Ваш счет поступит '.$amount.' рублей.';
		$data['text_after'] = '';
		
		$data['formcreat']['form'] = array(
			'_*features*_' => array(
				'method' => 'post',
				'name' => 'pay',
				'action' => 'http://test.robokassa.ru/Index.aspx',
			),
			'MrchLogin' => array(
				'value' => $this->config['mrh_login'],
				'type' => 'hidden',
			),
			'OutSum' => array(
				'value' => $amount,
				'type' => 'hidden',				
			),
			'InvId' => array(
				'value' => $inv_id,
				'type' => 'hidden',
			),
			'Desc' => array(
				'value' => $this->owner->config['desc'],
				'type' => 'hidden',
			),
			'SignatureValue' => array(
				'value' => $crc,
				'type' => 'hidden',
			),
			'IncCurrLabel' => array(
				'value' => $this->config['in_curr'],
				'type' => 'hidden',
			),
			'Culture' => array(
				'value' => $this->config['culture'],
				'type' => 'hidden',
			),
			'submit' => array(
				'value' => 'Перейти к оплате',
				'type' => 'submit',
			),
		);
		
		return $data;
	}
	
	
	// записывает в базу информацию о платеже
	// status=0 - платеж не осуществлен
	// status=1 - платеж осуществлен
	function add_payment($amount, $status) {
		return $this->owner->add_payment($amount, $status, $this->_cl);
	}
	

}

?>
