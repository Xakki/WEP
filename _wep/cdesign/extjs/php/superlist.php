<?php

function tpl_superlist(&$data)
{
	if (isset($data['_view']) && $data['_view'] == 'listcol')
	{

		$cols = array();

		$tdflag = 0;
		$i = 0;
		foreach($data['data']['thitem'] as $k=>$r) {
			if(!$tdflag) {
				if(isset($r['onetd'])){
					$tdflag = 1;
					$r['value'] = $r['onetd'];
				}	
				$cols[$i] = array(
					'header' => $r['value'],
					'dataIndex' => $k,
					'editor' => 'new fm.TextField({allowBlank: false})',
				);
				if ($k == 'id')
				{
					$cols[$i]['id'] = 'id';
				}
				
				$fields[$i] = array(
					'name' => $k,
					'type' => 'string'
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
		
		
		$fields[] = array(
			'name' => 'act',
			'type' => 'bool'
		);
		
		

		return json_encode(array('columns' => $cols, 'fields' => $fields)); 
	}


	$output = array();
	if(count($data['data']['item']))
	{
		$i = 0;
		foreach($data['data']['item'] as $k=>$r) {
			$tdflag = 0; 		
			
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
