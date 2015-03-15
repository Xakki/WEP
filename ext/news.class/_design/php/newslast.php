<?php
/**
 * Свежие новости
 * @type Новости
 * @tags newslast
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_newslast(&$data)
{
    $html = '';
    if (isset($data['#list#']) and count($data['#list#'])) {
        $html .= '<div class="newslast">';
        if ($data['#Ctitle#'])
            $html .= '<a href="' . $data['#page#'] . '.html"><h3>' . $data['#Ctitle#'] . '</h3></a>';
        $html .= '<div class="news-block">';
        foreach ($data['#list#'] as $k => $r) {
            $html .= '<div class="news-block-item">
					<span class="news-date">' . static_main::_date('d F Y', $r['ndate']) . 'г.</span><span class="news-cat">/ <a href="' . $data['#page#'] . '/' . $r['id'] . '.html">' . $r['name'] . '</a></span>
					<p>' . $r['description'] . '</p>
				</div>';
        }
        $html .= '
			</div></div>';
    }
    return $html;
}