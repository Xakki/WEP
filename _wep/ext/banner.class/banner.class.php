<?php
class banner_class extends kernel_extends {

	function _set_features() {
		parent::_set_features();
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Баннер';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['text'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['gnezdo'] = array('type' => 'tinyint', 'width' => 3, 'attr' => 'NOT NULL');
		$this->fields['cell'] = array('type' => 'tinyint', 'width' => 3, 'attr' => 'NOT NULL');
		$this->fields['city'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		//$this->fields['page'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['dataon'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['dataoff'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['showmax'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['clickmax'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['show'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['click'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');

		$this->_enum['cell'] = array(0,1,2,3,4,5,6);
	}
	/**
	* $form = 1 - для вывода формы
	*/
	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'HREF', 'mask' =>array('min'=>1,'name'=>'www'));
		$this->fields_form['text'] = array('type' => 'text', 'caption' => 'Текст');
		$this->fields_form['gnezdo'] = array('type' => 'text', 'caption' => 'Гнездо','comment'=>'Название гнезда для вызова');
		$this->fields_form['cell'] = array('type' => 'list', 'listname'=>'cell', 'caption' => 'Ячейка гнезда');
		$this->fields_form['city'] = array(
			'caption' => 'Город', 'type' => 'list', 
			'listname'=>array('class'=>'city','where'=>'tx.active=1 and tx.parent_id=0','nameField'=>'concat(tx.name,"*")'), 
			'mask' =>array('filter'=>1));
		$this->fields_form['page'] = array(
			'caption' => 'Страницы','type' => 'list', 'multiple'=>FORM_MULTIPLE_SIMPLE,
			'listname'=>array('class'=>'pg', 'where'=>'tx.active=1'));
		$this->fields_form['dataon'] = array('type' => 'date', 'caption' => 'Дата вкл.');
		$this->fields_form['dataoff'] = array('type' => 'date', 'caption' => 'Дата выкл.');
		$this->fields_form['showmax'] = array('type' => 'text', 'caption' => 'Макс. показа');
		$this->fields_form['clickmax'] = array('type' => 'text', 'caption' => 'Макс. кликов');
		$this->fields_form['show'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Стат. показа');
		$this->fields_form['click'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Стат. кликов');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');

	}

	/*function __destruct(){
		//тут запись статистики
		parent::__destruct();
	}*/

}


