<?php
	function tpl_messages(&$data) {
		$html = '';
		if(isset($data) and count($data) and is_array($data)) {
			$html .= '<div class="messages">';
			foreach($data as $r){
				$html .= '<div class="'.$r['name'].'">'.$r['value'].'</div>';
			}
			$html .= '</div>';
		}
		$html .= '';
		return $html;
	}
