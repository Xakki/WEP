WEP CMS
=============

Модульная система управления контентом

* https://github.com/Xakki/WEP -- Ветка для сторонних разработчиков
* http://wep.xakki.ru -- сайт проекта

Установка
-------

### Структура фаилов
...

### Структура базы данных
...

Настройка
------------

### Фаилы конфигурации


API
------------


WebEngineOnPHP (WEP)
Особенности:
- мультиязыковая платформа
- мультишаблонная 
- 


В БД существут табл message_ru со всеми основными сообщениями. Поле id -varchar(15) - код сообщения на латинском

-= Переменные =-
	Переменные переданные через .htaccess должный начинаться с префикса "ht_"
		ht_page - индекс страницы


-= Папки =-
	admin  -  движок сайта!
		class  - пользовательские классы, файлы с расширением .cl
		page -  обработчики страниц, файлы с расширением .pg ! WEP определяет список файлов в этом каталоге и вадает на выбор для ввода в БД!
		system - системные классы, файлы с расширением .cl / подключается один раз и от него наследуются остальные классы
		design  -  файлы для отображения хтмл
			default
				script
				style
				xsl
				template
			

--------------------------------------------------------------------------------------
ПРИМЕЧАНИЕ
 - массив $this->config доступен после parent::_create()

--------------------------------------------------------------------------------------
Константы ядра
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
$this->prm_add = true;// добавить в модуле
$this->prm_del = true;// удалять в модуле
$this->owner_unique = false; // поле owner_id не уникально
$this->showinowner = true;// показывать под родителем
$this->mf_mop = true;// выключить постраничное отображение
	$this->reversePageN = false; // обратный отчет для постраничного отображения
	$this->messages_on_page = 20;//число эл-ов на странице
	$this->numlist=20;//максим число страниц при котором отображ все номера страниц
$this->mf_statistic = false; // показывать  статистику по дате добавления
$this->cf_childs = false; // true - включить управление подмодулями в настройках модуля
$this->includeJStoWEP = false; // подключать ли скрипты для формы через настройки
$this->includeCSStoWEP = false; // подключать ли стили для формы через настройки
$this->version = 'WEP 2.0'; // версия ядра
$this->ver = '0.1.1'; // версия модуля

$this->text_ext = '.txt';// расширение для memo файлов

$this->_cl = str_replace('_class','',get_class($this)); - символическое id модуля
$this->owner_name = 'owner_id'; // название поля для родительской связи в БД
$this->tablename = $this->_CFG['sql']['dbpref'].$this->_cl; // название таблицы
$this->caption = $this->_cl; // заголовок модуля
$this->_listfields = array('name'); //select по умолч

$this->_enum =
$this->update_records =
$this->def_records =
$this->fields =
$this->fields_form =
$this->attaches =
$this->memos =
$this->childs =
$this->services =
$this->unique_fields =
$this->index_fields = array();
$this->ordfield = '';

************************* $this->fields_form
$this->form[<key>] = array(
type='info'-
type='hidden'-
type='password'- стандартный ввод пароля с подтверждением
type='password2'- ввод пароля для админа с генерацией пароля
type='password_new'- Новый тип вввода, с функцией отображения вводимых символов
type='int'- (если нет ['mask']['toint'] то приводит значение в целое)
type='text'- обычный текст
type='textarea'-
type='fckedit'-
type='checkbox'-checkbox
type='submit'-
type='captcha'-
type='attach'- файл
	"att_type" => "image"
	'candelete'=>1 - удалять?
type='list' - список
	'active'=>1 - отображать список активных эелементов
	'multiple'=1 - мульти селект
	'begin'=>начальны элемент массива с которого будет начинаться отображаться, не обязателен
	'listname'= ключ '_enum' массива, 
		'list'-тек. список модуля, _list()
		'select' - _select()
		'parentlist'-список многоуровневого списка($this->id исключ из списка),
		'ownerlist'- список родителя
	or
	'class'=>'class name',
	'include'- если надо подключить модуль, не обяз, если имя класса=class
	?'join' - если в этом дочернем узеле надо жостко связать с родителем ( не обяз)
	?'leftjoin' - не жосткая связь(не обяз), длявывода доп инфы
	?['mask']['field'] - 'tx.name,tx.id' , поля которые будут отображатся именем, обязателен если есть join или leftjoin?, но можне и без них

'type' => 'ckedit'
	'fckedit'=>array(
		'toolbar'=>'Page', {}, default=Full
		'CKFinder'=>1
		'height'=>250
		'removePlugins' => 'about,basicstyles,blockquote,button,clipboard,colorbutton,contextmenu,elementspath,enterkey,entities,filebrowser,find,flash,font,format,forms,horizontalrule,htmldataprocessor,image,indent,justify,keystrokes,link,list,maximize,newpage,pagebreak,pastefromword,pastetext,popup,preview,print,removeformat,resize,save,scayt,smiley,showblocks,sourcearea,stylescombo,table,tabletools,specialchar,tab,templates,toolbar,undo,wysiwygarea,wsc'
		'extraPlugins' => {uicolor  - цвета}
	)


?'href' - ссылка на название

'eval' - запись при добавлении и обновлении
or 'evala' - запись при добавлении
or 'evalu' - запись при обновлении
	-без ; в конце

?['mask']['fview']=>1 - не отображать столбец в списке
?['mask']['fview']=>2 - не отображать в форме
'disable'=>1 - не показывать ващ
"editable" => "0" - не редактируемый - по умолчанию редактир
?'onetd'=>'Наименование / Текст' - позволяет отображать не сколько столбцов в одном столбце, пример (/control/moder_bannnew.html)
	...."type" => "text", "caption" => "www",...'onetd'=>'Наименование / Текст');//первый onetd , задает название столбца
	...."type" => "list"... "caption" => "Фирма",'onetd'=>'none');//'onetd'=>'none' - соединяется с предыдущим столбцом
	... "caption" => "HTML код"...'onetd'=>'close');//'onetd'=>'close' - обязательно закрывать столбец
