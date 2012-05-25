<?php
/** Корзина для интеренет магазина
*
* Для включения "Корзины" достаточно подключить INC basket(Корзина)
* Варианта заказа : форма письма, онлайн покупка через платежные системы
*
*
*/
class shopbasket_class extends kernel_extends {

	/*protected function _create_conf() {
		parent::_create_conf();
		$this->config['orderset'] = array(0 => '0');
		$this->config_form['orderset'] = array('type' => 'list', 'listname'=>'orderset', 'multiple'=>1, 'caption'=>'Варианты заказа товара');
	}*/

	function _set_features() {
		if (!parent::_set_features()) return false;

		$this->ver = '0.0.1';
		$this->caption = 'Магазин - Корзина';
		$this->_AllowAjaxFn['jsAddBasket'] = true;
		$this->_AllowAjaxFn['jsCheckedBasket'] = true;
		$this->_dependClass = array('shop');
		$this->mf_namefields = false;
		$this->mf_timecr = true; // создать поле хранящее время создания записи
		$this->mf_timeup = true; // создать поле хранящее время обновления записи
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись

		$this->_enum['paytype']=array(
			0=>'Онлайн оплата',
			1=>'Наличными (при получении)',
			2=>'наложенным платежеом (по почте)',
			3=>'Безналичный (банковский перевод)'
		);


		return true;
	}

	protected function _create() {
		parent::_create();

		$this->fields['cost'] = array('type' => 'float', 'width' => '8,2', 'attr' => 'NOT NULL', 'default'=>'0.00', 'min' => '1');
		$this->fields['paytype'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['delivertype'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['laststatus'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default'=>0);

		//$this->ordfield = 'name DESC';

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Сумма', 'mask'=>array());
		$this->fields_form['paytype'] = array('type' => 'int', 'listname'=>'paytype', 'caption' => 'Тип платежа', 'readonly'=>1, 'mask' =>array());
		$this->fields_form['delivertype'] = array('type' => 'list', 'class'=>array('class'=>'shopdeliver'), 'caption' => 'Тип доставки', 'readonly'=>1, 'mask' =>array());
		$this->fields_form['laststatus'] = array('type' => 'list', 'listname'=>array('class'=>'shopbasketstatus'), 'caption' => 'Статус', 'readonly'=>1, 'mask' =>array());

		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'readonly'=>1, 'mask' =>array());
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата заказа', 'mask'=>array('fview'=>2));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'caption' => 'IP','readonly'=>1, 'css'=>'boardparam formparam', 'mask'=>array('usercheck'=>1));
		$this->fields_form[$this->mf_createrid] = array(
			'type' => 'ajaxlist', 
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'Заказчик',
			'readonly'=>1,
			'mask' =>array('usercheck'=>1)
		);

	}


	function _childs() {
		parent::_childs();
		$this->create_child('shopbasketitem');
		$this->create_child('shopbasketstatus');
	}

	/*function allChangeData($type = '', $data = '') {
		unlink($this->YML_FILE);
		return parent::allChangeData($type, $data);
	}*/
	function jsCheckedBasket() {
		$res = array('html'=>'');
		$BASKETITEM = &$this->childs['shopbasketitem'];
		$updData = array(
			'id_product'=>(int)$_GET['id_product'],
			'owner_id'=>0,
			$this->mf_createrid=>$this->userId(true)
		);
		$basketitemData = $BASKETITEM->qs('id',$updData);
		if(count($basketitemData) and $BASKETITEM->id=$basketitemData[0]['id']) {
			$BASKETITEM->_update(array('checked'=>($_GET['_checked']?1:0)));
		}
		return $res;
	}

	function jsAddBasket() {
		$html = '';
		$mess = array('error','Ошибка данных!');
		$res = array('html'=>'');

		if(!_new_class('shop',$SHOP)) return array('html'=>'Error: Need modul SHOP');
		$PRODUCT = &$SHOP->childs['product'];
		$PRODUCT->id = (int)$_GET['id_product'];
		$count = (int)$_GET['count'];

		if($PRODUCT->id) {
			$productData = $PRODUCT->qs('id',$PRODUCT->id);
			if(count($productData)) {
				$addData = array(
					'id_product'=>$PRODUCT->id,
					'owner_id'=>0,
					$this->mf_createrid=>$this->userId(true)
				);
				$BASKETITEM = &$this->childs['shopbasketitem'];
				$basketitemData = $BASKETITEM->qs('id',$addData);
				$BASKETITEM->id = null;
				if(count($basketitemData))
					$BASKETITEM->id = $basketitemData[0]['id'];

				$addData['count'] = $count;
				$mess = array();
				if($count and !$BASKETITEM->id and $BASKETITEM->_add($addData)) {
					//$mess = array('ok','Товар добавлен в корзину');
				}
				elseif($count and $BASKETITEM->id  and $BASKETITEM->_update(array('count'=>$count))) {
					//$mess = array('ok','Количество товара в корзине обновлено');
				}
				elseif(!$count and $BASKETITEM->id and $BASKETITEM->_delete()) {
					//$mess = array('ok','Товар удален из корзины');
				}
				else {
					$mess = array('error','Ошибка корзины ['.$count.','.$BASKETITEM->id.']- обратитесь к админу.');
				}
			}
		}
		if(isset($mess[0]))
			$html = '<div class="messages"><div class="'.$mess[0].'">'.$mess[1].'</div></div>';
		//$html = $HTML->transformPHP($DATA['formcreat'],'#pg#messages');
		$res['html'] = $html;
		return $res;
	}

	/**
	* Корзина
	*
	*/
	function fBasket() {
		$RESULT = array('cnt'=>0, 'summ'=>0);
		if(!$uId = $this->userId()) return $RESULT;
		_new_class('shop',$SHOP);
		$data = $this->childs['shopbasketitem']->qs('sum(t1.count) as cnt,sum(t2.cost*t1.count) as `summ`' ,'t1 JOIN '.$SHOP->childs['product']->tablename.' t2 ON t1.id_product=t2.id WHERE t1.owner_id=0 and t1.'.$this->mf_createrid.'='.$uId);
		$RESULT = $data[0];
		return $RESULT;
	}

	/** Список товаров положенных в корзину
	*
	*
	*/
	function fBasketList() {
		$RESULT = array();
		if(!$uId = $this->userId()) return $RESULT;
		_new_class('shop',$SHOP);
		// TODO ошибка выборки картинок
		$this->childs['shopbasketitem']->attaches = $SHOP->childs['product']->attaches;
		$RESULT['#list#'] = $this->childs['shopbasketitem']->qs('t1.*, t2.id, t2.cost, t2.name, t2.img_product, sum(t2.cost*t1.count) as `summ`' ,'t1 LEFT JOIN '.$SHOP->childs['product']->tablename.' t2 ON t1.id_product=t2.id WHERE t1.owner_id=0 and t1.'.$this->mf_createrid.'='.$uId.' GROUP BY t1.id');
		$this->childs['shopbasketitem']->attaches = array();

		return $RESULT;
	}

	function fBasketData() {
		$RESULT = array();
		if(!$uId = $this->userId()) return $RESULT;
		$RESULT = $this->childs['shopbasketitem']->qs('id,id_product,count','WHERE owner_id=0 and '.$this->mf_createrid.'='.$uId,'id_product');
		return $RESULT;
	}

	function userId($force=false) {
		$id = static_main::userId();
		if(!$id) {
			if(isset($_COOKIE['basketcid'])) {
				$id = -(int)$_COOKIE['basketcid'];
			}
			elseif($force) {
				$id = $this->generateId();
				$data = $this->qs('id',array($this->mf_createrid=>-$id));
				while(count($data)) {
					$id = $this->generateId();
					$data = $this->qs('id',array($this->mf_createrid=>-$id));
				}
				_setcookie('basketcid', $id, (time() + 999999999));
				$id = -$id;
			} 
			
		}
		return $id;
	}
	function generateId() {
		return rand(1,999999);
	}
}

class shopbasketitem_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;

		$this->ver = '0.0.1';
		$this->caption = 'Товары заказа';
		$this->mf_namefields = false;
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись

		/*$this->_enum['orderset']=array(
			0=>'Письмом',
			1=>'Онлайн оплата');*/

		return true;
	}

