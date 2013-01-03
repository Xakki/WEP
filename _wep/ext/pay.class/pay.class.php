<?php

define('PAY_NOPAID',0);
define('PAY_PAID',1);
define('PAY_USERCANCEL',2);
define('PAY_CANCEL',3);
define('PAY_TIMEOUT',4);

class pay_class extends kernel_extends {

	function _set_features() {
		parent::_set_features();
		$this->caption = 'Pay System';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись		
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->cf_childs = true;
		$this->mf_notif = true;

		$this->ver = '0.6.6';
		$this->default_access = '|0|';
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->index_fields['_key'] = '_key';
		$this->index_fields['user_id'] = 'user_id';
		$this->index_fields['status'] = 'status';
		$this->_AllowAjaxFn['statusForm'] = true;
		$this->ordfield = 'id DESC';
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config['curr'] = 'руб.';
		$this->config['NDS'] = 13;

		$this->config_form['curr'] = array('type' => 'text', 'caption'=>'Название валюты');
		$this->config_form['NDS'] = array('type' => 'text', 'caption'=>'НДС %');

	}

	protected function _create() {
		parent::_create();
		$this->fields['user_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL');
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL','default'=>1);
		$this->fields['pay_modul'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');
		$this->fields['_key'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL','default'=>''); // product1234 = название модуля + ID
		$this->fields['_eval'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');

		$this->_enum['status'] = array(
			PAY_NOPAID => 'Неоплачено',
			PAY_PAID => 'Оплачено',
			PAY_USERCANCEL => 'Отменено пользователем',
			PAY_CANCEL => 'Отменено магазином',
			PAY_TIMEOUT => 'Истекло время ожидания',
		);
		// TODO !!!!
		$this->successUrl = $_SERVER['REQUEST_URI'];
		$this->failUrl = $_SERVER['REQUEST_URI'];

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users','nameField'=>'concat("№",tx.id," ",tx.name)'), 'readonly'=>1, 'caption' => 'От кого', 'comment'=>'От кого переведены средства', 'mask'=>array());
		$this->fields_form['user_id'] = array('type' => 'list', 'listname'=>array('class'=>'users','nameField'=>'concat("№",tx.id," ",tx.name)'), 'readonly'=>1, 'caption' => 'Кому', 'comment'=>'Кому переведены средства', 'mask'=>array());
		$this->fields_form['cost'] = array('type' => 'decimal', 'readonly'=>1, 'caption' => 'Сумма', 'mask'=>array());
		$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1,'caption' => 'Комментарий', 'mask'=>array());
		$this->fields_form['pay_modul'] = array('type' => 'list', 'listname'=>'pay_modul', 'readonly'=>1,'caption' => 'Платежный модуль', 'mask'=>array());
		$this->fields_form['status'] = array('type' => 'list', 'listname'=>'status', 'readonly'=>1,'caption' => 'Статус', 'mask'=>array());
		
		$this->fields_form[$this->mf_timecr] = array('type' => 'date','readonly'=>1, 'caption' => 'Создание', 'mask'=>array('onetd' => 'Дата'));
		$this->fields_form[$this->mf_timestamp] = array('type' => 'date','readonly'=>1, 'caption' => 'Обновление', 'mask'=>array('onetd' => 'close'));

		$this->fields_form['mf_ipcreate'] = array('type' => 'text','readonly'=>1, 'caption' => 'IP', 'mask'=>array());

	}

	function _getlist($listname, $value = 0) 
	{
		$data = array();
		if ($listname == 'pay_modul') {
			foreach ($this->childs as $key => &$value) {
				$data[$key] = $value->caption;
			}
			return $data;
		} 
		else
			return parent::_getlist($listname, $value);
	}

	/**
	* Список счетов вывод по ключу
	* @param $key string ключ для вывода счетов определенного товара(услуги)
	* @param $user int фильтр по пользователю
	* @param $status array фильтр по статусам 
	*/
	public function getList($key=NULL, $user=NULL, $status=array()) 
	{
		$data = array('#config#' => $this->config['curr']);
		$q = 't1 WHERE 1=1 ';

		if(!is_null($key))
			$q .= ' and t1._key LIKE "'.$key.'"';

		if(!is_null($user))
			$q .= ' and (t1.`'.$this->mf_createrid.'` = '.$user.' or t1.`user_id` = '.$user.')';

		if(count($status) and $status=implode(',',$status))
			$q .= ' and t1.status IN ('.$status.')';

		$data['#list#'] = $this->qs('t1.*', $q.' ORDER BY t1.id DESC');

		$userlist = array();
		foreach($data['#list#'] as &$r) 
		{
			$r['#sign#'] = ($user==$r['user_id']?true:false);
			if(!isset($userlist[$r['user_id']]))
				$userlist[$r['user_id']] = $r['user_id'];
			if(!isset($userlist[$r[$this->mf_createrid]]))
				$userlist[$r[$this->mf_createrid]] = $r[$this->mf_createrid];

			$r['#status#'] = $this->_enum['status'][$r['status']];
			if(isset($this->childs[$r['pay_modul']])) 
			{
				$r['#pay_modul#'] = $this->childs[$r['pay_modul']]->caption;
				$r['#lifetime#'] = $this->childs[$r['pay_modul']]->config['lifetime'];
				$r['#formType#'] = $this->childs[$r['pay_modul']]->pay_formType;
			}
		}

		if(count($userlist))
		{
			_new_class('ugroup', $UGROUP);
			$data['#users#'] = $UGROUP->childs['users']->_query('t1.*,t2.level,t2.name as gname','t1 JOIN '.$UGROUP->tablename.' t2 ON t1.owner_id=t2.id WHERE t1.id IN ('.implode(',',$userlist).')','id');
		}
		return $data;
	}


	/**
	* Формы выставления счёта пользователю
	*
	*/
	public function billingForm($summ, $key, $comm='', $eval='', $addInfo=array()) 
	{
		global $_tpl;
		$data = array();

		$resFlag = 0; 
		// 0 : выводим варианты оплаты
		// -1 : нет прав доступа
		// -2 : ошибка в запросе
		// -3 : прочие ошибки

		//eval($eval);
		if( $this->isPayModul($_POST['paymethod']) ) 
		{
			// Если есть уже такой счет и он еще не оплачен, то информацию/форму для оплаты счета
			if($id = $this->getIdBill($summ, $key, $comm)) 
			{
				$data = $this->showPayForm($id);
				$resFlag = 1;
			} 
			else 
			{
				$CHILD = &$this->childs[$_POST['paymethod']];
				list($data, $resFlag) = $CHILD->billingForm($summ,$comm,$addInfo);
				if($resFlag==1) 
				{
					$from_user = $this->checkPayUsers($_POST['paymethod']); // User плат. системы
					if($this->payAdd($from_user,1,$summ, $key, $comm,0,$_POST['paymethod'],$eval)) 
					{
						return $this->statusForm($this->id);
					} 
					else
						$resFlag = -3;

				}
				else 
				{
					$resFlag = -2;
				}
			}
			
		}
		else 
		{
			// ADD pay
			foreach($this->childs as &$child) 
			{
				if (isset($child->pay_systems)) 
				{
					$data['child'][$child->_cl] = array('_cl'=>$child->_cl,'caption'=>$child->caption);
				}
			}
			$data['#summ#'] = $summ;
			$data['#comm#'] = $comm;
			//$data['messages'][] = array('info','Выберите вариант оплаты.');
		}
		
		$data['#resFlag#'] = $resFlag;
		$data['#config#'] = $this->config;
		$data['tpl'] = '#pay#billingForm';
		print_r('<pre>');print_r($data);
		return $data;
	}
	
	/**
	* Получить информацию
	*
	*/
	function statusForm($id=null) 
	{
		$result = array('#resFlag#'=>-1);
		if(is_null($id))
			$id = (int)$_GET['id'];

		$data = $this->getItem($id);

		if(count($data))
		{
			$CHILD = &$this->childs[$data['pay_modul']];
			$result = $CHILD->statusForm($data);
			if(isset($result['showStatus']) and $result['showStatus']===true)
			{
				$result['showStatus'] = $data;
			}
			$result['#config#'] = $this->config;
			$result['#resFlag#'] = 0;
		}
		$result['tpl'] = '#pay#statusForm';
		return $result;
	}

	/**
	* Получить данные 
	*
	*/
	public function getItem($id, $checkPermition=true)
	{
		if(!$id) return array();

		$sql = 'WHERE id="'.(int)$id.'"';
		if($checkPermition)
		{
			if($checkPermition===true)
				$checkPermition = $_SESSION['user']['id'];
			$sql .= ' and (`'.$this->mf_createrid.'` = '.$checkPermition.' or `user_id` = '.$checkPermition.')';
		}
		$data = $this->qs('*',$sql);

		if(count($data))
		{
			$data = $data[0];
			$data['#status#'] = $this->_enum['status'][$data['status']];
			$CHILD = $this->childs[$data['pay_modul']];

			if($CHILD->pay_formType===true)
				$data['#payLink#'] = '/_js.php?_modul=pay&_fn=statusForm&id='.$payData['id'].'" onclick="return wep.JSWin({type:this,onclk:\'reload\'});';
			elseif($CHILD->pay_formType)
				$data['#payLink#'] = $CHILD->pay_formType;

			$child = $CHILD->qs('*','WHERE owner_id="'.(int)$id.'"');
			$data['child'] = $child[0];
			return $data;
		}

		return array();
	}

	/*********************************************/
	/********************************************/

	/*function billingStatusForm(&$payData, &$CHILD)
	{
		$data = array();
		$data = $CHILD->billingStatusForm($payData);
		$data['#title#'] = '';//Счёт на оплату выставлен.

		if($CHILD->pay_formType===true)
			$data['#payLink#'] = '/_js.php?_modul=pay&_fn=statusForm&id='.$payData['id'].'" onclick="return wep.JSWin({type:this,onclk:\'reload\'});';
		elseif($CHILD->pay_formType)
			$data['#payLink#'] = $CHILD->pay_formType;
		
		//$data['#item#'] = $payData;
		$data['#status#'] = $this->_enum['status'][$payData['status']];
		$data['status'] = $payData['status'];
		return $data;
	}


	// Функция вызова формы оплаты для систем работающих только через форму оплаты (не выставляя счёт)
	function showPayInfo() {
		global $_tpl,$HTML;
		$res = array('html'=>'Нет доступа к данным!');
		$this->id = (int)$_GET['id'];
		$pData = current($this->_select());
		if($pData['id']) {
			$this->childs[$pData['pay_modul']]->id = NULL;
			$pcData = current($this->childs[$pData['pay_modul']]->_select());
			if($pcData['id']) 
			{
				$DATA = $this->childs[$pData['pay_modul']]->payFormBilling($pcData);
				$res = array('html'=>$HTML->transformPHP($DATA,'#pg#formcreat'), 'onload' => $_tpl['onload']);
			}
		}
		return $res;
	}*/



	// Проверяем, включен ли указанный платежный модуль
	public function isPayModul($paymethod)
	{
		if($paymethod and isset($this->childs[$paymethod]) and isset($this->childs[$paymethod]->pay_systems)) 
			return true;
		return false;
	}

	// Если есть уже такой счет и он еще не оплачен, то выводим информацю о нем
	public function getIdBill($summ, $key, $comm)
	{
		$sql = 'WHERE _key="'.$this->SqlEsc($key).'" and name="'.$this->SqlEsc($comm).'" and status=0';
		/*if(isset($_SESSION['user']['id']))
			$sql .= ' AND user_id='.$_SESSION['user']['id'];*/
		$payData = $this->qs('id', $sql);// status=0 and - один платеж
		if(!count($payData))
			return 0;
		return $payData[0]['id'];
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

		$res = $this->_add($data, false);

		/*if($res)
			$CHILD->_update(array('owner_id'=>$this->id));*/

		return $res;
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
				'negative'=>'-1000000000',
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
	function payTransaction($id, $status) 
	{
		$this->id = $id;
		$data = current($this->_select());

		if(!count($data)) return false;

		if($status==1) {
			
			_new_class('ugroup', $UGROUP);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance-'.$data['cost'].' WHERE id='.$data[$this->mf_createrid]);
			$this->SQL->execSQL('UPDATE '.$UGROUP->childs['users']->tablename.' SET balance=balance+'.$data['cost'].' WHERE id='.$data['user_id']);
			if($data['_eval']) {
				eval($data['_eval']);
			}
		} 
		/*else 
		{
		}*/
		$upd = array(
			'status'=>$status
		);
		return $this->_update($upd);
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
		$refer = NULL;
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
						list($flag, $mess, $refer) = call_user_func_array($functOK,$paramOK);
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
		if(!$refer) 
			$refer = array('Вернуться в корзину', $_SERVER['HTTP_REFERER']);
		$DATA = array(
			'flag'=>$flag,
			'mess'=>$mess,
			'balance'=>$balance,
			'code'=>$uq,
			'summ'=>$summ,
			'm'=>$UGROUP->config['payon'],
			'to_user'=>$to_user,
			'#post#' => $_POST,
			'#refer#' => $refer,
		);
		return $DATA;
	}

	/**
	* Функция проверки средств
	*/
	function checkBalance($from_user,$summ) {
		_new_class('ugroup', $UGROUP);
		$temp = $UGROUP->childs['users']->_query('t1.id,t1.owner_id,t1.name,t1.balance,t2.negative','t1 JOIN '.$UGROUP->tablename.' t2 ON t2.id=t1.owner_id WHERE t1.`active`=1 and t2.`active`=1 and t1.`id` = '.$from_user);
		
		if(!count($temp)) return array(static_main::m('pay_nouser', $this), 0);
		$d = $temp[0]['balance']-$summ;
		$mess = '';
		if($temp[0]['balance']<0)
			$mess = static_main::m('pay_nobalance',array($temp[0]['balance'].' '.$UGROUP->config['payon']), $this);
		elseif($temp[0]['negative'] and $d<0) {
			$def = ($temp[0]['negative']+$d);
			if($def<0)
				$mess = static_main::m('pay_nomonney',array($def.' '.$UGROUP->config['payon']), $this);
		}
		elseif($d<0) 
			$mess = static_main::m('pay_nomonney',array(abs($d).' '.$UGROUP->config['payon']), $this);
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
	function clearOldData($M, $leftTime) {
		$this->_update(array('status'=>'4'), 'status=0 and '.$this->mf_timecr.'<"'.(time()-$leftTime).'" and pay_modul="'.$M.'"');
	}


}




