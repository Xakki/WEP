<?php

function tpl_superlist(&$data)
{
	if (isset($data['_view']) && $data['_view'] == 'listcol')
	{
		$type_info = array(
			'default' => array(
				'editor' => 'new fm.TextField({allowBlank: false})',
				'type' => 'string',
			),
		);
		

		$cols = array();
		$fields = array();
		$field_info = array(); 
		$children = array();

		if (isset($data['data']['thitem']))
		{
			if (isset($data['data']['item']) && !empty($data['data']['item']))
			{
				$tmp_arr = current($data['data']['item']);
				foreach ($tmp_arr['tditem'] as $k=>$r)
				{
					if (isset($type_info[$r['type']]))
						$field_info[$k] = $type_info[$r['type']];
					else
						$field_info[$k] = $type_info['default'];
				}
				
				if (isset($tmp_arr['child']))
				{
					foreach ($tmp_arr['child'] as $k=>$r)
					{
						$child = json_encode(array(
							'cl' => $k,
							'title' => $r['value']
						));

						$children[] = array(
							'header' => $r['value'],
							'xtype' => 'actioncolumn',
							'width' => 50,
							'items' => array(
								array(
									'icon' => '_wep/cdesign/extjs/img/icon-tab-folder.png', // '/tpl/master-default/img/icon-tab-folder.gif'
									'tooltip' => 'LIST',
								)
							),
							'handler' => 'function() {
								showChildren(obj, \''.$child.'\');
							}',
						);
					}
				}
				
				if (isset($tmp_arr['istree']))
				{
					$child = json_encode(array(
						'cl' => $data['data']['cl'],
						'title' => $tmp_arr['istree']['value']
					));

					$children[] = array(
						'header' => $tmp_arr['istree']['value'],
						'xtype' => 'actioncolumn',
						'width' => 50,
						'items' => array(
							array(
								'icon' => '_wep/cdesign/extjs/img/icon-tab-folder.png', // '/tpl/master-default/img/icon-tab-folder.gif'
								'tooltip' => 'LIST',
							)
						),
						'handler' => 'function() {
							showChildren(obj, \''.$child.'\');
						}',
					);
				}
				
			}

			$tdflag = 0;
			$i = 0;

			if(!isset($data['data']['thitem']['id']))
			{
				$id_col = array('id' => array('value' => '№'));
				$data['data']['thitem'] = array_merge($id_col, $data['data']['thitem']);
			}

			foreach($data['data']['thitem'] as $k=>$r) {
				if(!$tdflag) {
					if(isset($r['onetd'])){
						$tdflag = 1;
						$r['value'] = $r['onetd'];
					}
					$cols[$i] = array(
						'header' => $r['value'],
						'dataIndex' => $k,
						'editor' => $field_info[$k]['editor'],
					);
					if ($k == 'id')
					{
						$cols[$i]['id'] = 'id';
					}

					$fields[$i] = array(
						'name' => $k,
						'type' => $field_info[$k]['type'],
					);

					$i++;
				}
				if($r['onetd']=='close') $tdflag = 0;

			}
			$cols[] = array(
				'header' => 'Вкл/Откл',
				'xtype' => 'checkcolumn',
				'dataIndex' => 'act',
				'width' => 55,
	//			'editor' => array(
	//				'xtype' => 'checkbox'
	//			),
			);


			$cols[] = array(
				'header' => 'Редактировать',
				'xtype' => 'actioncolumn',
				'width' => 50,
				'items' => array(
					array(
						'icon' => '_wep/cdesign/extjs/img/icon-tab-folder.png', // '/tpl/master-default/img/icon-tab-folder.gif'
						'tooltip' => 'LIST',
					)
				),
				'handler' => 'function() {
					showForm(obj);
				}',
			);

			$cols[] = array(
				'header' => 'Удалить',
				'xtype' => 'actioncolumn',
				'width' => 50,
				'items' => array(
					array(
						'icon' => '_wep/cdesign/extjs/img/icon-tab-folder.png', // '/tpl/master-default/img/icon-tab-folder.gif'
						'tooltip' => 'LIST',
					)
				),
				'handler' => 'function() {
					var SelectionModel = obj.getSelectionModel();
					var msg = "Вы действительно хотите удалить данную запись?";
					if (SelectionModel.selection.record.data.name != undefined)
					{
						msg += " (" + SelectionModel.selection.record.data.id + ")";
					}
					else if (SelectionModel.selection.record.data.name != undefined)
					{
						msg += " (" + SelectionModel.selection.record.data.name + ")";
					}
					Ext.Msg.confirm("Удаление записи",  msg, onDelete, obj);
				}',
			);
			
			$cols = array_merge($cols, $children);


			$fields[] = array(
				'name' => 'act',
				'type' => 'bool'
			);
		}

		return json_encode(array('columns' => $cols, 'fields' => $fields)); 
	}


	$output = array();
	if(count($data['data']['item']))
	{
		$i = 0;
		foreach($data['data']['item'] as $k=>$r) {
			$tdflag = 0; 		
			
			$output[$i]['id'] = $r['id'];

			foreach($r['tditem'] as $ktd=>$tditem) {
				if(!$tdflag) {
					if(isset($tditem['onetd'])) $tdflag = 1;

					$output[$i][$ktd] = $tditem['value'];
				}	 
				if(isset($tditem['onetd']) and $tditem['onetd']=='close')
					$tdflag = 0;
			}
			
			$output[$i]['act'] = true;
			if ($r['istree']['cnt'] == 0)
			{
				$output[$i]['leaf'] = true;
			}
			
			$i++;
		}
	}

//	print_r($data['data']['item']);

	return json_encode($output);
		
}
