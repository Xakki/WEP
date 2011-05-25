<?
class subscribe_class extends kernel_extends {

	protected function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->locallang['default']['add_name'] = 'Оформить подписку';
		$this->locallang['default']['_saveclose'] = 'Подписаться';
		return true;
	}

	protected function _create() {
		parent::_create();
		$this->caption = 'Подписка';
		$this->_listnameSQL ='concat(id,"-",email)';

		$this->fields['city'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['param'] = array('type' => 'varchar', 'width' => 254,'attr' => 'NOT NULL');
		$this->fields['email'] = array('type' => 'varchar', 'width' => 40, 'attr' => 'NOT NULL');
		//$this->fields['phone'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['date'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL');
		$this->fields['period'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');

		//$this->fields_form[$this->mf_createrid] = array('type' => 'list', 'listname'=>array('class'=>'users'),'caption' => 'Пользователи', 'mask' =>array('min'=>1,'usercheck'=>2));

		$this->fields_form['city'] = array(
			'type' => 'ajaxlist',
			'label'=>'Введите название города или региона',
			'listname'=>array('tablename'=>'city','where'=>'tx.active=1','nameField'=>'IF(tx.region_name_ru!=\'\',concat(tx.name,", ",tx.region_name_ru),tx.name)','ordfield'=>'tx.parent_id, tx.region_name_ru, tx.name'), 
			'caption' => 'Город', 
			'mask' =>array('min'=>1,'onetd'=>'Рубрика','filter'=>1));
		$this->fields_form['param'] = array('type' => 'infoinput','caption' => 'Параметры поиска');
		$this->fields_form['email'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'E-mail', 'mask'=>array('name'=>'email','min' => '7','eval'=>'$this->_CFG["userData"]["email"]'));
		//$this->fields_form['phone'] = array('type' => 'text', 'caption' => 'Контактные телефоны','min2'=>1, 'mask'=>array('name'=>'phone2','onetd'=>'none'));
		$this->fields_form['date'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата рассылки', 'mask'=>array('eval'=>'time()'));
		$this->fields_form['period'] = array('type' => 'list', 'listname'=>'period','caption' => 'Период рассылки','mask'=>array('min'=>1));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл','default'=>1);

		$this->_enum['period']=array(
			864000=>'10 дней',
			604800=>'7 дней',
			432000=>'5 дней',
			259200=>'3 дня',
			172800=>'2 дня',
			86400=>'1 день',
			43200=>'12 часов'
		);

		$this->ordfield = 'id DESC';

	}

	public function kPreFields(&$data,&$param) {
		global $_tpl,$CITY;
		
		if(!$data['param'])
			$data['param'] = htmlentities(substr(strstr($_SERVER['REQUEST_URI'],'?'),1));

		$nameparam='';
		if($data['param'] and $data['param']!='') {
			$arrparam = array();
			$temp=explode('&',$data['param']);
			foreach($temp as $row) {
				$temp2= explode('=',$row);
				$arrparam[$temp2[0]]=$temp2[1];
			}
			if($arrparam['rubric']) {
				global $RUBRIC;
				if(!$RUBRIC) 
					$RUBRIC = new rubric_class($this->SQL);
				if(!isset($RUBRIC->data2) or !count($RUBRIC->data2)){
					$RUBRIC->RubricCache();
				}
				$nameparam = $RUBRIC->data2[$arrparam['rubric']]['name'];
			}
			if($arrparam['type']) {
				global $BOARD;
				if(!$BOARD) 
					$BOARD = new board_class($SQL);
				$nameparam .= '; '.$BOARD->_enum['type'][$arrparam['type']];
			}
			if($arrparam['cost']) {
				$nameparam .= '; от '.$arrparam['cost'].' руб.';
			}
			if($arrparam['cost_2']) {
				$nameparam .= '; до '.$arrparam['cost_2'].' руб.';
			}
			if(count($arrparam)>2) {
				$nameparam .= '; ...';
			}
		}
		$this->fields_form['param'] = array(
			'type' => 'infoinput',
			'value'=>$data['param'],
			'caption' => 'Параметры поиска <a class="selectcity" href="/list.html" onclick="return JSWin({\'href\':\''.$this->_CFG['_HREF']['siteJS'].'?_view2=subscribeparam&amp;\'+$(\'form input[name=param]\').val()})">'.(!$nameparam?'Выбрать':$nameparam).'</a>');
		$mess = parent::kPreFields($data,$param);
		if(!$this->id)
			unset($this->fields_form['active']);
		return $mess;
	}

	public function _UpdItemModul($param) {
		$mess = array();
		$this->listfields = array('count(id) as cnt');
		$this->clause = 'WHERE '.$this->mf_createrid.'="'.$_SESSION['user']['id'].'"';
		$this->_list();
		if($this->data[0]['cnt']>=$_SESSION['user']['paramsubsc']) {
			$mess[] = array('name'=>'alert', 'value'=>'Ваш лимит подписок ('.$_SESSION['user']['paramsubsc'].') - исчерпан. Вы можете отредактировать или удалить ваши существующие подписки, в панеле управления');
			return $mess;
		}
		if(!count($mess)) {
			$xml = parent::_UpdItemModul($param);
			return $xml;
		}else
			return array(array('messages'=>$mess),-1);
	}

}


?>