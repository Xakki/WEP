<?
/*VERSION=2.2a*/
/*COMMENT=Ядро, дополняющая модули*/
abstract class kernel_class{

	/*function __get($name) {
		print('Привет, вы пытались обратиться к $name');
	} */

    function __construct(&$SQL, $owner=NULL, $alias = '') {
		global $_CFG;
		$this->_CFG = &$_CFG;//Config
		$this->SQL = $SQL;//link to sql class
		if(!$this->SQL)
			trigger_error("SQL class missing.", E_USER_WARNING);
		$this->owner = &$owner;//link to owner class
		if(!isset($this->_autoCheckMod)) {
			if($SQL->_iFlag)
				$this->_autoCheckMod = true;
			else
				$this->_autoCheckMod = false;
		}

		$this->_set_features(); // настройки модуля

		$this->_create_conf(); // загрузки формы конфига

		if (isset($this->config_form) and count($this->config_form)) { // загрузка конфига из фаила для модуля
			$this->configParse();
		}
		
		$this->_create();// предустановки модуля

		if($this->_autoCheckMod){ // вкл режим автосоздания полей и проверки модуля
			$this->_checkmodstruct();
		}
	}

	function __destruct(){

	}

/*-----------CMS---FUNCTION------------
_set_features()
_create()
create_child($class_name, $alias='')
_checkmodstruct() 

_install() 
_reinstall()
_insertDefault()
_checkdir($dir)
_fldformer($key, $param) 

--------------------------------------*/

	protected function _set_features() {// initalization of modul features
		//$this->mf_issimple = false;
		//$this->mf_typectrl = false;
		//$this->mf_struct_readonly = false;
		$this->mf_istree = false; // древовидная структура?
		$this->mf_ordctrl = false; // поле ordind для сортировки
		$this->mf_actctrl = false; // поле active
		$this->mf_use_charid = false;//if true - id varchar
			$this->mf_idwidth = 63; // длина поля ID
		$this->_setnamefields=true;//добавлять поле name
		$this->mf_timestamp = false; // создать поле  типа timestamp
		$this->mf_timecr = false; // создать поле хранящще время создания поля
		$this->mf_timeup = false; // создать поле хранящще время обновления поля
		$this->mf_timeoff = false; // создать поле хранящще время отключения поля (active=0)
		$this->mf_createrid = true;//польз владелец
		$this->mf_ipcreate = false;//IP адрес пользователя с котрого была добавлена запись	
		$this->mf_indexing = false; // индексация
		$this->mf_add = true;// добавить в модуле
		$this->mf_del = true;// удалять в модуле
		$this->owner_unique = false; // поле owner_id не уникально
		$this->showinowner = true;// показывать под родителем
		$this->mf_mop = true;// выключить постраничное отображение
			$this->reversePageN = false; // обратный отчет для постраничного отображения
			$this->messages_on_page = 20;//число эл-ов на странице
			$this->numlist=20;//максим число страниц при котором отображ все номера страниц
		$this->mf_statistic = false; // показывать  статистику по дате добавления
		$this->cf_childs = false; // true - включить управление подмодулями в настройках модуля
		$this->ver = '0.1';

		$this->text_ext = '.txt';// расширение для memo фиаилов

		$this->_cl = str_replace('_class','',get_class($this));
		$this->owner_name = 'owner_id';
		$this->nick = $this->_cl;
		$this->tablename = $this->_CFG['sql']['dbpref'].$this->_cl;
		$this->caption = $this->_cl;
		$this->version = 'unknown';
		$this->_listfields = array('name');
		$this->_unique =
		$this->_enum =
		$this->update_records =
		$this->def_records =
		$this->fields =
		$this->fields_form =
		$this->attaches =
		$this->memos =
		$this->childs =
		$this->services =
		$this->index_fields = array();
		$this->ordfield = '';
		return 0;
	}

	protected function _create() {

		$this->_listnameSQL = ($this->_setnamefields?'name':'id'); // для SQL запроса при выводе списка
		$this->_listname = ($this->_setnamefields?'name':'id');// ', `_listnameSQL` as `_listname`'

		// construct fields
		if ($this->mf_use_charid) 
			$this->fields['id'] = array('type' => 'varchar', 'width' => $this->mf_idwidth, 'attr' => 'NOT NULL');
		else
			$this->fields['id'] = array('type' => 'int', 'attr' => 'unsigned NOT NULL AUTO_INCREMENT');
		
		if($this->_setnamefields) 
			$this->fields['name'] = array('type' => 'varchar', 'width' => $this->mf_idwidth, 'attr' => 'NOT NULL');

		if ($this->owner) 
		{
			$this->fields[$this->owner_name] = $this->owner->fields['id'];
			$this->fields[$this->owner_name]['attr'] = '';
		}

		if($this->mf_createrid){
			$this->fields['creater_id'] = array('type' => 'varchar', 'width' => $this->mf_idwidth, 'attr' => 'NOT NULL');
		}

		if ($this->mf_istree) 
		{
			$this->fields['parent_id'] = $this->fields['id'];
			$this->fields['parent_id']['attr'] = 'NOT NULL DEFAULT ';
			if($this->mf_use_charid) $this->fields['parent_id']['attr'] .='""';
			else $this->fields['parent_id']['attr'] .='0';
		}

		if ($this->mf_actctrl) 
			$this->fields['active'] = array('type' => 'bool', 'attr' => 'NOT NULL DEFAULT 1','default'=>1);

		if($this->mf_timestamp) 
			$this->fields['_timestamp'] = array('type'=>'timestamp');
		if($this->mf_timecr)
			$this->fields['mf_timecr'] = array('type'=>'int', 'width'=>11, 'attr' => 'unsigned NOT NULL');
		if($this->mf_timeup) 
			$this->fields['mf_timeup'] = array('type'=>'int', 'width'=>11, 'attr' => 'unsigned NOT NULL');
		if($this->mf_timeoff) 
			$this->fields['mf_timeoff'] = array('type'=>'int', 'width'=>11, 'attr' => 'unsigned NOT NULL');
		if($this->mf_ipcreate) 
			$this->fields['mf_ipcreate'] = array('type'=>'bigint', 'width'=>20, 'attr' => 'unsigned NOT NULL');

		/*if ($this->mf_typectrl)
			$this->fields['typedata'] = array('type' => 'tinyint', 'attr' => 'unsigned NOT NULL');
		*/
		if ($this->mf_ordctrl) //Содание полей для сортировки
		{
			$this->fields['ordind'] = array('type' => 'int','width'=>'10', 'attr' => 'NOT NULL');
			$this->ordfield = 'ordind';
		}

		//pagenum
		if(isset($_GET[$this->_cl.'_mop'])) {
			$this->messages_on_page=(int)$_GET[$this->_cl.'_mop'];
			if($_COOKIE[$this->_cl.'_mop']!=$this->messages_on_page)
				_setcookie($this->_cl.'_mop',$this->messages_on_page, $this->_CFG['remember_expire']);
		}
		elseif(isset($_COOKIE[$this->_cl.'_mop']))
			$this->messages_on_page=(int)$_COOKIE[$this->_cl.'_mop'];
		// номер текущей страницы
		if(isset($_REQUEST[$this->_cl.'_pn']) && (int)$_REQUEST[$this->_cl.'_pn'])
			$this->_pn = (int)$_REQUEST[$this->_cl.'_pn'];
		elseif(isset($_REQUEST['_pn']) && (int)$_REQUEST['_pn'])
			$this->_pn = (int)$_REQUEST['_pn'];
		elseif($this->reversePageN)
			$this->_pn = 0;
		else
			$this->_pn = 1;

		$this->attprm = array('type' => 'varchar(4)', 'attr' => 'NOT NULL DEFAULT \'\'');

		if($this->cf_childs and $this->config['childs']) {
			foreach($this->config['childs'] as $r) {
				if(file_exists($this->_CFG['_PATH']['ext'].$this->_cl.'.class/'.$r.'.childs.php'))
					include_once($this->_CFG['_PATH']['ext'].$this->_cl.'.class/'.$r.'.childs.php');
				elseif(file_exists($this->_CFG['_PATH']['extcore'].$this->_cl.'.class/'.$r.'.childs.php'))
					include_once($this->_CFG['_PATH']['extcore'].$this->_cl.'.class/'.$r.'.childs.php');
				$this->create_child($r);
			}
		}

		return 0;  
	}