'usercheck'=>1 - проверка пользователя на соответствие группе пользователей (либо массив) для отображения формы и в списке
'mask'=>array(
	"name"=>"www" {phone,phone2,www,} - название фильтра маски из sql.class.php
	'patterns'=>'регулярное выражение для проверки знач',
	'striptags'=>'('all'-удаляет все теги,''- не удаляет теги, Иначе толко разрешенные теги',
	'replace'=>'рег выр для замены'(м.б. массивом)
	'replaceto'=> см выше, если не установленно то по умолчанию пустая строка
	'entities'=> htmlspecialchars для поля
	'checkwww'=> проверка на существ. если "name"=>"www"
	'toint' приводит type='int' в целое
	'max'=>100,
	'min'=>2  //0 -не обязательное поле
)
***********************************************

---установка занчения по умолчанию при установке и переустановке модуля
$this->def_records[] = array('id'=>$this->_CFG['wep']['login'],'name'=>'Главный','reg_date'=>time(),'owner_id'=>1);

************************************************

$this->sortfields['dateon'] = 'Дата вкл.'; //показать способы сортировки

************************************************

$this->ordfield = 'dateoff DESC';// сортировка по умолчанию

************************************************


$this->fields_form['##FILEDS##'] = array(
	'type' => 'list', [ajaxlist]
	'listname' => ... ,
		// текст (выполняется ф. _getCashedList)
			// если есть такой ключ в массиве $this->_enum
			// если есть такой ключ в массиве $this->_CFG['enum']
			// иначе выполняется _getlist($listname,$value), эту ф можно вызвать в своем модуле и выполнить свой код для списка ($value -это службная переменная) , где след константы зарезервированы
				// child.class - выводит список подмодулей 
				// style - стили
				// script - скрипты
				// list - дамп этого модуля
				// ownerlist  - дамп родителя
				// select - выводит список данного модуля 
				// parentlist - для дерева
		// масив (выполняется ф. _getCashedList для формы, я для superInc - встрайвается в запрос)
			//array(
				'class' => 'modul_name',
					либо 'tablename' =>'название табл',
				'idField'=>'поле для связи, по умолчанию "tx.id" поле в модуле',
				'nameField'=>'поле названия из связ табл, по умол "tx.name", перед названием поля связанной табл указывать "tx." поле в модуле',
				'join' => 'если дынное полу isset то будет жесткая связь с табл, по умол связь "LEFT JOIN", указываетсЯ связи табл , "tx." для связ табл, а для текущего модуля "t1."'
				'leftJoin'=>'тоже самое как и "join", но связь остается "LEFT JOIN"',
				//если есть 'join' или 'leftJoin' , то обязательно 'idThis' это поле текущего модуля с которым связывается эта, это можеьт быть тем же полем
			)
		// для формы вывод списка
			//is_tree - вывод в виде дерева , должно быть поле parent_id
			//is_checked - вывод в соответствии с полем checked , если у поля checked = 0 , то оно не доступно для выбора
			//'where' - ополнительные условия для выборки поля
	'caption' => 'Название поля', 
	'mask' => array(
		'filter'=>1 - включить фильтр для этого поля
	)
);

$this->fields_form['##FILEDS##'] = array(
	'type' => 'list', [ajaxlist]
	'listname' => ,
	'caption' => 'Название поля', 
	'mask' => array(
	)
);


#DEBUG

FB::log('Log message');
FB::info('Info message');
FB::warn('Warn message');
FB::error('Error message');