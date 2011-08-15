<?
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 'superlist';
	//$FUNCPARAM[0] - модуль
	//$FUNCPARAM[1] - включить AJAX

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		global $_CFG;
		$this->_getCashedList('phptemplates', __DIR__);
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
		if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
		if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
		if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

		if(static_main::_prmModul($FUNCPARAM[0],array(1,2))) {
			$tplphp = $this->FFTemplate($FUNCPARAM[1],__DIR__);

			$MODUL->_clp = '_view=list&_modul='.$MODUL->_cl.'&';
			$param = array('firstpath'=>$PGLIST->current_path.'?','phptemplate'=>$FUNCPARAM[1]);
			list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

			$firstpath = key($HTML->path);
			array_shift($HTML->path);
			$this->pageinfo['path'] = array_merge($this->pageinfo['path'],$HTML->path);
			$HTML->path = $this->pageinfo['path'];

			if(($_GET['_modul'] == $MODUL->_cl) && ($_GET['_type']=="add" or $_GET['_type']=="edit")) {
				if($flag==1) {
					end($HTML->path);prev($HTML->path);
					$_SESSION['mess']=$DATA['formcreat']['messages'];
					header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
					die();
				}
				else {
					$DATA['formcreat']['firstpath'] = $firstpath;
					$html = $HTML->transformPHP($DATA,'formcreat');
				}
			}elseif($flag!=3) {
				end($HTML->path);
				$_SESSION['mess']=$DATA[$FUNCPARAM[1]]['messages'];
				header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
				die();
			}else {
				if(!$_SESSION['mess']) 
					$_SESSION['mess']= array();
				$DATA[$FUNCPARAM[1]]['messages'] += $_SESSION['mess'];
				$DATA[$FUNCPARAM[1]]['firstpath'] = $firstpath;

				$html = $HTML->transformPHP($DATA,$tplphp);
				$_SESSION['mess'] = array();
			}

		}
		else
			$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$FUNCPARAM[0].' запрещён администратором</div>';
	}
	return $html;
