<?php
function tpl_path(&$data, $form = 0)
{
    $html = '';
    if (isset($data) and count($data)) {
        $html .= '<div class="path">';
        $last = count($data);
        $i = 1;
        foreach ($data as $k => $r) {
            if ($i > 1) $html .= ' / ';
            if ($i != $last) {
                $html .= '<a href="' . $k . '">' . $r . '</a>';
            } elseif ($i == $last) $html .= $r . '&#160;<a class="buttonimg imgf5" href="' . $k . '"></a>';
            $i++;
        }
        if ($form == 2)
            $html .= '&#160;<span class="buttonimg imgf6" onclick="$(\'form input[name=sbmt_save]\').click();" title="сохранить"></span>';
        if ($form > 0)
            $html .= '&#160;<span class="buttonimg imgf7" onclick="$(\'form input[name=sbmt]\').click();" title="сохранить и закрыть"></span>';
        $html .= '</div>';
    }
    return $html;
}
