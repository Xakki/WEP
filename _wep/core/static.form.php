<?php

class static_form {
/*------------- ADD ADD ADD ADD ADD ------------------*/

	// in:  id			opt
	//		fld_data:assoc array <fieldname>=><value> 	req
	//		att_data:assoc array <fieldname>=>array 	req
	//		mmo_data:assoc array <fieldname>=>text	req
	// out: 0 - success,
	//      otherwise errorcode

	static function _add(&$_this,$flag_select=true, $flag_update=false) {
		// add ordind field
		if ($_this->mf_ordctrl and (!isset($_this->fld_data[$_this->mf_ordctrl]) or $_this->fld_data[$_this->mf_ordctrl]==0)) {
			if ($ordind = $_this->_get_new_ord())
				$_this->fld_data[$_this->mf_ordctrl] = $ordind;
		}
		// add parent_id field
		if ($_this->mf_istree and $_this->parent_id and !$_this->fld_data[$_this->mf_istree])
			$_this->fld_data[$_this->mf_istree] = $_this->parent_id;
		// add owner_id field
		if ($_this->owner and (!isset($_this->fld_data[$_this->owner_name]) or !$_this->fld_data[$_this->owner_name]) )
			$_this->fld_data[$_this->owner_name] = $_this->owner->id;

		if (!isset($_this->fld_data) && !count($_this->fld_data))
			return static_main::log('error',static_main::m('add_empty',$_this));

		if (!self::_add_fields($_this,$flag_update)) return false;

		//umask($_this->_CFG['wep']['chmod']);
		if (isset($_this->att_data) && count($_this->att_data)) {
			if (!self::_add_attaches($_this)) {
				$_this->_delete();
				$_this->id = NULL;
				return false;
			}
		}
		if (isset($_this->mmo_data) && count($_this->mmo_data)) {
			if (!self::_add_memos($_this)) {
				$_this->_delete();
				$_this->id = NULL;
				return false;
			}
		}
		if($_this->id and $flag_select)
			$_this->data = $_this->_select();
		if (isset($_this->mf_indexing) && $_this->mf_indexing) $_this->indexing();
		//static_main::log('ok',static_main::m('add',array($_this->tablename),$_this));
		return true;
	}

	static function _add_fields(&$_this,$flag_update=false) {
		$int_type = array(
			'int'=>1,
			'tinyint'=>1,
			'longint'=>1,
			'shortint'=>1,
		);
		if (!count($_this->fld_data)) return false;
		// inserting
		$data = array();
		foreach($_this->fld_data as $key => &$value) {
			if(!isset($_this->fields[$key]['noquote'])) {
				if(is_array($value))
					$value = '\''.$_this->SqlEsc(preg_replace('/\|+/', '|', '|'.implode('|',$value).'|')).'\'';
				elseif($_this->fields[$key]['type']=='bool')
					$value = ((int)$value == 1 ? 1 : 0);
				elseif(isset($int_type[$_this->fields[$key]['type']]))
					$value =  preg_replace('/[^0-9\-]+/', '', $value);
				else
					$value = '\''.$_this->SqlEsc($value).'\'';
			}
			if($flag_update) {
				if(!isset($_this->fields[$key]['noquote']))
					$data[$key] = '`'.$key.'` = VALUES(`'.$key.'`)';
				else
					$data[$key] = '`'.$key.'` = '.$_this->fld_data[$key];
			}
		}
		if ($_this->mf_timecr) 
			$_this->fld_data['mf_timecr'] = $_this->_CFG['time'];
		if ($_this->mf_timeup) 
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if($_this->mf_ipcreate) {
			$_this->fld_data['mf_ipcreate'] = 'inet_aton("'.$_SERVER['REMOTE_ADDR'].'")';
			//$_this->fld_data['mf_ipcreate'] = sprintf("%u",ip2long($_SERVER['REMOTE_ADDR']));
			if(!$_SERVER['REMOTE_ADDR'])
				trigger_error('ERROR REMOTE_ADDR `'.$_SERVER['REMOTE_ADDR'].'`. ', E_USER_WARNING);
		}
		if($_this->mf_createrid and isset($_SESSION['user']['id']) and (!isset($_this->fld_data[$_this->mf_createrid]) or $_this->fld_data[$_this->mf_createrid]=='') )
			$_this->fld_data[$_this->mf_createrid]= $_SESSION['user']['id'];

		$q = 'INSERT INTO `'.$_this->tablename.'` (`'.implode('`,`', array_keys($_this->fld_data)).'`) VALUES ('.implode(',', $_this->fld_data).')';
		if($flag_update) { // параметр передается в ф. _addUp() - обновление данных если найдена конфликтная запись
			$q .= ' ON DUPLICATE KEY UPDATE '.implode(', ',$data);
		}

		$result=$_this->SQL->execSQL($q);

		if($result->err) return false;
		// get last id if not used nick
		if (!$_this->mf_use_charid && !isset($_this->fld_data['id']))
			$_this->id = (int)$result->lastId();
		elseif($_this->fld_data['id'])
			$_this->id = $_this->fld_data['id'];
		else $_this->id = NULL;

		return true;
	}

