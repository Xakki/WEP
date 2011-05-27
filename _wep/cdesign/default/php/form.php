<?
function tpl_form(&$data) {
	global $_CFG;
	$attr = array();
	if(isset($data['_*features*_'])) {
		$attr = $data['_*features*_'];
		unset($data['_*features*_']);
	}
	$texthtml = '';
	$_CFG['fileIncludeOption']['form'] = 1;

	foreach($data as $k=>$r) {
		if(!isset($r['value'])) $r['value'] = '';
		if($r['type']!='hidden')
			$texthtml .= '<div id="tr_'.$k.'" style="'.(isset($r['style'])?$r['style']:'').'" class="div-tr'.
				((isset($r['css']) and $r['css'])?' '.$r['css']:'').
				((isset($r['readonly']) and $r['readonly'])?' readonly':'').'">';
		if($r['type']=='submit') {
			$texthtml .= '<div class="form-submit">';
			if(isset($r['value_save']) and $r['value_save']) {
				$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'_save" value="'.$r['value_save'].'" class="sbmt"/>';
			}	
			$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'"  class="sbmt" onclick="';
			if(isset($r['confirm']) and $r['confirm'])
				$texthtml .= 'if(!confirm(\''.$r['confirm'].'\')) return false;'.($r['onclick']?' else ':'');
			if(isset($r['onclick']))
				$texthtml .= $r['onclick'];
			$texthtml .= '"/>';

			if(isset($r['value_close']) and $r['value_close']) {
				global $HTML;
				end($HTML->path);prev($HTML->path);
				$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'_close" value="'.$r['value_close'].'" class="sbmt" onclick="window.location.href=\''.key($HTML->path).'\';return false;"/>';
			}
			$texthtml .= '</div>';
		}
		elseif($r['type']=='infoinput') {
			$texthtml .= '<div class="infoinput"><input type="hidden" name="'.$k.'" value="'.$r['value'].'"/>'.$r['caption'].'</div>';
		}
		elseif($r['type']=='info') {
			$texthtml .= '<div>'.$r['caption'].'</div>';
		}
		elseif($r['type']=='hidden') {
			$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'" id="'.((isset($r['id']) and $r['id'])?$r['id']:$k).'"/>';
		}
		else {
			$texthtml .= '<div class="form-caption">'.$r['caption'];
			$texthtml .= ((isset($r['mask']['min']) and $r['mask']['min'])?'<span class="form-requere" onmouseover="showHelp(this,\'Данное поле обязательно для заполнения!\',2000,1)">*</span>':'').
				((isset($r['mask']['min2']) and $r['mask']['min2'])?'<span  class="form-requere" onmouseover="showHelp(this,\''.$r['mask']['min2'].'\',4000,1)">**</span>':'').'</div>';
			$attribute = '';
			if(isset($r['readonly']) and $r['readonly'])
				$attribute .= ' readonly="readonly" class="ronly"';
			if(isset($r['disabled']) and $r['disabled'])
				$attribute .= ' disabled="disabled" class="ronly"';
			if($r['type']=='file') {
				if(!isset($r['onchange']))
					$r['onchange'] = '';
				$r['onchange'] .= 'input_file(this)';
			}
			if(isset($r['onchange']) and $r['onchange'])
				$attribute .= ' onchange="'.$r['onchange'].'"';

			if(isset($r['error']) and count($r['error']))
				$texthtml .= '<div class="caption_error">['.implode(' ',$r['error']).']</div>';

			if($r['type']=='textarea') {
				if(!$r['mask']['max']) $r['mask']['max'] = 5000;
				$texthtml .= '<div class="form-value"><textarea name="'.$k.'" onkeyup="textareaChange(this,\''.$r['mask']['max'].'\')" rows="5" cols="50" '.$attribute.'>'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea></div>';
			}
			elseif($r['type']=='radio') {
				$texthtml .= '<div class="form-value">';
				if(!count($r['valuelist']))
					$texthtml .= '<font color="red">Нет элементов для отображения</font>';
				else {
					foreach($r['valuelist'] as $row) {
						$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$row['#id#'].'" class="radio" '.$attribute;
						if($row['#sel#'])
							$texthtml .= ' checked="checked"';
						$texthtml .= '/>'.$row['#name#'].' &#160;&#160;';
					}
				}
				$texthtml .= '</div>';
			}
			elseif($r['type']=='checkbox') {
				$texthtml .= '<div class="form-value checkbox-value';
				if(!isset($r['valuelist']) or !count($r['valuelist']))
					$texthtml .= '"><input type="'.$r['type'].'" name="'.$k.'" value="1" '.($r['value']?'checked="checked"':'').' '.$attribute.'/>';
				else {
					$texthtml .= ' checkbox-valuelist">';
					foreach($r['valuelist'] as $kv=>$rv) {
						$sel = false;
						if(is_array($rv) and isset($rv['#id#'])) {
							$id = $rv['#id#'];
							$name = $rv['#name#'];
							if(isset($rv['#sel#']) and $rv['#sel#'])
								$sel = true;
						} else {
							$id = $kv;
							$name = $rv;
							if(isset($r['value']) and $r['value']==$id)
								$sel = true;
						}
						$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'['.$id.']" value="'.$id.'" class="radio" '.$attribute;
						if($sel)
							$texthtml .= ' checked="checked"';
						$texthtml .= '/><div class="boxtitle">'.$name.'</div>';
					}
				}
				$texthtml .= '</div>';
			}
			elseif($r['type']=='ajaxlist' and isset($r['multiple'])) {
				global $_tpl;				

				if(!is_array($r['value']))
					$r['value'] = explode('|', trim($r['value'], '|'));
				if(!is_array($r['value_2']))
					$r['value_2'] = array($r['value_2']);
				$serl = serialize($r['listname']);
				$max = (isset($r['mask']['max'])?$r['mask']['max']:5);
				for($i=0;$i<$max;$i++) {
					if(isset($r['value'][$i])) {
						$value = $r['value'][$i];
					}
					else {
						$value = '';
						$_tpl['onload'] .= ' jQuery(\'#tr_'.$k.' div.ajaxlist\').eq('.$i.').hide(); ';
					}
					if(isset($r['value_2'][$i])) $value_2 = strip_tags ($r['value_2'][$i]);
					// TODO : Придумать форматированный вывод 
					else $value_2 = '';
					$r['labelstyle'] = ($value_2?'display: none;':'');
					$r['csscheck'] = ($value_2?'accept':'reject');
					$texthtml .= '<div class="form-value ajaxlist">
						<span style="'.$r['labelstyle'].'">'.$r['label'].'</span>
						<input type="text" name="'.$k.'_2['.$i.']" value="'.$value_2.'" onfocus="show_hide_label(this,\''.$k.'\',1,\''.$i.'\')" onblur="show_hide_label(this,\''.$k.'\',0,\''.$i.'\')" onkeyup="ajaxlist(this,\''.$k.'\',\''.$i.'\')" class="'.$r['csscheck'].'" autocomplete="off"/>
						<div id="ajaxlist_'.$k.'_'.$i.'_" style="display:none;" onfocus="chFocusList(0)" onblur="chFocusList(1)">не найдено</div>

						<input type="hidden" name="'.$k.'['.$i.']" value="'.$value.'" '.$attribute.'/>
					</div>';
				}
				$texthtml .= '<input type="hidden" name="hsh_'.$k.'" value="'.md5($serl.$_CFG['wep']['md5']).'"/>
					<input type="hidden" name="srlz_'.$k.'" value="'.htmlspecialchars($serl,ENT_QUOTES,$_CFG['wep']['charset']).'"/>';
				if(!isset($r['comment']))
					$r['comment'] = '';
				$r['comment'] .= '<div class="ajaxmultiple" onclick="jQuery(\'#tr_'.$k.' div.ajaxlist:hidden\').eq(0).show(); if (jQuery(\'#tr_'.$k.' div.ajaxlist:hidden\').size() == 0) jQuery(this).hide();">Добавить '.$r['caption'].'</div>';
			}
			elseif($r['type']=='ajaxlist') {
				$serl = serialize($r['listname']);
				$texthtml .= '<div class="form-value ajaxlist">
					<span style="'.$r['labelstyle'].'">'.$r['label'].'</span>
					<input type="text" name="'.$k.'_2" value="'.strip_tags ($r['value_2']).'" onfocus="show_hide_label(this,\''.$k.'\',1)" onblur="show_hide_label(this,\''.$k.'\',0)" onkeyup="ajaxlist(this,\''.$k.'\')" class="'.$r['csscheck'].'" autocomplete="off"/>
					<div id="ajaxlist_'.$k.'" style="display:none;" onfocus="chFocusList(0)" onblur="chFocusList(1)">не найдено</div>

					<input type="hidden" name="'.$k.'" value="'.$r['value'].'" '.$attribute.'/>
				</div>
				<input type="hidden" name="hsh_'.$k.'" value="'.md5($serl.$_CFG['wep']['md5']).'"/>
				<input type="hidden" name="srlz_'.$k.'" value="'.htmlspecialchars($serl,ENT_QUOTES,$_CFG['wep']['charset']).'"/>';
			}
			elseif($r['type']=='list' and !$r['readonly']) {
				$texthtml .= '<div class="form-value">';
				if(isset($r['size']) and $r['size']>1) {
					$texthtml .= '<select size="'.$r['size'].'" name="'.$k.'" class="small" '.$attribute;
					$texthtml .= '>'.selectitem($r['valuelist'],$r['value']).'</select>';
				}elseif(isset($r['multiple']) and $r['multiple']==2) {
					$texthtml .= '<select multiple="multiple" size="10" name="'.$k.'[]" class="multiple" '.$attribute;
					$texthtml .= '>'.selectitem($r['valuelist'],$r['value']).'</select>';
					$_CFG['fileIncludeOption']['multiple'] = 2;
				}elseif(isset($r['multiple']) and $r['multiple']) {
					$texthtml .= '<select multiple="multiple" size="10" name="'.$k.'[]" class="small" '.$attribute;
					$texthtml .= '>'.selectitem($r['valuelist'],$r['value']).'</select>';
				}else {
					$texthtml .= '<select name="'.$k.'" '.$attribute;
					$texthtml .= '>'.selectitem($r['valuelist'],$r['value']).'</select>';
				}
				$texthtml .= '</div>';
			}
			elseif($r['type']=='date' and $r['readonly']) {

				if($r['fields_type'] =='int' and $r['value']){
					if(isset($r['mask']['format']) and $r['mask']['format']) {
						$temp = date($r['mask']['format'],$r['value']);
					}
					else{
						$temp = date('Y-m-d H:i:s',$r['value']);
					}
				}
				elseif($r['fields_type'] =='timestamp' and $r['value']){
					$temp = sscanf($r['value'], "%d-%d-%d %d:%d:%d");//2007-09-11 10:16:15
				}
				else{
					$temp = array(date('Y'),date('m'),date('d'),date('H'));
				}

				$texthtml .= '<div class="form-value"><input type="text" name="'.$k.'" value="'.$temp.'" '.$attribute.'/></div>';
			}
			elseif($r['type']=='date' and !$r['readonly']) {
				$texthtml .= '<div class="form-value">';

				// формат для даты
				if ($r['mask']['format']) {
					preg_match_all('/[A-Za-z]/', $r['mask']['format'], $matches);
					$format = $matches[0];
				}
				else {
					$r['mask']['format'] = 'Y-m-d-H-i-s';
					$format = explode('-', 'Y-m-d-H-i-s');
					$r['mask']['params']['dateFormat']='yy-mm-dd';
					$r['mask']['params']['timeFormat'] = ' hh:mm:ss';
				}

				if($r['mask']['view']=='input') {

					global $_tpl;
					$prop = array();
					if(is_array($r['mask']['params'])) {
						 foreach ($r['mask']['params'] as $kp => $vp) {
							 $prop[] = $kp.':\''.$vp.'\'';
						 }
						 $prop = '{'.implode(',',$prop).'}';
					}
					else $prop = $r['mask']['params'];

					if(isset($r['mask']['time'])) {
						$_CFG['fileIncludeOption']['datepicker'] = 2;
						$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datetimepicker('.$prop.')}';
					}
					else {
						$_CFG['fileIncludeOption']['datepicker'] = 1;
						$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datepicker('.$prop.')}';
					}
					$_tpl['onload'] .= ' dp_'.$k.'(); ';

					// Тип поля
					if($r['fields_type']  =='int' and $r['value']){
						$temp = date($r['mask']['format'],$r['value']);
					}
					elseif($r['fields_type'] =='timestamp' and $r['value']){
						$fs = explode(' ', $r['value']);
						$f = explode('-', $fs[0]);
						$s = explode(':', $fs[1]);
						$temp = mktime($s[0], $s[1], $s[2], $f[1], $f[2], $f[0]);
						
						if($r['mask']['time'])
							$r['mask']['format'] = $r['mask']['format'].' '.$r['mask']['time'];
						
						$temp = date($r['mask']['format'],$temp);
					}
					
					$texthtml .= '<input type="text" name="'.$k.'" value="'.$temp.'" class="dateinput"/>';
				} 
				else {
				
					// Тип поля
					if($r['fields_type'] =='int' and $r['value']){
						$temp = explode('-',date('Y-m-d-H-i-s',$r['value']));
					}
					elseif($r['fields_type'] =='timestamp' and $r['value']){
						$temp = sscanf($r['value'], "%d-%d-%d %d:%d:%d");//2007-09-11 10:16:15
					}
					else{
						$temp = array(date('Y'),date('n'),date('d'),date('H'));
					}
					$r['value']= array();
					foreach($format as $item_date)
					{
						// год
						if($item_date == 'Y' || $item_date == 'y')
						{
							$r['value']['year'] = array('name'=>$_CFG['_MESS']['year_name'], 'css'=>'year','value'=>$temp[0]);// ГОД
							$temp[0] = (int)$temp[0]; 

							//значения по умолчанию
							if(!isset($r['range_back']['year'])) $r['range_back']['year'] = 2;
							if(!isset($r['range_up']['year'])) $r['range_up']['year'] = 3;
							for($i=((int)date('Y')-($r['range_back']['year']));$i<=((int)date('Y')+($r['range_up']['year']));$i++)
								$r['value']['year']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);							
						}
						// месяц
						if($item_date == 'm' || $item_date == 'n' || $item_date == 'M' || $item_date == 'F')
						{
							$r['value']['month'] = array('name'=>$_CFG['_MESS']['month_name'], 'css'=>'month','value'=>(int)$temp[1]);// Месяц
							foreach($_CFG['_MESS']['month'] as $kr=>$td) {
								$kr = (int)$kr;
								$r['value']['month']['item'][$kr] = array('#id#'=>$kr, '#name#'=>$td);
							}
						}
						// день
						if($item_date == 'd' || $item_date == 'j')
						{
							$r['value']['day'] = array('name'=>$_CFG['_MESS']['day_name'], 'css'=>'day','value'=>(int)$temp[2]);// День
							for($i=1;$i<=31;$i++)
								$r['value']['day']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);						
						}
						// час
						if($item_date == 'G' || $item_date == 'g' || $item_date == 'H' || $item_date == 'h')
						{
							$r['value']['hour'] = array('name'=>$_CFG['_MESS']['hour_name'], 'css'=>'hour','value'=>$temp[3]);// Час
							for($i=0;$i<=23;$i++)
								$r['value']['hour']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);
						}
						// минуты
						if($item_date == 'i')
						{
							$r['value']['minute'] = array('name'=>$_CFG['_MESS']['minute_name'], 'css'=>'minute','value'=>$temp[4]);// Minute
							for($i=1;$i<=60;$i++)
								$r['value']['minute']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);
						}
						// секунды
						if($item_date == 's')
						{
							$r['value']['sec'] = array('name'=>$_CFG['_MESS']['sec_name'], 'css'=>'sec','value'=>$temp[5]);
							for($i=1;$i<=60;$i++)
								$r['value']['sec']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);					
						}
					}

					foreach($r['value'] as $row) {
						$texthtml .= '<div class="dateselect '.$row['css'].'"><span class="name">'.$row['name'].'</span><select name="'.$k.'[]" '.$attribute.'>'.selectitem($row['item'],$row['value']).'</select></div>';
					}
				}		
				$texthtml .= '</div>';
			}
			elseif($r['type']=='captcha') {
				$texthtml .= '<div class="form-value">
						<div class="form-value-left"><input type="text" name="'.$k.'" value="'.$r['value'].'" maxlength="5" size="10" class="secret" autocomplete="off"/></div>
						<div class="secret"><img src="'.$r['src'].'" class="i_secret" id="captcha" alt="CARTHA"/></div>
					</div>';
			}
			elseif($r['type']=='file') {
				$texthtml .= '<div class="form-value divinputfile">';
				$texthtml .= '<input type="file" name="'.$k.'" '.$attribute.'/><span class="fileinfo"></span>';

				if($r['del']==1 and $r['value']!='')
					$texthtml .= '<div style="color:red;float:right;white-space: nowrap;">Удалить?&#160;<input type="checkbox" name="'.$k.'_del" class="del" value="1" onclick="$(\'#tr_'.$k.' td.td2 input[name='.$k.'],#tr_'.$k.' td.td2 div.dscr\').slideToggle(\'normal\')"/></div>';

				if($r['caption']==1)
					$texthtml .= '';
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='img') {
					$texthtml .= '<div class="clear"></div><div class="wep_thumb">
						<a rel="fancy" href="/'.$r['value'].'" target="_blank" class="fancyimg">
							<img src="/'.$r['value'].'" alt="img" class="attach"'.($r['mask']['width']?' width="'.$r['mask']['width'].'"':'').($r['mask']['height']?' height="'.$r['mask']['height'].'"':'').'/>
						</a>
						<div class="wep_thumb_comment">Размер '.$r['img_size'][0].'x'.$r['img_size'][1].'</div>
					</div>';
					if(isset($r['thumb']) and $r['mask']['thumb'])
						foreach($r['thumb'] as $thumb) {
						$texthtml .= '<div class="wep_thumb">
							<a rel="fancy" href="/'.$thumb['value'].'?size='.$thumb['filesize'].'" target="_blank" class="fancyimg">
								<img src="/'.$thumb['value'].'?size='.$thumb['filesize'].'" alt="img" class="attach"'.($thumb['w']?' width="'.$thumb['w'].'"':'').($thumb['h']?' height="'.$thumb['h'].'"':'').'/>
							</a>
							<div class="wep_thumb_comment">Эскиз размером '.$thumb['w'].'x'.$thumb['h'].'</div>
						</div>';
					}
					$texthtml .= '<div class="clear"></div>';
					$_CFG['fileIncludeOption']['fancybox'] = 1;
				}
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='swf')
					$texthtml .= '<object type="application/x-shockwave-flash" data="/'.$r['value'].'" height="50" width="200"><param name="movie" value="/'.$r['value'].'" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>';
				elseif(!is_array($r['value']) and $r['value']!=''){
					$texthtml .= '<span style="color:green"><a href="/'.$r['value'].'" target="_blank"> загружен(a)</a></span><br/>';
				}

				$texthtml .= '</div>';
			}
			elseif($r['type']=='ckedit') {
				if(isset($r['mask']['max']) and $r['mask']['max']) $attribute .= ' maxlength="'.$r['mask']['max'].'"';
				$texthtml .= '<div class="form-value ckedit-value"><textarea id="id_'.$k.'" name="'.$k.'" rows="10" cols="80" '.$attribute.'>'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea></div>';
			}
			elseif($r['type']=='int' and !$r['readonly']) {
				if(isset($r['mask']['max']) and $r['mask']['max']) $attribute .= ' maxlength="'.$r['mask']['max'].'"';
				$texthtml .= '<div class="form-value"><input type="text" name="'.$k.'" value="'.$r['value'].'" onkeydown="return checkInt(event)" '.$attribute.'/></div>';
			}
			elseif($r['type']=='password') {
				$texthtml .= '<div class="form-value"><input type="password" name="'.$k.'" value="" onkeyup="checkPass("'.$k.'")" '.$attribute.'/>
					<div class="dscr">Введите пароль</div>
					<input type="password" name="re_'.$k.'" value="" onkeyup="checkPass("'.$k.'")" '.$attribute.'/>
					<div class="dscr">Чтобы избежать ошибки повторите ввод пароля</div></div>';
			}
			elseif($r['type']=='password_new') {
				$texthtml .= '<div class="form-value"><input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password" '.$attribute.'/>
						<div class="passnewdesc" onclick="password_new()">Отобразить символы/скрыть</div></div>';
			}
			elseif($r['type']=='password_change') {
				$texthtml .= '<div class="form-value">
					<input type="password" name="'.$k.'_old" value=""/><div class="dscr">Введите старый пароль</div>
					<input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password"/>
					<div class="passnewdesc" onclick="password_new()">Отобразить символы/скрыть</div></div>';
			}	
			elseif($r['type']=='password2' and !$r['readonly']) {
				$texthtml .= '<div class="form-value"><input type="text" id="'.$k.'" name="'.$k.'" value="'.$r['value'].'" style="width:55%;float:left;background:#E1E1A1;" readonly="readonly"/>
							<div style="width:40%;float:right;">
								<img src="_wep/cdesign/default/img/aprm.gif" style="width:18px;cursor:pointer;" onclick="if(confirm(\'Вы действительно хотите изменить пароль?\')) $(\'#'.$k.'\').val(hex_md5(\''.$r['md5'].'\'+$(\'#a_'.$k.'\').val()));" alt="Сгенерировать пароль в формате MD5" title="Сгенерировать пароль в формате MD5"/>
								<input type="text" id="a_'.$k.'" name="a_'.$k.'" value="" style="width:80%;vertical-align:top;"/>
							</div></div>';
				$_CFG['fileIncludeOption']['md5'] = 1;
			}
			elseif($r['type']=='html') {
				$texthtml .= '<div class="form-value">'.$r['value'].'</div>';
			}
			else {
				if(isset($r['mask']['max']) and $r['mask']['max']) $attribute .= ' maxlength="'.$r['mask']['max'].'"';
				$texthtml .= '<div class="form-value"><input type="text" name="'.$k.'" value="'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'" '.$attribute.'/></div>';
			}
		}

		if(isset($r['comment']) and $r['comment']!='')
			$texthtml .= '<div class="dscr">'.$r['comment'].'</div>';
		if($r['type']!='hidden')
			$texthtml .= '</div>';
	}
	return $texthtml;
}

