<?php

	$_tpl['modulstree']=$eval='';

	if($_CFG['info']['email'])
		$_tpl['contact'] = '<div class="ctd1">e-mail:</div>	<div class="ctd2"><a href="mailto:'.$_CFG['info']['email'].'">'.$_CFG['info']['email'].'</a></div>';
	if($_CFG['info']['icq'])
		$_tpl['contact'] .= '<div class="ctd1">icq:</div><div class="ctd2">'.$_CFG['info']['icq'].'</div>';
	if(isset($_CFG['info']['phone']) and $_CFG['info']['phone'])
		$_tpl['contact'] .= '<div class="ctd1">телефон:</div><div class="ctd2">'.$_CFG['info']['phone'].'</div>';
	$DATA = fXmlSysconf(); $_tpl['sysconf'] = $HTML->transformPHP($DATA,'sysconf');
	$DATA = fXmlModulslist(); $_tpl['modulslist']=$HTML->transformPHP($DATA,'modulslist');
	$_tpl['uname']='<a href="'.$_CFG['PATH']['wepname'].'/login.php?exit=ok" class="exit"><img src="'.$_CFG['PATH']['wepname'].'/cdesign/extjs/img/close48.gif" class="exit" alt="CLOSE"/></a><div class="uname">'.$_SESSION['user']['name'].' ['.$_SESSION['user']['gname'].']</div>';

	

	if(!$_GET['_modul'] or !(isset($_GET['_view']) or isset($_GET['_type']))) {
	//	$html = '<div style="position:absolute;top:50%;left:50%;"><div style="width:200px;height:100px;position:absolute;top:-50px;left:-100px;"><img src="'.$_tpl['design'].'img/login.gif" width="250" alt="LOGO"/></div></div>';
	}
	else {
		/*if(count($_GET)==2)
			$SQL->_iFlag = TRUE;*/
		if(!_new_class($_GET['_modul'],$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль '.$_GET['_modul'].' не установлен</div>';
		}
		else {

			if (isset($_GET['_type']) && $_GET['_type'] == 'sort' && isset($_POST['dropindex']) && isset($_POST['nodeid'])) {
				$dropindex = (int)$_POST['dropindex'];
				$nodeid = mysql_real_escape_string($_POST['nodeid']);
				$HTML->flag = false;

				$msg = '';

				$result = $SQL->execSQL('SELECT ordind, parent_id FROM `'.$MODUL->tablename.'` WHERE id="'.$nodeid.'"');
				if ($row = $result->fetch_array()) {
					$dragindex = $row['ordind'];

					if ($dragindex < $dropindex) {
						$result2 = $SQL->execSQL('UPDATE `'.$MODUL->tablename.'` SET ordind=ordind-1 WHERE parent_id="'.$row['parent_id'].'" AND ordind>'.$dragindex.' AND ordind<'.$dropindex.' AND id!="'.$nodeid.'"');
					}
					elseif ($dragindex > $dropindex) {
						$result2 = $SQL->execSQL('UPDATE `'.$MODUL->tablename.'` SET ordind=ordind+1 WHERE parent_id="'.$row['parent_id'].'" AND ordind<'.$dragindex.' AND ordind>'.$dropindex.' AND id!="'.$nodeid.'"');
					}
					else {
						$json = array('result' => 'failure');
						echo json_encode($json);
						return '';
					}

					$result3 = $SQL->execSQL('UPDATE `'.$MODUL->tablename.'` SET ordind="'.$dropindex.'" WHERE id="'.$nodeid.'"');

					if ($result->err == '') {
						$json_res = 'success';
					}
					else {
						$json_res = 'failure';
					}

					$json = array('result' => $json_res, 'msg' => $msg);

				}
				else {
					$json = array('result' => 'failure', 'msg' => 'Переданы неверные аргументы');
				}

				echo json_encode($json);

				return '';
			}

			if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

			if(static_main::_prmModul($_GET['_modul'],array(1,2))) {
				
				
				if (isset($_GET['node']))
				{
					if (!strstr($_GET['node'], 'xnode-'))
					{
						$_GET[$_GET['_modul'].'_id'] = $_GET['node'];
						$MODUL->messages_on_page = 1000;
					}
				}

				if($_GET['_view']=='list' || $_GET['_view'] == 'listcol') {
					$MODUL->_clp = '_view=list&amp;_modul='.$MODUL->_cl.'&amp;';
					$param = array('sbmtsave'=>1,'close'=>1);
//$tt = array();$summ = 0;for($j = 1; $j <= 5; $j++) { $tt[$j] = getmicrotime(); for($i = 1; $i <= 20; $i++) {
							$MODUL->setFilter(1);

							if (isset($_GET['sort_mode']) && $_GET['sort_mode'] == true) {
								$MODUL->messages_on_page = 1000;
							}

							list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

							if ($_GET['_type'] == 'tools') {
								$HTML->flag = false;
								
								if (isset($_POST['sbmt'])) {
									if ($DATA['formtools']['messages'][0]['name'] == 'ok') {
										$success = true;										
									}
									else {
										$success = false;
									}

									$result = array(
										'success' => $success,
										'msg' => $DATA['formtools']['messages'][0]['value'],
									);

									$json = json_encode($result);
								}
								else {

									unset($DATA['formtools']['form']['_*features*_']);
									unset($DATA['formtools']['form']['_info']);

									if (!empty($DATA['formtools']['form'])) {

										unset($DATA['formtools']['form']['_*features*_']);
										unset($DATA['formtools']['form']['_info']);

										$DATA['js_fields'] = array();
										foreach ($DATA['formtools']['form'] as $k => $r) {
											if (isset($r['valuelist']) && $r['type']=='checkbox') {
												foreach ($r['valuelist'] as $r2) {
													$field = array(
														'name' => $k.'[]',
														'caption' => $r2['#name#'],
														'type' => $r['type'],
														'value' => $r['value'],
														'inputValue' => $r2['#id#'],
													);
													$DATA['js_fields'][] = get_js_field($field);
												}
											}
											else {
												$field = array(
													'name' => $k,
													'caption' => $r['caption'],
													'type' => $r['type'],
													'value' => $r['value'],
													'valuelist' => $r['valuelist'],
												);
												$DATA['js_fields'][] = get_js_field($field);
											}
										}

									}

									$json = json_encode($DATA);
								}

								echo $json;

								return '';
							}


							if($_GET['_type']=="add" or $_GET['_type']=="edit") {
								if(isset($DATA['formcreat']) and isset($DATA['formcreat']['form']) and count($DATA['formcreat']['form'])) {
									$DATA['formcreat']['path'] = $HTML->path;
									
									$HTML->flag = false;

									if (count($_POST) and ($_POST['sbmt'] or $_POST['sbmt_save'])) {


										if (isset($DATA['formcreat']['messages']) && !empty($DATA['formcreat']['messages'])) {
											$msg = '';
											$success = true;
											foreach ($DATA['formcreat']['messages'] as $r) {
												$msg .= $r['value'] . '<br/>';
											
												if ($r['name'] == 'error') {
													$success = false;
												}
											}

											$result = array(
												'success' => $success,
												'msg' => $msg
											);

										}
										else {
											$result = array(
												'success' => true
											);
										}
										
										$json = json_encode($result);
									}
									else {
										$json = $HTML->transformPHP($DATA,'formcreat');
									}
																		
									echo $json;
								
									//$_tpl['onload'] .= 'var tmp = $(\'#form_'.$_GET['_modul'].'\').attr(\'action\');$(\'#form_'.$_GET['_modul'].'\').attr(\'action\',tmp.replace(\'index.php\',\'js.php\'));JSFR(\'#form_'.$_GET['_modul'].'\');';
								}
								elseif($flag==1){

									$HTML->flag = false;
									$msg = '';
									$success = true;
									foreach ($DATA['formcreat']['messages'] as $r) {
										$msg .= $r['value'] . '<br/>';

										if ($r['name'] == 'error') {
											$success = false;
										}
									}

									$result = array(
										'success' => $success,
										'msg' => $msg
									);
									$json = json_encode($result);
									echo $json;

//									end($HTML->path);prev($HTML->path);
//									$_SESSION['mess']=$DATA['formcreat']['messages'];
//									header('Location: '.$_CFG['_HREF']['BH'].str_replace("&amp;", "&", key($HTML->path)));
									die();
								}
								else {
									//$DATA['formcreat']['messages'] = $_SESSION['mess'];
									$DATA['formcreat']['path'] = $HTML->path;
									$html = $HTML->transformPHP($DATA,'formcreat');
									//$_tpl['onload'] .= 'var tmp = $(\'#form_'.$_GET['_modul'].'\').attr(\'action\');$(\'#form_'.$_GET['_modul'].'\').attr(\'action\',tmp.replace(\'index.php\',\'js.php\'));JSFR(\'#form_'.$_GET['_modul'].'\');';
								}
							} elseif($flag!=3) {
								$HTML->flag = false;

								$result = array(
									'success' => true
								);
								$json = json_encode($result);
								echo $json;
								
	//							end($HTML->path);
	//							$_SESSION['mess']=$DATA['superlist']['messages'];
	//							header('Location: '.$_CFG['_HREF']['BH'].str_replace("&amp;", "&", key($HTML->path)));
	//							die();
							} else {

								$HTML->flag = false;

								if(!$_SESSION['mess']) 
									$_SESSION['mess']= array();
								$DATA['superlist']['messages'] += $_SESSION['mess'];
								$DATA['superlist']['path'] = $HTML->path;
//								$html = $HTML->transformPHP($DATA,'superlist');

								if ($_GET['_view']=='listcol')
								{
									$DATA['superlist']['_view'] = 'listcol';
								}
	
								$json = $HTML->transformPHP($DATA,'superlist');									
								echo $json;
								$_SESSION['mess'] = array();
							}

//} $tt[$j] = getmicrotime()-$tt[$j]; $summ += $tt[$j]; } echo 'Среднее время = "'.($summ/5).'" ';echo $tt;

				}

				if($MODUL->ver!=$_CFG['modulprm'][$MODUL->_cl]['ver'])
					$_tpl['onload'] .= 'showHelp(\'.weptools.wepchecktable\',\'Версия модуля '.$MODUL->caption.'['.$MODUL->_cl.'] ('.$MODUL->ver.') отличается от версии ('.$_CFG['modulprm'][$MODUL->_cl]['ver'].') сконфигурированного для этого сайта. Обновите здесь поля таблицы.\',4000);$(\'.weptools.wepchecktable\').addClass(\'weptools_sel\');';

			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>';
		}
	}
	$_tpl['modulsforms'] = $html;

	$_tpl['styles']['style'] = 1;

	// extjs style
//	$_tpl['styles']['extjs/ext-all'] = 1;
	

	unset($_tpl['script']['jquery']);
//	$_tpl['script']['jquery.form'] = 1;
	$_tpl['script']['utils'] = 1;
	
	// extjs script
//	$_tpl['script']['extjs/adapter/ext/ext-base'] = 1;
//	$_tpl['script']['extjs/ext-all-debug'] = 1;
//	$_tpl['script']['extjs/src/ext-core/src/util/Observable'] = 1;
	
	//$_tpl['script']['script.localisation/jquery.localisation-min'] = 1;

//$_CFG['fileIncludeOption']['fancybox']



/* * *************************************************
 * возвращает extjs поле контейнера Ext.forms
 * принимает массив со следующими ключами:
 * - name
 * - caption
 * - type
 * - value
 * - valuelist
 * - multiple
 * ************************************** */

function get_js_field($data) {
	$type_info = array(
		'default' => array(
			'xtype' => 'textfield',
			'value_attr' => 'value'
		),
		'text' => array(
			'xtype' => 'textfield',
			'value_attr' => 'value'
		),
		'checkbox' => array(
			'xtype' => 'checkbox',
			'value_attr' => 'checked',
		),
		'list' => array(
			'xtype' => 'combo',
			'mode' => 'local',
			'typeAhead' => true,
			'triggerAction' => 'all',
			'value_attr' => 'value',
		),
		'multiple1' => array(
			'xtype' => 'multiselect',
			'mode' => 'local',
			'emptyText' => '',
			'value_attr' => 'value',
			'delimiter' => '|',
		),
		'multiple2' => array(
			'xtype' => 'itemselector',
			'mode' => 'local',
			'emptyText' => '',
			'value_attr' => 'value',
			'delimiter' => '|',
			'dataFields' => array('code', 'desc'),
			'fromData' => array(array('1', 'One'), array('2', 'Two'), array('3', 'Three'), array('4', 'Four')),
			'toData' => array(array('6', 'Six')),
			'msWidth' => 100,
			'msHeight' => 200,
			'valueField' => 'code',
			'displayField' => 'desc'
//				'multiselects' => array(
//					array(
//						'width' => 250,
//						'height' => 200,
//					),
//					array(
//						'width' => 250,
//						'height' => 200,
//					)
//				),
		),
		'info' => array(
			'xtype' => 'fieldset'
		),
		'submit' => array(
			'xtype' => 'hidden',
			'value_attr' => 'value'
		),
	);

	if (isset($type_info[$data['type']]))
		$type = $data['type'];
	else
		$type = 'default';

	if ($type == 'list' && isset($data['multiple'])) {
		if ($data['multiple'] == 2)
			$type = 'multiple1';
		elseif ($data['multiple'] == 1)
			$type = 'multiple1';

		$data['name'] .= '[]';
	}

	$field = $type_info[$type];
	$field[$field['value_attr']] = $data['value'];

	unset($field['value_attr']);

	$field['name'] = $data['name'];

	if ($field['xtype'] == 'combo') {
		$field['hiddenName'] = $data['name'];
	}
	if ($field['xtype'] == 'checkbox') {
		if (isset($data['inputValue'])) {
			$field['inputValue'] = $data['inputValue'];
		} else {
			$field['inputValue'] = 1;
		}
	}

	$field['fieldLabel'] = $data['caption'];

	if (isset($data['valuelist'])) {
		$field['store'] = array();
		foreach ($data['valuelist'] as $k => $r) {
			$field['store'][] = array($r['#id#'], $r['#name#']);
		}
	}

	if (isset($data['mask']['min'])) {
		$field['allowBlank'] = false;
	}

	if ($data['mask']['width'] > 200 && $field['xtype'] == 'textfield') {
		$field['xtype'] = 'textarea';
	}

	return $field;
}


