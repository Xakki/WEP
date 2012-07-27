<?php
	function tpl_fcontrol(&$data) {
		//include_once($HTML->_cDesignPath.'/php/superlist.php');
		$html = '<div class="superMenu">';
		$html .= tpl_topmenuFE($data['button']['topmenu'], '/');
		$html .= '</div>';
		return $html;
	}


	function tpl_topmenuFE(&$data, $firstpath, $httpQuery=array()) {
		global $_CFG, $HTML, $_tpl;
		$temp_topmenu = '';
		if(count($data)) { //MENU
			$_tpl['styles']['button'] = 1;
			include_once($HTML->_cDesignPath.'/php/formSelect.php');
			foreach($data as $r) {
				if($r['type']=='split') {
					$temp_topmenu .= '<div class="split">&#160;</div>';
					continue;
				}

				if(!isset($r['title'])) $r['title'] = $r['caption'];
				if(!isset($r['style'])) $r['style'] = '';
				if(!isset($r['css'])) $r['css'] = '';

				// HREF path
				$href =  array_reverse($r['href']+$httpQuery);
				$href = http_build_query($href);
				
				$temp_topmenu .= '<a class="'.$r['type'].($r['sel']?' selected':'').'" style="'.$r['style'].'"';

				if($r['type']=='select') {
					$temp_topmenu .= ' onclick="return false;"><span class="caption">'.$r['caption'].'</span> <select class="'.$r['css'].'" title="'.$r['title'].'"';
					$temp_topmenu .= ' onchange="return wep.load_href(\''.$firstpath.$href.'\'+this.options[this.selectedIndex].value)"';
					$temp_topmenu .= '>'.tpl_formSelect($r['list']).'</select>';
				}
				else {
					//$temp_topmenu .= ' onclick="return wep.load_href(\''.$firstpath.$href.'\')"';
					$temp_topmenu .= ' href="'.$firstpath.$href.'"';
					if(isset($r['is_popup']) and $r['is_popup'])
						$temp_topmenu .= ' onclick="return ShowTools(\''.$_CFG['_HREF']['wepJS'].'?_view=list&'.$href.'\')"';//, \'tools_block\'
					$temp_topmenu .= '><span class="'.$r['css'].'" title="'.$r['title'].'"';
					$temp_topmenu .= '>'.$r['caption'].'</span>';
				}

				$temp_topmenu .= '</a>';
			}
		}
		return $temp_topmenu;
	}