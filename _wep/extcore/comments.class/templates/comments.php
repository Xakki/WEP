<?

	function tpl_comments(&$data) {
		global $_CFG, $HTML;

		$html = '';

		//$html .= $temp_pagenum =  tpl_pagenum($data['pagenum']);// pagenum
		$html .= '<div class="clk"></div><div id="tools_block" style="display:none;"></div>';
		if(isset($data['messages']) and count($data['messages'])) {
			include_once($HTML->_PATHd.'php/messages.php');
			$html .= tpl_messages($data['messages']);// messages
		}
		$html .= '<div class="commentlist"><h3>'.($data['headname']?$data['headname']:'Отзывы').'</h3>';
		$html .= tpl_commdata($data,0,$data['vote'],$data['treelevel']);// messages
		$html .= '</div>';
		//$html .= $temp_pagenum; //pagenum
		$html .= '<div class="clk"></div>';
		return $html;
	}

	function tpl_commdata(&$data,$pid, $vote=0, $treelevel=0) {
		if(!isset($data['data'][$pid]) or !count($data['data'][$pid])) return '';
		global $_CFG;
		$tdflag = 0;
		$html = '';
		foreach($data['data'][$pid] as $k=>$r) {
			$date = static_form::_usabilityDate($r['mf_timecr']);
			$html .= '<div class="commitem" id="commitem'.$r['id'].'">
			<p class="commitemhead"><span class="commdate">['.$date.']</span> <a id="elem'.$r['id'].'"></a>';
			if(isset($_SESSION['user']) and $r['creater_id']==$_SESSION['user'])
				$html .= ' <b>'.$r['name'].'</b>';
			else
				$html .= ' <i>'.$r['name'].'</i>';
			_new_class('extugroup', $EXTUGROUP);
			$html .=  $EXTUGROUP->displayRating($r['vote'],$r['id'],2,$data['modul']);
			if($vote) $html .= '<span class="commvote">'.$r['vote'].'</span>';
			if($treelevel>0) $html .= '<a href="?commanswer='.$r['id'].'#form_comments" class="commanswer" onclick="return commanswer('.$r['id'].','.$r['owner_id'].',\''.$data['modul'].'\');">Ответить</a>';
			$html .= '</p>';

			$html .= '<p class="commitemtext">'.$r['text'].'</p>';
			/*if($r['act'])
				$html .= '<a class="bottonimg img'.$r['active'].'" href="?'.$data['req'].$data['cl'].'_id='.$r['id'].'&amp;_type='.($r['active']==1?'dis':'act').'" onclick="return load_href(this)" title="['.$_CFG['_ACT_TITLE'][$r['active']].']"></a>';
			if($r['edit'])
				$html .= '<a class="bottonimg imgedit" href="?'.$data['req'].$data['cl'].'_id='.$r['id'].'&amp;_type=edit" onclick="return load_href(this)" title="['.$_CFG['_EDIT_TITLE'].']"></a>';
			if($r['del'])
				$html .= '<a class="bottonimg imgdel" href="?'.$data['req'].$data['cl'].'_id='.$r['id'].'&amp;_type=del" onclick="return hrefConfirm(this,\'del\')" title="['.$_CFG['_DEL_TITLE'].']"></a>';*/
			/*if(isset($r['istree']))
				$html .= '<br/><a href="?'.$data['req'].$data['cl'].'_id='.$r['id'].'" onclick="return load_href(this)">'.$r['istree'].' ('.$r['istree']['cnt'].')</a>';
			if(isset($r['child'])) foreach($r['child'] as $ck=>$cn)
				$html .= '<br/><a href="?'.$data['req'].$data['cl'].'_id='.$r['id'].'&amp;'.$data['cl'].'_ch='.$ck.'" onclick="return load_href(this)">'.$cn.' ('.$cn['cnt'].')</a>';*/
			$html .= '<div class="commchild">';
			if(isset($data['data'][$k]))
				$html .= tpl_commdata($data,$k, $vote, ($treelevel-1));
			$html .= '</div>';
			$html .= '<div class="commformanswer"></div>';
			$html .= '</div>';
		}
		return $html;
	}

	
	/*function tpl_pagenum(&$data) {
		global $_CFG;
		if(!$data or !count($data)) return '';
		$html = '<div class="pagenumcnt">'.$data['cnt'].'&#160;:&#160;&#160;</div>';
		if(count($data['link'])) {
			$html .= '<div class="pagenum">';
			foreach($data['link'] as $k=>$r) {
				if($r['href']=='')
					$html .=  $r;
				elseif($r['href']=='select_page')
					$html .=  '<b>['.$r.']</b>';
				else
					$html .=  '<a href="'.$r['href'].'" onclick="return load_href(this)">'.$r.'</a>';
			}
			$html .= '&#160;</div><div class="ppagenum"></div>';
		}
		$html .= '<select class="mopselect" onchange="JSWin({\'href\':\''.$_CFG['_HREF']['JS'].'?_view=pagenum&amp;_modul='.$data['modul'].'&amp;mop=\'+this.value})">';
		if(count($data['mop'])) {
			foreach($data['mop'] as $k=>$r) {
				$html .=  '<option value="'.$k.'"'.($r['sel']?' selected="selected"':'').'>'.$r.'</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}*/