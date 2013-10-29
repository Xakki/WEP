<?php
function tpl_form(&$data, $tabs = array())
{
	global $_CFG, $_tpl;
	plugForm();
	$texthtml = '';

	if (isset($data['_*features*_'])) {
		trigger_error('Ошибка. Старый формат данных. Атрибут _*features*_ не поддерживается.', E_USER_WARNING);
		return '';
	}

	// TABS
	$flagTabs = null;
	if (count($tabs)) {
		$i = 0;
		$tempTabs = array();
		$tabMenu = '<ul>';
		foreach ($tabs as $kS => $rS) {
			if (is_array($rS)) {
				$tabMenu .= '<li><a href="#weptabs' . $i . '">' . $kS . '</a></li>';
				$tempTabs[$i] = array_flip($rS);
				$i++;
			}
		}
		$tabMenu .= '</ul>';
		$flagTabs = 0;
	}

	$submitHtml = '';

	$tagStatus = false;
	foreach ($data as $k => $r) {
		if (!isset($r['type'])) continue;

		if (!is_null($flagTabs)) {

			if ($tagStatus and (isset($tempTabs[$flagTabs][$k]) or !isset($tempTabs[($flagTabs - 1)][$k]))) {
				$texthtml .= '</div>';
				$tagStatus = false;
			}

			if (!$tagStatus and isset($tempTabs[$flagTabs][$k])) {
				$texthtml .= $tabMenu . '<div id="weptabs' . $flagTabs . '">';
				$tagStatus = true;
				$flagTabs++;
				$tabMenu = '';
			}
		}

		if (!isset($r['value'])) $r['value'] = '';

		if ($r['type'] == 'hidden') {
			if (is_array($r['value']))
				$r['value'] = implode('|', $r['value']);
			$r['value'] = htmlentities($r['value'], ENT_QUOTES, $_CFG['wep']['charset']);
			$texthtml .= '<input type="' . $r['type'] . '" name="' . $k . '" value="' . $r['value'] . '" id="' . ((isset($r['id']) and $r['id']) ? $r['id'] : $k) . '"/>';
			continue;
		}
		/*if($r['type']=='submit' and is_array($r['value'])) {
			$submitHtml .= '<div class="form-submit">';
			foreach($r['value'] as $ksubmit=>$rsubmit)
				$submitHtml .= '<input type="'.$r['type'].'" name="'.$k.''.$ksubmit.'" value="'.$rsubmit.'" class="sbmt"/>';
			$submitHtml .= '</div>';
		}
		else*/
		if ($r['type'] == 'submit') // TODO сделать через списки
		{
			$submitHtml .= '<div class="div-tr form-submit">';

			if (is_array($r['value'])) {
				foreach ($r['value'] as $ki => $ri) {
					if (!is_array($ri)) // временный фикс
					$ri = array('#name#' => $ri);
					$submitHtml .= '<input type="' . $r['type'] . '" name="' . $ki . '" value="' . $ri['#name#'] . '"  class="sbmt" onclick="';
					if (isset($ri['confirm']) and $ri['confirm'])
						$submitHtml .= 'if(!confirm(\'' . $ri['confirm'] . '\')) return false;';
					if (isset($ri['onclick']))
						$submitHtml .= htmlentities($ri['onclick'], ENT_COMPAT, $_CFG['wep']['charset']);
					$submitHtml .= '"/>';
				}

			} else {
				$submitHtml .= '<input type="' . $r['type'] . '" name="' . $k . '" value="' . $r['value'] . '"  class="sbmt" onclick="';
				if (isset($r['confirm']) and $r['confirm'])
					$submitHtml .= 'if(!confirm(\'' . $r['confirm'] . '\')) return false;';
				if (isset($r['onclick']))
					$submitHtml .= htmlentities($r['onclick'], ENT_COMPAT, $_CFG['wep']['charset']);
				$submitHtml .= '"/>';
			}

			$submitHtml .= '</div>';
			continue;
		}

		$ID = $r['ID'] = str_replace(array('[', ']'), '_', $k);

		$texthtml .= '<div id="tr_' . $r['ID'] . '" style="' . (isset($r['style']) ? $r['style'] : '') . '" class="div-tr' .
			((isset($r['css']) and $r['css']) ? ' ' . $r['css'] : '') .
			((isset($r['mask']['min']) and $r['mask']['min']) ? ' required' : '') .
			((isset($r['readonly']) and $r['readonly']) ? ' readonly' : '') . '">';


		if ($r['type'] == 'infoinput') {
			$texthtml .= '<div class="infoinput"><input type="hidden" name="' . $k . '" value="' . $r['value'] . '"/>' . $r['caption'] . '</div>';
		} elseif ($r['type'] == 'info') {
			$texthtml .= '<div class="form-info">' . $r['caption'] . '</div>';
		} elseif ($r['type'] == 'html') {
			$texthtml .= '<div class="form-value">' . $r['value'] . '</div>';
		} elseif ($r['type'] == 'cf_fields') {
			include_once(dirname(__FILE__) . '/cffields.php');
			$texthtml .= '<div class="form-value">' . tpl_cffields($k, $r) . '</div>';
		} elseif ($r['type'] == 'map') {
			setScript('yamap');
			$texthtml .= '<div class="mapselect' . ($r['value'] ? ' setvalue' : '') . '" onclick="positionOnMap()">' . ($r['value'] ? $r['value'] : $r['caption']) . '</div>
                <input type="hidden" name="' . $k . '" id="field_' . $k . '" value="' . $r['value'] . '"/>';
		} else {
			$attribute = '';

			$CAPTION = $r['caption'];
			if (isset($r['mask']['min']) and $r['mask']['min']) {
				$CAPTION .= '<span class="form-requere">*</span>';
				if ($r['type'] != 'ckedit' and !($r['type'] == 'password' and isset($r['mask']['password']) and $r['mask']['password'] == 're')) // в CKEDITORE глюк из за этого
				$attribute .= ' required="required"';
			} elseif (isset($r['mask']['min2']) and $r['mask']['min2']) {
				$CAPTION .= '<span  class="form-requere" data-text="' . $r['mask']['min2'] . '">**</span>';
			}

			//if($r['type']=='ckedit' and static_main::_prmUserCheck(1))
			//	$CAPTION .= '<input type="checkbox" onchange="SetWysiwyg(this)" name="'.$k.'_ckedit" style="width:13px;vertical-align: bottom;margin: 0 0 0 5px;"/>';

			if ($r['type'] != 'checkbox') {
				$texthtml .= '<label class="form-caption">' . $CAPTION . '</label>';
			}

			if (isset($r['readonly']) and $r['readonly'])
				$attribute .= ' readonly="readonly" class="ronly"';
			else
				$r['readonly'] = false;

			if (isset($r['disabled']) and $r['disabled'])
				$attribute .= ' disabled="disabled" class="ronly"';

			if (isset($r['maxlength']) and $r['maxlength'])
				$attribute .= ' maxlength="' . $r['maxlength'] . '"';

			if ($r['type'] == 'file') {
				if (!isset($r['onchange']))
					$r['onchange'] = '';
				$r['onchange'] .= 'input_file(this)';
			}
			if (isset($r['onchange']) and $r['onchange'])
				$attribute .= ' onchange="' . htmlentities($r['onchange'], ENT_COMPAT, $_CFG['wep']['charset']) . '"';

			if (isset($r['placeholder']) and $r['placeholder'])
				$attribute .= ' placeholder="' . $r['placeholder'] . '"';

			if (isset($r['error']) and is_array($r['error']) and count($r['error']))
				$texthtml .= '<div class="caption_error">[' . implode(' ', $r['error']) . ']</div>';

			if ($r['type'] == 'textarea') {

				$texthtml .= '<div class="form-value textarea"><textarea name="' . $k . '" onkeyup="textareaChange(this)" rows="10" cols="80" ' . $attribute . '>' . @htmlspecialchars($r['value'], ENT_QUOTES, $_CFG['wep']['charset']) . '</textarea></div>';
			} elseif ($r['type'] == 'ckedit') {
				$_tpl['script'][$_CFG['_HREF']['vendors'] . 'ckeditor/ckeditor.js'] = 1;
				// http://docs.ckeditor.com/#!/api/CKEDITOR.config
				//http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html
				$ckedit = $r['paramedit'];
				// if(!isset($ckedit['skin']))
				// 	$ckedit['skin']='\'kama\'';
				if (!isset($ckedit['width']))
					$ckedit['width'] = '\'100%\'';
				if (!isset($ckedit['height']))
					$ckedit['height'] = '450';
				$ckedit['toolbarCanCollapse '] = 'true';
				if (!isset($ckedit['baseHref']))
					$ckedit['baseHref'] = '\'' . MY_BH . '\'';
				if (isset($ckedit['toolbar'])) {
					if (isset($_CFG['ckedit']['toolbar'][$ckedit['toolbar']]))
						$ckedit['toolbar'] = $_CFG['ckedit']['toolbar'][$ckedit['toolbar']];
					else
						$ckedit['toolbar'] = '\'' . $ckedit['toolbar'] . '\'';
				} else
					$ckedit['toolbar'] = $_CFG['ckedit']['toolbar']['Full'];
				if (!isset($ckedit['uiColor']))
					$ckedit['uiColor'] = '\'#9AB8F3\'';
				if (!isset($ckedit['language']))
					$ckedit['language'] = '\'ru\'';
				if (!isset($ckedit['enterMode']))
					$ckedit['enterMode'] = 'CKEDITOR.ENTER_BR';
				if (!isset($ckedit['shiftEnterMode']))
					$ckedit['shiftEnterMode'] = 'CKEDITOR.ENTER_P';
				if (!isset($ckedit['contentsCss']))
					$ckedit['contentsCss'] = '"/_design/default/style/main.css"';
				$ckedit['autoUpdateElement'] = 'true';
				$ckedit['pasteFromWordPromptCleanup'] = 'true';
				$ckedit['allowedContent'] = 'true';
				//unset($ckedit['extraPlugins']);

				// if(typeof CKEDITOR.instances.id_'.$ID.' == \'object\')
				// {
				// 	CKEDITOR.instances.id_'.$ID.'.destroy(true);
				// }
				$fckscript = 'function cke_' . $ID . '() {
						CKEDITOR.replace( \'id_' . $ID . '\',
							{';

				/**
				 * FILE brauser
				 */
				// KCFinder
				if (isset($ckedit['CKFinder'])) {

					$CKFinder = '/' . $_CFG['PATH']['vendors'] . 'ckfinder/';
					$ckedit['filebrowserBrowseUrl'] = '"' . $CKFinder . 'ckfinder.html"';
					$ckedit['filebrowserImageBrowseUrl'] = '"' . $CKFinder . 'ckfinder.html?type=Images"';
					$ckedit['filebrowserFlashBrowseUrl'] = '"' . $CKFinder . 'ckfinder.html?type=Flash"';
					$ckedit['filebrowserUploadUrl'] = '"' . $CKFinder . 'core/connector/php/connector.php?command=QuickUpload&type=Files"';
					$ckedit['filebrowserImageUploadUrl'] = '"' . $CKFinder . 'core/connector/php/connector.php?command=QuickUpload&type=Images"';
					$ckedit['filebrowserFlashUploadUrl'] = '"' . $CKFinder . 'core/connector/php/connector.php?command=QuickUpload&type=Flash"';

					// $kcfinder = '/'.$_CFG['PATH']['vendors'].'kcfinder-wep/';
					// $ckedit['filebrowserBrowseUrl'] = '"'.$kcfinder.'browse.php?type=files"';
					// $ckedit['filebrowserImageBrowseUrl'] = '"'.$kcfinder.'browse.php?type=images"';
					// $ckedit['filebrowserFlashBrowseUrl'] = '"'.$kcfinder.'browse.php?type=flash"';
					// $ckedit['filebrowserUploadUrl'] = '"'.$kcfinder.'upload.php?type=files"';
					// $ckedit['filebrowserImageUploadUrl'] = '"'.$kcfinder.'upload.php?type=images"';
					// $ckedit['filebrowserFlashUploadUrl'] = '"'.$kcfinder.'upload.php?type=flash"';

					// if(isset($ckedit['CKFinder']['allowedExtensions']) and $_SESSION)
					// 	$_SESSION['wswg']['AE'] = $ckedit['CKFinder']['allowedExtensions'];
				}

				foreach ($ckedit as $kc => $rc) {
					if (!is_array($rc))
						$fckscript .= $kc . ' : ' . $rc . ',';
				}
				$fckscript .= '\'temp\' : \'temp\' });';

				// if(isset($ckedit['CKFinder'])) {
				// 	$_tpl['script'][$_CFG['PATH']['vendors'].'ckfinder/ckfinder.js'] = 1;

				// 	$fckscript = ' function ckf_'.$ID.'() { CKFinder.setupCKEditor(editor_'.$ID.',\'/'.$_CFG['PATH']['WSWG'].'ckfinder/\');} '.$fckscript;
				// 	$fckscript .= ' ckf_'.$ID.'();';

				// 	if(isset($ckedit['CKFinder']['allowedExtensions']) and $_SESSION)
				// 		$_SESSION['wswg']['AE'] = $ckedit['CKFinder']['allowedExtensions'];
				// }


				$fckscript .= '}';
				//if(!isset($fields[$ID.'_ckedit']['value']) or $fields[$ID.'_ckedit']['value']=='' or $fields[$ID.'_ckedit']['value']=='1')
				$_tpl['onload'] .= $fckscript . ' cke_' . $ID . '();';

				$texthtml .= '<div class="form-value ckedit-value"><textarea id="id_' . $ID . '" name="' . $k . '" rows="10" cols="80" ' . $attribute . '>' . @htmlspecialchars((string)$r['value'], ENT_QUOTES, $_CFG['wep']['charset']) . '</textarea></div>';
			} elseif ($r['type'] == 'radio') {
				$texthtml .= '<div class="form-value radiolist">';
				if (!count($r['valuelist']))
					$texthtml .= '<font color="red">Нет элементов для отображения</font>';
				else {
					foreach ($r['valuelist'] as $row) {
						$texthtml .= '<label class="' . $row['#css#'] . '"><input type="' . $r['type'] . '" name="' . $k . '" value="' . $row['#id#'] . '" class="radio" ' . $attribute;
						if ($row['#sel#'])
							$texthtml .= ' checked="checked"';
						$texthtml .= '/>' . $row['#name#'] . '</label>';
					}
				}
				$texthtml .= '</div>';
			} elseif ($r['type'] == 'checkbox') {

				if (!isset($r['valuelist']) or !count($r['valuelist'])) {
					if ($r['value'])
						$attribute .= ' checked="checked"';
					$texthtml .= '<label class="form-value checkbox-value">
						<input type="' . $r['type'] . '" name="' . $k . '" value="1" ' . $attribute . '/>
						<label class="form-caption">' . $CAPTION . '</label>
					</label>';
				} else {
					$texthtml .= '<label class="form-caption">' . $CAPTION . '</label>
						<div class="form-value checkbox-value checkbox-valuelist">';
					foreach ($r['valuelist'] as $kv => $rv) {
						$sel = false;
						$readonly = false;
						if (is_array($rv) and isset($rv['#id#'])) {
							$id = $rv['#id#'];
							$name = $rv['#name#'];
							if ($rv['#readonly#'])
								$readonly = true;
							if (isset($rv['#sel#']) and $rv['#sel#'])
								$sel = true;
						} else {
							$id = $kv;
							$name = $rv;
							if (isset($r['value'])) {
								if (is_array($r['value'])) {
									if (isset($r['value'][$id]))
										$sel = true;
								} elseif ($r['value'] == $id)
									$sel = true;
							}
						}
						$texthtml .= '<label class="boxtitle"><input type="' . $r['type'] . '" name="' . $k . '[' . $id . ']" value="' . $id . '" class="radio" ' . $attribute;
						if ($sel)
							$texthtml .= ' checked="checked"';
						if ($readonly)
							$texthtml .= ' readonly="readonly"';
						$texthtml .= '/>' . $name . '</label>';
					}
					$texthtml .= '</div>';
				}
				// end checkbox
			} elseif ($r['type'] == 'ajaxlist' and isset($r['multiple']) and $r['multiple']) {
				global $_tpl;

				if (!is_array($r['value']))
					$r['value'] = explode('|', trim($r['value'], '|'));
				if (!is_array($r['value_2']))
					$r['value_2'] = array($r['value_2']);
				$serl = serialize($r['listname']);
				$max = (isset($r['mask']['maxarr']) ? $r['mask']['maxarr'] : 5);
				for ($i = 0; $i < $max; $i++) {
					if (isset($r['value'][$i])) {
						$value = $r['value'][$i];
					} else {
						$value = '';
						$_tpl['onload'] .= ' jQuery(\'#tr_' . $ID . ' div.ajaxlist\').eq(' . $i . ').hide(); ';
					}
					if (isset($r['value_2'][$i])) $value_2 = strip_tags($r['value_2'][$i]);
					// TODO : Придумать форматированный вывод
					else $value_2 = '';
					$r['csscheck'] = ($value_2 ? 'accept' : 'reject');
					$texthtml .= '<div class="form-value ajaxlist ' . $r['csscheck'] . '">
						<input type="text" name="' . $ID . '_2[' . $i . ']" id="' . $ID . '_2_' . $i . '" value="' . $value_2 . '" placeholder="' . $r['placeholder'] . '" autocomplete="off"/>
						<div id="ajaxlist_' . $ID . '_' . $i . '" style="display:none;">не найдено</div>
						<input type="hidden" name="' . $ID . '[' . $i . ']" id="' . $ID . '_' . $i . '" value="' . $value . '" ' . $attribute . '/>
					</div>';
					$_tpl['onload'] .= 'setEventAjaxList("#' . $ID . '_2_' . $i . '", "#' . $ID . '_' . $i . '","#ajaxlist_' . $ID . '_' . $i . '");';
//                  onfocus="show_hide_label(this,\''.$ID.'\',1,\''.$i.'\')"
//					onblur="show_hide_label(this,\''.$ID.'\',0,\''.$i.'\')"
//					onkeyup="return ajaxlistOnKey(event,this,\''.$ID.'\',\''.$i.'\')"
				}
				$texthtml .= '<input type="hidden" id="hsh_' . $k . '" value="' . md5($serl . $_CFG['wep']['md5']) . '"/>
					<input type="hidden" id="srlz_' . $k . '" value="' . @htmlspecialchars($serl, ENT_QUOTES, $_CFG['wep']['charset']) . '"/>';
				if (!isset($r['comment']))
					$r['comment'] = '';
				$r['comment'] .= '<div class="ajaxmultiple" onclick="jQuery(\'#tr_' . $ID . ' div.ajaxlist:hidden\').eq(0).show(); if (jQuery(\'#tr_' . $ID . ' div.ajaxlist:hidden\').size() == 0) jQuery(this).hide();">Добавить ' . $r['caption'] . '</div>';
			} elseif ($r['type'] == 'ajaxlist') {
				$defaultList = '';
				if (isset($r['defaultList'])) {
					$defaultList = '<div id="ajaxlist_' . $ID . '_default" style="display:none;">';
					foreach ($r['defaultList'] as $dlK => $dlR) {
						$defaultList .= '<label data-id="' . $dlK . '">' . $dlR . '</label>';
					}
					$defaultList .= '</div>';
				}
				$r['csscheck'] = ($r['value_2'] ? '' : 'reject');
				$serl = serialize($r['listname']);
				$texthtml .= '<div class="form-value ajaxlist ' . $r['csscheck'] . '">
					<input type="text" name="' . $k . '_2" id="' . $ID . '_2" value="' . $r['value_2'] . '" placeholder="' . $r['placeholder'] . '" autocomplete="off"/>
					<div id="ajaxlist_' . $ID . '" style="display:none;" val="' . $r['value_2'] . '">не найдено</div>
					' . $defaultList . '
					<input type="hidden" name="' . $k . '" id="' . $ID . '" value="' . $r['value'] . '" ' . $attribute . '/>
				</div>
				<input type="hidden" id="hsh_' . $k . '" value="' . md5($serl . $_CFG['wep']['md5']) . '"/>
				<input type="hidden" id="srlz_' . $k . '" value="' . @htmlspecialchars($serl, ENT_QUOTES, $_CFG['wep']['charset']) . '"/>';
				$_tpl['onload'] .= 'setEventAjaxList("#' . $ID . '_2", "#' . $ID . '", "#ajaxlist_' . $ID . '");';
			} elseif ($r['type'] == 'list' and !$r['readonly']) {
				include_once(dirname(__FILE__) . '/formSelect.php');

				$texthtml .= '<div class="form-value">';
				if (isset($r['multiple']) && $r['multiple']) {
					if (!isset($r['mask']['size'])) $r['mask']['size'] = 10;
					if (!isset($r['mask']['maxarr'])) $r['mask']['maxarr'] = 0;
					if (!isset($r['mask']['minarr'])) $r['mask']['minarr'] = 0;
				}

				if (isset($r['multiple']) and $r['multiple'] == FORM_MULTIPLE_JQUERY) {
					$texthtml .= '<select multiple="multiple" name="' . $k . '[]" class="multiple" size="' . $r['mask']['size'] . '" ' . $attribute;
					$texthtml .= '>' . tpl_formSelect($r['valuelist'], $r['value']) . '</select>';
					plugJQueryUI_multiselect();
				} elseif (isset($r['multiple']) and $r['multiple'] == FORM_MULTIPLE_KEY) {
					$texthtml .= helper_form_multiple($k, $r, $attribute);
				} elseif (isset($r['multiple']) and $r['multiple']) {

					$texthtml .= '<select multiple="multiple" name="' . $k . '[]" class="small" size="' . $r['mask']['size'] . '" ' . $attribute;
					$texthtml .= '>' . tpl_formSelect($r['valuelist'], $r['value']) . '</select>';
				} /*
				НА  стадии разработки
				* решить что делать с сабмитом,
				* списки для всех?
				*/
				elseif (isset($r['viewType']) and $r['viewType'] == 'button') {
					foreach ($r['valuelist'] as $ik => $ir) {
						if (!isset($ir['css']))
							$ir['css'] = '';
						if (isset($r['#sel#']))
							$ir['css'] .= ' selected';
						$attribute = ' type="submit" class="' . $ir['css'] . '"';
						if (isset($ir['#id#']))
							$attribute .= ' value="' . $ir['#id#'] . '"';
						if (isset($ir['#name#']))
							$attribute .= ' title="' . $ir['#name#'] . '"';

						$texthtml .= '<button name="' . $k . '" ' . $attribute . '/>';
					}
				} else {
					$texthtml .= '<select name="' . $k . '" ' . $attribute;
					$texthtml .= '>' . tpl_formSelect($r['valuelist'], $r['value']) . '</select>';
				}
				$texthtml .= '</div>';
			} elseif ($r['type'] == 'date' and $r['readonly']) {
				$temp = '';
				if ($r['fields_type'] == 'int' and $r['value']) {
					if (isset($r['mask']['format']) and $r['mask']['format']) {
						$temp = date($r['mask']['format'], $r['value']);
					} else {
						$temp = date('Y-m-d H:i:s', $r['value']);
					}
				} elseif ($r['fields_type'] == 'timestamp' and $r['value']) {
					$temp = $r['value']; //2007-09-11 10:16:15
				} else {
					$temp = date("Y-m-d H:i:s");
				}
				$texthtml .= '<div class="form-value"><input type="text" name="' . $k . '" value="' . $temp . '" ' . $attribute . '/></div>';
			} elseif ($r['type'] == 'date' and !$r['readonly']) {
				$texthtml .= '<div class="form-value dateinput">';
				$temp = '';
				if (isset($r['mask']['view']) and $r['mask']['view'] == 'split') {
					include_once(dirname(__FILE__) . '/formSelect.php');
					// Тип поля
					if (!is_array($r['value'])) {
						if ($r['fields_type'] == 'int' and $r['value']) {
							$temp = explode('-', date('Y-m-d-H-i-s', $r['value']));
						} elseif ($r['fields_type'] == 'timestamp' and $r['value'] and is_string($r['value'])) {
							$temp = sscanf($r['value'], "%d-%d-%d %d:%d:%d"); //2007-09-11 10:16:15
						} else {
							$temp = array_fill(0, 6, 0);
							//$temp = array(date('Y'),date('n'),date('d'),date('H'));
						}
					} else
						$temp = $r['value'];
					$r['value'] = array();

					// формат для даты
					preg_match_all('/[A-Za-z]/', $r['mask']['format'], $matches);
					$format = $matches[0];
					foreach ($format as $item_date) {
						// год
						if ($item_date == 'Y' || $item_date == 'y') {
							$r['value']['year'] = array('name' => static_main::m('year_name'), 'css' => 'year', 'value' => $temp[0]); // ГОД
							$temp[0] = (int)$temp[0];
							$r['value']['year']['item'][0] = array('#id#' => 0, '#name#' => '--');

							//значения по умолчанию
							if (!isset($r['mask']['year_back'])) $r['mask']['year_back'] = -2;
							if (!isset($r['mask']['year_up'])) $r['mask']['year_up'] = 3;
							for ($i = ((int)date('Y') + ($r['mask']['year_back'])); $i <= ((int)date('Y') + ($r['mask']['year_up'])); $i++)
								$r['value']['year']['item'][$i] = array('#id#' => $i, '#name#' => $i);
						}
						// месяц
						if ($item_date == 'm' || $item_date == 'n' || $item_date == 'M' || $item_date == 'F') {
							$r['value']['month'] = array('name' => static_main::m('month_name'), 'css' => 'month', 'value' => (int)$temp[1]); // Месяц
							$r['value']['month']['item'][0] = array('#id#' => 0, '#name#' => '--');
							foreach (static_main::m('month') as $kr => $td) {
								$kr = (int)$kr;
								$r['value']['month']['item'][$kr] = array('#id#' => $kr, '#name#' => $td);
							}
						}
						// день
						if ($item_date == 'd' || $item_date == 'j') {
							$r['value']['day'] = array('name' => static_main::m('day_name'), 'css' => 'day', 'value' => (int)$temp[2]); // День
							$r['value']['day']['item'][0] = array('#id#' => 0, '#name#' => '--');
							for ($i = 1; $i <= 31; $i++)
								$r['value']['day']['item'][$i] = array('#id#' => $i, '#name#' => $i);
						}
						// час
						if ($item_date == 'G' || $item_date == 'g' || $item_date == 'H' || $item_date == 'h') {
							$r['value']['hour'] = array('name' => static_main::m('hour_name'), 'css' => 'hour', 'value' => $temp[3]); // Час
							for ($i = 0; $i <= 23; $i++)
								$r['value']['hour']['item'][$i] = array('#id#' => $i, '#name#' => $i);
						}
						// минуты
						if ($item_date == 'i') {
							$r['value']['minute'] = array('name' => static_main::m('minute_name'), 'css' => 'minute', 'value' => $temp[4]); // Minute
							for ($i = 1; $i <= 60; $i++)
								$r['value']['minute']['item'][$i] = array('#id#' => $i, '#name#' => $i);
						}
						// секунды
						if ($item_date == 's') {
							$r['value']['sec'] = array('name' => static_main::m('sec_name'), 'css' => 'sec', 'value' => $temp[5]);
							for ($i = 1; $i <= 60; $i++)
								$r['value']['sec']['item'][$i] = array('#id#' => $i, '#name#' => $i);
						}
					}

					foreach ($r['value'] as $row) {
						$texthtml .= '<div class="dateselect ' . $row['css'] . '"><span class="name">' . $row['name'] . '</span><select name="' . $k . '[]" ' . $attribute . '>' . tpl_formSelect($row['item'], $row['value']) . '</select></div>';
					}
				} else {
					$time = NULL;
					// Тип поля
					if ($r['value']) {
						if ($r['fields_type'] == 'int') {
							$time = $r['value'];
							$temp = date($r['mask']['format'], $r['value']);
						} else { //$r['fields_type'] =='timestamp'
							$fs = explode(' ', $r['value']);
							$f = explode('-', $fs[0]);
							$s = explode(':', $fs[1]);
							$time = $temp = mktime($s[0], $s[1], $s[2], $f[1], $f[2], $f[0]);

							if ($r['mask']['time'])
								$r['mask']['format'] = $r['mask']['format'] . ' ' . $r['mask']['time'];

							$temp = date($r['mask']['format'], $temp);
						}
					}

					// текстовый формат ввода данных
					if (!isset($r['mask']['view']) or isset($r['mask']['datepicker'])) {
						//if(isset($r['mask']['datepicker'])) {
						if (!isset($r['mask']['datepicker']))
							$r['mask']['datepicker'] = array();
						elseif (!is_array($r['mask']['datepicker']))
							$r['mask']['datepicker'] = array('dateFormat' => $r['mask']['datepicker']);

						if (!isset($r['mask']['datepicker']['dateFormat']))
							$r['mask']['datepicker']['dateFormat'] = '\'yy-mm-dd\'';

						if (strpos($r['mask']['format'], 'H:i:s') !== false or $r['mask']['datepicker']['timeFormat'] === true)
							$r['mask']['datepicker']['timeFormat'] = '\' hh:mm:ss\'';


						global $_tpl;
						$prop = array();
						if (!is_null($time))
							$r['mask']['datepicker']['defaultDate'] = 'new Date(' . date('Y,m-1,d', $time) . ')';
						if (!isset($r['mask']['datepicker']['maxDate']) and isset($r['mask']['year_up'])) {
							$r['mask']['datepicker']['maxDate'] = '\'' . $r['mask']['year_up'] . 'y\'';
						}
						if (!isset($r['mask']['datepicker']['minDate']) and isset($r['mask']['year_back'])) {
							$r['mask']['datepicker']['minDate'] = '\'' . $r['mask']['year_back'] . 'y\'';
						}
						foreach ($r['mask']['datepicker'] as $kp => $vp) {
							if ($vp)
								$prop[] = $kp . ':' . $vp;
						}
						$prop = '{' . implode(',', $prop) . '}';
						if ($r['mask']['datepicker']['timeFormat']) {
							plugJQueryUI_datepicker(true);
							$_tpl['script']['dp_' . $ID] = 'function dp_' . $ID . '() { $("input[name=' . $k . ']").datetimepicker(' . $prop . ')}';
						} else {
							plugJQueryUI_datepicker();
							$_tpl['script']['dp_' . $ID] = 'function dp_' . $ID . '() { $("input[name=' . $k . ']").datepicker(' . $prop . ')}';
						}
						$_tpl['onload'] .= ' dp_' . $ID . '(); ';
					}

					$texthtml .= '<input type="text" name="' . $k . '" value="' . $temp . '"/>';
				}

				$texthtml .= '</div>';
			} elseif ($r['type'] == 'captcha') {
				$help = 'Нажмите чтобы обновить картинку / ';
				switch ($r['mask']['difficult']) {
					case 1:
						$help .= 'Только цифры и карилица';
						break;
					case 2:
						$help .= 'Только цифры и карилица';
						break;
					case 3:
						$help .= 'Только цифры и латиница';
						break;
					case 4:
						$help .= 'Только цифры и латиница';
						break;
					case 5:
						$help .= 'Только цифры и карилица';
						break;
					case 6:
						$help .= 'Только цифры и латиница';
						break;
					default:
						$help .= 'Только цифры';
				}
				$texthtml .= '
					<div class="form-value secret">
						<div class="inline"><input type="text" name="' . $k . '" value="' . $r['value'] . '" maxlength="5" size="10" class="secret" autocomplete="off"/></div>
						<div class="secretimg inline">
							<img src="' . $r['src'] . '" class="i_secret i-reload" id="captcha" alt="CARTHA" title="' . $help . '"/>
						</div>
					</div>';
				//$_tpl['onload'] .= ' jQuery(\'.i-reload\').click(function(){reloadCaptcha(\''.$k.'\');}); jQuery(\'#tr_captcha input\').click(function(){wep.setCookie(\'testtest\',1);});';
			} elseif ($r['type'] == 'file') {

				if (isset($r['default']) and $r['default'] != '' and $r['value'] == '') {
					$r['value'] = $r['default'];
					$r['att_type'] = 'img';
				}

				if ($r['caption'] == 1) {
					$texthtml .= '';
				} elseif (!is_array($r['value']) and $r['value'] != '') {
					/* Картинки */
					if ($r['att_type'] == 'img') {
						$css = '';
						if (isset($r['mask']['width']) and $r['mask']['width'])
							$css .= 'width:' . $r['mask']['width'] . 'px;';
						if (isset($r['mask']['height']) and $r['mask']['height'])
							$css = 'height:' . $r['mask']['height'] . 'px;';
						else
							$css = 'height:50px;';

						$texthtml .= '<div class="wep_thumb">
                            <a rel="fancy" href="/' . $r['value'] . '" target="_blank" class="fancyimg">
                                <img src="/' . $r['value'] . '" alt="img" class="attach" style="' . $css . '" id="' . $ID . '_temp_upload_img"/>
                            </a>';
						if (isset($r['img_size']))
							$texthtml .= '<div class="wep_thumb_comment">Размер ' . $r['img_size'][0] . 'x' . $r['img_size'][1] . '</div>';
						$texthtml .= '</div>';
						if (isset($r['thumb']) and $r['thumb'])
							foreach ($r['thumb'] as $thumb) {
								if (!$thumb['pref']) continue;
								$texthtml .= '<div class="wep_thumb">
                                    <a rel="fancy" href="/' . $thumb['value'] . '" target="_blank" class="fancyimg">
                                        <img src="/' . $thumb['value'] . '" alt="img" class="attach" style="' . $css . '"/>
                                    </a>';
								if ($thumb['w']) $texthtml .= '<div class="wep_thumb_comment">Эскиз размером ' . $thumb['w'] . 'x' . $thumb['h'] . '</div>';
								$texthtml .= '</div>';
							}
						plugFancybox();
					} /* Флешка*/
					elseif ($r['att_type'] == 'swf') {
						$texthtml .= '<object type="application/x-shockwave-flash" data="/' . $r['value'] . '" height="50" width="200"><param name="movie" value="/' . $r['value'] . '" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>';
					} /* прочее */
					else {
						$texthtml .= '<span style="color:green"><a href="/' . $r['value'] . '" target="_blank"> фаил загружен</a></span><br/>';
					}
				}

				$texthtml .= '<div class="form-value divinputfile">';

				if (isset($r['mask']['swf_uploader'])) {
					$texthtml .= '
						<div class="fuploader">Загрузка фаила<input type="file" name="' . $k . '" id="' . $ID . '_uploader" ' . $attribute . '/></div><span class="fileinfo"></span>
						<div id="' . $ID . '_notice_swf_uploader"></div>';

					$_tpl['script']['SWFUpload/swfupload_fp10/swfupload'] = 1;
					$_tpl['onload'] .= 'wep.swfuploader.bindSWFUpload({button_placeholder_id:"' . $ID . '_uploader", field_name:"' . $k . '"});';
					//SESSID = "'.session_id().'";
				} else {
					$texthtml .= '<input type="file" name="' . $k . '" ' . $attribute . '/><span class="fileinfo"></span>';
				}


				if (isset($r['del']) and $r['del'] == 1 and $r['value'] != '')
					$texthtml .= '<label class="filedelete">Удалить?&#160;<input type="checkbox" name="' . $k . '_del" value="1"/></label>';

				$texthtml .= '</div>';
			} elseif ($r['type'] == 'password') {
				if (isset($r['mask']['password']) and $r['mask']['password'] == 're') {
					$texthtml .= '<div class="form-value">
						<input type="password" name="' . $k . '" placeholder="Введите пароль" value="" onkeyup="checkPass("' . $k . '")" class="password" ' . $attribute . '/>
						<input type="password" name="re_' . $k . '" placeholder="Повторите ввод пароля" value="" onkeyup="checkPass("' . $k . '")" class="password" ' . $attribute . '/>
						</div>';
				} elseif (isset($r['mask']['password']) and $r['mask']['password'] == 'change') {
					$texthtml .= '<div class="form-value">
						<input type="password" name="' . $k . '_old" placeholder="Введите старый пароль" class="password"/>
						<input type="password" name="' . $k . '" placeholder="Введите новый пароль" class="password"/>
						<div class="passnewdesc" onclick="passwordShow(this)">Отобразить/скрыть символы</div></div>';
				} elseif (isset($r['mask']['password']) and $r['mask']['password'] == 'confirm') {
					$texthtml .= '<div class="form-value"><input type="password" name="' . $k . '" value="" class="password" placeholder="*********" ' . $attribute . '/>
						<div class="passnewdesc" onclick="passwordShow(this)">Отобразить/скрыть символы</div></div>';
				} else {
					$texthtml .= '<div class="form-value"><input type="password" name="' . $k . '" value="' . $r['value'] . '" class="password" ' . $attribute . '/>
						<div class="passnewdesc" onclick="passwordShow(this)">Отобразить/скрыть символы</div></div>';
				}
			} elseif ($r['type'] == 'password_new') {
				trigger_error('Ошибка. Старый формат данных. password_new не поддерживается', E_USER_WARNING);
			} /*elseif($r['type']=='password' and !$r['readonly']) {
				$texthtml .= '<div class="form-value"><input type="text" id="'.$k.'" name="'.$k.'" value="'.$r['value'].'" style="width:55%;float:left;background:#E1E1A1;" readonly="readonly"/>
							<div style="width:40%;float:right;">
								<img src="/_wep/cdesign/default/img/aprm.gif" style="width:18px;cursor:pointer;" onclick="if(confirm(\'Вы действительно хотите изменить пароль?\')) $(\'#'.$k.'\').val(hex_md5(\''.$r['md5'].'\'+$(\'#a_'.$k.'\').val()));" alt="Сгенерировать пароль в формате MD5" title="Сгенерировать пароль в формате MD5"/>
								<input type="text" id="a_'.$k.'" name="a_'.$k.'" value="" style="width:80%;vertical-align:top;"/>
							</div></div>';
				plugMD5();
			}*/
			elseif ($r['type'] == 'color') {
				$_tpl['styles']['../_script/script.jquery/colorpicker/css/colorpicker'] = 1;
				//			$_tpl['styles']['colorpicker/css/layout'] = true;

				$_tpl['script']['script.jquery/colorpicker/js/colorpicker'] = 1;
				$_tpl['script']['script.jquery/colorpicker/js/eye'] = 1;
				$_tpl['script']['script.jquery/colorpicker/js/utils'] = 1;
				$_tpl['script']['script.jquery/colorpicker/js/layout'] = 1;
				$_tpl['onload'] .= ' jQuery(\'#tr_' . $ID . ' div.colorPicker input\').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						$(el).val(\'#\'+hex);
						$(el).ColorPickerHide();
					},
					onBeforeShow: function () {
						$(this).ColorPickerSetColor(this.value.substring(1));
					}
				});';
				$texthtml .= '<div class="form-value colorPicker"><input type="text" name="' . $k . '" value="' . @htmlspecialchars($r['value'], ENT_QUOTES, $_CFG['wep']['charset']) . '" ' . $attribute . '/></div>';
			} /*elseif($r['type']=='phone') {
				$texthtml .= '<div class="form-value" style="font-size:1.2em;">+7(<input type="int" name="'.$k.'[0]" value="" maxlength="3" style="width:27px;"/>) <input type="int" name="'.$k.'[1]" value="" maxlength="3" style="width:27px;"/>
				<input type="int" name="'.$k.'[2]" value="" maxlength="4" style="width:40px;"/>
				</div>';

			}*/
			elseif (isset($r['multiple']) AND $r['multiple'] and !$r['readonly']) {
				$texthtml .= '<div class="form-value">' . helper_form_multiple($k, $r, $attribute) . '</div>';
			} else {
				if (isset($r['isFloat'])) {
					$maskFloat = explode(',', $r['mask']['width']);
					if (!isset($maskFloat[1])) $maskFloat[1] = 0;
					$attribute .= ' class="floatval" data-width0="' . $maskFloat[0] . '" data-width1="' . $maskFloat[1] . '"';
				}
				/*elseif(isset($r['isInt']))
				{
				}*/

				if ($r['type'] == 'email')
					$attribute .= ' x-autocompletetype="' . $r['type'] . '"';
				$texthtml .= '<div class="form-value"><input type="' . $r['type'] . '" name="' . $k . '" value="' . @htmlspecialchars($r['value'], ENT_QUOTES, $_CFG['wep']['charset']) . '" ' . $attribute . '/></div>';
			}
		}

		if (isset($r['comment']) and $r['comment'] != '')
			$texthtml .= '<div class="dscr">' . $r['comment'] . '</div>';

		$texthtml .= '</div>';
	}

	if ($submitHtml) {
		$texthtml .= $submitHtml;
		$submitHtml = '';
	}

	if (!is_null($flagTabs) and $tagStatus) { // TABS
		$texthtml .= '</div>';
	}

	return $texthtml;
}


