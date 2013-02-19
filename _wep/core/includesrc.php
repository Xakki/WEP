<?php

	function fileInclude($gfi) {
		//$gfi -> $_CFG['fileIncludeOption']
		if(is_null($gfi)) return true;
		global $_tpl,$_CFG;

		if(!isset($_tpl['script']))
			$_tpl['script'] = array();

		if(!isset($gfi['uiStyle']))
			$gfi['uiStyle'] = 'smoothness';
		
		if(isset($gfi['multiple'])) 
		{
			if($gfi['multiple']==2) 
			{
				setCss('style.jquery/'.$gfi['uiStyle'].'/jquery-ui|style.jquery/ui-multiselect');

				$_tpl['script'][getUrlScript('script.jquery/jquery-ui')] = array(
					getUrlScript('script.jquery/ui-multiselect') => 1,
					getUrlScript('script.jquery/jquery.localisation/ui-multiselect-ru') => 1,
				);
	
				$_tpl['onload'] .= 'jQuery(\'select.multiple\').multiselect();';
				##
				//$_tpl['onload'] .= '$.localise(\'ui-multiselect\', {language: \'ru\', path: \''.$_CFG['_HREF']['_script'].'script.localisation/\'});';
			}
		}
		if(isset($gfi['form']) and $gfi['form']) 
		{
			setCss('form');
		}
		if(isset($gfi['ajaxForm']) and $gfi['ajaxForm']) 
		{
			setCss('form');
			setScript('wepform|script.jquery/form');
		}
		if(isset($gfi['fcontrol']) and $gfi['fcontrol']) {
			setCss('fcontrol');
			setScript('fcontrol');
		}
		if(isset($gfi['md5']) and $gfi['md5']) 
		{
			setScript('md5');
		}
		if(isset($gfi['fancybox'])) 
		{
			setScript('fancybox/jquery.fancybox.pack');
			setCss('../_script/fancybox/jquery.fancybox');
			if($gfi['fancybox'])
			{
				if(!is_string($gfi['fancybox']))
					$gfi['fancybox'] = '.fancyimg';
				$_tpl['onload'] .= "jQuery('".$gfi['fancybox']."').fancybox();";
			}
		}
		if(isset($gfi['qrtip']) and $gfi['qrtip']) 
		{
			setScript('script.jquery/qrtip');
			setCss('style.jquery/qrtip');
			$_tpl['onload'] .= 'jQuery(\'a\').qr();';
		}
		if(isset($gfi['jquery-ui']) and $gfi['jquery-ui']) 
		{
			setScript('script.jquery/jquery-ui');
			setCss('style.jquery/'.$gfi['uiStyle'].'/jquery-ui');
		}
		if(isset($gfi['datepicker']) and $gfi['datepicker']) 
		{
			setScript('script.jquery/jquery-ui|script.jquery/jquery.localisation/jquery.ui.datepicker-ru');
			if($gfi['datepicker']==2) 
			{
				setScript('script.jquery/ui-timepicker-addon');
				setCss('style.jquery/ui-timepicker-addon');
			}
			setCss('style.jquery/'.$gfi['uiStyle'].'/jquery-ui');
		}

		if(isset($_tpl['script'][getUrlScript('wepform')]))
			$_tpl['script'] = array(getUrlScript('wepform')=>1)+$_tpl['script'];

		$_tpl['script'] = array(getUrlScript('wep')=>1)+$_tpl['script'];
		$_tpl['script'] = array(getUrlScript('jquery')=>1)+$_tpl['script'];


		/*if(isset($_tpl['script']['syntaxhighlighter'])) {
			$_tpl['onload'] .= '';
		}*/

		/////////////////////
		return true;
	}

	function arraySrcToStr() {
		global $_tpl,$_CFG;
		$temp = $solt = '';
		if($_CFG['wep']['debugmode'])
			$solt = '?t='.time();

		// include STYLE into HTML
		if(isset($_tpl['styles']) and is_array($_tpl['styles'])) 
		{
			$temp .= cssRecursive($_tpl['styles'], $solt);
		}
		$_tpl['styles'] = $temp;


		// include SCRIPT into HTML
		$temp = '';
		if(isset($_tpl['script']) and is_array($_tpl['script'])) {
			$temp .= scriptRecursive($_tpl['script'], $solt);
		}
		
		if(isset($_tpl['onload2']) and count($_tpl['onload2'])) // WTF?
				$_tpl['onload'] .= implode(' ',$_tpl['onload2']);

		$temp .= "<script>\n//<!--\n function readyF() {".(string)$_tpl['onload']."\n}\n//-->\n</script>\n";

		$_tpl['onload'] = 'readyF();';

		$_tpl['script'] = $temp;
	}

	function scriptRecursive($script, $solt='') {
		global $_CFG;

		$temp = '';
		foreach($script as $kk=>$rr) {
			if(is_string($rr) and (substr($rr,0,4)=='http' or substr($rr,0,1)=='<')) {
				trigger_error('Обнаружена не совместимость: ошибка загрузки скриптов `'.$kk.'` - `'.$rr.'`', E_USER_WARNING);
			}

			$src = '';
			if (is_string($kk) and strpos($kk, '//')!==false)
			{
				$src = str_replace(array('http:','https:'), '', $kk).$solt;
				$temp .= '<script src="'.$src.'"></script>'."\n";
			}

			if(is_array($rr))
				$temp .= scriptRecursive($rr, $solt);
			elseif(is_string($rr))
				$temp .= "<script>\n//<!--\n".$rr."\n//-->\n</script>\n";
		}
		return $temp;
	}

	function cssRecursive($css, $solt='') {
		global $_CFG;
		$temp = '';

		foreach($css as $kk=>$rr) {
			if(substr($rr,0,4)==='http' or substr($rr,0,1)==='<') {
				trigger_error('Обнаружена не совместимость: ошибка загрузки стилей `'.$kk.'` - `'.$rr.'`', E_USER_WARNING);
			}

			$src = '';
			if (is_string($kk) and strpos($kk, '//')!==false)
			{
				$src = str_replace(array('http:','https:'), '', $kk).$solt;
				$temp .= '<link rel="stylesheet" href="'.$src.'"/>'."\n";
			}

			if(is_array($rr))
				$temp .= cssRecursive($rr, $solt);
			elseif(is_string($rr) and _strlen($rr)>5)
				$temp .= "<style>".$rr."</style>\n";
		}
		return $temp;
	}