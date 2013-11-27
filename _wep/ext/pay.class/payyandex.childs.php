<?php
class payyandex_class extends kernel_extends
{

	function _set_features()
	{
		parent::_set_features();
		$this->caption = 'Яндекс.Деньги';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		/*$this->lang['add_name'] = 'Пополнение кошелька из QIWI';
		$this->lang['Save and close'] = 'Выписать счёт';
		$this->lang['add_err'] = 'Ошибка выставление счёта. Обратитесь к администратору сайта.';
		$this->lang['add'] = 'Счёт на оплату отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action" target="_blank">QIWI</a> в раздел "Счета".';
		//$this->lang['add'] = 'Счёт на пополнение кошелька отправлено в систему QIWI.<br/> Чтобы оплатить его перейдите на сайт <a href="https://w.qiwi.ru/orders.action">QIWI</a> и в течении 5ти минут после оплаты, сумма поступит на ваш баланс.';*/
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		//$this->_href = 'http://ishop.qiwi.ru/xml';
		$this->ver = '0.1';
		$this->pay_systems = true; // Это модуль платёжной системы
		$this->pay_formType = true; // Оплата производится по форме
		//$this->showinowner = false;

		$this->_enum['status'] = array(
			'success' => 'Успешное выполнение.',
			'refused' => 'Отказ в проведении платежа, объяснение причины отказа содержится в поле error. Это конечное состояние платежа.',
			'timeout' => 'Время действия платежа истекло',
		);

		$this->_enum['error'] = array(
			'' => ' - ',
			'illegal_params' => 'Обязательные параметры платежа отсутствуют или имеют недопустимые значения.',
			'phone_unknown' => 'Указан номер телефона не связанный со счетом пользователя или получателя платежа.',
			'payment_refused' => 'Магазин отказал в приеме платежа (например пользователь попробовал заплатить за товар, которого нет в магазине).',
			'1' => 'Техническая ошибка, повторите вызов операции позднее.',
			'small_money' => 'Счёт не оплачен полностью.',
		);

		$this->_enum['money_source'] = array(
			'wallet' => 'Платеж со счета пользователя.',
			'card' => 'Платеж с привязанной к счету банковской карты.',
		);

		$this->cron[] = array('modul' => $this->_cl, 'function' => 'checkBill()', 'active' => 1, 'time' => 300);
		$this->_AllowAjaxFn = array(
			'redirectFromYa' => true
		);
		$this->_Button = true;

		$this->REDIRECT_URI = 'http://' . $_SERVER['HTTP_HOST2'] . '/_js.php?_modul=' . $this->_cl . '&_func=redirectFromYa&noajax=1';
		$this->URI_YM_API = 'https://money.yandex.ru/api';
		$this->URI_YM_AUTH = 'https://sp-money.yandex.ru/oauth/authorize';
		$this->URI_YM_TOKEN = 'https://sp-money.yandex.ru/oauth/token';
		$this->YM_USER_AGENT = 'wep-php';
		$this->SSL = dirname(__FILE__) . '/lib/ym.crt';
		//$this->SCOPE = array('account-info','operation-history','operation-details','payment','payment-shop','payment-p2p','money-source("wallet","card")');
		$this->SCOPE = array('account-info', 'operation-history', 'operation-details');

	}

