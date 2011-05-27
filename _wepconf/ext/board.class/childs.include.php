<?

class paramb_class extends kernel_extends {
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->showinowner=false;// не показывать
		$this->mf_createrid = false;
		$this->owner_unique = true; // уникальная запис для одного объявления
		return true;
	}
	function _create() {
		parent::_create();
		$this->caption = 'Значения параметров';
		$this->fields['name0'] =	array('type' => 'tinyint', 'width' =>1, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name1'] =	array('type' => 'tinyint', 'width' =>1, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name2'] =	array('type' => 'tinyint', 'width' =>1, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name3'] =	array('type' => 'tinyint', 'width' =>1, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name4'] =	array('type' => 'tinyint', 'width' =>1, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name5'] =	array('type' => 'tinyint', 'width' =>1, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);

		$this->fields['name10'] =	array('type' => 'smallint', 'width' =>4, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name11'] =	array('type' => 'smallint', 'width' =>4, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name12'] =	array('type' => 'smallint', 'width' =>4, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name13'] =	array('type' => 'smallint', 'width' =>4, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		
		$this->fields['name20'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name21'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
	
		$this->fields['name50'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name51'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name52'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name53'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name54'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name55'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name56'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name57'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name58'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
		$this->fields['name59'] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
	
		$this->fields['name70'] = array('type' => 'varchar', 'width' =>254, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['name71'] = array('type' => 'varchar', 'width' =>254, 'attr' => 'NOT NULL','default'=>'');

		//$this->fields['name80'] = array('type' => 'float', 'width' =>11, 'attr' => 'NOT NULL');
	
		//$this->fields['name90'] = array('type' => 'text', 'attr' => 'NOT NULL');

	}
}

class boardvote_class extends kernel_extends {
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->showinowner=false;// не показывать
		$this->mf_ipcreate = true;
		$this->mf_timecr = true;
		$this->mf_namefields=false;
		$this->caption = 'Голосование';
		return true;
	}
	function _create() {
		parent::_create();
		$this->index_fields['mf_ipcreate'] = 'mf_ipcreate';
		$this->index_fields['mf_timecr'] = 'mf_timecr';
		$this->index_fields['type'] = 'type';
		$this->fields['type'] =	array('type' => 'tinyint', 'width' =>3, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['agent'] =	array('type' => 'varchar', 'width' =>255, 'attr' => 'NOT NULL','default'=>'');
		/*		1-5 номинации		*/
	}
}

?>