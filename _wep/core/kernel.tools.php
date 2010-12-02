<?

	function _checkdir(&$_this,$dir)
	{
		if (!file_exists($_this->_CFG['_PATH']['path'].$dir)) 
		{
			if (!mkdir($_this->_CFG['_PATH']['path'].$dir, 0755)) return $_this->_message('Cannot create directory <b>$dir</b>');
		}
		else
		{			
			$f = fopen($_this->_CFG['_PATH']['path'].$dir.'/t_e_s_t', 'w');
			if (!$f) 
				return $_this->_message('Cannot create file in directory <b>'.$dir.'</b>');

			$err = fwrite($f, 'zzz')==-1;
			fclose($f);
			unlink($_this->_CFG['_PATH']['path'].$dir.'/t_e_s_t');

			if ($err) 
				return $_this->_message('Cannot write/read file in directory <b>'.$dir.'</b>');
		}
		return 0;
	}



/////////////// _reinstall

	function _reinstall(&$_this)
	{
		$_this->_droped();
		if (count($_this->childs)) 
			foreach($_this->childs as $child) 
				$child->_droped();
		$_this->_install();
		if (count($_this->childs)) 
			foreach($_this->childs as $child) 
				$child->_install();
		return 0;
	}

	function _droped(&$_this){
		$result = $_this->SQL->execSQL('DROP TABLE `'.$_this->tablename.'`');
		if ($result->err) return $_this->_message($result->err);
		$_this->_message('Table `'.$_this->tablename.'` droped.',3);
		return 0;
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
		return 0;
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
			if($result->err) return $_this->_message($result->err);
			// create list

			while ($row = $result->fetch_array())
			{
				//$data[]= $row;
				foreach($_this->attaches as $key=>$value)
				{
					$pathimg = $_this->_CFG['_PATH']['path'].$_this->getPathForAtt($key);
					foreach(array_unique($value['mime']) as $k=>$ext) {
						if(file_exists($pathimg.'/'.$row['id'].'.'.$ext)) {
							$data[$key][$ext][]=$row['id'];
							break;
						}
					}
				}	
			}
			foreach($_this->attaches as $key=>$value) {
				$result=$_this->SQL->execSQL('UPDATE '.$_this->tablename.' SET '.$key.'=\'\' ');
				if($result->err) return $_this->_message($result->err);
			}
			foreach($data as $key1=>$row1) {

				foreach($row1 as $key2=>$row2)
				{
					$result=$_this->SQL->execSQL('UPDATE '.$_this->tablename.' SET '.$key1.'=\''.$key2.'\' WHERE id IN ('.implode(',',$row2).')');
					if($result->err) return $_this->_message($result->err);
				}
			}
		}
		return 0;
	}
	
	function _toolsCheckmodul($_this)
	{
		$_this->form = $mess = array();
		if(!_prmModul($_this->_cl,array(14)))
			$mess[] = array('name'=>'error', 'value'=>$_this->getMess('denied'));
		elseif(count($_POST) and isset($_POST['sbmt'])){
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
			}
			else {
				$mess[] = array('name' => 'ok', 'value' => $_this->getMess('_recheck_select_nothing'));
			}
		}else{

			$check_result = $_this->_checkmodstruct();
	
			if (isset($check_result['err'])) {
				$mess[] = $check_result['err'];
			}
			elseif (!empty($check_result['list_query'])) {
				if(count($_this->childs)) {
					foreach($_this->childs as $k=>&$r) {
						$ch_check_result = $r->_checkmodstruct();

						if (isset($ch_check_result['err'])) {
							$mess[] = $ch_check_result['err'];
							break;
						}
						else {
							if (!empty($ch_check_result['list_query']))
								$check_result['list_query'] = array_merge($check_result['list_query'], $ch_check_result['list_query']);
						}
					}
				}
				if(count($_this->attaches)) {
					include_once($_CFG['_PATH']['core'].'kernel.tools.php');
					if(!_reattaches($_this))
						$mess[] = array('name'=>'ok', 'value'=>$_this->getMess('_file_ok'));
					else
						$mess[] = array('name'=>'error', 'value'=>$_this->getMess('_file_err'));
				}
			}
			
			if (!empty($check_result['list_query'])) {
				
				$_this->form['_*features*_'] = array('name'=>'Checkmodul','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
				
				$_this->form['_info'] = array(
					'type'=>'info',
					'caption'=>$_this->getMess('_recheck'),
				);		
				
				$_this->form['invert'] = array(
					'type' => 'info',
					'caption' => '<a href="#" onclick="return invert_select(\'form_tools_Checkmodul\');">Инвертировать выделение</a>',
				);

			
				$_this->form['list_query[]']['type'] = 'checkbox';
				foreach ($check_result['list_query'] as $query) {
					$query[0] = htmlspecialchars($query[0]);
					if (isset($query[1]))
						$desc = htmlspecialchars($query[0].' ('.$query[1].')');
					else
						$desc = $query[0];
					$_this->form['list_query[]']['item'][] = array(
						'value' => $query[0],
						'title' => $desc,
					);
				}
						
				$_this->form['sbmt'] = array(
					'type'=>'submit',
					'value'=>$_this->getMess('_submit')
				);

			} else {
				$mess[] = array('name' => 'ok', 'value' => $_this->getMess('_recheck_have_nothing'));
			}
		}

		return Array('form'=>$_this->form, 'messages'=>$mess);
	}
?>
