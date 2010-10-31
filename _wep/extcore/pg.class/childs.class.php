<?

class content_class extends kernel_class {

	function _set_features()
	{
		if (parent::_set_features()) return 1;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = "Содержимое";
		$this->tablename = $this->_CFG['sql']['dbpref'].'pg_content';
		return 0;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['marker'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['global'] = array('type' => 'bool', 'attr' => 'NOT NULL DEFAULT 0');
		$this->fields['pagetype'] = array('type' => 'varchar', 'width'=>'15', 'attr' => 'NOT NULL DEFAULT ""');
		$this->fields['funcparam'] = array('type' => 'varchar', 'width'=>'255', 'attr' => 'NOT NULL DEFAULT ""');
		$this->fields['ugroup'] =array('type' => 'varchar', 'width'=>254, 'attr' => 'NOT NULL DEFAULT ""');
		$this->fields['styles'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL DEFAULT ""');
		$this->fields['script'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL DEFAULT ""');

		# memo
		$this->memos['pg'] = array('max' => 50000);

		# fields
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Подзаголовок');
		$this->fields_form['marker'] = array('type' => 'list', 'listname'=>'marker', 'caption' => 'Маркер','mask'=>array());
		$this->fields_form['global'] = array('type' => 'checkbox', 'caption' => 'Глобально?', 'mask' =>array());
		$this->fields_form['pagetype'] = array('type' => 'list', 'listname'=>'pagetype', 'caption' => 'INC', 'mask' =>array());
		$this->fields_form['funcparam'] = array('type' => 'text', 'caption' => 'Доп. параметры', 'mask' =>array('name'=>'all'), 'comment'=>'Значения разделять символом &');
		$this->fields_form['pg'] = array('type' => 'ckedit', 'caption' => 'Text','mask'=>array('fview'=>1, 'width' => 50000), 'paramedit'=>array('CKFinder'=>1,'extraPlugins'=>"'cntlen'"));
		if($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list','multiple'=>1,'listname'=>'ugroup', 'caption' => 'Доступ пользователю','default'=>'0');
		$this->fields_form['styles'] = array('type' => 'list', 'multiple'=>1, 'listname'=>'styles', 'caption' => 'CSS', 'mask' =>array('onetd'=>'Дизайн'));
		$this->fields_form['script'] = array('type' => 'list', 'multiple'=>1, 'listname'=>'script', 'caption' => 'SCRIPT','mask' =>array('onetd'=>'close'));
		$this->fields_form['ordind'] = array('type' => 'text', 'caption' => 'ORD');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');

		$this->def_records[] = array('owner_id'=>'404','pg'=>'Недостаточно прав для доступа к странице','marker'=>'text','active'=>1);
		$this->owner->_listnameSQL = 'template, name';
	}

	function _getlist($listname,$value=0) {
		global $_CFG;
		$data = array();
		if ($listname == "pagetype") {
			$dir = dir($this->_CFG['_PATH']['ctext']);
			$data[''] = ' - Текст - ';
			while (false !== ($entry = $dir->read())) {
				if ($entry[0]!='.' && $entry[0]!='..' && strstr($entry,'.inc.php')) {
					$entry = substr($entry, 0, strpos($entry, '.inc.php')); 
					$data[$entry] = 'WEP - '.$entry;
				}
			}
			$dir->close();

			$dir = dir($this->_CFG['_PATH']['ptext']);
			while (false !== ($entry = $dir->read())) {
				if ($entry[0]!='.' && $entry[0]!='..' && strstr($entry,'.inc.php')) {
					$entry = substr($entry, 0, strpos($entry, '.inc.php')); 
					$data[$entry] = $entry;
				}
			}
			$dir->close();
			return $data;
		}
		else {
			return $this->owner->_getlist($listname,$value);
		}
		return $data;
	}


}
?>