<?php
class paydogecoin_class extends kernel_extends
{

	const STATUS_NOYET = 0;
	const STATUS_ONWAY = 1;
	const STATUS_OK = 2;
	const STATUS_CANCEL = 3;
	const STATUS_CANCEL_BY_USER = 4;
	const STATUS_CANCEL_BY_TIMEOUT = 5;

	const MULTIPLIER = 100000000; // множитель курса

	function _create_conf()
	{
		parent::_create_conf();

		$this->config['account'] = '';
		$this->config['API_GET_TRANSACTION_LIST'] = 'https://blockchain.info/address/{val}?format=json';
		$this->config['API_GET_TRANSACTION_INFO'] = 'https://blockchain.info/ru/tx/{val}?format=json';
        $this->config['minpay'] = 10;
        $this->config['maxpay'] = PHP_INT_MAX;
        $this->config['lifetime'] = 1080;
        $this->config['rate'] = 500;

		$this->config_form['account'] = array('type' => 'text', 'caption' => 'Кошелек');
		$this->config_form['rate'] = array('type' => 'text', 'caption' => 'Курс к рублю', 'comment' => 'в Doge');
		$this->config_form['API_GET_TRANSACTION_LIST'] = array('type' => 'text', 'caption' => 'API транзакций ', 'comment' => '{val} - адрес');
		$this->config_form['minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#F60;');
		$this->config_form['lifetime'] = array('type' => 'text', 'caption' => 'Таймаут', 'comment' => 'Время жизни счёта по умолчанию. Задается в часах. Максимум 1080 часов (45 суток)', 'style' => 'background-color:#F60;');
	}

	function init()
	{
		parent::init();
		$this->caption = 'BitCoin';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		$this->lang['add_name'] = 'Пополнение кошелька из DogeCoin';
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
		$this->cron[] = array('modul' => $this->_cl, 'function' => 'checkRateCron()', 'active' => 1, 'time' => 10000);

	}

