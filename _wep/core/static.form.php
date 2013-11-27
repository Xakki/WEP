<?php

class static_form
{

	static function isSubmited($param)
	{
		// Флаг - запускает процесс сохранения или добавления записи
		$submitFlag = 0;
		if (count($_POST) and (isset($_POST['sbmt']) or isset($_POST['sbmt_save'])))
			$submitFlag = $param['setAutoSubmit'] = 1;
		elseif (isset($param['setAutoSubmit']) and $param['setAutoSubmit'])
			$submitFlag = $param['setAutoSubmit'] = 2;
		return $submitFlag;
	}

	/*------------- ADD ADD ADD ADD ADD ------------------*/

	// in:  id			opt
	//		fld_data:assoc array <fieldname>=><value> 	req
	//		att_data:assoc array <fieldname>=>array 	req
	//		mmo_data:assoc array <fieldname>=>text	req
	// out: 0 - success,
	//      otherwise errorcode

	static function _add(&$_this, $flag_select = true, $flag_update = false)
	{
		if (!isset($_this->fld_data) && !count($_this->fld_data)) {
			return static_main::log('error', static_main::m('add_empty'));
		}

		// add ordind field
		if ($_this->mf_ordctrl and (!isset($_this->fld_data[$_this->mf_ordctrl]) or $_this->fld_data[$_this->mf_ordctrl] == 0)) {
			if ($ordind = $_this->_get_new_ord())
				$_this->fld_data[$_this->mf_ordctrl] = $ordind;
		}
		// add parent_id field
		if ($_this->mf_istree and $_this->parent_id and !$_this->fld_data[$_this->mf_istree]) {
			$_this->fld_data[$_this->mf_istree] = $_this->parent_id;

		}

		// fix tree root
		if ($_this->mf_istree_root && $_this->parent_id) {
			$_this->fld_data[$_this->ns_config['root']] = $_this->tree_data[$_this->parent_id][$_this->ns_config['root']];
		}

		// add owner_id field
		if ($_this->owner and $_this->owner->id and (!isset($_this->fld_data[$_this->owner_name]) or !$_this->fld_data[$_this->owner_name]))
			$_this->fld_data[$_this->owner_name] = $_this->owner->id;


		if (!self::_add_fields($_this, $flag_update)) {
			return static_main::log('error', static_main::m('add_error_add_fields'));
		}

		// FIX tree root
		if ($_this->mf_istree_root && !$_this->parent_id) {
			$where = $_this->_id_as_string();
			$_this->fld_data = array($_this->ns_config['root'] => $_this->id);
			self::_update_fields($_this, $where);
		}

		//umask($_this->_CFG['wep']['chmod']);
		if (isset($_this->att_data) && count($_this->att_data)) {
			if (!self::_add_attaches($_this)) {
				$_this->_delete();
				$_this->id = NULL;
				return static_main::log('error', static_main::m('add_error_att_data'));
			}
		}
		if (isset($_this->mmo_data) && count($_this->mmo_data)) {
			if (!self::_add_memos($_this)) {
				$_this->_delete();
				$_this->id = NULL;
				return static_main::log('error', static_main::m('add_error_mmo_data'));
			}
		}
		if ($_this->id and $flag_select)
			$_this->data = $_this->_select('', true);
		if (isset($_this->mf_indexing) && $_this->mf_indexing) $_this->indexing();
		static_main::log('ok', static_main::m('add', array($_this->tablename), $_this));
		return true;
	}

	/**
	 *
	 * flag_update - Если необходимо обновить существующее поле - true
	 */
	static function _add_fields(&$_this, $flag_update = false)
	{
		if (!count($_this->fld_data)) return false;
		// inserting
		$keyData = array();
		foreach ($_this->fld_data as $key => &$value) {
			if (!isset($_this->fields[$key]['noquote'])) {
				// массив
				if (is_array($value))
					$value = '\'' . $_this->SqlEsc(preg_replace('/\|+/', '|', '|' . implode('|', $value) . '|')) . '\'';
				// логическое
				elseif (self::isTypeBool($_this->fields[$key]['type']))
					$value = (int)(bool)$value; // целое
				elseif (self::isTypeInt($_this->fields[$key]['type']))
					$value = str2int($value); // с запятой
				elseif (self::isTypeFloat($_this->fields[$key]['type']))
					$value = floatval($value); // Шифрованное поле
				elseif (isset($_this->fields[$key]['secure']))
					$value = '\'' . $_this->SqlEsc(static_main::EnDecryptString($value)) . '\'';
				else
					$value = '\'' . $_this->SqlEsc($value) . '\'';
			}
			if ($flag_update) {
				if (!isset($_this->fields[$key]['noquote']))
					$keyData[$key] = '`' . $key . '` = VALUES(`' . $key . '`)';
				else
					$keyData[$key] = '`' . $key . '` = ' . $value;
			}
		}
		if ($_this->mf_timecr)
			$_this->fld_data['mf_timecr'] = $_this->_CFG['time'];
		if ($_this->mf_timeup)
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if ($_this->mf_ipcreate) {
			$_this->fld_data['mf_ipcreate'] = 'inet_aton("' . $_SERVER['REMOTE_ADDR'] . '")';
			//$_this->fld_data['mf_ipcreate'] = sprintf("%u",ip2long($_SERVER['REMOTE_ADDR']));
			if (!$_SERVER['REMOTE_ADDR'])
				trigger_error('ERROR REMOTE_ADDR `' . $_SERVER['REMOTE_ADDR'] . '`. ', E_USER_WARNING);
		}
		if ($_this->mf_createrid and isset($_SESSION['user']['id']) and (!isset($_this->fld_data[$_this->mf_createrid]) or $_this->fld_data[$_this->mf_createrid] == ''))
			$_this->fld_data[$_this->mf_createrid] = $_SESSION['user']['id'];

		$q = 'INSERT INTO `' . $_this->tablename . '` (`' . implode('`,`', array_keys($_this->fld_data)) . '`) VALUES (' . implode(',', $_this->fld_data) . ')';
		if ($flag_update) { // параметр передается в ф. _addUp() - обновление данных если найдена конфликтная запись
			$q .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $keyData);
		}

		$result = $_this->SQL->execSQL($q);

		if ($result->err) return false;
		// get last id if not used nick
		if (!$_this->mf_use_charid && !isset($_this->fld_data['id']))
			$_this->id = (int)$result->lastId();
		elseif ($_this->fld_data['id'])
			$_this->id = $_this->fld_data['id'];
		else $_this->id = NULL;

