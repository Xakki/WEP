<?php
/**
 * новости
 * @type Новости
 * @tags news
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_news(&$data)
{
    global $_tpl;
    setCss('/style/news');
    $html = '';
    if (isset($data['#list#']) and count($data['#list#'])) {
        $html .= '<div class="news">';
        foreach ($data['#list#'] as $k => $r) {
            if (!$r['i_news']) $r['i_news'] = '';
            $html .= '<div class="news-items">
						' . ($r['i_news'] ? '<a class="news-read" href="' . $data['#page#'] . '/' . $r['id'] . '.html"><img src="' . $r['i_news'] . '" class="news-img" alt="' . htmlentities($r['name'], ENT_NOQUOTES, CHARSET) . '"/></a>' : '') . '
						<span class="news-date">' . static_main::_date('d F Yг.', $r['ndate']) . '/</span>
						<span class="news-name"><a href="' . $data['#page#'] . '/' . $r['id'] . '.html">' . $r['name'] . '</a></span>
						<p class="news-desc">' . $r['description'] . '</p>
					</div>';
        }
        if (isset($data['pagenum']) and count($data['pagenum'])) {
            $html .= transformPHP($data['pagenum'], '#pg#pagenum'); // pagenum
        }
        $html .= '</div>';
    } elseif (isset($data[0]) and count($data[0])) {
        $r = $data[0];
        $html .= '<div class="news-item">
						' . ($r['i_news'] ? '<img src="' . $r['i_news'] . '" class="news-img" alt="' . $r['name'] . '"/>' : '') . '
						<span class="news-date">' . static_main::_date('d F Yг.', $r['ndate']) . '/</span>
						<!--<span class="news-name">' . $r['name'] . '</span>-->
						<p class="news-desc">' . ($r['redirect'] ? static_main::redirectLink($r['text'], false, 2) : $r['text']) . '</p>';
        if ($r['tags'])
            $html .= '<p class="news-tags"><b>Теги:</b> ' . $r['tags'] . '</p>';
        if ($r['href'])
            $html .= '<p class="news-link"><b>Источник:</b> ' . ($r['redirect'] ? static_main::redirectLink($r['href'], false) : $r['href']) . '</p>';
        $html .= '</div>';
    }
    return $html;
}