	protected function _create_conf() { // Здесь можно установить стандартные настройки модулей
		if($this->cf_childs) {
			$this->config['childs'] = '';
			$this->config_form['childs'] = array('type' => 'list', 'multiple'=>1, 'listname'=>'child.class', 'caption' => 'Подмодули');
		}
	}

	protected function configParse() {
		if (isset($this->config_form)) { // загрузка конфига из фаила для модуля
			$this->_file_cfg = $this->_CFG['_PATH']['config'].get_class($this).'.cfg';
			if (file_exists($this->_file_cfg)) {
				$this->config = array_merge($this->config,_fParseIni($this->_file_cfg));}
		}
	}

	protected function create_child($class_name, $alias='')
	{
		$cl = $class_name.'_class';
		$this->childs[$class_name] = new $cl($this->SQL, $this, $alias);
	}

	protected function _checkmodstruct() 
	{
		return include($_CFG['_PATH']['core'].'kernel.checkmodstruct.php');
	}

	public function _install() 
	{
		return include($_CFG['_PATH']['core'].'kernel.install.php');
	}

	public function _insertDefault(){
		foreach($this->def_records as $row)
		{
			$result = $this->SQL->execSQL('INSERT INTO `'.$this->tablename.'` ('.implode(',',array_keys($row)).') values (\''.implode("','",$row).'\')');
			if ($result->err) return $this->_message($result->err);
			$this->_message('Insert default records into table '.$this->tablename.'.',2);
		}
		return 0;
	}

	public function _checkdir($dir)
	{
		include_once($_CFG['_PATH']['core'].'kernel.tools.php');
		return _checkdir($this,$dir);
	}

	public function _fldformer($key, $param) 
	{
		$m = '`'.$key.'` '.$param['type'];

			if (isset($param['width']) && $param['width']!='') 
				$m.= '('.$param['width'].')'; 

		$m.=' '.(isset($param['attr'])?$param['attr']:'').(isset($param['default'])?' DEFAULT \''.$param['default'].'\'':'');
		return $m;
	}

/*-----------MODUL---FUNCTION------------
_list($ord='')
_select($active=0)
_select_fields($active)
_select_attaches()
_select_memos()
_get_file($row, $key) 
_message($msg,$type=0)

---------------------------------------*/

	public function _dump($where='') 
	{
		$name = 'name';
		if (!isset($this->fields['name'])) 
			$name = 'id as '.$name;
		if($where!='') $where =' WHERE '.$where;
		$result = $this->SQL->execSQL('SELECT id, '.$name.' FROM `'.$this->tablename.'`'.$where);
		if ($result->err) return $this->_message($result->err);
		$data = array();
		while (list($key, $value) = $result->fetch_array(MYSQL_NUM))
			$data[$key] = $value;
		return $data;
	}

	public function  _list($ord='',$ord2='') {
/*--relative ----------- LIST LIST LIST ------------ relative --*/

	// in:  ulflds											req
	//		clause:string									req
	// out: data:array of rows:assoc array
	// return:	0 - success,otherwise errorcode

		$query = 'SELECT ';
		if (count($this->listfields)) $query .= implode(', ', $this->listfields);
		else $query .= '*';

		$from = ' FROM `'.$this->tablename.'` ';

		$result = $this->SQL->execSQL($query.$from.$this->clause);
		if ($result->err) return $this->_message($result->err);
		$this->data = array();
		if($ord!='' and $ord2!=''){
			while ($row = $result->fetch_array())
				$this->data[$row[$ord2]][$row[$ord]] = $row;
		}
		elseif($ord!=''){
			while ($row = $result->fetch_array())
				$this->data[$row[$ord]] = $row;
		}
		else{
			while ($row = $result->fetch_array())
				$this->data[] = $row;
		}

		if ($this->_select_attaches()) return 1;
		if ($this->_select_memos()) return 1;
		unset($result);
		return $this->_message('Select from `'.$this->caption.'` successful.',3);
	}

	public function _select() {/*------- SELECT ---------*/
	// in:  id											req
	// out: data:array of rows:assoc array
	// return:	0 - success,
	//			otherwise errorcode
		$this->data = array();

		if ($this->_select_fields()) return 1;
		if ($this->_select_attaches()) return 1;
		if ($this->_select_memos()) return 1;

		return $this->_message('Select from '.$this->caption.' successful!',3);
	}

	private function _select_fields() { 
		$agr = ', '.$this->_listnameSQL.' as name';
		$pref = 'SELECT *'.$agr;
		$sql_query = $pref.' FROM `'.$this->tablename.'`';
		if($this->id) $sql_query .= ' WHERE id IN ('.$this->_id_as_string().')';
		if ($this->ordfield) $sql_query .= ' ORDER BY '.$this->ordfield;
		$result = $this->SQL->execSQL($sql_query);
		if ($result->err) return $this->_message($result->err);
		while ($row = $result->fetch_array())
			$this->data[$row['id']] = $row;
		return 0;
	}

	private function _select_attaches() {
		if (!count($this->attaches) or !count($this->data)) return 0;
		else{
			foreach($this->data as $ri => &$row) {
				if (!isset($row['id'])) return 0;
				$merg = array_intersect_key($this->attaches,$row);
				if(!count($merg)) return 0;
				foreach($merg as $key => $value) {
					$row['_ext_'.$key] = $row[$key];
					$row[$key] = $this->_get_file($row,$key);
				}
			}
		}
		return 0;
	}

	private function _select_memos() {
		if (!count($this->memos)) return 0;
		foreach($this->data as $ri => &$row) {
			foreach($this->memos as $key => $value) {
				if (isset($row['id']))
				{
					$f = $this->_CFG['_PATH']['path'].$this->getPathForMemo($key).'/'.$row['id'].$this->text_ext;
					if (file_exists($f))
						$row[$key] = $f;
				}
			}
		}
		return 0;
	}

/*------------- ADD ADD ADD ADD ADD ------------------*/

	// in:  id			opt
	//		fld_data:assoc array <fieldname>=><value> 	req
	//		att_data:assoc array <fieldname>=>array 	req
	//		mmo_data:assoc array <fieldname>=>text	req
	// out: 0 - success,
	//      otherwise errorcode

	protected function _add() {
		include_once($_CFG['_PATH']['core'].'kernel.addup.php');
		return _add($this);
	}

/*------------- UPDATE UPDATE UPDATE -----------------*/

	// in:  id											req
	//		fld_data:assoc array <fieldname>=><value> 	req
	//		att_data:assoc array <fieldname>=>array 	req
	//		mmo_data:assoc array <fieldname>=>text		req
	// out: 0 - success,
	//      otherwise errorcode

	protected function _update() {
		include_once($_CFG['_PATH']['core'].'kernel.addup.php');
		return _update($this);
	}

/*------------- DELETE DELETE DELETE -----------------*/

	// in:  id											req
	// out: 0 - success,
	//      otherwise errorcode

	protected function _delete() {
		include_once($_CFG['_PATH']['core'].'kernel.addup.php');
		return _delete($this);
	}

	public function _get_file($row,$key) {
		if($row[$key]!='')
		{
			$f = $this->getPathForAtt($key).'/'.$row['id'].'.'.$row[$key];
			if (file_exists($this->_CFG['_PATH']['path'].$f) and $size=@filesize($this->_CFG['_PATH']['path'].$f)) 
				return $f.'?size='.$size;
		}
		return '';
	}

