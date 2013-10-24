<?php


class prodvote_class extends kernel_extends
{
    function _set_features()
    {
        parent::_set_features();
        $this->showinowner = false; // не показывать
        $this->mf_ipcreate = true;
        $this->mf_timecr = true;
        $this->mf_namefields = false;
        $this->caption = 'Голосование';
    }

    function _create()
    {
        parent::_create();
        $this->index_fields['mf_ipcreate'] = 'mf_ipcreate';
        $this->index_fields['mf_timecr'] = 'mf_timecr';
        $this->index_fields['type'] = 'type';
        $this->fields['type'] = array('type' => 'tinyint', 'width' => 3, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['agent'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
        /*		1-5 номинации		*/
    }
}

