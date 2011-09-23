<?php
class exportboard_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->caption = 'Экспорт объяв';
		$this->messages_on_page = 50;
		return true;
	}

	function _create() {
		parent::_create();

		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['www'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['wwwadd'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['encode'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['region'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['phpexport'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL');
		$this->fields['ondef'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default'=>0);

		$this->_enum['phpexport'] = array(
			0=>'0.php',
			1=>'1.php'
		);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' =>array('min'=>1));
		$this->fields_form['www'] = array('type' => 'text', 'caption' => 'Сайт', 'mask' =>array('name'=>'www'));
		$this->fields_form['phpexport'] = array('type' => 'list', 'listname'=>'phpexport', 'caption' => 'Скрипт экспорта');
		$this->fields_form['wwwadd'] = array('type' => 'text', 'caption' => 'Адрес формы', 'mask' =>array('name'=>'www'));
		$this->fields_form['encode'] = array('type' => 'text', 'caption' => 'Кодировка', 'mask' =>array('name'=>'all'));
		$this->fields_form['region'] = array(
			'type' => 'ajaxlist', 
			'label'=>'Введите название города или региона', 
			'listname'=>array('tablename'=>'city','where'=>'tx.active=1','nameField'=>'IF(tx.region_name_ru!=\'\',concat(tx.name,", ",tx.region_name_ru),tx.name)','ordfield'=>'tx.center DESC, tx.parent_id, tx.region_name_ru, tx.name','limit'=>30), 
			'caption' => 'Регион/Город',
		);
		$this->fields_form['ondef'] = array('type' => 'checkbox', 'caption' => 'Включено по умолчанию?');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');

	}

	function _childs() {
		$this->create_child('exportrubric');
		$this->create_child('sendboard');
	}

	function getListData($city=0) {
		$form = array();
		$query = 'WHERE (region=0';
		if($city)
			$query .= ' or region='.$city;
		$query .= ')';
		if(!static_main::_prmUserCheck(1))
			$query .= ' and active=1';
		$this->eDATA = $this->_query('*',$query);
		return $this->eDATA;
	}

	function getListBoard($city=0) {
		$eDATA = $this->getListData($city);
		$form= array();
		if(count($eDATA)) {
			foreach($eDATA as $ek=>$er) {
				$form['exportboard'.$er['id']] = array('type' => 'checkbox', 'caption' => $er['name'], 'comment'=>'Публикация на сайте '.$er['www'], 'css'=>'boardexport formparam', 'mask'=>array('fview'=>1));
				if($er['ondef']) {
					$form['exportboard'.$er['id']]['value'] = 1;
				}
				if(!$er['active'])
					$form['exportboard'.$er['id']]['comment'] .= ' <b>BETA тестирование</b>';
			}
			//$_tpl['onload'] .= 'jQuery(\'#tr_boardexport div.showparam\').click();';
		}
		return array($eDATA,$form);
	}
}

class exportrubric_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->caption = 'Экспортные рубрики';
		$this->ordfield = 'name';
		return true;
	}

	function _create() {
		parent::_create();

		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['nameid'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['rubric'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['over'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default'=>0);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' =>array('min'=>1));
		$this->fields_form['nameid'] = array('type' => 'text', 'caption' => 'Ключ рубрики', 'mask' =>array('min'=>1));
		$this->fields_form['rubric'] = array('type' => 'list', 'listname'=> array('tablename'=>'rubric','is_tree'=>true), 'caption' => 'Связь с рубрикой');
		$this->fields_form['over'] = array('type' => 'checkbox', 'caption' => 'Всё остальное');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
	}
}

class sendboard_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Отправленные объявления';
		$this->mf_timecr = true;
		$this->mf_timeup = true;
		$this->prm_add = false; // добавить в модуле
		//$this->prm_del = false; // удалять в модуле
		//$this->prm_edit = false; // редактировать в модуле
		$this->ordfield = 'mf_timecr DESC';
		return true;
	}

	function _create() {
		parent::_create();

		$this->fields['board_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['text'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['result'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['textresult'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['pageinfo'] = array('type' => 'text', 'attr' => 'NOT NULL');

		$this->_enum['resultsend'] = array(
			0=>'готовится к отправке',
			1=>'отправленно , ожидание статуса',
			2=>'отправка неудачна',
			3=>'отправка успешна');

		$this->index_fields['result'] = 'result';
		$this->index_fields['board_id'] = 'board_id';
	}
	
	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['board_id'] = array('type' => 'list', 'listname'=> array('tablename'=>'board','nameField'=>'concat(\'<a href="/_wep/index.php?_view=list&_modul=board&board_id=\',tx.id,\'&_type=edit">\',tx.id,\'</a>\')'), 'caption' => 'Объявление','readonly'=>true);
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата отправки', 'mask'=>array('fview'=>2,'sort'=>1));
		$this->fields_form['text'] = array('type' => 'textarea', 'caption' => 'Данные отправки', 'mask'=>array('name'=>'html'));
		$this->fields_form['result'] = array('type' => 'list', 'listname'=>'resultsend', 'caption' => 'Статус', 'mask'=>array('onetd'=>'Статус экспорта'));
		$this->fields_form['mf_timeup'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата создания', 'mask'=>array('fview'=>2,'sort'=>1,'onetd'=>'close'));
		$this->fields_form['textresult'] = array('type' => 'text', 'caption' => 'Текст результата','readonly'=>true);
		$this->fields_form['pageinfo'] = array('type' => 'text', 'caption' => 'Код результата','readonly'=>true);

	}
}

