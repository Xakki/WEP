<?
	function tpl_messages(&$data) {
		$html = '';
		if(isset($data) and count($data) and is_array($data)) {
			$html .= '<div class="messages">';
			foreach($data as $r){
				$html .= '<div class="'.(isset($r['name'])?$r['name']:$r[0]).'">'.(isset($r['value'])?$r['value']:$r[1]).'</div>';
			}
			$html .= '</div>';
		}
		$html .= '';
		return $html;
	}
