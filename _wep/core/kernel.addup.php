<?
/*------------- ADD ADD ADD ADD ADD ------------------*/

	// in:  id			opt
	//		fld_data:assoc array <fieldname>=><value> 	req
	//		att_data:assoc array <fieldname>=>array 	req
	//		mmo_data:assoc array <fieldname>=>text	req
	// out: 0 - success,
	//      otherwise errorcode

	function _add(&$_this) {
		// add ordind field
		if ($_this->mf_ordctrl and !isset($_this->fld_data['ordind'])) {
			if (!$_this->_get_new_ord()) return false;
			$_this->fld_data['ordind'] = $_this->ordind;
		}
		// add parent_id field
		if ($_this->mf_istree and $_this->parent_id and !$_this->fld_data['parent_id'])
			$_this->fld_data['parent_id'] = $_this->parent_id;
		// add owner_id field
		if (!$_this->fld_data[$_this->owner_name] and $_this->owner)
			$_this->fld_data[$_this->owner_name] = $_this->owner->id;
		if ($_this->mf_timecr) 
			$_this->fld_data['mf_timecr'] = $_this->_CFG['time'];
		if ($_this->mf_timeup) 
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if($_this->mf_ipcreate) {
			$_this->fld_data['mf_ipcreate'] = ip2long($_SERVER['REMOTE_ADDR']);
			if(!(int)$_this->fld_data['mf_ipcreate'])
				trigger_error('ERROR REMOTE_ADDR `'.$_SERVER['REMOTE_ADDR'].'`. '.print_r($_POST,true), E_USER_WARNING);
		}
		if($_this->mf_createrid and !$_this->fld_data[$_this->mf_createrid] and isset($_SESSION['user']['id']))
			$_this->fld_data[$_this->mf_createrid]= $_SESSION['user']['id'];

		if (!isset($_this->fld_data) && !count($_this->fld_data))
			return $_this->_message($_this->getMess('add_empty'),1);

		if (!_add_fields($_this)) return false;

		// get last id if not used nick
		if (!$_this->mf_use_charid && !isset($_this->fld_data['id']))
			$_this->id = (int)$_this->SQL->sql_id();
		elseif($_this->fld_data['id'])
			$_this->id = $_this->fld_data['id'];
		else $_this->id = NULL;

		if (isset($_this->att_data) && count($_this->att_data)) {
			if (!_add_attaches($_this)) {
				$_this->_delete();
				$_this->id = NULL;
				return false;
			}
		}
		if (isset($_this->mmo_data) && count($_this->mmo_data)) {
			if (!_add_memos($_this)) {
				$_this->_delete();
				$_this->id = NULL;
				return false;
			}
		}
		if (isset($_this->mf_indexing) && $_this->mf_indexing) $_this->indexing();
		return $_this->_message($_this->getMess('add'),3);
	}

	function _add_fields(&$_this) {
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
				 $value = '|'.implode('|',$value).'|';
			if($_this->fields[$key]['type']=='bool')
				 $value = ((int)$value == 1 ? 1 : 0);
			elseif(isset($int_type[$_this->fields[$key]['type']]))
				 $value =  (int)$value;
			$data[$key] = $value;
		}
		$result=$_this->SQL->execSQL('INSERT INTO `'.$_this->tablename.'` (`'.implode('`,`', array_keys($data)).'`) VALUES (\''.implode('\',\'', $data).'\')');
		if($result->err) return false;
		return true;
	}

	function _add_attaches(&$_this) {
		if (!count($_this->attaches) or !count($_this->att_data)) return true;
		$result=$_this->SQL->execSQL('SELECT id, '.implode(',', array_keys($_this->attaches)).' FROM `'.$_this->tablename.'` WHERE id IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		$row = $result->fetch_array();
		$prop = array();
		foreach($_this->att_data as $key => $value) 
		{
			if ($value['tmp_name'] == 'none' or $value['tmp_name'] == '') continue;
			$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
			// delete old
			$oldname =$pathimg.'/'. $_this->id. '.'.$row[$key];
			if (file_exists($oldname)) {
				unlink($oldname);
				$_this->_message('Old file '.$oldname.'deleted!',2);
			}
			if ($value['tmp_name'] == ':delete:') continue;

			$ext = $_this->attaches[$key]['mime'][$value['type']];
			$newname = $pathimg.'/'.$_this->id.'.'.$ext;
			if (file_exists($newname)) {
				unlink($newname);
			}

			if (!rename($value['tmp_name'], $newname))
				return $_this->_message('Error copy file '.$value['name'],1);

			if (isset($_this->attaches[$key]['thumb'])) {
				if(!exif_imagetype($newname)) // опред тип файла
					return $_this->_message('File '.$newname.' is not image',1);
				$prefix = $pathimg.'/';
				if (count($_this->attaches[$key]['thumb']))
					foreach($_this->attaches[$key]['thumb'] as $imod) {
						if(!$imod['pref']) $imod['pref'] = '';// по умолчинию без префикса
						$newname2 = $prefix.$imod['pref'].$_this->id.'.'.$ext;
						if($imod['path'])
							$newname2 = $_this->_CFG['_PATH']['path'].$imod['path'].'/'.$imod['pref'].$_this->id.'.'.$ext;
						if ($imod['type']=='crop')
							_cropImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
						elseif ($imod['type']=='resize')
							_resizeImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
						elseif ($imod['type']=='resizecrop')
							_resizecropImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
						elseif ($imod['type']=='water')
							_waterMark($_this,$newname,$newname2, $imod['w'], $imod['h']);
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

	function _add_memos(&$_this) {
		if (!count($_this->memos) or !count($_this->mmo_data)) return true;
		foreach($_this->mmo_data as $key => $value)
		{
			$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForMemo($key);
			if(!isset($_this->memos[$key])) return $_this->_message('Error add memo. Missing field `'.$key.'` in module `'.$_this->caption.'`');
			$name = $pathimg.'/'.$_this->id.$_this->text_ext;
			$f = fopen($name, 'w');
				if (!$f)
					return $_this->_message('Can`t create file '.$name,1);
				if (fwrite($f, $value) == -1)
					return $_this->_message('Can`t write data into file '.$name,1);
				if (!fclose($f))
					return $_this->_message('Can`t close file '. $name,1);
			chmod($name, 0644);
			$_this->_message('File '.$name.' writed.',3);
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

	function _update(&$_this) {
		if ($_this->mf_istree and !is_array($_this->id)) {
			if ($_this->fld_data['parent_id']==$_this->id)
				return $_this->_message('Child `'.$_this->caption.'` can`t be owner to self ',1);
		}
		if($_this->mf_timeup)
			$_this->fld_data['mf_timeup'] = $_this->_CFG['time'];
		if($_this->mf_timeoff and !isset($_this->fld_data['mf_timeoff']) and isset($_this->fld_data['active']) and !$_this->fld_data['active'] and $_this->data[$_this->id]['active']) 
			$_this->fld_data['mf_timeoff'] = $_this->_CFG['time'];
		//if($_this->mf_ipcreate) 
		//	$_this->fld_data['mf_ipcreate'] = ip2long($_SERVER['REMOTE_ADDR']);
		//if ($_this->_select()) return 1;
		// rename attaches & memos
		if (!is_array($_this->id) and isset($_this->fld_data['id']) && $_this->fld_data['id'] != $_this->id) {
			if (!_rename_parent_childs($_this)) return false;
			if (!_rename_childs($_this)) return false;
			if (!_rename_attaches($_this)) return false;
			if (!_rename_memos($_this)) return false;
		}
		if (!_update_fields($_this)) return false;
		if (isset($_this->fld_data['id'])) $_this->id = $_this->fld_data['id'];
		if (!_update_attaches($_this)) return false;
		if (!_update_memos($_this)) return false;
		if (isset($_this->mf_indexing) && $_this->mf_indexing) $_this->indexing();
		return $_this->_message('Chenge data in `'.$_this->tablename.'` successful!',3);
	}


	function _update_fields(&$_this) {
		if (!count($_this->fld_data)) return true;
		// preparing
		$data = array();
		foreach($_this->fld_data as $key => $value) {
			if(is_array($value))
				$data[$key] = '`'.$key.'` = \'|'.implode('|',$value).'|\'';
			else
				$data[$key] = '`'.$key.'` = \''.$value.'\'';
		}
		$result = $_this->SQL->execSQL('UPDATE `'.$_this->tablename.'` SET '.implode(',', $data).' WHERE id IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		if(isset($_this->fld_data[$_this->owner_name]) and !is_array($_this->id))
			$_this->owner_id = $_this->fld_data[$_this->owner_name];
		if(isset($_this->fld_data['parent_id']) and !is_array($_this->id))
			$_this->parent_id = $_this->fld_data['parent_id'];
		return true;
	}

	function _rename_childs(&$_this) {
		if(!count($_this->childs)) return true;
		foreach($_this->childs as $ch => $child) {
			$result=$_this->SQL->execSQL('UPDATE `'.$_this->childs[$ch]->tablename.'` SET '.$_this->childs[$ch]->owner_name.' = \''.$_this->fld_data['id'].'\' WHERE '.$_this->childs[$ch]->owner_name.' =\''.$_this->id.'\'');
			if($result->err) return false;
		}
		return true;
	}

	function _rename_parent_childs(&$_this) {
		if(!$_this->mf_istree) return true;
		$result=$_this->SQL->execSQL('UPDATE `'.$_this->tablename.'` SET `parent_id` = \''.$_this->fld_data['id'].'\' WHERE parent_id =\''.$_this->id.'\'');
		if($result->err) return false;
		return true;
	}

	function _rename_attaches(&$_this) {
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

	function _update_attaches(&$_this) {
		return _add_attaches($_this);
	}

	function _rename_memos(&$_this) {
		if(!count($_this->memos)) return true;
		$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForMemo($key);
		foreach($_this->memos as $key => $value) {
			$f = $pathimg.'/'.$_this->id.$_this->text_ext;
			if (file_exists($f)) rename($f, $pathimg.'/'.$_this->fld_data['id'].$_this->text_ext);
		}
		return true;
	}

	function _update_memos(&$_this) {
		return _add_memos($_this);
	}


/*------------- DELETE DELETE DELETE -----------------*/

	// in:  id											req
	// out: 0 - success,
	//      otherwise errorcode

	function _delete(&$_this) {
		if (!is_array($_this->id)) $_this->id = array($_this->id);
		if (!count($_this->id)) return true;
		// delete childs of owner
		if (count($_this->childs)) foreach($_this->childs as &$child){
			$child->id = $_this->id;
			if (!_delete_ownered($child)) return false;
		}
		// delete childs of tree
		if ($_this->mf_istree) {
			$id = $_this->id;
			if (!_delete_parented($_this)) return false;
			$_this->id = $id;
		}
		if (!_delete_attaches($_this)) return false;
		if (!_delete_memos($_this)) return false;
		if (!_delete_fields($_this)) return false;

		if ($_this->mf_indexing) $_this->deindexing();
		$_this->id = 0;
		return $_this->_message('Delete data from `'.$_this->caption.'` successful.',3);
	}

	function _delete_ownered(&$_this) {
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

	function _delete_parented(&$_this) {
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

	function _delete_fields(&$_this) {
		// delete records
		$result=$_this->SQL->execSQL('DELETE FROM `'.$_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;
		return true;
	}

	function _delete_attaches(&$_this) {
		if (!count($_this->attaches)) return true;
		$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
		$result=$_this->SQL->execSQL('SELECT `id`, `'.implode('`,`', array_keys($_this->attaches)).'` FROM `'. $_this->tablename.'` WHERE `id` IN ('.$_this->_id_as_string().')');
		if($result->err) return false;

		while ($row = $result->fetch_array()) {
			foreach($_this->attaches as $key => $value) {
			
				$f = $pathimg.'/'. $row['id']. '.'. $row[$key];
				if (file_exists($f)) {
					if (!unlink($f)) return $_this->_message('Cannot delete file `'.$f.'`',1);
				}
			}
		}
		return true;
	}

	function _delete_memos(&$_this) {
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
	function _waterMark(&$_this,$ConvertFile, $OutFile,$logo='',$pos=0)
	{
		if($logo=='')
			$logo = $_this->_CFG['_imgwater'];
		if($pos==0) $pos ='center';
		elseif($pos==1)  $pos ='eastnorth';
		else  $pos ='northeast';
		shell_exec('composite -gravity '.$pos.' -dissolve 30 $logo '.$ConvertFile.' '.$OutFile); 
		if(!file_exists($OutFile)) return $_this->_message('Cant composite file on '.__LINE__.' in kernel',1);
		return true;
	}

	function _resizeImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		chmod($InFile, 0755);
		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер

		if($width_orig<$WidthX and $height_orig<$HeightY) {
			if($InFile!=$OutFile) {
				copy($InFile,$OutFile);
				chmod($OutFile, 0755);
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
			return $_this->_message('File '.$InFile.' is not image',1);
		if($imtype>3) return true;
		$source = _imagecreatefrom($_this,$InFile,$imtype);//открываем рисунок
		imagecopyresized($thumb, $source, 0, 0, 0, 0, $WidthX, $HeightY, $width_orig, $height_orig);//меняем размер
		_image_to_file($thumb, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return $_this->_message('Cant create file',1);
		return true;
	}

	function _cropImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		chmod($InFile, 0755);
		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер
		// Resample
		$thumb = imagecreatetruecolor($WidthX, $HeightY);//созд пустой рисунок
		if(!$imtype = exif_imagetype($InFile)) // опред тип файла
			return $_this->_message('File is not image',1);
		if($imtype>3) return true;
		$source = _imagecreatefrom($_this,$InFile,$imtype);//открываем рисунок
		imagecopyresampled($thumb, $source, 0, 0, $width_orig/2-$WidthX/2, $height_orig/2-$HeightY/2, $WidthX, $HeightY, $WidthX, $HeightY);
		_image_to_file($thumb, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return $_this->_message('Cant create img file ',1);
		return true;
	}

	function _resizecropImage(&$_this,$InFile, $OutFile, $WidthX, $HeightY)
	{
		$trueX=$WidthX;$trueY=$HeightY;
		chmod($InFile, 0755);
		list($width_orig, $height_orig) = getimagesize($InFile);

		$ratio_orig = $width_orig/$height_orig;
		if ($WidthX/$HeightY > $ratio_orig) {
		   $HeightY = $WidthX/$ratio_orig;
		} else {
		   $WidthX = $HeightY*$ratio_orig;
		}
		/*Создаем пустое изображение на вывод*/
		if(!($thumb = @imagecreatetruecolor($WidthX, $HeightY)))
			return $_this->_message('Cannot Initialize new GD image stream',1);
		/*Определяем тип рисунка*/
		if(!$imtype = exif_imagetype($InFile))// опред тип файла
			return $_this->_message('File is not image',1);
		/*Обработка только jpeg, gif, png*/
		if($imtype>3) return true;
		/*Открываем исходный рисунок*/
		if(!$source = _imagecreatefrom($_this,$InFile,$imtype))//открываем рисунок
			return $_this->_message('File '.$InFile.' is not image',1);
		if(!imagecopyresampled($thumb, $source, 0, 0, 0, 0, $WidthX, $HeightY, $width_orig, $height_orig))
			return $_this->_message('Error imagecopyresampled',1);
		if(!($thumb2 = @imagecreatetruecolor($trueX, $trueY)))
			return $_this->_message('Cannot Initialize new GD image stream',1);
		if(!imagecopyresampled($thumb2, $thumb, 0, 0, $WidthX/2-$trueX/2, $HeightY/2-$trueY/2, $trueX, $trueY, $trueX, $trueY)) 
			return $_this->_message('Error imagecopyresampled',1);
		_image_to_file($thumb2, $OutFile,$_this->_CFG['_imgquality'],$imtype);//сохраняем в файл
		if(!file_exists($OutFile)) return $_this->_message('Cant create file',1);
		return true;
	}

	function _imagecreatefrom(&$_this,$im_file,$imtype)
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
				$_this->_message('Can not create a new image from file',1);
		}
		elseif($imtype==2) {
			if(!($image=imagecreatefromjpeg($im_file)))
				$_this->_message('Can not create a new image from file',1);
		}
		elseif($imtype==3) {
			if(!($image=imagecreatefrompng($im_file)))
				$_this->_message('Can not create a new image from file',1);
		}
		else return false;
		return $image;
	}

	function _image_to_file($im,$file,$q,$imtype)
	{
		if($imtype==1) imagegif($im, $file,$q);
		elseif($imtype==2) imagejpeg($im, $file,$q);
		elseif($imtype==3) imagepng($im, $file,8);
		else return false;
		return true;
	}



/******************* FORM *****************/

	function _fFormCheck(&$_this,&$data,&$param,&$FORMS) { //$_this->fields_form
		global $_tpl;
		if(!count($FORMS))
			return array('mess'=>array(array('name'=>'error', 'value'=>$_this->_CFG['_MESS']['errdata'])));
		$masks = &$_this->_CFG['_MASK'];
		$arr_nochek = array('info'=>1,'sbmt'=>1,'alert'=>1);
		$messages='';
		$arr_err_name=array();
		foreach($FORMS as $key=>&$value)
		{
			$error = array();
			if($key=='_*features*_') continue;
			if(!isset($value['type'])) return array('mess'=>array(array('name'=>'error', 'value'=>$_this->_CFG['_MESS']['errdata'].' : '.$key)));
			if(isset($arr_nochek[$value['type']])) continue;

			/*Поля которые недоступны пользователю не проверяем, дефолтные значения прописываются в kPreFields()*/
			if($value['readonly'] or $value['mask']['fview']==2 or (isset($value['mask']['usercheck']) and !_prmUserCheck($value['mask']['usercheck']))) {
				//unset($data[$key]);
				continue;
			}

			//*********** Файлы
			if(isset($_FILES[$key]['name'])) {
				$tmp = count($error);
				if(isset($data[$key.'_del']) and (int)$data[$key.'_del']==1){
					$_FILES[$key]['name'] = ':delete:';
					$_FILES[$key]['tmp_name'] = ':delete:';
					$value['value'] = $data[$key] = $_FILES[$key];
				}elseif((int)$value['mask']['min'] and ($_FILES[$key]['name']=='' or $_FILES[$key]['name'] == ':delete:'))
						$error[] = 1;
				elseif($_FILES[$key]['name']!='') {
					if(!isset($value['mime'][$_FILES[$key]['type']]))
						$error[]=39;
					if(isset($value['maxsize']) and $_FILES[$key]['size']>($value['maxsize']*1024))
						$error[]=29;
					if($tmp == count($error)){
						$temp = $_this->_CFG['_PATH']['temp'].basename($_FILES[$key]['name']);
						if(file_exists($temp)) unlink($temp);
						if (move_uploaded_file($_FILES[$key]['tmp_name'], $temp)){
							$_FILES[$key]['tmp_name']= $temp;
							$value['value'] = $data[$key] = $_FILES[$key];
						}else
							$error[]=40;
					}
				}

			}
			//*********** CHECKBOX
			elseif($value['type']=='checkbox') {
				$value['value'] = $data[$key] = ((int)$data[$key] == 1 ? 1 : 0);
			}
			elseif($value['type']=='date') {
				$value['value'] = $data[$key] = _get_fdate($value, $data[$key], $_this->fields[$key]['type']);
			}
			//*********** МАССИВЫ
			elseif(is_array($data[$key]) and count($data[$key])) {
/*Доработать*/
				if(isset($value['mask']['max'])){
					if(count($data[$key])>$value['mask']['max'])
						$error[] = 24;
				}
				if(isset($value['mask']['min'])){
					if(count($data[$key])<$value['mask']['min'])
						$error[] = 25;
				}
			}else{
				$value['value'] = trim($data[$key]);				
			//********** ОСТАЛЬНЫЕ
				if($value['value']!='') {
					$data[$key] = $value['value'];
					if($value['mask']['entities']==1) 
						$value['value'] = $data[$key]= htmlspecialchars($data[$key],ENT_QUOTES,$_this->_CFG['wep']['charset']);
					if(isset($value['mask']['replace'])) {
						if(!isset($value['mask']['replaceto']))
							$value['mask']['replaceto']='';
						$value['value'] = $data[$key] = preg_replace($value['mask']['replace'],$value['mask']['replaceto'],$data[$key]);
					}
					if(isset($value['mask']['striptags'])) {
						if($value['mask']['striptags']=='all') 
							$value['value'] = $data[$key] = strip_tags($data[$key]);
						elseif($value['mask']['striptags']=='') 
							$value['value'] = $data[$key] = strip_tags($data[$key],'<table><td><tr><p><span><center><div><a><b><strong><em><u><i><ul><ol><li><br>');
						else
							$value['value'] = $data[$key]=strip_tags($data[$key],$value['mask']['striptags']);
					}
					//$value['value']
					//$data[$key]

					/*CHECK TYPE*/
					if($value['type']=='ckedit'){
						$value['value'] = $data[$key] =stripslashes($data[$key]);
					}
					elseif($value['type']=='int' and !$value['mask']['toint']) 
						$value['value'] = $data[$key]= (int)$data[$key];
					elseif($value['type']=='captcha' && $data[$key]!=$value['captcha']) {
						$error[] = 31;
					}
					elseif($value['type']=='password')
					{
						if($data[$key]!=$data['re_'.$key])
							$error[] = 32;
					}
					elseif(($value['type']=='list' or $value['type']=='ajaxlist'))
					{
						if($_this->_checkList($value['listname'],$data[$key])===false)
							$error[] = 33;
					}

					/*CHECK MASK*/
					if($value['mask']['name']=='phone' or $value['mask']['name']=='phone2')
					{
						if($data[$key]!='') {
							$data[$key] = _phoneReplace($data[$key],$value['mask']['name']);
							if($data[$key]=='')
								$error[] = 3;
						}
						$value['value'] = $data[$key];
					}
					elseif($value['mask']['name']=='www')
					{
						if(!preg_match($masks['www'],$data[$key]))
							$error[] = 3;
						elseif($value['mask']['checkwww'] and !fopen ('http://'.str_replace('http://','',$data[$key]), 'r')) 
							$error[] = 4;
					}
					elseif(isset($masks[$value['mask']['name']]))
					{
						if(!preg_match($masks[$value['mask']['name']],$data[$key]))
							$error[] = 3;
					}
					elseif(isset($value['mask']['patterns']) && !preg_match($value['mask']['patterns'],$data[$key]))
						$error[] = 3;
					elseif(isset($masks[$value['type']]) and !preg_match($masks[$value['type']],$data[$key]))
						$error[] = 3;

					/*CHECK LEN*/
					if(isset($value['mask']['max']) && $value['mask']['max']>0)
					{
						if(_strlen($data[$key])>$value['mask']['max'])
							$error[] = 2;
					}
					if(isset($value['mask']['min']) and $value['mask']['min']>0)
					{
						if($value['mask']['min']>0 and (!$data[$key] or $data[$key]=='0'))
							$error[] = 1;
						elseif(_strlen($data[$key])<$value['mask']['min'])
							$error[] = 21;
					}
					if(isset($value['mask']['maxint']) && $value['mask']['maxint']>0 && (int)$data[$key]>$value['mask']['maxint'])
							$error[] = 22;
					if(isset($value['mask']['minint']) && $value['mask']['minint']>0 && (int)$data[$key]<$value['mask']['minint'])
							$error[] = 23;

				}
				elseif((int)$value['mask']['min'])
					$error[] = 1;
			}

///////////////////

				/*elseif(isset($value['mask']['name']) && $value['mask']['name']=='date')
				{
					if(is_array($data[$key]))
						foreach($data[$key] as $name=>$option)
						{
							if(!isset($value['mask']['patterns']) && !preg_match($masks[$value['mask']['name']],$data[$name])) $error[] = 3;
							elseif(isset($value['mask']['patterns']) && !preg_match($value['mask']['patterns'],$data[$name])) $error[] = 3;
						}
				}*/

			foreach($error as $row) {
				$messages = '';
				if($row==1) //no empty
					$messages = $_this->getMess('_err_1',array($value['caption']));
				elseif($row==2) //max chars
					$messages = $_this->getMess('_err_2',array($value['caption'],$value['mask']['max'],(_strlen($data[$key])-$value['mask']['max'])));
				elseif($row==21) // min chars
					$messages = $_this->getMess('_err_21',array($value['caption'],$value['mask']['min'],($value['mask']['min']-_strlen($data[$key]))));
				elseif($row==22) //min int
					$messages = $_this->getMess('_err_22',array($value['caption'])).$value['mask']['maxint'];
				elseif($row==23) //max int
					$messages = $_this->getMess('_err_23',array($value['caption'])).$value['mask']['minint'];
				elseif($row==24) // min chars
					$messages = $_this->getMess('_err_22',array($value['caption'])).$value['mask']['max'];
				elseif($row==25) // max chars
					$messages = $_this->getMess('_err_23',array($value['caption'])).$value['mask']['min'];
				elseif($row==29) //limit file size
					$messages = $_this->getMess('_err_29',array($value['caption'],$_FILES[$key]['name'])).$value['maxsize'].'Kb';
				elseif($row==3) //wrong data
					$messages = $_this->getMess('_err_3',array($value['caption']));
				elseif($row==31) //wrong captchs
					$messages = $_this->getMess('_err_31',array($value['caption']));
				elseif($row==32) // wrong repeat pass
					$messages = $_this->getMess('_err_32',array($value['caption']));
				elseif($row==33) //data error
					$messages = $_this->getMess('_err_33',array($value['caption']));
				elseif($row==39) //wrong file type
					$messages = $_this->getMess('_err_39',array($_FILES[$key]['name'])).'- '.implode(',',array_unique($value['mime'])).'.';
				elseif($row==40) //error load file
					$messages = $_this->getMess('_err_40',array($_FILES[$key]['name']));
				elseif($row==4)  // wrong link
					$messages = $_this->getMess('_err_4',array($value['caption']));
				$arr_err_name[$key]=$key;

				if($param['ajax'])
					$_tpl['onload'] .= 'putEMF(\''.$key.'\',\''.$messages.'\');'; // запись в форму по ссылке
				else
					$value['error'][] = $messages; // запись в форму по ссылке
			}

		}
		$messages = array();
		if(count($arr_err_name)>0){
			$messages[] = array('name'=>'error', 'value'=>'Поля формы заполненны не верно.');
		}
		/*$_tpl['onload'] .'CKEDITOR.replace( \'editor1\',
						 {
							  toolbar : \'basic\',
							  uiColor : \'# 9AB8F3\'
						 });';*/
		return array('mess'=>$messages,'vars'=>$data);
	}

	function _phoneReplace($phone,$mask)
	{
		$phone_2 = array();
		$phone_1 = preg_replace("/[^0-9,\(\)]+/",'',$phone);
		$phone_1 = explode(',',$phone_1);
		foreach($phone_1 as $k=>$p)
		{
			$temp = preg_replace(array(
				"/^([0-9]{2,3})([0-9]{2})([0-9]{2})$/",
				"/^(\([0-9]{3}\))([0-9]{3})([0-9]{2})([0-9]{2})$/",
				"/^(\([0-9]{4}\))([0-9]{2})([0-9]{2})([0-9]{2})$/",
				"/^(\([0-9]{5}\))([0-9]{1})([0-9]{2})([0-9]{2})$/",
				"/^([0-9])\(([0-9]{3})\)([0-9]{3})([0-9]{2})([0-9]{2})$/",
				"/^([0-9])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/"),
								array(
				"\\1-\\2-\\3",
				"\\1\\2-\\3-\\4",
				"\\1\\2-\\3-\\4",
				"\\1\\2-\\3-\\4",
				"\\1\\2\\3-\\4-\\5",
				"\\1-\\2-\\3-\\4-\\5"),	$p);
			global $_CFG;
			if($temp!=$p || preg_match($_CFG['_MASK'][$mask],$p)) $phone_2[$k]=$temp;
		}
		$phone_2 = implode(', ',$phone_2);
		return $phone_2;
	}

?>
