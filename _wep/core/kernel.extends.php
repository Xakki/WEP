<?

/* VERSION=2.2 */
/* COMMENT=Ядро, дополняющая модули */

abstract class kernel_extends {
	/*
	 * версия ядра
	 *  нумерация отличает от других версией
	 * 1 - структурной не совместимостью, различия в хранении данных и в исполняемых функциях, вызывающие критические ошибки в коде
	 * 2 - добавленн новый функционал, расширен и измененн меющиеся функции -
	 * 3 - Номер ревизии , исправленны ошибки
	 */
	const versionCore = '2.2.9';

	function __construct($owner=NULL) {
		global $_CFG;
		//FB::info($_CFG);
		$this->_CFG = true; // баг ПХП
		$this->_CFG = &$_CFG; //Config

		$this->owner = true;
		$this->owner = $owner; //link to owner class
		$this->_set_features(); // настройки модуля

		if ($this->singleton == true)
			$_CFG['singleton'][$this->_cl] = &$this;

		$this->_create_conf(); // загрузки формы конфига
		if (isset($this->config_form) and count($this->config_form)) { // загрузка конфига из файла для модуля
			$this->configParse();
		}
		$this->_create(); // предустановки модуля
		$this->_childs();
		$this->setFieldsForm();
		if (isset($this->_CFG['hook']['__construct']))
			$this->__do_hook('__construct', func_get_args());
	}

	function __destruct() {

	}

	function __get($name) {
		global $_CFG;
		if ($name == 'SQL') {
			if (!$this->grant_sql) {
				global $SQL;
				if (!$SQL)
					$SQL = new sql($_CFG['sql']);
				return $SQL;
			} else {
				return new sql($this->cfg_sql);
			}
		} elseif ($name == 'fields_form') {
			$this->setFieldsForm();
			return $this->fields_form;
		}
		echo ' - ' . $name;
		print_r(debugPrint());
		//return NULL;
	}

	/* function __set_hook($f) {
	  } */

	function __do_hook($f, $arg = array()) {
		if (!isset($this->_CFG['hook'][$f])) // проверка на всякий случай
			return false;
		///static_main::_prmModulLoad();
		$modul = array($this->_cl);

		if (isset($this->_CFG['modulprm'][$this->_cl]['extend']))
			$modul[] = $this->_CFG['modulprm'][$this->_cl]['extend'];

		foreach ($modul as $m) {
			if (isset($this->_CFG['hook'][$f][$m])) {
				foreach ($this->_CFG['hook'][$f][$m] as $k => $r) {
					$file = NULL;
					if (!function_exists($r)) {
						$file = $this->_CFG['_PATH']['path'] . $k;
						if (file_exists($file)) {
							include_once($file);
						}
					}
					if ($file === NULL or function_exists($r)) {
						eval('return ' . $r . '($this,$arg);');
					}else
						trigger_error('Для модуля `'.$this->_cl.'`, функция хука `'.$r.'` не определена', E_USER_WARNING);
				}
			}
		}
		return false;
	}

	/* -----------CMS---FUNCTION------------
	  _set_features()
	  _create()
	  create_child($class_name)


	  -------------------------------------- */

	protected function _set_features() {// initalization of modul features
		//$this->mf_issimple = false;
		//$this->mf_typectrl = false;
		//$this->mf_struct_readonly = false;
		$this->id = NULL;
		$this->_file_cfg = NULL;
		$this->mf_use_charid = false; //if true - id varchar
		$this->mf_idwidth = 63; // длина поля ID
		$this->mf_namefields = true; //добавлять поле name
		$this->mf_createrid = true; //польз владелец
		$this->mf_istree = false; // древовидная структура?
		$this->mf_treelevel = 0; // разрешенное число уровней в дереве , 0 - безлимита, 1 - разрешить 1 подуровень
		$this->mf_ordctrl = false; // поле ordind для сортировки
		$this->mf_actctrl = false; // поле active
		$this->mf_timestamp = false; // создать поле  типа timestamp
		$this->mf_timecr = false; // создать поле хранящще время создания поля
		$this->mf_timeup = false; // создать поле хранящще время обновления поля
		$this->mf_timeoff = false; // создать поле хранящще время отключения поля (active=0)
		$this->mf_ipcreate = false; //IP адрес пользователя с котрого была добавлена запись
		$this->prm_add = true; // добавить в модуле
		$this->prm_del = true; // удалять в модуле
		$this->prm_edit = true; // редактировать в модуле
		$this->showinowner = true; // показывать под родителем
		$this->owner_unique = false; // поле owner_id не уникально
		$this->mf_mop = true; // выключить постраничное отображение
		$this->reversePageN = false; // обратный отчет для постраничного отображения
		$this->messages_on_page = 20; //число эл-ов на странице
		$this->numlist = 20; //максим число страниц при котором отображ все номера страниц
		$this->mf_indexing = false; // TOOLS индексация
		$this->mf_statistic = false; // TOOLS показывать  статистику по дате добавления
		$this->cf_childs = false; // TOOLS true - включить управление подключение подмодулей в настройках модуля
		$this->cf_reinstall = false; // TOOLS
		$this->includeJStoWEP = false; // подключать ли скрипты для формы через настройки
		$this->includeCSStoWEP = false; // подключать ли стили для формы через настройки
		$this->singleton = true; // класс-одиночка
		$this->ver = '0.1.1'; // версия модуля
		$this->RCVerCore = '2.2.9';
		$this->icon = 0; /* числа  означают отсуп для бэкграунда, а если будет задан текст то это сам рисунок */

		$this->text_ext = '.txt'; // расширение для memo фиаилов

		$this->_cl = str_replace('_class', '', get_class($this)); //- символическое id модуля
		$this->owner_name = 'owner_id'; // название поля для родительской связи в БД
		$this->tablename = $this->_CFG['sql']['dbpref'] . $this->_cl; // название таблицы
		$this->caption = $this->_cl; // заголовок модуля
		$this->_listfields = array('name'); //select по умолч
		$this->unique_fields =
				$this->_enum =
				$this->update_records =
				$this->def_records =
				$this->fld_data =
				$this->fields =
				$this->form =
				$this->fields_form =
				$this->formSort = 
				$this->mess_form =
				$this->attaches = $this->att_data =
				$this->memos = $this->mmo_data =
				$this->services =
				$this->index_fields =
				$this->config =
				$this->config_form =
				$this->locallang =
				$this->enum =
				$this->child_path =
				$this->Achilds =
				$this->_setHook = array();
		$this->childs = new modul_child($this);
		$this->ordfield = $this->_clp = '';
		$this->data = array();
		$this->parent_id = NULL;
		$this->null = NULL;

		$this->grant_sql = false;
		$this->cfg_sql = $this->_CFG['sql'];
		return true;
	}

	protected function _create_conf() { // Здесь можно установить стандартные настройки модулей
		global $_CFG;
		if ($this->cf_childs) {
			$this->config['childs'] = '';
			$this->config_form['childs'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'child.class', 'caption' => 'Подмодули');
		}
		if ($this->includeJStoWEP) {
			$this->config['jsIncludeToWEP'] = '';
			$this->config_form['jsIncludeToWEP'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'script', 'caption' => 'Script модуля');
		}
		if ($this->includeCSStoWEP) {
			$this->config['cssIncludeToWEP'] = '';
			$this->config_form['cssIncludeToWEP'] = array('type' => 'list', 'multiple' => 2, 'listname' => 'style', 'caption' => 'CSS модуля');
		}
		return true;
	}

	protected function configParse() {
		if (isset($this->config_form)) { // загрузка конфига из файла для модуля
			if (is_null($this->_file_cfg))
				$this->_file_cfg = $this->_CFG['_PATH']['config'] . get_class($this) . '.cfg';
			if (file_exists($this->_file_cfg)) {
				$cont = file_get_contents($this->_file_cfg);
				if (substr($cont, 0, 5) == 'array')
					eval('$data=' . $cont . ';');
				else
					$data = static_main::_fParseIni($this->_file_cfg, $this->config_form);
				$this->config = array_merge($this->config, $data);
			}
		}
		return true;
	}

	protected function _create() {

		if (is_bool($this->mf_namefields) and $this->mf_namefields)
			$this->mf_namefields = 'name';
		if (is_bool($this->mf_createrid) and $this->mf_createrid)
			$this->mf_createrid = 'creater_id';
		if (is_bool($this->mf_istree) and $this->mf_istree)
			$this->mf_istree = 'parent_id';
		if (is_bool($this->mf_ordctrl) and $this->mf_ordctrl)
			$this->mf_ordctrl = 'ordind';
		if (is_bool($this->mf_actctrl) and $this->mf_actctrl)
			$this->mf_actctrl = 'active';

		$this->_listnameSQL = ($this->mf_namefields ? $this->mf_namefields : 'id'); // для SQL запроса при выводе списка
		$this->_listname = ($this->mf_namefields ? $this->mf_namefields : 'id'); // ', `_listnameSQL` as `_listname`'
		// construct fields
		if ($this->mf_use_charid) {
			$this->fields['id'] = array('type' => 'varchar', 'width' => $this->mf_idwidth, 'attr' => 'NOT NULL');
		}
		else
			$this->fields['id'] = array('type' => 'int', 'width' => 11, 'attr' => 'UNSIGNED NOT NULL AUTO_INCREMENT');

		if ($this->mf_namefields)
			$this->fields[$this->mf_namefields] = array('type' => 'varchar', 'width' => '255', 'attr' => 'NOT NULL', 'default' => '');

		if ($this->owner) {
			$this->fields[$this->owner_name] = $this->owner->fields['id'];
			if (strpos($this->fields[$this->owner_name]['attr'], 'UNSIGNED') !== false)
				$this->fields[$this->owner_name]['attr'] = 'UNSIGNED NOT NULL';
			else
				$this->fields[$this->owner_name]['attr'] = 'NOT NULL';
			if ($this->owner_unique)
				$this->unique_fields[$this->owner_name] = $this->owner_name;
			else
				$this->index_fields[$this->owner_name] = $this->owner_name;
		}

		if ($this->mf_createrid) {
			$this->fields[$this->mf_createrid] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
			$this->index_fields[$this->mf_createrid] = $this->mf_createrid;
		}

		if ($this->mf_istree) {
			$this->fields[$this->mf_istree] = $this->fields['id'];
			$this->fields[$this->mf_istree]['attr'] = 'NOT NULL';
			if ($this->mf_use_charid)
				$this->fields[$this->mf_istree]['default'] = '';
			else
				$this->fields[$this->mf_istree]['default'] = '0';
			$this->index_fields[$this->mf_istree] = $this->mf_istree;
		}

		if ($this->mf_actctrl) {
			$this->fields[$this->mf_actctrl] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 1);
			$this->index_fields[$this->mf_actctrl] = $this->mf_actctrl;
		}

