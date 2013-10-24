<?php
class redirect_class extends kernel_extends
{
    function _set_features()
    {
        parent::_set_features();
        $this->mf_createrid = true;
        $this->mf_ipcreate = true;
        $this->mf_timecr = true;
        $this->cf_reinstall = true;
        $this->prm_add = false;
        //$this->prm_edit = false;
        //$this->mf_namefields = false;
        //$this->cf_reinstall = true;
        $this->ver = '0.1';
        $this->caption = 'Редирект';
    }

    function _create()
    {
        parent::_create();
        $this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'default' => 'NULL');
        $this->fields['cnt'] = array('type' => 'int', 'width' => 11, 'default' => 0, 'noquote' => true);
        $this->fields['referer'] = array('type' => 'varchar', 'width' => 255, 'default' => '');
        $this->fields['useragent'] = array('type' => 'varchar', 'width' => 255, 'default' => '');
        $this->fields['cookies'] = array('type' => 'varchar', 'width' => 255, 'default' => '');
        $this->fields['uniq'] = array('type' => 'varchar', 'width' => 255, 'default' => '');

        $this->ordfield = $this->mf_timecr;

        $this->index_fields['name'] = 'name';
        $this->index_fields['referer'] = 'referer';
        $this->index_fields['mf_ipcreate'] = 'mf_ipcreate';
        $this->unique_fields['uniq'] = 'uniq';

        //$this->cron[] = array('modul'=>$this->_cl,'function'=>'gc()','active'=>1,'time'=>86400);
        $this->ordfield = 'id DESC';
    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);
        $this->fields_form['name'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Ссылка');
        $this->fields_form['cnt'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Кол-во');
        $this->fields_form['referer'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Источник');
        $this->fields_form['useragent'] = array('type' => 'textarea', 'readonly' => 1, 'caption' => 'Браузер');
        //$this->fields_form['cookies'] = array('type' => 'textarea', 'readonly' => 1, 'caption' => 'Куки');
        $this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Time');
        $this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'IP');
        $this->fields_form['creater_id'] = array('type' => 'list', 'listname' => array('class' => 'users'), 'readonly' => 1, 'caption' => 'User');
    }

    function addRedirect($name)
    {
        $data = array('name' => $name, 'referer' => $_SERVER['HTTP_REFERER'], 'useragent' => $_SERVER['HTTP_USER_AGENT'], 'cookies' => serialize($_COOKIE), 'cnt' => '1+cnt');
        $data['uniq'] = md5($data['name'] . $data['useragent'] . $data['cookies']);
        $this->_addUp($data);
    }
}
