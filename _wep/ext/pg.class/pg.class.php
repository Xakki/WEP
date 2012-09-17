<?php
/**
 * Модуль страниц для frontend
 * @author Xakki
 * @version 0.5.7 
 */
class pg_class extends kernel_extends {

	protected function _create_conf() {
		parent::_create_conf();

		$this->config['sitename'] = 'MY SITE';
		$this->config['keywords'] = 'Keys...';
		$this->config['description'] = 'Desc...';
		$this->config['design'] = 'default';
		$this->config['memcache'] = 0;
		$this->config['memcachezip'] = 0;
		$this->config['sitemap'] = 1;
		$this->config['IfDontHavePage'] = '';
		$this->config['rootPage'] =  array (0 => 1);
		$this->config['menu'] = array(
			0 => '',
			1 => 'Меню TOP',
			2 => 'Меню BOTTOM',
			3 => 'Меню LEFT',
			4 => 'Меню №4',
			5 => 'Меню №5',
			6 => 'Меню №6',
		);

		$this->config['marker'] = array(
			'text' => 'Главный текст',
			'head' => 'Заголовок',
			'lblock' => 'Левая колонка',
			'rblock' => 'Правая колонка',
			'path' => 'Хлебные крошки',
			'logs' => 'Логи',
			'foot' => 'Подвал');
		$this->config['auto_include'] = true;
		$this->config['auto_auth'] = true;
		$this->config['rf_on'] = false;
		$this->config['newadmin_on'] = true;

		// TODO : Сделать форму управления массивами данных и хранить в формате json

		$this->config_form['sitename'] = array('type' => 'text', 'caption' => 'Название сайта', 'mask' => array('max' => 1000));
		$this->config_form['keywords'] = array('type' => 'textarea', 'caption' => 'Ключевые слова по умолчанию', 'mask' => array('max' => 1000));
		$this->config_form['description'] = array('type' => 'textarea', 'caption' => 'Описание страницы по умолчанию', 'mask' => array('max' => 1000));
		$this->config_form['design'] = array('type' => 'list', 'listname' => 'mdesign', 'caption' => 'Дизаин по умолчанию');
		$this->config_form['memcache'] = array('type' => 'int', 'caption' => 'Memcache time по умолчанию', 'comment' => '-1 - отключить полностью, 0 - кеширование определяется в контенте, 1> - кеширование в сек. для всех по умолчанию');
		$this->config_form['memcachezip'] = array('type' => 'checkbox', 'caption' => 'Memcache сжатие кеша');
		$this->config_form['sitemap'] = array('type' => 'checkbox', 'caption' => 'SiteMap XML', 'comment' => 'создавать в корне сайта xml файл карты сайта для поисковиков');
		$this->config_form['IfDontHavePage'] = array('type' => 'list', 'listname' => 'list', 'caption' => 'Если нету совпадений , то по умолчанию будет следующая страница');
		$this->config_form['rootPage'] = array('type' => 'list', 'keytype' => 'text', 'listname' => array('class' => 'pg', 'where' => 'parent_id=0'), 'multiple' => 3, 'caption' => 'Мульти-домен', 'comment' => 'Укажите страницу для каждого домена, по умолчанию для ненайденного домена будет загружаться первая позиция', 'mask' => array('maxarr' => 20));
		$this->config_form['menu'] = array('type' => 'text', 'keytype' => 'int', 'multiple' => 3, 'caption' => 'Блоки меню', 'mask' => array('maxarr' => 30));
		$this->config_form['marker'] = array('type' => 'text', 'keytype' => 'text', 'multiple' => 3, 'caption' => 'Маркеры', 'mask' => array('maxarr' => 50));
		$this->config_form['auto_include'] = array('type' => 'checkbox', 'caption' => 'Подключать скрипты автоиматически');
		$this->config_form['auto_auth'] = array('type' => 'checkbox', 'caption' => 'Автоматическая авторизация');
		$this->config_form['rf_on'] = array('type' => 'checkbox', 'caption' => 'Для руского домена использовать НАЗВАНИЕ страницы');
		$this->config_form['newadmin_on'] = array('type' => 'checkbox', 'caption' => 'Включить "Новую админку"', 'comment'=>'В последствии к каждому контенту будет создаваться div обертка');
	}

	function _set_features() {
		if (!parent::_set_features())
			return false;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		//$this->unique_fields['pages'] = array('alias','parent_id');
		$this->caption = 'Страницы';
		$this->selected = array();
		$this->messages_on_page = 50;
		$this->ver = '0.5.8';
		$this->pageinfo =
				$this->dataCash = $this->dataCashTree = $this->dataCashTreeAlias = array();
		$this->pageParam = $this->pageParamId = array();
		$this->default_access = '|1|'; // По умолчанию ставим доступ на чтений всем пользователям
		$this->MEMCACHE = null;
		$this->rootPage = null;
		$this->_AllowAjaxFn['AjaxForm'] = true;
		$this->formFlag = null; // Для Аякс формы, 
		$this->current_path = '';
		$this->ajaxRequest = false; // ставится метка об аякс запросе
		$this->access_flag = false; // Если значение выставить true, то каждое "Содержимое" будет проверяться на допуск к отображению на "Спец страницах" (отмеченные галочкой не выполнятся)
		return true;
	}

