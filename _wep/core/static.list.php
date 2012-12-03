<?php
class static_list {

	/**
	 * проверка выбранных данных из списка
	 * @param mixed $listname - название списока или массив данных для списка
	 * @param mixed $value - значение
	 * @return array Список
	 */
	static function _checkList($_this, &$listname, $value = NULL) {

		$templistname = $listname;
		if (is_array($listname))
			$templistname = implode(',', $listname);
		$templistname = $_this->_cl.'_'.$templistname;

		if (!isset($_this->_CFG['enum_check'][$templistname])) {

			if (!isset($_this->_CFG['enum'][$templistname])) {
				$data = &$_this->_getCashedList($listname, $value); // , $value
				//$_this->_CFG['enum'][$templistname]
			} else
				$data = &$_this->_CFG['enum'][$templistname];

			if (!is_array($data) or !count($data))
				return false;

			$temp2 = array();
			$temp = current($data);

			// Скорее всего вскоре этот блок будет лишним , 
			// по идее _checkList всегжа жолжен иметь $value
			// и _getCashedList выдает готовый рез-тат
			if (is_array($temp) and !isset($temp['#name#'])) {
				foreach ($data as $krow => $row) {
					if (isset($temp2[$krow])) {
						if (is_array($temp2[$krow]))
							$adname = $temp2[$krow]['#name#'];
						else
							$adname = $temp2[$krow];
						foreach ($row as $kk => $rr) {
							if(is_array($rr)) {
								if(isset($rr['#name#']))
									$rr = $rr['#name#'];
								else
									$rr = implode(' / ',$rr);
							}
							$row[$kk] = $adname . ' - ' . $rr;
						}
						if (is_array($temp2[$krow]) and isset($temp2[$krow]['#checked#']))
							unset($temp2[$krow]);
					}
					$temp2 += $row;
				}
				$temp = &$temp2;
			}else
				$temp = &$data;
			if (is_null($value))// не кешируем если не задано значение и  or !is_array($listname) $listname - выборка из БД(в массиве)
				$_this->_CFG['enum_check'][$templistname] = $temp;
		}else
			$temp = &$_this->_CFG['enum_check'][$templistname];

		if (is_array($value)) {
			$return_value = array();
			foreach ($value as $r) {
				if (isset($temp[$r]))
					$return_value[] = $temp[$r];
			}
			if (count($return_value) == count($value))
				return $return_value;
		}
		elseif (isset($temp[$value])) {
			return $temp[$value];
		}
		return false;
	}

	/**
	 * Получение списка из кеша если он там есть
	 * @param mixed $listname - название списока или массив данных для списка
	 * @param mixed $value - значение
	 * @return array Список
	 */
	static function &_getCashedList($_this, $listname, $value = NULL) {
		$data = array();
		$templistname = $listname;
		if (is_array($listname))
			$templistname = implode(',', $listname);
		$templistname = $_this->_cl.'_'.$templistname;

		if (!is_null($value)) { // не кешируем если задано $value и $listname - выборка из таблиц(задается массивом)
			$data = $_this->_getlist($listname, $value);

			// VALUE
			if (!is_array($value))
				$tvalue = array($value => $value);
			else
				$tvalue = array_combine($value, $value);

			$new = array();
			if (!is_array(current($data)))
				$data = array_intersect_key($data, $tvalue);
			else {
				$tdata = array();
				foreach ($data as $r) {
					$tdata += array_intersect_key($r, $tvalue);
				}
				$data = $tdata;
			}
			return $data;
		}
		elseif (!isset($_this->_CFG['enum'][$templistname]))
			$_this->_CFG['enum'][$templistname] = $_this->_getlist($listname, $value);

		return $_this->_CFG['enum'][$templistname];
	}

