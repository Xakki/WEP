<?
class subscribe_class extends kernel_class {

	protected function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_actctrl = true;
		return 0;
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

		//$this->fields_form['creater_id'] = array('type' => 'list', 'listname'=>array('class'=>'users'),'caption' => 'Пользователи', 'mask' =>array('min'=>1,'usercheck'=>2));
		$this->fields_form['city'] = array('type' => 'infoinput','caption' => 'Выбирите город');
		$this->fields_form['param'] = array('type' => 'infoinput','caption' => 'Параметры поиска');
		$this->fields_form['email'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'E-mail', 'mask'=>array('name'=>'email','min' => '7','eval'=>'$_SESSION["user"]["email"]'));
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
		$_tpl['script'] .= '<script type="text/javascript" src="'.$this->_CFG['_HREF']['_script'].'jquery.ui.js"></script>
		<script type="text/javascript" src="'.$this->_CFG['_HREF']['_script'].'jquery.ui.widgets.js"></script>';
		$_tpl['styles'] .= '<link rel="stylesheet" href="'.$this->_CFG['_HREF']['_style'].'jquery-ui-redmond.css" type="text/css"/>';
		/*if(!isset($_POST['sbmt']) and (int)$data['rubric'])
			$_tpl['onload'] .='boardrubric(\'rubric\''.($this->id?','.$this->id:'').');';
		*/
		$mess = array();
		if($this->id){
			$result = $this->SQL->execSQL('SELECT * FROM city WHERE id='.(int)$data['city']);
			if(!$result->err and $row = $result->fetch_array()){
				$valcity = $row['id'];
				$namecity = $row['name'].($row['region_name_ru']?', '.$row['region_name_ru']:'');
			}
		}
		else{
			if($data['city'] and $data['_city']) {
				$valcity = $data['city'];
				$namecity = $data['_city'];
			}else {
				$valcity = $CITY->id;
				$namecity = ($valcity?$CITY->name:'');
			}
			if($this->countThisCreate()>=$_SESSION['user']['paramsubsc']) {
				$this->fields_form = array();
				$mess[] = array('name'=>'alert', 'value'=>'Ваш лимит подписок ('.$_SESSION['user']['paramsubsc'].') - исчерпан. Вы можете отредактировать или удалить ваши существующие подписки, в панеле управления');
				return $mess;
			}
		}
		$this->fields_form['city'] = array(
			'type' => 'ajaxlist',
			'value'=>$valcity,//значение
			'valuetxt'=>$namecity,//текст
			'valuedef'=>'Введите название города или региона',//текст заглушка
			'lablestyle'=>($valcity?'display: none;':''),
			'csscheck'=>($valcity?'accept':'reject'),
			'mask' =>array('min'=>1), 
			'caption' => 'Город');
		/*$this->fields_form['city'] = array(
			'type' => 'infoinput',
			'value'=>$valcity, 
			'caption' => 'Поиск в <a class="selectcity" href="/city.html" onclick="return JSHRWin(\''.$this->_CFG['_HREF']['siteJS'].'?_view=addcity\',\'\')">'.(!$namecity?'Вся Россия':$namecity).'</a>');*/
		
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
				if(!count($RUBRIC->data2)){
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
			'caption' => 'Параметры поиска <a class="selectcity" href="/list.html" onclick="return JSHRWin(\''.$this->_CFG['_HREF']['siteJS'].'?_view2=subscribeparam&amp;\'+$(\'form input[name=param]\').val(),\'\')">'.(!$nameparam?'Выбрать':$nameparam).'</a>');
		$mess = parent::kPreFields($data,$param);
		if(!$this->id)
			unset($this->fields_form['active']);

		//if(!isset($_SESSION['user']['id'])) {
		//	$this->form['captha']['comment'] = '';
		//}

		return $mess;
	}
}


?>