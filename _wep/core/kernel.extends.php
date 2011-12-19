<?php

/* VERSION=2 */
/* COMMENT=Ядро, дополняющая модули */

abstract class kernel_extends {
	/*
	 * версия ядра
	 *  нумерация отличает от других версией
	 * 1 - структурной не совместимостью, различия в хранении данных и в исполняемых функциях, вызывающие критические ошибки в коде
	 * 2 - добавленн новый функционал, расширен и измененн меющиеся функции -
	 * 3 - Номер ревизии , исправленны ошибки
	 */
	const versionCore = '2.8.20';

	function __construct($owner=NULL) {
		global $_CFG;
		//FB::info($_CFG);
		$this->_CFG = true; // баг ПХП
		$this->_CFG = &$_CFG; //Config
		$this->cfg_sql = $this->_CFG['sql'];

		$this->owner = true;
		if(is_object($owner) and isset($owner->fields))
			$this->owner = &$owner; //link to owner class
		else
			$this->owner = NULL;
		$this->_set_features(); // настройки модуля

		if ($this->singleton == true)
			$_CFG['singleton'][$this->_cl] = &$this;

		$this->_create_conf(); // загрузки формы конфига
		if (isset($this->config_form) and count($this->config_form)) { // загрузка конфига из файла для модуля
			$this->configParse();
		}
		$this->_create(); // предустановки модуля
		$this->_childs();
		if (isset($this->_CFG['hook']['__construct']))
			$this->__do_hook('__construct', func_get_args());
	}

	function __destruct() {

	}

	function __get($name) {
		global $_CFG,$SQL;
		if ($name == 'SQL') {
			if (!$this->grant_sql) {
				if (!isset($SQL) or !$SQL->ready) {
					$SQL = new sql($_CFG['sql']);
				}
				$this->SQL = &$SQL;
				return $SQL;
			} else {
				return new sql($this->cfg_sql);
			}
		} elseif ($name == 'fields_form') {
			$this->getFieldsForm(-1);
			return $this->fields_form;
		}
		trigger_error('Своиство '.$name.' не найдено в классе '.$this->_cl, E_USER_NOTICE);
		return NULL;
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
		$this->numlist = 10; //максим число страниц при котором отображ все номера страниц
		$this->mf_indexing = false; // TOOLS индексация
		$this->mf_statistic = false; // TOOLS показывать  статистику по дате добавления
		$this->cf_childs = false; // TOOLS true - включить управление подключение подмодулей в настройках модуля
		$this->cf_reinstall = false; // TOOLS
		$this->includeJStoWEP = false; // подключать ли скрипты для формы через настройки
		$this->includeCSStoWEP = false; // подключать ли стили для формы через настройки
		$this->singleton = true; // класс-одиночка
		$this->ver = '0.1.1'; // версия модуля
		$this->RCVerCore = self::versionCore;
		$this->icon = 0; /* числа  означают отсуп для бэкграунда, а если будет задан текст то это сам рисунок */
		$this->default_access = '|0|';

		$this->text_ext = '.txt'; // расширение для memo фиаилов

		$this->_cl = str_replace('_class', '', get_class($this)); //- символическое id модуля
		$this->owner_name = 'owner_id'; // название поля для родительской связи в БД
		$this->tablename = $this->_CFG['sql']['dbpref'] . $this->_cl; // название таблицы
		$this->tableEngine = 'MyISAM';
		$this->caption = $this->_cl; // заголовок модуля
		$this->_listfields = array('name'); //select по умолч
		$this->unique_fields =
				$this->index_fields =
				$this->_enum =
				$this->update_records =
				$this->def_records =
				$this->fld_data =
				$this->fields =
				$this->form =
				$this->formSort = 
				$this->formDSort =
				$this->mess_form =
				$this->attaches = $this->att_data =
				$this->memos = $this->mmo_data =
				$this->services =
				$this->config =
				$this->config_form =
				$this->lang =
				$this->enum =
				$this->child_path =
				$this->Achilds =
				$this->_setHook = //Перехватчик в предустановки модуля
				$this->HOOK = //перехватчик в реальном времени
				$this->_AllowAjaxFn =  // разрешённые функции для аякса, нужно прописывать в индекс
				$this->cron = // Задания на кроне time,file,modul,function,active,
				$this->_dependClass = 
				 array();
		$this->childs = new modul_child($this);
		$this->ordfield = '';
		$this->_clp = array();
		$this->data = array();
		$this->parent_id = NULL;
		$this->null = NULL;

		$this->grant_sql = false;
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
		if (is_bool($this->mf_timecr) and $this->mf_timecr)
			$this->mf_timecr = 'mf_timecr';
		if (is_bool($this->mf_timestamp) and $this->mf_timestamp)
			$this->mf_timestamp = 'mf_timestamp';
		if (is_bool($this->mf_ipcreate) and $this->mf_ipcreate)
			$this->mf_ipcreate = 'mf_ipcreate';

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
			$this->fields[$this->mf_timestamp] = array('type' => 'timestamp', 'attr' => 'NOT NULL');
		if ($this->mf_timecr)
			$this->fields[$this->mf_timecr] = array('type' => 'int', 'width' => 11, 'attr' => 'unsigned NOT NULL', 'default' => '0');
		if ($this->mf_timeup)
			$this->fields['mf_timeup'] = array('type' => 'int', 'width' => 11, 'attr' => 'unsigned NOT NULL', 'default' => '0');
		if ($this->mf_timeoff)
			$this->fields['mf_timeoff'] = array('type' => 'int', 'width' => 11, 'attr' => 'unsigned NOT NULL', 'default' => '0');
		if ($this->mf_ipcreate)
			$this->fields[$this->mf_ipcreate] = array('type' => 'bigint', 'noquote'=>true, 'width' => 20, 'attr' => 'NOT NULL', 'default' => '0');

		/* if ($this->mf_typectrl)
		  $this->fields['typedata'] = array('type' => 'tinyint', 'attr' => 'unsigned NOT NULL');
		 */
		if ($this->mf_ordctrl) { //Содание полей для сортировки
			$this->fields[$this->mf_ordctrl] = array('type' => 'int', 'width' => '10', 'attr' => 'NOT NULL', 'default' => '0');
			$this->ordfield = $this->mf_ordctrl;
			$this->index_fields[$this->mf_ordctrl] = $this->mf_ordctrl;
			$this->_AllowAjaxFn['_sorting'] = true;
		}

		$this->attprm = array('type' => 'varchar(4)', 'attr' => 'NOT NULL DEFAULT \'\'');

		$this->_pa = $this->_cl .'_pn'; // ключ страницы в ссылке
		// номер текущей страницы
		if (isset($_REQUEST[$this->_pa]) && (int) $_REQUEST[$this->_pa])
			$this->_pn = (int) $_REQUEST[$this->_pa];
		elseif (isset($_REQUEST['_pn']) && (int) $_REQUEST['_pn'])
			$this->_pn = (int) $_REQUEST['_pn'];
		elseif ($this->reversePageN)
			$this->_pn = 0;
		else
			$this->_pn = 1;

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
				elseif (file_exists($this->_CFG['_PATH']['wep_ext'] . $this->_cl . '.class/' . $r . '.childs.php'))
					$this->child_path[$r] = $this->_CFG['_PATH']['wep_ext'] . $this->_cl . '.class/' . $r . '.childs.php';
//					include_once($this->_CFG['_PATH']['wep_ext'].$this->_cl.'.class/'.$r.'.childs.php');
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
					$data[''][''] = static_main::m('_listroot',$this);
				else
					$data[0][0] = static_main::m('_listroot',$this);
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
	 * Экранирует специальные символы в строках для использования в выражениях SQL
	 */
	public function SqlEsc($val) {
		return $this->SQL->SqlEsc($val);
	}

	/**
	 * принимает данные для запроса в виде параметров и возвращает рез-тат 
	 * Функция аналогична _select(), только он принемает и передает данные непосредственно
	 * @param string $list - выборка
	 * @param string $cls - строка запроса
	 * @param string $ord - позволяет группировать выходные данные по этому полю (1 уровень)
	 * @param string $ord2 - позволяет группировать выходные данные по этому полю (2 уровень)
	 * @return array
	 */
	public function _query($list='', $cls='', $ord='', $ord2='',$debug=false) { // this func. dont use $this->data
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
		if($debug) print_r($query.' <br>');
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
			trigger_error('Ошибка выборки ATTACHES в классе '.$this->_cl, E_USER_WARNING);
		if (count($data) and !$this->_select_memos($data))
			trigger_error('Ошибка выборки MEMOS в классе '.$this->_cl, E_USER_WARNING);
		if (isset($this->_CFG['hook']['_query']))
			$this->__do_hook('_query', func_get_args());
		return $data;
	}
	/**
	 * Синоним _query
	 */
	public function qs($list='', $cls='', $ord='', $ord2='',$debug=false) {
		return $this->_query($list, $cls, $ord, $ord2,$debug);
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
					if(!$row[$key]) continue;
					$row['_ext_' . $key] = $row[$key];
					$row[$key] = $this->_get_file($row['id'], $key, $row[$key]);
					//$row[$key] = $this->_get_file($row['id'], $key, $row[$key]);
					if(isset($value['thumb'])) {
						foreach($value['thumb'] as $kk=>$rr) {
							if(isset($rr['pref']) and $rr['pref']) {
								$row[$rr['pref'].$key] = $this->_get_file($row['id'], $key, $row['_ext_' . $key],$kk);
								$row['#'.$rr['pref'].$key] = $rr;
							}
						}
					}
				}
			}//end foreach
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
	 * Сохранение данных form , Функция добавления записей в бд
	 * В случае успеха выполняет allChangeData('add')
	 *
	 * @return bool
	 */
	public function _add($data=array(),$flag_select=true) {
		if(is_bool($data)) {
			$flag_select = $data;
			trigger_error('Устаревший вариант вызова функция _add', E_USER_WARNING);
		} else {
			$this->fld_data = $this->att_data = $this->mmo_data = array();
			foreach ($data as $k => $r) {
				if (isset($this->memos[$k]))
					$this->mmo_data[$k] = $r;
				elseif (isset($this->attaches[$k]))
					$this->att_data[$k] = $r;
				elseif (isset($this->fields[$k])) {
					$this->fld_data[$k] = $r;
				}
			}
		}
		$result = static_form::_add($this, $flag_select);
		if ($result)
			$this->allChangeData('add');
		return $result;
	}

