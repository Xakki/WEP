<?php

class static_tools {

	private function ___construct() {

	}

	static function _reinstall(&$MODUL) {
		$MODUL->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
		self::_installTable($MODUL);
	}

	/**
	 * Установка модуля
	 *
	 * @return bool Результат
	 */
	static function _installTable(&$MODUL) {
		if (!$MODUL->tablename) {
			static_main::log('notice','Для модуля '.$MODUL->caption.' таблица не требуется.',$MODUL->_cl);
			return true;
		}
		$result = $MODUL->SQL->execSQL('SHOW TABLES LIKE \'' . $MODUL->tablename . '\''); // checking table exist

		if (!$result->num_rows()) {
			// contruct of query
			if(!self::_creatTable($MODUL)) {
				static_main::log('error','Для модуля `'.$MODUL->caption.'` не удалось создать таблицу.',$MODUL->_cl);
				return false;
			}
			if (count($MODUL->def_records)) {
				if (!self::_insertDefault($MODUL)) {
					$MODUL->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
					static_main::log('error','Для модуля `'.$MODUL->caption.'` не удалось записать дефолтные данные, и поэтому таблица не будет создана.',$MODUL->_cl);
					return false;
				}
			}
			static_main::log('notice','Для модуля `'.$MODUL->caption.'` успешно создана таблица.',$MODUL->_cl);
		}
		$flag = true;
		if (count($MODUL->Achilds))
			foreach ($MODUL->childs as &$child) {
				$temp = self::_installTable($child);
				if(!$temp) return false;
			}
		return $flag;
	}

	static function _checkTableRev(&$MODUL) {
		$rDATA = array();
		$rDATA = self::_checkTable($MODUL);
		if (count($rDATA))
			$rDATA = array($MODUL->_cl => $rDATA);
		if (count($MODUL->Achilds))
			foreach ($MODUL->childs as $childs) {
				$temp = self::_checkTableRev($childs);
				if ($temp and count($temp))
					$rDATA = array_merge($rDATA, $temp);
			}
		return $rDATA;
	}

