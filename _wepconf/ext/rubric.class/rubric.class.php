<?
class rubric_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Рубрики';
		$this->data_path = 
			$this->data3 = array();
		return true;
	}

	function _create() {
		parent::_create();

		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['lname'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['rname'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['checked'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL','default'=>'0');
		$this->fields['imgpos'] = array('type' => 'int', 'width' => 3, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['chtext'] = array('type' => 'text', 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['zerocost'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default'=>'');

		$this->index_fields['name'] = 'name';
		$this->unique_fields['rname'] = 'rname';
		$this->unique_fields['lname'] = 'lname';
		$this->index_fields['checked'] = 'checked';
		$this->index_fields['imgpos'] = 'imgpos';
		
		$this->selFields = 't1.id,t1.name,t1.rname,t1.lname,t1.parent_id,t1.imgpos,t1.zerocost,t1.ordind,t1.checked,t1.active';
		//id,name,rname,lname,parent_id,imgpos,ordind,checked,active
	}


	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название рубрики');
		$this->fields_form['rname'] = array('type' => 'text', 'caption' => 'Адресс на русском');
		$this->fields_form['lname'] = array('type' => 'text', 'caption' => 'Адресс латиницей');
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительская рубрика','mask' =>array('fview'=>1));
		$this->fields_form["imgpos"] = array("type" => "int", "caption" => "Icon");
		$this->fields_form["ordind"] = array("type" => "int", "caption" => "Сортировка");
		$this->fields_form["chtext"] = array("type" => "textarea", "caption" => "Текст",'mask' =>array('name'=>'all'));
		$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Доступ','comment'=>'разрешить для подачи объявления');
		$this->fields_form['zerocost'] = array('type' => 'text', 'caption' => 'Спец. цена');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

	}

	function _childs() {
		$this->create_child('param');
		$this->create_child("countb");
	}

	function RubricCache() {
		$this->data2=$this->data=array();$cls='';
		_new_class('city',$CITY);
		if(count($CITY->citylist))
			$cls = ' and t2.city IN ('.$CITY->id.') ';
		else
			$cls = ' and t2.city=0';
		$clause = 'SELECT '.$this->selFields.',sum(t2.cnt) as cnt FROM '.$this->tablename.' t1 LEFT JOIN '.$this->childs['countb']->tablename.' t2 ON t1.id=t2.owner_id '.$cls.' WHERE t1.active=1 GROUP BY t1.id ORDER BY t1.parent_id,t1.ordind';
		$result = $this->SQL->execSQL($clause);
		if(!$result->err) {
			$ar_last = array();
			while ($row = $result->fetch_array()){
				$this->data2[$row['id']] = $row;
				$this->data[$row['parent_id']][$row['id']] = $row['name'];
				$this->data3[$row['parent_id']][$row['id']] ['name'] = $row['name'];
				$this->data3[$row['parent_id']][$row['id']] ['imgpos'] = $row['imgpos'];
				$this->data3[$row['parent_id']][$row['id']] ['cnt'] = $row['cnt'];
				$this->data3[$row['parent_id']][$row['id']] ['path'] = $row['lname'];
				if($row['parent_id']) {
					if(isset($this->data2[$row['parent_id']])) {
						$tempid = $this->data2[$row['parent_id']]['parent_id'];
						$this->data3[$tempid] [$row['parent_id']] ['cnt'] += (int)$row['cnt'];
					} else
						$ar_last[] = $row;
				}
			}
			if(count($ar_last)) {
				foreach($ar_last as $row) {
					$tempid = $this->data2[$row['parent_id']]['parent_id'];
					$this->data3[$tempid] [$row['parent_id']] ['cnt'] += (int)$row['cnt'];
				}
			}
		}
		return 0;	
	}

	function simpleRubricCache() {
		if(isset($this->data2) and count($this->data2)) return 0;
		$this->data2=$this->data=array();
		$clause = 'SELECT '.$this->selFields.' FROM '.$this->tablename.' t1 WHERE t1.active=1 ORDER BY t1.parent_id,t1.ordind';
		$result = $this->SQL->execSQL($clause);
		if(!$result->err)
			while ($row = $result->fetch_array()){
				$this->data2[$row['id']] = $row;
				$this->data[$row['parent_id']][$row['id']] = $row['name'];
				$this->data_path[$row['lname']] = $row['id'];
		}
		return true;	
	}

	function MainRubricDisplay() {
		_new_class('city',$CITY);
		if(!$this->data3) $this->RubricCache();
		$xml='';
		$xml = $this->kData2xml($this->_forlist($this->data3,0),'item');
		return '<main city="'.$CITY->id.'">'.$xml.'</main>';
	}

	function getPath($id) {
		global $PGLIST;
		$temp = $id;
		$tpath= array();
		while(isset($this->data2[$temp])) {
			$PGLIST->pageinfo['keywords'] .= ', '.$this->data2[$temp]['name'];
			$tpath[$this->data2[$temp]['lname'].'/'.$PGLIST->getHref()] = array('name'=>$this->data2[$temp]['name']);
			$temp=$this->data2[$temp]['parent_id'];
		}
		if(count($tpath))
			$PGLIST->pageinfo['path']=$PGLIST->pageinfo['path']+array_reverse($tpath);	
	}

}

class param_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Параметры';
		return true;
	}

	function getTypeForm($type) {
		if($type<10)
			return 'checkbox';
		elseif($type<30)
			return 'int';
		elseif($type<50)
			return 'int';
		elseif($type<60)
			return 'list';
		else
			return 'text';
	}

	function _create() {
		parent::_create();
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
			10=>'Целое(4)0',
			11=>'Целое(4)1',
			12=>'Целое(4)2',
			13=>'Целое(4)3',
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
			//80=>'Дробное0'
			//90=>'Текст0'
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
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

	}

	function kPreFields(&$data,&$param) {
		$mess = parent::kPreFields($data,$param);
		if($data['type']<50 or $data['type']>=60) {
			$this->fields_form['typelist']['style'] = $this->fields_form['formlist']['style'] .='display:none;';
		}
		return $mess;
	}

}


class countb_class extends kernel_extends {
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->showinowner=false;// не показывать
		$this->mf_createrid = false;
		$this->mf_namefields = false;
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Подсчёт';
		$this->fields['city'] = array('type' => 'int', 'width' => 7,'attr' => 'NOT NULL');
		$this->fields['owner_id'] = array('type' => 'int', 'width' => 7,'attr' => 'NOT NULL');
		$this->fields['cnt'] = array('type' => 'int', 'width' => 7,'attr' => 'NOT NULL');

		$this->unique_fields['oc'] = array('owner_id','city');
	}

}