	function _add_item($data,$flag_select=true) {
		trigger_error('Устаревший функция _add_item -> заменить на _add', E_USER_WARNING);
		return $this->_add($data, $flag_select);;
	}

	/**
	 * Сохранение данных formФункция добавления записей в бд
	 * В случае успеха выполняет allChangeData('add')
	 *
	 * @return bool
	 */
	public function _addUp($data,$flag_select=true) {
		$this->fld_data = $this->att_data = $this->mmo_data = array();
		foreach ($data as $k => $r) {
			if (isset($this->memos[$k]))
				$this->mmo_data[$k] = $r;
			elseif (isset($this->attaches[$k]))
				$this->att_data[$k] = $r;
			elseif (isset($this->fields[$k])) {
				$this->fld_data[$k] = $r;
			}
		}
		$result = static_form::_add($this, $flag_select, true);
		if ($result)
			$this->allChangeData('add');
		return $result;
	}

	/**
	 * Обновление данных form
	 * for $where=false -> id= $this->id
	 * @param array $data 
	 * @param string $where 
	 * @return array
	*/
	protected function _update($data=array(),$where=false,$flag_select=true) {
		if(is_bool($data)) {
			trigger_error('Устаревший метод вызова _save_item -> первый параметр $data', E_USER_WARNING);
			$where = $flag_select;
			$flag_select = $data;
		}
		else {
			if(!count($data)) {
				trigger_error('Устаревший метод вызова _update -> первый параметр $data', E_USER_WARNING);
				if(!count($this->fld_data))
					return false;
			} else {
				$this->fld_data = $this->att_data = $this->mmo_data = array();
				foreach ($data as $k => $r) {
					if (isset($this->memos[$k]))
						$this->mmo_data[$k] = $r;
					elseif (isset($this->attaches[$k]))
						$this->att_data[$k] = $r;
					elseif (isset($this->fields[$k]))
						$this->fld_data[$k] = $r;
				}
			}
		}
		$result = static_form::_update($this, $flag_select,$where);
		if($result) $this->allChangeData('save');
		return $result;
	}

	public function _save_item($data,$where=false) {
		trigger_error('Устаревший функция _save_item -> заменить на _update', E_USER_WARNING);
		return $this->_update($data,true,$where);
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
			return '\'' . $this->SqlEsc($this->id) . '\'';
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

// *** PERMISSION ***//

	public function _prmModulAdd() {
		if (!$this->prm_add)
			return false;
		if (static_main::_prmModul($this->_cl, array(9)))
			return true;
		return false;
	}

	public function _prmModulEdit(&$data, $param=array()) {
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
		//включаем сортировку для всех полей
		/*if (isset($this->fields_form[$key]['mask']['sort']))
			return true;
		elseif ($key == $this->mf_namefields or $key == 'ordfield' or $key == $this->mf_actctrl)*/
			return true;
		//return false;
	}

// --END PERMISSION -----//


	/**
	 * Вывод и обработка данных
	 * @param array $param параметры
	* $param
	* - formflag
	* - ajax
	* - errMess
	* - showform
	 * @return array
	*/
	public function _UpdItemModul($param) {
		return include($this->_CFG['_PATH']['core'] . 'kernel.UpdItemModul.php');
	}

	/**
	 * Пре обработка формы
	 * @param array $data данные
	 * @param array $param параметры
	 * @return array
	*/
	public function kPreFields(&$data, &$param) {
		$this->getFieldsForm(1);//обнуляем форму
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
			}
			elseif (isset($r['multiple']) and $r['multiple'] > 0 and $r['type'] == 'list') {
				if(isset($data[$k])) {
					if (!is_array($data[$k])) {
						$data[$k] = trim($data[$k], '|');
						$r['value'] = explode('|', $data[$k]);
					}else
						$r['value'] = $data[$k];
				}
			}
			elseif ($r['type'] == 'date') {
				if(!isset($r['mask']['format']) or !$r['mask']['format'])
					$r['mask']['format'] = 'Y-m-d-H-i-s';
			}
			/*elseif ($r['type'] == 'checkbox') {
				$data[$k] = $r['value'] = ((isset($data[$k]) and $data[$k])?1:0);
			}*/
			if (isset($data[$k]) and !isset($r['value'])) //  and $data[$k]
				$r['value'] = $data[$k];

			if (isset($this->id) and isset($this->data[$this->id]['_ext_' . $k]))
				$r['ext'] = $this->data[$this->id]['_ext_' . $k];

			if (!isset($r['comment']))
				$r['comment'] = '';

			//end foreach
		}

		if(!isset($param['captchaOn'])) {
			if(!isset($_SESSION['user']['id']))
				$param['captchaOn'] = true;
			else
				$param['captchaOn'] = false;
		}
		if (count($this->fields_form) and $param['captchaOn']) {
			$this->fields_form['captcha'] = array(
				'type' => 'captcha',
				'caption' => static_main::m('_captcha',$this),
				'captcha' => static_form::getCaptcha(),
				'src' => $this->_CFG['_HREF']['captcha'] . '?' . rand(0, 9999),
				'value' => (isset($data['captcha'])?$data['captcha']:''),
				'mask' => array('min' => 1));
		}

