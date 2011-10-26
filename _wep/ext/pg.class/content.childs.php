<?php

class content_class extends kernel_extends {

	function _set_features()
	{
		if (!parent::_set_features()) return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Содержимое';
		$this->tablename = $this->_CFG['sql']['dbpref'].'pg_content';
		$this->addForm = array();
		return true;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['marker'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1', 'default'=>'text');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['global'] = array('type' => 'bool', 'attr' => 'NOT NULL','default'=>'0');
		$this->fields['pagetype'] = array('type' => 'varchar', 'width'=>255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['funcparam'] = array('type' => 'varchar', 'width'=>255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['keywords'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['description'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['ugroup'] =array('type' => 'varchar', 'width'=>254, 'attr' => 'NOT NULL','default'=>'|0|');
		$this->fields['styles'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['script'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['memcache'] = array('type' => 'int', 'width'=> 11,'attr' => 'NOT NULL','default'=>0);
		$this->fields['memcache_solt'] = array('type' => 'tinyint', 'width'=> 1,'attr' => 'NOT NULL','default'=>0);

		# memo
		//$this->memos['pg'] = array('max' => 50000);
		$this->fields['pg'] = array('type' => 'mediumtext', 'attr' => 'NOT NULL');

		$this->owner->_listnameSQL = 'template, name';
	}

	function setSystemFields() {
		$this->def_records[] = array('owner_id'=>1,'pg'=>'Сайт на стадии разработки','marker'=>'text','active'=>1);
		$this->def_records[] = array('owner_id'=>2,'pg'=>'СТраница не существует. Возможно была удалена.','marker'=>'text','active'=>1);
		$this->def_records[] = array('owner_id'=>3,'pg'=>'Недостаточно прав для доступа к странице','marker'=>'text','active'=>1);
		return parent::setSystemFields();
	}

	public function setFieldsForm($form=0) {
		# fields
		$this->fields_form['owner_id'] = array('type' => 'list', 'listname'=>'ownerlist', 'caption' => 'На странице'); 
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Подзаголовок');
		$this->fields_form['marker'] = array('type' => 'list', 'listname'=>'marker', 'caption' => 'Маркер','mask'=>array());
		$this->fields_form['global'] = array('type' => 'checkbox', 'caption' => 'Глобально?', 'mask' =>array());
		$this->fields_form['pagetype'] = array('type' => 'list', 'listname'=>'pagetype', 'caption' => 'INC', 'mask'=>array('onetd'=>'INC'));
		$this->fields_form['funcparam'] = array('type' => 'text', 'caption' => 'Опции', 'mask' =>array('name'=>'all','onetd'=>'Опции'), 'comment'=>'Значения разделять символом &');
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Redirect', 'mask' =>array('onetd'=>'close'));
		$this->fields_form['pg'] = array('type' => 'ckedit', 'caption' => 'Text',
			'mask'=>array('fview'=>1, 'max' => 500000), 
			'paramedit'=>array(
				'CKFinder'=>1,
				'extraPlugins'=>"'cntlen,syntaxhighlight,timestamp'",
				'contentsCss' => "['/_design/default/style/style.css', '/_design/_style/style.css']",
				'toolbar' => 'Page',
		));
		if($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list','multiple'=>2,'listname'=>'ugroup', 'caption' => 'Доступ','default'=>'0');
		$this->fields_form['styles'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'style', 'caption' => 'CSS', 'mask' =>array('onetd'=>'Дизайн'));
		$this->fields_form['script'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'script', 'caption' => 'SCRIPT','mask' =>array('onetd'=>'none'));
		$this->fields_form['keywords'] = array('type' => 'text', 'caption' => 'META-keywords','mask'=>array('onetd'=>'none'));
		$this->fields_form['description'] = array('type' => 'text', 'caption' => 'META-description','mask'=>array('onetd'=>'close'));
		$this->fields_form['ordind'] = array('type' => 'text', 'caption' => 'ORD');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
		$this->fields_form['memcache'] = array('type' => 'int', 'caption' => 'Memcache time','comment'=>'-1 - отключает кеш полностью,0 - откл кеширование,1> - кеширование в сек.','mask' =>array('fview'=>1));
		$this->fields_form['memcache_solt'] = array('type' => 'list', 'listname'=>'memcache_solt', 'caption' => 'Memcache соль','mask' =>array('fview'=>1));
		$this->_enum['memcache_solt'] = array(
			0=>'---',
			1=>'UserID',
			2=>'SessionID',
			3=>'COOKIE',
			4=>'IP',
		);
	}

	function _getlist(&$listname,$value=NULL) {
		global $_CFG;
		$data = array();
		if ($listname == 'pagetype') {
			return $this->getInc();
		}
		elseif ($listname == 'ugroup') {
			return $this->owner->_getlist($listname,$value);
		}
		elseif ($listname == 'marker') {
			return $this->owner->config['marker'];
		}
		else return parent::_getlist($listname,$value);
		/*else {
			return $this->owner->_getlist($listname,$value);
		}*/
		return $data;
	}

	function getInc($pref='.inc.php',$def=' - Текст - ') {
		$data = array();
		$dir = dir($this->_CFG['_PATH']['wep_inc']);
		$data[''] = $def;
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..' && strstr($entry,$pref)) {
				$entry = substr($entry, 0, strpos($entry, $pref)); 
				$data['0:'.$entry] = $this->owner->_enum['inc'][0]['name'].$entry;
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['wep_ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..') {
				if(is_dir($this->_CFG['_PATH']['wep_ext'].$entry)) {
					$dir2 = dir($this->_CFG['_PATH']['wep_ext'].$entry);
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

		$dir = dir($this->_CFG['_PATH']['inc']);
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

	public function kPreFields(&$data,&$param) {
		$mess = parent::kPreFields($data,$param);
		$this->addForm = array();
		$this->fields_form['pagetype']['onchange'] = 'contentIncParam(this,\''.$this->_CFG['PATH']['wepname'] .'\',\''.(isset($data['funcparam'])?htmlspecialchars($data['funcparam']):'').'\');';

		if(isset($data['pagetype']) and $data['pagetype']) {
			$this->addForm = $this->getContentIncParam($data);
			if(count($this->addForm)) {
				$this->fields_form = static_main::insertInArray($this->fields_form,'pagetype',$this->addForm); // обработчик параметров рубрики
				$this->fields_form['funcparam']['style'] = 'display:none;';
			}
		}
		return $mess;
	}

	function getContentIncParam(&$data,$ajax=false) {
		$pagetype = $data['pagetype'];
		$FUNCPARAM = $data['funcparam'];
		$formFlex = array();
		$flagPG = false;
		if($FUNCPARAM) $FUNCPARAM = explode('&',$FUNCPARAM);
		else $FUNCPARAM = array();
		$typePG = explode(':',$pagetype);
		if(count($typePG)==2 and file_exists($this->owner->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php'))
			$flagPG = $this->owner->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php';
		elseif(file_exists($this->_CFG['_PATH']['inc'].$rowPG['pagetype'].'.inc.php'))
			$flagPG = $this->_CFG['_PATH']['inc'].$rowPG['pagetype'].'.inc.php';
		elseif(file_exists($this->_CFG['_PATH']['wep_inc'].$rowPG['pagetype'].'.inc.php'))
			$flagPG = $this->_CFG['_PATH']['wep_inc'].$rowPG['pagetype'].'.inc.php';
		else {
			$formFlex['tr_flexform_0'] = array('type'=>'info', 'css'=>'addparam', 'caption'=>'<span class="error">Обрботчик страниц "'.$this->owner->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php" не найден!</span>');
			//trigger_error('Обрботчик страниц "'.$this->owner->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php" не найден!', E_USER_WARNING);
			return $formFlex;
		}

		if(count($_POST)!=count($data) or $ajax) {
			$fl = true;
		}
		else
			$fl = false;
		$file = file_get_contents($flagPG);
		if(strpos($file,'$ShowFlexForm')!==false) {
			$ShowFlexForm = true;
			$tempform = include($flagPG);
			if(count($tempform)) {
				foreach($tempform as $k=>$r) {
					if($fl) {
						$r['value'] = $data['flexform_'.$k] = $FUNCPARAM[$k];
					}
					$r['css']='addparam';
					$formFlex['flexform_'.$k] = $r;
				}
			}
		} else {
		}
		return $formFlex;
	}


	public function _save_item($vars=array(),$where=false) {
		$funcparam = array();
		if(count($this->addForm)) {
			foreach($this->addForm as $k=>$r) {
				if($r['type']!='info') {
					$funcparam[(int)substr($k,9)] = $vars[$k];
				}
			}
			if(count($funcparam)) {
				ksort($funcparam);
				$vars['funcparam'] = implode('&',$funcparam);
			}
		}
		if($ret = parent::_save_item($vars,$where)) {
		}
		return $ret;
	}

	public function _add_item($vars) {
		$funcparam = array();
		if(count($this->addForm)) {
			foreach($this->addForm as $k=>$r) {
				if($r['type']!='info')
					$funcparam[(int)substr($k,9)] = $vars[$k];
			}
			if(count($funcparam)) {
				ksort($funcparam);
				$vars['funcparam'] = implode('&',$funcparam);
			}
		}
		if($ret = parent::_add_item($vars)) {
		}
		return $ret;
	}
}

