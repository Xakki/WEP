<?php
/**
 * Список заказов 
 * @type Магазин Корзина
 * @tags basketlist
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
	function tpl_basketlist(&$data) {
		global $_tpl,$_CFG;
		setCss('/../_shop/style/shopBasket');
		$html = '';

		if(isset($data['messages'])) {
			$html .= transformPHP($data['messages'], '#pg#messages');
		}

		if(isset($data['#item#'])) {
			$html .= tpl_basketlist_item($data);
		}
		elseif(isset($data['#list#'])) {
			if(isset($data['#filter#']))
			{
				//print_r('<pre>');print_r($data);
				$html .= '<div id="dialog-filter" title="Фильтр" style="display:none;">'.transformPHP($data['#filter#'], '#pg#filter').'</div> <button id="open-filter">Показать фильтр</button>';
				$_tpl['onload'] .= '
					$("#dialog-filter .f_submit").hide();
					$( "#dialog-filter" ).dialog({
						autoOpen: false,
						height: 350,
						width: 620,
						modal: true,
						buttons: {
							"Отфильтровать": function() {
								$(this).find("form").append("<input name=\"sbmt\" value=\1\" type=\"hidden\"/>").submit();
								$( this ).dialog( "close" );

							},
							"Очистить": function() {
								$(this).find("form").append("<input name=\"f_clear_sbmt\" value=\1\" type=\"hidden\"/>").submit();
								$( this ).dialog( "close" );
							}
						},
						close: function() {
						}
					});
					$( "#open-filter" ).button().click(function() { $( "#dialog-filter" ).dialog( "open" ); });
				';
				plugJQueryUI();
				if(isset($data['#filter#']['filterEnabled']))
					$html .= '<messages><info>Включен фильтр</info></messages>';
			}

			if(count($data['#list#']))
			{
				$html .= '
				<table class="basketlist"><tr> 
					<th>№
					<th>Дата
					<th>Сумма
					<th>Метод оплаты
					<th>Товары
					<th>Статус
				</tr>';
				$summ = 0;
				foreach($data['#list#'] as $r) {
					$prod = '<ul>';
					foreach($r['#shopbasketitem#'] as $p) {
						if($p['count'])
							$prod .= '<li>'.$p['product_name'].' ['.$p['count'].' шт. по '.$p['cost_item'].' '.$data['#curr#'].']';
						else
							$prod .= '<li>'.$p['product_name'].' ['.$p['cost_item'].' '.$data['#curr#'].']';
					}
					$prod .= '</ul>';
					if($r['pay_id'])
						$link = '<a href="/_js.php?_modul=pay&_func=statusForm&id='.$r['pay_id'].'" onclick="return wep.JSWin({type:this});" target="_blank">'.$r['#laststatus#'].'</a>';
					else
						$link = 'Забронированно <a href="'.$data['#orderPage#'].'.html?basketpay='.$r['id'].'">Оформить заказ</a>';
					$html .= '
					<tr data-id="'.$r['id'].'">
						<td><a href="'.$data['#page#'].'/'.$r['id'].'.html">Заказ №'.$r['id'].'</a>
						<td>'.static_main::_date($_CFG['wep']['timeformat'],$r['mf_timecr']).'
						<td class="summ"><span>'.$r['summ'].'</span> '.$data['#curr#'].'
						<td>'.$r['#paytype#'].'
						<td class="basketlist-basketitem">'.$prod.'
						<td>'.$link.'
					</tr>';
				}
				$html .= '</table>';
			}
			else
				$html .= '<div class="basket">'.static_render::message('Заказы не найдены').'</div>';
			
		} 
		else
			$html .= '<div class="basket">'.static_render::message('Заказы не найдены').'</div>';
		
		return $html;
	}

	function tpl_basketlist_item(&$data)
	{
		global $_tpl, $_CFG;

		setCss('form');

		if(count($data['#item#'])) {
			$itemB = &$data['#item#'];
			$html = '
			<table class="basketInfo">
				<tr> <td>ФИО <td><b>'.$itemB['fio'].'</b>
				<tr> <td>Адрес доставки <td><b>'.$itemB['address'].'</b>
				<tr> <td>Телефон <td><b>'.$itemB['phone'].'</b>
				<tr> <td>Сумма покупки <td><b>'.$itemB['summ'].' '.$data['#curr#'].'</b>
				<tr> <td>Метод оплаты <td><b>'.$itemB['#paytype#'].'</b>
				<tr> <td>Доставка <td><b>'.$itemB['#delivery#']['name'].'</b>
				<tr> <td>Статус <td><b>'.$itemB['#laststatus#'].'</b>
			</table>';
			if(isset($data['#moder#']) and $data['#moder#'] and $itemB['laststatus']<5)
			{
				$_tpl['onload'] .= '

					$( "#dialog-confirm" ).dialog({
						autoOpen: false,
			            resizable: false,
			            modal: true,
			            buttons: {
			                "Подтверждаю": function() {
			                	var comm = $( this ).find("textarea").val();
			                	if(comm)
			                    {
			                    	$("#formOrderComment").val(comm);
			                    	$("#formOrder").submit();
			                    }
			                },
			                "Нет": function() {
			                    $( this ).dialog( "close" );
			                }
			            }
			        });

					$( "button.statusOrder" ).button().click(function() {
						$( "#dialog-confirm b").text($(this).text());
						if($(this).attr("data-comment"))
							$( "#dialog-confirm textarea" ).val("").show();
						else
							$( "#dialog-confirm textarea" ).val("default").hide();
						$("#formOrderStatus").val($(this).attr("data-val"));
						$( "#dialog-confirm" ).dialog( "open" ); 
						return false;
					});
				';
				plugJQueryUI();
				$html .= '
				<form method="POST" id="formOrder">
					<input type="hidden" name="status" id="formOrderStatus">
					<input type="hidden" name="comment" id="formOrderComment">
				</form>
				'.($itemB['laststatus']<3?'<button data-val="3" class="statusOrder">Оплачено</button>':'').'
				'.($itemB['laststatus']==3?'<button data-val="4" class="statusOrder">Отправлено</button>':'').'
				'.($itemB['laststatus']==4?'<button data-val="5" class="statusOrder">Доставлено</button>':'').'					
				'.($itemB['laststatus']<3?'<button data-val="7" class="statusOrder cancelOrder" data-comment="true">Отменить</button>':'').'

				<div id="dialog-confirm" title="Подтвердите действие" style="display:none;">
					<textarea placeholder="Напишите причину" style="width:260px;height:60px;display:none;" maxlength="250" onkeyup="textareaChange(this)"></textarea>
				    <div>Приминить статус "<b></b>" ?</div>
				</div>
				'; 
			}

			$html .= '<h3>Покупки</h3>
				<table class="basketItems">
					<tr><th>Наименование <th>Цена '.$data['#curr#'].'<th>Кол-во <th>Сумма '.$data['#curr#'].'';
				foreach($itemB['#shopbasketitem#'] as $r) {
					if($r['count'])
						$html .= '<tr> 
							<td><a href="'.$data['#pageCatalog#'].'/cataloglist/product_'.$r['product_id'].'.html" target="_blank">'.$r['product_name'].'</a> 
							<td>'.$r['cost_item'].'
							<td>'.$r['count'].'
							<td>'.$r['count']*$r['cost_item'];
					else
						$html .= '<tr> 
							<td>'.$r['product_name'].'
							<td>'.$r['cost_item'].'
							<td> - 
							<td>'.$r['cost_item'];
				}
				$html .= '</table>';

			$html .= '<br/><h3>История заказа</h3>
				<table class="basketItems">
					<tr><th>Статус<th>Комментарии<th>Дата<th>Оператор<th>IP';
				foreach($itemB['#history#'] as $r) {
					$html .= '<tr> 
					<td>'.$r['#status#'].'
					<td>'.$r['name'].'
					<td>'.static_main::_usabilityDate($r['mf_timecr']).'
					<td>'.$r['#creater_id#'].'
					<td>'.long2ip($r['mf_ipcreate']);
				}
				$html .= '</table>';
		}
		else {
			$html = '<div class="basket">'.static_render::message('Не верный адрес страницы либо данный заказ был удален').'</div>';
		}
		return $html;
	}
//'.($data['#moder#']?'<td><a href="'.$data['#pageUser#'].'/'.$r['creater_id'].'.html" target="_blank">'.$r['uname'].'</a>':'').'