<?

/**
 *
 *
 *
 * 	  _checkmodstruct()
 * 	  _install()
 * 	  _reinstall()
 * 	  _insertDefault()
 * 	  _checkdir($dir)
 * 	  _fldformer($key, $param)
 */
final class modulprm_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features())
			return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->mf_use_charid = true;
		$this->mf_istree = true;
		$this->mf_timestamp = true;
		$this->prm_add = false;
		//$this->prm_del = false;
		$this->mf_createrid = false;
		$this->caption = "Модули";
		return true;
	}

	function _create() {
		parent::_create();

		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['tablename'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
		$this->fields['path'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['extend'] = array('type' => 'varchar', 'width' => 255);
		$this->fields['ver'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '0.1');
		$this->fields['typemodul'] = array('type' => 'tinyint', 'width' => 2, 'attr' => 'NOT NULL');

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['tablename'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Таблица');
		$this->fields_form['path'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Путь');
		$this->fields_form['extend'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Подменяемый модуль');
		$this->fields_form['ver'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Версия');
		$this->fields_form['typemodul'] = array('type' => 'list', 'listname' => 'typemodul', 'readonly' => 1, 'caption' => 'Описание');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

		$this->_enum['typemodul'] = array(
			0 => 'Системный модуль',
			3 => 'WEPconf Модуль',
			5 => 'Дочерние модули');

		$this->ordfield = 'typemodul,name';
	}

	function _childs() {
		$this->create_child('modulgrp');
	}

	/**
	 * Дамп модулей установленных
	 *
	 * @param int $ugroup_id
	 * @return array
	 */
	function userPrm($ugroup_id=0) {
		$result = $this->SQL->execSQL('SELECT t1.*,t2.access, t2.mname FROM ' . $this->tablename . ' t1 LEFT Join ' . $this->childs['modulgrp']->tablename . ' t2 on t2.owner_id=t1.id and t2.ugroup_id=' . $ugroup_id . ' where t1.active=1 ORDER BY ' . $this->ordfield);
		if ($result->err)
			$this->_message($result->err);
		$this->data = array();
		while ($row = $result->fetch_array()) {
			$this->data[$row['id']]['active'] = $row['active'];
			$this->data[$row['id']]['access'] = array_flip(explode('|', trim($row['access'], '|')));
			if ($row['mname'])
				$this->data[$row['id']]['name'] = $row['mname'];
			else
				$this->data[$row['id']]['name'] = $row['name'];
			$this->data[$row['id']]['ver'] = $row['ver'];
			$this->data[$row['id']]['typemodul'] = $row['typemodul'];
			$path = explode(':', $row['path']);
			$this->data[$row['id']]['path'] = $this->_CFG['modulinc'][$path[0]]['path'] . $path[1];
			$this->data[$row['id']]['tablename'] = $row['tablename'];
		}
		return $this->data;
	}

	/**
	 * Форма  установки модулей
	 *
	 * @return array
	 */
	public function instalModulForm() {
		$html = '';
		$mess = array();
		$this->_select();

//print_r('<pre>');print_r($this->data);
		foreach ($this->_CFG['modulinc'] as $k => $r) {
			$dir = dir($r['path']);
			while (false !== ($entry = $dir->read())) {
				if ($entry != '.' && $entry != '..' && $pos = strpos($entry, '.class')) {
					$entry = substr($entry, 0, $pos);
					if ($entry != '') {
						if (isset($DATA[$k . '_' . $entry])) {
							$mess[] = array('name' => 'error', 'value' => 'Ошибка. Модуль с таким названием `' . $entry . '` уже имеется в системных модулях');
							continue;
						}
						if (count($_POST)) {
							if (isset($_POST[$k . '_' . $entry]))
								$val = true;
							else
								$val = false;
						}
						elseif (isset($this->data[$entry]))
							$val = true;
						else
							$val = false;
						$DATA[$k . '_' . $entry] = array(
							'caption' => $this->_enum['typemodul'][$k] . ' <b>' . $entry . '</b>',
							'comment' => '',
							'type' => 'checkbox',
							'value' => $val,
							'_entry' => $entry,
							'_parent' => '',
							'_type' => $k
						);
						$resData = $this->checkClassStruct($entry);
						if ($resData['parent']) {
							$DATA[$k . '_' . $entry]['comment'] .= '<div>Зависим от ' . $resData['parent'] . '</div>';
							$DATA[$k . '_' . $entry]['_parent'] = $resData['parent'];
						}
						// проверяем необходимые модули
						if (isset($this->_CFG['require_modul'][$entry])) {
							$DATA[$k . '_' . $entry]['disabled'] = true;
							$DATA[$k . '_' . $entry]['value'] = true;
							$DATA[$k . '_' . $entry]['comment'] .= 'Жизненно необходимый модуль!';
						}
						// выводим текст ошибки
						if (isset($resData['error'])) {
							$DATA[$k . '_' . $entry]['disabled'] = true;
							$DATA[$k . '_' . $entry]['value'] = false;
							foreach ($resData['error'] as $mr)
								$DATA[$k . '_' . $entry]['comment'] .= '<div class="err">' . $mr . '</div>';
						}
					}
				}
			}
			$dir->close();
		}

		$res = 0;
		if (count($_POST) and isset($_POST['sbmtinstall'])) {
			startCatchError();
			foreach ($DATA as $k => $r) {
				$MODUL = NULL;
				if (isset($_POST[$k]) or isset($this->_CFG['require_modul'][$r['_entry']])) {
					if (!isset($this->data[$r['_entry']])) {
						if ($r['_parent'] !== '' and !isset($_POST['0_' . $r['_parent']]) and !isset($_POST['3_' . $r['_parent']]) ) {
							$mess[] = array('name' => 'error', 'value' => 'Ошибка. Не подключен родительский модуль `' . $r['_parent'] . '` для `' . $r['_entry'] . '`. ');
							$res = -1;
							continue;
						}
						/*if (!_new_class($r['_entry'], $MODUL, $MODUL->null, true)) {
							$mess[] = array('name' => 'error', 'value' => 'Ошибка запуска модуля `' . $r['_entry'] . '`');
							$res = -1;
							continue;
						}*/
						//Установка модуля
						list($flag,$mess2) = $this->Minstall($r['_entry']);
						$mess = array_merge($mess,$mess2);
						if (!$flag) {
							$mess[] = array('name' => 'error', 'value' => 'Ошибка установки модуля `' . $r['_entry'] . '`');
							$res = -1;
						}else
							$mess[] = array('name' => 'ok', 'value' => 'Модуль `' . $r['_entry'] . '` установлен.');
					}
				}
				elseif (isset($this->data[$r['_entry']]) and !isset($r['disabled'])) {
					if (!_new_class($r['_entry'], $MODUL, $this->null, true)) {
						$mess[] = array('name' => 'error', 'value' => 'Ошибка запуска модуля `' . $r['_entry'] . '`');
						$res = -1;
						continue;
					}
					//Удаление модуля
					if (!$this->Mdelete($MODUL)) {
						$mess[] = array('name' => 'error', 'value' => 'Ошибка удаления модуля `' . $r['_entry'] . '`');
						$res = -1;
					} else
						$mess[] = array('name' => 'ok', 'value' => 'Модуль `' . $r['_entry'] . '` удалён!');
				}
			}
			$err = getCatchError();
			if($err[0]) {
				$mess[] = array('name' => 'error', 'value' => $err[0]);
				$res = -1;
			}

			if ($res === 0) {
				$mess[] = array('name' => 'ok', 'value' => 'Процесс установки/удаления модулей прошло успешно.');
				$res = 1;
			}
		}
		//TODO : Инфо Фаил модуля
		$DATA['sbmtinstall'] = array(
			'type' => 'submit',
			'value' => 'Сохранить');

		$DATA['_*features*_'] = array('method' => 'POST', 'name' => 'step1');

		$DATA = array('form' => $DATA, 'messages' => $mess);

		return array($res, $DATA);
	}


	/**
	 * Проверка и сбор информации о модуле
	 *
	 * @global array $_CFG
	 * @param string $name id модуля
	 * @param string $file фаил модуля
	 * @return array
	 */
	protected function checkClassStruct($name) {
		$data = array('parent' => '');
		startCatchError();
		try {
			$name = $name.'_class';
			$obj = new ReflectionClass($name);
			$data['parent'] = $obj->getParentClass();
			$data['parent'] = $data['parent']->name;
			if($data['parent']=='kernel_extends')
				$data['parent'] = '';
			else {
				$data['parent'] = explode('_',$data['parent']);$data['parent'] = $data['parent'][0];
			}
			$MODUL = $obj->newInstance();
		} catch  (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}
		if($err = getCatchError() and isset($err[0]) and $err[0]) {
			$data['error'][] = $err[0];
		}
		return $data;
	}

	/**
	 * Установка модуля
	 *
	 * @param <type> $MODUL
	 * @param <type> $type
	 * @param <type> $file
	 * @return <type>
	 */
	public function Minstall($Mid, &$OWN=NULL) {
		$flag = false;
		$mess = array();
		list($MODUL,$mess) = $this->ForUpdateModulInfo($Mid,$OWN);
		if($MODUL) {
			$flag = static_tools::_installTable($MODUL);
			if ($flag and count($MODUL->Achilds))
				foreach ($MODUL->Achilds as $k=>$r) {
					list($flag,$mess2) = $this->Minstall($k, $MODUL);
					$mess = array_merge($mess,$mess2);
				}
		}
		return array($flag,$mess);
	}

	/**
	 * Удаление модуля
	 *
	 * @param object $MODUL Текщий объект класса
	 * @return bool Результат
	 */
	protected function Mdelete(&$MODUL) {
		$res = true;
		$result = $this->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
		if ($result->err) $res = false;
		//static_main::_message('Table `' . $MODUL->tablename . '` droped.', 3);
		$query = 'DELETE FROM `' . $this->tablename . '` WHERE id="' . $MODUL->_cl . '"';
		$this->SQL->execSQL($query);
		if (count($MODUL->childs))
			foreach ($MODUL->childs as &$child)
				$this->Mdelete($child);
		return $res;
	}

	/**
	 * Переустановка модуля
	 *
	 * @param object $MODUL Текщий объект класса
	 * @return bool Результат
	 */
	protected function _reinstall(&$MODUL, $type, $file) {
		$this->Mdelete($MODUL);
		$this->Minstall($MODUL, $type, $file);
		return true;
	}

	protected function mDump() {
		if(!isset($this->pdata)) {
			$this->data = $this->pdata = array();
			$result = $this->SQL->execSQL('SELECT * FROM ' . $this->tablename);
			if ($result->err)
				return $check_result;
			while ($row = $result->fetch_array()) {
				$this->data[$row['id']] = $row;
				$this->pdata[$row['parent_id']][$row['id']] = $row['id'];
			}
		}
		return true;
	}
	//Обновление базы всех модулей
	public function _checkmodstruct() {
		$rDATA = array();
		$this->mDump();
		foreach ($this->pdata[''] as $k => $r) {
			if($this->data[$k]['active'])
				$rDATA = array_merge($rDATA,static_tools::_checkmodstruct($k));
		}

		return $rDATA;
	}

	function ForUpdateModulInfo($Mid,&$OWN = NULL) {
		$MESS = array();
		$flag = false;
		try { // ловец снов
			$this->mDump();//дамп, выполниться один раз только
			$fpath = '';
			$ret = static_main::includeModulFile($Mid,$OWN);
			if($ret['file']) {
				$fpath = $ret['file'];
				$path = $ret['path'];
				$typemodul= $ret['type'];
			}
			$this->fld_data = array();
			if($fpath) {
				include_once($fpath);
				if(_new_class($Mid, $MODUL,$OWN, true)) {
					if($OWN and (!isset($this->data[$Mid]) or $this->data[$Mid]['parent_id']!=$OWN->_cl))
						$this->fld_data['parent_id'] = $OWN->_cl;
					if(!isset($this->data[$Mid]) or $this->data[$Mid]['name']!=$MODUL->caption)
						$this->fld_data['name'] = $MODUL->caption;
					if(!isset($this->data[$Mid]) or $this->data[$Mid]['tablename']!=$MODUL->tablename)
						$this->fld_data['tablename'] = $MODUL->tablename;
					if(!isset($this->data[$Mid]) or $this->data[$Mid]['path']!=$path)
						$this->fld_data['path'] = $path;
					if(!isset($this->data[$Mid]) or $this->data[$Mid]['ver']!=$MODUL->ver)
						$this->fld_data['ver'] = $MODUL->ver;
					if(!isset($this->data[$Mid]) or $this->data[$Mid]['typemodul']!=$typemodul)
						$this->fld_data['typemodul'] = $typemodul;
					//if($this->data[$Mid]['active']!=1)					$this->fld_data['active'] = 1;
					$obj = new ReflectionClass($Mid.'_class');
					$extend = $obj->getParentClass();
					$extend = $extend->name;
					if($extend=='kernel_extends') $extend = '';
					else $extend = substr($extend,0,-6);
					if(!isset($this->data[$Mid]) or $this->data[$Mid]['extend']!=$extend)
						$this->fld_data['extend'] = $extend;

					if(count($this->fld_data)) {
						$this->id = $Mid;
						if(!isset($this->data[$Mid])) {
							$this->fld_data['id'] = $Mid;
							if($this->_add())
								$MESS[] = array('name' => 'alert', 'value' => 'Данные для модуля `'.$Mid.'`['.$path.'] успешно записанны.');
							else
								$MESS[] = array('name' => 'error', 'value' => 'Ошибка записи данных для модуля `'.$Mid.'`['.$path.'].');
						}
						else {
							if($this->_update())
								$MESS[] = array('name' => 'alert', 'value' => 'Данные для модуля `'.$Mid.'`['.$path.'] успешно обновленны.');
							else
								$MESS[] = array('name' => 'error', 'value' => 'Ошибка обновления данных для модуля `'.$Mid.'`['.$path.'].');
						}
					}
					$flag = &$MODUL;
				}else {
					$MESS[] = array('name' => 'error', 'value' => 'Ошибка при инициализации модуля `'.$Mid.'`['.$path.']. Модуль будет отключен.');
					$this->fld_data['active'] = 0;
					$this->id = $Mid;
					$this->_update();
				}
			} else {
				$MESS[] = array('name' => 'error', 'value' => 'Фаилы модуля `'.$Mid.'`['.$path.'] отсутствуют и этот модуль будет удален из базы данных.');
				$this->id = $Mid;
				$this->_delete();
			}
		} catch  (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			$MESS[] = array('name' => 'error', 'value' => 'Ошибка при инициализации модуля `'.$Mid.'`['.$path.']. Модуль будет отключен.');
			$this->fld_data['active'] = 0;
			$this->id = $Mid;
			$this->_update();
		}

		/*if(count($MODUL->Achilds))
			foreach($MODUL->Achilds as $k=>$r) {
				$MESS = array_merge($MESS,$this->ForUpdateModulInfo($k,$MODUL));
			}*/
		return array($flag,$MESS);
	}

}

class modulgrp_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features())
			return false;
		$this->mf_timestamp = true;
		$this->prm_add = false;
		$this->prm_del = false;
		$this->mf_createrid = false;
		$this->singleton = false;
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = "Привелегии";

		$this->unique_fields['ou'] = array('owner_id', 'ugroup_id');

		$this->_enum['access'] = array(
			0 => 'нет',
			1 => 'Чтение (Все)',
			2 => 'Чтение (Только свои)',
			3 => 'Редактирование (Все)',
			4 => 'Редактирование (Только свои)',
			5 => 'Удаление (Все)',
			6 => 'Удаление (Только свои)',
			7 => 'Отключение (Все)',
			8 => 'Отключение (Только свои)',
			9 => 'Добавление',
			'A' => 'Сортировка',
			'B' => 'Переустановка модуля',
			'C' => 'Переиндексация модуля',
			'D' => 'Настроика модуля',
			'E' => 'Проверка структуры модуля',
			'F' => 'Глобальные настройки сервера'
		);

		//$this->fields['name'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields['mname'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['ugroup_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['access'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'default' => '');

		//$this->fields_form['name'] = array('type' => 'text','readonly' => 1, 'caption' => 'Группа');
		$this->fields_form['owner_id'] = array('type' => 'hidden', 'readonly' => 1);
		$this->fields_form['ugroup_id'] = array('type' => 'list', 'readonly' => 1, 'listname' => array('class' => 'ugroup'), 'caption' => 'Группа');
		$this->fields_form['mname'] = array('type' => 'text', 'caption' => 'СпецНазвание модуля');
		$this->fields_form['access'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'access', 'caption' => 'Права доступа');
	}

	function _checkmodstruct_old($flag=true) {
		if ($this->owner->_cl != 'modulprm')
			return array();
		if ($flag)
			$check_result = parent::_checkmodstruct();

		$this->mQuery = $this->uQuery = $this->dQuery = array();

		global $UGROUP;
		if (!$UGROUP and !_new_class('ugroup', $UGROUP)) {
			trigger_error('Невозможно подключить `ugroup`', E_USER_WARNING);
			exit();
		}

		$grpdata = array();
		$result = $this->SQL->execSQL('SELECT id,name FROM ' . $UGROUP->tablename . ' WHERE level>0'); //админов не учитываем
		if ($result->err)
			exit();
		while ($row = $result->fetch_array())
			$grpdata[$row['id']] = $row;

		$result = $this->SQL->execSQL('SELECT * FROM ' . $this->owner->tablename);
		if ($result->err)
			exit();
		while ($row = $result->fetch_array()) {
			foreach ($grpdata as $k => $r) {
				$this->mQuery[$row['id'] . '_' . $k] = array('owner_id' => $row['id'], 'ugroup_id' => $k, 'name' => $r['name']);
			}
		}

		$data = array();
		$result = $this->SQL->execSQL('SELECT * FROM ' . $this->tablename);
		if ($result->err)
			exit();
		while ($row = $result->fetch_array()) {
			if (isset($this->mQuery[$row['owner_id'] . '_' . $row['ugroup_id']])) {
				if ($this->mQuery[$row['owner_id'] . '_' . $row['ugroup_id']]['name'] != $row['name'])
					$this->uQuery[$row['id']] = array('name' => $row['name']);
				unset($this->mQuery[$row['owner_id'] . '_' . $row['ugroup_id']]);
			}
			else {//delete row
				$this->dQuery[$row['id']] = $row;
			}
		}

		foreach ($this->mQuery as $k => $r) {
			$q = 'INSERT INTO `' . $this->tablename . '` (`' . implode('`,`', array_keys($r)) . '`) VALUES (\'' . implode('\',\'', $r) . '\')';
			$check_result[$this->tablename]['Новая запись для ' . $k]['newquery'] = $q;
		}
		foreach ($this->uQuery as $k => $r) {
			$q = array();
			foreach ($r as $kk => $rr) {
				if ($kk != 'id')
					$q[] = '`' . $kk . '`="' . $rr . '"';
			}
			$q = 'UPDATE `' . $this->tablename . '` SET ' . implode(', ', $q) . ' WHERE id="' . $r['id'] . '"';
			$check_result[$this->tablename]['Обновляем запись для ' . $k]['newquery'] = $q;
		}
		if (count($this->dQuery)) {
			$check_result[$this->tablename]['Удаляем запись']['newquery'] = 'DELETE FROM `' . $this->tablename . '` WHERE `id` IN ("' . implode('","', array_keys($this->dQuery)) . '")';
		}

		/* if (isset($_POST['sbmt'])) {
		  foreach($this->mQuery as $k=>$r) {
		  $q = 'INSERT INTO `'.$this->tablename.'` (`'.implode('`,`', array_keys($r)).'`) VALUES (\''.implode('\',\'', $r).'\')';
		  $result = $this->SQL->execSQL($q);
		  if($result->err) exit();
		  }
		  foreach($this->uQuery as $k=>$r) {
		  $q = array();
		  foreach($r as $kk=>$rr) {
		  if($kk!='id')
		  $q[] = '`'.$kk.'`="'.$rr.'"';
		  }
		  $q = 'UPDATE `'.$this->tablename.'` SET '.implode(', ',$q).' WHERE id="'.$r['id'].'"';
		  $result = $this->SQL->execSQL($q);
		  if($result->err) exit();
		  }
		  if(count($this->dQuery)) {
		  $result = $this->SQL->execSQL('DELETE FROM `'.$this->tablename.'` WHERE `id` IN ("'.implode('","',array_keys($this->dQuery)).'")');
		  if($result->err) exit();
		  }
		  } else {
		  if(count($this->dQuery))
		  $check_result[$this->tablename][]['ok'] = '<span style="color:#4949C9;">Будут удалены записи из табл '.$this->tablename.' ('.count($this->dQuery).')</span>';
		  if(count($this->uQuery))
		  $check_result[$this->tablename][]['ok'] = '<span style="color:#4949C9;">Будут обновлены записи из табл '.$this->tablename.' ('.count($this->dQuery).')</span>';
		  if(count($this->mQuery))
		  $check_result[$this->tablename][]['ok'] = '<span style="color:#4949C9;">Будут добавлены записи из табл '.$this->tablename.' ('.count($this->dQuery).')</span>';
		  } */

		return $check_result;
	}

	function _install() {
		$ret = parent::_install();
		$_POST['sbmt'] = 1;
		$this->_checkmodstruct(false);
		return $ret;
	}

	function _UpdItemModul($param) {
		$ret = parent::_UpdItemModul($param);
		//if($ret[1]) {
		//	session_unset();
		//}
		return $ret;
	}

}

?>