		if ($this->mf_timestamp)
			$this->fields['_timestamp'] = array('type' => 'timestamp', 'attr' => 'NOT NULL');
		if ($this->mf_timecr)
			$this->fields['mf_timecr'] = array('type' => 'int', 'width' => 11, 'attr' => 'unsigned NOT NULL', 'default' => '0');
		if ($this->mf_timeup)
			$this->fields['mf_timeup'] = array('type' => 'int', 'width' => 11, 'attr' => 'unsigned NOT NULL', 'default' => '0');
		if ($this->mf_timeoff)
			$this->fields['mf_timeoff'] = array('type' => 'int', 'width' => 11, 'attr' => 'unsigned NOT NULL', 'default' => '0');
		if ($this->mf_ipcreate)
			$this->fields['mf_ipcreate'] = array('type' => 'bigint', 'width' => 20, 'attr' => 'NOT NULL', 'default' => '0');

		/* if ($this->mf_typectrl)
		  $this->fields['typedata'] = array('type' => 'tinyint', 'attr' => 'unsigned NOT NULL');
		 */
		if ($this->mf_ordctrl) { //Содание полей для сортировки
			$this->fields[$this->mf_ordctrl] = array('type' => 'int', 'width' => '10', 'attr' => 'NOT NULL', 'default' => '0');
			$this->ordfield = $this->mf_ordctrl;
			$this->index_fields[$this->mf_ordctrl] = $this->mf_ordctrl;
		}

		//pagenum
		if (isset($_GET[$this->_cl . '_mop'])) {
			$this->messages_on_page = (int) $_GET[$this->_cl . '_mop'];
			if ($_COOKIE[$this->_cl . '_mop'] != $this->messages_on_page)
				_setcookie($this->_cl . '_mop', $this->messages_on_page, $this->_CFG['remember_expire']);
		}
		elseif (isset($_COOKIE[$this->_cl . '_mop']))
			$this->messages_on_page = (int) $_COOKIE[$this->_cl . '_mop'];
		if (!$this->messages_on_page)
			$this->messages_on_page = 20;
		// номер текущей страницы
		if (isset($_REQUEST[$this->_cl . '_pn']) && (int) $_REQUEST[$this->_cl . '_pn'])
			$this->_pn = (int) $_REQUEST[$this->_cl . '_pn'];
		elseif (isset($_REQUEST['_pn']) && (int) $_REQUEST['_pn'])
			$this->_pn = (int) $_REQUEST['_pn'];
		elseif ($this->reversePageN)
			$this->_pn = 0;
		else
			$this->_pn = 1;

		$this->attprm = array('type' => 'varchar(4)', 'attr' => 'NOT NULL DEFAULT \'\'');

