<?php


class paybank_class extends kernel_extends {

	function _set_features() {
		parent::_set_features();
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->pay_systems = true; // Это модуль платёжной системы
		//$this->showinowner = false;
		$this->pay_formType = true; // Оплата производится по форме

		$this->caption = 'Безналичный расчёт';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->ver = '0.1';

		$this->_AllowAjaxFn['printBill'] = true;

		$this->lang['add_name'] = 'Пополнение кошелька безналичным расчетом';
		$this->lang['Save and close'] = 'Выписать счёт';
		$this->lang['add_err'] = 'Ошибка выставление счёта. Обратитесь к администратору сайта.';
		$this->lang['add'] = 'Счёт на оплату сформировано<br/> Распечатайте квитанцию и оплатите его в ближайшем отделении банка.';

		$this->_enum['statuses'] = array(
			0 => 'Неоплаченный счёт',
			10 => 'Проводится',
			20 => 'Оплаченный счёт',
			30 => 'Отменен клиентом',
			31 => 'Отменено продавцом',
			32 => 'Отменен (Истекло время)',
		);

	}

	function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config['bank_prefix'] = 'НФ-';
		$this->config['bank_namefirm'] = 'ООО "Рога и Копыта"';
		$this->config['bank_INN'] = '';
		$this->config['bank_KPP'] = '';
		$this->config['bank_nomer'] = '';
		$this->config['bank_namebank'] = '';
		$this->config['bank_BIK'] = '';
		$this->config['bank_KC'] = '';
		$this->config['bank_info'] = '';
		$this->config['bank_firmaddress'] = '';
		$this->config['bank_firmcontact'] = '';
		$this->config['minpay'] = '';
		$this->config['maxpay'] = '';
		$this->config['lifetime'] = 720;

