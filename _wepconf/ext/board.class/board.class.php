<?
class board_class extends kernel_class {

	var $RUBRIC;

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		
		$this->config['onDate'] = 0;
		$this->config['onComm'] = 0;
		$this->config['levelComm'] = 0;
		$this->config['spamtime'] = 24;
		$this->config['defmax'] = 9;
		$this->config['defumax'] = 9;
		$this->config['nomination1'] = '';
		$this->config['nomination2'] = '';
		$this->config['nomination3'] = '';
		$this->config['nomination4'] = '';
		$this->config['nomination5'] = '';

		$this->config_form['onDate'] = array('type' => 'checkbox', 'caption' => 'Показывать согласно периоду?');
		$this->config_form['onComm'] = array('type' => 'list', 'listname'=>'onComm', 'caption' => 'Включить комментарии?');
		$this->config_form['levelComm'] = array('type' => 'int', 'caption' => 'Число подуровней коментария');
		$this->config_form['spamtime'] = array('type' => 'int', 'caption' => 'Часов для спама','comment'=>'Время в течении которого пользователь может подать максимальное число объвлений');
		$this->config_form['defmax'] = array('type' => 'int', 'caption' => 'Макс. объяв. не пользов.','comment'=>'Максимум объявлений за промежуток времени не авторизованному пользователю');
		$this->config_form['defumax'] = array('type' => 'int', 'caption' => 'Макс. объяв. пользов.','comment'=>'Максимум объявлений за промежуток времени авторизованному пользователю по умолчанию если не укзана у группы пользователя');
		$this->config_form['nomination1'] = array('type' => 'varchar', 'caption' => 'Номинация №1','comment'=>'Введите название, чтобы включить номинацию');
		$this->config_form['nomination2'] = array('type' => 'varchar', 'caption' => 'Номинация №2');
		$this->config_form['nomination3'] = array('type' => 'varchar', 'caption' => 'Номинация №3');
		$this->config_form['nomination4'] = array('type' => 'varchar', 'caption' => 'Номинация №4');
		$this->config_form['nomination5'] = array('type' => 'varchar', 'caption' => 'Номинация №5');

	}

	protected function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->caption = 'Объявления';
		$this->mf_statistic = array('Y'=>'count(id)','X'=>'FROM_UNIXTIME(datea,"%Y-%m")','Yname'=>'Кол','Xname'=>'Дата');//-%d
		//$this->reversePageN = true;
		$this->includeJStoFORM = true;
		$this->includeCSStoFORM = true;
		$this->locallang['default']['add'] = 'Объявление добавлено.';
		return true;
	}

	protected function _create() {
		parent::_create();
		$this->_listnameSQL ='SUBSTRING(text,1,30)';
		if(_prmUserCheck(2))
			$thumb = array('type'=>'resize', 'w'=>'1024', 'h'=>'768');
		else
			$thumb = array('type'=>'resize', 'w'=>'800', 'h'=>'600');
			
		$this->attaches['img_board'] = array('mime' => array('image/pjpeg'=>'jpg', 'image/jpeg'=>'jpg', 'image/gif'=>'gif', 'image/png'=>'png'), 'thumb'=>array($thumb,array('type'=>'resizecrop', 'w'=>'80', 'h'=>'100', 'pref'=>'s_', 'path'=>'')),'maxsize'=>3000,'path'=>'');
		$this->attaches['img_board2']=$this->attaches['img_board'];
		$this->attaches['img_board3']=$this->attaches['img_board'];
		$this->attaches['img_board4']=$this->attaches['img_board'];
		$this->attaches['img_board5']=$this->attaches['img_board'];
		$this->attaches['img_board6']=$this->attaches['img_board'];

		$this->fields['city'] = array('type' => 'int', 'width' => 8,'attr' => 'NOT NULL');
		$this->fields['rubric'] = array('type' => 'int', 'width' => 8,'attr' => 'NOT NULL');
		$this->fields['type'] = array('type' => 'int','attr' => 'NOT NULL','default'=>0);
		//$this->fields['name'] = array('type' => 'varchar', 'width' => 70, 'attr' => 'NOT NULL');
		$this->fields['text'] = array('type' => 'text', 'width' => 1024, 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'int', 'width' => 10,'attr' => 'NOT NULL','default'=>0);
		$this->fields['phone'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['email'] = array('type' => 'varchar', 'width' => 40, 'attr' => 'NOT NULL');
		$this->fields['contact'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
		$this->fields['datea'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL');
		$this->fields['period'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['on_comm'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL');
		$this->fields['ip'] = array('type' => 'varchar', 'width' => 15, 'attr' => 'NOT NULL');
		$this->fields['statview'] = array('type' => 'int', 'width' => 9, 'attr' => 'NOT NULL');
		$this->fields['tstamp'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL DEFAULT 0');

		if(!isset($_SESSION['user']['id']))
			$this->mess_form["info"]= array(
				"type" => "alert", 
				"value" => "<a href='/regme.html'>Зарегестрируйтесь</a> и вы получите <a href='/inform.html' title='продлевать на больший срок свои объявления, добовлять до 4х фотографий, подписка на рассылки объявления и тд.'>больше возможностей</a> для рамещения объявления.");

		$this->fields_form['city'] = array(
			'type' => 'ajaxlist',
			'label'=>'Введите название города или региона',
			'listname'=>array('tablename'=>'city','where'=>'tx.active=1','tx.name'=>'IF(tx.region_name_ru!=\'\',concat(tx.name,", ",tx.region_name_ru),tx.name)','ordfield'=>'tx.parent_id, tx.region_name_ru, tx.name'), 
			'caption' => 'Город', 
			'mask' =>array('min'=>1,'onetd'=>'Рубрика','filter'=>1));

		$this->fields_form['creater_id'] = array(
			'type' => 'list', 
			'listname'=>array('class'=>'users','tx.name'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'Пользователи',
			'readonly'=>1,
			'mask' =>array('usercheck'=>1,'onetd'=>'none','filter'=>1));

		$this->fields_form['type'] = array(
			'type' => 'list', 
			'listname'=>'type',
			'caption' => 'Тип объявления',
			'onchange'=>'rclaim(\'type\')',
			'mask' =>array('onetd'=>'none','filter'=>1));

		$this->fields_form['rubric'] = array(
			'type' => 'list', 
			'listname'=>array('class'=>'rubric', 'is_checked'=>true, 'is_tree'=>true, 'where'=>'tx.active=1'),
			'caption' => 'Рубрика',
			'onchange'=>'boardrubric(this)', 
			'mask' =>array('min'=>1,'onetd'=>'close','filter'=>1));

		//$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Заголовок', 'mask' =>array('min'=>1));
		$this->fields_form['text'] = array(
			'type' => 'ckedit', 
			'caption' => 'Тект объявления', 
			'mask' =>array('name'=>'html','min'=>15,'substr'=>150,'onetd'=>'Текст', 'filter'=>1, 
				'replace'=>array($this->_CFG['_repl']['href'],'/\n+/','/\r+/','/\t+/','/<br>/i'),
				'replaceto'=>array('','','','','<br/>'),
				'striptags'=>'<p><i><ul><ol><li><br/><sup><sub>'),
			'paramedit'=>array(
				'toolbar'=>'Board',
				'height'=>250,
				'forcePasteAsPlainText'=>'true',
				'toolbarStartupExpanded'=>'false',
				'extraPlugins'=>"'cntlen'",
				'plugins'=>"'button,contextmenu,enterkey,entities,justify,keystrokes,list,pastetext,popup,removeformat,toolbar,undo'"));
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Цена (руб.)', 'mask'=>array('max'=>8,'onetd'=>'none','filter'=>1,'maxint'=>20000000));
		$this->fields_form['phone'] = array('type' => 'text', 'caption' => 'Контактные телефоны', 'mask'=>array('min2'=>'Необходимо заполнить либо `телефон`, либо `E-mail`', 'name'=>'phone2','onetd'=>'none'));
		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'E-mail', 'mask'=>array('min2'=>'Необходимо заполнить либо `телефон`, либо `E-mail`','name'=>'email','onetd'=>'close','filter'=>1));
		$this->fields_form["img_board"] = array("type"=>"file","caption"=>"Фотография №1",'del'=>1, 'mask'=>array('fview'=>1,'width'=>80,'height'=>100));
		if(_prmUserCheck()) {
			$this->fields_form["img_board6"] = $this->fields_form["img_board5"] = $this->fields_form["img_board4"] = $this->fields_form["img_board3"] = $this->fields_form["img_board2"] = $this->fields_form["img_board"];
			$this->fields_form["img_board2"]["caption"] = "Фотография №2";
			$this->fields_form["img_board3"]["caption"] = "Фотография №3";
			$this->fields_form["img_board4"]["caption"] = "Фотография №4";
			$this->fields_form["img_board5"]["caption"] = "Фотография №5";
			$this->fields_form["img_board6"]["caption"] = "Фотография №6";
		}
		else {
			$this->fields_form["img_board"]['comment'] = 'Размер фото не больше 1500кб.<br/>Зарегестрированным до 6ти фотографий.';
		}

		$this->fields_form["img_board"]['mask']['filter'] = 1;
		$this->fields_form["img_board"]['mask']['fview'] = 0;

		$this->fields_form['showparam'] = array('type' => 'info', 'caption' => '<div class="showparam" onclick="show_params(\'.hideparams\')">Показать дополнительные параметры</div>','style'=>'display:none;');
		$this->fields_form['hideparam'] = array('type' => 'info', 'caption' => '<div class="hideparam" onclick="show_params(\'.hideparams\')">Скрыть дополнительные параметры</div>','style'=>'display:none;');

		$this->fields_form['contact'] = array('type' => 'text', 'caption' => 'Дополнительные контакты', 'comment'=>'ICQ, WWW', 'css'=>'hideparams', 'mask'=>array('fview'=>1));
		$this->fields_form['period'] = array('type' => 'list', 'listname'=>'period','caption' => 'Срок размещения', 'css'=>'hideparams', 'mask'=>array('fview'=>1),'xslprop'=>'block1');
		$this->fields_form['datea'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата размещения', 'css'=>'hideparams', 'mask'=>array('sort'=>1));
		if($this->config['onComm']=='1')
			$this->fields_form['on_comm'] = array('type' => 'checkbox', 'caption' => 'Включить отзывы?', 'css'=>'hideparams', 'mask'=>array('fview'=>1,'usercheck'=>2));
		$this->fields_form['tstamp'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата создания', 'mask'=>array('evala'=>'time()','fview'=>2));
		$this->fields_form['ip'] = array('type' => 'text', 'caption' => 'IP','readonly'=>1, 'mask'=>array('evala'=>'$_SERVER["REMOTE_ADDR"]','fview'=>1,'usercheck'=>1));
		$this->fields_form['statview'] = array('type' => 'int', 'caption' => 'Просмотры','readonly'=>1, 'mask' =>array('filter'=>1,'sort'=>1));
		
		/*Прописываем поля для номинаций*/
		$i = 1;
		while(isset($this->config['nomination'.$i])) {
			if($this->config['nomination'.$i]!='') {
				$this->fields['nomination'.$i] = array('type' => 'int', 'width' => 9, 'attr' => 'NOT NULL');
				$this->fields_form['nomination'.$i] = array('type' => 'int', 'caption' => '!'.$this->config['nomination'.$i],'readonly'=>1, 'mask' =>array('filter'=>1,'sort'=>1,'usercheck'=>2));//'fview'=>1,
			}
			$i++;
		}

		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл','default'=>1, 'mask' =>array('filter'=>1,'usercheck'=>2));


		if(_prmUserCheck()) {
			$this->_enum['period']=array(
				7776000=>'90 дней',
				5184000=>'60 дней',
				2592000=>'30 дней',
				1728000=>'20 дней',
				864000=>'10 дней',
				432000=>'5 дней'
			);
			$this->_enum['datea']=array(
				0=>' -немедленно- ',
				86400=>'через 1 день',
				172800=>'через 2 дня',
				259200=>'через 3 дня',
				432000=>'через 5 дней',
				604800=>'через 7 дней',
				864000=>'через 10 дней',
				1209600=>'через 14 дней');
		}
		else {
			$this->_enum['period']=array(
				5184000=>'60 дней',
				1728000=>'20 дней',
			);
			$this->_enum['datea']=array(
				0=>' -немедленно- ',
				86400=>'через 1 день');
			$this->fields_form['period']['comment']='Зарегестрированным срок размещения от 5 до 90 дней';
			$this->fields_form['datea']['comment']='Зарегестрированным дата отложенной публикации до 14 дней';
		}

		$this->_enum['type']=array(
			0=>'Предложение',
			1=>'Спрос',
			2=>'Аренда-предложение',
			3=>'Аренда-спрос',
			4=>'Отдам даром',
			5=>'Приму в дар',
			6=>'Обмен');

		$this->ordfield = 'datea DESC';

		if($this->_autoCheckMod or $_GET['_type']=='checkmodul') {
			include_once($this->_CFG['_PATH']['ext'].'board.class/childs.include.php');
			$this->create_child('paramb');
			$this->create_child('boardvote');
		}
		if($this->config['onComm']) {
			include_once($this->_CFG['_PATH']['extcore'].'comments.extend/comments.extend.php');
			$this->create_child('comments');
			$this->childs['comments']->tablename = $this->_CFG['sql']['dbpref'].'board_comments';
			$this->childs['comments']->caption = 'Отзывы';
		}

		$this->_enum['onComm']=array(
			0=>'Отключить',
			1=>'Пользователь',
			2=>'Включить');
	}

	public function filtrForm () {// фильтр админки
		$_FILTR = $_SESSION['filter'][$this->_cl];
		$this->fields_form['creater_id']['type'] = 'ajaxlist';
		return parent::filtrForm();
	}

	public function kPreFields(&$data,&$param) {
		global $_tpl,$CITY;
		if(!isset($data['rubric']) and isset($_REQUEST['rubric']))
			$data['rubric'] = (int)$_REQUEST['rubric'];
		/*if(!isset($_POST['sbmt']) and (int)$data['rubric'])
			$_tpl['onload'] .='boardrubric(\'rubric\''.($this->id?','.$this->id:'').');';*/
		if(_prmUserCheck()) {
			$this->fields_form['phone']['value']=$_SESSION['user']['phone'];
			$this->fields_form['email']['value']=$_SESSION['user']['email'];
			if(!$this->data[$this->id]['img_board2']) {
				$this->fields_form["img_board2"]['style'] = 'display:none;';
				$this->fields_form["img_board"]['comment'] .= ' <div class="shownextfoto" onclick="shownextfoto(this,\'img_board2\')">Ещё фото</div>';
			}
			if(!$this->data[$this->id]['img_board3']) {
				$this->fields_form["img_board3"]['style'] = 'display:none;';
				$this->fields_form["img_board2"]['comment'] .= ' <div class="shownextfoto" onclick="shownextfoto(this,\'img_board3\')">Ещё фото</div>';
			}
			if(!$this->data[$this->id]['img_board4']) {
				$this->fields_form["img_board4"]['style'] = 'display:none;';
				$this->fields_form["img_board3"]['comment'] .= ' <div class="shownextfoto" onclick="shownextfoto(this,\'img_board4\')">Ещё фото</div>';
			}
			if(!$this->data[$this->id]['img_board5']) {
				$this->fields_form["img_board5"]['style'] = 'display:none;';
				$this->fields_form["img_board4"]['comment'] .= ' <div class="shownextfoto" onclick="shownextfoto(this,\'img_board5\')">Ещё фото</div>';
			}
			if(!$this->data[$this->id]['img_board6']) {
				$this->fields_form["img_board6"]['style'] = 'display:none;';
				$this->fields_form["img_board5"]['comment'] .= ' <div class="shownextfoto" onclick="shownextfoto(this,\'img_board6\')">Ещё фото</div>';
			}
		}

		
		if($this->id){
			/*$result = $this->SQL->execSQL('SELECT * FROM city WHERE id='.(int)$data['city']);
			if(!$result->err and $row = $result->fetch_array()){
				$valcity = $row['id'];
				$namecity = $row['name'].($row['region_name_ru']?', '.$row['region_name_ru']:'');
			}*/
			$this->fields_form['rubric']['onchange'] = 'boardrubric(this,'.$this->id.')';
		}
		else{
			/*if($data['city'] and $data['city_2']) {
				$valcity = $data['city'];
				$namecity = $data['city_2'];
			}else {
				$valcity = $CITY->id;
				$namecity = ($valcity?$CITY->name:'');
			}*/
			$this->fields_form['datea'] = array('type' => 'list', 'listname'=>'datea', 'caption' => 'Дата публикации','css'=>'hideparams','comment'=>$this->fields_form['datea']['comment']);
			global $_tpl;
			if(!$data['contact'] and !$data['datea']) // скрывает доп поля
				$_tpl['onload'] .= 'show_params(\'.hideparams\');';
			$this->fields_form['active']['type'] = 'hidden';

		}
		$this->fields_form['city']['default'] = $CITY->id;
		$this->fields_form['city']['default_2'] = $CITY->name;
		/*
		$this->fields_form['city'] = array(
			'type' => 'infoinput',
			'value'=>$valcity, 
			'mask' =>array('min'=>1), 
			'caption' => 'Ваш город <a class="selectcity" href="/city.html" onclick="return JSHRWin({\'href\':\''.$this->_CFG['_HREF']['siteJS'].'?_view=addcity\'})">'.(!$namecity?'Выбирите город':$namecity).'</a>');*/
		$mess = parent::kPreFields($data,$param);
		if(!isset($this->RUBRIC->tablename))
			$this->RUBRIC = new rubric_class($this->SQL);
		if($data['rubric']) {
			$this->fields_form = $this->insertInArray($this->fields_form,'rubric',$this->ParamFieldsForm($this->id,$data['rubric'],$data['type'])); // обработчик параметров рубрики
		}
		return $mess;
	}

	public function kFields2Form(&$param) {
		$flag = parent::kFields2Form($param);
		if($this->id) {
			$this->form["_info"]['caption'] = 'Редактирование объявления';
			$this->form['sbmt']['value'] = 'Редактировать';
		}
		else {
			$this->form["_info"]['caption'] = 'Добавить объявление';
			$this->form['sbmt']['value'] = 'Добавить';
		}

		return $flag;
	}

	public function fFormCheck(&$vars,&$param,&$FORMS) {
		$arr =parent::fFormCheck($vars,$param,$FORMS);
		if(isset($arr['vars']['phone']) and $arr['vars']['phone']=='' and $arr['vars']['email']==''){
			global $_tpl;
			if(!count($arr['mess']))
				$arr['mess'][] = array('name'=>'error', 'value'=>'Поля формы заполненны не верно.');
			if($param['ajax']) {
				$_tpl['onload'] .= 'putEMF(\'phone\',\'Необходимо указать либо телефон...\');';
				$_tpl['onload'] .= 'putEMF(\'email\',\'либо Email\');';
			}
			else {
				$this->fields_form['phone']['error'][] = 'Необходимо указать либо телефон...';
				$this->fields_form['email']['error'][] = 'либо Email';
			}
		}
		return $arr;
	}

	public function _save_item($data=array()) {
		$cls=array();$ct= array();
		if(!$ret = parent::_save_item($data)) {
			if(isset($data['city'])) {
				$ct[] = array($data['city'],$data['rubric'],$data['active']);
				foreach($this->data as $r)
					$ct[] = array($r['city'],$r['rubric'],$r['active']);
				foreach($ct as $r)
					$this->updateCount2($r[0],$r[1],$r[2]);
			}
			$PARAM = &$this->RUBRIC->childs['param'];
			if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)) {
				
				foreach($PARAM->data as $k=>$r){
					if($data['param_'.$k])
						$cls['name'.$r['type']]=$data['param_'.$k];
				}

				if(count($cls)) {
					$result=$this->SQL->execSQL('DELETE FROM paramb WHERE owner_id='.$this->id);
					if($result->err) return $this->_message($result->err);
					$query = 'INSERT into paramb (owner_id,'.implode(',',array_keys($cls)).') values ('.$this->id.',"'.implode('","',$cls).'")';
					$result=$this->SQL->execSQL($query);
					if($result->err) return $this->_message($result->err);
				}
			}
		}
		return $ret;
	}

	public function _add_item(&$vars) {
		$vars['datea'] = ((int)$vars['datea']+time());
		if(!$ret = parent::_add_item($vars)) {
			if($vars['active']==1 or !isset($vars['active']))
				$this->updateCount2($vars['city'],$vars['rubric'],1);

			$cls=array();
			$PARAM = &$this->RUBRIC->childs['param'];
			if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)){
				foreach($PARAM->data as $k=>$r){
					if($vars['param_'.$k])
						$cls['name'.$r['type']]=$vars['param_'.$k];
				}
				if(count($cls)) {
					$query = 'INSERT into paramb (owner_id,'.implode(',',array_keys($cls)).') values ('.$this->id.',"'.implode('","',$cls).'")';
					$result=$this->SQL->execSQL($query);
					if($result->err) echo $this->_message($result->err);
				}else {
					$query = 'INSERT into paramb (owner_id) values ('.$this->id.')';
					$result=$this->SQL->execSQL($query);
					if($result->err) echo $this->_message($result->err);
				}
			}
			$this->form=array();
		}
		return $ret;
	}
	public function _Act($act,&$param) {
		$ret = parent::_Act($act,$param);
		if(!$ret[1]) {
			foreach($this->data as $r) {
				$this->updateCount($r['city'],$r['rubric'],(!$act?-1:1));
			}
		}
		return $ret;
	}
	public function _delete() {
		if(!$ret = parent::_delete()) {
			if(isset($this->data) and count($this->data))
				foreach($this->data as $r) {
					$this->updateCount($r['city'],$r['rubric'],-1);
				}
		}
		return $ret;
	}

	public function _UpdItemModul($param) {
		$mess=$this->antiSpam();
		if(!count($mess)) {
			$xml = parent::_UpdItemModul($param);
			return $xml;
		}else
			return array(array('messages'=>$mess),-1);
	}

	private function antiSpam() {
		$mess = array();
		if($this->id) return $mess;
		if(!isset($_SESSION['user']['id']))
			$pb= $this->config['defmax'];
		elseif(!(int)$_SESSION['user']['paramboard'])
			$pb= $this->config['defumax'];
		else
			$pb= (int)$_SESSION['user']['paramboard'];

		if($_POST['datea'])
			$time = ((int)$_POST['datea']+time());
		else
			$time = time();
		$cls ='SELECT id,city,rubric,text,tstamp FROM '.$this->tablename.' WHERE datea>='.($time-(3600*$this->config['spamtime'])).' and datea<'.$time;
		
		if(_prmUserCheck())
			$cls .= ' and creater_id="'.$_SESSION['user']['id'].'"';
		else
			$cls .= ' and ip="'.$_SERVER["REMOTE_ADDR"].'"';
		$result = $this->SQL->execSQL($cls.' order by datea DESC');
		$data = array();
		if(!$result->err) {
			if($row = $result->fetch_array()) {
				if(count($_POST)>3 and (time()-$row['tstamp'])<30) {
					$mess[] = array('name'=>'error', 'value'=>'Внимание! Прошло слишком мало времени с момента последней публикации. Оформите объявление лучше.');
					return $mess;
				}

				if(count($_POST)>3 and substr($row['text'],10,30)==substr($_POST['text'],10,30) and $row['city']==$_POST['city'] and $row['rubric']==$_POST['rubric']) {
					$mess[] = array('name'=>'error', 'value'=>'Внимание! Не нужно дублировать объявление! Оформите объявление лучше.');
					return $mess;
				}
			}

			if($result->num_rows()>=$pb) {
				$mess[] = array('name'=>'error', 'value'=>'Внимание! Вы привысили лимит, допускается отправка не более '.$pb.' в период '.$this->config['spamtime'].' часа. Можете запланировать время выпуска объявления на более поздний срок в дополнительных опциях - "Дата публикации"');
				if(!isset($_SESSION['user']['id']))
					$mess[] = array('name'=>'alert', 'value'=>'Зарегестрированные пользователи могут отправлять '.$this->config['defumax'].' и более объявлений в день. Подробности <a href="/inform.html">тут</a>.');
				return $mess;
			}
		}
		return $mess;
	}

	private function updateCount($city,$rub,$f) {
		$city=(int)$city;$rub=(int)$rub;
		$this->SQL->execSQL('UPDATE city SET cnt=cnt+'.$f.' WHERE id='.$city);
		$this->SQL->execSQL('INSERT INTO countb (city,owner_id,cnt) VALUES ('.$city.','.$rub.',1) ON DUPLICATE KEY UPDATE cnt=cnt+('.$f.') ');
	}

	function updateCount2($city,$rub,$f) {
		$city=(int)$city;$rub=(int)$rub;
		$qq ='UPDATE city SET cnt=(SELECT count(id) FROM board WHERE city='.$city.' '.(!$this->config['onDate']?'':'and datea>UNIX_TIMESTAMP()-period').' and datea<UNIX_TIMESTAMP() and active=1) WHERE id='.$city;
		$this->SQL->execSQL($qq);$qq='';
		$result = $this->SQL->execSQL('SELECT id FROM countb WHERE city='.$city.' and owner_id='.$rub.'');
		if($result->num_rows())
			$qq ='UPDATE countb SET cnt=(SELECT count(id) FROM board WHERE city='.$city.' and rubric='.$rub.' '.(!$this->config['onDate']?'':'and datea>UNIX_TIMESTAMP()-period').' and datea<UNIX_TIMESTAMP() and active=1) WHERE city='.$city.' and owner_id='.$rub;
		elseif($f)
			$qq = 'INSERT INTO countb (city,owner_id,cnt) VALUES ('.$city.','.$rub.',1)';
		if($qq!='')
			$this->SQL->execSQL($qq);
	}

	/** Пользовательские функции*/

	public function servUpdate() {
		$this->SQL->execSQL('UPDATE city t1 SET t1.cnt=(SELECT count(t2.id) FROM board t2 WHERE t2.city=t1.id '.(!$this->config['onDate']?'':'and t2.datea>UNIX_TIMESTAMP()-t2.period').' and t2.datea<UNIX_TIMESTAMP() and t2.active=1) WHERE t1.active=1');
		$this->SQL->execSQL('truncate table `countb`');
		$result = $this->SQL->execSQL('SELECT t1.city,t1.rubric FROM board t1 JOIN city t2 ON t1.city=t2.id JOIN rubric t3 ON t1.rubric=t3.id GROUP BY t1.city,t1.rubric');
		while(!$result->err and $row = $result->fetch_array()){
			$qq = 'INSERT INTO countb (city,owner_id,cnt) VALUES ('.$row['city'].','.$row['rubric'].',0) ON DUPLICATE KEY UPDATE cnt=cnt';
			$this->SQL->execSQL($qq);
			$qq ='UPDATE countb SET cnt=(SELECT count(id) FROM board WHERE city='.$row['city'].' and rubric='.$row['rubric'].' '.(!$this->config['onDate']?'':'and datea>UNIX_TIMESTAMP()-period').' and datea<UNIX_TIMESTAMP() and active=1) WHERE city='.$row['city'].' and owner_id='.$row['rubric'].';';
			$this->SQL->execSQL($qq);
		}
	}

	public function ParamFieldsForm($id,$rid,$tp=0,$listclause='') { // форма для редак-добавл объявы и поиска объявы
		//$id - id объявы
		//$rid - id рубрики
		//$tp - тип обявления
		//listclause - подзапрос
		global $_tpl;//в onLoad слайдер
		$FLI = array();
		if(is_array($id))
			$flagNew = 1;
		else
			$flagNew = 0;
		if(!$flagNew and $id) {
			$result = $this->SQL->execSQL('SELECT * FROM paramb WHERE owner_id IN ('.$id.')');
			if(!$result->err)
				while ($row = $result->fetch_array()){
					$paramdata=$row;
				}
			else exit($result->err);
		}
		$PARAM = &$this->RUBRIC->childs['param'];
		$PARAM->listfields = array('*');
		$PARAM->clause = 'WHERE owner_id="'.$rid.'" and active=1 order by ordind';
		$PARAM->_list('id');
		if(count($PARAM->data)) {
			$form=array();
			$pdata=array();
			foreach($PARAM->data as $k=>$r) {
				$val='';

				if($flagNew) {
					$val=$id['param_'.$k];
				}
				elseif($id) 
					$val=$paramdata['name'.$r['type']];
				
				$type = $PARAM->getTypeForm($r['type']);
				if($r['typelist']==1 and $type=='list')
					$type = 'ajaxlist';

				$form['param_'.$k] = array(
					'caption'=>$r['name'].($r['edi']!=''?', '.$r['edi']:''),
					'type'=>$type,
					'type2'=>$r['type'],
					'value'=>$val,
					'css'=>'addparam');
				
				if($r['def']!='' and !$flagNew) {// and !$val
					if(substr($r['def'],0,5) == 'eval=')
						eval('$form["param_'.$k.'"]["default"] = '.substr($r['def'],5).';');
					else
						$form['param_'.$k]['default'] = $r['def'];
				}

				if($type=='int') {
					$form['param_'.$k]['mask']=array(
						'minint'=>$r['min'],
						'maxint'=>$r['max']);
					if(is_array($id))
						$form['param_'.$k]['value_2']=$id['param_'.$k.'_2'];

					/*if($listclause!='' and ($r['min']>0 or $r['max']==0)) {
						$temcls = 'SELECT min(t2.name'.$k.') as min,max(t2.name'.$k.') as max FROM '.$this->tablename.' t1 
						JOIN paramb t2 ON t2.owner_id=t1.id and t2.owner_id= '.$listclause;
						$result2 = $this->SQL->execSQL($temcls);
						$maxmin = $result2->fetch_array(MYSQL_NUM);
						if($r['min']>0)
							$r['min']=$maxmin[0];
						if($r['max']==0)
							$r['max']=$maxmin[1];
					}*/

					if($form['param_'.$k]['value']=='')	$form['param_'.$k]['value']=$r['min'];
					if($form['param_'.$k]['value_2']=='')	$form['param_'.$k]['value_2']=$r['max'];
					if($flagNew) // для фильтра
						$_tpl['onload'] .= "gSlide('tr_param_".$k."',".(int)$r['min'].",".(int)$r['max'].",".(int)$form['param_'.$k]['value'].",".(int)$form['param_'.$k]['value_2'].",".(int)$r['step'].");";	
				}else
					$form['param_'.$k]['mask']=array('min'=>$r['min'],'max'=>$r['max']);

				if($r['mask']!='') $form['param_'.$k]['mask']['patterns']=$r['mask'];

				if($r['comment']!='') $form['param_'.$k]['comment']=$r['comment'];
				
				$tp=(int)$tp;
				if($tp==1 || $tp==3) unset($form['param_'.$k]['mask']['min']);

				if($type=='ajaxlist') {
					$form['param_'.$k]['listname'] = array('tablename'=>'formlistitems','where'=>' tx.checked=1 and tx.active=1 GROUP BY tx.id','ordfield'=>'tx.ordind');
					$form['param_'.$k]['value_2'] = $id['param_'.$k.'_2'];
				}elseif($type=='list'){
					$form['param_'.$k]['listname']='fli'.$r['formlist'];
					$form['param_'.$k]['mask']['begin']=0;
					$FLI[] = $r['formlist'];
				}
				
				if(!$r['max'] and $PARAM->_enum['typelen'][$r['type']]!=''){
					$form['param_'.$k]['mask']['max']=$PARAM->_enum['typewidth'][$r['type']];
					if($PARAM->_enum['typelist'][$r['type']]=='int')
						$form['param_'.$k]['mask']['maxint']=$PARAM->_enum['typelen'][$r['type']];
				}
			}

			if(count($FLI)) {
				$clause = 'SELECT t1.id,t1.owner_id,t1.name,t1.checked, GROUP_CONCAT(t2.id,":",t2.name,":",t2.checked,":",t2.val ORDER BY t2.ordind SEPARATOR "|") as elem 
FROM formlistitems t1 LEFT JOIN formlistitems t2 ON t1.id=t2.parent_id and t2.owner_id=0 and t2.active=1 
WHERE t1.owner_id IN ('.implode(',',$FLI).') and t1.active=1 GROUP BY t1.id ORDER BY t1.ordind';
				$result = $this->SQL->execSQL($clause);
				if(!$result->err)
					while ($row = $result->fetch_array()) {
						if(!isset($this->_enum['fli'.$row['owner_id']][0][0]))//$flagNew and 
							$this->_enum['fli'.$row['owner_id']][0][0] = ' --- ';

						if($row['elem']!='') {
							$param = explode('|',$row['elem']);
							foreach($param as $r) {
								$r = explode(':',$r);
								$this->_enum['fli'.$row['owner_id']][$row['id']][$r[0]] = array('#id#'=>$r[0],'#name#'=>$r[1],'#checked#'=>$r[2],'#val#'=>$r[3]);
							}
						}
						unset($row['elem']);
						$this->_enum['fli'.$row['owner_id']][0][$row['id']] = array('#id#'=>$row['id'],'#name#'=>$row['name'],'#checked#'=>$row['checked']);

					}
				else exit($result->err);
			}
			return $form;
		}
		return array();
	}


	public function fListDisplay($rid,$filter,$rss=0,$order='t1.datea',$limit=0) {
		//$this->RUBRIC->data - кэш рубрик
		//$this->RUBRIC->data2 - кэш рубрик
		//$PARAM->data
		// if $limit>0 без постранички

		$PARAM = &$this->RUBRIC->childs['param'];
		
		if(isset($PARAM->data)){
			reset($PARAM->data);
			$temp = current($PARAM->data);
		}
		if(!$temp or $temp['owner_id']!=$rid) { // для RSS и рассылки
			$PARAM->listfields = array('*');
			$PARAM->clause = 'WHERE owner_id="'.$rid.'" and active=1 order by ordind';
			$PARAM->_list('id');
		}
		$clauseF=array();
		$lcnt=4;
		$type='';
		if(count($filter)) {
			foreach($filter as $k=>$r) {
				if($k=='id' or $k=='rubric') continue;
				$tempid = substr($k,6);
				if(isset($PARAM->data[$tempid])) {
					$nameK = $PARAM->data[$tempid]['type'];
					$type = $PARAM->getTypeForm($nameK);
					if($type=='int'){
						$temparr=array();
						if((int)$r) $clauseF[] = "t4.name{$nameK}>=".(int)$filter[$k];
						if((int)$filter[$k.'_2']) {
							$clauseF[] = "t4.name{$nameK}<=".(int)$filter[$k.'_2'];
						}
					}
					elseif($r!='' and $r!=0)
						$clauseF[] = "t4.name{$nameK}='".$filter[$k]."'";
				}
				elseif($k=='datea')
					$clauseF[] = 't1.datea<"'.$r.'"';
				elseif(isset($this->fields[$k]) and isset($this->fields_form[$k])) {
					
					if(isset($filter[$k.'_2']) and $this->fields_form[$k]['type']=='int'){
						if((int)$r) $clauseF[] = 't1.'.$k.'>='.(int)$r;
						if((int)$filter[$k.'_2']) $clauseF[] = 't1.'.$k.'<='.(int)$filter[$k.'_2'];
					}
					elseif($r!='' and $k!='city' and $this->fields_form[$k]['type']=='list'){
						$clauseF[] = 't1.'.$k.'="'.$r.'"';
					}
					elseif($r!='' and $k=='text'){
						$clauseF[] = 't1.'.$k.' LIKE "%'.$r.'%"';
					}
					elseif($k=='datea')
						$clauseF[] = 't1.datea>"'.$r.'"';
				}
				elseif($r=='1' and $k=='foto'){
					$temp=array();
					foreach($this->attaches as $tk=>$tr)
						$temp[] = 't1.'.$tk.'!=""';
					$clauseF[] = '('.implode(' or ',$temp).')';
				}
			}
		}
		
		$xml='';

		$clause[1] = 'FROM '.$this->tablename.' t1 ';
		$clause[2] = ' LEFT JOIN paramb t4 ON t4.owner_id=t1.id ';
		$clause[3] = ' LEFT JOIN '.$PARAM->tablename.' t2 ON t1.rubric=t2.owner_id and t2.active and t2.constrn=1 ';
		$clause[4] = ' WHERE t1.active=1 and t1.datea<UNIX_TIMESTAMP() ';

		if($this->config['onDate'] and !$limit)
			$clause[4] .= 'and t1.datea>UNIX_TIMESTAMP()-t1.period';

		$rlist = array();
		if($rid) {
			if(isset($this->RUBRIC->data2[$rid]))
				$rlist[$rid] = $this->RUBRIC->data2[$rid]['name'];
			if(isset($this->RUBRIC->data[$rid])) {
				foreach($this->RUBRIC->data[$rid] as $k=>$r){
					if(isset($this->RUBRIC->data[$k])){
						$rlist = $this->RUBRIC->data[$k]+$rlist;
					}else
						$rlist[$k]=$r;						
				}
			}
		}
		if(count($rlist))
			$clause[4] .= ' and t1.rubric IN ('.implode(',',array_keys($rlist)).') ';
		global $CITY;
		if(isset($CITY->citylist) and count($CITY->citylist))
			$clause[4] .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
		$cls_filtr = '';
		if(count($clauseF)) {
			$clause[4] .= ' and '.implode(' and ',$clauseF);
			$cls_filtr = $clause[2];
		}

		$result = $this->SQL->execSQL('SELECT count(DISTINCT t1.id) as cnt '.$clause[1].$cls_filtr.$clause[4]);

		if(!$result->err and $row = $result->fetch_array() and $row['cnt']>0) {

			if(!$limit) {
				$xml .= '<cnt>'.$row['cnt'].'</cnt>';
				/*** PAGE NUM  REVERSE ***/
				if($this->reversePageN and $this->_pn == 0) $this->_pn = 1;
				elseif($this->reversePageN)
					$this->_pn = ceil($row['cnt']/$this->messages_on_page)-$this->_pn+1;
				/***/
				$xml .= $this->kData2xml($this->fPageNav($row['cnt']),'pagenum');
				if($this->reversePageN and $this->_pn==ceil($row['cnt']/$this->messages_on_page)) {
					$pcnt = 0;
					$this->messages_on_page = $row['cnt']-$this->messages_on_page*($this->_pn-1); // правдивый
					//$this->messages_on_page = $this->messages_on_page*$this->_pn-$countfield; // полная запись
				}
				elseif($this->reversePageN)
					$pcnt = $row['cnt']-$this->messages_on_page*$this->_pn; // начало отсчета
				else
					$pcnt = $this->messages_on_page*($this->_pn-1); // начало отсчета
			}



			$clause_from = '';
			if(!count($CITY->citylist) or count($CITY->citylist)>1 or $limit) {
				//global $CITY;
				//if(!$CITY) $CITY = new city_class($this->SQL);
				$clause_from =', t3.name as cityname, if(t3.city!="",t3.city,CONCAT("all",t3.region_name)) as domen ';
				$clause[3] .= ' JOIN '.$CITY->tablename.' t3 ON t1.city=t3.id ';
			}
			
			$clause[] = ' GROUP BY t1.id ORDER BY '.$order.' DESC';
			if(!$limit)
				$clause[] = ' LIMIT '.$pcnt.', '.$this->messages_on_page;
			else
				$clause[] = ' LIMIT '.$limit;
			$pData = $this->fGetParamBoard('SELECT t4.*, t1.*, GROUP_CONCAT(DISTINCT t2.id,":",t2.name,":",t2.type,":",t2.formlist,":",t2.edi ORDER BY t2.ordind SEPARATOR "|") as param '.$clause_from.implode(' ',$clause));// retutn $this->data

			foreach($this->data as $k=>$r) {
				$rname=array();
				$temp=$r['rubric'];
				if(!$rss) {
					while(isset($this->RUBRIC->data2[$temp]) and $rid!=$temp) {
						$rname[] = $this->RUBRIC->data2[$temp]['name'];
						$temp=$this->RUBRIC->data2[$temp]['parent_id'];
					};
					$xml .='<item>
						<id>'.$r['id'].'</id>
						<city>'.$r['city'].'</city>
						<rubric>'.$r['rubric'].'</rubric>';
					if($r['domen'])
						$xml .= '<domen>http://'.str_replace(array('\'',' '),array('_','-'),$r['domen']).'.'.$_SERVER['HTTP_HOST2'].'</domen>';
					else
						$xml .= '<domen>http://'.$_SERVER['HTTP_HOST2'].'</domen>';
					if(count($rname)) 
						$xml .='<rname>'.implode('</rname><rname>',array_reverse($rname)).'</rname>';
					$tempval = rand(0,9);
					$xml .='<type>'.$r['type'].'</type>
						<tname>'.($r['cityname']?$r['cityname'].' / ':'').$this->_enum['type'][$r['type']].'</tname>
						<text><![CDATA['.html_entity_decode(strip_tags($r['text']),2,'UTF-8').']]></text>
						<phone><![CDATA['.str_replace(array('-','8',$tempval),array('<span>-</span><span class="sdsd">'.$tempval.'</span>','<span style="display:none;">3</span>8','<span style="display:none;">'.$tempval.'</span><span class="sdsb">'.$tempval.'</span>'),$r['phone']).']]></phone>
						<email>'.$r['email'].'</email>
						<contact><![CDATA['.preg_replace($this->_CFG['_repl']['href'],"<a href='/_redirect.php?url=\\0'>Ссылка</a>",$r['contact']).']]></contact>
						<datea>'.date('Y-m-d',$r['datea']).'</datea>';
					if(count($r['param']))
						foreach($r['param'] as $pk=>$pr)
							if($r['name'.$pr[2]])
								$xml .= '<param name="'.$pr[1].'" id="'.$pr[0].'" edi="'.$pr[4].'">'.$r['name'.$pr[2]].'</param>';
					foreach($this->attaches as $tk=>$tr)
						if($r[$tk]!='')
							$xml .= '<image s="'.$this->getPathForAtt($tk).'/s_'.$r['id'].'.'.$r[$tk].'">'.$this->getPathForAtt($tk).'/'.$r['id'].'.'.$r[$tk].'</image>';
					$i = 1;
					while(isset($this->config['nomination'.$i])) {
						if($this->config['nomination'.$i]!='') {
							$xml .= '<nomination value="'.$r['nomination'.$i].'" sel="'.(isset($r['nomination'][$i])?1:0).'" type="'.$i.'">'.$this->config['nomination'.$i].'</nomination>';
						}
						$i++;
					}
					$xml .= '</item>';
				}else {
					while(isset($this->RUBRIC->data2[$temp])) {
						$rname[] = $this->RUBRIC->data2[$temp]['name'];
						$temp=$this->RUBRIC->data2[$temp]['parent_id'];
					}
					$xml .='<item><title>'.($r['cityname']?$r['cityname'].' / ':'').$this->_enum['type'][$r['type']].' / '.implode(' / ',array_reverse($rname));
					if(count($r['param']))
						foreach($r['param'] as $pk=>$pr)
							$xml .= '/ '.$r['name'.$pr[2]].' '.$pr[4];
					$xml .='</title> 
						<link>http://'.($r['domen']?str_replace(array('\'',' '),array('_','-'),$r['domen']).'.':'').$_SERVER['HTTP_HOST2'].'/board_'.$r['id'].'.html</link> 
						<description>'.html_entity_decode(strip_tags($r['text']),2,'UTF-8').'</description>
						<pubDate>'.date('r',$r['datea']).'</pubDate>';
					/**Номинации*/
					$i = 1;
					while(isset($this->config['nomination'.$i])) {
						if($this->config['nomination'.$i]!='') {
							$xml .= '<nomination value="'.$r['nomination'.$i].'" sel="'.(isset($r['nomination'][$i])?1:0).'" type="'.$i.'">'.$this->config['nomination'.$i].'</nomination>';
						}
						$i++;
					}
					$xml .= '</item>';
				}
			}
			if(!$rss) {
				if(isset($_COOKIE['checkloadfoto']) and $_COOKIE['checkloadfoto']=='0')
					$xml .= '<imcookie>0</imcookie>';
				else
					$xml .= '<imcookie>1</imcookie>';
			}
		}
		return $xml;
	}

	public function fNewDisplay($limit){
		global $CITY;
		$PARAM = &$this->RUBRIC->childs['param'];
		$clause = 'SELECT t3.*, t1.*, GROUP_CONCAT(t2.id,":",t2.name,":",t2.type,":",t2.formlist,":",t2.edi ORDER BY t2.ordind SEPARATOR "|") as param, t4.name as cityname, if(t4.city!="",t4.city,CONCAT("all",t4.region_name)) as domen
		FROM '.$this->tablename.' t1 
		JOIN '.$CITY->tablename.' t4 ON t1.city=t4.id 
		LEFT JOIN paramb t3 ON t1.id=t3.owner_id 
		LEFT JOIN '.$PARAM->tablename.' t2 ON t1.rubric=t2.owner_id and t2.active and t2.constrn=1 
		WHERE t1.active=1 and t1.datea<'.time().' ';

		if(count($CITY->citylist)==1) {
			$CITY->listfields = array('t1.id');
			$CITY->clause = 't1 JOIN '.$CITY->tablename.' t2 ON (t1.id=t2.parent_id or t1.parent_id=t2.parent_id) WHERE t2.id="'.$CITY->id.'" and t1.active=1';
			$CITY->_list('id');
			$clause .= ' and t1.city IN ('.implode(',',array_merge($CITY->citylist,array_keys($CITY->data))).')';
		}
		elseif(count($CITY->citylist)>1) {
			$clause .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
		}

		$clause .= ' GROUP BY t1.id ORDER BY t1.datea DESC LIMIT '.$limit;

		$this->fGetParamBoard($clause);

		$xml='<main>';
		foreach($this->data as $k=>$r) {
			$temp=$r['rubric'];
			$xml .='<item>
				<id>'.$r['id'].'</id>
				<city>'.$r['city'].'</city>
				<domen>http://'.str_replace(array('\'',' '),array('_','-'),$r['domen']).'.'.$_SERVER['HTTP_HOST2'].'</domen>
				<rubric>'.$r['rubric'].'</rubric>
				<rname>'.$this->RUBRIC->data2[$temp]['name'].'</rname>
				<type>'.$r['type'].'</type>
				<tname>'.$r['cityname'].'/ '.$this->_enum['type'][$r['type']].'</tname>
				<text><![CDATA['._substr(html_entity_decode(strip_tags($r['text']),ENT_QUOTES,'UTF-8'),0,200).'...]]></text>
				<phone>'.$r['phone'].'</phone>
				<email>'.$r['email'].'</email>
				<datea>'.date('Y-m-d',$r['datea']).'</datea>';
			if(count($r['param']))
				foreach($r['param'] as $pk=>$pr) {
					if($r['name'.$pr[2]]) 
						$xml .= '<param name="'.$pr[1].'" id="'.$pr[0].'" edi="'.$pr[4].'">'.$r['name'.$pr[2]].'</param>';
				}
			$xml .= '</item>';
		}
		$xml .='</main>';
		return $xml;
	}

	public function fDisplay($id) {//$id - число либо массив
		$idt= explode(',',$id);
		$arr_stat=$id=array();
		foreach($idt as $r)//сохр тока уник знач
			$id[(int)$r]=(int)$r;
		$PARAM = &$this->RUBRIC->childs['param'];
		$clause = 'SELECT t3.*, t1.*, GROUP_CONCAT(t2.id,":",t2.name,":",t2.type,":",t2.formlist,":",t2.edi ORDER BY t2.ordind SEPARATOR "|") as param FROM '.$this->tablename.' t1
		LEFT JOIN paramb t3 ON t1.id=t3.owner_id  
		LEFT JOIN '.$PARAM->tablename.' t2 ON t1.rubric=t2.owner_id and t2.active 
		WHERE t1.active=1 and t1.id IN ('.implode(',',$id).') 
		GROUP BY t1.id ORDER BY t1.datea DESC';
		
		$this->fGetParamBoard($clause);
		///** Nomination **///
		$clause = 'SELECT * FROM boardvote WHERE owner_id IN ('.implode(',',$id).')';
		if(_prmUserCheck())
			$clause .= ' and creater_id="'.$_SESSION['user']['id'].'"';
		else
			$clause .= ' and ip="'.$_SERVER["REMOTE_ADDR"].'"';
		$result = $this->SQL->execSQL($clause);
		while($row = $result->fetch_array()) {
			$this->data[$row['owner_id']]['nomination'][$row['type']] = 1;
		}
		///////////
		$xml = $this->fXMLCreate(1);

		return $xml;
	}
	
	function fXMLCreate ($statview=0) {
		if(!$this->RUBRIC->data2) 
			$this->RUBRIC->simpleRubricCache();
		$xml='<main>';
		foreach($this->data as $k=>$r) {
			$rname=array();
			$temp=$r['rubric'];
			while(isset($this->RUBRIC->data2[$temp]) and $rid!=$temp) {
				$rname[] = '<rname>'.$this->RUBRIC->data2[$temp]['name'].'</rname>';
				$temp=$this->RUBRIC->data2[$temp]['parent_id'];
			}

			$xml .='<item>
				<id>'.$r['id'].'</id>
				<rubric>'.$r['rubric'].'</rubric>
				'.implode('',array_reverse($rname)).'
				<type>'.$r['type'].'</type>
				<tname>'.$this->_enum['type'][$r['type']].'</tname>
				<text><![CDATA['.$r['text'].']]></text>
				<phone>'.$r['phone'].'</phone>
				<email>'.$r['email'].'</email>
				<contact><![CDATA['.preg_replace($this->_CFG['_repl']['href'],"<a href='/_redirect.php?url=\\0'>Ссылка</a>",$r['contact']).']]></contact>
				<datea>'.date('Y-m-d',$r['datea']).'</datea>
				<statview>'.$r['statview'].'</statview>';
			if($r['img_board']!='' and $file=$this->_get_file($r,'img_board'))
				$xml .='<image s="'.$this->_prefixImage($file,'s_').'">'.$file.'</image>';
			if($r['img_board2']!='' and $file=$this->_get_file($r,'img_board2'))
				$xml .='<image s="'.$this->_prefixImage($file,'s_').'">'.$file.'</image>';
			if($r['img_board3']!='' and $file=$this->_get_file($r,'img_board3'))
				$xml .='<image s="'.$this->_prefixImage($file,'s_').'">'.$file.'</image>';
			if($r['img_board4']!='' and $file=$this->_get_file($r,'img_board4'))
				$xml .='<image s="'.$this->_prefixImage($file,'s_').'">'.$file.'</image>';
			if($r['regname'] and $r['regname']!='')
				$xml .= '<param name="Город" id="'.$r['city'].'" edi="">'.$r['regname'].', '.$r['cityname'].'</param>';
			else
				$xml .= '<param name="Регион" id="'.$r['city'].'" edi="">'.$r['cityname'].'</param>';
			if(count($r['param']))
				foreach($r['param'] as $pk=>$pr){
					if($r['name'.$pr[2]]) {				
						$xml .= '<param name="'.$pr[1].'" id="'.$pr[0].'" edi="'.$pr[4].'"><![CDATA['.$r['name'.$pr[2]].']]></param>';
					}
				}
			/**Номинации*/
			$i = 1;
			while(isset($this->config['nomination'.$i])) {
				if($this->config['nomination'.$i]!='') {
					$xml .= '<nomination value="'.$r['nomination'.$i].'" sel="'.(isset($r['nomination'][$i])?1:0).'" type="'.$i.'">'.$this->config['nomination'.$i].'</nomination>';
				}
				$i++;
			}
			$xml .= '<param name="Цена" id="" edi="">'.($r['cost']?number_format($r['cost'], 0, ',', ' ').' руб.':'договорная').'</param>';
			$xml .= '</item>';
			if($_SERVER['robot']=='' and !isset($_SESSION['statview'][$r['id']]) and $statview){
				$arr_stat[]=$r['id'];
				$_SESSION['statview'][$r['id']]=1;
			}
		}
		$xml .='</main>';

		if(count($arr_stat) and $statview){
			//statview
			$this->SQL->execSQL('UPDATE '.$this->tablename.' SET statview=statview+1 WHERE id IN ('.implode(',',$arr_stat).')');
		}
		return $xml;
	}

	public function fGetParamBoard($clause) {
/*SELECT PARAMETR*/
		$idFL = array();
		$idCITY = array();
		$PARAM = &$this->RUBRIC->childs['param'];
		$this->data=$typeclass=$pData=$idList=$idFL=array();
		$result = $this->SQL->execSQL($clause);
		if(!$result->err)
			while ($row = $result->fetch_array()){
				if($row['param']!=''){
					$row['param'] = explode('|',$row['param']);
					foreach($row['param'] as $k=>$r) {
						$r=$row['param'][$k] = explode(':',$r);// параметры поля
						$r[2] = (int)$r[2];
						if($r[2]<10) 
							$row['name'.$pr[2]] = $this->_enum['yesno2'][$dta];
						elseif($r[2]>=50 and $r[2]<60) {
							//записываем массив для выборки из списка
							$idFL[(int)$row['name'.$r[2]]][] = array($row['id'],'name'.$r[2]);
							$row['name'.$r[2]] = '';// чтобы не выводить отключенные
						}
						elseif($r[2]>=70 and $r[2]<80){
							$row['name'.$r[2]] = preg_replace("/^http:\/\/(www.)?([0-9A-Za-z\-\.]+)([\/0-9A-Za-z\.\_\=\?]*)$/",'<a href="/_redirect.php?url=\\0">\\1\\2</a>',$row['name'.$r[2]]);
						}							
					}
				}
				$this->data[$row['id']] = $row;
				$idCITY[$row['city']][] = $row['id'];
			}
		else exit($result->err);

		if(count($idFL)) {
			$clause = 'SELECT id,name FROM formlistitems
			WHERE id IN ('.implode(',',array_keys($idFL)).')';
			$result = $this->SQL->execSQL($clause);
			if(!$result->err)
				while ($row = $result->fetch_array()){
					foreach($idFL[$row['id']] as $r)
						$this->data[$r[0]][$r[1]]=$row['name'];
				}
			else exit($result->err);
		}
		if(count($idCITY)) {
			global $CITY;
			$clause = 'SELECT t1.id,t1.name,t2.id as regid,t2.name as regname FROM '.$CITY->tablename.' t1 
			left JOIN '.$CITY->tablename.' t2 ON t1.parent_id=t2.id
			WHERE t1.id IN ('.implode(',',array_keys($idCITY)).')';
			$result = $this->SQL->execSQL($clause);
			if(!$result->err)
				while ($row = $result->fetch_array()){
					foreach($idCITY[$row['id']] as $r){
						$this->data[$r]['cityname']=$row['name'];
						$this->data[$r]['regname']=$row['regname'];
					}
				}
			else exit($result->err);
		}
		return true;
	}

	function boardFindForm($rid,$flag=1) {
		//$this->RUBRIC->data - кэш рубрик
		//$this->RUBRIC->data2 - кэш рубрик
		//if $flag==0 то это "утановка параметров при подписке"
//$newtime = getmicrotime();

		global $_tpl;
		$filter = $_REQUEST;
		$xml='';

		$this->filter_form=array();

		/*global $CITY,
		if(!$CITY->id){
			$this->filter_form['city']= array('type'=>'list','listname'=>array('class'=>'city'),'caption'=>'Город');
			if(isset($filter['city'])) $this->filter_form['city']['value']=$filter['city'];
		}*/

		if($flag) {
			$datalist[$rid] =$this->RUBRIC->data2[$rid]['name'];
			if(isset($this->RUBRIC->data[$rid])) {
				$this->_enum['rubrics'][$this->RUBRIC->data2[$rid]['parent_id']][$rid] = $this->RUBRIC->data2[$rid]['name'];
				$this->_enum['rubrics'][$rid] =$this->RUBRIC->data[$rid];
				foreach($this->RUBRIC->data[$rid] as $k=>$r){
					if(isset($this->RUBRIC->data[$k])){
						$datalist = $this->RUBRIC->data[$k]+$datalist;
						$this->_enum['rubrics'][$k]=$this->RUBRIC->data[$k];
					}else
						$datalist[$k]=$r;						
				}
				$this->filter_form['rubric']= array(
					'type'=>'list',
					'listname'=>'rubrics',
					'caption'=>'Рубрика',
					'onchange'=>'window.location.href=window.location.href.replace(\'_'.$rid.'\',\'_\'+this.value).replace(\'='.$rid.'\', \'=\'+this.value)');
				if(isset($filter['rubric']))
					$this->filter_form['rubric']['value']=$filter['rubric'];
			} else {
				//$this->filter_form['city']= array('type'=>'hidden','value'=>$CITY->id);
				$this->filter_form['rubric']= array('type'=>'hidden','value'=>$rid);
			}
		}
		else {
			$datalist[$rid] =(int)$filter['rubric'];
			$this->_enum['rubrics'] = &$this->RUBRIC->data;
			//$this->_enum['rubrics'][0] = array(0=>'---')+$this->_enum['rubrics'][0];
			$this->filter_form['rubric']= array(
				'type'=>'list',
				'listname'=>'rubrics',
				'caption'=>'Рубрика',
				'onchange'=>'JSHRWin({\'href\':\''.$this->_CFG['_HREF']['siteJS'].'?_view2=subscribeparam\',\'data\':{\'rubric\':this.value}})');
			if(isset($filter['rubric']))
				$this->filter_form['rubric']['value']=$filter['rubric'];
		}


		$this->filter_form['type'] = array('type' => 'list', 'listname'=>'type','caption' => 'Тип объявления');
			if(isset($filter['type']))
				$this->filter_form['type']['value']=$filter['type'];
		$this->filter_form['cost'] = $this->fields_form['cost'];

			$temcls = ' WHERE t1.active=1 and t1.rubric IN ('.implode(',',array_keys($datalist)).') ';
			//if(count($CITY->citylist))
			//	$temcls .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
			$result2 = $this->SQL->execSQL('SELECT min(t1.cost) as mincost,max(t1.cost) as maxcost FROM '.$this->tablename.' t1 '.$temcls);
			if(!$result2 or !$minmax = $result2->fetch_array(MYSQL_NUM) or !$minmax[1])
				$minmax = array(0,$this->filter_form['cost']['mask']['maxint']);
		$step=$minmax[1]/216;
		if($step<5) $step=1;
		elseif($step==5) $step=5;
		elseif($step<=10) $step=10;
		elseif($step<=50) $step=50;
		elseif($step<=100) $step=100;
		elseif($step<=500) $step=500;
		elseif($step<=1000) $step=1000;
		elseif($step<=5000) $step=5000;
		elseif($step<=10000) $step=10000;
		elseif($step<=50000) $step=50000;
		else $step=100000;
		if($minmax[1]>$this->filter_form['cost']['mask']['maxint'])
			$minmax[1]= $this->filter_form['cost']['mask']['maxint'];
		if(isset($filter['cost'])) {
			$this->filter_form['cost']['value']=$filter['cost'];
			$this->filter_form['cost']['value_2']=$filter['cost_2'];
			$_tpl['onload'] .= "gSlide('tr_cost',".(int)$minmax[0].",".(int)$minmax[1].",".(int)$filter['cost'].",".(int)$filter['cost_2'].",".$step.");";				
		}else{
			$this->filter_form['cost']['value']=$minmax[0];
			$this->filter_form['cost']['value_2']=$minmax[1];
			$_tpl['onload'] .= "gSlide('tr_cost',".(int)$minmax[0].",".(int)$minmax[1].",".(int)$minmax[0].",".(int)$minmax[1].",".$step.");";
		}
		$this->filter_form['foto'] = array('type' => 'checkbox','param'=>'checkbox','caption'=>'Только с фотографией','value'=>$filter['foto']);
		$this->filter_form['text'] = array('type' => 'text','caption' => 'Ключевое слово','mask' =>array('max'=>128),'value'=>$filter['text']);

		if($rid) {
			$this->filter_form = $this->insertInArray($this->filter_form,'rubric',$this->ParamFieldsForm($filter,$rid,0,$temcls));
		}

		$this->_enum['type']=array(''=>' --- ')+$this->_enum['type'];

		if($flag) {
			$this->filter_form[$this->_cl.'_mop'] = array(
				'type' => 'list',
				'listname'=>'_MOP',
				'caption' => 'Объявлений на странице',
				'value'=>$this->messages_on_page);
			$this->filter_form['_*features*_']=array('name'=>'paramselect','action'=>'','method'=>'get');
			$this->filter_form['sbmt'] = array('type'=>'submit','value'=>'Поиск');
		}
		else {
			$this->filter_form['_*features*_']=array('name'=>'paramselect','action'=>'','method'=>'GET','onsubmit'=>'return getToText(this)');
			$this->filter_form['sbmt'] = array('type'=>'submit','value'=>'задать параметры');
		}
		$this->kFields2FormFields($this->filter_form);
		return $this->form;
	}
/*
	function simpleBoardFind($rid,$filter) {
		$temcls = ' WHERE t1.active=1 and t1.rubric IN ('.implode(',',array_keys($datalist)).') ';
		if(count($CITY->citylist))
			$temcls .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
		if($rid) $this->ParamFieldsForm(0,$rid,0,$temcls);
	}
*/
}

?>
