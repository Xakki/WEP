<?

class static_tools {

	private function ___construct() {

	}

	static function _fldformer($key, $param) {
		$m = '`' . $key . '` ' . $param['type'];

		if (isset($param['width']) && is_array($param['width'])) {
			if ($param['type'] == 'enum')
				$m.= '("' . implode('","', array_keys($param['width'])) . '")';
		}
		elseif (isset($param['width']) && $param['width'] != '')
			$m.= '(' . $param['width'] . ')';
		$m.= ( isset($param['attr']) ? ' ' . $param['attr'] : '') . (isset($param['default']) ? ' DEFAULT \'' . $param['default'] . '\'' : '');
		return $m;
	}


	/**
	 * Установка модуля
	 *
	 * @return bool Результат
	 */
	static function _installTable(&$MODUL) {
		$check_result = array();
		if (!$MODUL->tablename)
			return true;
		$result = $MODUL->SQL->execSQL('SHOW TABLES LIKE \'' . $MODUL->tablename . '\''); // checking table exist
		//if($result->err) return array($MODUL->tablename => array(array('err'=>$MODUL->getMess('_big_err'))));
		if (!$result->num_rows()) {
			$MODUL->_install();
			// contruct of query
			$fld = array();
			if (count($MODUL->fields))
				foreach ($MODUL->fields as $key => $param)
					$fld[] = self::_fldformer($key, $param);
			if (count($MODUL->attaches))
				foreach ($MODUL->attaches as $key => $param)
					$fld[] = self::_fldformer($key, $MODUL->attprm);

			/* 			foreach($MODUL->memos as $key => $param)
			  $fld[]= self::_fldformer($key, $MODUL->mmoprm);
			 */
			$fld[] = 'PRIMARY KEY(id)';

			if (isset($MODUL->unique_fields) and count($MODUL->unique_fields)) {
				foreach ($MODUL->unique_fields as $k => $r) {
					if (is_array($r))
						$r = implode(',', $r);
					$fld[] = 'UNIQUE KEY ' . $k . ' (' . $r . ')';
				}
			}
			if (isset($MODUL->index_fields) and count($MODUL->index_fields)) {
				foreach ($MODUL->index_fields as $k => $r) {
					if (!isset($MODUL->unique_fields[$k])) {
						if (is_array($r))
							$r = implode(',', $r);
						$fld[] = 'KEY ' . $k . ' (' . $r . ')';
					}
				}
			}
			$query = 'CREATE TABLE `' . $MODUL->tablename . '` (' . implode(',', $fld) . ') ENGINE=MyISAM DEFAULT CHARSET=' . $MODUL->_CFG['sql']['setnames'] . ' COMMENT = "' . $MODUL->ver . '"';
			// to execute query
			$result = $MODUL->SQL->execSQL($query);
			if ($result->err)
				return false;
			if (count($MODUL->def_records)) {
				if (!self::_insertDefault($MODUL)) {
					$MODUL->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
					static_main::_message($MODUL->getMess('_install_err', array($MODUL->_cl)), 4);
					return false;
				}
			}
			static_main::_message('Table `' . $MODUL->tablename . '` installed.', 3);
		}

		return true;
	}


	/**
	 * Запись дефолтных данных
	 * @return <type>
	 */
	static function _insertDefault(&$MODUL) {
		foreach ($MODUL->def_records as $row) {
			if (!$MODUL->_add_item($row)) {
				static_main::_message('Error add default record into `'.$MODUL->_cl.'`', 4);
				return false;
			}
		}
		static_main::_message('Insert default records into table ' . $MODUL->tablename . '.', 3);
		return true;
	}

	static function instalModulForm() {
		//TODO: Перенести сюда из modulprm
	}