		return true;
	}

	static function getFileExtension(&$value)
	{
		if (isset($value['ext']) and $value['ext'])
			return $value['ext'];
		else
			return $value['ext'] = strtolower(array_pop(explode('.', $value['name'])));
	}

	static function _add_attaches(&$_this)
	{
		if (!count($_this->attaches) or !count($_this->att_data)) return true;
		$result = $_this->SQL->execSQL('SELECT id, ' . implode(',', array_keys($_this->attaches)) . ' FROM `' . $_this->tablename . '` WHERE id IN (' . $_this->_id_as_string() . ')');
		if ($result->err) return false;
		while ($row = $result->fetch()) {
			$prop = array();
			foreach ($_this->att_data as $key => $value) {
				// Пропускаем если нету данных ("вероятно" фаил не загружали или не меняли)
				if (!is_array($value) or $value['tmp_name'] == 'none' or $value['tmp_name'] == '') continue;

				// старый фаил, для удаления, может имет другое расширение
				$oldname = $_this->getLocalAttaches($key, $row['id'], $row[$key]);
				if ($row[$key] and file_exists($oldname)) {
					_chmod($oldname);
					unlink($oldname);
					if (count($_this->attaches[$key]['thumb'])) {
						foreach ($_this->attaches[$key]['thumb'] as $imod) {
							$oldname = $_this->getLocalThumb($imod, $key, $row['id'], $row[$key]);
							if (file_exists($oldname))
								unlink($oldname);
						}
					}
				}

				// Удаление фаила
				if ($value['tmp_name'] == ':delete:') {
					$prop[] = '`' . $key . '` = \'\'';
					continue; // дело сделали
				}

				$ext = self::getFileExtension($value);

				$newname = $_this->getLocalAttaches($key, $row['id'], $ext);

				if (file_exists($newname)) { // Удаляем старое
					_chmod($newname);
					unlink($newname);
				}
				else {
					static_tools::_checkdir(dirname($newname));
				}


				_chmod($value['tmp_name']);
				if (!rename($value['tmp_name'], $newname))
					return false;

				// Дополнительные изображения
				if (isset($_this->attaches[$key]['thumb'])) {
					if (isset($value['att_type']) and $value['att_type'] != 'img') // если это не рисунок, то thumb не приминим
						return static_main::log('error', static_main::m('File is not image', array($newname)));

					if (count($_this->attaches[$key]['thumb']))
						foreach ($_this->attaches[$key]['thumb'] as $imod) {
							$newThumb = $_this->getLocalThumb($imod, $key, $row['id'], $ext);
							self::imageThumbCreator($newname, $newThumb, $imod);
						}
				}
				$prop[] = '`' . $key . '` = \'' . $ext . '\'';
			}
			if (count($prop)) {
				$result2 = $_this->SQL->execSQL('UPDATE `' . $_this->tablename . '` SET ' . implode(',', $prop) . ' WHERE id = ' . $row['id'] . '');
				if ($result2->err) return false;
			}
		}
		return true;
	}

	static function imageThumbCreator($source, $thumb, $imod)
	{
		$res = true;
		if ($imod['type'] == 'crop')
			$res = static_image::_cropImage($source, $thumb, $imod['w'], $imod['h']);
		elseif ($imod['type'] == 'resize')
			$res = static_image::_resizeImage($source, $thumb, $imod['w'], $imod['h']); // TODO - IMAGE OPTION normalize
		elseif ($imod['type'] == 'resizecrop' or $imod['type'] == 'thumb' or $imod['type'] == 'resize')
			$res = static_image::_thumbnailImage($source, $thumb, $imod['w'], $imod['h']);
		elseif ($imod['type'] == 'watermark')
			$res = static_image::_waterMark($source, $thumb, $imod['logo'], $imod['x'], $imod['y']);
		elseif ($source != $thumb)
			$res = copy($source, $thumb);

		if ($res)
			_chmod($thumb);
		else
			trigger_error('Error chmod: for file ' . $thumb, E_USER_WARNING);

		return $res;
	}

	// depricated
	static function _add_memos(&$_this)
	{
		if (!count($_this->memos) or !count($_this->mmo_data)) return true;
		foreach ($_this->mmo_data as $key => $value) {
			$pathimg = SITE . $_this->getPathForMemo($key);
			if (!isset($_this->memos[$key]))
				return static_main::log('error', static_main::m('Error add memo', array($key, $_this->caption)));
			$name = $pathimg . '/' . $_this->id . $_this->text_ext;
			$f = fopen($name, 'w');
			if (!$f)
				return static_main::log('error', 'Can`t create file ' . $name);
			if (fwrite($f, $value) == -1)
				return static_main::log('error', 'Can`t write data into file ' . $name);
			if (!fclose($f))
				return static_main::log('error', 'Can`t close file ' . $name);
			global $_CFG;
			_chmod($name);
			static_main::log('notice', 'File ' . $name . ' writed.');
		}
		return true;
	}


	/*------------- UPDATE UPDATE UPDATE -----------------*/

	// in:  id											req
	//		fld_data:assoc array <fieldname>=><value> 	req
	//		att_data:assoc array <fieldname>=>array 	req
	//		mmo_data:assoc array <fieldname>=>text		req
	// out: 0 - success,
	//      otherwise errorcode

	static function _update(&$_this, $flag_select = true)
	{

		if ($_this->mf_istree and isset($_this->fld_data[$_this->mf_istree])) {
			if (is_array($_this->id) and isset($_this->id[$_this->fld_data[$_this->mf_istree]])) {
				unset($_this->id[$_this->fld_data[$_this->mf_istree]]);
				static_main::log('error', 'Child `' . $_this->caption . '` can`t be owner to self ');
			}
			if (!is_array($_this->id) and $_this->fld_data[$_this->mf_istree] == $_this->id) {
				static_main::log('error', 'Child `' . $_this->caption . '` can`t be owner to self ');
				return false;
			}
		}

		$where = $_this->_id_as_string();
		if (!$where) {
			trigger_error('Error update: miss id', E_USER_WARNING);
			return false;
		}
		$where = 'id IN (' . $where . ')';

		if ($_this->mf_timeup)
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if ($_this->mf_timeoff and !isset($_this->fld_data['mf_timeoff']) and isset($_this->fld_data[$_this->mf_actctrl]) and !$_this->fld_data[$_this->mf_actctrl] and $_this->data[$_this->id][$_this->mf_actctrl])
			$_this->fld_data['mf_timeoff'] = $_this->_CFG['time'];
		if ($_this->mf_ipcreate) {
			unset($_this->fld_data['mf_ipcreate']);
		}
		if ($_this->mf_istree_root) {
			if ($_this->fld_data[$_this->mf_istree]) {
				$_this->fld_data[$_this->ns_config['root']] = $_this->tree_data[$_this->fld_data[$_this->mf_istree]][$_this->ns_config['root']];
			}
			else {
				$_this->fld_data[$_this->ns_config['root']] = $_this->id;
			}
		}

		// rename attaches & memos
		if (!is_array($_this->id) and isset($_this->fld_data['id']) && $_this->fld_data['id'] != $_this->id && $_this->id) {
			if (!self::_rename_parent_childs($_this)) return false;
			if (!self::_rename_childs($_this)) return false;
			if (!self::_rename_attaches($_this)) return false;
			if (!self::_rename_memos($_this)) return false;
		}

		if (!self::_update_fields($_this, $where)) return false;

		if (isset($_this->fld_data['id']))
			$_this->id = $_this->fld_data['id'];
		//umask($_this->_CFG['wep']['chmod']);
		if (!self::_update_attaches($_this, $where)) return false;
		if (!self::_update_memos($_this, $where)) return false;
		if ($_this->id and $flag_select)
			$_this->data = $_this->_select('', true);
		if (isset($_this->mf_indexing) && $_this->mf_indexing) $_this->indexing();
		static_main::log('ok', static_main::m('update', array($_this->tablename), $_this));
		return true;

	}


	static function _update_fields(&$_this, $where)
	{
		if (!count($_this->fld_data)) return true;

		// preparing
		$data = array();
		foreach ($_this->fld_data as $key => $value) {
			if (!isset($_this->fields[$key]['noquote'])) {
				if (is_array($value)) {
					$value = '\'' . $_this->SqlEsc(preg_replace('/\|+/', '|', '|' . implode('|', $value) . '|')) . '\'';
				}
				elseif ($_this->fields[$key]['type'] == 'bool')
					$value = (int)(bool)$value;
				elseif (strpos($_this->fields[$key]['type'], 'int') !== false)
					$value = str2int($value);
				elseif (strpos($_this->fields[$key]['type'], 'float') !== false)
					$value = floatval($value);
				elseif (isset($_this->fields[$key]['secure']))
					$value = '\'' . $_this->SqlEsc(static_main::EnDecryptString($value)) . '\'';
				else
					$value = '\'' . $_this->SqlEsc($value) . '\'';
			}

			$data[$key] = '`' . $key . '` = ' . $value;
		}

		$q = 'UPDATE `' . $_this->tablename . '` SET ' . implode(',', $data) . ' WHERE ' . $where;
		$result = $_this->SQL->execSQL($q);
		if ($result->err) return false;

		if (isset($_this->fld_data[$_this->owner_name]) and !is_array($_this->id))
			$_this->owner_id = $_this->fld_data[$_this->owner_name];
		if (isset($_this->fld_data[$_this->mf_istree]) and !is_array($_this->id))
			$_this->parent_id = $_this->fld_data[$_this->mf_istree];

		return true;
	}

	static function _rename_childs(&$_this)
	{
		if (!count($_this->childs)) return true;
		foreach ($_this->childs as $ch => $child) {
			$result = $_this->SQL->execSQL('UPDATE `' . $_this->childs[$ch]->tablename . '` SET ' . $_this->childs[$ch]->owner_name . ' = \'' . $_this->fld_data['id'] . '\' WHERE ' . $_this->childs[$ch]->owner_name . ' =\'' . $_this->id . '\'');
			if ($result->err) return false;
		}
		return true;
	}

	static function _rename_parent_childs(&$_this)
	{
		if (!$_this->mf_istree) return true;
		$result = $_this->SQL->execSQL('UPDATE `' . $_this->tablename . '` SET `parent_id` = \'' . $_this->fld_data['id'] . '\' WHERE parent_id =\'' . $_this->id . '\'');
		if ($result->err) return false;
		return true;
	}

	static function _rename_attaches(&$_this)
	{
		if (!count($_this->attaches)) return true;
		$result = $_this->SQL->execSQL('SELECT `id`, `' . implode('`,`', array_keys($_this->attaches)) . '` FROM `' . $_this->tablename . '` WHERE `id` IN (' . $_this->_id_as_string() . ')');
		if ($result->err) return false;
		$row = $result->fetch();
		if ($row) {
			foreach ($_this->attaches as $key => $value) {
				$pathimg = SITE . $_this->getPathForAtt($key);
				$f = $pathimg . $row['id'] . '.' . $value['exts'][$row[$key]];
				if (file_exists($f))
					rename($f, $pathimg . $_this->fld_data['id'] . '.' . $value['exts'][$row[$key]]);
			}
		}
		return true;
	}

	static function _update_attaches(&$_this)
	{
		return self::_add_attaches($_this);
	}

	static function _rename_memos(&$_this)
	{
		if (!count($_this->memos)) return true;
		foreach ($_this->memos as $key => $value) {
			$pathimg = SITE . $_this->getPathForMemo($key);
			$f = $pathimg . '/' . $_this->id . $_this->text_ext;
			if (file_exists($f)) rename($f, $pathimg . '/' . $_this->fld_data['id'] . $_this->text_ext);
		}
		return true;
	}

	static function _update_memos(&$_this)
	{
		return self::_add_memos($_this);
	}


	/*------------- DELETE DELETE DELETE -----------------*/

	/**
	 * Удаление данных
	 * this->id
	 * @return bool - результат операции
	 */
	public static function _delete(&$_this, $id)
	{
		if (!is_array($id)) $id = array($id);
		if (!count($id)) return false;

		// delete childs of tree
		if ($_this->mf_istree) {
			$id = self::_delete_parented($_this, $id);
		}

		// delete childs of owner
		if (count($_this->childs)) {
			foreach ($_this->childs as &$child) {
				if (!self::_delete_ownered($child, $id)) return false;
			}
			unset($child);
		}

		if (!self::_delete_attaches($_this, $id)) return false;
		if (!self::_delete_memos($_this, $id)) return false;
		if (!self::_delete_fields($_this, $id)) return false;

		//if ($_this->mf_indexing) $_this->deindexing($id);
		return true;
	}

	/**
	 * Удаление дочерних данных из БД
	 * Вспомогательная функция
	 */
	private static function _delete_ownered(&$child, array $id)
	{
		// select record ids to delete
		$result = $child->SQL->execSQL('SELECT id FROM `' . $child->tablename . '` WHERE `' . $child->owner_name . '` IN (' . $child->_as_string($id) . ')');
		if ($result->err) return false;
		// create list
		$idChild = array();
		while (list($k) = $result->fetch_row())
			$idChild[] = $k;
		// if list not empty
		if (count($idChild)) self::_delete($child, $idChild);
		return true;
	}

	/**
	 * Удаление всех родителей даных
	 * Вспомогательная функция
	 */
	private static function _delete_parented(&$_this, array $id)
	{
		// select record ids to delete
		$data = $_this->_select_id_tree($id);

		if (count($data))
			$id = array_merge($id, $data);
		return $id;
	}

	/**
	 * Удаление данных из БД
	 * Вспомогательная функция
	 */
	private static function _delete_fields(&$_this, array $id)
	{
		// delete records
		$result = $_this->SQL->execSQL('DELETE FROM `' . $_this->tablename . '` WHERE `id` IN (' . $_this->_as_string($id) . ')');
		if ($result->err) return false;
		return true;
	}

	/**
	 * Удаление фаилов
	 * Вспомогательная функция
	 */
	private static function _delete_attaches(&$_this, array $id)
	{
		if (!count($_this->attaches)) return true;
		$result = $_this->SQL->execSQL('SELECT `id`, `' . implode('`,`', array_keys($_this->attaches)) . '` FROM `' . $_this->tablename . '` WHERE `id` IN (' . $_this->_as_string($id) . ')');
		if ($result->err) return false;

		while ($row = $result->fetch()) {
			foreach ($_this->attaches as $key => $att) {
				$oldname = $_this->getLocalAttaches($key, $row['id'], $row[$key]);
				if ($row[$key]) {
					if (file_exists($oldname)) {
						_chmod($oldname);
						if (!unlink($oldname))
							return static_main::log('error', 'Cannot delete file `' . $oldname . '`');
					}
					if (count($att['thumb']))
						foreach ($att['thumb'] as $imod) {
							$oldname = $_this->getLocalThumb($imod, $key, $row['id'], $row[$key]);
							if (file_exists($oldname))
								if (!unlink($oldname))
									return static_main::log('error', 'Cannot delete file `' . $oldname . '`');
						}

				}
			}
		}
		return true;
	}

	/**
	 * Удаление memo фаилов
	 * Вспомогательная функция
	 */
	private static function _delete_memos(&$_this, array $id)
	{
		if (!count($_this->memos)) return true;
		foreach ($id as $k) {
			foreach ($_this->memos as $key => $value) {
				$pathimg = $_this->getPathForMemo($key);
				$f = $pathimg . '/' . $k . $_this->text_ext;
				if (file_exists($f))
					if (!unlink($f)) return $_this->_error('Cannot delete memo `' . $f . '`', 1);
			}
		}
		return true;
	}

	static function getEvalForm(&$_this, $ff)
	{
		$eval = '';
		if (isset($ff['mask']['eval']))
			$eval = $ff['mask']['eval'];
		elseif (isset($ff['mask']['evala']) and !$_this->id)
			$eval = $ff['mask']['evala'];
		elseif (isset($ff['mask']['evalu']) and $_this->id)
			$eval = $ff['mask']['evalu'];
		return $eval;
	}

	/**
	 * Корректировака и обработка формы для вывода формы
	 * @param mixed $fields - название списока или массив данных для списка
	 * @return array
	 */
	static function kFields2FormFields(&$_this, &$fields)
	{
		foreach ($fields as $k => &$r) {
			if (!is_array($r)) continue;
			if (!isset($r['readonly']))
				$r['readonly'] = false;
			if (($r['readonly'] and !$_this->id) or
				(isset($r['mask']['fview']) and $r['mask']['fview'] == 2) or
				(isset($r['mask']['usercheck']) and !static_main::_prmGroupCheck($r['mask']['usercheck']))
			) {
				unset($fields[$k]);
				continue;
			}
			if (isset($r['type']) and $r['type'] != 'info') {
				if (!isset($r['value']) and isset($r['default']) and !isset($_POST[$k])) { // and !$_this->id
					$r['value'] = $r['default'];
					if (isset($r['default_2']))
						$r['value_2'] = $r['default_2'];
				}
				if (isset($_POST[$k . '_2']))
					$r['value_2'] = $_POST[$k . '_2'];

				if ($r['type'] == 'file') {
					// Процесс загрузки фаила
					if (isset($r['value']) and is_array($r['value']) and isset($r['value']['tmp_name']) and $r['value']['tmp_name']) {
						// TODO -  ?
						$r['value'] = $_CFG['PATH']['temp'] . $r['value']['name'];
					} // Редактирование формы - отображаем фаил
					elseif (isset($r['ext']) and $_this->id and !$r['value']) {
						exit('++++++!!'); // TODO
						$r['value'] = $_this->getAttaches($k, $_this->id, '');
					}


					if (isset($r['value']) and $r['value'] and file_exists(SITE . $r['value'])) {
						$_is_image = static_image::_is_image(SITE . $r['value']); // Проверяем , является ли фаил изображением
						if ($_is_image) { // Если это изображение
							$r['att_type'] = 'img'; // Маркер для рисования формы
							$r['img_size'] = getimagesize(SITE . $r['value']);
							$r['value'] = $_this->_getPathSize($r['value']);

							if (count($_this->attaches[$k]['thumb'])) {
								foreach ($_this->attaches[$k]['thumb'] as $modkey => $mr) {
									if (!$_this->data[$_this->id]['_ext_' . $k]) {
										continue;
									}

									if (isset($mr['display']) and !$mr['display']) {
										unset($r['thumb'][$modkey]);
										continue;
									}
									if (!isset($mr['pref'])) $mr['pref'] = '';
									if (!isset($mr['path'])) $mr['path'] = '';
									if ((!$mr['pref'] and !$mr['path']) or (!$mr['pref'] and $mr['path'] == $_this->attaches[$key]['path'])) {
										unset($r['thumb'][$modkey]);
										continue;
									}
									$_file = $_this->getThumb($mr, $k, $_this->id, $_this->data[$_this->id]['_ext_' . $k]);

//									if(file_exists(SITE.$_file))
//									{
									$mr['value'] = $_this->_getPathSize($_file);
//										$mr['filesize'] = filesize(SITE.$_file);
									$r['thumb'][$modkey] = $mr;
//									}
								}
							}
						}
						elseif (isset($_this->_CFG['form']['flashFormat'][$r['ext']]) and $_this->id) {
							$r['att_type'] = 'swf'; // Флешки
						}
						else {
							$r['value'] = '';
							// TODO : можно описать ещё какиенибудь специфические типы
						}
					}
					else {
						$r['value'] = '';
					}

					if (!isset($r['value']) or !is_string($r['value']) or !$r['value'])
						$r['value'] = '';

					if (!isset($r['comment']))
						$r['comment'] = static_main::m('_file_size') . $_this->attaches[$k]['maxsize'] . 'Kb';
				}
				elseif ($r['type'] == 'ajaxlist') {
					if (!isset($r['placeholder']) or !$r['placeholder'])
						$r['placeholder'] = 'Введите текст';
					if (isset($r['mask']['min']) and $r['mask']['min'] and (!isset($r['value']) or $r['value'] < $r['mask']['min']))
						$r['value_2'] = '';

					if ((!isset($r['value_2']) or !$r['value_2']) and isset($r['value']) and $r['value']) {
						if (isset($r['multiple'])) {
							$r['value'] = explode('|', trim($r['value'], '|'));
						}
						$md = $_this->_getCashedList($r['listname'], $r['value']);
						if (isset($r['multiple'])) {
							foreach ($r['value'] as $kv => $rv)
								$r['value_2'][$kv] = (isset($md[$rv]) ? $md[$rv] : '');
						}
						else $r['value_2'] = $md[$r['value']];
					}

				} /*elseif(isset($r['listname']) and isset($r['multiple']) and $r['multiple']===FORM_MULTIPLE_JQUERY and !$r['readonly']) {// and isset($_this->fields[$k])
					$_this->_checkList($r['listname'],$r['value']);
					$templistname = $r['listname'];
					if(is_array($r['listname']))
						$templistname = implode(',',$r['listname']);
					$templistname =	$_this->_cl.'_'.$templistname;
					$arrlist = &$_this->_CFG['enum_check'][$templistname];

					if($arrlist and is_array($arrlist)) {
						if(is_array($r['value']))
							$r['value'] = array_combine($r['value'],$r['value']);
						else
							$r['value'] = array($r['value']=>$r['value']);
						$temparr= array();
						foreach($r['value'] as $kk) {
							if(isset($arrlist[$kk])) {
								$temparr[$kk] = $arrlist[$kk];
								unset($_this->_CFG['enum_check'][$templistname][$kk]);
							}
						}
						$md = $temparr+$_this->_CFG['enum_check'][$templistname];
						if(is_array($md) and count($md)) {
							$md = array($md);
							$r['valuelist'] = $_this->_forlist($md,0,$r['value']);
						}
					}
				}*/
				/*elseif(isset($r['listname']) and isset($r['multiple']) and $r['multiple'] and !$r['readonly']) {
					$md = $_this->_getCashedList($r['listname']);

					if(!isset($r['value']))
						$r['value'] = array();
					elseif(!is_array($r['value']))
						$r['value'] = array($r['value']=>$r['value']);
					elseif($r['multiple']!=3 and is_array($r['value']) and count($r['value']))
						$r['value'] = array_combine($r['value'],$r['value']);

					if(is_array($md) and count($md)) {
						$temp = current($md);
						if(is_array($temp) and !isset($temp['#name#'])) {
							if(isset($r['mask']['begin']))
								$key = $r['mask']['begin'];//стартовый ID массива
							else
								$key = key($md);
						} else{
							$md = array($md);
							$key = 0;
						}
						$r['valuelist'] = $_this->_forlist($md, $key, $r['value'], $r['multiple']);
					}
				}*/
				elseif (isset($r['listname'])) {
					if (!$r['readonly']) {
						if (is_array($r['listname']) and !isset($r['listname']['idThis'])) {
							$r['listname']['idThis'] = $k;
						}
						if (!isset($r['multiple']))
							$r['multiple'] = 0;

						$md = $_this->_getCashedList($r['listname']);

						if (!isset($r['value']))
							$r['value'] = array();
						elseif (!is_array($r['value']))
							$r['value'] = array($r['value'] => $r['value']);
						elseif (is_array($r['value']) and count($r['value']) and $r['multiple'] != FORM_MULTIPLE_KEY)
							$r['value'] = array_combine($r['value'], $r['value']);

						if (is_array($md) and count($md)) {
							$temp = current($md);
							if (is_array($temp) and !isset($temp['#name#'])) {
								if (isset($r['mask']['begin']))
									$key = $r['mask']['begin']; //стартовый ID массива
								else
									$key = key($md);
							}
							else {
								$md = array($md);
								$key = 0;
							}
							list($r['valuelist'], $r['sel']) = $_this->_forlist($md, $key, $r['value'], $r['multiple']);
						}
					}
					else // Форма списка  только для чтения, выводится в виде текста
					{
						if (is_array($r['listname']) and isset($r['listname']['idThis']) and $_this->id) {
							$r['value'] = $_this->data[$_this->id][$r['listname']['idThis']];
						}
						if (isset($r['value']) and $r['value'] != '') {
							$selectedData = $_this->_getCashedList($r['listname'], $r['value']);
							// переводим полученный массив в строку
							if (is_array($selectedData)) {
								$temp = current($selectedData);
								if (isset($temp['#name#'])) {
									$r['value'] = array();
									foreach ($selectedData as $listname)
										$r['value'][] = $listname['#name#'];
									$r['value'] = implode(',', $r['value']);
								}
								else
									$r['value'] = implode(',', $selectedData);
							}
							else
								$r['value'] = $selectedData;
						}
					}
				}
				elseif ($r['type'] == 'ckedit') {
					if (!isset($r['paramedit']))
						$r['paramedit'] = array();
				}
				elseif ($k == 'mf_ipcreate') {
					$r['value'] = long2ip($r['value']);
				}

				// Преобразуем теги, чтобы их не съел шаблонизатор
				if (isset($r['value']) and $r['value'] and is_string($r['value']) and ($r['type'] == 'ckedit' or $r['type'] == 'text' or $r['type'] == 'textarea') and strpos($r['value'], '{#') !== false) {
					$r['value'] = str_replace(array('{#', '#}'), array('(#', '#)'), $r['value']);
					// TODO : возможна дыра в безопастности, решить срочно
				}

				if (isset($r['mask']['name'])) {
					if ($r['mask']['name'] == 'phone2')
						$r['comment'] .= static_main::m('_comment_phone2', $_this);
					//<br/>Допускается цифры, тире, пробел, запятые и скобки
					elseif ($r['mask']['name'] == 'phone')
						$r['comment'] .= static_main::m('_comment_phone', $_this);
					//Допускается цифры, тире, пробел и скобки
					//elseif($r['mask']['name']=='phone3')
					//	$r['comment'] = "";
					//Допускается цифры, тире, пробел, запятые и скобки
				}

				if (!isset($r['maxlength']) and isset($r['mask']['max']) && $r['mask']['max'] > 0) {
					if (static_form::isTypeNumber($r['type'])) {
						$r['maxlength'] = _strlen($r['mask']['max']);
					}
					else
						$r['maxlength'] = $r['mask']['max'];

				}

				if (isset($r['multiple']) && $r['multiple'] == FORM_MULTIPLE_KEY && isset($r['keyListName'])) {
					list($r['keyValueList'], $r['keysel']) = $_this->_forlist($_this->_getCashedList($r['keyListName']));
				}

			}
			if (isset($r['fields_type'])) {
				if (static_form::isTypeFloat($r['fields_type'])) {
					$r['isFloat'] = true;
				}
				if (static_form::isTypeInt($r['fields_type'])) {
					$r['isInt'] = true;
				}
			}

		}

		unset($r);

		return true;
	}


	/**
	 * Проверка формы
	 * $data - POST lfyyst либо  данные из БД
	 */
	static function _fFormCheck(&$_this, &$data, &$param, &$FORMS_FIELDS)
	{ //$_this->fields_form
		global $_tpl;
		if (!count($FORMS_FIELDS))
			return array('mess' => array(
				static_main::am('error', 'errdata', $_this)
			));
		//$MASK = &$_this->_CFG['_MASK'];
		$arr_nochek = array('info' => 1, 'sbmt' => 1, 'alert' => 1);
		$messages = '';
		$arr_err_name = array();
		$textm = '';
		$mess =
		$vars = array();

		foreach ($FORMS_FIELDS as $key => &$form) {
			$error = array();
			if ($key == '_*features*_') {
				trigger_error('Ошибка. В форме модуля ' . $_this->_cl . ', применяются устаревший _*features*_', E_USER_WARNING);
				continue;
			}
			if (!isset($form['type']))
				return array('mess' => array(
					static_main::am('error', 'errdata', ' : ' . $key, $_this)
				));
			if (isset($arr_nochek[$form['type']])) continue;

			/*Поля которые недоступны пользователю не проверяем, дефолтные значения прописываются в kPreFields()*/
			$eval = self::getEvalForm($_this, $form);
			if ($eval !== '') {
				// **************
			}
			elseif ((isset($form['readonly']) and $form['readonly']) or
				(isset($form['mask']['fview']) and $form['mask']['fview'] == 2) or
				(isset($form['mask']['usercheck']) and !static_main::_prmGroupCheck($form['mask']['usercheck']))
			) {
				continue;
			}

			if ($form['type'] == 'file') {
				self::check_file_field($_this, $form, $error, $data, $key);
			}
			elseif ($form['type'] == 'cf_fields') {
				// TODO : проверка правильности форм
			} /*Капча*/
			elseif ($form['type'] == 'captcha') {
				if (mb_strtolower($data[$key]) !== mb_strtolower($form['captcha']))
					$error[] = 31;
				//strcmp
				/*if($data[$key]!=$form['captcha']) {
					$error[] = 31;
				}*/
			}
			elseif (isset($form['multiple']) and $form['multiple']) {
				if (isset($form['mask']['minarr']) and $form['mask']['minarr'] > 0 and (!isset($data[$key]) or !count($data[$key])))
					$error[] = 1;
				elseif (isset($data[$key])) {
					if (is_array($data[$key])) {
						//if(count($data[$key])) {
						//	$data[$key] = array_filter($data[$key],array('static_form','trimArray'));
						//}
						if (isset($form['mask']['maxarr'])) {
							if (count($data[$key]) > $form['mask']['maxarr'])
								$error[] = 26;
						}

						if (isset($form['mask']['minarr'])) {
							if (count($data[$key]) < $form['mask']['minarr'])
								$error[] = 27;
						}

						if ($form['multiple'] != FORM_MULTIPLE_KEY) {
							$data[$key] = array_combine($data[$key], $data[$key]);
						}

						foreach ($data[$key] as $tk => $tv) {
							self::check_formfield($_this, $form, $error, $data[$key], $tk);
						}
					}
					else {
						$error[] = 51;
					}
				}
			}
			else {
				if (isset($data[$key]) and is_array($data[$key])) {
					$error[] = 5;
				}
				else {
					self::check_formfield($_this, $form, $error, $data, $key);
				}
			}

			foreach ($error as $row) {
				$messages = '';
				if ($row == 2) //max chars
					$messages = static_main::m('_err_2', array($form['mask']['max'], (_strlen($data[$key]) - $form['mask']['max'])), $_this);
				elseif ($row == 21) // min chars
					$messages = static_main::m('_err_21', array($form['mask']['min'], ($form['mask']['min'] - _strlen($data[$key]))), $_this);
				elseif ($row == 22) //max int
					$messages = static_main::m('_err_22', $_this) . $form['mask']['max'];
				elseif ($row == 23) //min int
					$messages = static_main::m('_err_23', $_this) . $form['mask']['min'];
				elseif ($row == 24) // min chars
					$messages = static_main::m('_err_22', $_this) . $form['mask']['max'];
				elseif ($row == 25) // max chars
					$messages = static_main::m('_err_23', $_this) . $form['mask']['min'];
				elseif ($row == 26) // min Array count
					$messages = static_main::m('_err_22', $_this) . $form['mask']['maxarr'];
				elseif ($row == 27) // max Array count
					$messages = static_main::m('_err_23', $_this) . $form['mask']['minarr'];
				elseif ($row == 29) //limit file size
					$messages = static_main::m('_err_29', array($_FILES[$key]['name']), $_this) . $form['maxsize'] . 'Kb';
				elseif ($row == 3) { //wrong data
					if (isset($form['matches_err']) and count($form['matches_err'][0])) {
						$textm = 'Обнаружены следующие недопустимые символы - ';
						foreach ($form['matches_err'][0] as $mk => $mr) {
							if (isset($mr[1]))
								$textm .= $mr[0] . '(поз. ' . $mr[1] . '), ';
							if ($mk > 10) {
								$textm .= 'и т.д., ';
								break;
							}
						}
						$textm .= ' и следующей попыткой удалить их автоматический?<input type="checkbox" value="1" name="' . $key . '_rplf' . '" checked="checked" style="height: 0.8em;">';
						//$FORMS_FIELDS[$key.'_rplf'] = array('type'=>'hidden','value'=>'del');
					}
					$messages = static_main::m('_err_3', array($textm), $_this);
				}
				elseif ($row == 39) //wrong file type
					$messages = static_main::m('_err_39', array($_FILES[$key]['name']), $_this) . '- ' . implode(',', array_unique($form['mime'])) . '.';
				elseif ($row >= 40 and $row < 50) //error load file
					$messages = static_main::m('_err_' . $row, array($_FILES[$key]['name']), $_this);
				elseif ($row == 5)
					$messages = 'Множественные значения не допустимы!';
				elseif ($row == 51)
					$messages = 'Множественные значения не обнаружены!';
				else
					$messages = static_main::m('_err_' . $row, $_this);

				$arr_err_name[$key] = $key;

				if (isset($param['errMess'])) {
					$mess[] = static_main::am('error', $form['caption'] . ': ' . $messages);
				}
				if (isset($param['ajax'])) {
					$_tpl['onload'] .= 'wep.form.putEMF(\'' . $key . '\',\'' . $messages . '\');'; // запись в форму по ссылке
				}
				else {
					if ($form['type'] == 'hidden') {
						$mess[] = static_main::am('error', 'err');
						trigger_error('Ошибка в элементе hidden, формы модуля ' . $_this->_cl . ' : `' . $form['caption'] . '` ' . $messages, E_USER_WARNING);
					}
					elseif (!isset($param['setAutoSubmit']) or $param['setAutoSubmit'] !== 2) // Если это не AutoSubmit
					{
						$form['error'][] = $messages; // запись в форму по ссылке
					}
				}
				//$form['caption'].': '.
			}
			if (isset($data[$key]))
				$vars[$key] = $data[$key];

		}
		unset($form);

		// Проверка уник полей
		if (count($_this->unique_fields)) {
			foreach ($_this->unique_fields as $uk => $ur) {
				//TODO: если массив данных вдруг
				if (is_array($ur) or !isset($FORMS_FIELDS[$ur])) continue;
				$key = $ur;
				$form = & $FORMS_FIELDS[$key];
				$q = 'WHERE ' . $key . '="' . $_this->SqlEsc($data[$key]) . '"';
				if ($_this->id)
					$q .= ' and id!=' . $_this->id;
				$temp = $_this->qs($key, $q);
				if (count($temp)) {
					$arr_err_name[$key] = $key;
					$messages = static_main::m('_err_34', $_this);
					if (isset($param['errMess'])) {
						$mess[] = static_main::am('error', $form['caption'] . ': ' . $messages);
					}
					if (isset($param['ajax'])) {
						$_tpl['onload'] .= 'wep.form.putEMF(\'' . $key . '\',\'' . $messages . '\');'; // запись в форму по ссылке
					}
					elseif (!isset($param['setAutoSubmit']) or $param['setAutoSubmit'] !== 2) // Если это не AutoSubmit
					{
						$form['error'][] = $messages; // запись в форму по ссылке
					}
				}
			}
		}

		if (count($arr_err_name) > 0 and !isset($param['errMess'])) {
			$mess[] = static_main::am('error', 'Поля формы заполнены не верно.');
		}

		/*$_tpl['onload'] .'CKEDITOR.replace( \'editor1\',
						 {
							  toolbar : \'basic\',
							  uiColor : \'# 9AB8F3\'
						 });';*/
		return array('mess' => $mess, 'vars' => $vars);
	}

	/// Проверяет только загрузку фаилов
	static function check_file_field(&$_this, &$form, &$error, &$data, $key)
	{
		//*********** Файлы
		if ($form['type'] == 'file') {
			//TODO: multiple
			if (isset($data[$key . '_del']) and (int)$data[$key . '_del'] == 1) {
				$_FILES[$key] = $data[$key] = array('name' => ':delete:', 'tmp_name' => ':delete:');
			}
			elseif (isset($_FILES[$key]['name']) and $_FILES[$key]['name'] != '') {
				$value = & $_FILES[$key];
				if ($value['error'] != 0) {
					$error[] = (int)'4' . $value['error'];
					return false;
				}
				elseif (isset($form['maxsize']) and $value['size'] > ($form['maxsize'] * 1024)) {
					$error[] = 29;
					return false;
				}
				elseif (!$value['tmp_name']) {
					$error[] = 40;
					return false;
				}
				else {
					$is_image = static_image::_is_image($value['tmp_name']);
					$form['att_type'] = '';
					if ($is_image) {
						$form['att_type'] = 'img';
						if ((!isset($form['toWebImg']) or $form['toWebImg']) and $is_image > 3)
							$value['ext'] = (is_string($form['toWebImg']) ? $form['toWebImg'] : 'jpg');
						else
							$value['ext'] = static_image::_get_ext($is_image, false);
					}
					else {
						if ($value['name'])
							$value['ext'] = strtolower(array_pop(explode('.', (string)$value['name'])));
						if (!$value['ext'] or preg_match('/[^A-Za-z0-9]/', $value['ext'])) { // Кривое расширение фаила
							$error[] = 39;
							return false;
						}
					}
					// Ищем совпадения
					if (isset($form['mime'])) {
						$flag = false;
						if (in_array('image', $form['mime']) and $form['att_type'] == 'img') // Для любых изображений
							$flag = true;
						elseif (in_array($value['ext'], $form['mime']))
							$flag = true;
						elseif ($value['type'] and isset($form['mime'][$value['type']]))
							$flag = true;
					}
					else
						$flag = true;

					if (!$flag) { //Не верный тип фаилы
						$error[] = 39;
						return false;
					}
					else {
						static_tools::_checkdir($_this->_CFG['_PATH']['temp']);
						$temp = $_this->_CFG['_PATH']['temp'] . substr(md5(getmicrotime() . rand(0, 50)), 16) . '.' . $value['ext'];
						if (move_uploaded_file($value['tmp_name'], $temp)) {
							$value['tmp_name'] = $temp;
							$data[$key] = $value;
						}
						else {
							$error[] = 40;
							return false;
						}
					}
				}
			}
			elseif (isset($data[$key . '_temp_upload']) && is_array($data[$key . '_temp_upload']) && $data[$key . '_temp_upload']['name'] && $data[$key . '_temp_upload']['type']) {
				$data[$key] = $data[$key . '_temp_upload'];
				$data[$key]['tmp_name'] = $_this->_CFG['_PATH']['temp'] . $data[$key . '_temp_upload']['name'];
				$_FILES[$key] = $data[$key];
			}
			if (isset($form['mask']['min']) and $form['mask']['min'] and !$_this->data[$_this->id][$key] and (!$_FILES[$key]['name'] or $_FILES[$key]['name'] == ':delete:')) {
				$error[] = 1;
				return false;
			}

		}
		return true;
	}

	static function passwordHash($val, $form)
	{
		global $_CFG;

		if (isset($form['md5']) and !$form['md5'])
			return $val;
		return md5($_CFG['wep']['md5'] . $val);
	}

	/**
	 * Проверяющий форму по отдельному полю
	 *
	 */
	static function check_formfield(&$_this, &$form, &$error, &$data, $key)
	{
		$MASK = & $_this->_CFG['_MASK'];
		if (!isset($form['fields_type']))
			$form['fields_type'] = $form['type'];

		//*********** CHECKBOX
		if ($form['type'] == 'checkbox') {
			$form['value'] = $data[$key] = ((isset($data[$key]) and $data[$key]) ? 1 : 0);
			return true;
		}
		/*пароль*/
		if ($form['type'] == 'password') {
			if (isset($form['mask']['password']) and $form['mask']['password'] == 're') {
				if ($data[$key] or $data['re_' . $key]) {
					if ($data[$key] != $data['re_' . $key])
						$error[] = 32;
					else
						$data[$key] = self::passwordHash($data[$key], $form);
				}
				else
					unset($data[$key]);
			} // Форма подтверждения пароля
			elseif (isset($form['mask']['password']) and $form['mask']['password'] == 'confirm') {
				if (isset($_this->data[$_this->id][$key]) and $data[$key]) {
					if ($_this->data[$_this->id][$key] != self::passwordHash($data[$key], $form))
						$error[] = 322;
					unset($data[$key]);
				}
			} // форма смены пароля
			elseif (isset($form['mask']['password']) and $form['mask']['password'] == 'change') {
				if (isset($_this->data[$_this->id][$key]) and $data[$key] or $data[$key . '_old']) {
					if ($_this->data[$_this->id][$key] != self::passwordHash($data[$key . '_old'], $form))
						$error[] = 321;
					else
						$data[$key] = self::passwordHash($data[$key], $form);
				}
				unset($data[$key]);
			}
			else {
				if (isset($form['mask']['max']) && $form['mask']['max'] > 0 && _strlen($data[$key]) > $form['mask']['max'])
					$error[] = 2;
				if (isset($form['mask']['min']) and $form['mask']['min'] > 0) {
					if (!$data[$key] or $data[$key] == '0')
						$error[] = 1;
					elseif (_strlen($data[$key]) < $form['mask']['min'])
						$error[] = 21;
				}
				$data[$key] = self::passwordHash($data[$key], $form);
			}

			return true;
		}

		if (!isset($data[$key])) {
			if (isset($form['mask']['min']) and $form['mask']['min'])
				$error[] = 1;
			return true;
		}

		/*Если тип данных ДАТА*/
		if ($form['type'] == 'date') {
			$data[$key] = self::_get_fdate($data[$key], $form['mask']['format'], $form['fields_type']);
		} /*Редактор*/
		elseif ($form['type'] == 'ckedit') {
			$data[$key] = stripslashes($data[$key]);
			if (!isset($form['paramedit']['allowBody']) or !$form['paramedit']['allowBody']) {
				// TODO - костыль
				$p1 = strpos($data[$key], '<body>');
				if ($p1 !== false) {
					$data[$key] = substr($data[$key], $p1 + 6);
					$data[$key] = substr($data[$key], 0, strpos($data[$key], '</body>'));
				}
			}
		}

		// Преобразуем теги, чтобы их не съел шаблонизатор
		if (($form['type'] == 'ckedit' or $form['type'] == 'text' or $form['type'] == 'textarea') and strpos($data[$key], '(#') !== false) {
			$data[$key] = str_replace(array('(#', '#)'), array('{#', '#}'), $data[$key]);
		} /*Целое число*/
		elseif ($form['type'] == 'int' and (!isset($form['mask']['toint']) or $form['mask']['toint'])) {
			$data[$key] = str2int($data[$key]);
		} /*Список*/
		elseif (($form['type'] == 'list' or $form['type'] == 'ajaxlist')) {
			if ($data[$key] and $_this->_checkList($form['listname'], $data[$key]) === false) {
				$error[] = 33;
			}

		}

		//keyValueList
		if (isset($form['keytype']) && $form['keytype'] == 'list' and isset($form['keyListName'])) {
			if ($data[$key] and $_this->_checkList($form['keyListName'], $key) === false) {
				$error[] = 35;
			}
		}


		/*Преоразуем HTML сущности*/
		if (isset($form['mask']['entities']) and $form['mask']['entities'] == 1) {
			$data[$key] = htmlspecialchars($data[$key], ENT_QUOTES, CHARSET);
		}

		/*Замена по регулярному выражению*/
		if (isset($form['mask']['replace'])) {
			if (!isset($form['mask']['replaceto']))
				$form['mask']['replaceto'] = '';
			$data[$key] = preg_replace($form['mask']['replace'], $form['mask']['replaceto'], $data[$key]);
		}

		/*Убираем теги*/
		if (isset($form['mask']['striptags'])) {
			if ($form['mask']['striptags'] == 'all')
				$data[$key] = strip_tags($data[$key]);
			elseif ($form['mask']['striptags'] == '')
				$data[$key] = strip_tags($data[$key], $_this->_CFG['_striptag']);
			else
				$data[$key] = strip_tags($data[$key], $form['mask']['striptags']);
		}

		/*Убираем атрибуты у тегов*/
		if (isset($form['mask']['stripAttr'])) {
			// TODO : сделать возможность оставлять некоторые атрибуты
			/*$tmp = '';
			if($form['mask']['stripAttr'] and $tmp = explode(',',$form['mask']['stripAttr']) and count($tmp)) {
				$tmp
			}*/
			$data[$key] = preg_replace("/<([^>\s]+) [^>]+>/u", "<\\1>", $data[$key]);
		}

		/*Проверка правописания*/
		if (isset($form['mask']['spellCheck'])) {
			$data[$key] = self::SpellCheck($data[$key]);
		}

		/*Проверка по регуляркам*/
		$preg_mask = '';
		if (isset($form['mask']['patterns']))
			$preg_mask = $form['mask']['patterns'];
		elseif (isset($form['mask']['name']) and isset($MASK[$form['mask']['name']]))
			$preg_mask = $MASK[$form['mask']['name']];
		elseif (isset($MASK[$form['type']]))
			$preg_mask = $MASK[$form['type']];

		if ($preg_mask AND $data[$key]) {
			$nomatch = '';
			$data[$key] = trim($data[$key]);
			if (is_array($preg_mask)) {
				if (isset($preg_mask['eval'])) {
					$value = $data[$key]; // for eval
					eval('$data[$key] = ' . $preg_mask['eval'] . ';');
				}
				if (isset($preg_mask['match'])) {
					$matches = preg_match_all($preg_mask['match'], $data[$key], $temp);
					if (!$matches) {
						$error[$key . 'mask'] = 3;
					}
				}
				if (isset($preg_mask['nomatch']))
					$nomatch = $preg_mask['nomatch'];
				if (isset($preg_mask['comment']))
					$form['comment'] .= $preg_mask['comment'];
			}
			else
				$nomatch = $preg_mask;

			/*CHECK MASK*/
			if (isset($form['mask']['name']) and ($form['mask']['name'] == 'phone' or $form['mask']['name'] == 'phone2')) {
				if ($data[$key]) {
					$data[$key] = self::_phoneReplace($data[$key]);
					if (!$data[$key]) {
						$error[$key . 'mask'] = 3;
						$textm = 'Не корректный номер телефона.';
					}
				}
			}
			elseif ($nomatch) {
				if (isset($data[$key . '_rplf'])) {
					$data[$key] = preg_replace($nomatch, '', $data[$key]);
					unset($error[$key . 'mask']);
				}
				$matches = preg_match_all($nomatch, $data[$key], $form['matches_err'], PREG_OFFSET_CAPTURE);
				if ($matches) {
					$error[$key . 'mask'] = 3;
				}
				elseif (isset($form['mask']['checkwww']) and $form['mask']['checkwww'] and !fopen('http://' . str_replace('http://', '', $data[$key]), 'r'))
					$error[] = 4;
			}

		}

		if (isset($form['mask']['max']) && $form['mask']['max'] > 0) {
			if (self::isTypeNumber($form['fields_type'])) {
				//для даты
				// TODO ??????
				/*if($form['type']=='date') {
					$form['mask']['max'] = date($form['mask']['format'],$form['mask']['max']);
				}*/
				if (str2int($data[$key]) > $form['mask']['max'])
					$error[] = 22;
			}
			else {
				if (_strlen($data[$key]) > $form['mask']['max'])
					$error[] = 2;
			}
		}
		/*
			if(self::isTypeFloat($form['fields_type']) and strpos($form['mask']['max'], ','))
			{
				$maskFloat = explode(',', $form['mask']['max']);
				$valFloat = explode('.', $data[$key]);
				if(_strlen($valFloat[0])>$maskFloat[0])
					$error[] = 22;
			}
		*/
		if (isset($form['mask']['min']) and $form['mask']['min'] > 0) {
			if (!$data[$key])
				$error[] = 1;
			elseif (self::isTypeNumber($form['fields_type'])) {
				/*if($form['type']=='date') {
					$form['mask']['minint'] = date($form['mask']['format'],$form['mask']['minint']);
				}*/
				if (str2int($data[$key]) < $form['mask']['min'])
					$error[] = 23;
			}
			else {
				if (_strlen($data[$key]) < $form['mask']['min'])
					$error[] = 21;
			}
		}

		$form['value'] = $data[$key];

		return true;
	}

	/**
	 * array_filter функция
	 * вспомогательная функция
	 * @param string $var - значение каждого элемента массива
	 * @return bool
	 */
	static function trimArray($var)
	{
		if ($var == '') return false;
		return true;
	}

	/**
	 * Унификация телефонных номеров
	 * @param string $phone - телефон (разделённые запятой)
	 * @return string - телефон унифицированный
	 */
	static function _phoneReplace($phone)
	{
		$phone_2 = array();
		$phone_1 = preg_replace("/[^0-9\,\;\+]+/", '', $phone);
		$phone_1 = preg_split("/[\,\;\+]+/", $phone_1, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($phone_1 as $k => $p) {
			if (trim($p)) {
				$phone_2[] = preg_replace(array(
					"/^([8])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
					"/^([7])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
					"/^([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
					"/^([0-9]{3})([0-9]{2})([0-9]{2})$/",
				), array(
					'+7 \\2 \\3-\\4-\\5',
					'+7 \\2 \\3-\\4-\\5',
					'+7 \\1 \\2-\\3-\\4',
					'\\1-\\2-\\3',
				), $p);
			}
		}
		$phone_2 = implode(', ', $phone_2);
		return $phone_2;
	}


	/**
	 * Собирает массив даты в массив для mktime
	 * @param array $arrdate - Дата
	 * @return string - Дата
	 */
	static function _parseDate($arrdate)
	{
		$date_str = array();
		// час
		if (isset($arrdate['H']) and $arrdate['H']) {
			$date_str[0] = $arrdate['H'];
		}
		else {
			$date_str[0] = '0';
		}
		// минуты
		if (isset($arrdate['i']) and $arrdate['i']) {
			$date_str[1] = $arrdate['i'];
		}
		else {
			$date_str[1] = '0';
		}
		// секунды
		if (isset($arrdate['s']) and $arrdate['s']) {
			$date_str[2] = $arrdate['s'];
		}
		else {
			$date_str[2] = '0';
		}

		// месяц
		if ($arrdate['m']) {
			$date_str[3] = $arrdate['m'];
		}
		else {
			$date_str[3] = '0';
		}
		// день
		if ($arrdate['d']) {
			$date_str[4] = $arrdate['d'];
		}
		else {
			$date_str[4] = '0';
		}
		//год
		if ($arrdate['Y']) {
			$date_str[5] = $arrdate['Y'];
		}
		else {
			$date_str[5] = '0';
		}
		return $date_str;
	}

	/**
	 * возвращает форматированную дату в зависимости от типа поля в fields_form, для добавления записи в БД
	 * @param string [array,int] $inp_date - Дата в различных форматах
	 * @param string $format - ФОрмат даты
	 * @param string $field_type - тип в БД (int,timestamp)
	 * @return string[array,int] date
	 */
	static function _get_fdate($inp_date, $format, $field_type)
	{
		$result = NULL;

		if (!$inp_date) {
			return $result;
		}
		if (!$format)
			$format = 'Y-m-d';
		preg_match_all('/[A-Za-z]+/', $format, $matches);
		$format = $matches[0];

		if (!is_array($inp_date)) {
			preg_match_all('/[0-9]+/', $inp_date, $matches);
			$inp_date = $matches[0];
		}
		$cf = count($format);
		$ci = count($inp_date);
		if ($ci == 1) {
			if ($field_type == 'int')
				return $inp_date[0];
			$date_str = explode('-', date('H-i-s-m-d-Y', $inp_date[0])); //1998-08-24 13:00:00
		}
		else {
			if ($cf > $ci) //если расхождения в массиве данных
				$inp_date = array_pad($inp_date, $cf, 0);
			elseif ($cf < $ci)
				$inp_date = array_slice($inp_date, 0, $cf);

			$final_array_date = array_combine($format, $inp_date);
			$date_str = self::_parseDate($final_array_date);
		}

		if ($field_type == 'int') {
			$result = mktime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4], $date_str[5]);
		}
		elseif ($field_type == 'timestamp') {
			$result = $date_str[5] . '-' . $date_str[3] . '-' . $date_str[4] . ' ' . $date_str[0] . ':' . $date_str[1] . ':' . $date_str[2];
		}

		return $result;
	}

	/**
	 * Записывает в куки закодированный КОД (рандомный), для отображения его на рисунке /capcha.php
	 * Использует для кодирования OpenSsl или MCrypt, в качестве ключа используется Хэш фаил $_CFG['_FILE']['HASH_KEY']
	 */
	static function setCaptcha($mask)
	{
		global $_CFG;

		$len = (int)$mask['max'];
		if ($len < 4 || $len > 15) $len = 5;

		$difficult = abs((int)$mask['difficult']);
		if ($difficult > 6) $difficult = 0;

		$A = array(
			0 => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0),
			1 => array(1, 2, 4, 5, 6, 7, 8, 9, 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'И', 'К', 'Л', 'М', 'Н', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Э', 'Ю', 'Я'),
			2 => array(1, 2, 4, 5, 6, 7, 8, 9, 'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'и', 'к', 'л', 'м', 'н', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'э', 'ю', 'я'),
			3 => array(1, 2, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'),
			4 => array(1, 2, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'),
			5 => array(1, 2, 4, 5, 6, 7, 8, 9, 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'И', 'К', 'Л', 'М', 'Н', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'и', 'к', 'л', 'м', 'н', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'э', 'ю', 'я'),
			6 => array(1, 2, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'),
		);
		$word = '';
		$C = count($A[$difficult]) - 1;
		for ($i = 1; $i <= $len; $i++) {
			$word .= $A[$difficult][rand(0, $C)];
		}

		$hash_key = file_get_contents($_CFG['_FILE']['HASH_KEY']) . $_SERVER['REMOTE_ADDR'];
		$hash_key = md5($hash_key); // получаем хешкод
		if (function_exists('openssl_encrypt')) { // если есть openssl
			$crypttext = openssl_encrypt($word, 'aes-128-cbc', $hash_key, false, "1234567812345678");
		}
		elseif (function_exists('mcrypt_encrypt')) { // будем надеяться что есть mcrypt
			//$ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			//$iv = mcrypt_create_iv($ivsize, MCRYPT_RAND);
			$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $hash_key, $word, MCRYPT_MODE_ECB);
			$crypttext = base64encode($crypttext);
		}
		else // если нет даже openssl значит и так сойдёт!
			$crypttext = $word;

		// Запись в куки зашифрованного кода
		_setcookie('chash', $crypttext, (time() + 9999999)); // У некоторых косяк из-за разных часовых поясов
		// Где хранится хэшкод (фаил доступен только на сервере)
		_setcookie('pkey', base64encode($_CFG['_FILE']['HASH_KEY']), (time() + 9999999));
	}

	/**
	 * Получить КОД расшифрованный из куки
	 * @return int
	 */
	static function getCaptcha()
	{
		global $_CFG;
		if (isset($_COOKIE['chash']) and $_COOKIE['chash']) {
			$hash_key = file_get_contents($_CFG['_FILE']['HASH_KEY']) . $_SERVER['REMOTE_ADDR'];
			$hash_key = md5($hash_key);
			if (function_exists('openssl_encrypt')) {
				$word = openssl_decrypt($_COOKIE['chash'], 'aes-128-cbc', $hash_key, false, "1234567812345678");
			}
			elseif (function_exists('mcrypt_encrypt')) {
				$word = base64decode($_COOKIE['chash']);
				$word = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $hash_key, base64decode($_COOKIE['chash']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
				$word = trim($word);
			}
			else
				$word = $_COOKIE['chash'];
			return $word;
		}
		return rand(145, 357); // Если ничего в куках нет, то генерим рандомный и пользователь по новой должен вводит капчу
	}

	static function SpellCheck($txt)
	{
		// исправляем пунктуацию
		$txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
		$txt2 = preg_replace(
			array(
				'/(\s|\`|\~|\@|\#|\$|\%|\^|\&|\*|\(|\)|\_|\-|\+|\=|\[|\]|\{|\}|\"|\'|\/){3,}?/u', // прочие повторяющиеся не символы
				'/([\s]?)(\.|\,|\!|\?\:\;)+/u', // Убирает пробел перед знаками припинания
				'/(\S)(\<br[ \/]+\>)/u', // ставим знак после до разрыва
				'/([^\s]{1})(\.|\,|\!|\?\:\;\-)([^\d\s]{1})/u', // Если после знака нету цифры, то исправляем
				'/([^\d\s]{1})(\.|\,|\!|\?\:\;\-)([^\s]{1})/u', // Если до знака нету цифры, то исправляем
				//'/(\.|\,|\!|\?\:\;\-)(\s)?([A-ZА-Я]{1})([A-ZА-Я]+)/eu', // исправляем капсы
				//'/([А-ЯЁ]{1})([А-ЯЁ]+)/eu', // исправляем капсы
			),
			array(
				'\\1',
				'\\2',
				'\\1 \\2',
				'\\1\\2 \\3',
				'\\1\\2 \\3',
				//"'\\1 \\2'.mb_strtolower('\\3')",
				//"mb_strtolower('\\1\\2')",
			),
			$txt);
		preg_match_all('/([А-ЯЁ]{4,})/eu', $txt2, $temp);
		if (count($temp[0]) > 1) { // исправляем капсы у тех кто переборщил
			$txt2 = preg_replace(
				'/([А-ЯЁ]{2,})/eu', // исправляем капсы
				"mb_strtolower('\\1\\2')",
				$txt2
			);
		}

		return $txt2;
	}

	// Проверка на тип поля Boolean
	static function isTypeBool($type)
	{
		$list = array(
			'bool' => true,
			'boolean' => true,
		);
		if (isset($list[$type]))
			return true;
		return false;
	}

	// Проверка на тип поля целое число
	static function isTypeInt($type)
	{
		$list = array(
			'int' => true,
			'integer' => true,
			'double' => true,
			'number' => true,
		);
		if (isset($list[$type]))
			return true;
		return false;
	}

	// Проверка на тип поля с запятой
	static function isTypeFloat($type)
	{
		$list = array(
			'float' => true,
			'double' => true,
			'decimal' => true
		);
		if (isset($list[$type]))
			return true;
		return false;
	}

	// Проверка на тип поля с запятой
	static function isTypeNumber($type)
	{
		if (self::isTypeFloat($type) or self::isTypeInt($type))
			return true;
		return false;
	}
}
