<?
function tpl_form(&$data) {
	global $_CFG;
	$attr = $data['_*features*_'];
	unset($data['_*features*_']);
	$html = '';
	$_CFG['fileIncludeOption']['form'] = 1;

	foreach($data as $k=>$r) {
		if($r['type']!='hidden')
			$html .= '<div id="tr_'.$k.'" style="'.$r['style'].'" class="div-tr'.($r['css']?' '.$r['css']:'').'">';

		if($r['type']=='submit') {
			$html .= '<div class="form-submit">';
			if($r['value_save']) {
				$html .= '<input type="'.$r['type'].'" name="'.$k.'_save" value="'.$r['value_save'].'" class="sbmt"/>';
			}	
			$html .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'"  class="sbmt" onclick="';
			if($r['confirm'])
				$html .= 'if(!confirm(\''.$r['confirm'].'\')) return false;'.($r['onclick']?' else ':'');
			$html .= $r['onclick'].'"/>';

			if($r['value_close']) {
				global $HTML;
				end($HTML->path);prev($HTML->path);
				$html .= '<input type="'.$r['type'].'" name="'.$k.'_close" value="'.$r['value_close'].'" class="sbmt" onclick="window.location.href=\''.key($HTML->path).'\';return false;"/>';
			}
			$html .= '</div>';
		}
		elseif($r['type']=='infoinput') {
			$html .= '<div class="infoinput"><input type="hidden" name="'.$k.'" value="'.$r['value'].'"/>'.$r['caption'].'</div>';
		}
		elseif($r['type']=='info') {
			$html .= '<div>'.$r['caption'].'</div>';
		}
		elseif($r['type']=='hidden') {
			$html .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'" id="'.($r['id']?$r['id']:$k).'"/>';
		}
		else {
			$html .= '<div class="form-caption">'.$r['caption'];
			$html .= ($r['mask']['min']?'<span class="form-requere" onmouseover="showHelp(this,\'Данное поле обязательно для заполнения!\',2000,1)">*</span>':'').($r['mask']['min2']?'<span  class="form-requere" onmouseover="showHelp(this,\''.$r['mask']['min2'].'\',4000,1)">**</span>':'').'</div>';
			
			if(isset($r['error']) and count($r['error']))
				$html .= '<div class="caption_error">['.implode(' ',$r['error']).']</div>';

			if($r['type']=='textarea') {
				if(!$r['mask']['max']) $r['mask']['max'] = 5000;
				$html .= '<div class="form-value"><textarea name="'.$k.'" onkeyup="textareaChange(this,\''.$r['mask']['max'].'\')" rows="5" cols="50"';
				if($r['readonly']) $html .= ' readonly="readonly"';
				$html .= '>'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea></div>';
			}
			elseif($r['type']=='radio') {
				$html .= '<div class="form-value">';
				if(!count($r['item']))
					$html .= '<font color="red">Нет элементов для отображения</font>';
				else {
					foreach($r['item'] as $row) {
						$html .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$row['value'].'" class="radio"';
						if($row['value']==$r['value'])
							$html .= ' checked="checked"';
						$html .= '/>'.$row['name'].' &#160;&#160;';
					}
				}
				$html .= '</div>';
			}
			elseif($r['type']=='checkbox') {
				$html .= '<div class="form-value checkbox-value">';
				if(!count($r['item']))
					$html .= '<input type="'.$r['type'].'" name="'.$k.'" value="1" '.($r['value']?'checked="checked"':'').'/>';
				else {
					foreach($r['item'] as $row) {
						$html .= '<input type="'.$r['type'].'" name="'.$k.'[]" value="'.$row['value'].'" class="radio"';
						if($row['sel'])
							$html .= ' checked="checked"';
						$html .= '/><div class="title">'.$row['#name#'].'</div>';
					}
				}
				$html .= '</div>';
			}
			elseif($r['type']=='ajaxlist') {
				$serl = serialize($r['listname']);
				$html .= '<div class="form-value ajaxlist">
					<span style="'.$r['labelstyle'].'">'.$r['label'].'</span>
					<input type="text" name="'.$k.'_2" value="'.$r['value_2'].'" onfocus="show_hide_label(this,\''.$k.'\',1)" onblur="show_hide_label(this,\''.$k.'\',0)" onkeyup="ajaxlist(this,\''.$k.'\')" class="'.$r['csscheck'].'" autocomplete="off"/>
					<div id="ajaxlist_'.$k.'" style="display:none;" onfocus="chFocusList(0)" onblur="chFocusList(1)">не найдено</div>

					<input type="hidden" name="'.$k.'" value="'.$r['value'].'"/>
				</div>
				<input type="hidden" name="hsh_'.$k.'" value="'.md5($serl.$_CFG['wep']['md5']).'"/>
				<input type="hidden" name="srlz_'.$k.'" value="'.htmlspecialchars($serl,ENT_QUOTES,$_CFG['wep']['charset']).'"/>';
			}
			elseif($r['type']=='list' and !$r['readonly']) {
				$html .= '<div class="form-value">';
				if($r['size']>1) {
					$html .= '<select size="'.$r['size'].'" name="'.$k.'" class="small" onchange="'.$r['onchange'].'"';
					$html .= '>'.selectitem($r['valuelist']).'</select>';
				}elseif($r['multiple']==2) {
					$html .= '<select multiple="multiple" size="10" name="'.$k.'[]" class="multiple" onchange="'.$r['onchange'].'"';
					$html .= '>'.selectitem($r['valuelist']).'</select>';
					$_CFG['fileIncludeOption']['multiple'] = 2;
				}elseif($r['multiple']) {
					$html .= '<select multiple="multiple" size="10" name="'.$k.'[]" class="small" onchange="'.$r['onchange'].'"';
					$html .= '>'.selectitem($r['valuelist']).'</select>';
				}else {
					$html .= '<select name="'.$k.'" onchange="'.$r['onchange'].'"';
					$html .= '>'.selectitem($r['valuelist']).'</select>';
				}
				$html .= '</div>';
			}
			elseif($r['type']=='date' and !$r['readonly']) {
	
				$html .= '<div class="form-value">';
				
				global $_tpl;
				if($r['mask']['time'])
					$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datetimepicker('.$r['mask']['params'].')}';
				else
					$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datepicker('.$r['mask']['params'].')}';
				$_tpl['onload'] .= ' dp_'.$k.'(); ';

				// формат даты
				if(isset($r['mask']['format']) and $r['mask']['format']) {
					if($r['mask']['separate']) {
						$format = explode($r['mask']['separate'], $r['mask']['format']);
					}
					else {
						$format = explode('-', $r['mask']['format']);
					}
				}
				else{
					$format = explode('-', 'Y-m-d-H-i-s');
				}
				if($r['mask']['view']=='input') {
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
					
					$html .= '<input type="text" name="'.$k.'" value="'.$temp.'" class="dateinput"/>';
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
						$temp = array(date('Y'),date('m'),date('d'),date('H'));
					}
					$r['value']= array();
					foreach($format as $item_date)
					{
						// год
						if($item_date == 'Y' || $item_date == 'y')
						{
							$r['value']['year'] = array('name'=>$_CFG['_MESS']['year_name'], 'css'=>'year');// ГОД
							$temp[0] = (int)$temp[0]; 

							//значения по умолчанию
							if(!$r['range_back']['year']) $r['range_back']['year'] = 2;
							if(!$r['range_up']['year']) $r['range_up']['year'] = 3;
							for($i=((int)date('Y')-($r['range_back']['year']));$i<((int)date('Y')+($r['range_up']['year']));$i++)
								$r['value']['year']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[0]==$i?1:0));							
						}
						// месяц
						if($item_date == 'm' || $item_date == 'n' || $item_date == 'M' || $item_date == 'F')
						{
							$r['value']['month'] = array('name'=>$_CFG['_MESS']['month_name'], 'css'=>'month');// Месяц
							foreach($_CFG['_MESS']['month'] as $kr=>$td) {
								$kr = (int)$kr;
								$r['value']['month']['item'][$kr] = array('#id#'=>$kr, '#name#'=>$td, '#sel#'=>($temp[1]==$kr?1:0));
							}						
						}
						// день
						if($item_date == 'd' || $item_date == 'j')
						{
							$r['value']['day'] = array('name'=>$_CFG['_MESS']['day_name'], 'css'=>'day');// День
							for($i=1;$i<=31;$i++)
								$r['value']['day']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[2]==$i?1:0));						
						}
						// час
						if($item_date == 'G' || $item_date == 'g' || $item_date == 'H' || $item_date == 'h')
						{
							$r['value']['hour'] = array('name'=>$_CFG['_MESS']['hour_name'], 'css'=>'hour');// Час
							for($i=1;$i<=24;$i++)
								$r['value']['hour']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[3]==$i?1:0));
						}
						// минуты
						if($item_date == 'i')
						{
							$r['value']['minute'] = array('name'=>$_CFG['_MESS']['minute_name'], 'css'=>'minute');// Minute
							for($i=1;$i<=60;$i++)
								$r['value']['minute']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[4]==$i?1:0));
						}
						// секунды
						if($item_date == 's')
						{
							$r['value']['sec'] = array('name'=>$_CFG['_MESS']['sec_name'], 'css'=>'sec');
							for($i=1;$i<=60;$i++)
								$r['value']['sec']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[5]==$i?1:0));					
						}
					}

					foreach($r['value'] as $row) {
						$html .= '<div class="dateselect '.$row['css'].'"><span class="name">'.$row['name'].'</span><select name="'.$k.'[]">'.selectitem($row['item']).'</select></div>';
					}
				}		
				$html .= '</div>';
			}
			elseif($r['type']=='captcha') {
				$html .= '<div class="form-value">
						<div class="left"><input type="text" name="'.$k.'" maxlength="5" size="10" class="secret" autocomplete="off"/></div>
						<div class="secret"><img src="'.$r['src'].'" class="i_secret" id="captcha" alt="CARTHA"/></div>
					</div>';
			}
			elseif($r['type']=='file') {
				$html .= '<div class="form-value divinputfile">';
				$html .= '<input type="file" name="'.$k.'" size="39" onchange="input_file(this)"/><span class="fileinfo"></span>';

				if($r['del']==1 and $r['value']!='')
					$html .= '<div style="color:red;float:right;white-space: nowrap;">Удалить?&#160;<input type="checkbox" name="'.$k.'_del" class="del" value="1" onclick="$(\'#tr_'.$k.' td.td2 input[name='.$k.'],#tr_'.$k.' td.td2 div.dscr\').slideToggle(\'normal\')"/></div>';

				if($r['caption']==1)
					$html .= '';
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='img') {
					$html .= '<div class="clear"></div><div class="wep_thumb">
						<a rel="fancy" href="/'.$r['value'].'" target="_blank" class="fancyimg">
							<img src="/'.$r['value'].'" alt="img" class="attach"'.($r['mask']['width']?' width="'.$r['mask']['width'].'"':'').($r['mask']['height']?' height="'.$r['mask']['height'].'"':'').'/>
						</a>
						<div class="wep_thumb_comment">Размер '.$r['img_size'][0].'x'.$r['img_size'][1].'</div>
					</div>';
					if(isset($r['thumb']))
						foreach($r['thumb'] as $thumb) {
						$html .= '<div class="wep_thumb">
							<a rel="fancy" href="/'.$thumb['value'].'?size='.$thumb['filesize'].'" target="_blank" class="fancyimg">
								<img src="/'.$thumb['value'].'?size='.$thumb['filesize'].'" alt="img" class="attach"'.($thumb['w']?' width="'.$thumb['w'].'"':'').($thumb['h']?' height="'.$thumb['h'].'"':'').'/>
							</a>
							<div class="wep_thumb_comment">Эскиз размером '.$thumb['w'].'x'.$thumb['h'].'</div>
						</div>';
					}
					$html .= '<div class="clear"></div>';
					$_CFG['fileIncludeOption']['fancybox'] = 1;
				}
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='swf')
					$html .= '<object type="application/x-shockwave-flash" data="/'.$r['value'].'" height="50" width="200"><param name="movie" value="/'.$r['value'].'" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>';
				elseif(!is_array($r['value']) and $r['value']!=''){
					$html .= '<span style="color:green"><a href="/'.$r['value'].'" target="_blank"> загружен(a)</a></span><br/>';
				}

				$html .= '</div>';
			}
			elseif($r['type']=='ckedit') {
				$html .= '<div class="form-value ckedit-value"><textarea name="'.$k.'" rows="10" cols="80" maxlength="'.$r['mask']['width'].'">'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea></div>';
			}
			elseif($r['type']=='int' and !$r['readonly']) {
				$html .= '<div class="form-value"><input type="text" name="'.$k.'" value="'.$r['value'].'" onkeydown="return checkInt(event)" maxlength="'.$r['mask']['width'].'"/></div>';
			}
			elseif($r['type']=='password') {
				$html .= '<div class="form-value"><input type="password" name="'.$k.'" value="" onkeyup="checkPass("'.$k.'")"/>
					<div class="dscr">Введите пароль</div>
					<input type="password" name="re_'.$k.'" value="" onkeyup="checkPass("'.$k.'")"/>
					<div class="dscr">Чтобы избежать ошибки повторите ввод пароля</div></div>';
			}
			elseif($r['type']=='password_new') {
				$html .= '<div class="form-value"><input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password"/>
						<div class="passnewdesc" onclick="password_new()">Отобразить символы/скрыть</div></div>';
			}
			elseif($r['type']=='password_change') {
				$html .= '<div class="form-value">
					<input type="password" name="'.$k.'_old" value=""/><div class="dscr">Введите старый пароль</div>
					<input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password"/>
					<div class="passnewdesc" onclick="password_new()">Отобразить символы/скрыть</div></div>';
			}	
			elseif($r['type']=='password2' and !$r['readonly']) {
				$html .= '<div class="form-value"><input type="text" id="'.$k.'" name="'.$k.'" value="'.$r['value'].'" style="width:55%;float:left;background:#E1E1A1;" readonly="readonly"/>
							<div style="width:40%;float:right;">
								<img src="_wep/cdesign/default/img/aprm.gif" style="width:18px;cursor:pointer;" onclick="if(confirm(\'Вы действительно хотите изменить пароль?\')) $(\'#'.$k.'\').val(hex_md5(\''.$r['md5'].'\'+$(\'#a_'.$k.'\').val()));" alt="Сгенерировать пароль в формате MD5" title="Сгенерировать пароль в формате MD5"/>
								<input type="text" id="a_'.$k.'" name="a_'.$k.'" value="" style="width:80%;vertical-align:top;"/>
							</div></div>';
				$_CFG['fileIncludeOption']['md5'] = 1;
			}
			elseif($r['type']=='html') {
				$html .= '<div class="form-value">'.$r['value'].'</div>';
			}
			else {
				$html .= '<div class="form-value"><input type="text" name="'.$k.'" value="'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'"';
				if($r['mask']['max'])
					$html .= ' maxlength="'.$r['mask']['max'].'"';
				if($r['readonly'])
					$html .= ' readonly="readonly" class="ronly"';
				if($r['disabled'])
					$html .= ' disabled="disabled" class="ronly"';
				$html .= '/></div>';
			}
		}

		if($r['comment']!='')
			$html .= '<div class="dscr">'.$r['comment'].'</div>';
		if($r['type']!='hidden')
			$html .= '</div>';
	}
	return $html;
}

function selectitem($data,$flag='') {
	$html = '';
	if(is_array($data) and count($data))
		foreach($data as $r) {
			//_substr($r['#name#'],0,60).(_strlen($r['#name#'])>60?'...':'')
			if(count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0)
				$html .= '<optgroup label="'.$flag.$r['#name#'].'"></optgroup>';
			else
				$html .= '<option value="'.$r['#id#'].'" '.($r['#sel#']?'selected="selected"':'').'>'.$flag.$r['#name#'].'</option>';
			if(count($r['#item#']))
				$html .= selectitem($r['#item#'],$flag);//.'&#160;--'
		}
	return $html;
}
?>