		return true;
	}

	function _childs() {
		//$dir = dir($this->_CFG['_PATH']['ext'].$this->_cl.'.class');
		//while (false !== ($entry = $dir->read())) {
		//}
		if ($this->cf_childs and $this->config['childs']) {
			foreach ($this->config['childs'] as $r) {
				if (file_exists($this->_CFG['_PATH']['ext'] . $this->_cl . '.class/' . $r . '.childs.php'))
					$this->child_path[$r] = $this->_CFG['_PATH']['ext'] . $this->_cl . '.class/' . $r . '.childs.php';
//						include_once($this->_CFG['_PATH']['ext'].$this->_cl.'.class/'.$r.'.childs.php');
				elseif (file_exists($this->_CFG['_PATH']['extcore'] . $this->_cl . '.class/' . $r . '.childs.php'))
					$this->child_path[$r] = $this->_CFG['_PATH']['extcore'] . $this->_cl . '.class/' . $r . '.childs.php';
//					include_once($this->_CFG['_PATH']['extcore'].$this->_cl.'.class/'.$r.'.childs.php');
				$this->create_child($r);
			}
		}
	}

	protected function create_child($class_name) {
		$this->childs[$class_name] = true;
		$this->Achilds[$class_name] = true;
		//if ($this->_autoCheckMod)
		//	$this->childs[$class_name]->tablename;
		return true;
	}

	/**
	 * Делает запрос к тек таблице с выводом всех данных
	 *
	 * @param string $where - для дополнительного условия
	 * @return array
	 */
	public function _dump($where='') {
		$data = array();
		if (!isset($this->fields[$this->mf_namefields]))
			$name = 'id as name';
		else
			$name = '`' . $this->mf_namefields . '` as name';
		if($this->mf_istree)
			$name .= ', '.$this->mf_istree;
		if ($where != '')
			$where = ' WHERE ' . $where;
		$result = $this->SQL->execSQL('SELECT id, ' . $name . ' FROM `' . $this->tablename . '`' . $where);
		if (!$result->err) {
			if($this->mf_istree) {
				if($this->mf_use_charid)
					$data[''][''] = $this->getMess('_listroot');
				else
					$data[0][0] = $this->getMess('_listroot');
				while (list($id, $name,$pid) = $result->fetch_array(MYSQL_NUM)) {
					$data[$pid][$id] = $name;
				}
			}
			else {
				while (list($key, $value) = $result->fetch_array(MYSQL_NUM))
					$data[$key] = $value;
			}
		}
		return $data;
	}

	/**
	 * Функция аналогична _query(), только он принимает данные для запроса в виде параметров и возвращает рез-тат не использую $this->data
	 *
	 * @param string $list - выборка
	 * @param string $cls - строка запроса
	 * @param string $ord - позволяет группировать выходные данные по этому полю (1 уровень)
	 * @param string $ord2 - позволяет группировать выходные данные по этому полю (2 уровень)
	 * @return array
	 */
	public function _query($list='', $cls='', $ord='', $ord2='') { // this func. dont use $this->data
		$query = 'SELECT ';
		if (is_array($list))
			$query .= implode(', ', $list);
		elseif ($list)
			$query .= $list;
		else
			$query .= '*';
		$query .= ' FROM `' . $this->tablename . '` ';
		if ($cls)
			$query .= $cls;

		$result = $this->SQL->execSQL($query);
		if ($result->err)
			return false;
		$data = array();
		if ($ord != '' and $ord2 != '') {
			while ($row = $result->fetch_array())
				$data[$row[$ord2]][$row[$ord]] = $row;
		} elseif ($ord != '') {
			while ($row = $result->fetch_array())
				$data[$row[$ord]] = $row;
		} else {
			while ($row = $result->fetch_array())
				$data[] = $row;
		}
		if (count($data) and !$this->_select_attaches($data))
			return false;
		if (count($data) and !$this->_select_memos($data))
			return false;
		return $data;
	}

	/**
	 * Запрос к БД , использует $this->id если он есть в качестве выборки
	 * возвращает в массив $this->data
	 *
	 * @return bool - true если успех
	 */
	public function _select() {
		$data = array();
		$data = $this->_select_fields();
		if (count($data)) {
			$this->_select_attaches($data);
			$this->_select_memos($data);
			reset($data);
		}
		return $data;
	}

	private function _select_fields() {
		$data = array();
		$agr = ', ' . $this->_listnameSQL . ' as name';
		$pref = 'SELECT *' . $agr;
		$sql_query = $pref . ' FROM `' . $this->tablename . '`';
		if (isset($this->id) and $this->id)
			$sql_query .= ' WHERE id IN (' . $this->_id_as_string() . ')';
		if ($this->ordfield)
			$sql_query .= ' ORDER BY ' . $this->ordfield;
		$result = $this->SQL->execSQL($sql_query);
		if ($result->err)
			return $data;
		while ($row = $result->fetch_array())
			$data[$row['id']] = $row;
		return $data;
	}

	private function _select_attaches(&$data) {
		if (count($this->attaches) and count($data)) {
			$temp = current($data);
			if (!isset($temp['id']))
				return true;
			$merg = array_intersect_key($this->attaches, $temp);
			if (!count($merg))
				return true;
			foreach ($data as $ri => &$row) {
				foreach ($merg as $key => $value) {
					$row['_ext_' . $key] = $row[$key];
					$row[$key] = $this->_get_file($row['id'], $key, $row[$key]);
				}
			}
		}
		return true;
	}

	private function _select_memos(&$data='') {
		if (count($this->memos) and count($data)) {
			$temp = current($data);
			if (!isset($temp['id']))
				return true;
			foreach ($data as $ri => &$row) {
				foreach ($this->memos as $key => $value) {
					$f = $this->_CFG['_PATH']['path'] . $this->getPathForMemo($key) . '/' . $row['id'] . $this->text_ext;
					if (file_exists($f))
						$row[$key] = $f;
				}
			}
		}
		return true;
	}

	/**
	 * Функция добавления записей в бд
	 * В случае успеха выполняет allChangeData('add')
	 *
	 * @return bool
	 */
	protected function _add($flag_select=true) {
		$result = static_form::_add($this, $flag_select);
		if ($result)
			$this->allChangeData('add');
		return $result;
	}

	/**
	 * Обновление/изменение данных
	 *
	 * @return bool
	 */
	protected function _update($flag_select=true) {
		$result = static_form::_update($this, $flag_select);
		if ($result)
			$this->allChangeData('save');
		return $result;
	}

	/**
	 * Удаление данных
	 *
	 * @return bool
	 */
	public function _delete() {
		$result = static_form::_delete($this);
		if ($result)
			$this->allChangeData('delete');
		return $result;
	}

	/**
	 * Возвращает путь с дополненным параметром size
	 *
	 * @param string $file - относительный путь
	 * @return string - относительный путь
	 */
	public function _getPathSize($file) {
		if ($file and file_exists($this->_CFG['_PATH']['path'] . $file) and $size = @filesize($this->_CFG['_PATH']['path'] . $file))
			return $file . '?size=' . $size;
		return '';
	}

	/**
	 * Формирует путь к фаилу
	 *
	 * @param <type> $id
	 * @param <type> $key
	 * @param <type> $extValue
	 * @param <type> $modkey
	 * @return string
	 */
	public function _get_file($id, $key, $extValue='', $modkey=-1) {
		if (!$id)
			$id = $this->id;
		if (!$extValue and isset($this->data[$id]))
			$extValue = $this->data[$id]['_ext_' . $key];
		if (!$id or !$extValue or !$key)
			return '';
		$pref = '';
		if (isset($this->attaches[$key]['thumb'][$modkey]['pref']) && $this->attaches[$key]['thumb'][$modkey]['pref'])
			$pref = $this->attaches[$key]['thumb'][$modkey]['pref'];

		if (isset($this->attaches[$key]['thumb'][$modkey]['path']) && $this->attaches[$key]['thumb'][$modkey]['path'])
			$pathimg = $this->attaches[$key]['thumb'][$modkey]['path'] . '/' . $pref . $id . '.' . $extValue;
		elseif (isset($this->attaches[$key]['path']) and $this->attaches[$key]['path'])
			$pathimg = $this->attaches[$key]['path'] . '/' . $pref . $id . '.' . $extValue;
		else
			$pathimg = $this->getPathForAtt($key) . '/' . $pref . $id . '.' . $extValue;

		return $pathimg;
	}

	public function _prefixImage($path, $pref) {
		if (trim($path) != '') {
			$img = _substr($path, 0, strrpos($path, '/') + 1) . $pref . _substr($path, strrpos($path, '/') + 2 - count($path));
			if (file_exists($this->_CFG['_PATH']['path'] . _substr($img, 0, strrpos($img, '?'))))
				return $img;
		}
		return $path;
	}

	public function _id_as_string() {
		if (is_array($this->id)) {
			/* 	foreach($this->id as $key => $value)
			  $this->id[$key] = $value; */
			return '\'' . implode('\',\'', $this->id) . '\'';
		}
		else
			return '\'' . mysql_real_escape_string($this->id) . '\'';
	}

	public function _get_new_ord() {
		$query = 'SELECT max(' . 
			(($this->mf_use_charid and $this->mf_ordctrl)?$this->mf_ordctrl:'id')
			. ') FROM `' . $this->tablename . '`';
		if ($this->mf_istree and $this->parent_id and !$this->fld_data[$this->mf_istree])
			$query .= ' WHERE ' . $this->mf_istree . '=' . $this->parent_id;
		$result = $this->SQL->execSQL($query);
		if ($result->err)
			return 0;
		list($ordind) = $result->fetch_array(MYSQL_NUM);
		if ($ordind = (int) $ordind)
			$ordind++;
		return $ordind;
	}

	/* ------------- ORDER ORDER ORDER ORDER ---------------- */

	public function _sorting($arr) {
		if (!$this->mf_ordctrl)
			return static_main::_message('alert','Sorting denied!');
		foreach ($arr as $r) {
			$id = str_replace($this->_cl . '_', '', $r['id']);
			$id2 = str_replace($this->_cl . '_', '', $r['id2']);
			$data = array();
			$qr = 'select id,' . $this->mf_ordctrl . ' from `' . $this->tablename . '`';
			$result = $this->SQL->execSQL($qr);
			if ($result->err)
				return false;
			while ($row = $result->fetch_array()) {
				$data[$row['id']] = (int) $row[$this->mf_ordctrl];
			}
			$ex = 0;
			if ($r['t'] == 'next' and ($data[$id2] - 1) == $data[$id])
				$ex = 1;
			elseif ($r['t'] == 'prev' and ($data[$id2] + 1) == $data[$id])
				$ex = 1;
			if ($ex != 1) {
				$qr = 'UPDATE `' . $this->tablename . '` SET `' . $this->mf_ordctrl . '` = -2147483647 WHERE id=\'' . $id . '\'';
				$result = $this->SQL->execSQL($qr);
				if ($result->err)
					return false;

				if ($r['t'] == 'next' and $data[$id2] < $data[$id]) {
					$ord = $data[$id2];
					$qr = 'UPDATE `' . $this->tablename . '` SET `' . $this->mf_ordctrl . '` = (' . $this->mf_ordctrl . '+1) WHERE ' . $data[$id2] . '<=' . $this->mf_ordctrl . ' and ' . $this->mf_ordctrl . '<=' . $data[$id] . ' order by `' . $this->mf_ordctrl . '` DESC';
				} else {
					if ($r['t'] == 'next')
						$ord = $data[$id2] - 1;
					else
						$ord= $data[$id2];
					$qr = 'UPDATE `' . $this->tablename . '` SET `' . $this->mf_ordctrl . '` =(' . $this->mf_ordctrl . '-1) WHERE ' . $data[$id] . '<=`' . $this->mf_ordctrl . '` and `' . $this->mf_ordctrl . '`<=' . $ord . ' order by `' . $this->mf_ordctrl . '`';
				}
				$result = $this->SQL->execSQL($qr);
				if ($result->err)
					return false;

				$qr = 'UPDATE `' . $this->tablename . '` SET `' . $this->mf_ordctrl . '` = ' . $ord . ' WHERE `id`=\'' . $id . '\'';
				$result = $this->SQL->execSQL($qr);
				if ($result->err)
					return false;
			}
		}

		return static_main::_message('notice','Sorting the module `' . $this->caption . '` successful.');
	}

	/*	 * *********************** EVENTS ************************ */

	function getMess($name, $wrap=array(), $obj=NULL) {
		//global $this->_CFG;
		if (isset($this->locallang['default'][$name]))
			$text = $this->locallang['default'][$name];
		elseif (isset($this->_CFG['_MESS'][$name]))
			$text = $this->_CFG['_MESS'][$name];
		else
			$text = 'Внимание. Нейзвестный тип сообщения `' . $name . '`!';
		if (count($wrap))
			foreach ($wrap as $k => $r)
				$text = str_replace('###' . ($k + 1) . '###', $r, $text);
		return $text;
	}

	/*	 * ************************ADMIN-PANEL---FUNCTION************************ */

	public function fXmlModuls($modul) {
		include_once($this->_CFG['_PATH']['core'] . 'kernel.moderxml.php');
		return _fXmlModuls($this, $modul);
	}

	public function fXmlModulsTree($modul, $id) {
		include_once($this->_CFG['_PATH']['core'] . 'kernel.moderxml.php');
		return _fXmlModulsTree($this, $modul, $id);
	}

// *** PERMISSION ***//

	public function _prmModulAdd() {
		if (!$this->prm_add)
			return false;
		if (static_main::_prmModul($this->_cl, array(9)))
			return true;
		return false;
	}

	public function _prmModulEdit(&$data, &$param) {
		if (!$this->prm_edit)
			return false;
		if (isset($param['prm']) and count($param['prm'])) {
			foreach ($param['prm'] as $k => $r) {
				foreach ($data as $row)
					if (!isset($row[$k]) and $row[$k] != $r)
						return false;
			}
			return true;
		}
		if (static_main::_prmModul($this->_cl, array(3)))
			return true;
		if ($this->mf_createrid and isset($_SESSION['user']['id']) and static_main::_prmModul($this->_cl, array(4)) and $data[$this->mf_createrid] == $_SESSION['user']['id'])
			return true;
		return false;
	}

	public function _prmModulDel($dataList, $param=array()) {//$dataList нельзя по ссылке
		if (!$this->prm_del)
			return false;
		if (static_main::_prmModul($this->_cl, array(5)))
			return true;
		if ($this->mf_createrid and static_main::_prmModul($this->_cl, array(6))) {
			foreach ($dataList as $k => $r)
				if ($r[$this->mf_createrid] != $_SESSION['user']['id'])
					return false;
			return true;
		}
		return false;
	}

	public function _prmModulAct($dataList, $param=array()) {//$dataList нельзя по ссылке
		if (!$this->mf_actctrl)
			return false;
		if (static_main::_prmModul($this->_cl, array(7)))
			return true;
		if ($this->mf_createrid and static_main::_prmModul($this->_cl, array(8))) {
			foreach ($dataList as $k => $r)
				if ($r[$this->mf_createrid] != $_SESSION['user']['id'])
					return false;
			return true;
		}
		return false;
	}

	public function _prmModulShow($mn) {
		if (static_main::_prmModul($mn, array(1)))
			return false;
		if ($this->mf_createrid and static_main::_prmModul($mn, array(2)))
			return true;
		return false;
	}

	private function _prmSortField($key) {
		if (isset($this->fields_form[$key]['mask']['sort']))
			return true;
		elseif ($key == $this->mf_namefields or $key == 'ordfield' or $key == $this->mf_actctrl)
			return true;
		return false;
	}

