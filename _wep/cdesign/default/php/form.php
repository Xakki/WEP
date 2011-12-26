<?php
function tpl_form(&$data) {
	global $_CFG, $_tpl;
	$attr = array();
	if(isset($data['_*features*_'])) {
		$attr = $data['_*features*_'];
		unset($data['_*features*_']);
	}
	if(!isset($attr['id']))
		$attr['id'] = 0;
	$texthtml = '';
	$_CFG['fileIncludeOption']['form'] = 1;

	foreach($data as $k=>$r) {
		if(!isset($r['value'])) $r['value'] = '';
		if($r['type']!='hidden')
			$texthtml .= '<div id="tr_'.$k.'" style="'.(isset($r['style'])?$r['style']:'').'" class="div-tr'.
				((isset($r['css']) and $r['css'])?' '.$r['css']:'').
				((isset($r['readonly']) and $r['readonly'])?' readonly':'').'">';

		if($r['type']=='submit' and is_array($r['value'])) {
			$texthtml .= '<div class="form-submit">';
			foreach($r['value'] as $ksubmit=>$rsubmit)
				$texthtml .= '<input type="'.$r['type'].'" name="'.$k.''.$ksubmit.'" value="'.$rsubmit.'" class="sbmt"/>';
			$texthtml .= '</div>';
		}
		elseif($r['type']=='submit') {
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

			if(isset($r['value_del']) and $r['value_del']) {
				$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'_del" value="'.$r['value_del'].'" class="sbmt" onclick="if(confirm(\''.$r['value_del'].'\')) return true; return false;"/>';
			}

			if(isset($r['value_close']) and $r['value_close']) {
				$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'_close" value="'.$r['value_close'].'" class="sbmt" onclick="window.location.href=\''.$attr['prevhref'].'\';return false;"/>';
			}
			$texthtml .= '</div>';
		}
		elseif($r['type']=='infoinput') {
			$texthtml .= '<div class="infoinput"><input type="hidden" name="'.$k.'" value="'.$r['value'].'"/>'.$r['caption'].'</div>';
		}
		elseif($r['type']=='info') {
			$texthtml .= '<div>'.$r['caption'].'</div>';
		}
		elseif($r['type']=='html') {
			$texthtml .= '<div class="form-value">'.$r['value'].'</div>';
		}
		elseif($r['type']=='hidden') {
			$r['value'] = htmlentities($r['value'],ENT_QUOTES,$_CFG['wep']['charset']);
			$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'" id="'.((isset($r['id']) and $r['id'])?$r['id']:$k).'"/>';
		}
		else {
			$texthtml .= '<div class="form-caption">'.$r['caption'];
			if((isset($r['mask']['min']) and $r['mask']['min']) or (isset($r['mask']['minint']) and $r['mask']['minint'])) {
				$texthtml .= '<span class="form-requere" onmouseover="showHelp(this,\'Данное поле обязательно для заполнения!\',2000,1)">*</span>';
			}
			elseif(isset($r['mask']['min2']) and $r['mask']['min2']) {
				$texthtml .= '<span  class="form-requere" onmouseover="showHelp(this,\''.$r['mask']['min2'].'\',2000,1)">**</span>';
			}
			if($r['type']=='ckedit' and static_main::_prmUserCheck(1))
				$texthtml .= '<input type="checkbox" onchange="SetWysiwyg(this)" name="'.$k.'_ckedit" style="width:13px;vertical-align: bottom;margin: 0 0 0 5px;"/>';
			$texthtml .= '</div>';

			$attribute = '';
			if(isset($r['readonly']) and $r['readonly'])
				$attribute .= ' readonly="readonly" class="ronly"';
			else
				$r['readonly'] = false;
			if(isset($r['disabled']) and $r['disabled'])
				$attribute .= ' disabled="disabled" class="ronly"';
			if($r['type']=='file') {
				if(!isset($r['onchange']))
					$r['onchange'] = '';
				$r['onchange'] .= 'input_file(this)';
			}
			if(isset($r['onchange']) and $r['onchange'])
				$attribute .= ' onchange="'.$r['onchange'].'"';

			if(isset($r['error']) and is_array($r['error']) and count($r['error']))
				$texthtml .= '<div class="caption_error">['.implode(' ',$r['error']).']</div>';

			if($r['type']=='textarea') {
				if(!isset($r['mask']['max'])) $r['mask']['max'] = 5000;
				$attribute .= ' maxlength="'.$r['mask']['max'].'"';
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
						$readonly = false;
						if(is_array($rv) and isset($rv['#id#'])) {
							$id = $rv['#id#'];
							$name = $rv['#name#'];
							if($rv['#readonly#'])
								$readonly = true;
							if(isset($rv['#sel#']) and $rv['#sel#'])
								$sel = true;
						} else {
							$id = $kv;
							$name = $rv;
							if(isset($r['value'])) {
								if(is_array($r['value'])) {
									if(isset($r['value'][$id]))
										$sel = true;
								}
								elseif($r['value']==$id)
									$sel = true;
							}
						}
						$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'['.$id.']" value="'.$id.'" class="radio" '.$attribute;
						if($sel)
							$texthtml .= ' checked="checked"';
						if($readonly)
							$texthtml .= ' readonly="readonly"';
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
				$max = (isset($r['mask']['maxarr'])?$r['mask']['maxarr']:5);
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
						<input type="text" name="'.$k.'_2['.$i.']" value="'.$value_2.'" onfocus="show_hide_label(this,\''.$k.'\',1,\''.$i.'\')" onblur="show_hide_label(this,\''.$k.'\',0,\''.$i.'\')" onkeyup="return ajaxlistOnKey(event,this,\''.$k.'\',\''.$i.'\')" class="'.$r['csscheck'].'" autocomplete="off"/>
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
					<input type="text" name="'.$k.'_2" value="'.strip_tags ($r['value_2']).'" onfocus="show_hide_label(this,\''.$k.'\',1)" onblur="show_hide_label(this,\''.$k.'\',0)" onkeydown="return ajaxlistOnKey(event,this,\''.$k.'\')" class="'.$r['csscheck'].'" autocomplete="off"/>
					<div id="ajaxlist_'.$k.'" style="display:none;" onfocus="chFocusList(0)" onblur="chFocusList(1)">не найдено</div>

					<input type="hidden" name="'.$k.'" value="'.$r['value'].'" '.$attribute.'/>
				</div>
				<input type="hidden" name="hsh_'.$k.'" value="'.md5($serl.$_CFG['wep']['md5']).'"/>
				<input type="hidden" name="srlz_'.$k.'" value="'.htmlspecialchars($serl,ENT_QUOTES,$_CFG['wep']['charset']).'"/>';
			}
			elseif($r['type']=='list' and !$r['readonly']) {
				$texthtml .= '<div class="form-value">';
				if(isset($r['multiple'])) {
					if(!isset($r['mask']['size'])) $r['mask']['size'] = 10;
					if(!isset($r['mask']['maxarr'])) $r['mask']['maxarr'] = 10;
				}

				if(isset($r['multiple']) and $r['multiple']==2) {
					$texthtml .= '<select multiple="multiple" name="'.$k.'[]" class="multiple" size="'.$r['mask']['size'].'" '.$attribute;
					$texthtml .= '>'.selectitem($r['valuelist']).'</select>';
					$_CFG['fileIncludeOption']['multiple'] = 2;
				}elseif(isset($r['multiple']) and $r['multiple']==3) {
					if(!is_array($r['value']) or !count($r['value'])) $r['value'] = array('');
					$cnt = 0;
					foreach($r['value'] as $kval=>$rval) {
						$cnt++;
						$texthtml .= '<div class="ilist">
							<input type="text" value="'.$kval.'" onkeyup="wep.form.ilist(this,\''.$k.'\')"/>
							<select name="'.$k.'['.$kval.']" '.$attribute.'>'.selectitem($r['valuelist'],$rval).'</select>
							<span'.($cnt==1?' style="display:none;"':'').' class="ilistdel" onclick="wep.form.ilistdel(this);" title="Удалить"></span>
						</div>';
						if($cnt==$r['mask']['maxarr']) break;
					}
					$texthtml .= '<span class="ilistmultiple" onclick="wep.form.ilistCopy(this,\'div.ilist\','.$r['mask']['maxarr'].')" title="Добавить '.$r['caption'].'">'.($r['mask']['maxarr']-count($r['value'])).'</span>';
				}elseif(isset($r['multiple']) and $r['multiple']) {
					
					$texthtml .= '<select multiple="multiple" name="'.$k.'[]" class="small" size="'.$r['mask']['size'].'" '.$attribute;
					$texthtml .= '>'.selectitem($r['valuelist']).'</select>';
				}else {
					$texthtml .= '<select name="'.$k.'" '.$attribute;
					$texthtml .= '>'.selectitem($r['valuelist']).'</select>';
				}
				$texthtml .= '</div>';
			}
			elseif($r['type']=='date' and $r['readonly']) {
				$temp = '';
				if($r['fields_type'] =='int' and $r['value']){
					if(isset($r['mask']['format']) and $r['mask']['format']) {
						$temp = date($r['mask']['format'],$r['value']);
					}
					else{
						$temp = date('Y-m-d H:i:s',$r['value']);
					}
				}
				elseif($r['fields_type'] =='timestamp' and $r['value']){
					$temp = $r['value'];//2007-09-11 10:16:15
				}
				else{
					$temp = date("Y-m-d H:i:s");
				}
				$texthtml .= '<div class="form-value"><input type="text" name="'.$k.'" value="'.$temp.'" '.$attribute.'/></div>';
			}
			elseif($r['type']=='date' and !$r['readonly']) {
				$texthtml .= '<div class="form-value">';
				$temp = '';

				if(isset($r['mask']['view']) and $r['mask']['view']=='input') {
					$time=NULL;
					// Тип поля
					if($r['fields_type']  =='int' and $r['value']){
						$time = $r['value'];
						$temp = date($r['mask']['format'],$r['value']);
					}
					elseif($r['fields_type'] =='timestamp' and $r['value']){
						$fs = explode(' ', $r['value']);
						$f = explode('-', $fs[0]);
						$s = explode(':', $fs[1]);
						$time = $temp = mktime($s[0], $s[1], $s[2], $f[1], $f[2], $f[0]);
						
						if($r['mask']['time'])
							$r['mask']['format'] = $r['mask']['format'].' '.$r['mask']['time'];
						
						$temp = date($r['mask']['format'],$temp);
					}

					// текстовый формат ввода данных
					if(isset($r['mask']['datepicker'])) {
						if(is_string($r['mask']['datepicker']))
							$r['mask']['datepicker'] = array('dateFormat'=>$r['mask']['datepicker']);
						elseif(!is_array($r['mask']['datepicker']))
							$r['mask']['datepicker'] = array();

						if(!isset($r['mask']['datepicker']['dateFormat']))
							$r['mask']['datepicker']['dateFormat']='\'yy-mm-dd\'';
						if(isset($r['mask']['datepicker']['timeFormat']) and $r['mask']['datepicker']['timeFormat']===true)
							$r['mask']['datepicker']['timeFormat'] = '\'-hh-mm-ss\'';

						global $_tpl;
						$prop = array();
						if(!is_null($time))
							$r['mask']['datepicker']['defaultDate'] = 'new Date('.date('Y,m-1,d',$time).')';
						foreach ($r['mask']['datepicker'] as $kp => $vp) {
							$prop[] = $kp.':'.$vp;
						}
						$prop = '{'.implode(',',$prop).'}';
						if(isset($r['mask']['datepicker']['timeFormat'])) {
							$_CFG['fileIncludeOption']['datepicker'] = 2;
							$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datetimepicker('.$prop.')}';
						}
						else {
							$_CFG['fileIncludeOption']['datepicker'] = 1;
							$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datepicker('.$prop.')}';
						}
						$_tpl['onload'] .= ' dp_'.$k.'(); ';
					}
					
					$texthtml .= '<input type="text" name="'.$k.'" value="'.$temp.'" class="dateinput"/>';
				}
				else {
				
					// Тип поля
					if(!is_array($r['value'])) {
						if($r['fields_type'] =='int' and $r['value']) {
							$temp = explode('-',date('Y-m-d-H-i-s',$r['value']));
						}
						elseif($r['fields_type'] =='timestamp' and $r['value'] and is_string($r['value'])){
							$temp = sscanf($r['value'], "%d-%d-%d %d:%d:%d");//2007-09-11 10:16:15
						}
						else{
							$temp = array_fill(0,6,0);
							//$temp = array(date('Y'),date('n'),date('d'),date('H'));
						}
					}else
						$temp = $r['value'];
					$r['value']= array();

					// формат для даты
					preg_match_all('/[A-Za-z]/', $r['mask']['format'], $matches);
					$format = $matches[0];
					foreach($format as $item_date)
					{
						// год
						if($item_date == 'Y' || $item_date == 'y')
						{
							$r['value']['year'] = array('name'=>static_main::m('year_name'), 'css'=>'year','value'=>$temp[0]);// ГОД
							$temp[0] = (int)$temp[0]; 
							$r['value']['year']['item'][0] = array('#id#'=>0, '#name#'=>'--');

							//значения по умолчанию
							if(!isset($r['mask']['year_back'])) $r['mask']['year_back'] = 2;
							if(!isset($r['mask']['year_up'])) $r['mask']['year_up'] = 3;
							for($i=((int)date('Y')-($r['mask']['year_back']));$i<=((int)date('Y')+($r['mask']['year_up']));$i++)
								$r['value']['year']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);							
						}
						// месяц
						if($item_date == 'm' || $item_date == 'n' || $item_date == 'M' || $item_date == 'F')
						{
							$r['value']['month'] = array('name'=>static_main::m('month_name'), 'css'=>'month','value'=>(int)$temp[1]);// Месяц
							$r['value']['month']['item'][0] = array('#id#'=>0, '#name#'=>'--');
							foreach(static_main::m('month') as $kr=>$td) {
								$kr = (int)$kr;
								$r['value']['month']['item'][$kr] = array('#id#'=>$kr, '#name#'=>$td);
							}
						}
						// день
						if($item_date == 'd' || $item_date == 'j')
						{
							$r['value']['day'] = array('name'=>static_main::m('day_name'), 'css'=>'day','value'=>(int)$temp[2]);// День
							$r['value']['day']['item'][0] = array('#id#'=>0, '#name#'=>'--');
							for($i=1;$i<=31;$i++)
								$r['value']['day']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);						
						}
						// час
						if($item_date == 'G' || $item_date == 'g' || $item_date == 'H' || $item_date == 'h')
						{
							$r['value']['hour'] = array('name'=>static_main::m('hour_name'), 'css'=>'hour','value'=>$temp[3]);// Час
							for($i=0;$i<=23;$i++)
								$r['value']['hour']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);
						}
						// минуты
						if($item_date == 'i')
						{
							$r['value']['minute'] = array('name'=>static_main::m('minute_name'), 'css'=>'minute','value'=>$temp[4]);// Minute
							for($i=1;$i<=60;$i++)
								$r['value']['minute']['item'][$i] = array('#id#'=>$i, '#name#'=>$i);
						}
						// секунды
						if($item_date == 's')
						{
							$r['value']['sec'] = array('name'=>static_main::m('sec_name'), 'css'=>'sec','value'=>$temp[5]);
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
			elseif($r['type']=='file' and isset($r['mask']['async'])) {
				
				if(isset($r['default']) and $r['default']!='' and $r['value']=='') {
					$r['value'] = $r['default'];
					$r['att_type']='img';
				}

				if($r['caption']==1)
					$texthtml .= '';
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='img') {
					$H = '50';
					if(isset($r['mask']['height']))
						$H = $r['mask']['height'];
					$texthtml .= '<div class="wep_thumb">
						<a rel="fancy" href="/'.$r['value'].'" target="_blank" class="fancyimg">
							<img src="/'.$r['value'].'" alt="img" class="attach" style="height:'.$H.'px;"/>
						</a>';
					if(isset($r['img_size']))
						$texthtml .= '<div class="wep_thumb_comment">Размер '.$r['img_size'][0].'x'.$r['img_size'][1].'</div>';
					$texthtml .= '</div>';
					if(isset($r['thumb']) and $r['mask']['thumb'])
						foreach($r['thumb'] as $thumb) {
							$texthtml .= '<div class="wep_thumb">
								<a rel="fancy" href="/'.$thumb['value'].'?size='.$thumb['filesize'].'" target="_blank" class="fancyimg">
									<img src="/'.$thumb['value'].'?size='.$thumb['filesize'].'" alt="img" class="attach" style="height:'.$H.'px;"/>
								</a>';
							if($thumb['w']) $texthtml .= '<div class="wep_thumb_comment">Эскиз размером '.$thumb['w'].'x'.$thumb['h'].'</div>';
							$texthtml .= '</div>';
						}
					$_CFG['fileIncludeOption']['fancybox'] = 1;
				}
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='swf')
					$texthtml .= '<object type="application/x-shockwave-flash" data="/'.$r['value'].'" height="50" width="200"><param name="movie" value="/'.$r['value'].'" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>';
				elseif(!is_array($r['value']) and $r['value']!=''){
					$texthtml .= '<span style="color:green"><a href="/'.$r['value'].'" target="_blank"> фаил загружен</a></span><br/>';
				}

				
				$texthtml .= '<div class="form-value divinputfile">';
				$texthtml .= '';
				/*$texthtml .= '<input type="file" name="'.$k.'" '.$attribute.'/><span class="fileinfo"></span>';
				if($r['del']==1 and $r['value']!='')
					$texthtml .= '<div class="filedelete"><lable for="'.$k.'_del">Удалить?&#160;</lable><input type="checkbox" name="'.$k.'_del" id="'.$k.'_del" value="1"/></div>';*/

				$texthtml .= '</div>';
			}
			elseif($r['type']=='file') {
				
				if(isset($r['default']) and $r['default']!='' and $r['value']=='') {
					$r['value'] = $r['default'];
					$r['att_type']='img';
				}

				if($r['caption']==1)
					$texthtml .= '';
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='img') {
					$css = '';
					if(isset($r['mask']['width']) and $r['mask']['width'])
						$css .= 'width:'.$r['mask']['width'].'px;';
					if(isset($r['mask']['height']) and $r['mask']['height'])
						$css = 'height:'.$r['mask']['height'].'px;';
					else
						$css = 'height:50px;';

					$texthtml .= '<div class="wep_thumb">
						<a rel="fancy" href="/'.$r['value'].'" target="_blank" class="fancyimg">
							<img src="/'.$r['value'].'" alt="img" class="attach" style="'.$css.'"/>
						</a>';
					if(isset($r['img_size']))
						$texthtml .= '<div class="wep_thumb_comment">Размер '.$r['img_size'][0].'x'.$r['img_size'][1].'</div>';
					$texthtml .= '</div>';
					if(isset($r['thumb']) and $r['thumb'])
						foreach($r['thumb'] as $thumb) {
						$texthtml .= '<div class="wep_thumb">
							<a rel="fancy" href="/'.$thumb['value'].'?size='.$thumb['filesize'].'" target="_blank" class="fancyimg">
								<img src="/'.$thumb['value'].'?size='.$thumb['filesize'].'" alt="img" class="attach" style="'.$css.'"/>
							</a>';
						if($thumb['w']) $texthtml .= '<div class="wep_thumb_comment">Эскиз размером '.$thumb['w'].'x'.$thumb['h'].'</div>';
						$texthtml .= '</div>';
					}
					$_CFG['fileIncludeOption']['fancybox'] = 1;
				}
				elseif(!is_array($r['value']) and $r['value']!='' and $r['att_type']=='swf')
					$texthtml .= '<object type="application/x-shockwave-flash" data="/'.$r['value'].'" height="50" width="200"><param name="movie" value="/'.$r['value'].'" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>';
				elseif(!is_array($r['value']) and $r['value']!=''){
					$texthtml .= '<span style="color:green"><a href="/'.$r['value'].'" target="_blank"> фаил загружен</a></span><br/>';
				}

				$texthtml .= '<div class="form-value divinputfile">';
				$texthtml .= '<input type="file" name="'.$k.'" '.$attribute.'/><span class="fileinfo"></span>';

				if($r['del']==1 and $r['value']!='')
					$texthtml .= '<div class="filedelete"><lable for="'.$k.'_del">Удалить?&#160;</lable><input type="checkbox" name="'.$k.'_del" id="'.$k.'_del" value="1"/></div>';

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
			elseif($r['type']=='password' and isset($r['mask']['password']) and $r['mask']['password']=='re') {
				$texthtml .= '<div class="form-value"><input type="password" name="'.$k.'" value="" onkeyup="checkPass("'.$k.'")" '.$attribute.'/>
					<div class="dscr">Введите пароль</div>
					<input type="password" name="re_'.$k.'" value="" onkeyup="checkPass("'.$k.'")" '.$attribute.'/>
					<div class="dscr">Чтобы избежать ошибки повторите ввод пароля</div></div>';
			}
			elseif($r['type']=='password' and isset($r['mask']['password']) and $r['mask']['password']=='change') {
				$texthtml .= '<div class="form-value">
					<input type="password" name="'.$k.'_old" value=""/><div class="dscr">Введите старый пароль</div>
					<input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password"/>
					<div class="passnewdesc" onclick="password_new()">Отобразить символы/скрыть</div></div>';
			}	
			elseif($r['type']=='password_new' or $r['type']=='password') {
				$texthtml .= '<div class="form-value"><input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password" '.$attribute.'/>
						<div class="passnewdesc" onclick="password_new(this)">Отобразить/скрыть символы</div></div>';
			}
			/*elseif($r['type']=='password' and !$r['readonly']) {
				$texthtml .= '<div class="form-value"><input type="text" id="'.$k.'" name="'.$k.'" value="'.$r['value'].'" style="width:55%;float:left;background:#E1E1A1;" readonly="readonly"/>
							<div style="width:40%;float:right;">
								<img src="_wep/cdesign/default/img/aprm.gif" style="width:18px;cursor:pointer;" onclick="if(confirm(\'Вы действительно хотите изменить пароль?\')) $(\'#'.$k.'\').val(hex_md5(\''.$r['md5'].'\'+$(\'#a_'.$k.'\').val()));" alt="Сгенерировать пароль в формате MD5" title="Сгенерировать пароль в формате MD5"/>
								<input type="text" id="a_'.$k.'" name="a_'.$k.'" value="" style="width:80%;vertical-align:top;"/>
							</div></div>';
				$_CFG['fileIncludeOption']['md5'] = 1;
			}*/
			elseif($r['type']=='color') {
				$_tpl['styles']['../_script/script.jquery/colorpicker/css/colorpicker'] = true;
	//			$_tpl['styles']['colorpicker/css/layout'] = true;

				$_tpl['script']['script.jquery/colorpicker/js/colorpicker'] = true;
				$_tpl['script']['script.jquery/colorpicker/js/eye'] = true;
				$_tpl['script']['script.jquery/colorpicker/js/utils'] = true;
				$_tpl['script']['script.jquery/colorpicker/js/layout'] = true;
				$_tpl['onload'] .= ' jQuery(\'#tr_'.$k.' div.colorPicker input\').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						$(el).val(\'#\'+hex);
						$(el).ColorPickerHide();
					},
					onBeforeShow: function () {
						$(this).ColorPickerSetColor(this.value.substring(1));
					}
				});';
				$texthtml .= '<div class="form-value colorPicker"><input type="text" name="'.$k.'" value="'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'" '.$attribute.'/></div>';
			}
			/*elseif($r['type']=='phone') {
				$texthtml .= '<div class="form-value" style="font-size:1.2em;">+7(<input type="int" name="'.$k.'[0]" value="" maxlength="3" style="width:27px;"/>) <input type="int" name="'.$k.'[1]" value="" maxlength="3" style="width:27px;"/>
				<input type="int" name="'.$k.'[2]" value="" maxlength="4" style="width:40px;"/>
				</div>';

			}*/
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
	if(!is_null($val)) {
		if(!is_array($val)) 
			$val = array($val=>true);
	}
	return selectitem2($data,$val,$flag);
	$texthtml = '';
	if(is_array($data) and count($data))
		foreach($data as $r) {
			//_substr($r['#name#'],0,60).(_strlen($r['#name#'])>60?'...':'')
			//$r['#name#'] = str_repeat(" -", $flag).' '.$r['#name#'];
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

function selectitem2($data,$val=NULL,$flag=0,&$openG=false) {
	$texthtml = ''."\n";
	if(is_array($data) and count($data))
		foreach($data as $r) {
			//_substr($r['#name#'],0,60).(_strlen($r['#name#'])>60?'...':'')
			if(isset($r['#item#']) and count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0) {
				if($flag>0)
					$r['#name#'] = str_repeat("&#160;&#160;", $flag).' '.$r['#name#'];
				if($openG)
					$texthtml .= '</optgroup>'."\n";
				$texthtml .= '<optgroup label="'.$r['#name#'].'">'."\n";
				$openG = true;
			}
			else {
				if($flag>0)
					$r['#name#'] = str_repeat("&#160;&#160;", $flag).' '.$r['#name#'];
				$sel = '';
				if(!is_null($val)) {
					if(isset($val[$r['#id#']]))
						$sel = 'selected="selected"';
				}
				elseif(isset($r['#sel#'])) {
					if($r['#sel#'])
						$sel = 'selected="selected"';
				}
					
				$texthtml .= "\t".'<option value="'.$r['#id#'].'" '.$sel.'>'.$r['#name#'].'</option>'."\n";
			}

			if(isset($r['#item#']) and count($r['#item#']))
				$texthtml .= selectitem2($r['#item#'],$val,($flag+1),$openG);//.'&#160;--'

			if(isset($r['#item#']) and count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0 and !$flag) {
				$texthtml .= '</optgroup>'."\n";
				$openG=false;
			}
		}
	return $texthtml;
}
