<?php

function _CHLU(&$_this, $arg=array()) {
	$_this->fields['loginza_login'] =  array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
	$_this->fields['loginza_token'] =  array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
	$_this->fields['loginza_provider'] =  array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
	$_this->fields['loginza_data'] =  array('type' => 'text', 'attr' => '');
	if(!$_this->owner->config['uniq_email'])
		unset($_this->owner->unique_fields['email']);
}