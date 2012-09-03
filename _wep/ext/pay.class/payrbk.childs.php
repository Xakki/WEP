<?php
class payrbk_class extends kernel_extends {
	
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
			3 => 'Операция принята на обработку',
			5 => 'Операция исполнена',
		);

		$this->_enum['language'] = array(
			'ru' => 'Русский',
			'en' => 'English',
		);

		/*$this->cron[] = array('modul'=>$this->_cl,'function'=>'checkBill()','active'=>1,'time'=>300);
		$this->_AllowAjaxFn = array(
			'redirectFromYa'=>true
		);*/
		//$this->_Button = true;

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

		$this->config_form['actionURL'] = array('type' => 'text', 'caption' => 'actionURL', 'comment'=>'');
		$this->config_form['eshopId'] = array('type' => 'text', 'caption' => 'eshopId');
		$this->config_form['secretKey'] = array('type' => 'text', 'caption' => 'secretKey','comment'=>'');
		$this->config_form['recipientCurrency'] = array('type' => 'list', 'listname'=>'recipientCurrency', 'caption' => 'Префикс в номере счёта','comment'=>'');
		$this->config_form['allow_ip'] = array('type' => 'text', 'caption' => 'Разрешенные IP','comment'=>'');
		$this->config_form['preference'] = array('type' => 'list', 'list'=>'preference', 'caption' => 'Метод оплаты по умолчанию','comment'=>'Позволяет пропустить окно выбора оплаты');
		$this->config_form['language'] = array('type' => 'list', 'list'=>'language', 'caption' => 'Локализация','comment'=>'язык');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#F60;');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма','comment'=>'при пополнении счёта', 'style'=>'background-color:#F60;');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут','comment'=>'Время жизни счёта по умолчанию. Задается в часах. Максимум 1080 часов (45 суток)', 'style'=>'background-color:#F60;');
	}

	protected function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL'); // наименование услуги
		$this->fields['email'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields['amount'] = array('type' => 'float', 'width' => '11,2','attr' => 'NOT NULL');
		$this->fields['userName '] = array('type' => 'varchar', 'width' => 20,'attr' => 'NOT NULL','default'=>''); // № плательщика в системе
		//Статус операции
		$this->fields['paymentStatus'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL', 'default'=>'');
		//Дата и время исполнения операции в Системе RBK Money в формате " YYYY - MM - DD HH : MM : SS ".
		$this->fields['paymentData'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL', 'default'=>'');
		// Контрольная подпись оповещения об исполнении операции, которая используется для проверки целостности полученной информации и однозначной идентификации отправителя.Алгоритм формирования описан в разделе " Контрольная подпись данных ".
		$this->fields['hash'] = array('type' => 'varchar', 'width' => 63,'attr' => 'NOT NULL', 'default'=>''); 
		//В этом поле передается идентификатор операции в Системе RBK Money.
		$this->fields['paymentId'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL', 'default'=>0);
		//Идентификатор учетной записи Участника в Системе RBK Money . Является уникальным в Системе RBK Money .
		$this->fields['eshopAccount'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL', 'default'=>'');
	}

	/*function getButton($summ,$comm) {
		return '<iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/small.xml?uid=4100198176118&amp;button-text=01&amp;button-size=l&amp;button-color=orange&amp;targets=%d0%a3%d1%81%d0%bb%d1%83%d0%b3%d0%b8+%d0%b4%d0%bb%d1%8f+%d0%be%d0%b1%d1%8a%d1%8f%d0%b2%d0%bb%d0%b5%d0%bd%d0%b8%d1%8f+%e2%84%96&amp;default-sum='.$summ.'&amp;mail=on" width="auto" height="54"></iframe>';
	}*/

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		//$this->fields_form['sender'] = array('type' => 'text', 'caption' => 'Номер плательщика');
		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'Email');
		$this->fields_form['amount'] = array('type' => 'int', 'caption' => 'Сумма (руб)', 'comment'=>'Минимум '.$this->config['minpay'].'р, максимум '.$this->config['maxpay'].'р', 'default'=>100, 'mask'=>array('minint'=>$this->config['minpay'],'maxint'=>$this->config['maxpay']));
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
		$_tpl['onload'] .= '$("#form_paymethod").submit();';
		$DATA = array();
		$DATA['messages'] = array(
			array('alert','Выполняется открытие страницы оплаты на RBK.Money.'),
			array('notice','<small>Если у вас не открылось окно оплаты, возможно ваш браузер заблокировал открытие окна (Ваш браузер должен был выдать предупреждение об этом, кликните на всплывшее сообщение и разрешите данную операцию)</small>'),
			array('txt','После оплаты обновите <a href="javascript:window.location.reload();">страницу</a>, чтобы узнать состояние счёта.'),
		);
		$DATA['form'] = array(
			'_*features*_' => array('name'=>'paymethod','action'=>$this->config['actionURL'].'"  target="_blank')
			'eshopId'=>array('type'=>'hidden','value'=>$this->config['eshopId']),
			'orderId'=>array('type'=>'hidden','value'=>$data['id']), // заголовок у отправителя
			'serviceName'=>array('type'=>'hidden','value'=>$data['name']), // Комментарий у отправителя
			'recipientAmount'=>array('type'=>'hidden','value'=>$data['amount']),
			'recipientCurrency'=>array('type'=>'hidden','value'=>$this->config['recipientCurrency']),
			'successUrl'=>array('type'=>'hidden','value'=>'false'),****
			'failUrl'=>array('type'=>'hidden','value'=>'true'),****
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

	function redirectFromYa() {
		if(!isset($_GET['code'])) {
			header("Location: ".$this->URI_YM_AUTH . '?client_id='.$this->config['yandex_cid'].'&response_type=code&scope=' . urlencode(implode(' ',$this->SCOPE)) . '&redirect_uri=' . urlencode($this->REDIRECT_URI));
			die();
		}
		$CODE = $this->receiveOAuthToken($this->config['yandex_cid'],$_GET['code']);
		return '<h2>Код вставить в поле `TOKEN` для RBK.Money в конфиге модуля:<h2><textarea style="width:500px;height:150px;">'.$CODE.'</textarea>';
	}

	function yandexAuth($LOGIN,$PASS) {
		$param = array();
		$param['COOKIEJAR'] = $this->_CFG['_PATH']['temp'].'payyandex.txt';
		$param['REFERER'] = true;
		$html = static_tools::_http('http://passport.yandex.ru/passport?mode=auth&msg=money',$param);
		$param['POST'] = 'from=passport&idkey=22M1332881456_tFPe13IK&display=page&login='.$LOGIN.'&passwd='.$PASS.'&timestamp=1332880245212&login=xakki&passwd=dedmazai28';
		
		$param['COOKIEFILE'] = $param['COOKIEJAR'];
		$param['redirect'] = true;
		$html = static_tools::_http('http://passport.yandex.ru/passport?mode=auth&msg=money',$param);
		//$html['text'] = htmlentities($html['text'],ENT_NOQUOTES,'UTF-8');
		//print_r('<pre>');print_r($html);
		//print_r(file_get_contents($param['COOKIEJAR']));
		return $param['COOKIEJAR'];
	}

	function yandexGetCode($CODE,$LOGIN,$PASS,$PASS2) {

		$CF = $this->yandexAuth($LOGIN,$PASS);

		$SCOPE = implode(' ',$this->SCOPE);
		$URL = $this->URI_YM_AUTH . '?client_id='.$CODE.'&response_type=code&scope=' . urlencode($SCOPE) . '&redirect_uri=' . urlencode($this->REDIRECT_URI);

		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['SSL'] = $this->SSL;
		$html = static_tools::_http($URL,$param);
		if(!$html['info']['redirect_url']) {return false;}

		/********/
		$param = array();
		$param['REFERER'] = $html['info']['url'];
		$param['redirect'] = true;
		$param['COOKIEFILE'] = $param['COOKIEJAR'] = $CF;
		$html = static_tools::_http($html['info']['redirect_url'],$param);

		$pos1 = mb_strpos($html['text'],'window.location.replace("')+25;	
		$pos2 = mb_strpos($html['text'],'");');
		$URL = mb_substr($html['text'],$pos1,($pos2-$pos1));
		if(!$URL) return false;

		/********/
		$param = array();
		$param['REFERER'] = $html['info']['url'];
		//$param['redirect'] = true;
		$param['SSL'] = $this->SSL;
		$param['COOKIEFILE'] = $param['COOKIEJAR'] = $CF;
		$html = static_tools::_http($URL,$param);

		/********/
		$param = array();
		$param['REFERER'] = $html['info']['url'];
		//$param['redirect'] = true;
		$param['SSL'] = $this->SSL;
		//Получаем код формы
		$pos1 = strpos($html['text'],'<form method="post" name="checkpay"');
		if(!$pos1) { print_r('Не верные данные');return false;}
		$html['text'] = substr($html['text'], $pos1);	
		$html['text'] = substr($html['text'], 0, (strpos($html['text'],'form>')+5));

		include_once($this->_CFG['_PATH']['wep_phpscript'].'lib/simple_html_dom.php');
		$DOM = str_get_html($html['text']);
		// Берем урл
		$obj = $DOM->find('form');
		$URL = $obj[0]->attr['action'];
		if(!$URL) return false;
		$temp = parse_url($html['info']['url']);
		$URL = $temp['scheme'].'://'.$temp['host'].$URL;
		// находим все инпуты
		$obj = $DOM->find('input');
		$POST = array();
		if(count($obj)) {
			foreach($obj as $r) {
				$POST[$r->attr['name']] = $r->attr['value'];
			}
		}
		$POST['passwd'] = $PASS2;
		$param['POST'] = $POST;
		$param['COOKIEFILE'] = $param['COOKIEJAR'] = $CF;
		$html = static_tools::_http($URL,$param);
		
		unlink($CF);
		//$html['text'] = htmlentities($html['text'],ENT_NOQUOTES,'windows-1251');//,'windows-1251' 'UTF-8'
		//print_r('<pre>');print_r($POST);print_r($html);
		//print_r(file_get_contents($param['COOKIEJAR']));
		if(strpos($html['info']['redirect_url'],$this->REDIRECT_URI)!==false) {
			$result = parse_url($html['info']['redirect_url']);
			$result = explode('=',$result['query']);
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
	private function  receiveOAuthToken($CLIENT_ID, $CODE) {
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
		$html = static_tools::_http($this->URI_YM_TOKEN,$param);

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
	private function accountInfo($accessToken) {
		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['HTTPHEADER'][] = 'Expect:';
		$param['HTTPHEADER'][] = 'Authorization: Bearer ' . $accessToken;
		$param['SSL'] = $this->SSL;
		$param['FORBID'] = true;
		$param['POST'] = true;
		$param['USERAGENT'] = $this->YM_USER_AGENT;
		$html = static_tools::_http($this->URI_YM_API. '/account-info',$param);
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
	public function operationHistory($accessToken, $startRecord = NULL, $records = NULL, $type = NULL) {
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
		$html = static_tools::_http($this->URI_YM_API. '/operation-history',$param);
		if(!$html['text'] or $html['info']['http_code']!=200)
			trigger_error('Ошибка запроса к Яндекс API', E_USER_WARNING);
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
    public function operationDetail($accessToken, $operationId) {
		$param = array();
		$param['HTTPHEADER'][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$param['HTTPHEADER'][] = 'Expect:';
		$param['HTTPHEADER'][] = 'Authorization: Bearer ' . $accessToken;
		$param['SSL'] = $this->SSL;
		$param['FORBID'] = true;
		$param['POST']['operation_id'] = $operationId;
		$param['USERAGENT'] = $this->YM_USER_AGENT;
		$html = static_tools::_http($this->URI_YM_API. '/operation-details',$param);
		$response = json_decode($html['text'], TRUE);
		return $response;
    }


	/*CRON*/
	function checkBill() {

		$temp = $this->qs('*','WHERE status=""', 'name');
		$DATA = array();
		foreach($temp as $r) {
			//$key = preg_replace('/[^0-9A-zА-я\:\;\№]+/ui', '', 'Счёт№'.$r['id'].'; '.$r['name']);
			//$key = trim($key,';:№,.\s');
			$DATA[$r['id']] = $r;
		}

		$CNT = count($DATA);
		if(!$CNT) return '-нет выставленных счетов-';

		//$INFO = $this->accountInfo($this->config['yandex_token']);
		$INFO = $this->operationHistory($this->config['yandex_token'], NULL, NULL, 'deposition');

		if(!count($INFO['operations'])) return '-нет платежей , '.$CNT.' не оплачено-';
		$i=0;
		foreach($INFO['operations'] as $r) {
			$tempOP = $this->qs('id','WHERE operation_id="'.$r['operation_id'].'"');
			if(count($tempOP)) continue;

			//date($r['datetime'])
			$INFO2 = $this->operationDetail($this->config['yandex_token'], $r['operation_id']);
			//$key = preg_replace('/[^0-9A-zА-я\:\;\№]+/ui','',$INFO2['message']);

			if(!isset($INFO2['message']) or mb_strpos($INFO2['message'],'Счёт№')===false) continue;

			preg_match_all('|Счёт№([0-9]+)|', $INFO2['message'], $out, PREG_SET_ORDER);
			$key = $out[0][1];

			if(isset($DATA[$key])) {

				$this->id = $DATA[$key]['id'];
				$upd = array('amount'=>$INFO2['amount'], 'tax'=>($DATA[$key]['amount']-$INFO2['amount']), 'sender'=>$INFO2['sender']);
				if($INFO2['amount']>=($DATA[$key]['amount']*0.95)) {
					$upd['status'] = 'success';
					$upd['operation_id'] = $r['operation_id'];
					//$upd['money_source'] = 'wallet';
					$this->_update($upd);
					$this->owner->PayTransaction(1,$DATA[$key]['amount'],$this->data[$this->id]['owner_id']);				
				} else {
					$upd['status'] = 'refused';
					$upd['error'] = 'small_money';
					//$upd['operation_id'] = $r['operation_id'];
					$this->_update($upd);
				}

				$i++;
				if($i>=$CNT) {
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
	function clearOldData() {
		$leftTime = ($this->config['lifetime']*3600);
		$this->_update(array('status'=>'timeout', $this->mf_actctrl=>0), 'status="" and '.$this->mf_timecr.'<"'.(time()-$leftTime).'"');
		$this->owner->clearOldData($this->_cl, $leftTime);
	}
}


