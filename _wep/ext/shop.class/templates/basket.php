<?php
	function tpl_basket(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			global $_tpl,$HTML;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopMain'] = 1;

			$html = '<div class="basket">';
			$html .= '</div>';
		}
		return $html;
	}
