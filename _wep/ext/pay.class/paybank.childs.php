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

		$this->lang['add_err'] = 'Ошибка выставление счёта. Обратитесь к администратору сайта.';
		$this->lang['add'] = 'Счёт на оплату сформировано<br/> Распечатайте квитанцию и оплатите его в ближайшем отделении банка.';
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config['bank_prefix'] = 'НФ-';
		$this->config['bank_namefirm'] = 'ООО "Рога и Копыта"';
		$this->config['bank_INN'] = '1234567890';
		$this->config['bank_KPP'] = '123456789';
		$this->config['bank_nomer'] = '12345678901234567890';
		$this->config['bank_namebank'] = 'БАНК РОССИИ';
		$this->config['bank_BIK'] = '044525181';
		$this->config['bank_KC'] = '30101810900000000181';
		$this->config['bank_info'] = '<h1 style="color: rgb(0, 0, 0); font-family: \'Times New Roman\'; line-height: normal; ">	Внимание! Ваш банк может взимать комиссию.</h1><h1 style="color: rgb(0, 0, 0); font-family: \'Times New Roman\'; line-height: normal; ">	<b>Метод оплаты:</b></h1><ol style="color: rgb(0, 0, 0); font-family: \'Times New Roman\'; font-size: medium; line-height: normal; ">	<li>		Распечатайте квитанцию. Если у вас нет принтера, перепишите верхнюю часть квитанции и заполните по этому образцу стандартный бланк квитанции в вашем банке.</li>	<li>		Вырежьте по контуру квитанцию.</li>	<li>		Оплатите квитанцию в любом отделении банка, принимающего платежи от частных лиц.&nbsp;<span style="color: red; "><b>*</b></span></li>	<li>		Сохраните квитанцию до подтверждения исполнения заказа.</li>	<li>		Срок резерва товара - 5 дней.</li></ol><h1 style="color: rgb(0, 0, 0); font-family: \'Times New Roman\'; line-height: normal; ">	<b>Условия поставки:</b></h1><ul style="color: rgb(0, 0, 0); font-family: \'Times New Roman\'; font-size: medium; line-height: normal; ">	<li>		Отгрузка оплаченного товара производится после подтверждения факта платежа.</li>	<li>		Идентификация платежа производится по квитанции, поступившей в наш банк.</li></ul><p style="color: rgb(0, 0, 0); line-height: normal; font-family: \'Times New Roman\'; font-size: medium; ">	<b>Примечание:</b>&nbsp;ООО &quot;Рога и копыта&quot; не может гарантировать конкретные сроки проведения вашего платежа. За дополнительной информацией о сроках поступления денежных средств в банк получателя, обращайтесь в свой банк.</p><p style="color: rgb(0, 0, 0); line-height: normal; font-family: \'Times New Roman\'; font-size: medium; ">	<b><span style="color: red; ">*</span>&nbsp;Помните, что банки за проведение платежа взимают небольшую комиссию</b><br />	&nbsp;</p>';
		$this->config['bank_firmaddress'] = '123456, Россия, Москва, Арбат, 1';
		$this->config['bank_firmcontact'] = '(917) 123 45 67';
		$this->config['minpay'] = '0';
		$this->config['maxpay'] = '10000000';
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
		$this->fields['cost'] = array('type' => 'decimal', 'width' => '10,2','attr' => 'NOT NULL');
		
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users','nameField'=>'concat("№",tx.id," ",tx.name)'), 'readonly'=>1, 'caption' => 'Пользователь', 'comment'=>'', 'mask'=>array());
		//$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Назначение платежа');
		$this->fields_form['fio'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'ФИО плательщика');
		$this->fields_form['address'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Адрес плательщика ');
		$this->fields_form['phone'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Контактный телефон');
		$this->fields_form['cost'] = array('type' => 'decimal', 'readonly'=>1, 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['minpay'].'р, максимум '.$this->config['maxpay'].'р', 'default'=>100, 'mask'=>array('min'=>$this->config['minpay'],'max'=>$this->config['maxpay']));
	}

	/*
	* При добавлении делаем запрос XML
	*/
	public function billingForm($summ, $comm, $data=array()) 
	{
		$this->prm_add = true; 
		$param = array('showform'=>1, 'savePost'=>true, 'setAutoSubmit'=>true);

		$this->owner->setPostData('fio', $data);
		$this->owner->setPostData('address', $data);
		$this->owner->setPostData('phone', $data);
		$this->owner->setPostData('email', $data);

		$argForm = array();
		$argForm['fio'] = array('type' => 'text', 'caption' => 'ФИО плательщика', 'mask' => array('min' => 6));
		$argForm['address'] = array('type' => 'text', 'caption' => 'Адрес плательщика ', 'mask' => array('min' => 6));
		$argForm['phone'] = array('type' => 'text', 'caption' => 'Контактный телефон', 'mask'=>array('name'=>'phone3', 'min'=>6));
		$argForm['email'] = array('type' => 'email', 'caption' => 'Email', 'mask'=>array('min'=>5)); // 'name'=>'email', 
		//$argForm['name'] = array('type' => 'hidden', 'readonly'=>1, 'mask' => array('eval' => $comm));
		$argForm['cost'] = array('type' => 'hidden', 'readonly'=>1, 'mask' => array('eval' => $summ));
		$this->lang['Save and close'] = 'Выписать счёт';
		return $this->_UpdItemModul($param, $argForm);
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

	
	function printBill() 
	{
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
				'html' => transformPHP($DATA,'#pay#paybankReceipt'),
				'title' => 'Квитанция',
			);
		} 
		else 
		{
			$result = array(
				'html' => transformPHP($DATA,'#pay#paybankBill'),
				'title' => 'Счёт',
			);
		}
		return $result;
	}
}