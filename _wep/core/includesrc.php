<?php

	function fileInclude($gfi) {
		//$gfi -> $_CFG['fileIncludeOption']
		if(is_null($gfi)) return true;
		global $_tpl,$_CFG;

		if(!isset($_tpl['script']))
			$_tpl['script'] = array();

		if(!isset($gfi['uiStyle']))
			$gfi['uiStyle'] = 'smoothness';
		
		if(isset($gfi['multiple'])) {
			if($gfi['multiple']==2) {
				$_tpl['styles']['style.jquery/'.$gfi['uiStyle'].'/jquery-ui'] = 1;
				$_tpl['styles']['style.jquery/ui-multiselect'] = 1;

				$_tpl['script']['script.jquery/jquery-ui'] = array(
					'script.jquery/ui-multiselect' => 1,
					'script.jquery/jquery.localisation/ui-multiselect-ru' => 1,
				);
	
				$_tpl['onload'] .= 'jQuery(\'select.multiple\').multiselect();';
				##
				//$_tpl['onload'] .= '$.localise(\'ui-multiselect\', {language: \'ru\', path: \''.$_CFG['_HREF']['_script'].'script.localisation/\'});';
			}
		}
		if(isset($gfi['form']) and $gfi['form']) 
		{
			$_tpl['styles']['form'] = 1;
			$_tpl['script']['wepform'] = 1;
		}
		if(isset($gfi['ajaxForm']) and $gfi['ajaxForm']) 
		{
			$_tpl['styles']['form'] = 1;
			$_tpl['script']['wepform'] = 1;
			$_tpl['script']['script.jquery/form'] = 1;
		}
		if(isset($gfi['fcontrol']) and $gfi['fcontrol']) {
			$_tpl['styles']['fcontrol'] = 1;
			$_tpl['script']['fcontrol'] = 1;
		}
		if(isset($gfi['md5']) and $gfi['md5']) {
			$_tpl['script']['md5'] = 1;
		}
		if(isset($gfi['fancybox'])) 
		{
			$_tpl['script']['fancybox/jquery.fancybox.pack'] = 1;
			$_tpl['styles']['../_script/fancybox/jquery.fancybox'] = 1;
			if($gfi['fancybox'])
			{
				if(!is_string($gfi['fancybox']))
					$gfi['fancybox'] = '.fancyimg';
				$_tpl['onload'] .= "jQuery('".$gfi['fancybox']."').fancybox();";
			}
		}
		if(isset($gfi['qrtip']) and $gfi['qrtip']) {
			$_tpl['script']['script.jquery/qrtip'] = 1;
			$_tpl['styles']['style.jquery/qrtip'] = 1;
			$_tpl['onload'] .= 'jQuery(\'a\').qr();';
		}
		if(isset($gfi['jquery-ui']) and $gfi['jquery-ui']) {
			if(!isset($_tpl['script']['script.jquery/jquery-ui']))
				$_tpl['script']['script.jquery/jquery-ui'] = 1;
			$_tpl['styles']['style.jquery/'.$gfi['uiStyle'].'/jquery-ui'] = 1;
		}
		if(isset($gfi['datepicker']) and $gfi['datepicker']) {
			if(!isset($_tpl['script']['script.jquery/jquery-ui']))
				$_tpl['script']['script.jquery/jquery-ui'] = 1;
			$_tpl['script']['script.jquery/jquery.localisation/jquery.ui.datepicker-ru'] = 1;
			if($gfi['datepicker']==2) {
				$_tpl['script']['script.jquery/ui-timepicker-addon'] = 1;
				$_tpl['styles']['style.jquery/ui-timepicker-addon'] = 1;
			}
			$_tpl['styles']['style.jquery/'.$gfi['uiStyle'].'/jquery-ui'] = 1;
		}

		if(isset($_tpl['script']['wepform']))
			$_tpl['script'] = array('wepform'=>1)+$_tpl['script'];

		$_tpl['script'] = array('wep'=>1)+$_tpl['script'];
		$_tpl['script'] = array('jquery'=>1)+$_tpl['script'];


		/*if(isset($_tpl['script']['syntaxhighlighter'])) {
			$_tpl['onload'] .= '';
		}*/

		// UPDATE FIX
		if(isset($_tpl['script']['utils'])) {
			unset($_tpl['script']['utils']);
		}
		if(isset($_tpl['script']['form'])) {
			unset($_tpl['script']['form']);
		}

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

		$temp .= "<script>\n//<!--\n function readyF() {".(string)$_tpl['onload']." wep.init(); \n}\n//-->\n</script>\n";

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
			if (strpos($kk, '//')===0 or strpos($kk, 'http:')===0 or strpos($kk, 'https:')===0)
				$src = str_replace(array('http:','https:'), '', $kk);
			elseif(is_string($kk) and !is_string($rr))
			{
				if(strpos($kk,'/')===0)
					$path = MY_THEME;
				else
					$path = $_CFG['_HREF']['_script'];
				$src = '//'.$_CFG['_HREF']['_BH'].$path.$kk.'.js'.$solt;
			}

			if($src)
				$temp .= '<script src="'.$src.'"></script>'."\n";

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
			if (strpos($kk, '//')!==false)
				$src = str_replace(array('http:','https:'), '', $kk);
			elseif(is_string($kk) and !is_string($rr)) {
				if(strpos($kk,'/')===0)
					$path = MY_THEME;
				else
					$path = $_CFG['_HREF']['_style'];
				$src = '//'.$_CFG['_HREF']['_BH'].$path.$kk.'.css'.$solt;
			}

			if($src)
				$temp .= '<link rel="stylesheet" href="'.$src.'"/>'."\n";

			if(is_array($rr)) {
				$temp .= cssRecursive($rr, $solt);
			}
			elseif(is_string($rr))
				$temp .= "<style>".$rr."</style>\n";
		}
		return $temp;
	}