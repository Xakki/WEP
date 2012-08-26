<?php

		foreach($fields as $k=>&$r) {
			if(!is_array($r)) continue;
			if(!isset($r['readonly']))
				$r['readonly'] = false;
			if(($r['readonly'] and !$this->id) or 
				(isset($r['mask']['fview']) and $r['mask']['fview']==2) or 
				(isset($r['mask']['usercheck']) and !static_main::_prmGroupCheck($r['mask']['usercheck']))) {
				unset($fields[$k]);
				continue;
			}
			if(isset($r['type']) and $r['type']!='info') {
				if(!isset($r['value']) and isset($r['default']) and !isset($_POST[$k])) {// and !$this->id
					$r['value']= $r['default'];
					if(isset($r['default_2']))
						$r['value_2']= $r['default_2'];
				}
				if(isset($_POST[$k.'_2']))
					$r['value_2']= $_POST[$k.'_2'];

				if($r['type']=='file') {
					// Процесс загрузки фаила
					if(isset($r['value']) and is_array($r['value']) and isset($r['value']['tmp_name']) and $r['value']['tmp_name']) {
						$r['value'] = $_CFG['PATH']['temp'].$r['value']['name'];
					}
					// Редактирование формы - отображаем фаил 
					elseif(isset($r['ext']) and $this->id) {
						$r['value'] = $this->_get_file($this->id,$k);// TODO
					}

					if(isset($r['value']) and $r['value'] and file_exists($this->_CFG['_PATH']['path'].$r['value'])) {
						$_is_image = static_image::_is_image($this->_CFG['_PATH']['path'].$r['value']); // Проверяем , является ли фаил изображением
						if($_is_image) {// Если это изображение
							$r['att_type'] = 'img'; // Маркер для рисования формы
							$r['img_size'] = getimagesize($this->_CFG['_PATH']['path'].$r['value']);
							$r['value'] = $this->_getPathSize($r['value']);

							if(count($this->attaches[$k]['thumb'])) {
								foreach($this->attaches[$k]['thumb'] as $modkey=>$mr) {
									if(isset($mr['display']) and !$mr['display']) {
										unset($r['thumb'][$modkey]);
										continue;
									}
									if(!isset($mr['pref'])) $mr['pref'] = '';
									if(!isset($mr['path'])) $mr['path'] = '';
									if((!$mr['pref'] and !$mr['path']) or (!$mr['pref'] and $mr['path']==$this->attaches[$key]['path']))
										{unset($r['thumb'][$modkey]);continue;}
									$_file = $this->_get_file($this->id,$k,'',$modkey);
									if(file_exists($this->_CFG['_PATH']['path'].$_file)) {
										$mr['value'] = $this->_getPathSize($_file);
										$mr['filesize'] = filesize($this->_CFG['_PATH']['path'].$_file);
										$r['thumb'][$modkey] = $mr;
									}
								}
							}
						} 
						elseif(isset($this->_CFG['form']['flashFormat'][$r['ext']]) and $this->id) {
							$r['att_type'] = 'swf'; // Флешки
						} 
						else
							$r['value'] = '';
						// TODO : можно описать ещё какиенибудь специфические типы
					}

					if(!isset($r['value']) or !is_string($r['value']) or !$r['value'])
						$r['value'] = '';
					
					if(!isset($r['comment']))
						$r['comment'] = static_main::m('_file_size').$this->attaches[$k]['maxsize'].'Kb';
				}
				elseif($r['type']=='ajaxlist') {
					if(!$r['label'])
						$r['label'] = 'Введите текст';
					if($r['mask']['min'] and (!isset($r['value']) or $r['value']<$r['mask']['min']))
						$r['value_2'] = '';

					if((!isset($r['value_2']) or !$r['value_2']) and isset($r['value']) and $r['value']) {
						if(isset($r['multiple'])) {
							$r['value'] = explode('|', trim($r['value'], '|'));
						}
						$md = $this->_getCashedList($r['listname'],$r['value']);
						if(isset($r['multiple'])) {
							foreach($r['value'] as $kv=>$rv)
								$r['value_2'][$kv] = (isset($md[$rv])?$md[$rv]:'');
						}
						else $r['value_2'] = $md[$r['value']];
					}

					$r['labelstyle'] = ($r['value_2']?'display: none;':'');
					$r['csscheck'] = ($r['value_2']?'accept':'reject');
				}
				/*elseif(isset($r['listname']) and isset($r['multiple']) and $r['multiple']===2 and !$r['readonly']) {// and isset($this->fields[$k])
					$this->_checkList($r['listname'],$r['value']);
					$templistname = $r['listname'];
					if(is_array($r['listname']))
						$templistname = implode(',',$r['listname']);
					$templistname =	$_this->_cl.'_'.$templistname;
					$arrlist = &$this->_CFG['enum_check'][$templistname];

					if($arrlist and is_array($arrlist)) {
						if(is_array($r['value']))
							$r['value'] = array_combine($r['value'],$r['value']);
						else
							$r['value'] = array($r['value']=>$r['value']);
						$temparr= array();
						foreach($r['value'] as $kk) {
							if(isset($arrlist[$kk])) {
								$temparr[$kk] = $arrlist[$kk];
								unset($this->_CFG['enum_check'][$templistname][$kk]);
							}
						}
						$md = $temparr+$this->_CFG['enum_check'][$templistname];
						if(is_array($md) and count($md)) {
							$md = array($md);
							$r['valuelist'] = $this->_forlist($md,0,$r['value']);
						}
					}
				}*/
				elseif(isset($r['listname']) and isset($r['multiple']) and $r['multiple'] and !$r['readonly']) {
					$md = $this->_getCashedList($r['listname']);
					if(!isset($r['value']) or !is_array($r['value']))
						$r['value'] = array();
					if($r['multiple']!=3 and count($r['value'])) {
						if(is_array($r['value']))
							$r['value'] = array_combine($r['value'],$r['value']);
						else
							$r['value'] = array($r['value']=>$r['value']);
					}
					$temp = current($md);
					if(is_array($temp) and !isset($temp['#name#'])) {
						if(isset($r['mask']['begin']))
							$key = $r['mask']['begin'];//стартовый ID массива
						else
							$key = key($md);
					} else{
						$md = array($md);
						$key = 0;
					}
					$r['valuelist'] = $this->_forlist($md ,$key,$r['value'],$r['multiple']);
				}
				elseif(isset($r['listname'])) {
					if(!$r['readonly']) {
						if (!isset($r['listname']['idThis'])) 
							$r['listname']['idThis'] = $k;
													
						$md= $this->_getCashedList($r['listname']);
						if(!isset($r['value']))
							$r['value'] = '';
						if(is_array($r['value']))
							$val = array_combine($r['value'],$r['value']);
						else
							$val = array($r['value']=>$r['value']);
						$r['value'] = $val;
						if(is_array($md) and count($md)) {
							$temp = current($md);
							if(is_array($temp) and !isset($temp['#name#'])) {
								if(isset($r['mask']['begin']))
									$key = $r['mask']['begin'];//стартовый ID массива
								else
									$key = key($md);
							} else{
								$md = array($md);
								$key = 0;
							}
							$r['valuelist'] = $this->_forlist($md,$key,$val);
						}
					}
					else {
						if(is_array($r['listname']) and isset($r['listname']['idThis']) and $this->id) {
							$r['value'] = $this->data[$this->id][$r['listname']['idThis']];
						}
						if(isset($r['value']) and $r['value']!='') {
							$tval = $r['value'];
							$ltemp = $this->_getCashedList($r['listname'],$tval);

							if(is_array($ltemp)) {
								$r['value'] = implode(',',$ltemp);
							}
							else
								$r['value'] = $ltemp;
						}
					}
				}
				elseif($r['type']=='ckedit') {
					if(!isset($r['paramedit']))
						$r['paramedit'] = array();
				}
				elseif($k=='mf_ipcreate') {
					$r['value'] = long2ip($r['value']);
				}

				// Преобразуем теги, чтобы их не съел шаблонизатор
				if(isset($r['value']) and $r['value'] and is_string($r['value']) and ($r['type']=='ckedit' or $r['type']=='text' or $r['type']=='textarea') and strpos($r['value'],'{#')!==false) {
					$r['value'] = str_replace(array('{#','#}'),array('(#','#)'),$r['value']);
					// TODO : возможна дыра в безопастности, решить срочно
				}

				if(isset($r['mask']['name']))
				{
					if($r['mask']['name']=='phone2')
						$r['comment'] .= static_main::m('_comment_phone2');
					//<br/>Допускается цифры, тире, пробел, запятые и скобки
					elseif($r['mask']['name']=='phone')
						$r['comment'] .= static_main::m('_comment_phone');
					//Допускается цифры, тире, пробел и скобки
					//elseif($r['mask']['name']=='phone3')
					//	$r['comment'] = "";
					//Допускается цифры, тире, пробел, запятые и скобки
				}
			}
			if(isset($this->fields[$k]))
				$r['fields_type'] = $this->fields[$k]['type'];
		}
		unset($r);
		if(count($fields)) {
			if(!isset($fields['_*features*_']))
				$fields['_*features*_'] = array('name' => 'f'.$this->_cl, 'method'=>$method, 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']), 'prevhref' => $_SERVER['HTTP_REFERER']);
			elseif(is_string($fields['_*features*_']))
				$fields['_*features*_'] = array('name' => $fields['_*features*_'], 'method'=>$method, 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']), 'prevhref' => $_SERVER['HTTP_REFERER']);
		}
		return true;