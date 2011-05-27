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
		elseif ($listname == "style") {

			$dir = dir($_this->_CFG['_PATH']['design'].$_this->_CFG['wep']['design'].'/style');
			while (false !== ($entry = $dir->read())) {
				if (strstr($entry,'.css')) {
					$entry = substr($entry, 0, strpos($entry, '.css'));
					$data['../'.$_this->_CFG['wep']['design'].'/style/'.$entry] = $_this->_CFG['wep']['design'].' - '.$entry;
				}
			}
			$dir->close();

			$dir = dir($_this->_CFG['_PATH']['_style']);
			while (false !== ($entry = $dir->read())) {
				if (strstr($entry,'.css')) {
					$entry = substr($entry, 0, strpos($entry, '.css'));
					$data[$entry] = $entry;
				}
			}
			$dir->close();
		}
		elseif ($listname == "script") {

			$dir = dir($_this->_CFG['_PATH']['design'].$_this->_CFG['wep']['design'].'/script');
			while (false !== ($entry = $dir->read())) {
				if (strstr($entry,'.js')) {
					$entry = substr($entry, 0, strpos($entry, '.js'));
					$data['']['../'.$_this->_CFG['wep']['design'].'/script/'.$entry] = $_this->_CFG['wep']['design'].' - '.$entry;
				}
			}
			$dir->close();
			$afterSubDir = array();
			$dir = dir($_this->_CFG['_PATH']['_script']);
			while (false !== ($entry = $dir->read())) {
				if (strstr($entry,'.js')) {
					$entry = substr($entry, 0, strpos($entry, '.js'));
					$data[''][$entry] = $entry;
				}elseif(substr($entry,0,7)=='script.'){
					$afterSubDir[$entry] = array('#name#'=> $entry, '#checked#'=>0);
					$dir2 = dir($_this->_CFG['_PATH']['_script'].'/'.$entry);
					while (false !== ($entry2 = $dir2->read())) {
						if (strstr($entry2,'.js')) {
							$entry2 = substr($entry2, 0, strpos($entry2, '.js'));
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
			$data = $_this->owner->_dump();
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
		elseif(is_array($listname) and (isset($listname['class']) or isset($listname['tablename'])))  {
			
			if (isset($listname['include']))
				require_once($_this->_CFG['_PATH']['ext'].$listname['include'].'.class.php');


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

			if($listname['where'])
				$clause['where'] .= ($clause['where']!=''?' AND ':'').$listname['where'];
			if($clause['where'])
				$clause['where'] = ' WHERE '.$clause['where'];

			if(isset($listname['leftJoin']))
				$clause['where'] .= ' GROUP BY t1.'.$listname['idThis'];
			if ($listname['ordfield'])
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
					elseif($listname['is_tree']) {
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
			static_main::_message('List data `'.$listname.'` not found',1);
		}
		else {
			static_main::_message('List '.current($listname).' not found',1);
		}

		return $data;
	}
