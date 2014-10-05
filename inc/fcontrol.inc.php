<?php
/**
 * Админка
 * Управление контентом для фронтенда
 * @ShowFlexForm false
 * @type Служебные
 * @ico system.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */

/*if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';
if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = array();*/

// рисуем форму для админки чтобы удобно задавать параметры
/*if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
    $form = array(
        '0'=>array('type'=>'list','listname'=>'modullist', 'caption'=>'Модуль'),
        '1'=>array('type'=>'list','listname'=>'userfieldlist', 'multiple'=>2, 'caption'=>'Выводимые поля'),
        '2'=>array('type'=>'checkbox', 'caption'=>'Включить AJAX форму?'),
    );
    return $form;
}*/
if (!static_main::_prmUserCheck(1)) return '';

plugControl();

/*if(0) {
    _new_class($FUNCPARAM[0],$MODUL);
    $DATA  = array();
    if($Ctitle!='')
        $MODUL->lang['add_name'] = ($Ctitle?$Ctitle:'');

    $argForm = array();
    foreach($FUNCPARAM[1] as $r) {
        if(isset($MODUL->fields_form[$r]))
            $argForm[$r] = $MODUL->fields_form[$r];
    }

    list($DATA['#pg#formcreat'],$this->formFlag) = $MODUL->_UpdItemModul(array('showform'=>1), $argForm);

    $html = transformPHP($DATA,'#pg#formcreat');

}**/

$cssClass = 'hidden';
$html = '';

if (isset($_COOKIE['wepfcontrol']) and $_COOKIE['wepfcontrol'] == 2) {
	$this->data = $this->dataCash;
//$this->id
	$cssClass = '';
	$DATA = array();
	$DATA['button'] = array(
		'config' => 1,
		'topmenu' => static_super::modulMenu($this),
		'firstpath' => ADMIN_BH . '?_view=list&',
		'_clp' => Array(
			'_modul' => 'pg',
			'pg_id' => 1,
		)
	);
	$linkWep = array(
		'wep' => array(
			'href' => array(),
			'caption' => 'АДМИНКА',
			'sel' => 0,
			'type' => 'button',
			'css' => 'button-admin',
			'link' => ADMIN_BH,
		),
		'wepsplit' => array('type' => 'split')
	);
	$DATA['button']['topmenu'] = $linkWep + $DATA['button']['topmenu'];
	$html = transformPHP($DATA, '#modulprm#fcontrol');
}

if (isAjax()) {
	if ($html)
		$html = '<div class="fcontrol-text">' . $html . '</div>';
	return $html;
}

$html = '<div id="fcontrol" class="' . $cssClass . '">
		<div class="fcontrol-text">' . $html . '</div>
		<div class="fcontrol-button" data-id="' . $this->contentID . '"><i></i></div>
	</div>';

return $html;