// --END PERMISSION -----//
// MODUL configuration

	public function _checkmodstruct() {
		return static_tools::_checkmodstruct($this->_cl);
	}

	function setSystemFields() {
		$temp = $this->fields_form;
		$this->fields_form = array();
		foreach($temp as $k=>$r) {
			if($r['type']=='ckedit') {
				$this->fields[$k.'_ckedit'] = array('type' => 'tinyint', 'width'=>3, 'attr' => 'NOT NULL','default'=>'1');
				$this->fields_form[$k.'_ckedit'] = array('type' => 'list', 'listname'=>'wysiwyg', 'caption' => $r['caption'].' - Выбор редактора', 'mask' =>array(),'onchange'=>'SetWysiwyg(this)');
			}
			$this->fields_form[$k] = $r;
		}
	}

	public function toolsReinstall() {
		$this->form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(11)))
			$mess[] = array('name' => 'error', 'value' => $this->getMess('denied'));
		elseif (count($_POST) and $_POST['sbmt']) {
			static_tools::_reinstall($this);
			$mess[] = array('name' => 'ok', 'value' => $this->getMess('_reinstall_ok'));
		} else {
			$this->form['_*features*_'] = array('name' => 'Reinstall', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
			$this->form['_info'] = array(
				'type' => 'info',
				'caption' => $this->_CFG['_MESS']['_reinstall_info']);
			$this->form['sbmt'] = array(
				'type' => 'submit',
				'value' => $this->getMess('_submit'));
		}
		self::kFields2FormFields($this->form);
		return Array('form' => $this->form, 'messages' => $mess);
	}

	public function toolsConfigmodul() {
		$this->form = array();
		$arr = array('mess' => '', 'vars' => '');
		if (!static_main::_prmModul($this->_cl, array(13)))
			$arr['mess'][] = array('name' => 'error', 'value' => $this->getMess('denied'));
		elseif (!count($this->config_form)) {
			$this->form['_info'] = array(
				'type' => 'info',
				'caption' => $this->_CFG['_MESS']['_configno']);
		} else {
			foreach ($this->config as $k => &$r) {
				if (is_array($r) and !isset($this->config_form[$k]['multiple'])) {
					$temp = array();
					foreach ($r as $t => $d) {
						if (strpos($d, ':=') === false)
							$temp[] = trim($t) . ':=' . trim($d);
						else
							$temp[] = trim($d);
					}
					$r = implode(' :| ', $temp);
				}
			}
			if (count($_POST)) {
				$arr = $this->fFormCheck($_POST, $arr['vars'], $this->config_form);
				$config = array();
				foreach ($this->config_form as $k => $r) {
					if (isset($arr['vars'][$k])) {
						$this->config_form[$k]['value'] = $arr['vars'][$k];
						$config[$k] = $arr['vars'][$k];
					}
				}
				$this->config = $config;
				if (!count($arr['mess'])) {
					$arr['mess'][] = array('name' => 'ok', 'value' => $this->getMess('update'));
					static_tools::_save_config($config, $this->_file_cfg);
				}
			}
			static_tools::_xmlFormConf($this);
		}
		$this->kFields2FormFields($this->form);
		return Array('form' => $this->form, 'messages' => $arr['mess']);
	}

	public function staticStatsmodul($oid='') {
		return static_tools::_staticStatsmodul($this, $oid);
	}

	public function toolsCheckmodul() {
		return static_tools::_toolsCheckmodul($this);
	}

	/*
	  public function toolsReindex(){
	  $this->form = $mess = array();
	  if(!static_main::_prmModul($this->_cl,array(12)))
	  $mess[] = array('name'=>'error', 'value'=>$this->getMess('denied'));
	  elseif(count($_POST) and $_POST['sbmt']){
	  if(!$this->_reindex())
	  $mess[] = array('name'=>'error', 'value'=>$this->getMess('_reindex_ok'));
	  else
	  $mess[] = array('name'=>'error', 'value'=>$this->getMess('_reindex_err'));
	  }else{
	  $this->form['_*features*_'] = array('name'=>'reindex','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	  $this->form['_info'] = array(
	  'type'=>'info',
	  'caption'=>$this->getMess('_reindex_info'));
	  $this->form['sbmt'] = array(
	  'type'=>'submit',
	  'value'=>$this->getMess('_submit'));
	  }
	  self::kFields2FormFields($this->form);
	  return Array('form'=>$this->form, 'messages'=>$mess);
	  }

	  private function _reindex()
	  {
	  return true;
	  }
	 */

	public function _save_item($data) {
		foreach ($data as $k => $r) {
			if (isset($this->memos[$k]))
				$this->mmo_data[$k] = $r;
			elseif (isset($this->attaches[$k]))
				$this->att_data[$k] = $r;
			elseif (isset($this->fields[$k])) {
				$this->fld_data[$k] = (is_string($r) ? mysql_real_escape_string($r) : $r);
			}
		}
		return $this->_update();
	}

	public function _add_item($data) {
		foreach ($data as $k => $r) {
			if (isset($this->memos[$k]))
				$this->mmo_data[$k] = $r;
			elseif (isset($this->attaches[$k]))
				$this->att_data[$k] = $r;
			elseif (isset($this->fields[$k])) {
				$this->fld_data[$k] = (is_string($r) ? mysql_real_escape_string($r) : $r);
			}
		}
		return $this->_add();
	}

	//update modul item
	public function _UpdItemModul($param) {
		return include($this->_CFG['_PATH']['core'] . 'kernel.UpdItemModul.php');
	}

	public function kPreFields(&$data, &$param) {
		$this->setSystemFields();
		foreach ($this->fields_form as $k => &$r) {
			if (isset($r['readonly']) and $r['readonly'] and $this->id) // если поле "только чтение" и редактируется , то значение берем из БД,
				$data[$k] = (isset($this->data[$this->id][$k])?$this->data[$this->id][$k]:'');
			if (isset($r['mask']['eval']))
				$eval = $r['mask']['eval'];
			elseif (isset($r['mask']['evala']) and !$this->id)
				$eval = $r['mask']['evala'];
			elseif (isset($r['mask']['evalu']) and $this->id)
				$eval = $r['mask']['evalu'];
			elseif ((isset($r['mask']['fview']) and $r['mask']['fview'] == 2) or (isset($r['mask']['usercheck']) and !static_main::_prmGroupCheck($r['mask']['usercheck']))) {
				$r['mask']['fview'] = 2;
				unset($data[$k]);
				continue;
			}
			if (isset($eval)) {
				if (isset($data[$k]))
					$val = $data[$k];
				else
					$val = '';
				$eval = '$data[$k]=' . $eval;
				if (substr($eval, -1) != ';')
					$eval .= ';';
				eval($eval);
				unset($eval);
			}

			if (isset($this->attaches[$k]))
				$r = $r + $this->attaches[$k];
			if (isset($this->memos[$k]))
				$r = $r + $this->memos[$k];

			//на всякий
			if (!isset($r['mask']['max']) and isset($this->fields[$k]['width']))
				$r['mask']['max'] = $this->fields[$k]['width'];
			if (!isset($r['default']) and isset($this->fields[$k]['default']))
				$r['default'] = $this->fields[$k]['default'];

			if ($k == $this->owner_name and !isset($data[$k])) {
				if (!isset($this->owner->id) and $this->owner->mf_use_charid)
					$this->owner->id = '';
				elseif (!isset($this->owner->id))
					$this->owner->id = 0;
				$r['value'] = $this->owner->id;
			}
			elseif ($k == $this->mf_istree and !isset($data[$k])) {
				if (isset($this->parent_id) and $this->parent_id)
					$r['value'] = $this->parent_id;
				elseif (!isset($this->parent_id) and $this->mf_use_charid)
					$this->parent_id = '';
				elseif (!isset($this->parent_id))
					$this->parent_id = 0;
			}
			elseif ($r['type'] == 'ckedit') {
				if (isset($this->memos[$k]) and !count($_POST) and file_exists($data[$k]))
					$r['value'] = file_get_contents($data[$k]);
				elseif (isset($data[$k]))
					$r['value'] = $data[$k];
				else
					$r['value'] = '';
			}
			elseif (isset($r['multiple']) and $r['multiple'] > 0 and $r['type'] == 'list') {
				if (!is_array($data[$k])) {
					$data[$k] = trim($data[$k], '|');
					$r['value'] = explode('|', $data[$k]);
				}else
					$r['value'] = $data[$k];
			}
			elseif (isset($data[$k])) //  and $data[$k]
				$r['value'] = $data[$k];

			if (isset($this->id) and isset($this->data[$this->id]['_ext_' . $k]))
				$r['ext'] = $this->data[$this->id]['_ext_' . $k];

			if (!isset($r['comment']))
				$r['comment'] = '';

			//end foreach
		}
		if(count($this->formSort)) {
			$temp = $this->fields_form;
			$this->fields_form = array();
			foreach($this->formSort as $rr) {
				if($rr=='#over#') {
					$diffForm = array_diff_key($temp,array_keys($this->formSort));
					$this->fields_form = array_merge($this->fields_form,$diffForm);
				}
				elseif(isset($temp[$rr])) {
					$this->fields_form[$rr] = $temp[$rr];
				}
			}
		}
		if (count($this->fields_form) and !isset($_SESSION['user']['id']) or isset($param['captchaOn'])) {
			$this->fields_form['captcha'] = array(
				'type' => 'captcha',
				'caption' => $this->getMess('_captcha'),
				'captcha' => static_form::getCaptcha(),
				'src' => $this->_CFG['_HREF']['captcha'] . '?' . rand(0, 9999),
				'value' => (isset($data['captcha'])?$data['captcha']:''),
				'mask' => array('min' => 1));
		}

		$mess = array();
		if (isset($this->mess_form) and count($this->mess_form))
			$mess = $this->mess_form;
		if (!count($this->fields_form))
			$mess[] = array('name' => 'error', 'value' => $this->getMess('nodata'));
		if (isset($this->_CFG['hook']['kPreFields']))
			$this->__do_hook('kPreFields', func_num_args());
		return $mess;
	}

	/**
	 * $view [form,list]
	 */
	public function setFieldsForm() {
		///$this->fields_form = array();
		return true;
	}

	/*	 * ************************CLIENT---FUNCTION************************ */

	public function kFields2Form(&$param) {
		/*
		  $this->form['уник название'] = array(
		  обяз*	'type'=>'ТИП(submit,info,hidden,checkbox,list,int,text,textarea)',
		  обяз*	'value'=>'Значение',
		  'data'=>'значение масивов и пр формируемое отдельно',
		  'mask'=>array(
		  'name'=>'маски из $SQL->_masks',
		  'key'=>'регулярное выражение для проверки знач',
		  'strip'=>'(1-удаляет все теги,2- не удаляет теги, Иначе по умол - удаляет толко неразрешенные теги',
		  'max'=>100,
		  'min'=>2//0 -не обязательное поле,)
		  );
		 */
		if (!is_array($this->fields_form) or !count($this->fields_form))
			return false;
		$this->form = array();
		$this->form['_*features*_'] = array('type' => 'info', 'name' => $this->_cl, 'method' => 'post', 'id' => $this->id, 'action' => $_SERVER['REQUEST_URI']);
		$this->form['_info'] = array('type' => 'info', 'css' => 'caption');
		if ($this->id)
			$this->form['_info']['caption'] = $this->getMess('update_name', array($this->caption));
		else
			$this->form['_info']['caption'] = $this->getMess('add_name', array($this->caption));

		$this->kFields2FormFields($this->fields_form);
		if (!$this->id or (isset($this->data[$this->id]) and $this->_prmModulEdit($this->data[$this->id], $param))) {
			$this->form['sbmt'] = array(
				'type' => 'submit',
				'value_save' => ((isset($param['sbmtsave']) and $this->id) ? $this->getMess('_save') : ''),
				'value_close' => (isset($param['close']) ? $this->getMess('_close') : ''),
				'value' => $this->getMess('_saveclose')
			);
		}
		return true;
	}

	public function kFields2FormFields(&$fields) {
		return include($this->_CFG['_PATH']['core'] . 'kernel.kFields2FormFields.php');
	}

	public function fFormCheck(&$data, &$param, &$FORMS) {
		return static_form::_fFormCheck($this, $data, $param, $FORMS);
	}

	function kData2xml($DATA, $f='') {
		$XML = '';
		if ($f) {
			$f = str_replace('#', '', $f);
			$attr = '';
			$value = '';
			if (is_array($DATA)) {
				if (is_int(key($DATA))) {
					foreach ($DATA as $k => $r) {
						$attr = '';
						$value = '';
						if (is_array($r)) {
							foreach ($r as $m => $d) {
								if (is_array($d))
									$value .= $this->kData2xml($d, $m);
								elseif ($m == 'value')
									$value .= $d;
								elseif ($m == 'name')
									$value .= '<name><![CDATA[' . $d . ']]></name>';
								else
									$attr .= ' ' . str_replace('#', '', $m) . '="' . $d . '"';
							}
						}
						else
							$value = $r;
						$XML .= '<' . $f . $attr . '>' . $value . '</' . $f . ">\n";
					}
					//$XML = '<'.$f.$attr.'>'.$value.'</'.$f.'>';
				}
				else {
					foreach ($DATA as $k => $r) {
						if (is_array($r)) {
							$value .= $this->kData2xml($r, $k);
						} elseif ($k == 'value')
							$value .= $r;
						elseif ($k == 'name')
							$value .= '<name><![CDATA[' . $r . ']]></name>';
						else
							$attr .= ' ' . str_replace('#', '', $k) . '="' . $r . '"';
					}
					$XML = '<' . $f . $attr . '>' . $value . '</' . $f . '>';
				}
			}
		}
		return $XML;
	}

	public function _checkList(&$listname, $value=NULL) {
		$templistname = $listname;
		if (is_array($listname))
			$templistname = implode(',', $listname);
		if (!isset($this->_CFG['enum'][$templistname])) {
			$this->_getCashedList($listname, $value);
			//$this->_CFG['enum'][$templistname]
		}
		if (!$this->_CFG['enum'][$templistname] and $value)
			return false;

		if (!isset($this->_CFG['enum_check'][$templistname])) {
			if (!is_array($this->_CFG['enum'][$templistname]))
				return false;
			$temp2 = array();
			$temp = current($this->_CFG['enum'][$templistname]);
			if (is_array($temp) and !isset($temp['#name#'])) {
				foreach ($this->_CFG['enum'][$templistname] as $krow => $row) {
					if (isset($temp2[$krow])) {
						if (is_array($temp2[$krow]))
							$adname = $temp2[$krow]['#name#'];
						else
							$adname = $temp2[$krow];
						foreach ($row as $kk => $rr)
							$row[$kk] = $adname . ' - ' . $rr;
						if (is_array($temp2[$krow]) and isset($temp2[$krow]['#checked#']))
							unset($temp2[$krow]);
					}
					$temp2 += $row;
				}
				$this->_CFG['enum_check'][$templistname] = $temp2;
			}else
				$this->_CFG['enum_check'][$templistname] = &$this->_CFG['enum'][$templistname];
		}
		$temp = &$this->_CFG['enum_check'][$templistname];

		if (is_array($value)) {
			$return_value = array();
			foreach ($value as $r) {
				if (isset($temp[$r]))
					$return_value[] = $temp[$r];
			}
			if (count($return_value) == count($value))
				return $return_value;
		}
		elseif (isset($temp[$value])) {
			return $temp[$value];
		}
		return false;
	}

	public function _getCashedList(&$listname, $value=NULL) {
		$data = array();
		$templistname = $listname;
		if (is_array($listname))
			$templistname = implode(',', $listname);
		if (isset($this->_enum[$templistname])) {
			$this->_CFG['enum'][$templistname] = $this->_enum[$templistname];
		} elseif (!isset($this->_CFG['enum'][$templistname])) {
			$this->_CFG['enum'][$templistname] = $this->_getlist($listname, $value);
		}
		return $this->_CFG['enum'][$templistname];
	}

	public function _getlist(&$listname, $value=0) {/* LIST SELECTOR */
		include_once($this->_CFG['_PATH']['core'] . 'kernel.getlist.php');
		return _getlist($this, $listname, $value);
	}

