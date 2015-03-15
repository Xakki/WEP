<?php
function tpl_sysconf(&$data)
{
    global $_CFG;
    $html = '<a href="' . ADMIN_BH . '/login.html?exit=ok"><img src="/_design/default/img/close48.gif" class="exit" alt="CLOSE"/></a><div class="uname">' . $data['user']['name'] . ' [' . $data['user']['gname'] . ']</div>';
    if (is_array($data['item']) and count($data['item']))
        foreach ($data['item'] as $k => $r)
            $html .= '<div class="modullist' . ($data['modul'] == $k ? ' selected' : '') . '"><a href="' . ADMIN_BH . '/index.php?_view=list&amp;_modul=' . $k . '">' . $r . '</a></div>';
    $html .= '<div class="modullist"><a href="index.html" target="_blank">Главная страница</a></div><div class="clk"></div>';
    return $html;
}