		$mess = array();
		if (isset($this->mess_form) and count($this->mess_form))
			$mess = $this->mess_form;
		if (!count($this->fields_form))
			$mess[] = array('name' => 'error', 'value' => static_main::m('nodata',$this));
		if (isset($this->_CFG['hook']['kPreFields']))
			$this->__do_hook('kPreFields', func_num_args());
		return $mess;
	}

	/**
	 * Создание данных для формы
	 * @param mixed $form - 1 = форма
	 * @return array
	*/
	function getFieldsForm($form=0) {
		$this->setFieldsForm($form);
		$temp = $this->fields_form;
		$this->fields_form = array();
		foreach($temp as $k=>$r) {
			if($r['type']=='ckedit') {
				//$this->fields[$k.'_ckedit'] = array('type' => 'tinyint', 'width'=>3, 'attr' => 'NOT NULL','default'=>'1');
				if($form>0 and static_main::_prmUserCheck(1))
					$this->fields_form[$k.'_ckedit'] = array('type' => 'list', 'listname'=>'wysiwyg', 'caption' => $r['caption'].' - Выбор редактора', 'onchange'=>'SetWysiwyg(this)','mask'=>array('usercheck'=>1));
			}elseif($r['type']=='list') {
				if(!isset($r['listname']))
					$r['listname'] = 'list';
			}
			$this->fields_form[$k] = $r;
		}
		if(!$form and count($this->formDSort)) {
			$temp = $this->fields_form;
			$this->fields_form = array();
			foreach($this->formDSort as $rr) {
				if($rr=='#over#') {
					$diffForm = array_diff_key($temp,array_keys($this->formdSort));
					$this->fields_form = array_merge($this->fields_form,$diffForm);
				}
				elseif(isset($temp[$rr])) {
					$this->fields_form[$rr] = $temp[$rr];
				}
			}
		}
		elseif($form and count($this->formSort)) {
			$temp = $this->fields_form;
			$this->key_formSort = array_flip($this->formSort);
			if(isset($this->key_formSort['#over#'])) {
				$over = array_diff_key($temp,$this->key_formSort);
			}
			$this->fields_form = array();
			foreach($this->formSort as $rr) {
				if($rr=='#over#') {
					$this->fields_form = array_merge($this->fields_form,$over);
				}
				elseif(isset($temp[$rr])) {
					$this->fields_form[$rr] = $temp[$rr];
				}
			}
		}
		return true;
	}

	public function setFieldsForm($form=0) {
		$this->fields_form = array();
		return true;
	}

	/**
	 * Создание данных для формы
	 * @param mixed $param - параметры
	 * @return array
	*/
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
			$this->form['_info']['caption'] = static_main::m('update_name', array($this->caption),$this);
		else
			$this->form['_info']['caption'] = static_main::m('add_name', array($this->caption),$this);

		$this->kFields2FormFields($this->fields_form);
		if (!$this->id or (isset($this->data[$this->id]) and $this->_prmModulEdit($this->data[$this->id], $param))) {
			$this->form['sbmt'] = array(
				'type' => 'submit',
				'value_save' => ((isset($param['sbmt_save']) and $this->id) ? static_main::m('_save',$this) : ''),
				'value_close' => (isset($param['sbmt_close']) ? static_main::m('_close',$this) : ''),
				'value' => static_main::m('_saveclose',$this)
			);
			if($this->id and $this->_prmModulDel($this->data, $param) and isset($param['sbmt_del']))
				$this->form['sbmt']['value_del'] = static_main::m('Удалить',$this);
		}
		return true;
	}

	/**
	 * Корректировака и обработка формы для вывода формы
	 * @param mixed $fields - название списока или массив данных для списка
	 * @return array
	*/
	public function kFields2FormFields(&$fields) {
		return include($this->_CFG['_PATH']['core'] . 'kernel.kFields2FormFields.php');
	}

	/**
	 * проверка формы
	 * @param mixed $data - данные
	 * @param mixed $param - параметры
	 * @param mixed $FORMS - форма
	 * @return array Данные
	*/
	public function fFormCheck(&$data, &$param, &$FORMS) {
		return static_form::_fFormCheck($this, $data, $param, $FORMS);
	}

	/**
	 * проверка выбранных данных из списка
	 * @param mixed $listname - название списока или массив данных для списка
	 * @param mixed $value - значение
	 * @return array Список
	*/
	public function _checkList(&$listname, $value=NULL) {
		$templistname = $listname;
		if (is_array($listname))
			$templistname = implode(',', $listname);

		if (!isset($this->_CFG['enum_check'][$templistname])) {

			if (!isset($this->_CFG['enum'][$templistname])) {
				$data = $this->_getCashedList($listname, $value);
				//$this->_CFG['enum'][$templistname]
			} else
				$data = $this->_CFG['enum'][$templistname];

			if (!is_array($data) or !count($data))
				return false;

			$temp2 = array();
			$temp = current($data);
			if (is_array($temp) and !isset($temp['#name#'])) {
				foreach ($data as $krow => $row) {
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
				$temp = &$temp2;
			}else
				$temp = &$data;
			if(is_null($value) or !is_array($listname))// не кешируем если задано значение и $listname - выборка из БД(в массиве)
				$this->_CFG['enum_check'][$templistname] = $temp;
		}else
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

	/**
	 * Получение списка из кеша если он там есть
	 * @param mixed $listname - название списока или массив данных для списка
	 * @param mixed $value - значение
	 * @return array Список
	*/
	public function _getCashedList($listname, $value=NULL) {
		$data = array();
		$templistname = $listname;
		if (is_array($listname))
			$templistname = implode(',', $listname);
		if (isset($this->_enum[$templistname])) {
			return $this->_enum[$templistname];
			//$this->_CFG['enum'][$templistname] = $this->_enum[$templistname];
		} elseif (!isset($this->_CFG['enum'][$templistname])) {
			if(!is_null($value) and is_array($listname)) // не кешируем если задано $value и $listname - выборка из таблиц(задается массивом)
				return $this->_getlist($listname, $value);
			else
				$this->_CFG['enum'][$templistname] = $this->_getlist($listname,$value);
				
		}
		return $this->_CFG['enum'][$templistname];
	}

	public function _getlist(&$listname, $value=NULL) {/* LIST SELECTOR */
		include_once($this->_CFG['_PATH']['core'] . 'kernel.getlist.php');
		return _getlist($this, $listname, $value);
	}

////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Универсальный обработчик вывода данных
	 * @param array $PARAM - параметры вывода данных и в нём формируется массив данных
	 * 	  $Ajax=0 - не скриптовая
	 *		$this->_cl - name текущего класса без _class
	 *		$this->_clp - построенный путь
	 *		$param['xsl'] - шаблонизатор
	 * @param string $ftype
	 * @return array Данные для шаблонизатора
	*/
	public function super_inc($PARAM=array(), $ftype='') {
		// Результат работы скрипта
		// $flag = 3; - вывод данных
		$flag = 1;

		// Задаем начальный массив данных
		if(!isset($PARAM['messages'])) {
			$PARAM['messages'] = array();
			$PARAM['path'] = array();
			$PARAM['_clp'] = array('_modul'=>$this->_cl);
			if(strpos($PARAM['firstpath'],'?')===false)
				$PARAM['firstpath'] .= '?';
			else {
				if(substr($PARAM['firstpath'],-1)!='&')
					$PARAM['firstpath'] .= '&';
			}
		}

		// ID элемента
		if (isset($_GET[$this->_cl . '_id']) and !is_array($_GET[$this->_cl . '_id'])) {
			if (!$this->mf_use_charid)
				$this->id = (int) $_GET[$this->_cl . '_id'];
			else {
				$rep = array('\'', '"', '\\', '/');
				$this->id = str_replace($rep, '', $_GET[$this->_cl . '_id']);
			}
		}

		$PARAM['path'][$this->_cl] = array(
			'path' => $PARAM['_clp'],
			'name'=>$this->caption
		);

		if ($this->id) {
			// Древо
			if ($this->mf_istree) {
				$parent_id = $this->id;
				$this->tree_data = $first_data = $path = array();
				$listfields = 'id,'.$this->mf_istree . ', ' . $this->_listnameSQL . ' as name';
				$name = $this->caption;
				while ($parent_id) {
					$clause = 'WHERE id="' . $parent_id . '"';
					$this->data = $this->_query($listfields, $clause, 'id');
					if(count($this->data)) {
						if (!count($first_data))
							$first_data = $this->data;
						$this->tree_data += $this->data;

						//********* Path ************
						$path[$this->_cl . $parent_id] = array(
							'path' => $PARAM['_clp']+array($this->_cl . '_id'=>$parent_id),
							'name' => $name
						);
						if($this->data[$parent_id][$this->_listname])
							$name = preg_replace($this->_CFG['_repl']['name'], '', $this->data[$parent_id][$this->_listname]);
						else
							$name = '№'.$parent_id;
						//BREAK
						if(!$this->parent_id and $parent_id!=$this->id)
							$this->parent_id = $parent_id;
						if (isset($PARAM['first_id']) and $PARAM['first_id'] and $parent_id == $PARAM['first_id'])
							break;


						$parent_id = $this->data[$parent_id][$this->mf_istree];

						// Задаем данные о номере странице
						$this->_pa = $this->_cl .$parent_id. '_pn';
						if (isset($_REQUEST[$this->_pa]) && (int) $_REQUEST[$this->_pa]) {
							$PARAM['_clp'][$this->_pa] = (int) $_REQUEST[$this->_pa];
							foreach($path as &$tp) {
								$tp['path'][$this->_pa] = $PARAM['_clp'][$this->_pa];
							}
						}
					}
				}
				//$path[$this->_cl . $parent_id]['name'] = $this->caption.': '.$path[$this->_cl . $parent_id]['name'];
				$this->data = $first_data;
				if (isset($PARAM['first_id']) and $PARAM['first_id'] and !$parent_id)
					$this->id = '';

				$PARAM['path'] += array_reverse($path); //Переворачиваем
				$PARAM['path'][$this->_cl]['name'] .= ' : '.$name;
			}
			else {
				$this->data = $this->_select();
				//********* Path ************
				if($this->data[$this->id][$this->_listname])
					$name = preg_replace($this->_CFG['_repl']['name'], '', $this->data[$this->id][$this->_listname]);
				else
					$name = '№'.$this->id;
				$PARAM['path'][$this->_cl]['name'] .= ': '.$name;
			}
			$PARAM['_clp'][$this->_cl . '_id'] = $this->id;
			$this->_pa = $this->_cl .$this->id. '_pn';
		}

		// Задаем данные о номере странице
		if (isset($_REQUEST[$this->_pa]) && (int) $_REQUEST[$this->_pa])
			$this->_pn = $PARAM['_clp'][$this->_pa] = (int) $_REQUEST[$this->_pa];


		if ($this->id and isset($_GET[$this->_cl . '_ch']) and isset($this->childs[$_GET[$this->_cl . '_ch']])) {
			if (count($this->data)) {
				if ($this->mf_istree)
					array_pop($PARAM['path']);
/****************************************/
/****** CHILD ***************************/
/****************************************/
				$PARAM['_clp'][$this->_cl . '_ch'] = $_GET[$this->_cl . '_ch'];
				list($PARAM, $flag) = $this->childs[$_GET[$this->_cl . '_ch']]->super_inc($PARAM, $ftype);
/****************************************/
/****** CHILD ***************************/
/****************************************/
			}
		} 
		else {
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
			$PARAM['clause'] = $filter_clause[0];

			$PARAM['topmenu'] = array();
			if ($this->_prmModulAdd()) {
				$t = array('_type'=>'add');
				if($this->id)
					$t[$this->_cl . '_id'] = $this->id;
				$PARAM['topmenu']['add'] = array(
					'href' => $t,
					'caption' => 'Добавить ' . $this->caption,
					'sel' => 0,
					'type' => '',
					'css' => 'add'
				);
			}


			if ($this->owner and count($this->owner->childs))
				foreach ($this->owner->childs as $ck => &$cn) {
					if (count($cn->fields_form) and $ck != $this->_cl and $cn->_prmModulShow($ck)) {
						$PARAM['topmenu']['ochild_'.$ck] = array(
							'href' => array($this->_cl . '_id'=>$this->owner->id , $this->_cl . '_ch'=>$ck),
							'caption' => $cn->caption . '(' . $row[$ck . '_cnt'] . ')',
							'sel' => 0,
							'type' => 'child'
						);
					}
				}
			if ($this->mf_istree and count($this->childs) and $this->id)
				foreach ($this->childs as $ck => &$cn) {
					if (count($cn->fields_form) and $ck != $this->_cl and $cn->_prmModulShow($ck))
						$PARAM['topmenu']['child' . $ck] = array(
							'href' => array($this->_cl . '_id' => $this->id, $this->_cl . '_ch' => $ck),
							'caption' => $cn->caption . '(' . $row[$ck . '_cnt'] . ')',
							'sel' => 0,
							'type' => 'child'
						);
				}

			if (is_null($this->owner) and static_main::_prmModul($this->_cl, array(14))) {
				/*$PARAM['topmenu']['Checkmodul'] = array(
					'href' => array('_type'=>'tools', '_func'=>'Checkmodul'),
					'caption' => 'Обновить поля таблицы',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepchecktable',
				);*/
				if($this->ver!=$this->_CFG['modulprm'][$this->_cl]['ver']) {
					//$_tpl['onload'] .= 'showHelp(\'.weptools.wepchecktable\',\'Версия модуля '.$MODUL->caption.'['.$MODUL->_cl.'] ('.$MODUL->ver.') отличается от версии ('.$_CFG['modulprm'][$MODUL->_cl]['ver'].') сконфигурированного для этого сайта. Обновите здесь поля таблицы.\',4000);$(\'.weptools.wepchecktable\').addClass(\'weptools_sel\');';
					$PARAM['messages'][] = array('error','Версия модуля '.$this->caption.'['.$this->_cl.'] ('.$this->ver.') отличается от версии ('.$this->_CFG['modulprm'][$this->_cl]['ver'].') сконфигурированного для этого сайта. Обновите модуль.');
				}

			}

			if (isset($this->config_form) and count($this->config_form) and static_main::_prmModul($this->_cl, array(13)))
				$PARAM['topmenu']['Configmodul'] = array(
					'href' => array('_type'=>'tools', '_func'=>'Configmodul'),
					'caption' => 'Настроика модуля',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepconfig',
				);
			if ($this->mf_indexing and static_main::_prmModul($this->_cl, array(12)))
				$PARAM['topmenu']['Reindex'] = array(
					'href' => array('_type'=>'tools', '_func'=>'Reindex'),
					'caption' => 'Переиндексация',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepreindex',
				);
			if ($this->cf_reinstall and static_main::_prmModul($this->_cl, array(11)))
				$PARAM['topmenu']['Reinstall'] = array(
					'href' => array('_type'=>'tools', '_func'=>'Reinstall'),
					'caption' => 'Переустановка',
					'sel' => 0,
					'type' => 'tools',
					'css' => 'wepreinstall',
				);
			if ($filter_clause[1]) {
				$PARAM['topmenu']['Formfilter'] = array(
					'href' => array('_type'=>'tools', '_func'=>'Formfilter'),
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
				$t = array('_type'=>'static', '_func'=>'Statsmodul');
				if($this->owner and $this->owner->id)
					$t['_oid'] = $this->owner->id;
				$PARAM['topmenu']['Statsmodul'] = array(
					'href' =>  $t,
					'caption' => 'Статистика',
					'sel' => 0,
					'type' => 'static',
					'css' => 'wepstats',
				);
			}

			// Групповые операции
			$sg = 0;
			if(isset($_COOKIE['SuperGroup'][$this->_cl])) {
				$sg += count($_COOKIE['SuperGroup'][$this->_cl]);
			}
			$t = array('_type'=>'tools', '_func'=>'SuperGroup');
			$PARAM['topmenu']['SuperGroup'] = array(
				'href' =>  $t,
				'caption' => 'Групповая операция</span><span class="wepSuperGroupCount" title="Кол-во выбранных элементов">'.$sg,
				'title'=>'Групповая операция',
				'sel' => 0,
				'type' => 'tools',
				'css' => 'wepSuperGroup',
				'style'=>(!$sg?'display:none;':'')
			);


			// Удаление через форму
			if(isset($_POST['sbmt_del']) and $this->id) {
				$ftype = 'del';
			}

			if ($ftype == 'add') {
				if($this->mf_istree and $this->id)
					$this->parent_id = $this->id;
				$this->id = NULL;
				list($PARAM['formcreat'], $flag) = $this->_UpdItemModul($PARAM);
				if ($flag == 1 and isset($this->parent_id) and $this->parent_id)
					$this->id = $this->parent_id;
				//else
				$tmp = $PARAM['_clp']+array('_type'=>'add');
				if($this->parent_id)
					$tmp[$this->_cl . '_id'] = $this->parent_id;
				$PARAM['path']['add'] = array(
					'path' => $tmp,
					'name'=>'Добавление'
				);
			}
			elseif ($ftype == 'edit' && $this->id) {
				if ($this->mf_istree)
					array_pop($PARAM['path']);
				$PARAM['path']['edit'] = array(
					'path' => $PARAM['_clp']+array($this->_cl . '_id'=>$this->id, '_type'=>'edit'),
					'name'=>'Редактирование'
				);
				list($PARAM['formcreat'], $flag) = $this->_UpdItemModul($PARAM);
				if ($flag == 1) {
					if (isset($this->parent_id) and $this->parent_id)
						$this->id = $this->parent_id;
					$PARAM['_clp'][$this->_cl . '_id'] = $this->id;
				}
			}
			elseif ($ftype == 'act' && $this->id) {
				if ($this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $this->_Act(1, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'],$messages);
				if($this->mf_istree)
					$this->id = $this->data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'dis' && $this->id) {
				if ($this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $this->_Act(0, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'],$messages);
				if ($this->mf_istree)
					$this->id = $this->tree_data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'ordup' && $this->id && $this->mf_ordctrl) {
				if ($this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $this->_ORD(-1, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'],$messages);
				if ($this->mf_istree)
					$this->id = $this->data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'orddown' && $this->id && $this->mf_ordctrl) {
				if ($this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $this->_ORD(1, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'],$messages);
				if ($this->mf_istree)
					$this->id = $this->tree_data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'del' && $this->id) {
				if ($this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $this->_Del($PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'],$messages);
				if ($this->mf_istree)
					$this->id = $this->tree_data[$this->id][$this->mf_istree];
				else
					$this->id = NULL;
			}
			elseif ($ftype == 'tools') {
				if($this->mf_istree and $this->id)
					$this->parent_id = $this->id;
				$PARAM['formtools'] = array();
				if (!isset($PARAM['topmenu'][$_REQUEST['_func']]))
					$PARAM['formtools']['messages'] = array(array('value' => 'Опция инструмента не найдена.', 'name' => 'error'));
				elseif (!method_exists($this, 'tools' . $_REQUEST['_func']))
					$PARAM['formtools']['messages'] = array(array('value' => 'Функция инструмента не найдена.', 'name' => 'error'));
				else
					eval('$PARAM[\'formtools\'] = $this->tools' . $_REQUEST['_func'] . '();');
			}
			elseif ($ftype == 'static') {
				if($this->mf_istree and $this->id)
					$this->parent_id = $this->id;
				$PARAM['static'] = array();
				if (!isset($PARAM['topmenu'][$_REQUEST['_func']]))
					$PARAM['messages'] = array(array('value' => 'Опция статики не найдена.', 'name' => 'error'));
				elseif (!method_exists($this, 'static' . $_REQUEST['_func']))
					$PARAM['messages'] = array(array('value' => 'Функция статики не найдена.', 'name' => 'error'));
				else {
					eval('$PARAM[\'static\'] = $this->static' . $_REQUEST['_func'] . '();');
				}
			} else {
				if($this->mf_istree and $this->id)
					$this->parent_id = $this->id;
				$flag = 3;
				$PARAM['data'] = $this->_displayXML($PARAM);
				if(count($PARAM['data']['messages']))
					$PARAM['messages'] = array_merge($PARAM['messages'],$PARAM['data']['messages']);
				unset($PARAM['data']['messages']);
			}
			/*elseif ($this->id) { //Просмотр данных
				$flag = 3;
				$PARAM['item'] = $this->data;
			}*/

		}
		$PARAM['_cl'] = $this->_cl;

		return array($PARAM, $flag);
	}

	/**
	 * вывод данных
	 * @param array $param - параметры вывода данных
	 * @return array
	*/
	public function _displayXML(&$param) {
		return include($this->_CFG['_PATH']['core'] . 'kernel.displayXML.php');
	}


	
// MODUL configuration

	/**
	 * Переустановка БД модуля
	 * @return array form
	*/
	public function toolsReinstall() {
		$this->form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(11)))
			$mess[] = static_main::am('error','denied',$this);
		elseif (count($_POST) and $_POST['sbmt']) {
			static_tools::_reinstall($this);
			$mess[] = static_main::am('ok','_reinstall_ok',$this);
		} else {
			$this->form['_*features*_'] = array('name' => 'Reinstall', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
			$this->form['_info'] = array(
				'type' => 'info',
				'caption' => static_main::m('_reinstall_info',$this));
			$this->form['sbmt'] = array(
				'type' => 'submit',
				'value' => static_main::m('_submit',$this));
		}
		self::kFields2FormFields($this->form);
		return Array('form' => $this->form, 'messages' => $mess);
	}

	public function toolsConfigmodul() {
		$this->form = array();
		$arr = array('mess' => '', 'vars' => '');
		if (!static_main::_prmModul($this->_cl, array(13)))
			$arr['mess'][] = static_main::am('error','denied',$this);
		elseif (!count($this->config_form)) {
			$this->form['_info'] = array(
				'type' => 'info',
				'caption' => static_main::m('_configno',$this));
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
				$arr = $this->fFormCheck($_POST, $arr['vars'], $this->config_form);// 2ой параметр просто так
				$config = array();
				foreach ($this->config_form as $k => $r) {
					if (isset($arr['vars'][$k])) {
						$this->config_form[$k]['value'] = $arr['vars'][$k];
						$config[$k] = $arr['vars'][$k];
					}
				}
				$this->config = $config;
				if (!count($arr['mess'])) {
					$arr['mess'][] = static_main::am('ok','update',$this);
					static_tools::_save_config($config, $this->_file_cfg);
				}
			}
			static_tools::_xmlFormConf($this);
		}
		$this->kFields2FormFields($this->form);
		return Array('form' => $this->form, 'messages' => $arr['mess']);
	}

	/**
	 * Групповые операции
	 * @return array form
	*/
	public function toolsSuperGroup() {
		global $_tpl;
		$this->form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(5,7)))
			$mess[] = static_main::am('error','denied',$this);
		elseif(!isset($_COOKIE['SuperGroup'][$this->_cl]) or !count($_COOKIE['SuperGroup'][$this->_cl]))
			$mess[] = static_main::am('alert','Нет выбранных элементов',$this);
		elseif (count($_POST)) {
			$type = '';
			if(isset($_POST['sbmt_on'])) {
				$type = 'on';
				$this->id = array_keys($_COOKIE['SuperGroup'][$this->_cl]);
				$this->_update(array('active'=>1));
				$mess[] = static_main::am('ok','Успешно включено',$this);
			}
			elseif(isset($_POST['sbmt_off'])) {
				$type = 'off';
				$this->id = array_keys($_COOKIE['SuperGroup'][$this->_cl]);
				$this->_update(array('active'=>0));
				$mess[] = static_main::am('ok','Успешно отключено',$this);
			}
			elseif(isset($_POST['sbmt_del'])) {
				$type = 'del';
				$this->id = array_keys($_COOKIE['SuperGroup'][$this->_cl]);
				$this->_delete();
				$mess[] = static_main::am('ok','Успешно удалено',$this);
			}
			elseif(isset($_POST['sbmt_clear'])) {
				$type = 'clear';
				$mess[] = static_main::am('ok','Список чист',$this);
			}
			if(count($mess)) {
				foreach($_COOKIE['SuperGroup'][$this->_cl] as $ck=>$ck)
					$_tpl['onload'] .= 'setCookie("SuperGroup['.$this->_cl.']['.$ck.']",0,-10000);';
				$_tpl['onload'] .= '$("span.wepSuperGroupCount").text(0).parent().hide("slow");wep.SuperGroupClear("'.$type.'");';
			}
		} 
		else {
			$this->form['_*features*_'] = array('name' => 'SuperGroup', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']), 'prevhref'=>$_SERVER['HTTP_REFERER']);
			$this->form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">'.$this->caption.'</h2><h3 style="text-align:center;">Выбранно элементов : '.count($_COOKIE['SuperGroup'][$this->_cl]).'</h3>');
			$this->form['sbmt'] = array(
				'type' => 'submit',
				'value' => array(
					'_off'=>static_main::m('Отключить',$this),
					'_on'=>static_main::m('Включить',$this),
					'_del'=>static_main::m('Удалить',$this),
					'_clear'=>static_main::m('Отменить выбранные элементы.',$this),
					''=>static_main::m('Отмена',$this),
				)
			);
		}
		self::kFields2FormFields($this->form);
		return Array('form' => $this->form, 'messages' => $mess);
	}
	/**
	 * Статистика модуля
	 * @param array $oid 
	 * @return array
	*/
	public function staticStatsmodul($oid='') {
		return static_tools::_staticStatsmodul($this, $oid);
	}


	/*
	  public function toolsReindex(){
	  $this->form = $mess = array();
	  if(!static_main::_prmModul($this->_cl,array(12)))
	  $mess[] = array('name'=>'error', 'value'=>static_main::m('denied',$this));
	  elseif(count($_POST) and $_POST['sbmt']){
	  if(!$this->_reindex())
	  $mess[] = array('name'=>'error', 'value'=>static_main::m('_reindex_ok',$this));
	  else
	  $mess[] = array('name'=>'error', 'value'=>static_main::m('_reindex_err',$this));
	  }else{
	  $this->form['_*features*_'] = array('name'=>'reindex','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	  $this->form['_info'] = array(
	  'type'=>'info',
	  'caption'=>static_main::m('_reindex_info',$this));
	  $this->form['sbmt'] = array(
	  'type'=>'submit',
	  'value'=>static_main::m('_submit',$this));
	  }
	  self::kFields2FormFields($this->form);
	  return Array('form'=>$this->form, 'messages'=>$mess);
	  }

	  private function _reindex()
	  {
	  return true;
	  }
	 */

	/**
	 * Tools для superinc - Проверка модуля (устарешая)
	 * @return array
	*/
	public function toolsCheckmodul() {
		return static_tools::_toolsCheckmodul($this);
	}

	/**
	 * Проверка модуля 
	 * @return array
	*/

	public function _checkmodstruct() {
		$DATA = static_tools::_checkmodstruct($this->_cl);
		return $DATA;
	}

	/**
	 * инструмент Фильтра super_inc
	 * @return array
	*/
	public function toolsFormfilter() {
		global $_tpl;
		$this->form = array();
		/**
		 * очистка фильтра
		 * */
		if (isset($_REQUEST['f_clear_sbmt'])) {
			unset($_SESSION['filter'][$this->_cl]);
			$_tpl['onload'] .= 'window.location.href = \'' . $_SERVER['HTTP_REFERER'] . '\';';
		}
		/**
		 * задаются параметры фильтра
		 * */ elseif (isset($_REQUEST['sbmt'])) {
			$this->setFilter();
			$_tpl['onload'] .= 'window.location.href = \'' . $_SERVER['HTTP_REFERER'] . '\';';
		}
		else
			$this->Formfilter();
		return Array('filter' => $this->form, 'messages' => array());
	}

	/**
	 * Создает форму данных для фильтра super_inc
	 * @return array form
	*/
	function Formfilter() {
		$_FILTR = array();
		if(isset($_SESSION['filter'][$this->_cl]))
			$_FILTR = $_SESSION['filter'][$this->_cl];
		$this->form = array();
		foreach ($this->fields_form as $k => $r) {
			if (isset($r['mask']['filter']) and $r['mask']['filter'] == 1) {
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

	/**
	 * задает параметры запроса для super_inc
	 * @param bool $flag - true не ощищать данные
	 * @return array
	*/
	function setFilter($flag=0) {
		if (isset($_REQUEST['f_clear_sbmt'])) {
			unset($_SESSION['filter'][$this->_cl]);
		} else {
			foreach ($this->fields_form as $k => $row) {
				if (isset($_REQUEST['f_' . $k]) && $_REQUEST['f_' . $k] != '' && isset($this->fields_form[$k]['mask']['filter'])) {
					$is_int = 0;
					if (!is_array($_REQUEST['f_' . $k])) {

						if ($row['type'] == 'date') {

							$_REQUEST['f_' . $k] = static_form::_get_fdate($_REQUEST['f_' . $k], $row['mask']['format'], $this->fields[$k]['type']);
							if (isset($_REQUEST['f_' . $k . '_2']))
								$_REQUEST['f_' . $k . '_2'] = static_form::_get_fdate($_REQUEST['f_' . $k . '_2'], $row['mask']['format'], $this->fields[$k]['type']);
						}

						$_SESSION['filter'][$this->_cl][$k] = $this->SqlEsc($_REQUEST['f_' . $k]);
						if (isset($_REQUEST['f_' . $k . '_2']))
							$_SESSION['filter'][$this->_cl][$k . '_2'] = $this->SqlEsc($_REQUEST['f_' . $k . '_2']);
					} else {
						$_SESSION['filter'][$this->_cl][$k] = array();
						if ($is_int)
							foreach ($_REQUEST['f_' . $k] as $row)
								$_SESSION['filter'][$this->_cl][$k][] = (int) $row;
						else
							foreach ($_REQUEST['f_' . $k] as $row)
								$_SESSION['filter'][$this->_cl][$k][] = $this->SqlEsc($row);
					}
					if (isset($_REQUEST['exc_' . $k]) and $_REQUEST['exc_' . $k])
						$_SESSION['filter'][$this->_cl]['exc_' . $k] = 1;
					else
						unset($_SESSION['filter'][$this->_cl]['exc_' . $k]);
				} else if (!$flag)
					unset($_SESSION['filter'][$this->_cl][$k]);
			}
		}
	}

	/**
	 * ФИЛЬТР в запросе для super_inc
	 * @return array
	*/
	function _filter_clause() {
		$cl = $_FILTR = array();
		$flag_filter = 0;
		if(isset($_REQUEST['filter_'.$this->_cl])) {
			if (!is_array($_REQUEST['filter_'.$this->_cl]))
				unset($_SESSION['filter'][$this->_cl]);
			else
				$_FILTR = $_REQUEST['filter_'.$this->_cl];
		}
		elseif(isset($_SESSION['filter'][$this->_cl]))
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

	/**
	 * задает параметры запроса для super_inc
	 * @param array $param - данные параметра
	 * @return array
	*/
	function _moder_clause(&$param) {
		if (!isset($param['clause']) or !is_array($param['clause']))
			$param['clause'] = array();
		if ($this->mf_createrid and $this->_prmModulShow($this->_cl))
			$param['clause']['t1.' . $this->mf_createrid] = 't1.' . $this->mf_createrid . '="' . $_SESSION['user']['id'] . '"';
		if ($this->owner and $this->owner->id)
			$param['clause']['t1.' . $this->owner_name] = 't1.' . $this->owner_name . '="' . $this->owner->id . '"';
		if ($this->mf_istree) {
			if ($this->id)
				$param['clause']['t1.'.$this->mf_istree] = 't1.'.$this->mf_istree.'="' . $this->id . '"';
			elseif (isset($param['first_id']))
				$param['clause']['t1.'.$this->mf_istree] = 't1.id="' . $param['first_id'] . '"';
			elseif (isset($param['first_pid']))
				$param['clause']['t1.'.$this->mf_istree] = 't1.'.$this->mf_istree.'="' . $param['first_id'] . '"';
			elseif ($this->mf_use_charid)
				$param['clause']['t1.'.$this->mf_istree] = 't1.'.$this->mf_istree.'=""';
			else
				$param['clause']['t1.'.$this->mf_istree] = 't1.'.$this->mf_istree.'=0';
			if ($this->owner and $this->owner->id and ($this->id or (isset($param['first_pid']) and $param['first_pid']) ) )
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

	/**
	 * задает атрибуты для super_inc
	 * @param array $row - данные
	 * @param array $param - данные параметра
	 * @return array
	*/
	private function _tr_attribute(&$row, &$param) {
		$DATA = array();
		if ($this->_prmModulEdit($row, $param))
			$DATA['edit'] = true;
		else
			$DATA['edit'] = false;
		if ($this->_prmModulDel(array($row), $param))
			$DATA['del'] = true;
		else
			$DATA['del'] = false;
		if ($this->_prmModulAct(array($row), $param))
			$DATA['act'] = true;
		else
			$DATA['act'] = false;
		return $DATA;
	}

	/**
	 * Активация данных $this->id
	 * @param array $act - 1 or 0 
	 * @param array $param - данные 
	 * @return array
	*/
	public function _Act($act, &$param) {
		$flag = 1;
		$DATA = array();
		if ($param['mess'])
			$DATA = $param['mess'];
		$param['act'] = $act;
		$this->data = $this->_select();
		if ($this->_prmModulAct($this->data, $param)) {
			$data = array();
			$act = (int) $act;
			if ($this->fields[$this->mf_actctrl]['type'] == 'bool')
				$data[$this->mf_actctrl] = $act;
			else {
				if (static_main::_prmModul($this->_cl, array(7))) {
					if ($act == 0)
						$data[$this->mf_actctrl] = 6;
					else
						$data[$this->mf_actctrl] = 1;
				}
				elseif ($act == 1)
					$data[$this->mf_actctrl] = 5;
				else
					$data[$this->mf_actctrl] = 2;
			}

			if ($this->_update($data)) {
				if ($data[$this->mf_actctrl] == 5)
					$DATA[] = static_main::am('ok','act5',$this);
				if ($data[$this->mf_actctrl] == 6)
					$DATA[] = static_main::am('ok','act6',$this);
				elseif ($act)
					$DATA[] = static_main::am('ok','act1',$this);
				else
					$DATA[] = static_main::am('ok','act0',$this);
				$flag = 0;
			}
			else
				$DATA[] = static_main::am('error','update_err',$this);
		}
		else
			$DATA[] = static_main::am('error','denied',$this);
		return array($DATA, $flag);
	}

	/**
	 * удаление данных $this->id
	 * @param array $param - данные 
	 * @return array
	*/
	public function _Del($param) {
		$flag = 1;
		$DATA = array();
		if (isset($param['mess']))
			$DATA = $param['mess'];
		$this->data = $this->_select();
		if (count($this->data) and $this->_prmModulDel($this->data, $param)) {
			if (isset($this->fields[$this->mf_actctrl]) and $this->fields[$this->mf_actctrl]['type'] != 'bool') {
				$data[$this->mf_actctrl] = 4;
				if ($this->_update($data)) {
					$DATA[] = static_main::am('ok','deleted',$this);
					$flag = 0;
				}else
					$DATA[] = static_main::am('error','del_err',$this);
			}else {
				if ($this->_delete($data)) {
					$DATA[] = static_main::am('ok','deleted',$this);
					$flag = 0;
				}else
					$DATA[] = static_main::am('error','del_err',$this);
			}
		}
		else
			$DATA[] = static_main::am('error','denied',$this);
		return array($DATA, $flag);
	}

	/**
	 * сортировка $this->id
	 * @param int $ord - позиция 
	 * @param array $param - данные 
	 * @return array
	*/
	public function _ORD($ord, &$param) {
		$flag = 1;
		$DATA = array();
		if ($param['mess'])
			$DATA = $param['mess'];
		$this->data = $this->_select();
		if ($this->_prmModulEdit($this->data, $param)) {
			$data = array();
			$data[$this->mf_ordctrl] = $this->data[$this->id][$this->mf_ordctrl]+$ord;

			if ($this->_update($data)) {
				if ($ord<0)
					$DATA[] = array('value' => 'UP', 'name' => 'ok');
				else
					$DATA[] = array('value' => 'DOWN', 'name' => 'ok');
				$flag = 0;
			}
			else
				$DATA[] = static_main::am('error','update_err',$this);
		}
		else
			$DATA[] = static_main::am('error','denied',$this);
		return array($DATA, $flag);
	}

	/**
	 * Сортировка 
	 * @return array
	*/
	public function _sorting() {
		$res = array('html'=>'','eval'=>'');
		$this->id = (int)$_GET['id'];
		$pid = (isset($_GET['pid'])?(int)$_GET['pid']:0);
		$t1 = (isset($_GET['t1'])?(int)$_GET['t1']:0);
		$t2 = (isset($_GET['t2'])?(int)$_GET['t2']:0);
		$data = $this->_select();
		if(!$this->mf_ordctrl or !$this->_prmModulEdit($data)) {//!static_main::_prmModul($this->_cl,array(10))
			$res['html'] = static_main::m('Sorting denied!');
			return $res;
		}
		$res['html'] = static_main::m('Sorting error');


		if ($t2) {
			$data = $this->qs($this->mf_ordctrl,'WHERE id='.$t2);
			$neword = $data[0][$this->mf_ordctrl];

			$qr = '`' . $this->mf_ordctrl . '`>=\'' . $neword . '\'';
			if($this->mf_istree and $pid)
				$qr .= ' and `'.$this->mf_istree.'`='.$pid;
			$this->fields[$this->mf_ordctrl]['noquote']= true;
			if(!$this->_update(array($this->mf_ordctrl => '`'.$this->mf_ordctrl . '`+1'),$qr,false))
				return $res;

			if(!$this->_update(array($this->mf_ordctrl => $neword) ,'`id`=' . $this->id ))
				return $res;
		}else {
			$qr = '';
			if($this->mf_istree and $pid)
				$qr .= ' WHERE `'.$this->mf_istree.'`='.$pid;

			$data = $this->qs('max('.$this->mf_ordctrl.') as mx',$qr);
			$neword = $data[0]['mx']+1;
			if(!$this->_update(array($this->mf_ordctrl => $neword) ,'`id`=' . $this->id ))
				return $res;
		}
		$res['html'] = '';//static_main::m('Sorting successful.')
		return $res;
	}

	/**
	 * при успешном изменении данных 
	 * @param string $type - add, save, delete
	 * @param array $data - данные 
	 * @return bool
	*/
	function allChangeData($type='', $data='') {
		return true;
	}

	/**
	 * пулучение древамасиива из одномерного массива
	 * @param array $path - путь
	 * @return string XML
	*/
	public function _forlist(&$data, $id, $select='',$multiple=0) {
		/*
		  array('name'=>'NAME','id'=>1 [, 'sel'=>0, 'checked'=>0])
		 */
		//$select - array(значение=>1)
		$s = array();
		
		if($multiple==2 and is_array($select) and count($select)) {
			foreach($select as $sr) {	
				foreach($data as $kk=>$kd) {
					if (isset($kd[$sr])) {
						$s[$sr] = array('#id#' => $sr, '#sel#' => 1);
						if(is_array($kd[$sr]) and isset($kd[$sr]['#name#']))
							$s[$sr]['#name#'] = $kd[$sr]['#name#'];
						else
							$s[$sr]['#name#'] = $kd[$sr];
						break;
					}
				}
			}
			$multiple = 22;
		}

		if (isset($data[$id]) and is_array($data[$id]) and count($data[$id]))
			foreach ($data[$id] as $key => $value) {
				if ($select != '' and is_array($select)) {
					if (isset($select[$key])) {
						if($multiple==22) continue;
						$sel = 1;
					}
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
					$s[$key]['#item#'] = $this->_forlist($data, $key, $select, $multiple);
			}
		return $s;
	}

	// Постраничная навигация
	public function fPageNav($countfield, $thisPage='', $flag=0) {
		//$countfield - бщее число элем-ов
		//$thisPage - по умол тек путь к странице
		//$this->messages_on_page - число эл-ов на странице
		//$this->_pn - № текущей страницы
		//$flag  - опция для paginator, 0 - если номер страницы перед list_2.html , 1 - после ?_pn=1
		$numlist = $this->numlist; // кличество числе по бокам максимум
		$DATA = array('cnt' => $countfield, 'cntpage' => 0, 'modul' => $this->_cl, 'reverse' => $this->reversePageN);
		if ($this->reversePageN) {
			$DATA['cntpage'] = floor($countfield / $this->messages_on_page);
			$temp_pn = $this->_pn;
			$this->_pn = $DATA['cntpage'] - $this->_pn + 1;
		} else {
			$DATA['cntpage'] = ceil($countfield / $this->messages_on_page);
		}
		// Приводим к правильным числам
		if($this->_pn > $DATA['cntpage'])
			$this->_pn = $DATA['cntpage'];
		if($this->_pn < 1)
			$this->_pn = 1;
		$DATA['_pn'] = $this->_pn;

		foreach ($this->_CFG['enum']['_MOP'] as $k => $r)
			$DATA['mop'][$k] = array('value' => $r, 'sel' => 0);
		$DATA['mop'][$this->messages_on_page]['sel'] = 1;

		if (!$countfield || $countfield <= $this->messages_on_page || !$this->messages_on_page)
			return $DATA;
		else {
			if ($thisPage == '')
				$thisPage = $_SERVER['REQUEST_URI'];
			if (strstr($thisPage, '&amp;')) {
				$thisPage = str_replace('&amp;', '&', $thisPage);
			}
			//$PP[0] - страница не выбрана
			//$PP[1] - первая часть 
			//$PP[2] - вторая часть
			$PP = array(0=>$thisPage,1=>$thisPage,2=>'');
			if($flag) {
				$pregreplPage = '/(.*)' . $this->_cl . '_pn=[0-9]*(.*)/';
				if (!preg_match($pregreplPage, $thisPage,$matches)) {
					$PP[0] = $thisPage;
					if(strpos($thisPage,'?')===false)
						$PP[1] .= '?';
					elseif (_substr($thisPage, -1) != '?' and _substr($thisPage, -1) != '&')
						$PP[1] .= '&';
					$PP[1] .= $this->_cl . '_pn=';
				} else {
					$PP[0] = $matches[1].$matches[2];
					$PP[1] = $matches[1];
					if(strpos($matches[1],'?')===false)
						$PP[1] .= '?';
					elseif (_substr($matches[1], -1) != '?' and _substr($matches[1], -1) != '&')
						$PP[1] .= '&';
					$PP[1] .= $this->_cl . '_pn=';
					$PP[2] = $matches[2];
					//print_r('<pre>');print_r($matches);
				}
			}
			else {
				$pregreplPage = '/(.*)_p[0-9]*(.*)/';
				if (!preg_match($pregreplPage, $thisPage,$matches)) {
					$temp = explode('.html',$thisPage);
					$PP[1] = $temp[0].'_p';
					$PP[2] = '.html'.$temp[1];
				} else {
					$PP[0] = $matches[1].$matches[2];
					$PP[1] = $matches[1].'_p';
					$PP[2] = $matches[2];
					//print_r('<pre>');print_r($matches);
				}
			}
			$DATA['PP'] = $PP;

			if ($this->reversePageN) {// обратная нумирация
				/*Собираем массив ссылок*/
				if ($this->_pn==$DATA['cntpage'])
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => $PP[0]);
				if (($this->_pn + $numlist) < $DATA['cntpage']-1) {
					$DATA['link'][] = array('value' => '...', 'href' => '');
					$j = $this->_pn + $numlist;
				} else 
					$j = $DATA['cntpage']-1;
				$vl = $this->_pn - $numlist;
				if($vl<2) $vl = 2;
				for ($i = $j; $i >= $vl; $i--) {
					if ($i == $this->_pn)
						$DATA['link'][] = array('value' => $i, 'href' => 'select_page');
					else
						$DATA['link'][] = array('value' => $i, 'href' => $PP[1].$i.$PP[2]);
				}
				if ($this->_pn - $numlist > 2)
					$DATA['link'][] = array('value' => '...', 'href' => '');
				if ($this->_pn==1)
					$DATA['link'][] = array('value' => 1, 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => 1, 'href' => $PP[1].'1'.$PP[2]);
			}
			else {
				/*****/
				if ($this->_pn==1)
					$DATA['link'][] = array('value' => 1, 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => 1, 'href' => $PP[0]);
				if (($this->_pn - $numlist) > 2) {
					$DATA['link'][] = array('value' => '...', 'href' => '');
					$j = $this->_pn - $numlist;
				} else {
					$j = 2;
				}
				$vl = $this->_pn + $numlist;
				if($vl>=$DATA['cntpage']) $vl = $DATA['cntpage']-1;
				//print_r($vl);print_r('*');print_r($numlist);
				for ($i = $j; $i <= $vl; $i++) {
					if ($i == $this->_pn)
						$DATA['link'][] = array('value' => $i, 'href' => 'select_page');
					else
						$DATA['link'][] = array('value' => $i, 'href' => $PP[1].$i.$PP[2]);// . '\\1'
				}
				if ($this->_pn + $numlist < $DATA['cntpage'])
					$DATA['link'][] = array('value' => '...', 'href' => '');
				if ($this->_pn==$DATA['cntpage'])
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => $PP[1].$DATA['cntpage'].$PP[2]);
			}
			//////////////////
		}

		return $DATA;
	}
	// Постраничная навигация
	public function fPageNav2($countfield, $param) {
		//$countfield - бщее число элем-ов
		//$$param - массив данных
		//$this->messages_on_page - число эл-ов на странице
		//$this->_pn - № текущей страницы
		$numlist = $this->numlist; // кличество числе по бокам максимум
		$DATA = array('cnt' => $countfield, 'cntpage' => 0, 'modul' => $this->_cl, 'reverse' => $this->reversePageN);

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

		/*** PAGE NUM REVERSE ***/
		if ($this->reversePageN) {
			if($this->_pn == 0) 
				$this->_pn = 1;
			else
				$this->_pn = floor($countfield/$this->messages_on_page)-$this->_pn+1;
			$DATA['cntpage'] = floor($countfield / $this->messages_on_page);
			$temp_pn = $this->_pn;
			$this->_pn = $DATA['cntpage'] - $this->_pn + 1;
		} 
		else {
			$DATA['cntpage'] = ceil($countfield / $this->messages_on_page);
		}

		// Приводим к правильным числам
		if($this->_pn > $DATA['cntpage'])
			$this->_pn = $DATA['cntpage'];
		if($this->_pn < 1)
			$this->_pn = 1;
		$DATA['_pn'] = $this->_pn;

		foreach ($this->_CFG['enum']['_MOP'] as $k => $r)
			$DATA['mop'][$k] = array('value' => $r, 'sel' => 0);
		$DATA['mop'][$this->messages_on_page]['sel'] = 1;

		if ($countfield and $countfield > $this->messages_on_page) {
			//$PP[0] - страница не выбрана
			//$PP[1] - первая часть 
			//$PP[2] - вторая часть
			$PP = array(0=>$param['firstpath'],1=>$param['firstpath'],2=>'');
			if(count($param['_clp'])) {
				$temp = $param['_clp'];unset($temp[$this->_pa]);
				$PP[0] .= http_build_query($temp).'&';
				$PP[1] = $PP[0];
			}
			$PP[1] .= $this->_pa . '=';
			$DATA['PP'] = $PP;

			if ($this->reversePageN) {// обратная нумирация
				/*Собираем массив ссылок*/
				if ($this->_pn==$DATA['cntpage'])
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => $PP[0]);
				if (($this->_pn + $numlist) < $DATA['cntpage']-1) {
					$DATA['link'][] = array('value' => '...', 'href' => '');
					$j = $this->_pn + $numlist;
				} else 
					$j = $DATA['cntpage']-1;
				$vl = $this->_pn - $numlist;
				if($vl<2) $vl = 2;
				for ($i = $j; $i >= $vl; $i--) {
					if ($i == $this->_pn)
						$DATA['link'][] = array('value' => $i, 'href' => 'select_page');
					else
						$DATA['link'][] = array('value' => $i, 'href' => $PP[1].$i.$PP[2]);
				}
				if ($this->_pn - $numlist > 2)
					$DATA['link'][] = array('value' => '...', 'href' => '');
				if ($this->_pn==1)
					$DATA['link'][] = array('value' => 1, 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => 1, 'href' => $PP[1].'1'.$PP[2]);
			}
			else {
				/*****/
				if ($this->_pn==1)
					$DATA['link'][] = array('value' => 1, 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => 1, 'href' => $PP[0]);
				if (($this->_pn - $numlist) > 2) {
					$DATA['link'][] = array('value' => '...', 'href' => '');
					$j = $this->_pn - $numlist;
				} else {
					$j = 2;
				}
				$vl = $this->_pn + $numlist;
				if($vl>=$DATA['cntpage']) $vl = $DATA['cntpage']-1;
				//print_r($vl);print_r('*');print_r($numlist);
				for ($i = $j; $i <= $vl; $i++) {
					if ($i == $this->_pn)
						$DATA['link'][] = array('value' => $i, 'href' => 'select_page');
					else
						$DATA['link'][] = array('value' => $i, 'href' => $PP[1].$i.$PP[2]);// . '\\1'
				}
				if ($this->_pn + $numlist < $DATA['cntpage'])
					$DATA['link'][] = array('value' => '...', 'href' => '');
				if ($this->_pn==$DATA['cntpage'])
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => 'select_page');
				else
					$DATA['link'][] = array('value' => $DATA['cntpage'], 'href' => $PP[1].$DATA['cntpage'].$PP[2]);
			}
			//////////////////
		}

		$DATA['start'] = 0;
		if($this->reversePageN) {
			if($this->_pn==floor($countfield/$this->messages_on_page)) {
				$this->messages_on_page = $countfield-$this->messages_on_page*($this->_pn-1); // правдивый
				//$this->messages_on_page = $this->messages_on_page*$this->_pn-$countfield; // полная запись
			}
			else
				$DATA['start'] = $countfield-$this->messages_on_page*$this->_pn; // начало отсчета
		}
		else
			$DATA['start'] = $this->messages_on_page*($this->_pn-1); // начало отсчета
		if($DATA['start']<0)
				$DATA['start'] = 0;
		return $DATA;
	}


	// путь к фаилам
	public function getPathForAtt($key) {
		if (isset($this->attaches[$key]['path']) and $this->attaches[$key]['path'])
			$pathimg = $this->attaches[$key]['path'];
		else
			$pathimg = $this->_CFG['PATH']['content'] . $key;
		return $pathimg;
	}

	// путь к фаилам MEMO данных
	public function getPathForMemo($key) {
		if (isset($this->memos[$key]['path']) and $this->memos[$key]['path'])
			$pathimg = $this->memos[$key]['path'];
		else
			$pathimg = $this->_CFG['PATH']['content'] . $key;
		return $pathimg;
	}

	function _http($link,$param=array()) {
		$default = array(
			'body'=>false,
			'HTTPHEADER'=>array('Content-Type' => 'text/xml; encoding=utf-8'),
			'redirect' => false,
			'USERAGENT' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.121 Safari/535.2'
		);
		$param = array_merge($default,$param);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);//задаём url
		if(isset($param['COOKIE']))
			curl_setopt($ch, CURLOPT_COOKIE, $param['COOKIE']);
		curl_setopt($ch, CURLOPT_USERAGENT, $param['USERAGENT']);//подделываем юзер-агента
		if($param['redirect']) {
			//переходить по редиректам, инициируемым сервером, пока не будет достигнуто CURLOPT_MAXREDIRS (если есть)
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		}
		if($param['body']) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $param['HTTPHEADER']);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param['body']);
		}
		//не включать заголовки ответа сервера в вывод
		curl_setopt($ch, CURLOPT_HEADER, false);
		//вернуть ответ сервера в виде строки
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$text = curl_exec ($ch);

		$PageInfo = curl_getinfo($ch);
		$err = '';
		if($err=curl_errno($ch))
			$flag = false;
		elseif($PageInfo['http_code'] == 200)
			$flag = true;
		else
			$flag = false;
		curl_close($ch);
		return array('text'=>$text,'info'=>$PageInfo,'err'=>$err,'flag'=>$flag);
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

