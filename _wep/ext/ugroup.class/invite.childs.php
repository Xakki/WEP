<?php

class invite_class extends kernel_extends
{

    function _set_features()
    {
        parent::_set_features();
        $this->mf_ordctrl = true;
        $this->mf_actctrl = true;
        $this->caption = 'Инвайты';
    }

    function _create()
    {
        parent::_create();

        # fields
        $this->fields['code'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
        $this->fields['user_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);

    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);
        # fields
        $this->fields_form['user_id'] = array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'Приглашённый');
        $this->fields_form['code'] = array('type' => 'text', 'caption' => 'Код приглашения');
    }

}

