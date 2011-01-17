<?

function tpl_formcreat(&$data) {
	$type_info = array(
		'default' => array(
			'xtype' => '',
			'value' => 'value'
		),
		'checkbox' => array(
			'xtype' => 'checkbox',
			'value' => 'checked'
		)

	);

	$fields = array();

	if(isset($data['form']) and count($data['form']))
	{
		unset($data['form']['_*features*_']);
		unset($data['form']['_info']);
		unset($data['form']['sbmt']);
		
		$i = 0;
		foreach ($data['form'] as $k=>$r)
		{
			$fields[$i] = array(
				'name' => $k,
				'fieldLabel' => $r['caption'],
//				'value' => $r['value']
			);

			if (isset($type_info[$r['type']]))
			{
				if ($type_info[$r['type']] != '')
					$fields[$i]['xtype'] = $type_info[$r['type']]['xtype'];
				$fields[$i][ $type_info[$r['type']]['value'] ] = $r['value'];
			}
			else
			{
				$fields[$i]['xtype'] = $type_info['default']['xtype'];
				$fields[$i][ $type_info['default']['value'] ] = $r['value'];
			}
			
			$i++;
		}
	}
	
	return json_encode($fields);
	
/*
	$data = array(
		array(
			'fieldLabel' => 'Name',
			'name' => 'company'
		),
		array(
			'fieldLabel' => 'Price',
			'name' => 'price'
		),
		array(
			'fieldLabel' => '% Change',
			'name' => 'pctChange'
		),
		array(
			'xtype' => 'datefield',
			'fieldLabel' => 'Last Updated',
			'name' => 'lastChange'
		),
		array(
			'xtype' => 'radiogroup',
			'columns' => 'auto',
			'fieldLabel' => 'Rating',
			'name' => 'rating',
			'items' => array(
				array(
					'inputValue' => '0',
					'boxLabel' => 'A',
				),
				array(
					'inputValue' => '1',
					'boxLabel' => 'B',
				),
				array(
					'inputValue' => '2',
					'boxLabel' => 'C',
				),
			),
		),
	);

*/

	
	 
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