////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
	/*
	  $param['xsl'] - шаблонизатор
	  $this->_cl - name текущего класса без _class
	  $this->_clp - построенный путь
	 */
////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	  $Ajax=0 - не скриптовая
	 */
	public function super_inc($param=array(), $ftype='') {
		global $HTML;
		$rep = array('\'', '"', '\\', '/');
		$cl = $this->_cl;
		$flag = 1;
		$xml = $messages = array();

		if (!isset($param['phptemplate']))
			$param['phptemplate'] = 'superlist';

		if ($this->owner and $this->owner->id and !$this->_clp)
			$this->_clp = $this->owner->_clp . $this->owner->_cl . '_id=' . $this->owner->id . '&amp;' . $this->owner->_cl . '_ch=' . $this->_cl . '&amp;';

		if ($this->_pn > 1)
			$this->_clp .= $cl . '_pn=' . $this->_pn . '&amp;';

		if (isset($_GET[$cl . '_id']) and !is_array($_GET[$cl . '_id'])) {
			if (!$this->mf_use_charid)
				$this->id = (int) $_GET[$cl . '_id'];
			else
				$this->id = str_replace($rep, '', $_GET[$cl . '_id']);
		}

		if (isset($param['firstpath']))
			$firstpath = $this->_CFG['_HREF']['BH'] . $param['firstpath'] . $this->_clp;
		else
			$firstpath = $this->_CFG['PATH']['wepname'] . '/index.php?' . $this->_clp;

		if ($this->id and $this->mf_istree) {
			$agr = ', ' . $this->_listnameSQL . ' as name';
			$this->tree_data = $first_data = $path2 = array();
			$parent_id = $this->id;
			$listfields = array('id,parent_id' . $agr);
			while ($parent_id) {
				$clause = 'WHERE id="' . $parent_id . '"';
				$this->data = $this->_query($listfields, $clause, 'id');
				if (!count($first_data))
					$first_data = $this->data;
				$this->tree_data += $this->data;
				$path2[$firstpath . $this->_cl . '_id=' . $this->data[$parent_id]['id'] . '&amp;'] = $this->caption . ': ' . $this->data[$parent_id][$this->_listname];
				if (isset($param['first_id']) and $param['first_id'] and $parent_id == $param['first_id'])
					break;
				$parent_id = $this->data[$parent_id][$this->mf_istree];
			}
			$this->data = $first_data;

			if (isset($param['first_id']) and $param['first_id'] and !$parent_id)
				$this->id = '';
			$path2 = array_reverse($path2);
		}
		elseif ($this->id) {
			$this->data = $this->_select();
		}
		if ($this->owner and $this->owner->id) {
			if ($this->owner->mf_istree)
				array_pop($HTML->path);
			$HTML->path[$firstpath] = $this->caption . ':' . $this->owner->data[$this->owner->id][$this->owner->_listname];
		}
		else
			$HTML->path[$firstpath] = $this->caption;
		if (isset($path2) and count($path2))
			$HTML->path = array_merge($HTML->path, $path2);

		if ($this->id and isset($_GET[$cl . '_ch']) and isset($this->childs[$_GET[$cl . '_ch']])) {
			if (count($this->data)) {
				//$HTML->path[$firstpath] =$this->caption.': '.$this->data[$this->id][$this->_listname];
				list($xml, $flag) = $this->childs[$_GET[$cl . '_ch']]->super_inc($param, $ftype);
				//	$tmp = $this->childs[$_GET[$cl.'_ch']]->_clp;
				//if(!isset($HTML->path[$this->_CFG['PATH']['wepname'].'/index.php?'.$tmp]))
				//	$HTML->path[$this->_CFG['PATH']['wepname'].'/index.php?'.$tmp] =$this->childs[$_GET[$cl.'_ch']]->caption;
			}
		} else {
			global $_tpl;
			if ($this->includeCSStoWEP and $this->config['cssIncludeToWEP']) {
				if (!is_array($this->config['cssIncludeToWEP']))
					$this->config['cssIncludeToWEP'] = explode('|', $this->config['cssIncludeToWEP']);
				if (count($this->config['cssIncludeToWEP'])) {
					foreach ($this->config['cssIncludeToWEP'] as $sr)
						$_tpl['styles'][$sr] = 1;
				}
			}
			if ($this->includeJStoWEP and $this->config['jsIncludeToWEP']) {
				if (!is_array($this->config['jsIncludeToWEP']))
					$this->config['jsIncludeToWEP'] = explode('|', $this->config['jsIncludeToWEP']);
				if (count($this->config['jsIncludeToWEP'])) {
					foreach ($this->config['jsIncludeToWEP'] as $sr)
						$_tpl['script'][$sr] = 1;
				}
			}
			$filter_clause = $this->_filter_clause();
			$param['clause'] = $filter_clause[0];

			$xml['topmenu'] = array();
			if ($this->_prmModulAdd())
				$xml['topmenu']['add'] = array(
					'href' => '_type=add' . (($this->id) ? '&amp;' . $this->_cl . '_id=' . $this->id : ''),
					'caption' => 'Добавить ' . $this->caption,
					'sel' => 0,
					'type' => '',
					'css' => 'add'
				);


			if ($this->owner and count($this->owner->childs))
				foreach ($this->owner->childs as $ck => $cn) {
					if (count($cn->fields_form) and $ck != $cl and $cn->_prmModulShow($ck))
						$xml['topmenu'][] = array(
							'href' => $this->_clp . $cl . '_id=' . $this->owner->id . '&amp;' . $cl . '_ch=' . $ck,
							'caption' => $cn->caption . '(' . $row[$ck . '_cnt'] . ')',
							'sel' => 0,
							'type' => 'child'
						);
				}
			if ($this->mf_istree and count($this->childs) and $this->id)
				foreach ($this->childs as $ck => $cn) {
					if (count($cn->fields_form) and $ck != $cl and $cn->_prmModulShow($ck))
						$xml['topmenu']['child' . $ck] = array(
							'href' => $this->_clp . $cl . '_id=' . $this->id . '&amp;' . $cl . '_ch=' . $ck,
							'caption' => $cn->caption . '(' . $row[$ck . '_cnt'] . ')',
							'sel' => 0,
							'type' => 'child'
						);
				}

			if (is_null($this->owner) and static_main::_prmModul($this->_cl, array(14)))
				$xml['topmenu']['Checkmodul'] = array(
					'href' => $this->_clp . '_type=tools&amp;_func=Checkmodul',
					'caption' => 'Обновить поля таблицы',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepchecktable',
				);

			if (isset($this->config_form) and count($this->config_form) and static_main::_prmModul($this->_cl, array(13)))
				$xml['topmenu']['Configmodul'] = array(
					'href' => $this->_clp . '_type=tools&amp;_func=Configmodul',
					'caption' => 'Настроика модуля',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepconfig',
				);
			if ($this->mf_indexing and static_main::_prmModul($this->_cl, array(12)))
				$xml['topmenu']['Reindex'] = array(
					'href' => $this->_clp . '_type=tools&amp;_func=Reindex',
					'caption' => 'Переиндексация',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepreindex',
				);
			if ($this->cf_reinstall and static_main::_prmModul($this->_cl, array(11)))
				$xml['topmenu']['Reinstall'] = array(
					'href' => $this->_clp . '_type=tools&amp;_func=Reinstall',
					'caption' => 'Переустановка',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepreinstall',
				);
			if ($filter_clause[1]) {
				$xml['topmenu']['Formfilter'] = array(
					'href' => $this->_clp . '_type=tools&amp;_func=Formfilter',
					'caption' => 'Фильтр',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepfilter',
				);
				if (count($filter_clause[0]) and isset($_SESSION['filter'][$this->_cl]) and count($_SESSION['filter'][$this->_cl])) {
					$_tpl['onload'] .= 'showHelp(\'.weptools.wepfilter\',\'Внимание! Включен фильтр.\',4000);$(\'.weptools.wepfilter\').addClass(\'weptools_sel\');';
				}
			}
			if ($this->mf_statistic) {
				$xml['topmenu']['Statsmodul'] = array(
					'href' => $this->_clp . '_type=static&amp;_func=Statsmodul' . (($this->owner and $this->owner->id) ? '&amp;_oid=' . $this->owner->id : ''),
					'caption' => 'Статистика',
					'sel' => 0,
					'type' => 'static',
					'css' => 'wepstats',
				);
			}

			if ($ftype == 'add') {
				$this->parent_id = $this->id;
				$this->id = NULL;
				list($xml['formcreat'], $flag) = $this->_UpdItemModul($param);
				if ($flag == 1 and isset($this->parent_id) and $this->parent_id)
					$this->id = $this->parent_id;
				//else
				$HTML->path[$firstpath . '_type=add' . (($this->parent_id) ? '&amp;' . $this->_cl . '_id=' . $this->parent_id : '')] = 'Добавить';
			}
			elseif ($ftype == 'edit' && $this->id) {
				if ($this->mf_istree)
					array_pop($HTML->path);
				$HTML->path[$firstpath . $this->_cl . '_id=' . $this->id . '&amp;_type=edit'] = 'Редактировать:<b>' . preg_replace($this->_CFG['_repl']['name'], '', $this->data[$this->id][$this->_listname]) . '</b>';
				list($xml['formcreat'], $flag) = $this->_UpdItemModul($param);
				if ($flag == 1) {
					if (isset($this->parent_id) and $this->parent_id)
						$this->id = $this->parent_id;
					if ($this->id)
						$this->_clp .= $this->_cl . '_id=' . $this->id;
				}
			}
			elseif ($ftype == 'act' && $this->id) {
				if ($this->mf_istree)
					array_pop($HTML->path);
				list($messages, $flag) = $this->_Act(1, $param);
				if ($this->mf_istree)
					$this->id = $this->data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'dis' && $this->id) {
				if ($this->mf_istree)
					array_pop($HTML->path);
				list($messages, $flag) = $this->_Act(0, $param);
				if ($this->mf_istree)
					$this->id = $this->tree_data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'ordup' && $this->id && $this->mf_ordctrl) {
				if ($this->mf_istree)
					array_pop($HTML->path);
				list($messages, $flag) = $this->_ORD(-1, $param);
				if ($this->mf_istree)
					$this->id = $this->data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'orddown' && $this->id && $this->mf_ordctrl) {
				if ($this->mf_istree)
					array_pop($HTML->path);
				list($messages, $flag) = $this->_ORD(1, $param);
				if ($this->mf_istree)
					$this->id = $this->tree_data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'del' && $this->id) {
				if ($this->mf_istree)
					array_pop($HTML->path);
				list($messages, $flag) = $this->_Del($param);
				if ($this->mf_istree)
					$this->id = $this->tree_data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'tools') {
				$xml['formtools'] = array();
				if (!isset($xml['topmenu'][$_REQUEST['_func']]))
					$xml['formtools']['messages'] = array(array('value' => 'Опция инструмента не найдена.', 'name' => 'error'));
				elseif (!method_exists($this, 'tools' . $_REQUEST['_func']))
					$xml['formtools']['messages'] = array(array('value' => 'Функция инструмента не найдена.', 'name' => 'error'));
				else
					eval('$xml[\'formtools\'] = $this->tools' . $_REQUEST['_func'] . '();');
			}
			elseif ($ftype == 'static') {
				$xml['static'] = array();
				if (!isset($xml['topmenu'][$_REQUEST['_func']]))
					$xml['messages'] = array(array('value' => 'Опция статики не найдена.', 'name' => 'error'));
				elseif (!method_exists($this, 'static' . $_REQUEST['_func']))
					$xml['messages'] = array(array('value' => 'Функция статики не найдена.', 'name' => 'error'));
				else {
					eval('$xml[\'static\'] = $this->static' . $_REQUEST['_func'] . '();');
				}
			} else {
				$flag = 3;
				$xml[$param['phptemplate']] = $this->_displayXML($param);
				$xml[$param['phptemplate']]['topmenu'] = &$xml['topmenu'];
			}
		}
		if (!isset($xml[$param['phptemplate']]['messages']))
			$xml[$param['phptemplate']]['messages'] = array();
		if (count($messages))
			$xml[$param['phptemplate']]['messages'] += $messages;
		$xml[$param['phptemplate']]['_cl'] = $cl;

		return array($xml, $flag);
	}

	public function _displayXML(&$param) {
		return include($this->_CFG['_PATH']['core'] . 'kernel.displayXML.php');
	}

	public function toolsFormfilter() {
		$this->form = array();
		/**
		 * очистка фильтра
		 * */
		if (isset($_REQUEST['f_clear_sbmt'])) {
			unset($_SESSION['filter'][$this->_cl]);
			$GLOBALS['_RESULT']['eval'] = 'window.location.href = \'' . $_SERVER['HTTP_REFERER'] . '\';';
		}
		/**
		 * задаются параметры фильтра
		 * */ elseif (isset($_REQUEST['sbmt'])) {
			$this->setFilter();
			$GLOBALS['_RESULT']['eval'] = 'window.location.href = \'' . $_SERVER['HTTP_REFERER'] . '\';';
		}
		else
			$this->Formfilter();
		return Array('filter' => $this->form, 'messages' => array());
	}

	function Formfilter() {
		$_FILTR = $_SESSION['filter'][$this->_cl];
		$this->form = array();
		foreach ($this->fields_form as $k => $r) {
			if ($r['mask']['filter'] == 1) {
				unset($r['default']);
				if ($r['type'] == 'list' && is_array($r['listname']) && !isset($r['listname']['idThis']))
					$r['listname']['idThis'] = $k;
				$this->form['f_' . $k] = $r;
				if (isset($_FILTR[$k])) {
					if (isset($_FILTR[$k . '_2']))
						$this->form['f_' . $k]['value_2'] = $_FILTR[$k . '_2'];
					$this->form['f_' . $k]['value'] = $_FILTR[$k];
				}
				if ($r['type'] == 'ajaxlist') {
					if (!$this->form['f_' . $k]['label'])
						$this->form['f_' . $k]['label'] = 'Введите текст';
					$this->form['f_' . $k]['labelstyle'] = ($_FILTR[$k] ? 'display: none;' : '');
					$this->form['f_' . $k]['csscheck'] = ($_FILTR[$k] ? 'accept' : 'reject');
				}
				elseif ($r['type'] != 'radio' and $r['type'] != 'checkbox' and $r['type'] != 'list' and $r['type'] != 'int' and $r['type'] != 'file' and $r['type'] != 'ajaxlist' and $r['type'] != 'date')
					$this->form['f_' . $k]['type'] = 'text';
				if (isset($_FILTR['exc_' . $k]))
					$this->form['f_' . $k]['exc'] = 1;
			}
		}
		//фильтр	
		if (count($this->form)) {
			$this->form['_*features*_'] = array('name' => 'Formfilter', 'action' => '', 'method' => 'post');
			$this->form['sbmt'] = array(
				'type' => 'submit',
				'value' => 'Отфильтровать');

			$this->kFields2FormFields($this->form);

			$this->form['f_clear_sbmt'] = array(
				'type' => 'info',
				'caption' => '<a href="' . $_SERVER['HTTP_REFERER'] . '" onclick="JSWin({\'insertObj\':\'#form_tools_Formfilter\',\'href\':$(\'#form_tools_Formfilter\').attr(\'action\'),\'data\':{ f_clear_sbmt:1}});return false;">Очистить</a>');
		}
		return $this->form;
	}

	function setFilter($flag=0) {
		if (isset($_REQUEST['f_clear_sbmt'])) {
			unset($_SESSION['filter'][$this->_cl]);
		} else {
			foreach ($this->fields_form as $k => $row) {
				if (isset($_REQUEST['f_' . $k]) && $_REQUEST['f_' . $k] != '' && isset($this->fields_form[$k]['mask']['filter'])) {
					$is_int = 0;
					if (!is_array($_REQUEST['f_' . $k])) {

						if ($row['type'] == 'date') {

							$_REQUEST['f_' . $k] = static_form::_get_fdate($row, $_REQUEST['f_' . $k], $this->fields[$k]['type']);
							if (isset($_REQUEST['f_' . $k . '_2']))
								$_REQUEST['f_' . $k . '_2'] = static_form::_get_fdate($row, $_REQUEST['f_' . $k . '_2'], $this->fields[$k]['type']);
						}

						$_SESSION['filter'][$this->_cl][$k] = mysql_real_escape_string($_REQUEST['f_' . $k]);
						if (isset($_REQUEST['f_' . $k . '_2']))
							$_SESSION['filter'][$this->_cl][$k . '_2'] = mysql_real_escape_string($_REQUEST['f_' . $k . '_2']);
					} else {
						$_SESSION['filter'][$this->_cl][$k] = array();
						if ($is_int)
							foreach ($_REQUEST['f_' . $k] as $row)
								$_SESSION['filter'][$this->_cl][$k][] = (int) $row;
						else
							foreach ($_REQUEST['f_' . $k] as $row)
								$_SESSION['filter'][$this->_cl][$k][] = mysql_real_escape_string($row);
					}
					if ($_REQUEST['exc_' . $k])
						$_SESSION['filter'][$this->_cl]['exc_' . $k] = 1;
					else
						unset($_SESSION['filter'][$this->_cl]['exc_' . $k]);
				} else if (!$flag)
					unset($_SESSION['filter'][$this->_cl][$k]);
			}
		}
	}

	/* вспомогательные функции */

	/**
	 * ФИЛЬТР в запросе
	 * */
	function _filter_clause() {
		$cl = $_FILTR = array();
		$flag_filter = 0;
		if (isset($_SESSION['filter'][$this->_cl]))
			$_FILTR = $_SESSION['filter'][$this->_cl];
		foreach ($this->fields_form as $k => $r) {
			if (isset($r['mask']['filter']) and $r['mask']['filter'] == 1) {
				if (isset($_FILTR[$k])) {
					$tempex = 0;
					if (isset($_FILTR['exc_' . $k]))
						$tempex = 1;
					if (is_array($_FILTR[$k]))
						$cl[$k] = 't1.' . $k . ' ' . ($tempex ? 'NOT' : '') . 'IN ("' . implode('","', $_FILTR[$k]) . '")';
					else {
						if ($this->fields_form[$k]['type'] == 'int') {
							if ($tempex)
								$cl[$k] = '(t1.' . $k . '<' . $_FILTR[$k] . ' or t1.' . $k . '>' . $_FILTR[$k . '_2'] . ')';
							else
								$cl[$k] = '(t1.' . $k . '>' . $_FILTR[$k] . ' and t1.' . $k . '<' . $_FILTR[$k . '_2'] . ')';
						}
						elseif ($this->fields_form[$k]['type'] == 'date') {
							if ($tempex)
								$cl[$k] = '(t1.' . $k . '<"' . $_FILTR[$k] . '" or t1.' . $k . '>"' . $_FILTR[$k . '_2'] . '")';
							else
								$cl[$k] = '(t1.' . $k . '>"' . $_FILTR[$k] . '" and t1.' . $k . '<"' . $_FILTR[$k . '_2'] . '")';
						}
						elseif ($this->fields_form[$k]['type'] == 'list') {
							if ($_FILTR[$k]) {
								$cl[$k] = 't1.' . $k . '="' . $_FILTR[$k] . '"';
							}
						} elseif ($_FILTR[$k] == '!0')
							$cl[$k] = 't1.' . $k . '!=""';
						elseif ($_FILTR[$k] == '!1')
							$cl[$k] = 't1.' . $k . '=""';
						else
							$cl[$k] = 't1.' . $k . ' ' . ($tempex ? 'NOT ' : '') . 'LIKE "' . $_FILTR[$k] . '"';
					}
				}
				$flag_filter = 1;
			}
		}
		return array($cl, $flag_filter);
	}

	function _moder_clause(&$param) {
		if (!isset($param['clause']) or !is_array($param['clause']))
			$param['clause'] = array();
		if ($this->mf_createrid and $this->_prmModulShow($this->_cl))
			$param['clause']['t1.' . $this->mf_createrid] = 't1.' . $this->mf_createrid . '="' . $_SESSION['user']['id'] . '"';
		if ($this->owner and $this->owner->id)
			$param['clause']['t1.' . $this->owner_name] = 't1.' . $this->owner_name . '="' . $this->owner->id . '"';
		if ($this->mf_istree) {
			if ($this->id)
				$param['clause']['t1.parent_id'] = 't1.parent_id="' . $this->id . '"';
			elseif (isset($param['first_id']))
				$param['clause']['t1.parent_id'] = 't1.id="' . $param['first_id'] . '"';
			elseif (isset($param['first_pid']))
				$param['clause']['t1.parent_id'] = 't1.parent_id="' . $param['first_id'] . '"';
			elseif ($this->mf_use_charid)
				$param['clause']['t1.parent_id'] = 't1.parent_id=""';
			else
				$param['clause']['t1.parent_id'] = 't1.parent_id=0';
			if ($this->owner and $this->owner->id and ($this->id or $param['first_pid']))
				unset($param['clause']['t1.' . $this->owner_name]);
		}
		//if(isset($this->fields['region_id']) and isset($_SESSION['city']))///////////////**********************
		//	$param['clause']['t1.region_id'] ='t1.region_id='.$_SESSION['city'];

		//if (isset($_GET['_type']) and $_GET['_type'] == 'deleted' and $this->fields_form[$this->mf_actctrl]['listname'] == $this->mf_actctrl)
		//	$param['clause']['t1.' . $this->mf_actctrl] = 't1.' . $this->mf_actctrl . '=4';
		elseif (isset($this->fields_form[$this->mf_actctrl]['listname']) and $this->fields_form[$this->mf_actctrl]['listname'] == $this->mf_actctrl)
			$param['clause']['t1.' . $this->mf_actctrl] = 't1.' . $this->mf_actctrl . '!=4';
		return $param['clause'];
	}

	private function _tr_attribute(&$row, &$param) {
		$xml = array();
		if ($this->_prmModulEdit($row, $param))
			$xml['edit'] = true;
		else
			$xml['edit'] = false;
		if ($this->_prmModulDel(array($row), $param))
			$xml['del'] = true;
		else
			$xml['del'] = false;
		if ($this->_prmModulAct(array($row), $param))
			$xml['act'] = true;
		else
			$xml['act'] = false;
		return $xml;
	}

	/* Активация */

	public function _Act($act, &$param) {
		$flag = 1;
		$xml = array();
		if ($param['mess'])
			$xml = $param['mess'];
		$param['act'] = $act;
		$this->data = $this->_select();
		if ($this->_prmModulAct($this->data, $param)) {
			$this->fld_data = array();
			$act = (int) $act;
			if ($this->fields[$this->mf_actctrl]['type'] == 'bool')
				$this->fld_data[$this->mf_actctrl] = $act;
			else {
				if (static_main::_prmModul($this->_cl, array(7))) {
					if ($act == 0)
						$this->fld_data[$this->mf_actctrl] = 6;
					else
						$this->fld_data[$this->mf_actctrl] = 1;
				}
				elseif ($act == 1)
					$this->fld_data[$this->mf_actctrl] = 5;
				else
					$this->fld_data[$this->mf_actctrl] = 2;
			}

			if ($this->_update()) {
				if ($this->fld_data[$this->mf_actctrl] == 5)
					$xml[] = array('value' => $this->getMess('act5'), 'name' => 'ok');
				if ($this->fld_data[$this->mf_actctrl] == 6)
					$xml[] = array('value' => $this->getMess('act6'), 'name' => 'ok');
				elseif ($act)
					$xml[] = array('value' => $this->getMess('act1'), 'name' => 'ok');
				else
					$xml[] = array('value' => $this->getMess('act0'), 'name' => 'ok');
				$flag = 0;
			}
			else
				$xml[] = array('value' => $this->getMess('update_err'), 'name' => 'error');
		}
		else
			$xml[] = array('value' => $this->getMess('denied'), 'name' => 'error');
		return array($xml, $flag);
	}

	public function _ORD($ord, &$param) {
		$flag = 1;
		$xml = array();
		if ($param['mess'])
			$xml = $param['mess'];
		$this->data = $this->_select();
		if ($this->_prmModulEdit($this->data, $param)) {
			$this->fld_data = array();
			$this->fld_data[$this->mf_ordctrl] = $this->data[$this->id][$this->mf_ordctrl]+$ord;

			if ($this->_update()) {
				if ($ord<0)
					$xml[] = array('value' => 'UP', 'name' => 'ok');
				else
					$xml[] = array('value' => 'DOWN', 'name' => 'ok');
				$flag = 0;
			}
			else
				$xml[] = array('value' => $this->getMess('update_err'), 'name' => 'error');
		}
		else
			$xml[] = array('value' => $this->getMess('denied'), 'name' => 'error');
		return array($xml, $flag);
	}


