<?php

	$DATA = array();
	if($_GET['_view']=='list') {
		$MODUL->_clp = '_view=list&amp;_modul='.$MODUL->_cl.'&amp;';
		$param = array('ajax'=>1, 'firstpath'=>$_CFG['PATH']['wepname'] . '/index.php?_view=list&');
		list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);
		$mess = '';
			if(isset($DATA['formcreat']) and count($DATA['formcreat'])) {
				if($flag==1) {
					end($HTML->path);prev($HTML->path);
					$_tpl['onload'] .= 'alert(\''.$HTML->transformPHP($DATA['formcreat'],'messages').'\');';
				}
				else {
					$DATA['formcreat']['path'] = $HTML->path;
					$html = $HTML->transformPHP($DATA,'formcreat');
					$_tpl['onload'] .= 'JSFR("form");';
				}
			}
			elseif(isset($DATA['static']) and $DATA['static']) {
				$html= '';
				if(isset($DATA['messages']) and count($DATA['messages'])) $html .= $HTML->transformPHP($DATA,'messages');
				$html .= $DATA['static'];
			}
			elseif(isset($DATA['formtools']) and count($DATA['formtools'])) {
				if(isset($DATA['formtools'][1]['form']))
					$DATA['formtools'] = $DATA['formtools'][1];
				$html = $HTML->transformPHP($DATA,'formtools');
				$_tpl['onload'] .= 'wep.jsForm(\'#form_tools_'.$_REQUEST['_func'].'\',{\'insertObj\':\'#tools_block\'});';
			}
			elseif($flag!=3) {
				end($HTML->path);
				$_tpl['onload'] = 'alert(\''.$HTML->transformPHP($DATA['superlist'],'messages').'\');wep.load_href("'.str_replace('&amp;','&',key($HTML->path)).'");';
			}else {
				$DATA['superlist']['path'] = $HTML->path;
				$html = $HTML->transformPHP($DATA,'superlist');
			}
	}
	elseif($_GET['_view']=='contentIncParam') {
		$CT = &$MODUL->childs['content'];
		$CT->fields_form = array();
		$_POST['funcparam'] = htmlspecialchars_decode($_POST['funcparam']);
		if($form = $CT->getContentIncParam($_POST,true) and count($form)) {
			if($CT->kFields2FormFields($form)) {
				$data['form'] = &$form;
				$html2 = $HTML->transformPHP($data,'form');
			}
			$_tpl['onload'] .= 'jQuery(\'#tr_funcparam\').hide();';
		}
		else {
			$_tpl['onload'] .= 'jQuery(\'#tr_funcparam\').show();';
		}
	}
	else
		$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Параметры заданны неверно!</div>\',1);';


	include($_CFG['_PATH']['core'].'/includesrc.php');
	fileInclude($_CFG['fileIncludeOption']);
	arraySrcToFunc();
	

