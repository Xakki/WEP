<?php


if (isset($_GET['_modul']) && isset($_GET['_view'])) {

	$HTML->flag = false;

	$modul_name = (string)$_GET['_modul'];
	$view = (string)$_GET['_view'];

	$data = array();

	if(!_new_class($modul_name, $MODUL)) {
		$data['err_msg'][] = date('H:i:s').' : Модуль '.$modul_name.' не установлен';
	}
	else {
		if(_prmModul($modul_name,array(1,2))) {

			$MODUL->_clp = '_view=list&amp;_modul='.$MODUL->_cl.'&amp;';
			$param = array('sbmtsave'=>1,'close'=>1);
			$MODUL->setFilter(1);
			if (isset($_GET['sort_mode']) && $_GET['sort_mode'] == true) {
				$MODUL->messages_on_page = 1000;
			}
			if (isset($_GET['_type'])) {
				$type = (string)$_GET['_type'];
			}
			else {
				$type = '';
			}

			list($DATA,$flag) = $MODUL->super_inc($param, $type);

			switch ($type)
			{
				case 'tools':
				{
					if (isset($_POST['sbmt'])) {
						if ($DATA['formtools']['messages'][0]['name'] == 'ok') {
							$success = true;
						}
						else {
							$success = false;
						}

						$data = array(
							'success' => $success,
							'msg' => $DATA['formtools']['messages'][0]['value'],
						);
					}
					else {

						unset($DATA['formtools']['form']['_*features*_']);
						unset($DATA['formtools']['form']['_info']);

						if (!empty($DATA['formtools']['form'])) {

							unset($DATA['formtools']['form']['_*features*_']);
							unset($DATA['formtools']['form']['_info']);

							$DATA['js_fields'] = array();
							foreach ($DATA['formtools']['form'] as $k => $r) {
								if (isset($r['valuelist']) && $r['type']=='checkbox') {
									foreach ($r['valuelist'] as $r2) {
										$field = array(
											'name' => $k.'[]',
											'caption' => $r2['#name#'],
											'type' => $r['type'],
											'value' => $r['value'],
											'inputValue' => $r2['#id#'],
										);
										$DATA['js_fields'][] = get_js_field($field);
									}
								}
								else {
									$field = array(
										'name' => $k,
										'caption' => $r['caption'],
										'type' => $r['type'],
										'value' => $r['value'],
										'valuelist' => $r['valuelist'],
									);
									$DATA['js_fields'][] = get_js_field($field);
								}
							}

						}

						$data = $DATA;
						
					}
				}
				break;

				default:
				{
					$DATA['superlist']['_view'] = $view;
					$data = $HTML->transformPHP($DATA,'superlist');
				}
			}		
		}
		else {
			$data['err_msg'][] = date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором';
		}
	}

	echo json_encode($data);
	
}
else {

	unset($_tpl['script']['jquery']);
	$_tpl['script']['utils'] = 1;

	$_tpl['modulstree'] = '';

	if($_CFG['info']['email'])
		$_tpl['contact'] = '<div class="ctd1">e-mail:</div>	<div class="ctd2"><a href="mailto:'.$_CFG['info']['email'].'">'.$_CFG['info']['email'].'</a></div>';
	if($_CFG['info']['icq'])
		$_tpl['contact'] .= '<div class="ctd1">icq:</div><div class="ctd2">'.$_CFG['info']['icq'].'</div>';
	if(isset($_CFG['info']['phone']) and $_CFG['info']['phone'])
		$_tpl['contact'] .= '<div class="ctd1">телефон:</div><div class="ctd2">'.$_CFG['info']['phone'].'</div>';

	$DATA = fXmlSysconf(); $_tpl['sysconf'] = $HTML->transformPHP($DATA,'sysconf');
	$DATA = fXmlModulslist(); $_tpl['modulslist']=$HTML->transformPHP($DATA,'modulslist');
	$_tpl['uname']='<a href="'.$_CFG['PATH']['wepname'].'/login.php?exit=ok" class="exit"><img src="'.$_CFG['PATH']['wepname'].'/cdesign/extjs/img/close48.gif" class="exit" alt="CLOSE"/></a><div class="uname">'.$_SESSION['user']['name'].' ['.$_SESSION['user']['gname'].']</div>';

}
