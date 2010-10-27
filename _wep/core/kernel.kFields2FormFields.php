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
						$md = $this->_getlist($r['listname'],$r['value']);
						$r['value_2'] = $md[$r['value']];
					}

					$r['labelstyle'] = ($r['value_2']?'display: none;':'');
					$r['csscheck'] = ($r['value_2']?'accept':'reject');
				}
				elseif($r['type']=='list') {
					if(!$r['readonly']){
						$md= $this->_getlist($r['listname']);
						if(is_array($r['value']))
							$val = array_flip($r['value']);
						else
							$val = $r['value'];
						if(is_array($md) and is_array(current($md))) {
							if(isset($r['mask']['begin']))
								$key = $r['mask']['begin'];//стартовый ID массива
							else
								$key = key($md);
							$r['valuelist'] = $this->_forlist($md,$key,$val);
						}
						elseif(is_array($md) and count($md))
							foreach($md as $km=>$rm) {
								$r['valuelist'][$km] = array('id'=>$km, 'name'=> $rm, 'sel'=>0);
								if((!is_array($val) and (string)$km==(string)$val) or (is_array($val) and isset($val[$km])))
									$r['valuelist'][$km]['sel'] = 1;
								//$xml .= '<item'.$param.'><name><![CDATA['._substr($rm,0,60).(_strlen($rm)>60?'...':'').']]></name></item>';
							}
					}
					else{
						$md = $this->_getlist($r['listname'],$r['value']);
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
					$_tpl['script'] .='<script type="text/javascript" src="'.$this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js"></script>';
					$fckscript = 'function cke_'.$k.'() { if(typeof CKEDITOR.instances.'.$k.' == \'object\'){CKEDITOR.instances.'.$k.'.destroy(true);} editor_'.$k.' = CKEDITOR.replace( \''.$k.'\',{';
					foreach($ckedit as $kc=>$rc)
						$fckscript .= $kc.' : '.$rc.',';
					$fckscript .= 'language : \'ru\'});';

					if($ckedit['CKFinder']) {
						$fckscript .='function ckf_'.$k.'() { CKFinder.SetupCKEditor(editor_'.$k.',\'/'.$this->_CFG['PATH']['WSWG'].'ckfinder/\');} if(!CKFinder) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js\',ckf_'.$k.'()); else ckf_'.$k.'();';
						$_tpl['script'] .='<script src="'.$this->_CFG['_HREF']['WSWG'].'ckfinder/ckfinder.js" type="text/javascript"></script>';
					}
					$_tpl['script'] .= '<script type="text/javascript">'.$fckscript.'}</script>';
					$_tpl['onload'] .= ' if(!window.CKEDITOR) $.include(\''.$this->_CFG['_HREF']['WSWG'].'ckeditor/ckeditor.js\',cke_'.$k.'); else cke_'.$k.'();';
				}
				elseif($r['type']=='date' and !$r['readonly']) {
			
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

					$r['value']['year'] = array('name'=>$this->_CFG['_MESS']['year_name']);// ГОД
					$temp[0] = (int)$temp[0]; 
					for($i=($temp[0]-2);$i<($temp[0]+3);$i++)
						$r['value']['year']['item'][$i] = array('id'=>$i, 'name'=>$i, 'sel'=>($temp[0]==$i?1:0));

					$r['value']['month'] = array('name'=>$this->_CFG['_MESS']['month_name']);// Месяц
					foreach($this->_CFG['_MESS']['month'] as $kr=>$td) {
						$kr = (int)$kr;
						$r['value']['month']['item'][$kr] = array('id'=>$kr, 'name'=>$td, 'sel'=>($temp[1]==$kr?1:0));
					}

					$r['value']['day'] = array('name'=>$this->_CFG['_MESS']['day_name']);// День
					for($i=1;$i<=31;$i++)
						$r['value']['day']['item'][$i] = array('id'=>$i, 'name'=>$i, 'sel'=>($temp[2]==$i?1:0));

					$r['value']['hour'] = array('name'=>$this->_CFG['_MESS']['hour_name']);// Час
					for($i=1;$i<=24;$i++)
						$r['value']['hour']['item'][$i] = array('id'=>$i, 'name'=>$i, 'sel'=>($temp[3]==$i?1:0));
					
					if(isset($r['mask']['date_min'])) {
						$r['value']['min'] = array('name'=>$this->_CFG['_MESS']['min_name']);// Minute
						for($i=1;$i<=60;$i++)
							$r['value']['min']['item'][$i] = array('id'=>$i, 'name'=>$i, 'sel'=>($temp[4]==$i?1:0));
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