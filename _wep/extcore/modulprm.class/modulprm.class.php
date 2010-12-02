<?


class modulprm_class extends kernel_class {

	function _set_features()
	{
		if (parent::_set_features()) return 1;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->mf_use_charid = true;
		$this->mf_istree = true;
		$this->mf_timestamp = true;
		$this->mf_add = false;
		$this->mf_del = false;
		$this->mf_createrid = false;
		$this->caption = "Модули";
		return 0;
	}

	function _create()
	{
		parent::_create();
		
		$this->fields['name'] = array('type' => 'varchar', 'width' => 64,'attr' => 'NOT NULL');
		$this->fields['tablename'] = array('type' => 'varchar', 'width' => 128,'attr' => 'NOT NULL');
		$this->fields['path'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL');
		$this->fields['ver'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields['typemodul'] = array('type' => 'tinyint', 'width' => 2, 'attr' => 'NOT NULL');

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['tablename'] = array('type' => 'text','readonly' => 1, 'caption' => 'Таблица');
		$this->fields_form['path'] = array('type' => 'text','readonly' => 1, 'caption' => 'Путь');
		$this->fields_form['ver'] = array('type' => 'text','readonly' => 1, 'caption' => 'Версия');
		$this->fields_form['typemodul'] = array('type' => 'list', 'listname'=>'typemodul', 'readonly' => 1, 'caption' => 'Описание');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');
		$this->create_child('modulgrp');

		$this->_enum['typemodul'] = array(
			0=>'Системный модуль',
			1=>'Расширенный системный модуль',
			2=>'Модуль',
			5=>'Дочерние модули');

		$this->enum['modulinc'] = array(
			1=>array('path'=>$this->_CFG['_PATH']['extcore'],'name'=>'WEPext - '),
			3=>array('path'=>$this->_CFG['_PATH']['ext'],'name'=>'EXT - ')
		);

		$this->ordfield = 'typemodul,name';
	}

	public function _checkmodstruct() {
		$check_result = parent::_checkmodstruct();
		if (isset($check_result['err']))
			return array('err' => $check_result['err']);
			
		//$q_query=array();
//		$this->SQL->_iFlag = true;
		$this->moduldir = array();
		$this->def_update_records = array();
		$result = $this->SQL->execSQL('SELECT * FROM '.$this->tablename);
			if ($result->err) $this->_message($result->err);
		$this->data = array();
		while ($row = $result->fetch_array()){
			$this->data[$row['id']] = $row;
		}
		$dir = dir($this->_CFG['_PATH']['extcore']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..' && $pos=strpos($entry, '.class')) {
				$entry = substr($entry, 0, $pos);
				if($entry!='' and _modulExists($entry)) {
					$pathm = '1:'.$entry.'.class/'.$entry.'.class.php';
					$this->moduldir[$entry] = '';
					$class_ = NULL;
					if($this->_cl!=$entry) {
						if(_new_class($entry,$class_)) {
							$this->_constr_childs($class_);
							$temp_check_result = $class_->_checkmodstruct();
							if (isset($temp_check_result['err']))
								return array($temp_check_result['err']);
							elseif (!empty($temp_check_result['list_query']))
								$check_result['list_query'] = array_merge($check_result['list_query'], $temp_check_result['list_query']);
						}
					}else {
						$class_ = &$this;
						$this->_constr_childs($class_,$pathm);
					}
					if(!isset($this->data[$entry]))
						$this->def_records[] = array('id'=>$entry,'name'=>$class_->caption,'parent_id'=>'','tablename'=>$class_->tablename, 'typemodul'=>0,'path'=>$pathm);
					else//if($class_->ver!=$this->data[$entry]['ver'] or $this->_cl==$entry)
						$this->def_update_records[$entry] = array('parent_id'=>'','tablename'=>$class_->tablename, 'typemodul'=>0,'path'=>$pathm);
				}
			}
		}
		$dir->close();

		$dir = dir($this->_CFG['_PATH']['ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..' && $pos=strpos($entry, '.class')) {
				$entry = substr($entry, 0, $pos);
				if($entry!='' and _modulExists($entry)) { 
					$this->moduldir[$entry] = '';
					$class_ = NULL;
					if(_new_class($entry,$class_)) {
						$pathm = '3:'.$entry.'.class/'.$entry.'.class.php';
						if(!isset($this->data[$entry]) and $class_->showinowner) 
							$this->def_records[] = array('id'=>$entry,'name'=>$class_->caption.' ['.$entry.']','parent_id'=>'','tablename'=>$class_->tablename, 'typemodul'=>2,'path'=>$pathm);
						else//if($class_->ver!=$this->data[$entry]['ver'])
							$this->def_update_records[$entry] = array('parent_id'=>'','tablename'=>$class_->tablename, 'typemodul'=>2,'path'=>$pathm);
						$this->_constr_childs($class_);
						$temp_check_result = $class_->_checkmodstruct();
						if (isset($temp_check_result['err']))
							return array($temp_check_result['err']);
						elseif (!empty($temp_check_result['list_query']))
							$check_result['list_query'] = array_merge($check_result['list_query'], $temp_check_result['list_query']);
					}
				}
			}
		}

		if(count($this->def_records)) {$this->_insertDefault();$this->def_records=array();}
		$dir->close();
		$list_query = array();
		foreach($this->def_update_records as $k=>$r) {
//			$this->SQL->execSQL('UPDATE `'.$this->tablename.'` SET `parent_id`="'.$r['parent_id'].'",`tablename`="'.$r['tablename'].'",`typemodul`="'.$r['typemodul'].'",`path`="'.$r['path'].'" WHERE id="'.$k.'"');
			$list_query[][0] = 'UPDATE `'.$this->tablename.'` SET `parent_id`="'.$r['parent_id'].'",`tablename`="'.$r['tablename'].'",`typemodul`="'.$r['typemodul'].'",`path`="'.$r['path'].'" WHERE id="'.$k.'"';
		}
		
		if (!empty($list_query))
			$check_result['list_query'] = array_merge($check_result['list_query'], $list_query);

//		return 0;
		return $check_result;

		
	}

	function _constr_childs(&$class_,$pathm='') {
		if(count($class_->childs)) {
			foreach($class_->childs as $k=>&$r) {
				$this->moduldir[$k] = $class_->_cl;
				if(!isset($this->data[$k]) and $r->showinowner) 
					$this->def_records[] = array('id'=>$k,'name'=>$r->caption.' ['.$k.']','parent_id'=>$class_->_cl,'tablename'=>$r->tablename, 'typemodul'=>5);
				else//if($r->ver!=$this->data[$k]['ver'])
					$this->def_update_records[$k] = array('parent_id'=>$class_->_cl,'tablename'=>$r->tablename, 'typemodul'=>5,'path'=>$pathm);
				$this->_constr_childs($r,$pathm);
			}
		}
		return 0;
	}

	function userPrm($ugroup_id=0) {
		$result = $this->SQL->execSQL('SELECT t1.*,t2.access, t2.mname FROM '.$this->tablename.' t1 LEFT Join '.$this->childs['modulgrp']->tablename.' t2 on t2.owner_id=t1.id and t2.ugroup_id='.$ugroup_id.' where t1.active=1 ORDER BY ordind');
		if ($result->err) $this->_message($result->err);
		$this->data = array();
		while ($row = $result->fetch_array()){
			$this->data[$row['id']]['active'] = $row['active'];
			$this->data[$row['id']]['access'] = array_flip(explode('|',substr(substr($row['access'],0,-1),1)));
			if($row['mname'])
				$this->data[$row['id']]['name'] = $row['mname'];
			else
				$this->data[$row['id']]['name'] = $row['name'];
			$this->data[$row['id']]['ver'] = $row['ver'];
		}
		return $this->data;
	}

	function _UpdItemModul($param) {
		$ret = parent::_UpdItemModul($param);
		//if($ret[1]) {
		//	session_unset();
		//}
		return $ret;
	}

}

class modulgrp_class extends kernel_class {

