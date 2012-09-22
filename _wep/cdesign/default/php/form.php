<?php
function tpl_form(&$data, $tabs = array()) {
	global $_CFG, $_tpl;

	$attr = array();
	if(isset($data['_*features*_'])) {
		$attr = $data['_*features*_'];
	}
	if(!isset($attr['id']))
		$attr['id'] = 0;
	$texthtml = '';
	$_CFG['fileIncludeOption']['form'] = 1;

	// TABS
	$flagTabs = null;
	if(count($tabs)) {
		$i=0;
		$tempTabs = array();
		$tabMenu = '<ul>';
		foreach($tabs as $kS=>$rS) {
			if(is_array($rS)) {
				$tabMenu .= '<li><a href="#weptabs'.$i.'">'.$kS.'</a></li>';
				$tempTabs[$i] = array_flip($rS);
				$i++;
			}
		}
		$tabMenu .= '</ul>';
		$flagTabs = 0;
	}

	$tagStatus = false;
	foreach($data as $k=>$r) {
		if(!isset($r['type'])) continue;

		if(!is_null($flagTabs)) {

			if($tagStatus and (isset($tempTabs[$flagTabs][$k]) or !isset($tempTabs[($flagTabs-1)][$k])) ) {
				$texthtml .= '</div>';
				$tagStatus = false;
			}

			if(!$tagStatus and isset($tempTabs[$flagTabs][$k])) {
				$texthtml .= $tabMenu.'<div id="weptabs'.$flagTabs.'">';
				$tagStatus = true;
				$flagTabs++;
				$tabMenu = '';
			}
		}

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
				$texthtml .= htmlentities($r['onclick'],ENT_COMPAT,$_CFG['wep']['charset']);
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
			$texthtml .= '<div class="form-info">'.$r['caption'].'</div>';
		}
		elseif($r['type']=='html') {
			$texthtml .= '<div class="form-value">'.$r['value'].'</div>';
		}
		elseif($r['type']=='cf_fields') {
			include_once(dirname(__FILE__).'/cffields.php');
			$texthtml .= '<div class="form-value">'.tpl_cffields($k,$r).'</div>';
		}
		elseif($r['type']=='hidden') {
			if(is_array($r['value']))
				$r['value'] = implode('|',$r['value']);
			$r['value'] = htmlentities($r['value'],ENT_QUOTES,$_CFG['wep']['charset']);
			$texthtml .= '<input type="'.$r['type'].'" name="'.$k.'" value="'.$r['value'].'" id="'.((isset($r['id']) and $r['id'])?$r['id']:$k).'"/>';
		}
		else {
			$attribute = '';

			$CAPTION = $r['caption'];
			if(isset($r['mask']['min']) and $r['mask']['min']) {
				$CAPTION .= '<span class="form-requere">*</span>';
				if($r['type']!='ckedit' and !($r['type']=='password' and isset($r['mask']['password']) and $r['mask']['password']=='re')) // в CKEDITORE глюк из за этого
					$attribute .= ' required="required"';
			}
			elseif(isset($r['mask']['min2']) and $r['mask']['min2']) {
				$CAPTION .= '<span  class="form-requere" data-text="'.$r['mask']['min2'].'">**</span>';
			}
			if($r['type']=='ckedit' and static_main::_prmUserCheck(1))
				$CAPTION .= '<input type="checkbox" onchange="SetWysiwyg(this)" name="'.$k.'_ckedit" style="width:13px;vertical-align: bottom;margin: 0 0 0 5px;"/>';

			if($r['type']!='checkbox') {
				$texthtml .= '<div class="form-caption">'.$CAPTION.'</div>';
			}

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
				$attribute .= ' onchange="'.htmlentities($r['onchange'],ENT_COMPAT,$_CFG['wep']['charset']).'"';

			if(isset($r['error']) and is_array($r['error']) and count($r['error']))
				$texthtml .= '<div class="caption_error">['.implode(' ',$r['error']).']</div>';

			if($r['type']=='textarea') {
				if(isset($r['mask']['max']) and $r['mask']['max']) $attribute .= ' maxlength="'.$r['mask']['max'].'"';
				$texthtml .= '<div class="form-value"><textarea name="'.$k.'" onkeyup="textareaChange(this)" rows="10" cols="80" '.$attribute.'>'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea></div>';
			}
			elseif($r['type']=='ckedit') {
				$_tpl['script'][$_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js'] = 1;

				if(isset($r['mask']['max']) and $r['mask']['max']) $attribute .= ' maxlength="'.$r['mask']['max'].'"';

				//http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html
					$ckedit = $r['paramedit'];
					if(!isset($ckedit['skin']))
						$ckedit['skin']='\'kama\'';
					if(!isset($ckedit['width']))
						$ckedit['width'] = '\'100%\'';
					if(!isset($ckedit['height']))
						$ckedit['height'] = '450';
					if(!isset($ckedit['toolbarStartupExpanded']))
						$ckedit['toolbarStartupExpanded']='true';
					if(!isset($ckedit['baseHref']))
						$ckedit['baseHref'] = '\''.$_CFG['_HREF']['BH'].'\'';
					if(isset($ckedit['toolbar'])) {
						if(isset($_CFG['ckedit']['toolbar'][$ckedit['toolbar']]))
							$ckedit['toolbar'] = $_CFG['ckedit']['toolbar'][$ckedit['toolbar']];
						else
							$ckedit['toolbar'] = '\''.$ckedit['toolbar'].'\'';
					} else
						$ckedit['toolbar'] = $_CFG['ckedit']['toolbar']['Full'];
					if(!isset($ckedit['uiColor']))
						$ckedit['uiColor'] = '\'#9AB8F3\'';
					if(!isset($ckedit['language']))
						$ckedit['language'] = '\'ru\'';
					if(!isset($ckedit['enterMode']))
						$ckedit['enterMode'] = 'CKEDITOR.ENTER_BR';
					if(!isset($ckedit['shiftEnterMode']))
						$ckedit['shiftEnterMode'] = 'CKEDITOR.ENTER_P';
					if(!isset($ckedit['contentsCss']))
						$ckedit['contentsCss'] = '"/_design/default/style/style.css"';
					$ckedit['autoUpdateElement'] = 'true';

					$fckscript = 'function cke_'.$k.'() { if(typeof CKEDITOR.instances.id_'.$k.' == \'object\'){CKEDITOR.instances.id_'.$k.'.destroy(true);} editor_'.$k.' = CKEDITOR.replace( \'id_'.$k.'\',{';

					foreach($ckedit as $kc=>$rc)
					{
						if(!is_array($rc))
							$fckscript .= $kc.' : '.$rc.',';
					}
					$fckscript .= '\'temp\' : \'temp\' });';

					if(isset($ckedit['CKFinder'])) {
						$_tpl['script'][$_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js'] = 1;

						$fckscript = ' function ckf_'.$k.'() { CKFinder.setupCKEditor(editor_'.$k.',\'/'.$_CFG['PATH']['WSWG'].'ckfinder/\');} '.$fckscript;
						$fckscript .= ' ckf_'.$k.'();';
						
						if(isset($ckedit['CKFinder']['allowedExtensions']) and $_SESSION)
							$_SESSION['wswg']['AE'] = $ckedit['CKFinder']['allowedExtensions'];
					}
					$fckscript .= '}';
					//if(!isset($fields[$k.'_ckedit']['value']) or $fields[$k.'_ckedit']['value']=='' or $fields[$k.'_ckedit']['value']=='1')
						$_tpl['onload'] .= $fckscript.' cke_'.$k.'();';

				$texthtml .= '<div class="form-value ckedit-value"><textarea id="id_'.$k.'" name="'.$k.'" rows="10" cols="80" '.$attribute.'>'.htmlspecialchars((string)$r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea></div>';
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

				if(!isset($r['valuelist']) or !count($r['valuelist'])) {
					if($r['value'])
						$attribute .= ' checked="checked"';
					$texthtml .= '<label class="form-value checkbox-value">
						<input type="'.$r['type'].'" name="'.$k.'" value="1" '.$attribute.'/>
						<div class="form-caption">'.$CAPTION.'</div>
					</label>';
				}
				else {
					$texthtml .= '<div class="form-caption">'.$CAPTION.'</div>
						<div class="form-value checkbox-value checkbox-valuelist">';
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
						$texthtml .= '<label class="boxtitle"><input type="'.$r['type'].'" name="'.$k.'['.$id.']" value="'.$id.'" class="radio" '.$attribute;
						if($sel)
							$texthtml .= ' checked="checked"';
						if($readonly)
							$texthtml .= ' readonly="readonly"';
						$texthtml .= '/>'.$name.'</label>';
					}
					$texthtml .= '</div>';
				}
				// end checkbox
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
					$r['csscheck'] = ($value_2?'accept':'reject');
					$texthtml .= '<div class="form-value ajaxlist">
						<input type="text" name="'.$k.'_2['.$i.']" value="'.$value_2.'" placeholder="'.$r['placeholder'].'" class="'.$r['csscheck'].'" autocomplete="off" 
							onfocus="show_hide_label(this,\''.$k.'\',1,\''.$i.'\')" 
							onblur="show_hide_label(this,\''.$k.'\',0,\''.$i.'\')"
							onkeyup="return ajaxlistOnKey(event,this,\''.$k.'\',\''.$i.'\')"/>
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
				$r['csscheck'] = ($r['value_2']?'accept':'reject');		
				$serl = serialize($r['listname']);
				$texthtml .= '<div class="form-value ajaxlist">
					<input type="text" name="'.$k.'_2" value="'.strip_tags ($r['value_2']).'" placeholder="'.$r['placeholder'].'" class="'.$r['csscheck'].'" autocomplete="off" 
						onfocus="show_hide_label(this,\''.$k.'\',1)" 
						onblur="show_hide_label(this,\''.$k.'\',0)" 
						onkeydown="return ajaxlistOnKey(event,this,\''.$k.'\')"/>
					<div id="ajaxlist_'.$k.'" style="display:none;" onfocus="chFocusList(0)" onblur="chFocusList(1)">не найдено</div>

					<input type="hidden" name="'.$k.'" value="'.$r['value'].'" '.$attribute.'/>
				</div>
				<input type="hidden" name="hsh_'.$k.'" value="'.md5($serl.$_CFG['wep']['md5']).'"/>
				<input type="hidden" name="srlz_'.$k.'" value="'.htmlspecialchars($serl,ENT_QUOTES,$_CFG['wep']['charset']).'"/>';
			}
			elseif($r['type']=='list' and !$r['readonly']) {
				include_once(dirname(__FILE__).'/formSelect.php');

				$texthtml .= '<div class="form-value">';
				if(isset($r['multiple'])) {
					if(!isset($r['mask']['size'])) $r['mask']['size'] = 10;
					if(!isset($r['mask']['maxarr'])) $r['mask']['maxarr'] = 0;
					if(!isset($r['mask']['minarr'])) $r['mask']['minarr'] = 0;
				}

				if(isset($r['multiple']) and $r['multiple']==2) {
					$texthtml .= '<select multiple="multiple" name="'.$k.'[]" class="multiple" size="'.$r['mask']['size'].'" '.$attribute;
					$texthtml .= '>'.tpl_formSelect($r['valuelist'],$r['value']).'</select>';
					$_CFG['fileIncludeOption']['multiple'] = 2;
				}
				elseif(isset($r['multiple']) and $r['multiple']==3) {
					if(!is_array($r['value']) or !count($r['value'])) $r['value'] = array('');
					$cnt = 0;
					foreach($r['value'] as $kval=>$rval) {
						$cnt++;
						$text2 = '';
						if(isset($r['mask']['keylist']) and $r['mask']['keylist'])
							$text2 = '<select class="ilist-val" onchange="wep.form.iListRev(this,\''.$k.'\')">'.tpl_formSelect($r['valuelist'],$kval).'</select>
								<input class="ilist-key" type="text" value="'.$rval.'" name="'.$k.'['.$kval.']"/>';
						else
							$text2 = '<input class="ilist-key" type="text" value="'.$kval.'" onkeyup="wep.form.iList(this,\''.$k.'\')"/>
								<select class="ilist-val" name="'.$k.'['.$kval.']" '.$attribute.'>'.tpl_formSelect($r['valuelist'],$rval).'</select>';
						$texthtml .= '<div class="ilist">
							'.$text2.'
							<span class="ilistsort" title="Переместить"></span>
							<span class="ilistdel" title="Удалить"'.(($cnt==1 and $r['mask']['minarr'])?' style="display:none;"':'').' onclick="wep.form.iListdel(this);"></span>
						</div>';
						if($r['mask']['maxarr'] and $cnt==$r['mask']['maxarr']) break;
					}
					$texthtml .= '<span class="ilistmultiple" onclick="wep.form.iListCopy(this,\'#tr_'.$k.' div.ilist\','.$r['mask']['maxarr'].')" title="Добавить '.$r['caption'].'">'.($r['mask']['maxarr']-count($r['value'])).'</span>';
					$_tpl['onload'] .= 'wep.form.iListsort("#tr_'.$k.' .form-value");';
					$_CFG['fileIncludeOption']['jquery-ui'] = 1;
				}
				elseif(isset($r['multiple']) and $r['multiple']) {
					
					$texthtml .= '<select multiple="multiple" name="'.$k.'[]" class="small" size="'.$r['mask']['size'].'" '.$attribute;
					$texthtml .= '>'.tpl_formSelect($r['valuelist'],$r['value']).'</select>';
				} 
				else {
					$texthtml .= '<select name="'.$k.'" '.$attribute;
					$texthtml .= '>'.tpl_formSelect($r['valuelist'],$r['value']).'</select>';
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
				$texthtml .= '<div class="form-value dateinput">';
				$temp = '';
				if(isset($r['mask']['view']) and $r['mask']['view']=='split') {
					include_once(dirname(__FILE__).'/formSelect.php');
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
							if(!isset($r['mask']['year_back'])) $r['mask']['year_back'] = -2;
							if(!isset($r['mask']['year_up'])) $r['mask']['year_up'] = 3;
							for($i=((int)date('Y')+($r['mask']['year_back']));$i<=((int)date('Y')+($r['mask']['year_up']));$i++)
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
						$texthtml .= '<div class="dateselect '.$row['css'].'"><span class="name">'.$row['name'].'</span><select name="'.$k.'[]" '.$attribute.'>'.tpl_formSelect($row['item'],$row['value']).'</select></div>';
					}
				}	
				else {
					$time=NULL;
					// Тип поля
					if($r['value']) {
						if($r['fields_type']  =='int') {
							$time = $r['value'];
							$temp = date($r['mask']['format'],$r['value']);
						}
						else {//$r['fields_type'] =='timestamp'
							$fs = explode(' ', $r['value']);
							$f = explode('-', $fs[0]);
							$s = explode(':', $fs[1]);
							$time = $temp = mktime($s[0], $s[1], $s[2], $f[1], $f[2], $f[0]);
							
							if($r['mask']['time'])
								$r['mask']['format'] = $r['mask']['format'].' '.$r['mask']['time'];
							
							$temp = date($r['mask']['format'],$temp);
						}
					}

					// текстовый формат ввода данных
					if(!isset($r['mask']['view']) or isset($r['mask']['datepicker'])) {
					//if(isset($r['mask']['datepicker'])) {
						if(!isset($r['mask']['datepicker']))
							$r['mask']['datepicker'] = array();
						elseif(!is_array($r['mask']['datepicker']))
							$r['mask']['datepicker'] = array('dateFormat'=>$r['mask']['datepicker']);

						if(!isset($r['mask']['datepicker']['dateFormat']))
							$r['mask']['datepicker']['dateFormat']='\'yy-mm-dd\'';

						if(strpos($r['mask']['format'],'H:i:s')!==false or $r['mask']['datepicker']['timeFormat']===true)
							$r['mask']['datepicker']['timeFormat'] = '\' hh:mm:ss\'';


						global $_tpl;
						$prop = array();
						if(!is_null($time))
							$r['mask']['datepicker']['defaultDate'] = 'new Date('.date('Y,m-1,d',$time).')';
						if(!isset($r['mask']['datepicker']['maxDate']) and isset($r['mask']['year_up'])) {
							$r['mask']['datepicker']['maxDate'] = '\''.$r['mask']['year_up'].'y\'';
						}
						if(!isset($r['mask']['datepicker']['minDate']) and isset($r['mask']['year_back'])) {
							$r['mask']['datepicker']['minDate'] = '\''.$r['mask']['year_back'].'y\'';
						}
						foreach ($r['mask']['datepicker'] as $kp => $vp) {
							if($vp)
								$prop[] = $kp.':'.$vp;
						}
						$prop = '{'.implode(',',$prop).'}';
						if($r['mask']['datepicker']['timeFormat']) {
							$_CFG['fileIncludeOption']['datepicker'] = 2;
							$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datetimepicker('.$prop.')}';
						}
						else {
							$_CFG['fileIncludeOption']['datepicker'] = 1;
							$_tpl['script']['dp_'.$k] = 'function dp_'.$k.'() { $("input[name='.$k.']").datepicker('.$prop.')}';
						}
						$_tpl['onload'] .= ' dp_'.$k.'(); ';
					}
					
					$texthtml .= '<input type="text" name="'.$k.'" value="'.$temp.'"/>';
				}

				$texthtml .= '</div>';
			}
			elseif($r['type']=='captcha') {
				$texthtml .= '<div class="form-value secret">
						<div class="inline"><input type="text" name="'.$k.'" value="'.$r['value'].'" maxlength="5" size="10" class="secret" autocomplete="off"/></div>
						<div class="secretimg inline"><img src="'.$r['src'].'" class="i_secret" id="captcha" alt="CARTHA"/></div>
						<div class="secretinfo inline">
							<a class="i-reload">&#160;&#160;&#160;&#160;&#160;Обновить картинку</a>';
						switch ($r['mask']['dif']) {
							case 0:
								$texthtml .= '<a class="i-help">&#160;&#160;&#160;&#160;&#160;Только цифры</a>';
							break;
							case 1:
								$texthtml .= '<a class="i-help">&#160;&#160;&#160;&#160;&#160;Только цифры и заглавные карилица</a>';
							break;
							case 2:
								$texthtml .= '<a class="i-help">&#160;&#160;&#160;&#160;&#160;Только цифры и карилица</a>';
							break;
							default:
								$texthtml .= '<a class="i-help">&#160;&#160;&#160;&#160;&#160;Только цифры, буквы</a>';
						}
				$texthtml .= '</div>
					</div>';
				$_tpl['onload'] .= ' jQuery(\'form a.i-reload\').click(function(){reloadCaptcha(\''.$k.'\');}); jQuery(\'#tr_captcha input\').click(function(){wep.setCookie(\'testtest\',1);});';
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
							<img src="/'.$r['value'].'" alt="img" class="attach" style="'.$css.'" id="'.$k.'_temp_upload_img"/>
						</a>';
					if(isset($r['img_size']))
						$texthtml .= '<div class="wep_thumb_comment">Размер '.$r['img_size'][0].'x'.$r['img_size'][1].'</div>';
					$texthtml .= '</div>';
					if(isset($r['thumb']) and $r['thumb'])
						foreach($r['thumb'] as $thumb) {
							if(!$thumb['pref']) continue;
							$texthtml .= '<div class="wep_thumb">
								<a rel="fancy" href="/'.$thumb['value'].'" target="_blank" class="fancyimg">
									<img src="/'.$thumb['value'].'" alt="img" class="attach" style="'.$css.'"/>
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
				
				if(isset($r['mask']['swf_uploader'])) {
					$texthtml .= '
						<div class="fuploader">Загрузка фаила<input type="file" name="'.$k.'" id="'.$k.'_uploader" '.$attribute.'/></div><span class="fileinfo"></span>
						<div id="'.$k.'_notice_swf_uploader"></div>';

					$_tpl['script']['SWFUpload/swfupload_fp10/swfupload'] = 1;
					$_tpl['onload'] .= 'wep.swfuploader.bindSWFUpload({button_placeholder_id:"'.$k.'_uploader", field_name:"'.$k.'"});';
					//SESSID = "'.session_id().'"; 
				}
				else {
					$texthtml .= '<input type="file" name="'.$k.'" '.$attribute.'/><span class="fileinfo"></span>';
				}


				if(isset($r['del']) and $r['del']==1 and $r['value']!='')
					$texthtml .= '<label class="filedelete">Удалить?&#160;<input type="checkbox" name="'.$k.'_del" value="1"/></label>';

				$texthtml .= '</div>';
			}
			elseif($r['type']=='password' and isset($r['mask']['password']) and $r['mask']['password']=='re') 
			{
				$texthtml .= '<div class="form-value">
					<span class="labelInput">Введите пароль</span>
					<input type="password" name="'.$k.'" value="" onkeyup="checkPass("'.$k.'")" class="password" '.$attribute.'/>
					<span class="labelInput">Повторите ввод пароля</span>
					<input type="password" name="re_'.$k.'" value="" onkeyup="checkPass("'.$k.'")" class="password" '.$attribute.'/>
					</div>';
			}
			elseif($r['type']=='password' and isset($r['mask']['password']) and $r['mask']['password']=='change') {
				$texthtml .= '<div class="form-value">
					<span class="labelInput">Введите старый пароль</span>
					<input type="password" name="'.$k.'_old" value="" class="password"/>
					<span class="labelInput">Введите новый пароль</span>
					<input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password"/>
					<div class="passnewdesc" onclick="passwordShow(this)">Отобразить символы/скрыть</div></div>';
			}	
			elseif($r['type']=='password_new' or $r['type']=='password') 
			{
				$texthtml .= '<div class="form-value"><input type="password" name="'.$k.'" '.($attr['id']?'':'value="'.$r['value'].'"').' class="password" '.$attribute.'/>
						<div class="passnewdesc" onclick="passwordShow(this)">Отобразить/скрыть символы</div></div>';
			}
			/*elseif($r['type']=='password' and !$r['readonly']) {
				$texthtml .= '<div class="form-value"><input type="text" id="'.$k.'" name="'.$k.'" value="'.$r['value'].'" style="width:55%;float:left;background:#E1E1A1;" readonly="readonly"/>
							<div style="width:40%;float:right;">
								<img src="_wep/cdesign/default/img/aprm.gif" style="width:18px;cursor:pointer;" onclick="if(confirm(\'Вы действительно хотите изменить пароль?\')) $(\'#'.$k.'\').val(hex_md5(\''.$r['md5'].'\'+$(\'#a_'.$k.'\').val()));" alt="Сгенерировать пароль в формате MD5" title="Сгенерировать пароль в формате MD5"/>
								<input type="text" id="a_'.$k.'" name="a_'.$k.'" value="" style="width:80%;vertical-align:top;"/>
							</div></div>';
				$_CFG['fileIncludeOption']['md5'] = 1;
			}*/
			elseif($r['type']=='color') 
			{
				$_tpl['styles']['../_script/script.jquery/colorpicker/css/colorpicker'] = 1;
	//			$_tpl['styles']['colorpicker/css/layout'] = true;

				$_tpl['script']['script.jquery/colorpicker/js/colorpicker'] = 1;
				$_tpl['script']['script.jquery/colorpicker/js/eye'] = 1;
				$_tpl['script']['script.jquery/colorpicker/js/utils'] = 1;
				$_tpl['script']['script.jquery/colorpicker/js/layout'] = 1;
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
			elseif(isset($r['multiple']) AND $r['multiple'] and !$r['readonly']) {
				if(!is_array($r['value']) or !count($r['value'])) $r['value'] = array('');
				if(isset($r['mask']['max']) and $r['mask']['max']) $attribute .= ' maxlength="'.$r['mask']['max'].'"';
				if(!isset($r['mask']['maxarr'])) $r['mask']['maxarr'] = 10;
				if(!isset($r['keytype'])) $r['keytype'] = 'text';
				$cnt = 0;
				$texthtml .= '<div class="form-value">';
					foreach($r['value'] as $kval=>$rval) {
						$cnt++;
						$texthtml .= '<div class="ilist">
						<input class="ilist-key" type="'.$r['keytype'].'" value="'.htmlspecialchars($kval,ENT_QUOTES,$_CFG['wep']['charset']).'" maxlength="20" onkeyup="wep.form.iList(this,\''.$k.'\')"/>
						<input class="ilist-val" type="text" name="'.$k.'['.$kval.']" value="'.htmlspecialchars($rval,ENT_QUOTES,$_CFG['wep']['charset']).'" '.$attribute.'/>
						<span'.($cnt==1?' style="display:none;"':'').' class="ilistdel" onclick="wep.form.iListdel(this);" title="Удалить"></span></div>';
						if($cnt==$r['mask']['maxarr']) break;
					}
					$texthtml .= '<span class="ilistmultiple" onclick="wep.form.iListCopy(this,\'#tr_'.$k.' div.ilist\','.$r['mask']['maxarr'].')" title="Добавить '.$r['caption'].'">'.($r['mask']['maxarr']-count($r['value'])).'</span>';
				$texthtml .= '</div>';
			}
			else 
			{
				if(isset($r['isFloat'])) 
				{
					$maskFloat = explode(',', $r['mask']['width']);
					if(!isset($maskFloat[1])) $maskFloat[1] = 0;
					$_tpl['script']['script.jquery/jquery.numberMask'] = 1;
					$_tpl['onload'] .= '$("input[name='.$k.']").numberMask({type:"float", beforePoint:'.$maskFloat[0].', afterPoint:'.$maskFloat[1].', defaultValueInput:"0", decimalMark:"."});';
				}
				elseif(isset($r['isInt'])) 
				{
					$_tpl['onload'] .= '$("input[name='.$k.']").on("keyup change",function(event){return wep.form.checkInt(event);});';
				}
				
				if(isset($r['mask']['max']) and $r['mask']['max']) $attribute .= ' maxlength="'.$r['mask']['max'].'"';

				if($r['type']=='email') $attribute .=  ' x-autocompletetype="'.$r['type'].'"';
				$texthtml .= '<div class="form-value"><input type="'.$r['type'].'" name="'.$k.'" value="'.htmlspecialchars($r['value'],ENT_QUOTES,$_CFG['wep']['charset']).'" '.$attribute.'/></div>';
			}
		}

		if(isset($r['comment']) and $r['comment']!='')
			$texthtml .= '<div class="dscr">'.$r['comment'].'</div>';
		if($r['type']!='hidden')
			$texthtml .= '</div>';
	}

	if(!is_null($flagTabs) and $tagStatus) { // TABS
		$texthtml .= '</div>';
	}

	return $texthtml;
}