function selectitem($data,$val=NULL,$flag=0) {
	$texthtml = '';
	if(!is_null($val)) {
		if(!is_array($val)) 
			$val = array($val=>true);
	}
	if(is_array($data) and count($data))
		foreach($data as $r) {
			//_substr($r['#name#'],0,60).(_strlen($r['#name#'])>60?'...':'')
			$r['#name#'] = str_repeat(" -", $flag).' '.$r['#name#'];
			if(isset($r['#item#']) and count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0)
				$texthtml .= '<optgroup label="'.$r['#name#'].'"></optgroup>';
			else {
				$sel = '';
				if(isset($r['#sel#'])) {
					if($r['#sel#'])
						$sel = 'selected="selected"';
				}
				elseif(!is_null($val) and isset($val[$r['#id#']]))
					$sel = 'selected="selected"';
					
				$texthtml .= '<option value="'.$r['#id#'].'" '.$sel.' class="selpad'.$flag.'">'.$r['#name#'].'</option>';
			}
			if(isset($r['#item#']) and count($r['#item#']))
				$texthtml .= selectitem($r['#item#'],$val,($flag+1));//.'&#160;--'
		}
	return $texthtml;
}

function selectitem_old($data,$val=NULL,$flag=0) {
	$texthtml = '';
	if(!is_null($val)) {
		if(!is_array($val)) 
			$val = array($val=>true);
		//else $val = array_keys($val);
	}
	if(is_array($data) and count($data))
		foreach($data as $r) {
			//_substr($r['#name#'],0,60).(_strlen($r['#name#'])>60?'...':'')
			if(isset($r['#item#']) and count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0)
				$texthtml .= '<optgroup label="'.$r['#name#'].'" class="selpad'.$flag.'"></optgroup>';
			else {
				$sel = '';
				if(isset($r['#sel#'])) {
					if($r['#sel#'])
						$sel = 'selected="selected"';
				}
				elseif(!is_null($val) and isset($val[$r['#id#']]))
					$sel = 'selected="selected"';
				$texthtml .= '<option value="'.$r['#id#'].'" '.$sel.' class="selpad'.$flag.'">'.$r['#name#'].'</option>';
			}
			if(isset($r['#item#']) and count($r['#item#']))
				$texthtml .= selectitem($r['#item#'],$val,($flag+1));//.'&#160;--'
		}
	return $texthtml;
}