	function _create() {
		parent::_create();
		$this->index_fields['ugroup'] = 'ugroup';
		//$this->unique_fields['address'] = array('parent_id','alias');
		# fields
		$this->fields['alias'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['name_in_menu'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['menuajax'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['design'] = array('type' => 'varchar', 'width' => 20, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['template'] = array('type' => 'varchar', 'width' => 20, 'attr' => 'NOT NULL', 'default' => 'default');
		$this->fields['ugroup'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '|0|');
		$this->fields['attr'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['aparam'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['onmenu'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['onmap'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['onmapinc'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);
		$this->fields['pagemap'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['pagemenu'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['onpath'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);

		# list
		//$this->listform_items['id'] = 'ID';
		//'type' => 'list', 'listname'=>'design',-+-

		$this->_enum['inc'] = array(
			0 => array('path' => $this->_CFG['_PATH']['wep_inc'], 'name' => 'Служебные'),
			1 => array('path' => $this->_CFG['_PATH']['wep_ext'], 'name' => 'Модульные'),
			2 => array('path' => $this->_CFG['_PATH']['inc'], 'name' => 'Общие'),
			3 => array('path' => $this->_CFG['_PATH']['ext'], 'name' => 'Модульные')
		);
	}

	function _childs() {
		$this->create_child('content');
	}

	public function setFieldsForm($form = 0) {
		parent::setFieldsForm($form);
		# fields
		$this->fields_form = array();
//if(!$this->parent_id)
		$this->fields_form['alias'] = array('type' => 'text', 'caption' => 'Алиас', 'comment' => 'Если не указвать, то адрес будет цыфрой', 'mask' => array());
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname' => 'parentlist', 'caption' => 'Родительская страница', 'mask' => array('fview' => 1));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' => array('min' => 1, 'onetd' => 'Название'));
		$this->fields_form['name_in_menu'] = array('type' => 'text', 'caption' => 'Название в меню', 'mask' => array('onetd' => 'close'));

		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Ссылка', 'comment'=>'Перенаправление по другому адресу (/ или http://)', 'mask' => array('onetd' => 'Содержимое'));

		$this->fields_form['design'] = array('type' => 'list', 'listname' => 'mdesign', 'caption' => 'Дизайн', 'mask' => array());
		$this->fields_form['template'] = array('type' => 'list', 'listname' => 'templates', 'caption' => 'Шаблон', 'mask' => array('onetd' => 'close'));

		$this->fields_form['onmenu'] = array('type' => 'list', 'listname' => 'menu', 'multiple' => 2, 'caption' => 'Меню', 'mask' => array('onetd' => 'Опции'));
		$this->fields_form['menuajax'] = array('type' => 'checkbox', 'caption' => 'AJAX', 'comment' => 'Загружать контент аяксом при клике в меню', 'default' => 0, 'style' => 'background-color:#33D142;');

		$this->fields_form['onmap'] = array('type' => 'checkbox', 'caption' => 'Карта', 'comment' => 'Отображать эту страницу на карте сайта', 'default' => 1, 'style' => 'background-color:#B3D142;');
		$this->fields_form['pagemap'] = array('type' => 'list', 'listname' => 'pagemap', 'caption' => 'Карта-php', 'comment' => 'Отображать на карте сайта, карту сгенерированную php', 'mask' => array('fview' => 1), 'style' => 'background-color:#B3D142;');
		$this->fields_form['pagemenu'] = array('type' => 'list', 'listname' => 'pagemap', 'caption' => 'Меню-php', 'comment' => 'Отображать подменю, сгенерированную php', 'mask' => array('fview' => 1), 'style' => 'background-color:#B3D142;');
		$this->fields_form['onpath'] = array('type' => 'checkbox', 'caption' => 'Путь', 'comment' => 'Отображать в хлебных крошках', 'default' => 1, 'mask' => array('onetd' => 'close'));

		$this->fields_form['attr'] = array('type' => 'text', 'caption' => 'Атрибуты для ссылки в меню', 'comment' => 'Например: `target="_blank" onclick=""` итп', 'mask' => array('name' => 'all', 'fview' => 1));
		$this->fields_form['aparam'] = array('type' => 'text', 'caption' => 'Параметры для ссылки в меню', 'comment' => 'Например если прописать: var=1&var2=3 ,дополняет путь в меню alias.html?var=1&var2=3', 'mask' => array('name' => 'all', 'fview' => 1));

		if ($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'ugroup', 'caption' => 'Доступ пользователю', 'default' => '0', 'mask' => array());
		$this->fields_form['ordind'] = array('type' => 'number', 'caption' => 'ORD', 'mask' => array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');

		
		$this->formSort = array(
			'Основное'=>array('alias','name','href','template','onmenu','ugroup','active'),
			'Дополнительно'=>array('parent_id','name_in_menu','menuajax','design','onmap','pagemap','pagemenu','onpath','attr','aparam','ordind'),
		);

	}

	function _getlist($listname, $value = 0) {
		$data = array();
		if ($listname == 'ugroup') {
			$data['user'] = ' - Авторизованные -';
			$data['anonim'] = ' - Не авторизованные -';
			$data['0'] = ' - Все -';
			$result = $this->SQL->execSQL('SELECT id, name FROM `' . static_main::getTableNameOfClass('ugroup') . '`');
			if (!$result->err) {
				while ($row = $result->fetch())
					$data[$row['id']] = $row['name'];
			}
			return $data;
		} 
		elseif ($listname == 'templates') {
			$data[''] = ' - По умолчанию -';
			$temp = 'mdesign';
			$temp = $this->_getlist($temp);
			foreach ($temp as $kt => $rt) {
				if ($kt) {
					$dir = dir($this->_CFG['_PATH']['design'] . $kt . '/templates');
					while (false !== ($entry = $dir->read())) {
						if (strstr($entry, '.tpl')) {
							$entry = substr($entry, 0, strpos($entry, '.tpl'));
							if (isset($data[$entry]))
								$data[$entry] = $entry;
							else
								$data[$entry] = strtoupper($rt) . ' - ' . $entry;
						}
					}
					$dir->close();
				}
			}
			return $data;
		}
		elseif ($listname == 'pagemap') {
			return $this->childs['content']->getInc('.map.php', ' --- ');
		} 
		elseif ($listname == 'realurl') {
			return $this->childs['content']->getInc('.realurl.php', ' --- ');
		} 
		elseif ($listname == 'pagetype') {
			return $this->childs['content']->getInc();
		} 
		elseif ($listname == 'menu') {
			return $this->config['menu'];
		} 
		elseif ($listname == 'content') {
			return $this->childs['content']->getContentList();
		}
		else
			return parent::_getlist($listname, $value);
	}

	function toolsConfigmodul() {
		$data = parent::toolsConfigmodul();
		if (!$this->incMemcach())
			$data['messages'][] = array('error', 'Модуль PHP Memcach отсутствует либо не верная конфигурия подключения');
		return $data;
	}

	/**
	 * Включение MEMCACHE 
	 * @return bool - true если успешно
	 */
	function incMemcach() {
		if (!$this->MEMCACHE) {
			$mc_load = false;
			if (!extension_loaded('memcache')) {
				$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
				if (function_exists('dl') and dl($prefix . 'memcache.' . PHP_SHLIB_SUFFIX))
					$mc_load = true;
			}else
				$mc_load = true;
			if ($mc_load) {
				$this->MEMCACHE = new Memcache;
				$this->MEMCACHE->addServer($this->_CFG['memcache']['host'], $this->_CFG['memcache']['port']);
				//$memstatus = $this->MEMCACHE->getServerStatus($this->_CFG['memcache']['host'],$this->_CFG['memcache']['port']);
				$memstatus = @$this->MEMCACHE->connect($this->_CFG['memcache']['host'], $this->_CFG['memcache']['port']);
				if ($memstatus) {
					$this->config['memcachezip'] = ($this->config['memcachezip'] ? MEMCACHE_COMPRESSED : 0);
				} else {
					$this->config['memcache'] = -1;
					$this->MEMCACHE = false;
				}
			}
		} else
			$mc_load = true;
		return $mc_load;
	}

	/* function allChangeData($type='') {
	  parent::allChangeData($type);
	  if($this->config['sitemap']) {
	  $xml = $this->creatSiteMaps();
	  file_put_contents($this->_CFG['_PATH']['path'].'sitemap.xml',$xml);

	  }
	  return true;
	  } */

	/**
	 * frontend display controller
	 * @return bool
	 */
	function initHTML($templ) {
		global $HTML;
		if (!$HTML) {
			// TODO : смена дизайна и получение инфы о странице в аяксе
			/*if (isset($_GET['_design']))
				$this->config['design'] = $_GET['_design'];
			else*/
			if ($this->pageinfo['design'])
				$this->config['design'] = $this->pageinfo['design'];
			elseif (!$this->config['design'])
				$this->config['design'] = 'default';
			$HTML = new html('_design/', $this->config['design'], $templ); //отправляет header и печатает страничку
		}
		return true;
	}
	
	function display($templ = true) {
		global $_tpl, $HTML;

		$flag_content = $this->can_show();
		$this->initHTML($templ);

		foreach ($this->config['marker'] as $km => $rm)
			$_tpl[$km] = '';
		$_tpl['onload'] = '';
		$_tpl['title'] = '';
		$_tpl['name'] = '';
		$_tpl['keywords'] = $this->config['keywords'];
		$_tpl['description'] = $this->config['description'];
		$temp_tpl = $_tpl;

		if(version_compare(phpversion(),'5.3.0','>'))
			$pageParamEncode = json_encode($this->pageParam, JSON_HEX_TAG);
		else
			$pageParamEncode = json_encode($this->pageParam);

		$getEncode = $_GET;unset($getEncode['pageParam']);
		if(version_compare(phpversion(),'5.3.0','>')) 
			$getEncode = json_encode($getEncode, JSON_HEX_TAG);
		else
			$getEncode = json_encode($getEncode);

		
		if ($flag_content == 1) {
			$flag_content = $this->display_page($this->id,true);
			$_tpl['title'] = $this->get_caption();
		}

		if (!$flag_content)
			$flag_content = 404;
		if ($flag_content >= 100) {
			if ($flag_content == 401 and static_main::_prmUserCheck())
				$flag_content = 403;

			$this->displayHttpCode($flag_content,true);
		}

		if ($this->config['sitename']) {
			$_tpl['name'] = $this->config['sitename'];
			if ($_tpl['title'])
				$_tpl['title'] .= ' - ';
			$_tpl['title'] .= $this->config['sitename']; //$_SERVER['SERVER_NAME']
		} else {
			reset($this->pageinfo['path']);
			$temp = current($this->pageinfo['path']);
			$_tpl['name'] = $temp['name'];
		}

		if (!$this->pageinfo['template']) {
			$this->pageinfo['template'] = 'default';
		}
		$HTML->_templates = $this->pageinfo['template'];
		if($this->_CFG['returnFormat'] == 'html') {
			$_tpl['script'][] = 'if(typeof wep !== "undefined") {
	wep.pgId = ' . $this->id . ';
	wep.pgParam =' . $pageParamEncode . ';
	wep.pgGet =' . $getEncode . ';
	wep.siteJS = "' . $this->_CFG['_HREF']['siteJS'] . '";
	wep.BH = "' . $this->_CFG['_HREF']['BH'] . '";
	wep.DOMAIN = "' . $_SERVER['HTTP_HOST2'] . '";
}

var tmp = wep.getCookie(\'wepjs123456\');
if(tmp==false){
	wep.setCookie(\'wepjs123456\',document.referrer);
}
';
		}
		return true;
	}

	function can_show() {
		if (empty($this->dataCashTree))
			$this->sqlCashPG();
		$fid = $this->rootPage;
		if (isset($_REQUEST['pageParam']) and is_array($_REQUEST['pageParam']) and count($_REQUEST['pageParam'])) {
			$this->pageParam = $this->pageParamId = array();
			foreach ($_REQUEST['pageParam'] as $k => $r) {
				// Ищем совпадение по алиасу
				if (isset($this->dataCashTreeAlias[$fid][$r]) and !$this->id) {
					$fid = $this->dataCashTreeAlias[$fid][$r]['id'];
					$this->pageParamId[$k] = $fid;
				}
				// Проверяем если не указан алиас но есть совпадение по ID
				elseif (isset($this->dataCashTree[$fid][$r]) and !$this->dataCashTree[$fid][$r]['alias'] and !$this->id) {
					$fid = $this->dataCashTree[$fid][$r]['id'];
					$this->pageParamId[$k] = $fid;
				} 
				elseif ($this->dataCash[$fid]['alias'] == $r) {
					$this->IfrootPage = true;
				} 
				elseif( count($_REQUEST['pageParam'])==1 and isset($this->dataCash[(int)$r]) ) {
					$href = $this->getHref((int) $r, true);
					if($_SERVER['HTTP_REQUEST']!=$href)
						static_main::redirect($href);
				} 
				else
					$this->pageParam[] = $r;
			}
		}
		if ($fid != $this->rootPage and !$this->id)
			$this->id = $fid;
		elseif (is_null($this->id) and isset($this->IfrootPage)) {
			$this->id = $fid;
		}

		/* $row = 0;
		  if(isset($this->dataCash[$this->id])) {
		  if ($parent!='' and $this->dataCash[$this->id]['parent_id']!=$parent)
		  $row = 0;
		  else
		  $row = $this->dataCash[$this->id];
		  } */
		if ($this->id and isset($this->dataCash[$this->id]) and !$this->pagePrmCheck($this->dataCash[$this->id]['ugroup'])) {
			$this->pageinfo = $this->dataCash[$this->id];
			$this->get_pageinfo();
			return 401;
		} elseif ($this->id and isset($this->dataCash[$this->id])) {
			$this->pageinfo = $this->dataCash[$this->id];
			if ($this->pageinfo['href'])
				static_main::redirect($this->pageinfo['href']);
			$this->get_pageinfo(); //$this->pageinfo['path']
			return 1;
		}
		elseif ($this->config['IfDontHavePage'] and !isset($this->IfDontHavePage)) {
			/*if(is_array($this->config['IfDontHavePage'])) {
				$this->display_inc(implode(',',$this->config['IfDontHavePage']));
			}
			*/
			$this->id = $this->config['IfDontHavePage'];
			$this->pageinfo = $this->dataCash[$this->id];
			if ($this->pageinfo['href'])
				static_main::redirect($this->pageinfo['href']);
			$this->get_pageinfo(); //$this->pageinfo['path']

			$this->IfDontHavePage = true;
			return 1;
		}
		return 0;
	}

	function get_pageinfo() {
		$this->current_path = $this->getHref($this->pageinfo['id'], true);
		$parent_id = $this->pageinfo['parent_id'];
		$id = $this->pageinfo['id'];
		$this->pageinfo['path'] = array($this->pageinfo['id'] => $this->pageinfo);
		$this->selected[$this->pageinfo['id']] = $this->pageinfo['id'];

		while ($parent_id) {
			if (isset($this->dataCash[$parent_id])) {
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
		if ($this->pageinfo['path'] and is_array($this->pageinfo['path']))
			foreach ($this->pageinfo['path'] as $row) {
				if (!is_array($row) or !isset($row['onpath']) or $row['onpath']) {
					if (is_array($row))
						$name = $row['name'];
					else
						$name = $row;

					if ($path == '')
						$path = $name;
					elseif ($name != '')
						$path = $name . ' - ' . $path;
				}
			}
		return $path;
	}

	function get_path() {
		$data = array();
		foreach ($this->pageinfo['path'] as $key => $row) {
			if (!is_array($row) or !isset($row['onpath']) or $row['onpath']) {
				if (is_array($row))
					$name = $row['name'];
				else
					$name = $row;
				$data[] = array('href' => $this->getHref($key, true), 'name' => $name);
			}
		}
		return $data;
	}

	function displayHttpCode($a = 404,$full=false) {
		header("HTTP/1.0 ".$a);
		if (isset($this->dataCashTreeAlias[$this->rootPage][$a])) {
			$this->id = $this->dataCashTreeAlias[$this->rootPage][$a]['id'];
		}
		else {
			$this->id = $this->rootPage;
			$text = '<h2>' . static_main::m($a) . '</h2>';
		}
		// Мог бы  использовать $this->can_show() , но поведение этой функции не предсказуемо
		$this->pageinfo = $this->dataCash[$this->id];
		$this->get_pageinfo();
		$this->display_page($this->id,$full);
		if (isset($text)) {
			global $_tpl;
			$_tpl['text'] = $text;
		}
		//$_tpl['title'] = $this->get_caption();
		return true;
	}

	function display_page($id,$full=false) {
		$Cdata = array();
		$cls = 'SELECT * FROM ' . $this->SQL_CFG['dbpref'] . 'pg_content WHERE active=1 and (owner_id="' . $id . '"';
		//if($this->id!='404') // откл повторные глобалные контенты, если это 400 и 401 страница
		if($full)
			$cls .= ' or (owner_id IN ("' . (implode('","', $this->selected)) . '") and global=1)';
		$cls .= ' ) ORDER BY ordind';
		$resultPG = $this->SQL->execSQL($cls);
		if (!$resultPG->err)
			while ($rowPG = $resultPG->fetch()) {
				$Cdata[$rowPG['id']] = $rowPG;
			}
		
		$this->access_flag = false;
		if($this->_CFG['returnFormat'] != 'html')
			$this->access_flag = true;

		return $this->getContent($Cdata);
	}

	function display_content($marker, $design = 'default') {
		global $HTML;
		if (!$HTML) {
			if (!$design)
				$design = $this->config['design'];
			require_once($this->_CFG['_PATH']['core'] . '/html.php');
			$HTML = new html('_design/', $design, false); //отправляет header и печатает страничку
		}
		$Cdata = array();
		$cls = 'SELECT * FROM ' . $this->SQL_CFG['dbpref'] . 'pg_content WHERE active=1 and marker IN ("' . $marker . '")';
		$resultPG = $this->SQL->execSQL($cls);
		if (!$resultPG->err)
			while ($rowPG = $resultPG->fetch()) {
				$Cdata[$rowPG['id']] = $rowPG;
			}
		
		$this->access_flag = false;
		if($this->_CFG['returnFormat'] != 'html')
			$this->access_flag = true;

		return $this->getContent($Cdata);
	}

	public function display_inc($id, $design = 'default') {
		$Cdata = $oId = array();
		$cls = 'SELECT * FROM ' . $this->SQL_CFG['dbpref'] . 'pg_content WHERE active=1 and id IN ("' . $id . '")';
		$resultPG = $this->SQL->execSQL($cls);
		if (!$resultPG->err)
			while ($rowPG = $resultPG->fetch()) {
				$Cdata[$rowPG['id']] = $rowPG;
				$oId[$rowPG['owner_id']] = $rowPG['owner_id'];
			}
		if(!count($this->pageinfo)) {
			$data = $this->qs('*','WHERE id IN ('.implode(',',$oId).') LIMIT 1');
			$this->pageinfo = $data[0];

			$this->initHTML(false);

			global $_tpl;
			$_tpl = array();
		}
		$this->access_flag = false;
		return $this->getContent($Cdata);
	}

	function getContent(&$Cdata) {
		global $SQL, $PGLIST, $HTML, $_CFG, $_tpl;
		$flagPG = 0;

		$PGLIST = &$this;
		$SQL = &$this->SQL;
		$Chref = $this->getHref();

		foreach ($Cdata as &$rowPG) {
			$this->contentID = $rowPG['id'];
			if (!$rowPG['active'])
				continue;
			$Ctitle = $rowPG['name'];

			if (!isset($_tpl[$rowPG['marker']])) {
				$_tpl[$rowPG['marker']] = '';
			}
			$_tempMarker = '';

			$html = '';
			if ($rowPG['ugroup']) {
				if (!$this->pagePrmCheck($rowPG['ugroup'])) {
					if ($this->_CFG['wep']['debugmode'] > 2)
						$_tpl[$rowPG['marker']] .= '<!--content' . $rowPG['id'] . ' ACCESS DENIED -->';
					continue;
				}
			}
			if ($this->access_flag and $rowPG['access_flag']) {
				if ($this->_CFG['wep']['debugmode'] > 2)
					$_tpl[$rowPG['marker']] .= '<!--content' . $rowPG['id'] . ' ACCESS2 DENIED -->';
				continue;
			}

			if ($rowPG['href']) {
				$temp = $this->_cl . '_' . preg_replace($this->_CFG['_repl']['alphaint'], '', $rowPG['href']);
				if (!isset($_COOKIE[$temp])) {
					_setcookie($temp, 1, time() + 1);
					static_main::redirect($rowPG['href']);
				} else {
					trigger_error('На этой странице ' . $this->id . '[' . $rowPG['id'] . '] обнаружена циклическая переадресация.Веб-страница привела к избыточному количеству переадресаций.', E_USER_WARNING);
				}
			}
			if ($rowPG['script']) {
				$rowPG['script'] = explode('|', trim($rowPG['script'], '|'));
				if (count($rowPG['script'])) {
					foreach ($rowPG['script'] as $r)
						if ($r)
							$_tpl['script'][$r] = 1;
				}
			}
			if ($rowPG['styles']) {
				$rowPG['styles'] = explode('|', trim($rowPG['styles'], '|'));
				if (count($rowPG['styles'])) {
					foreach ($rowPG['styles'] as $r)
						if ($r)
							$_tpl['styles'][$r] = 1;
				}
			}
			if($rowPG['keywords']) {
				if (!isset($_tpl['keywords']))
					$_tpl['keywords'] = $rowPG['keywords'];
				else
					$_tpl['keywords'] .= ', ' . $rowPG['keywords'];
			}

			if($rowPG['description']) {
				if (!isset($_tpl['description']))
					$_tpl['description'] = $rowPG['description'];
				else
					$_tpl['description'] .= ' ' . $rowPG['description'];
			}

			/* Статика */
			if ($rowPG['pagetype'] == '') {
				/* $text = $this->_CFG['_PATH']['path'].$this->_CFG['PATH']['content'].'pg/'.$rowPG['id'].$this->text_ext;
				  if (file_exists($text)) {
				  $flagPG = 1;
				  $_tempMarker .= file_get_contents($text);
				  } */
				$_tempMarker .= $rowPG['pg'];
				$flagPG = 1;
				//$_tpl[$rowPG['marker']] .= '<div id="pg_'.$rowPG['id'].'">'.$_tempMarker.'</div>';
				if (isset($_SESSION['_showallinfo']) && $_SESSION['_showallinfo'])
					$_tpl[$rowPG['marker']] .= '<!--content' . $rowPG['id'] . ' begin-->' . $_tempMarker . '<!--content' . $rowPG['id'] . ' end-->';
				else
					$_tpl[$rowPG['marker']] .= $_tempMarker;
			} else {
				// Флаг проверки о включенном memcache
				$flagMC = false;
				// Вообще отключаем кеш
				if ($this->config['memcache'] == -1)
					$rowPG['memcache'] = 0;
				// Если в контене 0, и есть в конфиг-кеш
				elseif ($rowPG['memcache'] == 0 and $this->config['memcache'] > 0)
					$rowPG['memcache'] = $this->config['memcache'];
				elseif ($rowPG['memcache'] < 0)
					$rowPG['memcache'] = 0;

				$MemFlag = false;
				//Если разрешено кеширование
				if ($rowPG['memcache']) {
					// собираем хешкод (идентификатор данных) для memcache
					$hashkeyPG = '';
					if ($rowPG['memcache_solt'] == 1) {
						if (isset($_SESSION['user']['id']))
							$hashkeyPG = $_SESSION['user']['id'];
					}
					elseif ($rowPG['memcache_solt'] == 2)
						$hashkeyPG = session_id();
					elseif ($rowPG['memcache_solt'] == 3) {
						$tc = '';
						if (count($_COOKIE))
							foreach ($_COOKIE as $ck => $cr)
								$tc .= $cr;
						$hashkeyPG = md5($tc);
					}
					elseif ($rowPG['memcache_solt'] == 4)
						$hashkeyPG = $_SERVER['REMOTE_ADDR'];
					$hashkeyPG .= '@' . $rowPG['id'] . '@' . $_SERVER['QUERY_STRING'] . $_SERVER['HTTP_HOST'];
					if (_strlen($hashkeyPG) > 255)
						$hashkeyPG = md5($hashkeyPG);
					// Включаем 1 раз MEMCACHE
					if (!$this->MEMCACHE) {
						$this->incMemcach();
					}
					// если MEMCACHE удалось подключить
					if ($this->MEMCACHE) {
						// Флаг о том что MEMCACHE успешно включен для этого контента
						$MemFlag = true;
						$temp = $this->MEMCACHE->get($hashkeyPG);
						//Получаем массив с ключами шаблона
						if ($temp and is_array($temp)) {
							// ставим флаг о том что данные поступают из кеша
							$flagPG = $flagMC = 1;
							// заполняем данные
							foreach ($temp as $tk => $tr) {
								if (is_array($tr)) {
									if (!isset($_tpl[$tk]))
										$_tpl[$tk] = $tr;
									else
										$_tpl[$tk] += $tr;
								} else {
									if (!isset($_tpl[$tk]))
										$_tpl[$tk] = '';
									$_tpl[$tk] .= $tr;
								}
							}
						}
					}
				}
				//Если дынне из кеша не поступили, то запускаем обработчик
				if (!$flagMC) {
					//Если флаг включен, то работаем с временными фаилами, чтобы потом их записать в кеш
					if ($MemFlag) {
						// обнуляем $_tpl
						$temp_tpl = $_tpl;
						foreach ($_tpl as &$r) {
							if (is_array($r))
								$r = array();
							else
								$r = '';
						}
						unset($r);
					}

					// Параметры для обработчика
					$FUNCPARAM = $this->parserFlexData($rowPG['funcparam'], $rowPG['pagetype']);

					// подключение и запуск обработчика
					if ($file = $this->getIncFile($rowPG['pagetype']) and $file)
						$flagPG = include($file);
					else {
						trigger_error('Обрботчик страниц "' . $rowPG['pagetype'] . '" не найден!', E_USER_WARNING);
						continue;
					}

					// если не булевое значение то выводим содержимое
					if (is_string($flagPG)) {
						if (!isset($_tpl[$rowPG['marker']]))
							$_tpl[$rowPG['marker']] = '';
						
						if ($this->config['newadmin_on'] and strpos($flagPG,'<')===0)
							$_tpl[$rowPG['marker']] .= '<div class="wep-pgc" id="pgc' . $rowPG['id'] . '">' . $flagPG . '</div>';
						else
							$_tpl[$rowPG['marker']] .= $flagPG;
						$flagPG = 1;
					}
					elseif (is_int($flagPG) and  $flagPG >= 100 and $flagPG < 600) {
						// Если INC возвращает число - то выводим HTTP код ошибки
						$this->displayHttpCode($flagPG);
						$MemFlag = false;
					}

					// Если включен кеш, то записываем полученные данные в кеш
					if ($MemFlag) {
						$this->MEMCACHE->set($hashkeyPG, $_tpl, $this->config['memcachezip'], $rowPG['memcache']);
						if (count($_tpl)) {
							foreach ($_tpl as $tk => $tr) {
								if (is_array($tr)) {
									if (!isset($temp_tpl[$tk]))
										$temp_tpl[$tk] = $tr;
									else
										$temp_tpl[$tk] += $tr;
								} else {
									if (!isset($temp_tpl[$tk]))
										$temp_tpl[$tk] = '';
									$temp_tpl[$tk] .= $tr;
								}
							}
						}
						$_tpl = $temp_tpl;
					}
				}
			}
			//////////////////////
		}
		unset($rowPG);
		if ($this->MEMCACHE) {
			$this->MEMCACHE->close();
			$this->MEMCACHE = NULL;
		}

		return $flagPG;
	}

	/* function getMap
	  $onmenuPG=-1 - вывод по onmap
	  $onmenuPG='',
	  $flagPG=0, - 0 выводит всю структуру дерева , 1 только первый уровень
	  $startPG=''
	 */

	function getMap($onmenuPG = '', $flagPG = 0, $startPG = 0) {
		if (empty($this->dataCashTree))
			$this->sqlCashPG();
		$DATA_PG = array();
		if ($flagPG == 1) {
			$tempPG = &$this->dataCash;
		} else {
			if (!$startPG)
				$startPG = $this->rootPage;
			elseif (strpos($startPG, '#') !== false) {
				$startPG = substr($startPG, 1);
				if ($startPG == '')
					$startPG = $this->dataCash[$this->id]['parent_id'];
				elseif ((int) $startPG) {
					$startPG = (int) $startPG;
					while (!isset($this->pageParamId[$startPG]))
						$startPG--;
					$startPG = $this->pageParamId[$startPG];
				}
			}
			$tempPG = &$this->dataCashTree[$startPG];
		}

		if (count($tempPG))
			foreach ($tempPG as $keyPG => $rowPG) {
				if ($rowPG['ugroup']) {
					if (!$this->pagePrmCheck($rowPG['ugroup']))
						continue;
				}
				if ($onmenuPG == -1) {
					if (!$rowPG['onmap']) {
						continue;
					}
				} elseif ($onmenuPG != '' and !isset($rowPG['onmenu'][$onmenuPG])) {
					continue;
				}

				$href = $this->_CFG['_HREF']['BH'] . $this->getHref($keyPG, true);

				if ($this->id == $keyPG)
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

				$DATA_PG[$keyPG] = array(
					'name' => $name, 
					'href' => $href, 
					'attr' => $rowPG['attr'], 
					'sel' => $selPG, 
					'pgid' => $keyPG,
					'menuajax'=>$rowPG['menuajax'], 
				);
				if ($flagPG == 0 and isset($this->dataCashTree[$keyPG])) {
					$temp = $this->getMap($onmenuPG, $flagPG, $keyPG);
					$DATA_PG[$keyPG]['#item#'] = $temp;
				}

				if ($onmenuPG == -1 and $rowPG['pagemap']) {
					$mapPG = explode(':', $rowPG['pagemap']);
					if (count($mapPG) == 2 and file_exists($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php')) {
						$tempinc = include($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php');
						if (isset($DATA_PG[$keyPG]['#item#']) and is_array($DATA_PG[$keyPG]['#item#']))
							$DATA_PG[$keyPG]['#item#'] += $tempinc;
						else
							$DATA_PG[$keyPG]['#item#'] = $tempinc;
					}
				}
				elseif ($rowPG['pagemenu']) {
					$mapPG = explode(':', $rowPG['pagemenu']);
					if (count($mapPG) == 2 and file_exists($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php')) {
						$tempinc = include($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php');
						if (isset($DATA_PG[$keyPG]['#item#']) and is_array($DATA_PG[$keyPG]['items']))
							$DATA_PG[$keyPG]['#item#'] += $tempinc;
						else
							$DATA_PG[$keyPG]['#item#'] = $tempinc;
					}
				}
			}
		return $DATA_PG;
	}


	function getPGMap($list) {
		if (empty($this->dataCashTree))
			$this->sqlCashPG();
		$DATA_PG = array();

		if (count($this->dataCash))
			foreach ($list as $keyPG) {
				$rowPG = $this->dataCash[$keyPG];
				if ($rowPG['ugroup']) {
					if (!$this->pagePrmCheck($rowPG['ugroup']))
						continue;
				}
				/*if ($onmenuPG == -1) {
					if (!$rowPG['onmap']) {
						continue;
					}
				}*/
				if (!isset($list[$keyPG])) {
					continue;
				}

				$href = $this->_CFG['_HREF']['BH'] . $this->getHref($keyPG, true);

				if ($this->id == $keyPG)
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

				$DATA_PG[$keyPG] = array(
					'name' => $name, 
					'href' => $href, 
					'attr' => $rowPG['attr'], 
					'sel' => $selPG, 
					'pgid' => $keyPG,
					'menuajax'=>$rowPG['menuajax'], 
				);
				if ($pid = $rowPG['parent_id'] and isset($this->dataCashTree[$pid]) and isset($DATA_PG[$pid])) {
					$DATA_PG[$pid]['#item#'][] = $DATA_PG[$keyPG];
					unset($DATA_PG[$keyPG]);
				}

				/*if ($onmenuPG == -1 and $rowPG['pagemap']) {
					$mapPG = explode(':', $rowPG['pagemap']);
					if (count($mapPG) == 2 and file_exists($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php')) {
						$tempinc = include($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php');
						if (isset($DATA_PG[$keyPG]['#item#']) and is_array($DATA_PG[$keyPG]['#item#']))
							$DATA_PG[$keyPG]['#item#'] += $tempinc;
						else
							$DATA_PG[$keyPG]['#item#'] = $tempinc;
					}
				}
				else*/
				if ($rowPG['pagemenu']) {
					$mapPG = explode(':', $rowPG['pagemenu']);
					if (count($mapPG) == 2 and file_exists($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php')) {
						$tempinc = include($this->_enum['inc'][$mapPG[0]]['path'] . $mapPG[1] . '.map.php');
						if (isset($DATA_PG[$keyPG]['#item#']) and is_array($DATA_PG[$keyPG]['items']))
							$DATA_PG[$keyPG]['#item#'] += $tempinc;
						else
							$DATA_PG[$keyPG]['#item#'] = $tempinc;
					}
				}
			}
		return $DATA_PG;
	}

	function sqlCashPG() {
		if (empty($this->dataCash)) {
			if (is_array($this->config['rootPage'])) {
				foreach ($this->config['rootPage'] as $k => $r) {
					if (strpos($_SERVER['HTTP_HOST'], $k) !== false) {
						$this->rootPage = $r;
						break;
					}
				}
				if (is_null($this->rootPage))
					$this->rootPage = array_shift($this->config['rootPage']);
			} else
				$this->rootPage = $this->config['rootPage'];

			$cls = 'SELECT *';
			/* if(isset($_SESSION['user']['id']))
			  $cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|user|" or ugroup LIKE "%|'.$_SESSION['user']['owner_id'].'|%"),1,0) as prm';
			  else
			  $cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|anonim|"),1,0) as prm'; */
			$cls .= ' FROM ' . $this->tablename . ' WHERE active=1';
			$result = $this->SQL->execSQL($cls . ' ORDER BY ordind');

			if (!$result->err) {
				while ($row = $result->fetch()) {
					if (!isset($row['alias'])) {
						$this->updateModul();
						return $this->sqlCashPG();
					}
					$row['onmenu'] = array_flip(explode('|', trim($row['onmenu'], '|')));
					$this->dataCash[$row['id']] = $row;
					$this->dataCashTree[$row['parent_id']][$row['id']] = &$this->dataCash[$row['id']];
					$this->dataCashTreeAlias[$row['parent_id']][$row['alias']] = &$this->dataCash[$row['id']];
				}
			} else {
				static_main::redirect($this->_CFG['_HREF']['BH'] . $this->_CFG['PATH']['wepname'] . '/install.php');
			}
		}
		return true;
	}

	function updateModul() {
		$this->SQL->execSQL('alter table ' . $this->tablename . ' 
		change `id` `alias` varchar(63) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
		drop primary key,
		add column `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT after `alias`,
		add primary key(`id`)');
		$this->SQL->execSQL('UPDATE ' . $this->childs['content']->tablename . ' SET owner_id=(SELECT id FROM ' . $this->tablename . ' WHERE alias=owner_id)');
		$this->SQL->execSQL('alter table ' . $this->childs['content']->tablename . ' change `owner_id` `owner_id` int(11) NOT NULL');
		$this->SQL->execSQL('CREATE TABLE ' . $this->tablename . '_copy AS(SELECT * FROM ' . $this->tablename . ')');
		$this->SQL->execSQL('UPDATE ' . $this->tablename . ' t1 SET t1.parent_id=(SELECT t2.id FROM ' . $this->tablename . '_copy t2 WHERE t2.alias=t1.parent_id)');
		$this->SQL->execSQL('drop table ' . $this->tablename . '_copy');
		$this->SQL->execSQL('alter table ' . $this->tablename . ' change `parent_id` `parent_id` int(11) DEFAULT "0" NOT NULL');
	}

	function getHref($id = false, $html = false) {
		if (!$id)
			$id = $this->id;
		if (empty($this->dataCashTree))
			$this->sqlCashPG();
		if ($html and isset($this->dataCash[$id]['href']) and $this->dataCash[$id]['href'] != '') {
			$href = $this->dataCash[$id]['href'];
			if (strstr($href, 'http://'))
				$href = '_redirect.php?url=' . base64encode($href);
		}
		else {
			$href = $id;
			if (isset($this->dataCash[$id])) {
				if ($this->dataCash[$id]['alias'])
					$href = $this->dataCash[$id]['alias'];
				$pid = $this->dataCash[$id]['parent_id'];
				while ($pid and $pid != $this->rootPage) {
					if (!isset($this->dataCash[$pid]))
						break;
					if ($this->dataCash[$pid]['alias'])
						$href = $this->dataCash[$pid]['alias'] . '/' . $href;
					else
						$href = $pid . '/' . $href;
					$pid = $this->dataCash[$pid]['parent_id'];
				}
			}
			if ($html and strpos($href, '.html') === false) {
				$href .= '.html';
				if (isset($this->dataCash[$id]['aparam']) and $this->dataCash[$id]['aparam'])
					$href .= '?' . $this->dataCash[$id]['aparam'];
			}
		}
		return $href;
	}

	/*
	* Проверка правд доступа к странице
	*/
	function pagePrmCheck($ugroup = '') {
		global $_tpl;

		if ($ugroup) {
			$ugroup = explode('|', trim($ugroup, '|'));
			$ugroup = array_flip($ugroup);
			if (!isset($ugroup[0]) and count($ugroup)) {
				if (isset($_SESSION['user']['id'])) {
					if (!isset($ugroup['user']) and !isset($ugroup[$_SESSION['user']['owner_id']])) {
						return false;
					}
				} elseif (!isset($ugroup['anonim'])) {
					return false;
				}
			}
		}
		return true;
	}

	/*
	* XML карта сайта
	*
	*/
	function creatSiteMaps() {
		$data = $this->getMap(-1);
		$xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$xml .= $this->reverseDataMap($data);
		$xml .= '</urlset>';
		return $xml;
	}

	/*
	* Вспомогательная (реверс) функ для XML карты
	*/
	function reverseDataMap(&$data) {
		$xml = '';
		foreach ($data as $k => $r) {
			if (isset($r['href']) and $r['href'])
				$xml .= '
	<url>
		<loc>' . $r['href'] . '</loc>
		<changefreq>daily</changefreq>
	</url>';
			if (isset($r['#item#']) and count($r['#item#']))
				$xml .= $this->reverseDataMap($r['#item#']);
		}
		return $xml;
	}

	/**
	* Получаем путь к фаилу INC из строкового параметра (запись в виде 1:ugroup.class/login)
	*
	*/
	public function getIncFile($typePG) {
		$inc = explode(':',$typePG);
		if (count($inc) == 2) {
			$file = $this->_enum['inc'][$inc[0]]['path'] . $inc[1] . '.inc.php';
		}
		else {
			$file = $this->_CFG['_PATH']['inc'] . $typePG . '.inc.php';
			if(!file_exists($file))
				$file = $this->_CFG['_PATH']['wep_inc'] .$typePG . '.inc.php';
		}
		if(!file_exists($file)) $file = false;
		return $file;
	}

	/*
	*
	*/
	function parserFlexData($stringFlexData, $pagetype, $setIndex=false) {
		// Преобразование данных Контент-модуля по полю funcparam
		$pagetype = explode(':', $pagetype);
		$FUNCPARAM = array();
		// Параметры для обработчика
		if ($stringFlexData) {
			$stringFlexData = explode('&', $stringFlexData);
			foreach ($stringFlexData as $k=>$r) {

				// UPDATE FIX
				if (strpos($r, '#ext#') !== false) { // Поддержка старых версий, где вместо названия модуля использовалось #ext# 
					$temp = explode('.',$pagetype[1]);
					$r = str_replace('#ext#','#'.$temp[0].'#',$r);
				}

				// UPDATE FIX
				if (strpos($r, '|') !== false) { // Делаем асоциативный массив
					$r = explode('|', $r);
					$r = array_combine($r, $r);
				}
				if($setIndex) $k = 'flexform_'.$k; // эта опция для формы
				$FUNCPARAM[$k] = $r;
			}
		}

		return $FUNCPARAM;
	}

	/*
	* Обработка АЯКС запросов с страницы содержащий форму
	*/
	public function AjaxForm() {
		global $HTML,$_tpl;
		$RESULT = array('html'=>'Не верные данные', 'html2'=>'', 'text'=>'','onload'=>'');
		$DATA  = array();
		$htmlb = '';
		$this->ajaxRequest = true;
		//if(count($_POST)) $_POST['sbmt'] = 1;

		$Cdata = array();
		if(isset($_GET['pageParam']))
			$this->pageParam = $_GET['pageParam'];
		// TODO : проверка правд доступа
		$cls = 'SELECT * FROM ' . $this->SQL_CFG['dbpref'] . 'pg_content WHERE active=1 and id=' . (int)$_GET['contentID'];
		$resultPG = $this->SQL->execSQL($cls);
		if (!$resultPG->err)
			while ($rowPG = $resultPG->fetch()) {
				$Cdata[$rowPG['id']] = $rowPG;
			}
		
		$this->access_flag = false;
		if(!count($Cdata) or !$this->getContent($Cdata))
			return $RESULT;

		if(is_null($this->formFlag)) {
			$RESULT['html'] = 'Не верные данные! Отсутствует параметр $this->formFlag';
			return $RESULT;
		}

		$RESULT['html'] = $_tpl['text'];

		if($this->formFlag==1) {
			$RESULT['onload'] .= 'clearTimeout(timerid2);wep.fShowload (1,false,result.html2,0,\'location.href = location.href;\');';
		}
		elseif($this->formFlag==-1) {
			//$RESULT['onload'] = 'GetId("messages").innerHTML=result.html2;'.$RESULT['onload'];
			$RESULT['onload'] = 'jQuery(\'.caption_error\').remove();'.$RESULT['onload'].'clearTimeout(timerid2);wep.fShowload(1,false,result.html2);';
			$RESULT['html']="<div class='blockhead'>Внимание. Некоректно заполнены поля.</div><div class='hrb'>&#160;</div>".$RESULT['html'];
		}
		else{
			$RESULT['onload'] .= 'clearTimeout(timerid2);wep.fShowload(1,false,result.html2);';
		}
		$RESULT['html2']=$RESULT['html'];
		$RESULT['html']='';
		if(!isset($_SESSION['user']['id']))
			$RESULT['onload'] .= 'reloadCaptcha(\'captcha\');';
		$RESULT['onload'] .= $_tpl['onload'];

		return $RESULT;
	}

//////////
	/*public function _update($data=array(),$where=null,$flag_select=true) {
		if($ret = parent::_update($data,$where,$flag_select)) {
		}
		return $ret;
	}
	public function _add($data=array(),$flag_select=true) {
		if($ret = parent::_add($data,$flag_select)) {
		}
		return $ret;
	}*/

	public function fFormCheck(&$DATA, &$param, &$argForm) {
		$RESULT = parent::fFormCheck($DATA,$param,$argForm);
		if(isset($DATA['alias']) and $DATA['alias']!=='') {
			$resdata = $this->qs('id','WHERE parent_id='.(int)$DATA['parent_id'].' and alias="'.$this->SqlEsc($DATA['alias']).'" '.($this->id?' and id!='.$this->id:''));
			if(count($resdata))
				$RESULT['mess'][] = static_main::am('error', 'Запрещено дублировать страницы (Алиас) на одном подуровне');
		}
		return $RESULT;
	}


	/*
	* Устаревшая функция 
	*/
	public function FFTemplate(&$tpl, $dir = false) {
		if (strpos($tpl, '#ext#') !== false and $dir) {
			$tpl = str_replace('#ext#', '', $tpl);
			$tpl2 = array($tpl, $dir . '/templates/');
		}else
			$tpl2 = $tpl;
		return $tpl2;
	}
}
