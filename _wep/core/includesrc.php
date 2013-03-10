<?php

	function fileInclude() 
	{
		global $_tpl;

		if(isset($_tpl['script'][getUrlScript('wepform')]))
			$_tpl['script'] = array(getUrlScript('wepform')=>1)+$_tpl['script'];
		$_tpl['AAAA'] = $_tpl['script'];
		$_tpl['script'] = array(getUrlScript('wep')=>1)+$_tpl['script'];
		$_tpl['script'] = array(getUrlScript('jquery')=>1)+$_tpl['script'];
		
		if(isset($_tpl['onloadArray']) and count($_tpl['onloadArray'])) // Для скриптов задающихся через массив, дабы не повторялись
			$_tpl['onload'] .= implode(' ',$_tpl['onloadArray']);
		unset($_tpl['onloadArray']);
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