	function _set_features()
	{
		if (parent::_set_features()) return 1;
		$this->mf_timestamp = true;
		$this->mf_add = false;
		$this->mf_del = false;
		$this->mf_createrid = false;
		return 0;
	}

	function _create()
	{
		parent::_create();
		$this->caption = "Привелегии";

		$this->_unique['ou'] = array('owner_id','ugroup_id');
		
		$this->_enum['access'] = array(
			0=>'нет',
			1=>'Чтение (Все)',
			2=>'Чтение (Только свои)',
			3=>'Редактирование (Все)',
			4=>'Редактирование (Только свои)',
			5=>'Удаление (Все)',
			6=>'Удаление (Только свои)',
			7=>'Отключение (Все)',
			8=>'Отключение (Только свои)',
			9=>'Добавление',
			10=>'Сортировка',
			11=>'Переустановка модуля',
			12=>'Переиндексация модуля',
			13=>'Настроика модуля',
			14=>'Проверка структуры модуля'
		);

		$this->fields["name"] = array("type" => "varchar", "width" => 32,"attr" => "NOT NULL");
		$this->fields["mname"] = array("type" => "varchar", "width" => 64,"attr" => "NOT NULL");
		$this->fields["ugroup_id"] = array("type" => "int", "width" => 11,"attr" => "NOT NULL");
		$this->fields["access"] = array("type" => "varchar", "width" => 128,"attr" => "NOT NULL");

		$this->fields_form["name"] = array("type" => "text","readonly" => 1, "caption" => "Группа");
		$this->fields_form["mname"] = array("type" => "text", "caption" => "Название модуля");
		$this->fields_form["access"] = array("type" => "list",'multiple'=>2,"listname"=>"access", "caption" => "Права доступа");

	}

