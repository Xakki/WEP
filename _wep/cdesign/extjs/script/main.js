
function load_href(hrf) {
	if(typeof hrf=='object')
		window.location.href = $(hrf).attr('href');
	else
		window.location.href = hrf;
	return false;
}

var MESS = {
	'del':'Вы действительно хотите провести операцию удаления?',
	'delprof':'Вы действительно хотите удалить свой профиль?',
};
function hrefConfirm(obj,mess)
{
	if(MESS[mess])
		mess = MESS[mess];

	if(confirm(mess)) {
		return true;
	}
	return false;
}

function invert_select(form_id)
{
	$('#'+form_id+' input[type=checkbox]').each(function() {
		this.checked = !this.checked;
	});
	return false;
}

// новое

$(document).ready(function() {
	// вешаем обработчик кликов на все ссылки
	$('a').click(wep.click_handler);
});

Ext.onReady(function() {  
//	Ext.select('a').on('click', wep.click_handler);	
	// инициализируем объект wep
	wep.init();
});


wep = {
	version: 0.1
};

Ext.apply(wep, {

	init: function()
	{
		wep.observer = new Ext.util.Observable();
	},

	// пути
	path: {
		extjs: '_design/_script/extjs/',
		cscript: '_wep/cdesign/extjs/script/'
	},

	// динамически подключенные файлы
	included_files: {},	

	// подключает css файл
	// id - идентификатор. Если будут будут подключены 2 файла с одним id, то первый удалится
	include_css: function(id, url)
	{
		if (wep.included_files[url] == undefined)
		{
			wep.included_files[url] = true;
			Ext.util.CSS.swapStyleSheet(id, url);
		}
	},
		
	// подключает js файлы
	// fileList - список подключаемых файлов. Может быть строкой или массивом
	// callback - выполнится после того, как файлы подключатся
	// scope - передаваемые в callback пар-ры
	// preserveOrder - если true, файлы будут подгружаться по очереди, по умолчанию false
	include_js: function(fileList, callback, scope, preserveOrder)
	{
		var uniqueFileList = [];

		Ext.each(fileList, function(file, index)
		{
			if (wep.included_files[file] == undefined)
			{
				wep.included_files[file] = true;
				uniqueFileList.push(file);
			}
		});

		if(!Ext.isEmpty(uniqueFileList, true))
		{
			Ext.Loader.load(uniqueFileList, callback, scope, preserveOrder);
		}
		else if (typeof callback == 'function')
		{
			var scope = scope || this; 

			callback.call(scope)
		}
	},

	// подключает 1 файл
	// при этом код, который идет после не выполнится, пока файл не подключится
	include_file: function(file)
	{
		if (wep.included_files[file] == undefined)
		{
			var matches = file.split('.');
			var code = '';
			var ext = matches[matches.length-1];
			switch (ext)
			{
				case 'js':
					code = '<script type="text/javascript" src="' + file + '"></script>';
					break;
				case 'css':
					code = '<link rel="stylesheet" type="text/css" href="' + file + '" rel="stylesheet" />';
					break;
				default:
					alert('Файлы с расширением ' + ext + ' не подключаются.');
					break;
			}
			if (code != '')
			{
				wep.included_files[file] = true;
				$('head').append(code);
			}
		}
	},

	// подключает файлы, может передаваться объект, массив или строка с адресом файла
	// при этом код, который идет после не выполнится, пока все файлы не подключится
	include: function(files)
	{
		if (Ext.isObject(files) || Ext.isArray(files))
		{
			$.each(files, function(index, file)
			{
				wep.include_file(file);
			});
			
			return true;
		}
		else if (Ext.isString(files))
		{
			wep.include_file(files);
		}
		else
		{
			alert('Передан неверный тип параметра в функцию include');
			return false;
		}
	},

	//обрабатывает клики по ссылкам
	click_handler: function(e) {
		wep.href = this.href;

		// меняем стиль для ссылок
		wep.change_menu_item_style(this);

		// парсим url
		wep.GET = {};
		if (wep.href.indexOf('?') != -1)
		{
			var get_matches = [];
			var request = wep.href.split('?',2)[1];
			if (request.indexOf('&') != -1)
			{
				 get_matches = request.split('&');			
			}		
			else
			{
				get_matches[0] = request;
			}
			
			for (var i=0; i<get_matches.length; i++) 
			{
				var params = get_matches[i].split('=');
				wep.GET[params[0]] = params[1];
			}
		}
		
		// добавляем в modulsforms дерево элементов
		if (wep.GET['_view'] == 'list')
		{
			wep.get_tree(wep.GET['_modul'], 'modulsforms');
		}
			
		return false;
	},

	// получает дерево элементов из модуля modul
	// и помещает их в элемент с id	= id_cont
	get_tree: function(modul, id_cont) 
	{
		//Очищаем элемент, в котором хотим нарисовать дерево
		Ext.get(id_cont).update('');

		// css
		wep.include_css('treegrid', wep.path['extjs'] + 'ux/treegrid/treegrid.css');
		
		// js
		var jsfiles = [
			wep.path['extjs'] + 'ux/treegrid/TreeGridSorter.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridColumnResizer.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridNodeUI.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridLoader.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridColumns.js',
			
			wep.path['extjs'] + 'ux/treegrid/TreeGrid.js',
		];

		wep.include_js(jsfiles, function()
		{
			Ext.QuickTips.init();


			Ext.Ajax.request({
				url: '_wep/index.php?_view=listcol&_modul=' + modul,
				success: function(result, textStatus) {
					data = Ext.util.JSON.decode(result.responseText);					
					var tree = new Ext.ux.tree.TreeGrid({
						title: 'Модуль ' + modul,
//						width: 500,
						width: 1000,
						height: 300,
						renderTo: Ext.getDom(id_cont),
						enableDD: true,
						columns: data,
						requestMethod: 'get',
						dataUrl: '_wep/index.php?_view=list&_modul=' + modul
					});	 
				},
				failure: function(result, textStatus) {
					Ext.Msg.alert('Ошибка', 'Произошла ошибка');
				}
			});
						
/*
			var tree = new Ext.ux.tree.TreeGrid({
				title: 'Модуль ' + modul,
//				width: 500,
				width: 1000,
				height: 300,
				renderTo: Ext.getDom(id_cont),
				enableDD: true,
				columns: data,
				dataUrl: '_wep/index.php?_view=list&_modul=' + modul
			});	 
*/			
		});
		
	},

	// меняет стиль ссылок при нажатии на них
	change_menu_item_style: function(obj)
	{
		if (wep.last_checked_link != undefined)
		{
			Ext.get(wep.last_checked_link).parent().removeClass('selected');
		}
		wep.last_checked_link = obj;
		Ext.get(obj).parent().addClass('selected');
	},

});


// пустая картинка для верстки
Ext.BLANK_IMAGE_URL = wep.path.extjs + 'resources/images/default/s.gif';

