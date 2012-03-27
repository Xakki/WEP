<?php
class payyandex_class extends kernel_extends {

	function _create_conf2(&$obj) {/*CONFIG*/
		//parent::_create_conf();
		$obj->config['ya_id'] = '';
		$obj->config['ya_code'] = '';
		$obj->config['ya_minpay'] = 10;
		$obj->config['ya_maxpay'] = 15000;

		$obj->config_form['ya_info'] = array('type' => 'info', 'caption'=>'<h3>Яндекс.Деньги</h3>');
		$obj->config_form['ya_id'] = array('type' => 'text', 'caption'=>'Номер счёта','style'=>'background-color:#F60;');
		$obj->config_form['ya_code'] = array('type' => 'text', 'caption'=>'Идентификатор приложения','comment'=>'Получить его можно <a href="https://sp-money.yandex.ru/myservices/new.xml" target="_blank">тут</a>', 'style'=>'background-color:#F60;');
		$obj->config_form['ya_minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#F60;');
		$obj->config_form['ya_maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#F60;');
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		$this->config = &$this->owner->config;
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Яндекс.Деньги';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		/*$this->lang['add_name'] = 'Пополнение кошелька из QIWI';
		$this->lang['Save and close'] = 'Выписать счёт';
		$this->lang['add_err'] = 'Ошибка выставление счёта. Обратитесь к администратору сайта.';
		$this->lang['add'] = 'Счёт на оплату отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action" target="_blank">QIWI</a> в раздел "Счета".';
		//$this->lang['add'] = 'Счёт на пополнение кошелька отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action">QIWI</a> и в течении 5ти минут после оплаты, сумма поступит на ваш баланс.';*/
		$this->default_access = '|9|';
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		//$this->_href = 'http://ishop.qiwi.ru/xml';
		$this->ver = '0.1';
		$this->pay_systems = true; // Это модуль платёжной системы

		$this->_enum['status'] = array(
			'success' => 'Успешное выполнение.',
			'refused' => 'Отказ в проведении платежа, объяснение причины отказа содержится в поле error. Это конечное состояние платежа.',
		);

		$this->_enum['error'] =array(
			0 => ' - ',
			'illegal_params' => 'Обязательные параметры платежа отсутствуют или имеют недопустимые значения.',
			'phone_unknown' => 'Указан номер телефона не связанный со счетом пользователя или получателя платежа.',
			'payment_refused' => 'Магазин отказал в приеме платежа (например пользователь попробовал заплатить за товар, которого нет в магазине).',
			1 => 'Техническая ошибка, повторите вызов операции позднее.',
		);

		$this->_enum['money_source'] = array(
			'wallet' => 'Платеж со счета пользователя.',
			'card' => 'Платеж с привязанной к счету банковской карты.',
		);

		//$this->cron[] = array('modul'=>$this->_cl,'function'=>'checkBill()','active'=>1,'time'=>300);
		$this->_AllowAjaxFn = array(
			'redirectFromYa'=>true
		);
		$this->_Button = true;
		return true;
	}

	protected function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL','default'=>'');
		$this->fields['phone'] = array('type' => 'bigint', 'width' => 13,'attr' => 'unsigned NOT NULL');
		$this->fields['email'] = array('type' => 'varchar', 'width' => 32,'attr' => 'unsigned NOT NULL');
		$this->fields['amount'] = array('type' => 'float', 'width' => '11,4','attr' => 'NOT NULL'); // в коппейках
		$this->fields['status'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL','default'=>'');
		//Код ошибки при проведении платежа (пояснение к полю status). Присутствует только при ошибках.
		$this->fields['error'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL','default'=>'');
		//Доступные для приложения методы проведения платежа, см. Доступные методы платежа. Присутствует только при успешном выполнении метода.
		//@allowed
		$this->fields['money_source'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL','default'=>'');
		//Идентификатор запроса платежа, сгенерированный системой. Присутствует только при успешном выполнении метода.
		$this->fields['request_id'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL','default'=>'');
		//Остаток на счете пользователя. Присутствует только при успешном выполнении метода.
		$this->fields['balance'] = array('type' => 'float', 'width' => '11,4','attr' => 'NOT NULL','default'=>0);
	}

	/*function getButton($summ,$comm) {
		return '<iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/small.xml?uid=4100198176118&amp;button-text=01&amp;button-size=l&amp;button-color=orange&amp;targets=%d0%a3%d1%81%d0%bb%d1%83%d0%b3%d0%b8+%d0%b4%d0%bb%d1%8f+%d0%be%d0%b1%d1%8a%d1%8f%d0%b2%d0%bb%d0%b5%d0%bd%d0%b8%d1%8f+%e2%84%96&amp;default-sum='.$summ.'&amp;mail=on" width="auto" height="54"></iframe>';
	}*/

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['phone'] = array('type' => 'int', 'caption' => 'Номер телефона');
		$this->fields_form['email'] = array('type' => 'int', 'caption' => 'Email');
		$this->fields_form['amount'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['ya_minpay'].'р, максимум '.$this->config['ya_maxpay'].'р', 'default'=>100, 'mask'=>array('minint'=>$this->config['ya_minpay'],'maxint'=>$this->config['ya_maxpay']));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Комментарий', 'mask'=>array('name'=>'all'));
		$this->fields_form['status'] = array('type' => 'list', 'listname'=>'status', 'readonly'=>1, 'caption' => 'Статус', 'mask'=>array());
		$this->fields_form['error'] = array('type' => 'list', 'listname'=>'error', 'readonly'=>1, 'caption' => 'Ошибка', 'mask'=>array());
	}


	/*
	* При добавлении делаем запрос XML
	*/
	function billingFrom($summ, $comm) {
		global $_tpl;
		$ADD = array('amount'=>$summ,'name'=>$comm);
		if(isset($_SESSION['user']['phone']))
			$ADD['phone'] = $_SESSION['user']['phone'];
		if(isset($_SESSION['user']['login']))
			$ADD['phone'] = $_SESSION['user']['login'];
		//$this->_add($ADD);
		$form = array(
			'info'=>array('type'=>'info','caption'=>'Выполняется открытие страницы оплаты на Яндекс.Деньги. Если у вас не открылось окно оплаты, возможно ваш браузер заблокировал открытие окна (Ваш браузер должен был выдать предупреждение об этом, кликните на всплывшее сообщение и разрешите данную операцию)'),
			'receiver'=>array('type'=>'hidden','value'=>$this->owner->config['ya_id']),
			'FormComment'=>array('type'=>'hidden','value'=>'Оплата товара/услуги'),
			'short-dest'=>array('type'=>'hidden','value'=>'Оплата товара/услуги'),
			'writable-targets'=>array('type'=>'hidden','value'=>'false'),
			'writable-sum'=>array('type'=>'hidden','value'=>'false'),
			'comment-needed'=>array('type'=>'hidden','value'=>'true'),
			'quickpay-form'=>array('type'=>'hidden','value'=>'small'),
			'targets'=>array('type'=>'hidden','value'=>$comm),
			'sum'=>array('type'=>'hidden','value'=>$summ),
			'mail'=>array('type'=>'hidden','value'=>'true'),
			'sbmt'=>array('type'=>'submit','value'=>'Перейти к оплате на Яндекс.Деньги'),
		);
		$_tpl['onload'] .= '$("#paymethod").submit();';
		return array(array('form'=>$form,'#action#'=>'https://money.yandex.ru/quickpay/confirm.xml"  target="_blank'),0);// 1
	}
	
	//http://unidoski.ru/_js.php?_modul=pay&_fn=redirectFromYa
	function redirectFromYa() {
	}

}