	private function _get_file2($id,$key,$extValue='',$modkey=-1) {
		if(!$id) $id = $this->id;
		if(!$extValue and $this->data[$id]) $extValue = $this->data[$id]['_ext_'.$key];
		$pref = '';
		if($this->attaches[$key]['thumb'][$modkey]['pref']) 
			$pref = $this->attaches[$key]['thumb'][$modkey]['pref'];
		if(!$id or !$extValue or !$key) return '';

		if($this->attaches[$key]['thumb'][$modkey]['path'])
			$pathimg = $this->attaches[$key]['thumb'][$modkey]['path'].'/'.$pref.$id.'.'.$extValue;
		elseif($this->attaches[$key]['path'])
			$pathimg = $this->attaches[$key]['path'].'/'.$pref.$id.'.'.$extValue;
		else
			$pathimg = $this->getPathForAtt($key).'/'.$pref.$id.'.'.$extValue;
		return $pathimg;
	}
	protected function _prefixImage($path,$pref)
	{
		if(trim($path)!='')
		{
			$img =  _substr($path,0,strrpos($path, '/')+1).$pref._substr($path,strrpos($path, '/')+2-count($path));
			if(file_exists($this->_CFG['_PATH']['path']._substr($img,0,strrpos($img, '?'))))
				return $img;
		}
		return $path;
	}

	public function _id_as_string() {
		if (is_array($this->id)) {
		/*	foreach($this->id as $key => $value)
				$this->id[$key] = $value;*/
			return '\''.implode('\',\'', $this->id).'\'';
		}
		else
			return '\''.$this->id.'\'';
	}


	public function _get_new_ord() {
		$query = 'SELECT max(ordind) + 1 FROM `'.$this->tablename.'`';
		$result=$this->SQL->execSQL($query);
		if($result->err) return $this->_message($result->err);
		list($this->ordind) = $result->fetch_array(MYSQL_NUM);
		if(!$this->ordind) $this->ordind=0;
		return 0;
	}


/*------------- ORDER ORDER ORDER ORDER ----------------*/

	public function _sorting($arr) {
		if(!$this->mf_ordctrl) return $this->_message('Sorting denied!');
		foreach($arr as $r) {
			$id = str_replace($this->_cl.'_','',$r['id']);
			$id2 = str_replace($this->_cl.'_','',$r['id2']);
			$data=array();
			$qr = 'select id,ordind from `'.$this->tablename.'`';
			$result=$this->SQL->execSQL($qr);if($result->err) return $this->_message($result->err);
			while ($row = $result->fetch_array()) {
				$data[$row['id']]=(int)$row['ordind'];
			}
			$ex=0;
			if($r['t']=='next' and ($data[$id2]-1)==$data[$id])
				$ex=1;
			elseif($r['t']=='prev' and ($data[$id2]+1)==$data[$id])
				$ex=1;
			if($ex!=1) {
				$qr = 'UPDATE `'.$this->tablename.'` SET `ordind` = -2147483647 WHERE id=\''.$id.'\'';
				$result=$this->SQL->execSQL($qr);if($result->err) return $this->_message($result->err);

				if($r['t']=='next' and $data[$id2]<$data[$id]) {
					$ord= $data[$id2];
					$qr = 'UPDATE `'.$this->tablename.'` SET `ordind` = (ordind+1) WHERE '.$data[$id2].'<=ordind and ordind<='.$data[$id].' order by `ordind` DESC';
				}
				else {
					if($r['t']=='next')
						$ord= $data[$id2]-1;
					else
						$ord= $data[$id2];
					$qr = 'UPDATE `'.$this->tablename.'` SET `ordind` =(ordind-1) WHERE '.$data[$id].'<=`ordind` and `ordind`<='.$ord.' order by `ordind`';
				}
				$result=$this->SQL->execSQL($qr);if($result->err) return $this->_message($result->err);

				$qr = 'UPDATE `'.$this->tablename.'` SET `ordind` = '.$ord.' WHERE `id`=\''.$id.'\'';
				$result=$this->SQL->execSQL($qr);if($result->err) return $this->_message($result->err);
			}
		}

		return $this->_message('Sorting the module `'.$this->caption.'` successful.',2);
	}


/************************* EVENTS *************************/	
	
	public function _message($msg,$type=0) 
	{
		$ar_type = array('error' , 'warning' , 'modify' , 'notify','ok');
		if($type<3 or $_SESSION['_showallinfo']>1) $this->_CFG['logs']['mess'][] = array($msg,$ar_type[$type]);
		if(!$type) return 1;
		else return 0;
	}

/**************************ADMIN-PANEL---FUNCTION*************************/

	public function fXmlModuls($modul){
		include_once($_CFG['_PATH']['core'].'kernel.moderxml.php');
		return _fXmlModuls($this,$modul);
	}
	
	public function fXmlModulsTree($modul,$id){
		include_once($_CFG['_PATH']['core'].'kernel.moderxml.php');
		return _fXmlModulsTree($this,$modul,$id);
	}

// *** PERMISSION ***//

	public function _moder_prm(&$data,&$param) {
		if(count($param['prm'])) {
			foreach($param['prm'] as $k=>$r){
				foreach($data as $row) 
					if($row[$k]!=$r) return false;
			}
		}
		return true;
	}


	public function _prmModulAdd($mn){
		if(!$this->mf_add) return false;
		if(_prmModul($mn,array(9))) return true;
		return false;
	}

	public function _prmModulEdit(&$data,&$param){
		if(count($param['prm'])) {
			foreach($param['prm'] as $k=>$r){
				foreach($data as $row) 
					if($row[$k]!=$r) return false;
			}
		}
		if(_prmModul($this->_cl,array(3))) return true;
		if($this->mf_createrid and _prmModul($this->_cl,array(4)) and $data['creater_id']==$_SESSION['user']['id']) return true;
		return false;
	}

	public function _prmModulDel($dataList,$param=array()){//$dataList нельзя по ссылке
		if(!$this->mf_del) return false;
		if(_prmModul($this->_cl,array(5))) return true;
		if($this->mf_createrid and _prmModul($this->_cl,array(6))) {
			foreach($dataList as $k=>$r)
				if($r['creater_id']!=$_SESSION['user']['id']) return false;
			return true;
		}
		return false;
	}

	public function _prmModulAct($dataList,$param=array()){//$dataList нельзя по ссылке
		if(!$this->mf_actctrl) return false;
		if(_prmModul($this->_cl,array(7))) return true;
		if($this->mf_createrid and _prmModul($this->_cl,array(8))) {
			foreach($dataList as $k=>$r)
				if($r['creater_id']!=$_SESSION['user']['id']) return false;
			return true;
		}
		return false;
	}

	public function _prmModulShow($mn){
		if(_prmModul($mn,array(1))) return false;
		if($this->mf_createrid and _prmModul($mn,array(2))) return true;
		return false;
	}

	private function _prmSortField($key) {
		if(isset($this->fields_form[$key]['mask']['sort']))
			return true;
		elseif($key=='name' or $key=='ordfield' or $key=='active')
			return true;
		return false;
	}

// --END PERMISSION -----//

// MODUL configuration

	public function confirmReinstall(){
		$this->form = $mess = array();
		if(!_prmModul($this->_cl,array(11)))
			$mess[] = array('name'=>'error', 'value'=>$this->getMess('denied'));
		elseif(count($_POST) and $_POST['sbmt']){
			include_once($_CFG['_PATH']['core'].'kernel.tools.php');
			_reinstall($this);
			$mess[] = array('name'=>'ok', 'value'=>$this->getMess('_reinstall_ok'));
		}else{
			$this->form['_*features*_'] = array('name'=>'reinstal','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
			$this->form['_info'] = array(
				'type'=>'info',
				'caption'=>$this->_CFG['_MESS']['_reinstall_info']);
			$this->form['sbmt'] = array(
				'type'=>'submit',
				'value'=>$this->getMess('_submit'));
		}
		self::kFields2FormFields($this->form);
		return Array('form'=>$this->form, 'messages'=>$mess);
	}


	public function confirmConfigmodul(){
		$this->form = array();
		$arr = array('mess'=>'','vars'=>'');
		if(!_prmModul($this->_cl,array(13)))
			$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('denied'));
		elseif(!count($this->config_form)){
			$this->form['_info'] = array(
				'type'=>'info',
				'caption'=>$this->_CFG['_MESS']['_configno']);
		}else{
			include_once($_CFG['_PATH']['core'].'kernel.tools.php');
			if(count($_POST)) {
				$arr = self::fFormCheck($_POST,$arr['vars'],$this->config_form);
				$this->config = array();
				foreach($this->config_form as $k=>$r){
					if(isset($arr['vars'][$k])){
						$this->config_form[$k]['value'] = $arr['vars'][$k];
						$this->config[$k] = $arr['vars'][$k];
					}
				}
				if(!count($arr['mess'])){
					$arr['mess'][] = array('name'=>'ok', 'value'=>$this->getMess('update'));
					_save_config($this); 
				}
			}
			_xmlFormConf($this);
		}
		self::kFields2FormFields($this->form);
		return Array('form'=>$this->form, 'messages'=>$arr['mess']);
	}
	public function statisticModule($oid) 
	{
		include_once($_CFG['_PATH']['core'].'kernel.tools.php');
		return _statisticModule($this,$oid);
	}

