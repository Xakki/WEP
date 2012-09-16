<?php
class payrbk_class extends kernel_extends {
	static $STATUS_PROCESS = 3;
	static $STATUS_SUCCESS = 5;
	
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'RBK.Money';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->ver = '0.1';
		$this->pay_systems = true; // Это модуль платёжной системы
		$this->pay_formType = true; // Оплата производится по форме
		//$this->showinowner = false;



		$this->_enum['recipientCurrency'] = array(
			'RUR' => 'RUR',
			'USD' => 'USD',
			'EUR' => 'EUR',
			'UAH' => 'UAH',
		);

		$this->_enum['preference'] = array(
			'all' => 'All (default)',
			'inner' => 'RBK Money wallet (inner)',
			'bankcard' => 'Visa/MasterCard bank card (bankcard)',
			'terminals' => 'Cash-in kiosks (terminals)',
			'prepaidcard' => 'RBK Money prepaid card (prepaidcard)',
			'postrus' => 'Russian Post (postrus)',
			'mobilestores' => 'Mobile stores (mobilestores)',
			'transfers' => 'Money transfer systems (transfers)',
			'ibank' => 'Internet banking (ibank)',
			'sberbank' => 'Bank payment (sberbank)',
			'svyaznoy' => 'Svyaznoy salons (svyaznoy)',
			'euroset' => 'Euroset salons (euroset)',
			'contact' => 'Contact salons (contact)',
			'uralsib' => 'Uralsib (uralsib)',
			'handybank' => 'HandyBank (handybank)',
			'ocean' => 'Ocean Bank (ocean)',
			'ibankuralsib' => 'Uralsib internert bank (ibankuralsib)',
		);

		$this->_enum['paymentStatus'] = array(
			self::$STATUS_PROCESS => 'Операция принята на обработку',
			self::$STATUS_SUCCESS => 'Операция исполнена',
		);

		$this->_enum['language'] = array(
			'ru' => 'Русский',
			'en' => 'English',
		);

		$this->cron[] = array('modul'=>$this->_cl,'function'=>'checkBill()','active'=>1,'time'=>300);
		$this->_AllowAjaxFn['successpayment'] = true;
		$this->_Button = true;

		/*$this->REDIRECT_URI = 'http://'.$_SERVER['HTTP_HOST2'].'/_js.php?_modul='.$this->_cl.'&_fn=redirectFromYa&noajax=1';
		$this->URI_YM_API = 'https://money.yandex.ru/api';
		$this->URI_YM_AUTH = 'https://sp-money.yandex.ru/oauth/authorize';
		$this->URI_YM_TOKEN = 'https://sp-money.yandex.ru/oauth/token';
		$this->YM_USER_AGENT = 'wep-php';
		$this->SSL = dirname(__FILE__).'/lib/ym.crt'; 
		$this->SCOPE = array('account-info','operation-history','operation-details');*/

