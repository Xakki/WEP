<?

	function _checkdir(&$_this,$dir)
	{
		if (!file_exists($_this->_CFG['_PATH']['path'].$dir)) 
		{
			if (!mkdir($_this->_CFG['_PATH']['path'].$dir, 0755)) return $_this->_message('Cannot create directory <b>'.$dir.'</b>',1);
		}
		else
		{			
			$f = fopen($_this->_CFG['_PATH']['path'].$dir.'/t_e_s_t', 'w');
			if (!$f) 
				return $_this->_message('Cannot create file in directory <b>'.$dir.'</b>',1);

			$err = fwrite($f, 'zzz')==-1;
			fclose($f);
			unlink($_this->_CFG['_PATH']['path'].$dir.'/t_e_s_t');

			if ($err) 
				return $_this->_message('Cannot write/read file in directory <b>'.$dir.'</b>',1);
		}
		return true;
	}



/////////////// _reinstall

	function _reinstall(&$_this)
	{
		_droped($_this);
		if (count($_this->childs)) 
			foreach($_this->childs as $child) 
				_droped($_this);
		$_this->_install();
		if (count($_this->childs)) 
			foreach($_this->childs as $child) 
				$child->_install();
		return true;
	}

	function _droped(&$_this){
		$result = $_this->SQL->execSQL('DROP TABLE `'.$_this->tablename.'`');
		if ($result->err) return false;
		$_this->_message('Table `'.$_this->tablename.'` droped.',3);
		return true;
	}

	function _xmlFormConf(&$_this) {
		$_this->form = array();
		$_this->form['_*features*_'] = array('name'=>'Configmodul','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
		$_this->form['_info'] = array('type'=>'info','css'=>'caption','caption'=>$_this->_CFG['_MESS']['_config']);
		foreach($_this->config_form as $k=>$r) {
			if(!is_array($_this->config[$k]))
				$_this->config_form[$k]['value']= stripslashes($_this->config[$k]);
			else
				$_this->config_form[$k]['value']= $_this->config[$k];
		}
		$_this->form = array_merge($_this->form,$_this->config_form);
		$_this->form['sbmt'] = array(
			'type'=>'submit',
			'value'=>$_this->_CFG['_MESS']['_submit']);
	}

	function _save_config(&$_this)
	{
		//if (file_exists($_this->_file_cfg)) unlink($_this->_file_cfg);
		$h = fopen($_this->_file_cfg, 'w');
			foreach($_this->config as $key=>$value) 
			{
				if(!is_array($value)) {
					$value = str_replace("\x0A", ' ', $value);
					$value = str_replace("\x0D", '', $value);
					$value = stripslashes($value);
				} else {
					$value = implode('|',$value);
				}
				fwrite($h, $key.'='.$value."\n");
			}
		fclose($h);
		return true;
	}


	function _staticStatsmodul(&$_this,$oid='') {
		$clause = array();
		if(!$oid and isset($_GET['_oid']))
			$oid = (int)$_GET['_oid'];
		if($oid)
			$clause[] = 't1.'.$_this->owner_name.'='.$oid;
		$filtr = $_this->_filter_clause();
		if(count($filtr[0])) $clause += $filtr[0];
		if(count($clause))
			$clause = 'WHERE '.implode(' and ',$clause);
		else
			$clause = '';
		$clause = 'SELECT '.$_this->mf_statistic['X'].' as `X`, '.$_this->mf_statistic['Y'].' as `Y` FROM `'.$_this->tablename.'` t1 '.$clause.' GROUP BY X ORDER BY X';
		$result = $_this->SQL->execSQL($clause);
		$data = array();
		$maxY = 0;
		$minX = 0;
		$maxX = 0;
		if(!$result->err) {
			while ($row = $result->fetch_array()){
				$data[] = '[\''.$row['X'].'\','.$row['Y'].']';
				if($row['Y']>$maxY) $maxY = $row['Y'];
				if($row['X']>$maxX) $maxX = $row['X'];
				if($minX==0 or $row['X']<$minX) $minX = $row['X'];
			}
		}
		else 
			return array($result->err,'');

		$stepY = $_this->okr($maxY,1)/10;
$eval = '
line1 = ['.implode(',',$data).'];
if(typeof $.jqplot == "undefined")
	$.include(\''.$_this->_CFG['_HREF']['_script'].'jquery.jqplot.0.9.7/plugins/jqplot.ohlcRenderer.min.js\',
		function(){readyPlot();},[
		$.include(\''.$_this->_CFG['_HREF']['_script'].'jquery.jqplot.0.9.7/jquery.jqplot.min.js\'),
		$.include(\''.$_this->_CFG['_HREF']['_script'].'jquery.jqplot.0.9.7/plugins/jqplot.cursor.min.js\'),
		$.include(\''.$_this->_CFG['_HREF']['_script'].'jquery.jqplot.0.9.7/plugins/jqplot.dateAxisRenderer.min.js\'),
		$.include(\''.$_this->_CFG['_HREF']['_script'].'jquery.jqplot.0.9.7/plugins/jqplot.highlighter.min.js\')
	]);
else {readyPlot();}

function readyPlot() {
	plot1 = $.jqplot(\'statschart2\', [line1], {
		title:\''.$_this->caption.'\',
		axes:{
			xaxis:{label:\''.$_this->mf_statistic['Xname'].'\',renderer:$.jqplot.DateAxisRenderer},
			yaxis:{label:\''.$_this->mf_statistic['Yname'].'\',min:0,tickInterval:'.$stepY.',tickOptions:{formatString:\'%d\'} }},
		cursor:{zoom: true},
		series:[{lineWidth:4, markerOptions:{style:\'square\'}}]
	});
}
';
/*
	//$.include(\'/script/jquery.ui.all.js\',function(){readyUI();});
	function readyUI(){
		$(\'#statstabs\').tabs();
		$(\'#statstabs\').bind(\'tabsshow\', function(event, ui) {
			if (ui.index == 1 && plot1._drawCount == 0) {
				plot1.replot();
			}
			else if (ui.index == 2 && plot2._drawCount == 0) {
				plot2.replot();
			}
		});
	};*/
	$html = '';
	if(count($filtr[0])) $html .= 'Результат статистики выводится по фильтру<br/>';
$html .= '
<div id="statschart2" data-height="300px" data-width="480px" style="margin-top:10px; margin-left:10px;"></div>
<style>
@import "'.$_this->_CFG['_HREF']['_style'].'jquery-ui-redmond.css";
@import "'.$_this->_CFG['_HREF']['_script'].'jquery.jqplot.0.9.7/jquery.jqplot.min.css";
</style>
';
		$html = '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>'.$html;
		global $_tpl;
		$_tpl['onload'] .= $eval;
		return $html;
	}

	function _reattaches(&$_this)
	{
		if(count($_this->attaches)) {
			$data= array();
			// select record ids to delete
			$result=$_this->SQL->execSQL('select id FROM '.$_this->tablename);
			if($result->err) return false;
			// create list

			while ($row = $result->fetch_array())
			{
				//$data[]= $row;
				foreach($_this->attaches as $key=>$value)
				{
					$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
					foreach(array_unique($value['mime']) as $k=>$ext) {
						$newname = $pathimg.'/'.$row['id'].'.'.$ext;
						if(file_exists($newname)) {
							if(isset($value['thumb']) and count($value['thumb'])) { // проверка на наличие модифицированных изображений
								if(!exif_imagetype($newname)) // опред тип файла
									break;
								foreach($value['thumb'] as $imod) {
									if(!$imod['pref']) $imod['pref'] = '';// по умолчинию без префикса
									if($imod['path'])
										$newname2 = $_this->_CFG['_PATH']['path'].$imod['path'].'/'.$imod['pref'].$row['id'].'.'.$ext;
									else
										$newname2 = $pathimg.'/'.$imod['pref'].$row['id'].'.'.$ext;
									if($newname!=$newname2 and !file_exists($newname2)) {
										include_once($_CFG['_PATH']['core'].'kernel.addup.php');
										if ($imod['type']=='crop')
											_cropImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type']=='resize')
											_resizeImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type']=='resizecrop')
											_resizecropImage($_this,$newname, $newname2, $imod['w'], $imod['h']);
										elseif ($imod['type']=='water')
											_waterMark($_this,$newname,$newname2, $imod['w'], $imod['h']);
									}
								}
							}
							$data[$key][$ext][]=$row['id'];
							break;
						}
					}
				}
			}
			foreach($_this->attaches as $key=>$value) {
				$result=$_this->SQL->execSQL('UPDATE '.$_this->tablename.' SET '.$key.'=\'\' ');
				if($result->err) return false;
			}
			foreach($data as $key1=>$row1) {

				foreach($row1 as $key2=>$row2)
				{
					$result=$_this->SQL->execSQL('UPDATE '.$_this->tablename.' SET '.$key1.'=\''.$key2.'\' WHERE id IN ('.implode(',',$row2).')');
					if($result->err) return false;
				}
			}
		}
		return true;
	}
	
	function _toolsCheckmodul($_this)
	{
		$check_err = false;
		$_this->form = $mess = array();
		if(!_prmModul($_this->_cl,array(14)))
			$mess[] = array('name'=>'error', 'value'=>$_this->getMess('denied'));
/*		elseif(isset($_POST['sbmt'])){
			if (isset($_POST['list_query'])) {
				$err = false;
				foreach ($_POST['list_query'] as $query) {
					$result = $_this->SQL->execSQL(stripslashes($query));
					if ($result->err != '') {
						$err = true;
						$mess[] = array('name' => 'error', 'value' => $_this->getMess('_recheck_err').'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>');
						break;
					}					
				}
				if ($err == false)
					$mess[] = array('name'=>'ok', 'value'=>$_this->getMess('_recheck_ok').'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>');

				$check_result = 
			}
			else {
				$mess[] = array('name' => 'ok', 'value' => $_this->getMess('_recheck_select_nothing'));
			}
		}*/
		else{
			
			$reattach = array();
			
			$check_result = $_this->_checkmodstruct();
	
			if (isset($check_result[$_this->tablename]['err'])) {
				$mess[] = array('name' => 'error', 'value' => $check_result[$_this->tablename]['err']);
				$check_err = true;
			}
//			elseif (!empty($check_result['list_query'])) {

			$tmp_check_result = array();
			
			if(count($_this->childs)) {
				foreach($_this->childs as $k=>&$r) {
					$tmp_check_result = $r->_checkmodstruct();

					if(count($r->attaches)) {
						$reattach[$r->tablename] = true;
					}
					
					if (isset($tmp_check_result[$r->tablename]['err'])) {
						$mess[] = array('name' => 'error', 'value' => $tmp_check_result[$r->tablename]['err']);
						$check_err = true;
						break;
					}
					$check_result = array_merge($check_result, $tmp_check_result);
				}
			}
			
			if(count($_this->attaches)) {
				$reattach[$_this->tablename] = true;
			}		
			
			$is_query = false;
			foreach ($check_result as $k=>$r) {
				if (isset($r['list_query']) && !empty($r['list_query'])) {
					foreach ($r['list_query'] as $field => $query) {
						if (isset($query[0]))
							$is_query = true;
					}
				}
			}
			
			if (!empty($reattach) || ($check_err == false && $is_query)) {
				
				if (isset($_POST['sbmt'])) {
					if (isset($_POST['list_query']) || isset($_POST['reattach'][$_this->tablename])) {
						
						if (isset($_POST['list_query']) && is_array($_POST['list_query']) && !empty($_POST['list_query'])) {
						
							$err = false;
							foreach ($_POST['list_query'] as $r) {
								$matches = explode('::', $r);
								if (count($matches) == 2 || count($matches) == 3) {
									$table = $matches[0];
									unset($matches[0]);
									if (isset($check_result[$table]['list_query'][implode('::',$matches)][0])) {
										$result = $_this->SQL->execSQL($check_result[$table]['list_query'][implode('::',$matches)][0]);
										if ($result->err) {
											$err = true;
											break;
										}					
									}
									else {
										$err = true;
									}
								}
								else {
									$mess[] = array('name' => 'error', 'value' => $_this->getMess('_recheck_err').'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>');
								}
							}
						}
						
						if (isset($_POST['reattach'][$_this->tablename])) {
							include_once($_CFG['_PATH']['core'].'kernel.tools.php');
							if(_reattaches($_this))
								$mess[] = array('name'=>'ok', 'value'=>$_this->getMess('_file_ok'));
							else
								$mess[] = array('name'=>'error', 'value'=>$_this->getMess('_file_err'));
						}
						
						
						if ($err == true)
							$mess[] = array('name' => 'error', 'value' => $_this->getMess('_recheck_err').'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>');
						else
							$mess[] = array('name'=>'ok', 'value'=>$_this->getMess('_recheck_ok').'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>');

					}
					else {
						$mess[] = array('name' => 'ok', 'value' => $_this->getMess('_recheck_select_nothing'));
					}
				}
				else {
				
					$_this->form['_*features*_'] = array('name'=>'Checkmodul','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
					
					$_this->form['_info'] = array(
						'type'=>'info',
						'caption'=>$_this->getMess('_recheck'),
					);		
					
					$_this->form['invert'] = array(
						'type' => 'info',
						'caption' => '<a href="#" onclick="return invert_select(\'form_tools_Checkmodul\');">Инвертировать выделение</a>',
					);

					foreach ($check_result as $table=>$list_query) {
						foreach ($list_query as $kk=>$fields) {
							foreach ($fields as $field => $query) {
		//						$query[0] = htmlspecialchars($query[0]);
								if(!is_array($query)) {
									$mess[] = array('name' => 'error', 'value' => 'Поле `'.$kk.'` - '.$query);
								}
								elseif (isset($query[0])) {
									if (isset($query[1]))
										$desc = 'Было: '.$query[1].'<br/>Будет: '.htmlspecialchars($query[0],ENT_QUOTES,$_this->_CFG['wep']['charset']);
									else
										$desc = $query[0];
									
									if (isset($query[2]))
										$desc .= '<br/><span style="color:red">'.$query[2].'</span>';
									
									$_this->form['list_query']['valuelist'][] = array(
										'#id#' => $table.'::'.$field,
										'#name#' => $desc,
									);
								}
								elseif (isset($query[2])) {
									$mess[] = array('name' => 'error', 'value' => $query[2]);
								}
							}
						}
					}
					if(isset($_this->form['list_query']))
						$_this->form['list_query']['type'] = 'checkbox';
				
					if (!empty($reattach)) {
						foreach ($reattach as $k=>$v) {
							$_this->form['reattach['.$k.']']['type'] = 'checkbox';
							$_this->form['reattach['.$k.']']['caption'] = 'Обновить файлы модуля '.$k;
						}
					}
							
					$_this->form['sbmt'] = array(
						'type'=>'submit',
						'value'=>$_this->getMess('_submit')
					);
				}
				
			} else {
				$mess[] = array('name' => 'ok', 'value' => $_this->getMess('_recheck_have_nothing'));
			}
		}

		return Array('form'=>$_this->form, 'messages'=>$mess);
	}
?>
