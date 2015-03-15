<?php
/**
 * Корзина
 * @type Магазин Корзина
 * @tags basket
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_basket(&$data)
{
    $html = '';
    if (isset($data) and count($data)) {
        global $_tpl, $PGLIST;
        setCss('/../_shop/style/shopMain');
        setScript('/../_shop/script/shop');
        if (!isset($_tpl['onload'])) $_tpl['onload'] = '';
        $_tpl['onload'] .= ' wep.shop.basketContenId = ' . $PGLIST->contentID . '; wep.shop.pageBasket="' . $data['#page#'] . '.html";';

        $html .= '<div id="basketBlock"><i class="ico"></i>';
        if ($data['cnt'])
            $html .= '<p>Товаров ' . $data['cnt'] . ' шт.</p>
				<p>' . $data['summ'] . ' ' . $data['#curr#'] . '</p>';
        else
            $html .= '<p class="emptybasket">Корзина пуста</p>';
        $html .= '</div>';
    }
    return $html;
}
