<?
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 1;
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 1;
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = '';//Показывать меню начиная с уровня ID page {id page, #1 - использовать id первого уровня адреса, #2 итп}
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 'menu';

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', __DIR__);
		$this->_enum['typemenuinc'] = array(
			0=>'выводит всё в виде структуры дерева',
			1=>'выводит все в общем массиве',
			2=>'только начальный уровень от `Уровень вывода данных`',
			3=>'выводить меню только на текущем уровне страницы',
		);
		$temp = 'ownerlist';
		$this->_enum['levelmenuinc'] = $this->_getCashedList($temp);
		$this->_enum['levelmenuinc'][0] = array_merge(array(
			'#0'=>'# первый уровнь адреса',
			'#1'=>'# второй уровнь адреса',
			'#2'=>'# третий уровнь адреса',
			'#3'=>'# четвертый уровнь адреса',
			'#4'=>'# пятый уровнь адреса'),
			$this->_enum['levelmenuinc'][0]);
		$form = array(
			'0'=>array('type'=>'list','listname'=>array('owner','menu'), 'caption'=>'Меню'),
			'1'=>array('type'=>'list','listname'=>'typemenuinc', 'caption'=>'Тип вывода меню','onchange'=>'if(this.value==2) jQuery(\'#tr_flexform_2\').show(); else jQuery(\'#tr_flexform_2\').hide();'),
			'2'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Уровень вывода данных'),
			'3'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
		);
		if($FUNCPARAM[1]!=2)
			$form[0]['1']['style'] = 'display:none;';
		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[3],__DIR__);

	$DATA = array($FUNCPARAM[3]=>$PGLIST->getMap($FUNCPARAM[0],$FUNCPARAM[1],$FUNCPARAM[2]));
	$html .= $HTML->transformPHP($DATA,$tplphp);

	return $html;
