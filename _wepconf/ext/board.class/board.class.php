<?
class board_class extends kernel_extends {

	var $RUBRIC;

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		
		$this->config['onDate'] = 0;
		$this->config['onComm'] = 0;
		$this->config['spamtime'] = 24;
		$this->config['defmax'] = 3;
		$this->config['defumax'] = 9;
		$this->config['nomination1'] = '';
		$this->config['nomination2'] = '';
		$this->config['nomination3'] = '';
		$this->config['nomination4'] = '';
		$this->config['nomination5'] = '';
		$this->config['mail'] = '';
		$this->config['mailend'] = '';
		$this->config['imageCnt'] = 6;
		$this->config['imageCntForAnonim'] = 2;

		$this->config_form['onDate'] = array('type' => 'checkbox', 'caption' => 'Показывать согласно периоду?');
		$this->config_form['onComm'] = array('type' => 'list', 'listname'=>'onComm', 'caption' => 'Включить комментарии?');
		$this->config_form['spamtime'] = array('type' => 'int', 'caption' => 'Часов для спама','comment'=>'Время в течении которого пользователь может подать максимальное число объвлений');
		$this->config_form['defmax'] = array('type' => 'int', 'caption' => 'Макс. объяв. не пользов.','comment'=>'Максимум объявлений за промежуток времени не авторизованному пользователю');
		$this->config_form['defumax'] = array('type' => 'int', 'caption' => 'Макс. объяв. пользов.','comment'=>'Максимум объявлений за промежуток времени авторизованному пользователю по умолчанию если не укзана у группы пользователя');
		$this->config_form['nomination1'] = array('type' => 'varchar', 'caption' => 'Номинация №1','comment'=>'Введите название, чтобы включить номинацию');
		$this->config_form['nomination2'] = array('type' => 'varchar', 'caption' => 'Номинация №2');
		$this->config_form['nomination3'] = array('type' => 'varchar', 'caption' => 'Номинация №3');
		$this->config_form['nomination4'] = array('type' => 'varchar', 'caption' => 'Номинация №4');
		$this->config_form['nomination5'] = array('type' => 'varchar', 'caption' => 'Номинация №5');
		$this->config_form['mail'] = array(
			'type' => 'ckedit', 
			'caption' => 'Письмо', 
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
		$this->config_form['mailend'] = array('type' => 'textarea', 'caption' => 'Концовка для письма');
		$this->config_form['imageCnt'] = array('type' => 'int', 'caption' => 'Число фотографий');
		$this->config_form['imageCntForAnonim'] = array('type' => 'int', 'caption' => 'Число фотографий для анонимов');
	}

	protected function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->caption = 'Объявления';
		$this->mf_statistic = array('Y'=>'count(id)','X'=>'FROM_UNIXTIME(datea,"%Y-%m")','Yname'=>'Кол','Xname'=>'Дата');//-%d
		$this->reversePageN = true;
		$this->messages_on_page = 20;
		$this->includeJStoWEP = true;
		$this->includeCSStoWEP = true;
		$this->locallang['default']['add'] = 'Объявление добавлено.';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->ver = '0.1.2';

