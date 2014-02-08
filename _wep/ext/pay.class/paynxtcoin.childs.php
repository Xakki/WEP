<?php
class paynxtcoin_class extends kernel_extends
{

	const STATUS_NOYET = 0;
	const STATUS_ONWAY = 1;
	const STATUS_OK = 2;
	const STATUS_CANCEL = 3;
	const STATUS_CANCEL_BY_USER = 4;
	const STATUS_CANCEL_BY_TIMEOUT = 5;


	function _create_conf()
	{
		parent::_create_conf();

		$this->config['account'] = '';
		$this->config['API_GET_TRANSACTION_LIST'] = 'http://localhost:7874/nxt?requestType=getAccountTransactionIds&account='; // 2747816215184844914&timestamp=0
		$this->config['API_GET_TRANSACTION_INFO'] = 'http://localhost:7874/nxt?requestType=getTransaction&transaction='; // 10045522459625736039
        $this->config['minpay'] = 10;
        $this->config['maxpay'] = 15000;
        $this->config['lifetime'] = 1080;
        $this->config['rate'] = 1;

		$this->config_form['account'] = array('type' => 'text', 'caption' => 'Кошелек');
		$this->config_form['rate'] = array('type' => 'text', 'caption' => 'Курс к рублю'); // 1nxt = 0.00007 btc = 1,75 руб
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
		$this->lang['add'] = 'Счет успешно создан. Кошелек для оплаты <b>'.$this->config['account'].'<b>';
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		//$this->showinowner = false;

		$this->ver = '0.1';
		$this->pay_systems = true; // Это модуль платёжной системы
		$this->pay_formType = true;

		$this->_enum['status'] = array(
			self::STATUS_NOYET => 'Ожидает оплаты',
			self::STATUS_ONWAY => 'Ожидает подтверждения',
			self::STATUS_OK => 'Оплаченный счёт',
			self::STATUS_CANCEL => 'Отменен',
			self::STATUS_CANCEL_BY_USER => 'Отменен пользователем',
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
//		$this->index_fields['transaction'] = 'transaction';
		$this->index_fields['status'] = 'status';
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['from'] = array('type' => 'text', 'caption' => 'Номер кошелька отправителя');
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Сумма (Nxt)', 'readonly' => 1, 'comment' => 'Минимум ' . $this->config['minpay'] . ', максимум ' . $this->config['maxpay'] , 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		$this->fields_form['status'] = array('type' => 'list', 'listname' => 'status', 'readonly' => 1, 'caption' => 'Статус', 'mask' => array());
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
		$argForm['info'] = array('type' => 'info', 'caption' => 'По курсу 1руб. = '.$this->config['rate'].' Nxt <br/> Сумма к зачислению <b>'.($summ * $this->config['rate']).'</b> Nxt');
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
			$result['messages'][] = array('logoPayStatus', 'Кошелек для оплаты <b>'.$this->config['account'].'</b><br/>Сумма к зачислению <b>'.$data['child']['cost'].'</b> nxt '.
				($data['child']['status'] == self::STATUS_ONWAY ? '<br/>Платеж ожидает подтверждения' : ''));
		}
		return $result;
	}

	public function _add($data = array(), $flag_select = true, $flag_update = false)
	{
		$cost = $data['cost'] * $this->config['rate'];
		$data2 = array(
			'from' => $data['from'],
			'cost' => $cost,
			'status' => self::STATUS_NOYET
		);

		$result = parent::_add($data2, true, $flag_update);
		return $result;
	}

