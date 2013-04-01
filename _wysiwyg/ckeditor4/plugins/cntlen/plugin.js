/*
* Copyright (c) 2010-2011, http://xakki.ru
* Плагин показывает число симовлов набранные в редакторе (учитываются также и теги) 
* и если  у textarea установлен атрибут maxlength аоказывает максимально допустимое знгачение.
* TODO : Планируется сделать ограничение по максимальному числу символов (запрет на добавление символа если достигнут лимит)
*/


(function()
{
	var emptyHtml = '&nbsp;';

	CKEDITOR.plugins.add( 'cntlen',
	{
		init : function( editor )
		{
			var spaceId = 'cke_cntlen_' + editor.name;
			var spaceElement;
			var maxi;
			if(editor.element.getAttribute('maxlength'))
				maxi = editor.element.getAttribute('maxlength');
			else
				maxi= '-';

			var getSpaceElement = function()
			{
				if ( !spaceElement )
					spaceElement = CKEDITOR.document.getById( spaceId );
				return spaceElement;
			};

			editor.on( 'uiSpace', function( event )
			{
				if ( event.data.space == 'bottom' )
					event.data.html += '<div id="' + spaceId + '" class="cke_path">' + emptyHtml + '</div>';
			});

			editor.on( 'key', function( event )
			{
				var valLen = this.getData().length;
				//if(valLen>maxi) return false;
				var html = '<span class="dscr">Cимволов: '+valLen+'/'+maxi+'</span>';
				getSpaceElement().setHtml( html + emptyHtml );
			});

			editor.on( 'selectionChange', function(event)
			{
				var valLen = this.getData().length;
				//if(valLen>maxi) return false;
				var html = '<span class="dscr">Cимволов: '+valLen+'/'+maxi+'</span>';
				getSpaceElement().setHtml( html + emptyHtml );
			});

		}
	});
}) ();