	static function _add_attaches(&$_this) {
		if (!count($_this->attaches) or !count($_this->att_data)) return true;
		$result=$_this->SQL->execSQL('SELECT id, '.implode(',', array_keys($_this->attaches)).' FROM `'.$_this->tablename.'` WHERE id IN ('.$_this->id.')');
		if($result->err) return false;
		$row = $result->fetch();
		$prop = array();

		foreach($_this->att_data as $key => $value) 
		{
			// Пропускаем если нету данных ("вероятно" фаил не загружали или не меняли)
			if (!is_array($value) or $value['tmp_name'] == 'none' or $value['tmp_name'] == '') continue;
			
			// Путь к папке фаила
			$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
			
			// старый фаил, для удаления, может имет другое расширение
			$oldname =$pathimg.'/'. $_this->id. '.'.$row[$key];
			if ($row[$key] and file_exists($oldname)) {
				chmod($oldname, $_this->_CFG['wep']['chmod']);
				unlink($oldname);
				if (count($_this->attaches[$key]['thumb']))
					foreach($_this->attaches[$key]['thumb'] as $imod) {
						if(!isset($imod['pref'])) $imod['pref'] = '';
						$oldname =$pathimg.'/'. $imod['pref'].$_this->id. '.'.$row[$key];
						if (file_exists($oldname))
							unlink($oldname);
					}

			}

			// Удаление фаила 
			if ($value['tmp_name'] == ':delete:') {
				$prop[] = '`'.$key.'` = \'\'';
				continue;
			}

			/*if(!isset($_this->fields_form[$key]['mime'])) {
				print_r('<pre>');print_r($value);exit();
				//$_this->attaches[$key]['mime'] = array($value['type']=>);
			}*/
			if(isset($value['ext']) and $value['ext'])
				$ext = $value['ext'];
			else
				$ext = $value['ext'] = strtolower(array_pop(explode('.',$value['name'])));

			$newname = $pathimg.'/'.$_this->id.'.'.$ext;
			if (file_exists($newname)) { // Удаляем старое
				chmod($newname, $_this->_CFG['wep']['chmod']);
				unlink($newname);
			}
			chmod($value['tmp_name'], $_this->_CFG['wep']['chmod']);
			if (!rename($value['tmp_name'], $newname))
				return static_main::log('error','Error copy file '.$value['name']);

			// Дополнительные изображения
			if (isset($_this->fields_form[$key]['thumb'])) {
				if(isset($value['att_type']) and $value['att_type']!='img') // если это не рисунок, то thumb не приминим
					return static_main::log('error','File `'.$newname.'` is not image. Function `thumb` not accept.');
				$prefix = $pathimg.'/';
				if (count($_this->fields_form[$key]['thumb']))
					foreach($_this->fields_form[$key]['thumb'] as $imod) {
						if(!isset($imod['pref']) or !$imod['pref'])
							$imod['pref'] = '';// по умолчинию без префикса
						if(isset($imod['path']) and $imod['path'])
							$newname2 = $_this->_CFG['_PATH']['path'].$imod['path'].'/'.$imod['pref'].$_this->id.'.'.$ext;
						else
							$newname2 = $prefix.$imod['pref'].$_this->id.'.'.$ext;
						if ($imod['type']=='crop')
							self::_cropImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
						elseif ($imod['type']=='resize')
							self::_resizeImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
						elseif ($imod['type']=='resizecrop')
							self::_resizecropImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
						elseif ($imod['type']=='watermark')
							self::_waterMark($_this,$newname,$newname2, $imod['logo'], $imod['x'], $imod['y']);
						elseif($newname!=$newname2)
							copy($newname,$newname2);
						chmod($newname, $_this->_CFG['wep']['chmod']);
					}
			}
			$prop[] = '`'.$key.'` = \''.$ext.'\'';
		}
		if (count($prop)) {
			$result=$_this->SQL->execSQL('UPDATE `'.$_this->tablename.'` SET '.implode(',', $prop).' WHERE id = \''.$_this->id.'\'');
			if($result->err) return false;
			unset($prop);
		}
		return true;
	}