	function _create_conf()
	{ /*CONFIG*/

		//parent::_create_conf();
		$this->config['actionURL'] = 'https://money.yandex.ru/quickpay/confirm.xml';
		$this->config['yandex_cid'] = '';
		$this->config['yandex_token'] = '';
		$this->config['yandex_id'] = '';
		/*$this->config['yandex_login'] = '';
		$this->config['yandex_pass'] = '';
		$this->config['yandex_pass2'] = '';*/
		$this->config['minpay'] = 5;
		$this->config['maxpay'] = 15000;
		$this->config['lifetime'] = 336; // 14 дней

		$this->config_form['yandex_info'] = array('type' => 'info', 'caption' => '<h3>Яндекс.Деньги</h3>');
		$this->config_form['actionURL'] = array('type' => 'text', 'caption' => 'Платежная ссылка');
		$this->config_form['yandex_id'] = array('type' => 'text', 'caption' => 'Номер счёта');
		$this->config_form['yandex_cid'] = array('type' => 'text', 'caption' => 'Идентификатор приложения', 'comment' => 'Получить его можно <a href="https://sp-money.yandex.ru/myservices/new.xml" target="_blank">тут</a> и <a href="https://sp-money.yandex.ru/myservices/admin.xml" target="_blank">настраивать</a><br>Redirect URI: <b>' . $this->REDIRECT_URI . '</b> ');
		$this->config_form['yandex_token'] = array('type' => 'password', 'caption' => 'TOKEN', 'mask' => array('password' => 'change'));
		/*$this->config_form['yandex_token'] = array('type' => 'hidden');
		$this->config_form['yandex_newtoken'] = array('type' => 'checkbox','caption'=>'Установить новый токен', 'onchange'=>'if(this.checked) $(\'.yandex_newtoken\').show(); else $(\'.yandex_newtoken\').hide();', 'style'=>'background-color:#F60;');
		$this->config_form['yandex_login'] = array('type' => 'text', 'caption'=>'Логин авторизации', 'css'=>'yandex_newtoken','style'=>'background-color:#F65;');
		$this->config_form['yandex_pass'] = array('type' => 'password', 'caption'=>'Пароль авторизации', 'css'=>'yandex_newtoken','style'=>'background-color:#F65;');
		$this->config_form['yandex_pass2'] = array('type' => 'password', 'caption'=>'Пароль подтверждения платежа', 'css'=>'yandex_newtoken','style'=>'background-color:#F65;');*/
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Время жизни счёта по умолчанию. Задается в часах. Если 0 , то будет максимум (45 суток)', 'style' => 'background-color:#F60;');

		if (isset($_GET['_func']) and $_GET['_func'] == 'Configmodul') {
			global $_tpl;
			if (count($_POST) and isset($_POST['yandex_id']) and isset($_POST['yandex_cid']) and !$_POST['yandex_token']) {
				$_tpl['onload'] .= 'window.open("' . $this->REDIRECT_URI . '","Получение TOKEN","width=800,height=750,resizable=yes,scrollbars=yes,status=yes");';
			}
			/*$_tpl['onload'] .= 'if($("input[name=yandex_token]").val()) $(\'.yandex_newtoken\').hide(); else $(\'.yandex_newtoken\').show();';
			if(count($_POST) and isset($_POST['yandex_id']) and isset($_POST['yandex_cid']) and (!$_POST['yandex_token'] or isset($_POST['yandex_newtoken']))) {
				if($_POST['yandex_login'] and $_POST['yandex_pass'] and $_POST['yandex_pass2']) {
					$CODE = $this->yandexGetCode($_POST['yandex_cid'],$_POST['yandex_login'],$_POST['yandex_pass'],$_POST['yandex_pass2']);
					if(!$CODE) {
						print_r('<h3>Error yandexGetCodeе</h3>');
					} else {
						$CODE = $this->receiveOAuthToken($_POST['yandex_cid'],$CODE);
						if(!$CODE) print_r('<h3>Error receiveOAuthToken</h3>');
						$_POST['yandex_token'] = $CODE;
					}
				}
				else
					print_r('<h3>Не введены необходимые данные</h3>');
			}*/
		}
	}

