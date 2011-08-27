<?
	function _getlist($_this,&$listname, $value=0) /*LIST SELECTOR*/
	{
		/*Выдает 1 уровневый массив, либо 2х уровневый для структуры типа дерева*/
		/*Конечный уровень может быть с елемнтами массива #name# итп, этот уровень в счет не входит*/
		$data = array();
		$templistname = $listname;
		if(is_array($listname))
			$templistname = implode(',',$listname);


		if($templistname == 'child.class') {
			$dir = array();
			if(file_exists($_this->_CFG['_PATH']['ext'].$_this->_cl.'.class'))
				$dir[''] = $_this->_CFG['_PATH']['ext'].$_this->_cl.'.class';
			if(file_exists($_this->_CFG['_PATH']['extcore'].$_this->_cl.'.class'))
				$dir['Ядро - '] = $_this->_CFG['_PATH']['extcore'].$_this->_cl.'.class';
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
		elseif($listname == 'phptemplates') {
			$data[''] = ' - ';
			$f = $value . '/templates';
			if(file_exists($f)) {
				$dir = dir($f);
				while (false !== ($entry = $dir->read())) {
					if ($entry[0]!='.' && $entry[0]!='..' && strstr($entry,'.php')) {
						$data['#ext#'.substr($entry,0,-4)] = basename($value).'/'.$entry;
					}
				}
				$dir->close();
			}
			_new_class('pg',$PGLIST);
			if(file_exists($PGLIST->_CFG['_PATH']['design'].$PGLIST->config['design'].'/php')) {
				$dir = dir($PGLIST->_CFG['_PATH']['design'].$PGLIST->config['design'].'/php');
				while (false !== ($entry = $dir->read())) {
					if ($entry[0]!='.' && $entry[0]!='..' && strstr($entry,'.php')) {
						$data[substr($entry,0,-4)] = ' '.$entry;
					}
				}
				$dir->close();
			}
		}
		elseif($listname == 'mdesign') {
			$data[''] = ' - По умолчанию -';
			$dir = dir($_this->_CFG['_PATH']['design']);
			while (false !== ($entry = $dir->read())) {
				if ($entry[0]!='.' && $entry[0]!='..' && $entry{0}!='_') {
					$data[$entry] = $entry;
				}
			}
			$dir->close();
		}
		elseif ($listname == 'style') {
			$mdesign = 'mdesign';
			$mdesign = $_this->_getlist($mdesign);
			foreach($mdesign as $k=>$r) {
				if($k) {
					$dir = dir($_this->_CFG['_PATH']['design'].$k.'/style');
					while (false !== ($entry = $dir->read())) {
						if (strpos($entry,'.css')) {
							$entry = substr($entry, 0, -4);
							$data['']['../'.$k.'/style/'.$entry] = strtoupper($r).' - '.$entry;
						}
					}
					$dir->close();
				}
			}
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
							$data[$entry][$entry.'/'.$entry2] = $entry2;
						}
					}
					$dir2->close();
				}
			}
			$dir->close();
			if(count($afterSubDir))
				$data[''] = $data['']+$afterSubDir;
		}
		elseif ($listname == "script") {
			$mdesign = 'mdesign';
			$mdesign = $_this->_getlist($mdesign);
			foreach($mdesign as $k=>$r) {
				if($k) {
					$dir = dir($_this->_CFG['_PATH']['design'].$k.'/script');
					while (false !== ($entry = $dir->read())) {
						if (strpos($entry,'.js')) {
							$entry = substr($entry, 0, -3);
							$data['']['../'.$k.'/script/'.$entry] = strtoupper($r).' - '.$entry;
						}
					}
					$dir->close();
				}
			}
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
							$data[$entry][$entry.'/'.$entry2] = $entry2;
						}
					}
					$dir2->close();
				}
			}
			$dir->close();
			if(count($afterSubDir))
				$data[''] = $data['']+$afterSubDir;
		}
		elseif($templistname == 'list') {
			$data = $_this->_dump();
		}
		elseif ($templistname == 'ownerlist') {
			if($_this->owner)
				$data = $_this->owner->_dump();
			else
				$data = array('Ошибка - список ownerlist не может быть создан, тк родитель не доступен');
		}
		elseif ($templistname == 'select') {
			$data = $_this->_select();
		}
		elseif ($templistname == 'parentlist' and $_this->mf_istree) {

			$data = array();
			if($_this->mf_use_charid)
				$data[''][''] = $_this->getMess('_listroot');
			else
				$data[0][0] = $_this->getMess('_listroot');

			$q = 'SELECT `id`, `name`, `parent_id` FROM `'.$_this->tablename.'`';
			if($_this->id) $q .=' WHERE `id`!="'.$_this->id.'"';
			if($_this->mf_ordctrl) $q .= ' ORDER BY '.$_this->mf_ordctrl;
			$result = $_this->SQL->execSQL($q);
			if(!$result->err)
				while (list($id, $name,$pid) = $result->fetch_array(MYSQL_NUM)) {
					$data[$pid][$id] = $name;
				}
		} 
		elseif(is_array($listname) and isset($listname[0]) and isset($listname[1]) and $listname[0]=='owner' ) {
			return $_this->owner->_getlist($listname[1],$value);
		}
		elseif(is_array($listname) and (isset($listname['class']) or isset($listname['tablename'])))  {
			
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
			
			$clause['where'] = '';
			if(isset($listname['where']) and is_array($listname['where']))
				$listname['where'] = implode(' and ',$listname['where']);
			/*Выбранные элементы*/ /*помоему это лишнее - надо проверить*/
			if(!is_null($value) and is_array($value))
				$clause['where'] = $listname['idField'].' IN ("'.implode('", "',$value).'")';
			elseif(!is_null($value))
				$clause['where'] = $listname['idField'].'="'.$value.'"';

			if(isset($listname['where']) and $listname['where'])
				$clause['where'] .= ($clause['where']!=''?' AND ':'').$listname['where'];
			if($clause['where'])
				$clause['where'] = ' WHERE '.$clause['where'];

			if(isset($listname['leftJoin']) and $listname['idThis'])
				$clause['where'] .= ' GROUP BY t1.'.$listname['idThis'];
			if (isset($listname['ordfield']) and $listname['ordfield'])
				$clause['where'] .= ' ORDER BY '.$listname['ordfield'];

			$result = $_this->SQL->execSQL($clause['field'].$clause['from'].$clause['where']);
//print($_this->SQL->query);
				if(!$result->err) {
					if(!is_null($value) and is_array($value) and count($value)) {
						while ($row = $result->fetch_array())
							$data[$row['id']] = $row['name'];
					}
					elseif(!is_null($value)) {
						if ($row = $result->fetch_array())
							$data[$row['id']] = $row['name'];
					}
					elseif(isset($listname['is_tree']) and $listname['is_tree']) {
						while ($row = $result->fetch_array()){
							if(!isset($row['checked'])) $row['checked'] = true;
							$data[$row['parent_id']][$row['id']] = array('#name#'=>$row['name'], '#checked#'=>$row['checked']);
						}
						if(isset($data[0]))
							$data[0] = array(0=>$_this->getMess('_listroot'))+$data[0];
						else
							$data[''] = array(''=>$_this->getMess('_listroot'))+$data[''];
					}
					else{
						$data[''] = ' --- ';
						while ($row = $result->fetch_array())
								$data[$row['id']] = $row['name'];
					}
				}
		}
		elseif(!is_array($listname)) {
			static_main::_message('error','List data `'.$listname.'` not found');
		}
		else {
			static_main::_message('error','List '.current($listname).' not found');
		}

		return $data;
	}
