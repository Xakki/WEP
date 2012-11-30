<?php


class paypost_class extends kernel_extends {

	function _set_features() {
		parent::_set_features();
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_actctrl = true;
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->pay_systems = true; // Это модуль платёжной системы

		$this->caption = 'Наложенный платёж';
		$this->comment = 'Почта России';
		$this->ver = '0.1';

	}
	
	function _create_conf2(&$obj) {
		//parent::_create_conf();

		/*$obj->config['mrh_login'] = 'rbxch';
		$obj->config['mrh_pass1'] = 'testing123';
		$obj->config['mrh_pass2'] = 'testing456';
		
		$obj->config['in_curr'] = 'PCR';
		$obj->config['culture'] = 'ru';*/
		
	}

	protected function _create() {
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

}


