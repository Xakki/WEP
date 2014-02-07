<?php
class paynxtcoin_class extends kernel_extends
{

	const STATUS_NOYET = 0;
	const STATUS_ONWAY = 1;
	const STATUS_OK = 2;
	const STATUS_CANCE_BY_USER = 3;
	const STATUS_CANCEL_BY_TIMEOUT = 4;

	function _create_conf()
	{
		parent::_create_conf();

		$this->config['account'] = '';
		$this->config['API_GET_TRANSACTION_LIST'] = 'http://localhost:7874/nxt?requestType=getAccountTransactionIds&account='; // 2747816215184844914&timestamp=0
		$this->config['API_GET_TRANSACTION_INFO'] = 'http://localhost:7874/nxt?requestType=getTransaction&'; // transaction=10045522459625736039
        $this->config['minpay'] = 10;
        $this->config['maxpay'] = 15000;
        $this->config['lifetime'] = 1080;

		$this->config_form['account'] = array('type' => 'text', 'caption' => 'Кошелек');
		$this->config_form['API_GET_TRANSACTION_LIST'] = array('type' => 'text', 'caption' => 'Кошелек');
		$this->config_form['API_GET_TRANSACTION_INFO'] = array('type' => 'text', 'caption' => 'Кошелек');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут', 'comment' => 'Время жизни счёта по умолчанию. Задается в часах. Максимум 1080 часов (45 суток)', 'style' => 'background-color:#F60;');
	}

	function init()
	{
		parent::init();
		$this->caption = 'NextCoin';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->lang['add_name'] = 'Пополнение кошелька из NextCoin';
		$this->lang['add_err'] = 'Ошибка выставление счёта. Обратитесь к администратору сайта.';
		$this->lang['add'] = 'Счет успешно создан. Переведите на кошелек '.$this->config['account'];
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		//$this->showinowner = false;

		$this->ver = '0.1';
		$this->pay_systems = true; // Это модуль платёжной системы
//		$this->pay_formType = 'https://w.qiwi.ru/orders.action';

		$this->_enum['status'] = array(
			self::STATUS_NOYET => 'Ожидает оплаты',
			self::STATUS_ONWAY => 'Проводится',
			self::STATUS_OK => 'Оплаченный счёт',
			self::STATUS_CANCE_BY_USER => 'Отменен',
			self::STATUS_CANCEL_BY_TIMEOUT => 'Отменен (Истекло время)',
		);

		$this->_enum['errors'] = array(
			0 => ' - ',
		);

		$this->cron[] = array('modul' => $this->_cl, 'function' => 'checkBill()', 'active' => 1, 'time' => 300);

	}

