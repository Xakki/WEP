<?

class payrobox_class extends kernel_class {
	
	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->caption = 'Платежи Робо кассы';
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
			
		);

	}
	
	function _pay() {
		return 'Оплата через Робо кассу';
	}
	

}

?>
