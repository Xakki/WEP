<?php
/**
 * Меню
 * @type Контент
 * @tags pgmenu
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_menu(&$data)
{
    $html = '';
    if (isset($data['#item#']) and count($data['#item#'])) {
        $html .= '<ul class="menu">';
        //$last = count($data);
        foreach ($data['#item#'] as $k => $r) {
            $html .= '<li>';
            $class = array();
            if ($r['sel'] == 2)
                $class[] = 'selected';
            if (isset($r['#item#']) and count($r['#item#'])) {
                $html .= tpl_menu($r);
                $class[] = 'hassub';
            }
            if ($r['menuajax']) {
                $class[] = 'isAjaxLink';
                $r['attr'] .= ' data-ajax="popup"';
            }
            $html .= '<a href="' . $r['href'] . '" ' . (count($class) ? ' class="' . implode(' ', $class) . '"' : '') . ' ' . $r['attr'] . '>' . $r['name'] . '</a>';
            /*if(strpos($r['attr'],'style="'))
                $r['attr'] = str_replace('style="','style="width:'.$prs.'%;',$r['attr']);
            else
                $r['attr'] .= ' style="width:'.$prs.'%;"';*/

            $html .= '</li>';
        }
        $html .= '</ul>';
    }
    return $html;
}
