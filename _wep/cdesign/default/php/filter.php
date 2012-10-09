<?php
	function tpl_filter(&$data) {
		global $_CFG, $_tpl;
		$html = '';
		if(isset($data['_*features*_'])) {
			$attr = $data['_*features*_'];
			unset($data['_*features*_']);
			$html .= '<form id="form_tools_'.$attr['name'].'" class="'.(isset($attr['css'])?$attr['css']:'filter').'" method="'.$attr['method'].'" action="'.$attr['action'].'"';
			if(isset($attr['onsubmit']))
				$html .= ' onsubmit="'.$attr['onsubmit'].'"';
			$html .= '>';
		}
		
		$html .= '<!--BEGIN_FILTER-->';
		foreach($data as $k=>$r) {
			if(!isset($r['value']))
				$r['value'] = '';
			$attribute = '';

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
				$html .= '<div class="f_item" id="tr_'.$k.'"> <div class="f_caption">'.$r['caption'].'</div>';
				if(isset($r['multiple']) and $r['multiple']) {
					$html .= '<div class="f_value multiplebox">';
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
					//else
					//	$r['value'] = array_combine($r['value'],$r['value']);
					$type = $r['type'];
					if(count($r['valuelist'])==2)
						$type = 'radio';
					$html .= '<label><input type="'.$type.'"';
					if(isset($r['value']['']) or !count($r['value'])) {
						$html .= 'checked="checked"';
					}
					$html .= '/>Все</label>';
					foreach($r['valuelist'] as $rk=>$rr) {
						$html .= '<label><input type="'.$type.'" name="'.$k.'[]" value="'.$rk.'" '.(isset($r['value'][$rk])?'checked="checked"':'').'/>'.$rr['#name#'].'</label>';
					}
					$html .= '</div>';
				}
				else {
					if(isset($r['param']) and $r['param']=='checkbox') {
						$html .= '<div class="f_value checkbox">';
						$html .= '<input type="checkbox" name="'.$k.'" value="1" '.($r['value']==1?'checked="checked"':'').'/>';
						$html .= '</div>';
					}
					elseif(isset($r['valuelist']) and is_array($r['valuelist'])) {
						$html .= '<div class="f_value multiplebox">';
						$html .= '<label><input type="radio" name="'.$k.'" value="" '.($r['value']==''?'checked="checked"':'').'/>Все</label>';
						foreach($r['valuelist'] as $rk=>$rr) {
							$html .= '<label><input type="radio" name="'.$k.'" value="'.$rk.'" '.($r['value']==$rk?'checked="checked"':'').'/>'.$rr['#name#'].'</label>';
						}
						$html .= '</div>';
					} 
					else {
						$html .= '<div class="f_value multiplebox">';
						$html .= '
							<label><input type="radio" name="'.$k.'" value="" class="radio" '.($r['value']==''?'checked="checked"':'').'/>Все</label>
							<label><input type="radio" name="'.$k.'" value="0" class="radio" '.($r['value']=='0'?'checked="checked"':'').'/>Нет</label>
							<label><input type="radio" name="'.$k.'" value="1" class="radio" '.($r['value']=='1'?'checked="checked"':'').'/>Да</label>
							';
						$html .= '</div>';
					}
				}
				$html .= '</div>';
			}
			elseif($r['type']=='linklist') {
				$html .= '<div class="f_item linklist" id="tr_'.$k.'"><div class="f_caption">'.$r['caption'].'</div><div class="f_value">';
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
				if(!isset($r['onchange']))
					$r['onchange'] = '';
				$html .= '<div class="f_item" id="tr_'.$k.'">
				<div class="f_caption">'.$r['caption'].'</div>
				<div class="f_value">
					<select name="'.$k.'" '.(($r['value']!=0 and $r['value']!=1)?'readonly="readonly"':'').' onchange="'.$r['onchange'].'">
						'.selectitem2($r['valuelist']).'
					</select>
				</div>
			  </div>	';
			}
			elseif($r['type']=='ajaxlist') {
				$r['csscheck'] = ($r['value_2']?'accept':'reject');	
				$serl = serialize($r['listname']);
				$html .= '<div class="f_item" id="tr_'.$k.'">
				<div class="f_caption">'.$r['caption'].'</div>
				<div class="f_value" style="position:relative;">
					<div class="ajaxlist">
						<input type="text" name="'.$k.'_2" value="'.strip_tags($r['value_2']).'" placeholder="'.$r['placeholder'].'" class="'.$r['csscheck'].'" autocomplete="off"
							onfocus="show_hide_label(this,\''.$k.'\',1)" 
							onblur="show_hide_label(this,\''.$k.'\',0)" 
							onkeydown="return ajaxlistOnKey(event,this,\''.$k.'\')"/>
						<div id="ajaxlist_'.$k.'" style="display:none;">не найдено</div>
						<input type="hidden" name="'.$k.'" value="'.$r['value'].'"/>
					</div>
				</div>
				<input type="hidden" name="hsh_'.$k.'" value="'.md5($serl.$_CFG['wep']['md5']).'"/>
				<input type="hidden" name="srlz_'.$k.'" value="'.htmlspecialchars($serl,ENT_QUOTES,$_CFG['wep']['charset']).'"/>
			  </div>	';
			}
			elseif($r['type']=='number') {
				if(!isset($r['mask']['min'])) $r['mask']['min'] = 0;
				if(!isset($r['mask']['max'])) $r['mask']['max'] = PHP_INT_MAX;
				if(!isset($r['mask']['step']) or !$r['mask']['step']) $r['mask']['step'] = 1;
				$attribute = ' maxlength="'.$r['mask']['max'].'"';

				$html .= '<div class="f_item" id="tr_'.$k.'">
				<div class="f_caption">'.$r['caption'].'</div>
				<div class="f_value f_int">
					<input type="number" name="'.$k.'" id="'.$k.'" value="'.$r['value'].'" '.$attribute.'/> - <input type="number" name="'.$k.'_2" id="'.$k.'_2" value="'.$r['value_2'].'" '.$attribute.'/>
				</div></div>';//<div class="f_exc"><input type="checkbox" name="exc_'.$k.'" value="exc" '.($r['exc']==1?'checked="checked"':'').'/></div>
				$_tpl['onload'] .= "wep.gSlide('tr_".$k."',".(int)$r['mask']['min'].",".(int)$r['mask']['max'].",".(int)$r['value'].",".(int)$r['value_2'].",".(int)$r['mask']['step'].");";				
				$_CFG['fileIncludeOption']['jquery-ui'] = true;
			}
			elseif($r['type']=='date') {
				$html .= '<div class="f_item" id="tr_'.$k.'">
					<div class="f_caption">'.$r['caption'].'</div>
					<div class="f_int">
						<input type="text" name="'.$k.'" id="'.$k.'" value="'.$r['value'].'" '.$attribute.'/>-<input type="text" name="'.$k.'_2" id="'.$k.'_2" value="'.$r['value_2'].'" '.$attribute.'/>
					</div>
				
			  	</div>';

				$_CFG['fileIncludeOption']['datepicker'] = 1;
				$_tpl['script']['dp_'.$k] = 'function dpf_'.$k.'() { 
					$( "input[name='.$k.']" ).datepicker({
						defaultDate: "+1w",
						changeMonth: true,
						showButtonPanel: true,
						dateFormat: "yy-mm-dd",
						onSelect: function( selectedDate ) {
							$( "input[name='.$k.'_2]" ).datepicker( "option", "minDate", selectedDate );
						}
					});
					$( "input[name='.$k.'_2]" ).datepicker({
						defaultDate: "+1w",
						changeMonth: true,
						showButtonPanel: true,
						dateFormat: "yy-mm-dd",
						onSelect: function( selectedDate ) {
							$( "input[name='.$k.']" ).datepicker( "option", "maxDate", selectedDate );
						}
					});
				}';
				$_tpl['onload'] .= ' dpf_'.$k.'(); ';
			}
			elseif($r['type']=='file') {
				$html .= '<div class="f_item" id="tr_'.$k.'">
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
				if(isset($r['max']) and $r['max'])
					$attribute = ' maxlength="'.$r['max'].'"';
				$html .= '<div class="f_item" id="tr_'.$k.'">
					<div class="f_caption">'.$r['caption'].'</div>
					<div class="f_value"><input type="'.$r['type'].'" name="'.$k.'" id="'.$k.'" value="'.$r['value'].'" '.$attribute.'/></div>
					
				</div>';//<div class="f_exc"><input type="checkbox" name="exc_'.$k.'" value="exc"></input></div>
			}
		}
		$html .= '<!--END_FILTER-->';
		if(isset($attr))
			$html .= '<div class="clear"></div></form>';
		return $html;
	}

function selectitem2($data,$flag=0) {
	$html = '';
	if(is_array($data) and count($data))
		foreach($data as $k=>$r) {
			//_substr($r['name'],0,60).(_strlen($r['name'])>60?'...':'')
			if(isset($r['#item#']) and count($r['#item#']) and (!isset($r['#checked#']) or $r['#checked#']==0))
				$html .= '<optgroup label="'.$r['#name#'].'" class="selpad'.$flag.'"></optgroup>';
			else
				$html .= '<option value="'.$k.'" '.((isset($r['#sel#']) and $r['#sel#'])?'selected="selected"':'').' class="selpad'.$flag.'">'.$r['#name#'].'</option>';
			if(isset($r['#item#']) and count($r['#item#']))
				$html .= selectitem2($r['#item#'],($flag+1));//.'&#160;--'
		}
	return $html;
}