	static function _add_memos(&$_this) {
		if (!count($_this->memos) or !count($_this->mmo_data)) return true;
		foreach($_this->mmo_data as $key => $value)
		{
			$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForMemo($key);
			if(!isset($_this->memos[$key])) return static_main::log('error','Error add memo. Missing field `'.$key.'` in module `'.$_this->caption.'`');
			$name = $pathimg.'/'.$_this->id.$_this->text_ext;
			$f = fopen($name, 'w');
				if (!$f)
					return static_main::log('error','Can`t create file '.$name);
				if (fwrite($f, $value) == -1)
					return static_main::log('error','Can`t write data into file '.$name);
				if (!fclose($f))
					return static_main::log('error','Can`t close file '. $name);
			global $_CFG;
			chmod($name, $_CFG['wep']['chmod']);
			static_main::log('notice','File '.$name.' writed.');
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

	static function _update(&$_this,$flag_select=true,$where=false) {
		if ($_this->mf_istree and !is_array($_this->id) and isset($_this->fld_data[$_this->mf_istree])) {
			if ($_this->fld_data[$_this->mf_istree]==$_this->id)
				return static_main::log('error','Child `'.$_this->caption.'` can`t be owner to self ');
		}
		if($_this->mf_timeup)
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if($_this->mf_timeoff and !isset($_this->fld_data['mf_timeoff']) and isset($_this->fld_data[$_this->mf_actctrl]) and !$_this->fld_data[$_this->mf_actctrl] and $_this->data[$_this->id][$_this->mf_actctrl]) 
			$_this->fld_data['mf_timeoff'] = $_this->_CFG['time'];
		if($_this->mf_ipcreate) {
			unset($_this->fld_data['mf_ipcreate']);
		}

		// rename attaches & memos
		if (!is_array($_this->id) and isset($_this->fld_data['id']) && $_this->fld_data['id'] != $_this->id) {
			if (!self::_rename_parent_childs($_this)) return false;
			if (!self::_rename_childs($_this)) return false;
			if (!self::_rename_attaches($_this)) return false;
			if (!self::_rename_memos($_this)) return false;
		}

		if (!self::_update_fields($_this,$where)) return false;

		if (isset($_this->fld_data['id']))
			$_this->id = $_this->fld_data['id'];
		//umask($_this->_CFG['wep']['chmod']);
		if (!self::_update_attaches($_this)) return false;
		if (!self::_update_memos($_this)) return false;
		if($_this->id and $flag_select)
			$_this->data = $_this->_select();
		if (isset($_this->mf_indexing) && $_this->mf_indexing) $_this->indexing();
		//static_main::log('ok',static_main::m('update',array($_this->tablename),$_this));
		return true;

	}


	static function _update_fields(&$_this,$where=false) {
		if (!count($_this->fld_data)) return true;
		$int_type = array(
			'int'=>1,
			'tinyint'=>1,
			'longint'=>1,
			'shortint'=>1,
		);
		// preparing
		$data = array();
		foreach($_this->fld_data as $key => $value) {
			if(!isset($_this->fields[$key]['noquote'])) {
				if(is_array($value)) {
					$value = '\''.$_this->SqlEsc(preg_replace('/\|+/', '|', '|'.implode('|',$value).'|')).'\'';
				}
				elseif($_this->fields[$key]['type']=='bool')
					$value = ((int)$value ? 1 : 0);
				elseif(isset($int_type[$_this->fields[$key]['type']]))
					$value = preg_replace('/[^0-9\-]+/', '', $value);
				else
					$value = '\''.$_this->SqlEsc($value).'\'';
			}

			$data[$key] = '`'.$key.'` = '.$value;
		}
		$q = 'UPDATE `'.$_this->tablename.'` SET '.implode(',', $data);
		if($where!==false) {
			$q .= ' WHERE '.$where;
		} else {
			$iq = $_this->_id_as_string();
			if(!$iq) {
				static_main::log('error','Error update: miss id');
				return false;
			}
			$q .= ' WHERE id IN ('.$iq.')';
		}
		$result = $_this->SQL->execSQL($q);
		if($result->err) return false;
		if(isset($_this->fld_data[$_this->owner_name]) and !is_array($_this->id))
			$_this->owner_id = $_this->fld_data[$_this->owner_name];
		if(isset($_this->fld_data[$_this->mf_istree]) and !is_array($_this->id))
			$_this->parent_id = $_this->fld_data[$_this->mf_istree];
		return true;
	}

	static function _rename_childs(&$_this) {
		if(!count($_this->childs)) return true;
		foreach($_this->childs as $ch => $child) {
			$result=$_this->SQL->execSQL('UPDATE `'.$_this->childs[$ch]->tablename.'` SET '.$_this->childs[$ch]->owner_name.' = \''.$_this->fld_data['id'].'\' WHERE '.$_this->childs[$ch]->owner_name.' =\''.$_this->id.'\'');
			if($result->err) return false;
		}
		return true;
	}

	static function _rename_parent_childs(&$_this) {
		if(!$_this->mf_istree) return true;
		$result=$_this->SQL->execSQL('UPDATE `'.$_this->tablename.'` SET `parent_id` = \''.$_this->fld_data['id'].'\' WHERE parent_id =\''.$_this->id.'\'');
		if($result->err) return false;
		return true;
	}

	static function _rename_attaches(&$_this) {
		if(!count($_this->attaches)) return true;
		$result=$_this->SQL->execSQL('SELECT `id`, `'.implode('`,`', array_keys($_this->attaches)).'` FROM `'.$_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		$row = $result->fetch();
		if ($row) {
			foreach($_this->attaches as $key => $value) {
				$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
				$f = $pathimg.'/'. $row['id'].'.'.$value['exts'][$row[$key]];
				if (file_exists($f))
					rename($f,$pathimg.'/'. $_this->fld_data['id'].'.'. $value['exts'][$row[$key]]);
			}
		}
		return true;
	}

	static function _update_attaches(&$_this) {
		return self::_add_attaches($_this);
	}

	static function _rename_memos(&$_this) {
		if(!count($_this->memos)) return true;
		foreach($_this->memos as $key => $value) {
			$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForMemo($key);
			$f = $pathimg.'/'.$_this->id.$_this->text_ext;
			if (file_exists($f)) rename($f, $pathimg.'/'.$_this->fld_data['id'].$_this->text_ext);
		}
		return true;
	}

	static function _update_memos(&$_this) {
		return self::_add_memos($_this);
	}


/*------------- DELETE DELETE DELETE -----------------*/

	/**
	 * Удаление данных
	 * this->id
	 * @return bool - результат операции 
	 */
	public static function _delete(&$_this) {
		if (!is_array($_this->id)) $_this->id = array($_this->id);
		if (!count($_this->id)) return true;
		// delete childs of owner
		if (count($_this->childs)) {
			foreach($_this->childs as &$child){
				$child->id = $_this->id;
				if (!self::_delete_ownered($child)) return false;
			}
			unset($child);
		}
		// delete childs of tree
		if ($_this->mf_istree) {
			$id = $_this->id;
			if (!self::_delete_parented($_this)) return false;
			$_this->id = $id;
		}
		if (!self::_delete_attaches($_this)) return false;
		if (!self::_delete_memos($_this)) return false;
		if (!self::_delete_fields($_this)) return false;

		if ($_this->mf_indexing) $_this->deindexing();
		$_this->id = NULL;
		return true;
	}

	/**
	 * Удаление дочерних данных из БД
	 * Вспомогательная функция
	 */
	private static function _delete_ownered(&$_this) {
		// select record ids to delete
		$result=$_this->SQL->execSQL('SELECT id FROM `'.$_this->tablename.'` WHERE `'.$_this->owner_name.'` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		// create list
		$_this->id = array();
		while (list($id) = $result->fetch_row()) 
			$_this->id[] = $id;
		// if list not empty
		if (count($_this->id)) $_this->_delete();
		return true;
	}

	/**
	 * Удаление всех родителей даных
	 * Вспомогательная функция
	 */
	private static function _delete_parented(&$_this) {
		// select record ids to delete
		$result=$_this->SQL->execSQL('SELECT id FROM `'.$_this->tablename.'` WHERE `parent_id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;

		// create list
		$_this->id = array();
		while (list($id) = $result->fetch_row())
			$_this->id[] = $id;

		// if list not empty
		if (count($_this->id)) $_this->_delete();
		return true;
	}

	/**
	 * Удаление данных из БД
	 * Вспомогательная функция
	 */
	private static function _delete_fields(&$_this) {
		// delete records
		$result=$_this->SQL->execSQL('DELETE FROM `'.$_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		return true;
	}

	/**
	 * Удаление фаилов 
	 * Вспомогательная функция
	 */
	private static function _delete_attaches(&$_this) {
		if (!count($_this->attaches)) return true;
		$result=$_this->SQL->execSQL('SELECT `id`, `'.implode('`,`', array_keys($_this->attaches)).'` FROM `'. $_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;

		while ($row = $result->fetch()) {
			foreach($_this->attaches as $key => $att) {
				$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
				$oldname =$pathimg.'/'. $row['id']. '.'.$row[$key];
				if ($row[$key]) {
					if(file_exists($oldname)) {
						chmod($oldname, $_this->_CFG['wep']['chmod']);
						if (!unlink($oldname)) return static_main::log('error','Cannot delete file `'.$oldname.'`');
					}
					if (count($att['thumb']))
						foreach($att['thumb'] as $imod) {
							if(!isset($imod['pref'])) $imod['pref'] = '';
							$oldname =$pathimg.'/'. $imod['pref'].$row['id']. '.'.$row[$key];
							if (file_exists($oldname))
								if (!unlink($oldname)) return static_main::log('error','Cannot delete file `'.$oldname.'`');
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
	private static function _delete_memos(&$_this) {
		if (!count($_this->memos)) return true;
		foreach($_this->id as $id) {
			foreach($_this->memos as $key => $value) {
				$pathimg = $_this->getPathForMemo($key);
				$f = $pathimg.'/'.$id.$_this->text_ext;
				if (file_exists($f))
					if (!unlink($f)) return $_this->_error('Cannot delete memo `'.$f.'`',1);
			}
		}
		return true;
	}

	/************************* IMAGE *****************************/
	/*
		Реализованно для GD2
		TODO - imagemagic
	*/

	/**
	 * Наложение водяного знака (маркера)
	 *
	 */
	static function _waterMark(&$_this,$InFile, $OutFile,$logoFile='',$posX=0,$posY=0)
	{
		if(!$logoFile)
			$logoFile = $_this->_CFG['_imgwater'];
		$logoFile = $_this->_CFG['_PATH']['path'].$logoFile;

		if(!$imtypeIn = self::_is_image($InFile))// опред тип файла
			return static_main::log('error','File '.$InFile.' is not image');
		if($imtypeIn>3) return false;

		if(!$imtypeLogo = self::_is_image($logoFile))// опред тип файла
			return static_main::log('error','File '.$logoFile.' is not image');
		if($imtypeLogo>3) return false;

		$znak_hw = getimagesize($logoFile);
		$foto_hw = getimagesize($InFile);

		$znak = self::_imagecreatefrom($_this,$logoFile,$imtypeLogo);
		$foto = self::_imagecreatefrom($_this,$InFile,$imtypeIn);

		imagecopy ($foto,
		$znak,
		$foto_hw[0] - $znak_hw[0],
		$foto_hw[1] - $znak_hw[1],
		0,
		0,
		$znak_hw[0],
		$znak_hw[1]);
		if(file_exists($OutFile)) {
			chmod($OutFile, $_this->_CFG['wep']['chmod']);
			unlink($OutFile);
		}
		self::_image_to_file($foto, $OutFile,$_this->_CFG['_imgquality'],$imtypeIn);//сохраняем в файл
		imagedestroy ($znak);
		imagedestroy ($foto);
		if(!file_exists($OutFile)) {
			static_main::log('error','Cant composite file on '.__LINE__.' in kernel');
			return false;
		}
		return true;
	}

	static function _resizeImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		chmod($InFile, $_CFG['wep']['chmod']);
		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер

		if(!$WidthX and !$HeightY) 
			return true;
		if(!$WidthX)
			$WidthX = ($width_orig*$HeightY)/$height_orig;
		if(!$HeightY) {
			$HeightY = ($height_orig*$WidthX)/$width_orig;
		}

		if($width_orig<$WidthX and $height_orig<$HeightY) {
			if($InFile!=$OutFile) {
				copy($InFile,$OutFile);
				global $_CFG;
				chmod($OutFile, $_CFG['wep']['chmod']);
			}
			return true;
		}
		elseif($width_orig/$WidthX < $height_orig/$HeightY) {
			$WidthX = round($HeightY*$width_orig/$height_orig);
		}
		elseif($width_orig/$WidthX > $height_orig/$HeightY) {
			$HeightY = round($WidthX*$height_orig/$width_orig);
		}

		$thumb = imagecreatetruecolor($WidthX, $HeightY);//созд пустой рисунок
		if(!$imtype = self::_is_image($InFile))// опред тип файла
			return static_main::log('error','File '.$InFile.' is not image');
		if($imtype>3) return true;
		$source = self::_imagecreatefrom($_this,$InFile,$imtype);//открываем рисунок
		imagecopyresized($thumb, $source, 0, 0, 0, 0, $WidthX, $HeightY, $width_orig, $height_orig);//меняем размер
		self::_image_to_file($thumb, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return static_main::log('error','Cant create file');
		return true;
	}

	static function _cropImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		chmod($InFile, $_CFG['wep']['chmod']);
		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер

		if(!$WidthX and !$HeightY) 
			return true;
		if(!$WidthX)
			$WidthX = ($width_orig*$HeightY)/$height_orig;
		if(!$HeightY) {
			$HeightY = ($height_orig*$WidthX)/$width_orig;
		}

		// Resample
		$thumb = imagecreatetruecolor($WidthX, $HeightY);//созд пустой рисунок
		if(!$imtype = self::_is_image($InFile)) // опред тип файла
			return static_main::log('error','File is not image');
		if($imtype>3) return true;
		$source = self::_imagecreatefrom($_this,$InFile,$imtype);//открываем рисунок
		imagecopyresampled($thumb, $source, 0, 0, $width_orig/2-$WidthX/2, $height_orig/2-$HeightY/2, $WidthX, $HeightY, $WidthX, $HeightY);
		self::_image_to_file($thumb, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return static_main::log('error','Cant create img file ');
		return true;
	}

	static function _resizecropImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		$trueX=$WidthX;$trueY=$HeightY;
		chmod($InFile, $_CFG['wep']['chmod']);
		list($width_orig, $height_orig) = getimagesize($InFile);

		if(!$trueX and !$trueY) 
			return true;
		if(!$trueX)
			$WidthX = $trueX = ($width_orig*$trueY)/$height_orig;
		if(!$trueY) {
			$HeightY = $trueY = ($height_orig*$trueX)/$width_orig;
		}

		$ratio_orig = $width_orig/$height_orig;
		if ($WidthX/$HeightY > $ratio_orig) {
		   $HeightY = $WidthX/$ratio_orig;
		} else {
		   $WidthX = $HeightY*$ratio_orig;
		}
		/*Создаем пустое изображение на вывод*/
		if(!($thumb = @imagecreatetruecolor($WidthX, $HeightY)))
			return static_main::log('error','Cannot Initialize new GD image stream');
		/*Определяем тип рисунка*/
		if(!$imtype = self::_is_image($InFile))// опред тип файла
			return static_main::log('error','File is not image');
		/*Обработка только jpeg, gif, png*/
		if($imtype>3) return true;
		/*Открываем исходный рисунок*/
		if(!$source = self::_imagecreatefrom($_this,$InFile,$imtype))//открываем рисунок
			return static_main::log('error','File '.$InFile.' is not image');
		if(!imagecopyresampled($thumb, $source, 0, 0, 0, 0, $WidthX, $HeightY, $width_orig, $height_orig))
			return static_main::log('error','Error imagecopyresampled');
		if(!($thumb2 = @imagecreatetruecolor($trueX, $trueY)))
			return static_main::log('error','Cannot Initialize new GD image stream');
		if(!imagecopyresampled($thumb2, $thumb, 0, 0, $WidthX/2-$trueX/2, $HeightY/2-$trueY/2, $trueX, $trueY, $trueX, $trueY)) 
			return static_main::log('error','Error imagecopyresampled');
		self::_image_to_file($thumb2, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return static_main::log('error','Cant create file');
		return true;
	}

	static function _imagecreatefrom(&$_this,$im_file,$imtype)
	{
		/*
Возвращаемое значение	Константа
1	IMAGETYPE_GIF
2	IMAGETYPE_JPEG
3	IMAGETYPE_PNG
4	IMAGETYPE_SWF
5	IMAGETYPE_PSD
6	IMAGETYPE_BMP
7	IMAGETYPE_TIFF_II
8	IMAGETYPE_TIFF_MM
9	IMAGETYPE_JPC
10	IMAGETYPE_JP2
11	IMAGETYPE_JPX
12	IMAGETYPE_JB2
13	IMAGETYPE_SWC
14	IMAGETYPE_IFF
15	IMAGETYPE_WBMP
16	IMAGETYPE_XBM
		*/
		if($imtype==1) {
			if(!($image=@imagecreatefromgif($im_file)))
				static_main::log('error','Can not create a new image from file');
		}
		elseif($imtype==2) {
			if(!($image=imagecreatefromjpeg($im_file)))
				static_main::log('error','Can not create a new image from file');
		}
		elseif($imtype==3) {
			if(!($image=imagecreatefrompng($im_file)))
				static_main::log('error','Can not create a new image from file');
		}
		else return false;
		return $image;
	}

	static function _image_to_file($im,$file,$q,$imtype)
	{
		if($imtype==1) imagegif($im, $file,$q);
		elseif($imtype==2) imagejpeg($im, $file,$q);
		elseif($imtype==3) imagepng($im, $file,8);
		else return false;
		return true;
	}

	static function _is_image($file) {
		return exif_imagetype($file);
	}


	/**
	* Проверка формы
	* $data - POST lfyyst либо  данные из БД
	*/
	static function _fFormCheck(&$_this,&$data,&$param,&$FORMS_FIELDS) { //$_this->fields_form
		global $_tpl;
		if(!count($FORMS_FIELDS))
			return array('mess'=>array(
				static_main::am('error','errdata',$_this)
			));
		//$MASK = &$_this->_CFG['_MASK'];
		$arr_nochek = array('info'=>1,'sbmt'=>1,'alert'=>1);
		$messages='';
		$arr_err_name=array();
		$textm = '';
		$mess = array();
		//print_r('<pre>');print_r($FORMS_FIELDS);
		foreach($FORMS_FIELDS as $key=>&$form)
		{
			$error = array();
			if($key=='_*features*_') continue;
			if(!isset($form['type']))
				return array('mess'=>array(
					static_main::am('error','errdata',' : '.$key,$_this)
				));
			if(isset($arr_nochek[$form['type']])) continue;

			/*Поля которые недоступны пользователю не проверяем, дефолтные значения прописываются в kPreFields()*/
			if((isset($form['readonly']) and $form['readonly']) or 
				(isset($form['mask']['fview']) and $form['mask']['fview']==2) or 
				(isset($form['mask']['usercheck']) and !static_main::_prmGroupCheck($form['mask']['usercheck']))) {
				continue;
			}

			if($form['type']=='file') {
				self::check_file_field($_this,$form,$error,$data,$key);	
			}
			/*Капча*/
			elseif($form['type']=='captcha') {
				//strcasecmp($data[$key],$form['captcha'])
				if($data[$key]!=$form['captcha']) {
					$error[] = 31;
				}
			}
			elseif(isset($form['multiple']) and $form['multiple']) {
				if(isset($form['mask']['minarr']) and $form['mask']['minarr']>0 and (!isset($data[$key]) or !count($data[$key])))
					$error[] = 1;
				elseif(isset($data[$key])) {
					if(is_array($data[$key])) {
						//if(count($data[$key])) {
						//	$data[$key] = array_filter($data[$key],array('static_form','trimArray'));
						//}
						if(isset($form['mask']['maxarr'])){
							if(count($data[$key])>$form['mask']['maxarr'])
								$error[] = 26;
						}
						if(isset($form['mask']['minarr'])){
							if(count($data[$key])<$form['mask']['minarr'])
								$error[] = 27;
						}
						foreach($data[$key] as $tk=>$tv) {
							self::check_formfield($_this,$form,$error,$data[$key],$tk);
						}
					}
					else {
						$error[] = 51;
					}
				}
			}
			else {
				if(isset($data[$key]) and is_array($data[$key])) {
					$error[] = 5;
				} else {
					self::check_formfield($_this,$form,$error,$data,$key);
				}
			}

			foreach($error as $row) {
				$messages = '';
				if($row==1) //no empty
					$messages = static_main::m('_err_1',$_this);
				elseif($row==2) //max chars
					$messages = static_main::m('_err_2',array($form['mask']['max'],(_strlen($data[$key])-$form['mask']['max'])),$_this);
				elseif($row==21) // min chars
					$messages = static_main::m('_err_21',array($form['mask']['min'],($form['mask']['min']-_strlen($data[$key]))),$_this);
				elseif($row==22) //max int
					$messages = static_main::m('_err_22',$_this).$form['mask']['maxint'];
				elseif($row==23) //min int
					$messages = static_main::m('_err_23',$_this).$form['mask']['minint'];
				elseif($row==24) // min chars
					$messages = static_main::m('_err_22',$_this).$form['mask']['max'];
				elseif($row==25) // max chars
					$messages = static_main::m('_err_23',$_this).$form['mask']['min'];
				elseif($row==26) // min Array count
					$messages = static_main::m('_err_22',$_this).$form['mask']['maxarr'];
				elseif($row==27) // max Array count
					$messages = static_main::m('_err_23',$_this).$form['mask']['minarr'];
				elseif($row==29) //limit file size
					$messages = static_main::m('_err_29',array($_FILES[$key]['name']),$_this).$form['maxsize'].'Kb';
				elseif($row==3) {//wrong data
					if(isset($form['matches_err']) and count($form['matches_err'][0])) {
						$textm = 'Обнаружены следующие недопустимые символы - ';
						foreach($form['matches_err'][0] as $mk=>$mr) {
							if(isset($mr[1]))
								$textm .= $mr[0].'(поз. '.$mr[1].'), ';
							if($mk>10) {
								$textm .= 'и т.д., ';
								break;
							}
						}
						$textm .= ' и следующей попыткой удалить их автоматический?<input type="checkbox" value="1" name="'.$key.'_rplf'.'" checked="checked" style="height: 0.8em;">';
						//$FORMS_FIELDS[$key.'_rplf'] = array('type'=>'hidden','value'=>'del');
					}
					$messages = static_main::m('_err_3',array($textm),$_this);
				}
				elseif($row==31) //wrong captchs
					$messages = static_main::m('_err_31',$_this);
				elseif($row==32) // wrong repeat pass
					$messages = static_main::m('_err_32',$_this);
				elseif($row==321) // wrong old pass
					$messages = static_main::m('_err_321',$_this);
				elseif($row==33) //data error
					$messages = static_main::m('_err_33',$_this);
				elseif($row==39) //wrong file type
					$messages = static_main::m('_err_39',array($_FILES[$key]['name']),$_this).'- '.implode(',',array_unique($form['mime'])).'.';
				elseif($row>=40 and $row<50) //error load file
					$messages = static_main::m('_err_'.$row,array($_FILES[$key]['name']),$_this);
				elseif($row==4)  // wrong link
					$messages = static_main::m('_err_4',$_this);
				elseif($row==5)  // wrong link
					$messages = 'Множественные значения не допустимы!';
				elseif($row==51)  // wrong link
					$messages = 'Множественные значения не обнаружены!';
				$arr_err_name[$key]=$key;

				if(isset($param['errMess'])) {
					$mess[] = static_main::am('error',$form['caption'].': '.$messages);
				}
				if(isset($param['ajax'])) {
					$_tpl['onload'] .= 'putEMF(\''.$key.'\',\''.$messages.'\');'; // запись в форму по ссылке
				}
				else
					$form['error'][] = $messages; // запись в форму по ссылке
				//$form['caption'].': '.
			}

		}
		unset($form);
		if(count($arr_err_name)>0 and !isset($param['errMess'])) {
			$mess[] = static_main::am('error','Поля формы заполненны не верно.');
		}
		/*$_tpl['onload'] .'CKEDITOR.replace( \'editor1\',
						 {
							  toolbar : \'basic\',
							  uiColor : \'# 9AB8F3\'
						 });';*/
		return array('mess'=>$mess,'vars'=>$data);
	}
	
	/// Проверяет только загрузку фаилов
 	static function check_file_field(&$_this,&$form,&$error,&$data,$key) {
		//*********** Файлы
		if($form['type']=='file') {
			//TODO: multiple
			if(isset($data[$key.'_del']) and (int)$data[$key.'_del']==1){
					$_FILES[$key] = $data[$key] = array('name'=>':delete:','tmp_name'=>':delete:');
			}
			elseif(isset($_FILES[$key]['name']) and $_FILES[$key]['name']!='') {
				$value = &$_FILES[$key];
				if($value['error'] != 0) {
					$error[]= (int)'4'.$value['error'];
					return false;
				}
				elseif(isset($form['maxsize']) and $value['size']>($form['maxsize']*1024)) {
					$error[]=29;
					return false;
				}
				elseif(!$value['tmp_name']) {
					$error[]=40;
					return false;
				}
				else {
					$is_image = self::_is_image($value['tmp_name']);
					$form['att_type'] = '';
					if($is_image) {
						$value['ext'] = image_type_to_extension($is_image,false);
						$form['att_type'] = 'img';
					} else {
						$value['ext'] = strtolower(array_pop(explode('.',$value['name'])));
						if(preg_match('/[^A-Za-z0-9]/',$value['ext'])) { // Кривое расширение фаила
							$error[]=39;
							return false;
						}
					}
					 // Ищем совпадения
					if(isset($form['mime'])) {
						$flag = false;
						if(in_array('image',$form['mime']) and $form['att_type'] == 'img') // Для любых изображений
							$flag = true;
						elseif(in_array($value['ext'],$form['mime']))
							$flag = true;
						elseif($value['type'] and isset($form['mime'][$value['type']]))
							$flag = true;
					}
					else
						$flag = true;

					if(!$flag) { //Не верный тип фаилы
						$error[]=39;
						return false;
					}
					else {
						static_tools::_checkdir($_this->_CFG['_PATH']['temp']);
						$temp = $_this->_CFG['_PATH']['temp'].substr(md5(getmicrotime().rand(0,50)),16).'.'.$value['ext'];
						static_tools::_checkdir($_this->_CFG['_PATH']['temp']);
						if (move_uploaded_file($value['tmp_name'], $temp)){
							$value['tmp_name']= $temp;
							$data[$key] = $value;
						}else {
							$error[]=40;
							return false;
						}
					}
				}
			}
			elseif(isset($data[$key . '_temp_upload']) && is_array($data[$key . '_temp_upload']) && $data[$key . '_temp_upload']['name'] && $data[$key . '_temp_upload']['type']) {
				$data[$key] = $data[$key . '_temp_upload'];	
				$data[$key]['tmp_name'] = $_this->_CFG['_PATH']['temp'] . $data[$key . '_temp_upload']['name'];
				$_FILES[$key] = $data[$key];
			}
			if(isset($form['mask']['min']) and $form['mask']['min'] and !$_this->data[$_this->id][$key] and (!$_FILES[$key]['name'] or $_FILES[$key]['name'] == ':delete:')) {
				$error[] = 1;
				return false;
			}

		}
		return true;
	}

	/**
	 * Проверяющий форму по отдельному полю
	 *
	 */
 	static function check_formfield(&$_this,&$form,&$error,&$data,$key) {
		$MASK = &$_this->_CFG['_MASK'];
		$FIELDS = &$_this->fields[$key];

		//*********** CHECKBOX
		if($form['type']=='checkbox')
		{
			$form['value'] = $data[$key] = ((isset($data[$key]) and $data[$key])? 1 : 0);
			return true;
		}
		/*пароль*/
		if($form['type']=='password') {
			if(isset($form['mask']['password']) and $form['mask']['password']=='re')
			{
				if($data[$key] or $data['re_'.$key]) {
					if($data[$key]!=$data['re_'.$key])
						$error[] = 32;
					else
						$data[$key] = md5($_this->_CFG['wep']['md5'].$data[$key]);
				}else
					unset($data[$key]);
			}
			elseif(isset($form['mask']['password']) and $form['mask']['password']=='change')
			{
				if(isset($_this->data[$_this->id][$key]) and $data[$key] or $data[$key.'_old']) {
					if($_this->data[$_this->id][$key]!=md5($_this->_CFG['wep']['md5'].$data[$key.'_old']))
						$error[] = 321;
					else
						$data[$key] = md5($_this->_CFG['wep']['md5'].$data[$key]);
				}
			} else {
				if(isset($form['mask']['max']) && $form['mask']['max']>0 && _strlen($data[$key])>$form['mask']['max'])
					$error[] = 2;
				if(isset($form['mask']['min']) and $form['mask']['min']>0)
				{
					if(!$data[$key] or $data[$key]=='0')
						$error[] = 1;
					elseif(_strlen($data[$key])<$form['mask']['min'])
						$error[] = 21;
				}
				$data[$key] = md5($_this->_CFG['wep']['md5'].$data[$key]);
			}
			return true;
		}

		if(!isset($data[$key])) {
			if(isset($form['mask']['min']) and $form['mask']['min'])
				$error[] = 1;
			elseif(isset($form['mask']['minint']) and $form['mask']['minint'])
				$error[] = 1;
			return true;
		}

		//print_r('<pre>');print_r($form);print_r('</pre>');print_r($data[$key]);
		
		/*Если тип данных ДАТА*/
		if($form['type']=='date') 
		{
			$data[$key] = self::_get_fdate($data[$key], $form['mask']['format'], $FIELDS['type']);
		}

		/*Редактор*/
		elseif($form['type']=='ckedit')
		{
			$data[$key] =stripslashes($data[$key]);
			if(!isset($form['paramedit']['allowBody']) or !$form['paramedit']['allowBody']) {
				// TODO - костыль
				$p1 = strpos($data[$key],'<body>');
				if($p1!==false) {
					$data[$key] = substr($data[$key],$p1+6);
					$data[$key] = substr($data[$key],0,strpos($data[$key],'</body>'));
				}
			}
		}

		/*Целое число*/
		elseif($form['type']=='int' and (!isset($form['mask']['toint']) or !$form['mask']['toint'])) 
			$data[$key]= preg_replace('/[^0-9\-]+/','',$data[$key]);

		/*Список*/
		elseif(($form['type']=='list' or $form['type']=='ajaxlist'))
		{
			if($data[$key] and $_this->_checkList($form['listname'],$data[$key])===false)
				$error[] = 33;
		}



		/*Преоразуем HTML сущности*/
		if(isset($form['mask']['entities']) and $form['mask']['entities']==1) 
			$data[$key]= htmlspecialchars($data[$key],ENT_QUOTES,$_this->_CFG['wep']['charset']);

		/*Замена по регулярному выражению*/
		if(isset($form['mask']['replace']))
		{
			if(!isset($form['mask']['replaceto']))
				$form['mask']['replaceto']='';
			$data[$key] = preg_replace($form['mask']['replace'],$form['mask']['replaceto'],$data[$key]);
		}

		/*Убираем теги*/
		if(isset($form['mask']['striptags'])) 
		{
			if($form['mask']['striptags']=='all') 
				$data[$key] = strip_tags($data[$key]);
			elseif($form['mask']['striptags']=='') 
				$data[$key] = strip_tags($data[$key],'<table><td><tr><p><span><center><div><a><b><strong><em><u><i><ul><ol><li><br>');
			else
				$data[$key]=strip_tags($data[$key],$form['mask']['striptags']);
		}

		/*Проверка по регуляркам*/
		$preg_mask = '';
		if(isset($form['mask']['patterns']))
			$preg_mask = $form['mask']['patterns'];
		elseif(isset($form['mask']['name']) and isset($MASK[$form['mask']['name']]))
			$preg_mask = $MASK[$form['mask']['name']];
		elseif(isset($MASK[$form['type']]))
			$preg_mask = $MASK[$form['type']];
	
		if($preg_mask AND $data[$key]) {
			$nomatch = '';
			$data[$key] = trim($data[$key]);
			if(is_array($preg_mask)) {
				$value = $data[$key];
				if(isset($preg_mask['eval'])) {
					eval('$data[$key] = '.$preg_mask['eval'].';');
				}
				if(isset($preg_mask['match'])) {
					$matches = preg_match_all($preg_mask['match'],$data[$key],$temp);
					if(!$matches) {
						$error[$key.'mask'] = 3;
					}
				}
				if(isset($preg_mask['nomatch']))
					$nomatch = $preg_mask['nomatch'];
				if(isset($preg_mask['comment']))
					$form['comment'] .= $preg_mask['comment'];
			} else
				$nomatch = $preg_mask;

			/*CHECK MASK*/
			if(isset($form['mask']['name']) and ($form['mask']['name']=='phone' or $form['mask']['name']=='phone2')) {
				if($data[$key]) {
					$data[$key] = self::_phoneReplace($data[$key]);
					if(!$data[$key]) {
						$error[$key.'mask'] = 3;
						$textm = 'Не корректный номер телефона.';
					}
				}
			}
			elseif($nomatch) {
				if(isset($data[$key.'_rplf'])) {
					$data[$key] = preg_replace($nomatch,'',$data[$key]);
					unset($error[$key.'mask']);
				}
				$matches = preg_match_all($nomatch,$data[$key],$form['matches_err'],PREG_OFFSET_CAPTURE);
				if($matches) {
					$error[$key.'mask'] = 3;
				}
				elseif(isset($form['mask']['checkwww']) and $form['mask']['checkwww'] and !fopen ('http://'.str_replace('http://','',$data[$key]), 'r'))
					$error[] = 4;
			}

		}

		if(isset($form['mask']['max']) && $form['mask']['max']>0)
		{
			if(_strlen($data[$key])>$form['mask']['max'])
				$error[] = 2;
		}
		if(isset($form['mask']['min']) and $form['mask']['min']>0)
		{
			if(!$data[$key] or $data[$key]=='0')
				$error[] = 1;
			elseif(_strlen($data[$key])<$form['mask']['min'])
				$error[] = 21;
		}

		if(isset($form['mask']['maxint']) && $form['mask']['maxint']>0 && (int)$data[$key]>$form['mask']['maxint'])
		{
			$error[] = 22;
			if($form['type']=='date' and $FIELDS['type']=='int') {
				$form['mask']['maxint'] = date($form['mask']['format'],$form['mask']['maxint']);
			}
		}
		if(isset($form['mask']['minint']) && $form['mask']['minint']>0 && (int)$data[$key]<$form['mask']['minint'])
		{
			$error[] = 23;
			if($form['type']=='date' and $FIELDS['type']=='int') {
				$form['mask']['minint'] = date($form['mask']['format'],$form['mask']['minint']);
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
	static function trimArray($var) {
		if($var=='') return false;
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
		$phone_1 = preg_replace("/[^0-9\,\;]+/",'',$phone);
		$phone_1 = preg_split("/[\,\;]+/",$phone_1, -1, PREG_SPLIT_NO_EMPTY);
		foreach($phone_1 as $k=>$p)
		{
			$phone_2[] = preg_replace(array(
				"/^([8])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
				"/^([7])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
				"/^([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
				"/^([0-9]{3})([0-9]{2})([0-9]{2})$/",
				),array(
				'+7 \\2 \\3-\\4-\\5',
				'+7 \\2 \\3-\\4-\\5',
				'+7 \\1 \\2-\\3-\\4',
				'\\1-\\2-\\3',
				),	$p);
		}
		$phone_2 = implode(', ',$phone_2);
		return $phone_2;
	}


	/**
	 * Собирает массив даты в массив для mktime
	 * @param array $arrdate - Дата
	 * @return string - Дата
	 */
	static function _parseDate($arrdate) {
		$date_str = array();
		// час
		if (isset($arrdate['H']) and $arrdate['H']) {
			$date_str[0] = $arrdate['H'];
		} else {
			$date_str[0] = '0';
		}
		// минуты
		if (isset($arrdate['i']) and $arrdate['i']) {
			$date_str[1] = $arrdate['i'];
		} else {
			$date_str[1] = '0';
		}
		// секунды
		if (isset($arrdate['s']) and $arrdate['s']) {
			$date_str[2] = $arrdate['s'];
		} else {
			$date_str[2] = '0';
		}

		// месяц
		if ($arrdate['m']) {
			$date_str[3] = $arrdate['m'];
		} else {
			$date_str[3] = '0';
		}
		// день
		if ($arrdate['d']) {
			$date_str[4] = $arrdate['d'];
		} else {
			$date_str[4] = '0';
		}
		//год
		if ($arrdate['Y']) {
			$date_str[5] = $arrdate['Y'];
		} else {
			$date_str[5] = '0';
		}
		return $date_str;
	}

	/**
	 * возвращает форматированную дату в зависимости от типа поля в fields_form, для добавления записи в БД
	 * @param string[array,int] $inp_date - Дата в различных форматах
	 * @param string $format - ФОрмат даты
	 * @param string $field_type - тип в БД (int,timestamp)
	 * @return string[array,int] date
	 */
	static function _get_fdate($inp_date, $format, $field_type) {
		$result = NULL;

		if (!$inp_date) {
			return $result;
		}
		preg_match_all('/[A-Za-z]+/', $format, $matches);
		$format = $matches[0];

		if (!is_array($inp_date)) {
			preg_match_all('/[0-9]+/', $inp_date, $matches);
			$inp_date = $matches[0];
		}
		$cf = count($format);
		$ci = count($inp_date);
		if($ci==1) {
			if ($field_type == 'int')
				return $inp_date[0];
			$date_str = explode('-',date('H-i-s-m-d-Y',$inp_date[0]));//1998-08-24 13:00:00
		} else {
			if ($cf > $ci) //если расхождения в массиве данных
				$inp_date = array_pad($inp_date,$cf,0);
			elseif($cf < $ci)
				$inp_date = array_slice($inp_date,0,$cf);

			$final_array_date = array_combine($format, $inp_date);
			$date_str = self::_parseDate($final_array_date);
		}

		if ($field_type == 'int') {
			$result = mktime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4], $date_str[5]);
		} elseif ($field_type == 'timestamp') {
			$result = $date_str[5].'-'.$date_str[3].'-'.$date_str[4].' '.$date_str[0].':'.$date_str[1].':'.$date_str[2];
		}

		return $result;
	}

	/**
	 * Записывает в куки закодированный КОД (рандомный), для отображения его на рисунке /capcha.php
	 * Использует для кодирования OpenSsl или MCrypt, в качестве ключа используется Хэш фаил $_CFG['_FILE']['HASH_KEY']
	 */
	static function setCaptcha($len=5,$def=0) {
		global $_CFG;

		if(!$def) {
			$word = rand(10000, 99999); //Рандомный код 5ти-значный
		}
		else {
			$A = array(
				1=>array(0,1,2,3,4,5,6,7,8,9,'А','Б','В','Г','Д','Е','Ж','З','И','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Э','Ю','Я'),
				2=>array(0,1,2,3,4,5,6,7,8,9,'а','б','в','г','д','е','ж','з','и','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','э','ю','я'),
				3=>array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'),
				4=>array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'),
				5=>array(0,1,2,3,4,5,6,7,8,9,'А','Б','В','Г','Д','Е','Ж','З','И','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Э','Ю','Я','а','б','в','г','д','е','ж','з','и','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','э','ю','я'),
				6=>array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'),
				7=>array(0,1,2,3,4,5,6,7,8,9,'А','Б','В','Г','Д','Е','Ж','З','И','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Э','Ю','Я','а','б','в','г','д','е','ж','з','и','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','э','ю','я','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'),
			);
			$word = '';
			$C = count($A[$def])-1;
			for($i = 1; $i <= $len; $i++) {
				$word .= $A[$def][rand(0,$C)];
			}
		}

		$hash_key = file_get_contents($_CFG['_FILE']['HASH_KEY']).$_SERVER['REMOTE_ADDR'];
		$hash_key = md5($hash_key); // получаем хешкод
		if(function_exists('openssl_encrypt')) { // если есть openssl
			$crypttext = openssl_encrypt($word,'aes-128-cbc',$hash_key,false,"1234567812345678");
		}
		elseif(function_exists('mcrypt_encrypt')) { // будем надеяться что есть mcrypt
			//$ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			//$iv = mcrypt_create_iv($ivsize, MCRYPT_RAND);
			$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $hash_key, $word, MCRYPT_MODE_ECB);
			$crypttext = base64encode($crypttext);
		} 
		else // если нет даже openssl значит и так сойдёт!
			$crypttext = $word;
		// Запись в куки зашифрованного кода
		_setcookie('chash', $crypttext, (time() + 1800));
		// Где хранится хэшкод (фаил доступен только на сервере)
		_setcookie('pkey', base64encode($_CFG['_FILE']['HASH_KEY']), (time() + 1800));
	}
	
	/**
	 * Получить КОД расшифрованный из куки
	 * @return int
	 */
	static function getCaptcha() {
		global $_CFG;
		if (isset($_COOKIE['chash']) and $_COOKIE['chash']) {
			$hash_key = file_get_contents($_CFG['_FILE']['HASH_KEY']).$_SERVER['REMOTE_ADDR'];
			$hash_key = md5($hash_key);
			if(function_exists('openssl_encrypt')) {
				$word = openssl_decrypt($_COOKIE['chash'],'aes-128-cbc',$hash_key,false,"1234567812345678");
			} elseif(function_exists('mcrypt_encrypt')) {
				$word = base64decode($_COOKIE['chash']);
				$word = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $hash_key, base64decode($_COOKIE['chash']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
				$word = trim($word);
			}else
				$word = $_COOKIE['chash'];
			return $word;
		}
		return rand(145, 357); // Если ничего в куках нет, то генерим рандомный и пользователь по новой должен вводит капчу
	}

	function spellCheck($text) {
		// Проверка знаков препинания

		// Проверка орфографии

		return $text;
	}

}
