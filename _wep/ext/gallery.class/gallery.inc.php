<?php
/**
 * Галлерея
 * Лента изображений модуля "Галлерея"
 * @ShowFlexForm true
 * @type Контент
 * @ico system.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')
	$FUNCPARAM[0] = '#gallery#gallery';
/*if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = 1;*/


if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
	);
	return $form;
}

_new_class('gallery', $GALLERY);

if(isset($this->pageParam[0])) {
	$GALLERY->id = (int)$this->pageParam[0];
}

$DATA = $GALLERY->mainList();
$DATA['#title#'] = $Ctitle;
$DATA['#page#'] = $Chref;
$html .= $HTML->transformPHP($DATA, $FUNCPARAM[0]);

if($GALLERY->id)
	$this->pageinfo['path'][] = $DATA['#info-gallery#']['name'];

return $html;