function helper_form_multiple($k, $r, $attribute)
{
	global $_tpl;
	$texthtml = '';
	if (!is_array($r['value']) or !count($r['value'])) $r['value'] = array('');
	if (!isset($r['keytype'])) $r['keytype'] = 'text';
	if (!isset($r['mask']['maxarr'])) $r['mask']['maxarr'] = 10;
	if (!isset($r['mask']['minarr'])) $r['mask']['minarr'] = 0;
	$cnt = 0;

	foreach ($r['value'] as $kval => $rval) {
		$cnt++;
		$texthtml .= '<div class="ilist">
			' . helper_form_keytype($k, $r, $kval) . '
			' . helper_form_valuetype($k, $r, $kval, $rval, $attribute) . '
			<span class="ilistsort" title="Переместить"></span>
			<span class="ilistdel" title="Удалить"' . (($cnt == 1 and $r['mask']['minarr']) ? ' style="display:none;"' : '') . ' onclick="wep.form.iListdel(this);"></span>
		</div>';
		if ($r['mask']['maxarr'] and $cnt == $r['mask']['maxarr']) break;
	}
	$texthtml .= '<span class="ilistmultiple" onclick="wep.form.iListCopy(this,$(this).prev(),' . $r['mask']['maxarr'] . ')" title="Добавить ' . $r['caption'] . '">' . ($r['mask']['maxarr'] - count($r['value'])) . '</span>';
	$_tpl['onload'] .= 'wep.form.iListsort($("#tr_' . $r['ID'] . ' .form-value"));';
	plugJQueryUI();
	return $texthtml;
}

