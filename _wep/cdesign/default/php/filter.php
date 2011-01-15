<?
	function tpl_filter(&$data) {
		global $_CFG;
		$html = '';
		$attr = $data['_*features*_'];
		
		unset($data['_*features*_']);
		
		$html .= '<form id="form_tools_'.$attr['name'].'" method="'.$attr['method'].'" action="'.$attr['action'].'" onsubmit="'.$attr['onsubmit'].'"><div class="filter"><!--BEGIN_FILTER-->';
		foreach($data as $k=>$r) {
			if($r['type']=='submit') {
				$html .= '<div class="f_submit"><input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'"/></div>';
			}
			elseif($r['type']=='hidden') {
				$html .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'" id="'.$k.'"/>';
			}
			elseif($r['type']=='infoinput') {
				$html .= '<div class="infoinput"><input type="hidden" name="'.$k.'" value="'.$r['value'].'"/>'.$r['caption'].'</div>';
			}
			elseif($r['type']=='info') {
				$html .= '<div class="f_submit">'.$r['caption'].'</div>';
			}
			/*elseif($r['type']=='radio') {
				$html .= '<div id="row_f_'.$k.'" style="'.$r['style'].'" class="cont2"><div class="cll1">'.$r['caption'].'</div><div class="cll2_2">';
				if(is_array($r['value']))
					foreach($r['value'] as $row) {
						$html .= '<div><input type="checkbox" name="'.$k.'[]" value="'.$row['value'].'" class="ch_filter" '.($row['sel']?'checked="checked"':'').'/>'.$row['name'].'</div>';
					}
				$html .= '</div></div>';
			}*/
			elseif($r['type']=='checkbox') {
				$html .= '<div class="f_item"><div class="f_caption">'.$r['caption'].'</div>';
				if($r['multiple']) {
					//print_r('<pre>');print_r($r['valuelist']);
					if(!isset($r['valuelist']) or !is_array($r['valuelist']))
						$r['valuelist'] = array(''=>array('#name#'=>'error','#id#'=>''));
					else {
						unset($r['valuelist']['']);
						if(isset($r['valuelist'][0]) and $r['valuelist'][0]['#name#']==' --- ')
							unset($r['valuelist'][0]);
					}
					if(!is_array($r['value'])) {
						if($r['value'])
							$r['value'] = array($r['value']=>$r['value']);
						else
							$r['value'] = array();
					}
					else
						$r['value'] = array_combine($r['value'],$r['value']);
					$html .= '<div class="f_value multiplebox">';
					$html .= '<div><input type="checkbox" name="null" value="" ';
					if(isset($r['value']['']) or !count($r['value'])) {
						$html .= 'checked="checked"';
					}
					$html .= '/>Все</div>';
					foreach($r['valuelist'] as $rk=>$rr) {
						$html .= '<div><input type="checkbox" name="'.$k.'[]" value="'.$rk.'" '.(isset($r['value'][$rk])?'checked="checked"':'').'/>'.$rr['#name#'].'</div>';
					}
				}
				else {
					$html .= '<div class="f_value checkbox">';
					if($r['param']=='checkbox') {
						$html .= '<input type="checkbox" name="'.$k.'" value="1" '.($r['value']==1?'checked="checked"':'').'/>';
					}
					elseif(is_array($r['value'])) {
						$html .= '<table border="0" cellpadding="0" cellspacing="1">';
						foreach($r['value'] as $row) {
							$html .= '<tr>
										<td>'.$row['title'].'</td>
										<td><input type="'.$r['type'].'" name="'.$k.'" value="'.$row['value'].'" class="radio" '.($row['sel']?'checked="checked"':'').'/></td>
									</tr>';
						}
						$html .= '</table>';
					} else {
						$html .= '<select name="'.$k.'">
								<option value="">Все</option>
								<option value="0" '.($r['value']=='0'?'selected="selected"':'').'>Выкл(0)</option>
								<option value="1" '.($r['value']=='1'?'selected="selected"':'').'>Вкл(1)</option>
							</select>';
					}
				}
				$html .= '</div></div>';
			}
			elseif($r['type']=='linklist') {
				//print_r('<pre>');print_r($r['valuelist']);
				$html .= '<div class="f_item linklist"><div class="f_caption">'.$r['caption'].'</div><div class="f_value">';
				foreach($r['valuelist'] as $rk=>$rr) {
					$html .= '<div><a href="'.$rr['#href#'].'">'.$rr['#name#'].'</a></div>';
					if(isset($rr['#item#']) and is_array($rr['#item#'])) {
						foreach($rr['#item#'] as $rrk=>$rrr)
							$html .= '<div>&#160;&#160;<a href="'.$rrr['#href#'].'">'.$rrr['#name#'].'</a></div>';
					}
				}
				$html .= '</div></div>';
			}
			elseif($r['type']=='list') {
				if(!isset($r['valuelist']) or !is_array($r['valuelist']))
					$r['valuelist'] = array(''=>array('#name#'=>'error','#id#'=>''));
				else {
					if(isset($r['valuelist'][0]) and $r['valuelist'][0]['#name#']==' --- ')
						unset($r['valuelist'][0]);
					if(isset($r['valuelist']['']) and $r['valuelist']['']['#name#']==' --- ')
						$r['valuelist']['']['#name#'] = 'Все';
					else
						$r['valuelist'] = array(''=>array('#name#'=>'Все','#id#'=>''))+$r['valuelist'];
				}
				$html .= '<div class="f_item">
				<div class="f_caption">'.$r['caption'].'</div>
				<div class="f_value">
					<select name="'.$k.'" '.(($r['value']!=0 and $r['value']!=1)?'readonly="readonly"':'').' onchange="'.$r['onchange'].'">
						'.selectitem2($r['valuelist']).'
					</select>
				</div>
			  </div>	';
			}
			elseif($r['type']=='ajaxlist') {
				$serl = serialize($r['listname']);
				$html .= '<div class="f_item">
				<div class="f_caption">'.$r['caption'].'</div>
				<div class="f_value" style="position:relative;">
					<div class="ajaxlist">
						<span style="'.$r['labelstyle'].'">'.$r['label'].'</span>
						<input type="text" name="'.$k.'_2" value="'.$r['value_2'].'" onfocus="show_hide_label(this,\''.$k.'\',1)" onblur="show_hide_label(this,\''.$k.'\',0)" onkeyup="ajaxlist(this,\''.$k.'\')" class="'.$r['csscheck'].'" style="width:180px;" autocomplete="off"/>
						<div id="ajaxlist_'.$k.'" style="display:none;">не найдено</div>
						<input type="hidden" name="'.$k.'" value="'.$r['value'].'"/>
					</div>
				</div>
				<input type="hidden" name="hsh_'.$k.'" value="'.md5($serl.$_CFG['wep']['md5']).'"/>
				<input type="hidden" name="srlz_'.$k.'" value="'.htmlspecialchars($serl,ENT_QUOTES,$_CFG['wep']['charset']).'"/>
			  </div>	';
			}
			elseif($r['type']=='int') {
				$html .= '<div class="f_item" id="tr_'.$k.'">
				<div class="f_caption">'.$r['caption'].'</div>
				<div class="f_value f_int">
					<input type="text" name="'.$k.'" id="'.$k.'" value="'.$r['value'].'" onkeydown="return checkInt(event)" maxlength="'.$r['max'].'"/> - <input type="text" name="'.$k.'_2" id="'.$k.'_2" value="'.$r['value_2'].'" onkeydown="return checkInt(event)" maxlength="'.$r['max'].'"/>
				</div>
				
			  </div>';//<div class="f_exc"><input type="checkbox" name="exc_'.$k.'" value="exc" '.($r['exc']==1?'checked="checked"':'').'/></div>
			}
			elseif($r['type']=='date') {
				$html .= '<div class="f_item" id="tr_'.$k.'">
				<div class="f_int">
					Период с <input type="text" name="'.$k.'" id="'.$k.'" value="'.$r['value'].'" maxlength="'.$r['max'].'"/> <span class="po">по</span> 
					<input type="text" name="'.$k.'_2" id="'.$k.'_2" value="'.$r['value_2'].'"  maxlength="'.$r['max'].'"/>
				</div>
				
			  </div>';
			}
			elseif($r['type']=='file') {
				$html .= '<div class="f_item">
					<div class="f_caption">'.$r['caption'].'</div>
					<div class="f_value">
						<select name="'.$k.'">
							<option value="">Все</option>
							<option value="!0" '.($r['value']=='!0'?'selected="selected"':'').'>Есть файл</option>
							<option value="!1" '.($r['value']=='!1'?'selected="selected"':'').'>Нету файла</option>
						</select>
					</div>
				</div>';
			}
			else {
				$html .= '<div class="f_item">
					<div class="f_caption">'.$r['caption'].'</div>
					<div class="f_value"><input type="'.$r['type'].'" name="'.$k.'" id="'.$k.'" value="'.$r['value'].'" maxlength="'.$r['max'].'"/></div>
					
				</div>';//<div class="f_exc"><input type="checkbox" name="exc_'.$k.'" value="exc"></input></div>
			}
		}
		$html .= '<!--END_FILTER--></div><div class="clk"></div></form>';
		return $html;
	}

function selectitem2($data,$flag=0) {
	$html = '';
	if(is_array($data) and count($data))
		foreach($data as $k=>$r) {
			//_substr($r['name'],0,60).(_strlen($r['name'])>60?'...':'')
			if(count($r['#item#']) and $r['#checked#']==0)
				$html .= '<optgroup label="'.$r['#name#'].'" class="selpad'.$flag.'"></optgroup>';
			else
				$html .= '<option value="'.$k.'" '.($r['#sel#']?'selected="selected"':'').' class="selpad'.$flag.'">'.$r['#name#'].'</option>';
			if(count($r['#item#']))
				$html .= selectitem2($r['#item#'],($flag+1));//.'&#160;--'
		}
	return $html;
}
?>
