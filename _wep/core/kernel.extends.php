<?php

/**
 * COMMENT = класс расширения для модулей 
 */
abstract class kernel_extends {

	function __construct($owner = NULL, $_forceLoad=false) {
		global $_CFG;
		//FB::info($_CFG);
		$this->_CFG = true; // bug fix for link
		$this->_CFG = &$_CFG; //Config
		$this->SQL_CFG = $this->_CFG['sql'];
		$this->_forceLoad = $_forceLoad;// если true - Принудительная загрузка не подключенного класса
		$this->owner = true; // bug fix  for link
		if (is_object($owner) and isset($owner->fields))
			$this->owner = &$owner; //link to owner class
		else
			$this->owner = NULL;

		$this->_set_features(); // настройки модуля

		if ($this->singleton == true)
			$_CFG['singleton'][$this->_cl] = &$this;

		$this->_create_conf(); // загрузки формы конфига
		if (isset($this->config_form) and count($this->config_form)) {
			// загрузка конфига из файла для модуля
			$this->configParse();
		}
		$this->_create(); // предустановки модуля
		$this->_childs();
		if (isset($this->_CFG['hook']['__construct'])) {
			$funcParam = func_get_args();
			$this->__do_hook('__construct', $funcParam);
		}
	}

	function __destruct() {
		
	}

	function __get($name) {
		global $SQL, $PSQL;
		if ($name == 'SQL') {
			if (!$this->grant_sql) {
				if ($this->SQL_CFG['type'] == 'sqlpostgre') {
					if (!isset($PSQL) or !$PSQL->ready) {
						$PSQL = new $this->SQL_CFG['type']($this->SQL_CFG);
					}
					$this->SQL = &$PSQL;
					return $PSQL;
				} else {
					if (!isset($SQL) or !$SQL->ready) {
						$SQL = new $this->SQL_CFG['type']($this->SQL_CFG);
					}
					$this->SQL = &$SQL;
					return $SQL;
				}
			} else {
				return new $this->SQL_CFG['type']($this->SQL_CFG);
			}
		} elseif ($name == 'setHook') {
			$this->_setHook();
			return $this->setHook;
		} elseif ($name == 'def_records') {
			$this->def_records = array();
			$this->_setDefaultRecords();
			return $this->def_records;
		} elseif ($name == 'fields_form') {
			$this->getFieldsForm(1);
			return $this->fields_form;
		}
		trigger_error('Своиство ' . $name . ' не найдено в классе ' . $this->_cl, E_USER_NOTICE);
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
					if (strpos($k, ':') !== false) {
						$temp = explode(':', $k);
						if (isset($this->_CFG['modulprm'][$temp[0]])) {
							$file = $this->_CFG['modulprm'][$temp[0]]['path'];
							$file = substr($file, 0, strrpos($file, '/')) . '/' . $temp[1];
							if (file_exists($file)) {
								include_once($file);
							}
						}
					} elseif (isset($this->_CFG['modulprm'][$k])) {
						if (_new_class($k, $TEMP)) {
							$r = '$TEMP->' . $r;
						}
					} elseif (!function_exists($r)) {
						$file = $this->_CFG['_PATH']['path'] . $k;
						if (file_exists($file)) {
							include_once($file);
						}
					}

					if ($file === NULL or function_exists($r)) {
						eval('return ' . $r . '($this,$arg);');
					}else
						trigger_error('Для модуля `' . $this->_cl . '`, функция хука `' . $r . '` не определена', E_USER_WARNING);
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
/**
* инициалия основных настроек модуля для подключения к БД и связи
* а также определение основных атрибутов 
* префикс mf_ - атрибуты создающие спец поля в бд
* prm_ - атрибуты определяющие права доступа
* cf_ - атрибуты настроики конфига для модуля
* 
**/
	protected function _set_features() {// initalization of modul features
		//$this->mf_issimple = false;
		//$this->mf_typectrl = false;
		//$this->mf_struct_readonly = false;
		// по умол. false - главный индекс хранится в бд как число, иначе как текст
		$this->mf_use_charid = false; //if true - id varchar
          // true - добавляется поле в бд , хранящий наименование записи, и это поле будет фигурировать в списках,  (можно вписать свое наименование) [bool,string]
		$this->mf_namefields = true; //добавлять поле name
          // создаст поле хранящий id пользователя создавший данную запись (можно вписать свое наименование) [bool,string]
		$this->mf_createrid = true; //польз владелец
           // true - создаст поле parent_id и записи могут храниться в виде дерева, (можно вписать свое наименование) [bool,string]
		$this->mf_istree = false; // древовидная структура?
		$this->mf_treelevel = 0; // разрешенное число уровней в дереве , 0 - безлимита, 1 - разрешить 1 подуровень
		$this->mf_ordctrl = false; // создаст поле ordind для сортировки (можно вписать свое наименование) [bool,string]
		$this->mf_actctrl = false; // создаст поле active, (можно вписать свое наименование) [bool,string]
		$this->mf_timestamp = false; // создать поле  типа timestamp, (можно вписать свое наименование) [bool,string]
		$this->mf_timecr = false; // создать поле хранящще время создания поля
		$this->mf_timeup = false; // создать поле хранящще время обновления поля
		$this->mf_timeoff = false; // создать поле хранящще время отключения поля (active=0)
		$this->mf_ipcreate = false; //IP адрес пользователя с котрого была добавлена запись
		$this->mf_notif = false; // Уведомления о добавлении записи в базу
		$this->cf_fields = false; // Возможность добавлять дополнительные поля в конфиге
		$this->prm_add = true; // добавить в модуле
		$this->prm_del = true; // удалять в модуле
		$this->prm_edit = true; // редактировать в модуле
          // если это дочерний модуль,то false запретит доступ к этому модулю через админку
		$this->showinowner = true; // показывать под родителем
          // для дочерних модулей, true разрешит создавать только одну запись для каждого элемента родителя
		$this->owner_unique = false; // поле owner_id не уникально
          // записи выводятся постранично
		$this->mf_mop = true; // выключить постраничное отображение
		$this->reversePageN = false; // обратный отчет для постраничного отображения
		$this->messages_on_page = 20; //число эл-ов на странице
		$this->numlist = 10; //максим число страниц при котором отображ все номера страниц
		$this->mf_indexing = false; // TOOLS индексация
		$this->mf_statistic = false; // TOOLS показывать  статистику по дате добавления
		$this->cf_childs = false; // TOOLS true - включить управление подключение подмодулей в настройках модуля
		$this->cf_reinstall = false; // TOOLS
		$this->cf_filter = true;
		$this->cf_tools = array(); // TOOLS button, function
		$this->includeJStoWEP = false; // подключать ли скрипты для формы через настройки
		$this->includeCSStoWEP = false; // подключать ли стили для формы через настройки
		$this->singleton = true; // класс-одиночка
		$this->ver = '0.1.1'; // версия модуля
		$this->verCore = $this->_CFG['info']['version'];
		$this->icon = 0; /* числа  означают отсуп для бэкграунда, а если будет задан текст то это сам рисунок */
		$this->default_access = '|0|';

		$this->text_ext = '.txt'; // расширение для memo фиаилов

		$this->_cl = str_replace('_class', '', get_class($this)); //- символическое id модуля
		$this->owner_name = 'owner_id'; // название поля для родительской связи в БД
		$this->tablename = $this->_cl; // название таблицы
		$this->caption = $this->_cl; // заголовок модуля
		$this->_listfields = array('name'); //select по умолч
		$this->_listname = false;
		$this->unique_fields =
				$this->index_fields =
				$this->_enum =
				$this->update_records =
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
				$this->HOOK = //перехватчик в реальном времени
				$this->_AllowAjaxFn = // разрешённые функции для аякса, нужно прописывать в индекс
				$this->cron = // Задания на кроне time,file,modul,function,active,
				$this->_dependClass =
				array();
		$this->childs = new modul_child($this);
		$this->ordfield = '';
		$this->_clp = array();
		$this->data = array();
		$this->parent_id = NULL;
		$this->null = NULL;
          $this->id = NULL;
		$this->_file_cfg = NULL;
		$this->grant_sql = false;
		return true;
	}

	protected function _create_conf() { // Здесь можно установить стандартные настройки модулей
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
		if($this->cf_fields) {
			$this->config['cf_fields'] = array();
			$this->config_form['cf_fields'] = array('type' => 'cf_fields', 'caption' => 'Дополнительные поля формы');
			// TODO form create
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
				$this->config = $data+$this->config;
			}
		}
		return true;
	}