function helper_form_keytype($k, $r, $kval)
{
	global $_CFG;
	if ($r['keytype'] == 'list') {
		$html = '<select class="ilist-key" onchange="wep.form.iListRev(this,\'' . $k . '\')">' . tpl_formSelect($r['keyValueList'], $kval) . '</select>';
	} // elseif(isset($r['mask']['keylist']) and $r['mask']['keylist']) {
	// 	$text2 = '
	// 		<select class="ilist-val" onchange="wep.form.iListRev(this,\''.$k.'\')">'.tpl_formSelect($r['valuelist'],$kval).'</select>
	// 		<input class="ilist-key" type="'.$r['keytype'].'" value="'.$rval.'" name="'.$k.'['.$kval.']"/>';
	// }

	else {
		$html = '<input class="ilist-key" type="' . $r['keytype'] . '" value="' . @htmlspecialchars($kval, ENT_QUOTES, $_CFG['wep']['charset']) . '" onkeyup="wep.form.iList(this,\'' . $k . '\')"/>';
	}
	return $html;
}

function helper_form_valuetype($k, $r, $kval, $rval, $attribute)
{
	global $_CFG;
	$html = '';
	if ($r['type'] == 'list') {
		$html = '<select class="ilist-val" name="' . $k . '[' . $kval . ']" ' . $attribute . '>' . tpl_formSelect($r['valuelist'], $rval) . '</select>';
	} else {
		$html = '<input class="ilist-val" type="text" name="' . $k . '[' . $kval . ']" value="' . @htmlspecialchars($rval, ENT_QUOTES, $_CFG['wep']['charset']) . '" ' . $attribute . '/>';
	}
	return $html;
}
