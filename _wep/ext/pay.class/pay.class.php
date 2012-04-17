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
		$this->ver = '0.3.4';
		$this->default_access = '|0|';
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->index_fields['_key'] = '_key';
		$this->index_fields['user_id'] = 'user_id';
		$this->index_fields['status'] = 'status';
		$this->_AllowAjaxFn['payFormBilling'] = true;
		return true;
	}

	protected function _create_conf() {/*CONFIG*/
		$this->config['curr'] = 'руб.';
		$this->config_form['curr'] = array('type' => 'text', 'caption'=>'Название валюты');

		parent::_create_conf();
	}

	function _childs() { /*CONFIG*/
		parent::_childs();
		foreach($this->childs as &$r)
			$r->_create_conf2($this);
		$this->configParse();
	}


	protected function _create() {
		parent::_create();
		$this->fields['user_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'float', 'width' => '11,4', 'attr' => 'NOT NULL');
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL','default'=>1);
		$this->fields['pay_modul'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');
		$this->fields['_key'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL','default'=>'');
		$this->fields['_eval'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');

		$this->_enum['status'] = array(
			0 => 'Неоплаченный счёт',
			1 => 'Оплаченный счёт',
			2 => 'Счёт отклонён',
			3 => 'Счёт отменён',
			4 => 'Истекло время ожидания',
		);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users','nameField'=>'concat("№",tx.id," ",tx.name)'), 'readonly'=>1, 'caption' => 'От кого', 'comment'=>'От кого переведены средства', 'mask'=>array());
		$this->fields_form['user_id'] = array('type' => 'list', 'listname'=>array('class'=>'users','nameField'=>'concat("№",tx.id," ",tx.name)'), 'readonly'=>1, 'caption' => 'Кому', 'comment'=>'Кому переведены средства', 'mask'=>array());
		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Сумма', 'mask'=>array());
		$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Комментарий', 'mask'=>array());
		$this->fields_form['pay_modul'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Платежный модуль', 'mask'=>array());
		$this->fields_form['status'] = array('type' => 'list', 'listname'=>'status', 'readonly'=>1,'caption' => 'Статус', 'mask'=>array());
		
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата', 'mask'=>array());
		$this->fields_form['mf_ipcreate'] = array('type' => 'text','readonly'=>1, 'caption' => 'IP', 'mask'=>array('fview'=>2));

	}
	
	// Формы выставления счёта пользователю
	function billingFrom($summ, $key, $comm='',$eval='',$addInfo=array()) {
		global $_tpl;
		$data = array();

		//eval($eval);
		if(isset($_POST['paymethod']) and isset($this->childs[$_POST['paymethod']]) and isset($this->childs[$_POST['paymethod']]->pay_systems)) {

			$CHILD = &$this->childs[$_POST['paymethod']];

			$temp = $this->qs('id','WHERE status=0 and _key="'.$this->SqlEsc($key).'" and name="'.$this->SqlEsc($comm).'"');
			if(count($temp)) {
				$data['#title#'] = '';//Счёт на оплату выставлен.
				if($CHILD->pay_formType===true)
					$data['#title#'] .= '<a id="gotopay" href="/_js.php?_modul=pay&_fn=payFormBilling&id='.$temp[0]['id'].'" onclick="return wep.JSWin({type:this,onclk:\'reload\'});" target="_blank">Оплатить</a>';
				elseif($CHILD->pay_formType)
					$data['#title#'] .= '<a id="gotopay" href="'.$CHILD->pay_formType.'" target="_blank">Оплатить</a>';
				$resFlag = 1;
				$data['#foot#'] = '<div class="paySpanMess" onclick="window.location.reload();">Обновите страницу, чтобы узнать состояния счёта.</div>';
				$_tpl['onload'] .= '$("#gotopay").click();';
			} 
			else {
				list($data,$resFlag) = $CHILD->billingFrom($summ,$comm,$addInfo);
				if($resFlag==1) {
					$from_user = $this->checkPayUsers($_POST['paymethod']); // User плат. системы
					// тк это функция сразу оплачивает услуги, то сумму переводим сразу АДМИНУ и списываем со счета плат.системы
					if($this->payAdd($from_user,1,$summ, $key, $comm,0,$_POST['paymethod'],$eval)) {
						$this->childs[$_POST['paymethod']]->_update(array('owner_id'=>$this->id));
						$data['#title#'] = 'Счёт выставлен успешно!';
						// Открыть окно системы в новом окне
						$data['#foot#'] = '<span class="paySpanMess" onclick="window.location.reload();">Обновите страницу, чтобы узнать состояния счёта.</span>';
					} 
					else
						$data['#title#'] = 'Ошибка';
				}
				else {
					$data['#title#'] = 'Укажите необходимые данные';
				}
			}
			$data['#resFlag#'] = $resFlag;
		} else {
			// ADD pay
			foreach($this->childs as &$child) {
				if (isset($child->pay_systems)) {
					$data['child'][$child->_cl] = array('_cl'=>$child->_cl,'caption'=>$child->caption);
				}
			}
			$data['#title#'] = 'Выберите вариант оплаты';
		}

		$data['summ'] = $summ;
		$data['comm'] = $comm;
		$data['#currency#'] = $this->config['curr'];
		return $data;
	}

	// Функция вызова формы оплаты для систем работающих только через форму оплаты (не выставляя счёт)
	function payFormBilling() {
		global $_tpl,$HTML;
		$res = array('html'=>'Нет доступа к данным!');
		$this->id = (int)$_GET['id'];
		$pData = current($this->_select());
		if($pData['id']) {
			$this->childs[$pData['pay_modul']]->id = NULL;
			$pcData = current($this->childs[$pData['pay_modul']]->_select());
			if($pcData['id']) {
				$this->childs[$pData['pay_modul']]->id = $pcData['id'];
				$DATA = $this->childs[$pData['pay_modul']]->payFormBilling($pcData);
				$res = array('html'=>$HTML->transformPHP($DATA,'#pg#formcreat'), 'onload' => $_tpl['onload']);
			}
		}
		return $res;
	}

	/**
	* Функция оплаты и перевода средств
	* @param $from_user
	* @param $to_user
	* @param $summ
	* @param $key - ключ операции, для вывода потом и поиска
	* @param $mess - коммент
	* @param $status - 1 производит перевод средств сразу
	* return 1 - Успешно
	* return 0 - ошибка данных
	*/
	function payAdd($from_user,$to_user,$summ,$key,$mess='',$status=1,$pay_modul='',$eval='') {
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
			'pay_modul'=>$pay_modul,
			'_key'=>$key,
			'_eval'=>$eval,
		);
		return $this->_add($data);
	}


	/**
	* Проверяем есть ли группа и пользователи для системы платежей и возвращаем ID плат.сист. если указана плат.сист.
	*
	*/
	function checkPayUsers($paychild='',$flag=false) {
		_new_class('ugroup', $UGROUP);
		$id = 0;
		// 51 - уникальный код уровня доступа для этого модуля
		// Группа Платежные системы
		$data1 = $UGROUP->_query('*','WHERE level = "51"');
		if(count($data1)!=1) {
			$UGROUP->_add(array(
				'level'=>'51',
				'name'=>$this->caption,
				'wep'=>'0',
				'negative'=>'1',
			));
		}else
			$UGROUP->id = $data1[0]['id'];

		// Юзеры Платежные системы
		$data2 = $UGROUP->childs['users']->_query('*','WHERE owner_id = '.$UGROUP->id,'email');

		// юзер по умолчанию
		$email = 'pay@'.$this->_CFG['site']['www'];
		if(!isset($data2[$email])) {
			$UGROUP->childs['users']->_add(array(
				'email'=>$email,
				'name'=>'Pay',
				'owner_id'=>$UGROUP->id,
				'parent_id'=>0,
			));
			$id = $UGROUP->childs['users']->id;
		} else
			$id = $data2[$email]['id'];

		if(count($this->childs)) {
			foreach($this->childs as &$childs) {
				$email = $childs->_cl.'@'.$this->_CFG['site']['www'];
				if(!isset($data2[$email])) {
					$UGROUP->childs['users']->_add(array(
						'email'=>$email,
						'name'=>$childs->caption,
						'owner_id'=>$UGROUP->id,
						'parent_id'=>0,
					));
					$data2[$email] = current($UGROUP->childs['users']->data);
				}
				if($paychild==$childs->_cl)
					$id = $data2[$email]['id'];
			}
			unset($child);
		}
		if($flag and $paychild)
			return $data2[$paychild.'@'.$this->_CFG['site']['www']];
		return $id;
	}

	/**
	* В случае успешной оплаты, переводим средва му пользователями, ставим соотв. статус, выполняем необходимые операции
	*/
	function PayTransaction($status,$cost,$id) {
		$this->id = $id;
		if($status==1) {
			$data = current($this->_select());
			if(!count($data)) return false;
			_new_class('ugroup', $UGROUP);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance-'.$cost.' WHERE id='.$data[$this->mf_createrid]);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance+'.$cost.' WHERE id='.$data['user_id']);
			if($data['_eval']) {
				eval($data['_eval']);
			}
		} else {
		}
		$upd = array(
			'cost'=>$cost,
			'status'=>$status
		);
		$this->_update($upd);
		return true;
	}


	function displayListKey($key=NULL, $status=array()) {
		$data = array();
		$data['#curr#'] = $this->config['curr'];
		$q = 't1 WHERE id ';
		if(!is_null($key))
			$q .= 'and t1._key LIKE "'.$key.'"';
		if(count($status) and $status=implode(',',$status))
			$q .= 'and t1.status IN ('.$status.')';
		$data['#list#'] = $this->qs('t1.*', $q.' ORDER BY id DESC');
		foreach($data['#list#'] as &$r) {
			$r['#status#'] = $this->_enum['status'][$r['status']];
			$r['#pay_modul#'] = $this->childs[$r['pay_modul']]->caption;
			$cl = $this->childs[$r['pay_modul']]->_cl;
			$r['#lifetime#'] = $this->config[substr($cl,3).'_lifetime'];
			$r['#formType#'] = $this->childs[$r['pay_modul']]->pay_formType;
		}
		return $data;
	}

	// Список операций
	function displayListUser($user) {
		$data = array();
		$data['#curr#'] = $this->config['curr'];
		$where = 'WHERE (`'.$this->mf_createrid.'` = '.$user.' or `user_id` = '.$user.')';
		$data['#list#'] = $this->_query('*',$where);
		if(count($data['#list#'])) {
			$userlist = array();
			foreach($data['#list#'] as &$r) {
				if(!isset($userlist[$r['user_id']]))
					$userlist[$r['user_id']] = $r['user_id'];
				if(!isset($userlist[$r[$this->mf_createrid]]))
					$userlist[$r[$this->mf_createrid]] = $r[$this->mf_createrid];
				$r['#status#'] = $this->_enum['status'][$r['status']];
				if(isset($this->childs[$r['pay_modul']])) {
					$r['#pay_modul#'] = $this->childs[$r['pay_modul']]->caption;
					$r['#lifetime#'] = $this->config[substr($this->childs[$r['pay_modul']]->_cl,3).'_lifetime'];
					$r['#formType#'] = $this->childs[$r['pay_modul']]->pay_formType;
				}
			}
			_new_class('ugroup', $UGROUP);
			$data['#users#'] = $UGROUP->childs['users']->_query('t1.*,t2.level,t2.name as gname','t1 JOIN '.$UGROUP->tablename.' t2 ON t1.owner_id=t2.id WHERE t1.id IN ('.implode(',',$userlist).')','id');
		}
		return $data;
	}

/********************************************/
/********************************************/
/********************************************/

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
				$data['respost'] = array('flag'=>0,'mess'=>'Неверные данные.');
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
					$flag = $this->payAdd($u1,$u2,$summ,'move',$txt);
				}
				$data['respost'] = array('flag'=>$flag,'mess'=>$mess,'balance'=>$balance);
			}
		}

		_new_class('ugroup', $UGROUP);
		$query = 'WHERE '.$query;
		$data['users'] = $UGROUP->childs['users']->_query('t1.*,t2.name as gname','t1 JOIN '.$UGROUP->tablename.' t2 ON t1.owner_id=t2.id and t2.active=1 '.$query.' ORDER BY t1.id','id');

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
				if($this->payAdd($from_user,$to_user,$summ,'refill')) {
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
	
	// проверяет формат пополняемой суммы
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
			$flag = $this->payAdd($from_user,$_SESSION['user']['id'],$childData['cost'],'addMoney',$childData['name'],0,$paychild);
			if(!$flag) {
				$this->childs[$paychild]->_delete();
				$DATA['messages'] = static_main::am('error','Ошибка БД, платёж '.$this->childs[$paychild]->id.' анулирован.'); 
			} else {
				$this->childs[$paychild]->_update(array('owner_id'=>$this->id));
			}
		}
		return array($DATA,$flag);
	}


	/**
	* Сервис служба очистки данных
	* Отключает неоплаченные платежи 
	* @param $M - модуль платежной системы
	* @param $leftTime - в секундах
	*/
	function clearOldData($M, $leftTime, $dataUp) {
		$temp = $this->qs('id','WHERE status=0 and '.$this->mf_timecr.'<"'.(time()-$leftTime).'" and pay_modul="'.$M.'"','id');

		if(count($temp)) {
			$this->id = array_keys($temp);
			$this->childs[$M]->_update($dataUp);
			$this->_update(array('status'=>'4'));
		}
	}
}




