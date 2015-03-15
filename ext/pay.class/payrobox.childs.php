<?php


class payrobox_class extends kernel_extends
{

    function init()
    {
        parent::init();
        $this->default_access = '|9|';
        $this->mf_timecr = true; // создать поле хранящее время создания поля
        $this->mf_actctrl = true;
        $this->prm_add = false; // добавить в модуле
        $this->prm_del = false; // удалять в модуле
        $this->prm_edit = false; // редактировать в модуле
        //$this->pay_systems = true; // Это модуль платёжной системы
        $this->showinowner = false;

        $this->caption = 'Платежи Робо кассы - ALPHA';
        $this->comment = 'Логи платежей и пополнения счетов пользователями';
        $this->ver = '0.1';
    }

    function _create_conf2(&$obj)
    {
        //parent::_create_conf();

        $obj->config['mrh_login'] = 'rbxch';
        $obj->config['mrh_pass1'] = 'testing123';
        $obj->config['mrh_pass2'] = 'testing456';

        $obj->config['in_curr'] = 'PCR';
        $obj->config['culture'] = 'ru';

    }

    protected function _create()
    {
        parent::_create();

        $this->pay_systems = array(
            'WMZ' => array(
                'caption' => 'webmoney Z',
                'icon' => 'wmz.gif',
            ),
            'WMU' => array(
                'caption' => 'webmoney U',
                'icon' => 'wmu.gif',
            ),
        );

    }

    // возвращает массив с формой, которая отправит пользователя на сайт платежной системы
    function get_pay_form($system, $amount, $inv_id)
    {

        $crc = md5($this->config['mrh_login'] . ':' . $amount . ':' . $inv_id . ':' . $this->config['mrh_pass1']);

        $data['text_before'] = 'Пополнение баланса через Робокассу<br/>После оплаты, на Ваш счет поступит ' . $amount . ' рублей.';
        $data['text_after'] = '';
        $data['options'] = array(
            'method' => 'post',
            'name' => 'pay',
            'action' => 'http://test.robokassa.ru/Index.aspx',
        );
        $data['form'] = array(
            'MrchLogin' => array(
                'value' => $this->config['mrh_login'],
                'type' => 'hidden',
            ),
            'OutSum' => array(
                'value' => $amount,
                'type' => 'hidden',
            ),
            'InvId' => array(
                'value' => $inv_id,
                'type' => 'hidden',
            ),
            'Desc' => array(
                'value' => $this->owner->config['desc'],
                'type' => 'hidden',
            ),
            'SignatureValue' => array(
                'value' => $crc,
                'type' => 'hidden',
            ),
            'IncCurrLabel' => array(
                'value' => $this->config['in_curr'],
                'type' => 'hidden',
            ),
            'Culture' => array(
                'value' => $this->config['culture'],
                'type' => 'hidden',
            ),
            'submit' => array(
                'value' => 'Перейти к оплате',
                'type' => 'submit',
            ),
        );

        return $data;
    }


}


