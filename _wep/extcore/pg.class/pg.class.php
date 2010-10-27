<?
class pg_class extends kernel_class {

	protected function _create_conf() {
		parent::_create_conf();

		$this->config['sitename'] = 'MY SITE';
		$this->config['address'] = '';
		$this->config['description'] = 'Desc...';
		$this->config['keywords'] = 'Keys...';
		$this->config['copyright'] = '';
		$this->config['counter'] = '';
		$this->config['design'] = 'default';
		$this->config['menu'] = '';

		$this->config_form['sitename'] = array('type' => 'text', 'caption' => 'Название сайта','mask'=>array('max'=>1000));
		$this->config_form['address'] = array('type' => 'textarea', 'caption' => 'Адрес и контакты','mask'=>array('max'=>1000));
		$this->config_form['copyright'] = array('type' => 'textarea', 'caption' => 'Копирайт','mask'=>array('max'=>1000));
		$this->config_form['counter'] = array('type' => 'textarea', 'caption' => 'Счётчик','mask'=>array('max'=>1500));
		$this->config_form['keywords'] = array('type' => 'textarea', 'caption' => 'Ключевые слова по умолчанию','mask'=>array('max'=>1000));
		$this->config_form['description'] = array('type' => 'textarea', 'caption' => 'Описание страницы по умолчанию','mask'=>array('max'=>1000));
		$this->config_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизаин по умолчанию');
	}

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_use_charid = true;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Страницы';
		$this->selected = array();
		$this->ver = '0.1';
		return 0;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['keywords'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['description'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['design'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['template'] = array('type' => 'varchar', 'width'=>20, 'attr' => 'NOT NULL','default'=>'default');
		$this->fields['styles'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL','default'=>'style');
		$this->fields['script'] = array('type' => 'varchar', 'width'=> 254, 'attr' => 'NOT NULL','default'=>'script');
		$this->fields['ugroup'] =array('type' => 'varchar', 'width'=>254, 'attr' => 'NOT NULL DEFAULT "|0|"');
		$this->fields['attr'] = array('type' => 'varchar', 'width'=>254, 'attr' => 'NOT NULL DEFAULT ""');
		$this->fields['onmenu'] = array('type' => 'tinyint', 'width'=>5, 'attr' => 'NOT NULL DEFAULT 0');
		$this->fields['onpath'] = array('type' => 'tinyint', 'width'=>1, 'attr' => 'NOT NULL DEFAULT 1');

		# fields
		$this->fields_form['id'] = array('type' => 'text', 'caption' => 'ID','mask'=>array('sort'=>1,'min'=>1));
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительская страница','mask'=>array('fview'=>1));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Name','mask'=>array('sort'=>1,'min'=>1));
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'HREF', 'mask' =>array('onetd'=>'Содержимое'));
		$this->fields_form['design'] = array('type' => 'list', 'listname'=>'mdesign', 'caption' => 'Дизайн', 'mask' =>array('onetd'=>'Дизайн'));
		$this->fields_form['template'] = array('type' => 'list', 'listname'=>'templates', 'caption' => 'Шаблон', 'mask' =>array('onetd'=>'none'));
		$this->fields_form['styles'] = array('type' => 'list', 'multiple'=>1, 'listname'=>'styles', 'caption' => 'CSS', 'mask' =>array('onetd'=>'none'));
		$this->fields_form['script'] = array('type' => 'list', 'multiple'=>1, 'listname'=>'script', 'caption' => 'SCRIPT', 'mask' =>array('onetd'=>'close'));
		$this->fields_form['keywords'] = array('type' => 'text', 'caption' => 'META-keywords','mask'=>array('fview'=>1));
		$this->fields_form['description'] = array('type' => 'text', 'caption' => 'META-description','mask'=>array('fview'=>1));
		$this->fields_form['onmenu'] = array('type' => 'list', 'listname'=>'menu', 'multiple'=>1, 'caption' => 'Меню', 'mask'=>array('onetd'=>'Опции'));
		$this->fields_form['onpath'] = array('type' => 'checkbox', 'caption'=>'Путь', 'comment' => 'Отображать в хлебных крошках');
		$this->fields_form['attr'] = array('type' => 'text', 'caption' => 'Атрибуты для ссылки в меню', 'comment'=>'Например: `target="_blank" onclick=""` итп', 'mask' =>array('name'=>'text', 'fview'=>1));
		if($this->_CFG['wep']['access'])
			$this->fields_form['ugroup'] = array('type' => 'list','multiple'=>1,'listname'=>'ugroup', 'caption' => 'Доступ пользователю','default'=>'0');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
		$this->fields_form['ordind'] = array('type' => 'int', 'caption' => 'ORD');

		# list
		//$this->listform_items['id'] = 'ID';
		//'type' => 'list', 'listname'=>'design',-+-

		$this->_fields_key = array('ugroup'=>'ugroup');

		$this->def_records[] = array('id'=>'index','name'=>'Главная страница','active'=>1,'template'=>'default');
		$this->def_records[] = array('id'=>'404','parent_id'=>'index','name'=>'Страницы нету','active'=>1,'template'=>'default');
		$this->def_records[] = array('id'=>'500','parent_id'=>'index','name'=>'Недостаточно прав для доступа к странице','active'=>1,'template'=>'default');

		if($this->_CFG['_F']['adminpage']) {
			include_once($this->_CFG['_PATH']['extcore'].'pg.class/childs.class.php');
			$this->create_child("content");
		}
	}

	function _getlist($listname,$fields_form=array()) {
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
		elseif ($listname == "styles") {
			$dir = dir($this->_CFG['_PATH']['_style']);
			while (false !== ($entry = $dir->read())) {
				if (strstr($entry,'.css')) {
					$entry = substr($entry, 0, strpos($entry, '.css'));
					$data[$entry] = $entry;
				}
			}
			$dir->close();
			return $data;
		}
		elseif ($listname == "script") {
			$dir = dir($this->_CFG['_PATH']['_script']);
			while (false !== ($entry = $dir->read())) {
				if (strstr($entry,'.js')) {
					$entry = substr($entry, 0, strpos($entry, '.js'));
					$data[$entry] = $entry;
				}
			}
			$dir->close();
			return $data;
		}
		elseif ($listname == "templates") {
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
		elseif ($listname == "mdesign") {
			$data['default'] = 'default';
			$data['new'] = 'new';
			return $data;
		}
		else return parent::_getlist($listname,$fields_form);
	}

	function display() {
		global $_tpl,$HTML;
		$flag_content = $this->can_show();
		//PAGE****************
		if ($flag_content==2) {
			$this->id = "401";
			header("HTTP/1.0 401");
			if(!$this->can_show())
			{
				$this->display_page($_tpl);
				$HTML->_templates = "default";
				$_tpl['text'] = 'У вас не достаточно прав для доступа к странице.';
				$_tpl['title'] = "Нет доступа";
				$_tpl['keywords'] = "";
				$_tpl['description'] = "";
			}
			else
			{
				$this->display_page($_tpl);
				$HTML->_templates = $this->pageinfo['template'];
				$_tpl['title'] = $this->get_caption();
				$_tpl['keywords'] = $this->pageinfo['keywords'];
				$_tpl['description'] = $this->pageinfo['description'];
			}
		}
		elseif ($flag_content and $this->display_page($_tpl))
		{
			$HTML->_templates = $this->pageinfo['template'];
			$_tpl['title'] = $this->get_caption();
			$_tpl['keywords'] = $this->pageinfo['keywords'];
			$_tpl['description'] = $this->pageinfo['description'];
		}
		else
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
				$this->display_page($_tpl);
				$HTML->_templates = $this->pageinfo['template'];
				$_tpl['title'] = $this->get_caption();
				$_tpl['keywords'] = $this->pageinfo['keywords'];
				$_tpl['description'] = $this->pageinfo['description'];
			}
		}
		if($this->config['sitename'])
			$_tpl['title'] .= ' - '.$this->config['sitename'];//$_SERVER['SERVER_NAME']


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
		if(isset($this->dataCash[$this->id]) and !$this->dataCash[$this->id]['prm']) {
			$this->pageinfo = $this->dataCash[$this->id];
			return 2;
		}
		elseif(isset($this->dataCash[$this->id]))
		{
			if ($row['href']){
				header('Location: '.$row['href']);die();}
			$this->pageinfo = $this->dataCash[$this->id];
			$this->pageinfo['keywords'] = $this->config['keywords'];
			if($row['keywords']) $this->pageinfo['keywords'] = $this->dataCash[$this->id]['keywords'].', '.$this->pageinfo['keywords'];
			$this->pageinfo['description'] = $this->config['description'];
			if($row['description']) $this->pageinfo['description'] = $this->dataCash[$this->id]['description'].', '.$this->pageinfo['description'];
			$this->pageinfo['script'] = explode('|',$this->dataCash[$this->id]['script']);
			$this->pageinfo['styles'] = explode('|',$this->dataCash[$this->id]['styles']);
			$this->get_pageinfo();//$this->pageinfo['path']
			return 1;
		}
		else
			return 0;
	}

