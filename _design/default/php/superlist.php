<?php
	function tpl_superlist(&$data) {
		global $_CFG, $HTML;
		$html = '';

		if(!isset($data['_clp'])) {
			$data['_clp'] = array();
		}

		if(!isset($data['DIR']))
			$data['DIR'] = dirname(__FILE__);

		$flag = 0;
		if(isset($data['formcreat']) and count($data['formcreat'])) {
			if(isset($data['formcreat']['form']['_*features*_']['id']) and $data['formcreat']['form']['_*features*_']['id'])
				$flag = 2;
			else
				$flag = 1;
		}

		$temp_topmenu = '<div class="superMenu">';
			$temp_topmenu .= tpl_topmenu($data['topmenu'], $data['firstpath'], $data['_clp']);

			include_once($data['DIR'].'/pagenum.php');
			$temp_topmenu .= tpl_pagenum($data['data']['pagenum']);// pagenum
		$temp_topmenu .= '</div>';

		$html .= $temp_topmenu;


		if(isset($data['path']) and count($data['path'])) {
			include_once($data['DIR'].'/path.php');
			$html .= tpl_path($data['path'],$flag);// PATH
		}

		$html .= '<div id="tools_block" style="display:none;"></div>';

		if(isset($data['messages']) and count($data['messages'])) {
			include_once($data['DIR'].'/messages.php');
			$html .= tpl_messages($data['messages']);// messages
		}

		if(isset($data['formcreat']) and count($data['formcreat'])) {
			include_once($data['DIR'].'/formcreat.php');
			$html .= tpl_formcreat($data['formcreat']);// PATH
		}
		else {
			$html .= tpl_data($data['data'], $data['firstpath'].http_build_query($data['_clp']).'&');// messages
			if(!isset($data['data']['item']) or count($data['data']['item'])<8)
				$temp_topmenu = '';
		}

		$html .= $temp_topmenu; //MENU
		//$html .= $temp_pagenum; //pagenum
		$html .= '<div class="clk"></div>';
		return $html;
	}

	function tpl_topmenu(&$data, $firstpath, $httpQuery=array()) {
		global $_CFG;
		$temp_topmenu = '';
		if(is_array($data) and count($data)) { //MENU
			include_once(dirname(__FILE__).'/formSelect.php');
			foreach($data as $r) {
				if($r['type']=='split') {
					$temp_topmenu .= '<div class="split">&#160;</div>';
					continue;
				}

				if(!isset($r['title'])) $r['title'] = $r['caption'];
				if(!isset($r['style'])) $r['style'] = '';
				if(!isset($r['css'])) $r['css'] = '';
				if(!isset($r['onConfirm'])) $r['onConfirm'] = false;

				// HREF path
				$href =  array_merge($httpQuery, $r['href']);
				$href = http_build_query($href);
				
				if($r['type']=='select') {
					$temp_topmenu .= '<div class="'.$r['type'].($r['sel']?' selected':'').'" style="'.$r['style'].'" title="'.$r['title'].'" ';
					$temp_topmenu .= ' onclick="return false;"><span class="caption">'.$r['caption'].'</span> <select class="'.$r['css'].'"';
					$temp_topmenu .= ' onchange="return wep.load_href(\''.$firstpath.$href.'\'+this.options[this.selectedIndex].value)"';
					$temp_topmenu .= '>'.tpl_formSelect($r['list']).'</select>';
					$temp_topmenu .= '</div>';
				}
				else {
					$temp_topmenu .= '<a class="'.$r['type'].($r['sel']?' selected':'').'" style="'.$r['style'].'" title="'.$r['title'].'" ';
					//$temp_topmenu .= ' onclick="return wep.load_href(\''.$firstpath.$href.'\')"';
					
					if(isset($r['is_popup']) and $r['is_popup'])
						$temp_topmenu .= ' onclick="return ShowTools(\''.$_CFG['_HREF']['wepJS'].'?_view=list&'.$href.'\')"';//, \'tools_block\'
					else
						$temp_topmenu .= ' href="'.$firstpath.$href.'"';
					$temp_topmenu .= '> <span class="'.$r['css'].'">'.$r['caption'].'</span>';
					$temp_topmenu .= '</a>';
				}
			}
		}
		return $temp_topmenu;
	}


	function tpl_data(&$data, $firstpath='') {
		if(!$data or !count($data) or !isset($data['thitem']) or !count($data['thitem'])) return '';
		global $_CFG,$_tpl;
		$html = '<table class="superlist"><tbody><tr>';
		$tdflag = 0;
		if(!isset($data['thitem']['id']))
			$html .= '<th>№</th>';
		// Сортировка
		if(isset($data['mf_ordctrl'])) {
			if($data['order']=='t1.'.$data['mf_ordctrl'])
				$_tpl['onload'] .= 'wep.iSortable();';
			else
				unset($data['mf_ordctrl']);
		}
		foreach($data['thitem'] as $r) {
			if(!$tdflag) {
				if(isset($r['onetd'])){
					$tdflag = 1;
					$r['value'] = $r['onetd'];
				}
				$html .= '<th>';
				if(isset($r['href']) and $r['href']!='') {
					$html .= '<a class="'.($r['sel']==1?'buttonimg_sel':'buttonimg').' imgup" title="[SORT]" href="'.$firstpath.'sort='.$r['href'].'"></a>';
				}
				$html .= $r['value'];
				if(isset($r['href']) and $r['href']!='') {
					$html .= '<a class="'.($r['sel']==2?'buttonimg_sel':'buttonimg').' imgdown" title="[SORT]" href="'.$firstpath.'dsort='.$r['href'].'"></a>';
				}
				$html .= '</th>';
			}
			if(isset($r['onetd']) and $r['onetd']=='close') $tdflag = 0;
		}
		$html .= '<th style="text-align:right;"><a class="uiicons img10" onclick="wep.SuperGroupInvert(this)" title="Инверт чекбоксов">Инверт</a></th></tr>';
        if(count($data['item']))
		if(!isset($data['cl']))
			  $data['cl'] = '';

		// Проходимся про каждой записи
		foreach($data['item'] as $k=>$r) {
			$html .= '<tr class="tritem" data-id="'.$k.'" data-mod="'.$data['cl'].'"';
			// Атрибут значения сортировки
			if(isset($data['mf_ordctrl'])) $html .= ' data-ord="'.$r['tditem'][$data['mf_ordctrl']]['value'].'"';
			// атрибут значения родителя
			if(isset($data['pid'])) $html .= ' data-pid="'.$data['pid'].'"';
			if(isset($r['style']) and $r['style']) $html .= ' style="'.$r['style'].'"';
			$html .= '>';
			if(!isset($data['thitem']['id']))
				$html .= '<td valign="top" id="items_'.$r['id'].'"><a id="elem'.$r['id'].'">'.$r['id'].'</a></td>';
			$tdflag = 0;

			// Путь для опции(редактирования, удаления, откл и прочее) по каждой записи
			$hrefpref = $firstpath.$data['cl'].'_id='.$r['id'];

			//Проходимся по каждому полю
			foreach($r['tditem'] as $ktd=>$tditem) {
				/////
				if(!$tdflag) {
					$html .= '<td valign="top">';
					if(isset($tditem['onetd'])) $tdflag = 1;
				}
				/////
				if(isset($data['mf_ordctrl']) and $ktd==$data['mf_ordctrl']) {
					$html .= '<a class="buttonimg imgdragdrop" href="'.$hrefpref.'&_type=ordup" onclick="return false;" title="'.$r['tditem'][$data['mf_ordctrl']]['value'].'"></a>';
					/*$html .= '<a class="buttonimg imgup" href="'.$hrefpref.'&_type=ordup" title="[-1]"></a>'
						.$tditem['value']
						.'<a class="buttonimg imgdown" href="'.$hrefpref.'&_type=orddown" title="[+1]"></a>';*/
				}
				elseif(isset($tditem['value']) and $tditem['value']!='') {
					if($tdflag)
						$html .= '<b>'.$data['thitem'][$ktd]['value'].'</b>: ';
					if(isset($tditem['fileType']) and $tditem['fileType']=='img') {
						$_CFG['fileIncludeOption']['fancybox'] = 1;
						$html .= '<a rel="fancy" title="рисунок" class="fancyimg" href="'.$tditem['value'].'"><img src="'.$tditem['value'].'" alt="" width="50"/></a>&#160;';
					}
					elseif(isset($tditem['fileType']) and $tditem['fileType']=='swf') {
						if($tditem['value']!='')
							$html .= $tditem['value'].'&#160;<object type="application/x-shockwave-flash" data="/'.$tditem['value'].'" height="60" width="200"><param name="movie" value="/'.$tditem['value'].'" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>';
					}
					elseif(isset($tditem['fileType']) and $tditem['fileType']=='file') {
						if($tditem['value']!='')
							$html .= '<a href="'.$tditem['value'].'" target="_blank">Файл</a>&#160;';
					}
					elseif(isset($tditem['href']) and $tditem['href']!='') {
						$html .= '<a href="'.$tditem['href'].'" target="_blank">'.$tditem['value'].'</a>&#160;';
					}
					else {
						$html .= $tditem['value'];
					}
				}
				/////
				if(isset($tditem['onetd']) and $tditem['onetd']=='close')
					$tdflag = 0;
				if(!$tdflag)
					$html .= '</td>';
				elseif(isset($tditem['value']) and $tditem['value']!='')
					$html .= '<br/>';
				/////
			}
			if($tdflag) $html .= '</td>';
			$html .= '<td class="ic" style="vertical-align:top;white-space:nowrap;">';

			if(isset($data['abtn']) and count($data['abtn'])) {
				foreach($data['abtn'] as $rr) {
					//$rr['css']
					//$rr['title']
					if(!isset($rr['style'])) $rr['style']='';
					if(!isset($rr['onclick'])) $rr['onclick']='';
					$rr['href'] = str_replace(array('%id%','%firstpath%'),array($r['id'],$hrefpref.'&_type='),$rr['href']);
					$html .= '<a class="buttonimg img'.$rr['css'].'" style="'.$rr['style'].'" href="'.$rr['href'].'" title="['.$rr['title'].']" onclick="'.$rr['onclick'].'"></a>';
				}
			}
			
			if(isset($r['active'])) {
				if($r['act'])
					$html .= '<a class="buttonimg img'.$r['active'].'" href="'.$hrefpref.'&_type='.($r['active']==1?'dis':'act').'" title="['.static_main::m('act'.$r['active']).']"></a>';
				else
					$html .= '<a class="buttonimg img'.$r['active'].'" title="Изменение данного своиства вам не доступна."></a>';
			}
			
			if($r['edit'])
				$html .= '<a class="buttonimg imgedit" href="'.$hrefpref.'&_type=edit" title="['.static_main::m('_EDIT_TITLE').']"></a>';
			
			if($r['del'])
				$html .= '<a class="buttonimg imgdel" href="'.$hrefpref.'&_type=del" onclick="return wep.hrefConfirm(this,\'del\')" title="['.static_main::m('_DEL_TITLE').']"></a>';

			if($r['del'] or (isset($r['active']) and $r['act']))
				$html .= '<input type="checkbox" name="SuperGroup['.$data['cl'].']['.$r['id'].']" onclick="wep.SuperGroup(this)" title="Групповая операция" '.((isset($_COOKIE['SuperGroup'][$data['cl']][$r['id']]) and $_COOKIE['SuperGroup'][$data['cl']][$r['id']])?' checked="checked"':'').'>';

			if(isset($r['istree']))
				$html .= '<br/><a href="'.$hrefpref.'" >'.$r['istree']['value'].' ('.$r['istree']['cnt'].')</a>';
			
			if(isset($r['child'])) foreach($r['child'] as $ck=>$cn)
				$html .= '<br/><a href="'.$hrefpref.'&'.$data['cl'].'_ch='.$ck.'">'.$cn['value'].' ('.$cn['cnt'].')</a>';


			$html .= '</td></tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}