	static function &_getlist($_this, &$listname, $value=NULL) /*LIST SELECTOR*/
	{
		/*Выдает 1 уровневый массив, либо 2х уровневый для структуры типа дерева*/
		/*Конечный уровень может быть с елемнтами массива #name# итп, этот уровень в счет не входит*/
		$data = array();
		$templistname = $listname;
		if(is_array($listname))
		{
			if(isset($listname[0]))
				$templistname = $listname[0];
			else
				$templistname = implode(',',$listname);
		}

		if (isset($_this->_enum[$templistname])) {
			return $_this->_enum[$templistname];
		}
		elseif($templistname == 'count') {
			if(!$listname[1]) $listname[1] = 1;
			if(!$listname[2]) $listname[2] = 20;
			for($i=$listname[1];$i<=$listname[2];$i++) {
				$data[$i] = $i;
			}
		}
		elseif($templistname == 'classList') 
		{
			$data = array(''=>' --- ');
			foreach($_this->_CFG['modulprm'] as $k=>$r)
				$data[$k] = $r['name'];			
		}
		elseif($templistname == 'child.class') 
		{
			$dir = array();
			if(file_exists($_this->_CFG['_PATH']['ext'].$_this->_cl.'.class'))
				$dir[''] = $_this->_CFG['_PATH']['ext'].$_this->_cl.'.class';
			if(file_exists($_this->_CFG['_PATH']['wep_ext'].$_this->_cl.'.class'))
				$dir['Ядро - '] = $_this->_CFG['_PATH']['wep_ext'].$_this->_cl.'.class';
			$data = array(''=>' --- ');
			foreach($dir as $k=>$r) {
				$odir = dir($r);
				while (false !== ($entry = $odir->read())) {
					if (substr($entry,-11)=='.childs.php') {
						$entry = substr($entry,0,-11);
						$data[$entry] = $k.$entry;
					}
				}
				$odir->close();
			}
		}
		elseif($templistname == 'phptemplates') {
			// вызов только для PG
			$data[''][''] = ' - ';

			// Системные модули
			$dir = dir($_this->_CFG['_PATH']['wep_ext']);
			while (false !== ($entry = $dir->read())) {
				if (_strpos($entry,'.class')!==false) {
					$key = _substr($entry,0,-6);
					$dir2 = $_this->_CFG['_PATH']['wep_ext'].$entry.'/_design/php';
					if(file_exists($dir2) and is_dir($dir2)) {
						$dir2Obj = dir($dir2);
						while (false !== ($entry2 = $dir2Obj->read())) 
						{
							if (mb_strstr($entry2,'.php')) 
							{
								$docs = static_tools::getDocFileInfo($dir2.'/'.$entry2);

								if(!$docs['type'])
									$docs['type'] = $entry;
								if(!$docs['name'])
									$docs['name'] = $entry2;

								if(!isset($data[$docs['type']]))
									$data[''][$docs['type']] = array('#name#'=>$docs['type'], '#checked#'=>0);

								// Определяем совместимость шаблонов
								if(isset($listname['tags']))
								{
									// todo - suport multiple tag
									if(!$docs['tags'])
										$docs['#css#'] = 'notags';
									elseif($docs['tags']!=$listname['tags'])
										$docs['#css#'] = 'nosupport';
									else
										$docs['#css#'] = 'support';
								}
								$docs['#name#'] = $docs['name'];

								$data[$docs['type']] ['#'.$key.'#'._substr($entry2,0,-4)] = $docs;

							}
						}
						$dir2Obj->close();
					}
				}
			}
			$dir->close();

			// Пользовательские модули
			$dir = dir($_this->_CFG['_PATH']['ext']);
			while (false !== ($entry = $dir->read())) {
				if (strpos($entry,'.class')!==false) {
					$key = substr($entry,0,-6);
					$dir2 = $_this->_CFG['_PATH']['ext'].$entry.'/_design/php';
					if(file_exists($dir2) and is_dir($dir2)) {
						$dir2 = dir($dir2);
						while (false !== ($entry2 = $dir2->read())) {
							if (strstr($entry2,'.php')) 
							{
								$docs = static_tools::getDocFileInfo($dir2.'/'.$entry2);

								if(!$docs['type'])
									$docs['type'] = $entry;
								if(!$docs['name'])
									$docs['name'] = $entry2;

								if(!isset($data[$docs['type']]))
									$data[''][$docs['type']] = array('#name#'=>$docs['type'], '#checked#'=>0);

								// Определяем совместимость шаблонов
								if(isset($listname['tags']))
								{
									// todo - suport multiple tag
									if(!$docs['tags'])
										$docs['#css#'] = 'notags';
									elseif($docs['tags']!=$listname['tags'])
										$docs['#css#'] = 'nosupport';
									else
										$docs['#css#'] = 'support';
								}
								$docs['#name#'] = $docs['name'];

								$data[$docs['type']] ['#'.$key.'#'._substr($entry2,0,-4)] = $docs;
							}
						}
						$dir2->close();
					}
				}
			}
			$dir->close();

			// Дизайн шаблоны
			_new_class('pg',$PGLIST);
			if(file_exists($PGLIST->_CFG['_PATH']['themes'].$PGLIST->config['design'].'/php')) {
				$dir = $PGLIST->_CFG['_PATH']['themes'].$PGLIST->config['design'].'/php';
				$dirObj = dir($dir);
				while (false !== ($entry = $dirObj->read())) {
					if (strstr($entry,'.php')) {

						$docs = static_tools::getDocFileInfo($dir.'/'.$entry);

						if(!$docs['type'])
							$docs['type'] = $PGLIST->config['design'];
						if(!$docs['name'])
							$docs['name'] = $entry;

						if(!isset($data[$docs['type']]))
							$data[''][$docs['type']] = array('#name#'=>$docs['type'], '#checked#'=>0);

						// Определяем совместимость шаблонов
						if(isset($listname['tags']))
						{
							// todo - suport multiple tag
							if(!$docs['tags'])
								$docs['#css#'] = 'notags';
							elseif($docs['tags']!=$listname['tags'])
								$docs['#css#'] = 'nosupport';
							else
								$docs['#css#'] = 'support';
						}
						$docs['#name#'] = $docs['name'];

						$data[$docs['type']] [substr($entry,0,-4)] = $docs;
					}
				}
				$dirObj->close();
			}

			// Совместимость со старой версией
			// TODO - clear this code
			global $FUNCPARAM_FIX;
			$f = $value . '/templates';
			if($FUNCPARAM_FIX and count($FUNCPARAM_FIX) and file_exists($f)) {
				$temp = basename($value);$temp = substr($temp,0,-6);
				print_r('<p class="alert">Данные старой версии - срочно сохраните форму!<p>');
				foreach($FUNCPARAM_FIX as &$rff) {
					$rff = str_replace('#ext#','#'.$temp.'#',$rff);
				}
				unset($rff);
			}
		}
		elseif($listname == 'themes') {
			// вызов только для PG
			$data[''] = ' - По умолчанию -';
			$dir = dir($_this->_CFG['_PATH']['themes']);
			if($dir)
			{
				while (false !== ($entry = $dir->read())) {
					if ($entry[0]!='.' && $entry[0]!='..' && $entry{0}!='_') {
						$data[$entry] = $entry;
					}
				}
				$dir->close();
			}
		}
		elseif ($listname == 'style') {
			// вызов только для PG
			$dir = dir($_this->_CFG['_PATH']['themes'].'default/style');
			while (false !== ($entry = $dir->read())) {
				if (strpos($entry,'.css')) {
					$entry = substr($entry, 0, -4);
					$data['']['#themes#'.$entry] = '*'.$entry;
				}
			}
			$dir->close();

			$afterSubDir = array();
			$dir = dir($_this->_CFG['_PATH']['_style']);
			while (false !== ($entry = $dir->read())) {
				if (strpos($entry,'.css')) {
					$entry = substr($entry, 0, -4);
					$data[''][$entry] = $entry;
				}elseif(strpos($entry,'style.')===0) {
					$afterSubDir[$entry] = array('#name#'=> $entry, '#checked#'=>0);
					$dir2 = dir($_this->_CFG['_PATH']['_style'].'/'.$entry);
					while (false !== ($entry2 = $dir2->read())) {
						if (strpos($entry2,'.css')) {
							$entry2 = substr($entry2, 0, -4);
							$data[$entry][$entry.'/'.$entry2] = $entry.'/'.$entry2;
						}
					}
					$dir2->close();
				}
			}
			$dir->close();
			if(count($afterSubDir))
				$data[''] = $data['']+$afterSubDir;
		}
		elseif ($templistname == "script") {
			// вызов только для PG
			$dir = dir($_this->_CFG['_PATH']['themes'].'default/script');
			while (false !== ($entry = $dir->read())) {
				if (strpos($entry,'.js')) {
					$entry = substr($entry, 0, -3);
					$data['']['#themes#'.$entry] = '*'.$entry;
				}
			}
			$dir->close();

			$afterSubDir = array();
			$dir = dir($_this->_CFG['_PATH']['_script']);
			while (false !== ($entry = $dir->read())) {
				if (strpos($entry,'.js')) {
					$entry = substr($entry, 0, -3);
					$data[''][$entry] = $entry;
				}elseif(strpos($entry,'script.')===0) {
					$afterSubDir[$entry] = array('#name#'=> $entry, '#checked#'=>0);
					$dir2 = dir($_this->_CFG['_PATH']['_script'].'/'.$entry);
					while (false !== ($entry2 = $dir2->read())) {
						if (strpos($entry2,'.js')) {
							$entry2 = substr($entry2, 0, -3);
							$data[$entry][$entry.'/'.$entry2] = $entry.'/'.$entry2;
						}
					}
					$dir2->close();
				}
			}
			$dir->close();
			if(count($afterSubDir))
				$data[''] = $data['']+$afterSubDir;
		}
		elseif('fieldslist' == $templistname) {
			$data['id'] = '№';
			foreach($_this->fields_form as $k=>$r) {
				if($_this->fields_form[$k]['caption'])
					$data[$k] = $_this->fields_form[$k]['caption'];
			}
		}
		elseif('list' == $templistname) {

			$q_where = array();
			$q_order = '';
			
			$name = 'id, ' . $_this->_listname . ' as name';
			if ($_this->mf_istree)
				$name .= ', ' . $_this->mf_istree;
		
			if ($_this->ordfield)
				$q_order = ' ORDER BY '.$_this->ordfield;

			if (isset($_this->owner->id) and $_this->owner->id) // либо по owner id
				$q_where[] = $_this->owner_name.' IN (' . $_this->owner->_id_as_string() . ')';

			if(count($q_where))
				$q_where = ' WHERE '.implode(' and ', $q_where);
			else
				$q_where = ' ';

			$result = $_this->SQL->execSQL('SELECT ' . $name . ' FROM `' . $_this->tablename . '`' . $q_where.$q_order);

			if (!$result->err) {
				if ($_this->mf_istree) {
			
					if ($_this->mf_use_charid)
						$data[''][''] = static_main::m('_zeroname', $_this);
					else
						$data[0][0] = static_main::m('_zeroname', $_this);

					while (list($id, $name, $pid) = $result->fetch_row()) {
						$data[$pid][$id] = ($name?$name:$_this->caption.' #'.$id);
					}
				} else {
					if ($_this->mf_use_charid)
						$data[''] = static_main::m('_zeroname', $_this);
					else
						$data[0] = static_main::m('_zeroname', $_this);

					while (list($id, $name) = $result->fetch_row())
						$data[$id] = ($name?$name:$_this->caption.' #'.$id);
				}
			}
		}
		elseif ('select' == $templistname) {
			trigger_error('Использование списка `select` переделать на `list`', E_USER_WARNING);
			$data = $_this->_select();
		}
		elseif ('parentlist' == $templistname and $_this->mf_istree) {

			$data = array();
			if($_this->mf_use_charid)
				$data[''][''] = static_main::m('_zeroname',$_this);
			else
				$data[0][0] = static_main::m('_zeroname',$_this);

			$q = 'SELECT `id`, `name`, `parent_id` FROM `'.$_this->tablename.'`';
			if($_this->id) $q .=' WHERE `id`!="'.$_this->id.'"';
			if($_this->mf_ordctrl) $q .= ' ORDER BY '.$_this->mf_ordctrl;
			$result = $_this->SQL->execSQL($q);
			if(!$result->err)
				while (list($id, $name,$pid) = $result->fetch_row()) {
					$data[$pid][$id] = $name;
				}
		} 
		elseif(is_array($listname) and isset($listname[0]) and isset($listname[1]) and 'owner' == $listname[0] ) {
			$data = $_this->owner->_getlist($listname[1],$value);
		}
		elseif ('ownerlist' == $templistname) {
			// TODO : это Кастыль совместимости
			if($_this->owner)
				$data = $_this->owner->_getlist('list',$value);
			else
				$data = array('Ошибка - список ownerlist не может быть создан, так как родитель не доступен');
		}
		elseif(is_array($listname) and (isset($listname['class']) or isset($listname['tablename'])))  {
			$clause = array();
			if(isset($listname['class'])) {
				$listname['tablename'] = static_main::getTableNameOfClass($listname['class']);
			}

			if(!isset($listname['idField']))
				$listname['idField'] = 'tx.id';
			if(!isset($listname['nameField']))
				$listname['nameField'] = 'tx.name';

			if(isset($listname['leftJoin'])) {
				$clause['from'] = ' FROM `'.$_this->tablename.'` t1 LEFT JOIN `'.$listname['tablename'].'` tx ON '.$listname['idField'].'=t1.'.$listname['idThis'];
				$clause['field'] = 'SELECT t1.'.$listname['idThis'].' as id,'.$listname['nameField'].' as name';
			}
			elseif(isset($listname['join'])) {
				$clause['from'] = ' FROM `'.$_this->tablename.'` t1 JOIN `'.$listname['tablename'].'` tx ON '.$listname['idField'].'=t1.'.$listname['idThis'].' '.$listname['join'];				
				$clause['field'] = 'SELECT t1.'.$listname['idThis'].' as id,'.$listname['nameField'].' as name';
			}
			else {
				$clause['from'] = ' FROM `'.$listname['tablename'].'` tx ';
				$clause['field'] = 'SELECT '.$listname['idField'].' as id,'.$listname['nameField'].' as name';
			}
	
			if(isset($listname['is_tree'])) {
				if($listname['is_tree']===true)
					$clause['field'] .= ', tx.parent_id as parent_id';
				else
					$clause['field'] .= ', '.$listname['is_tree'].' as parent_id';
			}
			if(isset($listname['is_checked']))
				$clause['field'] .= ', tx.checked as checked';

			if(!isset($listname['where']))
				$listname['where'] = array();
			elseif(!is_array($listname['where']))
				$listname['where'] = array($listname['where']);

			/*Выбранные элементы*/
			if(!is_null($value)) {
				if(is_array($value))
					$listname['where'][] = $listname['idField'].' IN ("'.implode('", "',$value).'")';
				else
					$listname['where'][] = $listname['idField'].'="'.$value.'"';
			}

			if(count($listname['where']))
				$listname['where'] = ' WHERE '.implode(' and ',$listname['where']);
			else
				$listname['where'] = '';

			if(isset($listname['leftJoin']) and $listname['idThis'])
				$listname['where'] .= ' GROUP BY t1.'.$listname['idThis'];
			if (isset($listname['ordfield']) and $listname['ordfield'])
				$listname['where'] .= ' ORDER BY '.$listname['ordfield'];


			if(isset($listname['zeroname']))
				$_zeroname = $listname['zeroname'];
			else
				$_zeroname = static_main::m('_zeroname',$_this);

			$result = $_this->SQL->execSQL($clause['field'].$clause['from'].$listname['where']);
//print($_this->SQL->query);
				if(!$result->err) {
					if(!is_null($value) and is_array($value) and count($value)) {
						while ($row = $result->fetch())
							$data[$row['id']] = $row['name'];
					}
					elseif(!is_null($value)) {
						if ($row = $result->fetch())
							$data[$row['id']] = $row['name'];
					}
					elseif(isset($listname['is_tree']) and $listname['is_tree']) {
						while ($row = $result->fetch()){
							if(!isset($row['checked'])) $row['checked'] = true;
							$data[$row['parent_id']][$row['id']] = array('#name#'=>$row['name'], '#checked#'=>$row['checked']);
						}
						if(isset($data[0]))
							$def = 0;
						else
							$def = '';
						if($_zeroname)
							$data[$def] = static_main::MergeArrays(array($def=>$_zeroname),$data[$def]);
					}
					else {
						if($_zeroname)
							$data[''] = $_zeroname;
						while ($row = $result->fetch())
								$data[$row['id']] = $row['name'];
					}
				}
			return $data; // Потому что тут уже обрабатывается $value
		}
		elseif(!is_array($listname)) {
			static_main::log('error','List data `'.$listname.'` not found');
		}
		else {
			static_main::log('error','List '.current($listname).' not found');
		}

		return $data;
	}

}