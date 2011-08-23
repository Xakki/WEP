<?

class static_form {
/*------------- ADD ADD ADD ADD ADD ------------------*/

	// in:  id			opt
	//		fld_data:assoc array <fieldname>=><value> 	req
	//		att_data:assoc array <fieldname>=>array 	req
	//		mmo_data:assoc array <fieldname>=>text	req
	// out: 0 - success,
	//      otherwise errorcode

	static function _add(&$_this,$flag_select=true) {
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
		if ($_this->mf_timecr) 
			$_this->fld_data['mf_timecr'] = $_this->_CFG['time'];
		if ($_this->mf_timeup) 
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if($_this->mf_ipcreate) {
			$_this->fld_data['mf_ipcreate'] = sprintf("%u",ip2long($_SERVER['REMOTE_ADDR']));
			//$_this->fld_data['mf_ipcreate'] = 'inet_aton("'.$_SERVER['REMOTE_ADDR'].'")';
			if(!(int)$_this->fld_data['mf_ipcreate'])
				trigger_error('ERROR REMOTE_ADDR `'.$_SERVER['REMOTE_ADDR'].'`. '.print_r($_POST,true), E_USER_WARNING);
		}
		if($_this->mf_createrid and isset($_SESSION['user']['id']) and (!isset($_this->fld_data[$_this->mf_createrid]) or $_this->fld_data[$_this->mf_createrid]!='') )
			$_this->fld_data[$_this->mf_createrid]= $_SESSION['user']['id'];

		if (!isset($_this->fld_data) && !count($_this->fld_data))
			return static_main::_message('error',$_this->getMess('add_empty'));

		if (!self::_add_fields($_this)) return false;

		// get last id if not used nick
		if (!$_this->mf_use_charid && !isset($_this->fld_data['id']))
			$_this->id = (int)$_this->SQL->sql_id();
		elseif($_this->fld_data['id'])
			$_this->id = $_this->fld_data['id'];
		else $_this->id = NULL;

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
		return static_main::_message('ok',$_this->getMess('add'));
	}

	static function _add_fields(&$_this) {
		$int_type = array(
			'int'=>1,
			'tinyint'=>1,
			'longint'=>1,
			'shortint'=>1,
		);
		if (!count($_this->fld_data)) return false;
		// inserting
		$data = array();
		foreach($_this->fld_data as $key => $value) {
			if(is_array($value))
				 $value = preg_replace('/\|+/', '|', '|'.implode('|',$value).'|');
			if($_this->fields[$key]['type']=='bool')
				 $value = ((int)$value == 1 ? 1 : 0);
			elseif(isset($int_type[$_this->fields[$key]['type']]))
				 $value =  preg_replace('/[^0-9\-]/', '', $value);
			$data[$key] = $value;
		}
		$result=$_this->SQL->execSQL('INSERT INTO `'.$_this->tablename.'` (`'.implode('`,`', array_keys($data)).'`) VALUES (\''.implode('\',\'', $data).'\')');
		if($result->err) return false;
		return true;
	}

