<?php

/**
 * Скидки для интеренет магазина
 *
 */
class shopsale_class extends kernel_extends
{

    function init()
    {
        parent::init();

        $this->ver = '0.0.1';
        $this->caption = 'Магазин - Акции';
        $this->_dependClass = array('shop');
        $this->mf_actctrl = true;
        $this->index_fields['selkey'] = array('active', 'periode', 'periods');

        $this->_enum['saletype'] = array(
            0 => '%',
            1 => 'руб.',
        );

    }

    protected function _create_conf()
    { /*CONFIG*/
        parent::_create_conf();

        $this->config['shopField'] = 'id';
        $this->config['productField'] = 'id';


        //$this->config_form['shopField'] = array('type' => 'text', 'caption' => 'Связь поля `Каталог`', 'comment'=>'id или code');
        //$this->config_form['productField'] = array('type' => 'text', 'caption' => 'Связь поля `Товар`', 'comment'=>'id или code');
    }


    protected function _create()
    {
        parent::_create();

        $this->fields['sale'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['saletype'] = array('type' => 'tinyint', 'width' => '1', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['periods'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['periode'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['shop'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['product'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL', 'default' => '0');

    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);

        //$this->fields_form['dscr'] = array('type' => 'textarea', 'caption' => 'Описание');
        $this->fields_form['name'] = array('type' => 'text', 'caption' => 'Описание');
        $this->fields_form['sale'] = array('type' => 'int', 'caption' => 'Скидка');
        $this->fields_form['saletype'] = array('type' => 'list', 'listname' => 'saletype', 'caption' => 'Тип скидки');
        $this->fields_form['periods'] = array('type' => 'date', 'caption' => 'Период начала'); //, 'mask'=>array('format'=>'Y-m-d', 'min'=>(time()-3600*20))
        $this->fields_form['periode'] = array('type' => 'date', 'caption' => 'Период конца'); //, 'mask'=>array('format'=>'Y-m-d', 'min'=>(time()-3600*20))
        $this->fields_form['shop'] = array('type' => 'list', 'listname' => array('class' => 'shop', 'is_tree' => true), 'caption' => 'Каталог', 'comment' => 'Выбирите каталог ...');
        $this->fields_form['product'] = array('type' => 'ajaxlist', 'listname' => array('class' => 'product', 'nameField' => 'concat(tx.name," [",tx.cost,"]")'), 'caption' => 'Товар', 'comment' => '...или товар');
        $this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать', 'default' => 1, 'mask' => array());
    }

    function getData(&$prodList, $rid = 0)
    {
        // TODO - почемуто корзина вызывает эту ф каждый раз???
        if (!$rid) {
            $rid = array();
            foreach ($prodList as $r) {
                if (isset($r['shop']))
                    $rid[$r['shop']] = $r['shop'];
            }
        } elseif (!is_array($rid))
            $rid = array($rid => $rid);
        $prodKey = array_keys($prodList);

        $data = $this->qs('*', 'WHERE active=1 and periode>=' . $this->_CFG['time'] . ' and periods<=' . $this->_CFG['time'] . ' and (product in (' . trim(implode(',', $prodKey), ',') . ') or shop in (' . implode(',', $rid) . '))');
        $this->setNewCost($prodList, $data);
    }

    function setNewCost(&$prodList, &$data)
    {
        $saleProd = $saleShop = array();
        foreach ($data as $dr) {
            if ($dr['product'])
                $saleProd[$dr['product']] = $dr;
            elseif ($dr['shop'])
                $saleShop[$dr['shop']] = $dr;
            else {
                $saleDefault = $dr;
            }
        }

        foreach ($prodList as &$r) {
            if (isset($saleProd[$r['id']]))
                $r['sale'] = $saleProd[$r['id']];
            elseif (isset($saleShop[$r['shop']]))
                $r['sale'] = $saleShop[$r['shop']];
            elseif (isset($saleDefault))
                $r['sale'] = $saleDefault;
            if (isset($r['sale'])) {
                $r['old_cost'] = round($r['cost'], 2);
                $r['sale']['#saletype#'] = $this->_enum['saletype'][$r['sale']['saletype']];
                if ($r['sale']['saletype'])
                    $r['cost'] = $r['cost'] - $r['sale']['sale'];
                else
                    $r['cost'] = round($r['cost'] - $r['cost'] * $r['sale']['sale'] / 100, 2);
            }
            //trim(trim($r['old_cost'],'0'),'.')
        }
    }

    function getTodaySale(&$PRODUCT)
    {
        $data = $this->qs('*', 'WHERE active=1 and periode>=' . $this->_CFG['time'] . ' and periods<=' . $this->_CFG['time'] . ' and product!=0 ORDER BY RAND() LIMIT 1');
        if (count($data)) {
            $prodList = $PRODUCT->fItem($data[0]['product'], $this->config['productField']);
            $this->setNewCost($prodList, $data);
            return $prodList[$data[0]['product']];

        }
        return array();
    }

}