	protected function _create()
	{
		parent::_create();
		$this->fields['from'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
        $this->fields['cost'] = array('type' => 'int', 'width' => 10, 'attr' => 'NOT NULL');
		$this->fields['transaction'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['transaction_data'] = array('type' => 'text');
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default' => 0);
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['from'] = array('type' => 'text', 'caption' => 'Номер кошелька отправителя');
		$this->fields_form['cost'] = array('type' => 'decimal', 'caption' => 'Сумма (Nxt)', 'readonly' => 1, 'comment' => 'Минимум ' . $this->config['minpay'] . ', максимум ' . $this->config['maxpay'] , 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		$this->fields_form['statuses'] = array('type' => 'list', 'listname' => 'statuses', 'readonly' => 1, 'caption' => 'Статус', 'mask' => array());
		$this->fields_form['transaction'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'transaction', 'mask' => array());
	}

	/*
	* Создание счёта
	*/
	public function billingForm($summ, $comm, $data = array())
	{
		$this->prm_add = true;
		$param = array('showform' => 1, 'savePost' => true); // , 'setAutoSubmit' => true

//		$this->owner->setPostData('phone', $data);
//		$this->owner->setPostData('email', $data);

		$argForm = array();
		$argForm['from'] = array('type' => 'text', 'caption' => 'Номер кошелька', 'comment'=> 'С которого будет оплачен счет.', 'mask' => array('min' => 5));
		$argForm['name'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $comm)); // иначе name не попадает в БД
		if ($summ > 0)
			$argForm['cost'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $summ, 'min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		else
			$argForm['cost'] = array('type' => 'int', 'caption' => 'Сумма (Nxt)', 'comment' => 'Минимум ' . $this->config['minpay'] . ', максимум ' . $this->config['maxpay'] , 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		$this->lang['Save and close'] = 'Создать счет в ' . $this->caption;
		return $this->_UpdItemModul($param, $argForm);
	}


	// INFO
	public function statusForm($data)
	{
		$result = array('showStatus' => true, 'messages' => array());
		if (count($data)) {
			$result['messages'][] = array('logoPayStatus', $this->_enum['status'][$data['status']]);
		}
		return $result;
	}

	public function _add($data = array(), $flag_select = true, $flag_update = false)
	{
		$data2 = array(
			'from' => $data['from'],
			'cost' => $data['cost'],
			'statuses' => self::STATUS_NOYET
		);

		$result = parent::_add($data2, true, $flag_update);
		return $result;
	}

	/// CRON
	function checkBill()
	{
		$this->clearOldData();

		$bills = $this->_query('*', 'WHERE status<'.STATUS_OK);
		if (!count($bills)) return '-нет выставленных счетов-';

        $genesis_timestamp = strtotime('24 Nov 2013 12:00:00 UTC');
        $timestamp = strtotime($bills[0]['mf_timecr']) - $genesis_timestamp;

        $transaction_list = @file_get_contents($this->config['API_GET_TRANSACTION_LIST'].urlencode($this->config['account'].'&timestamp='.$timestamp));

        if (!$transaction_list) {
            throw new Exception('Error whilst retrieving account transactions.');
        }

        $transaction_list = json_decode($transaction_list);

        if (!$transaction_list || isset($transaction_list->errorCode) || !isset($transaction_list->transactionIds)) {
            throw new Exception('Error whilst retrieving account transactions.');
        }

        // Проверим каждую транзакцию
        foreach($transaction_list as $transactionId) {
            $transaction = @file_get_contents($this->config['API_GET_TRANSACTION_LIST'] . urlencode($transactionId));

            if (!$transaction) {
                throw new Exception('Error whilst retrieving account transaction.');
            }

            $transaction = json_decode($transaction);

            if (!$transaction || isset($transaction->errorCode) || !isset($transaction->recipient)) {
                throw new Exception('Error whilst retrieving account transaction.');
            }

            $tList = array();
            if ($transaction->recipient == $this->config['accountId'] && $transaction->type == 0) {
                $tList[$transactionId] = $transaction;
            }

            foreach($bills as $idBill=>$dataBill) {
                // ищем плательщика
                // если клиент оплатил несколько услуг одинаковой стоимостью

                if ($dataBill['transaction']) {
                    if (isset($tList[$transactionId])) {
                        $status = $this->getStatusTransaction($tList[$transactionId]);
                        if ( $status  != $dataBill['status']) {
                            // update status if change
                            $this->id = $idBill;
                            $this->_update(array('status' => $status), false, false);
                            // TODO - PAY ?
                        }
                        else {
                            // nothing
                        }
                    }
                    else {
                        // TODO?
                    }

                }
                else {
                    // add transactionID if have formId
                }
//                $date = date('Y-m-d H:i:s', $genesis_timestamp + $transaction->timestamp);
//
//                $incoming[] = array('sender' => $transaction->sender, 'amount' => $transaction->amount, 'status' => $status, 'date' => $date, 'confirmations' => $transaction->confirmations, 'transaction_id' => $transactionId);

            }
        }

	}

    private function getStatusTransaction($transaction)
    {
            if (!isset($transaction->confirmations)) {
                $status = self::STATUS_NOYET;
            }
            elseif ($transaction->confirmations > 10) {
                $status = self::STATUS_OK;
            } else {
                $status = self::STATUS_ONWAY;
            }
            return $status;
    }

	/**
	 * Сервис служба очистки данных
	 * Отключает неоплаченные платежи
	 * @param $M - модуль платежной системы
	 * @param $leftTime - в секундах
	 */
	function clearOldData()
	{
		if (!$this->config['lifetime']) $this->config['lifetime'] = 1080;
		$leftTime = ($this->config['lifetime'] * 3600);

		$this->_update(array('status' => self::STATUS_CANCEL_BY_TIMEOUT, $this->mf_actctrl => 0), 'WHERE status=0 and ' . $this->mf_timecr . '<"' . (time() - $leftTime) . '"');

		$this->owner->clearOldData($this->_cl, $leftTime);
	}
}


