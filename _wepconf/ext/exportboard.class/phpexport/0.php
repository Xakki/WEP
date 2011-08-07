<?
//$vars
//$exportValue
$newPost = array();
//$vars['rubric']

$rub = $vars['rubric'];
$allRub = array();
while($rub and $this->RUBRIC->cacheData[$rub]) {
	$allRub[$rub] =$rub;
	$rub = $this->RUBRIC->cacheData[$rub]['parent_id'];
}
$eDATA = $EXPORTBOARD->childs['exportrubric']->_query('*','WHERE (rubric IN ('.implode(',',$allRub).') or over=1) and active=1 order by over');
if(count($eDATA)) {
	$newPost['category'] = $eDATA[0]['nameid'];
	$newPost['name'] = 'Продавец';
	if($vars['phone']) $newPost['phone'] = mb_substr(preg_match('/[^0-9\,\-\(\) ]/','',$vars['phone']),0,254);
	if($vars['email']) $newPost['email'] = $vars['email'];
	$newPost['code'] = substr(md5(time()),0,8);
	$newPost['text'] = '';

		$fulltext = '';
		$PARAM = &$this->RUBRIC->childs['param'];
		if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)) {
			foreach($PARAM->data as $k=>$r){
				if($vars['param_'.$k]) {
					$val = $vars['param_'.$k];
					if(isset($this->_enum['fli'.$r['formlist']])) {
						if(isset($this->_enum['fli'.$r['formlist']][$val]))
							$val = $this->_enum['fli'.$r['formlist']][$val];
						elseif(is_array($this->_enum['fli'.$r['formlist']])) {
							foreach($this->_enum['fli'.$r['formlist']] as $kk=>$rr) {
								if(isset($rr[$val])) {
									$val = $rr[$val];
									break;
								}
							}
						}
					}
					if(is_array($val))
						$val = $val['#name#'];
					$fulltext .= $r['name'].': '.$val;
					if($r['edi'])
						$fulltext .= ' '.$r['edi'];
					$fulltext .= ". \n";
				}
			}
		}

	$newPost['text'] .= $this->RUBRIC->cacheData[$vars['rubric']]['name'].' - '.$this->_enum['type'][$vars['type']].". \n";
	$newPost['text'] .= $fulltext;
	if($vars['cost']) $newPost['text'] .= 'Цена: '.$vars['cost'].". \n";
	if($vars['contact']) $newPost['text'] .= 'Дополнительные контакты: '.$vars['contact'].". \n";
	$vars['text'] = str_replace(array('</li>','</div>','</p>','<br />','<br/>'),array("</li> \n","</div> \n","</p> \n"," \n"," \n"),$vars['text']);
	$newPost['text'] .= strip_tags($vars['text']);
	$newPost['text'] = mb_substr($newPost['text'],0,999);
	$newPost['add'] = 'Сохранить';
	$newPost['cid'] = '';

	/*if(strpos($this->_CFG['_PATH']['path'],'\\')) // кастыль для винды
		$PATH = str_replace('\\','\',$this->_CFG['_PATH']['path']);
	else*/
	$PATH = $this->_CFG['_PATH']['path'];
	/*if($this->data[$this->id]['img_board'])
		$newPost['i_boardfoto1'] = '@'.$PATH.$this->_get_file($this->id,'img_board','',1);
	if($this->data[$this->id]['img_board2'])
		$newPost['i_boardfoto2'] = '@'.$PATH.$this->_get_file($this->id,'img_board2','',1);*/
	if($this->data[$this->id]['img_board']) {
		$newPost['i_boardfoto1'] = '@'.$PATH.$this->data[$this->id]['img_board'];
		if (function_exists('mime_content_type'))
			$newPost['i_boardfoto1'] .= ';type='.mime_content_type($PATH.$this->data[$this->id]['img_board']);
	}
	if($this->data[$this->id]['img_board2']) {
		$newPost['i_boardfoto2'] = '@'.$PATH.$this->data[$this->id]['img_board2'];
		if (function_exists('mime_content_type'))
			$newPost['i_boardfoto2'] .= ';type='.mime_content_type($PATH.$this->data[$this->id]['img_board']);
	}
	$EXPORTBOARD->childs['sendboard']->fld_data['board_id'] = $this->id;
	$EXPORTBOARD->childs['sendboard']->fld_data['text'] = mysql_real_escape_string(var_export($newPost,true));
	$EXPORTBOARD->childs['sendboard']->fld_data['result'] = 0;
	$EXPORTBOARD->id = $exportValue['id'];// owner
	if($EXPORTBOARD->childs['sendboard']->_add()) {
		$this->temp_AddText = ' Также ваше объявление отправленно на сайт <a href="'.$exportValue['www'].'">'.$exportValue['name'].'</a> и попадёт в рубрику <a href="'.$exportValue['www'].'/boardcat'.$eDATA[0]['nameid'].'.html">'.$eDATA[0]['name'].'</a> в ближайшие 10 минут.<br/> Для удаления этого объявления на этом сайте вам понадобиться код <b>'.$newPost['code'].'</b>';
	}
}

