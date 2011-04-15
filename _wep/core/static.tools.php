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
	static function _installTable(&$MODUL=NULL) {
		if(!$MODUL) $MODUL = &$this;

		$check_result = array();

		$result = $MODUL->SQL->execSQL('SHOW TABLES LIKE \'' . $MODUL->tablename . '\''); // checking table exist
		//if($result->err) return array($MODUL->tablename => array(array('err'=>$MODUL->getMess('_big_err'))));
		if (!$result->num_rows()) {

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
			self::_message('Table `' . $MODUL->tablename . '` installed.', 3);
			if (count($MODUL->def_records)) {
				if (!$MODUL->_insertDefault()) {
					$MODUL->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
					self::_message($MODUL->getMess('_install_err', array($MODUL->_cl)), 4);
					return false;
				}
			}
		}

		return true;
	}

	/////////////// _reinstall
	/**
	 * Переустановка модуля
	 *
	 * @param object $MODUL Текщий объект класса
	 * @return bool Результат
	 */
	static function _reinstall(&$MODUL) {
		self::_droped($MODUL);
		self::_installTable($MODUL);
		return true;
	}

	/**
	 * Удаление модуля
	 *
	 * @param object $MODUL Текщий объект класса
	 * @return bool Результат
	 */
	static function _droped(&$MODUL) {
		$result = $MODUL->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
		//if ($result->err) return false;
		self::_message('Table `' . $MODUL->tablename . '` droped.', 3);
		if (count($MODUL->childs))
			foreach ($MODUL->childs as $child)
				self::_droped($child);
		return true;
	}

	/**
	 * Проверка структуры модуля
	 *
	 * @param object $MODUL Текщий объект класса
	 * @return array
	 */
	static function _checkmodstruct(&$MODUL) {

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
		if ($result->err)
			return array($MODUL->tablename => array(array('err' => $MODUL->getMess('_big_err'))));
		if (!$result->num_rows()) {
			$check_result[$MODUL->tablename]['Установка модуля']['newquery'] = $q;
			if (isset($_POST['sbmt'])) {
				if (!self::_installTable($MODUL))
					return array($MODUL->tablename => array(array('err' => $MODUL->getMess('_install_err'))));
				else
					return array($MODUL->tablename => array(array('ok' => $MODUL->getMess('_install_ok'))));
			}
			else
				return array($MODUL->tablename => array(array('ok' => $MODUL->getMess('_install_info'))));
		}

		$out = array();

		if (isset($MODUL->fields))
			foreach ($MODUL->fields as $key => $param) {
				if (stristr($param['attr'], 'default')) {
					$out[$key]['err'][] = 'Ненужный пар-р default в ключе attr';
				}

				if (
						isset($param['default']) &&
						isset($types_without_default[strtoupper($param['type'])]) &&
						$types_without_default[strtoupper($param['type'])] === true
				) {
					$out[$key]['err'][] = 'Ненужный пар-р `default` (Для типов полей ' . $param['type'] . ' указывать `default` необязательно.';
					unset($MODUL->fields[$key]['default']);
				}
			}

		$result = $MODUL->SQL->execSQL('SHOW COLUMNS FROM `' . $MODUL->tablename . '`');
		while (list($fldname, $fldtype, $null, $key, $default, $extra) = $result->fetch_array(MYSQL_NUM)) {
			$fldtype = strtoupper($fldtype);
			$null = strtoupper($null);
			$key = strtoupper($key);
			$extra = strtoupper($extra);

			if (isset($MODUL->fields[$fldname])) {
				$MODUL->fields[$fldname]['inst'] = '1';

				$tmp_type = strtoupper($MODUL->fields[$fldname]['type']);
				if (isset($MODUL->fields[$fldname]['width'])) {
					if (isset($types_width[$tmp_type]) && $types_width[$tmp_type] === false) {
						unset($MODUL->fields[$fldname]['width']); // чистим от ненужного парметра
					}
				} else {
					if (isset($types_width[$tmp_type]) && $types_width[$tmp_type] !== false) {
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
							if (strstr(strtoupper($MODUL->fields[$fldname]['attr']), 'NULL'))
								$table_properties[$i] .= ' NULL';
						}
						else {
							$table_properties[$i] .= ' NOT NULL';
							//if(!isset($MODUL->fields[$fldname]['default']) and $tmp_type=='VARCHAR')
							//	$MODUL->fields[$fldname]['default'] = '';
						}
						if ($default !== NULL) {
							$table_properties[$i] .= ' DEFAULT \'' . $default . '\'';
						}
						if ($extra != '')
							$table_properties[$i] .= ' ' . $extra;
					}
					$table_properties_up_case[$i] = str_replace(array('"', "'"), array('', ''), trim(strtoupper($table_properties[$i])));
					$i++;
				}
				$temp_fldformer = trim(self::_fldformer($fldname, $MODUL->fields[$fldname]));
				if (isset($MODUL->fields[$fldname]['type']) and !in_array(str_replace(array('"', "'"), array('', ''), strtoupper($temp_fldformer)), $table_properties_up_case)) {
					$out[$fldname]['newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` CHANGE `' . $fldname . '` ' . $temp_fldformer;
					$out[$fldname]['oldquery'] = $table_properties[0];
	//					$out[] = 'ALTER TABLE `'.$MODUL->tablename.'` CHANGE `'.$fldname.'` '.self::_fldformer($fldname, $MODUL->fields[$fldname]).' ('.$table_properties[0].')';
				}

	//				if (isset($MODUL->fields[$fldname]['width'])) {
	//					if ($MODUL->fields[$fldname]['type'].'('.$MODUL->fields[$fldname]['width'].')' != $type) {
	//						$out[] = 'ALTER TABLE `'.$MODUL->tablename.'` CHANGE `'.$fldname.'` `'.$fldname.'` '.$MODUL->fields[$fldname]['type'].'('.$MODUL->fields[$fldname]['width'].') NOT NULL';
	//					}
	//				}
			} elseif (isset($MODUL->attaches[$fldname]))
				$MODUL->attaches[$fldname]['inst'] = '1';
			elseif (isset($MODUL->memos[$fldname]))
				$MODUL->memos[$fldname]['inst'] = '1';
			else
				$out[$fldname]['newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` DROP `' . $fldname . '`';
		}

		if (isset($MODUL->fields))
			foreach ($MODUL->fields as $key => $param) {
				if (!isset($param['inst'])) {
					$out[$key]['newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' . self::_fldformer($key, $param);
				}
			}

		if (isset($MODUL->attaches))
			foreach ($MODUL->attaches as $key => $param) {
				if (!isset($param['inst']))
					$out[$key]['newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' . self::_fldformer($key, $MODUL->attprm);
				if (!$MODUL->_checkdir($MODUL->getPathForAtt($key))) {
					$out[$key]['err'][] = $MODUL->getMess('_checkdir_error', array($MODUL->getPathForAtt($key)));
				}
				$out['reattach'] = &$MODUL;
			}

		if (isset($MODUL->memos))
			foreach ($MODUL->memos as $key => $param) {
				//	if (!$param['inst']) $out[] = 'ADD '.self::_fldformer($key, $MODUL->mmoprm);
				if (!$MODUL->_checkdir($MODUL->getPathForMemo($key))) {
					print_r('******8');
					$out[$key]['err'][] = $MODUL->getMess('_recheck_err');
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
			$out['id']['index'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD PRIMARY KEY(id)';
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
					if (isset($indexlist[$k]))
						$tmp = 'drop key `' . $k . '`, ';
					if (is_array($r))
						$r = implode('`,`', $r);
					$out[$k]['index'] = 'ALTER TABLE `' . $MODUL->tablename . '` ' . $tmp . ' ADD UNIQUE KEY `' . $k . '` (`' . $r . '`)';
				} else {
					unset($uniqlist[$k]);
				}
			}
		}
		if (count($uniqlist)) {
			foreach ($uniqlist as $k => $r) {
				$out[$k]['index'] = 'ALTER TABLE `' . $MODUL->tablename . '` drop key ' . $k . ' ';
				unset($uniqlistR[$k]);
			}
		}
		//$uniqlistR - Действующие уник ключи в итоге
		// CREATE INDEX KEY
		if ($MODUL->owner)
			$MODUL->index_fields[$MODUL->owner_name] = $MODUL->owner_name;
		if ($MODUL->mf_istree)
			$MODUL->index_fields['parent_id'] = 'parent_id';
		if ($MODUL->mf_actctrl)
			$MODUL->index_fields['active'] = 'active';
		if ($MODUL->mf_ordctrl)
			$MODUL->index_fields['ordind'] = 'ordind';
		if (count($MODUL->index_fields))
			foreach ($MODUL->index_fields as $k => $r) {
				if (!isset($indexlist[$k]) and !isset($uniqlistR[$k])) {
					if (isset($out[$k]['index']))
						$out[$k]['index'] .= ', add index `' . $k . '` (`' . $r . '`)';
					else
						$out[$k]['index'] = 'ALTER TABLE `' . $MODUL->tablename . '` add index `' . $k . '` (`' . $r . '`)';
				} else {
					unset($indexlist[$k]);
				}
			}
		if (count($indexlist)) {
			foreach ($indexlist as $k => $r) {
				if (isset($out[$k]['index']))
					$out[$k]['index'] = ', drop key ' . $k . ' ';
				else
					$out[$k]['index'] = 'ALTER TABLE `' . $MODUL->tablename . '` drop key ' . $k . ' ';
			}
		}

		if (count($out))
			$out = array($MODUL->tablename => $out);
		if (count($MODUL->childs))
			foreach ($MODUL->childs as $k => &$r) {
				$temp = self::_checkmodstruct($r);
				if ($temp and count($temp))
					$out = array_merge($out, $temp);
			}
		$out[$MODUL->tablename]['oprimize']['newquery'] = 'OPTIMIZE TABLE `' . $MODUL->tablename . '`';
		if (isset($MODUL->_cl) and $MODUL->_cl != 'modulprm' and $MODUL->_cl != 'modulgrp') {
			_new_class('modulprm', $MODULPRM, $MODUL->null, true);
			$out[$MODUL->tablename]['ver']['newquery'] = 'UPDATE `' . $MODULPRM->tablename . '` SET `ver`="' . $MODUL->ver . '" WHERE `id`="' . $MODUL->_cl . '"';
		}
		return $out;
	}

	/**
	 * Проверка существования директории и прав записи в него, и создание
	 *
	 * @param object $MODUL Текщий объект класса
	 * @param string $dir Проверяемая дирректория
	 * @return bool Результат
	 */
	static function _checkdir(&$MODUL, $dir) {
		$pdir = $MODUL->_CFG['_PATH']['path'] . $dir;
		if (!file_exists($pdir)) {
			if (!file_exists(dirname($pdir))) {
				_checkdir($MODUL, dirname($dir));
			}
			if (!mkdir($pdir, 0755))
				return self::_message('Cannot create directory <b>' . $dir . '</b>', 1);
		}
		else {
			$f = fopen($MODUL->_CFG['_PATH']['path'] . $dir . '/t_e_s_t', 'w');
			if (!$f)
				return self::_message('Cannot create file in directory <b>' . $dir . '</b>', 1);

			$err = fwrite($f, 'zzz') == -1;
			fclose($f);
			unlink($MODUL->_CFG['_PATH']['path'] . $dir . '/t_e_s_t');

			if ($err)
				return self::_message('Cannot write/read file in directory <b>' . $dir . '</b>', 1);
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

	static function _save_config(&$MODUL) {
		//if (file_exists($MODUL->_file_cfg)) unlink($MODUL->_file_cfg);
		$h = fopen($MODUL->_file_cfg, 'w');
		foreach ($MODUL->config as $key => $value) {
			if (!is_array($value)) {
				$value = str_replace("\x0A", ' ', $value);
				$value = str_replace("\x0D", '', $value);
				$value = stripslashes($value);
			} else {
				$value = implode('|', $value);
			}
			fwrite($h, $key . '=' . $value . "\n");
		}
		fclose($h);
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

		$stepY = $MODUL->okr($maxY, 1) / 10;
		$f = 'readyPlot(\'' . $MODUL->caption . '\',\'' . $MODUL->mf_statistic['Xname'] . '\',\'' . $MODUL->mf_statistic['Yname'] . '\',' . $stepY . ');';
		$eval = '
	line1 = [' . implode(',', $data) . '];
	if(typeof $.jqplot == "undefined")
		$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'jquery.jqplot.0.9.7/plugins/jqplot.ohlcRenderer.min.js\',
			static function(){' . $f . '},[
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
										include_once($_CFG['_PATH']['core'] . 'kernel.addup.php');
										if ($imod['type'] == 'crop')
											_cropImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'resize')
											_resizeImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'resizecrop')
											_resizecropImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'water')
											_waterMark($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
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

	static function _toolsCheckmodul($MODUL) {
		$check_err = false;
		$MODUL->form = $mess = array();
		if (!static_main::_prmModul($MODUL->_cl, array(14)))
			$mess[] = array('name' => 'error', 'value' => $MODUL->getMess('denied'));
		else {
			$check_result = self::_checkmodstruct($MODUL);

			if (isset($_POST['sbmt'])) {
				if (count($_POST['list_query'])) {
					foreach ($_POST['list_query'] as $k => $r) {
						$temp = explode('::', $r);
						if (isset($check_result[$temp[0]][$temp[1]])) {
							$trow = &$check_result[$temp[0]][$temp[1]];
							if ($temp[1] == 'reattach') {
								if (is_object($trow) and _reattaches($trow))
									$mess[] = array('name' => 'ok', 'value' => '<b>' . $temp[0] . '</b>::<i>' . $temp[1] . '</i> - ' . $MODUL->getMess('_file_ok'));
								else
									$mess[] = array('name' => 'error', 'value' => '<b>' . $temp[0] . '</b>::<i>' . $temp[1] . '</i> - ' . $MODUL->getMess('_file_err'));
							}elseif (isset($temp[2]) and $temp[2] == 'index') {
								$result = $MODUL->SQL->execSQL($trow['index']);
								if ($result->err) {
									$mess[] = array('name' => 'error', 'value' => 'Error index query(' . $trow['index'] . ')');
								}
							} elseif ($trow['newquery']) {
								$result = $MODUL->SQL->execSQL($trow['newquery']);
								if ($result->err) {
									$mess[] = array('name' => 'error', 'value' => 'Error new query(' . $trow['newquery'] . ')');
								}
							}
						}
						//else
						//$mess[] = array('name' => 'error', 'value' => 'Error request('.$r.')');
					}
				}else
					$mess[] = array('name' => 'ok', 'value' => $MODUL->getMess('_recheck_have_nothing'));
				if (!count($mess))
					$mess[] = array('name' => 'ok', 'value' => $MODUL->getMess('_recheck_ok') . '  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>');
			}
			else {
				$MODUL->form['_*features*_'] = array('name' => 'Checkmodul', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
				if (count($check_result)) {
					$MODUL->form['_info'] = array(
						'type' => 'info',
						'caption' => $MODUL->getMess('_recheck'),
					);
					$MODUL->form['invert'] = array(
						'type' => 'info',
						'caption' => '<a href="#" onclick="return invert_select(\'form_tools_Checkmodul\');">Инвертировать выделение</a>',
					);

					foreach ($check_result as $table => $row) {
						if (is_array($row) and count($row)) {
							if (isset($row['reattach'])) {
								$MODUL->form['list_query']['valuelist'][] = array(
									'#id#' => $table . '::reattach',
									'#name#' => '<b>' . $table . '</b> - <span style="color:blue;">Обновить файлы</span>',
								);
								unset($row['reattach']);
							}
							foreach ($row as $kk => $rr) {
								if (is_array($rr) and isset($rr['err'])) {
									if (is_array($rr['err']))
										$rr['err'] = implode('. ', $rr['err']);
									$mess[] = array('name' => 'error', 'value' => '<b>' . $table . '</b>' . (is_int($kk) ? '' : '::<i>' . $kk . '</i>') . ' - ' . $rr['err']);
								}
								if (is_array($rr) and isset($rr['ok'])) {
									if (is_array($rr['ok']))
										$rr['ok'] = implode('. ', $rr['ok']);
									$mess[] = array('name' => 'ok', 'value' => '<b>' . $table . '</b>' . (is_int($kk) ? '' : '::<i>' . $kk . '</i>') . ' - ' . $rr['ok']);
								}
								if (!is_array($rr))
									$desc = $rr;
								elseif (isset($rr['newquery']) and isset($rr['oldquery']))
									$desc = 'Было: ' . htmlspecialchars($rr['oldquery'], ENT_QUOTES, $MODUL->_CFG['wep']['charset']) . '<br/>Будет: ' . htmlspecialchars($rr['newquery'], ENT_QUOTES, $MODUL->_CFG['wep']['charset']);
								elseif (isset($rr['newquery']))
									$desc = $rr['newquery'];
								else
									$desc = '';
								if ($desc)
									$MODUL->form['list_query']['valuelist'][] = array(
										'#id#' => $table . '::' . $kk,
										'#name#' => '<b>' . $table . '</b>::<i>' . $kk . '</i> - ' . $desc,
									);
								if (is_array($rr) and isset($rr['index']))
									$MODUL->form['list_query']['valuelist'][] = array(
										'#id#' => $table . '::' . $kk . '::index',
										'#name#' => '<b>' . $table . '</b>::<i>' . $kk . '</i> - ' . $rr['index'],
									);
							}
						}
						else
							$mess[] = array('name' => 'error', 'value' => 'Error data (' . $table . ' - ' . print_r($row, true) . ')');
					}

					if (isset($MODUL->form['list_query'])) {
						$MODUL->form['list_query']['type'] = 'checkbox';
					} else
						unset($MODUL->form['invert']);
					$MODUL->form['sbmt'] = array(
						'type' => 'submit',
						'value' => $MODUL->getMess('_submit')
					);
				} else
					$mess[] = array('name' => 'ok', 'value' => $MODUL->getMess('_recheck_have_nothing'));
			}
		}
		return Array('form' => $MODUL->form, 'messages' => $mess);
	}

	static function okr($x, $y) {
		$z = pow(10, $y);
		return $z * round($x / $z);
	}
	
	static function insertInArray($data, $afterkey, $insert_data) {
		$output = array();
		if (count($data)) {
			foreach ($data as $k => $r) {
				$output[$k] = $r;
				if ($k == $afterkey) {
					//$output = array_merge($output,$insert_data);
					$output = $output + $insert_data;
				}
			}
			return $output;
		}
		return $insert_data;
	}

}

?>
