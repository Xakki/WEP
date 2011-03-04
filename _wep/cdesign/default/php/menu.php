<?
	function tpl_menu(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			//$html .= '<div class="menu">';
			//$last = count($data);
			foreach($data as $k=>$r) {
				if($r['sel'])
					$r['name'] = '<span>'.$r['name'].'</span>';
				/*if(strpos($r['attr'],'style="'))
					$r['attr'] = str_replace('style="','style="width:'.$prs.'%;',$r['attr']);
				else
					$r['attr'] .= ' style="width:'.$prs.'%;"';*/
				$html .= '<a href="'.$r['href'].'" '.$r['attr'].'>'.$r['name'].'</a>';
				if(count($r['#item#'])) {
					$html .= tpl_menu($r['#item#']);
				}
			}
			//$html .= '</div>';
		}
		return $html;
	}
?>