	protected function _create()
	{
		parent::_create();
		$this->fields['from'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
        $this->fields['cost'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
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
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Сумма (Doge)', 'readonly' => 1, 'comment' => 'Минимум ' . $this->config['minpay'] . ', максимум ' . $this->config['maxpay'] , 'default' => 100, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
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
		$argForm['info'] = array('type' => 'info', 'caption' => 'По курсу 1руб. = '.$this->config['rate'].' Doge <br/> Сумма к зачислению <b>'.round(($summ * $this->config['rate']), 0, PHP_ROUND_HALF_UP).'</b> Satoshi');
		$argForm['from'] = array('type' => 'text', 'caption' => 'Адресс кошелька', 'comment'=> 'С которого будет оплачен счет.', 'mask' => array('min' => 5));
		$argForm['name'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $comm)); // иначе name не попадает в БД
		if ($summ > 0)
			$argForm['cost'] = array('type' => 'hidden', 'readonly' => 1, 'mask' => array('eval' => $summ));
		else
			$argForm['cost'] = array('type' => 'int', 'caption' => 'Сумма (Satoshi)', 'comment' => 'Минимум ' . $this->config['minpay'] . ', максимум ' . $this->config['maxpay'] , 'default' => 400, 'mask' => array('min' => $this->config['minpay'], 'max' => $this->config['maxpay']));
		$this->lang['Save and close'] = 'Создать счет в ' . $this->caption;
		return $this->_UpdItemModul($param, $argForm);
	}


	// INFO
	public function statusForm($data)
	{
		$result = array('showStatus' => true, 'messages' => array());
		if (count($data)) {
			$result['messages'][] = array('logoPayStatus', 'Адресс для оплаты <b>'.$this->config['account'].'</b><br/>Сумма к зачислению <b>'.$data['child']['cost'].'</b> Doge'.
				($data['child']['status'] == self::STATUS_ONWAY ? '<br/>Платеж ожидает подтверждения' : ''));
		}
		return $result;
	}

	public function _add($data = array(), $flag_select = true, $flag_update = false)
	{
		$cost = round( ($data['cost'] * $this->config['rate']), 0, PHP_ROUND_HALF_UP);
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

		$url = str_replace('{val}', $this->config['account'], $this->config['API_GET_TRANSACTION_LIST']);
		$param = array(
			'COOKIE' => '__cfduid=db8a51342ff46e25fb6c4ba8663c2ab2f1392182277864;cf_clearance=cd4c4636c6f8aec6c380dcd5f9bddaf4f078e372-1392182283-604800',
			'USERAGENT' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36'
		);
		$result = static_tools::_http($url, $param);


		if (!$result or $result['err']) {
			trigger_error($this->_cl.' - Ошибка получения информации от blockchain - "'.$result['err'].'"', E_USER_WARNING);
			exit();
		}

		$transaction_list = json_decode($result['text']);

        if (!$transaction_list || !isset($transaction_list->address)) {
			trigger_error($this->_cl.' - Ошибка получения списка транзакций', E_USER_WARNING);
			return '--ERROR--';
        }

		$tList = // Клч- транзакции
		$aList = array(); //ключ - кошелки плательщика
        // Проверим каждую транзакцию
        foreach($transaction_list->txs as $txs) {
//			$url = str_replace('{val}', $txs->hash, $this->config['API_GET_TRANSACTION_INFO']);
//			$result = static_tools::_http($url);
//			if (!$result or $result['err']) {
//				trigger_error($this->_cl.' - Ошибка получения информации от blockchain - "'.$result['err'].'"', E_USER_WARNING);
//				exit();
//			}
//			current_block_count - transaction_block_height + 1
//            if ($txs->recipient == $this->config['account'] && $transaction->type == 0) { // входящие
//                $tList[$transactionId] = $txs;
//				$aList[$transaction->sender][$transactionId] = $txs;
//            }
			// на выходе ищем наш адресс
			foreach($txs->out as $out) {
				if ($out->addr==$this->config['account']) {
					// ищем в исходящих нашего клиента
					if (count($txs->inputs)==1 && isset($txs->inputs[0]->prev_out)) {
						$txs->outThis = $out;
						$tList[$txs->hash] = $txs;
						$aList[$txs->inputs[0]->prev_out->addr][$txs->hash] = $txs;
					}
				}
			}
        }

//		print_r('<pre>');
////		print_r($tList);
//		print_r($aList);
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
					if ($transaction->outThis->value==$dataBill['cost'] && !$this->existsTransaction($transactionId)) {
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
            if (!isset($transaction->result)) {
                $status = self::STATUS_NOYET;
            }
            elseif ($transaction->result != 0 and $transaction->block_height) {
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


	function checkRateCron()
	{
		$nxt_btc = $this->getRateBtcBtc();
		$btc_rur = $this->getRateBtcRur();
		$costRub = round(($nxt_btc*$btc_rur), 2, PHP_ROUND_HALF_DOWN);
		$costBtc = round( (1.4/($costRub)), 1, PHP_ROUND_HALF_UP); //-40%
		$dif = abs(($costBtc*10-$this->config['rate']*10));
		$html = '<div>'.$nxt_btc.'btc</div> '.
			'<div>'.$btc_rur.' руб</div>'.
			'<div>1nxt -> '.$costRub.' руб</div>'.
			'<div>1руб -> '.$costBtc.' nxt</div>'.
			'<div>DIF '.($dif/10).'</div>';

		if ( $dif >= 2 ) {
			_new_class('mail', $MAIL);
			$datamail['mail_to'] = $this->_CFG['info']['email'];
			$datamail['subject'] = '**' . strtoupper($_SERVER['HTTP_HOST']).' - изменился курс';
			$datamail['text'] = $html;
			$MAIL->reply = 0;
			$MAIL->Send($datamail);
		}

		return $html;
	}

	function getRateBtcBtc()
	{
		$result = $result_text = @file_get_contents('http://bter.com/api/1/ticker/nxt_btc/');

		if (!$result) {
			trigger_error($this->_cl.' - Ошибка получения информации о курсах - "'.$result.'"', E_USER_WARNING);
			exit();
		}

		$result = json_decode($result);

		if (!$result || !$result->result) {
			trigger_error($this->_cl.' - Ошибка получения информации о транзакций - "'.$result_text.'"', E_USER_WARNING);
			exit();
		}

		return $result->buy;
	}

	function getRateBtcRur()
	{
//		$url = 'https://api.bitcoinaverage.com/exchanges/RUB';
		$url = 'https://btc-e.com/api/2/btc_rur/ticker';
		$result = static_tools::_http($url);


		if (!$result or $result['err']) {
			trigger_error($this->_cl.' - Ошибка получения информации о курсах - "'.$result['err'].'"', E_USER_WARNING);
			exit();
		}

		$data = json_decode($result['text']);

		if (!$data || !$data->ticker) {
			trigger_error($this->_cl.' - Ошибка получения информации о транзакций - "'.var_dump($result).'"', E_USER_WARNING);
			exit();
		}
//
//		print_r('<pre>');
//		print_r($data);
//		exit();

		return $data->ticker->buy;
	}
}


