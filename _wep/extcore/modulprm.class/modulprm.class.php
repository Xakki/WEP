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
		
		$this->fields["name"] = array("type" => "VARCHAR", "width" => 32,"attr" => "NOT NULL");
		$this->fields["tablename"] = array("type" => "VARCHAR", "width" => 100,"attr" => "NOT NULL");

		$this->fields_form["name"] = array("type" => "text", "caption" => "Название");
		$this->fields_form["tablename"] = array("type" => "text","readonly" => 0, "caption" => "Таблица");
		$this->fields_form["active"] = array("type" => "checkbox", "caption" => "Активность");
		$this->create_child("modulgrp");
	}

	public function _checkmodstruct() {
		parent::_checkmodstruct();
		//$q_query=array();
		$this->moduldir = array();
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
					$this->moduldir[$entry] = '';
					if($this->_cl!=$entry) {
						if(_new_class($entry,$class_))
							$this->_constr_childs($class_);
					}else $class_ = &$this;
					if(!isset($this->data[$entry]))
						$this->def_records[] = array('id'=>$entry,'name'=>$class_->caption,'parent_id'=>'','tablename'=>$class_->tablename);
					
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
					if(_new_class($entry,$class_)) {
						if(!isset($this->data[$entry]) and $class_->showinowner) 
							$this->def_records[] = array('id'=>$entry,'name'=>$class_->caption.' ['.$entry.']','parent_id'=>'','tablename'=>$class_->tablename);
						$this->_constr_childs($class_);
					}
				}
			}
		}
		//if(count($q_query)) foreach($q_query as $row) $this->SQL->execSQL($row);

		if(count($this->def_records)) {$this->_insertDefault();$this->def_records=array();}
		$dir->close();
		return 0;
	}

	function _constr_childs(&$class_) {
		if(count($class_->childs)) {
			foreach($class_->childs as $k=>&$r) {
				$this->moduldir[$k] = $class_->_cl;
				if(!isset($this->data[$k]) and $r->showinowner) 
					$this->def_records[] = array('id'=>$k,'name'=>$r->caption.' ['.$k.']','parent_id'=>$class_->_cl,'tablename'=>$r->tablename);
				/*elseif($this->data[$entry]['name']!=$class_->caption.' ['.$entry.']')
					$q_query[] = 'UPDATE '.$this->tablename.'SET name="'.$class_->caption.' ['.$entry.']'.'" WHERE id="'.$entry.'";';*/
				$this->_constr_childs($r);
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
		$this->fields_form["access"] = array("type" => "list",'multiple'=>1,"listname"=>"access", "caption" => "Права доступа");

	}

	function _checkmodstruct() {
		global $UGROUP;
		parent::_checkmodstruct();
		if(!$UGROUP)
			if(!_new_class('ugroup',$UGROUP))
				return 1;
				
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
						$q_query[] = 'DELETE FROM '.$this->tablename.' WHERE id='.$r['id'].';';
					}elseif($grpdata[$k]['name']!=$r['name'])
						$q_query[] = 'UPDATE '.$this->tablename.' SET name="'.$grpdata[$k]['name'].'" WHERE id="'.$r['id'].'" ; ';
				}
			}
		}
		
		if(count($q_query)) foreach($q_query as $row) $this->SQL->execSQL($row);
		if(count($this->def_records)) {$this->_insertDefault();$this->def_records=array();}
		return 0;
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