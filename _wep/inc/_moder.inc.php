<?php
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 'superlist';
	//$FUNCPARAM[0] - модуль
	//$FUNCPARAM[1] - включить AJAX

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		global $_CFG;
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$this->_enum['modullist'] = array();
		foreach($_CFG['modulprm'] as $k=>$r) {
			if($r['active'])
				$this->_enum['modullist'][$r['pid']][$k] = $r['name'];
		}
		$form = array(
			'0'=>array('type'=>'list','listname'=>'modullist', 'caption'=>'Модуль'),
			'1'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
		);
		return $form;
	}

	//$FUNCPARAM[0] - модуль
	//$FUNCPARAM[1] - php template
	if(!$FUNCPARAM[0] or !_new_class($FUNCPARAM[0],$MODUL)) {
		$html = '<div style="color:red;">'.date('H:i:s').' : Модуль '.$FUNCPARAM[0].' не установлен</div>';
	}
	else {
		if(!$FUNCPARAM[1]) $FUNCPARAM[1] = 'superlist';
		if(isset($_GET['_oid']) and $_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
		if(isset($_GET['_pid']) and $_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
		if(isset($_GET['_id']) and $_GET['_id']!='') $MODUL->id = $_GET['_id'];
		if(!isset($_GET['_type'])) $_GET['_type'] = '';
		if(!isset($_GET['_modul'])) $_GET['_modul'] = $FUNCPARAM[0];

		if(static_main::_prmModul($FUNCPARAM[0],array(1,2))) {
			global $HTML;
			$tplphp = $this->FFTemplate($FUNCPARAM[1],dirname(__FILE__));

			$param = array('phptemplate'=>$FUNCPARAM[1]);
			list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

			$DATA['firstpath'] = $this->_CFG['_HREF']['BH'].$PGLIST->current_path;
			if(strpos($DATA['firstpath'],'?')===false)
				$DATA['firstpath'] .= '?';
			else
				$DATA['firstpath'] .= '&';
			// Adept path
			$path = array();
			$temp = $DATA['firstpath'];
			foreach($DATA['path'] as $r) {
				foreach($r['path'] as $kp=>$rp)
					$temp .= $kp.'='.$rp.'&';
				$path[$temp] = $r['name'];
			}
			array_pop($this->pageinfo['path']);
			$DATA['path'] = $this->pageinfo['path'] = $this->pageinfo['path']+$path;

			if(isset($DATA['formcreat'])) {
				end($DATA['path']);prev($DATA['path']);
				$DATA['formcreat']['form']['_*features*_']['prevhref'] = $_CFG['_HREF']['BH'].str_replace('&amp;', '&', key($DATA['path']));
			}

			if(isset($DATA['formcreat']) and $flag==1) {
				$_SESSION['mess']=$DATA['formcreat']['messages'];
				static_main::redirect($DATA['formcreat']['form']['_*features*_']['prevhref']);
			}
			elseif(!isset($DATA['formcreat']) and $flag!=3) {
				$_SESSION['mess']=$DATA['messages'];
				end($DATA['path']);
				static_main::redirect($_CFG['_HREF']['BH'].str_replace("&amp;", "&", key($DATA['path'])));
			}
			else {
				if(!isset($_SESSION['mess']) or !is_array($_SESSION['mess'])) 
					$_SESSION['mess']= array();
				elseif(count($_SESSION['mess']))
					$DATA['messages'] += $_SESSION['mess'];
				unset($DATA['path']);
				$DATA = array($FUNCPARAM[1]=>$DATA);
				$html = $HTML->transformPHP($DATA,$tplphp);
				$_SESSION['mess'] = array();
			}

		}
		else
			$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$FUNCPARAM[0].' запрещён администратором</div>';
	}
	return $html;
