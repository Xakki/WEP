<?

class content_class extends kernel_extends {

	function _set_features()
	{
		if (!parent::_set_features()) return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = "Содержимое";
		$this->tablename = $this->_CFG['sql']['dbpref'].'pg_content';
		return true;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['marker'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['global'] = array('type' => 'bool', 'attr' => 'NOT NULL','default'=>'0');
		$this->fields['pagetype'] = array('type' => 'varchar', 'width'=>255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['funcparam'] = array('type' => 'varchar', 'width'=>255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['ugroup'] =array('type' => 'varchar', 'width'=>254, 'attr' => 'NOT NULL','default'=>'|0|');
		$this->fields['styles'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['script'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['memcache'] = array('type' => 'int', 'width'=> 11,'attr' => 'NOT NULL','default'=>0);

		# memo
		$this->memos['pg'] = array('max' => 50000);

		# fields
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Подзаголовок');
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Redirect', 'mask' =>array());
		$this->fields_form['marker'] = array('type' => 'list', 'listname'=>'marker', 'caption' => 'Маркер','mask'=>array());
		$this->fields_form['global'] = array('type' => 'checkbox', 'caption' => 'Глобально?', 'mask' =>array());
		$this->fields_form['pagetype'] = array('type' => 'list', 'listname'=>'pagetype', 'caption' => 'INC', 'mask' =>array());
		$this->fields_form['funcparam'] = array('type' => 'text', 'caption' => 'Доп. параметры', 'mask' =>array('name'=>'all'), 'comment'=>'Значения разделять символом &');
		$this->fields_form['pg'] = array('type' => 'ckedit', 'caption' => 'Text','mask'=>array('fview'=>1, 'width' => 50000), 'paramedit'=>array('CKFinder'=>1,'extraPlugins'=>"'cntlen'"));
		if($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list','multiple'=>2,'listname'=>'ugroup', 'caption' => 'Доступ пользователю','default'=>'0');
		$this->fields_form['styles'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'style', 'caption' => 'CSS', 'mask' =>array('onetd'=>'Дизайн'));
		$this->fields_form['script'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'script', 'caption' => 'SCRIPT','mask' =>array('onetd'=>'close'));
		$this->fields_form['ordind'] = array('type' => 'text', 'caption' => 'ORD');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
		$this->fields_form['memcache'] = array('type' => 'int', 'caption' => 'Memcache time','comment'=>'0 - откл кеширование,1> - кеширование в сек.');

		$this->owner->_listnameSQL = 'template, name';
	}

	function _install() {
		$this->def_records[] = array('owner_id'=>'404','pg'=>'Недостаточно прав для доступа к странице','marker'=>'text','active'=>1);
		return parent::_install();
	}

	function _getlist(&$listname,$value=0) {
		global $_CFG;
		$data = array();
		if ($listname == "pagetype") {
			return $this->getInc();
		}
		else {
			return $this->owner->_getlist($listname,$value);
		}
		return $data;
	}

	function getInc($pref='.inc.php') {
		$data = array();
		$dir = dir($this->_CFG['_PATH']['ctext']);
		$data[''] = ' - Текст - ';
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..' && strstr($entry,$pref)) {
				$entry = substr($entry, 0, strpos($entry, $pref)); 
				$data['0:'.$entry] = $this->owner->_enum['inc'][0]['name'].$entry;
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['extcore']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..') {
				if(is_dir($this->_CFG['_PATH']['extcore'].$entry)) {
					$dir2 = dir($this->_CFG['_PATH']['extcore'].$entry);
					while (false !== ($entry2 = $dir2->read())) {
						if ($entry2[0]!='.' && $entry2[0]!='..' && strstr($entry2,$pref)) {
							$entry2 = substr($entry2, 0, strpos($entry2, $pref)); 
							$data['1:'.$entry.'/'.$entry2] = $this->owner->_enum['inc'][1]['name'].$entry.'/'.$entry2;
						}
					}
					$dir2->close();
				}
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['ptext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..' && strstr($entry,$pref)) {
				$entry = substr($entry, 0, strpos($entry, $pref)); 
				$data['2:'.$entry] = $this->owner->_enum['inc'][2]['name'].$entry;
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..') {
				if(is_dir($this->_CFG['_PATH']['ext'].$entry)) {
					$dir2 = dir($this->_CFG['_PATH']['ext'].$entry);
					while (false !== ($entry2 = $dir2->read())) {
						if ($entry2[0]!='.' && $entry2[0]!='..' && strstr($entry2,$pref)) {
							$entry2 = substr($entry2, 0, strpos($entry2, $pref)); 
							$data['3:'.$entry.'/'.$entry2] = $this->owner->_enum['inc'][3]['name'].$entry.'/'.$entry2;
						}
					}
					$dir2->close();
				}
			}
		}
		$dir->close();
		return $data;
	}

}
?>