	static function _checkTable(&$MODUL) {
		$rDATA = array();
		if (!$MODUL->tablename) {
			$rDATA['Создание таблицы']['@mess'][] = array( 'notice', 'Модуль `'.$MODUL->caption.'`['.$MODUL->_cl.'] не использует базу данных.' );
			return $rDATA;
		}
		$result = $MODUL->SQL->execSQL('SHOW TABLES LIKE \'' . $MODUL->tablename . '\''); // checking table exist
		if ($result->err) {
			$rDATA['Создание таблицы']['@mess'][] = static_main::am('error','_big_err',$MODUL);
			return $rDATA;
		}
		$MODUL->setSystemFields();// установка системных полей
//if($MODUL->_cl=='uniusers') {print_r('<pre>');print_r($MODUL->_CFG['hook']);}
		if (!$result->num_rows()) {
			if (isset($_POST['sbmt'])) {
				if (!self::_creatTable($MODUL)) {
					$rDATA['Создание таблицы']['@mess'][] = array( 'error','Для модуля `'.$MODUL->caption.'` не удалось создать таблицу.' );
				}
				else {
					if (count($MODUL->def_records) and !self::_insertDefault($MODUL)) {
						self::deleteTable($MODUL);
						$rDATA['Создание таблицы']['@mess'][] = array( 'error', 'Для модуля `'.$MODUL->caption.'` не удалось записать дефолтные данные, и поэтому таблица не будет создана.' );
					} else
						$rDATA['Создание таблицы']['@mess'][] = array( 'notice', 'Для модуля `'.$MODUL->caption.'` успешно создана таблица.' );
				}
			}
			else {
				$rDATA['Создание таблицы']['@mess'][] = static_main::am('alert','_install_info',array($MODUL->_cl.'['.$MODUL->tablename.']'),$MODUL);
			}
			return $rDATA;
		}
		
		$dataTable = $MODUL->SQL->_getSQLTableInfo($MODUL->tablename);
//exit('TODO');

		foreach($dataTable as $fldname=>$fp) {
			if (isset($MODUL->fields[$fldname])) {
				$MODUL->fields[$fldname]['inst'] = '1';

				$currentFields = $fp['create'];
				$temp_currentFields = trim( str_replace(array(' ','"', "'", chr(194).chr(160),"\xC2xA0","\n"), '', mb_strtolower($currentFields)) );

				list($newFields,$rDATA[$fldname]['@mess']) = $MODUL->SQL->_fldformer($fldname, $MODUL->fields[$fldname]);
				$temp_newFields = trim(str_replace(array(' ','"', "'", chr(194).chr(160),"\xC2xA0","\n"), '', mb_strtolower($newFields)));

				if (isset($MODUL->fields[$fldname]['type']) and $temp_currentFields!=$temp_newFields) {
					$rDATA[$fldname]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` CHANGE `' . $fldname . '` ' . $newFields;
					$rDATA[$fldname]['@oldquery'] = $currentFields;
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
					list($temp,$rDATA[$key]['@mess']) = $MODUL->SQL->_fldformer($key, $param);
					$rDATA[$key]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' .$temp ;
				}
			}

		if (isset($MODUL->attaches))
			foreach ($MODUL->attaches as $key => $param) {
				if (!isset($param['inst'])) {
					list($temp,$rDATA[$key]['@mess']) = $MODUL->SQL->_fldformer($key, $MODUL->attprm);
					$rDATA[$key]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' .$temp ;
				}
				if (!self::_checkdir($MODUL->_CFG['_PATH']['path'].$MODUL->getPathForAtt($key))) {
					$rDATA[$key]['@mess'][] = static_main::am('error','_checkdir_error', array($MODUL->getPathForAtt($key)),$MODUL);
				}
				$rDATA['@reattach'] = true;
			}

		if (isset($MODUL->memos))
			foreach ($MODUL->memos as $key => $param) {
				if (!self::_checkdir($MODUL->_CFG['_PATH']['path'].$MODUL->getPathForMemo($key))) {
					$rDATA[$key]['@mess'][] = static_main::am('error','_recheck_err',$MODUL);
				}
			}


		$indexlist = $uniqlistR = $uniqlist = array();
		$primary = '';
		$result = $MODUL->SQL->execSQL('SHOW KEYS FROM `' . $MODUL->tablename . '`');
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
				$rDATA[$k]['@index'] .= ' drop key `' . $k . '` ';
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
				$rDATA[$k]['@index'] .= ' drop key `' . $k . '` ';
			}
		}
		$rDATA['Оптимизация']['@newquery'] = 'OPTIMIZE TABLE `' . $MODUL->tablename . '`';
		return $rDATA;
	}


	static function _xmlFormConf(&$MODUL) {
		$MODUL->form = array();
		$MODUL->form['_*features*_'] = array('name' => 'Configmodul', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
		$MODUL->form['_info'] = array('type' => 'info', 'css' => 'caption', 'caption' => static_main::m('_config'));
		foreach ($MODUL->config_form as $k => $r) {
			if (!is_array($MODUL->config[$k]))
				$MODUL->config_form[$k]['value'] = stripslashes($MODUL->config[$k]);
			else
				$MODUL->config_form[$k]['value'] = $MODUL->config[$k];
		}
		$MODUL->form = array_merge($MODUL->form, $MODUL->config_form);
		$MODUL->form['sbmt'] = array(
			'type' => 'submit',
			'value' => static_main::m('_submit'));
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
		$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'script.jquery/jqplot/plugins/jqplot.ohlcRenderer.min.js\',
			function(){' . $f . '},[
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'script.jquery/jqplot/jquery.jqplot.min.js\'),
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'script.jquery/jqplot/plugins/jqplot.cursor.min.js\'),
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'script.jquery/jqplot/plugins/jqplot.dateAxisRenderer.min.js\'),
			$.include(\'' . $MODUL->_CFG['_HREF']['_script'] . 'script.jquery/jqplot/plugins/jqplot.highlighter.min.js\')
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
	@import "' . $MODUL->_CFG['_HREF']['_style'] . 'style.jquery/ui.css";
	@import "' . $MODUL->_CFG['_HREF']['_script'] . 'script.jquery/jqplot/jquery.jqplot.min.css";
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
									if (!isset($imod['pref']) or !$imod['pref'])
										$imod['pref'] = ''; // по умолчинию без префикса
									if (isset($imod['path']) and $imod['path'])
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
			$mess[] = array( 'error','Access denied');
		else {
			$check_result = $MODUL->_checkmodstruct();

			if (isset($_POST['sbmt'])) {
				$flag = 1;
				foreach ($check_result as $_cl => $row) {
					if (isset($row['@reattach']) and isset($_POST['query_'.$_cl]['reattach'])) {
						_new_class($_cl, $MODUL_R);
						if (self::_reattaches($MODUL_R))
							$mess[] = array( 'ok', '<b>' . $_cl . '</b> - ' . static_main::m('_file_ok',$MODUL));
						else {
							$mess[] = array( 'error','<b>' . $_cl . '</b> - ' . static_main::m('_file_err',$MODUL));
							$flag = -1;
						}
						unset($row['@reattach']);
					}
					foreach ($row as $kk => $rr) {
						if(is_array($rr)) {
							if(isset($rr['@newquery']) and isset($_POST['query_'.$_cl][$kk.'@newquery'])) {
								$result = $MODUL->SQL->execSQL($rr['@newquery']);
								if ($result->err) {
									$mess[] = array( 'error','Error new query(' . $rr['@newquery'] . ')');
									$flag = -1;
								}
							}
							if(isset($rr['@index']) and isset($_POST['query_'.$_cl][$kk.'@index'])) {
									$result = $MODUL->SQL->execSQL($rr['@index']);
									if ($result->err) {
										$mess[] = array( 'error','Error index query(' . $rr['@index']. ')');
										$flag = -1;
									}
							}
						}
					}//end foreach
				}
				if (count($_POST)<=1)
					$mess[] = static_main::m('alert','_recheck_have_nothing',$MODUL);
				if ($flag)
					$mess[] = static_main::m('ok','_recheck_ok',$MODUL);
				//'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>'
			}
			else {
				$MODUL->form['_*features*_'] = array('name' => 'Checkmodul', 'method' => 'POST', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
				if (count($check_result)) {
					$MODUL->form['_info'] = array(
						'type' => 'info',
						'caption' => static_main::m('_recheck',$MODUL),
					);
					$MODUL->form['invert'] = array(
						'type' => 'info',
						'caption' => '<a href="#" onclick="return invert_select(\'form\');">Инвертировать выделение</a>',
					);

					foreach ($check_result as $_cl => $row) {
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
								$MODUL->form['query_'.$_cl]  = array(
									'caption'=>'Модуль '.$_cl,
									'type'=>'checkbox',
									'valuelist'=>$valuelist,
									'comment' => $HTML->transformPHP($message, 'messages'),
									'style'=>'border:solid 1px gray;margin:3px 0;'
								);
							}elseif(count($message))
								$mess = array_merge($mess,$message);
						}
						else
							$mess[] = array( 'error','Error data (' . $_cl . ' - ' . print_r($row, true) . ')');
					}

					$MODUL->form['sbmt'] = array(
						'type' => 'submit',
						'value' => static_main::m('_submit',$MODUL)
					);
				} else
					$mess[] = static_main::m('ok','_recheck_have_nothing',$MODUL);
			}
		}
		$DATA = array('form' => $MODUL->form, 'messages' => $mess);
		return Array($flag,$DATA);
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
			$rDATA['Ошибка']['@mess'][] = array( 'error','Ошибка инициализации модуля `modulprm`');
			return array($Mid => $rDATA);
		}

		list($MODUL,$rDATA['modulprm']['@mess']) = $MODULPRM->ForUpdateModulInfo($Mid,$OWN);
		if ($MODUL===false) {
			$rDATA['Ошибка']['@mess'][] = array( 'error','Ошибка инициализации модуля `'.$Mid.'`');
			return array($Mid => $rDATA);
		}
		elseif(!$MODUL->tablename) {
			$rDATA['Ахтунг']['@mess'][] = array( 'alert', 'Модуль `'.$MODUL->caption.'`['.$Mid.'] не использует базу данных.');
			return array($Mid => $rDATA);
		}
		elseif(!isset($MODULPRM->data[$Mid]) or $MODULPRM->data[$Mid][$MODULPRM->mf_actctrl]) {
			// синонимы для типов полей
			$temp = self::_checkTable($MODUL);
			if ($temp and count($temp))
				$rDATA = array_merge($rDATA, $temp);
		}

		if (count($rDATA))
			$rDATA = array($Mid => $rDATA);
		if (count($MODUL->Achilds))
			foreach ($MODUL->Achilds as $k => $r) {
				$temp = self::_checkmodstruct($k,$MODUL);
				if ($temp and count($temp))
					$rDATA = array_merge($rDATA, $temp);
			}

		return $rDATA;
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
		$_CFG = array();
		if ($mData !== false) {
			$_CFG = $mData;
		}
		if(!file_exists($file))
			return $_CFG;
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

		$fc = trim($fc, "<?php>\n");

		if ($fc)
			eval($fc);
		else
			trigger_error('NO CFG', E_USER_WARNING);

		return $_CFG;
	}

	static function saveUserCFG($SetDataCFG, $tempCFG=array()) {
		global $_CFG,$_CFGFORM;
		$mess = array();
		$DEF_CFG = self::getFdata($_CFG['_FILE']['core_config_f'], '/* MAIN_CFG */', '/* END_MAIN_CFG */');// чистый конфиг ядра
		$USER_CFG = self::getFdata($_CFG['_FILE']['config_f'], '', '', $DEF_CFG); // конечный конфиг
		// Редактируемые конфиги
		include_once($_CFG['_FILE']['core_configform_f']);

		$fl = false;$mess = array();
		$putFile = array();
		if(isset($SetDataCFG['wep'])) {
			if(!isset($SetDataCFG['wep']['password']) or !$SetDataCFG['wep']['password'] or $SetDataCFG['wep']['password']==$DEF_CFG['wep']['password']) {
				$mess[] = array( 'error','Поле '.$_CFGFORM['wep']['password']['caption'].' обязательное и не должно совпадать с дефолтным');
			}
			if(!isset($SetDataCFG['wep']['md5']) or !$SetDataCFG['wep']['md5'] or $SetDataCFG['wep']['md5']==$DEF_CFG['wep']['md5']) {
				$mess[] = array( 'error','Поле '.$_CFGFORM['wep']['md5']['caption'].' обязательное и не должно совпадать с дефолтным');
			}
		}
		if(count($mess))
			return array($fl,$mess);

		if(isset($SetDataCFG['sql'])) {
			$SQL = new sql($SetDataCFG['sql']); //пробуем подключиться к БД
			if(!$SQL->ready) {
				$mess[] = array( 'error','Ошибка подключения к БД.');
				return array($fl,$mess);
			}
		}
		//$SetDataCFG = static_main::MergeArrays($USER_CFG,$SetDataCFG);
		// объединяем конфиг записанный на пользователя и новые конфиги

		foreach ($_CFGFORM as $k => $r) {
			foreach ($DEF_CFG[$k] as $kk => $defr) {
				if(isset($SetDataCFG[$k][$kk]))
					$newr = $SetDataCFG[$k][$kk];
				elseif(isset($USER_CFG[$k][$kk]))
					$newr = $USER_CFG[$k][$kk];
				
				$flag = false;
				if(is_string($newr)) {
					if($newr != $defr)
						$flag = true;
					$newr = '\''.addcslashes($newr, '\'').'\'';
				}
				elseif(is_array($newr)) {
					if(!is_array($defr) or count(array_diff($newr,$defr)))
						$flag = true;
					$newr = str_replace(array("\n","\t","\r",'   ','  '),array('','','',' ',' '),var_export($newr,true));
				}else {
					$newr = (int)$newr;
					if($newr != $defr)
						$flag = true;
				}

				if ($flag) {
					$putFile[$k . '_' . $kk] = '$_CFG[\'' . $k . '\'][\'' . $kk . '\'] = ' . $newr . ';';
				}
			}
		}
		$putFile = "<?php\n\t//create time " . date('Y-m-d H:i') . "\n\t".implode("\n\t", $putFile)."\n";
		//Записать в конфиг все данные которые отличаются от данных по умолчанию
		if (!file_put_contents($_CFG['_FILE']['config_f'], $putFile)) {
			$mess[] = array( 'error','Ошибка записи настроек. Нет доступа к фаилу');
		} else {
			$fl = true;
			if(isset($SetDataCFG['sql']))
				$mess[] = array( 'ok', 'Подключение к БД успешно.');
			$mess[] = array( 'ok', 'Конфигурация успешно сохранена.');
		}
		return array($fl,$mess);
	}

	static function _creatTable(&$MODUL) {
		$fld = array();
		if (count($MODUL->fields))
			foreach ($MODUL->fields as $key => $param)
				list($fld[],$mess) = $MODUL->SQL->_fldformer($key, $param);
		if (count($MODUL->attaches))
			foreach ($MODUL->attaches as $key => $param)
				list($fld[],$mess) = $MODUL->SQL->_fldformer($key, $MODUL->attprm);
		/*foreach($MODUL->memos as $key => $param)
		  $fld[]= $MODUL->SQL->_fldformer($key, $MODUL->mmoprm);
		 */
		$fld[] = 'PRIMARY KEY(id)';

		if (isset($MODUL->unique_fields) and count($MODUL->unique_fields)) {
			foreach ($MODUL->unique_fields as $k => $r) {
				if (is_array($r))
					$r = implode('`,`', $r);
				$fld[] = 'UNIQUE KEY `' . $k . '` (`' . $r . '`)';
			}
		}
		if (isset($MODUL->index_fields) and count($MODUL->index_fields)) {
			foreach ($MODUL->index_fields as $k => $r) {
				if (!isset($MODUL->unique_fields[$k])) {
					if (is_array($r))
						$r = implode(',', $r);
					$fld[] = 'KEY `' . $k . '` (`' . $r . '`)';
				}
			}
		}
		$query = 'CREATE TABLE `' . $MODUL->tablename . '` (' . implode(',', $fld) . ') ENGINE=MyISAM DEFAULT CHARSET=' . $MODUL->_CFG['sql']['setnames'] . ' COMMENT = "' . $MODUL->ver . '"';
		// to execute query
		$result = $MODUL->SQL->execSQL($query);
		if ($result->err) {
			return false;
		}
		return true;
	}

	static function deleteTable(&$MODUL) {
		return $MODUL->SQL->execSQL('DROP TABLE `' . $MODUL->tablename . '`');
	}
	/**
	 * Запись дефолтных данных
	 * @return <type>
	 */
	static function _insertDefault(&$MODUL) {
		foreach ($MODUL->def_records as $row) {
			if (!$MODUL->_add_item($row)) {
				//return static_main::log('error','Error add default record into `'.$MODUL->tablename.'`',$MODUL->_cl);
				return false;
			}
		}
		//return static_main::log('ok','Insert default records into table ' . $MODUL->tablename . '.',$MODUL->_cl);
		return true;
	}

	/**
	 * Проверка существования директории и прав записи в него, и создание
	 *
	 * @param object $MODUL Текщий объект класса
	 * @param string $dir Проверяемая дирректория
	 * @return bool Результат
	 */
	static function _checkdir($dir) {
		global $_CFG;
		if(!$dir) return false;
		if (!file_exists($dir)) {
			if (!file_exists(dirname($dir))) {
				self::_checkdir(dirname($dir));
			}
			if (!mkdir($dir, $_CFG['wep']['chmod']))
				return static_main::log('error','Cannot create directory <b>' . $dir . '</b>');
		}
		else {
			$f = fopen($dir . '/t_e_s_t', 'w');
			if (!$f)
				return static_main::log('error','Cannot create file in directory <b>' . $dir . '</b>');

			$err = fwrite($f, 'zzz') == -1;
			fclose($f);
			unlink($dir . '/t_e_s_t');

			if ($err)
				return static_main::log('error','Cannot write/read file in directory <b>' . $dir . '</b>');
		}
		return true;
	}

	static function checkWepconf() {
		global $_CFG;
		$flag= true;
		foreach($_CFG['_PATH'] as $k=>$r) {
			if(!self::_checkdir($r)) {
				static_main::log('error','Ошибка создания директории '.$r);
				$flag= false;
			}
		}
		if(!file_exists($_CFG['_PATH']['wepconf']. '.htaccess')) {
			file_put_contents($_CFG['_PATH']['wepconf'] . '.htaccess','Deny from all');
		}
		return $flag;
	}

// END static class
}