	/// CRON
	function checkBill()
	{
		$this->clearOldData();

		$bills = $this->_query('*', 'WHERE active=1 and status<'.self::STATUS_OK);
		if (!count($bills)) return '--Empty--';

        $genesis_timestamp = strtotime('24 Nov 2013 12:00:00 UTC');
        $timestamp = $bills[0]['mf_timecr'] - $genesis_timestamp;

        $transaction_list = $transaction_list_text = @file_get_contents($this->config['API_GET_TRANSACTION_LIST'].urlencode($this->config['account']).'&timestamp='.$timestamp);

        if (!$transaction_list) {
			trigger_error($this->_cl.' - Ошибка получения списка транзакций' . $this->_cl, E_USER_WARNING);
			return '--ERROR--';
        }

        $transaction_list = json_decode($transaction_list);

        if (!$transaction_list || isset($transaction_list->errorCode) || !isset($transaction_list->transactionIds)) {
			trigger_error($this->_cl.' - Ошибка получения списка транзакций - "'.$transaction_list_text.'"', E_USER_WARNING);
			return '--ERROR--';
        }

		$tList = // Клч- транзакции
		$aList = array(); //ключ - кошелки плательщика
        // Проверим каждую транзакцию
        foreach($transaction_list->transactionIds as $transactionId) {
            $transaction = $transaction_text = @file_get_contents($this->config['API_GET_TRANSACTION_INFO'] . urlencode($transactionId));

            if (!$transaction) {
				trigger_error($this->_cl.' - Ошибка получения информации о транзакций - "'.$transactionId.'"', E_USER_WARNING);
				continue;
            }

            $transaction = json_decode($transaction);

            if (!$transaction || isset($transaction->errorCode) || !isset($transaction->recipient)) {
				trigger_error($this->_cl.' - Ошибка получения информации о транзакций - "'.$transaction_text.'"', E_USER_WARNING);
				continue;
            }


            if ($transaction->recipient == $this->config['account'] && $transaction->type == 0) { // входящие
                $tList[$transactionId] = $transaction;
				$aList[$transaction->sender][$transactionId] = $transaction;
            }
        }

//		print_r('<pre>');
//		print_r($tList);
//		exit();

		foreach($bills as $dataBill) {
			$idBill = $dataBill['id'];
			// ищем плательщика
			// если клиент оплатил несколько услуг одинаковой стоимостью

			// Если уже есть транзакция
			if ($dataBill['transaction']) {
				if (isset($tList[$dataBill['transaction']])) {
					$transaction = $tList[$dataBill['transaction']];
					$status = $this->getStatusTransaction($transaction);
					if ( $status  != $dataBill['status']) {
						// update status if change
						$this->id = $idBill;
						$this->_update(array('status' => $status, 'transaction_data' => json_encode($transaction)), false, false);
						if ($status==self::STATUS_OK) {
							$this->owner->payTransaction($dataBill['owner_id'], PAY_PAID);
						}
					}
					else {
						// nothing
					}
				}
				else {
					// Возможно транзакция была отменена
					$this->id = $idBill;
					$this->_update(array('status' => self::STATUS_NOYET, 'transaction' => '', 'transaction_data' => ''), false, false);
				}

			}
			elseif(isset($aList[$dataBill['from']])) {
				foreach($aList[$dataBill['from']] as $transactionId => $transaction) {
					// Если сумма совпадает и транзакции нет в базе
					if ($transaction->amount==$dataBill['cost'] && !$this->existsTransaction($transactionId)) {
						$status = $this->getStatusTransaction($transaction);
						$this->id = $idBill;
						$this->_update(array('status' => $status, 'transaction' =>$transactionId, 'transaction_data' => json_encode($transaction)), false, false);
						if ($status==self::STATUS_OK) {
							$this->owner->payTransaction($dataBill['owner_id'], PAY_PAID);
						}
						break;
					}
				}
			}
		}
		return '--OK--';
	}

    private function existsTransaction($transactionId)
    {
		$DATA = $this->qs('id', array('transaction' => $transactionId));
		if (count($DATA))
			return true;
		return false;
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

	function cancelPay($owner_id)
	{
		$this->_update(array('status' => self::STATUS_CANCEL_BY_USER, $this->mf_actctrl => 0), array('owner_id' => $owner_id));
	}
}


