<?php
class pay_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Pay System';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись		
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->cf_childs = true;
		$this->ver = '0.2.1';
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
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL','default'=>1);
		$this->fields['pay_modul'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');

		$this->_enum['status'] = array(
			0 => 'Неоплаченный счёт',
			1 => 'Оплаченный счёт',
			2 => 'Счёт отклонён'
		);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users'), 'readonly'=>1, 'caption' => 'От кого', 'comment'=>'От кого переведены средства', 'mask'=>array());
		$this->fields_form['user_id'] = array('type' => 'list', 'listname'=>array('class'=>'users'), 'readonly'=>1, 'caption' => 'Кому', 'comment'=>'Куму переведены средства', 'mask'=>array());
		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Деньга', 'mask'=>array());
		$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Комментарий', 'mask'=>array());
		$this->fields_form['pay_modul'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Платежный модуль', 'mask'=>array());
		$this->fields_form['status'] = array('type' => 'list', 'listname'=>'status', 'readonly'=>1,'caption' => 'Статус', 'mask'=>array());
		
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
		
		if(isset($param['POST'])) {
			$param['POST']['pay'] = (int)$param['POST']['pay'];
			$param['POST']['users'] = (int)$param['POST']['users'];
			if(!$param['POST']['pay']) {
				$data['respost'] = array('flag'=>0,'mess'=>'Не верные данные.');
			}
			else {
				if(isset($param['POST']['plus'])){
					$u1 = $_SESSION['user']['id'];
					$u2 = (int)$param['POST']['users'];
					$txt = 'Пополнение баланса';
				}
				else {
					$u2 = $_SESSION['user']['id'];
					$u1 = (int)$param['POST']['users'];
					$txt = 'Снятие со счёта';
				}
				if(isset($param['POST']['name']))
					$txt .= ': '.$param['POST']['name'];
				$summ = (int)$param['POST']['pay'];
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
					$mess = static_main::m('pay_err',$this);
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
		
		if(!count($temp)) return array(static_main::m('pay_nouser',$this),0);
		$d = $temp[0]['balance']-$summ;
		$mess = '';
		if(!$temp[0]['negative'] and $d<0) 
			$mess = static_main::m('pay_nomonney',array(abs($d).' '.$UGROUP->config['payon']),$this);
		return array($mess,$temp[0]['balance']);
	}

	/**
	* Функция оплаты и перевода средств
	* @param $from_user
	* @param $to_user
	* @param $summ
	* @param $mess - коммент
	* @param $status - 1 производит перевод средств сразу
	* return 1 - Успешно
	* return 0 - ошибка данных
	*/
	function pay($from_user,$to_user,$summ,$mess='',$status=1,$pay_modul='') {
		if($status==1) {
			_new_class('ugroup', $UGROUP);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance-'.$summ.' WHERE id='.$from_user);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance+'.$summ.' WHERE id='.$to_user);
		}
		$data = array(
			$this->mf_createrid=>$from_user,
			'user_id'=>$to_user,
			'cost'=>$summ,
			'name'=>$mess,
			'status'=>$status,
			'pay_modul'=>$pay_modul
		);
		return $this->_add($data);
	}

	private function payBack($from_user,$to_user,$summ) {
		_new_class('ugroup', $UGROUP);

		$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance+'.$summ.' WHERE id='.$from_user);
		$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance-'.$summ.' WHERE id='.$to_user);

		return $this->_delete();
	}

	// Коммент для платежа
	private function addPayMess($mess) {
		if(!$this->id) return 0;
		$data = array(
			'name'=>$mess);
		return $this->_update($data);
	}


	// Список операций
	function diplayList($user) {
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
				if ($this->_add($data)) {
					return $this->id;
				} else {
					return false;
				}

			break;
			case 1:
				$data = array(
					'status' => 1,
				);
				if ($this->_update($data)) {
					return $this->id;
				} else {
					return false;
				}				
			break;
			default:
				trigger_error('Передан неизвестный пар-р status', E_USER_WARNING);
		}
			
	}

	/**
	* Проверяем если группа и пользователи для счиистемы платежей
	*
	*/
	function checkPayUsers($paychild='') {
		_new_class('ugroup', $UGROUP);
		$id = 0;
		// Группа Платежные системы
		$data1 = $UGROUP->_query('*','WHERE name = "'.$this->caption.'"');
		if(count($data1)!=1) {
			$UGROUP->_add(array(
				'level'=>'-1',
				'name'=>$this->caption,
				'wep'=>'0',
				'negative'=>'1',
			));
		}else
			$UGROUP->id = $data1[0]['id'];

		// Юзеры Платежные системы
		$data2 = $UGROUP->childs['users']->_query('*','WHERE owner_id = '.$UGROUP->id,'email');

		// юзер по умолчанию
		$email = 'pay_block@'.$_SERVER['HTTP_HOST'];
		if(!isset($data2[$email])) {
			$UGROUP->childs['users']->_add(array(
				'email'=>$email,
				'name'=>'Pay',
				'owner_id'=>$UGROUP->id
			));
			$id = $UGROUP->childs['users']->id;
		} else
			$id = $data2[$email]['id'];

		if(count($this->childs)) {
			foreach($this->childs as &$childs) {
				$email = $childs->_cl.'@'.$_SERVER['HTTP_HOST'];
				if(!isset($data2[$email])) {
					$UGROUP->childs['users']->_add(array(
						'email'=>$email,
						'name'=>$childs->caption,
						'owner_id'=>$UGROUP->id
					));
					$data2[$email] = current($UGROUP->childs['users']->data);
				}
				if($paychild==$childs->_cl)
					$id = $data2[$email]['id'];
			}
			unset($child);
		}
		return $id;
	}
	
	/*
	*
	*
	*/
	function addMoney($paychild,$comment) {
		$param = array('errMess'=>true);
		$this->childs[$paychild]->prm_add = true;
		$this->id = 0;
		$_POST['name'] = $comment;
		list($DATA,$flag) = $this->childs[$paychild]->_UpdItemModul($param);
		unset($DATA['form']['name']);
		if($flag==1) {
			$from_user = $this->checkPayUsers($paychild);
			$childData = $this->childs[$paychild]->data[$this->childs[$paychild]->id];
			$flag = $this->pay($from_user,$_SESSION['user']['id'],$childData['cost'],$childData['name'],0,$paychild);
			if(!$flag) {
				$this->childs[$paychild]->_delete();
				$DATA['messages'] = static_main::am('error','Ошибка БД, платёж '.$this->childs[$paychild]->id.' анулирован.'); 
			} else {
				$this->childs[$paychild]->_update(array('owner_id'=>$this->id));
			}
		}
		return array($DATA,$flag);
	}

	function PayTransaction($status,$cost,$id) {
		$this->id = $id;
		if($status==1) {
			$data = $this->_select();
			$data = $data[$id];
			_new_class('ugroup', $UGROUP);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance-'.$cost.' WHERE id='.$data[$this->mf_createrid]);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance+'.$cost.' WHERE id='.$data['user_id']);
		} else {
		}
		$upd = array(
			'cost'=>$cost,
			'status'=>$status
		);
		$this->_update($upd);
		return true;
	}


}




