<?php
	function tpl_superlist(&$data) {
		global $_CFG, $HTML;
		$html = '';

		$firstpath = '';
		if(count($data['_clp'])) {
			$firstpath = http_build_query($data['_clp']).'&';
		}
		$flag = 0;
		if(isset($data['formcreat']) and count($data['formcreat'])) {
			if(isset($data['formcreat']['form']['_*features*_']['id']) and $data['formcreat']['form']['_*features*_']['id'])
				$flag = 2;
			else
				$flag = 1;
		}

		$temp_topmenu = '<div class="menu_new">';
		if(count($data['topmenu'])) { //MENU
			foreach($data['topmenu'] as $r) {
				$temp_topmenu .= '<div class="botton"><span';
				// HREF path
				if(isset($r['href']) and is_array($r['href'])) {
					$temp = '';
					foreach($r['href'] as $hk=>$hr)
						$temp .= $hk.'='.$hr.'&';
					$r['href'] = $temp;
				}
				if($r['type']=='tools' or $r['type']=='static') {
					$r['css'] = 'weptools '.$r['css'];
					$temp_topmenu .= ' onclick="return ShowTools(\'tools_block\',\''.$_CFG['_HREF']['wepJS'].'?_view=list&'.$firstpath.$r['href'].'\')"';
				}
				else {
					$temp_topmenu .= ' onclick="return wep.load_href(\''.$data['firstpath'].$firstpath.$r['href'].'\')"';
				}
				if($r['sel'])
					$temp_topmenu .= ' style="border:2px solid red;"';
				$temp_topmenu .= ' class="'.$r['css'].'" title="'.$r['caption'].'">'.$r['caption'].'</span></div>';
			}
		}
		$temp_topmenu .= tpl_spagenum($data['data']['pagenum']);// pagenum
		$temp_topmenu .= '</div>';

		$html .= $temp_topmenu;


		if(isset($data['path']) and count($data['path'])) {
			include_once($HTML->_PATHd.'php/path.php');
			$html .= tpl_path($data['path'],$flag);// PATH
		}

		$html .= '<div id="tools_block" style="display:none;"></div>';

		if(isset($data['messages']) and count($data['messages'])) {
			include_once($HTML->_PATHd.'php/messages.php');
			$html .= tpl_messages($data['messages']);// messages
		}

		if(isset($data['formcreat']) and count($data['formcreat'])) {
			include_once($HTML->_PATHd.'php/formcreat.php');
			$html .= tpl_formcreat($data['formcreat']);// PATH
		}
		else {
			$html .= tpl_data($data['data'],$data['firstpath'].$firstpath);// messages
		}

		$html .= $temp_topmenu; //MENU
		//$html .= $temp_pagenum; //pagenum
		$html .= '<div class="clk"></div>';
		return $html;
	}

	function tpl_data(&$data,$firstpath='') {
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
					$html .= '<a class="'.($r['sel']==1?'bottonimg_sel':'bottonimg').' imgup" title="[SORT]" href="'.$firstpath.'sort='.$r['href'].'" onclick="return wep.load_href(this)"></a>';
				}
				$html .= $r['value'];
				if(isset($r['href']) and $r['href']!='') {
					$html .= '<a class="'.($r['sel']==2?'bottonimg_sel':'bottonimg').' imgdown" title="[SORT]" href="'.$firstpath.'dsort='.$r['href'].'" onclick="return wep.load_href(this)"></a>';
				}
				$html .= '</th>';
			}
			if(isset($r['onetd']) and $r['onetd']=='close') $tdflag = 0;
		}
		$html .= '<th>&#160;</th></tr>';
        if(count($data['item']))

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
					$html .= '<a class="bottonimg imgdragdrop" href="'.$hrefpref.'&_type=ordup" onclick="return false;" title="'.$r['tditem'][$data['mf_ordctrl']]['value'].'"></a>';
					/*$html .= '<a class="bottonimg imgup" href="'.$hrefpref.'&_type=ordup" onclick="return wep.load_href(this)" title="[-1]"></a>'
						.$tditem['value']
						.'<a class="bottonimg imgdown" href="'.$hrefpref.'&_type=orddown" onclick="return wep.load_href(this)" title="[+1]"></a>';*/
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
					else $html .= $tditem['value'];
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
					$html .= '<a class="bottonimg img'.$rr['css'].'" style="'.$rr['style'].'" href="'.$rr['href'].'" title="['.$rr['title'].']" onclick="'.$rr['onclick'].'"></a>';
				}
			}
			if(isset($r['active'])) {
				if($r['act'])
					$html .= '<a class="bottonimg img'.$r['active'].'" href="'.$hrefpref.'&_type='.($r['active']==1?'dis':'act').'" onclick="return wep.load_href(this)" title="['.static_main::m('_ACT_TITLE'.$r['active']).']"></a>';
				else
					$html .= '<a class="bottonimg img'.$r['active'].'" title="Изменение данного своиства вам не доступна."></a>';
			}
			if($r['edit'])
				$html .= '<a class="bottonimg imgedit" href="'.$hrefpref.'&_type=edit" onclick="return wep.load_href(this)" title="['.static_main::m('_EDIT_TITLE').']"></a>';
			if($r['del'])
				$html .= '<a class="bottonimg imgdel" href="'.$hrefpref.'&_type=del" onclick="return wep.hrefConfirm(this,\'del\')" title="['.static_main::m('_DEL_TITLE').']"></a>';
			if(isset($r['istree']))
				$html .= '<br/><a href="'.$hrefpref.'" onclick="return wep.load_href(this)">'.$r['istree']['value'].' ('.$r['istree']['cnt'].')</a>';
			if(isset($r['child'])) foreach($r['child'] as $ck=>$cn)
				$html .= '<br/><a href="'.$hrefpref.'&'.$data['cl'].'_ch='.$ck.'" onclick="return wep.load_href(this)">'.$cn['value'].' ('.$cn['cnt'].')</a>';


			$html .= '</td></tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}


	function tpl_spagenum(&$data) {
		global $_CFG,$_tpl;
		if(!$data or !count($data)) return '';
		$html = '<div class="pagenumcnt">'.$data['cnt'].'&#160;:&#160;&#160;</div>';
		if(isset($data['link']) and count($data['link'])) {
			$_tpl['onload'] .='pagenum_super('.$data['cntpage'].','.$data['_pn'].',\''.$data['modul'].'\','.($data['reverse']?'true':'false').');';
			$html .= '<div class="pagenum">';
			foreach($data['link'] as $k=>$r) {
				if($r['href']=='')
					$html .=  $r['value'];
				elseif($r['href']=='select_page')
					$html .=  '<b>['.$r['value'].']</b>';
				else
					$html .=  '<a href="'.$r['href'].'" onclick="return wep.load_href(this)">'.$r['value'].'</a>';
			}
			$html .= '&#160;</div><div class="ppagenum"></div>';
		}
		$html .= '<select class="mopselect" onchange="setCookie(\''.$data['modul'].'_mop\',this.value,20);window.location.reload();">';
		//,\''.$_CFG['session']['path'].'\',\''.$_CFG['session']['domain'].'\',\''.$_CFG['session']['secure'].'\'
		//JSWin({\'href\':\''.$_CFG['_HREF']['JS'].'?_view=pagenum&_modul='.$data['modul'].'&mop=\'+this.value})
		if(count($data['mop'])) {
			foreach($data['mop'] as $k=>$r) {
				$html .=  '<option value="'.$k.'"'.($r['sel']?' selected="selected"':'').'>'.$r['value'].'</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}

