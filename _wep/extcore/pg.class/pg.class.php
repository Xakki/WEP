<?
class pg_class extends kernel_extends {

	protected function _create_conf() {
		parent::_create_conf();

		$this->config['sitename'] = 'MY SITE';
		$this->config['counter'] = '';
		$this->config['keywords'] = 'Keys...';
		$this->config['description'] = 'Desc...';
		$this->config['design'] = 'default';
		$this->config['memcache'] = 0;
		$this->config['memcachezip'] = 0;
		$this->config['sitemap'] = 0;
		$this->config['IfDontHavePage'] = '';
		$this->config['rootPage'] = '1';
		$this->config['menu'] = array(
			0 => '',
			1 => 'Меню №1',
			2 => 'Меню №2',
			3 => 'Меню №3',
			4 => 'Меню №4',
			5 => 'Меню №5',
			6 => 'Меню №6',
		);

		$this->config['marker'] = array(
			'text' => 'text',
			'left_column' => 'left_column',
			'right_column' => 'right_column',
			'head' => 'head',
			'blockadd' => 'blockadd',
			'param' => 'param',
			'path' => 'path',
			'logs' => 'logs',
			'foot' => 'foot');

                $this->config['auto_include'] = true;
                
		// TODO : Сделать форму управления массивами данных и хранить в формате json

		$this->config_form['sitename'] = array('type' => 'text', 'caption' => 'Название сайта','mask'=>array('max'=>1000));
		$this->config_form['counter'] = array('type' => 'textarea', 'caption' => 'Счётчик','mask'=>array('max'=>1500));
		$this->config_form['keywords'] = array('type' => 'textarea', 'caption' => 'Ключевые слова по умолчанию','mask'=>array('max'=>1000));
		$this->config_form['description'] = array('type' => 'textarea', 'caption' => 'Описание страницы по умолчанию','mask'=>array('max'=>1000));
		$this->config_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизаин по умолчанию');
		$this->config_form['memcache'] = array('type' => 'int', 'caption' => 'Memcache time', 'comment'=>'0 - откл кеширование, 1> - кеширование в сек.');
		$this->config_form['memcachezip'] = array('type' => 'checkbox', 'caption' => 'Memcache сжатие');
		$this->config_form['sitemap'] = array('type' => 'checkbox', 'caption' => 'SiteMap XML' ,'comment'=>'создавать в корне сайта xml файл карты сайта для поисковиков');
		$this->config_form['IfDontHavePage'] = array('type' => 'list', 'listname'=>'pagetype', 'caption' => 'Если нет страницы в базе, то вызываем обрабочик');
		$this->config_form['rootPage'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Начальная страница сайта');
		$this->config_form['menu'] = array('type' => 'textarea', 'caption' => 'Блоки меню');
		$this->config_form['marker'] = array('type' => 'textarea', 'caption' => 'Маркеры');
                $this->config_form['auto_include'] = array('type' => 'checkbox', 'caption' => 'Подключать скрипты автоиматически');
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Страницы';
		$this->selected = array();
		$this->ver = '0.2.1';
		$this->pageinfo = 
			$this->dataCash = $this->dataCashTree = $this->dataCashTreeAlias = array();
		$this->pageParam = array();
		return true;
	}

	function _create() {
		parent::_create();
		$this->index_fields['ugroup'] = 'ugroup';
		$this->unique_fields['adress'] = array('parent_id','alias');

		# fields
		$this->fields['alias'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['name_in_menu'] = array('type' => 'varchar', 'width'=>63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['keywords'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['description'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['design'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['template'] = array('type' => 'varchar', 'width'=>20, 'attr' => 'NOT NULL','default'=>'default');
		$this->fields['styles'] = array('type' => 'varchar', 'width'=> 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['script'] = array('type' => 'varchar', 'width'=> 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['ugroup'] =array('type' => 'varchar', 'width'=>255, 'attr' => 'NOT NULL', 'default' => '|0|');
		$this->fields['attr'] = array('type' => 'varchar', 'width'=>255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['onmenu'] = array('type' => 'varchar', 'width'=>63, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['onmap'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['onmapinc'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['pagemap'] = array('type' => 'varchar', 'width'=>63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['pagemenu'] = array('type' => 'varchar', 'width'=>63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['onpath'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);
		
		# list
		//$this->listform_items['id'] = 'ID';
		//'type' => 'list', 'listname'=>'design',-+-

		$this->_enum['inc'] = array(
			0=>array('path'=>$this->_CFG['_PATH']['ctext'],'name'=>'WEP - '),
			1=>array('path'=>$this->_CFG['_PATH']['extcore'],'name'=>'WEPext - '),
			2=>array('path'=>$this->_CFG['_PATH']['ptext'],'name'=>'CONF - '),
			3=>array('path'=>$this->_CFG['_PATH']['ext'],'name'=>'EXT - ')
		);
	}

	function setSystemFields() {
		$this->def_records[] = array('id'=>1, 'alias'=>'index','name'=>'Главная страница','active'=>1,'template'=>'default');
		$this->def_records[] = array('id'=>404,'parent_id'=>1, 'alias'=>'404','name'=>'Страницы нету','active'=>1,'template'=>'default');
		$this->def_records[] = array('id'=>401,'parent_id'=>1, 'alias'=>'401','name'=>'Недостаточно прав для доступа к странице','active'=>1,'template'=>'default');
		return parent::setSystemFields();
	}

	function _childs() {
		$this->create_child('content');
	}	
	public function setFieldsForm() {
		# fields
		$this->fields_form = array();
		$this->fields_form['alias'] = array('type' => 'text', 'caption' => 'Алиас', 'comment'=>'Если не указвать, то адрес будет цыфрой', 'mask'=>array());
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительская страница','mask'=>array('fview'=>1));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Name','mask'=>array('min'=>1));
		$this->fields_form['name_in_menu'] = array('type' => 'text', 'caption' => 'Название в меню', 'mask' =>array());
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Redirect', 'mask' =>array('onetd'=>'Содержимое'));
		$this->fields_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизайн', 'mask' =>array());
		$this->fields_form['template'] = array('type' => 'list', 'listname'=>'templates', 'caption' => 'Шаблон', 'mask' =>array('onetd'=>'none'));

		$this->fields_form['styles'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'style', 'caption' => 'CSS(*)', 'mask'=>array('onetd'=>'none'));
		$this->fields_form['script'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'script', 'caption' => 'SCRIPT(*)', 'mask'=>array('onetd'=>'close'));

		$this->fields_form['keywords'] = array('type' => 'text', 'caption' => 'META-keywords','mask'=>array('fview'=>1));
		$this->fields_form['description'] = array('type' => 'text', 'caption' => 'META-description','mask'=>array('fview'=>1));
		$this->fields_form['onmenu'] = array('type' => 'list', 'listname'=>'menu', 'multiple'=>2, 'caption' => 'Меню', 'mask'=>array('onetd'=>'Опции'));
		$this->fields_form['onmap'] = array('type' => 'checkbox', 'caption'=>'Карта', 'comment' => 'Отображать эту страницу на карте сайта','default'=>1,'style'=>'background-color:#B3D142;');
		//$this->fields_form['onmapinc'] = array('type' => 'checkbox', 'caption'=>'Карта-php', 'comment' => 'Отображать на карте сайта, карту сгенерированную php','default'=>1,'style'=>'background-color:e1e1e1;');
		$this->fields_form['pagemap'] = array('type' => 'list', 'listname'=>'pagemap', 'caption' => 'Карта-php', 'comment' => 'Отображать на карте сайта, карту сгенерированную php', 'mask' =>array('fview'=>1),'style'=>'background-color:#B3D142;');
		$this->fields_form['pagemenu'] = array('type' => 'list', 'listname'=>'pagemap', 'caption' => 'Меню-php', 'comment' => 'Отображать подменю, сгенерированную php', 'mask' =>array('fview'=>1),'style'=>'background-color:#B3D142;');
		$this->fields_form['onpath'] = array('type' => 'checkbox', 'caption'=>'Путь', 'comment' => 'Отображать в хлебных крошках','default'=>1,'mask'=>array('onetd'=>'close'));
		$this->fields_form['attr'] = array('type' => 'text', 'caption' => 'Атрибуты для ссылки в меню', 'comment'=>'Например: `target="_blank" onclick=""` итп', 'mask' =>array('name'=>'all', 'fview'=>1));
		if($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list','multiple'=>2,'listname'=>'ugroup', 'caption' => 'Доступ пользователю','default'=>'0','mask'=>array());
		$this->fields_form['ordind'] = array('type' => 'int', 'caption' => 'ORD','mask'=>array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
	}

	function _getlist(&$listname,$value=0) {
		$data = array();
		if ($listname == 'ugroup') {
			$data['user'] = ' - Авторизованные -';
			$data['anonim'] = ' - Не авторизованные -';
			$data['0'] = ' - Все -';
			$result = $this->SQL->execSQL('SELECT id, name FROM `'.static_main::getTableNameOfClass('ugroup').'`');
			if(!$result->err) {
				while ($row = $result->fetch_array())
					$data[$row['id']] = $row['name'];
			}
			return $data;
		}
		elseif($listname == 'templates') {
			$data[''] = ' - По умолчанию -';
			$temp = 'mdesign';
			$temp = $this->_getlist($temp);
			foreach($temp as $kt=>$rt) {
				if($kt) {
					$dir = dir($this->_CFG['_PATH']['design'].$kt.'/templates');
					while (false !== ($entry = $dir->read())) {
						if (strstr($entry,'.tpl')) {
							$entry = substr($entry, 0, strpos($entry, '.tpl'));
							if(isset($data[$entry]))
								$data[$entry] = $entry;
							else
								$data[$entry] = strtoupper($rt).' - '.$entry;
						}
					}
					$dir->close();
				}
			}
			return $data;
		}
		elseif($listname == 'pagemap') {
			return $this->childs['content']->getInc('.map.php');
		}
		elseif($listname == 'pagetype') {
			return $this->childs['content']->getInc();
		}
		elseif ($listname == 'menu') {
			return $this->config['menu'];
		}
		else return parent::_getlist($listname,$value);
	}


	/*function allChangeData($type='') {
		parent::allChangeData($type);
		if($this->config['sitemap']) {
			$xml = $this->creatSiteMaps();
			file_put_contents($this->_CFG['_PATH']['path'].'sitemap.xml',$xml);

		}
		return true;
	}*/

	function display($templ=true) {
		$this->current_path = '';
		global $_tpl,$HTML;
		$temp_tpl = $_tpl;
		$flag_content = $this->can_show();
		//PAGE****************
		if(!$HTML) {
			if($this->pageinfo['design'])
				$this->config['design'] = $this->pageinfo['design'];
			elseif(!$this->config['design'])
				$this->config['design'] = 'default';
			$HTML = new html('_design/',$this->config['design'],$templ);//отправляет header и печатает страничку
		}
		if ($flag_content==1) {
			$flag_content = $this->display_page();
			$_tpl['title'] = $this->get_caption();
			$_tpl['keywords'] = $this->pageinfo['keywords'];
			$_tpl['description'] = $this->pageinfo['description'];
		}

		if ($flag_content==2) {
			$this->id = "401";
			header("HTTP/1.0 401");
			if(!$this->can_show())
			{
				$_tpl['text'] = 'У вас не достаточно прав для доступа к странице. Необходима авторизация.';
				$_tpl['title'] = "Нет доступа";
				$_tpl['keywords'] = "";
				$_tpl['description'] = "";
			}
			else
			{
				$_tpl=$temp_tpl;
				$_tpl['title'] = $this->get_caption();
				$_tpl['keywords'] = $this->pageinfo['keywords'];
				$_tpl['description'] = $this->pageinfo['description'];
				$this->display_page();
			}
		}
		elseif(!$flag_content)
		{
			$this->id = "404";
			header("HTTP/1.0 404 Not Found");
			if (!$this->can_show())
			{
				$_tpl['text'] = "Страница не найдена!";
				$_tpl['title'] = "404 Страница не найдена!";
				$_tpl['keywords'] = "";
				$_tpl['description'] = "";
			}
			else
			{
				$_tpl=$temp_tpl;
				$_tpl['title'] = $this->get_caption();
				$_tpl['keywords'] = $this->pageinfo['keywords'];
				$_tpl['description'] = $this->pageinfo['description'];
				$this->display_page();

			}
		}
		if($this->config['sitename']) {
			if($_tpl['title']) $_tpl['title'] .= ' - ';
			$_tpl['title'] .= $this->config['sitename'];//$_SERVER['SERVER_NAME']
		}

		if(!$this->pageinfo['template']) {
			$this->pageinfo['template'] = 'default';
		}
		$HTML->_templates = $this->pageinfo['template'];
	}

	function can_show() {
		if(empty($this->dataCashTree))
			$this->sqlCashPG();
		$fid = $this->config['rootPage'];
		if(isset($_GET['page']) and is_array($_GET['page']) and count($_GET['page'])) {
			$this->pageParam = array();
			foreach($_GET['page'] as $k=>$r) {
				if(isset($this->dataCashTreeAlias[$fid][$r]) and !$this->id) {
					$fid = $this->dataCashTreeAlias[$fid][$r]['id'];
				}
				else
					$this->pageParam[] = $r;
			}
		}
		if($fid!=$this->config['rootPage'] and !$this->id)
			$this->id = $fid;

		/*$row = 0;
		if(isset($this->dataCash[$this->id])) {
			if ($parent!='' and $this->dataCash[$this->id]['parent_id']!=$parent)
				$row = 0;
			else
				$row = $this->dataCash[$this->id];
		}*/
		if($this->id and isset($this->dataCash[$this->id]) and !$this->pagePrmCheck($this->dataCash[$this->id]['ugroup'])) {
			$this->pageinfo = $this->dataCash[$this->id];
			return 2;
		}
		elseif($this->id and isset($this->dataCash[$this->id]))
		{
			$this->pageinfo = $this->dataCash[$this->id];
			if ($this->pageinfo['href']){
				header('Location: '.$this->pageinfo['href']);die();}			
			if($this->pageinfo['keywords'])
				$this->pageinfo['keywords'] = $this->config['keywords'].', '.$this->pageinfo['keywords'];
			else
				$this->pageinfo['keywords'] = $this->config['keywords'];
			if($this->pageinfo['description'])
				$this->pageinfo['description'] = $this->config['description'].' '.$this->pageinfo['description'];
			else
				$this->pageinfo['description'] = $this->config['description'];
			$this->pageinfo['script'] = explode('|',trim($this->pageinfo['script'],'|'));
			if(count($this->pageinfo['script'])) {
				$temp = $this->pageinfo['script'];$this->pageinfo['script'] = array();
				foreach($temp as $r)
					if($r)
						$this->pageinfo['script'][$r] = 1;
			}
			$this->pageinfo['styles'] = explode('|',trim($this->pageinfo['styles'],'|'));
			if(count($this->pageinfo['styles'])) {
				$temp = $this->pageinfo['styles'];$this->pageinfo['styles'] = array();
				foreach($temp as $r)
					if($r)
						$this->pageinfo['styles'][$r] = 1;
			}
			$this->get_pageinfo();//$this->pageinfo['path']
			return 1;
		}
		elseif($this->config['IfDontHavePage'] and !isset($this->IfDontHavePage)) {
			$IfDontHavePage = explode(':',$this->config['IfDontHavePage']);
			if(file_exists($this->_enum['inc'][$IfDontHavePage[0]]['path'].$IfDontHavePage[1].'.inc.php')) {
				include($this->_enum['inc'][$IfDontHavePage[0]]['path'].$IfDontHavePage[1].'.inc.php');
				$this->config['IfDontHavePage'] = '';
				$this->IfDontHavePage = true;
				return $this->can_show();
			}
		}elseif((!$this->id or $this->id != $this->config['rootPage']) and !isset($this->IfrootPage)) {
			$this->id = $this->config['rootPage'];
			$this->IfrootPage = true;
			return $this->can_show();
		}
		return 0;
	}

	function get_pageinfo() {
		$this->current_path = $this->getHref($this->pageinfo['id'],true);
		$parent_id = $this->pageinfo['parent_id'];
		$id = $this->pageinfo['id'];
		$this->pageinfo['path'] = array($this->pageinfo['id'] => $this->pageinfo);
		$this->selected[$this->pageinfo['id']] = $this->pageinfo['id'];
		
		while ($parent_id) {
			if(isset($this->dataCash[$parent_id])) {
				$id = $this->dataCash[$parent_id]['id'];
				$this->selected[$id] = $id;
				$this->pageinfo['path'][$id] = $this->dataCash[$parent_id];
				$parent_id = $this->dataCash[$parent_id]['parent_id'];
			}
		}
		$this->main_category = $id;
		$this->pageinfo['path'] = array_reverse($this->pageinfo['path'], true);
		return true;
	}


	function get_caption() {
		$path = '';
		if($this->pageinfo['path'] and is_array($this->pageinfo['path']))
			foreach($this->pageinfo['path'] as $row)
			{
				if(!is_array($row) or !isset($row['onpath']) or $row['onpath']) {
					if(is_array($row)) $name = $row['name'];
					else $name = $row;

					if($path=='') $path = $name;
					elseif($name!='') $path = $name.' - '.$path;
				}
			}
		return $path;
	}


	function get_path() {
		$data = array();
		foreach($this->pageinfo['path'] as $key=>$row)
		{
			if(!is_array($row) or !isset($row['onpath']) or $row['onpath']) {
				if(is_array($row)) $name = $row['name'];
				else $name = $row;
				$data[] = array('href'=>$this->getHref($key,true),'name'=>$name);
			}
		}
		return $data;
	}


	function display_page() {
		global $SQL, $PGLIST, $HTML, $_CFG, $_tpl;
		$flagPG = 0;
		$PGLIST = &$this;
		$SQL = &$this->SQL;
		$this->Cdata = array();

		$cls = 'SELECT * FROM '.$this->_CFG['sql']['dbpref'].'pg_content WHERE active=1 and (owner_id="'.$this->id.'"';
		//if($this->id!='404') // откл повторные глобалные контенты, если это 400 и 401 страница
			$cls .= ' or (owner_id IN ("'.(implode('","',$this->selected)).'") and global=1)';
		$cls .= ' ) ORDER BY ordind';
		$resultPG = $this->SQL->execSQL($cls);
		if(!$resultPG->err)
			while ($rowPG = $resultPG->fetch_array()) {
				$this->Cdata[$rowPG['id']] = $rowPG;
			}

			foreach($this->Cdata as &$rowPG) {
				if(!$rowPG['active']) continue;
				$Ctitle = $rowPG['name'];

				if (!isset($_tpl[$rowPG['marker']]))
				{
					$_tpl[$rowPG['marker']] = '';
				}
				$_tempMarker = '';

				$html = '';
				if($rowPG['ugroup']) {
					if(!$this->pagePrmCheck($rowPG['ugroup'])) {
						$_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' ACCESS DENIED-->';
						continue;
					}
						
				}
				if ($rowPG['href']){
					$temp = $this->_cl .'_'.preg_replace($this->_CFG['_repl']['alphaint'], '', $rowPG['href']);
					if(!isset($_COOKIE[$temp])) {
						_setcookie($temp, 1, time()+1);
						header('Location: '.$rowPG['href']);
						die();
					}else {
						trigger_error('На этой странице '.$this->id.'['.$rowPG['id'].'] обнаружена циклическая переадресация.Веб-страница привела к избыточному количеству переадресаций.', E_USER_WARNING);
					}
				}
				if($rowPG['script']) {
					$rowPG['script'] = explode('|',trim($rowPG['script'],'|'));
					if(count($rowPG['script'])) {
						foreach($rowPG['script'] as $r)
							if($r)
								$this->pageinfo['script'][$r] = 1;
					}
				}
				if($rowPG['styles']) {
					$rowPG['styles'] = explode('|',trim($rowPG['styles'],'|'));
					if(count($rowPG['styles'])) {
						foreach($rowPG['styles'] as $r)
							if($r)
								$this->pageinfo['styles'][$r] = 1;
					}
				}

				if($rowPG['pagetype']=='') {
					/*$text = $this->_CFG['_PATH']['path'].$this->_CFG['PATH']['content'].'pg/'.$rowPG['id'].$this->text_ext;
					if (file_exists($text)) {
						$flagPG = 1;
						$_tempMarker .= file_get_contents($text);
					}*/
					$_tempMarker .= $rowPG['pg'];
					$flagPG = 1;
				} else {
					$flagMC = false;
					if(!$rowPG['memcache'] and $this->config['memcache'])
						$rowPG['memcache'] = $this->config['memcache'];
					if($rowPG['memcache']) {
						$hashkeyPG = $_SERVER['HTTP_HOST'].$_SERVER['HTTP_HOST'];
						global $MEMCACHE;

						if(!$MEMCACHE) {
							$mc_load = false;
							if(!extension_loaded('memcache')) {
								$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
								if(function_exists('dl') and dl($prefix . 'memcache.' . PHP_SHLIB_SUFFIX))
									$mc_load = true;
							}else
								$mc_load = true;
							if($mc_load) {
								$MEMCACHE = new Memcache;
								$MEMCACHE->connect($_CFG['memcache']['host'],$_CFG['memcache']['port']);
								$this->config['memcachezip'] = ($this->config['memcachezip']?true:false);
							}
						}
						if($MEMCACHE)
							$flagPG = $flagMC = $MEMCACHE->get($hashkeyPG);
					}
					if(!$flagMC) {
						if($rowPG['funcparam']) $FUNCPARAM = explode('&',$rowPG['funcparam']);
						else $FUNCPARAM = array();
						$typePG = explode(':',$rowPG['pagetype']);
						if(count($typePG)==2 and file_exists($this->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php'))
							$flagPG = include($this->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php');
						elseif(file_exists($this->_CFG['_PATH']['ptext'].$rowPG['pagetype'].'.inc.php'))
							$flagPG = include($this->_CFG['_PATH']['ptext'].$rowPG['pagetype'].'.inc.php');
						elseif(file_exists($this->_CFG['_PATH']['ctext'].$rowPG['pagetype'].'.inc.php'))
							$flagPG = include($this->_CFG['_PATH']['ctext'].$rowPG['pagetype'].'.inc.php');
						else {
							trigger_error('Обрботчик страниц "'.$this->_enum['inc'][$typePG[0]]['path'].$typePG[1].'.inc.php" не найден!', E_USER_WARNING);
							continue;
						}
						if($rowPG['memcache'] and $MEMCACHE)
							$MEMCACHE->set($hashkeyPG,$flagPG , $this->config['memcachezip'], $rowPG['memcache']);
					}
					if($rowPG['memcache'] and $MEMCACHE)
						$memcache_obj->close();
					
					if(is_string($flagPG)) // если не булевое значение то выводим содержимое
						$_tempMarker .= $flagPG;
					$flagPG = 1;
				}
				//$_tpl[$rowPG['marker']] .= '<div id="pg_'.$rowPG['id'].'">'.$_tempMarker.'</div>';
				if(isset($_SESSION['_showallinfo']) && $_SESSION['_showallinfo'])
					$_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' begin-->'.$_tempMarker.'<!--content'.$rowPG['id'].' end-->';
				else
					$_tpl[$rowPG['marker']] .= $_tempMarker;
			}
		return $flagPG;
	}

	/*function getMap
			$onmenuPG=-1 - вывод по onmap
			$onmenuPG='',
			$flagPG=0, - 0 выводит всю структуру дерева , 1 только первый уровень
			$startPG=''
	*/

	function getMap($onmenuPG='',$flagPG=0,$startPG=0) {
		if(empty($this->dataCashTree))
			$this->sqlCashPG();
		$DATA_PG = array();
		if($flagPG==3)
			$startPG = $this->dataCash[$this->id]['parent_id'];
		elseif(!$startPG)
			$startPG = $this->config['rootPage'];

		if($flagPG>1) //только начальный уровень
			$tempPG = &$this->dataCashTree[$startPG];
		elseif($flagPG==1) //выводит все в общем массиве
			$tempPG = &$this->dataCash;
		else //выводит всё в виде структуры дерева
			$tempPG = &$this->dataCashTree[$startPG];
		if(count($tempPG))
			foreach ($tempPG as $keyPG=>$rowPG)
			{
				if($rowPG['ugroup']) {
					if(!$this->pagePrmCheck($rowPG['ugroup']))
						continue;
				}
				if($onmenuPG==-1) {
					if(!$rowPG['onmap']) {continue;}
				}
				elseif($onmenuPG!='' and !isset($rowPG['onmenu'][$onmenuPG])) {
					continue;
				}

				$href = $this->_CFG['_HREF']['BH'].$this->getHref($keyPG,true);

				if($this->id==$keyPG)
					$selPG = 2;
				elseif (is_array($this->selected) and isset($this->selected[$keyPG]))
					$selPG = 1;
				else
					$selPG = 0;

				if ($rowPG['name_in_menu'] == '') {
					$name = $rowPG['name'];
				} else {
					$name = $rowPG['name_in_menu'];
				}

				$DATA_PG[$keyPG] = array('name'=>$name, 'href'=>$href, 'attr'=>$rowPG['attr'], 'sel'=>$selPG, 'pgid'=>$keyPG);
				if($flagPG<1 and isset($this->dataCashTree[$keyPG])) {
					$DATA_PG[$keyPG]['#item#'] = $this->getMap($onmenuPG,$flagPG,$keyPG);
				}

				if($onmenuPG==-1 and $rowPG['pagemap']) {
					$mapPG = explode(':',$rowPG['pagemap']);
					if(count($mapPG)==2 and file_exists($this->_enum['inc'][$mapPG[0]]['path'].$mapPG[1].'.map.php')) {
						$tempinc = include($this->_enum['inc'][$mapPG[0]]['path'].$mapPG[1].'.map.php');
						if(isset($DATA_PG[$keyPG]['#item#']) and is_array($DATA_PG[$keyPG]['#item#']))
							$DATA_PG[$keyPG]['#item#'] += $tempinc;
						else
							$DATA_PG[$keyPG]['#item#'] = $tempinc;
							
					}
				}
				elseif($rowPG['pagemenu']) {
					$mapPG = explode(':',$rowPG['pagemenu']);
					if(count($mapPG)==2 and file_exists($this->_enum['inc'][$mapPG[0]]['path'].$mapPG[1].'.map.php')) {
						$tempinc = include($this->_enum['inc'][$mapPG[0]]['path'].$mapPG[1].'.map.php');
						if(isset($DATA_PG[$keyPG]['#item#']) and is_array($DATA_PG[$keyPG]['items']))
							$DATA_PG[$keyPG]['#item#'] += $tempinc;
						else
							$DATA_PG[$keyPG]['#item#'] = $tempinc;
							
					}
				}
			}
		return $DATA_PG;
	}

	function sqlCashPG() {
		if(empty($this->dataCash)) {
			$cls = 'SELECT *';
			/*if(isset($_SESSION['user']['id']))
				$cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|user|" or ugroup LIKE "%|'.$_SESSION['user']['owner_id'].'|%"),1,0) as prm';
			else
				$cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|anonim|"),1,0) as prm'; */
			$cls .= ' FROM '.$this->tablename.' WHERE active=1';
			$result = $this->SQL->execSQL($cls.' ORDER BY ordind');

			if(!$result->err) {
				while($row = $result->fetch_array()) {
					if(!isset($row['alias'])) {$this->updateModul();return $this->sqlCashPG();}
					$row['onmenu'] = array_flip(explode('|',trim($row['onmenu'],'|')));
					$this->dataCash[$row['id']] = $row;
					$this->dataCashTree[$row['parent_id']][$row['id']] = &$this->dataCash[$row['id']];
					$this->dataCashTreeAlias[$row['parent_id']][$row['alias']] = &$this->dataCash[$row['id']];
				}
			}else {
				header('Location: '.$this->_CFG['_HREF']['BH'].$this->_CFG['PATH']['wepname'].'/install.php');die();}
		}
		return true;
	}

	function updateModul() {
		$this->SQL->execSQL('alter table '.$this->tablename.' 
		change `id` `alias` varchar(63) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
		drop primary key,
		add column `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT after `alias`,
		add primary key(`id`)');
		$this->SQL->execSQL('UPDATE '.$this->childs['content']->tablename.' SET owner_id=(SELECT id FROM '.$this->tablename.' WHERE alias=owner_id)');
		$this->SQL->execSQL('alter table '.$this->childs['content']->tablename.' change `owner_id` `owner_id` int(11) NOT NULL');
		$this->SQL->execSQL('CREATE TABLE '.$this->tablename.'_copy AS(SELECT * FROM '.$this->tablename.')');
		$this->SQL->execSQL('UPDATE '.$this->tablename.' t1 SET t1.parent_id=(SELECT t2.id FROM '.$this->tablename.'_copy t2 WHERE t2.alias=t1.parent_id)');
		$this->SQL->execSQL('drop table '.$this->tablename.'_copy');
		$this->SQL->execSQL('alter table '.$this->tablename.' change `parent_id` `parent_id` int(11) DEFAULT "0" NOT NULL');
	}

	function getHref($id=false,$html=false) {
		if(!$id) $id = $this->id;
		if($html and isset($this->dataCash[$id]['href']) and $this->dataCash[$id]['href']!='') {
			$href = $this->dataCash[$id]['href'];
			if(strstr($href,'http://'))
				$href ='_redirect.php?url='.base64_encode($href);
		}
		else {
			$href = $id;
			if(isset($this->dataCash[$id])) {
				if($this->dataCash[$id]['alias'])
					$href = $this->dataCash[$id]['alias'];
				$pid = $this->dataCash[$id]['parent_id'];
				while($pid and $pid!=$this->config['rootPage']) {
					if($this->dataCash[$pid]['alias'])
						$href = $this->dataCash[$pid]['alias'].'/'.$href;
					else
						$href = $pid.'/'.$href;
					$pid = $this->dataCash[$pid]['parent_id'];
				}
			}
			if($html) $href .= '.html';
		}
		return $href;
	}

	function pagePrmCheck($ugroup='') {
		global $_tpl;

		if($ugroup) {
			$ugroup = explode('|',trim($ugroup,'|'));
			$ugroup = array_flip($ugroup);
			if(!isset($ugroup[0]) and count($ugroup)) {
				if(isset($_SESSION['user']['id'])) {
					if(!isset($ugroup['user']) and !isset($ugroup[$_SESSION['user']['owner_id']])) {						
						return false;
					}
				}
				elseif(!isset($ugroup['anonim'])) {
					return false;
				}
			}
		}
		return true;
	}

	function creatSiteMaps() {
		$data = $this->getMap(-1);
		$xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$xml .= $this->reverseDataMap($data);
		$xml .= '</urlset>';
		return $xml;
	}

	function reverseDataMap(&$data) {
		foreach($data as $k=>$r) {
			if(isset($r['href']) and $r['href'])
				$xml .= '
	<url>
		<loc>'.$r['href'].'</loc>
		<changefreq>daily</changefreq>
	</url>';
			if(isset($r['#item#']) and count($r['#item#']))
				$xml .= $this->reverseDataMap($r['#item#']);
		}
		return $xml;
	}

	function FFTemplate(&$tpl,$dir) {
		if(strpos($tpl,'#ext#')!==false) {
			$tpl = str_replace('#ext#','',$tpl);
			$tpl2 = array($tpl, $dir . '/templates/');
		}else 
			$tpl2 = $tpl;
		return $tpl2;
	}

//////////
}
