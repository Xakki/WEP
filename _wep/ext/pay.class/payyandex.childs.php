<?php
class payyandex_class extends kernel_extends {

	function _create_conf2(&$obj) {/*CONFIG*/
		//parent::_create_conf();

		$obj->config['ya_login'] = '';
		$obj->config['ya_password'] = '';

		$obj->config_form['ya_info'] = array('type' => 'info', 'caption'=>'<h3>Яндекс.Деньги</h3>');
		$obj->config_form['ya_login'] = array('type' => 'text', 'caption' => 'Логин', 'comment'=>'', 'style'=>'background-color:#Aab7ec;');
		$obj->config_form['ya_password'] = array('type' => 'password', 'md5'=>false, 'caption' => 'Пароль', 'style'=>'background-color:#Aab7ec;');
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		$this->config = &$this->owner->config;
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Яндекс.Деньги';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		return true;
	}


	protected function _create() {
		parent::_create();
		$this->fields['cost'] = array('type' => 'float', 'width' => '11,2','attr' => 'NOT NULL');
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Цена (руб.)', 'mask'=>array());
	}
}


