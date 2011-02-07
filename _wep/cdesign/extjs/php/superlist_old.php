<?
	function tpl_superlist(&$data) {
		global $_CFG, $HTML;
		if(isset($data['path']) and count($data['path'])) {
			include_once($HTML->_PATHd.'php/path.php');
			$html = tpl_path($data['path']);// PATH
		}
		end($data['path']);
		$firstpath = key($data['path']);

		if(count($data['topmenu'])) { //MENU
			$temp_topmenu = '<div class="menu_new">';
			foreach($data['topmenu'] as $r) {
				$temp_topmenu .= '<div class="botton"><span';
				if($r['type']=='tools' or $r['type']=='static') {
					$r['css'] = 'weptools '.$r['css'];
					$temp_topmenu .= ' onclick="return ShowTools(\'tools_block\',\''.$_CFG['_HREF']['wepJS'].'?'.$r['href'].'\')"';
				}
				else {
					$temp_topmenu .= ' onclick="return load_href(\''.$_CFG['PATH']['wepname'].'/index.php?'.$r['href'].'\')"';
				}
				if($r['sel'])
					$temp_topmenu .= ' style="border:2px solid red;"';
				$temp_topmenu .= ' class="'.$r['css'].'" title="'.$r['caption'].'">'.$r['caption'].'</span></div>';
			}
			$temp_topmenu .= '</div>';
			$html .= $temp_topmenu;
		}

		$html .= $temp_pagenum =  tpl_pagenum($data['pagenum']);// pagenum
		$html .= '<div id="tools_block" style="display:none;"></div>';
		if(isset($data['messages']) and count($data['messages'])) {
			include_once($HTML->_PATHd.'php/messages.php');
			$html .= tpl_messages($data['messages']);// messages
		}

		$html .= tpl_data($data['data'],$firstpath);// messages

		$html .= $temp_topmenu; //MENU
		$html .= $temp_pagenum; //pagenum
		$html .= '<div class="clk"></div>';
		return $html;
	}

	function tpl_data(&$data,$firstpath='') {
		if(!$data or !count($data)or !count($data['thitem'])) return '';
		global $_CFG;
		$html .= '<table class="superlist"><tbody><tr>';
		$tdflag = 0;
		if(!isset($data['thitem']['id']))
			$html .= '<th>№</th>';
		foreach($data['thitem'] as $r) {
			if(!$tdflag) {
				if(isset($r['onetd'])){
					$tdflag = 1;
					$r['value'] = $r['onetd'];
				}
				$html .= '<th>';
				if($r['href']!='') {
					$html .= '<a class="'.($r['sel']==1?'bottonimg_sel':'bottonimg').' imgup" title="[SORT]" href="'.$firstpath.'sort='.$r['href'].'" onclick="return load_href(this)"></a>';
				}
				$html .= $r['value'];
				if($r['href']!='') {
					$html .= '<a class="'.($r['sel']==2?'bottonimg_sel':'bottonimg').' imgdown" title="[SORT]" href="'.$firstpath.'dsort='.$r['href'].'" onclick="return load_href(this)"></a>';
				}
				$html .= '</th>';
			}
			if($r['onetd']=='close') $tdflag = 0;
		}
		$html .= '<th>&#160;</th></tr>';
        if(count($data['item']))
		foreach($data['item'] as $k=>$r) {
			$html .= '<tr';
			if($r['css']) $html .= ' class="'.$r['css'].'"';
			elseif($r['style']) $html .= ' style="'.$r['style'].'"';
			$html .= '>';
			if(!isset($data['thitem']['id']))
				$html .= '<td valign="top" id="items_'.$r['id'].'"><a id="elem'.$r['id'].'">'.$r['id'].'</a></td>';
			$tdflag = 0;
			foreach($r['tditem'] as $ktd=>$tditem) {
				if(!$tdflag) {
					$html .= '<td valign="top">';
					if(isset($tditem['onetd'])) $tdflag = 1;
				}

				if($tditem['value']!='') {
					if($tdflag)
						$html .= '<b>'.$data['thitem'][$ktd]['value'].'</b>: ';
					if($tditem['type']=='img') {
						$_CFG['fileIncludeOption']['fancybox'] = 1;
						$html .= '<a rel="fancy" title="рисунок" class="fancyimg" href="'.$tditem['value'].'"><img src="'.$tditem['value'].'" alt="" width="50"/></a>&#160;';
					}
					elseif($tditem['type']=='swf') {
						if($tditem['value']!='')
							$html .= $tditem['value'].'&#160;<object type="application/x-shockwave-flash" data="/'.$tditem['value'].'" height="60" width="200"><param name="movie" value="/'.$tditem['value'].'" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>';
					}
					elseif($tditem['type']=='file') {
						if($tditem['value']!='')
							$html .= '<a href="'.$tditem['value'].'" target="_blank">Файл</a>&#160;';
					}
					elseif($tditem['href']!='') {
						$html .= '<a href="'.$tditem['href'].'" target="_blank">'.$tditem['value'].'</a>&#160;';
					}
					else $html .= $tditem['value'];
				}
				if(isset($tditem['onetd']) and $tditem['onetd']=='close')
					$tdflag = 0;
				if(!$tdflag)
					$html .= '</td>';
				elseif($tditem['value']!='')
					$html .= '<br/>';
			}
			if($tdflag) $html .= '</td>';
			$html .= '<td class="ic" style="vertical-align:top;white-space:nowrap;">';

			if($r['act'])
				$html .= '<a class="bottonimg img'.$r['active'].'" href="'.$firstpath.$data['cl'].'_id='.$r['id'].'&amp;_type='.($r['active']==1?'dis':'act').'" onclick="return load_href(this)" title="['.$_CFG['_ACT_TITLE'][$r['active']].']"></a>';
			if($r['edit'])
				$html .= '<a class="bottonimg imgedit" href="'.$firstpath.$data['cl'].'_id='.$r['id'].'&amp;_type=edit" onclick="return load_href(this)" title="['.$_CFG['_EDIT_TITLE'].']"></a>';
			if($r['del'])
				$html .= '<a class="bottonimg imgdel" href="'.$firstpath.$data['cl'].'_id='.$r['id'].'&amp;_type=del" onclick="return hrefConfirm(this,\'del\')" title="['.$_CFG['_DEL_TITLE'].']"></a>';
			if(isset($r['istree']))
				$html .= '<br/><a href="'.$firstpath.$data['cl'].'_id='.$r['id'].'" onclick="return load_href(this)">'.$r['istree']['value'].' ('.$r['istree']['cnt'].')</a>';
			if(isset($r['child'])) foreach($r['child'] as $ck=>$cn)
				$html .= '<br/><a href="'.$firstpath.$data['cl'].'_id='.$r['id'].'&amp;'.$data['cl'].'_ch='.$ck.'" onclick="return load_href(this)">'.$cn['value'].' ('.$cn['cnt'].')</a>';


			$html .= '</td></tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}


	function tpl_pagenum(&$data) {
		global $_CFG,$_tpl;
		if(!$data or !count($data)) return '';
		$html = '<div class="pagenumcnt">'.$data['cnt'].'&#160;:&#160;&#160;</div>';
		if(count($data['link'])) {
			$_tpl['onload'] .='pagenum_super('.$data['cntpage'].','.$data['_pn'].',\''.$data['modul'].'\','.($data['reverse']?'true':'false').');';
			$html .= '<div class="pagenum">';
			foreach($data['link'] as $k=>$r) {
				if($r['href']=='')
					$html .=  $r['value'];
				elseif($r['href']=='select_page')
					$html .=  '<b>['.$r['value'].']</b>';
				else
					$html .=  '<a href="'.$r['href'].'" onclick="return load_href(this)">'.$r['value'].'</a>';
			}
			$html .= '&#160;</div><div class="ppagenum"></div>';
		}
		$html .= '<select class="mopselect" onchange="setCookie(\''.$data['modul'].'_mop\',this.value,20);window.location.reload();">';
		//,\''.$_CFG['session']['path'].'\',\''.$_CFG['session']['domain'].'\',\''.$_CFG['session']['secure'].'\'
		//JSWin({\'href\':\''.$_CFG['_HREF']['JS'].'?_view=pagenum&amp;_modul='.$data['modul'].'&amp;mop=\'+this.value})
		if(count($data['mop'])) {
			foreach($data['mop'] as $k=>$r) {
				$html .=  '<option value="'.$k.'"'.($r['sel']?' selected="selected"':'').'>'.$r['value'].'</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}
?>