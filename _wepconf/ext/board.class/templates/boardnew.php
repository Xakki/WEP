<?php
	function tpl_boardnew(&$data) {
		global $_tpl,$_CFG,$HTML;
		$html = '';
		$comm = false;
		//print_r('<pre>');print_r($data);
		if(!isset($data['items']) or !count($data['items']))
			$html = '';
		else {
			$html = '<ul class="boardnew">';
			foreach($data['items'] as $k=>$r) {
				$html .= '<li>
					<div class="name">
						<a target="_blank" href="'.$r['domen'].''.$r['rubpath'].'/'.$r['path'].'_'.$r['id'].'.html"> ['.date('Y-m-d',$r['datea']).']&#160;'.$r['tname'];
						if(count($r['rname'])) {
							if(is_array($r['rname']))
								$html .= implode('/ ',$r['rname']);
							else
								$html .= '/ '.$r['rname'];
						}
						$html .= $r['name'].'</a>
					</div>
					<div class="text">'.html_entity_decode(static_main::pre_text($r['text'],200),ENT_QUOTES,'UTF-8').'</div>';
				$html .= '</li>';
			}
			$html .= '</ul>';
		}
		$html = str_replace('$','&#036;',$html);
		return $html;
	}
