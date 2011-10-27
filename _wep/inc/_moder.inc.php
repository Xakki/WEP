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

			$MODUL->_clp = '_view=list&_modul='.$MODUL->_cl.'&';
			$param = array('firstpath'=>$PGLIST->current_path.'?','phptemplate'=>$FUNCPARAM[1]);
			list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

			reset($HTML->path);
			//$firstpath = key($HTML->path);
			array_shift($HTML->path);
			$HTML->path = $this->pageinfo['path'] = $this->pageinfo['path']+$HTML->path;
			end($HTML->path);
			$cp = current($HTML->path);
			if(is_array($cp)) {
				$cp = $this->getHref($cp['id'],true).'?';
			}else
				$cp = key($HTML->path);
			if(($_GET['_modul'] == $MODUL->_cl) && ($_GET['_type']=="add" or $_GET['_type']=="edit")) {
				if($flag==1) {
					prev($HTML->path);
					$_SESSION['mess']=$DATA['formcreat']['messages'];
					$cp = current($HTML->path);
					if(is_array($cp)) {
						$cp = $this->getHref($cp['id'],true).'?';
					}else
						$cp = key($HTML->path);
					static_main::redirect(str_replace("&amp;", "&", $cp));
				}
				else {
					$DATA['formcreat']['firstpath'] = key($HTML->path);
					$html = $HTML->transformPHP($DATA,'formcreat');
				}
			}elseif($flag!=3) {
				$_SESSION['mess']=$DATA[$FUNCPARAM[1]]['messages'];
				static_main::redirect(str_replace("&amp;", "&", $cp));
			}else {
				if(!isset($_SESSION['mess'])) 
					$_SESSION['mess']= array();
				$DATA[$FUNCPARAM[1]]['messages'] += $_SESSION['mess'];
				$DATA[$FUNCPARAM[1]]['firstpath'] = $cp;

				$html = $HTML->transformPHP($DATA,$tplphp);
				$_SESSION['mess'] = array();
			}

		}
		else
			$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$FUNCPARAM[0].' запрещён администратором</div>';
	}
	return $html;
