<?php
	function tpl_boarditems(&$data) {
		global $_tpl,$_CFG,$HTML;
		$html = '';
		$comm = false;
		//print_r('<pre>');print_r($data);
		if(!isset($data['items']) or !count($data['items']))
			$html = '<div class="divform">	<div class="messages"><div class="error">Ссылка не верна. Вероятно данное объявление было удалено с сайта пользователем.</div></div></div>';
		else {
			$html = '<ul class="boardlist">';
			foreach($data['items'] as $k=>$r) {
				$html .= '<li>
					<div class="name">
						['.date('Y-m-d',$r['datea']).']&#160;
						'.$r['tname'].' / '.implode(' / ',$r['rname']).' '.$r['name'].'
						'.($r['moder']==1?'&#160;&#160;<i><a href="/_wep/index.php?_view=list&_modul=board&board_id='.$r['id'].'&_type=edit" target="_blank">Редактировать</a></i>':'')
						.'
					</div>';
				if(count($r['paramdata'])) {
					$html .= '<table class="boardparam"><tbody>';
					foreach($r['paramdata'] as $kp=>$rp) {
						$html .= '
						<tr><td class="tdn">'.$kp.'</td><td class="tdt">'.$rp['value'].'&#160;'.$rp['edi'].'</td></tr>
						';
					}
					$html .= '</tbody></table>';
				}
				$html .= '<div class="text">'.$r['text'].'</div>
					<noindex>';
				if($r['contact']) {
					//if($this->_CFG['robot']=='')
						$html .= '<div class="contact">'.static_main::redirectLink($r['contact'],false).'</div>';
				}
				if($r['phone'])
					$html .= '<div class="phone">'.$r['phone'].'</div>';
				if($r['email'])
					$html .= '<div class="email"><a href="/mail_'.$r['id'].'.html" target="_blank">Написать письмо</a></div>';
				$html .= '</noindex>
					<div class="imagebox">';
				$firstimg = '';
					foreach($r['img'] as $img) {
						if($img['s'] and $img['f']) {
							$html .= '<a href="/'.$img['f'].'" rel="fancy'.$r['id'].'" title="'.$r['name'].'" class="fancyimg"><img src="/'.$img['s'].'" alt="'.$r['name'].'"/></a>';
							if($firstimg==='')
								$firstimg = $_CFG['_HREF']['BH'].$img['f'];
						}
					}

				$html .= '</div>';
					/*<xsl:if test="count(nomination)>0">
						<div class="nomination"><span class="nomination-name">Номинация объявления за</span><br/>
							<xsl:for-each select="nomination">
								<xsl:if test="@sel=1">
									<span class="nomination-item navi1"><xsl:value-of disable-output-escaping="yes" select="."/> [<xsl:value-of select="@value"/>]</span>
								</xsl:if>
								<xsl:if test="@sel=0">
									<span class="nomination-item navi0" onclick="JSWin({{'insertObj':this,'data':'_view=boardvote&_id={../id}&_type={@type}','href':'/_js.php'}})"><xsl:value-of disable-output-escaping="yes" select="."/> [<xsl:value-of select="@value"/>]</span>
								</xsl:if>
							</xsl:for-each>
						</div>
					</xsl:if>*/
				$html = str_replace('$','&#036;',$html);
				$html .= '<div class="nomination">{$_tpl[\'share\']}</div>';

				$html .= '</li>';
				if($r['mapx']) {
					//.ru ALOgRE0BAAAAv6zZcQIAjsjexB7rFg3HTA_g1j-coGlstYMAAAAAAAAAAAD2tWiNHDQrFWdJRx7iuVAiNWEmTA==
					//.i AOCoRE0BAAAA88JZPQIANdFMmqSCC13UptUv7elqUYOoyxQAAAAAAAAAAADQ4SW-iwo9kv-xUCuu5MlHifOX8w==
					//унидоски.рф AEBlTE0BAAAAI8CRMQIACx_AbSrbH-5VVjtyAEq4d1AmTZsAAAAAAAAAAABOmdN9uXx5VhMuwpOp8geXLZRLCg==
					$_tpl['script']['api-maps'] = array('http://api-maps.yandex.ru/1.1/index.xml?loadByRequire=1&key=ALOgRE0BAAAAv6zZcQIAjsjexB7rFg3HTA_g1j-coGlstYMAAAAAAAAAAAD2tWiNHDQrFWdJRx7iuVAiNWEmTA==~AOCoRE0BAAAA88JZPQIANdFMmqSCC13UptUv7elqUYOoyxQAAAAAAAAAAADQ4SW-iwo9kv-xUCuu5MlHifOX8w==~AEBlTE0BAAAAI8CRMQIACx_AbSrbH-5VVjtyAEq4d1AmTZsAAAAAAAAAAABOmdN9uXx5VhMuwpOp8geXLZRLCg==');
				}
				if($r['on_comm'])
					$comm = true;
			}
			$html .= '</ul>';
		}

		if($comm or (count($data['items']) and $data['config']['onComm']==2)) {
			$html .= '{$_tpl[\'scomments\']}';
		}
		return $html;
	}
