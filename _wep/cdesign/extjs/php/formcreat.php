<?

function tpl_formcreat(&$data) {

	$fields = array();

	if(isset($data['form']) and count($data['form']))
	{
		unset($data['form']['_*features*_']);
		unset($data['form']['_info']);
		unset($data['form']['sbmt']);
		
		foreach ($data['form'] as $k=>$r)
		{
			$input_data = array(
				'name' => $k,
				'caption' => $r['caption'],
				'type' => $r['type'],
				'value' => $r['value']
			);
			if (isset($r['valuelist']))
			{
				$input_data['valuelist'] = $r['valuelist'];
			}
			if (isset($r['multiple']))
			{
				$input_data['multiple'] = $r['multiple'];
			}

			$fields[] = get_js_field($input_data);
		}
	}

	
	return json_encode($fields);
 
}

/***************************************************
 * возвращает extjs поле контейнера Ext.forms
 * принимает массив со следующими ключами:
 * - name
 * - caption
 * - type
 * - value
 * - valuelist
 * - multiple
 * ***************************************/
function get_js_field($data)
{
	$type_info = array(
		'default' => array(
			'xtype' => 'textfield',
			'value' => 'value'
		),
		'checkbox' => array(
			'xtype' => 'checkbox',
			'value' => 'checked'
		),
		'list' => array(
			'xtype' => 'combo',
			'mode' => 'local',
			'emptyText' => '',
			'value' => 'value',
		),
		'multiple1' => array(
			'xtype' => 'multiselect',
			'mode' => 'local',
			'emptyText' => '',
			'value' => 'value',
			'delimiter' => '|',
		),
		'multiple2' => array(
			'xtype' => 'itemselector',
			'mode' => 'local',
			'emptyText' => '',
			'value' => 'value',
			'delimiter' => '|',
			'multiselects' => array(
				array(
					'width' => 250,
					'height' => 200,
				),
				array(
					'width' => 250,
					'height' => 200,
				)
			),
		),

	);

	$field = array(
		'name' => $data['name'],
		'fieldLabel' => $data['caption'],
	);

	if (isset($type_info[$data['type']]))
		$type = $data['type'];
	else
		$type = 'default';

	if ($type == 'list' && isset($data['multiple']))
	{
		if ($data['multiple'] == 2)
			$type = 'multiple1';
		elseif ($data['multiple'] == 1)
			$type = 'multiple1';
	}

	if (isset($data['valuelist']))
	{
		foreach ($data['valuelist'] as $k=>$r)
		{
			$field['store'][] = array($r['#id#'], $r['#name#']);
		}
	}



	$field[ $type_info[$type]['value'] ] = $data['value'];
	unset($type_info[$type]['value']);

	$field = array_merge($field, $type_info[$type]);

	return $field;

}


/*
	function tpl_formcreat(&$data) {
		global $HTML;
		if(isset($data['path']) and count($data['path'])) {
			include_once($HTML->_cDesignPath.'/php/path.php');
			$html = tpl_path($data['path'],1);// PATH
		}
		$html .= '<div class="divform'.($data['css']?' '.$data['css']:'').'"';
		if($data['style'])
			$html .= ' style="'.$data['style'].'"';
		$html .= '>';
		if(isset($data['messages']) and count($data['messages'])) {
			include_once($HTML->_cDesignPath.'/php/messages.php');
			$html .= tpl_messages($data['messages']);// messages
		}
		if(isset($data['form']) and count($data['form'])) {
			include_once($HTML->_cDesignPath.'/php/form.php');
			$attr = $data['form']['_*features*_'];
			if (isset($attr['enctype']))
				if ($attr['enctype'] == '')
					$enctype = '';
				else
					$enctype = ' enctype="'.$attr['enctype'].'"';
			else
				$enctype = ' enctype="multipart/form-data"';
			$html .= '<form id="form_'.$attr['name'].'" method="'.$attr['method'].'"'.$enctype.' action="'.$attr['action'].'" '.($attr['onsubmit']?'onsubmit="'.$attr['onsubmit'].'"':'').'>';
			$html .= tpl_form($data['form']).'</form>';
		}
		$html .= '</div>';
		return $html;
	}
//<!--<div class="dscr"><span style="color:#F00">*</span> - обязательно для заполнения</div>
//<div class="dscr"><span style="color:#F00">**</span> - обязательно для заполнения хотябы одно поле</div>-->
*/
