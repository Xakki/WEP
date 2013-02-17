<?php
/**
 * Меню управления сайтом
 * @type Control
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */

	function tpl_fcontrol(&$data) {
		//include_once(getPathTheme(true).'/php/superlist.php');
		$html = '<div class="superMenu">';
		$html .= tpl_topmenuFE($data['button']['topmenu'], $data['button']['firstpath'], $data['button']['_clp']);
		$html .= '</div>';
		return $html;
	}


	function tpl_topmenuFE(&$data, $firstpath, $httpQuery=array()) {
		global $_CFG, $_tpl;
		$_tpl['onload'] .= 'wep.hTopPos=100;';
		$temp_topmenu = '';
		if(count($data)) 
		{ //MENU
			setCss('button');
			include_once(getPathTheme(true).'php/formSelect.php');
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
				
				if($r['type']=='select') {
					$temp_topmenu .= '<div class="'.$r['type'].($r['sel']?' selected':'').'" style="'.$r['style'].'"';
					$temp_topmenu .= ' onclick="return false;"><span class="caption">'.$r['caption'].'</span> <select class="'.$r['css'].'" title="'.$r['title'].'"';
					if(count($r)>2)
						$temp_topmenu .= 'if(this.options[this.selectedIndex].value) onchange="return ShowTools(\''.$_CFG['_HREF']['wepJS'].'?_view=list&'.$href.'&_type=edit&content_id=\'+this.options[this.selectedIndex].value);"';
					else
						$temp_topmenu .= ' onchange="if(this.options[this.selectedIndex].value) return wep.load_href(\'/\'+this.options[this.selectedIndex].value+\'.html\');"';
					$temp_topmenu .= '>'.tpl_formSelect($r['list']).'</select>';
					$temp_topmenu .= '</div>';
				}
				else {
					$temp_topmenu .= '<a class="'.$r['type'].($r['sel']?' selected':'').'" style="'.$r['style'].'"';
					//$temp_topmenu .= ' onclick="return wep.load_href(\''.$firstpath.$href.'\')"';
					
					//if(isset($r['is_popup']) and $r['is_popup'])
					if(isset($r['link'])) {
						$temp_topmenu .= ' target="_blank" href="'.$r['link'].'"';
					}
					else {
						$temp_topmenu .= ' href="'.$firstpath.$href.'"';
						$temp_topmenu .= ' onclick="return ShowTools(\''.$_CFG['_HREF']['wepJS'].'?_view=list&'.$href.'\');"';//, \'tools_block\'
					}
					$temp_topmenu .= '><span class="'.$r['css'].'" title="'.$r['title'].'"';
					$temp_topmenu .= '>'.$r['caption'].'</span>';
					$temp_topmenu .= '</a>';
				}
				
			}
		}
		return $temp_topmenu;
	}