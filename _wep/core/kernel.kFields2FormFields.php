<?
		foreach($fields as $k=>&$r) {
			if(!isset($r['readonly']))
				$r['readonly'] = false;
			if(($r['readonly'] and !$this->id) or 
				(isset($r['mask']['fview']) and $r['mask']['fview']==2) or 
				(isset($r['mask']['usercheck']) and !static_main::_prmUserCheck($r['mask']['usercheck'])))
				continue;
			if($r['type']!='info') {
				if(!isset($r['value']) and isset($r['default']) and !isset($_POST[$k])) {// and !$this->id
					$r['value']= $r['default'];
					if(isset($r['default_2']))
						$r['value_2']= $r['default_2'];
				}
				if(isset($_POST[$k.'_2']))
					$r['value_2']= $_POST[$k.'_2'];

				if($r['type']=='file') {
					if(isset($r['ext']) and isset($this->_CFG['form']['imgFormat'][$r['ext']]) and $this->id) {
						$r['att_type'] = 'img';
						$r['value'] = $this->_get_file($this->id,$k);
						$r['img_size'] = getimagesize($this->_CFG['_PATH']['path'].$r['value']);
						$r['value'] = $this->_getPathSize($r['value']);

						if(count($this->attaches[$k]['thumb'])) {
							foreach($this->attaches[$k]['thumb'] as $modkey=>$mr) {
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
					}elseif(isset($r['ext']) and isset($this->_CFG['form']['flashFormat'][$r['ext']]) and $this->id) {
						$r['att_type'] = 'swf';
					}
					
					if(!isset($r['comment']))
						$r['comment'] = $this->_CFG['_MESS']['_file_size'].$this->attaches[$k]['maxsize'].'Kb';
				}
				elseif($r['type']=='ajaxlist') {
					if(!$r['label'])
						$r['label'] = 'Введите текст';
					if($r['mask']['min'] and $r['value']<$r['mask']['min'])
						$r['value_2'] = '';

					if((!isset($r['value_2']) or !$r['value_2']) and $r['value']) {
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
				elseif(isset($r['listname']) and isset($r['multiple']) and $r['multiple']===2 and !$r['readonly']) {// and isset($this->fields[$k])
					$this->_checkList($r['listname'],0);
					$templistname = $r['listname'];
					if(is_array($r['listname']))
						$templistname = implode(',',$r['listname']);
					$arrlist = &$this->_CFG['enum_check'][$templistname];

					if($arrlist and is_array($arrlist)) {
						if(is_array($r['value']))
							$val = array_combine($r['value'],$r['value']);
						else
							$val = array($r['value']=>$r['value']);
						$r['value'] = $val;
						$temparr= array();
						foreach($val as $kk) {
							if(isset($arrlist[$kk])) {
								$temparr[$kk] = $arrlist[$kk];
								unset($this->_CFG['enum_check'][$templistname][$kk]);
							}
						}
						$md = $temparr+$this->_CFG['enum_check'][$templistname];
						if(is_array($md) and count($md)) {
							$md = array($md);
							$r['valuelist'] = $this->_forlist($md,0,$val);
						}
					}
				}
				elseif(isset($r['listname']) and isset($r['multiple']) and $r['multiple'] and !$r['readonly']) {
					$md = $this->_getCashedList($r['listname']);
					if(is_array($r['value']))
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
					$r['valuelist'] = $this->_forlist($md ,$key,$val);
				}
				elseif(isset($r['listname'])) {
					if(!$r['readonly']) {
						if (!isset($r['listname']['idThis'])) 
							$r['listname']['idThis'] = $k;
													
						$md= $this->_getCashedList($r['listname']);
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
							//print_r('<pre>');print_r($md);
							$r['valuelist'] = $this->_forlist($md,$key,$val);
						}
					}
					else{
						if(isset($r['listname']['idThis']))
							$r['value'] = $fields[$r['listname']['idThis']]['value'];
						if($r['value']) {
							$md = $this->_getCashedList($r['listname'],$r['value']);
							$r['value'] = implode(',',$md);
						}
					}
				}
				elseif($r['type']=='ckedit') {
					$ckedit = $r['paramedit'];
					if(!isset($ckedit['skin']))
						$ckedit['skin']='\'kama\'';
					if(!isset($ckedit['width']))
						$ckedit['width'] = '\'100%\'';
					if(!isset($ckedit['height']))
						$ckedit['height'] = '450';
					if(!isset($ckedit['toolbarStartupExpanded']))
						$ckedit['toolbarStartupExpanded']='true';
					if(isset($ckedit['toolbar'])) {
						if(isset($this->_CFG['ckedit']['toolbar'][$ckedit['toolbar']]))
							$ckedit['toolbar'] = $this->_CFG['ckedit']['toolbar'][$ckedit['toolbar']];
						else
							$ckedit['toolbar'] = '\''.$ckedit['toolbar'].'\'';
					} else
						$ckedit['toolbar'] = $this->_CFG['ckedit']['toolbar']['Full'];
					$ckedit['uiColor'] = '\'#9AB8F3\'';
					//$ckedit['language'] = '\'ru\'';
					$ckedit['enterMode'] = 'CKEDITOR.ENTER_BR';
					$ckedit['shiftEnterMode'] = 'CKEDITOR.ENTER_P';

					global $_tpl;
					$_tpl['script']['ckeditor.js'] = array($this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js');
					$fckscript = 'function cke_'.$k.'() { if(typeof CKEDITOR.instances.id_'.$k.' == \'object\'){CKEDITOR.instances.id_'.$k.'.destroy(true);} editor_'.$k.' = CKEDITOR.replace( \'id_'.$k.'\',{';
					foreach($ckedit as $kc=>$rc)
						$fckscript .= $kc.' : '.$rc.',';
					$fckscript .= 'language : \'ru\'});';

					if(isset($ckedit['CKFinder']) and $ckedit['CKFinder']) {
						$fckscript = ' function ckf_'.$k.'() { CKFinder.setupCKEditor(editor_'.$k.',\'/'.$this->_CFG['PATH']['WSWG'].'ckfinder/\');} '.$fckscript;
						$fckscript .= ' ckf_'.$k.'();';
//if(!CKFinder) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js\',ckf_'.$k.'()); else 
						$_tpl['script']['ckfinder.js'] = array($this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js');
					}
					$_tpl['script']['ckeditor.ckf_'.$k] = $fckscript.'}';
					$_tpl['onload'] .= ' cke_'.$k.'();';
//if(!window.CKEDITOR) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js\',cke_'.$k.'); else 
				} elseif($k=='mf_ipcreate') {
					$r['value'] = long2ip($r['value']);
				}

				if(isset($r['mask']['name']))
				{
					if($r['mask']['name']=='phone2')
						$r['comment'] .= $this->_CFG['_MESS']['_comment_phone2'];
					//<br/>Допускается цифры, тире, пробел, запятые и скобки
					elseif($r['mask']['name']=='phone')
						$r['comment'] .= $this->_CFG['_MESS']['_comment_phone'];
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