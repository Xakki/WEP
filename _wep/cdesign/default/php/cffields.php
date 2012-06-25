<?php
	function tpl_cffields($k, $r) {
		$enum = array(
			'type'=>array(
				array('#name#'=>'text', '#id#'=>'text'),
				array('#name#'=>'textarea', '#id#'=>'textarea'),
				array('#name#'=>'ckedit', '#id#'=>'ckedit'),
				array('#name#'=>'int', '#id#'=>'int'),
				array('#name#'=>'list', '#id#'=>'list'),
				array('#name#'=>'ajaxlist', '#id#'=>'ajaxlist'),
				array('#name#'=>'date', '#id#'=>'date'),
				array('#name#'=>'radio', '#id#'=>'radio'),
				array('#name#'=>'checkbox', '#id#'=>'checkbox'),
				array('#name#'=>'file', '#id#'=>'file'),
				array('#name#'=>'color', '#id#'=>'color'),
				array('#name#'=>'password', '#id#'=>'password'),
				array('#name#'=>'hidden', '#id#'=>'hidden'),
				array('#name#'=>'submit', '#id#'=>'submit'),
				array('#name#'=>'html', '#id#'=>'html'),
				array('#name#'=>'info', '#id#'=>'info'),
			),	
		);
		$html = '<div class="form-caption">'.$r['caption'].'</div>
		<ul class="cffields">';
		foreach($r['value'] as $kf=>$rf) {
			$html .= tpl_cffields_item($k, $kf, $rf, $enum);
		}

		$html .= '</ul>';
		return $html;
	}

	function tpl_cffields_item($k, $kf, $rf, &$enum) {
		return '<li>
			<select name="'.$k.'['.$kf.'][type]">'.selectitem($enum['type'], $rf['type']).'</select>
		</li>';
	}
