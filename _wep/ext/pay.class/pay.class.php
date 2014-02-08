<?php

define('PAY_NOPAID', 0);
define('PAY_PAID', 1);
define('PAY_USERCANCEL', 2);
define('PAY_CANCEL', 3);
define('PAY_TIMEOUT', 4);

define('PAYCASH', 0); // оплата услуг
define('ADDCASH', 1); // пополнение счета
define('MOVECASH', 2); // перевод средств другому пользователю
define('OVERCASH', 3); // прочее

class pay_class extends kernel_extends
{

	protected function init()
	{
		parent::init();
		$this->caption = 'Pay System';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';

		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_ipcreate = true; //IP адрес пользователя с котрого была добавлена запись
		$this->mf_timestamp = true; // создать поле  типа timestamp
		$this->cf_childs = true;
		$this->mf_notif = true;

		$this->ver = '0.8.94';
		$this->default_access = '|0|';
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->index_fields['_key'] = '_key';
		$this->index_fields['from_user'] = 'from_user';
		$this->index_fields['to_user'] = 'to_user';
		$this->index_fields['status'] = 'status';
		$this->_AllowAjaxFn['statusForm'] = true;
		$this->ordfield = 'id DESC';

		$this->lang['lk_name'] = 'Личный счет';

		$this->cron[] = array('modul' => $this->_cl, 'function' => 'sendNotifPay()', 'active' => 1, 'time' => 300, 'caption' => 'Оповещение по успешной оплате');
		$this->cron[] = array('modul' => $this->_cl, 'function' => 'sendNotifNoPay()', 'active' => 1, 'time' => 300, 'caption' => 'Оповещение по не уплаченным счетам');
	}

	protected function _create_conf()
	{ /*CONFIG*/
		parent::_create_conf();

		$this->config['curr'] = 'руб.';
		$this->config['NDS'] = 13;
		//$this->config['notifPeriod'] = 13;
		$this->config['subjectNoPay'] = 'Не оплаченный счет на сумму #cost#';
		$this->config['notifNoPay'] = '<ul><li>#name#<li>Счёт № #id#<li>Сумма #cost#<li>Оплата с помощью #payCaption#<li>Счет создан #payDate#</ul><hr/>';
		$this->config['subjectPay'] = 'Cчет #name# оплачен';
		$this->config['notifPay'] = '<p>Спасибо , за пользование нашими услугами.</p><ul><li>#name#<li>Счёт № #id#<li>Сумма #cost#<li>Оплата с помощью #payCaption#<li>Счет создан #payDate#</ul><hr/>';

		$this->config_form['curr'] = array('type' => 'text', 'caption' => 'Название валюты');
		$this->config_form['NDS'] = array('type' => 'text', 'caption' => 'НДС %');
		$this->config_form['subjectNoPay'] = array('type' => 'text', 'caption' => 'Тема в письме оповещения');
		$this->config_form['notifNoPay'] = array(
			'type' => 'ckedit',
			'caption' => 'Оповещение о неоплаченном счете',
			'paramedit' => array(
				'CKFinder' => 1,
				'height' => 350,
				'fullPage' => 'true',
				'toolbarStartupExpanded' => 'false'));
	}

