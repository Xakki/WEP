<?
class pay_class extends kernel_class {

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->caption = 'Платежи';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_timeoff = true; // создать поле хранящее время отключения поля (active=0)
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->cf_childs = true;
		$this->ver = '0.1';
		return 0;
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		$this->config_form['childs']['caption'] = 'Подключенные модули платежных систем';
		
		$this->config['desc'] = 'Пополнение партнерского счёта partner.ru';
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
	}

	function get_pay_systems()
	{
		$ps = array();
		foreach ($this->childs as $k=>$v) {
			if (isset($v->pay_systems)) {
				$ps[$k] = $v->pay_systems;
			}
		}
		return $ps;
	}
	
}



?>