		return true;
	}

	function _create_conf() {/*CONFIG*/
		//parent::_create_conf();

		$this->config['actionURL'] = 'https://rbkmoney.ru/acceptpurchase.aspx';
		$this->config['eshopId'] = '';
		$this->config['recipientCurrency'] = '';
		$this->config['secretKey'] = '';
		$this->config['allow_ip'] = '89.111.188.128, 46.38.182.208, 46.38.182.209, 46.38.182.210';
		$this->config['preference'] = 'all';
		$this->config['language'] = 'ru';
		$this->config['minpay'] = 10;
		$this->config['maxpay'] = 15000;
		$this->config['lifetime'] = 1080;

		$this->config_form['info'] = array('type' => 'info', 'caption'=>'<input value="http://'.$_SERVER['HTTP_HOST'].'/_js.php?_modul='.$this->_cl.'&_fn=successpayment&noajax=1" readonly="true"/>');
		$this->config_form['actionURL'] = array('type' => 'text', 'caption' => 'actionURL', 'comment'=>'');
		$this->config_form['eshopId'] = array('type' => 'text', 'caption' => 'eshopId');
		$this->config_form['secretKey'] = array('type' => 'text', 'caption' => 'secretKey','comment'=>'');
		$this->config_form['recipientCurrency'] = array('type' => 'list', 'listname'=>'recipientCurrency', 'caption' => 'Валюта','comment'=>'');
		$this->config_form['allow_ip'] = array('type' => 'text', 'caption' => 'Разрешенные IP', 'default'=>'89.111.188.128, 46.38.182.208, 46.38.182.209, 46.38.182.210');
		$this->config_form['preference'] = array('type' => 'list', 'listname'=>'preference', 'caption' => 'Метод оплаты по умолчанию','comment'=>'Позволяет пропустить окно выбора оплаты');
		$this->config_form['language'] = array('type' => 'list', 'listname'=>'language', 'caption' => 'Локализация','comment'=>'язык');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#F60;');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#F60;');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут','comment'=>'Время жизни счёта по умолчанию. Задается в часах. Максимум 1080 часов (45 суток)', 'style'=>'background-color:#F60;');
	}

	function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL'); // наименование услуги
		$this->fields['email'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields['amount'] = array('type' => 'decimal', 'width' => '10,2','attr' => 'NOT NULL');
		$this->fields['username'] = array('type' => 'varchar', 'width' => 20, 'attr' => 'NOT NULL', 'default'=>''); // № плательщика в системе
		//Статус операции
		$this->fields['paymentstatus'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL', 'default'=>'');
		//Дата и время исполнения операции в Системе RBK Money в формате " YYYY - MM - DD HH : MM : SS ".
		$this->fields['paymentdata'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL', 'default'=>'');
		// Контрольная подпись оповещения об исполнении операции, которая используется для проверки целостности полученной информации и однозначной идентификации отправителя.Алгоритм формирования описан в разделе " Контрольная подпись данных ".
		$this->fields['hash'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL', 'default'=>''); 
		//В этом поле передается идентификатор операции в Системе RBK Money.
		$this->fields['paymentid'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL', 'default'=>0);
		//Идентификатор учетной записи Участника в Системе RBK Money . Является уникальным в Системе RBK Money .
		$this->fields['eshopaccount'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL', 'default'=>'');
	}

	/*function getButton($summ,$comm) {
		return '<iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/small.xml?uid=4100198176118&amp;button-text=01&amp;button-size=l&amp;button-color=orange&amp;targets=%d0%a3%d1%81%d0%bb%d1%83%d0%b3%d0%b8+%d0%b4%d0%bb%d1%8f+%d0%be%d0%b1%d1%8a%d1%8f%d0%b2%d0%bb%d0%b5%d0%bd%d0%b8%d1%8f+%e2%84%96&amp;default-sum='.$summ.'&amp;mail=on" width="auto" height="54"></iframe>';
	}*/

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		//$this->fields_form['sender'] = array('type' => 'text', 'caption' => 'Номер плательщика');
		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'Email');
		$this->fields_form['amount'] = array('type' => 'decimal', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['minpay'].'р, максимум '.$this->config['maxpay'].'р', 'default'=>100, 'mask'=>array('min'=>$this->config['minpay'],'max'=>$this->config['maxpay']));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Комментарий', 'mask'=>array('name'=>'all'));
		$this->fields_form['paymentStatus'] = array('type' => 'list', 'listname'=>'paymentStatus', 'readonly'=>1, 'caption' => 'Статус', 'mask'=>array());
		//$this->fields_form['error'] = array('type' => 'list', 'listname'=>'error', 'readonly'=>1, 'caption' => 'Ошибка', 'mask'=>array());
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly'=>1, 'caption' => 'Дата', 'mask'=>array());
	}


	/*
	* При добавлении делаем запрос XML
	*/
	function billingFrom($summ, $comm, $data=array()) {
		$ADD = array('amount'=>$summ,'name'=>$comm);

		if(isset($_SESSION['user']['email']))
			$ADD['email'] = $data['email'] = $_SESSION['user']['email'];
		elseif(isset($data['email']))
			$ADD['email'] = $data['email'];

		$result = array(
			array(),0
		);
		if($this->_add($ADD)) {
			$DATA = $this->payFormBilling($this->data[$this->id]);
			$result = array($DATA, 1);
		}
		return $result;
	}

	function payFormBilling($data) {
		global $_tpl;
		//$_tpl['onload'] .= '$("#form_paymethod").submit();';
		$DATA = array();
		$DATA['messages'] = array(
			array('alert','Выполняется открытие страницы оплаты на RBK.Money.'),
			array('notice','<small>Если у вас не открылось окно оплаты, возможно ваш браузер заблокировал открытие окна (Ваш браузер должен был выдать предупреждение об этом, кликните на всплывшее сообщение и разрешите данную операцию)</small>'),
			array('txt','После оплаты обновите <a href="javascript:window.location.reload();">страницу</a>, чтобы узнать состояние счёта.'),
		);
		$DATA['form'] = array(
			'_*features*_' => array('name'=>'paymethod','action'=>$this->config['actionURL'].'"  target="_blank'),
			'eshopId'=>array('type'=>'hidden','value'=>$this->config['eshopId']),
			'orderId'=>array('type'=>'hidden','value'=>$data['id']), // заголовок у отправителя
			'serviceName'=>array('type'=>'hidden','value'=>$data['name']), // Комментарий у отправителя
			'recipientAmount'=>array('type'=>'hidden','value'=>$data['amount']),
			'recipientCurrency'=>array('type'=>'hidden','value'=>$this->config['recipientCurrency']),
			'successUrl'=>array('type'=>'hidden','value'=>$this->owner->successUrl),
			'failUrl'=>array('type'=>'hidden','value'=>$this->owner->failUrl),
			'user_email'=>array('type'=>'hidden','value'=>$data['email']),
			'language'=>array('type'=>'hidden','value'=>$this->config['language']),
		);
		if ($this->config['preference'] != 'all') {
			$form['preference'] = array(
				'#type' => 'hidden',
				'#value' => $this->config['preference'],
			);
		}
		$DATA['form']['sbmt'] = array('type'=>'submit','value'=>'Перейти на RBK.Money');
		return $DATA;
	}


	///////////////////////////////////////
	//////////////////////////////////////////
	/////////////////////////////////////////////

	function checkBill() 
	{

	}
	/**
	 * Callback for RBK Money system response.
	 */
	function successpayment() {

	/* Check for allowed IP */
	  if ($this->config['allow_ip']) {
	    $allowed_ip = explode(',', $this->config['allow_ip']);
	    $valid_ip = in_array($_SERVER['REMOTE_ADDR'], $allowed_ip);
	    if (!$valid_ip) {
	    	// TODO log
	    	return false;
	    }
	  }
	  
	  $response['orderId'] = $_POST['orderId'];
	  $response['serviceName'] = $_POST['serviceName'];
	  $response['eshopAccount'] = $_POST['eshopAccount'];
	  $response['paymentStatus'] = $_POST['paymentStatus'];
	  $response['userName'] = $_POST['userName'];
	  $response['userEmail'] = $_POST['userEmail'];
	  $response['paymentData'] = $_POST['paymentData'];
	  $response['hash'] = $_POST['hash'];

	  if (!empty($response['hash'])) {

	    $order = uc_order_load($response['orderId']);
	    if(!count($order))
	    	trigger_error('RBK Money : Полученный orderId ('.$response['orderId'] .') не найден в базе', E_USER_WARNING);

	    $string = $this->config['eshopId'] . '::' . $response['orderId'] . '::' . $response['serviceName'] . '::' . $response['eshopAccount'] . '::' . number_format($order->order_total, 2, '.', '') . '::' . $this->config['recipientCurrency'] . '::' . $response['paymentStatus'] . '::' . $response['userName'] . '::' . $response['userEmail'] . '::' . $response['paymentData'] . '::' . $this->config['secretKey'];
	    $crc = md5($string);

	    if ($response['hash'] == $crc) {
	      switch ($response['paymentStatus']) {
	        case self::$STATUS_PROCESS:
	          /*uc_order_update_status($response['orderId'], 'processing');
	          uc_order_comment_save($response['orderId'], $order->uid, t('RBK Money: payment processing'), $type = 'admin', $status = 1, $notify = FALSE);*/
	          break;
	        case self::$STATUS_SUCCESS:
	          /*uc_payment_enter($response['orderId'], 'RBK Money', $order->order_total, $order->uid, NULL, NULL);
	          uc_cart_complete_sale($order);
	          uc_order_comment_save($response['orderId'], $order->uid, t('RBK Money: payment successful'), $type = 'admin', $status = 1, $notify = FALSE);*/
	          break;
	      }
	    }
	    elseif ($response['hash'] !== $crc) {
	      /*uc_order_update_status($response['orderId'], 'canceled');
	      uc_order_comment_save($response['orderId'], $order->uid, t('MD5 checksum fail, possible fraud. Order canceled'), $type = 'admin', $status = 1, $notify = FALSE);
	      watchdog('uc_rbkmoney', 'MD5 checksum fail, possible fraud. Order canceled');*/
	      trigger_error('RBK Money : Полученный hash не верный', E_USER_WARNING);
	    }
	  }
	}

	/**
	* Сервис служба очистки данных
	* Отключает неоплаченные платежи 
	* @param $M - модуль платежной системы
	* @param $leftTime - в секундах
	*/
	function clearOldData() {
		$leftTime = ($this->config['lifetime']*3600);
		$this->_update(array('status'=>'timeout', $this->mf_actctrl=>0), 'status="" and '.$this->mf_timecr.'<"'.(time()-$leftTime).'"');
		$this->owner->clearOldData($this->_cl, $leftTime);
	}
}


