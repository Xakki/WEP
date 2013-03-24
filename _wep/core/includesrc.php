<?php

	function fileInclude() 
	{
		global $_tpl;

		if(isset($_tpl['script'][getUrlScript('wepform')]))
			$_tpl['script'] = array(getUrlScript('wepform')=>1)+$_tpl['script'];

		$_tpl['script'] = array(getUrlScript('wep')=>1)+$_tpl['script'];
		$_tpl['script'] = array(getUrlScript('jquery')=>1)+$_tpl['script'];
		
		if(isset($_tpl['onloadArray']) and count($_tpl['onloadArray'])) // Для скриптов задающихся через массив, дабы не повторялись
			$_tpl['onload'] .= implode(' ',$_tpl['onloadArray']);
		unset($_tpl['onloadArray']);
		/////////////////////

		return true;
	}


	function arraySrcToStr() 
	{
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
		if(isset($_tpl['script']) and is_array($_tpl['script'])) 
		{
			$temp .= scriptRecursive($_tpl['script'], $solt);
		}

		if($_CFG['site']['production'])
			$_tpl['onload'] = ' window.isProduction = true; '.$_tpl['onload'];

		$temp .= "<script>\n//<!--\n function readyF() {".(string)$_tpl['onload']."\n}\n//-->\n</script>\n";

		$_tpl['onload'] = 'readyF();';

		$_tpl['script'] = $temp;
	}

	function scriptRecursive($script, $solt='') {
		global $_CFG;

		$temp = '';
		foreach($script as $kk=>$rr) {
			if(is_string($rr) and substr($rr,0,1)=='<') {
				trigger_error('Обнаружена не совместимость: ошибка загрузки скриптов `'.$kk.'` - `'.$rr.'`', E_USER_WARNING);
			}

			if (is_string($kk) and isUrl($kk))
			{
				$temp .= '<script src="'.$kk.$solt.'"></script>'."\n";
			}

			if (is_string($rr) and $rr)
			{
				if(isUrl($rr))
					$temp .= '<script src="'.$rr.$solt.'"></script>'."\n";
				else
					$temp .= "<script>\n//<!--\n".$rr."\n//-->\n</script>\n";
			}
			elseif(is_array($rr))
				$temp .= scriptRecursive($rr, $solt);
				
		}
		return $temp;
	}

	function cssRecursive($css, $solt='') {
		global $_CFG;
		$temp = '';

		foreach($css as $kk=>$rr) {
			if(is_string($rr) and substr($rr,0,1)==='<') {
				trigger_error('Обнаружена не совместимость: ошибка загрузки стилей `'.$kk.'` - `'.$rr.'`', E_USER_WARNING);
			}

			if (is_string($kk) and isUrl($kk))
			{
				$temp .= '<link rel="stylesheet" href="'.$kk.$solt.'"/>'."\n";
			}

			if (is_string($rr) and $rr)
			{
				if(isUrl($rr))
					$temp .= '<link rel="stylesheet" href="'.$rr.$solt.'"/>'."\n";
				else
					$temp .= "<style>".$rr."</style>\n";
			}
			elseif(is_array($rr))
				$temp .= cssRecursive($rr, $solt);
			
		}
		return $temp;
	}

	function isUrl($str)
	{
		if(
			strpos($str, '//')===0 or 
			strpos($str, 'https://')===0 or 
			strpos($str, 'http://')===0
		)
			return true;
		return false;
	}