<?php
class confighistory_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля

		$this->caption = 'История настроек';
		$this->comment = 'Различные варианты конфигураций модулей';
		$this->ver = '0.1';
		return true;
	}


	protected function _create() {
		parent::_create();
		$this->fields['conf'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['modul'] = array('type' => 'varchar', 'width'=>64, 'attr' => 'NOT NULL'); 
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'hidden', 'caption' => 'Название', 'mask'=>array('min'=>3));
		$this->fields_form['modul'] = array('type' => 'list', 'listname'=>'classList', 'caption' => 'Модуль', 'mask'=>array(), 'relationForm'=>true);
		$this->fields_form['conf'] = array('type' => 'hidden', 'caption' => 'Конфиг', 'mask'=>array('min'=>3));
	}

	protected function relationForm($val, &$my_fieldsForm) {
		$my_fieldsForm['param_'] = array(
			'caption'=>'***',
			'type'=>'text',
			'value'=>'+',
			'css'=>'addparam');
	}
	// TODO - сделать inc для применения различных вариантов конфигов

	// TODO - метод добавления - храниение конфига в виде JSON в поле conf
}