	static function _add_attaches(&$_this) {
		if (!count($_this->attaches) or !count($_this->att_data)) return true;
		$result=$_this->SQL->execSQL('SELECT id, '.implode(',', array_keys($_this->attaches)).' FROM `'.$_this->tablename.'` WHERE id IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		$row = $result->fetch_array();
		$prop = array();
		
		foreach($_this->att_data as $key => $value) 
		{
			if (!is_array($value) or $value['tmp_name'] == 'none' or $value['tmp_name'] == '') continue;
			$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
			// delete old
			$oldname =$pathimg.'/'. $_this->id. '.'.$row[$key];
			if (file_exists($oldname)) {
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
			if ($value['tmp_name'] == ':delete:') continue;

			$ext = $_this->attaches[$key]['mime'][$value['type']];
			$newname = $pathimg.'/'.$_this->id.'.'.$ext;
			if (file_exists($newname)) {
				chmod($newname, $_this->_CFG['wep']['chmod']);
				unlink($newname);
			}
			chmod($value['tmp_name'], $_this->_CFG['wep']['chmod']);
			if (!rename($value['tmp_name'], $newname))
				return static_main::_message('error','Error copy file '.$value['name']);

			if (isset($_this->attaches[$key]['thumb'])) {
				if(!exif_imagetype($newname)) // опред тип файла
					return static_main::_message('error','File '.$newname.' is not image');
				$prefix = $pathimg.'/';
				if (count($_this->attaches[$key]['thumb']))
					foreach($_this->attaches[$key]['thumb'] as $imod) {
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
			if(!isset($_this->memos[$key])) return static_main::_message('error','Error add memo. Missing field `'.$key.'` in module `'.$_this->caption.'`');
			$name = $pathimg.'/'.$_this->id.$_this->text_ext;
			$f = fopen($name, 'w');
				if (!$f)
					return static_main::_message('error','Can`t create file '.$name);
				if (fwrite($f, $value) == -1)
					return static_main::_message('error','Can`t write data into file '.$name);
				if (!fclose($f))
					return static_main::_message('error','Can`t close file '. $name);
			global $_CFG;
			chmod($name, $_CFG['wep']['chmod']);
			static_main::_message('notice','File '.$name.' writed.');
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

	static function _update(&$_this,$flag_select=true) {
		if ($_this->mf_istree and !is_array($_this->id)) {
			if ($_this->fld_data[$_this->mf_istree]==$_this->id)
				return static_main::_message('error','Child `'.$_this->caption.'` can`t be owner to self ');
		}
		if($_this->mf_timeup)
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if($_this->mf_timeoff and !isset($_this->fld_data['mf_timeoff']) and isset($_this->fld_data[$_this->mf_actctrl]) and !$_this->fld_data[$_this->mf_actctrl] and $_this->data[$_this->id][$_this->mf_actctrl]) 
			$_this->fld_data['mf_timeoff'] = $_this->_CFG['time'];
		//if($_this->mf_ipcreate) 
		//	$_this->fld_data['mf_ipcreate'] = ip2long($_SERVER['REMOTE_ADDR']);
		// rename attaches & memos
		if (!is_array($_this->id) and isset($_this->fld_data['id']) && $_this->fld_data['id'] != $_this->id) {
			if (!self::_rename_parent_childs($_this)) return false;
			if (!self::_rename_childs($_this)) return false;
			if (!self::_rename_attaches($_this)) return false;
			if (!self::_rename_memos($_this)) return false;
		}
		if (!self::_update_fields($_this)) return false;
		if (isset($_this->fld_data['id']))
			$_this->id = $_this->fld_data['id'];
		//umask($_this->_CFG['wep']['chmod']);
		if (!self::_update_attaches($_this)) return false;
		if (!self::_update_memos($_this)) return false;
		if($_this->id and $flag_select)
			$_this->data = $_this->_select();
		if (isset($_this->mf_indexing) && $_this->mf_indexing) $_this->indexing();
		return static_main::_message('ok','Chenge data in `'.$_this->tablename.'` successful!');
	}


	static function _update_fields(&$_this) {
		if (!count($_this->fld_data)) return true;
		// preparing
		$data = array();
		foreach($_this->fld_data as $key => $value) {
			if(is_array($value)) {
				$data[$key] = '`'.$key.'` = \''.preg_replace('/\|+/', '|', '|'.implode('|',$value).'|').'\'';
			}
			else
				$data[$key] = '`'.$key.'` = \''.$value.'\'';
		}
		
		$result = $_this->SQL->execSQL('UPDATE `'.$_this->tablename.'` SET '.implode(',', $data).' WHERE id IN ('.$_this->_id_as_string().')');
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
		$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
		$result=$_this->SQL->execSQL('SELECT `id`, `'.implode('`,`', array_keys($_this->attaches)).'` FROM `'.$_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		$row = $result->fetch_array();
		if ($row) {
			foreach($_this->attaches as $key => $value) {
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
		$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForMemo($key);
		foreach($_this->memos as $key => $value) {
			$f = $pathimg.'/'.$_this->id.$_this->text_ext;
			if (file_exists($f)) rename($f, $pathimg.'/'.$_this->fld_data['id'].$_this->text_ext);
		}
		return true;
	}

	static function _update_memos(&$_this) {
		return self::_add_memos($_this);
	}


/*------------- DELETE DELETE DELETE -----------------*/

	// in:  id											req
	// out: 0 - success,
	//      otherwise errorcode

	static function _delete(&$_this) {
		if (!is_array($_this->id)) $_this->id = array($_this->id);
		if (!count($_this->id)) return true;
		// delete childs of owner
		if (count($_this->childs)) foreach($_this->childs as &$child){
			$child->id = $_this->id;
			if (!self::_delete_ownered($child)) return false;
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
		return static_main::_message('notice','Delete data from `'.$_this->caption.'` successful.');
	}

	static function _delete_ownered(&$_this) {
		// select record ids to delete
		$result=$_this->SQL->execSQL('SELECT id FROM `'.$_this->tablename.'` WHERE `'.$_this->owner_name.'` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		// create list
		$_this->id = array();
		while (list($id) = $result->fetch_array(MYSQL_NUM)) 
			$_this->id[] = $id;
		// if list not empty
		if (count($_this->id)) $_this->_delete();
		return true;
	}

	static function _delete_parented(&$_this) {
		// select record ids to delete
		$result=$_this->SQL->execSQL('SELECT id FROM `'.$_this->tablename.'` WHERE `parent_id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;

		// create list
		$_this->id = array();
		while (list($id) = $result->fetch_array(MYSQL_NUM)) $_this->id[] = $id;

		// if list not empty
		if (count($_this->id)) $_this->_delete();
		return true;
	}

	static function _delete_fields(&$_this) {
		// delete records
		$result=$_this->SQL->execSQL('DELETE FROM `'.$_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		return true;
	}

	static function _delete_attaches(&$_this) {
		if (!count($_this->attaches)) return true;
		$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
		$result=$_this->SQL->execSQL('SELECT `id`, `'.implode('`,`', array_keys($_this->attaches)).'` FROM `'. $_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;

		while ($row = $result->fetch_array()) {
			foreach($_this->attaches as $key => $value) {
			
				$f = $pathimg.'/'. $row['id']. '.'. $row[$key];
				if (file_exists($f)) {
					if (!unlink($f)) return static_main::_message('error','Cannot delete file `'.$f.'`');
				}
			}
		}
		return true;
	}

	static function _delete_memos(&$_this) {
		if (!count($_this->memos)) return true;
		$pathimg = $_this->getPathForMemo($key);
		foreach($_this->id as $id) {
			foreach($_this->memos as $key => $value) {
				$f = $pathimg.'/'.$id.$_this->text_ext;
				if (file_exists($f))
					if (!unlink($f)) return $_this->_error('Cannot delete memo `'.$f.'`',1);
			}
		}
		return true;
	}

/************************* IMAGE *****************************/
	static function _waterMark(&$_this,$InFile, $OutFile,$logoFile='',$posX=0,$posY=0)
	{
		if(!$logoFile)
			$logoFile = $_this->_CFG['_imgwater'];
		$logoFile = $_this->_CFG['_PATH']['path'].$logoFile;

		if(!$imtypeIn = exif_imagetype($InFile))// опред тип файла
			return static_main::_message('error','File '.$InFile.' is not image');
		if($imtypeIn>3) return false;

		if(!$imtypeLogo = exif_imagetype($logoFile))// опред тип файла
			return static_main::_message('error','File '.$logoFile.' is not image');
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
			static_main::_message('error','Cant composite file on '.__LINE__.' in kernel');
			return false;
		}
		return true;
	}

	static function _resizeImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		chmod($InFile, $_CFG['wep']['chmod']);
		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер

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
		if(!$imtype = exif_imagetype($InFile))// опред тип файла
			return static_main::_message('error','File '.$InFile.' is not image');
		if($imtype>3) return true;
		$source = self::_imagecreatefrom($_this,$InFile,$imtype);//открываем рисунок
		imagecopyresized($thumb, $source, 0, 0, 0, 0, $WidthX, $HeightY, $width_orig, $height_orig);//меняем размер
		self::_image_to_file($thumb, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return static_main::_message('error','Cant create file');
		return true;
	}

	static function _cropImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		chmod($InFile, $_CFG['wep']['chmod']);
		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер
		// Resample
		$thumb = imagecreatetruecolor($WidthX, $HeightY);//созд пустой рисунок
		if(!$imtype = exif_imagetype($InFile)) // опред тип файла
			return static_main::_message('error','File is not image');
		if($imtype>3) return true;
		$source = self::_imagecreatefrom($_this,$InFile,$imtype);//открываем рисунок
		imagecopyresampled($thumb, $source, 0, 0, $width_orig/2-$WidthX/2, $height_orig/2-$HeightY/2, $WidthX, $HeightY, $WidthX, $HeightY);
		self::_image_to_file($thumb, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return static_main::_message('error','Cant create img file ');
		return true;
	}

	static function _resizecropImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		$trueX=$WidthX;$trueY=$HeightY;
		chmod($InFile, $_CFG['wep']['chmod']);
		list($width_orig, $height_orig) = getimagesize($InFile);

		$ratio_orig = $width_orig/$height_orig;
		if ($WidthX/$HeightY > $ratio_orig) {
		   $HeightY = $WidthX/$ratio_orig;
		} else {
		   $WidthX = $HeightY*$ratio_orig;
		}
		/*Создаем пустое изображение на вывод*/
		if(!($thumb = @imagecreatetruecolor($WidthX, $HeightY)))
			return static_main::_message('error','Cannot Initialize new GD image stream');
		/*Определяем тип рисунка*/
		if(!$imtype = exif_imagetype($InFile))// опред тип файла
			return static_main::_message('error','File is not image');
		/*Обработка только jpeg, gif, png*/
		if($imtype>3) return true;
		/*Открываем исходный рисунок*/
		if(!$source = self::_imagecreatefrom($_this,$InFile,$imtype))//открываем рисунок
			return static_main::_message('error','File '.$InFile.' is not image');
		if(!imagecopyresampled($thumb, $source, 0, 0, 0, 0, $WidthX, $HeightY, $width_orig, $height_orig))
			return static_main::_message('error','Error imagecopyresampled');
		if(!($thumb2 = @imagecreatetruecolor($trueX, $trueY)))
			return static_main::_message('error','Cannot Initialize new GD image stream');
		if(!imagecopyresampled($thumb2, $thumb, 0, 0, $WidthX/2-$trueX/2, $HeightY/2-$trueY/2, $trueX, $trueY, $trueX, $trueY)) 
			return static_main::_message('error','Error imagecopyresampled');
		self::_image_to_file($thumb2, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return static_main::_message('error','Cant create file');
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
				static_main::_message('error','Can not create a new image from file');
		}
		elseif($imtype==2) {
			if(!($image=imagecreatefromjpeg($im_file)))
				static_main::_message('error','Can not create a new image from file');
		}
		elseif($imtype==3) {
			if(!($image=imagecreatefrompng($im_file)))
				static_main::_message('error','Can not create a new image from file');
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



/******************* FORM *****************/

	static function _fFormCheck(&$_this,&$data,&$param,&$FORMS_FIELDS) { //$_this->fields_form
		global $_tpl;
		if(!count($FORMS_FIELDS))
			return array('mess'=>array(array('name'=>'error', 'value'=>$_this->_CFG['_MESS']['errdata'])));
		$MASK = &$_this->_CFG['_MASK'];
		$arr_nochek = array('info'=>1,'sbmt'=>1,'alert'=>1);
		$messages='';
		$arr_err_name=array();
		$textm = '';
		foreach($FORMS_FIELDS as $key=>&$form)
		{
			$error = array();
			if($key=='_*features*_') continue;
			if(!isset($form['type'])) return array('mess'=>array(array('name'=>'error', 'value'=>$_this->_CFG['_MESS']['errdata'].' : '.$key)));
			if(isset($arr_nochek[$form['type']])) continue;

			/*Поля которые недоступны пользователю не проверяем, дефолтные значения прописываются в kPreFields()*/
			if((isset($form['readonly']) and $form['readonly']) or 
				(isset($form['mask']['fview']) and $form['mask']['fview']==2) or 
				(isset($form['mask']['usercheck']) and !static_main::_prmGroupCheck($form['mask']['usercheck']))) {
				//unset($value);
				continue;
			}

			$value = (isset($data[$key])?$data[$key]:'');
			//*********** Файлы
			if(isset($_FILES[$key]['name'])) {
				$tmp = count($error);
				if(isset($data[$key.'_del']) and (int)$data[$key.'_del']==1){
					$_FILES[$key]['name'] = ':delete:';
					$_FILES[$key]['tmp_name'] = ':delete:';
					$value = $_FILES[$key];
				}elseif(isset($form['mask']['min']) and $form['mask']['min'] and ($_FILES[$key]['name']=='' or $_FILES[$key]['name'] == ':delete:'))
						$error[] = 1;
				elseif($_FILES[$key]['name']!='') {
					if(!isset($form['mime'][$_FILES[$key]['type']]))
						$error[]=39;
					if(isset($form['maxsize']) and $_FILES[$key]['size']>($form['maxsize']*1024))
						$error[]=29;
					if($tmp == count($error)){
						static_tools::_checkdir($_this->_CFG['_PATH']['temp']);
						$temp = $_this->_CFG['_PATH']['temp'].substr(md5(getmicrotime()),16).'.'.$form['mime'][$_FILES[$key]['type']];
						static_tools::_checkdir($_this->_CFG['_PATH']['temp']);
						if (move_uploaded_file($_FILES[$key]['tmp_name'], $temp)){
							$_FILES[$key]['tmp_name']= $temp;
							$value = $_FILES[$key];
						}else
							$error[]=40;
					}					
				}
				$data[$key] = $value;
			}
			elseif (isset($_POST[$key . '_temp_upload']) && is_array($_POST[$key . '_temp_upload'])) {
				$value = $_POST[$key . '_temp_upload'];	
				$value['tmp_name'] = $_this->_CFG['_PATH']['temp'] . $value['name'];
				$data[$key] = $value;
			}						
			//*********** CHECKBOX
			elseif($form['type']=='checkbox') {
				$value = ($value? 1 : 0);
			}
			//*********** МАССИВЫ
			elseif(is_array($value) and count($value)) {
/*Доработать*/
				if(isset($form['mask']['max'])){
					if(count($value)>$form['mask']['max'])
						$error[] = 24;
				}
				if(isset($form['mask']['min'])){
					if(count($value)<$form['mask']['min'])
						$error[] = 25;
				}
			}else{
				$value = trim($value);				
			//********** ОСТАЛЬНЫЕ
				if($value!='') {
					$value;
					if(isset($form['mask']['entities']) and $form['mask']['entities']==1) 
						$value= htmlspecialchars($value,ENT_QUOTES,$_this->_CFG['wep']['charset']);
					if(isset($form['mask']['replace'])) {
						if(!isset($form['mask']['replaceto']))
							$form['mask']['replaceto']='';
						$value = preg_replace($form['mask']['replace'],$form['mask']['replaceto'],$value);
					}
					if(isset($form['mask']['striptags'])) {
						if($form['mask']['striptags']=='all') 
							$value = strip_tags($value);
						elseif($form['mask']['striptags']=='') 
							$value = strip_tags($value,'<table><td><tr><p><span><center><div><a><b><strong><em><u><i><ul><ol><li><br>');
						else
							$value=strip_tags($value,$form['mask']['striptags']);
					}
					//$value
					//$value

					/*CHECK TYPE*/
					if($form['type']=='ckedit'){
						$value =stripslashes($value);
						if(!isset($form['paramedit']['allowBody']) or !$form['paramedit']['allowBody']) {
							$p1 = strpos($value,'<body>');
							if($p1!==false) {
								$value = substr($value,$p1+6);
								$value = substr($value,0,strpos($value,'</body>'));
							}
						}
						$value;
					}
					elseif($form['type']=='int' and (!isset($form['mask']['toint']) or !$form['mask']['toint'])) 
						$value= (int)$value;
					elseif($form['type']=='captcha' && $value!=$form['captcha']) {
						$error[] = 31;
					}
					elseif($form['type']=='password')
					{
						if($value!=$data['re_'.$key])
							$error[] = 32;
					}
					elseif(($form['type']=='list' or $form['type']=='ajaxlist'))
					{
						if($value and $_this->_checkList($form['listname'],$value)===false)
							$error[] = 33;
					}
					elseif($form['type']=='date') {
						$value = self::_get_fdate($value, $form['mask']['format'], $_this->fields[$key]['type']);
					}

					$preg_mask = '';
					if(isset($form['mask']['patterns']))
						$preg_mask = $form['mask']['patterns'];
					elseif(isset($form['mask']['name']) and isset($MASK[$form['mask']['name']]))
						$preg_mask = $MASK[$form['mask']['name']];
					elseif(isset($MASK[$form['type']]))
						$preg_mask = $MASK[$form['type']];

					if($preg_mask AND $value) {
						$nomatch = '';
						if(is_array($preg_mask)) {
							if(isset($preg_mask['eval']))
								eval('$value = '.$preg_mask['eval'].';');
							if(isset($preg_mask['match'])) {
								$matches = preg_match_all($preg_mask['match'],$value,$matches2,PREG_OFFSET_CAPTURE);
								if(!$matches) {
									$error[$k.'mask'] = 3;
								}
							}
							if(isset($preg_mask['nomatch']))
								$nomatch = $preg_mask['nomatch'];
							if(isset($preg_mask['comment']))
								$FORMS_FIELDS[$key]['comment'] .= $preg_mask['comment'];
						} else
							$nomatch = $preg_mask;

						/*CHECK MASK*/
						if(isset($form['mask']['name']) and ($form['mask']['name']=='phone' or $form['mask']['name']=='phone2')) {
							if($value) {
								$value = self::_phoneReplace($value);
								if(!$value) {
									$error[$k.'mask'] = 3;
									$textm = 'Не корректный номер телефона.';
								}
							}
						}
						elseif($nomatch) {
							if(isset($data[$key.'_rplf'])) {
								$value = preg_replace($nomatch,'',$value);
								unset($error[$k.'mask']);
							}
							$matches = preg_match_all($nomatch,$value,$matches2,PREG_OFFSET_CAPTURE);
							if($matches) {
								$error[$k.'mask'] = 3;
							}
							elseif(isset($form['mask']['checkwww']) and $form['mask']['checkwww'] and !fopen ('http://'.str_replace('http://','',$value), 'r'))
								$error[] = 4;
						}

					}

					/*CHECK LEN*/
					if(isset($form['mask']['max']) && $form['mask']['max']>0)
					{
						if(_strlen($value)>$form['mask']['max'])
							$error[] = 2;
					}
					if(isset($form['mask']['min']) and $form['mask']['min']>0)
					{
						if($form['mask']['min']>0 and (!$value or $value=='0'))
							$error[] = 1;
						elseif(_strlen($value)<$form['mask']['min'])
							$error[] = 21;
					}
					if(isset($form['mask']['maxint']) && $form['mask']['maxint']>0 && (int)$value>$form['mask']['maxint']) {
						$error[] = 22;
						if($form['type']=='date' and $_this->fields[$key]['type']=='int') {
							$form['mask']['maxint'] = date($form['mask']['format'],$form['mask']['maxint']);
						}
					}
					if(isset($form['mask']['minint']) && $form['mask']['minint']>0 && (int)$value<$form['mask']['minint']) {
						$error[] = 23;
						if($form['type']=='date' and $_this->fields[$key]['type']=='int') {
							$form['mask']['minint'] = date($form['mask']['format'],$form['mask']['minint']);
						}
					}

				}
				elseif(isset($form['mask']['min']) and $form['mask']['min'])
					$error[] = 1;
				elseif(isset($form['mask']['minint']) and $form['mask']['minint'])
					$error[] = 1;
			}

			$form['value'] = $value;
			if(isset($data[$key]))
				$data[$key] = $value;

///////////////////

				/*elseif(isset($form['mask']['name']) && $form['mask']['name']=='date')
				{
					if(is_array($value))
						foreach($value as $name=>$option)
						{
							if(!isset($form['mask']['patterns']) && !preg_match($MASK[$form['mask']['name']],$data[$name])) $error[] = 3;
							elseif(isset($form['mask']['patterns']) && !preg_match($form['mask']['patterns'],$data[$name])) $error[] = 3;
						}
				}*/

			foreach($error as $row) {
				$messages = '';
				if($row==1) //no empty
					$messages = $_this->getMess('_err_1',array($form['caption']));
				elseif($row==2) //max chars
					$messages = $_this->getMess('_err_2',array($form['caption'],$form['mask']['max'],(_strlen($value)-$form['mask']['max'])));
				elseif($row==21) // min chars
					$messages = $_this->getMess('_err_21',array($form['caption'],$form['mask']['min'],($form['mask']['min']-_strlen($value))));
				elseif($row==22) //min int
					$messages = $_this->getMess('_err_22',array($form['caption'])).$form['mask']['maxint'];
				elseif($row==23) //max int
					$messages = $_this->getMess('_err_23',array($form['caption'])).$form['mask']['minint'];
				elseif($row==24) // min chars
					$messages = $_this->getMess('_err_22',array($form['caption'])).$form['mask']['max'];
				elseif($row==25) // max chars
					$messages = $_this->getMess('_err_23',array($form['caption'])).$form['mask']['min'];
				elseif($row==29) //limit file size
					$messages = $_this->getMess('_err_29',array($form['caption'],$_FILES[$key]['name'])).$form['maxsize'].'Kb';
				elseif($row==3) {//wrong data
					if(isset($matches2) and count($matches2[0])) {
						$textm = 'Обнаружены следующие недопустимые символы - ';
						foreach($matches2[0] as $mk=>$mr) {
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
					$messages = $_this->getMess('_err_3',array($form['caption'],$textm));
				}
				elseif($row==31) //wrong captchs
					$messages = $_this->getMess('_err_31',array($form['caption']));
				elseif($row==32) // wrong repeat pass
					$messages = $_this->getMess('_err_32',array($form['caption']));
				elseif($row==33) //data error
					$messages = $_this->getMess('_err_33',array($form['caption']));
				elseif($row==39) //wrong file type
					$messages = $_this->getMess('_err_39',array($_FILES[$key]['name'])).'- '.implode(',',array_unique($form['mime'])).'.';
				elseif($row==40) //error load file
					$messages = $_this->getMess('_err_40',array($_FILES[$key]['name']));
				elseif($row==4)  // wrong link
					$messages = $_this->getMess('_err_4',array($form['caption']));
				$arr_err_name[$key]=$key;

				if(isset($param['ajax']) and $param['ajax'])
					$_tpl['onload'] .= 'putEMF(\''.$key.'\',\''.$messages.'\');'; // запись в форму по ссылке
				else
					$form['error'][] = $messages; // запись в форму по ссылке
			}

		}
		
		$messages = array();
		if(count($arr_err_name)>0) {
			$messages[] = array('name'=>'error', 'value'=>'Поля формы заполненны не верно.');
		}
		/*$_tpl['onload'] .'CKEDITOR.replace( \'editor1\',
						 {
							  toolbar : \'basic\',
							  uiColor : \'# 9AB8F3\'
						 });';*/
		return array('mess'=>$messages,'vars'=>$data);
	}

	static function _phoneReplace($phone)
	{
		$phone_2 = array();
		$phone_1 = preg_replace("/[^0-9\,]+/",'',$phone);
		$phone_1 = explode(',',$phone_1);
		foreach($phone_1 as $k=>$p)
		{
			$phone_2[] = preg_replace(array(
				"/^([8])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
				"/^([7])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
				"/^([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/",
				),array(
				'+7 \\2 \\3-\\4-\\5',
				'+7 \\2 \\3-\\4-\\5',
				'+7 \\1 \\2-\\3-\\4',
				),	$p);
		}
		$phone_2 = implode(', ',$phone_2);
		return $phone_2;
	}
	static function _usabilityDate($time, $format='Y-m-d H:i') {
		global $_CFG;
		$date = getdate($time);
		$de = $_CFG['time'] - $time;
		if ($de < 3600) {
			if ($de < 240) {
				if ($de < 60)
					$date = 'Минуту назад';
				else
					$date = ceil($de / 60) . ' минуты назад';
			}
			else
				$date = ceil($de / 60) . ' минут назад';
		}
		elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] == $date['yday'])
			$date = 'Сегодня ' . date('H:i', $time);
		elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] - $date['yday'] == 1)
			$date = 'Вчера ' . date('H:i', $time);
		else
			$date = date($format, $time);
		return $date;
	}

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

//возвращает форматированную дату в зависимости от типа поля в fields_form, для добавления записи в БД
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
		if ($cf > $ci) //если расхождения в массиве данных
			$inp_date = array_pad($inp_date,$cf,0);
		elseif($cf < $ci)
			$inp_date = array_slice($inp_date,0,$cf);

		$final_array_date = array_combine($format, $inp_date);
		$date_str = self::_parseDate($final_array_date);
		if ($field_type == 'int') {
			$result = mktime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4], $date_str[5]);
		} elseif ($field_type == 'timestamp') {
			$result = date("Y-m-d H:i:s", mktime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4], $date_str[5]));
		}

		return $result;
	}

	static function setCaptcha() {
		global $_CFG;
		$data = rand(10000, 99999); //$_SESSION['captcha']
		if ($_CFG['wep']['sessiontype'] == 1) {
			$hash_key = file_get_contents($_CFG['_PATH']['HASH_KEY']);
			$hash_key = md5($hash_key);
			$crypttext = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $hash_key, $data, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
			_setcookie('chash', $crypttext, (time() + 1800));
			_setcookie('pkey', base64_encode($_CFG['PATH']['HASH_KEY']), (time() + 1800));
		}
	}

	static function getCaptcha() {
		global $_CFG;
		if (isset($_COOKIE['chash']) and $_COOKIE['chash']) {
			$hash_key = file_get_contents($_CFG['_PATH']['HASH_KEY']);
			$hash_key = md5($hash_key);
			$data = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $hash_key, base64_decode($_COOKIE['chash']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
			return $data;
		}
		return rand(145, 357);
	}

}
