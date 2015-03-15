<?php

class paysms_class extends kernel_extends
{

    function _create_conf2(&$obj)
    { /*CONFIG*/
        //parent::_create_conf();

        $obj->config['sms_login'] = '';
        $obj->config['sms_password'] = '';
        $obj->config['sms_txn-prefix'] = '';
        $obj->config['sms_create-agt'] = 1;
        $obj->config['sms_lifetime'] = 1080;
        $obj->config['sms_alarm-sms'] = 0;
        $obj->config['sms_alarm-call'] = 0;
        $obj->config['sms_minpay'] = 10;
        $obj->config['sms_maxpay'] = 15000;

        $obj->config_form['sms_info'] = array('type' => 'info', 'caption' => '<h3>sms</h3><p>На сайте необходимо разместить логотип и описание(<a href="http://ishopnew.sms.ru/docs.html" target="_blank">материалы sms для сайта</a>)</p>');
        $obj->config_form['sms_login'] = array('type' => 'text', 'caption' => 'Логин', 'comment' => '', 'style' => 'background-color:#2ab7ec;');
        $obj->config_form['sms_password'] = array('type' => 'password', 'md5' => false, 'caption' => 'Пароль', 'style' => 'background-color:#2ab7ec;');
        $obj->config_form['sms_txn-prefix'] = array('type' => 'text', 'caption' => 'Префикс в номере счёта', 'comment' => '', 'style' => 'background-color:#2ab7ec;');
        //$this->owner->config_form['sms_create-agt'] = array('type' => 'text', 'caption' => 'Логин','comment'=>'Если 1 то при выставлении счёта создается пользователь в системе sms. При этом оплатить счёт можно в терминале наличными без ввода ПИН-кода.', 'style'=>'background-color:gray;');
        $obj->config_form['sms_lifetime'] = array('type' => 'text', 'caption' => 'Таймаут', 'comment' => 'Время жизни счёта по умолчанию. Задается в часах. Максимум 45 суток (1080 часов)', 'style' => 'background-color:#2ab7ec;');
        $obj->config_form['sms_alarm-sms'] = array('type' => 'text', 'caption' => 'alarm-sms', 'comment' => '1 - включит СМС оповещение (СМС платно)', 'style' => 'background-color:#2ab7ec;');
        $obj->config_form['sms_alarm-call'] = array('type' => 'text', 'caption' => 'alarm-call', 'comment' => '1 - включит звонок (платно)', 'style' => 'background-color:#2ab7ec;');
        $obj->config_form['sms_minpay'] = array('type' => 'int', 'caption' => 'Миним. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#2ab7ec;');
        $obj->config_form['sms_maxpay'] = array('type' => 'int', 'caption' => 'Максим. сумма', 'comment' => 'при пополнении счёта', 'style' => 'background-color:#2ab7ec;');
    }

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

        $this->caption = 'SMS - ALPHA';
        $this->comment = 'Логи платежей и пополнения счетов пользователями';
        $this->ver = '0.1';
    }


    protected function _create()
    {
        parent::_create();
        $this->fields['cost'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL'); // в коппейках
    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);
        $this->fields_form['cost'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Цена (руб.)', 'mask' => array());
    }
}


