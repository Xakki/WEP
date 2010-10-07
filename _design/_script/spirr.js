pname = '';

function set_mspirr(id, v, nm){
	if(id!=''){
		if(v) set_spirr(id, 1, nm);
		else  set_spirr(id, 0, nm);
	}else {// ������ ������� ��� input � name=spirr � ����������� ID
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

// ��������� ���� �-��� - ���. �� ���� �������� ����������� �� ���.
function set_spirr2()
{
	var res = 0;
	var act = document.getElementById("all_decl_fl1").value;	// ������������� ������������� ��������, 0 - ���� ������� �������, 1 - ���� ������ �������
	var all1 = document.getElementById("all_decl_fl2").value;	// �� ���� ���������� �� ��������, ����������� �������� d, �.�. ��������: 1d45d7d29d86d34
	var er2 = new Array();	// � ���� ������ ���� ���������� �� ����������, ���� ��������� ������ � �������� ��� ���� ����������� �� ���� ���������, �.�. ���� �� ������ �������� �������� ��� ����������, � ������� �� �����������, ��� ��������� ������ ����� - � �� �������
	
	if (request.readyState == 4)
	{
		if (request.status == 200)
		{
			var response = request.responseText;	// �������� ����� �� �������� � ����: yes1d35d46no7d10d11 ����� ����� yes ���� ������ ����������, ����������� �������� d, �������� ��� ������� ���� ������� ���������, �.�. ������ �������� �������� � ������� ��������. ����� ����� no ���� ��� �� ����������� ������ d ������ ����������, �� ���������, ���� �������� �� ���� �����������

			if (response != '')
			{
				var y1 = response.substring(3, response.indexOf('no'));	// � ���� ������ ����� ������ ����������� ���������� ����� d
				var n1 = response.substring(response.indexOf('no')+2);	// � ���� ������ ����� ������ ������������� ���������� ����� d
				var y2 = new Array();	// ����� ��� ����������� �� d � � ���� ������� ����� ������ ������ ����������� �����
				var n2 = new Array();	// � � ���� ������� ����� ������ ������ ������������� �����
				if (y1 != '')
				{
					y2 = y1.split('d');
				}
				if (n1 != '')
				{
					n2 = n1.split('d');
				}

				// ������� �������� ��� ������ ������� � ����������� �� �������� � ���������, �-�� ������� ���������
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
				// ������� �������� ��� ������ ������� � ����������� �� �������� � ���������, �-�� �� ���������
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
		// ��������� �����-�� ������, ����� ������ ������� ��� �������� ��������� ��, ����� �� �������� �����������: �� �������� ������� ���������� � ����������, � � ������ �� ������ �� ����������
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

	// �� ��� ������ ��������� ������������ � �������� ����������, ���� �����-�� �������� �� ���� ���������
	if (er2.length > 0)
	{
		var er1 = er2.join(', ');
		if (act == 1)
		{
			alert('������! ���������� � ' + er1 + ' �� �������� ��� �������� � ������!');
		}
		else
		{
			alert('������! � ���������� � ' + er1 + ' �� ����� ������� ��� �������� � ������!');
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
		alert("��������� �������� �������� ���������� ��� ��������� � ����� ��������.");
}