	function _checkmodstruct() {
		global $UGROUP;
		$check_result = parent::_checkmodstruct();
		if (isset($check_result['err']))
			return array('err' => $check_result['err']);
			
		if(!$UGROUP)
			if(!_new_class('ugroup',$UGROUP))
				return array('err' => $this->getMess('_recheck_err'));
				
		$q_query =$data=$data_owner=array();
		$result = $this->SQL->execSQL('SELECT * FROM '.$this->tablename);
		if (!$result->err)
			while ($row = $result->fetch_array())
				$data[$row['owner_id']][$row['ugroup_id']] = $row;

		$result = $this->SQL->execSQL('SELECT * FROM '.$UGROUP->tablename.' WHERE level>0');
		if ($result->err) return $this->_message($result->err);
		$grpdata[0] = array('name'=>'Аноним');
		while ($row = $result->fetch_array())
			$grpdata[$row['id']] = $row;

		$result = $this->SQL->execSQL('SELECT * FROM '.$this->owner->tablename);
		if (!$result->err)
			while ($row = $result->fetch_array())
				$data_owner[$row['id']] = $row['name'];

		foreach($data_owner as $kd=>$rd) {
			foreach($grpdata as $k=>$r){
				if(!isset($data[$kd][$k])) {
					$this->def_records[] = array('owner_id'=>$kd,'ugroup_id'=>$k,'name'=>$r['name']);
				}
			}
			if(isset($data[$kd])) {
				foreach($data[$kd] as $k=>$r){
					if(!isset($grpdata[$k])){
						$q_query[][0] = 'DELETE FROM '.$this->tablename.' WHERE id='.$r['id'].';';
					}elseif($grpdata[$k]['name']!=$r['name'])
						$q_query[][0] = 'UPDATE '.$this->tablename.' SET name="'.$grpdata[$k]['name'].'" WHERE id="'.$r['id'].'" ; ';
				}
			}
		}
		
//		if(count($q_query)) foreach($q_query as $row) $this->SQL->execSQL($row);
		if(count($this->def_records)) {$this->_insertDefault();$this->def_records=array();}
	
		if (!empty($check_result['list']))
			$q_query = array_merge($check_result['list'], $q_query);
	
		return array('list_query' => $q_query);
//		return 0;
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