		$this->_enum['onComm']=array(
			0=>'Отключить',
			1=>'Пользователь',
			2=>'Включить');
		$this->_enum['type']=array(
			0=>'Предложение',
			1=>'Спрос',
			2=>'Аренда-предложение',
			3=>'Аренда-спрос',
			///4=>'Отдам даром',
			//5=>'Приму в дар',
			//6=>'Обмен'
		);
		$this->filter_form = array();
		return true;
	}

	protected function _create() {
		parent::_create();
		$this->index_fields['city'] = 'city';
		$this->index_fields['type'] = 'type';
		$this->index_fields['rubric'] = 'rubric';
		$this->index_fields['datea'] = 'datea';
		$this->index_fields['rubric'] = 'rubric';
		$this->index_fields['img_board'] = 'img_board';
		$this->index_fields['name'] = 'name';

		$this->_listnameSQL ='SUBSTRING(text,1,30)';

		if(static_main::_prmUserCheck()) {
			$thumb = array('type'=>'resize', 'w'=>'1024', 'h'=>'768');
			$maxsize = 3000;
		}
		else {
			$thumb = array('type'=>'resize', 'w'=>'800', 'h'=>'600');
			$maxsize = 1000;
		}
			
		$this->attaches['img_board'] = array('mime' => array('image/pjpeg'=>'jpg', 'image/jpeg'=>'jpg', 'image/gif'=>'gif', 'image/png'=>'png'), 'thumb'=>array($thumb,array('type'=>'resizecrop', 'w'=>'80', 'h'=>'100', 'pref'=>'s_', 'path'=>'')),'maxsize'=>$maxsize,'path'=>'');
		if($this->config['imageCnt']>0) {
			for($i = 2; $i <= $this->config['imageCnt']; $i++) 
				$this->attaches['img_board'.$i]=$this->attaches['img_board'];
		}

		$this->fields['city'] = array('type' => 'int', 'width' => 8,'attr' => 'NOT NULL');
		$this->fields['rubric'] = array('type' => 'int', 'width' => 8,'attr' => 'NOT NULL');
		$this->fields['type'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['text'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'int', 'width' => 10,'attr' => 'NOT NULL','default'=>0);
		$this->fields['phone'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['email'] = array('type' => 'varchar', 'width' => 40, 'attr' => 'NOT NULL');
		$this->fields['contact'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['period'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['on_comm'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['statview'] = array('type' => 'int', 'width' => 9, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['datea'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL','default'=>0);
		$this->fields['lastdatea'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL','default'=>0);
		$this->fields['mapx'] = array('type' => 'float','width' => '','attr' => 'NOT NULL','default'=>'0');
		$this->fields['mapy'] = array('type' => 'float','width' => '','attr' => 'NOT NULL','default'=>'0');
		$this->fields['path'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['hash'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['debug_info'] = array('type' => 'text');

		if(static_main::_prmUserCheck()) {
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
		}

		$this->ordfield = 'datea DESC';
	}

	public function setFieldsForm() {
		parent::setFieldsForm();
		if(!isset($_SESSION['user']['id']))
			$this->mess_form["info"]= array(
				"name" => "alert", 
				"value" => '<a href="/regme.html">Зарегестрируйтесь</a> или <a href="http://unidoski.ru/login.html" onclick="return showLoginForm(\'loginblock\')" class="ajaxlink">авторизируйтесь через OpenID провайдера</a>, и вы получите <a href="/inform.html" title="продлевать на больший срок свои объявления, добовлять до 4х фотографий, подписка на рассылки объявления и тд.">больше возможностей</a> для рамещения объявления.');
		$this->fields_form['name'] = array('type' => 'hidden', 'caption' => 'Назв', 'readonly'=>true);
		$this->fields_form['city'] = array(
			'type' => 'ajaxlist',
			'label'=>'Введите название города или региона',
			'listname'=>array('tablename'=>'city','where'=>'tx.active=1','nameField'=>'IF(tx.region_name_ru!=\'\',concat(tx.name,", ",tx.region_name_ru),tx.name)','ordfield'=>'tx.center DESC, tx.parent_id, tx.region_name_ru, tx.name','limit'=>30), 
			'caption' => 'Город',
			'onchange'=>'boardexport(this.value)',
			'mask' =>array('min'=>1,'onetd'=>'Рубрика','filter'=>1));

		$this->fields_form[$this->mf_createrid] = array(
			'type' => 'list', 
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
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
			'listname'=>array('class'=>'rubric', 'is_checked'=>true, 'is_tree'=>true, 'where'=>'tx.active=1', 'ordfield'=>'ordind'),
			'caption' => 'Рубрика',
			'onchange'=>'boardrubric(\'rubric\')', 
			'mask' =>array('min'=>1,'onetd'=>'close','filter'=>1));

		//$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Заголовок', 'mask' =>array('min'=>1));
		$this->fields_form['text'] = array(
			'type' => 'ckedit', 
			'caption' => 'Тект объявления', 
			'mask' =>array('name'=>'all','min'=>15,'substr'=>150,'onetd'=>'Текст', 'filter'=>1, 
				'replace'=>array('/\$/',$this->_CFG['_repl']['href'],'/\n+/','/\r+/','/\t+/','/<br>/i'),
				'replaceto'=>array('&#036;','','','','','<br/>'),
				'striptags'=>'<p><i><ul><ol><li><sup><sub><br>',
				'max'=>1600	),
			'paramedit'=>array(
				'toolbar'=>'Board',
				'height'=>250,
				'forcePasteAsPlainText'=>'true',
				'toolbarStartupExpanded'=>'false',
				'extraPlugins'=>"'cntlen'",
				'plugins'=>"'button,contextmenu,enterkey,entities,justify,keystrokes,list,pastetext,popup,removeformat,toolbar,undo'"));
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Цена (руб.)', 'mask'=>array('max'=>8,'onetd'=>'none','filter'=>1,'maxint'=>20000000));
		$this->fields_form['phone'] = array('type' => 'text', 'caption' => 'Контактные телефоны', 'mask'=>array('min2'=>'Необходимо заполнить либо `телефон`, либо `E-mail`', 'name'=>'phone3','onetd'=>'none'),'comment'=>'Обязательно указывайте код города. Можно ввести также несколько телефонов (не больше 3х) через запятую.');
		$this->fields_form['email'] = array('type' => 'text', 'caption' => 'E-mail', 'mask'=>array('min2'=>'Необходимо заполнить либо `телефон`, либо `E-mail`','name'=>'email','onetd'=>'close','filter'=>1), 'comment' => 'На ваше мыло будет выслана информация об управлении вашим объявлением.');
		
		$this->fields_form['img_board'] = array('type'=>'file','caption'=>'Фотография №1','del'=>1, 'mask'=>array('fview'=>1,'height'=>100), 'comment'=>$this->_CFG['_MESS']['_file_size'].$this->attaches['img_board']['maxsize'].'Kb');
		if($this->config['imageCnt']>0) {
			if(!static_main::_prmUserCheck()) {
				$fcnt = $this->config['imageCntForAnonim'];
				$this->fields_form['img_board']['comment'] = 'Размер фото не больше 1,5 МБ. Зарегестрированные могут публиковать до 6ти фотографий.';
			}
			else
				$fcnt = $this->config['imageCnt'];
			for($i = 2; $i <= $fcnt; $i++) {
				$this->fields_form['img_board'.$i]=$this->fields_form['img_board'];
				$this->fields_form['img_board'.$i]['caption'] = 'Фотография №'.$i;
			}
		}

		$this->fields_form['boardparam'] = array('type' => 'info', 'caption' => '<div class="showparam" onclick="show_params(\'div.boardparam\',this)"> <span>Показать</span><span style="display:none;">Скрыть</span> параметры</div>');

		$this->fields_form['contact'] = array('type' => 'text', 'caption' => 'Дополнительные контакты', 'comment'=>'ICQ, WWW', 'css'=>'boardparam formparam sdsd', 'mask'=>array('fview'=>1));
		$this->fields_form['period'] = array('type' => 'list', 'listname'=>'period','caption' => 'Срок размещения', 'css'=>'boardparam formparam sdsd', 'mask'=>array('fview'=>1),'xslprop'=>'block1');
		$this->fields_form['datea'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата размещения', 'css'=>'boardparam formparam sdsd', 'mask'=>array('sort'=>1));
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата создания', 'mask'=>array('fview'=>2,'sort'=>1));
		if($this->config['onComm']=='1')
			$this->fields_form['on_comm'] = array('type' => 'checkbox', 'caption' => 'Включить отзывы?', 'css'=>'boardparam formparam sdsd', 'mask'=>array('fview'=>1,'usercheck'=>2));
		$this->fields_form['mapx'] = array('type' => 'hidden');
		$this->fields_form['mapy'] = array('type' => 'hidden');
		$this->fields_form['map'] = array('type' => 'info', 'caption' => '<span class="jshref" onclick="boardOnMap()">Установить метку на карте</span>', 'css'=>'boardparam formparam sdsd','style'=>'text-align: center;');
		//.ru ALOgRE0BAAAAv6zZcQIAjsjexB7rFg3HTA_g1j-coGlstYMAAAAAAAAAAAD2tWiNHDQrFWdJRx7iuVAiNWEmTA==
		//.i AOCoRE0BAAAA88JZPQIANdFMmqSCC13UptUv7elqUYOoyxQAAAAAAAAAAADQ4SW-iwo9kv-xUCuu5MlHifOX8w==
		//унидоски.рф AEBlTE0BAAAAI8CRMQIACx_AbSrbH-5VVjtyAEq4d1AmTZsAAAAAAAAAAABOmdN9uXx5VhMuwpOp8geXLZRLCg==
		$_tpl['script']['api-maps'] = array('http://api-maps.yandex.ru/1.1/index.xml?loadByRequire=1&key=ALOgRE0BAAAAv6zZcQIAjsjexB7rFg3HTA_g1j-coGlstYMAAAAAAAAAAAD2tWiNHDQrFWdJRx7iuVAiNWEmTA==~AOCoRE0BAAAA88JZPQIANdFMmqSCC13UptUv7elqUYOoyxQAAAAAAAAAAADQ4SW-iwo9kv-xUCuu5MlHifOX8w==~AEBlTE0BAAAAI8CRMQIACx_AbSrbH-5VVjtyAEq4d1AmTZsAAAAAAAAAAABOmdN9uXx5VhMuwpOp8geXLZRLCg==');

		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'caption' => 'IP','readonly'=>1, 'css'=>'boardparam formparam sdsd', 'mask'=>array('usercheck'=>1,'filter'=>1,'sort'=>1));
		$this->fields_form['statview'] = array('type' => 'int', 'caption' => 'Просмотры','readonly'=>1, 'css'=>'boardparam formparam sdsd', 'mask' =>array('filter'=>1,'sort'=>1));
		$this->fields_form['path'] = array('type' => 'hidden', 'caption' => 'Путь','readonly'=>1);
		
		/*Прописываем поля для номинаций*/
		$i = 1;
		while(isset($this->config['nomination'.$i])) {
			if($this->config['nomination'.$i]!='') {
				$this->fields['nomination'.$i] = array('type' => 'int', 'width' => 9, 'attr' => 'NOT NULL','default'=>0);
				$this->fields_form['nomination'.$i] = array('type' => 'int', 'caption' => '!'.$this->config['nomination'.$i],'readonly'=>1, 'mask' =>array('filter'=>1,'sort'=>1,'usercheck'=>2));//'fview'=>1,
			}
			$i++;
		}

		$this->fields_form['debug_info'] = array(
			'type' => 'textarea',
			'caption' => 'Инфа для отладки',
			'readonly'=>1, 
			'css'=>'boardparam formparam sdsd',
			'mask' =>array('usercheck'=>1,'evala'=>'\'HTTP_USER_AGENT = \'.$_SERVER[\'HTTP_USER_AGENT\'].\'  <br/>HTTP_REFERER=\'.$_SERVER[\'HTTP_REFERER\'].\'  <br/>REQUEST_URI=\'.$_SERVER[\'REQUEST_URI\']'));

		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'mask' =>array('filter'=>1,'usercheck'=>1));


		if(static_main::_prmUserCheck()) {
			$this->fields_form['text']['mask']['max'] = 4500;
		}
		else {
			$this->fields_form['text']['comment'] = 'Текст не более 1600 символов. Зарегестрированные могут публиковать объявления с текстом до 4500 символов.';
			$this->fields_form['period']['comment']='Зарегестрированным срок размещения от 5 до 90 дней';
			$this->fields_form['datea']['comment']='Зарегестрированным дата отложенной публикации до 14 дней';
		}

		$this->fields_form['img_board']['mask']['filter'] = 1;
		$this->fields_form['img_board']['mask']['fview'] = 0;
	}

	function _childs() {
		if($this->_CFG['_F']['adminpage']) {
			include_once($this->_CFG['_PATH']['ext'].'board.class/childs.include.php');
			$this->create_child('paramb');
			$this->create_child('boardvote');
		}
	}

	function _checkmodstruct() {
		include('board.checkmodstruct.php');
		return parent::_checkmodstruct();
	}

	public function Formfilter() {// фильтр админки
		$_FILTR = $_SESSION['filter'][$this->_cl];
		$this->fields_form[$this->mf_createrid]['type'] = 'ajaxlist';
		return parent::Formfilter();
	}

	public function kPreFields(&$data,&$param) {
		$mess = parent::kPreFields($data,$param);

		global $_tpl;
		_new_class('city',$CITY);
		if(!isset($data['rubric']) and isset($_REQUEST['rubric']))
			$data['rubric'] = (int)$_REQUEST['rubric'];

		if(static_main::_prmUserCheck() and !isset($data['phone'])) {
			$this->fields_form['phone']['value']=$_SESSION['user']['phone'];
			$this->fields_form['email']['value']=$_SESSION['user']['email'];
		}
	
		if($this->config['imageCnt']>0) {
			if(!static_main::_prmUserCheck()) {
				$fcnt = $this->config['imageCntForAnonim'];
			}
			else
				$fcnt = $this->config['imageCnt'];
			for($i = 2; $i <= $fcnt; $i++) {
				if(!isset($data['img_board'.$i]) or !$data['img_board'.$i]) {
					$this->fields_form['img_board'.$i]['style'] = 'display:none;';
					if($i==2)
						$ni = '';
					else
						$ni = ($i-1);
					if(!isset($this->fields_form['img_board'.$ni]['comment']))
						$this->fields_form['img_board'.$ni]['comment'] = '';
					$this->fields_form['img_board'.$ni]['comment'] .= ' <div class="shownextfoto" onclick="shownextfoto(this,\'img_board'.$i.'\')">Ещё фото</div>';
				}
			}
		}
		
		if($this->id){
			$this->fields_form['rubric']['onchange'] = 'boardrubric(\'rubric\','.$this->id.')';
			if(static_main::_prmUserCheck(1)) {
				unset($this->fields_form['datea']['readonly']);
			}
		}
		else{
			$this->fields_form['datea']['type'] = 'list';
			$this->fields_form['datea']['listname'] = 'datea';
			unset($this->fields_form['datea']['readonly']);
			//= array('type' => 'list', 'listname'=>'datea', 'caption' => 'Дата публикации','css'=>'boardparam formparam sdsd','comment'=>$this->fields_form['datea']['comment']);
			//if(!$data['contact'] and !$data['datea']) // скрывает доп поля
			//	$_tpl['onload'] .= 'jQuery(\'#tr_boardparam div.showparam\').click();';
		}
		$this->fields_form['city']['default'] = $CITY->id;
		$this->fields_form['city']['default_2'] = $CITY->name;

		if(!$this->id) {
			$this->eDATA = array();
			$this->fields_form['boardexport'] = array('type' => 'info', 'caption' => '<div class="showparam hideparam" onclick="show_params(\'div.boardexport\',this)"> <span>Показать</span><span style="display:none;">Скрыть</span>  публикации</div>');
			if($CITY->id) {
				// см. _phpscript/_js.php [34]
				_new_class('exportboard',$EXPORTBOARD);
				list($this->eDATA,$form) = $EXPORTBOARD->getListBoard($CITY->id);
			}
			if(!count($this->eDATA))
				$this->fields_form['boardexport']['style'] = 'display:none;';
			else
				$this->fields_form = array_merge($this->fields_form,$form);
			$this->fields_form['active']['type'] = 'hidden';
		}
		if(!isset($this->RUBRIC->tablename))
			_new_class('rubric',$this->RUBRIC);
		if($data['rubric']) {
			$this->fields_form = static_main::insertInArray($this->fields_form,'rubric',$this->ParamFieldsForm($this->id,$data['rubric'],$data['type'])); // обработчик параметров рубрики
		}
		if(!static_main::_prmUserCheck(1))
			unset($this->fields_form['text_ckedit']);
		return $mess;
	}

	public function super_inc($param=array(),$ftype='') {
		$data = parent::super_inc($param,$ftype);
		$tmp = array(
			array('css'=>'up','href'=>'http://'.$_SERVER['HTTP_HOST'].'/board.html?id=%id%&hash='.md5('md5').'&type=up','title'=>'Обновить дату')
		);
		$data[0]['superlist']['data']['abtn'] = $tmp;
		return $data;
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
				if(isset($_POST['phone']) and $_POST['phone']) {
					$_tpl['onload'] .= 'putEMF(\'phone\',\'Телефонный номер должен состоять из 11(с кодом страны) или 10 цифр, несколько телефонов разделяйте запятой.\');';
				} else {
					$_tpl['onload'] .= 'putEMF(\'phone\',\'Необходимо указать либо телефон...\');';
					$_tpl['onload'] .= 'putEMF(\'email\',\'либо Email\');';
				}
			}
			else {
				$this->fields_form['phone']['error'][] = 'Необходимо указать либо телефон...';
				$this->fields_form['email']['error'][] = 'либо Email';
			}
		}
		return $arr;
	}

	function allChangeData($type='',$data='') {
		return true;
	}

	public function _save_item($vars=array()) {
		$cls=array();$ct= array();$tmp = array();
		$vars['name'] = '';
		$PARAM = &$this->RUBRIC->childs['param'];

		if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)){
			foreach($PARAM->data as $k=>$r) {
				if($vars['param_'.$k]) {
					$val = $vars['param_'.$k];
					$cls['name'.$r['type']]=mysql_real_escape_string($val);
					if($r['constrn']) {
						if(isset($this->_enum['fli'.$r['formlist']])) {
							if(isset($this->_enum['fli'.$r['formlist']][$val]))
								$val = $this->_enum['fli'.$r['formlist']][$val];
							elseif(is_array($this->_enum['fli'.$r['formlist']])) {
								foreach($this->_enum['fli'.$r['formlist']] as $kk=>$rr) {
									if(isset($rr[$val])) {
										$val = $rr[$val];
										break;
									}
								}
							}
						}
						if($val) {
							if(is_array($val))
								$val = $val['#name#'];
							$vars['name'] .= '/ '.$val;
							if($r['edi'])
								$vars['name'] .= ' '.$r['edi'];
						}
					}
				}
			}
		}
		$vars['path'] = $this->getTranslitePatchFromText($vars['text']);
		if($ret = parent::_save_item($vars)) {
			/*if(isset($vars['city'])) {
				foreach($this->data as $r)
					$this->updateCount2($r['city'],$r['rubric'],$r['active']);					
			}*/
			if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)) {
				if(count($cls)) {
					$result=$this->SQL->execSQL('DELETE FROM paramb WHERE owner_id='.$this->id);
					if($result->err) return false;
					$query = 'INSERT into paramb (owner_id,'.implode(',',array_keys($cls)).') values ('.$this->id.',"'.implode('","',$cls).'")';
					$result=$this->SQL->execSQL($query);
					if($result->err) return false;
				}
			}
		}
		return $ret;
	}

	public function _add_item($vars) {
		$vars['datea'] = ((int)$vars['datea']+time());
		$vars['name'] = '';//$this->_enum['type'][$vars['type']].'/ '.$this->RUBRIC->data2[$vars['rubric']]['name'];
		$PARAM = &$this->RUBRIC->childs['param'];
		$cls=array();
		$tmp = array();

		if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)) {
			foreach($PARAM->data as $k=>$r){
				if($vars['param_'.$k]) {
					$val = $vars['param_'.$k];
					$cls['name'.$r['type']]=mysql_real_escape_string($val);
					if($r['constrn']) {
						if(isset($this->_enum['fli'.$r['formlist']])) {
							if(isset($this->_enum['fli'.$r['formlist']][$val]))
								$val = $this->_enum['fli'.$r['formlist']][$val];
							elseif(is_array($this->_enum['fli'.$r['formlist']])) {
								foreach($this->_enum['fli'.$r['formlist']] as $kk=>$rr) {
									if(isset($rr[$val])) {
										$val = $rr[$val];
										break;
									}
								}
							}
						}
						if($val) {
							if(is_array($val))
								$val = $val['#name#'];
							$vars['name'] .= '/ '.$val;
							if($r['edi'])
								$vars['name'] .= ' '.$r['edi'];
						}
					}
				}
			}
		}

		$vars['path'] = $this->getTranslitePatchFromText($vars['text']);
		//if(!static_main::_prmUserCheck())
		$vars['hash'] = md5($vars['mf_timecr'].'solt3'.$vars['mf_ipcreate'].$vars['city'].$vars['email']);
		if($ret = parent::_add_item($vars)) {
			$this->temp_AddText = '';
			/////////////
			if(!isset($this->RUBRIC->cacheData))
				$this->RUBRIC->cacheData = $this->RUBRIC->_query('id,parent_id,name,lname','WHERE active=1','id');
			if($vars['city']) { // EXPORT BOARD
				_new_class('exportboard',$EXPORTBOARD);
				$this->eDATA = $EXPORTBOARD->getListData($vars['city']);
				if(count($this->eDATA))
					foreach($this->eDATA as $ek=>$exportValue) {
						if(isset($vars['exportboard'.$exportValue['id']]) and $vars['exportboard'.$exportValue['id']]) {
							include($this->_CFG['_PATH']['ext'].'/exportboard.class/phpexport/'.$exportValue['phpexport'].'.php');
						}
					}
			}
			if($vars['active']==1 or !isset($vars['active']))
				$this->updateCount($vars['city'],$vars['rubric'],1);
			if(count($cls)) {
				$query = 'INSERT into paramb (owner_id,'.implode(',',array_keys($cls)).') values ('.$this->id.',"'.implode('","',$cls).'")';
				$result=$this->SQL->execSQL($query);
				if($result->err) return false;
			}else {
				$query = 'INSERT into paramb (owner_id) values ('.$this->id.')';
				$result=$this->SQL->execSQL($query);
				if($result->err) return false;
			}
			$this->form=array();
			if($vars['email']) { //!static_main::_prmUserCheck() and 
				_new_class('mail',$MAIL);
				$datamail = array('from'=>$MAIL->config['mailrobot']);
				$datamail['mail_to'] = $vars['email'];
				$datamail['subject'] = strtoupper($_SERVER['HTTP_HOST']).' - Ваше объявление №'.$this->id;
				$datamail['text'] = str_replace(
						array('%idhref%','%href%','%host%','%addtext%'),
						array('http://'.$_SERVER['HTTP_HOST'].'/'.$this->RUBRIC->cacheData[$vars['rubric']]['lname'].'/'.$vars['path'].'_'.$this->id.'.html','?id='.$this->id.'&hash='.$vars['hash'],$_SERVER['HTTP_HOST'],$this->temp_AddText),
						$this->config['mail']);
				$MAIL->reply = 0;
				if(!$MAIL->Send($datamail))
					trigger_error('Добавление объявления - '.$this->_CFG['_MESS']['mailerr'], E_USER_WARNING);
			}
		}
		return $ret;
	}

	public function _Act($act,&$param) {
		$ret = parent::_Act($act,$param);
		if($ret[1]) {
			foreach($this->data as $r) {
				$this->updateCount($r['city'],$r['rubric'],(!$act?-1:1));
			}
		}
		return $ret;
	}
	public function _delete() {
		if($ret = parent::_delete()) {
			if(isset($this->data) and count($this->data))
				foreach($this->data as $r) {
					$this->updateCount($r['city'],$r['rubric'],-1);
				}
		}
		return $ret;
	}

	public function _UpdItemModul($param) {
		if(isset($_POST['text']) and mb_strlen($_POST['text'])>14) {
			preg_match_all('/[А-Яа-яЁё]/u',$_POST['text'],$matches);
			if(count($matches[0])<5) {
				$mess[] = array('name'=>'error', 'value'=>'Тест должен быть написан могучем русском языке!');
				return array(array('messages'=>$mess),-1);
			}
		}
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

		if(isset($_POST['datea']) and $_POST['datea'])
			$time = ((int)$_POST['datea']+time());
		else
			$time = time();

		$cls ='SELECT id,city,rubric,text,mf_timecr FROM '.$this->tablename.' WHERE datea>='.($time-(3600*$this->config['spamtime'])).' and datea<'.$time;
		
		if(static_main::_prmUserCheck())
			$cls .= ' and '.$this->mf_createrid.'="'.$_SESSION['user']['id'].'"';
		else
			$cls .= ' and mf_ipcreate=INET_ATON("'.$_SERVER["REMOTE_ADDR"].'")';

		$result = $this->SQL->execSQL($cls.' order by datea DESC');
		$data = array();
		if(!$result->err) {
			if($row = $result->fetch_array()) {
				if(count($_POST)>3 and (time()-$row['mf_timecr'])<30) {
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
		$this->SQL->execSQL('INSERT INTO countb (city,owner_id,cnt) VALUES ('.$city.','.$rub.','.($f<1?0:$f).') ON DUPLICATE KEY UPDATE cnt=cnt+('.$f.') ');
		$this->SQL->execSQL('INSERT INTO countb (city,owner_id,cnt) VALUES (0,'.$rub.','.($f<1?0:$f).') ON DUPLICATE KEY UPDATE cnt=cnt+('.$f.') ');
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
		$this->SQL->execSQL('UPDATE city t1 SET t1.cnt=0');
		$this->SQL->execSQL('truncate table `countb`');

		$DataCity = $DataPcity = array();
		$resCity = $this->SQL->execSQL('SELECT id,parent_id FROM city WHERE active=1');
		while($row = $resCity->fetch_array()) {
			$DataCity[$row['id']] = $row['parent_id'];
			$DataPcity[$row['parent_id']] [$row['id']] = true;
		}

		$tempCountb = array(); // countb
		$tempCity = array(); //city

		$result = $this->SQL->execSQL('SELECT t1.city,t1.rubric,count(t1.id) as cnt FROM board t1 WHERE t1.datea<UNIX_TIMESTAMP() and t1.active=1 '.(!$this->config['onDate']?'':'and t1.datea>UNIX_TIMESTAMP()-t1.period').' GROUP BY t1.city,t1.rubric');
		while(!$result->err and $row = $result->fetch_array()) {
			// ДЛя всей России по рубрикам
			if(isset($tempCountb[0][$row['rubric']])) 
				$tempCountb[0][$row['rubric']] += $row['cnt'];
			else
				$tempCountb[0][$row['rubric']] = $row['cnt'];
			//для конкретного города по рубрикам
			if(isset($tempCountb[$row['city']][$row['rubric']]))
				$tempCountb[$row['city']][$row['rubric']] += $row['cnt'];
			else
				$tempCountb[$row['city']][$row['rubric']] = $row['cnt'];
			// для всего города
			if(isset($tempCity[$row['city']]))
				$tempCity[$row['city']] += $row['cnt'];
			else
				$tempCity[$row['city']] = $row['cnt'];

			if($DataCity[$row['city']]) {// если город, то району прибавляем тоже
				$pcity = $DataCity[$row['city']];
				if(isset($tempCountb[$pcity][$row['rubric']]))
					$tempCountb[$pcity][$row['rubric']] += $row['cnt'];
				else
					$tempCountb[$pcity][$row['rubric']] = $row['cnt'];

				if(isset($tempCity[$pcity]))
					$tempCity[$pcity] += $row['cnt'];
				else
					$tempCity[$pcity] = $row['cnt'];
			} 
			elseif(isset($DataPcity[$row['city']])) {//если район, то всем его городам прибавляем его объявы
				foreach($DataPcity[$row['city']] as $pc=>$pr) {
					if(isset($tempCountb[$pc][$row['rubric']]))
						$tempCountb[$pc][$row['rubric']] += $row['cnt'];
					else
						$tempCountb[$pc][$row['rubric']] = $row['cnt'];

					if(isset($tempCity[$pc]))
						$tempCity[$pc] += $row['cnt'];
					else
						$tempCity[$pc] = $row['cnt'];
				}
			}

		}

		foreach($tempCountb as $cit=>$row) {
			$cls = array();
			foreach($row as $rub=>$ct) {
				$cls[] = '('.$cit.','.$rub.','.$ct.')';
			}
			$qq = 'INSERT INTO countb (city,owner_id,cnt) VALUES '.implode(',',$cls);
			$this->SQL->execSQL($qq);
		}
		foreach($tempCity as $k=>$r) {
			$qq = 'UPDATE city t1 SET t1.cnt =t1.cnt+'.$r.' WHERE t1.id='.$k;
			$this->SQL->execSQL($qq);
		}
		return true;
	}

	function servClear() {
		$result = $this->SQL->execSQL('UPDATE board t1 SET t1.active=0 WHERE '.(!$this->config['onDate']?'t1.datea<'.(time()-3600*24*120):'t1.datea<UNIX_TIMESTAMP()-t1.period'));
		$id = array();
		$result = $this->SQL->execSQL('SELECT t2.id FROM board t2 LEFT JOIN rubric t3 ON t2.rubric=t3.id WHERE isNull(t3.id) and t2.active=1 GROUP BY t2.id');
		if(!$result->err) {
			while($row = $result->fetch_array())
				$id[] = $row['id'];
		}
		$result = $this->SQL->execSQL('SELECT t2.id FROM board t2 LEFT JOIN city t3 ON t2.city=t3.id WHERE isNull(t3.id) and t2.active=1 GROUP BY t2.id');
		if(!$result->err) {
			while($row = $result->fetch_array())
				$id[] = $row['id'];
		}
		if(count($id))
			$this->SQL->execSQL('UPDATE board t1 SET t1.active=0 WHERE t1.id IN ('.implode(',',$id).')');
		$this->servUpdate();
	}

	public function ParamFieldsForm($id,$rid,$tp=0,$listclause='') { // форма для редак-добавл объявы и поиска объявы
		//$id - id объявы
		//$rid - id рубрики
		//$tp - тип объявления
		//listclause - подзапрос
		global $_tpl;//в onLoad слайдер
		$FLI = $FMCB = array();
		if(is_array($id))
			$fSearch = 1;// если это для поискового фильтра
		else
			$fSearch = 0;
		if(!$fSearch and $id) {
			$result = $this->SQL->execSQL('SELECT * FROM paramb WHERE owner_id IN ('.$id.')');
			if(!$result->err) {
				if ($row = $result->fetch_array()){
					$paramdata=$row;
				}
			}
			else {
				return array();
			}
		}
		$PARAM = &$this->RUBRIC->childs['param'];
		$listfields = array('*');
		$cls = 'WHERE owner_id="'.$rid.'" and active=1 order by ordind';
		$PARAM->data = $PARAM->_query($listfields,$cls,'id');
		if(count($PARAM->data)) {
			$form=array();
			$pdata=array();
			foreach($PARAM->data as $k=>$r) {
				$val='';

				if($fSearch) {
					$val = (isset($id['param_'.$k])?$id['param_'.$k]:'');
				}
				elseif($id) {
					$val=$paramdata['name'.$r['type']];
				}
				
				$type = $PARAM->getTypeForm($r['type']);
				$multiple = 0;
				if($type=='list' and $r['typelist']) {
					if($r['typelist']==1)
						$type = 'ajaxlist';
					elseif($r['typelist']==2 and $fSearch) {
						$type = 'checkbox';
						$multiple = 1;
					}
				}

				$form['param_'.$k] = array(
					'caption'=>$r['name'].($r['edi']!=''?', '.$r['edi']:''),
					'type'=>$type,
					'multiple'=>$multiple,
					'type2'=>$r['type'],
					'value'=>$val,
					'css'=>'addparam');

				if($r['def']!='' and !$fSearch) {// and !$val
					if(strpos($r['def'],'eval=')!==false)
						eval('$form["param_'.$k.'"]["default"] = '.substr($r['def'],5).';');
					else
						$form['param_'.$k]['default'] = $r['def'];
				}
				elseif($r['type']>39 and $r['type']<49) {
					$form['param_'.$k]['default'] = date('Y');
				}

				if($type=='int') {
					if($r['min'])
						$form['param_'.$k]['mask']['minint'] = $r['min'];
					elseif($r['type']>39 and $r['type']<49)
						$form['param_'.$k]['mask']['minint'] = $r['min'] = 1949;
					if($r['max'])
						$form['param_'.$k]['mask']['maxint'] = $r['max'];
					elseif($r['type']>39 and $r['type']<49) {
						$form['param_'.$k]['mask']['maxint'] = $r['max'] = date('Y');
					}

					if(is_array($id))
						$form['param_'.$k]['value_2']= (isset($id['param_'.$k.'_2'])?$id['param_'.$k.'_2']:'');

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
					if($fSearch) {
						if($form['param_'.$k]['value']=='')
							$form['param_'.$k]['value']=$r['min'];
						if(!isset($form['param_'.$k]['value_2']) or $form['param_'.$k]['value_2']=='')
							$form['param_'.$k]['value_2']=$r['max'];
					}elseif($form['param_'.$k]['value']=='' and isset($form['param_'.$k]['default']))
						$form['param_'.$k]['value'] = $form['param_'.$k]['default'];
					if($fSearch) // для фильтра
						$_tpl['onload'] .= "gSlide('tr_param_".$k."',".(int)$r['min'].",".(int)$r['max'].",".(int)$form['param_'.$k]['value'].",".(int)$form['param_'.$k]['value_2'].",".(int)$r['step'].");";
				}else
					$form['param_'.$k]['mask']=array('min'=>$r['min'],'max'=>$r['max']);

				if($r['mask']) $form['param_'.$k]['mask']['patterns']=array('match'=>$r['mask']);
				if($r['maskn']) $form['param_'.$k]['mask']['patterns']=array('nomatch'=>$r['maskn']);

				if($r['comment']!='') $form['param_'.$k]['comment']=$r['comment'];
				
				$tp=(int)$tp;
				if($tp==1 || $tp==3) unset($form['param_'.$k]['mask']['min']);

				if($type=='ajaxlist') {
					$form['param_'.$k]['listname'] = array('tablename'=>'formlistitems','where'=>' tx.checked=1 and tx.active=1 GROUP BY tx.id','ordfield'=>'tx.ordind');
					$form['param_'.$k]['value_2'] = $id['param_'.$k.'_2'];
				}elseif($type=='list') {
					$form['param_'.$k]['listname']='fli'.$r['formlist'];
					$form['param_'.$k]['mask']['begin']=0;
					$FLI[] = $r['formlist'];
				}elseif($type == 'checkbox' and $multiple) {
					$form['param_'.$k]['listname']='fli'.$r['formlist'];
					$form['param_'.$k]['mask']['begin']=0;
					$FLI[] = $r['formlist'];
					$FMCB[$r['formlist']] = $k;// сотавляем массив тех списков которые checkbox и multiple , чтобы скрывать скриптом непопулярные элементы
					if(!is_array($form['param_'.$k]['value']) and $form['param_'.$k]['value']) {
						$form['param_'.$k]['value'] = array($form['param_'.$k]['value']);
					}
					if(is_array($form['param_'.$k]['value']))
					foreach($form['param_'.$k]['value'] as $kkk=>$rrr) {
						if($rrr) $form['param_'.$k.'_'.$rrr] = array();
					}
					$_tpl['onload'] .= 'mCBoxVis('.$k.');';
				}
			}

			if(count($FLI)) {
				$clause = 'SELECT t1.id,t1.owner_id,t1.parent_id,t1.name,t1.checked,t1.cntdec FROM formlistitems t1 WHERE t1.owner_id IN ('.implode(',',$FLI).') and t1.active=1 ORDER BY t1.ordind';
				$result = $this->SQL->execSQL($clause);
				if(!$result->err) {
					$templ = array();
					while ($row = $result->fetch_array()) {
						if(!isset($this->_enum['fli'.$row['owner_id']][0][0]))//$fSearch and 
							$this->_enum['fli'.$row['owner_id']][0][0] = ' --- ';
						$this->_enum['fli'.$row['owner_id']][$row['parent_id']][$row['id']] = array('#id#'=>$row['id'],'#name#'=>$row['name'],'#checked#'=>$row['checked']);
						if($fSearch and !$row['parent_id'] and isset($FMCB[$row['owner_id']])) {
							$templ[$row['owner_id']][$row['cntdec']][] = $row['id'];
						}
					}
					if($fSearch) {
						/*Ограничение числа вывода элементов checkbox*/
						$out = array();
						foreach($templ as $kk=>$rr) {
							$tempr = array();
							if(count($rr)>1) {
								$rr = array_multisort($rr, SORT_NUMERIC, SORT_DESC);
								foreach($rr as $rrr) {
									$tempr = array_merge($tempr,$rrr);
								}
							}else
								$tempr = current($rr);
							if(count($tempr)>12) {
								$tempr = array_slice($tempr,0,10);
								foreach($tempr as $rrr)
									$out[] = $rrr.':1';
								$_tpl['onload'] .= 'mCBoxShortHide('.$FMCB[$kk].');';
							}
						}
						if(count($out))
							$_tpl['script']['vfmcb'] = 'var vfmcb = {'.implode(',',$out).'};';
						/*Под элементы мульти чекбоксов*/

						foreach($FMCB as $kk=>$rr) {
							if(is_array($form['param_'.$rr]['value'])) { 
							foreach($form['param_'.$rr]['value'] as $kkk=>$rrr) {	
								if(isset($this->_enum[$form['param_'.$rr]['listname']][$rrr]) and is_array($this->_enum[$form['param_'.$rr]['listname']][$rrr])) {
									$form['param_'.$rr.'_'.$rrr] = array(
										'caption'=>$this->_enum[$form['param_'.$rr]['listname']][0][$rrr]['#name#'],//$PARAM->data[$rr]['name'],
										'type'=>'checkbox',
										'multiple'=>1,
										'type2'=>$PARAM->data[$rr]['type'],
										'value'=>(isset($id['param_'.$rr.'_'.$rrr])?$id['param_'.$rr.'_'.$rrr]:''),
										'css'=>'addparam',
										'listname'=>'fli'.$PARAM->data[$rr]['formlist'],
										'mask' => array('begin'=>$rrr),
									);
									$_tpl['onload'] .= 'mCBoxVis(\''.$rr.'_'.$rrr.'\');';
								}
								else
									unset($form['param_'.$rr.'_'.$rrr]);
							}}
							if(count($this->_enum[$form['param_'.$rr]['listname']])>1)
								$_tpl['onload'] .= 'mCBoxCA('.$rr.');';
						}

					}

				}
				else return array();
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
			$listfields = array('*');
			$cls = 'WHERE owner_id="'.$rid.'" and active=1 order by ordind';
			$PARAM->data = $PARAM->_query($listfields,$cls,'id');
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
					if($type=='checkbox') {
						if($r!='')
							$clauseF[$k] = 't4.name'.$nameK.'="'.$r.'"';
					}
					elseif($type=='int') {
						$temparr=array();
						$r = (int)$r;
						if($r and (!$this->filter_form[$k]['mask']['minint'] or $this->filter_form[$k]['mask']['minint']<$r))
							$clauseF[$k] = 't4.name'.$nameK.'>='.(int)$r;
						if((int)$filter[$k.'_2'] and (!$this->filter_form[$k]['mask']['maxint'] or $this->filter_form[$k]['mask']['maxint']>(int)$filter[$k.'_2'])) {
							$clauseF[$k.'_2'] = 't4.name'.$nameK.'<='.(int)$filter[$k.'_2'];
						}
					}
					elseif(is_array($r)) {
						if(count($r)) {
							$t3mp = $r;
							foreach($r as $kk=>$rr) {
								if(isset($filter['param_'.$tempid.'_'.$rr]) and count($filter['param_'.$tempid.'_'.$rr])) {
									$t3mp = array_merge($t3mp,$filter['param_'.$tempid.'_'.$rr]);
								}
								elseif(isset($this->_enum[$this->filter_form[$k]['listname']][$rr]) and is_array($this->_enum[$this->filter_form[$k]['listname']][$rr])) {
									foreach($this->_enum[$this->filter_form[$k]['listname']][$rr] as $kkk=>$rrr)
										$t3mp[] = $rrr['#id#'];
								}
							}
							$clauseF[$k] = 't4.name'.$nameK.' IN ("'.implode('","',$t3mp).'")';
						}
					}
					else {
						if($r)
							$clauseF[$k] = 't4.name'.$nameK.'="'.$r.'"';
					}
				}
				//elseif($k=='datea')
				//	$clauseF[$k] = 't1.datea<"'.$r.'"';
				elseif(isset($this->fields[$k]) and isset($this->fields_form[$k])) {
					if(isset($filter[$k.'_2']) and $this->fields_form[$k]['type']=='int'){
						$r = (int)$r;
						if($r and (!$this->filter_form[$k]['mask']['minint'] or $this->filter_form[$k]['mask']['minint']<$r))
							$clauseF[$k] = 't1.'.$k.'>='.$r;
						if((int)$filter[$k.'_2'] and (!$this->filter_form[$k]['mask']['maxint'] or $this->filter_form[$k]['mask']['maxint']>(int)$filter[$k.'_2']))
							$clauseF[$k.'_2'] = 't1.'.$k.'<='.(int)$filter[$k.'_2'];
					}
					elseif($k!='city' and $this->fields_form[$k]['type']=='list'){
						if(is_array($r) and count($r))
							$clauseF[$k] = 't1.'.$k.' IN ("'.implode('","',$r).'")';
						elseif($r!='')
							$clauseF[$k] = 't1.'.$k.'="'.$r.'"';
					}
					elseif($k=='text'){
						if($r!='')
							$clauseF[$k] = 't1.'.$k.' LIKE "%'.$r.'%"';
					}
					elseif($k=='datea')
						$clauseF[$k] = 't1.datea>"'.$r.'"';
				}
				elseif($r=='1' and $k=='foto') {
					$temp=array();
					foreach($this->attaches as $tk=>$tr)
						$temp[] = 't1.'.$tk.'!=""';
					$clauseF[$k] = '('.implode(' or ',$temp).')';
				}
			}
		}
		$xml='';

		$clause['from'] = 'FROM '.$this->tablename.' t1 ';//1
		$clause['ljoin'] = ' LEFT JOIN paramb t4 ON t4.owner_id=t1.id ';//2
		$clause['where'] = ' WHERE t1.active=1 and t1.datea<UNIX_TIMESTAMP() ';//4

		if($this->config['onDate'] and !$limit)
			$clause['where'] .= 'and t1.datea>UNIX_TIMESTAMP()-t1.period';

		$rlist = array();
		if($rid) {
			if(isset($this->RUBRIC->data2[$rid]))
				$rlist[$rid] = $this->RUBRIC->data2[$rid]['name'];
			if(isset($this->RUBRIC->data[$rid])) {
				foreach($this->RUBRIC->data[$rid] as $k=>$r) {
					if(isset($this->RUBRIC->data[$k])){
						$rlist = $this->RUBRIC->data[$k]+$rlist;
					}else
						$rlist[$k]=$r;						
				}
			}
		}
		if(count($rlist))
			$clause['where'] .= ' and t1.rubric IN ('.implode(',',array_keys($rlist)).') ';

		_new_class('city',$CITY);
		/*if(isset($CITY->center) and $CITY->center) { // для центральных городов
			$clause['where'] .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
		}
		else*/
		if(!$CITY->parent_id and $CITY->id and !$CITY->center) {// для районов
			$listfields = array('t1.id');
			$cls = 't1 WHERE t1.parent_id="'.$CITY->id.'" and t1.active=1';
			$CITY->data = $CITY->_query($listfields,$cls,'id');
			$clause['where'] .= ' and t1.city IN ('.implode(',',array_merge($CITY->citylist,array_keys($CITY->data))).')';
		}
		//elseif(isset($CITY->citylist) and count($CITY->citylist)==1) { //для прочих городков выводим объявления только региона и самого города
		//	$clause['where'] .= ' and t1.city IN ('.implode(',',array_merge($CITY->citylist,array($CITY->parent_id))).')';
		//}
		elseif(count($CITY->citylist)) {//так и не понял где и когда это выполняется
			$clause['where'] .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
		}

		$cls_filtr = '';
		if(count($clauseF)) {
			$clause['where'] .= ' and '.implode(' and ',$clauseF);
			$cls_filtr = $clause['ljoin'];
		}
		$result = $this->SQL->execSQL('SELECT count(DISTINCT t1.id) as cnt '.$clause['from'].$cls_filtr.$clause['where']);
		if(!$result->err and $row = $result->fetch_array() and $row['cnt']>0) {
			if(is_string($rss) and $rss=='cnt') return $row['cnt'];
			if(!$limit) {
				$xml .= '<cnt>'.$row['cnt'].'</cnt>';
				/*** PAGE NUM  REVERSE ***/
				if($this->reversePageN) {
					if($this->_pn == 0) 
						$this->_pn = 1;
					else
						$this->_pn = floor($row['cnt']/$this->messages_on_page)-$this->_pn+1;
				}
				/***/
				global $PGLIST;
				$pgData = $this->fPageNav($row['cnt'],key($PGLIST->pageinfo['path']).'.html');
				$xml .= $this->kData2xml($pgData,'pagenum');
				if($row['cnt']>12) {
					global $_tpl;
					$_tpl['onload'] .='pagenum('.$pgData['cntpage'].','.($this->reversePageN?'true':'false').');';
				}
				$pcnt = 0;
				if($this->reversePageN) {
					if($this->_pn==floor($row['cnt']/$this->messages_on_page)) {
						$this->messages_on_page = $row['cnt']-$this->messages_on_page*($this->_pn-1); // правдивый
						//$this->messages_on_page = $this->messages_on_page*$this->_pn-$countfield; // полная запись
					}
					else {
						$pcnt = $row['cnt']-$this->messages_on_page*$this->_pn; // начало отсчета
					}
				}
				else
					$pcnt = $this->messages_on_page*($this->_pn-1); // начало отсчета
				if($pcnt<0)
					$pcnt = 0;
			}



			$clause_from =', t3.name as cityname, '.($this->_CFG['site']['rf']?'t3.domen_rf':'t3.domen').' as domen ';
			$clause['ljoin'] .= ' JOIN '.$CITY->tablename.' t3 ON t1.city=t3.id ';

			$clause['where'] .= ' GROUP BY t1.id ORDER BY '.$order.' DESC';
			if(!$limit)
				$clause['where'] .= ' LIMIT '.$pcnt.', '.$this->messages_on_page;
			else
				$clause['where'] .= ' LIMIT '.$limit;
			$pData = $this->fGetParamBoard('SELECT t4.*, t1.* '.$clause_from.implode(' ',$clause));// retutn $this->data
			if(static_main::_prmUserCheck() and static_main::_prmModul($this->_cl, array(3)))
				$moder = 1;
			else
				$moder = 0;
			foreach($this->data as $k=>$r) {
				$rname=array();
				$temp=$r['rubric'];
				if(!$rss) {
					if(!$r['path']) {
						$r['path'] = $this->getTranslitePatchFromText($r['text']);
						$vars = array('path'=>$r['path']);
						$this->id = $r['id'];
						parent::_save_item($vars);
					}
					while(isset($this->RUBRIC->data2[$temp]) and $rid!=$temp) {
						$rname[] = $this->RUBRIC->data2[$temp]['name'];
						$temp=$this->RUBRIC->data2[$temp]['parent_id'];
					};
					$xml .='<item>
						<id>'.$r['id'].'</id>
						<city>'.$r['city'].'</city>
						<rubric>'.$r['rubric'].'</rubric>';
					$xml .= '<domen>http://'.$r['domen'].'.'.$_SERVER['HTTP_HOST2'].'</domen>';
					$xml .= '<rubpath>/'.$this->RUBRIC->data2[$r['rubric']]['lname'].'</rubpath>';
					if(count($rname)) 
						$xml .='<rname>'.implode('</rname><rname>',array_reverse($rname)).'</rname>';
					$tempval = rand(0,9);
					$xml .='<type>'.$r['type'].'</type>
						<name>'.$r['name'].'</name>
						<moder>'.$moder.'</moder>
						<path>'.$r['path'].'</path>
						<tname>'.($r['cityname']?$r['cityname'].' / ':'').$this->_enum['type'][$r['type']].'</tname>
						<text><![CDATA['.html_entity_decode(strip_tags($r['text']),2,'UTF-8').']]></text>
						<phone><![CDATA['.str_replace(array('-','8',$tempval),array('<span>-</span><span class="sdsd">'.$tempval.'</span>','<span style="display:none;">3</span>8','<span style="display:none;">'.$tempval.'</span><span class="sdsb">'.$tempval.'</span>'),$r['phone']).']]></phone>
						<email>'.$r['email'].'</email>
						<datea>'.date('Y-m-d',$r['datea']).'</datea>';
						/*preg_match_all($this->_CFG['_repl']['href'],$r['contact'],$cont);
						if(count($cont[0])) {
							$temp = array();
							foreach($cont[0] as $rc)
								$temp[] = '<a href="/_redirect.php?url='.(base64_encode($rc)).'" rel="nofollow">Ссылка</a>';
							$r['contact'] = str_replace($cont[0],$temp,$r['contact']);
						}
						$xml .= '<contact><![CDATA['.$r['contact'].']]></contact>';
						*/

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
				} else {
					while(isset($this->RUBRIC->data2[$temp])) {
						$rname[] = $this->RUBRIC->data2[$temp]['name'];
						$temp=$this->RUBRIC->data2[$temp]['parent_id'];
					}
					$xml .='<item><title>'.($r['cityname']?$r['cityname'].' / ':'').$this->_enum['type'][$r['type']].' / '.implode(' / ',array_reverse($rname));
					if($r['name'])
						$xml .= $r['name'];
					$xml .='</title> 
						<link>http://'.$r['domen'].'.'.$_SERVER['HTTP_HOST2'].'/'.$this->RUBRIC->data2[$r['rubric']]['lname'].'/'.$r['path'].'_'.$r['id'].'.html</link> 
						<description>'.mb_substr(html_entity_decode(strip_tags($r['text']),2,'UTF-8'),0,300).'...</description>
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

	public function fNewDisplay($limit) {
		_new_class('city',$CITY);
		$PARAM = &$this->RUBRIC->childs['param'];
		$clause = 'SELECT t1.id,t1.path,t1.name,t1.rubric,t1.type,t1.text,t1.datea,t1.city FROM '.$this->tablename.' t1 WHERE t1.active=1 and t1.datea<'.time().' ';

		if(isset($CITY->center) and $CITY->center) { // для центральных городов
			$clause .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
		}
		elseif(!$CITY->parent_id and $CITY->id) {// для районов
			$listfields = array('t1.id');
			$cls = 't1 WHERE t1.parent_id="'.$CITY->id.'" and t1.active=1';
			$CITY->data = $CITY->_query($listfields,$cls,'id');
			$clause .= ' and t1.city IN ('.implode(',',array_merge($CITY->citylist,array_keys($CITY->data))).')';
		}
		elseif(count($CITY->citylist)==1) { //для прочих городков выводим объявления только региона и самого города
			$clause .= ' and t1.city IN ('.implode(',',array_merge($CITY->citylist,array($CITY->parent_id))).')';
		}
		elseif(count($CITY->citylist)>1) {//так и не понял где и когда это выполняется
			$clause .= ' and t1.city IN ('.implode(',',$CITY->citylist).')';
		}
		$clause .= ' ORDER BY t1.datea DESC LIMIT '.$limit;
		
		$result = $this->SQL->execSQL($clause);
		$DATA = array();
		if(!$result->err) {
			$cityList = array();
			while ($r = $result->fetch_array()) {
				$cityList[$r['city']] = $r['city'];
				$DATA[$r['id']] = $r;
			}
			if(count($DATA)) {
				$result2 = $this->SQL->execSQL('SELECT t4.id,t4.name,'.($this->_CFG['site']['rf']?'t4.domen_rf':'t4.domen').' as domen FROM city t4 WHERE t4.id IN ('.implode(',',$cityList).')');
				if(!$result2->err){
					$cityList = array();
					while ($r = $result2->fetch_array()) {
						$cityList[$r['id']] = $r;
					}
					foreach($DATA as &$r) {
						if(!$r['path']) {
							$r['path'] = $this->getTranslitePatchFromText($r['text']);
							$vars = array('path'=>$r['path']);
							$this->id = $r['id'];
							parent::_save_item($vars);
						}
						$r['domen'] = 'http://'.$cityList[$r['city']]['domen'].'.'.$_SERVER['HTTP_HOST2'].'';
						$r['rname'] = $this->RUBRIC->data2[$r['rubric']]['name'];
						$r['tname'] = $cityList[$r['city']]['name'].'/ '.$this->_enum['type'][$r['type']];
						$r['rubpath'] = '/'.$this->RUBRIC->data2[$r['rubric']]['lname'];
					}
				}
			}
		}
		return array('items'=>$DATA);
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
		WHERE t1.id IN ('.implode(',',$id).') 
		GROUP BY t1.id ORDER BY t1.datea DESC';//t1.active=1 and 
		
		$this->fGetParamBoard($clause);
		///** Nomination **///
		$clause = 'SELECT * FROM boardvote WHERE owner_id IN ('.implode(',',$id).')';
		if(static_main::_prmUserCheck())
			$clause .= ' and '.$this->mf_createrid.'="'.$_SESSION['user']['id'].'"';
		else
			$clause .= ' and mf_ipcreate=INET_ATON("'.$_SERVER["REMOTE_ADDR"].'")';
		$result = $this->SQL->execSQL($clause);
		while($row = $result->fetch_array()) {
			$this->data[$row['owner_id']]['nomination'][$row['type']] = 1;
		}
		///////////
		$DATA = $this->fXMLCreate(1);

		return array('items'=>$DATA,'config'=>$this->config);
	}
	
	function fXMLCreate($statview=0) {
		$arr_stat = array();
		if(!$this->RUBRIC->data2) 
			$this->RUBRIC->simpleRubricCache();
		$xml = array();
		if(static_main::_prmUserCheck() and static_main::_prmModul($this->_cl, array(3)))
			$moder = 1;
		else
			$moder = 0;
		foreach($this->data as $k=>&$r) {
			$r['moder'] = $moder;
			$r['tname'] = $this->_enum['type'][$r['type']];
			$r['rubric_param'] = $this->RUBRIC->data2[$r['rubric']];
			$r['rubrics'] = $r['rname'] = array();
			$temp=$r['rubric'];
			while(isset($this->RUBRIC->data2[$temp])) {
				$r['rubrics'][] = array('id'=>$temp, 'name'=>$this->RUBRIC->data2[$temp]['name']); // board.inc for path
				$r['rname'][] = $this->RUBRIC->data2[$temp]['name'];
				$temp=$this->RUBRIC->data2[$temp]['parent_id'];
			}
			$r['rname'] = array_reverse($r['rname']);

			$tempval = rand(0,9);
			$r['phone'] = str_replace(array('-','8',$tempval),array('<span>-</span><span class="sdsd">'.$tempval.$r['type'].'</span>', '<span style="display:none;">'.$tempval.'</span><span>8</span>', '<span style="display:none;">'.$tempval.'</span><span class="sdsb">'.$tempval.'</span>'),$r['phone']);
			//$r['datea'] = date('Y-m-d',$r['datea']);
			$ik = '';
			$r['img'] = array();
			while(isset($r['img_board'.$ik])) {
				if($r['img_board'.$ik]!='' and $file=$this->_get_file($r['id'],'img_board'.$ik,$r['img_board'.$ik]))
					$r['img'][] = array('s'=>$this->_get_file($r['id'],'img_board'.$ik,$r['img_board'.$ik],1), 'f'=>$file);
				if(!$ik) $ik=1;
				$ik++;
			}
			$r['paramdata'] = array();
			if($r['regname'] and $r['regname']!='')
				$r['paramdata']['Город'] = array('domen'=>$r['domen'],'edi'=>'','value'=>$r['regname'].', '.$r['cityname']);
			else
				$r['paramdata']['Регион'] = array('domen'=>$r['domen'],'edi'=>'','value'=>$r['cityname']);
			if(count($r['param']))
				foreach($r['param'] as $pk=>$pr){
					if($r['name'.$pr[2]]) {
						//if($pr[2]<10)
						//	$r['name'.$pr[2]] = $this->_CFG['enum']['yesno2'][$r['name'.$pr[2]]];
						$r['paramdata'][$pr[1]] = array('id'=>$pr[0],'edi'=>$pr[4],'value'=>$r['name'.$pr[2]]);
					}
				}
			/**Номинации*/
			/*$i = 1;
			while(isset($this->config['nomination'.$i])) {
				if($this->config['nomination'.$i]!='') {
					$xml .= '<nomination value="'.$r['nomination'.$i].'" sel="'.(isset($r['nomination'][$i])?1:0).'" type="'.$i.'">'.$this->config['nomination'.$i].'</nomination>';
				}
				$i++;
			}*/
			$r['paramdata']['Цена'] = array('id'=>'','edi'=>'','value'=>'');
			if($r['rubric_param']['zerocost'])
				$r['paramdata']['Цена']['value'] = $r['rubric_param']['zerocost'];
			elseif($r['cost'])
				$r['paramdata']['Цена']['value'] = number_format($r['cost'], 0, ',', ' ').' руб.';
			else
				$r['paramdata']['Цена']['value'] = 'договорная';

			if($r['mapx']!=0) {
				$r['paramdata']['Отметка на карте'] = array('id'=>'','edi'=>'','value'=>'<span onclick="boardOnMap(1)" style="font-size: 14px;" class="jshref">Посмотреть</span><input type="hidden" id="mapx" value="'.$r['mapx'].'" name="mapx"><input type="hidden" id="mapy" value="'.$r['mapy'].'" name="mapy">');
			}
			if($this->_CFG['robot']=='' and !isset($_COOKIE['statview_'.$r['id']]) and $statview){
				$arr_stat[]=$r['id'];
				_setcookie('statview_'.$r['id'], 1, (time()+3600*24));
			}
		}

		if(count($arr_stat) and $statview){
			//statview
			$this->SQL->execSQL('UPDATE '.$this->tablename.' SET statview=statview+1 WHERE id IN ('.implode(',',$arr_stat).')');
		}
		return $this->data;
	}

	public function fGetParamBoard($clause) {
/*SELECT PARAMETR*/
		$idFL = array();
		$idCITY = array();
		$PARAM = &$this->RUBRIC->childs['param'];
		$this->data=$typeclass=$pData=$idList=$idFL=array();
		$result = $this->SQL->execSQL($clause);
		if(!$result->err)
			while ($row = $result->fetch_array()) {
				if(isset($row['param']) and $row['param']!=''){
					$row['param'] = explode('|',$row['param']);
					foreach($row['param'] as $k=>$r) {
						$r=$row['param'][$k] = explode(':',$r);// параметры поля
						$r[2] = (int)$r[2];
						if($r[2]<10)
							$row['name'.$r[2]] = $this->_CFG['enum']['yesno2'][$r[2]];
						elseif($r[2]>=50 and $r[2]<60) {
							//записываем массив для выборки из списка
							$idFL[(int)$row['name'.$r[2]]][] = array($row['id'],'name'.$r[2]);
							$row['name'.$r[2]] = '';// чтобы не выводить отключенные, см ниже
						}
						elseif($r[2]>=70 and $r[2]<80){
							$row['name'.$r[2]] = static_main::redirectLink($row['name'.$r[2]],false);
						}							
					}
				}
				$this->data[$row['id']] = $row;
				$idCITY[$row['city']][] = $row['id'];
			}
		else return 0;

		if(count($idFL)) {
			$clause = 'SELECT id,name FROM formlistitems
			WHERE id IN ('.implode(',',array_keys($idFL)).')';
			$result = $this->SQL->execSQL($clause);
			if(!$result->err)
				while ($row = $result->fetch_array()){
					foreach($idFL[$row['id']] as $r)
						$this->data[$r[0]][$r[1]]=$row['name'];
				}
			else return 0;
		}
		if(count($idCITY)) {
			_new_class('city',$CITY);
			$clause = 'SELECT t1.id,t1.name,t2.id as regid,t2.name as regname, '.($this->_CFG['site']['rf']?'t1.domen_rf':'t1.domen').' as domen FROM '.$CITY->tablename.' t1 
			left JOIN '.$CITY->tablename.' t2 ON t1.parent_id=t2.id
			WHERE t1.id IN ('.implode(',',array_keys($idCITY)).')';
			$result = $this->SQL->execSQL($clause);
			if(!$result->err)
				while ($row = $result->fetch_array()){
					foreach($idCITY[$row['id']] as $r){
						$this->data[$r]['cityname']=$row['name'];
						$this->data[$r]['regname']=$row['regname'];
						$this->data[$r]['domen']=$row['domen'];
					}
				}
		}
		return 0;
	}

	function boardFindForm($rid,$flag=1) {
		//$this->RUBRIC->data - кэш рубрик
		//$this->RUBRIC->data2 - кэш рубрик
		//if $flag==0 то это "утановка параметров при подписке"
//$newtime = getmicrotime();

		global $_tpl,$PGLIST;
		$filter = $_REQUEST;
		$xml='';
		$datalist = array();

		$this->filter_form=array();

		/*_new_class('city',$CITY);
		if(!$CITY->id){
			$this->filter_form['city']= array('type'=>'list','listname'=>array('class'=>'city'),'caption'=>'Город');
			if(isset($filter['city'])) $this->filter_form['city']['value']=$filter['city'];
		}*/

		if($flag) {
			$datalist[$rid] =$this->RUBRIC->data2[$rid]['name'];
			if(isset($this->RUBRIC->data[$rid])) {
				//$this->_enum['rubrics'][$this->RUBRIC->data2[$rid]['parent_id']][$rid] = $this->RUBRIC->data2[$rid]['name'];
				//$this->_enum['rubrics'][$rid] =$this->RUBRIC->data[$rid];
				foreach($this->RUBRIC->data[$rid] as $k=>$r){
					if(isset($this->RUBRIC->data[$k])){
						$datalist = $this->RUBRIC->data[$k]+$datalist;
						$this->_enum['rubrics'][$k]= array('#name#'=>$r,'#href#'=>$this->RUBRIC->data2[$k]['lname'].'/'.$PGLIST->id.'.html');	
						foreach($this->RUBRIC->data[$k] as $rk=>$rr)
							$this->_enum['rubrics'][$k]['#item#'][$rk]= array('#name#'=>$rr,'#href#'=>$this->RUBRIC->data2[$rk]['lname'].'/'.$PGLIST->id.'.html');
					}else
						$this->_enum['rubrics'][$k]= array('#name#'=>$r,'#href#'=>$this->RUBRIC->data2[$k]['lname'].'/'.$PGLIST->id.'.html');						
				}
				$this->filter_form['rubricl']= array(
					'type'=>'linklist',
					'caption'=>'Рубрика',
					//'onchange'=>'window.location.href=window.location.href.replace(\'_'.$rid.'\',\'_\'+this.value).replace(\'='.$rid.'\', \'=\'+this.value)',
					'valuelist'=>$this->_enum['rubrics']);
				if(isset($filter['rubric']))
					$this->filter_form['rubricl']['value']=$filter['rubric'];
			}
			//$this->filter_form['city']= array('type'=>'hidden','value'=>$CITY->id);
			$this->filter_form['rubric']= array('type'=>'hidden','value'=>$rid);
		}
		else {
			$datalist[$rid] =(int)$filter['rubric'];
			$this->_enum['rubrics'] = &$this->RUBRIC->data;
			//$this->_enum['rubrics'][0] = array(0=>'---')+$this->_enum['rubrics'][0];
			$this->filter_form['rubric']= array(
				'type'=>'list',
				'listname'=>'rubrics',
				'caption'=>'Рубрика',
				'onchange'=>'JSWin({\'href\':\''.$this->_CFG['_HREF']['siteJS'].'?_view2=subscribeparam\',\'data\':{\'rubric\':this.value}})');
			if(isset($filter['rubric']))
				$this->filter_form['rubric']['value']=$filter['rubric'];
		}


		$this->filter_form['type'] = array('type' => 'checkbox', 'listname'=>'type', 'multiple'=>1, 'caption' => 'Тип объявления');
		if(isset($filter['type'])) {
			$this->filter_form['type']['value'] = $filter['type'];
		}
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
		$this->filter_form['cost']['mask']['minint']=$minmax[0];
		$this->filter_form['cost']['mask']['maxint']=$minmax[1];
		$this->filter_form['foto'] = array('type' => 'checkbox','param'=>'checkbox','caption'=>'Только с фотографией','value'=>(isset($filter['foto'])?$filter['foto']:0));
		$this->filter_form['text'] = array('type' => 'text','caption' => 'Ключевое слово','mask' =>array('max'=>128),'value'=>(isset($filter['text'])?$filter['text']:''));

		if($rid) {
			//$this->filter_form = static_main::insertInArray($this->filter_form,'rubric',$this->ParamFieldsForm($filter,$rid,0,$temcls));
			$temp = $this->ParamFieldsForm($filter,$rid,0,$temcls);
			$this->filter_form += $temp;
		}

		$this->_enum['type'] = $this->_enum['type'];

		if($flag) {
			/*$this->filter_form[$this->_cl.'_mop'] = array(
				'type' => 'list',
				'listname'=>'_MOP',
				'caption' => 'Объявлений на странице',
				'value'=>$this->messages_on_page);*/
			$this->filter_form['_*features*_']=array('name'=>'paramselect','action'=>'','method'=>'get');
			$this->filter_form['sbmt'] = array('type'=>'submit','value'=>'Поиск');
		}
		else {
			$this->filter_form['_*features*_']=array('name'=>'paramselect','action'=>'','method'=>'GET','onsubmit'=>'return getToText(this)');
			$this->filter_form['sbmt'] = array('type'=>'submit','value'=>'задать параметры');
		}
		$this->kFields2FormFields($this->filter_form);
		if($flag)
			$_tpl['onload'] .= '$(\'#form_tools_paramselect div.multiplebox input\').live(\'click\',multiCheckBox); $(\'#form_tools_paramselect input\').live(\'change\',filterChange);';
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
	function getTranslitePatchFromText($var) {
		$var = strip_tags(html_entity_decode($var));
		$var = strtr($var,
			array(
				'<br />'=>'_',' '=>'_','-'=>'_',','=>'_','.'=>'_','+'=>'_',
				'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
				'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ы'=>'i','э'=>'e',
				'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'E','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R',
				'С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ы'=>'I','Э'=>'E',
				"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh",
				"щ"=>"shch", "ю"=>"yu", "я"=>"ya",
				"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH",
				"Щ"=>"SHCH", "Ю"=>"YU", "Я"=>"YA",
				"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye"
				,"Ь"=>"","Ъ"=>"","ь"=>"","ъ"=>""
				)
		 );
		$var = preg_replace("/[^0-9A-Za-z_]+/",'',$var);
		$var = strtr($var,array('_____'=>'_','____'=>'_','___'=>'_','__'=>'_'));
		$var = mb_substr($var,0,80,'UTF-8');
		return trim($var,' _');

	}
}