	protected function _create() {
		parent::_create();

		$this->fields['id_product'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'min'=>1);
		$this->fields['count'] = array('type' => 'tinyint', 'width' => 3, 'attr' => 'NOT NULL', 'default'=>1, 'min'=>1);
		//$this->fields['cost_item'] = array('type' => 'float', 'width' => '8,2', 'attr' => 'NOT NULL', 'default'=>'0.00', 'min' => '1');
		//$this->fields['cost_full'] = array('type' => 'float', 'width' => '8,2', 'attr' => 'NOT NULL', 'default'=>'0.00', 'min' => '1');
		$this->fields['checked'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default'=>1);

		//$this->ordfield = 'name DESC';

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['id_product'] = array(
			'type' => 'list', 
			'listname'=>array('class'=>'product'),
			'caption' => 'Товар', 'readonly' => 1,
			'mask' =>array('min'=>1));
		$this->fields_form['count'] = array('type' => 'int', 'caption' => 'Кол-во');
		//$this->fields_form['cost_item'] = array('type' => 'text', 'caption' => 'Цена', 'readonly' => 1);
		//$this->fields_form['cost_full'] = array('type' => 'text', 'caption' => 'Итого', 'readonly' => 1);
		$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Отмечено', 'default'=>1);

	}
}

class shopbasketstatus_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;

		$this->ver = '0.0.1';
		$this->caption = 'Статусы';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись

		$this->_enum['status']=array(
			0=>'Заказ ожидает оплаты',
			0=>'Заказ ожидает подтверждения менеджером',
			0=>'Заказ забронирован',
			1=>'Оплачено',
			2=>'Отправлено',
			2=>'Доставлено',
			3=>'Отменено пользователем',
			4=>'Отменено магазином',
			5=>'',
		);

		return true;
	}

	protected function _create() {
		parent::_create();
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1, 'default'=>0);
		$this->fields['comment'] = array('type' => 'varchar', 'width' => 255);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['status'] = array('type' => 'int', 'listname'=>'status', 'caption' => 'Статус', 'default'=>1);
		$this->fields_form['comment'] = array('type' => 'text', 'caption' => 'Комментарий');

		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'mask' =>array());
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата заказа', 'mask'=>array('fview'=>2,'sort'=>1,'filter'=>1));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'caption' => 'IP','readonly'=>1, 'css'=>'boardparam formparam', 'mask'=>array('usercheck'=>1,'filter'=>1,'sort'=>1));
		/*$this->fields_form[$this->mf_createrid] = array(
			'type' => 'ajaxlist', 
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'Заказчик',
			'readonly'=>1,
			'mask' =>array('usercheck'=>1, 'filter'=>1)
		);*/
	}
}