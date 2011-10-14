<?php

		foreach($fields as $k=>&$r) {
			if(!isset($r['readonly']))
				$r['readonly'] = false;
			if(($r['readonly'] and !$this->id) or 
				(isset($r['mask']['fview']) and $r['mask']['fview']==2) or 
				(isset($r['mask']['usercheck']) and !static_main::_prmGroupCheck($r['mask']['usercheck'])))
				continue;
			if(isset($r['type']) and $r['type']!='info') {
				if(!isset($r['value']) and isset($r['default']) and !isset($_POST[$k])) {// and !$this->id
					$r['value']= $r['default'];
					if(isset($r['default_2']))
						$r['value_2']= $r['default_2'];
				}
				if(isset($_POST[$k.'_2']))
					$r['value_2']= $_POST[$k.'_2'];

				if($r['type']=='file') {
					if(isset($r['value']) and is_array($r['value']) and isset($r['value']['tmp_name']) and $r['value']['tmp_name']) {
						$r['value'] = $_CFG['PATH']['temp'].$r['value']['name'];
						if(isset($this->_CFG['form']['imgFormat'][$r['ext']])) {
							$r['att_type'] = 'img';
							if(file_exists($this->_CFG['_PATH']['path'].$r['value'])) {
								$r['img_size'] = getimagesize($this->_CFG['_PATH']['path'].$r['value']);
								$r['value'] = $this->_getPathSize($r['value']);
							}
						} elseif(isset($this->_CFG['form']['flashFormat'][$r['ext']])) {
							$r['att_type'] = 'swf';
							
						}
					}
					elseif(isset($r['ext']) and $this->id) {
						$r['value'] = $this->_get_file($this->id,$k);// TODO
						if(isset($this->_CFG['form']['imgFormat'][$r['ext']]) and $this->id) {
							$r['att_type'] = 'img';
							if(file_exists($this->_CFG['_PATH']['path'].$r['value'])) {
								$r['img_size'] = getimagesize($this->_CFG['_PATH']['path'].$r['value']);
								$r['value'] = $this->_getPathSize($r['value']);

								if(count($this->attaches[$k]['thumb'])) {
									foreach($this->attaches[$k]['thumb'] as $modkey=>$mr) {
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
							} else
								$r['value'] = '';
						} elseif(isset($this->_CFG['form']['flashFormat'][$r['ext']]) and $this->id) {
							$r['att_type'] = 'swf';
						}
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
					if(!isset($r['value']))
						$val = array();
					elseif(is_array($r['value']))
						$val = array_combine($r['value'],$r['value']);
					else
						$val = array($r['value']=>$r['value']);
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
					$r['valuelist'] = $this->_forlist($md ,$key,$val,$r['multiple']);
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
					else{
						if(isset($r['listname']['idThis']) and $this->id) {
							$r['value'] = $this->data[$this->id][$r['listname']['idThis']];
						}
						if($r['value']) {
							$md = $this->_getCashedList($r['listname'],$r['value']);
							$r['value'] = implode(',',$md);
						}
					}
				}
				elseif($r['type']=='ckedit') {
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
						$ckedit['baseHref'] = '\''.$this->_CFG['_HREF']['BH'].'\'';
					if(isset($ckedit['toolbar'])) {
						if(isset($this->_CFG['ckedit']['toolbar'][$ckedit['toolbar']]))
							$ckedit['toolbar'] = $this->_CFG['ckedit']['toolbar'][$ckedit['toolbar']];
						else
							$ckedit['toolbar'] = '\''.$ckedit['toolbar'].'\'';
					} else
						$ckedit['toolbar'] = $this->_CFG['ckedit']['toolbar']['Full'];
					if(!isset($ckedit['uiColor']))
						$ckedit['uiColor'] = '\'#9AB8F3\'';
					if(!isset($ckedit['language']))
						$ckedit['language'] = '\'ru\'';
					if(!isset($ckedit['enterMode']))
						$ckedit['enterMode'] = 'CKEDITOR.ENTER_BR';
					if(!isset($ckedit['shiftEnterMode']))
						$ckedit['shiftEnterMode'] = 'CKEDITOR.ENTER_P';

					global $_tpl;
					$_tpl['script']['ckeditor.js'] = array($this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js');
					$fckscript = 'function cke_'.$k.'() { if(typeof CKEDITOR.instances.id_'.$k.' == \'object\'){CKEDITOR.instances.id_'.$k.'.destroy(true);} editor_'.$k.' = CKEDITOR.replace( \'id_'.$k.'\',{';
					foreach($ckedit as $kc=>$rc)
						$fckscript .= $kc.' : '.$rc.',';
					$fckscript .= '\'temp\' : \'temp\' });';

					if(isset($ckedit['CKFinder']) and $ckedit['CKFinder']) {
						$fckscript = ' function ckf_'.$k.'() { CKFinder.setupCKEditor(editor_'.$k.',\'/'.$this->_CFG['PATH']['WSWG'].'ckfinder/\');} '.$fckscript;
						$fckscript .= ' ckf_'.$k.'();';
//if(!CKFinder) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js\',ckf_'.$k.'()); else 
						$_tpl['script']['ckfinder.js'] = array($this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js');
					}
					$_tpl['script']['ckeditor.ckf_'.$k] = $fckscript.'}';
					if(!isset($fields[$k.'_ckedit']['value']) or $fields[$k.'_ckedit']['value']=='1')
						$_tpl['onload'] .= ' cke_'.$k.'();';
//if(!window.CKEDITOR) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js\',cke_'.$k.'); else 
				} elseif($k=='mf_ipcreate') {
					$r['value'] = long2ip($r['value']);
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
			$this->form[$k] = $r;
		}

		return true;