	protected function _create()
	{
		parent::_create();
		$this->fields['to_user'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['from_user'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL');
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['paytype'] = array('type' => 'tinyint', 'width' => 32, 'attr' => 'NOT NULL', 'default' => PAYCASH);
		$this->fields['pay_modul'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['_key'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => ''); // product1234 = название модуля + ID
		$this->fields['_eval'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['json_data'] = array('type' => 'text');
		$this->fields['mailnotif'] = array('type' => 'int', 'width' => 1, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['paylink'] = array('type' => 'text');
		$this->fields['email'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '');

		$this->_enum['status'] = array(
			PAY_NOPAID => 'Неоплачено',
			PAY_PAID => 'Оплачено',
			PAY_USERCANCEL => 'Отменено пользователем',
			PAY_CANCEL => 'Отменено магазином',
			PAY_TIMEOUT => 'Истекло время ожидания',
		);
		$this->_enum['paytype'] = array(
			PAYCASH => 'Услуга',
			ADDCASH => 'Пополнение',
			MOVECASH => 'Перевод',
			OVERCASH => 'Прочее',
		);
		// TODO !!!!
		$this->successUrl = $_SERVER['REQUEST_URI'];
		$this->failUrl = $_SERVER['REQUEST_URI'];

	}

	public function _childs()
	{
		parent::_childs();
		$this->create_child('payhistory');
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);

		$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname' => array('class' => 'users', 'nameField' => 'concat("№",tx.id," ",tx.name)'), 'readonly' => 1, 'caption' => 'Создатель', 'mask' => array());
		$this->fields_form['from_user'] = array('type' => 'list', 'listname' => array('class' => 'users', 'nameField' => 'concat("№",tx.id," ",tx.name)'), 'readonly' => 1, 'caption' => 'От кого', 'comment' => 'От кого переведены средства', 'mask' => array());
		$this->fields_form['to_user'] = array('type' => 'list', 'listname' => array('class' => 'users', 'nameField' => 'concat("№",tx.id," ",tx.name)'), 'readonly' => 1, 'caption' => 'Кому', 'comment' => 'Кому переведены средства', 'mask' => array());
		$this->fields_form['cost'] = array('type' => 'decimal', 'readonly' => 1, 'caption' => 'Сумма', 'mask' => array());
		$this->fields_form['name'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Комментарий', 'mask' => array());
		$this->fields_form['pay_modul'] = array('type' => 'list', 'listname' => 'pay_modul', 'readonly' => 1, 'caption' => 'Платежный модуль', 'mask' => array());
		$this->fields_form['email'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Email');
		$this->fields_form['status'] = array('type' => 'list', 'listname' => 'status', 'readonly' => 1, 'caption' => 'Статус', 'mask' => array());
		$this->fields_form['paytype'] = array('type' => 'list', 'listname' => 'paytype', 'readonly' => 1, 'caption' => 'Тип', 'mask' => array());

		$this->fields_form[$this->mf_timecr] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Создание', 'mask' => array('onetd' => 'Дата'));
		$this->fields_form[$this->mf_timestamp] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Обновление', 'mask' => array('onetd' => 'close'));

		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'IP', 'mask' => array());
		$this->fields_form['json_data'] = array('type' => 'textarea', 'readonly' => 1, 'caption' => 'JSON DATA', 'mask' => array('fview' => 1));

	}

	function _getlist($listname, $value = 0)
	{
		$data = array();
		if ($listname == 'pay_modul' || $listname == 'pay_modul2') {
			if ($listname == 'pay_modul2' and static_main::_prmUserCheck())
				$data['pay'] = array('css' => 'ico_pay', '#name#' => 'Оплата со счета. На счете ' . $_SESSION['user']['balance'] . ' ' . $this->config['curr'], '#id#' => 'pay');
			foreach ($this->childs as $key => &$value) {
				if (isset($value->pay_systems))
					$data[$key] = array('css' => 'ico_' . $key, '#name#' => $value->caption, '#id#' => $key);
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
	public function getPayList($key = NULL, $user = NULL, $status = array())
	{
		$data = array('#config#' => $this->config);
		$q = 't1 WHERE 1=1 ';

		if (!is_null($key))
			$q .= ' and t1._key = "' . $this->SqlEsc($key) . '"';

		if (!is_null($user))
			$q .= ' and (t1.`' . $this->mf_createrid . '` = ' . $user . ' or t1.`to_user` = ' . $user . ' or t1.`from_user` = ' . $user . ')';

		if (count($status) and $status = implode(',', $status))
			$q .= ' and t1.status IN (' . $status . ')';

		$data['#list#'] = $this->qs('t1.*', $q . ' ORDER BY t1.id DESC');

		$userlist = array();
		$data['userId'] = $user;
		foreach ($data['#list#'] as &$r) {
			// приход или расход
			$r['#sign#'] = false;
			if ($user && $user == $r['to_user'])
				$r['#sign#'] = true;

			if (!isset($userlist[$r['from_user']]))
				$userlist[$r['from_user']] = $r['from_user'];
			if (!isset($userlist[$r['to_user']]))
				$userlist[$r['to_user']] = $r['to_user'];
			if (!isset($userlist[$r[$this->mf_createrid]]))
				$userlist[$r[$this->mf_createrid]] = $r[$this->mf_createrid];

			$r['#status#'] = $this->_enum['status'][$r['status']];
			$r['#paytype#'] = $this->_enum['paytype'][$r['paytype']];


			if (isset($this->childs[$r['pay_modul']])) {
				$r['#pay_modul#'] = $this->childs[$r['pay_modul']]->caption;
				$r['#lifetime#'] = $this->childs[$r['pay_modul']]->config['lifetime'];
				$r['#formType#'] = $this->childs[$r['pay_modul']]->pay_formType;
				$r['#leftTime#'] = ($r['mf_timecr'] + ($r['#lifetime#'] * 3600));
			}
			else {
				$r['#pay_modul#'] = static_main::m('lk_name', array(), $this);
			}
		}

		if (count($userlist)) {
			_new_class('ugroup', $UGROUP);
			$data['#users#'] = $UGROUP->childs['users']->_query('t1.*,t2.level,t2.name as gname', 't1 JOIN ' . $UGROUP->tablename . ' t2 ON t1.owner_id=t2.id WHERE t1.id IN (' . implode(',', $userlist) . ')', 'id');
		}
		$data['#config#'] = $this->config;
		return $data;
	}


	/**
	 * Формы выставления счёта пользователю
	 * status production
	 */
	public function billingForm($payData, $addInfo = array())
	{
		//$summ, $key, $comm='', $eval=''
		global $_tpl;
		$data = static_main::tplMess('errdata'); // Формат для вывода сообщения в шаблон

		$resFlag = 0;
		// 0 : выводим варианты оплаты
		// -1 : нет прав доступа
		// -2 : ошибка в запросе
		// -3 : прочие ошибки

		//eval($eval);
		if (!isset($payData['cost']))
			$payData['cost'] = null;
		if (!isset($payData['pay_modul']) and isset($_POST['pay_modul']))
			$payData['pay_modul'] = $_POST['pay_modul'];
		if (!isset($payData['_key']) or !$payData['_key'])
			return $data;
		if (!isset($payData['name']) or !$payData['name'])
			return $data;

		if (!isset($payData['paytype']))
			$payData['paytype'] = PAYCASH;

		if (isset($payData['pay_modul']) and $payData['pay_modul'] == 'pay' and $this->allowUserBalance($payData)) {
			// ONLY FOR $payData['paytype'] = PAYCASH;
			$payData['from_user'] = $_SESSION['user']['id'];
			$payData['to_user'] = 1;
			$payData['status'] = PAY_PAID;

			/// ФОРМА подтверждения
			$param = array('captchaOn' => true);
			$last = round(($_SESSION['user']['balance'] - $payData['cost']), 2);
			$param['lang']['_info'] = '<div>На вашем счету ' . $_SESSION['user']['balance'] . ' ' . $this->config['curr'] . '</div>
			<div>После оплаты останется ' . $last . ' ' . $this->config['curr'] . '</div>';
			$param['lang']['sbmt'] = 'Оплатить';

			$data = $this->confirmForm($param);
			$resFlag = $data['flag'];

			if ($resFlag == 1) {
				if ($this->payAdd($payData, $addInfo)) {
					$this->saveLog($this->id, $payData['name']);
					return $this->statusForm($this->id);
				}
				else
					$resFlag = -3;
			}


		}
		elseif (isset($payData['pay_modul']) and $this->isPayModul($payData['pay_modul'])) {
			// Если есть уже такой счет и он еще не оплачен, то информацию/форму для оплаты счета
			if ($id = $this->getIdBill($payData['cost'], $payData['_key'], $payData['name'], $payData['pay_modul'])) {
				return $this->statusForm($id);
			}
			else {
				$CHILD = & $this->childs[$payData['pay_modul']];
				list($data, $resFlag) = $CHILD->billingForm($payData['cost'], $payData['name'], $addInfo);

				if ($resFlag == FORM_STATUS_OK) {
					$payData['status'] = PAY_NOPAID;
					$payData['from_user'] = $this->checkPayUsers($payData['pay_modul']); // User плат. системы


					if (!isset($payData['to_user']) or !$payData['to_user']) {
						// пополнение или оплата услуги
						if ($payData['paytype'] == ADDCASH)
							$payData['to_user'] = $_SESSION['user']['id'];
						else
							$payData['to_user'] = 1;
					}

					if ($this->payAdd($payData, $addInfo)) {
						$this->saveLog($this->id, $payData['name']);
						return $this->statusForm($this->id);
					}
					else
						$resFlag = FORM_STATUS_ERROR;

				}
			}

		}
		else {
			// TODO : возможность оплты со своего счета

			$argForm = array();

			if ($this->allowUserBalance($payData))
				$argForm['pay_modul'] = array('type' => 'list', 'listname' => 'pay_modul2', 'viewType' => 'button', 'css' => 'paytype', 'caption' => 'Выбирите метод оплаты', 'mask' => array('min' => 1));
			else
				$argForm['pay_modul'] = array('type' => 'list', 'listname' => 'pay_modul', 'viewType' => 'button', 'css' => 'paytype', 'caption' => 'Выбирите метод оплаты', 'mask' => array('min' => 1));

			$argForm['sbmt'] = array('type' => 'submit', 'value' => array());
			$this->prm_add = true;
			$this->id = null;
			list($data, $resFlag) = $this->_UpdItemModul(array('showform' => 1, 'savePost' => true, 'captchaOn' => false), $argForm);
			//$data['messages'][] = array('info','Выберите вариант оплаты.');
		}

		$data['#summ#'] = $payData['cost'];
		$data['#comm#'] = $payData['name'];
		$data['#resFlag#'] = $resFlag;
		$data['#config#'] = $this->config;
		$data['tpl'] = '#pay#billingForm'; //'#pay#billing'
		/*global $PGLIST;
		if($PGLIST)
			$DATA['#contentID#'] = $PGLIST->contentID;*/

		return $data;
	}

	/**
	 * Проверка доступности оплаты услуги с помощью счета
	 * status production
	 */
	public function allowUserBalance($payData)
	{
		if ($payData['paytype'] == PAYCASH and static_main::_prmUserCheck() and $payData['cost'] and $_SESSION['user']['balance'] >= $payData['cost'])
			return true;
		return false;
	}

	/**
	 * Получить информацию
	 * AJAX Allow
	 * status production
	 */
	public function statusForm($id = null, $checkPermition = null)
	{
		$result = array('#resFlag#' => FORM_STATUS_ERROR, 'flag' => FORM_STATUS_ERROR);
		if (is_null($id))
			$id = (int)$_GET['id'];
		else
			$id = (int)$id;
		if (!$id) return $result;

		$data = $this->getItem($id, $checkPermition);

		if (count($data)) {
            $result['#resFlag#'] = FORM_STATUS_DEFAULT;
			if (isset($this->childs[$data['pay_modul']])) {
				$CHILD = & $this->childs[$data['pay_modul']];
				$result['showFrom'] = $CHILD->statusForm($data);
				if (isset($result['showFrom']['showStatus']) and $result['showFrom']['showStatus'] === true) {

					$param = array('captchaOn' => true, 'confirmValue' => $id, 'lang' => array('sbmt' => 'Подтверждаю отмену счета'));
					$result['confirmCancel'] = $this->confirmForm($param);
                    $result['#resFlag#'] = $result['confirmFlag'] = $result['confirmCancel']['flag'];

                    if ($result['confirmFlag'] == FORM_STATUS_OK) {
						unset($result['showFrom']);
						$result['messages'] = $this->cancelPay($id, $CHILD);
					}
					else {
						$result['showStatus'] = $data;
                    }
				}
			}
			else
				$result['showStatus'] = $data;

			$result['#config#'] = $this->config;

		}
		$result['tpl'] = '#pay#statusForm';
		return $result;
	}

	/**
	 * Отменить счет
	 * status production
	 */
	protected function cancelPay($id, $CHILD)
	{
		$status = PAY_USERCANCEL;
		$messages = array();
		$this->id = $id;
		$upd = array('status' => $status);
		if ($this->_update($upd)) {
			$CHILD->cancelPay($id);
			$messages[] = array('ok', 'Счет #' . $id . ' успешно отменен!');
			if ($status == PAY_USERCANCEL)
				$this->saveLog($id, 'Счет #' . $id . ' отменен пользователем!');
			else
				$this->saveLog($id, 'Счет #' . $id . ' отменен!');
		}
		else
			$messages[] = array('error', 'Ошибка при отмене счета #' . $id . '! ' . static_main::m('feedback'));
		return $messages;
	}


	/**
	 * Получить данные
	 * status production
	 */
	public function getItem($id, $checkPermition = null)
	{
		if (!$id) return array();

		$sql = 'WHERE id="' . (int)$id . '"';

		$sqlPermission = array();
		if (!is_null($checkPermition)) {
			if (is_string($checkPermition)) {
				$sqlPermission[] = '_key="' . $this->SqlEsc($checkPermition) . '"';
			}
			else {
				if ($checkPermition === true) {
					if (isset($_SESSION['user']['id']))
						$checkPermition = $_SESSION['user']['id'];
					else
						$checkPermition = 0;
				}
				$sqlPermission[] = '`' . $this->mf_createrid . '` = ' . (int)$checkPermition . '';
			}
		}
		if (isset($_GET['payhash'])) {
			// TODO
			$sqlPermission[] = 'md5(CONCAT(id,email,mf_timecr)) = "' . $this->SqlEsc($_GET['payhash']) . '"';
		}

		if (count($sqlPermission))
			$sql .= ' AND (' . implode(' or ', $sqlPermission) . ')';

		$data = $this->qs('*', $sql);

		if (count($data)) {
			$data = $data[0];
			$data['#status#'] = $this->_enum['status'][$data['status']];

			if (isset($this->childs[$data['pay_modul']])) {
				$CHILD = $this->childs[$data['pay_modul']];
				if ($CHILD->pay_formType === true)
					$data['#payLink#'] = '/_js.php?_modul=pay&_func=statusForm&id=' . $data['id'] . '" onclick="return wep.JSWin({type:this,onclk:\'reload\'});';
				elseif ($CHILD->pay_formType)
					$data['#payLink#'] = $CHILD->pay_formType;

				$child = $CHILD->qs('*', 'WHERE owner_id="' . (int)$id . '"');
				$data['child'] = $child[0];
			}
			return $data;
		}

		return array();
	}

	/**
	 * Предназначено для ф billingForm() у платежной системы
	 * Передает полученные данные в POST
	 * status production
	 * TODO  - перенести в ядро
	 */
	public function setPostData($name, $data = array())
	{
		if (!isset($_POST[$name]) or !$_POST[$name]) {
			if (isset($data[$name]) AND $data[$name])
				$_POST[$name] = $data[$name];
			elseif (isset($_SESSION['user'][$name]))
				$_POST[$name] = $_SESSION['user'][$name];
			elseif (isset($_COOKIE['field_' . $name]))
				$_POST[$name] = $_COOKIE['field_' . $name];
		}
		return $_POST[$name];
	}

	/*********************************************/
	/********************************************/

	/**
	 * Проверяем, включен ли указанный платежный модуль
	 * status production
	 */
	public function isPayModul($pay_modul)
	{
		if ($pay_modul and isset($this->childs[$pay_modul]) and isset($this->childs[$pay_modul]->pay_systems))
			return true;
		return false;
	}

	/**
	 * Если есть уже такой счет и он еще не оплачен, то выводим информацю о нем
	 * getIdBill
	 */
	public function getIdBill($summ, $key, $comm, $pay_modul)
	{
		$sql = 'WHERE _key="' . $this->SqlEsc($key) . '" and name="' . $this->SqlEsc($comm) . '" and pay_modul="' . $this->SqlEsc($pay_modul) . '" and status=0';
		if ($summ > 0)
			$sql .= ' AND cost="' . (int)$summ . '" ';
		$payData = $this->qs('id', $sql); // status=0 and - один платеж
		if (!count($payData))
			return 0;
		return $payData[0]['id'];
	}


	/**
	 * Функция оплаты и перевода средств
	 * @param $data
	 * @param $addInfo
	 * return 1 - Успешно
	 * return 0 - ошибка данных
	 * status develop
	 */
	function payAdd($data, $addInfo = array())
	{
		if (!isset($data['status']))
			$data['status'] = PAY_PAID;

		if (!isset($data['email']))
			$data['email'] = $this->setPostData('email', $addInfo);

		if (!isset($data['json_data']) and isset($addInfo['json_data']))
			$data['json_data'] = $addInfo['json_data'];

		$res = $this->_add($data, false);

		if ($res) {
			$this->transaction($data, $data['status']);
			if ($this->isPayModul($data['pay_modul']) and $this->childs[$data['pay_modul']]->id)
				$this->childs[$data['pay_modul']]->_update(array('owner_id' => $this->id));
		}

		return $res;
	}

	/**
	 * В случае успешной оплаты, переводим средва му пользователями, ставим соотв. статус, выполняем необходимые операции
	 * status develop
	 */
	public function payTransaction($id, $status)
	{
		$this->id = $id;
		$data = current($this->_select());

		if (!count($data)) return false;

		$upd = array(
			'status' => $status,
			'mailnotif' => 0
		);
		$res = $this->_update($upd, false);

		if ($res) {
			$this->transaction($data, $status);
		}

		return $res;
	}

	/**
	 * Завершение транзакции
	 * status develop
	 */
	private function transaction($data, $newStatus)
	{
		if ($newStatus == PAY_PAID) {
			_new_class('ugroup', $UGROUP);
			$this->SQL->execSQL('UPDATE ' . $UGROUP->childs['users']->tablename . ' SET balance=balance-' . $data['cost'] . ' WHERE id=' . $data['from_user']);
			$this->SQL->execSQL('UPDATE ' . $UGROUP->childs['users']->tablename . ' SET balance=balance+' . $data['cost'] . ' WHERE id=' . $data['to_user']);
			if ($data['_eval']) {
				if (substr($data['_eval'], 0, 3) != 'if(') // для совместимости со старым форматом
				{
					$temp = explode('::', $data['_eval']);
					$data['_eval'] = 'if(_new_class("' . $temp[0] . '",$M)){$M->' . $temp[1] . ';}';

				}
				eval($data['_eval']);

			}
		}
	}

	/**
	 * Проверяем есть ли группа и пользователи для системы платежей и возвращаем ID плат.сист. если указана плат.сист.
	 * status develop
	 */
	function checkPayUsers($paychild = '', $flag = false)
	{
		_new_class('ugroup', $UGROUP);
		$id = 0;
		// 51 - уникальный код уровня доступа для этого модуля
		// Группа Платежные системы
		$data1 = $UGROUP->_query('*', 'WHERE level = "51"');
		if (count($data1) != 1) {
			$UGROUP->_add(array(
				'level' => '51',
				'name' => $this->caption,
				'wep' => '0',
				'negative' => '-1000000000',
			));
		}
		else
			$UGROUP->id = $data1[0]['id'];

		// Юзеры Платежные системы
		$data2 = $UGROUP->childs['users']->_query('*', 'WHERE owner_id = ' . $UGROUP->id, 'email');

		// юзер по умолчанию
		$email = 'pay@' . $this->_CFG['site']['www'];
		if (!isset($data2[$email])) {
			$UGROUP->childs['users']->_add(array(
				'email' => $email,
				'name' => 'Pay',
				'owner_id' => $UGROUP->id,
				'parent_id' => 0,
			));
			$id = $UGROUP->childs['users']->id;
		}
		else
			$id = $data2[$email]['id'];

		if (count($this->childs)) {
			foreach ($this->childs as &$childs) {
				$email = $childs->_cl . '@' . $this->_CFG['site']['www'];
				if (!isset($data2[$email])) {
					$UGROUP->childs['users']->_add(array(
						'email' => $email,
						'name' => $childs->caption,
						'owner_id' => $UGROUP->id,
						'parent_id' => 0,
						'pass' => ''
					));
					$data2[$email] = current($UGROUP->childs['users']->data);
				}
				if ($paychild == $childs->_cl)
					$id = $data2[$email]['id'];
			}
			unset($child);
		}
		if ($flag and $paychild)
			return $data2[$paychild . '@' . $this->_CFG['site']['www']];
		return $id;
	}


	/********************************************/
	/********************************************/
	/********************************************/

	/** Форма перевода средств
	 * status develop
	 */
	function payMove($param = array())
	{
		$data = array();

		$query = 't1.`id` != ' . $_SESSION['user']['id'] . ' and t1.`active`=1';
		if (isset($param['cls']))
			$query .= $param['cls'];

		if (isset($param['POST'])) {
			$param['POST']['pay'] = (int)$param['POST']['pay'];
			$param['POST']['users'] = (int)$param['POST']['users'];
			if (!$param['POST']['pay']) {
				$data['respost'] = array('flag' => 0, 'mess' => 'Неверные данные.');
			}
			else {
				if (isset($param['POST']['plus'])) {
					$u1 = $_SESSION['user']['id'];
					$u2 = (int)$param['POST']['users'];
					$txt = 'Пополнение баланса';
				}
				else {
					$u2 = $_SESSION['user']['id'];
					$u1 = (int)$param['POST']['users'];
					$txt = 'Снятие со счёта';
				}
				if (isset($param['POST']['name']))
					$txt .= ': ' . $param['POST']['name'];
				$summ = (int)$param['POST']['pay'];
				$flag = 0;
				list($mess, $balance) = $this->checkBalance($u1, $summ);
				if (!$mess) {
					$payData = array(
						'cost' => $summ,
						'_key' => 'move',
						'name' => $txt,
						'from_user' => $u1,
						'to_user' => $u2,
						'status' => PAY_PAID,
						'paytype' => MOVECASH
					);
					$flag = $this->payAdd($payData);
				}
				$data['respost'] = array('flag' => $flag, 'mess' => $mess, 'balance' => $balance);
			}
		}

		_new_class('ugroup', $UGROUP);
		$query = 'WHERE ' . $query;
		$data['users'] = $UGROUP->childs['users']->_query('t1.*,t2.name as gname', 't1 JOIN ' . $UGROUP->tablename . ' t2 ON t1.owner_id=t2.id and t2.active=1 ' . $query . ' ORDER BY t1.id', 'id');

		return $data;
	}

	/**
	 * $flag = -1 - вывод форма подтверждения
	 * $flag = 0 - ошибка
	 * $flag = 1 - успешная оплата
	 * status develop
	 */
	function payDialog($from_user, $to_user, $summ, $functOK = NULL, $paramOK = array())
	{
		$refer = NULL;
		$flag = 0; //Ошибка
		$mess = '';
		$uq = md5($from_user . $to_user . $summ);
		$n = 'paycode' . $uq;
		list($mess, $balance) = $this->checkBalance($from_user, $summ);
		if (isset($_SESSION[$n]) and isset($_POST[$uq])) {
			//Действие
			unset($_SESSION[$n]);
			list($mess, $balance) = $this->checkBalance($from_user, $summ);
			if ($mess == '') {
				$payData = array(
					'cost' => $summ,
					'_key' => 'refill',
					'name' => 'tempo',
					'from_user' => $from_user,
					'to_user' => $to_user,
					'status' => PAY_PAID,
					//'paytype' => MOVECASH
				);
				if ($this->payAdd($payData)) {
					$flag = 1; //Оплата
					// Выполняем пользовательскую функцию при успешной оплате
					if (!is_null($functOK)) {
						list($flag, $mess, $refer) = call_user_func_array($functOK, $paramOK);
						if ($flag == 1) {
							$this->addPayMess($mess);
						}
						else {
							$this->payBack($from_user, $to_user, $summ);
						}
					}
				}
				else
					$mess = static_main::m('pay_err', $this);
			}
		}
		else {
			$flag = -1; //Выводим форму подтверждения
			$_SESSION[$n] = true;
		}
		_new_class('ugroup', $UGROUP);
		if (!$refer)
			$refer = array('Вернуться в корзину', $_SERVER['HTTP_REFERER']);
		$DATA = array(
			'flag' => $flag,
			'mess' => $mess,
			'balance' => $balance,
			'code' => $uq,
			'summ' => $summ,
			'm' => $UGROUP->config['payon'],
			'to_user' => $to_user,
			'#post#' => $_POST,
			'#refer#' => $refer,
		);
		return $DATA;
	}

	/**
	 * Функция проверки средств
	 * status develop
	 */
	function checkBalance($from_user, $summ)
	{
		_new_class('ugroup', $UGROUP);
		$temp = $UGROUP->childs['users']->_query('t1.id,t1.owner_id,t1.name,t1.balance,t2.negative', 't1 JOIN ' . $UGROUP->tablename . ' t2 ON t2.id=t1.owner_id WHERE t1.`active`=1 and t2.`active`=1 and t1.`id` = ' . $from_user);

		if (!count($temp)) return array(static_main::m('pay_nouser', $this), 0);
		$d = $temp[0]['balance'] - $summ;
		$mess = '';
		if ($temp[0]['balance'] < 0)
			$mess = static_main::m('pay_nobalance', array($temp[0]['balance'] . ' ' . $UGROUP->config['payon']), $this);
		elseif ($temp[0]['negative'] and $d < 0) {
			$def = ($temp[0]['negative'] + $d);
			if ($def < 0)
				$mess = static_main::m('pay_nomonney', array($def . ' ' . $UGROUP->config['payon']), $this);
		}
		elseif ($d < 0)
			$mess = static_main::m('pay_nomonney', array(abs($d) . ' ' . $UGROUP->config['payon']), $this);
		return array($mess, $temp[0]['balance']);
	}

	/**
	 * Отмена оплаты
	 * status develop
	 */
	private function payBack($from_user, $to_user, $summ)
	{
		_new_class('ugroup', $UGROUP);

		$this->SQL->execSQL('UPDATE ' . $UGROUP->childs['users']->tablename . ' SET balance=balance+' . $summ . ' WHERE id=' . $from_user);
		$this->SQL->execSQL('UPDATE ' . $UGROUP->childs['users']->tablename . ' SET balance=balance-' . $summ . ' WHERE id=' . $to_user);

		return $this->_delete();
	}

	/**
	 * Коммент для платежа
	 * status develop
	 */
	private function addPayMess($mess)
	{
		if (!$this->id) return 0;
		$data = array(
			'name' => $mess);
		return $this->_update($data);
	}


	/**
	 * возвращает список платежных систем
	 * status develop
	 */
	function get_pay_systems()
	{
		$ps = array();
		foreach ($this->childs as $k => $v) {
			if (isset($v->pay_systems)) {
				$ps[$k] = $v->pay_systems;
			}
		}
		return $ps;
	}

	/**
	 * проверяет формат пополняемой суммы
	 * status develop
	 */
	function check_amount($amount)
	{
		if (is_numeric($amount) && $amount > 0) {
			$dot_pos = strpos($amount, '.');
			if ($dot_pos == true) {
				if ((strlen($amount) - $dot_pos - 1) <= 2)
					return true;
				else
					return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Сервис служба очистки данных
	 * Отключает неоплаченные платежи
	 * @param $M - модуль платежной системы
	 * @param $leftTime - в секундах
	 * status develop
	 */
	function clearOldData($M, $leftTime)
	{
		$this->_update(array('status' => '4'), 'WHERE status=0 and ' . $this->mf_timecr . '<"' . (time() - $leftTime) . '" and pay_modul="' . $M . '"');
	}

	/**
	 * Оповещение об успешной оплате улуги
	 * status develop
	 */
	function sendNotifPay()
	{
		if (!_new_class('mail', $MAIL)) return '-Ошибка Почтовой службы-';

		$list = $this->_query('*', 'WHERE mailnotif=0 and status=' . PAY_PAID, 'id');

		if (count($list)) {
			$this->id = array_keys($list);
			$this->_update(array('mailnotif' => time()), null, false);
		}

		foreach ($list as $row) {
			if (!$row['email']) continue;
			$CHILD = & $this->childs[$row['pay_modul']];

			$datamail = array(
				'subject' => $this->config['subjectPay'],
				'text' => $this->config['notifPay']
			);
			$datamail = str_replace(
				array('#id#', '#name#', '#cost#', '#payCaption#', '#payDate#'),
				array($row['id'], $row['name'], $row['cost'], $CHILD->caption, date('Y-m-d H-i-s', $row['mf_timecr'])),
				$datamail
			);
			$datamail['creater_id'] = -1;
			$datamail['mail_to'] = $row['email'];

			$MAIL->reply = 0;
			//$MAIL->config['mailcron'] = 0;// немедленная отправка письма
			if (!$MAIL->Send($datamail)) {
				trigger_error('Оповещение - ' . static_main::m('mailerr', $this), E_USER_WARNING);
			}

		}
		return '-Успешно-';
	}

	/**
	 * оповещение о неоплаченном счете
	 * status develop
	 */
	function sendNotifNoPay()
	{
		if (!_new_class('mail', $MAIL)) return '-Ошибка Почтовой службы-';

		$list = $this->_query('*', 'WHERE mailnotif=0 and status=' . PAY_NOPAID . ' and mf_timecr<' . (time() - 600), 'id');

		if (count($list)) {
			$this->id = array_keys($list);
			$this->_update(array('mailnotif' => time()), null, false);
		}
		foreach ($list as $row) {
			if (!$row['email']) continue;
			$CHILD = & $this->childs[$row['pay_modul']];
			$payLink = '';
			$caption = 'Просмотреть статус счета';
			if (is_string($CHILD->pay_formType))
				$payLink .= '<p>Вы можете сразу оплатить выставленный счет в ' . $CHILD->caption . ' перейдя по <a href="' . $CHILD->pay_formType . '" target="_blank">ссылке</a></p>';
			else
				$caption = 'Просмотреть статус и оплатить счет';
			if ($row['paylink']) {
				$row['paylink'] .= '&payhash=' . $this->getPayHash($row);
				$payLink .= '<p><a href="' . $row['paylink'] . '" target="_blank">' . $caption . '</a></p>';
			}
			else
				$payLink .= '<p><a href="http://' . $this->_CFG['site']['www'] . '" target="_blank">Cтатус счета смотреть на сайте</a></p>';

			$datamail = array(
				'subject' => $this->config['subjectNoPay'],
				'text' => $this->config['notifNoPay'] . $payLink
			);
			$datamail = str_replace(
				array('#id#', '#name#', '#cost#', '#payCaption#', '#payDate#'),
				array($row['id'], $row['name'], $row['cost'], $CHILD->caption, date('Y-m-d H-i-s', $row['mf_timecr'])),
				$datamail
			);
			$datamail['creater_id'] = -1;
			$datamail['mail_to'] = $row['email'];

			$MAIL->reply = 0;
			//$MAIL->config['mailcron'] = 0;// немедленная отправка письма
			if (!$MAIL->Send($datamail)) {
				trigger_error('Оповещение - ' . static_main::m('mailerr', $this), E_USER_WARNING);
			}

		}
		return '-Успешно-';
	}

	/**
	 * Сохраняем лог
	 * status develop
	 */
	private function saveLog($id, $name)
	{
		$data = array('owner_id' => $id, 'name' => $name);
		$data['refer'] = $_SERVER['HTTP_REFERER'];
		$data['url'] = $_SERVER['REQUEST_URI'];
		return $this->childs['payhistory']->_add($data, false);
	}

	/**
	 * Хеш код платежа
	 * status develop
	 */
	private function getPayHash($payData)
	{
		return md5($payData['id'] . $payData['email'] . $payData['mf_timecr']);
	}

	/**
	 * Проверка Хеш код платежа
	 * status develop
	 */
	private function checkPayHash($hash, $payData)
	{
		return $hash == md5($payData['id'] . $payData['email'] . $payData['mf_timecr']);
	}
}


/**
 * Логи платежей
 * status develop
 */
class payhistory_class extends kernel_extends
{

	protected function init()
	{
		parent::init();
		$this->default_access = '|0|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_ipcreate = true; //IP адрес пользователя с котрого была добавлена запись
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->caption = 'История';
		$this->ver = '0.1';
	}

	protected function _create()
	{
		parent::_create();
		$this->fields['url'] =
		$this->fields['refer'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Описание', 'mask' => array());
		$this->fields_form['url'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Ссылка', 'mask' => array());
		$this->fields_form['refer'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Переход', 'mask' => array());
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Дата', 'mask' => array());
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'IP', 'mask' => array('fview' => 2));
	}

}