		$this->config_form['i'] = array('type' => 'info', 'caption'=>'<h3>Оплата безналичным расчетом</h3>');
		$this->config_form['bank_prefix'] = array('type' => 'text', 'caption' => 'Префикс номера счёта');
		$this->config_form['bank_namefirm'] = array('type' => 'text', 'caption' => 'Наименование получателя платежа');
		$this->config_form['bank_INN'] = array('type' => 'text', 'caption' => 'ИНН получателя платежа');
		$this->config_form['bank_KPP'] = array('type' => 'text', 'caption' => 'КПП получателя платежа');
		$this->config_form['bank_nomer'] = array('type' => 'text', 'caption' => 'Номер счета получателя');
		$this->config_form['bank_namebank'] = array('type' => 'text', 'caption' => 'Наименование банка получателя');
		$this->config_form['bank_BIK'] = array('type' => 'text', 'caption' => 'БИК');
		$this->config_form['bank_KC'] = array('type' => 'text', 'caption' => 'К/С счет банка получателя');
		$this->config_form['bank_info'] = array('type' => 'ckedit', 'caption' => 'Информация', 'comment'=>'вывод при распечатке квитанции');
		$this->config_form['bank_firmaddress'] = array('type' => 'text', 'caption' => 'Адресс');
		$this->config_form['bank_firmcontact'] = array('type' => 'text', 'caption' => 'Контакты');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут','comment'=>'Время жизни счёта по умолчанию. В часах.');
	}


	protected function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['fio'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['phone'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['address'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['amount'] = array('type' => 'decimal', 'width' => '10,2','attr' => 'NOT NULL'); // в коппейках
		$this->fields['statuses'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL');
		$this->fields['json_data'] = array('type' => 'text', 'attr' => 'NOT NULL');
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users','nameField'=>'concat("№",tx.id," ",tx.name)'), 'readonly'=>1, 'caption' => 'Пользователь', 'comment'=>'', 'mask'=>array());
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Назначение платежа');
		$this->fields_form['fio'] = array('type' => 'text', 'caption' => 'ФИО плательщика');
		$this->fields_form['address'] = array('type' => 'text', 'caption' => 'Адрес плательщика ');
		$this->fields_form['phone'] = array('type' => 'text', 'caption' => 'Контактный телефон');
		$this->fields_form['amount'] = array('type' => 'decimal', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['minpay'].'р, максимум '.$this->config['maxpay'].'р', 'default'=>100, 'mask'=>array('min'=>$this->config['minpay'],'max'=>$this->config['maxpay']));
		if(isset($_GET['summ']))
			$this->fields_form['amount']['default'] = ceil(floatval($_GET['summ']));
		$this->fields_form['statuses'] = array('type' => 'list', 'listname'=>'statuses', 'readonly'=>1, 'caption' => 'Статус', 'mask'=>array());
		$this->fields_form['json_data'] = array('type' => 'textarea', 'caption' => 'JSON DATA', 'mask'=>array('fview'=>1));
	}

	/*
	* При добавлении делаем запрос XML
	*/
	/*
	function billingForm($summ, $comm, $data=array()) {
		$this->prm_add = true;
		$this->getFieldsForm(1);
		$argForm = $this->fields_form;
		$argForm['cost']['mask']['evala'] = $summ;
		$argForm['cost']['readonly'] = true;
		$argForm['name']['mask']['evala'] = '"'.addcslashes($comm,'"').'"';
		$argForm['name']['readonly'] = true;
		return $this->_UpdItemModul(array('showform'=>1),$argForm);
	}
	*/



	function billingForm($summ, $comm, $data=array()) {
		$ADD = array('amount'=>$summ,'name'=>$comm);

		if(isset($_SESSION['user']['fio']))
			$ADD['fio'] = $data['fio'] = $_SESSION['user']['fio'];
		elseif(isset($data['fio']))
			$ADD['fio'] = $data['fio'];

		if(isset($_SESSION['user']['address']))
			$ADD['address'] = $data['address'] = $_SESSION['user']['address'];
		elseif(isset($data['address']))
			$ADD['address'] = $data['address'];

		if(isset($_SESSION['user']['phone']))
			$ADD['phone'] = $data['phone'] = $_SESSION['user']['phone'];
		elseif(isset($data['phone']))
			$ADD['phone'] = $data['phone'];

		if(isset($data['json_data']))
			$ADD['json_data'] = $data['json_data'];
		// TODO : Если нету fio и address - то выводить форму сначала для их заполнения
		$this->_add($ADD, false);
		return $ADD;
	}

	public function statusForm($data)
	{
		//$data['child']
		$DATA = array('showStatus'=>true,'messages'=>array());
		if(count($data)) {
			//$DATA['messages'][] = array('payselect-comm',$data['name']);
			//$DATA['messages'][] = array('payselect-summ','Сумма : <span>'.number_format($data['amount'], 2, ',', ' ').' '.$this->owner->config['curr'].'');

			$DATA['messages'][] = array('txt','Распечатайте квитанцию и оплатите по нему в ближайшем банке');
			$DATA['messages'][] = array('alert payselect-kvit','<a href="'.$this->_CFG['_HREF']['siteJS'].'?_modul='.$this->_cl.'&_fn=printBill&blank=kvit&id='.$data['id'].'&_template=print&noajax=1" target="_blank">Распечатать квитанцию</a>');
			$DATA['messages'][] = array('alert payselect-schet','<a href="'.$this->_CFG['_HREF']['siteJS'].'?_modul='.$this->_cl.'&_fn=printBill&blank=schet&id='.$data['id'].'&_template=print&noajax=1" target="_blank">Распечатать счёт</a>');
		}

		return $DATA;
	}

	/**
	* Форма вывода информации о счете
	*
	*/

	
	function printBill() {
		global $HTML;
		$result = array(
			'text' => 'Ошибка. Нет данных.',
			'title' => 'Ошибка',
		);
		$this->id = (int)$_GET['id'];
		if(!$this->id) return $result;
		$item = $this->_select();
		if(!count($item)) return $result;

		$DATA = array(
			'#config#' => ($this->owner->config + $this->config),
			'#item#' => $item[$this->id]
		);
		if($_GET['blank']=='kvit') {
			$result = array(
				'text' => $HTML->transformPHP($DATA,'#pay#paybankReceipt'),
				'title' => 'Квитанция',
			);
		} else {
			$result = array(
				'text' => $HTML->transformPHP($DATA,'#pay#paybankBill'),
				'title' => 'Счёт',
			);
		}
		return $result;
	}
}