	protected function _create() {
		if ($this->tablename)
			$this->tablename = $this->SQL_CFG['dbpref'] . $this->tablename; // название таблицы
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
		if (is_bool($this->mf_notif) and $this->mf_notif)
			$this->mf_notif = 'mf_notif';

		if(!$this->_listname)
			$this->_listname = ($this->mf_namefields ? $this->mf_namefields : 'id');

		// construct fields
		if ($this->mf_use_charid) {
			if (is_bool($this->mf_use_charid))
				$this->mf_use_charid = 63; // длина поля ID
			$this->fields['id'] = array('type' => 'varchar', 'width' => $this->mf_use_charid, 'attr' => 'NOT NULL');
		}
		else
			$this->fields['id'] = array('type' => 'int', 'width' => 11, 'attr' => 'UNSIGNED NOT NULL AUTO_INCREMENT');

		if ($this->mf_namefields)
			$this->fields[$this->mf_namefields] = array('type' => 'varchar', 'width' => '255', 'attr' => 'NOT NULL', 'default' => '');

		if ($this->owner) {
			$this->fields[$this->owner_name] = $this->owner->fields['id'];
			if (stripos($this->fields[$this->owner_name]['attr'], 'UNSIGNED') !== false)
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

		if ($this->mf_notif) {
			$this->fields[$this->mf_notif] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);
			$this->index_fields[$this->mf_notif] = $this->mf_notif;
			$this->cron[] = array('modul'=>$this->_cl,'function'=>'sendNotif()','active'=>0,'time'=>3600);
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
			$this->fields[$this->mf_ipcreate] = array('type' => 'bigint', 'noquote' => true, 'width' => 20, 'attr' => 'NOT NULL', 'default' => '0');

		/* if ($this->mf_typectrl)
		  $this->fields['typedata'] = array('type' => 'tinyint', 'attr' => 'unsigned NOT NULL');
		 */
		if ($this->mf_ordctrl) { //Содание полей для сортировки
			$this->fields[$this->mf_ordctrl] = array('type' => 'int', 'width' => '10', 'attr' => 'NOT NULL', 'default' => '0');
			$this->ordfield = $this->mf_ordctrl;
			$this->index_fields[$this->mf_ordctrl] = $this->mf_ordctrl;
			$this->_AllowAjaxFn['_sorting'] = true;
		}

		if($this->cf_fields and count($this->config['cf_fields'])) {
			foreach($this->config['cf_fields'] as $fk=>$fr) {
				$this->fields[$fk] = $fr;
				if(isset($fr['unique']) and $fr['unique']) {
					$this->unique_fields[$fk] = $fk;
				}
				if(isset($fr['index']) and $fr['index']) {
					$this->index_fields[$fk] = $fk;
				}
			}
		}

		$this->attprm = array('type' => 'varchar(4)', 'attr' => 'NOT NULL DEFAULT \'\'');

		$this->_pa = $this->_cl . '_pn'; // ключ страницы в ссылке
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

	public function _childs() {
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

	// В этой функции устанавливается хук
	protected function _setHook() {
		$this->setHook = array(); //Перехватчик в предустановки модуля
	}

	// Дефолтные значения
	protected function _setDefaultRecords() {
	}

	/**
	 * Экранирует специальные символы в строках для использования в выражениях SQL
	 */
	public function SqlEsc($val) {
		assert('!is_array($val)', ' * '.print_r($val, true));
		return $this->SQL->escape((string)$val);
	}

	public function queryEscape($where, $union='and') {
		if(is_array($where)) {
			$where2 = array();
			foreach($where as $k=>$r) {
				if(isset($this->fields[$k]))
					$where2[$k] = '`'.$k.'`="'.$this->SqlEsc($r).'"';
			}
			if(count($where2))
				$where = 'WHERE '.implode(' '.$union.' ',$where2);
			else
				$where = '';
		}
		elseif(isint($where)) {
			if((int)$where>0)
				$where = 'WHERE `id`="'.(int)$where.'"';
			else
				$where = '';
		}
		else {
			//TODO : экранировать запрос от инъекций
		}
		/*if(stripos($where, 'where')===false)
				$where = 'WHERE '.$where;*/
		return $where;
	}

	// Простой запрос к БД
	public function exec($query, $debug=false)
	{
		if ($debug)
			echo(' * '.htmlentities($query) . ' * <br>');
		$result = $this->SQL->execSQL($query);
		if ($result->err)
			return false; //todo exeption
		return $result;
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
	public function _query($list = '', $cls = '', $ord = '', $ord2 = '', $debug = false) { // this func. dont use $this->data
		$query = 'SELECT ';
		if (is_array($list))
			$query .= implode(', ', $list);
		elseif ($list)
			$query .= $list;
		else
			$query .= '*';
		$query .= ' FROM `' . $this->tablename . '` ';
		$query .= $this->queryEscape($cls);

		$data = array();

		$result = $this->exec($query, $debug);
		if ($result===false)
			return $data;

		foreach($this->fields as $kf=>$rf) {
			if(isset($rf['secure']))
				$secureKey[] = $kf;
		}
		if ($ord != '' and $ord2 != '') {
			while ($row = $result->fetch()) {
				if(isset($secureKey)) {
					foreach($secureKey as $sk)
						$row[$sk] = static_main::EnDecryptString($row[$sk]);
				}
				$data[$row[$ord2]][$row[$ord]] = $row;
			}
		} elseif ($ord != '') {
			while ($row = $result->fetch()) {
				if(isset($secureKey)) {
					foreach($secureKey as $sk)
						$row[$sk] = static_main::EnDecryptString($row[$sk]);
				}
				$data[$row[$ord]] = $row;
			}
		} else {
			while ($row = $result->fetch()) {
				if(isset($secureKey)) {
					foreach($secureKey as $sk)
						$row[$sk] = static_main::EnDecryptString($row[$sk]);
				}
				$data[] = $row;
			}
		}
		if (count($data) and !$this->_select_attaches($data))
			trigger_error('Ошибка выборки ATTACHES в классе ' . $this->_cl, E_USER_WARNING);
		if (count($data) and !$this->_select_memos($data))
			trigger_error('Ошибка выборки MEMOS в классе ' . $this->_cl, E_USER_WARNING);
		if (isset($this->_CFG['hook']['_query']))
			$this->__do_hook('_query', func_get_args());
		return $data;
	}

	/**
	 * Синоним _query
	 */
	public function qs($list = '', $cls = '', $ord = '', $ord2 = '', $debug = false) {
		return $this->_query($list, $cls, $ord, $ord2, $debug);
	}
	protected function _tableClear() {
		$this->SQL->_tableClear($this->tablename);
	}


	/**
	 * Проверка на существование записи в БД
	 * в this->id записывает ID выбранных данных
	 * @return bool - true если успех
	 */
	public function _isset($where=NULL) {
		if(!is_null($where) and $where!==false) {
			$where = $this->queryEscape($where);
			if(!$where)  {
				trigger_error('Ошибка! Условие запроса пустое!', E_USER_WARNING);
				return false; // TODO : need error reporting
			}

			$this->id = null;
			$res = $this->_query('id', $where, 'id');
			if($res and count($res)) {
				$this->id = array_keys($res);
				$this->id = array_combine($this->id, $this->id);
				return true;
			}
		}
		else {
			return $this->SQL->_tableExists($this->tablename);
		}
		return false;
	}

	/**
	 * Запрос к БД , использует $this->id если он есть в качестве выборки
	 * возвращает в массив $this->data
	 *
	 * @return bool - true если успех
	 */
	public function _select($sql=null, $simple=false) {
		// TODO : избавиться от $cls либо заэкранировать
		$data = array();
		$data = $this->_select_fields($sql, $simple);
		if (count($data)) {
			$this->_select_attaches($data);
			$this->_select_memos($data);
			reset($data);
		}
		return $data;
	}


	private function _select_fields($sql=null, $simple=false) {
		$data = array();
		$q_select = '*, ' . $this->_listname . ' as name';
		$q_where = array();
		$q_order = '';
		
		if ($this->ordfield)
			$q_order = $this->ordfield;

		if (isset($this->id) and $this->id) // либо по ID
			$q_where[] = 'id IN (' . $this->_id_as_string() . ')';
		elseif (isset($this->owner->id) and $this->owner->id) // либо по owner id
			$q_where[] = $this->owner_name.' IN (' . $this->owner->_id_as_string() . ')';

		if(!is_null($sql)) {
			if(is_array($sql)) {
				if(isset($sql['select']) and $sql['select'])
					$q_select = $sql['select'];
				if(isset($sql['where']) and $sql['where'])
					$q_where[] = $sql['where'];
				if(isset($sql['order']) and $sql['order'])
					$q_order = $sql['order'];
			}
			elseif($sql)
				$q_where[] = $sql;
		}

		if(count($q_where))
			$q_where = ' WHERE '.implode(' and ', $q_where);
		else
			$q_where = ' ';

		if($q_order)
			$q_order = ' ORDER BY '.$q_order;

		$result = $this->exec('SELECT '.$q_select.' FROM `' . $this->tablename . '` '.$q_where.$q_order);

		if($result===false)
			return $data;

		if(!$simple) {
			$listAr = array();
			$fields = $result->fetch_fields();
			foreach($fields as $fr) {
				if(isset($this->fields_form[$fr->name]) and $this->fields_form[$fr->name]['type']=='list') {
					$listAr[$fr->name] = array();
				}
			}
			if(!count($listAr))
				$simple = true;
		}

		while ($row = $result->fetch()) {
			if(!$simple) {
				foreach($listAr as $k=>$r) {
					$listAr[$k][$row[$k]] = $row[$k];
				}
			}
			$data[$row['id']] = $row;
		}

		if(!$simple) {
			foreach($listAr as $k=>&$r) {
				if(count($r)) {
					$r = $this->_getCashedList($this->fields_form[$k]['listname'], $r);
				}
			} unset($r);



			foreach($data as &$row) {
				foreach($listAr as $k=>$r) {
					if(isset($r[$row[$k]]))
						$row['#'.$k.'#'] = $r[$row[$k]];
				}
			}
		}

		if (isset($this->id) and $this->id) {
			reset($data);
			if(count($data)==1)
				$this->id = key($data);
			elseif(count($data)>1)
				$this->id = array_keys($data);
			else
				$this->id = NULL;
		}
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
					if (!$row[$key])
						continue;
					$row['_ext_' . $key] = $row[$key];
					$row[$key] = $this->_get_file($row['id'], $key, $row[$key]);
					//$row[$key] = $this->_get_file($row['id'], $key, $row[$key]);
					if (isset($value['thumb'])) {
						foreach ($value['thumb'] as $kk => $rr) {
							if (isset($rr['pref']) and $rr['pref']) {
								$row[$rr['pref'] . $key] = $this->_get_file($row['id'], $key, $row['_ext_' . $key], $kk);
								$row['#' . $rr['pref'] . $key] = $rr;
							}
						}
					}
				}
			}//end foreach
		}
		return true;
	}

	private function _select_memos(&$data = '') {
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

	public function _select_id_tree(array $id, $format='simple') { // TODO const
		$list = array();
		if(!$this->mf_istree or !count($id)) return $list;
		$result = $this->exec('SELECT id FROM `' . $this->tablename . '` WHERE  '.$this->mf_istree.' IN ('.implode(',',$id).')');

		if($result===false)
			return $list;

		while ($row = $result->fetch()) {
			$list[] = $row['id'];
		}
		if(count($list)) {
			$subList  = $this->_select_id_tree($list);
			if(count($subList))
				$list = array_merge($list, $subList);
		}
		return $list;
	}


	/**
	 * Удаление данных
	 *
	 * @return bool
	 */
	public function _delete() {
		$result = static_form::_delete($this, $this->id);
		if ($result) {
			$this->allChangeData('delete');
			$this->id = NULL;
		}
		return $result;
	}

	/**
	 * Сохранение данных form , Функция добавления записей в бд
	 * В случае успеха выполняет allChangeData('add')
	 *
	 * @return bool
	 */
	public function _add($data = array(), $flag_select = true, $flag_update=false) {
		if (is_bool($data)) {
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
		$result = static_form::_add($this, $flag_select, $flag_update);
		if ($result)
			$this->allChangeData('add');
		return $result;
	}

	/**
	 * Сохранение данных formФункция добавления записей в бд
	 * В случае успеха выполняет allChangeData('add')
	 *
	 * @return bool
	 */
	public function _addUp($data, $flag_select = true) {
		return $this->_add($data, $flag_select, true);
	}

	/**
	 * Обновление данных по $this->id или $where
	 * @param ARRAY $data
	 * @param STRING $where 
	 * @param BOOL $flag_select - выборка данных после обновления ($this->data) 
	 * @return BOOL
	 */
	public function _update($data = array(), $where = NULL, $flag_select = true) {
		if (!is_array($data) or !count($data)) {
			trigger_error('Устаревший метод вызова _save_item -> первый параметр $data', E_USER_WARNING);
			return false;
		}

		$this->fld_data = $this->att_data = $this->mmo_data = array();
		foreach ($data as $k => $r) {
			if (isset($this->memos[$k]))
				$this->mmo_data[$k] = $r;
			elseif (isset($this->attaches[$k]))
				$this->att_data[$k] = $r;
			elseif (isset($this->fields[$k]))
				$this->fld_data[$k] = $r;
		}

		// Если задан $where - получаем список IDшников
		if(!is_null($where) and $where!==false) {
			if(!$this->_isset($where)) return false; // в $this->id вносит выбранные ID
		}

		$result = static_form::_update($this, $flag_select);
		if ($result)
			$this->allChangeData('save');
		return $result;
	}

	/**
	 * Обновление данных выбранные по ID родителя
	 * @param ARRAY $data
	 * @param BOOL $flag_select - выборка данных после обновления ($this->data) 
	 * @return BOOL
	 */
	protected function _updateByOwner($data = array(), $flag_select = true) {
		if(!$this->owner) {
			trigger_error('Error update: CLASS don`t have OWNER', E_USER_WARNING);
			return false;
		}
		$where = $this->owner->_id_as_string();
		if(!$where) {
			trigger_error('Error update: miss OWNER id', E_USER_WARNING);
			return false;
		}
		$where = $this->owner_name.' IN ('.$where.')';
		return $this->_update($data, $where, $flag_select);
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
	public function _get_file($id, $key, $extValue = '', $modkey = -1) {
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
		return $this->_as_string($this->id);
	}

	public function _as_string($list) {
		if (is_array($list)) {
			if(!count($list)) return 0;
			foreach($list as &$value)
			  $value = $this->SqlEsc($value); 
			return '\'' . implode('\',\'', $list) . '\'';
		}
		else {
			if(!$list) return 0;
			return '\'' . $this->SqlEsc($list) . '\'';
		}
	}

	public function _get_new_ord() {
		$query = 'SELECT max(' .
				(($this->mf_use_charid and $this->mf_ordctrl) ? $this->mf_ordctrl : 'id')
				. ') FROM `' . $this->tablename . '`';
		if ($this->mf_istree and $this->parent_id and !$this->fld_data[$this->mf_istree])
			$query .= ' WHERE ' . $this->mf_istree . '=' . $this->parent_id;
		$result = $this->exec($query);
		if ($result===false)
			return 0;
		list($ordind) = $result->fetch_row();
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

	public function _prmModulEdit($dataList, $param = array()) {
		if (!$this->prm_edit)
			return false;
		if (isset($param['prm']) and count($param['prm'])) {
			foreach ($param['prm'] as $k => $r) {
				foreach ($dataList as $row)
					if (!isset($row[$k]) and $row[$k] != $r)
						return false;
			}
			return true;
		}
		if (static_main::_prmModul($this->_cl, array(3)))
			return true;
		if ($this->mf_createrid and static_main::_prmModul($this->_cl, array(4))) {
			foreach ($dataList as $k => $r)
				if ($r[$this->mf_createrid] != $_SESSION['user']['id'])
					return false;
			return true;
		}
		return false;
	}

	public function _prmModulDel($dataList, $param = array()) {//$dataList нельзя по ссылке
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

	public function _prmModulAct($dataList, $param = array()) {//$dataList нельзя по ссылке
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

	public function _prmModulShow(array $dataList, array $param = array()) {
		if (static_main::_prmModul($this->_cl, array(1)))
		{
			return true;
		}
		if ($this->mf_createrid and static_main::_prmModul($this->_cl, array(2))) 
		{
			foreach ($dataList as $k => $r)
				if ($r[$this->mf_createrid] != $_SESSION['user']['id'])
					return false;
			return true;
		}
		return false;
	}

	/**
	* Определяет необходимость создания запроса по создателю
	*/
	public function _prmModulShowCriteria(array $param = array()) {

		if (static_main::_prmModul($this->_cl, array(1)))
		{
			return false; 
		}
		if ($this->mf_createrid and static_main::_prmModul($this->_cl, array(2))) 
		{
			return true;
		}

		return false;
	}
	

	public function _prmSortField($key='') {
		//включаем сортировку для всех полей
		/* if (isset($this->fields_form[$key]['mask']['sort']))
		  return true;
		  elseif ($key == $this->mf_namefields or $key == 'ordfield' or $key == $this->mf_actctrl) */
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
	public function _UpdItemModul($param = array(), &$argForm = null) {
		if(is_null($argForm)) {
			$this->getFieldsForm(1);
			$argForm = $this->fields_form;
		}
		return static_control::_UpdItemModul($this, $param, $argForm);
	}

	/**
	 * Пре обработка формы
	 * @param array $data данные
	 * @param array $param параметры
	 * @return array
	 */
	public function kPreFields(&$f_data, &$f_param = array(), &$f_fieldsForm = null) {
		if(is_null($f_fieldsForm)) {
			$this->getFieldsForm(1);
			$f_fieldsForm = &$this->fields_form;
		}

		$my_fieldsForm = array();

		foreach ($f_fieldsForm as $k => $r) {
			if(!isset($r['type'])) continue;
			if (isset($r['readonly']) and $r['readonly'] and $this->id) // если поле "только чтение" и редактируется , то значение берем из БД,
				$f_data[$k] = (isset($this->data[$this->id][$k]) ? $this->data[$this->id][$k] : '');

			$eval = static_form::getEvalForm($this, $r);

			if ($eval!=='') 
			{
				if (isset($f_data[$k]))
					$val = $f_data[$k]; // Переменная используемая в eval
				else
					$val = '';
				
				if (substr($eval, -1) != ';')
				{
					$eval = '"'.addcslashes($eval, '"').'";';
				}
				$eval = '$f_data[$k]=' . $eval;
				eval($eval);
				unset($eval);
			}
			elseif ((isset($r['mask']['fview']) and $r['mask']['fview'] == 2) or (isset($r['mask']['usercheck']) and !static_main::_prmGroupCheck($r['mask']['usercheck']))) 
			{
				$r['mask']['fview'] = 2;
				unset($f_data[$k]);
				continue;
			}

			if(!isset($r['fields_type']))
			{
				if(isset($this->fields[$k]))
					$r['fields_type'] = $this->fields[$k]['type'];
				else
					$r['fields_type'] = $r['type'];
			}

			if (isset($this->attaches[$k]))
				$r = $r + $this->attaches[$k];
			if (isset($this->memos[$k]))
				$r = $r + $this->memos[$k];

			//на всякий
			if (!isset($r['mask']['width']) and isset($this->fields[$k]['width']))
				$r['mask']['width'] = $this->fields[$k]['width'];
			if (!isset($r['default']) and isset($this->fields[$k]['default']))
				$r['default'] = $this->fields[$k]['default'];

			if ($k == $this->owner_name and !isset($f_data[$k])) {
				if (!isset($this->owner->id) and $this->owner->mf_use_charid)
					$this->owner->id = '';
				elseif (!isset($this->owner->id))
					$this->owner->id = 0;
				$r['value'] = $this->owner->id;
			}
			elseif ($k == $this->mf_istree and !isset($f_data[$k])) {
				if (isset($this->parent_id) and $this->parent_id)
					$r['value'] = $this->parent_id;
				elseif (!isset($this->parent_id) and $this->mf_use_charid)
					$this->parent_id = '';
				elseif (!isset($this->parent_id))
					$this->parent_id = 0;
			}
			elseif ($r['type'] == 'ckedit') {
				if (isset($this->memos[$k]) and !count($_POST) and file_exists($f_data[$k]))
					$r['value'] = file_get_contents($f_data[$k]);
				elseif (isset($f_data[$k]))
					$r['value'] = $f_data[$k];
			}
			elseif (isset($r['multiple']) and $r['multiple'] > 0 and $r['type'] == 'list') {
				if (isset($f_data[$k])) {
					if (!is_array($f_data[$k])) {
						$f_data[$k] = trim($f_data[$k], '|');
						$r['value'] = explode('|', $f_data[$k]);
					}else
						$r['value'] = $f_data[$k];
					$r['value'] = array_combine($r['value'],$r['value']); // На всякий, иногда эта функция может самостоятельно работать
				}
			}
			elseif ($r['type'] == 'date') {
				if (!isset($r['mask']['format']) or !$r['mask']['format'])
					$r['mask']['format'] = 'Y-m-d H:i:s';
			}
			/* elseif ($r['type'] == 'checkbox') {
			  $f_data[$k] = $r['value'] = ((isset($f_data[$k]) and $f_data[$k])?1:0);
			  } */
			if (isset($f_data[$k]) and !isset($r['value'])) //  and $f_data[$k]
				$r['value'] = $f_data[$k];

			if (isset($this->id) and isset($this->data[$this->id]['_ext_' . $k]))
				$r['ext'] = $this->data[$this->id]['_ext_' . $k];

			if (!isset($r['comment']))
				$r['comment'] = '';

			//*****************
			$my_fieldsForm[$k] = $r;

			// Зависимые формы
			if (isset($r['relationForm']))
			{
				//TODO - change script
				if($r['relationForm']===true) // default function
					$r['relationForm'] = 'relationForm';

				if(method_exists($this, $r['relationForm'])) 
				{
					call_user_func_array(
						array($this, $r['relationForm']), 
						array($r['value'], &$my_fieldsForm)
					);//$r['relationForm']
					//$f_fieldsForm = static_main::insertInArray($f_fieldsForm, $k, $my_fieldsForm);
				} 
				else 
				{
					trigger_error('Метод `'.$r['relationForm'].'` не определен в модуле '.$this->_cl, E_USER_WARNING);
				}
			}
			//end foreach
		}
		$f_fieldsForm = $my_fieldsForm;

		if (!isset($f_param['captchaOn'])) {
			if (!isset($_SESSION['user']['id']))
				$f_param['captchaOn'] = true;
			else
				$f_param['captchaOn'] = false;
		}
		if (count($f_fieldsForm) and $f_param['captchaOn']) {
			$LEN = 5;
			$DIF = 1;
			if (is_array($f_param['captchaOn'])) {
				if (isset($f_param['captchaOn']['len']))
					$LEN = $f_param['captchaOn']['len'];
				if (isset($f_param['captchaOn']['dif']))
					$DIF = $f_param['captchaOn']['dif'];
			}
			//$LEN,$DIF
			$f_fieldsForm['captcha'] = array(
				'type' => 'captcha',
				'caption' => static_main::m('_captcha', $this),
				'captcha' => static_form::getCaptcha(),
				'src' => $this->_CFG['_HREF']['captcha'] . '?' . rand(0, 9999),
				'value' => (isset($f_data['captcha']) ? $f_data['captcha'] : ''),
				'mask' => array('min' => 1, 'max' => $LEN, 'dif' => $DIF));
			if (0) {// TODO /
				$f_fieldsForm['captcha']['error'] = array('У вас отключены Куки');
			}
		}

		$mess = array();
		if (isset($this->mess_form) and count($this->mess_form))
			$mess = $this->mess_form;
		if (!count($f_fieldsForm))
			$mess[] = array('name' => 'error', 'value' => static_main::m('nodata', $this));
		if (isset($this->_CFG['hook']['kPreFields']))
			$this->__do_hook('kPreFields', func_num_args());
		return $mess;
	}

	/**
	 * Создание данных для формы
	 * @param mixed $form - 1 = форма
	 * @return array
	 */
	function getFieldsForm($form = 0) {
		$this->setFieldsForm($form);
		/* $temp = $this->fields_form;
		  $this->fields_form = array();
		  foreach($temp as $k=>$r) {
		  if($r['type']=='ckedit') {
		  //$this->fields[$k.'_ckedit'] = array('type' => 'tinyint', 'width'=>3, 'attr' => 'NOT NULL','default'=>'1');
		  if($form>0 and static_main::_prmUserCheck(1))
		  $this->fields_form[$k.'_ckedit'] = array('type' => 'list', 'listname'=>'wysiwyg', 'caption' => $r['caption'].' - Выбор редактора', 'onchange'=>'SetWysiwyg(this)','mask'=>array('usercheck'=>1));
		  }
		  elseif($r['type']=='list') {
		  if(!isset($r['listname']))
		  $r['listname'] = 'list';
		  }
		  $this->fields_form[$k] = $r;
		  } */

		if($this->cf_fields and is_array($this->config['cf_fields']) and count($this->config['cf_fields'])) {
			foreach($this->config['cf_fields'] as $fk=>$fr) {
				if(isset($fr['ftype']))
					$fr['type'] = $fr['ftype'];
				$this->fields_form[$fk] = $fr;
			}
		}

		if ($form==0 and count($this->formDSort)) {
			$temp = $this->fields_form;
			$this->fields_form = array();
			foreach ($this->formDSort as $rr) {
				if ($rr == '#over#') {
					$diffForm = array_diff_key($temp, array_keys($this->formdSort));
					$this->fields_form = $diffForm+$this->fields_form;
				} elseif (isset($temp[$rr])) {
					$this->fields_form[$rr] = $temp[$rr];
				}
			}
		} elseif ($form==1 and count($this->formSort)) {
			$formSort = array();
			if(is_array(current($this->formSort))) {
				foreach($this->formSort as $tr) {
					if(is_array($tr))
						$formSort = array_merge($formSort, $tr);
					else
						$formSort[] = $tr;
				}
			}
			else
				$formSort = $this->formSort;
			$temp = $this->fields_form;
			$this->key_formSort = array_flip($formSort);
			if (isset($this->key_formSort['#over#'])) {
				$over = array_diff_key($temp, $this->key_formSort);
			}
			$this->fields_form = array();
			foreach ($formSort as $rr) {
				if ($rr == '#over#') {
					$this->fields_form = $over+$this->fields_form;
				} elseif (isset($temp[$rr])) {
					$this->fields_form[$rr] = $temp[$rr];
				}
			}
		}

		if(isset($this->HOOK['getFieldsForm'])){
			call_user_func($this->HOOK['getFieldsForm'],$this);
		}
		return true;
	}

	public function setFieldsForm($form = 0) {
		$this->fields_form = array();
		return true;
	}

	/**
	 * Создание данных для формы
	 * @param mixed $param - параметры
	 * @return array
	 */
	public function kFields2Form(&$param, &$fields_form=null) {
		/*
		  $fields_form['уник название'] = array(
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
		if(is_null($fields_form)) {
			//$this->getFieldsForm(1); //обнуляем форму
			$fields_form = &$this->fields_form;
		}
		if (!is_array($fields_form) or !count($fields_form))
			return false;
		$fields_form['_*features*_'] = array('name' => $this->_cl, 'method' => 'post', 'id' => $this->id, 'action' => $_SERVER['REQUEST_URI']);
		$fields_form = array('_info'=>array('type' => 'info', 'css' => 'caption')) + $fields_form;
		if ($this->id)
			$fields_form['_info']['caption'] = static_main::m('update_name', array($this->caption), $this);
		else
			$fields_form['_info']['caption'] = static_main::m('add_name', array($this->caption), $this);

		$this->kFields2FormFields($fields_form);
		if (!$this->id or (isset($this->data[$this->id]) and $this->_prmModulEdit($this->data, $param))) {
			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value_save' => ((isset($param['sbmt_save']) and $this->id) ? static_main::m('Save', $this) : ''),
				'value_close' => (isset($param['sbmt_close']) ? static_main::m('Close', $this) : ''),
				'value' => static_main::m('Save and close', $this)
			);
			if ($this->id and $this->_prmModulDel($this->data, $param) and isset($param['sbmt_del']))
				$fields_form['sbmt']['value_del'] = static_main::m('Delete', $this);
		}
		return true;
	}

	/**
	 * Корректировака и обработка формы для вывода формы
	 * @param mixed $fields - название списока или массив данных для списка
	 * @return array
	 */
	public function kFields2FormFields(&$fields, $method='GET') {
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
	public function _checkList(&$listname, $value = NULL) {
		return static_list::_checkList($this, $listname, $value);
	}

	/**
	 * Получение списка из кеша если он там есть
	 * @param mixed $listname - название списока или массив данных для списка
	 * @param mixed $value - значение
	 * @return array Список
	 */
	public function &_getCashedList($listname, $value = NULL) {
		return static_list::_getCashedList($this, $listname, $value);
	}

	public function _getlist($listname, $value = NULL) {/* LIST SELECTOR */
		return static_list::_getlist($this, $listname, $value);
	}

	//Универсальный обработчик вывода данных
	public function super_inc($PARAM = array(), $ftype = '') {
		return static_super::super_inc($this, $PARAM , $ftype);
	}

	// вывод данных
	public function _displayXML(&$param) {
		return static_super::_displayXML($this, $param);
	}

// MODUL configuration

	/**
	 * Переустановка БД модуля
	 * @return array form
	 */
	public function toolsReinstall() {
		return static_tools::toolsReinstall($this);
	}

	public function toolsConfigmodul() {
		return static_tools::toolsConfigmodul($this);
	}

	/**
	 * Групповые операции
	 * TODO : это контрол - нужно его вынести из модуля
	 * @return array form
	 */
	public function toolsSuperGroup() {
		return static_tools::toolsSuperGroup($this);
	}

	/**
	 * Статистика модуля
	 * @param array $oid 
	 * @return array
	 */
	public function toolsStatsmodul($oid = '') {
		return static_tools::toolsStatsmodul($this, $oid);
	}
	
	/*public function toolsReindex(){
		return static_tools::toolsReindex($this, $oid);
	}*/

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
		$fields_form = array();
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
			$fields_form = $this->Formfilter();
		return Array('filter' => $fields_form, 'messages' => array());
	}

	/**
	 * Создает форму данных для фильтра super_inc
	 * @return array form
	 */
	function Formfilter() {
		$_FILTR = array();
		if (isset($_SESSION['filter'][$this->_cl]))
			$_FILTR = $_SESSION['filter'][$this->_cl];
		$fields_form = array();
		$this->getFieldsForm(1);
		foreach ($this->fields_form as $k => &$r) {
			if(!isset($r['caption']) or !$r['caption']) continue;
			if($r['type']=='hidden' or !isset($this->fields[$k])) continue;
			//if (isset($r['mask']['filter']) and $r['mask']['filter'] == 1) {
				unset($r['default']);
				if ($r['type'] == 'list' && is_array($r['listname']) && !isset($r['listname']['idThis']))
					$r['listname']['idThis'] = $k;
				$fields_form['f_' . $k] = $r;
				unset($fields_form['f_' . $k]['readonly']);
				$fields_form['f_' . $k]['value'] = '';
				$fields_form['f_' . $k]['value_2'] = '';
				if (isset($_FILTR[$k])) {
					if ($r['type'] == 'date') 
					{
						$fields_form['f_' . $k]['value_2'] = date('Y-m-d',$_FILTR[$k . '_2']);
						$fields_form['f_' . $k]['value'] = date('Y-m-d',$_FILTR[$k]);
					}
					else
					{
						if (isset($_FILTR[$k . '_2']))
							$fields_form['f_' . $k]['value_2'] = $_FILTR[$k . '_2'];
						$fields_form['f_' . $k]['value'] = $_FILTR[$k];
					}
				}
				if ($r['type'] == 'ajaxlist') {
					if (!isset($fields_form['f_' . $k]['label']))
						$fields_form['f_' . $k]['label'] = 'Введите текст';
					$fields_form['f_' . $k]['labelstyle'] = ((isset($_FILTR[$k]) and $_FILTR[$k]) ? 'display: none;' : '');
					$fields_form['f_' . $k]['csscheck'] = ((isset($_FILTR[$k]) and $_FILTR[$k]) ? 'accept' : 'reject');
				}
				elseif ($r['type'] != 'radio' and $r['type'] != 'checkbox' and $r['type'] != 'list' and $r['type'] != 'int' and $r['type'] != 'file' and $r['type'] != 'ajaxlist' and $r['type'] != 'date')
					$fields_form['f_' . $k]['type'] = 'text';
				if (isset($_FILTR['exc_' . $k]))
					$fields_form['f_' . $k]['exc'] = 1;

			//}
		}
		//фильтр	
		if (count($fields_form)) {
			//$fields_form['_*features*_'] = array('name' => 'Formfilter', 'action' => '', 'method' => 'post');
			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value' => 'Отфильтровать'
			);

			$this->kFields2FormFields($fields_form);

			$fields_form['f_clear_sbmt'] = array(
				'type' => 'info',
				'caption' => '<a href="' . $_SERVER['HTTP_REFERER'] . '" onclick="JSWin({\'insertObj\':\'#form_tools_f'.$this->_cl.'\',\'href\':$(\'#form_tools_f'.$this->_cl.'\').attr(\'action\'),\'data\':{ f_clear_sbmt:1}});return false;">Очистить</a>');
		}
		if(count($_FILTR))
		{
			$fields_form['filterEnabled'] = array('type'=>'hidden');
		}
		return $fields_form;
	}

	/**
	 * задает параметры запроса для super_inc
	 * @param bool $flag - true не ощищать данные
	 * @return array
	 */
	function setFilter($flag = 0) {
		if (isset($_REQUEST['f_clear_sbmt'])) {
			unset($_SESSION['filter'][$this->_cl]);
		} else {
			foreach ($this->fields_form as $k => $row) {
				if (isset($_REQUEST['f_' . $k]) && $_REQUEST['f_' . $k] != '') {// && isset($this->fields_form[$k]['mask']['filter'])
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
		if(!$this->cf_filter or !$this->_prmSortField()) return $cl;

		if (isset($_REQUEST['filter_' . $this->_cl])) {
			if (!is_array($_REQUEST['filter_' . $this->_cl]))
				unset($_SESSION['filter'][$this->_cl]);
			else
				$_FILTR = $_REQUEST['filter_' . $this->_cl];
		}
		elseif (isset($_SESSION['filter'][$this->_cl]))
			$_FILTR = $_SESSION['filter'][$this->_cl];

		if(count($_FILTR)) {
			$this->getFieldsForm(1);
			foreach ($this->fields_form as $k => &$r) {
				//if (isset($r['mask']['filter']) and $r['mask']['filter'] == 1) {
					if (isset($_FILTR[$k]) or isset($_FILTR[$k . '_2'])) {
						$tempex = 0;
						if (isset($_FILTR['exc_' . $k]))
							$tempex = 1;
						if (isset($_FILTR[$k]) and is_array($_FILTR[$k])) {
							array_map(array($this,'SqlEsc'),$_FILTR[$k]);
							$cl[$k] = 't1.' . $k . ' ' . ($tempex ? 'NOT' : '') . 'IN ("' . implode('","', $_FILTR[$k]) . '")';
						}
						else {
							if ($r['type'] == 'checkbox') 
							{
								$cl[$k] = 't1.' . $k . '="' . (int)$_FILTR[$k] . '"';
							}
							elseif ($r['type'] == 'int' or $r['type'] == 'date') {
								$_FILTR[$k] = (int)$_FILTR[$k];
								$_FILTR[$k . '_2'] = (int)$_FILTR[$k . '_2'];
								$tmp = array();
								if (isset($_FILTR[$k]) and $_FILTR[$k]!='')
									$tmp[] = 't1.' . $k . ($tempex?'<':'>') . $_FILTR[$k];
								if (isset($_FILTR[$k . '_2']) and $_FILTR[$k . '_2']!='')
									$tmp[] = 't1.' . $k . ($tempex?'>':'<') . $_FILTR[$k . '_2'];
								if(count($tmp))
									$cl[$k] = '(' . implode(($tempex?' or ':' and '),$tmp) . ')';
							}
							elseif ($r['type'] == 'list') {
								if ($_FILTR[$k]!='') {
									$cl[$k] = 't1.' . $k . '="' . $this->SqlEsc($_FILTR[$k]) . '"';
								}
							} elseif ($_FILTR[$k] == '!0')
								$cl[$k] = 't1.' . $k . '!=""';
							elseif ($_FILTR[$k] == '!1')
								$cl[$k] = 't1.' . $k . '=""';
							else
								$cl[$k] = 't1.' . $k . ' ' . ($tempex ? 'NOT ' : '') . 'LIKE "' . $this->SqlEsc($_FILTR[$k]) . '"';
						}
					}
				//}
			}
		}
		return $cl;
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
		if (isset($param['mess']))
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
					$DATA[] = static_main::am('ok', 'act5', $this);
				if ($data[$this->mf_actctrl] == 6)
					$DATA[] = static_main::am('ok', 'act6', $this);
				elseif ($act)
					$DATA[] = static_main::am('ok', 'act1', $this);
				else
					$DATA[] = static_main::am('ok', 'act0', $this);
				$flag = 0;
			}
			else
				$DATA[] = static_main::am('error', 'update_err', $this);
		}
		else
			$DATA[] = static_main::am('error', 'denied', $this);
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
					$DATA[] = static_main::am('ok', 'deleted', $this);
					$flag = 0;
				}else
					$DATA[] = static_main::am('error', 'del_err', $this);
			}else {
				if ($this->_delete()) {
					$DATA[] = static_main::am('ok', 'deleted', $this);
					$flag = 0;
				}else
					$DATA[] = static_main::am('error', 'del_err', $this);
			}
		}
		else
			$DATA[] = static_main::am('error', 'denied', $this);
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
			$data[$this->mf_ordctrl] = $this->data[$this->id][$this->mf_ordctrl] + $ord;

			if ($this->_update($data)) {
				if ($ord < 0)
					$DATA[] = array('value' => 'UP', 'name' => 'ok');
				else
					$DATA[] = array('value' => 'DOWN', 'name' => 'ok');
				$flag = 0;
			}
			else
				$DATA[] = static_main::am('error', 'update_err', $this);
		}
		else
			$DATA[] = static_main::am('error', 'denied', $this);
		return array($DATA, $flag);
	}

	/**
	 * Сортировка 
	 * @return array
	 */
	public function _sorting() {
		$res = array('html' => '', 'eval' => '');
		$this->id = $id = (int) $_GET['id'];
		$pid = (isset($_GET['pid']) ? (int) $_GET['pid'] : 0);
		$t1 = (isset($_GET['t1']) ? (int) $_GET['t1'] : 0);
		$t2 = (isset($_GET['t2']) ? (int) $_GET['t2'] : 0);
		$data = $this->_select();

		if (!$this->mf_ordctrl or !$this->_prmModulEdit($data)) {//!static_main::_prmModul($this->_cl,array(10))
			$res['html'] = static_main::m('Sorting denied!');
			return $res;
		}
		$res['html'] = static_main::m('Sorting error');


		if ($t2) {
			$data = $this->qs($this->mf_ordctrl, 'WHERE id=' . $t2);
			$neword = $data[0][$this->mf_ordctrl];

			$qr = '`' . $this->mf_ordctrl . '`>=\'' . $neword . '\'';
			if ($this->mf_istree and $pid)
				$qr .= ' and `' . $this->mf_istree . '`=' . $pid;
			$this->fields[$this->mf_ordctrl]['noquote'] = true;
			if (!$this->_update(array($this->mf_ordctrl => '`' . $this->mf_ordctrl . '`+1'), $qr, false))
				return $res;
			$this->id = $id;
			if (!$this->_update(array($this->mf_ordctrl => $neword)))
				return $res;
		}else {
			$qr = '';
			if ($this->mf_istree and $pid)
				$qr .= ' WHERE `' . $this->mf_istree . '`=' . $pid;

			$data = $this->qs('max(' . $this->mf_ordctrl . ') as mx', $qr);
			$neword = $data[0]['mx'] + 1;
			$this->id = $id;
			if (!$this->_update(array($this->mf_ordctrl => $neword)))
				return $res;
		}
		$res['html'] = ''; //static_main::m('Sorting successful.')
		return $res;
	}

	/**
	 * при успешном изменении данных 
	 * @param string $type - add, save, delete
	 * @param array $data - данные 
	 * @return bool
	 */
	function allChangeData($type = '', $data = '') {
		return true;
	}

	/**
	 * Преобразование списка в шаблонный список для формы
	 * @param array $path - путь
	 * @return string XML
	 */
	public function _forlist(&$data, $id=0, $select = '', $multiple = 0) {
		/*
		  array('name'=>'NAME','id'=>1 [, 'sel'=>0, 'checked'=>0])
		 */
		//$select - array(значение=>1)
		$s = array();
		if(!is_array($data) or !count($data)) return $s;

		if ($multiple == 2 and is_array($select) and count($select)) {
			foreach ($select as $sr) {
				foreach ($data as $kk => $kd) {
					if (isset($kd[$sr])) {
						$s[$sr] = array('#id#' => $sr, '#sel#' => 1);
						if (is_array($kd[$sr]) and isset($kd[$sr]['#name#']))
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
			$temp = &$data[$id];
		else
			$temp = &$data;

		foreach ($temp as $key => $value) {
			if ($select != '' and is_array($select)) {
				if (isset($select[$key])) {
					if ($multiple == 22)
						continue;
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
			/*Если это использовать то проверка данных сломается*/
			//if (isset($value['#item#']) and is_array($value['#item#']) and count($value['#item#']))
			//	$s[$key]['#item#'] = $value['#item#']+$s[$key]['#item#'];
		}
		return $s;
	}

	// Постраничная навигация
	public function fPageNav($countfield, $thisPage = '') {
		return $this->fPageNav2($countfield, array('firstpath' => $thisPage));
	}

	// Постраничная навигация
	public function fPageNav2($countfield, $param = array()) {
		//$countfield - бщее число элем-ов
		//$$param - массив данных
		//$this->messages_on_page - число эл-ов на странице
		//$this->_pn - № текущей страницы
		$numlist = $this->numlist; // кличество числе по бокам максимум
		$DATA = array('cnt' => $countfield, 'messages_on_page' => $this->messages_on_page, 'cntpage' => 0, 'modul' => $this->_cl, 'reverse' => $this->reversePageN);

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

		/*		 * * PAGE NUM REVERSE ** */
		if ($this->reversePageN) {
			if ($this->_pn == 0)
				$this->_pn = 1;
			else
				$this->_pn = floor($countfield / $this->messages_on_page) - $this->_pn + 1;
			$DATA['cntpage'] = floor($countfield / $this->messages_on_page);
			$temp_pn = $this->_pn;
			$this->_pn = $DATA['cntpage'] - $this->_pn + 1;
		}
		else {
			$DATA['cntpage'] = ceil($countfield / $this->messages_on_page);
		}

		// Приводим к правильным числам
		if ($this->_pn > $DATA['cntpage'])
			$this->_pn = $DATA['cntpage'];
		if ($this->_pn < 1)
			$this->_pn = 1;
		$DATA['_pn'] = $this->_pn;

		foreach ($this->_CFG['enum']['_MOP'] as $k => $r)
			$DATA['mop'][$k] = array('value' => $r, 'sel' => 0);
		$DATA['mop'][$this->messages_on_page]['sel'] = 1;

		$flag = false;
		if ($countfield) {
			if ($this->reversePageN and $countfield >= ($this->messages_on_page * 2))
				$flag = true;
			elseif (!$this->reversePageN and $countfield > $this->messages_on_page)
				$flag = true;
		}

		if ($flag) {
			//$PP[0] - страница не выбрана
			//$PP[1] - первая часть 
			//$PP[2] - вторая часть
			if (!isset($param['firstpath']) or !$param['firstpath'])
				$param['firstpath'] = $_SERVER['REQUEST_URI'];
			$PP = array(0 => $param['firstpath'], 1 => $param['firstpath'], 2 => '');
			if (isset($param['_clp'])) {
				if (count($param['_clp'])) {
					$temp = $param['_clp'];
					unset($temp[$this->_pa]);
					$PP[0] .= http_build_query($temp) . '&';
					$PP[1] = $PP[0];
				}
				$PP[1] .= $this->_pa . '=';
			} else {
				$pregreplPage = '/(.*)_p[0-9]*(.*)/';
				if (!preg_match($pregreplPage, $param['firstpath'], $matches)) {
					$temp = explode('.html', $param['firstpath']);
					$PP[1] = $temp[0] . '_p';
					$PP[2] = '.html' . $temp[1];
				} else {
					$PP[0] = $matches[1] . $matches[2];
					$PP[1] = $matches[1] . '_p';
					$PP[2] = $matches[2];
				}
			}

			$DATA['PP'] = $PP;

			if ($this->reversePageN) {// обратная нумирация
				/* Собираем массив ссылок */
				$DATA['link'][$DATA['cntpage']] = $PP[0];
				if (($this->_pn + $numlist) < $DATA['cntpage'] - 1) {
					$j = $this->_pn + $numlist;
				} else
					$j = $DATA['cntpage'] - 1;
				$vl = $this->_pn - $numlist;
				if ($vl < 2)
					$vl = 2;
				for ($i = $j; $i >= $vl; $i--) {
					$DATA['link'][$i] = $PP[1] . $i . $PP[2];
				}
				$DATA['link'][1] = $PP[1] . '1' . $PP[2];
			} else {
				$DATA['link'][1] = $PP[0];

				if (($this->_pn - $numlist) > 3) {
					$j = $this->_pn - $numlist;
					$DATA['link'][' ...'] = false;
				} else {
					$j = 2;
				}

				$vl = $this->_pn + $numlist;
				if ($vl >= ($DATA['cntpage'] - 2))
					$vl = $DATA['cntpage'] - 1;
				for ($i = $j; $i <= $vl; $i++) {
					$DATA['link'][$i] = $PP[1] . $i . $PP[2];
				}
				if ($vl < $DATA['cntpage'] - 1)
					$DATA['link']['... '] = false;
				$DATA['link'][$DATA['cntpage']] = $PP[1] . $DATA['cntpage'] . $PP[2];

				/* $DATA['link'][1] = $PP[0];
				  for ($i = 2; $i <= $DATA['cntpage']; $i++) {
				  $DATA['link'][$i] = $PP[1].$i.$PP[2];
				  } */
			}
			//////////////////
		}

		$DATA['start'] = 0;
		if ($this->reversePageN) {
			if ($this->_pn == floor($countfield / $this->messages_on_page)) {
				$this->messages_on_page = $countfield - $this->messages_on_page * ($this->_pn - 1); // правдивый
				//$this->messages_on_page = $this->messages_on_page*$this->_pn-$countfield; // полная запись
			}
			else
				$DATA['start'] = $countfield - $this->messages_on_page * $this->_pn; // начало отсчета
		}
		else
			$DATA['start'] = $this->messages_on_page * ($this->_pn - 1); // начало отсчета
		if ($DATA['start'] < 0)
			$DATA['start'] = 0;
		return $DATA;
	}

	/**
	* Генератор сообщений на мыло
	*/
	function sendNotif($email='') {
		global $HTML;
		$param = array(
			'clause'=>array('t1.'.$this->mf_notif=>'t1.'.$this->mf_notif.'=0'),
			'hide_topmenu' => true,
			'hide_child' => true
		);
		list($DATA,$formFlag) = $this->super_inc($param);

		if(isset($DATA['data']['item']) and $cnt = count($DATA['data']['item'])) 
		{
			unset($DATA['data']['pagenum']);
			unset($DATA['path']);

			_new_class('mail',$MAIL);
			$datamail = array(
				'creater_id' => -1,
				'mail_to' => ($email?$email:$MAIL->config['mailrobot']),
				'subject' => strtoupper($_SERVER['HTTP_HOST']).' - Оформленно заказов '.$cnt.'шт.',
				'text' => '<p>Список заказов</p>'. $HTML->transformPHP($DATA,'#pg#superlist'),

			);
			$MAIL->reply = 0;
			$MAIL->config['mailcron'] = 1;
			$MAIL->Send($datamail);
			$result = $this->exec('UPDATE `' . $this->tablename . '` SET '.$this->mf_notif.'=1 WHERE id IN (' . implode(',',array_keys($DATA['data']['item'])).')');
			return '   -sendNotif-  ';
		}
		return '';
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

	/**
	 * AJAX add data function
	 * TODO : вынести в отдельный "модуль-контролер"
	 */
	public function AjaxAdd() {
		global $HTML, $_tpl;
		$RESULT = array('html' => '', 'html2' => '', 'text' => '', 'onload' => '');
		$DATA = array();
		//$htmlb = '';

		if (count($_POST))
			$_POST['sbmt'] = 1;
		list($DATA['formcreat'], $flag) = $this->_UpdItemModul(array('ajax' => 1, 'errMess' => 1));
		$RESULT['html'] = $HTML->transformPHP($DATA, 'formcreat');

		if ($flag == 1) {
			$RESULT['onload'] .= 'clearTimeout(timerid2);fShowload (1,result.html2,0,0,\'location.href = location.href;\');';

			$RESULT['html2'] = '<div class="blockhead ok">' . static_main::m('add', $this) . '</div><div class="hrb">&nbsp;</div>
			<div class="divform"><div class="messages" style="text-align:justify;">
			</div></div>';
			$RESULT['html'] = '';
		} elseif ($flag == -1) {
			//$RESULT['onload'] = 'GetId("messages").innerHTML=result.html2;'.$RESULT['onload'];
			$RESULT['onload'] = 'jQuery(\'.caption_error\').remove();' . $RESULT['onload'] . 'clearTimeout(timerid2);fShowload(1,result.html2);';
			$RESULT['html2'] = "<div class='blockhead'>Внимание. Некоректно заполнены поля.</div><div class='hrb'>&#160;</div>" . $RESULT['html'];
			$RESULT['html'] = '';
		} else {
			$RESULT['onload'] .= 'clearTimeout(timerid2);fShowload(1,result.html2);';
			$RESULT['html2'] = $RESULT['html'];
			$RESULT['html'] = '';
		}
		if (!isset($_SESSION['user']['id']))
			$RESULT['onload'] .= 'reloadCaptcha(\'captcha\');jQuery(\'input.secret\').attr(\'value\',\'\');';
		$RESULT['onload'] .= $_tpl['onload'];

		return $RESULT;
	}

	public function transliteRuToLat($var,$len=0) {
		return static_tools::transliteRuToLat($var,$len);
	}

	/**
	* Ключ для платежной системы
	*/
	public function getPayKey() {
		assert($this->id);
		return $this->_cl.':'.$this->id;
	}
}

//// Kernel END


class modul_child extends ArrayObject {

	function __construct(&$obj) {
		$this->modul_obj = $obj;
		$this->childs_obj = array();
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
		/* if (isset($_CFG['modulprm_ext'][$index]) && isset($_CFG['modulprm'][$index]) && !$_CFG['modulprm'][$index][$this->modul_obj->mf_actctrl])
		  $clname = $_CFG['modulprm_ext'][$index][0];
		  else
		  $clname = $index; */
		$clname = _getExtMod($index);
		$value = parent ::offsetGet($clname);
		if ($this->offsetExists($clname) && $value === true) {
			if (isset($this->modul_obj->child_path[$clname])) {
				require_once $this->modul_obj->child_path[$clname];
			}
			$modul_child = NULL;
			if (!_new_class($clname, $modul_child,$this->modul_obj,$this->modul_obj->_forceLoad)) {
				exit('Cant find child class');
				return false;
			}
			//
			$this->modul_obj->childs[$index] = $modul_child; // Исправил ошибку, когнда для несинглтона, каждый вызов подмодуля приводит к созданию объекта
			//$this->childs_obj[$index] = $modul_child;
			return $modul_child;
		} else {
			//если один и тот же клас исползуется в как ребенок в других классах, то $this->singleton = false; вам в помощь, иначе сюда будут выдаваться ссылки на класс созданный в первы раз для другого модуля
		}

		return $value;
	}

}

