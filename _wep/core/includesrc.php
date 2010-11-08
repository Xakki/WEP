<?

	function fileInclude($gfi) {
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
	}

	function arraySrcToStr() {
		global $_tpl,$_CFG;
		if($_tpl['styles'] and is_array($_tpl['styles']) and count($_tpl['styles'])) {
			$temp = '';
			foreach($_tpl['styles'] as $kk=>$rr) {
				if($rr==1 and $kk)
					$temp .= '<link type="text/css" href="'.$_CFG['_HREF']['_style'].$kk.'.css" rel="stylesheet"/>'."\n";
				elseif($rr)
					$temp .= $rr."\n";
			}
			$_tpl['styles'] = $temp;
		}

		if($_tpl['script'] and is_array($_tpl['script']) and count($_tpl['script'])) {
			$temp = '';
			foreach($_tpl['script'] as $kk=>$rr) {
				if($rr==1 and $kk)
					$temp .= '<script type="text/javascript" src="'.$_CFG['_HREF']['_script'].$kk.'.js"></script>'."\n";
				elseif($rr)
					$temp .= $rr."\n";
			}
			$_tpl['script'] = $temp;
		}
	}

	function arraySrcToFunc() {
		global $_tpl,$_CFG;
		$temp = 'clearTimeout(timerid);fShowload(1);';
		if($_tpl['styles'] and is_array($_tpl['styles']) and count($_tpl['styles'])) {
			foreach($_tpl['styles'] as $kk=>$rr) {
				if($rr==1 and $kk)
					$temp .= '$.includeCSS(\''.$_CFG['_HREF']['_style'].$kk.'.css\');';
				elseif($rr)
					$temp .= 'alert($kk);';
			}
		}
		$temp .= '';
		if($_tpl['script'] and is_array($_tpl['script']) and count($_tpl['script'])) {
			foreach($_tpl['script'] as $kk=>$rr) {
				if($rr==1 and $kk)
					$temp .= '$.include(\''.$_CFG['_HREF']['_script'].$kk.'.js\');';
				elseif($rr)
					$temp .= 'alert($kk);';
			}
		}
		$_tpl['onload'] = $temp.$_tpl['onload'].'fShowload(0);';
	}
?>