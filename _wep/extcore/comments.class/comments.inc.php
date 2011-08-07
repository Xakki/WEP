<?
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')	$FUNCPARAM[0] = 'pg';
	if(!isset($FUNCPARAM[1]))	$FUNCPARAM[1] = 'comments';
	if(!isset($FUNCPARAM[2]))	$FUNCPARAM[2] = 1;
	if(!isset($FUNCPARAM[3]))	$FUNCPARAM[3] = 10;
	if(!isset($FUNCPARAM[4]))	$FUNCPARAM[4] = '';
	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_enum['modullist'] = array();
		foreach($this->_CFG['modulprm'] as $k=>$r)
			$this->_enum['modullist'][$r['pid']][$k] = $r['name'].'['.$r['tablename'].']';
		$this->_enum['templatelist'] = array(
			'#ext#comments'=>'#ext#comments',
			'comments'=>'comments',
		);
		$form = array (
			'0'=>array('type'=>'list','listname'=>'modullist', 'caption'=>'Модуль'),
			'1'=>array('type'=>'list','listname'=>'templatelist', 'caption'=>'Шаблон'),
			'2'=>array('type'=>'checkbox', 'caption'=>'Постраничная навигация'),
			'3'=>array('type'=>'int', 'caption'=>'Limit'),
			'4'=>array('type'=>'text', 'caption'=>'Заголовок'),
		);
		return $form;
	}
	
	/*CODE*/
		if(!_new_class('comments',$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль `comments` не установлен</div>';
		} else {

			if(isset($_GET['_oid']) and $_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if(isset($_GET['_pid']) and $_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if(isset($_GET['_id']) and $_GET['_id']!='') $MODUL->id = $_GET['_id'];

			if(static_main::_prmModul('comments',array(1,2))) {

				if(isset($_CFG['singleton'][$FUNCPARAM[0]]) and $_CFG['singleton'][$FUNCPARAM[0]]->id) {
					if(strpos($FUNCPARAM[1],'#ext#')!==false) {
						$FUNCPARAM[1] = str_replace('#ext#','',$FUNCPARAM[1]);
						$tplphp = array($FUNCPARAM[1], __DIR__ . '/templates/');
					}
					else
						$tplphp = $FUNCPARAM[1];
					$DATA = $MODUL->displayList(array(
						'modul'=>$FUNCPARAM[0],
						'modul_id'=>$_CFG['singleton'][$FUNCPARAM[0]]->id,
						'pn'=>$FUNCPARAM[2],
						'cntpage'=>$FUNCPARAM[3],
						)
					);
					$DATA = array($FUNCPARAM[1]=>array(
						'items'=>$DATA,
						'headname'=>$FUNCPARAM[4],
						'modul'=>$FUNCPARAM[0],
						'modul_id'=>$_CFG['singleton'][$FUNCPARAM[0]]->id,
					));
					$html = $HTML->transformPHP($DATA,$tplphp);
				}
					
/*
				$param = array();
				
				list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

				if($_GET['_type']=="add" or $_GET['_type']=="edit") {
					if($flag==1) {
						end($HTML->path);prev($HTML->path);
						$_SESSION['mess']=$DATA['formcreat']['messages'];
						header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
						die();
					}
					else {
						$DATA['formcreat']['path'] = $HTML->path;
						$html = $HTML->transformPHP($DATA,'formcreat');
					}
				}elseif($flag!=3) {
					end($HTML->path);
					$_SESSION['mess']=$DATA['superlist']['messages'];
					header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
					die();
				}else {
					if(!$_SESSION['mess']) 
						$_SESSION['mess']= array();
					$DATA['superlist']['messages'] += $_SESSION['mess'];
					$DATA['superlist']['path'] = $HTML->path;
					$html = $HTML->transformPHP($DATA,$FUNCPARAM[1]);
					$_SESSION['mess'] = array();
				}

				//$_tpl['onload'] .= "$('.fancyimg').fancybox();";
				*/
			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю `comments` запрещён администратором</div>';
		}
	return $html;
