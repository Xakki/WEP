<?
class pay_class extends kernel_class {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Платежи';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->_setnamefields=false;
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_timeoff = true; // создать поле хранящее время отключения поля (active=0)
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись		
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->cf_childs = true;
		$this->icon = '12';
		$this->ver = '0.1';
		return true;
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		$this->config_form['childs']['caption'] = 'Подключенные модули платежных систем';
		
		$this->config['desc'] = 'Пополнение партнерского счёта partner.ru';
		
		$this->config_form['desc'] = array('type' => 'text', 'mask' =>array(), 'caption' => 'Описание денежного перевода');
	}

	protected function _create() {
		parent::_create();
		$this->fields['user_id'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL'); // в копейках
		$this->fields['pay_modul'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL'); // в копейках
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL'); // в копейках

		$this->fields_form['user_id'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Юзер', 'mask'=>array());
		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Цена (коп.)', 'mask'=>array());
		$this->fields_form['pay_modul'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Платежный модуль', 'mask'=>array());
		$this->fields_form['status'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Статус', 'mask'=>array());
		
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата', 'mask'=>array());
		//$this->fields_form['mf_timeup'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата обновления', 'mask'=>array('fview'=>2));
		//$this->fields_form['mf_timeoff'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата отключения', 'mask'=>array('fview'=>2));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text','readonly'=>1, 'caption' => 'Дата', 'mask'=>array('fview'=>2));
		
		$this->locallang['default']['wrong_pay_method'] = 'Данного способа оплаты не существует';
		$this->locallang['default']['wrong_pay_amount'] = 'Неверно указана цена';
	}

	// возвращает список платежных систем
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
	
	// проверяет формат пополняемой суммы в рублях
	function check_amount($amount) {
		if (is_numeric($amount) && $amount>0) {
			$dot_pos = strpos($amount, '.');
			if ($dot_pos == true) {
				if ((strlen($amount)-$dot_pos-1)<=2)
					return true;
				else
					return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	// записывает в базу информацию о платеже
	// status=0 - платеж не осуществлен
	// status=1 - платеж осуществлен
	function add_payment($amount, $status, $pay_modul) {
		
		$amount = $amount*100;

		switch($status)
		{
			case 0:
				$data = array(
					'user_id' => $_SESSION['user']['id'],
					'cost' => $amount,
					'pay_modul' => $pay_modul,
					'status' => 0,
				);
				if ($this->_add_item($data)) {
					return $this->SQL->sql_id();
				} else {
					return false;
				}

			break;
			case 1:
				$data = array(
					'status' => 1,
				);
				if ($this->_save_item($data)) {
					return $this->id;
				} else {
					return false;
				}				
			break;
			default:
				trigger_error('Передан неизвестный пар-р status', E_USER_WARNING);
		}
			
	}
	
}



?>
