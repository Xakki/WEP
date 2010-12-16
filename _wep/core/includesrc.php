<?

	function fileInclude($gfi) {
		if(!count($gfi)) return true;
		global $_tpl,$_CFG;
		if($gfi['multiple']==2) {
			$_tpl['styles']['jquery-ui-redmond'] = 1;
		}
		if($gfi['multiple']==2) {
			$_tpl['script']['script.jquery.ui/jquery.ui.core.min'] = 1;
		}
		if($gfi['multiple']==2) {
			$_tpl['script']['script.jquery.ui/jquery.ui.widget.min'] = 1;
		}
		if($gfi['multiple']==2) {			
			$_tpl['script']['script.jquery.ui/jquery.ui.mouse.min'] = 1;
		}
		if($gfi['multiple']==2) {
			$_tpl['script']['script.jquery.ui/jquery.ui.sortable.min'] = 1;
		}
		if($gfi['multiple']==2) {
			$_tpl['script']['script.jquery.ui/jquery.ui.draggable.min'] = 1;
		}
		if($gfi['multiple']==2) {
			//$_tpl['onload'] .= '$.localise(\'ui-multiselect\', {language: \'ru\', path: \''.$_CFG['_HREF']['_script'].'script.localisation/\'});';
			$_tpl['onload'] .= '$(\'select.multiple\').multiselect();';
			$_tpl['styles']['jquery-ui.multiselect'] = 1;
			$_tpl['script']['script.jquery.ui/jquery.ui.multiselect'] = 1;
		}
		if($gfi['form']) {
			$_tpl['script']['form'] = 1;
			$_tpl['styles']['form'] = 1;
		}
		if($gfi['md5']) {
			$_tpl['script']['md5'] = 1;
		}
		if($gfi['fancybox']) {
			$_tpl['script']['jquery.fancybox'] = 1;
			$_tpl['styles']['jquery.fancybox'] = 1;
			$_tpl['onload'] .= "$('.fancyimg').fancybox();";
		}
		return true;
	}

	function arraySrcToStr() {
		global $_tpl,$_CFG;
		$temp = '';
		if(isset($_tpl['styles']) and is_array($_tpl['styles'])) {
			foreach($_tpl['styles'] as $kk=>$rr) {
				if($rr[0]=='<')
					$temp .= $rr."\n";
				elseif(is_array($rr))
					$temp .= '<link type="text/css" href="'.implode('" rel="stylesheet"/>'."\n".'<link type="text/css" href="',$rr).'" rel="stylesheet"/>'."\n";
				elseif($rr==1 and $kk)
					$temp .= '<link type="text/css" href="'.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_style'].$kk.'.css" rel="stylesheet"/>'."\n";
				else
					$temp .= '<style type="text/css">'.$rr.'</style>'."\n";
			}
		}
		$_tpl['styles'] = $temp;

		$temp = '';
		if(isset($_tpl['script']) and is_array($_tpl['script'])) {
			foreach($_tpl['script'] as $kk=>$rr) {
				if(is_array($rr))
					$temp .= '<script type="text/javascript" src="'.implode('"></script>'."\n".'<script type="text/javascript src="',$rr).'"></script>'."\n";
				elseif($rr==1 and $kk) {
					$temp .= '<script type="text/javascript" src="'.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_script'].$kk.'.js"></script>'."\n";
					if($kk=='jquery.fancybox')
						$_tpl['onload'] .= "$('.imagebox a').fancybox();";
				}
				else
					$temp .= "<script type=\"text/javascript\">//<!--\n".$rr."\n//--></script>\n";
			}
		}
		$_tpl['script'] = $temp;
	}

	function arraySrcToFunc() {
		global $_tpl,$_CFG;
		$temp = 'clearTimeout(timerid);fShowload(1);';
		if($_tpl['styles'] and is_array($_tpl['styles']) and count($_tpl['styles'])) {
			foreach($_tpl['styles'] as $kk=>$rr) {
				if(is_array($rr))
					$temp .= '$.includeCSS(\''.implode('\'); $.includeCSS(\'',$rr).'\'); ';
				elseif($rr==1 and $kk)
					$temp .= '$.includeCSS(\''.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_style'].$kk.'.css\');';
				else
					$temp .= 'alert(\'CSS not find '.$kk.'\');';
			}
		}
		$temp .= 'var chekcnt=0;';
		$temp2 = '';
		$tcnt = 0;
		if($_tpl['script'] and is_array($_tpl['script']) and count($_tpl['script'])) {
			foreach($_tpl['script'] as $kk=>$rr) {
				if(is_array($rr)) {
					$temp .= '$.include(\''.implode('\',chekcnt++); $.include(\'',$rr).'\',chekcnt++); ';//
					$tcnt++;
				}
				elseif($rr==1 and $kk) {
					$temp .= '$.include(\''.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_script'].$kk.'.js\',chekcnt++);';
					$tcnt++;
				}
				else
					$temp2 .= $rr;
			}
		}
		$temp2 .= $_tpl['onload'];
		$_tpl['onload'] = $temp;
		$_tpl['onload'] .= 'function fchekcnt() {if(chekcnt=='.$tcnt.') {'.$temp2.'fShowload(0);} else setTimeout(fchekcnt,200);} setTimeout(fchekcnt,200);';
		//$_tpl['onload'] .= $temp2;
	}
?>