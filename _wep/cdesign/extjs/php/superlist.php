<?php

function tpl_superlist(&$data)
{
	$output = array();

	switch ($data['_view'])
	{
		case 'listcol':
		{

			$type_info = array(
				'default' => array(
					'editor' => array(
						'eval' => 'new fm.TextField({allowBlank: false})',
					),
					'type' => 'string',
				),
			);

			if (isset($data['data']['thitem']))
			{
				$cols = array();
				$fields = array();
				$field_info = array();
				$children = array();
				$top_menu = array();

				if (isset($data['topmenu']))
				{
					foreach ($data['topmenu'] as $k=>$r)
					{
						$top_menu[$k] = true;
					}
				}

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
								'cl' => $k,
								'xtype' => 'actioncolumn',
								'width' => 50,
								'items' => array(
									array(
										'icon' => '_wep/cdesign/extjs/img/icon-tab-folder.png', // '/tpl/master-default/img/icon-tab-folder.gif'
										'tooltip' => 'LIST',
									)
								),
								'handler' => array(
									'eval' => 'function() {
										showChildren(obj, \''.$child.'\');
									}',
								),
							);

						}
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
							'width' => 100,
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

				$fields[] = array(
					'name' => 'act',
					'type' => 'bool'
				);

				$output = array(
					'columns' => $cols,
					'fields' => $fields,
					'children' => $children,
					'pagenum' => $data['pagenum'],
					'top_menu' => $top_menu,
				);
			}
		}
		break;

		case 'list':
		{
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
					$output[$i]['checked'] = true;
					if ($r['istree']['cnt'] == 0)
					{
						$output[$i]['leaf'] = true;
					}

					$i++;
				}
			}
		}
		break;

		default:
		{
			exit('vfvfdvfd');
		}
	}	


	return $output;
}