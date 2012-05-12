<?php
	function tpl_prodListTable(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			global $_tpl,$HTML;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/product'] = 1;

			$html = '<div class="prodListTable">';
			if(!isset($data['#item#']) or !count($data['#item#'])) {
				if(isset($data['#filter#']))
					$html .= '<h3>К сожаленю, по вашему запросу товары не найдены</h3>';
				else
					$html .= '<h3>В данной категории товары ещё не добавлены!</h3>';
			} 
			else {

				$PGnum = '';
				if(isset($data['pagenum']) and count($data['pagenum'])) {
					global $HTML;
					$PGnum = $HTML->transformPHP($data['pagenum'],'#pg#pagenum');
					$html .= $PGnum;
				}
				$html .= '<table cellpadding="0" cellspacing="0"><tr>';
				if(isset($data['#prodListTable#'])) {
					foreach($data['#prodListTable#'] as $cf_r)
						$html .= '<th>'.$cf_r;
				}
				$html .= '</tr>';
				foreach($data['#item#'] as $r) {
					$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
					if(isset($r['image']) and count($r['image']) and $r['image'][0][1]) {
						$img = $r['image'][0][1];
					} else
						$img = '_design/'.$HTML->_design.'/_shop/img/nofoto.gif';
					$html .= '<tr>';
					if(isset($data['#prodListTable#'])) {
						foreach($data['#prodListTable#'] as $cf_k=>$cf_r) {
							if($cf_k=='cost')
								$html .= '<td>'.($r['cost']?$r['cost'].' <span>руб.</span>':'&#160;');
							elseif(strpos($cf_k,'img_product')!==false)
								$html .= '<td><img src="'.$img.'" alt="'.$r['name'].'"/>';
							elseif($cf_k=='name')
								$html .= '<td><a href="'.$href.'" title="'.$r['name'].'">'.$r['name'].'</a>';
							else
								$html .= '<td>'.$r[$cf_k];
						}
					}
					$html .= '</tr>';					
				}
				$html .= '</table>';
				$html .= $PGnum;
			}
			$html .= '</div>';
		}
		return $html;
	}

