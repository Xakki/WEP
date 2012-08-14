<?php

class static_tools {

	private function ___construct() {
		
	}

	static function _reinstall(&$MODUL) {
		$MODUL->SQL->_tableDelete($MODUL->tablename);
		self::_installTable($MODUL);
	}

	/**
	 * Установка модуля
	 *
	 * @return bool Результат
	 */
	static function _installTable(&$MODUL) {
		if (!$MODUL->tablename) {
			static_main::log('notice', 'Для модуля ' . $MODUL->caption . ' таблица не требуется.', $MODUL->_cl);
			return true;
		}
		$flag = $MODUL->SQL->_tableExists($MODUL->tablename); // checking table exist

		if (!$flag) {
			// contruct of query
			if (!$MODUL->SQL->_tableCreate($MODUL)) {
				static_main::log('error', 'Для модуля `' . $MODUL->caption . '` не удалось создать таблицу.', $MODUL->_cl);
				return false;
			}
			if (!self::_insertDefault($MODUL)) {
				$MODUL->SQL->_tableDelete($MODUL->tablename);
				static_main::log('error', 'Для модуля `' . $MODUL->caption . '` не удалось записать дефолтные данные, и поэтому таблица не будет создана.', $MODUL->_cl);
				return false;
			}
			static_main::log('notice', 'Для модуля `' . $MODUL->caption . '` успешно создана таблица.', $MODUL->_cl);
		}

		$flag = true;
		if (count($MODUL->Achilds)) {
			foreach ($MODUL->childs as &$child) {
				$temp = self::_installTable($child);
				if (!$temp)
					return false;
			}
			unset($child);
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
			$rDATA['Создание таблицы']['@mess'][] = array('notice', 'Модуль `' . $MODUL->caption . '`[' . $MODUL->_cl . '] не использует базу данных.');
			return $rDATA;
		}
		$flag = $MODUL->SQL->_tableExists($MODUL->tablename);
		/* if (is_null($flag)) {
		  $rDATA['Создание таблицы']['@mess'][] = static_main::am('error','_big_err',$MODUL);
		  return $rDATA;
		  } */
		if (!$flag) {
			if (isset($_POST['sbmt'])) {
				if (!$MODUL->SQL->_tableCreate($MODUL)) {
					$rDATA['Создание таблицы']['@mess'][] = array('error', 'Для модуля `' . $MODUL->caption . '` не удалось создать таблицу.');
				} else {
					if (!self::_insertDefault($MODUL)) {
						$MODUL->SQL->_tableDelete($MODUL->tablename);
						$rDATA['Создание таблицы']['@mess'][] = array('error', 'Для модуля `' . $MODUL->caption . '` не удалось записать дефолтные данные, и поэтому таблица не будет создана.');
					} else
						$rDATA['Создание таблицы']['@mess'][] = array('notice', 'Для модуля `' . $MODUL->caption . '` успешно создана таблица.');
				}
			}
			else {
				$rDATA['Создание таблицы']['@mess'][] = static_main::am('alert', '_install_info', array($MODUL->_cl . '[' . $MODUL->tablename . ']'), $MODUL);
			}
			return $rDATA;
		}

		$dataTable = $MODUL->SQL->_getSQLTableInfo($MODUL->tablename);
//exit('TODO');

		foreach ($dataTable as $fldname => $fp) {
			if (isset($MODUL->fields[$fldname])) {
				$MODUL->fields[$fldname]['inst'] = '1';

				$currentFields = $fp['create'];
				$temp_currentFields = trim(str_replace(array(' ', '"', "'", chr(194) . chr(160), "\xC2xA0", "\n"), '', mb_strtolower($currentFields)));

				list($newFields, $rDATA[$fldname]['@mess']) = $MODUL->SQL->_fldformer($fldname, $MODUL->fields[$fldname]);
				$temp_newFields = trim(str_replace(array(' ', '"', "'", chr(194) . chr(160), "\xC2xA0", "\n"), '', mb_strtolower($newFields)));

				if (isset($MODUL->fields[$fldname]['type']) and $temp_currentFields != $temp_newFields) {
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
					list($temp, $rDATA[$key]['@mess']) = $MODUL->SQL->_fldformer($key, $param);
					$rDATA[$key]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' . $temp;
				}
			}

		// Проверка фаилов
		if (isset($MODUL->attaches))
			foreach ($MODUL->attaches as $key => $param) {
				if (!isset($param['inst'])) {
					list($temp, $rDATA[$key]['@mess']) = $MODUL->SQL->_fldformer($key, $MODUL->attprm);
					$rDATA[$key]['@newquery'] = 'ALTER TABLE `' . $MODUL->tablename . '` ADD ' . $temp;
				}
				if (!self::_checkdir($MODUL->_CFG['_PATH']['path'] . $MODUL->getPathForAtt($key))) {
					$rDATA[$key]['@mess'][] = static_main::am('error', '_checkdir_error', array($MODUL->getPathForAtt($key)), $MODUL);
				}
				$rDATA['@reattach'] = true;
			}

		if (isset($MODUL->memos))
			foreach ($MODUL->memos as $key => $param) {
				if (!self::_checkdir($MODUL->_CFG['_PATH']['path'] . $MODUL->getPathForMemo($key))) {
					$rDATA[$key]['@mess'][] = static_main::am('error', '_recheck_err', $MODUL);
				}
			}


		$indexlist = $uniqlistR = $uniqlist = array();
		$primary = '';
		list($primary, $uniqlist, $indexlist) = $MODUL->SQL->_tableKeys($MODUL);

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
					if (isset($indexlist[$k])) {
						$tmp = ' drop key `' . $k . '`, ';
						unset($indexlist[$k]);
					}
					if (is_array($r))
						$r = implode('`,`', $r);
					if (!isset($rDATA[$k]['@index']))
						$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
					else
						$rDATA[$k]['@index'] .= ', ';
					if(is_array($r)) $r = implode('`,`',$r);
					$rDATA[$k]['@index'] .= ' ' . $tmp . ' ADD UNIQUE KEY `' . $k . '` (`' . $r . '`)';
				} else {
					unset($uniqlist[$k]);
				}
			}
		}
		if (count($uniqlist)) {
			foreach ($uniqlist as $k => $r) {
				if (!isset($rDATA[$k]['@index']))
					$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
				else
					$rDATA[$k]['@index'] .= ', ';
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
					if (!isset($rDATA[$k]['@index']))
						$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
					else
						$rDATA[$k]['@index'] .= ', ';
					if(is_array($r)) $r = implode('`,`',$r);
					$rDATA[$k]['@index'] .= ' add index `' . $k . '` (`' . $r . '`)';
				} else {
					unset($indexlist[$k]);
				}
			}
		if (count($indexlist)) {
			foreach ($indexlist as $k => $r) {
				if (!isset($rDATA[$k]['@index']))
					$rDATA[$k]['@index'] = 'ALTER TABLE `' . $MODUL->tablename . '`';
				else
					$rDATA[$k]['@index'] .= ', ';
				$rDATA[$k]['@index'] .= ' drop key `' . $k . '` ';
			}
		}
		//TODO : перенести в отдельный раздел - Обслуживание БД
		//$rDATA['Оптимизация']['@newquery'] = 'OPTIMIZE TABLE `' . $MODUL->tablename . '`';
		return $rDATA;
	}

	static function _save_config($conf, $file) {
		foreach ($conf as &$r) {
			if (is_string($r) and strpos($r, ':|') !== false) {
				$temp = explode(':|', $r);
				$r = array();
				foreach ($temp as $t => $d) {
					$temp2 = explode(':=', $d);
					if (count($temp2) > 1)
						$r[trim($temp2[0])] = trim($temp2[1]);
					else
						$r[] = trim($d);
				}
			}
		}
		unset($r);
		file_put_contents($file, var_export($conf, true));
		return true;
	}
	/**
	 * Переустановка БД модуля
	 * @return array form
	 */
	static public function toolsReinstall($_this) {
		$RESULT = array('messages' => array(), 'form' => array());
		$fields_form = $mess = array();
		if (!static_main::_prmModul($_this->_cl, array(11)))
			$RESULT['messages'][] = static_main::am('error', 'denied', $_this);
		elseif (count($_POST) and $_POST['sbmt']) {
			static_tools::_reinstall($_this);
			$RESULT['messages'][] = static_main::am('ok', '_reinstall_ok', $_this);
			$RESULT['reloadPage'] = true;
		} 
		else {
			//$fields_form['_*features*_'] = array('name' => 'Reinstall', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => static_main::m('_reinstall_info', $_this));
			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value' => static_main::m('Submit', $_this));
		}
		$_this->kFields2FormFields($fields_form);
		$RESULT['form'] = $fields_form;
		return $RESULT;
	}

	/**
	* Tools для редактирования конфига у модуля
	*/
	static public function toolsConfigmodul($_this) {
		$fields_form = array();
		$RESULT = array('messages' => array(), 'form' => array());

		if (!static_main::_prmModul($_this->_cl, array(13))) 
		{
			$RESULT['messages'][] = static_main::am('error', 'denied', $_this);
		}
		elseif (!count($_this->config_form)) 
		{
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => static_main::m('_configno', $_this));
		} 
		else
		{
			foreach ($_this->config as $k => &$r) {
				if (is_array($r) and isset($_this->config_form[$k]) and !isset($_this->config_form[$k]['multiple'])) {
					/*$temp = array();
					foreach ($r as $t => $d) {
						if (strpos($d, ':=') === false)
							$temp[] = trim($t) . ':=' . trim($d);
						else
							$temp[] = trim($d);
					}
					$r = implode(' :| ', $temp);*/
				}
			}
			unset($r);

			// Сохраняемся
			if (count($_POST)) 
			{
				$params = array();
				$arr = $_this->fFormCheck($_POST, $params, $_this->config_form); // 2ой параметр просто так
				$config = array();
				foreach ($_this->config as $k => $r) 
				{
					if (isset($arr['vars'][$k])) {
						$_this->config_form[$k]['value'] = $arr['vars'][$k];
						$config[$k] = $arr['vars'][$k];
					}
				}
				$_this->config = $config;
				if (!count($arr['mess'])) 
				{
					$RESULT['messages'][] = static_main::am('ok', 'update', $_this);
					static_tools::_save_config($config, $_this->_file_cfg);
					$RESULT['reloadPage'] = true; // Это явный костыль, но включать мозг у меня нет времени, когда нибудь что нибудь придумаю
				}
				else
					$RESULT['messages'] = array_merge($RESULT['messages'],$arr['mess']);
			} 
			else 
			{
				$fields_form['_info'] = array('type' => 'info', 'css' => 'caption', 'caption' => static_main::m('_config'));
				foreach ($_this->config_form as $k => $r) 
				{
					if(isset($_this->config[$k])) 
					{
						if (!is_array($_this->config[$k]))
							$_this->config_form[$k]['value'] = stripslashes($_this->config[$k]);
						else
							$_this->config_form[$k]['value'] = $_this->config[$k];
					}
				}
				$fields_form = $fields_form+$_this->config_form;
				$fields_form['sbmt'] = array(
					'type' => 'submit',
					'value' => static_main::m('Submit'));
			}
		}
		$_this->kFields2FormFields($fields_form);
		$RESULT['form'] = $fields_form;
		return $RESULT;
	}

	/**
	 * Групповые операции
	 * TODO : это контрол - нужно его вынести из модуля
	 * @return array form
	 */
	static public function toolsSuperGroup(&$_this) {
		global $_tpl;
		$fields_form = $mess = array();
		if (!static_main::_prmModul($_this->_cl, array(5, 7)))
			$mess[] = static_main::am('error', 'denied', $_this);
		elseif (!isset($_COOKIE['SuperGroup'][$_this->_cl]) or !count($_COOKIE['SuperGroup'][$_this->_cl]))
			$mess[] = static_main::am('alert', 'Нет выбранных элементов', $_this);
		elseif (count($_POST)) {

			$type = '';
			if (isset($_POST['sbmt_on'])) {
				$type = 'on';
				$_this->id = array_keys($_COOKIE['SuperGroup'][$_this->_cl]);
				$_this->_update(array('active' => 1));
				$mess[] = static_main::am('ok', 'Успешно включено', $_this);
			} 
			elseif (isset($_POST['sbmt_off'])) {
				$type = 'off';
				$_this->id = array_keys($_COOKIE['SuperGroup'][$_this->_cl]);
				$_this->_update(array('active' => 0));
				$mess[] = static_main::am('ok', 'Успешно отключено', $_this);
			} 
			elseif (isset($_POST['sbmt_del'])) {
				$type = 'del';
				$_this->id = array_keys($_COOKIE['SuperGroup'][$_this->_cl]);
				$_this->_delete();
				$mess[] = static_main::am('ok', 'Успешно удалено', $_this);
			} 
			elseif (isset($_POST['sbmt_clear'])) {
				$type = 'clear';
				$mess[] = static_main::am('ok', 'Список чист', $_this);
			}

			if (count($mess)) {
				foreach ($_COOKIE['SuperGroup'][$_this->_cl] as $ck => $ck)
					$_tpl['onload'] .= 'setCookie("SuperGroup[' . $_this->_cl . '][' . $ck . ']",0,-10000);';
				$_tpl['onload'] .= '$("span.wepSuperGroupCount").text(0).parent().hide("slow");wep.SuperGroupClear("' . $type . '");';
				$_tpl['onload'] .= '$("#tools_block").hide();';
			}
		} else {
			$fields_form['_*features*_'] = array('name' => 'SuperGroup', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']), 'prevhref' => $_SERVER['HTTP_REFERER']);
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">' . $_this->caption . '</h2><h3 style="text-align:center;">Выбранно элементов : ' . count($_COOKIE['SuperGroup'][$_this->_cl]) . '</h3>');
			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value' => array(
					'_off' => static_main::m('Отключить', $_this),
					'_on' => static_main::m('Включить', $_this),
					'_del' => static_main::m('Delete', $_this),
					'_clear' => static_main::m('Отменить выбранные элементы.', $_this),
					'' => static_main::m('Отмена', $_this),
				)
			);
		}
		$_this->kFields2FormFields($fields_form);
		return Array('form' => $fields_form, 'messages' => $mess);
	}

	/*
	  static public function toolsReindex(&$_this){
	  $fields_form = $mess = array();
	  if(!static_main::_prmModul($_this->_cl,array(12)))
	  $mess[] = array('name'=>'error', 'value'=>static_main::m('denied',$_this));
	  elseif(count($_POST) and $_POST['sbmt']){
	  if(!$_this->_reindex())
	  $mess[] = array('name'=>'error', 'value'=>static_main::m('_reindex_ok',$_this));
	  else
	  $mess[] = array('name'=>'error', 'value'=>static_main::m('_reindex_err',$_this));
	  }else{
	  $fields_form['_*features*_'] = array('name'=>'reindex','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	  $fields_form['_info'] = array(
	  'type'=>'info',
	  'caption'=>static_main::m('_reindex_info',$_this));
	  $fields_form['sbmt'] = array(
	  'type'=>'submit',
	  'value'=>static_main::m('Submit',$_this));
	  }
	  $_this->kFields2FormFields($fields_form);
	  return Array('form'=>$fields_form, 'messages'=>$mess);
	  }

	  private function _reindex()
	  {
	  return true;
	  }
	 */
	static function _staticStatsmodul(&$MODUL, $oid = '') {
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
		if (is_array($MODUL->mf_statistic['X'])) {
			$X = current($MODUL->mf_statistic['X']);
		}else
			$X = $MODUL->mf_statistic['X'];
		$clause = 'SELECT ' . $X . ' as `X`, ' . $MODUL->mf_statistic['Y'] . ' as `Y` FROM `' . $MODUL->tablename . '` t1 ' . $clause . ' GROUP BY X ORDER BY X';
		$result = $MODUL->SQL->execSQL($clause);
		$data = array();
		$maxY = 0;
		$minX = 0;
		$maxX = 0;
		if (!$result->err) {
			while ($row = $result->fetch()) {
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

		$stepY = round($maxY, -1) / 10;
		$eval= ' 
			jqplot = function() {
				line1 = [' . implode(',', $data) . '];
				var option = {
					caption : \'' . $MODUL->caption . '\',
					xName : \'' . $MODUL->mf_statistic['Xname'] . '\',
					yName : \'' . $MODUL->mf_statistic['Yname'] . '\',
					yStep : ' . $stepY . ',
				};
				readyPlot(option);
			}
			var plotScript = {
				\'script.jquery/jqplot/jquery.jqplot.min\' : {
					\'script.jquery/jqplot/plugins/jqplot.cursor.min\' : {
						\'script.jquery/jqplot/plugins/jqplot.dateAxisRenderer.min\': {
							\'script.jquery/jqplot/plugins/jqplot.highlighter.min\': {
								\'script.jquery/jqplot/plugins/jqplot.ohlcRenderer.min\': {\'onload\':\'jqplot();\'}
							}
						}
					}
				}
			};
			wep.scriptLoad(plotScript);
		';
		/* $plugin = '';
		  if(isset($MODUL->mf_statistic['plugin_date']))
		  $plugin .= ''; */

		$html = '';
		if (count($filtr[0]))
			$html .= 'Результат статистики выводится по фильтру<br/>';
		$html .= '
	<div id="statschart1" data-height="380px" data-width="100%" style="margin-top:10px; margin-left:10px;min-width:1200px;width:100%;"></div>
	<div id="statschart2" data-height="150px" data-width="100%" style="margin-top:10px; margin-left:10px;width:100%;"></div>
	<style>
	@import "' . $MODUL->_CFG['_HREF']['_style'] . 'style.jquery/ui.css";
	@import "' . $MODUL->_CFG['_HREF']['_script'] . 'script.jquery/jqplot/jquery.jqplot.min.css";
	</style>
	';
		//$html = '<span class="buttonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>' . $html;
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

			while ($row = $result->fetch()) {
				//$data[]= $row;
				foreach ($MODUL->attaches as $key => $value) {
					$pathimg = $MODUL->_CFG['_PATH']['path'] . $MODUL->getPathForAtt($key);
					$MIME = $value['mime'];
					if(isset($MIME['image'])) {
						unset($MIME['image']);
						$MIME = array_merge($MIME, array('gif','jpeg','jpg','png','swf','psd','bmp','tiff','jpc','jp2','jpx','jb2','swc','iff','wbmp','xbm','ico'));
					}
					foreach ($MIME as $k => $ext) {
						$newname = $pathimg . '/' . $row['id'] . '.' . $ext;
						if (file_exists($newname)) {
							if (isset($value['thumb']) and count($value['thumb'])) { // проверка на наличие модифицированных изображений
								if (!static_image::_is_image($newname)) // опред тип файла
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
											static_image::_cropImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'resize')
											static_image::_resizeImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'resizecrop')
											static_image::_resizecropImage($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type'] == 'water')
											static_image::_waterMark($MODUL, $newname, $newname2, $imod['w'], $imod['h']);
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

	/**
	 * Проверка структуры модуля
	 *
	 *
	 * @param object $MODUL Текущий объект класса
	 * @return array
	 */
	static function _checkmodstruct($Mid, &$OWN = NULL) {
		$rDATA = array();
		//'mess'=>array(),
		//'oldquery'=>array(),
		//'newquery'=>array()

		if (!_new_class('modulprm', $MODULPRM)) {
			$rDATA['Ошибка']['@mess'][] = array('error', 'Ошибка инициализации модуля `modulprm`');
			return array($Mid => $rDATA);
		}
		unset($MODULPRM->_CFG['modulprm2'][$Mid]); // Потом Удаляем отсутствующие модули
		list($MODUL, $rDATA['modulprm']['@mess']) = $MODULPRM->ForUpdateModulInfo($Mid, $OWN);
		if ($MODUL === false) {
			$rDATA['Ошибка']['@mess'][] = array('error', 'Ошибка инициализации модуля `' . $Mid . '`');
			return array($Mid => $rDATA);
		} elseif (!$MODUL->tablename) {
			$rDATA['Ахтунг']['@mess'][] = array('alert', 'Модуль `' . $MODUL->caption . '`[' . $Mid . '] не использует базу данных.');
			return array($Mid => $rDATA);
		} elseif (!isset($MODULPRM->data[$Mid]) or $MODULPRM->data[$Mid][$MODULPRM->mf_actctrl]) {
			// синонимы для типов полей
			$temp = self::_checkTable($MODUL);
			if ($temp and count($temp))
				$rDATA = array_merge($rDATA, $temp);
		}

		if (count($rDATA))
			$rDATA = array($Mid => $rDATA);
		if (count($MODUL->Achilds))
			foreach ($MODUL->Achilds as $k => $r) {
				$temp = self::_checkmodstruct($k, $MODUL);
				if ($temp and count($temp))
					$rDATA = array_merge($rDATA, $temp);
			}

		if (!$OWN and isset($MODUL->_CFG['modulprm'][$MODUL->_cl]) and $MODUL->ver != $MODUL->_CFG['modulprm'][$MODUL->_cl]['ver']) {
			$file = $MODUL->_CFG['modulprm'][$Mid]['path'];
			$file = substr($file, 0, -(strlen($Mid . '.class.php'))) . 'updater/' . $MODUL->ver . '.php';
			if (file_exists($file)) {
				include($file);
			}
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
	static function getFdata($file, $start = '', $end = '', $mData = false) {
		$_CFG = array();
		if ($mData !== false) {
			$_CFG = $mData;
		}
		if (!file_exists($file))
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

	static function saveUserCFG($SetDataCFG) {
		//$SetDataCFG = static_main::MergeArrays($USER_CFG,$SetDataCFG);
		// объединяем конфиг записанный на пользователя и новые конфиги
		global $_CFG, $_CFGFORM;
		$fl = false;
		$mess = array();

		include_once($_CFG['_FILE']['wep_config_form']);

		$DEF_CFG = self::getFdata($_CFG['_FILE']['wep_config'], '/* MAIN_CFG */', '/* END_MAIN_CFG */'); // чистый конфиг ядра

		if (isset($SetDataCFG['wep'])) {
			if (!isset($SetDataCFG['wep']['password']) or !$SetDataCFG['wep']['password'] or $SetDataCFG['wep']['password'] == $DEF_CFG['wep']['password']) {
				$mess[] = array('error', 'Поле ' . $_CFGFORM['wep']['password']['caption'] . ' обязательное и не должно совпадать с дефолтным');
			}
			if (!isset($SetDataCFG['wep']['md5']) or !$SetDataCFG['wep']['md5'] or $SetDataCFG['wep']['md5'] == $DEF_CFG['wep']['md5']) {
				$mess[] = array('error', 'Поле ' . $_CFGFORM['wep']['md5']['caption'] . ' обязательное и не должно совпадать с дефолтным');
			}
		}

		if (isset($SetDataCFG['sql']) and $SetDataCFG['sql']['type']) {
			$SQL = new $SetDataCFG['sql']['type']($SetDataCFG['sql']); //пробуем подключиться к БД
			if (!$SQL->ready) {
				$mess[] = array('error', 'Ошибка подключения к БД.');
			}
		}
		if (count($mess))
			return array($fl, $mess);

		return self::saveCFG($SetDataCFG, $_CFG['_FILE']['config'], $DEF_CFG);
	}

	static function saveCFG($SetDataCFG, $file, $DEF_CFG = array()) {
		global $_CFG;
		$fl = false;
		$mess = array();
		$putFile = array();
		$USER_CFG = self::getFdata($file, '', '', $DEF_CFG); // конечный конфиг
		// Редактируемые конфиги
		$fl = false;
		if (!count($DEF_CFG)) {
			$fl = true;
			$DEF_CFG = $SetDataCFG;
		}
		foreach ($DEF_CFG as $k => $r) {
			foreach ($r as $kk => $defr) {
				if (isset($SetDataCFG[$k][$kk]))
					$newr = $SetDataCFG[$k][$kk];
				elseif (isset($USER_CFG[$k][$kk]))
					$newr = $USER_CFG[$k][$kk];

				$flag = false;
				if (is_string($newr)) {
					if ($fl or $newr != $defr)
						$flag = true;
					$newr = '\'' . addcslashes($newr, '\'') . '\'';
				}
				elseif (is_array($newr)) {
					if ($fl or !is_array($defr) or count(array_diff($defr, $newr))){
						$flag = true;
					}
					$newr = str_replace(array("\n", "\t", "\r", '   ', '  '), array('', '', '', ' ', ' '), var_export($newr, true));
				} else {
					$newr = (int) $newr;
					if ($fl or $newr != $defr)
						$flag = true;
				}

				if ($flag) {
					$putFile[$k . '_' . $kk] = '$_CFG[\'' . $k . '\'][\'' . $kk . '\'] = ' . $newr . ';';
				}
			}
		}
		$putFile = "<?php\n\t//create time " . date('Y-m-d H:i') . "\n\t" . implode("\n\t", $putFile) . "\n";
		//Записать в конфиг все данные которые отличаются от данных по умолчанию
		if (!file_put_contents($file, $putFile)) {
			$mess[] = array('error', 'Ошибка записи настроек. Нет доступа к фаилу');
		} else {
			$fl = true;
			if (isset($SetDataCFG['sql']))
				$mess[] = array('ok', 'Подключение к БД успешно.');
			$mess[] = array('ok', 'Конфигурация успешно сохранена.');
		}
		return array($fl, $mess);
	}

	/**
	 * Запись дефолтных данных
	 * @return <type>
	 */
	static function _insertDefault(&$MODUL) {
		global $_CFG;
		$ReflectedClass = new ReflectionClass($MODUL->_cl.'_class');
		$file = $ReflectedClass->getFileName();
		$file = dirname($file).'/'.$MODUL->_cl.'.default.'.$MODUL->SQL_CFG['type'];
		if(file_exists($file)) {
			$lines = file($file);
			foreach ($lines as $line) {
				$line = trim($line,"\s\;");
				if($line) {
					if(!$MODUL->SQL->execSQL($line))
						return false;
				}
			}
		}
		foreach ($MODUL->def_records as $row) {
			if (!$MODUL->_add($row)) {
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
		$dir = rtrim($dir,'/');
		if (!$dir)
			return false;
		if (!file_exists($dir)) {
			/*if (!file_exists(dirname($dir))) {
				self::_checkdir(dirname($dir));
			}*/
			if (!mkdir($dir, $_CFG['wep']['chmod'], true))
				return static_main::log('error', 'Cannot create directory <b>' . $dir . '</b>');
		}
		else {
			_chmod($dir);
			$f = fopen($dir . '/test.file', 'w');
			if (!$f)
				return static_main::log('error', 'Cannot create file `test.file` in directory `' . $dir . '`');

			$err = fwrite($f, 'zzz') == -1;
			fclose($f);
			unlink($dir . '/test.file');

			if ($err)
				return static_main::log('error', 'Cannot write/read file `test.file` in directory `' . $dir . '`');
		}
		return true;
	}

	/**
	 * Удаление дериктории с патрохами
	 *
	 * @param string $dir Удаляемая дирректория
	 * @return bool Результат
	 */
	
	static function _rmdir($dir) {
		if (!file_exists($dir)) return true;
		if (!is_dir($dir) || is_link($dir)) return unlink($dir);
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') continue;
			if (!self::_rmdir($dir . "/" . $item)) {
				_chmod($dir . "/" . $item, 0777);
				if (!self::_rmdir($dir . "/" . $item)) return false;
			}
		}
		return rmdir($dir);
	}
	
	static function checkWepconf() {
		global $_CFG;
		$flag = true;
		foreach ($_CFG['_PATH'] as $k => $r) {
			if (!self::_checkdir($r)) {
				static_main::log('error', 'Ошибка создания директории ' . $r);
				$flag = false;
			}
		}

		if (!file_exists($_CFG['_PATH']['wepconf'] . '.htaccess')) {
			file_put_contents($_CFG['_PATH']['wepconf'] . '.htaccess', 'php_flag engine 0
<FilesMatch "\.(php|inc|cfg|key|htaccess|cmd)$">
order allow,deny
deny from all
</FilesMatch>');
		}

		if (!file_exists($_CFG['_PATH']['phpscript'] . 'cron.php')) {
			file_put_contents($_CFG['_PATH']['phpscript'] . 'cron.php', '<?php
	$_CFG[\'_PATH\'][\'wep\'] = dirname(dirname(dirname(__FILE__))).\'/_wep/\';
	include($_CFG[\'_PATH\'][\'wep\'].\'_phpscript/cron.php\');');
		}

		if (!file_exists($_CFG['_PATH']['content'] . '.htaccess')) {
			file_put_contents($_CFG['_PATH']['content'] . '.htaccess', 'php_flag engine 0');
		}
		return $flag;
	}

	/* STEP2 функция */

	static function _toolsCheckmodul(&$MODUL) {
		global $HTML;
		$flag = 0;
		$MODUL->form = $mess = array();
		if (!static_main::_prmModul($MODUL->_cl, array(14)))
			$mess[] = array('error', 'Access denied');
		else {
			$check_result = $MODUL->_checkmodstruct();

			if (isset($_POST['sbmt'])) {
				$flag = 1;
				foreach ($check_result as $_cl => $row) {
					if (isset($row['@reattach']) and isset($_POST['query_' . $_cl]['reattach'])) {
						_new_class($_cl, $MODUL_R);
						if (self::_reattaches($MODUL_R))
							$mess[] = array('ok', '<b>' . $_cl . '</b> - ' . static_main::m('_file_ok', $MODUL));
						else {
							$mess[] = array('error', '<b>' . $_cl . '</b> - ' . static_main::m('_file_err', $MODUL));
							$flag = -1;
						}
						unset($row['@reattach']);
					}
					foreach ($row as $kk => $rr) {
						if (is_array($rr)) {
							if (isset($rr['@newquery']) and isset($_POST['query_' . $_cl][$kk . '@newquery'])) {
								$result = $MODUL->SQL->execSQL($rr['@newquery']);
								if ($result->err) {
									$mess[] = array('error', 'Error new query(' . $rr['@newquery'] . ')');
									$flag = -1;
								}
							}
							if (isset($rr['@index']) and isset($_POST['query_' . $_cl][$kk . '@index'])) {
								$result = $MODUL->SQL->execSQL($rr['@index']);
								if ($result->err) {
									$mess[] = array('error', 'Error index query(' . $rr['@index'] . ')');
									$flag = -1;
								}
							}
						}
					}//end foreach
				}
				if (count($_POST) <= 1)
					$mess[] = static_main::am('alert', '_recheck_have_nothing', $MODUL);
				if ($flag)
					$mess[] = static_main::am('ok', '_recheck_ok', $MODUL);
				//'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>'
			}
			else {
				$MODUL->form['_*features*_'] = array('name' => 'Checkmodul', 'method' => 'POST', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
				if (count($check_result)) {
					$MODUL->form['_info'] = array(
						'type' => 'info',
						'caption' => static_main::m('_recheck', $MODUL),
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
							$value = '';
							if (isset($row['@value'])) {
								$value = $row['@value'];
								unset($row['@value']);
							}
							foreach ($row as $kk => $rr) {
								if (isset($rr['@mess'])) {
									$message = array_merge($message, $rr['@mess']);
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
									$valuelist[$kk . '@newquery'] = '<i>' . $kk . '</i> - ' . $desc;

								if (is_array($rr) and isset($rr['@index']))
									$valuelist[$kk . '@index'] = '<i>' . $kk . '</i> - ' . $rr['@index'];
							}
							if (count($valuelist)) {
								$MODUL->form['query_' . $_cl] = array(
									'caption' => 'Модуль ' . $_cl,
									'type' => 'checkbox',
									'valuelist' => $valuelist,
									'comment' => $HTML->transformPHP($message, 'messages'),
									'style' => 'border-bottom:solid 1px #e1e1e1;margin:3px 0;'
								);
								if ($value)
									$MODUL->form['query_' . $_cl]['value'] = $value;
							} elseif (count($message)) {
								$MODUL->form['query_' . $_cl] = array(
									'type' => 'html',
									'value' => 'Модуль ' . $_cl.' : '.$HTML->transformPHP($message, 'messages'),
									'style' => 'border-bottom:solid 1px gray;margin:3px 0;'
								);
								//$mess = array_merge($mess, $message);
							}
						}
						else
							$mess[] = array('error', 'Error data (' . $_cl . ' - ' . print_r($row, true) . ')');
					}

					$MODUL->form['sbmt'] = array(
						'type' => 'submit',
						'value' => static_main::m('Submit', $MODUL)
					);
				} else
					$mess[] = static_main::am('ok', '_recheck_have_nothing', $MODUL);
			}
		}
		$DATA = array('form' => $MODUL->form, 'messages' => $mess);
		return Array($flag, $DATA);
	}

	static function extractZip($zipFile = '', $zipDir = '', $dirFromZip = '') {
		 // $zipDir Папка для распаковки.
		if(!$zipDir) {
			$zipDir = substr($zipFile,0,-4).'/';
		}
		$zip = zip_open($zipFile);

		 if ($zip) {
			  while ($zip_entry = zip_read($zip)) {
					// Перекодируем с CP866 в CP1251
					$completePath = $zipDir . dirname(iconv('CP866', 'CP1251', zip_entry_name($zip_entry)));
					$completeName = $zipDir . iconv('CP866', 'CP1251', zip_entry_name($zip_entry));
					
					if (!file_exists($completePath) && preg_match('#^' . $dirFromZip .'.*#', dirname(zip_entry_name($zip_entry)))) {
						 $tmp = '';
						 foreach (explode('/', $completePath) as $k) {
							  $tmp .= $k . '/';
							  if (!file_exists(trim($tmp,'/'))) {
									@mkdir($tmp, 0777);
							  }
						 }
					}
				  
					if (zip_entry_open($zip, $zip_entry, "r")) {
						 if (preg_match( '#^' . $dirFromZip . '.*#', dirname(zip_entry_name($zip_entry)))) {
							  if (substr($completeName,-1)!='/' and $fd = @fopen($completeName, 'w+')) {
									fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
									fclose($fd);
							  } else {
									if (!file_exists(trim($completeName,'/')))
										mkdir($completeName, 0777);
							  }
							  
							  zip_entry_close($zip_entry);
						 }
					}
			  }
			  
			  zip_close($zip);
		 }
		 
		 return rtrim($zipDir,'/');
	}

	static function transliteRuToLat($var,$len=0) {

		$var = strip_tags(html_entity_decode($var,ENT_QUOTES,'UTF-8'));
		$var = strtr($var,
			array(
				'<br />'=>'-',' '=>'-','_'=>'-',','=>'-','.'=>'-','+'=>'-',
				'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
				'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ы'=>'i','э'=>'e',
				'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'E','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R',
				'С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ы'=>'I','Э'=>'E',
				"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh",
				"щ"=>"shch", "ю"=>"yu", "я"=>"ya",
				"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH",
				"Щ"=>"SHCH", "Ю"=>"YU", "Я"=>"YA",
				"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye"
				,"Ь"=>"","Ъ"=>"","ь"=>"","ъ"=>""
				)
		);
		$var = preg_replace("/[^0-9A-Za-z\-]+/",'',$var);
		$var = strtr($var,array('-----'=>'-','----'=>'-','---'=>'-','--'=>'-'));
		if($len)
			$var = mb_substr($var,0,$len,'UTF-8');
		return trim($var,'-');
	}

	static function _http($link, $param = array()) {
		global $_CFG;
		//http://ru.php.net/curl_setopt
		if (isset($param['body'])) {
			exit('ERROR - body не поддерживается');
		}

		$default = array(
			'proxy' => false,
			'proxyList' => array(
				//array('11.11.11.11:8080','user:pass'),
				'82.200.55.142:3128',
			//'115.78.135.30:80',
			//'122.248.194.9:80',
			/**/
			),
			'HTTPHEADER' => array('Content-Type' => 'text/xml; encoding=utf-8'),
			'redirect' => false,
			'USERAGENT' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/'.rand(50,190).' (KHTML, like Gecko) Chrome/'.rand(9,16).'.0.8'.rand(1,99).'.121 Safari/535.2',
			'TIMEOUT' => 20,
			'REFERER' => false,
			'POST'=>false,
			'SSL'=>false,
			'FORBID'=>false, //TRUE для принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно.
		);
		$param = array_merge($default, $param);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link); //задаём url

		if (isset($param['COOKIE']))
			curl_setopt($ch, CURLOPT_COOKIE, $param['COOKIE']);

		if (isset($param['COOKIEFILE'])) // Считываем из фаила
			curl_setopt($ch, CURLOPT_COOKIEFILE, $param['COOKIEFILE']);

		if (isset($param['COOKIEJAR'])) // Записываем куки в фаил
			curl_setopt($ch, CURLOPT_COOKIEJAR, $param['COOKIEJAR']);

		curl_setopt($ch, CURLOPT_USERAGENT, $param['USERAGENT']); //подделываем юзер-агента

		if ($param['redirect']) {
			//переходить по редиректам, инициируемым сервером, пока не будет достигнуто CURLOPT_MAXREDIRS (если есть)
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		}

		if($param['REFERER']) {
			if($param['REFERER']===true)
				$param['REFERER'] = $link;
			curl_setopt($ch, CURLOPT_REFERER, $param['REFERER']); 
		}

		if ($param['SSL']) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
			curl_setopt($ch, CURLOPT_CAINFO, $param['SSL']);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}

		if ($param['HTTPHEADER']) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $param['HTTPHEADER']);
		}

		if ($param['FORBID']) {
			curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
		}

		if ($param['POST']) {
			if(is_array($param['POST'])) {
				$param['POST'] = http_build_query($param['POST']);
			}
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$param['POST']); 
		}
		//не включать заголовки ответа сервера в вывод
		curl_setopt($ch, CURLOPT_HEADER, false);
		//вернуть ответ сервера в виде строки
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $param['TIMEOUT']);

		// ПРОКСИ
		if ($param['proxy']) {
			$c = count($param['proxyList']) - 1;
			$prox = $param['proxyList'][rand(0, $c)];
			// указываем адрес
			$CURLOPT_PROXY = '';
			$CURLOPT_PROXYUSERPWD = '';
			if (is_array($prox)) {
				$CURLOPT_PROXY = $prox[0];
				$CURLOPT_PROXYUSERPWD = $prox[1];
			}else
				$CURLOPT_PROXY = $prox;
			curl_setopt($ch, CURLOPT_PROXY, $CURLOPT_PROXY);
			if($_CFG['wep']['debugmode']>1)
				echo ' * '.$CURLOPT_PROXY.' * ';
			if ($CURLOPT_PROXYUSERPWD) {
				// если необходимо предоставить имя пользователя и пароль
				//curl_setopt($ch, CURLOPT_PROXYUSERPWD,$CURLOPT_PROXYUSERPWD);
			}
		}
		//Функции обратного вызова
		//curl_setopt($ch, CURLOPT_WRITEFUNCTION,"progress_function");

		$text = curl_exec($ch);

		$PageInfo = curl_getinfo($ch);
		$err = '';
		if ($err = curl_errno($ch))
			$flag = false;
		elseif ($PageInfo['http_code'] == 200)
			$flag = true;
		else
			$flag = false;
		curl_close($ch);
		return array('text' => $text, 'info' => $PageInfo, 'err' => $err, 'flag' => $flag);
	}

	static function progress_function($ch, $str) {
		echo $str;
		return strlen($str);
	}

// END static class
}
