<?php

/** Корзина для интеренет магазина
 *
 * Для включения "Корзины" достаточно подключить INC basket(Корзина)
 * Варианта заказа : форма письма, онлайн покупка через платежные системы
 *
 *
 */

define('SHOPBASKET_NOPAID', 0);
define('SHOPBASKET_WAITAPPROVED', 1);
define('SHOPBASKET_TIMEOUT', 2);
define('SHOPBASKET_PAID', 3);
define('SHOPBASKET_SENT', 4);
define('SHOPBASKET_DELIVERED', 5);
define('SHOPBASKET_USERCANCEL', 6);
define('SHOPBASKET_CANCEL', 7);

class shopbasket_class extends kernel_extends
{

    /*protected function _create_conf() {
        parent::_create_conf();
        $this->config['orderset'] = array(0 => '0');
        $this->config_form['orderset'] = array('type' => 'list', 'listname'=>'orderset', 'multiple'=> FORM_MULTIPLE_SIMPLE, 'caption'=>'Варианты заказа товара');
    }*/

    protected function init()
    {
        parent::init();

        $this->ver = '0.0.2';
        $this->caption = 'Магазин - Корзина';
        $this->_AllowAjaxFn['jsAddBasket'] = true;
        $this->_AllowAjaxFn['jsCheckedBasket'] = true;
        $this->_dependClass = array('shop');
        $this->mf_namefields = false;
        $this->mf_timecr = true; // создать поле хранящее время создания записи
        $this->mf_timeup = true; // создать поле хранящее время обновления записи
        $this->mf_ipcreate = true; //IP адрес пользователя с котрого была добавлена запись
        $this->mf_notif = true;

        $this->prm_add = false; // добавить в модуле
        $this->prm_del = false; // удалять в модуле
        $this->prm_edit = true; // редактировать в модуле

        $this->allowedPay = array();

        $this->_enum['status'] = array(
            SHOPBASKET_NOPAID => 'Ожидание оплаты',
            SHOPBASKET_WAITAPPROVED => 'Ожидание подтверждения менеджером',
            SHOPBASKET_TIMEOUT => 'Истекло время ожидания',
            SHOPBASKET_PAID => 'Оплачено',
            SHOPBASKET_SENT => 'Отправлено',
            SHOPBASKET_DELIVERED => 'Доставлено',
            SHOPBASKET_USERCANCEL => 'Отменено пользователем',
            SHOPBASKET_CANCEL => 'Отменено магазином',
        );
    }

