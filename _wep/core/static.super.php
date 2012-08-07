<?php

class static_super {

////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Универсальный обработчик вывода данных
	 * @param array $PARAM - параметры вывода данных и в нём формируется массив данных
	 * 	  $Ajax=0 - не скриптовая
	 * 		$_this->_cl - name текущего класса без _class
	 * 		$_this->_clp - построенный путь
	 * 		$param['xsl'] - шаблонизатор
	 * @param string $ftype
	 * @return array Данные для шаблонизатора
	 */
	static function super_inc(&$_this, $PARAM = array(), $ftype = '') {
		// Результат работы скрипта
		// $flag = 3; - вывод данных
		$flag = 1;
		if(!isset($PARAM['clause']))
			$PARAM['clause'] = array();
		
		// Задаем начальный массив данных
		if (!isset($PARAM['messages'])) {
			$PARAM['messages'] = array();
			$PARAM['path'] = array();
			$PARAM['_clp'] = array('_modul' => $_this->_cl);
			if(!isset($PARAM['firstpath']))
				$PARAM['firstpath'] = '';
			else {
				if (strpos($PARAM['firstpath'], '?') === false)
					$PARAM['firstpath'] .= '?';
				else {
					if (substr($PARAM['firstpath'], -1) != '&')
						$PARAM['firstpath'] .= '&';
				}
			}
		}

		// ID элемента
		if (isset($_GET[$_this->_cl . '_id']) and !is_array($_GET[$_this->_cl . '_id'])) {
			if (!$_this->mf_use_charid)
				$_this->id = (int) $_GET[$_this->_cl . '_id'];
			else {
				$rep = array('\'', '"', '\\', '/');
				$_this->id = str_replace($rep, '', $_GET[$_this->_cl . '_id']);
			}
		}

		$PARAM['path'][$_this->_cl] = array(
			'path' => $PARAM['_clp'],
			'name' => '<b>'.$_this->caption.'</b>'
		);

		if ($_this->id) {
			// Древо
			if ($_this->mf_istree) {
				$parent_id = $_this->id;
				$_this->tree_data = $first_data = $path = array();
				$listfields = 'id,' . $_this->mf_istree . ', ' . $_this->_listname . ' as name';
				if($_this->mf_actctrl)
					$listfields .= ','.$_this->mf_actctrl;
				$name = '<i>Список подуровня</i>';//'.$_this->caption.'
				while ($parent_id) {
					$clause = 'WHERE id="' . $parent_id . '"';
					$_this->data = $_this->_query($listfields, $clause, 'id');
					if (count($_this->data)) {
						if (!count($first_data))
							$first_data = $_this->data;
						$_this->tree_data += $_this->data;

						//********* Path ************
						$path[$_this->_cl . $parent_id] = array(
							'path' => $PARAM['_clp'] + array($_this->_cl . '_id' => $parent_id),
							'name' => $name
						);
						if ($_this->data[$parent_id][$_this->_listname])
							$name = preg_replace($_this->_CFG['_repl']['name'], '', $_this->data[$parent_id][$_this->_listname]);
						else
							$name = '№' . $parent_id;
						//BREAK
						if (!$_this->parent_id and $parent_id != $_this->id)
							$_this->parent_id = $parent_id;
						if (isset($PARAM['first_id']) and $PARAM['first_id'] and $parent_id == $PARAM['first_id'])
							break;


						$parent_id = $_this->data[$parent_id][$_this->mf_istree];

						// Задаем данные о номере странице
						$_this->_pa = $_this->_cl . $parent_id . '_pn';
						if (isset($_REQUEST[$_this->_pa]) && (int) $_REQUEST[$_this->_pa]) {
							$PARAM['_clp'][$_this->_pa] = (int) $_REQUEST[$_this->_pa];
							foreach ($path as &$tp) {
								$tp['path'][$_this->_pa] = $PARAM['_clp'][$_this->_pa];
							}
							unset($tp);
						}
					}
					else
						$parent_id = 0;
				}
				//$path[$_this->_cl . $parent_id]['name'] = $_this->caption.': '.$path[$_this->_cl . $parent_id]['name'];
				$_this->data = $first_data;
				if (isset($PARAM['first_id']) and $PARAM['first_id'] and !$parent_id)
					$_this->id = '';

				$PARAM['path'] += array_reverse($path); //Переворачиваем
				$PARAM['path'][$_this->_cl]['name'] .= ' : ' . $name;
			}
			else {
				$_this->data = $_this->_select();
				//********* Path ************
				if ($_this->data[$_this->id][$_this->_listname])
					$name = preg_replace($_this->_CFG['_repl']['name'], '', $_this->data[$_this->id][$_this->_listname]);
				else
					$name = '№' . $_this->id;
				$PARAM['path'][$_this->_cl]['name'] .= ': ' . $name;
			}
			$PARAM['_clp'][$_this->_cl . '_id'] = $_this->id;
			$_this->_pa = $_this->_cl . $_this->id . '_pn';
		}

		// Задаем данные о номере странице
		if (isset($_REQUEST[$_this->_pa]) && (int) $_REQUEST[$_this->_pa])
			$_this->_pn = $PARAM['_clp'][$_this->_pa] = (int) $_REQUEST[$_this->_pa];


		if ($_this->id and isset($_GET[$_this->_cl . '_ch']) and isset($_this->childs[$_GET[$_this->_cl . '_ch']])) {
			if (count($_this->data)) {
				if ($_this->mf_istree)
					array_pop($PARAM['path']);
				/*				 * ************************************* */
				/*				 * **** CHILD ************************** */
				/*				 * ************************************* */
				$PARAM['_clp'][$_this->_cl . '_ch'] = $_GET[$_this->_cl . '_ch'];
				list($PARAM, $flag) = $_this->childs[$_GET[$_this->_cl . '_ch']]->super_inc($PARAM, $ftype);
				/*				 * ************************************* */
				/*				 * **** CHILD ************************** */
				/*				 * ************************************* */
			}
		}
		else {
			global $_tpl;
			if ($_this->includeCSStoWEP and $_this->config['cssIncludeToWEP']) {
				if (!is_array($_this->config['cssIncludeToWEP']))
					$_this->config['cssIncludeToWEP'] = explode('|', $_this->config['cssIncludeToWEP']);
				if (count($_this->config['cssIncludeToWEP'])) {
					foreach ($_this->config['cssIncludeToWEP'] as $sr)
						$_tpl['styles'][$sr] = 1;
				}
			}
			if ($_this->includeJStoWEP and $_this->config['jsIncludeToWEP']) {
				if (!is_array($_this->config['jsIncludeToWEP']))
					$_this->config['jsIncludeToWEP'] = explode('|', $_this->config['jsIncludeToWEP']);
				if (count($_this->config['jsIncludeToWEP'])) {
					foreach ($_this->config['jsIncludeToWEP'] as $sr)
						$_tpl['script'][$sr] = 1;
				}
			}

			if (!isset($PARAM['filter']) or $PARAM['filter'] == true) {
				$PARAM['clause'] += $_this->_filter_clause();

				if (count($PARAM['clause']) and isset($_SESSION['filter'][$_this->_cl]) and count($_SESSION['filter'][$_this->_cl])) {
					$_tpl['onload'] .= 'showHelp(\'.button-filter\',\'Внимание! Включен фильтр.\',4000);$(\'.button-filter\').addClass(\'weptools_sel\');';
				}
			}

			if (is_null($_this->owner) and static_main::_prmModul($_this->_cl, array(14))) {
				if ($_this->ver != $_this->_CFG['modulprm'][$_this->_cl]['ver']) {
					//$_tpl['onload'] .= 'showHelp(\'.button-checktable\',\'Версия модуля '.$MODUL->caption.'['.$MODUL->_cl.'] ('.$MODUL->ver.') отличается от версии ('.$_this->_CFG['modulprm'][$MODUL->_cl]['ver'].') сконфигурированного для этого сайта. Обновите здесь поля таблицы.\',4000);$(\'.button-checktable\').addClass(\'weptools_sel\');';
					$PARAM['messages'][] = array('error', 'Версия модуля ' . $_this->caption . '[' . $_this->_cl . '] (' . $_this->ver . ') отличается от версии (' . $_this->_CFG['modulprm'][$_this->_cl]['ver'] . ') сконфигурированного для этого сайта. Обновите модуль.');
				}
			}

			// Удаление через форму
			if (isset($_POST['sbmt_del']) and $_this->id and $ftype=='edit') {
				$ftype = 'del';
			}

			if(!isset($PARAM['hide_topmenu']))
				$PARAM['topmenu'] = static_super::modulMenu($_this, $PARAM);

			if ($ftype == 'add') {
				if ($_this->mf_istree and $_this->id)
					$_this->parent_id = $_this->id;
				$_this->id = NULL;
				list($PARAM['formcreat'], $flag) = $_this->_UpdItemModul($PARAM);
				if ($flag == 1 and isset($_this->parent_id) and $_this->parent_id)
					$_this->id = $_this->parent_id;
				//else
				$tmp = $PARAM['_clp'] + array('_type' => 'add');
				if ($_this->parent_id)
					$tmp[$_this->_cl . '_id'] = $_this->parent_id;
				$PARAM['path']['add'] = array(
					'path' => $tmp,
					'name' => 'Добавление'
				);
			}
			elseif ($ftype == 'edit' && $_this->id) {
				if ($_this->mf_istree)
					array_pop($PARAM['path']);
				$PARAM['path']['edit'] = array(
					'path' => $PARAM['_clp'] + array($_this->_cl . '_id' => $_this->id, '_type' => 'edit'),
					'name' => 'Редактирование'
				);
				list($PARAM['formcreat'], $flag) = $_this->_UpdItemModul($PARAM);
				if ($flag == 1) {
					if (isset($_this->parent_id) and $_this->parent_id)
						$_this->id = $_this->parent_id;
					$PARAM['_clp'][$_this->_cl . '_id'] = $_this->id;
				}
			}
			elseif ($ftype == 'act' && $_this->id) {
				if ($_this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $_this->_Act(1, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'], $messages);
				if ($_this->mf_istree)
					$_this->id = $_this->data[$_this->id][$_this->mf_istree];
				else
					$_this->id = NULL;
			}
			elseif ($ftype == 'dis' && $_this->id) {
				if ($_this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $_this->_Act(0, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'], $messages);
				if ($_this->mf_istree)
					$_this->id = $_this->tree_data[$_this->id][$_this->mf_istree];
				else
					$_this->id = NULL;
			}
			elseif ($ftype == 'ordup' && $_this->id && $_this->mf_ordctrl) {
				if ($_this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $_this->_ORD(-1, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'], $messages);
				if ($_this->mf_istree)
					$_this->id = $_this->data[$_this->id][$_this->mf_istree];
				else
					$_this->id = NULL;
			}
			elseif ($ftype == 'orddown' && $_this->id && $_this->mf_ordctrl) {
				if ($_this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $_this->_ORD(1, $PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'], $messages);
				if ($_this->mf_istree)
					$_this->id = $_this->tree_data[$_this->id][$_this->mf_istree];
				else
					$_this->id = NULL;
			}
			elseif ($ftype == 'del' && $_this->id) {
				if ($_this->mf_istree)
					array_pop($PARAM['path']);
				list($messages, $flag) = $_this->_Del($PARAM);
				$PARAM['messages'] = array_merge($PARAM['messages'], $messages);
				if ($_this->mf_istree and isset($_this->tree_data[$_this->id]))
					$_this->id = $_this->tree_data[$_this->id][$_this->mf_istree];
				else
					$_this->id = NULL;
			}
			elseif ($ftype == 'tools') {
				if ($_this->mf_istree and $_this->id)
					$_this->parent_id = $_this->id;
				$PARAM['formtools'] = array();
				if (!isset($PARAM['topmenu'][$_REQUEST['_func']]))
					$PARAM['formtools']['messages'] = array(array('value' => 'Опция инструмента не найдена.', 'name' => 'error'));
				elseif (!method_exists($_this, 'tools' . $_REQUEST['_func']))
					$PARAM['formtools']['messages'] = array(array('value' => 'Функция инструмента не найдена.', 'name' => 'error'));
				else {
					eval('$PARAM[\'formtools\'] = $_this->tools' . $_REQUEST['_func'] . '();');
					if(isset($PARAM['formtools']['form']) and count($PARAM['formtools']['form']))
						$PARAM['formtools']['form']['_*features*_'] = array('name' => $_REQUEST['_func'], 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']), 'prevhref' => $_SERVER['HTTP_REFERER']);
				}
			}
			elseif ($ftype == 'static') {
				if ($_this->mf_istree and $_this->id)
					$_this->parent_id = $_this->id;
				$PARAM['static'] = array();
				if (!isset($PARAM['topmenu'][$_REQUEST['_func']]))
					$PARAM['messages'] = array(array('value' => 'Опция статики не найдена.', 'name' => 'error'));
				elseif (!method_exists($_this, 'static' . $_REQUEST['_func']))
					$PARAM['messages'] = array(array('value' => 'Функция статики не найдена.', 'name' => 'error'));
				else {
					eval('$PARAM[\'static\'] = $_this->static' . $_REQUEST['_func'] . '();');
				}
			} 
			else {
				if ($_this->mf_istree and $_this->id)
					$_this->parent_id = $_this->id;
				$flag = 3;
				$PARAM['data'] = $_this->_displayXML($PARAM);
				if (count($PARAM['data']['messages']))
					$PARAM['messages'] = array_merge($PARAM['messages'], $PARAM['data']['messages']);
				unset($PARAM['data']['messages']);
			}
			/* elseif ($_this->id) { //Просмотр данных
			  $flag = 3;
			  $PARAM['item'] = $_this->data;
			  } */

		}
		$PARAM['_cl'] = $_this->_cl;

		return array($PARAM, $flag);
	}

	/**
	 * вывод данных
	 * @param array $param - параметры вывода данных
	 * @return array
	 */
	static function _displayXML(&$_this, $param) {
		/** КОСТЫЛИ **/
		// Сделать механизм создания форм
		$_this->getFieldsForm();
		/**END  костыли**/

		$DATA = array('cl'=>$_this->_cl, 'caption'=>$_this->caption, 'messages'=>array());
		$listfields = array('count(t1.id) as cnt');
		$moder_clause = self::_moder_clause($_this, $param);
		if(is_array($moder_clause) and count($moder_clause))
			$clause =' t1 WHERE '.(implode(' and ',$moder_clause)); 
		else 
			$clause =' t1 WHERE t1.id';
		$_this->data = $_this->_query($listfields,$clause);

		//print($_this->SQL->query);

		$countfield = $_this->data[0]['cnt'];

		if(!$countfield) {
			$DATA['messages'][] = array('value'=>'Пусто','name'=>'alert');
			return $DATA;
		}

		// Функция постраничной навигации
		$DATA['pagenum'] = $_this->fPageNav2($countfield,$param);

		// Начальный отчет элементов на странице
		$DATA['pcnt'] = $DATA['pagenum']['start'];

		$climit= $DATA['pagenum']['start'].', '.$_this->messages_on_page;

		//Паратметры запроса
		// 0 - запрашиваемые поля
		// 1 - JOIN
		// 2 - WHERE
		$cls =array(
			0=>array('id'=>'t1.id'),
			1=>'',
			2=>array()
		);

		//Исключительные поля ()
		$arrno = array();

		// Родитель
		if($_this->owner and $_this->owner->id) {
			$arrno[$_this->owner_name] = 1;
			$cls[0][$_this->owner_name] = 't1.'.$_this->owner_name;
		}
		if($_this->mf_createrid)
			$cls[0][$_this->mf_createrid] = 't1.'.$_this->mf_createrid;
		if($_this->mf_istree)
			$cls[0][$_this->mf_istree] = 't1.'.$_this->mf_istree;
		if ($_this->mf_ordctrl)
			$cls[0][$_this->mf_ordctrl] = 't1.'.$_this->mf_ordctrl;
		if ($_this->mf_actctrl)
			$cls[0][$_this->mf_actctrl] = 't1.'.$_this->mf_actctrl;
		if ($_this->mf_timecr)
			$cls[0][$_this->mf_timecr] = 't1.'.$_this->mf_timecr;

		// Дети
		$t=2;
		if(count($_this->childs)) foreach($_this->childs as $ck=>$cn) {
			if($cn->tablename and $cn->owner_name and $cn->showinowner) {
				$arrno[$ck.'_cnt'] = 1;
				$cls[0][] = '(SELECT count(t'.$t.'.id) FROM `'.$cn->tablename.'` t'.$t.' WHERE t'.$t.'.'.$cn->owner_name.'=t1.id) as '.$ck.'_cnt';
				/*$temp = self::_moder_clause($cn, $param);// сырая и недоработана
				if(count($temp)) $cls[1] .= ' and '.str_replace('t1.','t'.$t.'.',implode(' and ',$temp));
				//if($cn->_join_check==TRUE)
					foreach($cn->fields_form as $cnk=>$cnr){
						if(is_array($cnr['listname']) and isset($cnr['listname']['join']) and $cnr['listname']['class']){
							$t++;
							//if (isset($cnr['listname']['include']))
							//	require_once($_this->_CFG['_PATH']['ext'].$cnr['listname']['include'].'.class.php');
							$cls[1] .=' AND t'.$t.'.id>0 RIGHT JOIN '.getTableNameOfClass($classname).' t'.$t.' ON t'.($t-1).'.'.$cnk.'=t'.$t.'.id ';
							if(isset($cnr['listname']['join']) and $cnr['listname']['join']!='')
								$cls[1] .= 'and '.str_replace('tx.','t'.$t.'.',$cnr['listname']['join']).' ';
						}
					}
				//if(isset($cn->fields['region_id'])) $cls[1] .=' and t'.$t.'.region_id='.$_SESSION['city'];
				*/
				$t++;
			}
		}
		// Древовидность
		if($_this->mf_istree) {
			$arrno[$_this->mf_istree] = 1;
			$arrno['istree_cnt']=1;
			//SET listfields
			$cls[0][$_this->mf_istree] = 't1.'.$_this->mf_istree;
			$cls[0][] = '(SELECT count(t'.$t.'.id) FROM `'.$_this->tablename.'` t'.$t.' WHERE t'.$t.'.'.$_this->mf_istree.'=t1.id) as istree_cnt';
			$t++;
		}
		//SСортировка
		if($_this->mf_ordctrl)
			$cls[0][$_this->mf_ordctrl] = 't1.'.$_this->mf_ordctrl;
		// Статуст активности
		if($_this->mf_actctrl) {
			$arrno[$_this->mf_actctrl] = 1;
			$cls[0][$_this->mf_actctrl] = 't1.'.$_this->mf_actctrl;
		}

		//DEFAULT SET SORT
		if($_this->ordfield!='') $order='t1.'.$_this->ordfield;
		else $order='t1.id';

		foreach($_this->fields_form as $k=>$r) {
			//SET listfields
			if(isset($_this->fields[$k]) or isset($_this->attaches[$k]) or isset($_this->memos[$k]))
				$cls[0][$k] = 't1.'.$k;

			if(isset($r['mask']['usercheck']) and !static_main::_prmGroupCheck($r['mask']['usercheck']))
				{$arrno[$k]=1; continue;}
			$tmpsort = false;

			if( (isset($r['mask']['fview']) and $r['mask']['fview']==1) or 
					(isset($r['mask']['disable']) and $r['mask']['disable']) or 
					($r['type']=='hidden') or 
					($r['type']=='info')
				)
				$arrno[$k]=1; 
			elseif(!isset($arrno[$k])) {
				//Списки
				if(isset($r['listname']) and is_array($r['listname']) and (isset($r['listname']['class']) or isset($r['listname']['tablename']))) {
					$tmpsort = true;
					$lsn = $r['listname'];
					if(!isset($lsn['nameField']) or !$lsn['nameField'])
						$lsn['nameField'] = 't'.$t.'.name';
					else 
						$lsn['nameField'] = str_replace('tx.','t'.$t.'.',$lsn['nameField']);
					//if (isset($lsn['include']))
					//	require_once($_this->_CFG['_PATH']['ext'].$lsn['include'].'.class.php');
					if(isset($r['multiple']) and $r['multiple'])
						$cls[0][] = 'group_concat('.$lsn['nameField'].' SEPARATOR " | ") as name_'.$k;
					else
						$cls[0][] = $lsn['nameField'].' as name_'.$k;

					if(!isset($lsn['join'])) 
						$cls[1] .= ' LEFT';

					$cls[1] .= ' JOIN `'.((isset($lsn['class']))?static_main::getTableNameOfClass($lsn['class']):$lsn['tablename']).'` t'.$t.' ON ';

					if(!isset($lsn['idField']) or !$lsn['idField']) 
						$lsn['idField'] = 't'.$t.'.id';
					else 
						$lsn['idField'] = str_replace('tx.','t'.$t.'.',$lsn['idField']);

					if(isset($lsn['join']) or isset($lsn['leftJoin'])) {
						if(!isset($lsn['idThis'])) 
							$lsn['idThis'] = $k;
						$cls[1] .= ' '.$lsn['idField'].'=t1.'.$lsn['idThis'].' '.str_replace('tx.','t'.$t.'.',($lsn['leftJoin'].$lsn['join']));
					}
					elseif(isset($r['multiple']) and $r['multiple'])
						$cls[1] .= 't1.'.$k.' LIKE concat("%|",'.$lsn['idField'].',"|%") ';
					else
						$cls[1] .= 't1.'.$k.'='.$lsn['idField'].' ';
					$t++;
				}elseif(isset($r['listname']) and !is_array($r['listname'])) {
					$_this->_checkList($r['listname']);
				}
				elseif(isset($r['concat']) and $r['concat']) {
					$cls[0][] = $r['concat'].' as '.$k;
					$r['mask']['sort'] = '';
				}

				$act=0;
				//if($_this->_prmSortField($k)) {
					if(isset($_GET['sort']) and $_GET['sort']==$k) $act=1;
					elseif(isset($_GET['dsort']) and $_GET['dsort']==$k) $act=2;
					elseif(strpos($order,'t1.'.$k)!==false) {
						if($order=='t1.'.$k)
							$act=1;
						else
							$act=2;
					}
					$temphref = $k.(($_this->id)?'&amp;'.$_this->_cl.'_id='.$_this->id:'');
				//}
				//else $temphref = '';
				$DATA['thitem'][$k] = array('value'=>$r['caption'],'href'=>$temphref,'sel'=>$act);
				if(isset($r['mask']['onetd']))
					$DATA['thitem'][$k]['onetd'] = $r['mask']['onetd'];
			}

			//if($_this->_prmSortField($k)) {
				if((isset($_GET['sort']) and $k==$_GET['sort']) or (isset($_GET['dsort']) and $k==$_GET['dsort'])) {
					if($tmpsort)
						$order = 'name_'.$k;
					elseif(isset($r['mask']['sort']) and is_string($r['mask']['sort']))
						$order = $r['mask']['sort'].$k;
					else
						$order = 't1.'.$k;
					if(isset($_GET['dsort']) and $k==$_GET['dsort'])
						$order .= ' DESC';
				}
			//}
		}

		/** Сборка запроса на вывод*/
		$cls[2] = self::_moder_clause($_this, $param);
		$cls[2] = array_merge($cls[2], $moder_clause);
		if(count($cls[2])>0) $cls[1] .=' WHERE '.implode(' AND ',$cls[2]);

		$listfields = $cls[0];
		$clause = 't1 '.$cls[1].' GROUP BY t1.id';
		if($order!='') $clause .= ' ORDER BY '.$order;
		$DATA['order'] = $order;
		//if(!$_this->mf_istree)
			$clause .= ' LIMIT '.$climit;
		$_this->data = $_this->_query($listfields,$clause,'id');
///print($_this->SQL->query);
		/** Обработка запроса*/
		if(count($_this->data)) {
			$temp = current($_this->data);
			if(isset($temp[$_this->mf_ordctrl]))
				$DATA['mf_ordctrl'] = $_this->mf_ordctrl;
			foreach($_this->data as $key=>$row) {
				if(!isset($DATA['pid']) and $_this->mf_istree and isset($row[$_this->mf_istree]))
					$DATA['pid'] = $row[$_this->mf_istree];
				$DATA['item'][$key] = self::_tr_attribute($_this, $row, $param);
				$DATA['item'][$key]['id'] = $row['id'];
				$DATA['item'][$key]['row'] = $row;
				//if($DATA['item'][$key]['act'])
				if($_this->mf_actctrl and isset($row[$_this->mf_actctrl]))
					$DATA['item'][$key]['active'] = $row[$_this->mf_actctrl];
				foreach($_this->fields_form as $k=>$r) {

					if(isset($arrno[$k])) {
						continue;// исключаем поля которые будут отображаться спецефично
					}
					$tditem = array('name'=>$k,'type'=>$r['type']);
					if($r['type']=='file') {
						if(isset($row['_ext_'.$k])) {
							if(isset($_this->_CFG['form']['flashFormat'][$row['_ext_'.$k]])) $tditem['fileType']='swf';
							elseif(isset($_this->_CFG['form']['imgFormat'][$row['_ext_'.$k]])) $tditem['fileType']='img';
						}
						else
							$tditem['fileType']='file';
					}

					if(isset($r['mask']['href']))
						$tditem['href'] = str_replace('{id}',$row['id'],$r['mask']['href']);
					elseif($r['type']=='attach')
						$tditem['href'] = $row[$k];
					if(isset($r['mask']['onetd']))
						$tditem['onetd'] = $r['mask']['onetd'];

					/** Отображаем "значение" если НЕ мультистолбец или если "значение" TRUE*/
					if(!isset($r['mask']['onetd']) or ($row[$k]!='0' and $row[$k]!='') or $r['type']=='list') {
						if(!isset($tditem['value'])) $tditem['value'] = '';
						if(isset($_this->memos[$k]))
							$tditem['value'] .= _substr(strip_tags(htmlspecialchars_decode(file_get_contents($row[$k]))),0,400);
						elseif($r['type']=='date') {
							$temp = '';
							if(!isset($r['mask']['format']))
								$r['mask']['format'] = 'Y-m-d H:i';							
							// Тип поля
							if($_this->fields[$k]['type']=='int'  and $row[$k]){
								$temp = date($r['mask']['format'],$row[$k]);
							}
							elseif($_this->fields[$k]['type']=='timestamp' and $row[$k]){
								$fs = explode(' ', $row[$k]);
								$f = explode('-', $fs[0]);
								$s = explode(':', $fs[1]);
								$temp = mktime($s[0], $s[1], $s[2], $f[1], $f[2], $f[0]);
								
								if($r['mask']['time'])
									$r['mask']['format'] = $r['mask']['format'].' '.$r['mask']['time'];
								
								$temp = date($r['mask']['format'],$temp);
							}
							
							$tditem['value'] .= $temp;
							
						}
						elseif($k=='mf_ipcreate')
							$tditem['value'] .= long2ip($row[$k]);
						elseif($r['type']=='checkbox')
							$tditem['value'] .= $_this->_CFG['enum']['yesno'][$row[$k]];
						elseif(isset($r['listname']) and is_array($r['listname'])) {//isset($row['name_'.$k])
							if(isset($r['multiple']) and $r['multiple']) 
								$tditem['value']= str_replace('|',', ',trim($row['name_'.$k],'|'));
							else
								$tditem['value'] = $row['name_'.$k];
						}
						elseif(isset($r['listname']) and $r['listname']) {// and !is_array($r['listname'])
							if(isset($r['multiple']) and $r['multiple']) 
								$row[$k]= explode('|',trim($row[$k],'|'));
							else
								$row[$k] = array($row[$k]);
							$temp=array();

							foreach($row[$k] as $er) {
								if(isset($_this->_CFG['enum_check'][$_this->_cl.'_'.$r['listname']][$er])) {
									$templist = $_this->_CFG['enum_check'][$_this->_cl.'_'.$r['listname']][$er];
									if(!is_array($templist)) {
										$temp[] = $templist;
									} elseif(isset($templist['#name#'])) {
										$temp[] = $templist['#name#'];
									} else
										$temp[] = '#unknown_data#';
								}elseif($er)
									$temp[] = '<span style="color:gray;">'.$er.'</span>';
							}
							$tditem['value'] = implode(', ',$temp);
						}
						elseif(isset($r['mask']['substr']) and $r['mask']['substr']>0)
							$tditem['value'] = _substr(strip_tags(htmlspecialchars_decode($row[$k])),0,$r['mask']['substr']);
						else//if($r['type']!='file')
							$tditem['value'] = $row[$k];

						if(isset($r['mask']['sformat'])) {
							if(method_exists($_this, $r['mask']['sformat']))
								eval('$tditem["value"] = $_this->'.$r['mask']['sformat'].'($tditem["value"]);');
							elseif(function_exists($r['mask']['sformat']))
								eval('$tditem["value"] = '.$r['mask']['sformat'].'($tditem["value"]);');
						}
					}
					$DATA['item'][$key]['tditem'][$k] = $tditem;
				}
				if(count($_this->childs) and !isset($param['hide_child'])) {
					foreach($_this->childs as $ck=>&$cn) {
						if($cn->showinowner and count($cn->fields_form))
							$DATA['item'][$key]['child'][$ck] = array('value'=>$cn->caption, 'cnt'=>$row[$ck.'_cnt']);
					}
					unset($cn);
				}
				if($_this->mf_istree and (!$_this->mf_treelevel or !isset($_this->tree_data) or (count($_this->tree_data)<($_this->mf_treelevel))))
					$DATA['item'][$key]['istree'] = array('value'=>$_this->caption, 'cnt'=>$row['istree_cnt']);
			}
		}

		return  $DATA;
	}


	/**
	 * задает атрибуты для super_inc
	 * @param array $row - данные
	 * @param array $param - данные параметра
	 * @return array
	 */
	static function _tr_attribute(&$_this, &$row, &$param) {
		$DATA = array();
		if ($_this->_prmModulEdit(array($row), $param))
			$DATA['edit'] = true;
		else
			$DATA['edit'] = false;
		if ($_this->_prmModulDel(array($row), $param))
			$DATA['del'] = true;
		else
			$DATA['del'] = false;
		if ($_this->_prmModulAct(array($row), $param))
			$DATA['act'] = true;
		else
			$DATA['act'] = false;
		return $DATA;
	}


	/**
	 * задает параметры запроса для super_inc
	 * @param array $param - данные параметра
	 * @return array
	 */
	static function _moder_clause(&$_this, &$param) {
		if (!isset($param['clause']) or !is_array($param['clause']))
			$param['clause'] = array();

		if ($_this->_prmModulShowCriteria($param))
			$param['clause']['t1.' . $_this->mf_createrid] = 't1.' . $_this->mf_createrid . '="' . $_SESSION['user']['id'] . '"';

		if ($_this->owner and $_this->owner->id)
			$param['clause']['t1.' . $_this->owner_name] = 't1.' . $_this->owner_name . '="' . $_this->owner->id . '"';

		if ($_this->mf_istree) {
			if ($_this->id)
				$param['clause']['t1.' . $_this->mf_istree] = 't1.' . $_this->mf_istree . '="' . $_this->id . '"';
			elseif (isset($param['first_id']))
				$param['clause']['t1.' . $_this->mf_istree] = 't1.id="' . $param['first_id'] . '"';
			elseif (isset($param['first_pid']))
				$param['clause']['t1.' . $_this->mf_istree] = 't1.' . $_this->mf_istree . '="' . $param['first_id'] . '"';
			elseif ($_this->mf_use_charid)
				$param['clause']['t1.' . $_this->mf_istree] = 't1.' . $_this->mf_istree . '=""';
			else
				$param['clause']['t1.' . $_this->mf_istree] = 't1.' . $_this->mf_istree . '=0';

			if ($_this->owner and $_this->owner->id and ($_this->id or (isset($param['first_pid']) and $param['first_pid']) ))
				unset($param['clause']['t1.' . $_this->owner_name]);
		}
		//if(isset($_this->fields['region_id']) and isset($_SESSION['city']))///////////////**********************
		//	$param['clause']['t1.region_id'] ='t1.region_id='.$_SESSION['city'];
		//if (isset($_GET['_type']) and $_GET['_type'] == 'deleted' and $_this->fields_form[$_this->mf_actctrl]['listname'] == $_this->mf_actctrl)
		//	$param['clause']['t1.' . $_this->mf_actctrl] = 't1.' . $_this->mf_actctrl . '=4';
		elseif (isset($_this->fields_form[$_this->mf_actctrl]['listname']) and $_this->fields_form[$_this->mf_actctrl]['listname'] == $_this->mf_actctrl)
			$param['clause']['t1.' . $_this->mf_actctrl] = 't1.' . $_this->mf_actctrl . '!=4';
		return $param['clause'];
	}


	/**
	* Менюшечка админки
	*/
	static function modulMenu(&$_this, $PARAM=array()) { //, $row=array()

		$topmenu = array();
		if(!isset($_this->data[$_this->id]))
			$_this->id = null;

		if ($_this->_prmModulAdd()) {
			$t = array('_type' => 'add');
			if ($_this->id)
				$t[$_this->_cl . '_id'] = $_this->id;
			$topmenu['add'] = array(
				'href' => $t,
				'caption' => 'Добавить - ' . $_this->caption,
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-add',
				//'is_popup' => true,
			);
		}

		if ($_this->mf_istree) {
			$t = array($_this->_cl . '_id' => '');
			//if (!$_this->mf_istree)
			//	$t['_type'] = 'edit';
			$topmenu['select_'.$_this->_cl ] = array(
				'href' => $t,
				'caption' => $_this->caption,
				'sel' => 0,
				'type' => 'select',
				'css' => '',
				'list' => $_this->_forlist($_this->_getCashedList('list'), 0, $_this->id),
			);
			//$topmenu['select_'.$_this->_cl ]['caption'] .= ' ('.count($topmenu['select_'.$_this->_cl ]['list']).')';
		}

		if ($_this->id) {
			//if(isset($_this->data[$_this->id]))
			$data = $_this->data;
			//else
			//	$data = $_this->_select();

			if($_this->_prmModulEdit($data))
				$topmenu['edit'] = array(
					'href' => array('_type' => 'edit', $_this->_cl . '_id' => $_this->id),
					'caption' => 'Редактировать - ' . $data[$_this->id]['name'],
					'sel' => 0,
					'type' => 'button',
					'css' => 'button-edit',
					//'is_popup' => true,
				);

			if($_this->mf_actctrl) {
				$topmenu['act'] = array(
					'href' => array('_type' => 'dis', $_this->_cl . '_id' => $_this->id),
					'caption' => 'Отключить - ' . $data[$_this->id]['name'],
					'sel' => 0,
					'type' => 'button',
					'css' => 'button-1',
					'onConfirm'=> true,
				);
				if(!$data[$_this->id][$_this->mf_actctrl]) {
					$topmenu['act']['href']['_type'] = 'act';
					$topmenu['act']['caption'] = 'Включить - ' . $data[$_this->id]['name'];
					$topmenu['act']['css'] = 'button-0';
				}
			}

			$topmenu['del'] = array(
				'href' => array('_type' => 'del', $_this->_cl . '_id' => $_this->id),
				'caption' => 'Удалить - ' . $data[$_this->id]['name'],
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-del',
				'onConfirm'=> true,
			);
		}
		$topmenu[] = array('type'=>'split');


		if (isset($_this->config_form) and count($_this->config_form) and static_main::_prmModul($_this->_cl, array(13)))
			$topmenu['Configmodul'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Configmodul'),
				'caption' => 'Настроика модуля',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-config',
				'is_popup' => true,
			);
		if ($_this->mf_indexing and static_main::_prmModul($_this->_cl, array(12)))
			$topmenu['Reindex'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Reindex'),
				'caption' => 'Переиндексация',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-reindex',
				'is_popup' => true,
			);
		if ($_this->cf_reinstall and static_main::_prmModul($_this->_cl, array(11)))
			$topmenu['Reinstall'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Reinstall'),
				'caption' => 'Переустановка',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-reinstall',
				'is_popup' => true,
			);
		if ($_this->cf_filter and $_this->_prmSortField()) {
			$topmenu['Formfilter'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Formfilter'),
				'caption' => 'Фильтр',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-filter',
				'is_popup' => true,
			);

		}
		if ($_this->mf_statistic) {
			$t = array('_type' => 'static', '_func' => 'Statsmodul');
			if ($_this->owner and $_this->owner->id)
				$t['_oid'] = $_this->owner->id;
			$topmenu['Statsmodul'] = array(
				'href' => $t,
				'caption' => 'Статистика',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-stats',
				'is_popup' => true,
			);
		}

		// Групповые операции
		$sg = 0;
		if (isset($_COOKIE['SuperGroup'][$_this->_cl])) {
			$sg += count($_COOKIE['SuperGroup'][$_this->_cl]);
		}
		$t = array('_type' => 'tools', '_func' => 'SuperGroup');
		$topmenu['SuperGroup'] = array(
			'href' => $t,
			'caption' => 'Групповая операция<i title="Кол-во выбранных элементов">'.$sg.'</i>',
			'title' => 'Групповая операция',
			'sel' => 0,
			'type' => 'button',
			'css' => 'button-SuperGroup',
			'style' => (!$sg ? 'display:none;' : ''),
			'is_popup' => true,
		);

		// TOOLS
		if(count($_this->cf_tools)) {
			foreach($_this->cf_tools as $r) {
				$topmenu[$r['func']] = array(
					'href' => array('_type' => 'tools', '_func' => $r['func']),
					'caption' => $r['name'],
					//'sel' => 0,
					'type' => 'button',
					'css' => $r['func'],
					'is_popup' => true,
					//'style' => (!$sg ? 'display:none;' : '')
				);
			}
		}


		/*if ($_this->owner and count($_this->owner->childs) and $_this->owner->id)
			foreach ($_this->owner->childs as $ck => &$cn) {
				if ($ck != $_this->_cl and $cn->_prmModulShow($PARAM)) { //count($cn->fields_form) and 
					$topmenu['ochild_' . $ck] = array(
						'href' => array($_this->_cl . '_id' => $_this->owner->id, $_this->_cl . '_ch' => $ck),
						'caption' => $cn->caption, 
						'sel' => 0,
						'list' => $_this->_forlist($_this->_getlist('list'), 0),
						'type' => 'select',
					);
				}
			}*/

		if (count($_this->childs) and $_this->id)
			foreach ($_this->childs as $ck => &$cn)
				if (count($cn->fields_form) and $ck != $_this->_cl and $cn->_prmModulShow($PARAM) ) {

					$topmenu[] = array('type'=>'split');

					$t = array(
						$_this->_cl . '_ch' => $ck, 
						$_this->_cl . '_id' => $_this->id, 
					);

					if ($cn->_prmModulAdd()) {
						$topmenu['add_' . $ck] = array(
							'href' => $t + array('_type' => 'add'),
							'caption' => 'Добавить ' . $cn->caption,
							'sel' => 0,
							'type' => 'button',
							'css' => 'button-add'
						);
					}


					$topmenu['child' . $ck] = array(
						'href' => $t + array('_type' => 'edit', $ck . '_id' => ''),
						'caption' => $cn->caption ,
						'sel' => 0,
						'list' => $cn->_forlist($cn->_getCashedList('list'), 0),
						'type' => 'select',
					);
					$topmenu['child' . $ck]['caption'] .= '(' . count($topmenu['child' . $ck]['list']) . ')';


				}
			
		return $topmenu;
	}

}
