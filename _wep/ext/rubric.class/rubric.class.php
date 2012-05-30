<?php
class rubric_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->ver = '0.0.1';
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Рубрикатор';
		$this->data_path = 
			$this->data3 = 
			$this->data2 = array();
		$this->_dependClass = array('formlist');
		$this->v_img = 'img_'.$this->_cl;
		$this->_enum['thumb'] = array(
			'original'=>'original',
			'resizecrop'=>'resizecrop',
			'resize'=>'resize'
		);
		return true;
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		

		$this->config['thumbs'] = array(
			'pref=org_'=> 'original',
			'w=60;h=;pref=;path='=> 'resizecrop',
		);
		$this->config['imgsize'] = 3000;

		$this->config_form['thumbs'] = array('type' => 'list', 'listname'=>'thumb', 'multiple'=>3, 'caption' => 'Модификации изображений','mask'=>array('maxarr'=>10));
		$this->config_form['imgsize'] = array('type' => 'int', 'caption' => 'Максим. размер загружаемых изображений');
	}

	public function _create() {
		parent::_create();

		$this->attaches[$this->v_img] = array(
			'mime' => array('image'), 'thumb'=>array(),
			/*'thumb'=>array(
				array('type'=>'original', 'pref'=>'org_'),
				array('type'=>'resizecrop', 'w'=>'60', 'h'=>false, 'pref'=>'', 'path'=>'')
			),*/
			'maxsize'=>$this->config['imgsize'], 
			'path'=>'');

		foreach($this->config['thumbs'] as $k=>$r) {
			$k = explode(';',$k);
			$new = array('type'=>$r);
			foreach($k as $p) {
				$p = explode('=',$p);
				$new[$p[0]] = $p[1];
			}
			$this->attaches[$this->v_img]['thumb'][] = $new;
		}
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['lname'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['checked'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL','default'=>'0');
		$this->fields['dsc'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['txt'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['cnt'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>'0');

		$this->unique_fields['name'] = 'name';
		$this->unique_fields['lname'] = 'lname';
		$this->index_fields['checked'] = 'checked';
		$this->index_fields[$this->v_img] = $this->v_img;
		
		$this->selFields = 't1.id,t1.name,t1.lname as path,t1.parent_id,t1.ordind,t1.checked,t1.active,t1.cnt,t1.'.$this->v_img.' as img';//
	}


	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название рубрики');
		$this->fields_form['lname'] = array('type' => 'text', 'caption' => 'Название латиницей', 'mask'=>array('name'=>'/[^0-9A-Za-zА-Яа-яЁё_\- ]/u', 'min'=>2));
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительская рубрика','mask' =>array('fview'=>1));
		$this->fields_form[$this->v_img] = array('type'=>'file','caption'=>'Картинка','del'=>1, 'mask'=>array('height'=>80), 'comment'=>static_main::m('_file_size').$this->attaches[$this->v_img]['maxsize'].'Kb');	
		$this->fields_form["dsc"] = array("type" => "textarea", "caption" => "Описание",'mask' =>array('name'=>'all'));
		$this->fields_form["txt"] = array("type" => "ckedit", "caption" => "Полный текст",'mask' =>array('fview'=>1,'name'=>'all'));
		$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Доступ','comment'=>'разрешить для выбора в списке');
		$this->fields_form["ordind"] = array("type" => "int", "caption" => "Сортировка");
		$this->fields_form["cnt"] = array("type" => "int", 'readonly'=>true, "caption" => "Кол-во элм.");
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

	}

	function _childs() {
		$this->create_child('rubricparam');
	}

	function fCache() {
		if(count($this->data3)) return true;

		$this->data2=$this->data=array();
		$clause = 'SELECT '.$this->selFields.' FROM '.$this->tablename.' t1 WHERE t1.active=1 ORDER BY t1.parent_id,t1.ordind';
		$result = $this->SQL->execSQL($clause);
		if(!$result->err) {
			$ar_last = array();
			while ($row = $result->fetch()){
				$row['img'] = $this->_get_file($row['id'],$this->v_img,$row['img']);
				$row['orig_img'] = $this->_get_file($row['id'],$this->v_img,$row['img'],1);
				
				$this->data2[$row['id']] = $row;
				$this->data[$row['parent_id']][$row['id']] = $row['name'];
				$this->data3[$row['parent_id']][$row['id']] = &$this->data2[$row['id']];
				if($row['parent_id']) {
					if(isset($this->data2[$row['parent_id']])) {
						$tempid = $this->data2[$row['parent_id']]['parent_id'];
						$this->data3[$tempid] [$row['parent_id']] ['cnt'] += (int)$row['cnt'];
					} else
						$ar_last[] = $row;
				}
				$this->data_path[$row['path']] = $row['id'];
			}
			if(count($ar_last)) {
				foreach($ar_last as $row) {
					if(isset($this->data2[$row['parent_id']])) {
						$tempid = $this->data2[$row['parent_id']]['parent_id'];
						$this->data3[$tempid] [$row['parent_id']] ['cnt'] += (int)$row['cnt'];
					}
				}
			}
		}
		return 0;	
	}

	function fDisplay($start=0,$select=0) {
		$this->fCache();
		return $this->_forlist($this->data3,$start,$select);
	}

	function getPath($id, $page, $startId=0) {
		global $_tpl;
		$temp = $id;
		$tpath= array();
		while(isset($this->data2[$temp])) {
			$_tpl['keywords'] .= ', '.$this->data2[$temp]['name'];
			$tpath[$this->data2[$temp]['path'].'/'.$page] = array('name'=>$this->data2[$temp]['name']);
			$temp=$this->data2[$temp]['parent_id'];
			if($startId==$temp) break;
		}
		return array_reverse($tpath);
	}

	function fItem($id,$field='*') {
		$data = $this->qs($field,'WHERE id="'.$id.'"');
		if($field!="*") {
			if(count($data))
				return $data[0][$field];
			return '';
		} else {
			if(count($data))
				return $data[0];
			return array();
		}
	}

////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////

	function simplefCache() {
		if(isset($this->data2) and count($this->data2)) return true;
		$this->data2=$this->data=array();
		$clause = 'SELECT '.$this->selFields.' FROM '.$this->tablename.' t1 WHERE t1.active=1 ORDER BY t1.parent_id,t1.ordind';
		$result = $this->SQL->execSQL($clause);
		if(!$result->err)
			while ($row = $result->fetch()){
				$this->data2[$row['id']] = $row;
				$this->data[$row['parent_id']][$row['id']] = $row['name'];
				$this->data_path[$row['path']] = $row['id'];
		}
		return true;	
	}

	public function _add($data=array(), $flag_select=true) {

		if(!isset($data['lname']) or !$data['lname'])
			$data['lname'] = $this->transliteRuToLat($data['name']);

		if($ret = parent::_add($data, $flag_select)) {
		}
		return $ret;
	}

	public function _update($data=array(), $where=null, $flag_select=true) {

		if(!isset($data['lname']) or !$data['lname'])
			$data['lname'] = $this->transliteRuToLat($data['name']);

		if($ret = parent::_update($data, $where, $flag_select)) {
		}
		return $ret;
	}
}

class rubricparam_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Параметры';
		$this->singleton = false;
		$this->tablename = $this->owner->_cl.'_param';
		//print_r("TODO : Доработать  проблему вызова подкласса 2 раза ".$this->tablename);
		return true;
	}

	function getTypeForm($type) {
		if($type<10)
			return 'checkbox';
		elseif($type<30)
			return 'int';
		elseif($type<50)
			return 'int';
		elseif($type<70)
			return 'list';
		elseif($type<80)
			return 'text';
		else
			return 'float';
	}

	function _create() {
		parent::_create();

		$this->index_fields['slist'] = array('owner_id','active');

		$this->_enum['typelist'] = array(
			0=>'Простой список',
			1=>'AJAX список',
			2=>'CHECKBOX список',
		);
		$this->_enum['type'] = array(
			0=>'CheckBox0',
			1=>'CheckBox1',
			2=>'CheckBox2',
			3=>'CheckBox3',
			4=>'CheckBox4',
			5=>'CheckBox5',
			6=>'CheckBox6',
			7=>'CheckBox7',
			8=>'CheckBox8',
			10=>'Целое(4)0',
			11=>'Целое(4)1',
			12=>'Целое(4)2',
			13=>'Целое(4)3',
			14=>'Целое(4)4',
			15=>'Целое(4)5',
			16=>'Целое(4)6',
			17=>'Целое(4)7',
			18=>'Целое(4)8',
			20=>'Целое(11)0',
			21=>'Целое(11)1',
			40=>'Год 1',
			41=>'Год 2',
			50=>'Cписок 0',
			51=>'Cписок 1',
			52=>'Cписок 2',
			53=>'Cписок 3',
			54=>'Cписок 4',
			55=>'Cписок 5',
			56=>'Cписок 6',
			57=>'Cписок 7',
			58=>'Cписок 8',
			59=>'Cписок 9',
			70=>'Текст(254)0',
			71=>'Текст(254)1',
			72=>'Текст(128)2',
			73=>'Текст(64)3',
			74=>'Текст(64)4',
			75=>'Текст(64)5',
			80=>'Дробное0',
			81=>'Дробное1',
			82=>'Дробное2',
			83=>'Дробное3',
			84=>'Дробное4',
			85=>'Дробное5',
			);

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields["type"] = array("type" => "tinyint", "width" =>4, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["typelist"] = array("type" => "tinyint", "width" =>4, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["formlist"] = array("type" => "tinyint", "width" =>4, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields['constrn'] = array("type" => "tinyint", "width" =>1, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields['edi'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['def'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields["min"] = array("type" => "int", "width" =>8, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["max"] = array("type" => "int", "width" =>8, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["step"] = array("type" => "int", "width" =>8, "attr" => 'NOT NULL', 'default'=>'1');
		$this->fields['mask'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['maskn'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['comment'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');

		# attaches

		# memo
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		# fields
		$this->fields_form['owner_id'] = array('type' => 'list', 'listname'=>'ownerlist', 'caption' => 'Рубрика','mask' =>array('fview'=>1));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' =>array('min'=>1));
		$this->fields_form["type"] = array("type" => "list", "listname"=>"type", "caption" => "Тип параметра", 'mask'=>array('sort'=>1), 'onchange'=>'if(this.value>=50 &amp;&amp; this.value&lt;60) jQuery(\'#tr_formlist, #tr_typelist\').show(); else jQuery(\'#tr_formlist, #tr_typelist\').hide();');
		$this->fields_form['typelist'] = array('type' => 'list', 'listname'=>'typelist', 'caption' => 'Вид списка', 'style'=>'background:#e1e1e1;');
		$this->fields_form['formlist'] = array("type" => "list",'listname'=>array('tablename'=>'formlist'), 'caption' => 'Список', 'style'=>'background:#e1e1e1;');
		$this->fields_form['constrn'] = array('type' => 'checkbox', 'caption' => 'В имени');
		$this->fields_form['edi'] = array('type' => 'text', 'caption' => 'Ед.');
		$this->fields_form['def'] = array('type' => 'text', 'caption' => 'Default','comment'=>'Если в начале прописать "eval=", то будет выполнятся команда');
		$this->fields_form['min'] = array('type' => 'int', 'caption' => 'Min','comment'=>'Минимум символов или минимальное число, 0 - поле не обязательное');
		$this->fields_form['max'] = array('type' => 'int', 'caption' => 'Max','comment'=>'Максимум символов или максимальное число, 0 - максимум соответствует типу');
		$this->fields_form['step'] = array('type' => 'int', 'caption' => 'Шаг','comment'=>'Если "Тип параметра" целое число, нужен шаг для поиска по параметрам');
		$this->fields_form['mask'] = array('type' => 'text', 'caption' => 'Match', 'comment'=>'(поиск точного соответствия)', 'mask' =>array('name'=>'all'), 'comment'=>'/^(http:\/\/)?([A-Za-zЁёА-Яа-я\.]+\.)?[0-9A-Za-zЁёА-Яа-я\-\_]+\.[A-Za-zЁёА-Яа-я]+[\/0-9A-Za-zЁёА-Яа-я\.\-\_\=\?\&]*$/u');
		$this->fields_form['maskn'] = array('type' => 'text', 'caption' => 'NoMatch', 'comment'=>'(поик не соответствия)', 'mask' =>array('name'=>'all'), 'comment'=>'/[^0-9A-Za-zЁёА-Яа-я:\/\.\-\_\=\?\&]/u');
		$this->fields_form['comment'] = array('type' => 'text', 'caption' => 'Комменты', 'mask' =>array('name'=>'all'));
		$this->fields_form['ordind'] = array('type' => 'int', 'caption' => 'ORD','mask'=>array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');
		
		$this->formSort = array(
			'Основное'=>array('name','type','typelist','formlist','edi','active'),
			'Дополнительно'=>array('owner_id','constrn','def','min','max','step','mask','maskn','comment','ordind'),
		);

	}

	function kPreFields(&$data, &$param=array(), &$fields_form=NULL) {
		$mess = parent::kPreFields($data, $param, $fields_form);
		if($data['type']<50 or $data['type']>=60) {
			$fields_form['typelist']['style'] = $fields_form['formlist']['style'] .='display:none;';
		}
		return $mess;
	}

}

