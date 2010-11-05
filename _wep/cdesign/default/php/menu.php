<?
	function tpl_menu(&$data) {
		$html = '';
		//print_r('<pre>');print_r($data);print_r('</pre>');exit();
		if(isset($data) and count($data)) {
			//$html .= '<div class="menu">';
			//$last = count($data);
			foreach($data as $k=>$r) {
				if($r['sel'])
					$r['name'] = '<span>'.$r['name'].'</span>';
				$html .= '<a href="'.$r['href'].'" '.$r['attr'].'>'.$r['name'].'</a>';
				if(count($r['items'])) {
					$html .= tpl_menu($r['items']);
				}
			}
			//$html .= '</div>';
		}
		return $html;
	}
?>