	/**
	 * Проверка структуры модуля
	 *
	 *
	 * @param object $MODUL Текщий объект класса
	 * @return array
	 */
	static function _checkmodstruct($Mid,&$OWN = NULL) {
		$rDATA = array();
			//'mess'=>array(),
			//'oldquery'=>array(),
			//'newquery'=>array()

		if (!_new_class('modulprm', $MODULPRM)) {
			$rDATA['Ошибка']['@mess'][] = array('name' => 'error', 'value' => 'Ошибка инициализации модуля `modulprm`');
			return array($Mid => $rDATA);
		}

		list($MODUL,$rDATA['modulprm']['@mess']) = $MODULPRM->ForUpdateModulInfo($Mid,$OWN);
		if ($MODUL===false) {
			$rDATA['Ошибка']['@mess'][] = array('name' => 'error', 'value' => 'Ошибка инициализации модуля `'.$Mid.'`');
			return array($Mid => $rDATA);
		}
		elseif(!$MODUL->tablename) {
			$rDATA['Ахтунг']['@mess'][] = array('name' => 'alert', 'value' => 'Модуль `'.$MODUL->caption.'`['.$Mid.'] не использует базу данных.');
			return array($Mid => $rDATA);
		}
		elseif($MODULPRM->data[$Mid][$MODULPRM->mf_actctrl]) {
			// синонимы для типов полей
			$alias_types = array(
				'TINYINT(1)' => 'BOOL',
			);

			// типы полей, число - это значение, которое запишется в базу по умолчанию, если не указывать ширину явно
			// false - означает, что для данного типа поля в mysql ширина не указывается
			$types_width = array(
				'TINYBLOB' => false,
				'TINYTEXT' => false,
				'BLOB' => false,
				'TEXT' => false,
				'MEDIUMBLOB' => false,
				'MEDIUMTEXT' => false,
				'LONGBLOB' => false,
				'LONGTEXT' => false,
				'DATE' => false,
				'DATETIME' => false,
				'TIMESTAMP' => false,
				'TIME' => false,
				'FLOAT' => '8,2',
				'DOUBLE' => false,
				'PRECISION' => false,
				'REAL' => false,
				'INT' => 11,
				'INTEGER ' => 11,
				'VARCHAR' => 255,
			);

			// типы полей, в которых нет атрибута default
			$types_without_default = array(
				'TINYTEXT' => true,
				'TEXT' => true,
				'MEDIUMTEXT' => true,
				'LONGTEXT' => true,
			);

			$result = $MODUL->SQL->execSQL('SHOW TABLES LIKE \'' . $MODUL->tablename . '\''); // checking table exist
			if ($result->err) {
				$rDATA['Ошибка БД']['@mess'][] = array('name' => 'error', 'value' => $MODUL->getMess('_big_err'));
				return array($MODUL->_cl => $rDATA);
			}
			if (!$result->num_rows()) {
				if (isset($_POST['sbmt'])) {
					if (!self::_installTable($MODUL)) {
						$rDATA['Создание таблицы']['@mess'][] = array('name' => 'error', 'value' => $MODUL->getMess('_install_err') );
						return array($MODUL->_cl => $rDATA);
					}
					else {
						$rDATA['Создание таблицы']['@mess'][] = array('name' => 'ok', 'value' => $MODUL->getMess('_install_ok') );
						return array($MODUL->_cl => $rDATA);
					}
				}
				else {
					$rDATA['Создание таблицы']['@mess'][] = array('name' => 'alert', 'value' => $MODUL->getMess('_install_info',array($MODUL->_cl.'['.$MODUL->tablename.']')) );
					return array($MODUL->_cl => $rDATA);
				}
			}

			$out = array();

			if (isset($MODUL->fields))
				foreach ($MODUL->fields as $key => $param) {
					if (isset($param['attr']) and stristr($param['attr'], 'default')) {
						$rDATA[$key]['@mess'][] = array('name' => 'alert', 'value' => 'Пар-р default прописан в ключе attr. Для корректной работы необходимо прописать его в отдельном элементе с ключом `default`' );
					}
					if (
						isset($param['default']) &&
						isset($types_without_default[mb_strtoupper($param['type'])]) &&
						$types_without_default[mb_strtoupper($param['type'])] === true
					) {
						$rDATA[$key]['@mess'][] = array('name' => 'alert', 'value' => 'Параметр `default` для поля `'.$key.'` указывать не обязательно.');
						unset($MODUL->fields[$key]['default']);
					}
				}

			$result = $MODUL->SQL->execSQL('SHOW COLUMNS FROM `' . $MODUL->tablename . '`');
			while (list($fldname, $fldtype, $null, $key, $default, $extra) = $result->fetch_array(MYSQL_NUM)) {
				$fldtype = mb_strtoupper($fldtype);
				$null = mb_strtoupper($null);
				$key = mb_strtoupper($key);
				$extra = mb_strtoupper($extra);

				if (isset($MODUL->fields[$fldname])) {
					$MODUL->fields[$fldname]['inst'] = '1';
					$tmp_type = mb_strtoupper($MODUL->fields[$fldname]['type']);
					if (isset($MODUL->fields[$fldname]['width'])) {
						if (isset($types_width[$tmp_type]) && $types_width[$tmp_type] === false) {
							$rDATA[$fldname]['@mess'][] = array('name' => 'alert', 'value' => 'Параметр `width` для поля `'.$fldname.'` указывать не обязательно.');
							unset($MODUL->fields[$fldname]['width']); // чистим от ненужного парметра
						}
					} else {
						if (isset($types_width[$tmp_type]) && $types_width[$tmp_type] !== false) {
							$rDATA[$fldname]['@mess'][] = array('name' => 'alert', 'value' => 'Параметр `width` для поля `'.$fldname.'` необходим. По умолчанию будет установленно значение `'.$types_width[$tmp_type].'`');
							$MODUL->fields[$fldname]['width'] = $types_width[$tmp_type];
						}
					}

					$types = array();
					$types[] = $fldtype;
					if (isset($alias_types[$fldtype]))
						$types[] = $alias_types[$fldtype];

					$table_properties = array();
					$table_properties_up_case = array();
					$i = 0;
					foreach ($types as $type) {
						$table_properties[$i] = '`' . $fldname . '` ' . $type;

						if ($type != 'TIMESTAMP') {
							if ($null == 'YES') {
								if (isset($MODUL->fields[$fldname]['attr']) and strstr(mb_strtoupper($MODUL->fields[$fldname]['attr']), 'NULL'))
									$table_properties[$i] .= ' NULL';
							}
							else {
								$table_properties[$i] .= ' NOT NULL';
							}
							if ($default !== NULL) {
								$table_properties[$i] .= ' DEFAULT \'' . addcslashes($default,'\'') . '\'';
							}
							if ($extra != '')
								$table_properties[$i] .= ' ' . $extra;
						}
						$table_properties_up_case[trim( str_replace(array('"', "'", chr(194).chr(160),"\xC2xA0","\n"), array('', '', ' ', ' ', ' '), mb_strtoupper($table_properties[$i],'UTF-8')) )] = true;
						$i++;
					}
					$temp_fldformer = self::_fldformer($fldname, $MODUL->fields[$fldname]);
					$temp = trim(str_replace(array('"', "'", chr(194).chr(160),"\xC2xA0","\n"), array('', '', ' ', ' ', ' '), mb_strtoupper($temp_fldformer,'UTF-8')));

					if (isset($MODUL->fields[$fldname]['type']) and !isset($table_properties_up_case[$temp])) {
						$rDATA[$fldname]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` CHANGE `' . $fldname . '` ' . $temp_fldformer;
						$rDATA[$fldname]['@oldquery'] = $table_properties[0];
					}
				} elseif (isset($MODUL->attaches[$fldname]))
					$MODUL->attaches[$fldname]['inst'] = '1';
				elseif (isset($MODUL->memos[$fldname]))
					$MODUL->memos[$fldname]['inst'] = '1';
				else
					$rDATA[$fldname]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` DROP `' . $fldname . '`';
			}

			if (isset($MODUL->fields))
				foreach ($MODUL->fields as $key => $param) {
					if (!isset($param['inst'])) {
						$rDATA[$key]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' . self::_fldformer($key, $param);
					}
				}

			if (isset($MODUL->attaches))
				foreach ($MODUL->attaches as $key => $param) {
					if (!isset($param['inst']))
						$rDATA[$key]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' . self::_fldformer($key, $MODUL->attprm);
					if (!self::_checkdir($MODUL->_CFG['_PATH']['path'].$MODUL->getPathForAtt($key))) {
						$rDATA[$key]['@mess'][] = array('name' => 'error', 'value' => $MODUL->getMess('_checkdir_error', array($MODUL->getPathForAtt($key))) );
					}
					$rDATA['@reattach'] = &$MODUL;
				}

			if (isset($MODUL->memos))
				foreach ($MODUL->memos as $key => $param) {
					if (!self::_checkdir($MODUL->_CFG['_PATH']['path'].$MODUL->getPathForMemo($key))) {
						$rDATA[$key]['@mess'][] = array('name' => 'error', 'value' => $MODUL->getMess('_recheck_err') );
					}
				}


			$indexlist = $uniqlistR = $uniqlist = array();
			$primary = '';
			$result = $MODUL->SQL->execSQL('SHOW INDEX FROM `' . $MODUL->tablename . '`');
			while ($data = $result->fetch_array(MYSQL_NUM)) {
				if ($data[2] == 'PRIMARY') //только 1 примарикей
					$primary = $data[4];
				elseif (!$data[1]) //!NON_unique
					$uniqlist[$data[2]][$data[4]] = $data[4];
				else
					$indexlist[$data[2]][$data[4]] = $data[4];
			}

			// CREATE PRIMARY KEY
			if (isset($MODUL->fields['id']) and !$primary) {
				$rDATA['id']['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD PRIMARY KEY(id)';
				$primary = 'id';
			}
			// CREATE UNIQ KEY
			$uniqlistR = $uniqlist;
			if (isset($MODUL->unique_fields) and count($MODUL->unique_fields)) {
				foreach ($MODUL->unique_fields as $k => $r) {
					if (!is_array($r))
						$r = array($r);
					if (!isset($uniqlist[$k])) {// and !isset($uniqlistR[$k])
						foreach ($r as $kk => $rr)
							$uniqlistR[$k][$kk] = $rr;
						$tmp = '';
						if (isset($indexlist[$k])){
							$tmp = ' drop key `' . $k . '`, ';
							unset($indexlist[$k]);
						}
						if (is_array($r))
							$r = implode('`,`', $r);
						if (!isset($rDATA[$k]['@index']))		$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
						else		$rDATA[$k]['@index'] .= ', ';
						$rDATA[$k]['@index'] .= ' ' . $tmp . ' ADD UNIQUE KEY `' . $k . '` (`' . $r . '`)';
					} else {
						unset($uniqlist[$k]);
					}
				}
			}
			if (count($uniqlist)) {
				foreach ($uniqlist as $k => $r) {
					if (!isset($rDATA[$k]['@index']))		$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
					else		$rDATA[$k]['@index'] .= ', ';
					$rDATA[$k]['@index'] .= ' drop key ' . $k . ' ';
					unset($uniqlistR[$k]);
				}
			}
			//$uniqlistR - Действующие уник ключи в итоге
			// CREATE INDEX KEY
			if ($MODUL->owner)
				$MODUL->index_fields[$MODUL->owner_name] = $MODUL->owner_name;
			if ($MODUL->mf_istree)
				$MODUL->index_fields[$MODUL->mf_istree] = $MODUL->mf_istree;
			if ($MODUL->mf_actctrl)
				$MODUL->index_fields[$MODUL->mf_actctrl] = $MODUL->mf_actctrl;
			if ($MODUL->mf_ordctrl)
				$MODUL->index_fields[$MODUL->mf_ordctrl] = $MODUL->mf_ordctrl;
			if (count($MODUL->index_fields))
				foreach ($MODUL->index_fields as $k => $r) {
					if (!isset($indexlist[$k]) and !isset($uniqlistR[$k])) {
						if (!isset($rDATA[$k]['@index']))		$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
						else		$rDATA[$k]['@index'] .= ', ';
						$rDATA[$k]['@index'] .= ' add index `' . $k . '` (`' . $r . '`)';
					} else {
						unset($indexlist[$k]);
					}
				}
			if (count($indexlist)) {
				foreach ($indexlist as $k => $r) {
					if (!isset($rDATA[$k]['@index']))		$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
					else		$rDATA[$k]['@index'] .= ', ';
					$rDATA[$k]['@index'] .= ' drop key ' . $k . ' ';
				}
			}
			$rDATA['Оптимизация']['@newquery'] = 'OPTIMIZE TABLE `' . $MODUL->tablename . '`';
		}

		if (count($rDATA))
			$rDATA = array($MODUL->_cl => $rDATA);
		if (count($MODUL->Achilds))
			foreach ($MODUL->Achilds as $k => $r) {
				$temp = self::_checkmodstruct($k,$MODUL);
				if ($temp and count($temp))
					$rDATA = array_merge($rDATA, $temp);
			}

		return $rDATA;
	}

	/**
	 * Проверка существования директории и прав записи в него, и создание
	 *
	 * @param object $MODUL Текщий объект класса
	 * @param string $dir Проверяемая дирректория
	 * @return bool Результат
	 */
	static function _checkdir($dir) {
		if (!file_exists($dir)) {
			if (!file_exists(dirname($dir))) {
				self::_checkdir(dirname($dir));
			}
			if (!mkdir($dir, 0755))
				return static_main::_message('Cannot create directory <b>' . $dir . '</b>', 1);
		}
		else {
			$f = fopen($dir . '/t_e_s_t', 'w');
			if (!$f)
				return static_main::_message('Cannot create file in directory <b>' . $dir . '</b>', 1);

			$err = fwrite($f, 'zzz') == -1;
			fclose($f);
			unlink($dir . '/t_e_s_t');

			if ($err)
				return static_main::_message('Cannot write/read file in directory <b>' . $dir . '</b>', 1);
		}
		return true;
	}

	static function _xmlFormConf(&$MODUL) {
		$MODUL->form = array();
		$MODUL->form['_*features*_'] = array('name' => 'Configmodul', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
		$MODUL->form['_info'] = array('type' => 'info', 'css' => 'caption', 'caption' => $MODUL->_CFG['_MESS']['_config']);
		foreach ($MODUL->config_form as $k => $r) {
			if (!is_array($MODUL->config[$k]))
				$MODUL->config_form[$k]['value'] = stripslashes($MODUL->config[$k]);
			else
				$MODUL->config_form[$k]['value'] = $MODUL->config[$k];
		}
		$MODUL->form = array_merge($MODUL->form, $MODUL->config_form);
		$MODUL->form['sbmt'] = array(
			'type' => 'submit',
			'value' => $MODUL->_CFG['_MESS']['_submit']);
	}

	static function _save_config($conf,$file) {
		foreach($conf as $k=>&$r) {
			if(is_string($r) and strpos($r,':|')!==false) {
				$temp = explode(':|',$r);
				$r = array();
				foreach($temp as $t=>$d) {
					$temp2 = explode(':=',$d);
					if(count($temp2)>1)
						$r[trim($temp2[0])]=trim($temp2[1]);
					else
						$r[]=trim($d);
				}
			}
		}
		file_put_contents($file, var_export($conf, true) );
		return true;
	}

	static function _staticStatsmodul(&$MODUL, $oid='') {
		$clause = array();
		if (!$oid and isset($_GET['_oid']))
			$oid = (int) $_GET['_oid'];
		if ($oid)
			$clause[] = 't1.' . $MODUL->owner_name . '=' . $oid;
		$filtr = $MODUL->_filter_clause();
		if (count($filtr[0]))
			$clause += $filtr[0];
		if (count($clause))
			$clause = 'WHERE ' . implode(' and ', $clause);
		else
			$clause = '';
		$clause = 'SELECT ' . $MODUL->mf_statistic['X'] . ' as `X`, ' . $MODUL->mf_statistic['Y'] . ' as `Y` FROM `' . $MODUL->tablename . '` t1 ' . $clause . ' GROUP BY X ORDER BY X';
		$result = $MODUL->SQL->execSQL($clause);
		$data = array();
		$maxY = 0;
		$minX = 0;
		$maxX = 0;
		if (!$result->err) {
			while ($row = $result->fetch_array()) {
				$data[] = '[\'' . $row['X'] . '\',' . $row['Y'] . ']';
				if ($row['Y'] > $maxY)
					$maxY = $row['Y'];
				if ($row['X'] > $maxX)
					$maxX = $row['X'];
				if ($minX == 0 or $row['X'] < $minX)
					$minX = $row['X'];
			}
		}
		else
			return array($result->err, '');

		$stepY = static_main::okr($maxY, 1) / 10;
		$f = 'readyPlot(\'' . $MODUL->caption . '\',\'' . $MODUL->mf_statistic['Xname'] . '\',\'' . $MODUL->mf_statistic['Yname'] . '\',' . $stepY . ');';
		$eval = '
	line1 = [' . implode(',', $data) . '];
	if(typeof $.jqplot == "undefined")
		$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'jquery.jqplot.0.9.7/plugins/jqplot.ohlcRenderer.min.js\',
			function(){' . $f . '},[
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'jquery.jqplot.0.9.7/jquery.jqplot.min.js\'),
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'jquery.jqplot.0.9.7/plugins/jqplot.cursor.min.js\'),
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'jquery.jqplot.0.9.7/plugins/jqplot.dateAxisRenderer.min.js\'),
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'jquery.jqplot.0.9.7/plugins/jqplot.highlighter.min.js\')
		]);
	else {' . $f . '}

	';
		/*
		  //$.include(\'/script/jquery.ui.all.js\',static function(){readyUI();});
		  static function readyUI(){
		  $(\'#statstabs\').tabs();
		  $(\'#statstabs\').bind(\'tabsshow\', static function(event, ui) {
		  if (ui.index == 1 && plot1._drawCount == 0) {
		  plot1.replot();
		  }
		  else if (ui.index == 2 && plot2._drawCount == 0) {
		  plot2.replot();
		  }
		  });
		  }; */
		$html = '';
		if (count($filtr[0]))
			$html .= 'Результат статистики выводится по фильтру<br/>';
		$html .= '
	<div id="statschart2" data-height="300px" data-width="480px" style="margin-top:10px; margin-left:10px;"></div>
	<style>
	@import "' . $MODUL->_CFG['_HREF']['_style'] . 'jquery-ui-redmond.css";
	@import "' . $MODUL->_CFG['_HREF']['_script'] . 'jquery.jqplot.0.9.7/jquery.jqplot.min.css";
	</style>
	';
		$html = '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>' . $html;
		global $_tpl;
		$_tpl['onload'] .= $eval;
		return $html;
	}

	static function _reattaches(&$MODUL) {
		if (count($MODUL->attaches)) {
			$data = array();
			// select record ids to delete
			$result = $MODUL->SQL->execSQL('select id FROM ' . $MODUL->tablename);
			if ($result->err)
				return false;
			// create list

			while ($row = $result->fetch_array()) {
				//$data[]= $row;
				foreach ($MODUL->attaches as $key => $value) {
					$pathimg = $MODUL->_CFG['_PATH']['path'] . $MODUL->getPathForAtt($key);
					foreach (array_unique($value['mime']) as $k => $ext) {
						$newname = $pathimg . '/' . $row['id'] . '.' . $ext;
						if (file_exists($newname)) {
							if (isset($value['thumb']) and count($value['thumb'])) { // проверка на наличие модифицированных изображений
								if (!exif_imagetype($newname)) // опред тип файла
									break;
								foreach ($value['thumb'] as $imod) {
									if (!$imod['pref'])
										$imod['pref'] = ''; // по умолчинию без префикса
 if ($imod['path'])
										$newname2 = $MODUL->_CFG['_PATH']['path'] . $imod['path'] . '/' . $imod['pref'] . $row['id'] . '.' . $ext;
									else
										$newname2 = $pathimg . '/' . $imod['pref'] . $row['id'] . '.' . $ext;
									if ($newname != $newname2 and !file_exists($newname2)) {
										if ($imod['type'] == 'crop')
											static_form::_cropImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'resize')
											static_form::_resizeImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'resizecrop')
											static_form::_resizecropImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'water')
											static_form::_waterMark($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
									}
								}
							}
							$data[$key][$ext][] = $row['id'];
							break;
						}
					}
				}
			}
			foreach ($MODUL->attaches as $key => $value) {
				$result = $MODUL->SQL->execSQL('UPDATE ' . $MODUL->tablename . ' SET ' . $key . '=\'\' ');
				if ($result->err)
					return false;
			}
			foreach ($data as $key1 => $row1) {

				foreach ($row1 as $key2 => $row2) {
					$result = $MODUL->SQL->execSQL('UPDATE ' . $MODUL->tablename . ' SET ' . $key1 . '=\'' . $key2 . '\' WHERE id IN (' . implode(',', $row2) . ')');
					if ($result->err)
						return false;
				}
			}
		}
		return true;
	}

	static function _toolsCheckmodul(&$MODUL) {
		global $HTML;
		$flag = 0;
		$MODUL->form = $mess = array();
		if (!static_main::_prmModul($MODUL->_cl, array(14)))
			$mess[] = array('name' => 'error', 'value' => 'Access denied');
		else {
			$check_result = $MODUL->_checkmodstruct();

			if (isset($_POST['sbmt'])) {
				$flag = 1;
				foreach ($check_result as $table => $row) {
					if (isset($row['@reattach']) and isset($_POST['query_'.$table]['reattach'])) {
						if (is_object($row['@reattach']) and self::_reattaches($row['@reattach']))
							$mess[] = array('name' => 'ok', 'value' => '<b>' . $table . '</b> - ' . $MODUL->getMess('_file_ok'));
						else {
							$mess[] = array('name' => 'error', 'value' => '<b>' . $table . '</b> - ' . $MODUL->getMess('_file_err'));
							$flag = -1;
						}
						unset($row['@reattach']);
					}
					foreach ($row as $kk => $rr) {
						if(is_array($rr)) {
							if(isset($rr['@newquery']) and isset($_POST['query_'.$table][$kk.'@newquery'])) {
								$result = $MODUL->SQL->execSQL($rr['@newquery']);
								if ($result->err) {
									$mess[] = array('name' => 'error', 'value' => 'Error new query(' . $rr['@newquery'] . ')');
									$flag = -1;
								}
							}
							if(isset($rr['@index']) and isset($_POST['query_'.$table][$kk.'@index'])) {
									$result = $MODUL->SQL->execSQL($rr['@index']);
									if ($result->err) {
										$mess[] = array('name' => 'error', 'value' => 'Error index query(' . $rr['@index']. ')');
										$flag = -1;
									}
							}
						}
					}//end foreach
				}
				if (count($_POST)<=1)
					$mess[] = array('name' => 'alert', 'value' => $MODUL->getMess('_recheck_have_nothing'));
				if ($flag)
					$mess[] = array('name' => 'ok', 'value' => $MODUL->getMess('_recheck_ok'));
				//'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>'
			}
			else {
				$MODUL->form['_*features*_'] = array('name' => 'Checkmodul', 'method' => 'POST', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
				if (count($check_result)) {
					$MODUL->form['_info'] = array(
						'type' => 'info',
						'caption' => $MODUL->getMess('_recheck'),
					);
					$MODUL->form['invert'] = array(
						'type' => 'info',
						'caption' => '<a href="#" onclick="return invert_select(\'form\');">Инвертировать выделение</a>',
					);

					foreach ($check_result as $table => $row) {
						$valuelist = $message = array();
						if (is_array($row) and count($row)) {
							if (isset($row['@reattach'])) {
								$valuelist['reattach'] = '<span style="color:blue;">Обновить файлы</span>';
								unset($row['@reattach']);
							}
							foreach ($row as $kk => $rr) {
								if (isset($rr['@mess'])) {
									$message = array_merge($message,$rr['@mess']);
								}
								if (!is_array($rr))
									$desc = $rr;
								elseif (isset($rr['@newquery']) and isset($rr['@oldquery']))
									$desc = 'Было: ' . htmlspecialchars($rr['@oldquery'], ENT_QUOTES, $MODUL->_CFG['wep']['charset']) . '<br/>Будет: ' . htmlspecialchars($rr['@newquery'], ENT_QUOTES, $MODUL->_CFG['wep']['charset']);
								elseif (isset($rr['@newquery']))
									$desc = htmlspecialchars($rr['@newquery'], ENT_QUOTES, $MODUL->_CFG['wep']['charset']);
								else
									$desc = '';
								if ($desc)
									$valuelist[$kk.'@newquery'] = '<i>' . $kk . '</i> - ' . $desc;

								if (is_array($rr) and isset($rr['@index']))
									$valuelist[$kk.'@index'] = '<i>' . $kk . '</i> - ' . $rr['@index'];
							}
							if (count($valuelist)) {
								$message = array('messages'=>$message);
								$MODUL->form['query_'.$table]  = array(
									'caption'=>'Модуль '.$table,
									'type'=>'checkbox',
									'valuelist'=>$valuelist,
									'comment' => $HTML->transformPHP($message, 'messages'),
									'style'=>'border:solid 1px gray;margin:3px 0;'
								);
							}elseif(count($message))
								$mess = array_merge($mess,$message);
						}
						else
							$mess[] = array('name' => 'error', 'value' => 'Error data (' . $table . ' - ' . print_r($row, true) . ')');
					}

					$MODUL->form['sbmt'] = array(
						'type' => 'submit',
						'value' => $MODUL->getMess('_submit')
					);
				} else
					$mess[] = array('name' => 'ok', 'value' => $MODUL->getMess('_recheck_have_nothing'));
			}
		}
		$DATA = array('form' => $MODUL->form, 'messages' => $mess);
		return Array($flag,$DATA);
	}

	/**
	 * Сбор переменных хранящихся в фаиле
	 * @param <type> $file Фаил из которого будут браться данные о перменных
	 * @param <type> $start Не обязательная, указывает строку после которой начинается сбор полезных строк
	 * @param <type> $end не обязательная, указывает строку до которой будет сбор строк
	 * @param <type> $mData не обязательно, дефолтное значение отслеживаемой переменной
	 * @return <type> Возвращает массив полученных данных $_CFG
	 */
	static function getFdata($file, $start='', $end='', $mData = false) {
		$fc = '';
		if ($start == '' and $end == '') {
			$fc = file_get_contents($file);
		} else {
			$fc = false;
			$file = file($file);
			foreach ($file as $k => $r) {
				if ($fc === false and strpos($r, $start) !== false)
					$fc = '';
				elseif (strpos($r, $end) !== false)
					break;
				if ($fc !== false)
					$fc .= $r . "\n";
			}
		}
		if ($mData !== false) {
			$_CFG = $mData;
		}
		$fc = trim($fc, "<?>\n");

		if ($fc)
			eval($fc);
		else
			print_r('NO CFG');

		return $_CFG;
	}

	static function saveUserCFG($SetDataCFG, $tempCFG=array()) {
		global $_CFG;
		$mess = array();
		$DEF_CFG = self::getFdata($_CFG['_PATH']['wep'] . '/config/config.php', '/* MAIN_CFG */', '/* END_MAIN_CFG */');// чистый конфиг ядра
		$USER_CFG = self::getFdata($_CFG['_PATH']['wepconf'] . '/config/config.php', '', '', $DEF_CFG); // конечный конфиг
		// Редактируемые конфиги
		$edit_cfg = array(
			'sql' => true,
			'memcache' => true,
			'wep' => true,
			'site' => true,
		);
		$fl = false;
		$putFile = array();
		$SetDataCFG = static_main::MergeArrays($USER_CFG, $SetDataCFG);// объединяем конфиг записанный на пользователя и новые конфиги
		foreach ($edit_cfg as $k => $r) {
			foreach ($SetDataCFG[$k] as $kk => $rr) {
				$flag = false;
				if(is_string($rr)) {
					if($rr != $DEF_CFG[$k][$kk])
						$flag = true;
					$rr = '\''.addcslashes($rr, '\'').'\'';
				}
				elseif(is_array($rr)) {
					if(!is_array($DEF_CFG[$k][$kk]) or count(array_diff($rr,$DEF_CFG[$k][$kk])))
						$flag = true;
					$rr = array_combine($rr,$rr);
					$rr = var_export($rr,true);
				}
				if (!isset($DEF_CFG[$k][$kk]) or $flag) {
					$putFile[$k . '_' . $kk] = '$_CFG[\'' . $k . '\'][\'' . $kk . '\'] = ' . $rr . ';';
				}
			}
		}
		if(count($tempCFG))
			$SetDataCFG = static_main::MergeArrays($SetDataCFG, $tempCFG);
		$SQL = new sql($SetDataCFG['sql']); //пробуем подключиться к БД

		$putFile = "<?\n\t//create time " . date('Y-m-d H:i') . "\n\t".implode("\n\t", $putFile)."\n";
		//Записать в конфиг все данные которые отличаются от данных по умолчанию
		if (!file_put_contents($_CFG['_PATH']['wepconf'] . '/config/config.php', $putFile)) {
			$mess[] = array('name' => 'error', 'value' => 'Ошибка записи настроек. Нет доступа к фаилу');
		} else {
			$fl = true;
			$mess[] = array('name' => 'ok', 'value' => 'Подключение к БД успешно.');
			$mess[] = array('name' => 'ok', 'value' => 'Конфигурация успешно сохранена.');
		}
		return array($fl,$mess);
	}

}
