<?
		foreach($fields as $k=>$r) {
			if(($r['readonly'] and !$this->id) or $r['mask']['fview']==2 or (isset($r['mask']['usercheck']) and !_prmUserCheck($r['mask']['usercheck'])))
				continue;
			if($r['type']!='info') {
				if(!$this->id and isset($r['default']) and !isset($_POST[$k])) {
					$r['value']= $r['default'];
					if(isset($r['default_2']))
						$r['value_2']= $r['default_2'];
				}
				if(isset($_POST[$k.'_2']))
					$r['value_2']= $_POST[$k.'_2'];

				if($r['type']=='file') {
					if(isset($r['ext']) and isset($this->_CFG['form']['imgFormat'][$r['ext']])) {
						$r['att_type'] = 'img';
						$r['img_size'] = getimagesize($this->_CFG['_PATH']['path'].$this->_get_file2(0,$k));
						$r['value'] = $this->data[$this->id][$k];

						if(count($this->attaches[$k]['thumb'])) {
							foreach($this->attaches[$k]['thumb'] as $modkey=>$mr) {
								if((!$mr['pref'] and !$mr['path']) or (!$mr['pref'] and $mr['path']==$this->attaches[$key]['path']))
									{unset($r['thumb'][$modkey]);continue;}
								$_file = $this->_get_file2(0,$k,'',$modkey);
								if(file_exists($this->_CFG['_PATH']['path'].$_file)) {
									$mr['value'] = $_file;
									$mr['filesize'] = filesize($this->_CFG['_PATH']['path'].$_file);
									$r['thumb'][$modkey] = $mr;
								}
							}
						}
					}elseif(isset($r['ext']) and isset($this->_CFG['form']['flashFormat'][$r['ext']])) {
						$r['att_type'] = 'swf';
					}
					
					if(!$r['comment'])
						$r['comment'] = $this->_CFG['_MESS']['_file_size'].$this->attaches[$k]['maxsize'].'Kb';
				}
				elseif($r['type']=='ajaxlist') {
					if(!$r['label'])
						$r['label'] = 'Введите текст';
					if($r['mask']['min'] and $r['value']<$r['mask']['min'])
						$r['value_2'] = '';

					if(!$r['value_2'] and $r['value']) {
						$md = $this->_getCashedList($r['listname'],$r['value']);
						$r['value_2'] = $md[$r['value']];
					}

					$r['labelstyle'] = ($r['value_2']?'display: none;':'');
					$r['csscheck'] = ($r['value_2']?'accept':'reject');
				}
				elseif($r['type']=='list' and $r['multiple']==2 and !$r['readonly']) {
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
				elseif($r['type']=='list' and $r['multiple'] and !$r['readonly']) {
					$md = $this->_getCashedList($r['listname'],0);
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
				elseif($r['type']=='list') {
					if(!$r['readonly']){
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
							$r['valuelist'] = $this->_forlist($md,$key,$val);
							//if($k=='rubric') {print_r('<pre>');print_r($r['valuelist']);}
						}
					}
					else{
						$md = $this->_getCashedList($r['listname'],$r['value']);
						$r['value'] = $md[$r['value']];
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
					$_tpl['script']['ckeditor.js'] ='<script type="text/javascript" src="'.$this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js"></script>';
					$fckscript = 'function cke_'.$k.'() { if(typeof CKEDITOR.instances.'.$k.' == \'object\'){CKEDITOR.instances.'.$k.'.destroy(true);} editor_'.$k.' = CKEDITOR.replace( \''.$k.'\',{';
					foreach($ckedit as $kc=>$rc)
						$fckscript .= $kc.' : '.$rc.',';
					$fckscript .= 'language : \'ru\'});';

					if($ckedit['CKFinder']) {
						$fckscript .='function ckf_'.$k.'() { CKFinder.SetupCKEditor(editor_'.$k.',\'/'.$this->_CFG['PATH']['WSWG'].'ckfinder/\');} if(!CKFinder) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js\',ckf_'.$k.'()); else ckf_'.$k.'();';
						$_tpl['script']['ckfinder.js'] .='<script src="'.$this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js" type="text/javascript"></script>';
					}
					$_tpl['script']['ckfinder.js'] = '<script type="text/javascript">'.$fckscript.'}</script>';
					$_tpl['onload'] .= ' if(!window.CKEDITOR) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js\',cke_'.$k.'); else cke_'.$k.'();';
				}
				elseif($r['type']=='date' and !$r['readonly']) {
					// форомат даты
					if($r['format']) {
						$format = explode('-', $r['format']);
					}
					else{
						$format = explode('-', 'Y-m-d-H-i-s');
					}
					// Тип поля
					if($this->fields[$k]['type']=='int' and $r['value']){
						$temp = explode('-',date('Y-m-d-H-i-s',$r['value']));
					}
					elseif($this->fields[$k]['type']=='timestamp' and $r['value']){
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
							$r['value']['year'] = array('name'=>$this->_CFG['_MESS']['year_name'], 'css'=>'year');// ГОД
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
							$r['value']['month'] = array('name'=>$this->_CFG['_MESS']['month_name'], 'css'=>'month');// Месяц
							foreach($this->_CFG['_MESS']['month'] as $kr=>$td) {
								$kr = (int)$kr;
								$r['value']['month']['item'][$kr] = array('#id#'=>$kr, '#name#'=>$td, '#sel#'=>($temp[1]==$kr?1:0));
							}						
						}
						// день
						if($item_date == 'd' || $item_date == 'j')
						{
							$r['value']['day'] = array('name'=>$this->_CFG['_MESS']['day_name'], 'css'=>'day');// День
							for($i=1;$i<=31;$i++)
								$r['value']['day']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[2]==$i?1:0));						
						}
						// час
						if($item_date == 'G' || $item_date == 'g' || $item_date == 'H' || $item_date == 'h')
						{
							$r['value']['hour'] = array('name'=>$this->_CFG['_MESS']['hour_name'], 'css'=>'hour');// Час
							for($i=1;$i<=24;$i++)
								$r['value']['hour']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[3]==$i?1:0));
						}
						// минуты
						if($item_date == 'i')
						{
							$r['value']['minute'] = array('name'=>$this->_CFG['_MESS']['minute_name'], 'css'=>'minute');// Minute
							for($i=1;$i<=60;$i++)
								$r['value']['minute']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[4]==$i?1:0));
						}
						// секунды
						if($item_date == 's')
						{
							$r['value']['sec'] = array('name'=>$this->_CFG['_MESS']['sec_name'], 'css'=>'sec');
							for($i=1;$i<=60;$i++)
								$r['value']['sec']['item'][$i] = array('#id#'=>$i, '#name#'=>$i, '#sel#'=>($temp[5]==$i?1:0));					
						}
					}

				}
				elseif($r['type']=='date') {
					if($this->fields[$k]['type']=='int'){
						if(!$r['format']) 
							$r['format'] = 'Y-m-d H:i:s';
						$r['value']= date($r['format'],$r['value']);
					}
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
			$this->form[$k] = $r;
		}
		return true;

?>