	protected function _create()
	{
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['phone'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['email'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['sender'] = array('type' => 'varchar', 'width' => 20, 'attr' => 'NOT NULL', 'default' => ''); // № плательщика в системе
		$this->fields['cost'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL', 'default' => '0.00'); // в коппейках
		$this->fields['tax'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL', 'default' => '0.00'); // в коппейках
		$this->fields['status'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['operation_id'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '');
		//Код ошибки при проведении платежа (пояснение к полю status). Присутствует только при ошибках.
		$this->fields['error'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		//Доступные для приложения методы проведения платежа, см. Доступные методы платежа. Присутствует только при успешном выполнении метода.
		//@allowed
		//$this->fields['money_source'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL','default'=>'');
		//Идентификатор запроса платежа, сгенерированный системой. Присутствует только при успешном выполнении метода.
		//$this->fields['request_id'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL','default'=>'');
		//Остаток на счете пользователя. Присутствует только при успешном выполнении метода.
		//$this->fields['balance'] = array('type' => 'decimal', 'width' => '10,2','attr' => 'NOT NULL','default'=>0);
	}

	/*function getButton($summ,$comm) {
		return '<iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/small.xml?uid=4100198176118&amp;button-text=01&amp;button-size=l&amp;button-color=orange&amp;targets=%d0%a3%d1%81%d0%bb%d1%83%d0%b3%d0%b8+%d0%b4%d0%bb%d1%8f+%d0%be%d0%b1%d1%8a%d1%8f%d0%b2%d0%bb%d0%b5%d0%bd%d0%b8%d1%8f+%e2%84%96&amp;default-sum='.$summ.'&amp;mail=on" width="auto" height="54"></iframe>';
	}*/

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['sender'] = array('type' => 'text', 'caption' => 'Номер плательщика');
		$this->fields_form['phone'] = array('type' => 'text', 'caption' => 'Номер телефона');
		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'Email');
		$this->fields_form['cost'] = array('type' => 'decimal', 'caption' => 'Сумма (руб)', 'comment' => 'Минимум ' . $this->config['minpay'] . 'р, максимум ' . $this->config['maxpay'] . 'р', 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		//$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Комментарий', 'mask'=>array('name'=>'all'));
		$this->fields_form['status'] = array('type' => 'list', 'listname' => 'status', 'readonly' => 1, 'caption' => 'Статус', 'mask' => array());
		$this->fields_form['error'] = array('type' => 'list', 'listname' => 'error', 'readonly' => 1, 'caption' => 'Ошибка', 'mask' => array());
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Дата', 'mask' => array());
	}


	/*
	* Создание счёта
	*/
	public function billingForm($summ, $comm, $data = array())
	{
		$this->prm_add = true;
		$param = array('showform' => 1, 'savePost' => true, 'setAutoSubmit' => true);

		$this->owner->setPostData('email', $data);
		$this->owner->setPostData('phone', $data);

		$argForm = array();
		$argForm['email'] = array('type' => 'email', 'caption' => 'Email', 'mask' => array('min' => 5)); // 'name'=>'email',
		$argForm['phone'] = array('type' => 'text', 'caption' => 'Телефон', 'mask' => array('name' => 'phone3'));
		$argForm['name'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $comm)); // name не попадает в БД
		if ($summ > 0)
			$argForm['cost'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $summ, 'min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		else
			$argForm['cost'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment' => 'Минимум ' . $this->config['minpay'] . 'р, максимум ' . $this->config['maxpay'] . 'р', 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		$this->lang['Save and close'] = 'Оплатить через ' . $this->caption;
		return $this->_UpdItemModul($param, $argForm);
	}

	/**
	 * Статус платежа
	 */
	public function statusForm($data)
	{
		//$data['child']
		$result = array('showStatus' => true, 'messages' => array());

		if (count($data) and $data['status'] < 2) {
			$result['messages'] = array(
				array('alert', 'Выполняется открытие страницы оплаты на ' . $this->caption),
				array('notice', '<small>Если у вас не открылось окно оплаты, возможно ваш браузер заблокировал открытие окна (Ваш браузер должен был выдать предупреждение об этом, кликните на всплывшее сообщение и разрешите данную операцию)</small>'),
			);
			$result['options'] = array('name' => 'form_yandex', 'action' => $this->config['actionURL'] . '"  target="_blank');
			$result['form'] = array(
				'FormComment' => array('type' => 'hidden', 'value' => 'Счёт№' . $data['child']['id']), // заголовок у отправителя
				'comment-needed' => array('type' => 'hidden', 'value' => 'false'), // не нужны коменты, ТОДУЖ брать опцию из конфига
				'label' => array('type' => 'hidden', 'value' => 'true'), //
				'mail' => array('type' => 'hidden', 'value' => '1'),
				'quickpay-form' => array('type' => 'hidden', 'value' => 'shop'),
				'receiver' => array('type' => 'hidden', 'value' => $this->config['yandex_id']),
				'short-dest' => array('type' => 'hidden', 'value' => $data['name']), // Комментарий у отправителя
				'submit-button' => array('type' => 'hidden', 'value' => 'Оплатить'),
				'sum' => array('type' => 'hidden', 'value' => $data['child']['cost']),
				'targets' => array('type' => 'hidden', 'value' => 'Счёт№' . $data['child']['id']), // Сообщение получателю
				'writable-targets' => array('type' => 'hidden', 'value' => 'false'),
				'writable-sum' => array('type' => 'hidden', 'value' => 'true'),
				'writable-targets' => array('type' => 'hidden', 'value' => 'false'),

				//'p2payment'=>array('type'=>'hidden','value'=>$this->id),
				//'destination'=>array('type'=>'hidden','value'=>$this->id),
				//'codepro'=>array('type'=>'hidden','value'=>$this->id),
			);

			$result['form']['sbmt'] = array('type' => 'submit', 'value' => 'Перейти на ' . $this->caption . ' для оплаты счета');
			$result['messages'][] = array('autoClick', '<a title="Отменить" id="autoClick">Автоматический переход через <i>5</i> сек.</a>');
			global $_tpl;
			$_tpl['onload'] .= 'wep.timerFunction(function(){$("#form_yandex").submit();}, \'#autoClick\', \'#form_yandex .sbmt\');';
		}

		return $result;
	}


	/***************************************************/
	/***************************************************/

	//http://unidoski.ru
	function redirectFromYa()
	{
		if (!isset($_GET['code'])) {
			header("Location: " . $this->URI_YM_AUTH . '?client_id=' . $this->config['yandex_cid'] . '&response_type=code&scope=' . urlencode(implode(' ', $this->SCOPE)) . '&redirect_uri=' . urlencode($this->REDIRECT_URI));
			die();
		}
		$CODE = $this->receiveOAuthToken($this->config['yandex_cid'], $_GET['code']);
		return '<h2>Код вставить в поле `TOKEN` для Яндекс.Деньги в конфиге модуля:<h2><textarea style="width:500px;height:150px;">' . $CODE . '</textarea>';
	}

	function yandexAuth($LOGIN, $PASS)
	{
		$param = array();
		$param['COOKIEJAR'] = $this->_CFG['_PATH']['temp'] . 'payyandex.txt';
		$param['REFERER'] = true;
		$html = static_tools::_http('http://passport.yandex.ru/passport?mode=auth&msg=money', $param);
		$param['POST'] = 'from=passport&idkey=22M1332881456_tFPe13IK&display=page&login=' . $LOGIN . '&passwd=' . $PASS . '&timestamp=1332880245212&login=xakki&passwd=dedmazai28';

		$param['COOKIEFILE'] = $param['COOKIEJAR'];
		$param['redirect'] = true;
		$html = static_tools::_http('http://passport.yandex.ru/passport?mode=auth&msg=money', $param);
		return $param['COOKIEJAR'];
	}

	function yandexGetCode($CODE, $LOGIN, $PASS, $PASS2)
	{

		$CF = $this->yandexAuth($LOGIN, $PASS);

		$SCOPE = implode(' ', $this->SCOPE);
		$URL = $this->URI_YM_AUTH . '?client_id=' . $CODE . '&response_type=code&scope=' . urlencode($SCOPE) . '&redirect_uri=' . urlencode($this->REDIRECT_URI);

		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['SSL'] = $this->SSL;
		$html = static_tools::_http($URL, $param);
		if (!$html['info']['redirect_url']) {
			return false;
		}

		/********/
		$param = array();
		$param['REFERER'] = $html['info']['url'];
		$param['redirect'] = true;
		$param['COOKIEFILE'] = $param['COOKIEJAR'] = $CF;
		$html = static_tools::_http($html['info']['redirect_url'], $param);

		$pos1 = mb_strpos($html['text'], 'window.location.replace("') + 25;
		$pos2 = mb_strpos($html['text'], '");');
		$URL = mb_substr($html['text'], $pos1, ($pos2 - $pos1));
		if (!$URL) return false;

		/********/
		$param = array();
		$param['REFERER'] = $html['info']['url'];
		//$param['redirect'] = true;
		$param['SSL'] = $this->SSL;
		$param['COOKIEFILE'] = $param['COOKIEJAR'] = $CF;
		$html = static_tools::_http($URL, $param);

		/********/
		$param = array();
		$param['REFERER'] = $html['info']['url'];
		//$param['redirect'] = true;
		$param['SSL'] = $this->SSL;
		//Получаем код формы
		$pos1 = strpos($html['text'], '<form method="post" name="checkpay"');
		if (!$pos1) {
			print_r('Не верные данные');
			return false;
		}
		$html['text'] = substr($html['text'], $pos1);
		$html['text'] = substr($html['text'], 0, (strpos($html['text'], 'form>') + 5));

		include_once($this->_CFG['_PATH']['wep_controllers'] . 'lib/simple_html_dom.php');
		$DOM = str_get_html($html['text']);
		// Берем урл
		$obj = $DOM->find('form');
		$URL = $obj[0]->attr['action'];
		if (!$URL) return false;
		$temp = parse_url($html['info']['url']);
		$URL = $temp['scheme'] . '://' . $temp['host'] . $URL;
		// находим все инпуты
		$obj = $DOM->find('input');
		$POST = array();
		if (count($obj)) {
			foreach ($obj as $r) {
				$POST[$r->attr['name']] = $r->attr['value'];
			}
		}
		$POST['passwd'] = $PASS2;
		$param['POST'] = $POST;
		$param['COOKIEFILE'] = $param['COOKIEJAR'] = $CF;
		$html = static_tools::_http($URL, $param);

		unlink($CF);
		//$html['text'] = htmlentities($html['text'],ENT_NOQUOTES,'windows-1251');//,'windows-1251' 'UTF-8'
		if (strpos($html['info']['redirect_url'], $this->REDIRECT_URI) !== false) {
			$result = parse_url($html['info']['redirect_url']);
			$result = explode('=', $result['query']);
			return array_pop($result);
		}
		else {
			// triger errror
			return false;
		}

	}

	/**
	 * Метод для обмена временного кода, полученного от сервера Яндекс.Денег
	 * после вызова метода authorize, на постоянный токен доступа к счету
	 * пользователя.
	 * @abstract
	 * @param $code string временный код (токен), подлежащий обмену на токен авторизации.
	 * Присутствует в случае успешного подтверждения авторизации пользователем.
	 * @param $REDIRECT_URI string URI, на который OAuth-сервер осуществляет передачу
	 * события результата авторизации. Значение этого параметра при посимвольном сравнении
	 * должно быть идентично значению REDIRECT_URI, ранее переданному в метод authorize.
	 * @return string при успешном выполнении возвращает токен авторизации пользователя
	 */
	private function  receiveOAuthToken($CLIENT_ID, $CODE)
	{
		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['HTTPHEADER'][] = 'Expect:';
		$param['POST']['grant_type'] = 'authorization_code';
		$param['POST']['client_id'] = $CLIENT_ID;
		$param['POST']['code'] = $CODE;
		$param['POST']['redirect_uri'] = $this->REDIRECT_URI;
		$param['SSL'] = $this->SSL;
		$param['FORBID'] = true;
		$param['USERAGENT'] = $this->YM_USER_AGENT;
		$html = static_tools::_http($this->URI_YM_TOKEN, $param);

		$response = json_decode($html['text'], TRUE);
		if (!$response or isset($response['error']) or !$response['access_token']) {
			// err
			return false;
		}
		return $response['access_token'];
	}

	/**
	 * Метод получения информации о текущем состоянии счета пользователя.
	 * Требуемые права токена: account-info
	 * @abstract
	 * @param $accessToken string токен авторизации пользователя
	 * @return YMAccountInfoResponse возвращает экземпляр класса AccountInfoResponse
	 */
	private function accountInfo($accessToken)
	{
		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['HTTPHEADER'][] = 'Expect:';
		$param['HTTPHEADER'][] = 'Authorization: Bearer ' . $accessToken;
		$param['SSL'] = $this->SSL;
		$param['FORBID'] = true;
		$param['POST'] = true;
		$param['USERAGENT'] = $this->YM_USER_AGENT;
		$html = static_tools::_http($this->URI_YM_API . '/account-info', $param);
		$response = json_decode($html['text'], TRUE);
		return $response;
	}


	/**
	 * Метод позволяет просматривать историю операций (полностью или частично)
	 * в постраничном режиме. Записи истории выдаются в обратном хронологическом
	 * порядке. Операции выдаются для постраничного отображения (ограниченное количество).
	 * Требуемые права токена: operation-history.
	 * @abstract
	 * @param $accessToken string токен авторизации пользователя
	 * @param $startRecord integer порядковый номер первой записи в выдаче. По умолчанию
	 * выдается с первой записи
	 * @param $records integer количество запрашиваемых записей истории операций.
	 * Допустимые значения: от 1 до 100, по умолчанию 30.
	 * @param $type string перечень типов операций, которые требуется отобразить.
	 * Типы операций перечисляются через пробел. В случае, если параметр
	 * отсутствует, выводятся все операции. Возможные значения: payment deposition.
	 * В качестве разделителя элементов списка используется пробел, элементы списка
	 * чувствительны к регистру.
	 * @return YMOperationHistoryResponse возвращает экземпляр класса
	 * OperationHistoryResponse
	 */
	public function operationHistory($accessToken, $startRecord = NULL, $records = NULL, $type = NULL)
	{
		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['HTTPHEADER'][] = 'Expect:';
		$param['HTTPHEADER'][] = 'Authorization: Bearer ' . $accessToken;
		$param['SSL'] = $this->SSL;
		$param['FORBID'] = true;
		$param['POST'] = array();
		if ($type != NULL)
			$param['POST']['type'] = $type;
		if ($startRecord != NULL)
			$param['POST']['start_record'] = $startRecord;
		if ($records != NULL)
			$param['POST']['records'] = $records;
		$param['USERAGENT'] = $this->YM_USER_AGENT;
		$html = static_tools::_http($this->URI_YM_API . '/operation-history', $param);
		if (!$html['text'] or $html['info']['http_code'] != 200)
			trigger_error('Ошибка запроса к Яндекс API <pre>' . print_r($html, true) . '</pre>', E_USER_WARNING);
		$response = json_decode($html['text'], TRUE);
		return $response;
	}


	/**
	 * Метод получения детальной информации по операции из истории.
	 * @abstract
	 * @param $accessToken string токен авторизации пользователя
	 * @param $operationId string идентификатор операции. Значение параметра соответствует
	 * либо значению поля operationId ответа метода operationHistory, либо, в
	 * случае если запрашивается история счета плательщика, значению поля
	 * paymentId ответа метода processPayment.
	 * @return YMOperationDetailResponse возвращает экземпляр класса
	 * OperationDetailResponse
	 */
	public function operationDetail($accessToken, $operationId)
	{
		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['HTTPHEADER'][] = 'Expect:';
		$param['HTTPHEADER'][] = 'Authorization: Bearer ' . $accessToken;
		$param['SSL'] = $this->SSL;
		$param['FORBID'] = true;
		$param['POST']['operation_id'] = $operationId;
		$param['USERAGENT'] = $this->YM_USER_AGENT;
		$html = static_tools::_http($this->URI_YM_API . '/operation-details', $param);
		$response = json_decode($html['text'], TRUE);
		return $response;
	}


	/*CRON*/
	function checkBill()
	{

		$temp = $this->qs('*', 'WHERE status=""', 'name');
		$DATA = array();
		foreach ($temp as $r) {
			//$key = preg_replace('/[^0-9A-zА-я\:\;\№]+/ui', '', 'Счёт№'.$r['id'].'; '.$r['name']);
			//$key = trim($key,';:№,.\s');
			$DATA[$r['id']] = $r;
		}

		$CNT = count($DATA);
		if (!$CNT) return '-нет выставленных счетов-';

		//$INFO = $this->accountInfo($this->config['yandex_token']);
		$INFO = $this->operationHistory($this->config['yandex_token'], NULL, NULL, 'deposition');

		if (!count($INFO['operations'])) return '-нет платежей , ' . $CNT . ' не оплачено-';
		$i = 0;
		foreach ($INFO['operations'] as $r) {
			$tempOP = $this->qs('id', 'WHERE operation_id="' . $r['operation_id'] . '"');
			if (count($tempOP)) continue;

			//date($r['datetime'])
			$INFO2 = $this->operationDetail($this->config['yandex_token'], $r['operation_id']);
			//$key = preg_replace('/[^0-9A-zА-я\:\;\№]+/ui','',$INFO2['message']);

			if (!isset($INFO2['message']) or mb_strpos($INFO2['message'], 'Счёт№') === false) continue;

			preg_match_all('|Счёт№([0-9]+)|', $INFO2['message'], $out, PREG_SET_ORDER);
			$key = $out[0][1];

			if (isset($DATA[$key])) {

				$this->id = $DATA[$key]['id'];
				$upd = array('cost' => $INFO2['amount'], 'tax' => ($DATA[$key]['cost'] - $INFO2['amount']), 'sender' => $INFO2['sender']);
				if ($INFO2['amount'] >= ($DATA[$key]['cost'] * 0.95)) {
					$upd['status'] = 'success';
					$upd['operation_id'] = $r['operation_id'];
					//$upd['money_source'] = 'wallet';
					$this->_update($upd);
					$this->owner->payTransaction($this->data[$this->id]['owner_id'], PAY_PAID);
				}
				else {
					$upd = array();
					$upd['status'] = '';
					$upd['error'] = 'small_money';
					//$upd['operation_id'] = $r['operation_id'];
					$this->_update($upd);
					trigger_error('Оплата услуг через яндекс - поступило мало денег [Поступило на счет ' . $INFO2['amount'] . ', Выписано ' . $DATA[$key]['cost'] . ', с учетом пошлины ' . ($DATA[$key]['cost'] * 0.95) . ', payYandex ID=' . $this->id . ']', E_USER_WARNING);
				}

				$i++;
				if ($i >= $CNT) {
					return '-Всё счета проверены-';
				}
			}
		}
		$this->clearOldData();
		return '-OK-';
	}

	/**
	 * Сервис служба очистки данных
	 * Отключает неоплаченные платежи
	 * @param $M - модуль платежной системы
	 * @param $leftTime - в секундах
	 */
	function clearOldData()
	{
		$leftTime = ($this->config['lifetime'] * 3600);
		$this->_update(array('status' => 'timeout', $this->mf_actctrl => 0), 'WHERE status="" and ' . $this->mf_timecr . '<"' . (time() - $leftTime) . '"');
		$this->owner->clearOldData($this->_cl, $leftTime);
	}
}


