<?php
function tpl_filter(&$data)
{
    global $_CFG, $_tpl;
    $html = '';

    if (isset($data['_*features*_'])) {
        trigger_error('Ошибка. Старый формат данных. Отсутствуют опции для формы', E_USER_WARNING);
        return '';
    }

    if (isset($data['options'])) {
        $html .= '<form id="' . $data['options']['name'] . '" class="' . (isset($data['options']['css']) ? $data['options']['css'] : 'filter') . '" method="' . $data['options']['method'] . '" action="' . $data['options']['action'] . '"';
        if (isset($data['options']['onsubmit']))
            $html .= ' onsubmit="' . $data['options']['onsubmit'] . '"';
        $html .= '>';
    }

    if (!isset($data['noLabel'])) {
        $data['noLabel'] = false;
    }

    $html .= '<!--BEGIN_FILTER-->';
    foreach ($data['form'] as $k => $r) {
        if (isset($r['field_key'])) {
            $name = $r['field_key'];
        } else {
            $name = $k;
        }
        if (!isset($r['noLabel'])) {
            $r['noLabel'] = $data['noLabel'];
        }
        if (!isset($r['value'])) {
            $r['value'] = '';
        }
        $attribute = '';
        if (!isset($r['css']) || !$r['css']) {
            $r['css'] = '';
        }

        $ID = $r['ID'] = str_replace(array('[', ']'), '_', $k);

        if ($r['type'] == 'submit') {
            $html .= '<div class="f_submit ' . ($r['css'] ? $r['css'] : '') . '" id="tr_' . $k . '"><input type="' . $r['type'] . '" name="' . $name . '" value="' . $r['value'] . '"/></div>';
        } elseif ($r['type'] == 'hidden') {
            $html .= '<input type="' . $r['type'] . '" name="' . $name . '" value="' . $r['value'] . '" id="' . $k . '"/>';
        } elseif ($r['type'] == 'infoinput') {
            $html .= '<div class="infoinput" id="tr_' . $k . '"><input type="hidden" name="' . $name . '" value="' . $r['value'] . '"/>' . $r['caption'] . '</div>';
        } elseif ($r['type'] == 'info') {
            $html .= '<div class="f_submit" id="tr_' . $k . '">' . $r['caption'] . '</div>';
        } /*elseif($r['type']=='radio') {
				$html .= '<div id="row_f_'.$k.'" style="'.$r['style'].'" class="cont2"><div class="cll1">'.$r['caption'].'</div><div class="cll2_2">';
				if(is_array($r['value']))
					foreach($r['value'] as $row) {
						$html .= '<div><input type="checkbox" name="'.$name.'[]" value="'.$row['value'].'" class="ch_filter" '.($row['sel']?'checked="checked"':'').'/>'.$row['name'].'</div>';
					}
				$html .= '</div></div>';
			}*/
        elseif ($r['type'] == 'checkbox') {
            $r['noLabel'] = false;
            $html .= '<div class="f_item" id="tr_' . $k . '">';

            if (isset($r['multiple']) and $r['multiple']) {
                $html .= filterCaptionRender($r);
                $html .= '<div class="f_value multiplebox">';
                if (!isset($r['valuelist']) or !is_array($r['valuelist']))
                    $r['valuelist'] = array('' => array('#name#' => 'error', '#id#' => ''));
                else {
                    unset($r['valuelist']['']);
                    if (isset($r['valuelist'][0]) and $r['valuelist'][0]['#name#'] == ' --- ')
                        unset($r['valuelist'][0]);
                }
                if (!is_array($r['value'])) {
                    if ($r['value'])
                        $r['value'] = array($r['value'] => $r['value']);
                    else
                        $r['value'] = array();
                }
                //else
                //	$r['value'] = array_combine($r['value'],$r['value']);
                $type = $r['type'];
                if (count($r['valuelist']) == 2)
                    $type = 'radio';
                $html .= '<label><input type="' . $type . '"';
                if (isset($r['value']['']) or !count($r['value'])) {
                    $html .= 'checked="checked"';
                }
                $html .= '/>' . filterAllRender($r) . '</label>';
                foreach ($r['valuelist'] as $rk => $rr) {
                    $html .= '<label><input type="' . $type . '" name="' . $name . '[]" value="' . $rk . '" ' . (isset($r['value'][$rk]) ? 'checked="checked"' : '') . '/>' . $rr['#name#'] . '</label>';
                }
                $html .= '</div>';
            } else {
                if (isset($r['param']) and $r['param'] == 'checkbox') {
                    $html .= '<label class="f_value checkbox-label">';
                    $html .= '<input type="checkbox" name="' . $name . '" value="1" ' . ($r['value'] == 1 ? 'checked="checked"' : '') . '/>';
                    $html .= filterCaptionRender($r) . '</label>';
                } elseif (isset($r['valuelist']) and is_array($r['valuelist'])) {
                    $html .= filterCaptionRender($r);
                    $html .= '<div class="f_value multiplebox">';
                    $html .= '<label><input type="radio" name="' . $name . '" value="" ' . ($r['value'] == '' ? 'checked="checked"' : '') . '/>' . filterAllRender($r) . '</label>';
                    foreach ($r['valuelist'] as $rk => $rr) {
                        $html .= '<label><input type="radio" name="' . $name . '" value="' . $rk . '" ' . ($r['value'] == $rk ? 'checked="checked"' : '') . '/>' . $rr['#name#'] . '</label>';
                    }
                    $html .= '</div>';
                } else {
                    $html .= filterCaptionRender($r);
                    $html .= '<div class="f_value multiplebox">';
                    $html .= '
							<label><input type="radio" name="' . $name . '" value="" class="radio" ' . ($r['value'] == '' ? 'checked="checked"' : '') . '/>' . filterAllRender($r) . '</label>
							<label><input type="radio" name="' . $name . '" value="0" class="radio" ' . ($r['value'] == '0' ? 'checked="checked"' : '') . '/>Нет</label>
							<label><input type="radio" name="' . $name . '" value="1" class="radio" ' . ($r['value'] == '1' ? 'checked="checked"' : '') . '/>Да</label>
							';
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
        } elseif ($r['type'] == 'linklist') {
            $r['noLabel'] = false;
            $html .= '<div class="f_item linklist" id="tr_' . $k . '">';
            $html .= filterCaptionRender($r);
            $html .= '<div class="f_value">';
            foreach ($r['valuelist'] as $rk => $rr) {
                $html .= '<div><a href="' . $rr['#href#'] . '">' . $rr['#name#'] . '</a></div>';
                if (isset($rr['#item#']) and is_array($rr['#item#'])) {
                    foreach ($rr['#item#'] as $rrk => $rrr)
                        $html .= '<div>&#160;&#160;<a href="' . $rrr['#href#'] . '">' . $rrr['#name#'] . '</a></div>';
                }
            }
            $html .= '</div></div>';
        } elseif ($r['type'] == 'list') {
            $attr = '';
            if (isset($r['onchange']) and $r['onchange']) {
                $attr .= ' onchange="' . $r['onchange'] . '"';
            }

            if ($r['value'] != 0 and $r['value'] != 1) {
                $attr .= ' readonly="readonly"';
            }

            if (!isset($r['valuelist']) or !is_array($r['valuelist'])) {
                $r['valuelist'] = array('' => array('#name#' => 'error', '#id#' => ''));
            } else {
                if (isset($r['valuelist'][0]) and $r['valuelist'][0]['#name#'] == ' --- ')
                    unset($r['valuelist'][0]);
                if (isset($r['valuelist']['']) and $r['valuelist']['']['#name#'] == ' --- ')
                    $r['valuelist']['']['#name#'] = filterAllRender($r);
                else
                    $r['valuelist'] = array('' => array('#name#' => filterAllRender($r), '#id#' => '')) + $r['valuelist'];
            }

            if (isset($r['multiple']) and $r['multiple']) {
                $attr .= ' multiple="multiple" class="multiselectFilter"';
                plugBootstrapMultiselect('select.multiselectFilter');
                if ($name) {
                    $attr .= ' name="' . $name . '[]"';
                }
            } else {
                $attr .= ' name="' . $name . '"';
            }

            $html .= '<div class="f_item" id="tr_' . $k . '">
				' . filterCaptionRender($r) . '
				<div class="f_value">
					<select ' . $attr . '>
						' . filterSelectOptionsRender($r['valuelist']) . '
					</select>
				</div>
			  </div>	';
        } elseif ($r['type'] == 'ajaxlist') {
            /////////////////////
            $defaultList = '';
            if (isset($r['defaultList'])) {
                $defaultList = '<div id="ajaxlist_' . $ID . '_default" style="display:none;">';
                foreach ($r['defaultList'] as $dlK => $dlR) {
                    $defaultList .= '<label data-id="' . $dlK . '">' . $dlR . '</label>';
                }
                $defaultList .= '</div>';
            }
            if (!isset($r['value_2'])) {
                $r['value_2'] = '';
            }
            $r['csscheck'] = ($r['value_2'] ? '' : 'reject');
            $serl = serialize($r['listname']);

            $html .= '<div class="f_item" id="tr_' . $k . '">
				' . filterCaptionRender($r) . '
				<div class="f_value ajaxlist ' . $r['csscheck'] . '">
					<input type="text" name="' . $k . '_2" id="' . $ID . '_2" value="' . _e($r['value_2']) . '" placeholder="' . $r['placeholder'] . '" autocomplete="off"/>
					<div id="ajaxlist_' . $ID . '" style="display:none;" val="' . $r['value_2'] . '">не найдено</div>
					' . $defaultList . '
					<input type="hidden" name="' . $k . '" id="' . $ID . '" value="' . $r['value'] . '" ' . $attribute . '/>
				</div>
				<input type="hidden" id="hsh_' . $k . '" value="' . md5($serl . $_CFG['wep']['md5']) . '"/>
				<input type="hidden" id="srlz_' . $k . '" value="' . _e($serl) . '"/>
			</div>';
            $_tpl['onload'] .= 'setEventAjaxList("#' . $ID . '_2", "#' . $ID . '", "#ajaxlist_' . $ID . '");';
        } elseif ($r['type'] == 'number' or $r['type'] == 'int') {
            if (!isset($r['placeholder1'])) $r['placeholder1'] = 'от';
            if (!isset($r['placeholder2'])) $r['placeholder2'] = 'до';
            if (!isset($r['mask']['min'])) $r['mask']['min'] = 0;
            if (!isset($r['mask']['max'])) $r['mask']['max'] = PHP_INT_MAX;
            if (!isset($r['mask']['step']) or !$r['mask']['step']) $r['mask']['step'] = 1;
            $attribute = ' maxlength="' . $r['mask']['max'] . '"';

            $_tpl['onload'] .= "wep.gSlide('tr_" . $k . "'," . (int)$r['mask']['min'] . "," . (int)$r['mask']['max'] . "," . (int)$r['value'] . "," . (int)$r['value_2'] . "," . (int)$r['mask']['step'] . ");";

            $html .= '<div class="f_item" id="tr_' . $k . '">
				' . filterCaptionRender($r) . '
				<div class="f_value f_int">
					<input type="text" class="form-control" name="' . $name . '" id="' . $k . '" value="' . ($r['value'] != $r['mask']['min'] ? $r['value'] : '') . '" placeholder="' . $r['placeholder1'] . '" ' . $attribute . '/> - <input type="text" class="form-control" name="' . $name . '_2" id="' . $k . '_2" value="' . ($r['value_2'] != $r['mask']['max'] ? $r['value_2'] : '') . '" placeholder="' . $r['placeholder2'] . '" ' . $attribute . '/>
				</div></div>';
            //<div class="f_exc"><input type="checkbox" name="exc_'.$name.'" value="exc" '.($r['exc']==1?'checked="checked"':'').'/></div>
            plugJQueryUI();
        } elseif ($r['type'] == 'date') {
            $r['noLabel'] = false;
            $html .= '<div class="f_item" id="tr_' . $k . '">
					' . filterCaptionRender($r) . '
					<div class="f_int">
						<input type="text" name="' . $name . '" id="' . $k . '" value="' . $r['value'] . '" ' . $attribute . '/>-<input type="text" name="' . $name . '_2" id="' . $k . '_2" value="' . $r['value_2'] . '" ' . $attribute . '/>
					</div>
				
			  	</div>';

            plugJQueryUI_datepicker();
            $_tpl['script']['dp_' . $k] = 'function dpf_' . $k . '() {
					$( "input[name=' . $k . ']" ).datepicker({
						defaultDate: "+1w",
						changeMonth: true,
						showButtonPanel: true,
						dateFormat: "yy-mm-dd",
						onSelect: function( selectedDate ) {
							$( "input[name=' . $k . '_2]" ).datepicker( "option", "minDate", selectedDate );
						}
					});
					$( "input[name=' . $k . '_2]" ).datepicker({
						defaultDate: "+1w",
						changeMonth: true,
						showButtonPanel: true,
						dateFormat: "yy-mm-dd",
						onSelect: function( selectedDate ) {
							$( "input[name=' . $k . ']" ).datepicker( "option", "maxDate", selectedDate );
						}
					});
				}';
            $_tpl['onload'] .= ' dpf_' . $k . '(); ';
        } elseif ($r['type'] == 'file') {
            $html .= '<div class="f_item" id="tr_' . $k . '">
					' . filterCaptionRender($r) . '
					<div class="f_value">
						<select name="' . $name . '">
							<option value="">' . filterAllRender($r) . '</option>
							<option value="!0" ' . ($r['value'] == '!0' ? 'selected="selected"' : '') . '>Есть файл</option>
							<option value="!1" ' . ($r['value'] == '!1' ? 'selected="selected"' : '') . '>Нету файла</option>
						</select>
					</div>
				</div>';
        } else {
            if (isset($r['max']) and $r['max'])
                $attribute = ' maxlength="' . $r['max'] . '"';
            if ($r['noLabel']) {
                $attribute = ' placeholder="' . $r['caption'] . '"';
            }
            $html .= '<div class="f_item" id="tr_' . $k . '">
					' . filterCaptionRender($r) . '
					<div class="f_value"><input type="' . $r['type'] . '" name="' . $name . '" id="' . $k . '" value="' . $r['value'] . '" ' . $attribute . '/></div>
				</div>';
            //<div class="f_exc"><input type="checkbox" name="exc_'.$name.'" value="exc"></input></div>
        }
    }
    $html .= '<!--END_FILTER-->';
    if (isset($attr)) {
        $html .= '<div class="clear"></div>';
    }

    if (isset($data['options'])) {
        $html .= '</form>';
    }
    return $html;
}

function filterSelectOptionsRender($data, $flag = 0)
{
    $html = '';
    if (is_array($data) and count($data))
        foreach ($data as $k => $r) {
            //_substr($r['name'],0,60).(_strlen($r['name'])>60?'...':'')
            if (isset($r['#item#']) and count($r['#item#']) and (!isset($r['#checked#']) or $r['#checked#'] == 0))
                $html .= '<optgroup label="' . $r['#name#'] . '" class="selpad' . $flag . '"></optgroup>';
            else
                $html .= '<option value="' . (isset($r['#id#']) ? $r['#id#'] : $k) . '" ' . ((isset($r['#sel#']) and $r['#sel#']) ? 'selected="selected"' : '') . ' class="selpad' . $flag . '">' . $r['#name#'] . '</option>';
            if (isset($r['#item#']) and count($r['#item#']))
                $html .= filterSelectOptionsRender($r['#item#'], ($flag + 1));
            //.'&#160;--'
        }
    return $html;
}


function filterCaptionRender($field)
{
    if ($field['noLabel']) return '';
    return '<div class="f_caption">' . $field['caption'] . '</div>';
}

function filterAllRender($field)
{
    if ($field['noLabel']) return $field['caption'];
    return 'Все';
}