	public function confirmCheckmodul()
	{
		$this->form = $mess = array();
		if(!_prmModul($this->_cl,array(14)))
			$mess[] = array('name'=>'error', 'value'=>$this->getMess('denied'));
		elseif(count($_POST) and $_POST['sbmt']){
			if(!$this->_checkmodstruct()) {
				if(count($this->childs)) {
					foreach($this->childs as $k=>&$r)
						$r->_checkmodstruct();
				}
				$mess[] = array('name'=>'ok', 'value'=>$this->getMess('_recheck_ok').'  <a href="" onclick="window.location.reload();return false;">Обновите страницу.</a>');
				if(count($this->attaches)) {
					include_once($_CFG['_PATH']['core'].'kernel.tools.php');
					if(!_reattaches($this))
						$mess[] = array('name'=>'ok', 'value'=>$this->getMess('_file_ok'));
					else
						$mess[] = array('name'=>'error', 'value'=>$this->getMess('_file_err'));
				}
			}
			else
				$mess[] = array('name'=>'error', 'value'=>$this->getMess('_recheck_err'));
		}else{
			$this->form['_*features*_'] = array('name'=>'checkmodul','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
			$this->form['_info'] = array(
				'type'=>'info',
				'caption'=>$this->getMess('_recheck'));
			$this->form['sbmt'] = array(
				'type'=>'submit',
				'value'=>$this->getMess('_submit'));
		}

		return Array('form'=>$this->form, 'messages'=>$mess);
	}
/*
	public function confirmReindex(){
		$this->form = $mess = array();
		if(!_prmModul($this->_cl,array(12)))
			$mess[] = array('name'=>'error', 'value'=>$this->getMess('denied'));
		elseif(count($_POST) and $_POST['sbmt']){
			if(!$this->_reindex())
				$mess[] = array('name'=>'error', 'value'=>$this->getMess('_reindex_ok'));
			else
				$mess[] = array('name'=>'error', 'value'=>$this->getMess('_reindex_err'));
		}else{
			$this->form['_*features*_'] = array('name'=>'reindex','action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
			$this->form['_info'] = array(
				'type'=>'info',
				'caption'=>$this->getMess('_reindex_info'));
			$this->form['sbmt'] = array(
				'type'=>'submit',
				'value'=>$this->getMess('_submit'));
		}
		self::kFields2FormFields($this->form);
		return Array('form'=>$this->form, 'messages'=>$mess);
	}

	private function _reindex()
	{
		return 0;
	}
*/

	public function _save_item($data){
		foreach($this->fields_form as $k=>$r){
			if(isset($data[$k]) and isset($this->memos[$k]))
				$this->mmo_data[$k]= $data[$k];
			elseif(isset($data[$k]) and isset($this->attaches[$k]))
				$this->att_data[$k]= $data[$k];
			elseif(isset($data[$k]) and isset($this->fields[$k])) {
				$this->fld_data[$k]= $data[$k];
			}
		}
		return $this->_update();
	}
	
	public function _add_item($data){
		foreach($this->fields_form as $k=>$r){
			if(isset($data[$k]) and isset($this->memos[$k]))
				$this->mmo_data[$k]= $data[$k];
			elseif(isset($data[$k]) and isset($this->attaches[$k]))
				$this->att_data[$k]= $data[$k];
			elseif(isset($data[$k]) and isset($this->fields[$k])) {
				$this->fld_data[$k]= $data[$k];
			}
		}
		if($this->mf_createrid and !$this->fld_data['creater_id']) $this->fld_data['creater_id']= $_SESSION['user']['id'];
		return $this->_add();
	}

	//update modul item
	public function _UpdItemModul($param){
		return include($_CFG['_PATH']['core'].'kernel.UpdItemModul.php');
	}


	public function kPreFields(&$data,&$param) {
		foreach($this->fields_form as $k=>&$r) {
			if($r['readonly'] and $this->id) // если поле "только чтение" и редактируется , то значение берем из БД,
				$data[$k] = $this->data[$this->id][$k];
			
			if(isset($r['mask']['eval']))
				eval('$data[$k]='.$r['mask']['eval'].';');
			elseif(isset($r['mask']['evala']) and !$this->id)
				eval('$data[$k]='.$r['mask']['evala'].';');
			elseif(isset($r['mask']['evalu']) and $this->id)
				eval('$data[$k]='.$r['mask']['evalu'].';');
			elseif($r['mask']['fview']==2 or (isset($r['mask']['usercheck']) and !_prmUserCheck($r['mask']['usercheck']))) {
				$r['mask']['fview']=2;
				unset($data[$k]);
				continue;
			}
		
			if(isset($this->attaches[$k])) $r = $r+$this->attaches[$k];
			if(isset($this->memos[$k])) $r = $r+$this->memos[$k];

			//на всякий
			if(!isset($r['mask']['width']) and isset($this->fields[$k]['width']))
				$r['mask']['width']= $this->fields[$k]['width'];
			if(!isset($r['default']) and isset($this->fields[$k]['default']))
				$r['default']= $this->fields[$k]['default'];

			if($k==$this->owner_name and !isset($data[$k])) {
				if(!isset($this->owner->id) and $this->owner->mf_use_charid) $this->owner->id='';
				elseif(!isset($this->owner->id)) $this->owner->id=0;
				$r['value']= $this->owner->id;
			}
			elseif($k=='parent_id' and !isset($data[$k])) {
				if(isset($this->parent_id) and $this->parent_id) $r['value']= $this->parent_id;
				elseif(!isset($this->parent_id) and $this->mf_use_charid)
					$this->parent_id='';
				elseif(!isset($this->parent_id))
					$this->parent_id=0;
			}
			elseif($r['type']=='ckedit'){
				if(isset($this->memos[$k]) and !count($_POST) and file_exists($data[$k]))
					$r['value'] = file_get_contents($data[$k]);
				elseif(isset($data[$k]))
					$r['value']=$data[$k];
				else
					$r['value'] = '';
			}
			elseif($r['multiple']==1 and $r['type']=='list') {
				if(!is_array($data[$k])){
					if(_strlen($data[$k])>2)
						$data[$k] = _substr(_substr($data[$k],0,-1),1);
					$r['value']= explode('|',$data[$k]);
				}else
					$r['value']= $data[$k];
			}
			elseif(isset($data[$k]) and $data[$k])
				$r['value'] = $data[$k];
			
			if(isset($this->data[$this->id]['_ext_'.$k]))
				$r['ext']= $this->data[$this->id]['_ext_'.$k];

			//end foreach
		}

		if(!isset($_SESSION['user']['id']) or isset($param['captchaOn']))
			$this->fields_form['captcha'] = array(
				'type'=>'captcha',
				'caption'=>$this->getMess('_captcha'),
				'captcha'=>$this->getCaptcha(),
				'src'=>$this->_CFG['_HREF']['captcha'].'?'.rand(0,9999),
				'mask'=>array('min'=>1));

		$mess=array();
		if(isset($this->mess_form) and count($this->mess_form))
			$mess = $this->mess_form;
		return $mess;
	}


	/**************************CLIENT---FUNCTION*************************/

	public function kFields2Form(&$param)
	{
		/*
		$this->form['уник название'] = array(
	обяз*	'type'=>'ТИП(submit,info,hidden,checkbox,list,int,text,textarea)',
	обяз*	'value'=>'Значение',
			'data'=>'значение масивов и пр формируемое отдельно',
			'mask'=>array(
				'name'=>'маски из $SQL->_masks',
				'key'=>'регулярное выражение для проверки знач',
				'strip'=>'(1-удаляет все теги,2- не удаляет теги, Иначе по умол - удаляет толко неразрешенные теги',
				'max'=>100,
				'min'=>2//0 -не обязательное поле,)
			);
		*/
		if(!is_array($this->fields_form) or !count($this->fields_form)) return false;
		$this->form = array();
		$this->form['_*features*_'] = array('type'=>'info', 'name'=>$this->_cl, 'method'=>'post', 'id'=>$this->id,'action'=>str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
		$this->form['_info'] = array('type'=>'info','css'=>'caption');
		if($this->id)
			$this->form['_info']['caption'] = $this->getMess('update_name',array($this->caption));
		else
			$this->form['_info']['caption'] = $this->getMess('add_name',array($this->caption));
		
		$this->kFields2FormFields($this->fields_form);
 
		if(!$this->id or $this->_prmModulEdit($this->data[$this->id],$param))
			$this->form['sbmt'] = array(
				'type'=>'submit',
				'value'=>$this->getMess('_submit'));

		return true;
	}

	public function kFields2FormFields(&$fields) {
		return include($_CFG['_PATH']['core'].'kernel.kFields2FormFields.php');
	}


	public function fFormCheck(&$data,&$param,&$FORMS) {
		include_once($_CFG['_PATH']['core'].'kernel.addup.php');
		return _fFormCheck($this,$data,$param,$FORMS);
	}

	function kData2xml($DATA,$f='') {
		$XML = '';
		if($f) {
			$attr = '';
			$value = '';
			if(is_array($DATA)) {
				if(is_int(key($DATA))) {
					foreach($DATA as $k=>$r) {
						$attr = '';
						$value = '';
						if(is_array($r)) {
							foreach($r as $m=>$d){
								if(is_array($d))
									$value .= $this->kData2xml($d,$m);
								elseif($m=='value')
									$value .= $d;
								elseif($m=='name')
									$value .= '<name><![CDATA['.$d.']]></name>';
								else
									$attr .= ' '.$m.'="'.$d.'"';
							}
						}
						else
							$value = $r;
						$XML .= '<'.$f.$attr.'>'.$value.'</'.$f.">\n";
					}
					//$XML = '<'.$f.$attr.'>'.$value.'</'.$f.'>';
				}
				else {
					foreach($DATA as $k=>$r) {
						if(is_array($r)) {
							$value .= $this->kData2xml($r,$k);
						}
						elseif($k=='value')
							$value .= $r;
						elseif($k=='name')
							$value .= '<name><![CDATA['.$r.']]></name>';
						else
							$attr .= ' '.$k.'="'.$r.'"';
					}
					$XML = '<'.$f.$attr.'>'.$value.'</'.$f.'>';
				}

			}
		}
		return $XML;
	}

	public function _getlist(&$listname, $value=0) /*LIST SELECTOR*/
	{
		$data = array();
		$templistname = $listname;
		if(is_array($listname))
			$templistname = implode(',',$listname);


		if(isset($this->_enum[$templistname])) {
			$data = &$this->_enum[$templistname];
		}
		elseif(isset($this->_CFG['enum'][$templistname])) {
			$data = &$this->_CFG['enum'][$templistname];
		}
		elseif($templistname == 'child.class') {
			$dir = array();
			if(file_exists($this->_CFG['_PATH']['ext'].$this->_cl.'.class'))
				$dir[''] = $this->_CFG['_PATH']['ext'].$this->_cl.'.class';
			if(file_exists($this->_CFG['_PATH']['extcore'].$this->_cl.'.class'))
				$dir['Ядро - '] = $this->_CFG['_PATH']['extcore'].$this->_cl.'.class';
			$data = array(''=>' --- ');
			foreach($dir as $k=>$r) {
				$odir = dir($r);
				while (false !== ($entry = $odir->read())) {
					if (substr($entry,-11)=='.childs.php') {
						$entry = substr($entry,0,-11);
						$data[$entry] = $k.$entry;
					}
				}
				$odir->close();
			}
		}
		elseif($templistname == 'list') {
			$data = $this->_dump();
		}
		elseif ($templistname == 'ownerlist') {
			$data = $this->owner->_dump();
		}
		elseif ($templistname == 'select') {
			$this->_select();
			$data = &$this->data;
		}
		elseif ($templistname == 'parentlist' and $this->mf_istree) {

			$data = array();
			if($this->mf_use_charid)
				$data[''][''] = $this->getMess('_listroot');
			else
				$data[0][0] = $this->getMess('_listroot');

			$q = 'SELECT `id`, `name`, `parent_id` FROM `'.$this->tablename.'`';
			if($this->id) $q .=' WHERE `id`!="'.$this->id.'"';
			$result = $this->SQL->execSQL($q);
			if($result->err) return $this->_message($result->err);
			while (list($id, $name,$pid) = $result->fetch_array(MYSQL_NUM)) {
				$data[$pid][$id] = $name;
			}
		}
		elseif(is_array($listname) and (isset($listname['class']) or isset($listname['tablename'])))  {
			
			if (isset($listname['include']))
				require_once($this->_CFG['_PATH']['ext'].$listname['include'].'.class.php');


			if(isset($listname['class'])) {
				$listname['tablename'] = getTableNameOfClass($listname['class']);
			}

			if(!$listname['tx.id']) 
				$listname['tx.id'] = 'tx.id';
			if(!$listname['tx.name']) 
				$listname['tx.name'] = 'tx.name';

			$clause['field'] = 'SELECT '.$listname['tx.id'].' as id,'.$listname['tx.name'].' as name';
			if($listname['is_tree'])
				$clause['field'] .= ', tx.parent_id as parent_id';
			if($listname['is_checked'])
				$clause['field'] .= ', tx.checked as checked';
			
			$clause['where'] = '';
			if(isset($listname['where']) and is_array($listname['where']))
				$listname['where'] = implode(' and ',$listname['where']);
			if($value)
				$clause['where'] = $listname['tx.id'].'="'.$value.'"';
			if($listname['where'])
				$clause['where'] .= ($clause['where']!=''?' AND ':'').$listname['where'];
			if($clause['where'])
				$clause['where'] = ' WHERE '.$clause['where'];

			if($listname['leftjoin'] or $listname['join'])
				$clause['from'] = ' FROM `'.$this->tablename.'` t1 '.($listname['leftjoin']?'LEFT':'').' JOIN `'.$listname['tablename'].'` tx ON '.$listname['leftjoin'];
			else 
				$clause['from'] = ' FROM `'.$listname['tablename'].'` tx ';
			if ($listname['ordfield'])
				$clause['where'] .= ' ORDER BY '.$listname['ordfield'];
			
			$result = $this->SQL->execSQL($clause['field'].$clause['from'].$clause['where']);
				if(!$result->err) {
					if($value) {
						if ($row = $result->fetch_array())
							$data[$row['id']] = $row['name'];
					}
					elseif($listname['is_tree']) {
						if($MODUL->mf_use_charid)//is_int($row['parent_id'])
							$data[''][''] = $this->getMess('_listroot');
						else 
							$data[0][0] = $this->getMess('_listroot');
						while ($row = $result->fetch_array()){
							$data[$row['parent_id']][$row['id']] = $row;
						}
					}
					else{
						if($MODUL->mf_use_charid)//is_int($row['id'])
							$data[''] = ' --- ';
						else 
							$data[0] = ' --- ';
						while ($row = $result->fetch_array())
								$data[$row['id']] = $row['name'];
					}
				}
				else 
					return $this->_message($result->err);
		}
		elseif(!is_array($listname))
			return $this->_message('List data `'.$listname.'` not found');
		else
			return $this->_message('List '.current($listname).' not found');

		if (!isset($this->_CFG['enum'][$templistname]))
			$this->_CFG['enum'][$templistname]=$data;
		return $data;
	}

////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
/*
$param['xsl'] - шаблонизатор
$this->_cl - name текущего класса без _class
$this->_clp - построенный путь
*/
////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
/**
$Ajax=0 - не скриптовая
*/
	public function super_inc($param=array(),$ftype='') {
		global $HTML;
		$rep =array('\'','"','\\','/');
		$cl = $this->_cl;
		$flag=1;
		$xml= $messages = array();

		if($this->owner and $this->owner->id)
			$this->_clp =$this->owner->_clp.$this->owner->_cl.'_id='.$this->owner->id.'&amp;'.$this->owner->_cl.'_ch='.$this->_cl.'&amp;';
		else $this->_clp = '';
		if($this->_clp=='')
			$this->_clp = $param['fhref'];
		if($this->_pn>1)
			$this->_clp .= $cl.'_pn='.$this->_pn.'&amp;';
		
		if(!is_array($_GET[$cl.'_id'])) {
			if(!$this->mf_use_charid)
				$this->id=(int)$_GET[$cl.'_id'];
			else
				$this->id=str_replace($rep,'',$_GET[$cl.'_id']);
		}

		if($this->id and $this->mf_istree) {
			$agr = ', '.$this->_listnameSQL.' as name';
			$this->tree_data = $first_data = $path2=array();
			$parent_id = $this->id;
			$this->listfields = array('id,parent_id'.$agr);
			while ($parent_id) {
				$this->clause = 'WHERE id="'.$parent_id.'"';
				$this->_list('id');
				if(!count($first_data)) $first_data = $this->data;
				$this->tree_data += $this->data;
				$path2['/'.$this->_CFG['PATH']['wepname'].'/index.php?'.$this->_clp.$this->_cl.'_id='.$this->data[$parent_id]['id'].'&amp;'] =$this->caption.': '.$this->data[$parent_id][$this->_listname];
				if($param['first_id'] and $parent_id==$param['first_id'])
					break;
				$parent_id = $this->data[$parent_id]['parent_id'];
			}
			$this->data = $first_data;

			if($param['first_id'] and !$parent_id) $this->id='';
			$path2=array_reverse($path2);			
		}
		elseif($this->id) {
			$this->_select();
		}
		
		if($this->owner->id) {
			if($this->owner->mf_istree) array_pop($HTML->path);
			$HTML->path['/'.$this->_CFG['PATH']['wepname'].'/index.php?'.$this->_clp] =$this->caption.':'.$this->owner->data[$this->owner->id][$this->owner->_listname];
		}
		else
			$HTML->path['/'.$this->_CFG['PATH']['wepname'].'/index.php?'.$this->_clp] =$this->caption;
		if(count($path2)) 
			$HTML->path = array_merge($HTML->path,$path2);

		if($this->id and isset($_GET[$cl.'_ch']) and isset($this->childs[$_GET[$cl.'_ch']])) {
			if(count($this->data)) {
				//$HTML->path['/'.$this->_CFG['PATH']['wepname'].'/index.php?'.$this->_clp] =$this->caption.': '.$this->data[$this->id][$this->_listname];
				list($xml,$flag) = $this->childs[$_GET[$cl.'_ch']]->super_inc($param,$ftype);
				//	$tmp = $this->childs[$_GET[$cl.'_ch']]->_clp;
				//if(!isset($HTML->path[$this->_CFG['PATH']['wepname'].'/index.php?'.$tmp]))
				//	$HTML->path['/'.$this->_CFG['PATH']['wepname'].'/index.php?'.$tmp] =$this->childs[$_GET[$cl.'_ch']]->caption;
			}
		}else {
			if($ftype=='add') {
				$this->parent_id = $this->id;
				unset($this->id);
				list($xml['formcreat'],$flag) = $this->_UpdItemModul($param);
				if($flag==1)
					$this->id=$this->parent_id;
				//else
					$HTML->path['/'.$this->_CFG['PATH']['wepname'].'/index.php?'.$this->_clp.'_type=add'.(($this->parent_id)?'&amp;'.$this->_cl.'_id='.$this->parent_id:'')] ='Добавить';
			}
			elseif($ftype=='edit' && $this->id) {
				if($this->mf_istree) 
					array_pop($HTML->path);
				$HTML->path['/'.$this->_CFG['PATH']['wepname'].'/index.php?'.$this->_clp.$this->_cl.'_id='.$this->id.'&amp;_type=edit'] ='Редактировать:<b>'.preg_replace($this->_CFG['_repl']['name'],'',$this->data[$this->id][$this->_listname]).'</b>';
				list($xml['formcreat'],$flag) = $this->_UpdItemModul($param);
				if($flag==1){
					$this->id=$this->parent_id;
					if($this->id) $this->_clp .= $this->_cl.'_id='.$this->id;
				}
			}
			elseif($ftype=='act' && $this->id){
				if($this->mf_istree) 
					array_pop($HTML->path);
				list($messages,$flag) = $this->_Act(1,$param);
				if($this->mf_istree)
					$this->id=$this->data[$this->id]['parent_id'];
				else
					unset($this->id);
			}
			elseif($ftype=='dis' && $this->id) {
				if($this->mf_istree) 
					array_pop($HTML->path);
				list($messages,$flag) = $this->_Act(0,$param);
				if($this->mf_istree)
					$this->id=$this->tree_data[$this->id]['parent_id'];
				else
					unset($this->id);
			}
			elseif($ftype=='del' && $this->id) {
				if($this->mf_istree) 
					array_pop($HTML->path);
				list($messages,$flag) = $this->_Del($param);
				if($this->mf_istree)
					$this->id=$this->tree_data[$this->id]['parent_id'];
				else
					unset($this->id);
			} else {
				$flag=3;
				$xml['superlist'] = $this->_displayXML($param);
			}

		}
		if(!$xml['superlist']['messages'])
			$xml['superlist']['messages'] = array();
		if(count($messages))
			$xml['superlist']['messages'] += $messages;

		return array($xml,$flag);

	}

	public function _displayXML(&$param) {
		return include($_CFG['_PATH']['core'].'kernel.displayXML.php');
	}

	public function filtrForm () {
		$_FILTR = $_SESSION['filter'][$this->_cl];
		$this->form = array();
		foreach($this->fields_form as $k=>$r) {
			if($r['mask']['filter']==1) {
				$this->form['f_'.$k] = $r;
				if(isset($_FILTR[$k])) {
					if(isset($_FILTR[$k.'_2'])) 
						$this->form['f_'.$k]['value_2'] = $_FILTR[$k.'_2'];
					$this->form['f_'.$k]['value'] = $_FILTR[$k];
				}
				if($r['type']=='ajaxlist') {
					if(!$this->form['f_'.$k]['label'])
						$this->form['f_'.$k]['label'] = 'Введите текст';
					$this->form['f_'.$k]['labelstyle'] = ($_FILTR[$k]?'display: none;':'');
					$this->form['f_'.$k]['csscheck'] = ($_FILTR[$k]?'accept':'reject');
				}
				elseif($r['type']!='radio' and $r['type']!='checkbox' and $r['type']!='list' and $r['type']!='int' and $r['type']!='file' and $r['type']!='ajaxlist')
					$this->form['f_'.$k]['type'] = 'text';
				if(isset($_FILTR['exc_'.$k]))
					$this->form['f_'.$k]['exc'] = 1;
			}
		}
		//фильтр	
		if(count($this->form)) 
		{
			$this->form['_*features*_'] = array('name'=>'filter','action'=>$this->_CFG['_HREF']['JS'].'?_modul='.$this->_cl.'&amp;_type=filter', 'method'=>'post');
			$this->form['f_fltr_sbmt'] = array(
				'type'=>'submit',
				'value'=>'Отфильтровать');			
			
			$this->form['f_clear_sbmt'] = array(
				'type'=>'submit',
				'value'=>'Очистить');
			$this->kFields2FormFields($this->form);
			return $this->form;
		}
		return false;
	}
/* вспомогательные функции*/
	/**
		* ФИЛЬТР в запросе
	**/
	function _filter_clause () {
		$cl = array();
		$flag_filter = 0;
		$_FILTR = $_SESSION['filter'][$this->_cl];
		foreach($this->fields_form as $k=>$r){
			if($r['mask']['filter']==1) {
				if(isset($_FILTR[$k])) {
					$tempex = 0;
					if(isset($_FILTR['exc_'.$k]))
						$tempex = 1;
					if(is_array($_FILTR[$k]))
						$cl[$k] = 't1.'.$k.' '.($tempex?'NOT':'').'IN ("'.implode('","',$_FILTR[$k]).'")';
					else
					{
						if(strpos($this->fields_form[$k]['type'], 'int') !== false) {
							if($tempex)
								$cl[$k] = '(t1.'.$k.'<'.$_FILTR[$k].' or t1.'.$k.'>'.$_FILTR[$k.'_2'].')';
							else
								$cl[$k] = '(t1.'.$k.'>'.$_FILTR[$k].' and t1.'.$k.'<'.$_FILTR[$k.'_2'].')';
						}
						elseif($_FILTR[$k]=='!0')
							$cl[$k] = 't1.'.$k.'!=""';
						elseif($_FILTR[$k]=='!1')
							$cl[$k] = 't1.'.$k.'=""';
						else
							$cl[$k] = 't1.'.$k.' '.($tempex?'NOT ':'').'LIKE "'.$_FILTR[$k].'"';
					}
				}
				$flag_filter = 1;
			}
		}
		return array($cl,$flag_filter);
	}

	function _moder_clause ($cl=array(),$param) {
		if($this->_prmModulShow($this->_cl)) $cl['t1.creater_id'] = 't1.creater_id="'.$_SESSION['user']['id'].'"';
		if($this->owner and $this->owner->id) $cl['t1.'.$this->owner_name] = 't1.'.$this->owner_name.'="'.$this->owner->id.'"';
		if($this->mf_istree){
			if($this->id) $cl['t1.parent_id'] = 't1.parent_id="'.$this->id.'"';
			elseif($param['first_id']) $cl['t1.parent_id'] = 't1.id="'.$param['first_id'].'"';
			elseif($param['first_pid']) $cl['t1.parent_id'] = 't1.parent_id="'.$param['first_id'].'"';
			elseif($this->mf_use_charid) $cl['t1.parent_id'] = 't1.parent_id=""';
			else  $cl['t1.parent_id'] = 't1.parent_id=0';
			if($this->owner and $this->owner->id and ($this->id or $param['first_pid']))
				unset($cl['t1.'.$this->owner_name]);
		}
		//if(isset($this->fields['region_id']) and isset($_SESSION['city']))///////////////**********************
		//	$cl['t1.region_id'] ='t1.region_id='.$_SESSION['city'];

		if($_GET['_type']=='deleted' and $this->itemform_items['active']['listname']=='active')
			$cl['t1.active'] ='t1.active=4';
		elseif($this->itemform_items['active']['listname']=='active')
			$cl['t1.active'] ='t1.active!=4';
		return $cl;
	}

	private function _tr_attribute(&$row,&$param) {
		$xml=array();
		if($this->_prmModulEdit($row,$param)) $xml['edit'] = true;
		else $xml['edit'] = false;
		if($this->_prmModulDel(array($row),$param)) $xml['del'] = true;
		else $xml['del'] = false;
		if($this->_prmModulAct(array($row),$param)) $xml['act'] = true;
		else $xml['act'] = false;
		return $xml;
	}

/* Активация*/

	public function _Act($act,&$param) {	
		$flag=1;$xml = array();
		if($param['mess']) $xml = $param['mess'];
		$param['act']=$act;
		$this->_select();
		if($this->_prmModulAct($this->data,$param))
		{
			$this->fld_data = array();
			$act =(int)$act;
			if($this->fields['active']['type']=='bool')
				$this->fld_data['active'] = $act;
			else {
				if(_prmModul($this->_cl,array(7)))
				{
					if ($act == 0)
						$this->fld_data['active'] = 6;
					else
						$this->fld_data['active'] = 1;
				}
				elseif($act==1)
					$this->fld_data['active'] = 5;
				else
					$this->fld_data['active'] = 2;
			}

			if (!$this->_update())
			{
				if($this->fld_data['active']==5)
					$xml[] = array('value'=>$this->getMess('act5'),'name'=>'ok');
				if($this->fld_data['active']==6)
					$xml[] = array('value'=>$this->getMess('act6'),'name'=>'ok');
				elseif($act)
					$xml[] = array('value'=>$this->getMess('act1'),'name'=>'ok');
				else
					$xml[] = array('value'=>$this->getMess('act0'),'name'=>'ok');
				$flag=0;
			}
			else
				$xml[] = array('value'=>$this->getMess('update_err'),'name'=>'error');
		}
		else
			$xml[] = array('value'=>$this->getMess('denied'),'name'=>'error');
		return array($xml,$flag);
	}

////////////// -------DELETE---------------

	public function _Del($param) {
		$flag=1;$xml = array();
		if($param['mess']) $xml = $param['mess'];
		$this->_select();
		if(count($this->data) and $this->_prmModulDel($this->data,$param))
		{
			if(isset($this->fields['active']) and $this->fields['active']['type']!='bool'){
				$this->fld_data['active'] = 4;
				if(!$this->_update()){
					$xml[] = array('value'=>$this->getMess('deleted'),'name'=>'ok');
					$flag=0;
				}else
					$xml[] = array('value'=>$this->getMess('del_err'),'name'=>'error');
			}else{
				if(!$this->_delete()){
					$xml[] = array('value'=>$this->getMess('deleted'),'name'=>'ok');
					$flag=0;
				}else
					$xml[] = array('value'=>$this->getMess('del_err'),'name'=>'error');
			}
		}
		else
			$xml[] = array('value'=>$this->getMess('denied'),'name'=>'error');
		return array($xml,$flag);
	}

/* TREE CREATOR*/
	public function _forlist(&$data,$id,$select='') { 
		//$select - array(значение=>1)
		$s = array();
		if (count($data[$id]) and is_array($data[$id]))
			foreach ($data[$id] as $key => $value)
			{
				if($select!='' and is_array($select))
				{
					if(isset($select[$key]))
						$sel = 1;
					else
						$sel = 0;
				}
				elseif($select!='' and $select==$key)
					$sel = 1;
				else
					$sel = 0;
				$s[$key] = array('id'=>$key,'sel'=>$sel);
				if(is_array($value)){
					foreach($value as $k=>$r)
						if($k!='name' and $k!='id')
							$s[$key][$k] = $r;
					$s[$key]['name'] = $value['name'];//_substr($value['name'],0,60).(_strlen($value['name'])>60?'...':'')
				}else
					$s[$key]['name'] = $value;
				if ($key!=$id and count($data[$key]) and is_array($data[$key]))
					$s[$key]['item'] = $this->_forlist($data,$key, $select);
			}

		return $s;
	}

	public function path2xsl($path) {
		$xml = '<path>';
		foreach($path as $key=>$value)
		{
			if(is_int($key))
				$href = '<href></href>';
			else
				$href = '<href>'.$key.'</href>';

			$xml.= '<item>'.$href.'<name><![CDATA['.$value.']]></name></item>';
		}
		$xml .= '</path>';
		return $xml;
	}

	public function fPageNav($countfield, $thisPage='', $flag=0) {
		//$countfield - бщее число элем-ов
		//$thisPage - по умол тек путь к странице
		//$this->messages_on_page - число эл-ов на странице
		//$this->_pn - № текущей страницы
		$DATA = array('cnt'=>$countfield,'cntpage'=>ceil($countfield/$this->messages_on_page),'modul'=>$this->_cl);
		$numlist=$this->numlist;
		if(($this->messages_on_page*($this->_pn-1))>$countfield) {
			$this->_pn=$DATA['cntpage'];
		}

		foreach($this->_CFG['enum']['_MOP'] as $k=>$r)
			$DATA['mop'][$k] = array('value'=>$r,'sel'=>0);
		$DATA['mop'][$this->messages_on_page]['sel'] = 1;

		if (!$countfield || $countfield<=$this->messages_on_page || !$this->messages_on_page || $this->_pn>$DATA['cntpage'] || $this->_pn<1)
			return $DATA;
		else
		{
			if($thisPage=='') $thisPage = $_SERVER['REQUEST_URI'];
			if(strstr($thisPage,'&')) {
				$thisPage = str_replace('&amp;', '&', $thisPage);
				$thisPage = str_replace('&', '&amp;', $thisPage);
			}

			if($this->reversePageN) {// обратная нумирация
				$temp_pn = $this->_pn;
				$this->_pn = $DATA['cntpage']-$this->_pn+1;
				if(strpos($thisPage,'.html')) {
					$pregreplPage = '/(_p)[0-9]*/';
					if(!preg_match($pregreplPage,$thisPage)) {
						$thisPage = str_replace('.html','_p'.$this->_pn.'.html',$thisPage);
					}
				}else {
					$pregreplPage = '/('.$this->_cl.'_pn=)[0-9]*/';
					if(!preg_match($pregreplPage,$thisPage)) {
						if(_substr($thisPage,-5)!='&amp;')
							$thisPage .= '&amp;';
						$thisPage .= $this->_cl.'_pn='.$this->_pn;
					}
				}
				for ($i=$DATA['cntpage'];$i>0;$i--) {
					if($i==$this->_pn)
						$DATA['link'][] = array('value'=>$i,'href'=>'select_page');
					else
						$DATA['link'][] = array('value'=>$i,'href'=>preg_replace($pregreplPage,($i==$DATA['cntpage']?'':"\${1}".$i),$thisPage));
				}
			} else {
			
				if(strpos($thisPage,'.html')) {
					$replPage = '_p'.$this->_pn;
					$pregreplPage = '/_p'.$this->_pn.'/';
					$inPage = '_p';
				}
				else {
					$replPage = $this->_cl.'_pn='.$this->_pn;
					$pregreplPage = '/'.$this->_cl.'_pn=[0-9]*(&amp;)*/';
					$inPage = $this->_cl.'_pn=';
				}
				if(strpos($thisPage,$replPage)=== false) {
					if(strpos($thisPage,'.html')){
						$pageSuf = _substr($thisPage, strpos($thisPage,'.html')+5);
						$thisPage = _substr($thisPage, 0, strpos($thisPage,'.html')).'_p1.html'.$pageSuf;
					}else{
						if(_substr($thisPage,-5)=='&amp;')
							$thisPage .= $replPage;
						elseif(strpos($thisPage,'?')=== false)
							$thisPage .= '?'.$replPage;
						else
							$thisPage .='&amp;'.$replPage;
					}
				}
				if(($this->_pn-$numlist) > 1){
					$DATA['link'][] = array('value'=>1,'href'=>preg_replace($pregreplPage,'',$thisPage));
					$DATA['link'][] = array('value'=>'...','href'=>'');
					$j = $this->_pn - $numlist;
				}
				else{
					$j = 1;
				}
				for ($i=$j;$i<=$this->_pn+$numlist;$i++)
					if($i<=($DATA['cntpage']))
						if($i==$this->_pn)
							$DATA['link'][] = array('value'=>$i,'href'=>'select_page');
						else
							$DATA['link'][] = array('value'=>$i,'href'=>preg_replace($pregreplPage,($i==1?'':$inPage.$i),$thisPage));
				if($this->_pn+$numlist<$DATA['cntpage']){
					$DATA['link'][] = array('value'=>'...','href'=>'');
					$DATA['link'][] = array('value'=>$DATA['cntpage'],'href'=>str_replace($replPage,$inPage.$DATA['cntpage'],$thisPage));
				}
			}
		//////////////////

		}
		if(!$flag && $this->_CFG['site']['msp']=='paginator') {
			global $_tpl;
			$_tpl['script'] .='<script type="text/javascript" src="'.$this->_CFG['_HREF']['_script'].'jquery.paginator.js"></script>'."\n";
			$_tpl['styles'] .='<link rel="stylesheet" href="'.$this->_CFG['_HREF']['_style'].'paginator.css" type="text/css"/>'."\n";
			$_tpl['onload'] .='pagenum('.$DATA['cntpage'].','.($this->reversePageN?'true':'false').');';
		}
		elseif($flag && $this->_CFG['wep']['msp']=='paginator') {
			global $_tpl;
			$_tpl['script'] .='<script type="text/javascript" src="'.$this->_CFG['_HREF']['_script'].'jquery.paginator.js"></script>'."\n";
			$_tpl['styles'] .='<link rel="stylesheet" href="'.$this->_CFG['_HREF']['_style'].'paginator.css" type="text/css"/>'."\n";
			$_tpl['onload'] .='pagenum_default('.$DATA['cntpage'].','.$this->_pn.',\''.$this->_cl.'\','.($this->reversePageN?'true':'false').');';
		}
		//if($this->reversePageN)
		//	$this->_pn = $temp_pn;
		return $DATA;
	}

	public function insertInArray($data,$afterkey,$insert_data) {
		$output = array();
		if(count($data)) {
			foreach($data as $k=>$r){
				$output[$k]=$r;
				if($k==$afterkey)
					$output = array_merge($output,$insert_data);
			}
			return $output;
		}
		return $insert_data;
	}

	public function countThisCreate() {
		$cnt = 0;
		$cls = 'SELECT count(id) as cnt FROM `'.$this->tablename.'`';
		if($this->mf_createrid)
			$cls .= ' WHERE creater_id="'.$_SESSION['user']['id'].'"';
		$result = $this->SQL->execSQL($cls);
		if(!$result->err and $row = $result->fetch_array())
			$cnt = $row['cnt'];
		return $cnt;
	}

	public function getPathForAtt($key) {
		if($this->attaches[$key]['path'])
			$pathimg = $this->attaches[$key]['path'];
		else
			$pathimg = $this->_CFG['PATH']['content'].$key;
		return $pathimg;
	}

	public function getPathForMemo($key) {
		if($this->memos[$key]['path'])
			$pathimg = $this->memos[$key]['path'];
		else
			$pathimg = $this->_CFG['PATH']['content'].$key;
		return $pathimg;
	}

	function okr($x, $y)
	{
		$z = pow(10, $y);
		return  $z * round($x / $z);
	}

	function getMess($name,$wrap=array(),$obj=NULL) {
		//global $_CFG;
		if(isset($this->locallang['default'][$name]))
			$text = $this->locallang['default'][$name];
		elseif(isset($this->_CFG['_MESS'][$name]))
			$text = $this->_CFG['_MESS'][$name];
		else
			$text = 'Внимание. Нейзвестный тип `сообщения`!';
		if(count($wrap))
			foreach($wrap as $k=>$r)
				$text = str_replace('###'.($k+1).'###', $r, $text);
		return $text;
	}

	function setCaptcha() {
		global $_CFG;
		$_SESSION['captcha'] = rand(10000,99999);
		if($_CFG['wep']['sessiontype']==1) {
			$hash_key = file_get_contents($_CFG['_PATH']['HASH_KEY']);
			$hash_key = md5($hash_key);
			$crypttext = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $hash_key, $_SESSION['captcha'], MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
			_setcookie('chash',$crypttext,(time()+1800));
			_setcookie('pkey',base64_encode($_CFG['PATH']['HASH_KEY']),(time()+1800));
			
		}
	}

	function getCaptcha() {
		global $_CFG;
		if(isset($_COOKIE['chash']) and $_COOKIE['chash']) {
			$hash_key = file_get_contents($_CFG['_PATH']['HASH_KEY']);
			$hash_key = md5($hash_key);
			$data = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $hash_key, base64_decode($_COOKIE['chash']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
			return $data;
		}
		return $_SESSION['captcha'];
	}
}
//// Kernel END

	function _usabilityDate($time,$format='Y-m-d H:i') {
		global $_CFG;
		$date = getdate($time);
		$de = $_CFG['time']-$time;
		if($de<3600) {
			if($de<240) {
				if($de<60)
					$date = 'Минуту назад';
				else
					$date = ceil($de/60).' минуты назад';
			}
			else
				$date = ceil($de/60).' минут назад';
		}
		elseif($_CFG['getdate']['year']==$date['year'] and $_CFG['getdate']['yday']==$date['yday'] )
			$date = 'Сегодня '.date('H:i',$time);
		elseif($_CFG['getdate']['year']==$date['year'] and $_CFG['getdate']['yday']-$date['yday']==1)
			$date = 'Вчера '.date('H:i',$time);
		else
			$date = date($format,$time);
		return $date;
	}
?>