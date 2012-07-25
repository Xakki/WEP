<?php

class static_super {

	static function modulMenu(&$_this) {//, $row=array()

		$topmenu = array();
		if(!isset($_this->data[$_this->id]))
			$_this->id = null;

		if ($_this->_prmModulAdd()) {
			$t = array('_type' => 'add');
			if ($_this->id)
				$t[$_this->_cl . '_id'] = $_this->id;
			$topmenu['add'] = array(
				'href' => $t,
				'caption' => 'Добавить - ' . $_this->caption,
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-add',
				'is_popup' => true,
			);
		}


		if ($_this->id) {
			//if(isset($_this->data[$_this->id]))
			$data = $_this->data;
			//else
			//	$data = $_this->_select();

			$topmenu['select_'.$_this->_cl ] = array(
				'href' => array($_this->_cl . '_id' => '', '_type' => 'edit'),
				'caption' => $_this->caption,
				'sel' => 0,
				'type' => 'select',
				'css' => '',
				'list' => $_this->_forlist($_this->_getCashedList('list'), 0, $_this->id),
			);

			if($_this->_prmModulEdit($data))
				$topmenu['edit'] = array(
					'href' => array('_type' => 'edit', $_this->_cl . '_id' => $_this->id),
					'caption' => 'Редактированть - ' . $_this->caption,
					'sel' => 0,
					'type' => 'button',
					'css' => 'button-edit',
					'is_popup' => true,
				);

			if($_this->mf_actctrl and $data[$_this->id][$_this->mf_actctrl])
				$topmenu['act'] = array(
					'href' => array('_type' => 'dis', $_this->_cl . '_id' => $_this->id),
					'caption' => 'Отключить',
					'sel' => 0,
					'type' => 'button',
					'css' => 'button-1',
					'onClick'=> '',
				);
			else
				$topmenu['act'] = array(
					'href' => array('_type' => 'act', $_this->_cl . '_id' => $_this->id),
					'caption' => 'Включить',
					'sel' => 0,
					'type' => 'button',
					'css' => 'button-0',
					'onClick'=> '',
				);

			$topmenu['del'] = array(
				'href' => array('_type' => 'del', $_this->_cl . '_id' => $_this->id),
				'caption' => 'Удалить',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-del'
			);
		}
		$topmenu[] = array('type'=>'split');


		if (isset($_this->config_form) and count($_this->config_form) and static_main::_prmModul($_this->_cl, array(13)))
			$topmenu['Configmodul'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Configmodul'),
				'caption' => 'Настроика модуля',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-config',
				'is_popup' => true,
			);
		if ($_this->mf_indexing and static_main::_prmModul($_this->_cl, array(12)))
			$topmenu['Reindex'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Reindex'),
				'caption' => 'Переиндексация',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-reindex',
				'is_popup' => true,
			);
		if ($_this->cf_reinstall and static_main::_prmModul($_this->_cl, array(11)))
			$topmenu['Reinstall'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Reinstall'),
				'caption' => 'Переустановка',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-reinstall',
				'is_popup' => true,
			);
		if ($_this->cf_filter and $_this->_prmSortField()) {
			$topmenu['Formfilter'] = array(
				'href' => array('_type' => 'tools', '_func' => 'Formfilter'),
				'caption' => 'Фильтр',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-filter',
				'is_popup' => true,
			);

		}
		if ($_this->mf_statistic) {
			$t = array('_type' => 'static', '_func' => 'Statsmodul');
			if ($_this->owner and $_this->owner->id)
				$t['_oid'] = $_this->owner->id;
			$topmenu['Statsmodul'] = array(
				'href' => $t,
				'caption' => 'Статистика',
				'sel' => 0,
				'type' => 'button',
				'css' => 'button-stats',
				'is_popup' => true,
			);
		}

		// Групповые операции
		$sg = 0;
		if (isset($_COOKIE['SuperGroup'][$_this->_cl])) {
			$sg += count($_COOKIE['SuperGroup'][$_this->_cl]);
		}
		$t = array('_type' => 'tools', '_func' => 'SuperGroup');
		$topmenu['SuperGroup'] = array(
			'href' => $t,
			'caption' => 'Групповая операция</span><span class="wepSuperGroupCount" title="Кол-во выбранных элементов">' . $sg,
			'title' => 'Групповая операция',
			'sel' => 0,
			'type' => 'button',
			'css' => 'button-SuperGroup',
			'style' => (!$sg ? 'display:none;' : ''),
			'is_popup' => true,
		);

		// TOOLS
		if(count($_this->cf_tools)) {
			foreach($_this->cf_tools as $r) {
				$topmenu[$r['func']] = array(
					'href' => array('_type' => 'tools', '_func' => $r['func']),
					'caption' => $r['name'],
					//'sel' => 0,
					'type' => 'button',
					'css' => $r['func'],
					'is_popup' => true,
					//'style' => (!$sg ? 'display:none;' : '')
				);
			}
		}

		$topmenu[] = array('type'=>'split');

		/*if ($_this->owner and count($_this->owner->childs) and $_this->owner->id)
			foreach ($_this->owner->childs as $ck => &$cn) {
				if ($ck != $_this->_cl and $cn->_prmModulShow()) { //count($cn->fields_form) and 
					$topmenu['ochild_' . $ck] = array(
						'href' => array($_this->_cl . '_id' => $_this->owner->id, $_this->_cl . '_ch' => $ck),
						'caption' => $cn->caption . '(' . $cn->getListCount() . ')',
						'sel' => 0,
						'list' => $_this->_forlist($_this->_getlist('list'), 0),
						'type' => 'select',
					);
				}
			}*/

		if ($_this->mf_istree and count($_this->childs) and $_this->id)
			foreach ($_this->childs as $ck => &$cn) {
				if (count($cn->fields_form) and $ck != $_this->_cl and $cn->_prmModulShow())
					$t = array(
						$ck . '_id' => '',
						'_type' => 'edit', 
						$_this->_cl . '_ch' => $ck, 
						$_this->_cl . '_id' => $_this->id, 
					);

					if ($cn->_prmModulAdd()) {
						$topmenu['add_' . $ck] = array(
							'href' => $t+array('_type' => 'add'),
							'caption' => 'Добавить ' . $cn->caption,
							'sel' => 0,
							'type' => 'button',
							'css' => 'button-add'
						);
					}

					$topmenu['child' . $ck] = array(
						'href' => $t,
						'caption' => $cn->caption . '(' . $cn->getListCount() . ')',
						'sel' => 0,
						'list' => $cn->_forlist($cn->_getCashedList('list'), 0),
						'type' => 'select',
					);
			}
			
		return $topmenu;
	}

}