	function get_pageinfo() {
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
		return 0;
	}


	function get_caption() {
		$path = '';
		foreach($this->pageinfo['path'] as $row)
		{
			if(is_array($row)) $name = $row['name'];
			else $name = $row;

			if($path=='') $path = $name;
			elseif($name!='') $path = $name.' - '.$path;
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


	function display_page(&$_tpl) {
		global $SQL, $PGLIST, $HTML, $_CFG, $_tpl;
		$flagPG = 0;

		$cls = 'SELECT * FROM '.$this->_CFG['sql']['dbpref'].'pg_content WHERE active=1 and (owner_id="'.$this->id.'"';
		//if($this->id!='404') // откл повторные глобалные контенты, если это 400 и 500 страница
			$cls .= ' or (owner_id IN ("'.(implode('","',$this->selected)).'") and global=1)';
		$cls .= ' ) ORDER BY ordind';
		$resultPG = $this->SQL->execSQL($cls);
		if(!$resultPG->err)
			while ($rowPG = $resultPG->fetch_array()){
				$html = '';
				if($rowPG['pagetype']=='') {
					$text = $this->_CFG['_PATH']['path'].$this->_CFG['PATH']['content'].'pg/'.$rowPG['id'].$this->text_ext;
					if (file_exists($text)) {
						$flagPG = 1;
						$_tpl[$rowPG['marker']] .= file_get_contents($text);
					}
				} else {
					$FUNCPARAM = $rowPG['funcparam'];
					if(file_exists($this->_CFG['_PATH']['ptext'].$rowPG['pagetype'].".inc.php"))
						$flagPG = include($this->_CFG['_PATH']['ptext'].$rowPG['pagetype'].".inc.php");
					elseif(file_exists($this->_CFG['_PATH']['ctext'].$rowPG['pagetype'].".inc.php"))
						$flagPG = include($this->_CFG['_PATH']['ctext'].$rowPG['pagetype'].".inc.php");
					else {
						trigger_error('Display block '.$rowPG['pagetype'].' not exists', E_USER_WARNING);
						continue;
					}
					if($_SESSION['_showallinfo']) $_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' begin-->'; // для отладчика
					if($flagPG===false) //если INCa вернула значение flase , то завершаем отображение страницы и выдаем в итоге 404
						return 0;
					elseif($flagPG!==true) // если не булевое значение то выводим содержимое
						$_tpl[$rowPG['marker']] .= $flagPG;
					$flagPG = 1;
					if($_SESSION['_showallinfo'])
						$_tpl[$rowPG['marker']] .= '<!--content'.$rowPG['id'].' end-->';
				}
			}

		return $flagPG;
	}

	/*function getMap
			$onmenu='',
			$flag=0, - 0 выводит всю структуру дерева , 1 только первый уровень
			$start=''
	*/

	function getMap($onmenu='',$flag=0,$start='') {
		if(!isset($this->dataCashTree))
			$this->sqlCashPG();
		$DATA = array();
		if($flag>1) //только начальный уровень
			$temp = &$this->dataCashTree[$start];
		elseif($flag) //выводит все в общем массиве
			$temp = &$this->dataCash;
		else //выводит всё в виде структуры дерева
			$temp = &$this->dataCashTree[$start];
		if(count($temp))
			foreach ($temp as $key=>$row)
			{
				if(!$row['prm'] or ($onmenu!='' and !isset($row['onmenu'][$onmenu])))
					continue;

				$href = $this->getHref($key,$row);

				if (is_array($this->selected) and isset($this->selected[$key]))
					$sel = 1;
				else
					$sel = 0;

				$DATA[$key] = array('name'=>$row['name'], 'href'=>$href, 'attr'=>$row['attr'], 'sel'=>$sel);
				if(!$flag and isset($this->dataCashTree[$key]))
					$DATA[$key]['items'] = $this->getMap($onmenu,$flag,$key);
			}
		return $DATA;
	}

	function sqlCashPG() {
		if(!isset($this->dataCash)) {
			$cls = 'SELECT *';
			if(isset($_SESSION['user']['id']))
				$cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|user|" or ugroup LIKE "%|'.$_SESSION['user']['owner_id'].'|%"),1,0) as prm';
			else
				$cls .= ',if((ugroup="" or ugroup="|0|" or ugroup="|anonim|"),1,0) as prm'; 
			$cls .= ' FROM '.$this->tablename.' WHERE active=1';
			$result = $this->SQL->execSQL($cls.' ORDER BY ordind');
			if(!$result->err){
				while($row = $result->fetch_array()) {
					$row['onmenu'] = array_flip(explode('|',trim($row['onmenu'],'|')));
					$this->dataCash[$row['id']] = $row;
					$this->dataCashTree[$row['parent_id']][$row['id']] = &$this->dataCash[$row['id']];
				}
			}
		}
		return 0;
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
}

?>