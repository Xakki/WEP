<?php


class paybank_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->pay_systems = true; // Это модуль платёжной системы

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
		return true;
	}

	function _create_conf2(&$obj) {/*CONFIG*/
		//parent::_create_conf();

		$obj->config['bank_namefirm'] = 'ООО "Рога и Копыта"';
		$obj->config['bank_INN'] = '';
		$obj->config['bank_KPP'] = '';
		$obj->config['bank_nomer'] = '';
		$obj->config['bank_namebank'] = '';
		$obj->config['bank_BIK'] = '';
		$obj->config['bank_KC'] = '';
		$obj->config['bank_minpay'] = '';
		$obj->config['bank_maxpay'] = '';

		$obj->config_form['bank_info'] = array('type' => 'info', 'caption'=>'<h3>Оплата безналичным расчетом</h3>');
		$obj->config_form['bank_namefirm'] = array('type' => 'text', 'caption' => 'Наименование получателя платежа', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_INN'] = array('type' => 'text', 'caption' => 'ИНН получателя платежа', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_KPP'] = array('type' => 'text', 'caption' => 'КПП получателя платежа', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_nomer'] = array('type' => 'text', 'caption' => 'Номер счета получателя', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_namebank'] = array('type' => 'text', 'caption' => 'Наименование банка получателя', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_BIK'] = array('type' => 'text', 'caption' => 'БИК', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_KC'] = array('type' => 'text', 'caption' => 'К/С счет банка получателя', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2a37ec;');
		$obj->config_form['bank_maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#2a37ec;');
	}


	protected function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['fio'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['address'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['amount'] = array('type' => 'float', 'width' => '11,4','attr' => 'NOT NULL'); // в коппейках
		$this->fields['statuses'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL');
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Назначение платежа');
		$this->fields_form['fio'] = array('type' => 'text', 'caption' => 'ФИО плательщика');
		$this->fields_form['address'] = array('type' => 'text', 'caption' => 'Адрес плательщика ');
		$this->fields_form['amount'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->owner->config['bank_minpay'].'р, максимум '.$this->owner->config['bank_maxpay'].'р', 'default'=>100, 'mask'=>array('minint'=>$this->owner->config['bank_minpay'],'maxint'=>$this->owner->config['bank_maxpay']));
		if(isset($_GET['summ']))
			$this->fields_form['amount']['default'] = ceil(floatval($_GET['summ']));
		$this->fields_form['statuses'] = array('type' => 'list', 'listname'=>'statuses', 'readonly'=>1, 'caption' => 'Статус', 'mask'=>array());
	}

	function billingFrom($summ, $comm, $data=array()) {
		$ADD = array('amount'=>$summ,'name'=>$comm);

		if(isset($_SESSION['user']['fio']))
			$ADD['fio'] = $data['fio'] = $_SESSION['user']['fio'];
		elseif(isset($data['fio']))
			$ADD['fio'] = $data['fio'];

		if(isset($_SESSION['user']['address']))
			$ADD['address'] = $data['address'] = $_SESSION['user']['address'];
		elseif(isset($data['address']))
			$ADD['address'] = $data['address'];
		// TODO : Если нету fio и address - то выводить форму сначала для их заполнения

		$st = $this->_add($ADD);

		$DATA = $this->payFormBilling($this->data[$this->id], 1);
		return array($DATA, $st);// 1
	}


	function payFormBilling($data,$status=0) {

		//TODO : сообщение об успешном оформлении и с предложением распечатать (или открытие окна с распечаткой) квитанцию и счет. 
		//TODO : оповещение в платежной системе о процессах (у родителя этого класса)
		//global $_tpl;
		//$_tpl['onload'] .= '$("#form_paymethod").submit();';
		$DATA = array('messages'=>array());

		if($status) {
			if(count($data))
				$DATA['messages'][] = array('ok','Счёт успешно сформирован.');
			else
				$DATA['messages'][] = array('error','Ошибка выставление счёта!');
		}

		if(count($data)) {
			$DATA['messages'][] = array('payselect-comm',$data['name']);
			$DATA['messages'][] = array('payselect-summ','Сумма : <span>'.number_format($data['amount'], 2, ',', ' ').' '.$this->owner->config['curr'].'');

			$DATA['messages'][] = array('txt','Распечатайте квитанцию и оплатите по нему в ближайшем банке');
			$DATA['messages'][] = array('alert payselect-kvit','<a href="'.$this->_CFG['_HREF']['siteJS'].'?_modul='.$this->_cl.'&_fn=printBill&blank=kvit&id='.$data['id'].'&_template=print&noajax=1" target="_blank">Распечатать квитанцию</a>');
			$DATA['messages'][] = array('alert payselect-schet','<a href="'.$this->_CFG['_HREF']['siteJS'].'?_modul='.$this->_cl.'&_fn=printBill&blank=schet&id='.$data['id'].'&_template=print&noajax=1" target="_blank">Распечатать счёт</a>');
		}

		return $DATA;
	}
	
	function printBill() {
		global $HTML;
		$DATA = array();
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