<?php
	function tpl_rubricMenu(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			$html = '<div class="rubricMenu">
			'.($data['#title#']?'<h3>'.$data['#title#'].'</h3>':'').'';
			$html .= tpl_rubric_rev($data['#item#'],'',$data['#page#']);
			$html .= '</div>';
		}
		return $html;
	}
	function tpl_rubric_rev(&$data,$pref='',$pgid=0) {
		$html = '<ul>';
		foreach($data as $k=>$r) {
			$html .= '<li>';
			if($r['img']) {
				$html .= '<img src="'.$r['img'].'" alt="'.$r['name'].'"/>';
			}
			$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="'.($r['#sel#']?'selected':'').'">';
			$html .= $r['name'].'</a>';
			if(isset($r['#item#']) and count($r['#item#'])) {
				//$pref .= ' - ';
				$html .= tpl_rubric_rev($r['#item#'],$pref,$pgid);
			}
			$html .= '</li>';
		}
		$html .= '</ul><div class="clear"></div>';
		return $html;
	}