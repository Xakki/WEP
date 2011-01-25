<?

function tpl_formcreat(&$data) {

//	print_r($data);
//	return '';

	$fields = array();


	if(isset($data['form']) and count($data['form']))
	{
		unset($data['form']['_*features*_']);
		unset($data['form']['_info']);
//		unset($data['form']['sbmt']);
		
		foreach ($data['form'] as $k=>$r)
		{
			$input_data = $r;
			$input_data['name'] = $k;
		
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
			'value_attr' => 'value'
		),
		'text' => array(
			'xtype' => 'textfield',
			'value_attr' => 'value'
		),
		'checkbox' => array(
			'xtype' => 'checkbox',
			'value_attr' => 'checked',
			'inputValue' => 1
		),
		'list' => array(
			'xtype' => 'combo',
			'mode' => 'local',

			'typeAhead' => true,
			'triggerAction' => 'all',

			'value_attr' => 'value',

	/*		'store' => array(
				'eval' => "new Ext.data.ArrayStore({
					fields: ['myId','displayText'],
					data: this.data_store
				});"
			),
			'listeners' => array(
				'eval' =>  "{
					select: function(f,r,i){
					//	alert(r.data.value);
					},
				}"
			),
			'valueField' => 'myId',
			'displayField' => 'displayText'*/
		),
		'multiple1' => array(
			'xtype' => 'multiselect',
			'mode' => 'local',
			'emptyText' => '',
			'value_attr' => 'value',
			'delimiter' => '|',
			'store' => array(
				'eval' => "new Ext.data.ArrayStore({
					fields: ['value', 'name'],
					data: [[1, 'item1'], [2, 'item2']]
				});"
			),
			'valueField' => 'value',
			'displayField' => 'name'
		),
		'multiple2' => array(
			'xtype' => 'itemselector',
			'mode' => 'local',
			'emptyText' => '',
			'value_attr' => 'value',
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
	//		'data' => this.data_store,
			'store' => array(
				'eval' => "new Ext.data.ArrayStore({
					fields: ['value', 'name'],
					 data: [[1, 'item1'], [2, 'item2']]
					
				});"
			),
			'valueField' => 'value',
			'displayField' => 'name'
		),
		'submit' => array(
			'xtype' => 'hidden',
			'value_attr' => 'value'
		),

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

	$field = $type_info[$type];
	$field[$field['value_attr']] = $data['value'];

	unset($field['value_attr']);

	$field['name'] = $data['name'];

	if ($field['xtype'] == 'combo') {
		$field['hiddenName'] = $data['name'];
	}
	
	$field['fieldLabel'] = $data['caption'];
		
	if (isset($data['valuelist']))
	{
		$field['store'] = array();
		foreach ($data['valuelist'] as $k=>$r)
		{
			$field['store'][] = array($r['#id#'], $r['#name#']);
		}
	}

	if (isset($data['mask']['min']))
	{
		$field['allowBlank'] = false;
	}

	if ($data['mask']['width'] > 200 && $field['xtype'] == 'textfield')
	{
		$field['xtype'] = 'textarea';
	}

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
