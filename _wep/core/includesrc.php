<?php

	function fileInclude($gfi) {
		//$gfi -> $_CFG['fileIncludeOption']
		if(!count($gfi)) return true;
		global $_tpl,$_CFG;
		if(!isset($_CFG['fileIncludeOption']['uiStyle']))
			$_CFG['fileIncludeOption']['uiStyle'] = 'ui-smoothness';
		if(isset($gfi['multiple'])) {
			if($gfi['multiple']==2) {
				$_tpl['styles']['style.jquery/'.$_CFG['fileIncludeOption']['uiStyle'].'/jquery-ui'] = 1;
				$_tpl['styles']['style.jquery/ui-multiselect'] = 1;

				$_tpl['script']['script.jquery/jquery-ui'] = 1;
				$_tpl['script']['script.jquery/ui-multiselect'] = 1;
				
				//
				$_tpl['script']['jquery.localisation/ui-multiselect-ru'] = 1;
	
				$_tpl['onload'] .= 'jQuery(\'select.multiple\').multiselect();';
				##
				$_tpl['onload'] .= '$.localise(\'ui-multiselect\', {language: \'ru\', path: \''.$_CFG['_HREF']['_script'].'script.localisation/\'});';
			}
		}
		if(isset($gfi['form']) and $gfi['form']) {
			$_tpl['script']['wepform'] = 1;
			$_tpl['styles']['form'] = 1;
		}
		if(isset($gfi['jqueryform'])) {
			$_tpl['script']['script.jquery/form'] = 1;
		}
		if(isset($gfi['md5']) and $gfi['md5']) {
			$_tpl['script']['md5'] = 1;
		}
		if(isset($gfi['fancybox']) and $gfi['fancybox']) {
			$_tpl['script']['script.jquery/fancybox'] = 1;
			$_tpl['styles']['style.jquery/fancybox'] = 1;
			$_tpl['onload'] .= "jQuery('.fancyimg').fancybox();";
		}
		if(isset($gfi['datepicker']) and $gfi['datepicker']) {
			$_tpl['script']['script.jquery/jquery-ui'] = 1;
			$_tpl['script']['jquery.localisation/jquery.ui.datepicker-ru'] = 1;
			if($gfi['datepicker']==2) {
				$_tpl['script']['script.jquery/ui-timepicker-addon'] = 1;
				$_tpl['styles']['style.jquery/ui-timepicker-addon'] = 1;
			}
			$_tpl['styles']['style.jquery/'.$_CFG['fileIncludeOption']['uiStyle'].'/jquery-ui'] = 1;
		}
		return true;
	}

	function arraySrcToStr() {
		global $_tpl,$_CFG;
		$temp = $solt = '';
		if($_CFG['wep']['debugmode'])
			$solt = '?t='.time();

		if(isset($_tpl['styles']) and is_array($_tpl['styles'])) {
			foreach($_tpl['styles'] as $kk=>$rr) {
				if($rr[0]=='<')
					$temp .= $rr."\n";
				elseif(is_array($rr))
					$temp .= '<link type="text/css" href="'.implode('" rel="stylesheet"/>'."\n".'<link type="text/css" href="',$rr).'" rel="stylesheet"/>'."\n";
				elseif($rr==1 and $kk)
					$temp .= '<link type="text/css" href="'.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_style'].$kk.'.css'.$solt.'" rel="stylesheet"/>'."\n";
				else
					$temp .= '<style type="text/css">'.$rr.'</style>'."\n";
			}
		}
		$_tpl['styles'] = $temp;

		$temp = '';

		if(isset($_tpl['script']) and is_array($_tpl['script'])) {
			if(isset($_tpl['script']['wepform']))
				$_tpl['script'] = array('jquery'=>1,'wep'=>1,'wepform'=>1)+$_tpl['script'];
			else
				$_tpl['script'] = array('jquery'=>1,'wep'=>1)+$_tpl['script'];

			if(isset($_tpl['script']['syntaxhighlighter'])) {
				$_tpl['onload'] .= '';
			}
			// UPDATE FIX
			if(isset($_tpl['script']['utils'])) {
				unset($_tpl['script']['utils']);
			}
			if(isset($_tpl['script']['form'])) {
				unset($_tpl['script']['form']);
			}

			foreach($_tpl['script'] as $kk=>$rr) {
				if(is_array($rr))
					$temp .= '<script type="text/javascript" src="'.implode('"></script>'."\n".'<script type="text/javascript" src="',$rr).'"></script>'."\n";
				elseif($rr==1 and $kk) {
					$temp .= '<script type="text/javascript" src="'.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_script'].$kk.'.js'.$solt.'"></script>'."\n";
					if($kk=='script.jquery/fancybox')
						$_tpl['onload'] .= 'jQuery(\'.fancyimg\').fancybox();';//$_tpl['onload'] .= 'jQuery(\'div.imagebox a\').fancybox();jQuery(\'a.fancyimg\').fancybox();';
					elseif(strpos($kk,'qrtip')!== false) {
						$_tpl['onload'] .= 'jQuery(\'a\').qr();';
					}
				}
				else
					$temp .= "<script type=\"text/javascript\">//<!--\n".$rr."\n//--></script>\n";
			}
		}
		if(strpos($temp,'jquery')!==false and !isset($_tpl['script']['include']))
			$temp .= '<script type="text/javascript" src="'.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_script'].'include.js'.$solt.'"></script>';

		if(!isset($_tpl['script']['jquery']) and strpos($temp,'script.jquery/')!==false)
			$temp = '<script type="text/javascript" src="'.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_script'].'jquery.js'.$solt.'"></script>'.$temp;
		
		if(isset($_tpl['onload2']) and count($_tpl['onload2']))
				$_tpl['onload'] .= implode(' ',$_tpl['onload2']);

		if(isset($_tpl['onload']) and $_tpl['onload']) {
			$temp .= "<script type=\"text/javascript\">\n//<!--\nfunction readyF() {".(string)$_tpl['onload']."}\n//-->\n</script>\n";
			$_tpl['onload'] = 'readyF();';
		}
		$_tpl['script'] = $temp;
	}

	function arraySrcToFunc() {
		global $_tpl,$_CFG;
		$solt = '';
		if($_CFG['wep']['debugmode'])
			$solt = '?t='.time();

		$temp = '';
		if($_tpl['styles'] and is_array($_tpl['styles']) and count($_tpl['styles'])) {
			foreach($_tpl['styles'] as $kk=>$rr) {
				if(is_array($rr))
					$temp .= '$.includeCSS(\''.implode('\'); $.includeCSS(\'',$rr).'\'); ';
				elseif($rr==1 and $kk)
					$temp .= '$.includeCSS(\''.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_style'].$kk.'.css'.$solt.'\');';
				else
					$temp .= 'alert(\'CSS not found '.$kk.'\');';
			}
		}
		$temp2 = '';
		$tcnt = 0;
		if($_tpl['script'] and is_array($_tpl['script']) and count($_tpl['script'])) {
			$wrap = false;
			if(isset($_tpl['script']['script.jquery/jquery-ui'])) {
				$wrap = 'script.jquery/jquery-ui';
				unset($_tpl['script']['script.jquery/jquery-ui']);
			}
			foreach($_tpl['script'] as $kk=>$rr) {
				$fn = 'function(){chekcnt++;}';
				if(is_array($rr)) {
					$temp .= '$.include(\''.implode('\','.$fn.'); $.include(\'',$rr).'\','.$fn.'); ';//
					$tcnt++;
				}
				elseif($rr==1 and $kk) {
					$temp .= '$.include(\''.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_script'].$kk.'.js'.$solt.'\','.$fn.'); ';
					$tcnt++;
				}
				else
					$temp2 .= $rr;
			}
			if($wrap!==false) {
				$temp = '$.include(\''.$_CFG['_HREF']['BH'].$_CFG['_HREF']['_script'].$wrap.'.js'.$solt.'\',function(){'.$temp.'}); ';
			}
		}
		$temp2 .= $_tpl['onload'];
		$_tpl['onload'] = 'var chekcnt=0; '.$temp;
		$_tpl['onload'] .= 'function fchekcnt() {if(chekcnt=='.$tcnt.') {'.$temp2.'} else setTimeout(fchekcnt,200);} setTimeout(fchekcnt,200);';
		//$_tpl['onload'] .= $temp2;
	}
