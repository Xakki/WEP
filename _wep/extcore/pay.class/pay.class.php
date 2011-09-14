<?
class pay_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Pay stream';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись		
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->cf_childs = true;
		$this->ver = '0.2';
		$this->default_access = '|0|';
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		return true;
	}

	protected function _create_conf() { /*CONFIG*/
		parent::_create_conf();
	}

	protected function _create() {
		parent::_create();
		$this->fields['user_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'float', 'width' => '11,4', 'attr' => 'NOT NULL');
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		//$this->fields['status'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL');
		//$this->fields['pay_modul'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL');
	}

	public function setFieldsForm() {
		parent::setFieldsForm();

		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users'), 'readonly'=>1, 'caption' => 'От кого', 'comment'=>'От кого переведены средства', 'mask'=>array());
		$this->fields_form['user_id'] = array('type' => 'list', 'listname'=>array('class'=>'users'), 'readonly'=>1, 'caption' => 'Кому', 'comment'=>'Куму переведены средства', 'mask'=>array());
		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Деньга', 'mask'=>array());
		$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Комментарий', 'mask'=>array());
		//$this->fields_form['pay_modul'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Платежный модуль', 'mask'=>array());
		//$this->fields_form['status'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Статус', 'mask'=>array());
		
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата', 'mask'=>array());
		//$this->fields_form['mf_timeup'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата обновления', 'mask'=>array('fview'=>2));
		//$this->fields_form['mf_timeoff'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата отключения', 'mask'=>array('fview'=>2));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text','readonly'=>1, 'caption' => 'IP', 'mask'=>array('fview'=>2));

	}

	/** Форма перевода средств
	* 
	*/
	function payMove($param=array()) {
		$data = array();

		$query = 't1.`id` != '.$_SESSION['user']['id'].' and t1.`active`=1';
		if(isset($param['cls']))
			$query .= $param['cls'];
		
		if(count($_POST)) {
			$_POST['pay'] = (int)$_POST['pay'];
			$_POST['users'] = (int)$_POST['users'];
			if(!$_POST['pay']) {
				$data['respost'] = array('flag'=>0,'mess'=>'Не верные данные.');
			}
			else {
				if(isset($_POST['plus'])){
					$u1 = $_SESSION['user']['id'];
					$u2 = (int)$_POST['users'];
					$txt = 'Пополнение баланса';
				}
				else {
					$u2 = $_SESSION['user']['id'];
					$u1 = (int)$_POST['users'];
					$txt = 'Снятие со счёта';
				}
				if(isset($_POST['name']))
					$txt .= ': '.$_POST['name'];
				$summ = (int)$_POST['pay'];
				$flag = 0;
				list($mess,$balance) = $this->checkBalance($u1,$summ);
				if(!$mess) {
					$flag = $this->pay($u1,$u2,$summ,$txt);
				}
				$data['respost'] = array('flag'=>$flag,'mess'=>$mess,'balance'=>$balance);
			}
		}

		_new_class('ugroup', $UGROUP);
		$query = 'WHERE '.$query;
		$data['users'] = $UGROUP->childs['users']->_query('t1.*,t2.name as gname','t1 JOIN '.$UGROUP->tablename.' t2 ON t1.owner_id=t2.id and t2.active=1 '.$query,'id');

		return $data;
	}

	/**
	* $flag = -1 - вывод форма подтверждения
	* $flag = 0 - ошибка
	* $flag = 1 - успешная оплата
	*/

	function payDialog($from_user,$to_user,$summ,$functOK=NULL,$paramOK=array()) {
		$flag = 0;//Ошибка
		$mess = '';
		$uq = md5($from_user.$to_user.$summ);
		$n = 'paycode'.$uq;
		list($mess,$balance) = $this->checkBalance($from_user,$summ);
		if(isset($_SESSION[$n]) and isset($_POST[$uq])) {
			//Действие
			unset($_SESSION[$n]);
			list($mess,$balance) = $this->checkBalance($from_user,$summ);
			if($mess=='') {
				if($this->pay($from_user,$to_user,$summ)) {
					$flag = 1;//Оплата
					// Выполняем пользовательскую функцию при успешной оплате
					if(!is_null($functOK)) {
						list($flag,$mess) = call_user_func_array($functOK,$paramOK);
						if($flag==1) {
							$this->addPayMess($mess);
						}
						else {
							$this->payBack($from_user,$to_user,$summ);
						}
					}
				}
				else
					$mess = $this->getMess('pay_err');
			}
		}
		else {
			$flag = -1;//Выводим форму подтверждения
			$_SESSION[$n] = true;
		}
		_new_class('ugroup', $UGROUP);
		$DATA = array(
			'flag'=>$flag,
			'mess'=>$mess,
			'balance'=>$balance,
			'code'=>$uq,
			'summ'=>$summ,
			'm'=>$UGROUP->config['payon'],
			'to_user'=>$to_user,
			'#post#' => $_POST,
		);
		return $DATA;
	}
	/**
	* Функция проверки средств
	*/
	function checkBalance($from_user,$summ) {
		_new_class('ugroup', $UGROUP);
		$temp = $UGROUP->childs['users']->_query('t1.id,t1.owner_id,t1.name,t1.balance,t2.negative','t1 JOIN '.$UGROUP->tablename.' t2 ON t2.id=t1.owner_id WHERE t1.`active`=1 and t2.`active`=1 and t1.`id` = '.$from_user);
		
		if(!count($temp)) return array($this->getMess('pay_nouser'),0);
		$d = $temp[0]['balance']-$summ;
		$mess = '';
		if(!$temp[0]['negative'] and $d<0) 
			$mess = $this->getMess('pay_nomonney',array(abs($d).' '.$UGROUP->config['payon']));
		return array($mess,$temp[0]['balance']);
	}
	/**
	* Функция оплаты и перевода средств
	* returт 1 - Успешно
	* returт 0 - ошибка данных
	*/
	function pay($from_user,$to_user,$summ,$mess='') {
		_new_class('ugroup', $UGROUP);

		$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance-'.$summ.' WHERE id='.$from_user);
		$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance+'.$summ.' WHERE id='.$to_user);
		$data = array(
			$this->mf_createrid=>$from_user,
			'user_id'=>$to_user,
			'cost'=>$summ,
			'name'=>$mess);
		return $this->_add_item($data);
	}

	private function payBack($from_user,$to_user,$summ) {
		_new_class('ugroup', $UGROUP);

		$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance+'.$summ.' WHERE id='.$from_user);
		$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance-'.$summ.' WHERE id='.$to_user);

		return $this->_delete();
	}

	private function addPayMess($mess) {
		if(!$this->id) return 0;
		$data = array(
			'name'=>$mess);
		return $this->_save_item($data);
	}

	function diplayList($user) { // Список операций
		$data = array();
		$where = 'WHERE (`'.$this->mf_createrid.'` = '.$user.' or `user_id` = '.$user.')';
		$data['#list#'] = $this->_query('*',$where);
		if(count($data['#list#'])) {
			$userlist = array();
			foreach($data['#list#'] as $k=>$r) {
				if(!isset($userlist[$r['user_id']]))
					$userlist[$r['user_id']] = $r['user_id'];
				if(!isset($userlist[$r[$this->mf_createrid]]))
					$userlist[$r[$this->mf_createrid]] = $r[$this->mf_createrid];
			}
			_new_class('ugroup', $UGROUP);
			$data['#users#'] = $UGROUP->childs['users']->_query('name,firma,id,balance','WHERE id IN ('.implode(',',$userlist).')','id');
		}
		return $data;
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




