<?php
function tpl_menu(&$data, $fl = false)
{
    $html = '';
    if (isset($data['#item#']) and count($data['#item#'])) {
        if ($fl)
            $html .= '<ul class="menu_sub">';
        else
            $html .= '<ul class="menu">';
        foreach ($data['#item#'] as $k => $r) {
            $html .= '<li class="' . ($r['sel'] ? 'selmenu' : '') . '"><a href="' . $r['href'] . '" ' . $r['attr'] . '>' . $r['name'] . '</a>';
            if (isset($r['#item#']) and count($r['#item#'])) {
                $html .= tpl_menu($r, true);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
    }
    return $html;
}