    protected function _create()
    {
        parent::_create();
        $this->fields['pay_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
        $this->fields['fio'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'min' => 6);
        $this->fields['address'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['phone'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'min' => 6);
        $this->fields['summ'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL', 'default' => '0.00', 'min' => '1');
        $this->fields['paytype'] = array('type' => 'varchar', 'width' => 16, 'attr' => 'NOT NULL', 'min' => '1');
        $this->fields['delivertype'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'min' => '1');
        $this->fields['laststatus'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default' => 0);

        $this->ordfield = 'mf_timecr DESC';

    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);

        $this->fields_form['fio'] = array('type' => 'text', 'caption' => 'Ваша фамилия и имя', 'mask' => array('min' => 6));
        $this->fields_form['address'] = array('type' => 'text', 'caption' => 'Адрес доставки', 'mask' => array('min' => 6));
        $this->fields_form['phone'] = array('type' => 'phone', 'caption' => 'Телефон для связи и оповещения', 'mask' => array('name' => 'phone3', 'min' => 6));
        $this->fields_form['summ'] = array('type' => 'decimal', 'caption' => 'Сумма', 'readonly' => 1, 'mask' => array());
        $this->fields_form['delivertype'] = array('type' => 'list', 'listname' => 'delivertype', 'readonly' => 1, 'caption' => 'Тип доставки', 'mask' => array('min' => 1));
        $this->fields_form['paytype'] = array('type' => 'list', 'listname' => 'paytype', 'caption' => 'Тип платежа', 'mask' => array('min' => 1));
        $this->fields_form['laststatus'] = array('type' => 'list', 'listname' => 'status', 'caption' => 'Статус', 'readonly' => 1, 'mask' => array());
        //$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'readonly'=>1, 'mask' =>array());
        $this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Дата заказа', 'mask' => array());
        $this->fields_form['mf_ipcreate'] = array('type' => 'text', 'caption' => 'IP', 'readonly' => 1, 'mask' => array('usercheck' => 1));
        $this->fields_form[$this->mf_createrid] = array(
            'type' => 'ajaxlist',
            'listname' => array('class' => 'users', 'nameField' => 'concat(tx.name," [",tx.id,"]")'),
            'caption' => 'Заказчик',
            'readonly' => 1,
            'mask' => array('usercheck' => 1)
        );

    }


    function _getlist($listname, $value = 0)
    {
        $data = array();
        if ($listname == 'paytype') {
            _new_class('pay', $PAY);
            foreach ($PAY->childs as &$child) {
                if (isset($child->pay_systems) and (!count($this->allowedPay) or in_array($child->_cl, $this->allowedPay))) {
                    $data[$child->_cl] = array('#name#' => $child->caption, '#css#' => 'ico_' . $child->_cl,);
                }
            }
            return $data;
        } elseif ($listname == 'delivertype') {
            _new_class('shopdeliver', $MODUL);
            $dataTemp = $MODUL->qs('id,name,paylist', 'WHERE active=1', 'id');

            if ($value and is_string($value) and $value = trim($dataTemp[$value]['paylist'], '|')) {
                $this->allowedPay = explode('|', $value);
            }

            foreach ($dataTemp as $r)
                $data[$r['id']] = array('#name#' => $r['name'], '#img#' => getUrlTheme() . '_img/avatar/default3.jpg',);

            return $data;
        } else
            return parent::_getlist($listname, $value);
    }

    function _childs()
    {
        parent::_childs();
        $this->create_child('shopbasketitem');
        $this->create_child('shopbasketstatus');
    }

    public function _UpdItemModul($param = array(), &$argForm = null)
    {
        if ($this->id and $this->data[$this->id]['laststatus'] >= SHOPBASKET_TIMEOUT) {
            $this->prm_edit = false;
        }
        return parent::_UpdItemModul($param, $argForm);
    }

    /*function allChangeData($type = '', $data = '') {
        unlink($this->YML_FILE);
        return parent::allChangeData($type, $data);
    }*/
    function jsCheckedBasket()
    {
        if ($this->_CFG['robot']) {
            return array('html' => 'Ботам ни к чему сюда лезть!');
        }

        $res = array('html' => '');
        $BASKETITEM = & $this->childs['shopbasketitem'];
        $updData = array(
            'product_id' => (int)$_GET['product_id'],
            'owner_id' => 0,
            $this->mf_createrid => $this->userId(true)
        );
        $basketitemData = $BASKETITEM->qs('id', $updData);
        if (count($basketitemData) and $BASKETITEM->id = $basketitemData[0]['id']) {
            $BASKETITEM->_update(array('checked' => ($_GET['_checked'] ? 1 : 0)));
        }
        return $res;
    }

    function jsAddBasket()
    {
        $html = '';
        $mess = array('error', 'Ошибка данных!');
        $res = array('html' => '');

        if ($this->_CFG['robot']) {
            return array('html' => 'Ботам ни к чему сюда лезть!');
        }

        if (!_new_class('shop', $SHOP)) return array('html' => 'Error: Need modul SHOP');

        $PRODUCT = & $SHOP->childs['product'];
        $PRODUCT->id = (int)$_GET['product_id'];
        $count = (int)$_GET['count'];

        if ($PRODUCT->id) {
            $productData = $PRODUCT->qs('id, name', $PRODUCT->id, 'id');
            if (count($productData)) {
                $addData = array(
                    'product_id' => $PRODUCT->id,
                    'product_name' => $productData[$PRODUCT->id]['name'],
                    'owner_id' => 0,
                    $this->mf_createrid => $this->userId(true)
                );
                $BASKETITEM = & $this->childs['shopbasketitem'];
                $basketitemData = $BASKETITEM->qs('id', $addData);
                $BASKETITEM->id = null;
                if (count($basketitemData))
                    $BASKETITEM->id = $basketitemData[0]['id'];

                $addData['count'] = $count;
                $mess = array();
                if ($count and !$BASKETITEM->id and $BASKETITEM->_add($addData)) {
                    //$mess = array('ok','Товар добавлен в корзину');
                } elseif ($count and $BASKETITEM->id and $BASKETITEM->_update(array('count' => $count))) {
                    //$mess = array('ok','Количество товара в корзине обновлено');
                } elseif (!$count and $BASKETITEM->id and $BASKETITEM->_delete()) {
                    //$mess = array('ok','Товар удален из корзины');
                } else {
                    $mess = array('error', 'Ошибка корзины [' . $count . ',' . $BASKETITEM->id . ']- обратитесь к админу.');
                }
            }
        }
        if (isset($mess[0]))
            $html = '<div class="messages"><div class="' . $mess[0] . '">' . $mess[1] . '</div></div>';
        //$html = transformPHP($DATA['formcreat'],'#pg#messages');
        $res['html'] = $html;
        return $res;
    }

    /**
     * Корзина
     *
     */
    function fBasket()
    {
        $RESULT = array('cnt' => 0, 'summ' => 0);

        if (!$uId = $this->userId()) return $RESULT;

        _new_class('shop', $SHOP);

        $DATA = $this->childs['shopbasketitem']->qs('t1.count, t2.id, t2.cost, t2.shop', 't1 JOIN ' . $SHOP->childs['product']->tablename . ' t2 ON t1.product_id=t2.id WHERE t1.owner_id=0 and t1.' . $this->mf_createrid . '=' . $uId . ' GROUP BY t1.id', 'id');

        if (count($DATA) and _new_class('shopsale', $SHOPSALE)) {
            $SHOPSALE->getData($DATA);
        }
        foreach ($DATA as $r) {
            $RESULT['cnt'] += $r['count'];
            $RESULT['summ'] += $r['count'] * $r['cost'];
        }
        return $RESULT;
    }

    /** Список заказов
     *
     *
     */
    function fBasketList($showUser = false, $PARAM = array())
    {
        _new_class('pay', $PAY);
        //_new_class('shop',$SHOP);

        $RESULT = array();

        if (!$uId = $this->userId()) return $RESULT;
        $where = '';
        if (isset($PARAM['clause']) and count($PARAM['clause'])) {
            $where = ' WHERE ' . implode(' and ', $PARAM['clause']);
        }

        if ($showUser)
            $RESULT = $this->qs('t1.*, t2.name as uname', 't1 JOIN ' . static_main::getTableNameOfClass('users') . ' t2 ON t1.creater_id=t2.id ' . $where . ' GROUP BY t1.id ORDER BY t1.mf_timecr DESC', 'id');
        else
            $RESULT = $this->qs('t1.*', 't1 WHERE t1.' . $this->mf_createrid . '=' . $uId . ' GROUP BY t1.id ORDER BY t1.mf_timecr DESC', 'id');

        if (count($RESULT)) {
            $shopbasketitem = $this->childs['shopbasketitem']->qs('t1.*', 't1 WHERE t1.owner_id in (' . implode(',', array_keys($RESULT)) . ') GROUP BY t1.id', 'id', 'owner_id');

            foreach ($RESULT as &$r) {
                if ($r['paytype'] and isset($PAY->childs[$r['paytype']]))
                    $r['#paytype#'] = $PAY->childs[$r['paytype']]->caption;
                else
                    $r['#paytype#'] = ' - ';
                $r['#laststatus#'] = $this->_enum['status'][$r['laststatus']];
                $r['#shopbasketitem#'] = $shopbasketitem[$r['id']];
            }
            unset($r);
        }
        return $RESULT;
    }


    public function displayItem($id, $flag = true)
    {
        $RESULT = array();
        $this->id = $id;
        $data = $this->_select();
        if (!count($data)) return $RESULT;

        _new_class('pay', $PAY);
        _new_class('shopdeliver', $SHOPDELIVER);

        if ($data[$this->id]['paytype'] and isset($PAY->childs[$data[$this->id]['paytype']]))
            $data[$this->id]['#paytype#'] = $PAY->childs[$data[$this->id]['paytype']]->caption;
        else
            $data[$this->id]['#paytype#'] = ' - ';
        $data[$this->id]['#laststatus#'] = $this->_enum['status'][$data[$this->id]['laststatus']];

        $RESULT = $data[$this->id];
        $this->childs['shopbasketitem']->id = null;
        $RESULT['#shopbasketitem#'] = $this->childs['shopbasketitem']->_select();
        $this->childs['shopbasketstatus']->id = null;
        $RESULT['#history#'] = $this->childs['shopbasketstatus']->_select();

        list($RESULT['#delivery#']) = $SHOPDELIVER->qs('*', 'WHERE id=' . $data[$this->id]['delivertype']);

        return $RESULT;
    }

    /** Список товаров положенных в корзину
     *
     *
     */
    function fBasketListItem($oid = 0)
    {
        $RESULT = array();
        if (!$uId = $this->userId()) return $RESULT;

        _new_class('shop', $SHOP);

        $this->childs['shopbasketitem']->attaches = $SHOP->childs['product']->attaches; // кастыль для загрузки изобр
        $RESULT = $this->childs['shopbasketitem']->qs('t1.*, t2.id, t2.cost, t2.shop, t2.name, t2.img_product', 't1 LEFT JOIN ' . $SHOP->childs['product']->tablename . ' t2 ON t1.count!=0 and t1.product_id=t2.id WHERE t1.owner_id=' . $oid . ' and t1.' . $this->mf_createrid . '=' . $uId . ' GROUP BY t1.id', 'id');
        $this->childs['shopbasketitem']->attaches = array();
        // Среди заказов в корзине также есть и опция доставки и любая другая опция

        if (count($RESULT) and _new_class('shopsale', $SHOPSALE)) {
            $SHOPSALE->getData($RESULT);
        }

        return $RESULT;
    }

    function fBasketData()
    {
        $RESULT = array();
        if (!$uId = $this->userId()) return $RESULT;
        $RESULT = $this->childs['shopbasketitem']->qs('id,product_id,count', 'WHERE owner_id=0 and ' . $this->mf_createrid . '=' . $uId, 'product_id');
        return $RESULT;
    }

    /**
     * получаем id пользователя или генерим  для гостя чтоб мог ложить товары в корзину
     */
    function userId($force = false)
    {
        if ($this->_CFG['robot']) {
            return 0;
        }

        $id = static_main::userId();
        if (!$id) {
            if (isset($_COOKIE['basketcid']) and $_COOKIE['basketcid']) {
                $id = -(int)$_COOKIE['basketcid'];
            } elseif ($force) {
                $id = $this->generateId();
                $data = $this->childs['shopbasketitem']->qs('id', array($this->mf_createrid => -$id));
                while (count($data)) {
                    $id = $this->generateId();
                    $data = $this->childs['shopbasketitem']->qs('id', array($this->mf_createrid => -$id));
                }
                _setcookie('basketcid', $id, (time() + 999999999));
                $id = -$id;
            }

        } elseif (isset($_COOKIE['basketcid']) and $_COOKIE['basketcid']) {
            $this->childs['shopbasketitem']->_update(array($this->mf_createrid => $id), array($this->mf_createrid => -(int)$_COOKIE['basketcid']));
            _setcookie('basketcid', 0);
            $res = $this->childs['shopbasketitem']->qs('id', 'WHERE ' . $this->mf_createrid . '=' . $id . ' GROUP BY product_id HAVING count(id)>1', 'id');
            if (count($res)) {
                $this->childs['shopbasketitem']->id = array_keys($res);
                $this->childs['shopbasketitem']->_delete();
            }
        }
        return $id;
    }

    function generateId()
    {
        return rand(1, 999999);
    }

    /**
     * Сумма текущего заказа
     */
    function getSummOrder($deliveryData = null)
    {
        if (!$uId = $this->userId()) return 0;
        _new_class('shop', $SHOP);
        $RESULT = array();
        $RESULT['#list#'] = $this->childs['shopbasketitem']->qs('t1.*, t1.id as bid, t2.id, t2.cost, t2.shop', 't1 JOIN ' . $SHOP->childs['product']->tablename . ' t2 ON t1.product_id=t2.id WHERE t1.owner_id=0 and t1.checked=1 and t1.' . $this->mf_createrid . '=' . $uId . ' GROUP BY t1.id');

        if (is_null($deliveryData)) {
            return (count($RESULT['#list#']) ? true : false);
        }

        if (count($RESULT['#list#']) and _new_class('shopsale', $SHOPSALE)) {
            $SHOPSALE->getData($RESULT['#list#']);
        }
        $summ = 0;
        $this->orderItem = array();
        foreach ($RESULT['#list#'] as $r) {
            $summ += $r['cost'] * $r['count'];
            if (isset($r['sale']))
                $this->orderItem[$r['bid']] = array('cost_item' => $r['old_cost'], 'shopsale_id' => $r['sale']['id']);
            else
                $this->orderItem[$r['bid']] = array('cost_item' => $r['cost'], 'shopsale_id' => 0);
        }
        if (!$deliveryData['minsumm'] or $deliveryData['minsumm'] >= $summ) {
            $this->orderItem[0] = array(
                'product_id' => $deliveryData['id'],
                'product_name' => 'Доставка : ' . $deliveryData['name'],
                'owner_id' => 0,
                'count' => 0,
                $this->mf_createrid => $this->userId(),
                'cost_item' => $deliveryData['cost'],
                'shopsale_id' => 0
            );
            $summ += $deliveryData['cost'];
        }
        return $summ;
    }

    function orderDeliveryCost()
    {
        if (isset($this->orderItem[0])) {
            return $this->orderItem[0]['cost_item'];
        }
        return 0;
    }


    public function _add($data = array(), $flag_select = true, $flag_update = false)
    {
        if ($result = parent::_add($data, $flag_select, $flag_update)) {
            foreach ($this->orderItem as $k => $r) {
                $r['owner_id'] = $this->id;
                if ($k) {
                    $this->childs['shopbasketitem']->id = $k;
                    $this->childs['shopbasketitem']->_update($r);
                } else {
                    $this->childs['shopbasketitem']->_addUp($r);
                }

            }
            // Добавить статус
            $this->childs['shopbasketstatus']->_add(array('status' => SHOPBASKET_NOPAID));
        }
        return $result;
    }

    function payStatus($id, $status, $comment = '')
    {
        $this->id = $id;
        $this->childs['shopbasketstatus']->_add(array('status' => $status, 'comment' => $comment));
    }

    public function setStatus($id, $status, $comment = '')
    {
        $this->id = $id;
        $data = $this->_select();
        if (!count($data)) return false;

        $result = false;

        if ($status == SHOPBASKET_PAID) {
            if ($data[$this->id]['laststatus'] < SHOPBASKET_TIMEOUT) {
                _new_class('pay', $PAY);
                $result = $PAY->payTransaction($data[$this->id]['pay_id'], PAY_PAID);
            } else
                false;
        } elseif ($status >= SHOPBASKET_USERCANCEL) {
            if ($data[$this->id]['laststatus'] < SHOPBASKET_TIMEOUT) {
                _new_class('pay', $PAY);
                $result = $PAY->payTransaction($data[$this->id]['pay_id'], ($status == SHOPBASKET_USERCANCEL ? PAY_USERCANCEL : PAY_CANCEL));
            }
        }

        $this->payStatus($id, $status, $comment);
        return $result;
    }
}

class shopbasketitem_class extends kernel_extends
{

    function init()
    {
        parent::init();

        $this->ver = '0.0.1';
        $this->caption = 'Товары заказа';
        $this->mf_namefields = false;
        $this->mf_timecr = true; // создать поле хранящее время создания поля
        $this->mf_timeup = true; // создать поле хранящее время обновления поля
        $this->mf_ipcreate = true; //IP адрес пользователя с котрого была добавлена запись

        /*$this->_enum['orderset']=array(
            0=>'Письмом',
            1=>'Онлайн оплата');*/

    }

    protected function _create()
    {
        parent::_create();

        $this->fields['product_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'min' => 1);
        $this->fields['product_name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
        $this->fields['count'] = array('type' => 'tinyint', 'width' => 3, 'attr' => 'NOT NULL', 'default' => 1, 'min' => 1);
        $this->fields['cost_item'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL', 'default' => '0.00');
        $this->fields['shopsale_id'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['checked'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);

        //$this->ordfield = 'name DESC';

    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);

        /*$this->fields_form['product_id'] = array(
            'type' => 'list',
            'listname'=>array('class'=>'product'),
            'caption' => 'Товар', 'readonly' => 1,
            'mask' =>array('min'=>1));*/
        $this->fields_form['product_id'] = array('type' => 'int', 'caption' => 'ID Товара');
        $this->fields_form['product_name'] = array('type' => 'text', 'caption' => 'Название Товара');
        $this->fields_form['count'] = array('type' => 'int', 'caption' => 'Кол-во');
        //$this->fields_form['cost_item'] = array('type' => 'decimal', 'caption' => 'Цена товара на момент заказа', 'comment'=>'без учета скидки', 'readonly' => 1);
        //$this->fields_form['shopsale_id'] = array('type' => 'list', 'listname'=>array('class'=>'shopsale',), 'caption' => 'Скидка', 'readonly' => 1);
        $this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Отмечено', 'default' => 1);

    }
}

class shopbasketstatus_class extends kernel_extends
{

    function init()
    {
        parent::init();

        $this->ver = '0.0.1';
        $this->caption = 'Статусы';
        $this->mf_timecr = true; // создать поле хранящее время создания поля
        $this->mf_ipcreate = true; //IP адрес пользователя с котрого была добавлена запись

        $this->_enum['status'] = $this->owner->_enum['status'];

        $this->prm_add = false; // добавить в модуле
        $this->prm_del = false; // удалять в модуле
        $this->prm_edit = false; // редактировать в модуле

        return true;
    }

    protected function _create()
    {
        parent::_create();
        $this->fields['status'] = array('type' => 'tinyint', 'width' => 1, 'default' => 0);
    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);

        $this->fields_form['name'] = array('type' => 'text', 'caption' => 'Комментарий');
        $this->fields_form['status'] = array('type' => 'list', 'listname' => array('owner', 'status'), 'caption' => 'Статус', 'default' => 1);

        //$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'mask' =>array());
        $this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Дата заказа', 'mask' => array('fview' => 2, 'sort' => 1, 'filter' => 1));
        $this->fields_form['mf_ipcreate'] = array('type' => 'text', 'caption' => 'IP', 'readonly' => 1, 'css' => 'boardparam formparam', 'mask' => array('usercheck' => 1, 'filter' => 1, 'sort' => 1));
        $this->fields_form[$this->mf_createrid] = array(
            'type' => 'list',
            'listname' => array('class' => 'users', 'nameField+' => 'concat(tx.name," [",tx.email,"]")'),
            'caption' => 'Оператор',
            'readonly' => 1,
            'mask' => array('usercheck' => 1)
        );
    }


    public function _add($data = array(), $flag_select = true, $flag_update = false)
    {
        if ($result = parent::_add($data, $flag_select, $flag_update)) {
            $result = $this->owner->_update(array('laststatus' => $data['status']));
        }
        return $result;
    }

}