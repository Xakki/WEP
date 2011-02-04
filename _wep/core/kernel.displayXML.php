<?
		$xml = array();
		$this->listfields = array('count(t1.id) as cnt');
		$clause = $this->_moder_clause($param);
		if(count($clause))
			$this->clause =' t1 WHERE '.(implode(' and ',$clause)); 
		else 
			$this->clause =' t1';
		$this->_list();
#		print($this->SQL->query);
		$countfield = $this->data[0]['cnt'];
		
		$cl = $this->_cl;

		if($countfield){
			$xml['data'] = array('cl'=>$cl);
			if($this->_clp!='') 
				$xml['data']['req'] =$this->_clp;
						
			/*** PAGE NUM  REVERSE ***/
			if($this->reversePageN) {
				if($this->_pn == 0) 
					$this->_pn = 1;
				else
					$this->_pn = floor($countfield/$this->messages_on_page)-$this->_pn+1;
			}
			/***/

			$xml['pagenum'] = $this->fPageNav($countfield,$_SERVER['SCRIPT_NAME'].'?'.$this->_clp.(($this->id)?$this->_cl.'_id='.$this->id.'&amp;':''),1);
			$pcnt = 0;
			if($this->reversePageN) {
				if($this->_pn==floor($countfield/$this->messages_on_page)) {
					$this->messages_on_page = $countfield-$this->messages_on_page*($this->_pn-1); // правдивый
					//$this->messages_on_page = $this->messages_on_page*$this->_pn-$countfield; // полная запись
				}
				else
					$pcnt = $countfield-$this->messages_on_page*$this->_pn; // начало отсчета
			}
			else
				$pcnt = $this->messages_on_page*($this->_pn-1); // начало отсчета
			if($pcnt<0)
					$pcnt = 0;
			$climit= $pcnt.', '.$this->messages_on_page;

			$cls =array(array('t1.*'),'',array());
			$arrno = array('active'=>1,'parent_id'=>1);
			if($this->owner and $this->owner->id)
				$arrno[$this->owner_name] = 1;
			$xml['data']['pcnt'] = $pcnt;

			$t=2;
			if(count($this->childs)) foreach($this->childs as $ck=>$cn) {
				$arrno[$ck.'_cnt'] = 1;
				$cls[0][] = 'count(DISTINCT t'.$t.'.id) as '.$ck.'_cnt';
				$cls[1] .=' LEFT JOIN `'.$cn->tablename.'` t'.$t.' ON t'.$t.'.'.$cn->owner_name.'=t1.id';
				/*$temp = $cn->_moder_clause(array(),$param);// сырая и недоработана
				if(count($temp)) $cls[1] .= ' and '.str_replace('t1.','t'.$t.'.',implode(' and ',$temp));
				//if($cn->_join_check==TRUE)
					foreach($cn->fields_form as $cnk=>$cnr){
						if(is_array($cnr['listname']) and isset($cnr['listname']['join']) and $cnr['listname']['class']){
							$t++;
							//if (isset($cnr['listname']['include']))
							//	require_once($this->_CFG['_PATH']['ext'].$cnr['listname']['include'].'.class.php');
							$cls[1] .=' AND t'.$t.'.id>0 RIGHT JOIN '.getTableNameOfClass($classname).' t'.$t.' ON t'.($t-1).'.'.$cnk.'=t'.$t.'.id ';
							if(isset($cnr['listname']['join']) and $cnr['listname']['join']!='')
								$cls[1] .= 'and '.str_replace('tx.','t'.$t.'.',$cnr['listname']['join']).' ';
						}
					}
				//if(isset($cn->fields['region_id'])) $cls[1] .=' and t'.$t.'.region_id='.$_SESSION['city'];
				*/
				$t++;
			}
			if($this->mf_istree) {
				$arrno['istree_cnt']=1;
				$cls[0][] = 'count(DISTINCT t'.$t.'.id) as istree_cnt';
				$cls[1] .=' LEFT JOIN `'.$this->tablename.'` t'.$t.' ON t'.$t.'.parent_id=t1.id';
				$t++;
			}

			if($this->ordfield!='') $order='t1.'.$this->ordfield;
			else $order='t1.id';
			foreach($this->fields_form as $k=>$r) {
				if(isset($r['mask']['usercheck']) and !_prmUserCheck($r['mask']['usercheck']))
					{$arrno[$k]=1; continue;}
	
				if($this->_prmSortField($k)) {
					if(isset($_GET['sort']) and $k==$_GET['sort']) 
						$order = (is_string($r['mask']['sort'])?$r['mask']['sort']:'t1.').$k;
					elseif(isset($_GET['dsort']) and $k==$_GET['dsort']) 
						$order = (is_string($r['mask']['sort'])?$r['mask']['sort']:'t1.').$k.' DESC';
				}

				if($r['mask']['fview']==1 or $r['mask']['disable'] or $r['type']=='hidden' or $r['type']=='info')
					$arrno[$k]=1; 
				elseif(!isset($arrno[$k])) {
					if(isset($r['listname']) and is_array($r['listname']) and (isset($r['listname']['class']) or isset($r['listname']['tablename']))) {
						$lsn = $r['listname'];
						if(!$lsn['nameField'])
							$lsn['nameField'] = 't'.$t.'.name';
						else 
							$lsn['nameField'] = str_replace('tx.','t'.$t.'.',$lsn['nameField']);
						//if (isset($lsn['include']))
						//	require_once($this->_CFG['_PATH']['ext'].$lsn['include'].'.class.php');
						if($r['multiple']==1)
							$cls[0][] = 'group_concat('.$lsn['nameField'].' SEPARATOR " | ") as name_'.$k;
						else
							$cls[0][] = $lsn['nameField'].' as name_'.$k;

						if(!isset($lsn['join'])) 
							$cls[1] .= ' LEFT';

						$cls[1] .= ' JOIN `'.($lsn['class']?getTableNameOfClass($lsn['class']):$lsn['tablename']).'` t'.$t.' ON ';

						if(!$lsn['idField']) 
							$lsn['idField'] = 't'.$t.'.id';
						else 
							$lsn['idField'] = str_replace('tx.','t'.$t.'.',$lsn['idField']);

						if(isset($lsn['join']) or isset($lsn['leftJoin'])) {
							if(!isset($lsn['idThis'])) 
								$lsn['idThis'] = $k;
							$cls[1] .= ' '.$lsn['idField'].'=t1.'.$lsn['idThis'].' '.str_replace('tx.','t'.$t.'.',($lsn['leftJoin'].$lsn['join']));
						}
						elseif($r['multiple']==1)
							$cls[1] .= 't1.'.$k.' LIKE concat("%|",'.$lsn['idField'].',"|%") ';
						else
							$cls[1] .= 't1.'.$k.'='.$lsn['idField'].' ';
						$t++;
					}elseif(isset($r['listname']) and !is_array($r['listname'])) {
						$this->_checkList($r['listname'],0);
					}

					$act=0;
					if($this->_prmSortField($k)) {
						if($_GET['sort']==$k) $act=1;
						elseif($_GET['dsort']==$k) $act=2;
						$temphref = $k.(($this->id)?'&amp;'.$this->_cl.'_id='.$this->id:'');
					}
					else $temphref = '';
					$xml['data']['thitem'][$k] = array('value'=>$r['caption'],'href'=>$temphref,'sel'=>$act);
					if(isset($r['mask']['onetd']))
						$xml['data']['thitem'][$k]['onetd'] = $r['mask']['onetd'];
				}
			}

			/** Сборка запроса на вывод*/
			//$cls[2] = $this->_moder_clause($cls[2],$param);
			$cls[2] = array_merge($cls[2], $clause);
			if(count($cls[2])>0) $cls[1] .=' WHERE '.implode(' AND ',$cls[2]);

			$this->listfields = $cls[0];
			$this->clause = 't1 '.$cls[1].' GROUP BY t1.id';
			if($order!='') $this->clause .= ' ORDER BY '.$order;
			//if(!$this->mf_istree)
				$this->clause .= ' LIMIT '.$climit;
			$this->_list('id');
//print($this->SQL->query);
			/** Обработка запроса*/
			foreach($this->data as $key=>$row) {
				$xml['data']['item'][$key] = array('id'=>$row['id']);
				$xml['data']['item'][$key] += $this->_tr_attribute($row,$param);
				if($xml['data']['item'][$key]['act'])
					$xml['data']['item'][$key]['active'] = $row['active'];
				foreach($this->fields_form as $k=>$r) {
					if(isset($arrno[$k])) continue;
					$tditem = array('name'=>$k,'type'=>$r['type']);
					if($r['type']=='file') {
						if(isset($this->_CFG['form']['flashFormat'][$row['_ext_'.$k]])) $tditem['fileType']='swf';
						elseif(isset($this->_CFG['form']['imgFormat'][$row['_ext_'.$k]])) $tditem['fileType']='img';
						else $tditem['fileType']='file';
					}

					if(isset($r['mask']['href']))
						$tditem['href'] = str_replace('{id}',$row['id'],$r['mask']['href']);
					elseif($r['type']=='attach')
						$tditem['href'] = $row[$k];
					if(isset($r['mask']['onetd']))
						$tditem['onetd'] = $r['mask']['onetd'];

					/** Отображаем "значение" если НЕ мультистолбец или если "значение" TRUE*/
					if(!isset($r['mask']['onetd']) or ($row[$k]!='0' and $row[$k]!='') or $r['type']=='list') {

						if(isset($this->memos[$k]))
							$tditem['value'] .= _substr(strip_tags(htmlspecialchars_decode(file_get_contents($row[$k]))),0,400);
						elseif($r['type']=='date') {
							$temp = '';
							if(!isset($r['mask']['format']))
								$r['mask']['format'] = 'Y-m-d H:i';							
							// Тип поля
							if($this->fields[$k]['type']=='int'  and $row[$k]){
								$temp = date($r['mask']['format'],$row[$k]);
							}
							elseif($this->fields[$k]['type']=='timestamp' and $row[$k]){
								$fs = explode(' ', $row[$k]);
								$f = explode('-', $fs[0]);
								$s = explode(':', $fs[1]);
								$temp = mktime($s[0], $s[1], $s[2], $f[1], $f[2], $f[0]);
								
								if($r['mask']['time'])
									$r['mask']['format'] = $r['mask']['format'].' '.$r['mask']['time'];
								
								$temp = date($r['mask']['format'],$temp);
							}
							
							$tditem['value'] .= $temp;
							
						}
						elseif($k=='mf_ipcreate')
							$tditem['value'] .= long2ip($row[$k]);
						elseif($r['type']=='checkbox')
							$tditem['value'] .= $this->_CFG['enum']['yesno'][$row[$k]];
						elseif(isset($r['listname']) and is_array($r['listname'])) {//isset($row['name_'.$k])
							if($r['multiple']) 
								$tditem['value']= str_replace('|',', ',trim($row['name_'.$k],'|'));
							else
								$tditem['value'] = $row['name_'.$k];
						}
						elseif(isset($r['listname']) and $r['listname']) {// and !is_array($r['listname'])
							if($r['multiple']) 
								$row[$k]= explode('|',trim($row[$k],'|'));
							else
								$row[$k] = array($row[$k]);
							$temp=array();

							foreach($row[$k] as $er) {
								if(isset($this->_CFG['enum_check'][$r['listname']][$er])) {
									$templist = $this->_CFG['enum_check'][$r['listname']][$er];
									if(!is_array($templist)) {
										$temp[] = $templist;
									} elseif(isset($templist['#name#'])) {
										$temp[] = $templist['#name#'];
									} else
										$temp[] = '#unknown_data#';
								}elseif($er)
									$temp[] = '<span style="color:gray;">'.$er.'</span>';
							}
							$tditem['value'] .= implode(', ',$temp);
						}
						elseif(isset($r['mask']['substr']) and $r['mask']['substr']>0)
							$tditem['value'] .= _substr(strip_tags(htmlspecialchars_decode($row[$k])),0,$r['mask']['substr']);
						else//if($r['type']!='file')
							$tditem['value'] .= $row[$k];
					}
					$xml['data']['item'][$key]['tditem'][$k] = $tditem;
				}
				if(count($this->childs))
					foreach($this->childs as $ck=>$cn) {
						if(count($cn->fields_form))
							$xml['data']['item'][$key]['child'][$ck] = array('value'=>$cn->caption, 'cnt'=>$row[$ck.'_cnt']);
					}
				if($this->mf_istree)
					$xml['data']['item'][$key]['istree'] = array('value'=>$this->caption, 'cnt'=>$row['istree_cnt']);
			}
		}else
			$xml['messages'][] = array('value'=>'Пусто','name'=>'alert');


		return  $xml;

?>
