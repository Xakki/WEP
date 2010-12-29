<?
	function tpl_pgmap(&$data,$attr='') {
		$html = '';
		if(isset($data) and count($data)) {
			$html .= '<ul class="pgmap'.$attr.'">';
			foreach($data as $k=>$r) {
				if(!$r['name']) continue;
				$html .= '<li';
				if(isset($r['hidechild']))
					$html .= ' style="list-style:none inside none;"';
				$html .= '>';
				if($r['sel'])
					$r['name'] = '<span>'.$r['name'].'</span>';
				if(isset($r['hidechild']))
					$html .= '<span class="foldedul clickable" onclick="ulToggle(this,\'unfoldedul\')"></span>';
				$html .= '<a href="'.$r['href'].'">'.$r['name'].'</a>';
				if(count($r['#item#'])) {
					$html .= tpl_pgmap($r['#item#'],(isset($r['hidechild'])?' sdsd':''));
				}
				$html .= '</li>';
			}
			$html .= '</ul>';
		}
		return $html;
	}
?>