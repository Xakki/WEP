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
					'jquery.localisation/ui-multiselect-ru' => 1,
				);
	
				$_tpl['onload'] .= 'jQuery(\'select.multiple\').multiselect();';
				##
				//$_tpl['onload'] .= '$.localise(\'ui-multiselect\', {language: \'ru\', path: \''.$_CFG['_HREF']['_script'].'script.localisation/\'});';
			}
		}
		if(isset($gfi['form']) and $gfi['form']) {
			$_tpl['styles']['form'] = 1;
			$_tpl['script']['wepform'] = 1;
		}
		if(isset($gfi['fcontrol']) and $gfi['fcontrol']) {
			$_tpl['styles']['fcontrol'] = 1;
			$_tpl['script']['fcontrol'] = 1;
		}
		if(isset($gfi['md5']) and $gfi['md5']) {
			$_tpl['script']['md5'] = 1;
		}
		if(isset($gfi['fancybox']) and $gfi['fancybox']) {
			$_tpl['script']['script.jquery/fancybox'] = 1;
			$_tpl['styles']['style.jquery/fancybox'] = 1;
			$_tpl['onload'] .= "jQuery('.fancyimg').fancybox();";
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
			$_tpl['script']['jquery.localisation/jquery.ui.datepicker-ru'] = 1;
			if($gfi['datepicker']==2) {
				$_tpl['script']['script.jquery/ui-timepicker-addon'] = 1;
				$_tpl['styles']['style.jquery/ui-timepicker-addon'] = 1;
			}
			$_tpl['styles']['style.jquery/'.$gfi['uiStyle'].'/jquery-ui'] = 1;
		}

		if(isset($_tpl['script']['wepform']))
			$_tpl['script'] = array('wepform'=>1)+$_tpl['script'];

		$_tpl['script'] = array('wep'=>1)+$_tpl['script'];
		$_tpl['script'] = array('include'=>1)+$_tpl['script'];
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
		// TODO : сделать все преобрзования на сервере, а скрипту передать готовые массивы
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

		if(isset($_tpl['onload']) and $_tpl['onload']) {
			$temp .= "<script>\n//<!--\nfunction readyF() {".(string)$_tpl['onload']."}\n//-->\n</script>\n";
			$_tpl['onload'] = 'readyF();';
		} else
			$_tpl['onload'] = '';
		$_tpl['script'] = $temp;
	}

	function scriptRecursive($script, $solt='') {
		global $_CFG, $HTML;
		if(!isset($HTML->_design)) {
			trigger_error('Название дизайна ГДЕ???', E_USER_WARNING);
			$HTML->_design = 'default';
		}
		$temp = '';
		foreach($script as $kk=>$rr) {
			if(is_string($rr) and (substr($rr,0,4)=='http' or substr($rr,0,1)=='<')) {
				trigger_error('Обнаружена не совместимость: ошибка загрузки скриптов `'.$kk.'` - `'.$rr.'`', E_USER_WARNING);
			}

			$src = '';
			// TODO - учесть и преобразовывать, возможен косяк, когда внутри урла есть "http:"
			if (strpos($kk, '//')===0 or strpos($kk, 'http:')===0 or strpos($kk, 'https:')===0)
				$src = str_replace(array('http:','https:'), '', $kk);
			elseif(is_string($kk))
			{
				if(strpos($kk,'/')===0)
					$path = '_design/'.$HTML->_design.'/';
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
		global $_CFG, $HTML;
		$temp = '';

		foreach($css as $kk=>$rr) {
			if(substr($rr,0,4)==='http' or substr($rr,0,1)==='<') {
				trigger_error('Обнаружена не совместимость: ошибка загрузки стилей `'.$kk.'` - `'.$rr.'`', E_USER_WARNING);
			}

			$src = '';
			if (strpos($kk, '//')!==false)
				$src = str_replace(array('http:','https:'), '', $kk);
			elseif(is_string($kk)) {
				if(strpos($kk,'/')===0)
					$path = '_design/'.$HTML->_design.'/';
				else
					$path = $_CFG['_HREF']['_style'];
				$src = '//'.$_CFG['_HREF']['_BH'].$path.$kk.'.css'.$solt;
			}

			if($src)
				$temp .= '<link rel="stylesheet" href="'.$src.'"></link>'."\n";

			if(is_array($rr)) {
				$temp .= cssRecursive($rr, $solt);
			}
			elseif(is_string($rr))
				$temp .= "<style>".$rr."</style>\n";
		}
		return $temp;
	}