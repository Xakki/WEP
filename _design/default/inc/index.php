<?php

$_tpl['modulstree'] = $eval = '';
$_tpl['title'] = 'WEP';


if (!$_GET['_modul'] or !(isset($_GET['_view']) or isset($_GET['_type']))) {
	$_tpl['text'] .= '<div style="position:absolute;top:50%;left:50%;"><div style="width:200px;height:100px;position:absolute;top:-50px;left:-100px;"><img src="/' . getUrlTheme() . 'img/login.gif" width="250" alt="LOGO"/></div></div>';
}
else {
	/*if(count($_GET)==2)
		$SQL->_iFlag = TRUE;*/
	if ($_GET['_view'] == 'list' and $_GET['_modul'] == '_tools') {
		if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] == 0)
			$_tpl['text'] .= include($_CFG['_PATH']['wep_controllers'] . '/tools.php');
		else
			$_tpl['text'] .= '<div style="color:red;">Доступ только АДмиму</div>';
	}
	elseif (!_new_class($_GET['_modul'], $MODUL)) {
		$_tpl['text'] .= '<div style="color:red;">' . date('H:i:s') . ' : Модуль ' . $_GET['_modul'] . ' не установлен</div>';
	}
	elseif ($_GET['_view'] == 'contentIncParam') {
		$CT = & $MODUL->childs['content'];
		$CT->fields_form = array();
		$_POST['funcparam'] = htmlspecialchars_decode($_POST['funcparam']);
		if ($form = $CT->getContentIncParam($_POST, true) and count($form)) {
			if ($CT->kFields2FormFields($form)) {
				$data['form'] = & $form;
				$_tpl['text'] = transformPHP($data, 'form');
			}
			$_tpl['onload'] .= 'jQuery(\'#tr_funcparam\').hide();';
		}
		else {
			$_tpl['onload'] .= 'jQuery(\'#tr_funcparam\').show();';
		}
	}
	else {
		$html = '';
		if (isset($_GET['_oid']) and $_GET['_oid'] != '') $MODUL->owner_id = $_GET['_oid'];
		if (isset($_GET['_pid']) and $_GET['_pid'] != '') $MODUL->parent_id = $_GET['_pid'];
		if (isset($_GET['_id']) and $_GET['_id'] != '') $MODUL->id = $_GET['_id'];
		if (!isset($_GET['_type'])) $_GET['_type'] = '';

		if (static_main::_prmModul($_GET['_modul'], array(1, 2))) {
			if ($_GET['_view'] == 'list') {
				$param = array(
					'sbmt_save' => true,
					'sbmt_close' => true,
					'sbmt_del' => true,
					'firstpath' => ADMIN_BH . '?_view=list&'
				);
//$tt = array();$summ = 0;for($j = 1; $j <= 5; $j++) { $tt[$j] = getmicrotime(); for($i = 1; $i <= 20; $i++) {
				$MODUL->setFilter(1);
				list($DATA, $flag) = $MODUL->super_inc($param, $_GET['_type']);

				// Adept path
				$path = array();
				foreach ($DATA['path'] as $r) {
					$temp = $DATA['firstpath'];
					foreach ($r['path'] as $kp => $rp)
						$temp .= $kp . '=' . $rp . '&';
					$path[$temp] = $r['name'];
				}
				$DATA['path'] = $path;
				end($DATA['path']);
				$curhref = str_replace('&amp;', '&', key($DATA['path']));
				prev($DATA['path']);
				$prevhref = str_replace('&amp;', '&', key($DATA['path']));

				if ($flag === 1) {
					//if(isAjax())
					$_SESSION['mess'] = $DATA['messages'];
					$html .= transformPHP($DATA, 'messages');
					//$_tpl['mylog'] = $DATA;
					if (isset($_POST['sbmt_save']))
						static_main::redirect($curhref);
					else
						static_main::redirect($prevhref);
				}
				elseif ($flag === -1) {
					$html .= transformPHP($DATA, 'messages');
				}
				else {
					$_tpl['title'] .= strip_tags(' : ' . implode(' - ', $DATA['path']));

					if ($MODUL->ver != $_CFG['modulprm'][$MODUL->_cl]['ver']) {
						$html .= 'Версия модуля ' . $MODUL->caption . '[' . $MODUL->_cl . '] (' . $MODUL->ver . ') отличается от версии (' . $_CFG['modulprm'][$MODUL->_cl]['ver'] . ') сконфигурированного для этого сайта. Обновите здесь поля таблицы.';
					}

					if (!isset($_SESSION['mess']) or !is_array($_SESSION['mess']))
						$_SESSION['mess'] = array();
					elseif (count($_SESSION['mess']))
						$DATA['messages'] += $_SESSION['mess'];
					$_SESSION['mess'] = array();

					if (isset($DATA['formcreat']['options']))
						$DATA['formcreat']['options']['prevhref'] = $prevhref;

					$html .= transformPHP($DATA, 'superlist');
				}

//} $tt[$j] = getmicrotime()-$tt[$j]; $summ += $tt[$j]; } echo 'Среднее время = "'.($summ/5).'" ';echo $tt;
			}

		}
		else
			$html = '<div style="color:red;">' . date('H:i:s') . ' : Доступ к модулю ' . $_GET['_modul'] . ' запрещён администратором</div>';
		$_tpl['text'] .= $html;
	}
}


if (!isAjax()) {
	//<base href="'.MY_BH.'/"/>
	$_tpl['meta'] = '
			<title>{#title#}</title>
			<link rel="SHORTCUT ICON" href="{#design#}img/favicon.ico"/>
			<meta charset="utf-8">
			<meta name="keywords" content="WEP"/> 
			<meta name="description" content="CMS"/>
			';
	//<!--<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>-->


	setScript('jquery|wep|script.jquery/form|/main');
	setCss('button|main|/main');

	$DATA = array('adminmenu' => fAdminMenu($_GET['_modul']));
	$_tpl['adminmenu'] = transformPHP($DATA, 'adminmenu');

	selectDebugMode();
	showCmsInfo();
}


