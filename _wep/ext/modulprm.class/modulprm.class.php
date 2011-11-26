<?php

/**
 *
 *
 *
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
		$this->default_access = '|0|';
		return true;
	}

	function _create() {
		parent::_create();
		$this->ordfield = 'typemodul,name';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['tablename'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
		$this->fields['path'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['extend'] = array('type' => 'varchar', 'width' => 255);
		$this->fields['ver'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default' => '0.1');
		$this->fields['typemodul'] = array('type' => 'tinyint', 'width' => 2, 'attr' => 'NOT NULL');
		$this->fields['hook'] = array('type' => 'text');

		$this->_enum['typemodul'] = array(
			0 => 'Системный модуль',
			3 => 'WEPconf Модуль',
			5 => 'Дочерние модули');
	}

	function _childs() {
		$this->create_child('modulgrp');
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['tablename'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Таблица');
		$this->fields_form['path'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Путь');
		$this->fields_form['extend'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Подменяемый модуль');
		$this->fields_form['hook'] = array('type' => 'textarea', 'caption' => 'Перехватчики','mask'=>array('fview'=>1));
		$this->fields_form['ver'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Версия');
		$this->fields_form['typemodul'] = array('type' => 'list', 'listname' => 'typemodul', 'readonly' => 1, 'caption' => 'Описание');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

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
			static_main::log($result->err);
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
			$this->data[$row['id']]['hook'] = $row['hook'];
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
		$this->mDump();
		$this->modulprm_ext = $this->_CFG['modulprm_ext'];
		unset($this->_CFG['modulprm_ext']); // чтобы не подменять обращения к модулям

		foreach ($this->_CFG['modulinc'] as $k => $r) {
			$dir = dir($r['path']);
			while (false !== ($entry = $dir->read())) {
				if ($entry != '.' && $entry != '..' && $pos = strpos($entry, '.class')) {
					$entry = substr($entry, 0, $pos);
					if ($entry != '') {
						if (isset($DATA[$k . '_' . $entry])) {
							$mess[] = array('error','Ошибка. Модуль с таким названием `' . $entry . '` уже имеется в системных модулях');
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
							'comment' => (isset($this->data[$entry])?' <i>Включено.</i> ':''),
							'type' => 'checkbox',
							'value' => $val,
							'_entry' => $entry,
							'_parent' => '',
							'_type' => $k
						);
						$ret = static_main::includeModulFile($entry);
						if($ret['file']) require_once($ret['file']);
						$resData = array();
						if(class_exists($entry. '_class',false)) {
							$resData = $this->checkClassStruct($entry);
						}else
							$DATA[$k . '_' . $entry]['comment'] .= 'Не возможно подключить модуль. Класс `'.$entry.'_class` не найден.';
						if ($resData['parent']) {
							$DATA[$k . '_' . $entry]['comment'] .= '<div>Наследуется от ' . $resData['parent'] . '</div>';
							$DATA[$k . '_' . $entry]['_parent'] = $resData['parent'];
						}
						if ($resData['depend']) {
							$DATA[$k . '_' . $entry]['comment'] .= '<div>Зависим от ' . implode(', ',$resData['depend']) . '</div>';
							$DATA[$k . '_' . $entry]['_depend'] = $resData['depend'];
						}
						// проверяем необходимые модули
						if (isset($this->_CFG['require_modul'][$entry])) {
							$DATA[$k . '_' . $entry]['disabled'] = true;
							$DATA[$k . '_' . $entry]['value'] = true;
							$DATA[$k . '_' . $entry]['style'] = 'display:none;';
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
		if (count($_POST) and isset($_POST['sbmt'])) {
			startCatchError();
			foreach ($DATA as $k => $r) {
				$MODUL = NULL;
				if (isset($_POST[$k]) or isset($this->_CFG['require_modul'][$r['_entry']])) {
					if (!isset($this->data[$r['_entry']])) {
						if ($r['_parent'] !== '' and !isset($_POST['0_' . $r['_parent']]) and !isset($_POST['3_' . $r['_parent']]) and !isset($this->data[$r['_parent']])) {
							$mess[] = array('error', 'Ошибка. Не подключен родительский модуль `' . $r['_parent'] . '` для `' . $r['_entry'] . '`. ');
							$res = -1;
							continue;
						}
						if(isset($r['_depend']) and count($r['_depend'])) {
							foreach($r['_depend'] as $dep) {
								if (!isset($_POST['0_' . $dep]) and !isset($_POST['3_' . $dep]) and !isset($this->data[$dep])) {
									$mess[] = array('error', 'Ошибка. Не подключен зависимый модуль `' . $dep . '` для `' . $r['_entry'] . '`. ');
									$res = -1;
									continue;
								}
							}
						}
						//Установка модуля
						$flag = true;
						$rDATA = static_tools::_checkmodstruct($r['_entry']);
						foreach($rDATA as $tr) {
							foreach($tr as $tr2) {
								if(isset($tr2['@mess'])) {
									foreach($tr2['@mess'] as $tm) {
										$mess[] = $tm;
										if(isset($tm[0]) and $tm[0]=='error')
											$flag = false;
									}
								}
							}
						}
						if (!$flag) {
							$mess[] = array('error','Ошибка установки модуля `' . $r['_entry'] . '`');
							$res = -1;
						}else
							$mess[] = array('ok', 'Модуль `' . $r['_entry'] . '` установлен.');
					} else {
						if(isset($r['_depend']) and count($r['_depend']) and $this->data[$r['_entry']]['active']) {
							foreach($r['_depend'] as $dep) {
								if (!isset($_POST['0_' . $dep]) and !isset($_POST['3_' . $dep]) and !isset($this->data[$dep])) {
									$mess[] = array('error', 'Ошибка. Не подключен зависимый модуль `' . $dep . '` для `' . $r['_entry'] . '`. ');
									$res = -1;
									continue;
								}
							}
						}
					}
				}
				elseif (isset($this->data[$r['_entry']]) and !isset($r['disabled'])) {
					if (!_new_class($r['_entry'], $MODUL, $this->null, true)) {
						$mess[] = array('error', 'Ошибка запуска модуля `' . $r['_entry'] . '`');
						$res = -1;
						continue;
					}
					//Удаление модуля
					if (!$this->Mdelete($MODUL)) {
						$mess[] = array('error', 'Ошибка удаления модуля `' . $r['_entry'] . '`');
						$res = -1;
					} else
						$mess[] = array('ok', 'Модуль `' . $r['_entry'] . '` удалён!');
				} 
			}
			$err = getCatchError();
			if ($err) {
				$mess[] = array('error', $err);
				$res = -1;
			}

			if ($res === 0) {
				$mess[] = array('ok', 'Процесс установки/удаления модулей прошло успешно.');
				$res = 1;
			}
		}

		if(!isset($this->_CFG['modulprm_ext']) and isset($this->temp_modulprm_ext))
			$this->_CFG['modulprm_ext'] = $this->temp_modulprm_ext;

		//TODO : Инфо Фаил модуля
		$DATA['sbmt'] = array(
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
			$name = $name . '_class';
			$obj = new ReflectionClass($name);
			$data['parent'] = $obj->getParentClass();
			$data['parent'] = $data['parent']->name;
			if ($data['parent'] == 'kernel_extends')
				$data['parent'] = '';
			else {
				$data['parent'] = explode('_', $data['parent']);
				$data['parent'] = $data['parent'][0];
			}
			$MODUL = $obj->newInstance();
			$data['depend'] = $MODUL->_dependClass;
		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}
		if ($err = getCatchError() and $err) {
			$data['error'][] = $err;
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

	/**
	 * Удаление модуля
	 *
	 * @param object $MODUL Текщий объект класса
	 * @return bool Результат
	 */
	protected function Mdelete(&$MODUL) {
		$res = true;
		if ($MODUL->tablename) {
			$result = $this->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
			if ($result->err)
				$res = false;
		}
		//static_main::log('Table `' . $MODUL->tablename . '` droped.', 3);
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
		static_tools::_checkmodstruct($MODUL->_cl);
		return true;
	}

	protected function mDump() {
		if (!isset($this->pdata) or !count($this->pdata) or !count($this->data)) {
			$this->data = $this->pdata = array();
			$result = $this->SQL->execSQL('SELECT * FROM ' . $this->tablename);
			if ($result->err)
				return $check_result;
			while ($row = $result->fetch_array()) {
				$this->data[$row['id']] = $row;
				$this->pdata[$row['parent_id']][$row['id']] = $row['id'];
			}
			foreach($this->_CFG['require_modul'] as $k=>$r) {
				if(!isset($this->pdata[''][$k]))
					$this->pdata[''][$k] = $r;
			}
		}
		return true;
	}

	//Обновление базы всех модулей
	public function _checkmodstruct() {
		$rDATA = array();
		/*Установка и проверка главных модулей*/
		//$this->_CFG['modulprm'] = array();
		if(!isset($_POST['sbmt'])) {
			$_POST['sbmt'] = 1;
			foreach($this->_CFG['require_modul'] as $k=>$r) {
				_new_class($k, $MODUL);
				$temp = static_tools::_checkTableRev($MODUL);
				if(count($temp))
					$rDATA = array_merge($rDATA, $temp);
			}
			unset($_POST['sbmt']);
			unset($this->_CFG['modulprm']);
		}

		$this->mDump();
		if(isset($this->pdata['']) and count($this->pdata[''])) {
			$this->modulgrpDump();
			$this->temp_modulprm_ext = $this->_CFG['modulprm_ext'];
			$this->_CFG['modulprm_ext'] = NULL;
			foreach ($this->pdata[''] as $k => $r) {
				$rDATA = array_merge($rDATA, static_tools::_checkmodstruct($k));
			}

			$this->_CFG['modulprm_ext'] = $this->temp_modulprm_ext;	

		}

		return $rDATA;
	}

	function ForUpdateModulInfo($Mid, &$OWN = NULL) {
		$MESS = array();
		$flag = false;
		$this->mDump();
		$this->modulgrpDump();
		try { // ловец снов
			$fpath = '';
			$ret = static_main::includeModulFile($Mid, $OWN);
			if ($ret['file']) {
				$fpath = $ret['file'];
				$path = $ret['path'];
				$typemodul = $ret['type'];
			}
			$this->fld_data = array();
			if ($fpath) {
				include_once($fpath);

				if (_new_class($Mid, $MODUL, $OWN)) {
					$MODUL->setSystemFields();// установка системных полей
					if ($OWN and (!isset($this->data[$Mid]) or $this->data[$Mid]['parent_id'] != $OWN->_cl))
						$this->fld_data['parent_id'] = $OWN->_cl;
					if (!isset($this->data[$Mid]) or !$this->data[$Mid]['name'])// or $this->data[$Mid]['name'] != $MODUL->caption
						$this->fld_data['name'] = $MODUL->caption;
					if (!isset($this->data[$Mid]) or $this->data[$Mid]['tablename'] != $MODUL->tablename)
						$this->fld_data['tablename'] = $MODUL->tablename;
					if (!isset($this->data[$Mid]) or $this->data[$Mid]['path'] != $path)
						$this->fld_data['path'] = $path;
					if (!isset($this->data[$Mid]) or $this->data[$Mid]['ver'] != $MODUL->ver)
						$this->fld_data['ver'] = $MODUL->ver;
					if ($MODUL->RCVerCore) {
						if (version_compare($MODUL->RCVerCore, self::versionCore) === -1)
							$MESS[] = array('name' => 'error', 'value' => 'Модуль `' . $Mid . '` устарел. Текущая версия ядра `' . self::versionCore . '`! ');
					}
					if (!isset($this->data[$Mid]) or $this->data[$Mid]['typemodul'] != $typemodul)
						$this->fld_data['typemodul'] = $typemodul;

					/*Xros HOOK*/
					$hook = '';
					if (count($MODUL->_setHook)) {
						$hook = str_replace(array("\t", "\n", "\r", ' '), '', var_export($MODUL->_setHook, true));
					}
					if (!isset($this->data[$Mid]) or $this->data[$Mid]['hook'] != $hook)
						$this->fld_data['hook'] = $hook;
					//if($this->data[$Mid]['active']!=1)					$this->fld_data['active'] = 1;
					// CRON JOB
					if(count($MODUL->cron)) {
						$CRON = static_tools::getFdata($this->_CFG['_FILE']['cron']);
						$cflag= false;
						foreach($MODUL->cron as $cr) {
							if(!isset($cr['file'])) $cr['file'] = '';
							if(!isset($cr['modul'])) $cr['modul'] = '';
							if(!isset($cr['function'])) $cr['function'] = '';
							$cn = md5($cr['file'].$cr['modul'].$cr['function']);
							if(!isset($CRON['cron'][$cn])) {
								if (isset($_POST['sbmt'])) {
									if(!isset($cr['active']))
										$cr['active'] = 1;
									if(!isset($cr['time']))
										$cr['time'] = 86400;
									$CRON['cron'][$cn] = $cr;
								}
								$cflag= true;
							}
						}
						if($cflag) {
							if(isset($_POST['sbmt'])) {
								list($cfl,$cmess) = static_tools::saveCFG($CRON,$this->_CFG['_FILE']['cron']);
								$MESS = array_merge($MESS,$cmess);
							} else 
								$MESS[] = array('notice', 'Будет добавленно задание в CRON');
						}
					}

					$obj = new ReflectionClass($Mid . '_class');
					$extend = $obj->getParentClass();
					$extend = $extend->name;
					if ($extend == 'kernel_extends')
						$extend = '';
					else
						$extend = substr($extend, 0, -6);
					if (!isset($this->data[$Mid]) or $this->data[$Mid]['extend'] != $extend)
						$this->fld_data['extend'] = $extend;
					$flag = &$MODUL;
					if (count($this->fld_data)) {
						$this->id = $Mid;
						// Добавляем инфу о модуле
						if (!isset($this->data[$Mid])) {
							$this->fld_data['id'] = $Mid;
							if (isset($_POST['sbmt'])) {print_r('<pre>');print_r($this->data);print_r($Mid);exit();
								if ($this->_add($this->fld_data,false))
									$MESS[] = array('notice', 'Информация о модуле `' . $Mid . '`[' . $path . '] успешно записанна.');
								else {
									$MESS[] = array('error', 'Ошибка записи данных для модуля `' . $Mid . '`[' . $path . '].'.print_r($this->fld_data,true));
									$flag = false;
								}
							}else
								$MESS[] = array('alert', 'Информация о модуле `' . $Mid . '`[' . $path . '] будет добавлена.');
						}
						// Если существует обновляем данные
						else {
							if (isset($_POST['sbmt'])) {
								if ($this->_update($this->fld_data,false, false))
									$MESS[] = array('notice', 'Информация о модуле `' . $Mid . '`[' . $path . '] успешно обновленна.');
								else {
									$MESS[] = array('error', 'Ошибка обновления данных для модуля `' . $Mid . '`[' . $path . '].');
									$flag = false;
								}
							} else
								$MESS[] = array('alert', 'Информация о модуле `' . $Mid . '`[' . $path . '] будет обновленна.');
						}
					}
					// Обновляем права доступа
					if ($flag) {
						$addQuery = $upQuery = array();
						if (isset($this->modulgrpData[$Mid]) and count($this->modulgrpData[$Mid]))
							foreach ($this->modulgrpData[$Mid] as $mk => $mr) {
								if (!isset($this->guserData[$mk])) {
									if (isset($_POST['sbmt'])) {
										$q = 'DELETE FROM `' . $this->childs['modulgrp']->tablename . '` WHERE `id`=' . $mr['id'];
										$result = $this->SQL->execSQL($q);
										if ($result->err)
											exit();
									} else
										$MESS[] = array('alert', 'Будет удалена группа '.$mr['name'].'['.$mr['id'].']');
								}
							}
						if (isset($this->guserData) and count($this->guserData))
							foreach ($this->guserData as $gk => $gr) {
								/*if (isset($this->modulgrpData[$Mid][$gk])) {
									if($this->modulgrpData[$Mid][$gk]['name']!=$gr['name']) {
										$q = 'UPDATE `' . $this->childs['modulgrp']->tablename . '` SET `name`="' . $gr['name'] . '" WHERE id="' . $this->modulgrpData[$Mid][$gk]['id'] . '"';
										$result = $this->SQL->execSQL($q);
										if ($result->err)
											exit();
									}
								}*/
								if (!isset($this->modulgrpData[$Mid][$gk])) {
									if (isset($_POST['sbmt'])) {
										$q = array('owner_id' => $Mid, 'ugroup_id' => $gk, 'name' => $gr['name'],'access'=>$MODUL->default_access);//
										$q = 'INSERT INTO `' . $this->childs['modulgrp']->tablename . '` (`' . implode('`,`', array_keys($q)) . '`) VALUES (\'' . implode('\',\'', $q) . '\')';
										$result = $this->SQL->execSQL($q);
										if ($result->err)
											exit();
									} else
										$MESS[] = array('alert', 'Будет добавлена группа '.$gr['name'].'['.$gk.']');
								}
							}
					}
				} else {
					if (isset($_POST['sbmt'])) {
						$MESS[] = array('alert', 'Ошибка при инициализации модуля `' . $Mid . '`[' . $path . ']. Модуль отключен.');
						$this->fld_data['active'] = 0;
						$this->id = $Mid;
						$this->_update($this->fld_data, false, false);
					} else
						$MESS[] = array('error', 'Ошибка при инициализации модуля `' . $Mid . '`[' . $path . ']. Модуль будет отключен.');
				}

			} else {
				if (isset($_POST['sbmt'])) {
					$MESS[] = array('alert', 'Фаилы модуля `' . $Mid . '`[' . $path . '] отсутствуют и этот модуль удален из базы данных.');
					$this->id = $Mid;
					$this->_delete();
				} else 
					$MESS[] = array('error', 'Фаилы модуля `' . $Mid . '`[' . $path . '] отсутствуют и этот модуль будет удален из базы данных.');

			}
		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			if (isset($_POST['sbmt'])) {
				$MESS[] = array('alert', 'Ошибка при инициализации модуля `' . $Mid . '`[' . $path . ']. Модуль отключен.');
				$this->fld_data['active'] = 0;
				$this->id = $Mid;
				$this->_update($this->fld_data,false, false);
			} else
				$MESS[] = array('error', 'Ошибка при инициализации модуля `' . $Mid . '`[' . $path . ']. Модуль будет отключен.');
		}

		return array($flag, $MESS);
	}

	function modulgrpDump() {
		if (!isset($this->guserData)) {
			$this->guserData = array();
			_new_class('ugroup', $UGROUP);
			$result = $this->SQL->execSQL('SELECT id,name,level FROM ' . $UGROUP->tablename . ' WHERE level>0'); //админов не учитываем
			if ($result->err) {
				exit();
			}
			while ($row = $result->fetch_array()) {
				//if($row['level']==5) $row['id'] = 0;
				$this->guserData[$row['id']] = $row;
			}
		}
		if (!isset($this->modulgrpData)) {
			$this->modulgrpData = array();
			$result = $this->SQL->execSQL('SELECT * FROM ' . $this->childs['modulgrp']->tablename);
			if ($result->err){
				exit();
			}
			while ($row = $result->fetch_array())
				$this->modulgrpData[$row['owner_id']][$row['ugroup_id']] = $row;
		}
		return true;
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
		$this->default_access = '|0|';
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
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		//$this->fields_form['name'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Группа');
		$this->fields_form['owner_id'] = array('type' => 'hidden', 'readonly' => 1);
		$this->fields_form['ugroup_id'] = array('type' => 'list', 'readonly' => 1, 'listname' => array('class' => 'ugroup'), 'caption' => 'Группа');
		$this->fields_form['mname'] = array('type' => 'text', 'caption' => 'СпецНазвание модуля');
		$this->fields_form['access'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'access', 'caption' => 'Права доступа');

	}
}

