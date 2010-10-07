pname = '';

function set_mspirr(id, v, nm){
	if(id!=''){
		if(v) set_spirr(id, 1, nm);
		else  set_spirr(id, 0, nm);
	}else {// просто находим все input с name=spirr и вытаскиваем ID
		inp = document.getElementsByName('spirr');
		for (i=0; i<inp.length; i++)
			id +='d'+inp[i].id.substr(5);
		set_spirr(id.substr(1), v, nm);
	}
	return false;
}

function set_spirr(id, v, nm)
{
	document.getElementById('all_decl_fl1').value = v;
	document.getElementById('all_decl_fl2').value = id;
	
	createRequest();
	var url = "/control/set_spirr.php?id=" + id + "&v=" + v + "&nm=" + nm;
	pname = nm;
	request.open("GET", url, true);
	request.onreadystatechange = set_spirr2;
	request.send(null);
	return false;
}

// создатель этой ф-ции - Зоя. По всем вопросам обращайтесь ко мне.
function set_spirr2()
{
	var res = 0;
	var act = document.getElementById("all_decl_fl1").value;	// идентификатор производимого действия, 0 - если снимаем галочку, 1 - если ставим галочку
	var all1 = document.getElementById("all_decl_fl2").value;	// ИД всех объявлений на странице, разделенных символом d, т.е. например: 1d45d7d29d86d34
	var er2 = new Array();	// в этот массив буду записывать ИД объявлений, если произошла ошибка и действие над этим объявлением не было выполнено, т.е. если мы хотели отметить галочкой это объявление, а галочка не поставилась, или наоболрот хотели снять - и не снялась
	
	if (request.readyState == 4)
	{
		if (request.status == 200)
		{
			var response = request.responseText;	// получаем ответ от сценария в виде: yes1d35d46no7d10d11 После слова yes идут номера объявлений, разделенные символом d, операция над которым была успешно выполнена, т.е. хотели отметить галочкой и успешно отметили. После слова no идут так же разделенные буквой d номера объявлений, но ошибочные, если действие не было произведено

			if (response != '')
			{
				var y1 = response.substring(3, response.indexOf('no'));	// в этой строке будут номера выполненных объявлений через d
				var n1 = response.substring(response.indexOf('no')+2);	// в этой строке будут номера невыполненных объявлений через d
				var y2 = new Array();	// здесь уже избавляемся от d и в этом массиве будут просто номера выполненных объяв
				var n2 = new Array();	// а в этом массиве будут просто номера невыполненных объяв
				if (y1 != '')
				{
					y2 = y1.split('d');
				}
				if (n1 != '')
				{
					n2 = n1.split('d');
				}

				// отмечаю галочкой или снимаю галочку в зависимости от действия у чекбоксов, к-ые успешно выполнены
				for (i=0; i<y2.length; i++)
				{
					if (act == 1)
					{
						document.getElementById(pname+y2[i]).checked = true;
					}
					else
					{
						document.getElementById(pname+y2[i]).checked = false;
					}
				}
				// отмечаю галочкой или снимаю галочку в зависимости от действия у чекбоксов, к-ые не выполнены
				for (i=0; i<n2.length; i++)
				{
					if (act == 1)
					{
						document.getElementById(pname+n2[i]).checked = false;
					}
					else
					{
						document.getElementById(pname+n2[i]).checked = true;
					}
					er2[er2.length] = n2[i];
				}
				res = 1;
			}
			else
			{
				res = 2;
			}
		}
		else
		{
			res = 2;
		}
	}
	
	if (res == 2)
	{
		// произошла какая-то ошибка, нужно убрать галочку или наоборот поставить ее, чтобы не возникло разногласия: на странице галочка поставлена у объявления, а в сесиию то ничего не записалось
		var all2 = all1.split('d');

		for (i=0; i<all2.length; i++)
		{
			if (act == 1)
			{
				document.getElementById(pname+all2[i]).checked = false;
			}
			else
			{
				document.getElementById(pname+all2[i]).checked = true;
			}
			er2[er2.length] = all2[i];
		}
	}

	// ну тут вывожу сообщение пользователю с номерами объявлений, если какие-то опреации не были выполнены
	if (er2.length > 0)
	{
		var er1 = er2.join(', ');
		if (act == 1)
		{
			alert('Ошибка! Объявления № ' + er1 + ' не отмечены для отправки в газету!');
		}
		else
		{
			alert('Ошибка! С объявлений № ' + er1 + ' не снята отметка для отправки в газету!');
		}
	}
}

function createRequest()
{
	try{
		request = new XMLHttpRequest();}
	catch (trymicrosoft)
	{
		try{
			request = new ActiveXObject("Msxml2.XMLHTTP");}
		catch (othermicrosoft)
		{
			try{
				request = new ActiveXObject("Microsoft.XMLHTTP");}
			catch (failed){
				request = false;}
		}
	}

	if (!request)
		alert("Некоторые элементы страницы недоступны для просмотра в Вашем браузере.");
}