////////////// -------DELETE---------------

	public function _Del($param) {
		$flag = 1;
		$xml = array();
		if ($param['mess'])
			$xml = $param['mess'];
		$this->data = $this->_select();
		if (count($this->data) and $this->_prmModulDel($this->data, $param)) {
			if (isset($this->fields[$this->mf_actctrl]) and $this->fields[$this->mf_actctrl]['type'] != 'bool') {
				$this->fld_data[$this->mf_actctrl] = 4;
				if ($this->_update()) {
					$xml[] = array('value' => $this->getMess('deleted'), 'name' => 'ok');
					$flag = 0;
				}else
					$xml[] = array('value' => $this->getMess('del_err'), 'name' => 'error');
			}else {
				if ($this->_delete()) {
					$xml[] = array('value' => $this->getMess('deleted'), 'name' => 'ok');
					$flag = 0;
				}else
					$xml[] = array('value' => $this->getMess('del_err'), 'name' => 'error');
			}
		}
		else
			$xml[] = array('value' => $this->getMess('denied'), 'name' => 'error');
		return array($xml, $flag);
	}

	function allChangeData($type='', $data='') {
		return true;
	}

	/* TREE CREATOR */

	public function _forlist(&$data, $id, $select='') {
		/*
		  array('name'=>'NAME','id'=>1 [, 'sel'=>0, 'checked'=>0])
		 */
		//$select - array(значение=>1)
		$s = array();
		if (isset($data[$id]) and is_array($data[$id]) and count($data[$id]))
			foreach ($data[$id] as $key => $value) {
				if ($select != '' and is_array($select)) {
					if (isset($select[$key]))
						$sel = 1;
					else
						$sel = 0;
				}
				elseif ($select != '' and $select == $key)
					$sel = 1;
				else
					$sel = 0;
				$s[$key] = array('#id#' => $key, '#sel#' => $sel);
				if (is_array($value)) {
					foreach ($value as $k => $r)
						if ($k != '#name#' and $k != '#id#')
							$s[$key][$k] = $r;
					if (!isset($value['#name#']))
						$s[$key]['#name#'] = $key;
					else
						$s[$key]['#name#'] = $value['#name#']; //_substr($value['name'],0,60).(_strlen($value['name'])>60?'...':'')
				}else
					$s[$key]['#name#'] = $value;
				if ($key != $id and isset($data[$key]) and count($data[$key]) and is_array($data[$key]))
					$s[$key]['#item#'] = $this->_forlist($data, $key, $select);
			}
		return $s;
	}

	public function path2xsl($path) {
		$xml = '<path>';
		foreach ($path as $key => $value) {
			if (is_int($key))
				$href = '<href></href>';
			else
				$href = '<href>' . $key . '</href>';

			$xml.= '<item>' . $href . '<name><![CDATA[' . $value . ']]></name></item>';
		}
		$xml .= '</path>';
		return $xml;
	}

	public function fPageNav($countfield, $thisPage='', $flag=0) {
		//$countfield - бщее число элем-ов
		//$thisPage - по умол тек путь к странице
		//$this->messages_on_page - число эл-ов на странице
		//$this->_pn - № текущей страницы
		//$flag  - опция для paginator, 0 - если номер страницы перед list_2.html , 1 - после ?_pn=1
		$DATA = array('cnt' => $countfield, 'cntpage' => ceil($countfield / $this->messages_on_page), 'modul' => $this->_cl, 'reverse' => $this->reversePageN);
		$numlist = $this->numlist;
		if (($this->messages_on_page * ($this->_pn - 1)) > $countfield) {
			$this->_pn = $DATA['cntpage'];
		}

		foreach ($this->_CFG['enum']['_MOP'] as $k => $r)
			$DATA['mop'][$k] = array('value' => $r, 'sel' => 0);
		$DATA['mop'][$this->messages_on_page]['sel'] = 1;

		if (!$countfield || $countfield <= $this->messages_on_page || !$this->messages_on_page || $this->_pn > $DATA['cntpage'] || $this->_pn < 1)
			return $DATA;
		else {
			if ($thisPage == '')
				$thisPage = $_SERVER['REQUEST_URI'];
			if (strstr($thisPage, '&')) {
				$thisPage = str_replace('&amp;', '&', $thisPage);
				$thisPage = str_replace('&', '&amp;', $thisPage);
			}

			if ($this->reversePageN) {// обратная нумирация
				$DATA['cntpage'] = floor($countfield / $this->messages_on_page);
				$temp_pn = $this->_pn;
				$this->_pn = $DATA['cntpage'] - $this->_pn + 1;
				if (!$flag and strpos($thisPage, '.html')) {
					$pregreplPage = '/(_p)[0-9]*/';
					if (!preg_match($pregreplPage, $thisPage)) {
						$thisPage = str_replace('.html', '_p' . $this->_pn . '.html', $thisPage);
					}
				} else {
					$pregreplPage = '/(' . $this->_cl . '_pn=)[0-9]*/';
					if (!preg_match($pregreplPage, $thisPage)) {
						if (_substr($thisPage, -5) != '&amp;')
							$thisPage .= '&amp;';
						$thisPage .= $this->_cl . '_pn=' . $this->_pn;
					}
				}
				for ($i = $DATA['cntpage']; $i > 0; $i--) {
					if ($i == $this->_pn)
						$DATA['link'][] = array('value' => $i, 'href' => 'select_page');
					else
						$DATA['link'][] = array('value' => $i, 'href' => preg_replace($pregreplPage, ($i == $DATA['cntpage'] ? '' : "\${1}" . $i), $thisPage));
				}
			} else {

				if (!$flag and strpos($thisPage, '.html')) {
					$replPage = '_p' . $this->_pn;
					$pregreplPage = '/_p' . $this->_pn . '/';
					$inPage = '_p';
				} else {
					$replPage = $this->_cl . '_pn=' . $this->_pn;
					$pregreplPage = '/' . $this->_cl . '_pn=[0-9]*(&amp;)*/';
					$inPage = $this->_cl . '_pn=';
				}
				if (strpos($thisPage, $replPage) === false) {
					if (!$flag and strpos($thisPage, '.html')) {
						$pageSuf = _substr($thisPage, strpos($thisPage, '.html') + 5);
						$thisPage = _substr($thisPage, 0, strpos($thisPage, '.html')) . '_p1.html' . $pageSuf;
					} else {
						if (_substr($thisPage, -5) == '&amp;')
							$thisPage .= $replPage;
						elseif (strpos($thisPage, '?') === false)
							$thisPage .= '?' . $replPage;
						else
							$thisPage .='&amp;' . $replPage;
					}
				}
				if (($this->_pn - $numlist) > 1) {
					$DATA['link'][] = array('value' => 1, 'href' => preg_replace($pregreplPage, '', $thisPage));
					$DATA['link'][] = array('value' => '...', 'href' => '');
					$j = $this->_pn - $numlist;
				} else {
					$j = 1;
				}
				for ($i = $j; $i <= $this->_pn + $numlist; $i++)
					if ($i <= ($DATA['cntpage']))
						if ($i == $this->_pn)
							$DATA['link'][] = array('value' => $i, 'href' => 'select_page');
						else
							$DATA['link'][] = array('value' => $i, 'href' => preg_replace($pregreplPage, ($i == 1 ? '' : $inPage . $i . '\\1'), $thisPage));
				if ($this->_pn + $numlist < $DATA['cntpage']) {
					$DATA['link'][] = array('value' => '...', 'href' => '');
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => str_replace($replPage, $inPage . $DATA['cntpage'], $thisPage));
				}
			}
			$DATA['_pn'] = $this->_pn;
			//////////////////
		}

		return $DATA;
	}

	public function getPathForAtt($key) {
		if (isset($this->attaches[$key]['path']) and $this->attaches[$key]['path'])
			$pathimg = $this->attaches[$key]['path'];
		else
			$pathimg = $this->_CFG['PATH']['content'] . $key;
		return $pathimg;
	}

	public function getPathForMemo($key) {
		if (isset($this->memos[$key]['path']) and $this->memos[$key]['path'])
			$pathimg = $this->memos[$key]['path'];
		else
			$pathimg = $this->_CFG['PATH']['content'] . $key;
		return $pathimg;
	}

}

