<?php

function tpl_formSelect(&$data,$val=NULL) {
	//global $HTML,$_tpl,$PGLIST,$_CFG;
	if(!is_null($val)) {
		if(!is_array($val)) 
			$val = array($val=>true);
	}
	return formSelectGenerator($data,$val);
	/*$texthtml = '';
	if(is_array($data) and count($data))
		foreach($data as $r) {
			//_substr($r['#name#'],0,60).(_strlen($r['#name#'])>60?'...':'')
			//$r['#name#'] = str_repeat(" -", $flag).' '.$r['#name#'];
			if(isset($r['#item#']) and count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0)
				$texthtml .= '<optgroup label="'.$r['#name#'].'"></optgroup>';
			else {
				$sel = '';
				if(isset($r['#sel#'])) {
					if($r['#sel#'])
						$sel = 'selected="selected"';
				}
				elseif(!is_null($val) and isset($val[$r['#id#']]))
					$sel = 'selected="selected"';
					
				$texthtml .= '<option value="'.$r['#id#'].'" '.$sel.' class="selpad'.$flag.'">'.$r['#name#'].'</option>';
			}
			if(isset($r['#item#']) and count($r['#item#']))
				$texthtml .= selectitem($r['#item#'],$val,($flag+1));//.'&#160;--'
		}
	return $texthtml;*/
}


function formSelectGenerator($data,$val=NULL,$flag=0,&$openG=false) {
	$texthtml = ''."\n";
	if(is_array($data) and count($data))
		foreach($data as $r) {
			//_substr($r['#name#'],0,60).(_strlen($r['#name#'])>60?'...':'')
			if(isset($r['#item#']) and count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0) {
				//if($flag>0)
				//	$r['#name#'] = str_repeat("&#160;&#160;", $flag).' '.$r['#name#'];
				if($openG)
					$texthtml .= '</optgroup>'."\n";
				$texthtml .= '<optgroup label="'.$r['#name#'].'">'."\n";
				$openG = true;
			}
			else {
				if($flag>0 and !$openG)
					$r['#name#'] = str_repeat("&#160;&#160;", $flag).' '.$r['#name#'];
				$sel = '';
				if(!is_null($val)) {
					if(isset($val[$r['#id#']]))
						$sel = 'selected="selected"';
				}
				elseif(isset($r['#sel#'])) {
					if($r['#sel#'])
						$sel = 'selected="selected"';
				}
					
				$texthtml .= "\t".'<option value="'.$r['#id#'].'" '.$sel.'>'.$r['#name#'].'</option>'."\n";
			}

			if(isset($r['#item#']) and count($r['#item#']))
				$texthtml .= formSelectGenerator($r['#item#'],$val,($flag+1),$openG);//.'&#160;--'

			if(isset($r['#item#']) and count($r['#item#']) and isset($r['#checked#']) and $r['#checked#']==0 and !$flag) {
				$texthtml .= '</optgroup>'."\n";
				$openG=false;
			}
		}
	return $texthtml;
}