<?php
/**
 * Дочерний модуль "Контент страниц"
 * @author Xakki
 * @version 0.4.5 
 */
class content_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features())
			return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Содержимое';
		$this->tablename = 'pg_content';
		$this->addForm = array();
		return true;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['marker'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1', 'default' => 'text');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['global'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '0');
		$this->fields['pagetype'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['funcparam'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['keywords'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['description'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['ugroup'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default' => '|0|');
		$this->fields['styles'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['script'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['memcache'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['memcache_solt'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default' => 0);

		# memo
		//$this->memos['pg'] = array('max' => 50000);
		$this->fields['pg'] = array('type' => 'mediumtext', 'attr' => 'NOT NULL');

		$this->owner->_listnameSQL = 'template, name';
	}

	public function setFieldsForm($form = 0) {
		# fields
		$this->fields_form['owner_id'] = array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'На странице');
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Подзаголовок');
		$this->fields_form['marker'] = array('type' => 'list', 'listname' => 'marker', 'caption' => 'Маркер', 'mask' => array());
		$this->fields_form['global'] = array('type' => 'checkbox', 'caption' => 'Глобально?', 'mask' => array());
		$this->fields_form['pagetype'] = array('type' => 'list', 'listname' => 'pagetype', 'caption' => 'INC', 'mask' => array('onetd' => 'INC'));
		$this->fields_form['funcparam'] = array('type' => 'text', 'caption' => 'Опции', 'mask' => array('name' => 'all', 'onetd' => 'Опции'), 'comment' => 'Значения разделять символом &', 'css' => 'addparam');
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Redirect', 'mask' => array('onetd' => 'close'));
		$this->fields_form['pg'] = array('type' => 'ckedit', 'caption' => 'Text',
			'mask' => array('fview' => 1, 'max' => 500000),
			'paramedit' => array(
				'CKFinder' => array('allowedExtensions'=>''), // разрешаем загрузку любых фаилов
				'extraPlugins' => "'cntlen,syntaxhighlight,timestamp'",
				'toolbar' => 'Page',
				));
		if ($form) {
			//TODO : сделать подключение стилей , которые подключены к этой странице (включая глобальные стили)
			//$this->fields_form['pg']['paramedit']['contentsCss'] = "['/_design/default/style/main.css', '/_design/_style/main.css']";
		}
		if ($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'ugroup', 'caption' => 'Доступ', 'default' => '0', 'css'=>'minform');
		$this->fields_form['styles'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'style', 'caption' => 'CSS', 'mask' => array('onetd' => 'Дизайн'), 'css'=>'minform');
		$this->fields_form['script'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'script', 'caption' => 'SCRIPT', 'mask' => array('onetd' => 'none'), 'css'=>'minform');
		$this->fields_form['keywords'] = array('type' => 'text', 'caption' => 'META-keywords', 'mask' => array('onetd' => 'none'));
		$this->fields_form['description'] = array('type' => 'text', 'caption' => 'META-description', 'mask' => array('onetd' => 'close', 'name' => 'all'));
		$this->fields_form['ordind'] = array('type' => 'int', 'caption' => 'ORD');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
		$this->fields_form['memcache'] = array('type' => 'int', 'caption' => 'Memcache time', 'comment' => '-1 - отключает кеш полностью,0 - откл кеширование,1> - кеширование в сек.', 'mask' => array('fview' => 1));
		$this->fields_form['memcache_solt'] = array('type' => 'list', 'listname' => 'memcache_solt', 'caption' => 'Memcache соль', 'mask' => array('fview' => 1));
		$this->_enum['memcache_solt'] = array(
			0 => '---',
			1 => 'UserID',
			2 => 'SessionID',
			3 => 'COOKIE',
			4 => 'IP',
		);
	}

	function _getlist(&$listname, $value = NULL) {
		global $_CFG;
		$data = array();
		if ($listname == 'pagetype') {
			return $this->getInc();
		} 
		elseif ($listname == 'ugroup') {
			return $this->owner->_getlist($listname, $value);
		} 
		elseif ($listname == 'marker') {
			return $this->owner->config['marker'];
		}
		elseif ($listname == 'content') {
			return $this->getContentList();
		}
		else
			return parent::_getlist($listname, $value);
		/* else {
		  return $this->owner->_getlist($listname,$value);
		  } */
		return $data;
	}

	function getContentList() {
		$contentData = $this->qs('id as `#id#`,concat(id," - ",name," [",marker,"] ",pagetype) as `#name#`,"1" as `#checked#`,concat("p",owner_id) as oid','','#id#','oid');
		$vData = $this->owner->qs('concat("p",parent_id) as pid, concat("p",id) as `#id#`,name as `#name#`,"0" as `#checked#`','','#id#','pid');
		foreach($contentData as $k=>&$r) {
			if(isset($vData[$k])) {
				$vData[$k] = $r+$vData[$k];
			} else
				$vData[$k] = $r;
		}
		//print_r('<pre>');print_r($vData);
		return $vData;
	}

	function getInc($pref = '.inc.php', $def = ' - Текст - ') {
		$data = array();
		$dir = dir($this->_CFG['_PATH']['wep_inc']);
		$data[''] = $def;
		while (false !== ($entry = $dir->read())) {
			if ($entry[0] != '.' && $entry[0] != '..' && strstr($entry, $pref)) {
				$temp = substr($entry, 0, strpos($entry, $pref));

				$name= $this->getIncFileInfo($this->_CFG['_PATH']['wep_inc'] . '/'.$entry,'name');

				if($name)
					$name = $this->owner->_enum['inc'][0]['name'] . $entry. '('.$name.')';
				else
					$name = $this->owner->_enum['inc'][0]['name'] . $temp;
				$data['0:' . $temp] = $name;
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['wep_ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0] != '.' && $entry[0] != '..') {
				if (is_dir($this->_CFG['_PATH']['wep_ext'] . $entry)) {
					$dir2 = dir($this->_CFG['_PATH']['wep_ext'] . $entry);
					while (false !== ($entry2 = $dir2->read())) {
						if ($entry2[0] != '.' && $entry2[0] != '..' && strstr($entry2, $pref)) {
							$temp = substr($entry2, 0, strpos($entry2, $pref));

							$name= $this->getIncFileInfo($this->_CFG['_PATH']['wep_ext'] . $entry.'/'.$entry2,'name');

							if($name)
								$name = $this->owner->_enum['inc'][1]['name'] . $entry. '('.$name.')';
							else
								$name = $this->owner->_enum['inc'][1]['name'] . $entry . '/' . $temp;

							$data['1:' . $entry . '/' . $temp] = $name;
						}
					}
					$dir2->close();
				}
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['inc']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0] != '.' && $entry[0] != '..' && strstr($entry, $pref)) {
				$temp = substr($entry, 0, strpos($entry, $pref));
				
				$name= $this->getIncFileInfo($this->_CFG['_PATH']['inc'] . '/'.$entry,'name');

				if($name)
					$name = $this->owner->_enum['inc'][2]['name'] . $entry. '('.$name.')';
				else
					$name = $this->owner->_enum['inc'][2]['name'] . $temp;

				$data['2:' . $temp] = $name;
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0] != '.' && $entry[0] != '..') {
				if (is_dir($this->_CFG['_PATH']['ext'] . $entry)) {
					$dir2 = dir($this->_CFG['_PATH']['ext'] . $entry);
					while (false !== ($entry2 = $dir2->read())) {
						if ($entry2[0] != '.' && $entry2[0] != '..' && strstr($entry2, $pref)) {
							$temp = substr($entry2, 0, strpos($entry2, $pref));

							$name= $this->getIncFileInfo($this->_CFG['_PATH']['ext'] . $entry.'/'.$entry2,'name');

							if($name)
								$name = $this->owner->_enum['inc'][3]['name'] . $entry. '('.$name.')';
							else
								$name = $this->owner->_enum['inc'][3]['name'] . $entry . '/' . $temp;

							$data['3:' . $entry . '/' . $temp] = $name;
						}
					}
					$dir2->close();
				}
			}
		}
		$dir->close();
		return $data;
	}

	private function getIncFileInfo($file,$param=false) {
		$fcontent = file_get_contents($file);
		if($p1 = mb_strpos($fcontent,'/**')) {
			$fcontent = mb_substr($fcontent,($p1+3),(mb_strpos($fcontent,'*/')-($p1+3)));
			$fcontent = explode('*',$fcontent);
			if($param=='name')
				return $fcontent[1];
			return $fcontent;
		}
		return false;
	}

	public function kPreFields(&$f_data, &$f_param = array(), &$f_fieldsForm = null) {
		$mess = parent::kPreFields($f_data, $f_param, $f_fieldsForm );
		$this->addForm = array();
		$this->fields_form['pagetype']['onchange'] = 'contentIncParam(this,\'' . $this->_CFG['PATH']['wepname'] . '\',\'' . (isset($f_data['funcparam']) ? htmlspecialchars($f_data['funcparam']) : '') . '\');';

		if (isset($f_data['pagetype']) and $f_data['pagetype']) {
			$this->addForm = $this->getContentIncParam($f_data);
			if (count($this->addForm)) {
				$this->fields_form = static_main::insertInArray($this->fields_form, 'pagetype', $this->addForm); // обработчик параметров рубрики
				$this->fields_form['funcparam']['style'] = 'display:none;';
			}
		} else
			$this->fields_form['funcparam']['style'] = 'display:none;';
		return $mess;
	}

	function getContentIncParam(&$data, $ajax = false) {
		global $FUNCPARAM_FIX; // Фикс для совместимости со старыми версиями
		$pagetype = $data['pagetype'];
		$FUNCPARAM = $data['funcparam'];
		$formFlex = array();
		$flagPG = false;
		$flag_FIX = false;
		if ($FUNCPARAM) {
			$FUNCPARAM = explode('&', $FUNCPARAM);
			foreach ($FUNCPARAM as $kf => &$rf) {
				if (strpos($rf, '|') !== false) {
					$rf = explode('|', $rf);
					$rf = array_combine($rf, $rf);
				} elseif (strpos($rf, '#ext#') !== false)
					$FUNCPARAM_FIX[$kf] = &$rf;
			}
			unset($rf);
		}
		else
			$FUNCPARAM = array();
		$typePG = explode(':', $pagetype);
		if (count($typePG) == 2 and file_exists($this->owner->_enum['inc'][$typePG[0]]['path'] . $typePG[1] . '.inc.php'))
			$flagPG = $this->owner->_enum['inc'][$typePG[0]]['path'] . $typePG[1] . '.inc.php';
		elseif (!$pagetype)
			return $formFlex;
		/* elseif(file_exists($this->_CFG['_PATH']['inc'].$pagetype.'.inc.php'))
		  $flagPG = $this->_CFG['_PATH']['inc'].$pagetype.'.inc.php';
		  elseif(file_exists($this->_CFG['_PATH']['wep_inc'].$pagetype.'.inc.php'))
		  $flagPG = $this->_CFG['_PATH']['wep_inc'].$pagetype.'.inc.php'; */
		else {
			$formFlex['tr_flexform_0'] = array('type' => 'info', 'css' => 'addparam flexform', 'caption' => '<span class="error">Обрботчик страниц "' . $this->owner->_enum['inc'][$typePG[0]]['path'] . $typePG[1] . '.inc.php" не найден!</span>');
			//trigger_error('Обрботчик страниц "'.$this->owner->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php" не найден!', E_USER_WARNING);
			return $formFlex;
		}

		if (count($_POST) != count($data) or $ajax) {
			$fl = true;
		}
		else
			$fl = false;
		$file = file_get_contents($flagPG);
		//Проверяем есть ли в коде флексформа
		if (strpos($file, '$ShowFlexForm') !== false) {
			$ShowFlexForm = true;
			$tempform = include($flagPG);
			if (is_array($tempform) and count($tempform)) {
				foreach ($tempform as $k => $r) {
					if ($fl) {
						$r['value'] = $data['flexform_' . $k] = $FUNCPARAM[$k];
					}
					$r['css'] = 'addparam flexform'; // Добавляем форме спец стиль (завязано на скриптах)
					$formFlex['flexform_' . $k] = $r;
				}
			}
		} else {
			
		}
		return $formFlex;
	}

	public function _update($vars = array(), $where = false, $flag_select = true) {
		$vars = $this->SetFuncparam($vars);
		if ($ret = parent::_update($vars, $where, $flag_select)) {
			
		}
		return $ret;
	}

	public function _add($vars = array(), $flag_select = true) {
		$vars = $this->SetFuncparam($vars);
		if ($ret = parent::_add($vars, $flag_select)) {
			
		}
		return $ret;
	}

	private function SetFuncparam($vars) {
		$funcparam = array();
		if (count($this->addForm)) {
			foreach ($this->addForm as $k => $r) {
				if ($r['type'] != 'info') {
					$key = (int) substr($k, 9);
					if (is_array($vars[$k]))
						$funcparam[$key] = implode('|', $vars[$k]);
					else
						$funcparam[$key] = $vars[$k];
				}
			}
			if (count($funcparam)) {
				ksort($funcparam);
				$vars['funcparam'] = implode('&', $funcparam);
			}
		}
		return $vars;
	}

}

