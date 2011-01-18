<?
class pg_class extends kernel_class {

	protected function _create_conf() {
		parent::_create_conf();

		$this->config['sitename'] = 'MY SITE';
		$this->config['address'] = '';
		$this->config['copyright'] = '';
		$this->config['counter'] = '';
		$this->config['keywords'] = 'Keys...';
		$this->config['description'] = 'Desc...';
		$this->config['design'] = 'default';
		$this->config['memcache'] = 0;
		$this->config['memcachezip'] = 0;
		$this->config['sitemap'] = 0;

		$this->config_form['sitename'] = array('type' => 'text', 'caption' => 'Название сайта','mask'=>array('max'=>1000));
		$this->config_form['address'] = array('type' => 'textarea', 'caption' => 'Адрес и контакты','mask'=>array('max'=>1000));
		$this->config_form['copyright'] = array('type' => 'textarea', 'caption' => 'Копирайт','mask'=>array('max'=>1000));
		$this->config_form['counter'] = array('type' => 'textarea', 'caption' => 'Счётчик','mask'=>array('max'=>1500));
		$this->config_form['keywords'] = array('type' => 'textarea', 'caption' => 'Ключевые слова по умолчанию','mask'=>array('max'=>1000));
		$this->config_form['description'] = array('type' => 'textarea', 'caption' => 'Описание страницы по умолчанию','mask'=>array('max'=>1000));
		$this->config_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизаин по умолчанию');
		$this->config_form['memcache'] = array('type' => 'int', 'caption' => 'Memcache time', 'comment'=>'0 - откл кеширование, 1> - кеширование в сек.');
		$this->config_form['memcachezip'] = array('type' => 'checkbox', 'caption' => 'Memcache сжатие');
		$this->config_form['sitemap'] = array('type' => 'checkbox', 'caption' => 'SiteMap XML' ,'comment'=>'создавать в корне сайта xml фаил карты сайта для поисковиков');
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_use_charid = true;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Страницы';
		$this->selected = array();
		$this->ver = '0.1.1';
		return true;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['keywords'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['description'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['design'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['template'] = array('type' => 'varchar', 'width'=>20, 'attr' => 'NOT NULL','default'=>'default');
		$this->fields['styles'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['script'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['ugroup'] =array('type' => 'varchar', 'width'=>254, 'attr' => 'NOT NULL', 'default' => '|0|');
		$this->fields['attr'] = array('type' => 'varchar', 'width'=>254, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['onmenu'] = array('type' => 'varchar', 'width'=>63, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['onmap'] = array('type' => 'tinyint', 'width'=>1, 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['onmapinc'] = array('type' => 'tinyint', 'width'=>1, 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['pagemap'] = array('type' => 'varchar', 'width'=>63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['onpath'] = array('type' => 'tinyint', 'width'=>1, 'attr' => 'NOT NULL', 'default' => 1);

		# fields
		$this->fields_form['id'] = array('type' => 'text', 'caption' => 'ID','mask'=>array('sort'=>1,'min'=>1));
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительская страница','mask'=>array('fview'=>1));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Name','mask'=>array('sort'=>1,'min'=>1));
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Redirect', 'mask' =>array('onetd'=>'Содержимое'));
		$this->fields_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизайн', 'mask' =>array('onetd'=>'Дизайн'));
		$this->fields_form['template'] = array('type' => 'list', 'listname'=>'templates', 'caption' => 'Шаблон', 'mask' =>array('onetd'=>'none'));
		$this->fields_form['styles'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'style', 'caption' => 'CSS', 'mask'=>array('onetd'=>'none'));
		$this->fields_form['script'] = array('type' => 'list', 'multiple'=>2, 'listname'=>'script', 'caption' => 'SCRIPT', 'mask'=>array('onetd'=>'close'));
		$this->fields_form['keywords'] = array('type' => 'text', 'caption' => 'META-keywords','mask'=>array('fview'=>1));
		$this->fields_form['description'] = array('type' => 'text', 'caption' => 'META-description','mask'=>array('fview'=>1));
		$this->fields_form['onmenu'] = array('type' => 'list', 'listname'=>'menu', 'multiple'=>2, 'caption' => 'Меню', 'mask'=>array('onetd'=>'Опции'));
		$this->fields_form['onmap'] = array('type' => 'checkbox', 'caption'=>'Карта', 'comment' => 'Отображать в карте сайта','default'=>1);
		$this->fields_form['onmapinc'] = array('type' => 'checkbox', 'caption'=>'Карта-php', 'comment' => 'Отображать карту сгенерированную php','default'=>1);
		$this->fields_form['pagemap'] = array('type' => 'list', 'listname'=>'pagemap', 'caption' => 'MAP', 'mask' =>array('fview'=>1));
		$this->fields_form['onpath'] = array('type' => 'checkbox', 'caption'=>'Путь', 'comment' => 'Отображать в хлебных крошках','default'=>1,'mask'=>array('onetd'=>'close'));
		$this->fields_form['attr'] = array('type' => 'text', 'caption' => 'Атрибуты для ссылки в меню', 'comment'=>'Например: `target="_blank" onclick=""` итп', 'mask' =>array('name'=>'all', 'fview'=>1));
		if($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list','multiple'=>2,'listname'=>'ugroup', 'caption' => 'Доступ пользователю','default'=>'0','mask'=>array('sort'=>'1'));
		$this->fields_form['ordind'] = array('type' => 'int', 'caption' => 'ORD','mask'=>array('sort'=>'1'));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');

		# list
		//$this->listform_items['id'] = 'ID';
		//'type' => 'list', 'listname'=>'design',-+-

		$this->_fields_key = array('ugroup'=>'ugroup');

		$this->def_records[] = array('id'=>'index','name'=>'Главная страница','active'=>1,'template'=>'default');
		$this->def_records[] = array('id'=>'404','parent_id'=>'index','name'=>'Страницы нету','active'=>1,'template'=>'default');
		$this->def_records[] = array('id'=>'401','parent_id'=>'index','name'=>'Недостаточно прав для доступа к странице','active'=>1,'template'=>'default');

		//if($this->_CFG['_F']['adminpage']) {
			//include_once($this->_CFG['_PATH']['extcore'].'pg.class/content.childs.php');
			$this->create_child("content");
		//}

		$this->_enum['inc'] = array(
			0=>array('path'=>$this->_CFG['_PATH']['ctext'],'name'=>'WEP - '),
			1=>array('path'=>$this->_CFG['_PATH']['extcore'],'name'=>'WEPext - '),
			2=>array('path'=>$this->_CFG['_PATH']['ptext'],'name'=>'CONF - '),
			3=>array('path'=>$this->_CFG['_PATH']['ext'],'name'=>'EXT - ')
		);
	}

	function _getlist($listname,$value=0) {
		$data = array();
		if ($listname == "ugroup") {
			$data['user'] = ' - Авторизованные -';
			$data['anonim'] = ' - Не авторизованные -';
			$data['0'] = ' - Все -';
			$result = $this->SQL->execSQL('SELECT id, name FROM `'.$this->_CFG['sql']['dbpref'].'ugroup`');
			if(!$result->err) {
				while ($row = $result->fetch_array())
					$data[$row['id']] = $row['name'];
			}
			return $data;
		}
		elseif($listname == "templates") {
			$dir = dir($this->_CFG['_PATH']['design'].$this->_CFG['wep']['design'].'/templates');
			while (false !== ($entry = $dir->read())) {
				if (strstr($entry,'.tpl')) {
					$entry = substr($entry, 0, strpos($entry, '.tpl'));
					$data[$entry] = $entry;
				}
			}
			$dir->close();
			return $data;
		}
		elseif($listname == "mdesign") {
			$data['default'] = 'default';
			$data['new'] = 'new';
			return $data;
		}
		elseif($listname == "pagemap") {
			return $this->childs['content']->getInc('.map.php');
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


	function display() {
		$this->current_path = '';
		global $_tpl,$HTML;
		$flag_content = $this->can_show();
//$this->childs['content']->getInc('.map.php');
		//PAGE****************
		if ($flag_content==1) {
			$HTML->_templates = $this->pageinfo['template'];
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
				$HTML->_templates = "default";
				$_tpl['text'] = 'У вас не достаточно прав для доступа к странице. Необходима авторизация.';
				$_tpl['title'] = "Нет доступа";
				$_tpl['keywords'] = "";
				$_tpl['description'] = "";
			}
			else
			{
				$_tpl = array('script'=>array(),'styles'=>array());
				$HTML->_templates = $this->pageinfo['template'];
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
				$HTML->_templates = "default";
				$_tpl['text'] = "Страница не найдена!";
				$_tpl['title'] = "404 Страница не найдена!";
				$_tpl['keywords'] = "";
				$_tpl['description'] = "";
			}
			else
			{
				$_tpl = array('script'=>array(),'styles'=>array());
				$HTML->_templates = $this->pageinfo['template'];
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


	}

	function can_show() {
		if(!isset($this->dataCashTree))
			$this->sqlCashPG();
		/*$row = 0;
		if(isset($this->dataCash[$this->id])) {
			if ($parent!='' and $this->dataCash[$this->id]['parent_id']!=$parent)
				$row = 0;
			else
				$row = $this->dataCash[$this->id];
		}*/
		if(isset($this->dataCash[$this->id]) and !$this->pagePrmCheck($this->dataCash[$this->id]['ugroup'])) {
			$this->pageinfo = $this->dataCash[$this->id];
			return 2;
		}
		elseif(isset($this->dataCash[$this->id]))
		{
			if ($row['href']){
				header('Location: '.$row['href']);die();}
			$this->pageinfo = $this->dataCash[$this->id];
			$this->pageinfo['keywords'] = $this->config['keywords'].', '.$this->pageinfo['keywords'];
			$this->pageinfo['description'] = $this->config['description'].', '.$this->pageinfo['description'];
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
		else
			return 0;
	}

	function get_pageinfo() {
		$this->current_path = $this->getHref($this->pageinfo['id'],$this->pageinfo);
		$parent_id = $this->pageinfo['parent_id'];
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
		$this->pageinfo['path'] = array_reverse($this->pageinfo['path']);
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
		$xml = '<path>';
		foreach($this->pageinfo['path'] as $key=>$row)
		{
			if(!is_array($row) or !isset($row['onpath']) or $row['onpath']) {
				if(is_array($row)) $name = $row['name'];
				else $name = $row;
				$xml.= '<item><href>'.$this->getHref($key,$row).'</href><name><![CDATA['.$name.']]></name></item>';
			}
		}
		$xml .= '</path>';
		return $xml;
	}


	function display_page() {
		global $SQL, $PGLIST, $HTML, $_CFG, $_tpl;
		$flagPG = 0;

		$cls = 'SELECT * FROM '.$this->_CFG['sql']['dbpref'].'pg_content WHERE active=1 and (owner_id="'.$this->id.'"';
		//if($this->id!='404') // откл повторные глобалные контенты, если это 400 и 500 страница
			$cls .= ' or (owner_id IN ("'.(implode('","',$this->selected)).'") and global=1)';
		$cls .= ' ) ORDER BY ordind';
		$resultPG = $this->SQL->execSQL($cls);
		if(!$resultPG->err)
			while ($rowPG = $resultPG->fetch_array()) {
				if($_SESSION['_showallinfo']) 
					$_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' begin-->'; // для отладчика
				$html = '';
				if($rowPG['ugroup']) {
					if(!$this->pagePrmCheck($rowPG['ugroup']))
						continue;
				}
				if ($rowPG['href']){
					header('Location: '.$rowPG['href']);die();}
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
					$text = $this->_CFG['_PATH']['path'].$this->_CFG['PATH']['content'].'pg/'.$rowPG['id'].$this->text_ext;
					if (file_exists($text)) {
						$flagPG = 1;
						$_tpl[$rowPG['marker']] .= file_get_contents($text);
					}
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
								if(dl($prefix . 'memcache.' . PHP_SHLIB_SUFFIX))
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
						$FUNCPARAM = $rowPG['funcparam'];
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
						$_tpl[$rowPG['marker']] .= $flagPG;
					$flagPG = 1;
				}
				if($_SESSION['_showallinfo'])
					$_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' end-->';
			}

		return $flagPG;
	}

	/*function getMap
			$onmenuPG=-1 - вывод по onmap
			$onmenuPG='',
			$flagPG=0, - 0 выводит всю структуру дерева , 1 только первый уровень
			$startPG=''
	*/

	function getMap($onmenuPG='',$flagPG=0,$startPG='') {
		if(!isset($this->dataCashTree))
			$this->sqlCashPG();
		$DATA_PG = array();
		if($flagPG>1) //только начальный уровень
			$tempPG = &$this->dataCashTree[$startPG];
		elseif($flagPG) //выводит все в общем массиве
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
					if(!$rowPG['onmap']) continue;
				}
				elseif($onmenuPG!='' and !isset($rowPG['onmenu'][$onmenuPG]))
					continue;

				$href = $this->_CFG['_HREF']['BH'].$this->getHref($keyPG,$rowPG);

				if (is_array($this->selected) and isset($this->selected[$keyPG]))
					$selPG = 1;
				else
					$selPG = 0;

				$DATA_PG[$keyPG] = array('name'=>$rowPG['name'], 'href'=>$href, 'attr'=>$rowPG['attr'], 'sel'=>$selPG);
				if(!$flagPG and isset($this->dataCashTree[$keyPG]))
					$DATA_PG[$keyPG]['#item#'] = $this->getMap($onmenuPG,$flagPG,$keyPG);

				if($onmenuPG==-1 and $rowPG['onmapinc']) {
					$mapPG = explode(':',$rowPG['pagemap']);
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
		if(!isset($this->dataCash)) {
			$cls = 'SELECT *';
			/*if(isset($_SESSION['user']['id']))
				$cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|user|" or ugroup LIKE "%|'.$_SESSION['user']['owner_id'].'|%"),1,0) as prm';
			else
				$cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|anonim|"),1,0) as prm'; */
			$cls .= ' FROM '.$this->tablename.' WHERE active=1';
			$result = $this->SQL->execSQL($cls.' ORDER BY ordind');
			if(!$result->err) {
				while($row = $result->fetch_array()) {
					$row['onmenu'] = array_flip(explode('|',trim($row['onmenu'],'|')));
					$this->dataCash[$row['id']] = $row;
					$this->dataCashTree[$row['parent_id']][$row['id']] = &$this->dataCash[$row['id']];
				}
			}else {
				header('Location: '.$this->_CFG['_HREF']['BH'].$this->_CFG['PATH']['wepname'].'/login.php?install');die();}
		}
		return true;
	}

	function getHref($key,$row) {
		if(is_array($row) and isset($row['href']) and $row['href']!='') {
			$href = $row['href'];
			if(strstr($href,'http://'))
				$href ='/_redirect.php?url='.$href;
		}
		else $href = $key.'.html';
		return $href;
	}

	function pagePrmCheck($ugroup='') {
		if($ugroup) {
			$ugroup = explode('|',trim($ugroup,'|'));
			$ugroup = array_flip($ugroup);
			if(!isset($ugroup[0]) and count($ugroup)) {
				if(isset($_SESSION['user']['id'])) {
					if(!isset($ugroup['user']) and !isset($ugroup[$_SESSION['user']['owner_id']])) {
						$_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' ACCESS DENIED-->';
						return false;
					}
				}
				elseif(!isset($ugroup['anonim'])) {
					$_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' ACCESS DENIED-->';
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

//////////
}

?>