//// Kernel END


class modul_child extends ArrayObject {

	function __construct(&$obj) {
		$this->modul_obj = $obj;
	}

	function getIterator() {
		$iterator = parent::getIterator();
		while ($iterator->valid()) {
			$key = $iterator->key();
			if ($iterator->current() === true) {
				$this->offsetSet($key, $this->offsetGet($key));
			}
			$iterator->next();
		}
		return $iterator;
	}

	function offsetGet($index) {
		global $_CFG;
		/*if (isset($_CFG['modulprm_ext'][$index]) && isset($_CFG['modulprm'][$index]) && !$_CFG['modulprm'][$index][$this->modul_obj->mf_actctrl])
			$clname = $_CFG['modulprm_ext'][$index][0];
		else
			$clname = $index;*/
		$clname = _getExtMod($index);
		$value = parent ::offsetGet($clname);
		if ($this->offsetExists($clname) && $value === true) {
			if (isset($this->modul_obj->child_path[$clname])) {
				require_once $this->modul_obj->child_path[$clname];
			}
			$modul_child = NULL;
			if (!_new_class($clname, $modul_child, $this->modul_obj))
				return false;
			//$this->modul_obj->childs[$index] = $modul_child;
			return $modul_child;
		} else {
			//если один и тот же клас исползуется в как ребенок в других классах, то $this->singleton = false; вам в помощь, иначе сюда будут выдаваться ссылки на класс созданный в первы раз для другого модуля
		}

		return $value;
	}

}

