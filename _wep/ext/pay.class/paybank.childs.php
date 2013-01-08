<?php


class paybank_class extends kernel_extends 
{
	protected function _set_features() 
	{
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
	}

	protected function _create_conf() {/*CONFIG*/
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
		//$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['fio'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['phone'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['address'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['amount'] = array('type' => 'decimal', 'width' => '10,2','attr' => 'NOT NULL');
		
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users','nameField'=>'concat("№",tx.id," ",tx.name)'), 'readonly'=>1, 'caption' => 'Пользователь', 'comment'=>'', 'mask'=>array());
		//$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Назначение платежа');
		$this->fields_form['fio'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'ФИО плательщика');
		$this->fields_form['address'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Адрес плательщика ');
		$this->fields_form['phone'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Контактный телефон');
		$this->fields_form['amount'] = array('type' => 'decimal', 'readonly'=>1, 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['minpay'].'р, максимум '.$this->config['maxpay'].'р', 'default'=>100, 'mask'=>array('min'=>$this->config['minpay'],'max'=>$this->config['maxpay']));
	}

	/*
	* При добавлении делаем запрос XML
	*/
	public function billingForm($summ, $comm, $data=array()) 
	{
		//print_r('<pre>');print_r($data);print_r($_POST);
		//exit();
		$this->owner->setPostData('fio', $data);
		$this->owner->setPostData('address', $data);
		$this->owner->setPostData('phone', $data);

		$argForm = array();
		$argForm['fio'] = array('type' => 'text', 'caption' => 'ФИО плательщика', 'mask' => array('min' => 6));
		$argForm['address'] = array('type' => 'text', 'caption' => 'Адрес плательщика ', 'mask' => array('min' => 6));
		$argForm['phone'] = array('type' => 'text', 'caption' => 'Контактный телефон', 'mask'=>array('name'=>'phone3', 'min'=>6));
		//$argForm['name'] = array('type' => 'hidden', 'readonly'=>1, 'mask' => array('eval' => $comm));
		$argForm['amount'] = array('type' => 'hidden', 'readonly'=>1, 'mask' => array('eval' => $summ));

		$_POST['sbmt'] = true;
		$this->prm_add = true; 
		return $this->_UpdItemModul(array('showform'=>1), $argForm);
	}

	public function statusForm($data)
	{
		//$data['child']
		$result = array('showStatus'=>true,'messages'=>array());
		if(count($data) and $data['status']<2) 
		{
			$result['messages'][] = array('txt','Распечатайте квитанцию и оплатите по нему в ближайшем банке');
			$result['messages'][] = array('alert payselect-kvit','<a href="'.$this->_CFG['_HREF']['siteJS'].'?_modul='.$this->_cl.'&_fn=printBill&blank=kvit&id='.$data['child']['id'].'&_template=print&noajax=1" target="_blank">Распечатать квитанцию</a>');
			$result['messages'][] = array('alert payselect-schet','<a href="'.$this->_CFG['_HREF']['siteJS'].'?_modul='.$this->_cl.'&_fn=printBill&blank=schet&id='.$data['child']['id'].'&_template=print&noajax=1" target="_blank">Распечатать счёт</a>');
		}

		return $result;
	}

	/**
	* Форма вывода информации о счете
	*
	*/

	
	function printBill() {
		global $HTML;
		$result = array(
			'html' => 'Ошибка. Нет данных.',
			'title' => 'Ошибка',
		);
		$this->id = (int)$_GET['id'];
		if(!$this->id) return $result;

		$item = $this->_select();
		if(!count($item) or !$item[$this->id]['owner_id']) return $result;

		$this->owner->id = $item[$this->id]['owner_id'];
		$itemOwner = $this->owner->_select();
		if(!count($itemOwner)) return $result;

		$DATA = array(
			'#config#' => ($this->owner->config + $this->config),
			'#item#' => $item[$this->id],
			'#payData#' => $itemOwner[$this->owner->id]
		);
		if($_GET['blank']=='kvit') 
		{
			$result = array(
				'html' => $HTML->transformPHP($DATA,'#pay#paybankReceipt'),
				'title' => 'Квитанция',
			);
		} 
		else 
		{
			$result = array(
				'html' => $HTML->transformPHP($DATA,'#pay#paybankBill'),
				'title' => 'Счёт',
			);
		}
		return $result;
	}
}