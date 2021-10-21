// Coding : Timmy Hsieh 2013/01/17

var ROW_BASE = 1; // first number (for display)
var IsLoaded = false;
var TABLE_NAME = 'tblStaticRoute';
var TABLE_NAME2 = 'tblStaticRoute2';
var TABLE_NAME3 = 'tblStaticRoute3';
var STATUS = 'status_'
var NAME = 'name_';
var DESTNETWORK = 'destNetwork_';
var PREFIXLEN = 'prefixlen_';
var GATEWAY = 'gateway_';
var MATRIX = 'matrix_';
var INTERFACE = 'interface_';
var EDIT_ICON = 'editicon';
var DELETE_ICON = 'delicon_';

// Value
var a, b ,c ,d ,e, f, g;
var Name_CheckList = [];

// Pop Window Get Temp Information
var tmp_Name;
var tmp_DestNetwork;
var tmp_PrefixLen;
var tmp_Gateway;
var tmp_Matrix;
var tmp_Interface;
var IsLoaded = true;

function GetHNAPInformation(Status, Name, DestNetwork, PrefixLen, Gateway, Matrix, Interface)
{	
	a = Status;
	b = Name;
	c = DestNetwork;
	d = PrefixLen;
	e = Gateway;
	f = Matrix;
	g = Interface;
}
function myRowObject(one, two, three, four, five, six, seven, eight, nine)
{
	this.one = one;			// input Status
	this.two = two;			// input Name
	this.three = three;		// input Destination IP
	this.four = four;		// input Netmask
	this.five = five;		// input Gateway
	this.six = six;			// input Matrix
	this.seven = seven;		// input Interface
	this.eight = eight;		// input Edit icon
	this.nine = nine;		// input Delete icon
}
function addRowToTable(num)
{
	if (IsLoaded)
	{
		var tbl = document.getElementById(TABLE_NAME);
		var nextRow = tbl.tBodies[0].rows.length;
		var iteration = nextRow + ROW_BASE;
		if (num == null)
		{
			num = nextRow;
		}
		else
		{
			iteration = num + ROW_BASE;
		}
		// add the Row
		var row = tbl.tBodies[0].insertRow(num);
		
		// Status
		var cell_0 = row.insertCell(0);
		var Input1 = document.createElement('input');
		Input1.setAttribute('type', 'checkbox');
		Input1.setAttribute('id', STATUS + iteration);
		Input1.onchange = function () { save_button_changed(); };
		switch (a)
		{
			case "true":
				Input1.checked = true;
				break;
			case "false":
				Input1.checked = false;
				break;
			default:
				alert("Bad request!");
				break;
		}

		cell_0.appendChild(Input1);
		
		// Name
		var cell_1 = row.insertCell(1);
		var Input2 = document.createElement('label');
		Input2.setAttribute('id', NAME + iteration);
		Input2.setAttribute('size', '10');
		Input2.innerHTML = b;
		cell_1.appendChild(Input2);
		
		// Destination Network
		var cell_2 = row.insertCell(2);
		var Input3 = document.createElement('label');
		Input3.setAttribute('id', DESTNETWORK + iteration);
		Input3.setAttribute('size', '10');
		Input3.innerHTML = c;
		cell_2.appendChild(Input3);
		
		// PrefixLen
		var cell_3 = row.insertCell(3);
		var Input4 = document.createElement('label');
		Input4.setAttribute('id', PREFIXLEN + iteration);
		Input4.setAttribute('size', '10');
		Input4.innerHTML = d;
		cell_3.appendChild(Input4);
		
		// Gateway
		var cell_4 = row.insertCell(4);
		var Input5 = document.createElement('label');
		Input5.setAttribute('id', GATEWAY + iteration);
		Input5.setAttribute('size', '10');
		Input5.innerHTML = e;
		cell_4.appendChild(Input5);
		
		// Matrix
		var cell_5 = row.insertCell(5);
		var Input6 = document.createElement('label');
		Input6.setAttribute('id', MATRIX + iteration);
		Input6.setAttribute('size', '10');
		Input6.innerHTML = f;
		cell_5.appendChild(Input6);
		
		// Interface
		var cell_6 = row.insertCell(6);
		var Input7 = document.createElement('label');
		Input7.setAttribute('id', INTERFACE + iteration);
		Input7.setAttribute('size', '10');
		Input7.innerHTML = g;
		cell_6.appendChild(Input7);
		
		// Edit
		var cell_7 = row.insertCell(7);
		var Input8 = document.createElement('img');
		Input8.setAttribute('src', 'image/edit_btn.gif');
		Input8.setAttribute('width', '28');
		Input8.setAttribute('height', '28');
		Input8.setAttribute('id', EDIT_ICON + iteration);
		Input8.setAttribute('style', 'cursor:pointer');
		Input8.onclick = function () { editData(iteration) };
		cell_7.appendChild(Input8);
		
		// Delete Icon
		var cell_8 = row.insertCell(8);
		var Input9 = document.createElement('img');
		Input9.setAttribute('src', 'image/trash.gif');
		Input9.setAttribute('width', '41');
		Input9.setAttribute('height', '41');
		Input9.setAttribute('id', DELETE_ICON + iteration);
		Input9.setAttribute('style', 'cursor:pointer');
		Input9.onclick = function () { deleteCurrentRow(this) };
		cell_8.appendChild(Input9);

		row.myRow = new myRowObject(Input1, Input2, Input3, Input4, Input5, Input6, Input7, Input8, Input9);
		
		a = null;
		b = null;
		c = null;
		d = null;
		e = null;
		f = null;
		g = null;
	}
}
function AddRowToIndex()
{
	var tbl = document.getElementById(TABLE_NAME);
	tmp_Name = document.getElementById("sr_Name").value;
	
	// Clear all name
	for (var _clearName = 0; _clearName < Name_CheckList.length; _clearName ++)
	{
		Name_CheckList[_clearName] = null;
	}
	
	// Save all name
	var getName_Row = 0;
	for (var _getName = 1; _getName <= Total_StaticRouteRules; _getName ++)
	{
		Name_CheckList[getName_Row] = tbl.rows[_getName].cells[1].childNodes[0].innerText;
		// console.log(Name_CheckList[getName_Row] + " ,length: " + Name_CheckList.length);
		getName_Row ++;
	}
	
	// Check Name
	for (var _checkName = 0; _checkName < Name_CheckList.length; _checkName ++)
	{
		// console.log(Name_CheckList[_checkName] + " & " + tmp_Name);
		if (tmp_Name == Name_CheckList[_checkName])
		{
			alert("Name cannot be the same.");
			return "Error";
		}
	}
	
	a = "true";
	b = document.getElementById("sr_Name").value;
	c = document.getElementById("sr_DestNetwork").value;
	d = document.getElementById("sr_PrefixLen").value;
	e = document.getElementById("sr_Gateway").value;
	f = document.getElementById("sr_Matrix").value;
	g = document.getElementById("sr_Interface").value;

	switch (g)
	{
		case "0":
			g = "NULL";
			break;
		case "1":
			g = "WAN";
			break;
		case "2":
			g = "LAN";
			break;
		case "3":
			g = "LAN(DHCP-PD)";
			break;
		default:
			alert("Bad Request - sr_Interface");
			break;
	}
	
	switch(g)
	{
		case "NULL":
			if (b == '' || c == '' || d == '' || f == '')
			{
				alert("Error! Value cannot be null.");
				return "Error";
			}
			else
			{
				addRowToTable(null);
				Total_StaticRouteRules += 1;
				check_TotalRule(Limit_TotalStaticRouteRules, Total_StaticRouteRules);
			}
			break;
		case "WAN":
		case "LAN":
		case "LAN(DHCP-PD)":
			if (b == '' || c == '' || d == '' || e == '' || f == '')
			{
				alert("Error! Value cannot be null.");
				return "Error";
			}
			else
			{
				addRowToTable(null);
				Total_StaticRouteRules += 1;
				check_TotalRule(Limit_TotalStaticRouteRules, Total_StaticRouteRules);
			}
			break;
	}
	
	return "Success";
}
function editDataGet(id)
{
	var tbl = document.getElementById(TABLE_NAME);
	var tmp_editName = tbl.rows[id].childNodes[1].childNodes[0].innerHTML;
	var tmp_editDestNetwork = tbl.rows[id].childNodes[2].childNodes[0].innerHTML;
	var tmp_editPrefixLen = tbl.rows[id].childNodes[3].childNodes[0].innerHTML;
	var tmp_editGateway = tbl.rows[id].childNodes[4].childNodes[0].innerHTML;
	var tmp_editMatrix = tbl.rows[id].childNodes[5].childNodes[0].innerHTML;
	var tmp_editInterface = tbl.rows[id].childNodes[6].childNodes[0].innerHTML;
	
	document.getElementById("sr_EditName").value = tmp_editName;
	document.getElementById("sr_EditDestNetwork").value = tmp_editDestNetwork;
	document.getElementById("sr_EditPrefixLen").value = tmp_editPrefixLen;
	document.getElementById("sr_EditGateway").value = tmp_editGateway;
	document.getElementById("sr_EditMatrix").value = tmp_editMatrix;
	
	switch (tmp_editInterface)
	{
		case "NULL":
		case "NUL":
			$("#sr_EditInterface").selectbox('detach');
			$("#sr_EditInterface").val('0');
			$("#sr_EditInterface").selectbox('attach');
			break;
		case "WAN":
			$("#sr_EditInterface").selectbox('detach');
			$("#sr_EditInterface").val('1');
			$("#sr_EditInterface").selectbox('attach');
			document.getElementById("create_EditGateway").style.display = "table-row";
			break;
		case "LAN":
			$("#sr_EditInterface").selectbox('detach');
			$("#sr_EditInterface").val('2');
			$("#sr_EditInterface").selectbox('attach');
			document.getElementById("create_EditGateway").style.display = "table-row";
			break;
		case "LAN(DHCP-PD)":
			$("#sr_EditInterface").selectbox('detach');
			$("#sr_EditInterface").val('3');
			$("#sr_EditInterface").selectbox('attach');
			document.getElementById("create_EditGateway").style.display = "table-row";
			break;
		default:
			alert("Bad Request - sr_EditInterface");
			break;
	}
	
	tmp_Name = tmp_editName;
	tmp_DestNetwork = tmp_editDestNetwork;
	tmp_PrefixLen = tmp_editPrefixLen;
	tmp_Gateway = tmp_editGateway;
	tmp_Matrix = tmp_editMatrix;
	tmp_Interface = tmp_editInterface;

	setIteration(id);
}
function assignRowToIndex(id)
{
	var tbl = document.getElementById(TABLE_NAME);
	var edit_Name = document.getElementById("sr_EditName").value;
	
	// console.log("this name is: " + edit_Name + " , and row id is: " + id);
	if (edit_Name != "")
	{
		// Clear all name
		for (var _clearName = 0; _clearName < Name_CheckList.length; _clearName ++)
		{
			Name_CheckList[_clearName] = null;
			// console.log(Name_CheckList[_clearName]);
		}
		
		// Save all name
		var getName_Row = 0;
		for (var _getName = 1; _getName <= Total_StaticRouteRules; _getName ++)
		{
			// console.log("-------------------->" + tmp_ID);
			if (id == _getName)
			{
				Name_CheckList[getName_Row] = edit_Name;
			}
			else
			{
				Name_CheckList[getName_Row] = tbl.rows[_getName].cells[1].childNodes[0].innerText;
			}
			
			// console.log(Name_CheckList[getName_Row] + " ,length: " + Name_CheckList.length);
			getName_Row ++;
		}
		
		// Check Name
		for (var _checkName = 0; _checkName < Name_CheckList.length; _checkName ++)
		{
			// console.log(Name_CheckList[_checkName] + " & " + edit_Name);
			if (edit_Name == Name_CheckList[_checkName])
			{
				if (_checkName == id - 1)
				{
					continue;
				}
				else
				{
					alert("Name cannot be the same.");
					return "Error";
				}
			}
		}
		
		tbl.rows[id].childNodes[0].childNodes[0].innerHTML = document.getElementById("sr_EditName").value;
	}
	
	var get_EditName = document.getElementById("sr_EditName").value;
	var get_EditLocalIP = document.getElementById("sr_EditDestNetwork").value;
	var get_EditNetmask = document.getElementById("sr_EditPrefixLen").value;
	var get_EditGateway = document.getElementById("sr_EditGateway").value;
	var get_EditMatrix = document.getElementById("sr_EditMatrix").value;
	
	if (get_EditName == "" || get_EditLocalIP == "" || get_EditNetmask == "" || get_EditMatrix == "")
	{
		alert("Error! Value cannot be null.");
		return "Error";
	}
	else
	{
		var tbl = document.getElementById(TABLE_NAME);
		tbl.rows[id].childNodes[1].childNodes[0].innerHTML = tmp_Name;
		tbl.rows[id].childNodes[2].childNodes[0].innerHTML = tmp_DestNetwork;
		tbl.rows[id].childNodes[3].childNodes[0].innerHTML = tmp_PrefixLen;
		tbl.rows[id].childNodes[4].childNodes[0].innerHTML = tmp_Gateway;
		tbl.rows[id].childNodes[5].childNodes[0].innerHTML = tmp_Matrix;
	}

	// alert(tmp_Interface);
	
	switch (tmp_Interface)
	{
		case "0":
		case "NULL":
		case "NUL":
			tbl.rows[id].childNodes[6].childNodes[0].innerHTML = "NULL";
			break;
		case "1":
		case "WAN":
			tbl.rows[id].childNodes[6].childNodes[0].innerHTML = "WAN";
			break;
		case "2":
		case "LAN":
			tbl.rows[id].childNodes[6].childNodes[0].innerHTML = "LAN";
			break;
		case "3":
		case "LAN(DHCP-PD)":
			tbl.rows[id].childNodes[6].childNodes[0].innerHTML = "LAN(DHCP-PD)";
			break;
		default:
			alert("Bad Request - tmp_Interface");
			break;
	}

	check_TotalRule(Limit_TotalStaticRouteRules, Total_StaticRouteRules);
	return "Success";
}
function deleteCurrentRow(obj)
{
	if (IsLoaded)
	{
		var delRow = obj.parentNode.parentNode;
		var tbl = delRow.parentNode.parentNode;
		var rIndex = delRow.sectionRowIndex;
		var rowArray = new Array(delRow);
		deleteRows(rowArray);
		reorderRows(tbl, rIndex);

		Total_StaticRouteRules -= 1;
		check_TotalRule(Limit_TotalStaticRouteRules, Total_StaticRouteRules);
		save_button_changed();
	}
}
function OnChangeName(num)
{
	tmp_Name = num;
}
function OnChangeDestinationIP(num)
{
	tmp_DestNetwork = num;
}
function OnChangeNetmask(num)
{
	tmp_PrefixLen = num;
}
function OnChangeGateway(num)
{
	tmp_Gateway = num;
}
function OnChangeMatrix(num)
{
	tmp_Matrix = num;
}
function OnChangeInterface(num)
{
	tmp_Interface = num;
	
	switch (tmp_Interface)
	{
		case "0":
			document.getElementById("create_EditGateway").style.display = "none";
			break;
		case "1":
		case "2":
		case "3":
			document.getElementById("create_EditGateway").style.display = "table-row";
			break;
	}
}
function deleteRows(rowObjArray)
{
	if (IsLoaded)
	{
		for (var i = 0; i < rowObjArray.length; i ++)
		{
			var rIndex = rowObjArray[i].sectionRowIndex;
			rowObjArray[i].parentNode.deleteRow(rIndex);
		}
	}
}
function changeRowIndex(count)
{
	// Change Index List
	var tbl = document.getElementById(TABLE_NAME);
	tbl.rows[count].childNodes[7].childNodes[0].onclick = function() { editData(count) };
}
function reorderRows(tbl, startingIndex)
{
	var tbl = document.getElementById(TABLE_NAME);
	if (IsLoaded)
	{
		if (tbl.tBodies[0].rows[startingIndex])
		{
			var count = startingIndex + ROW_BASE;
			for (var i = startingIndex; i < tbl.tBodies[0].rows.length; i ++)
			{
				tbl.tBodies[0].rows[i].myRow.one.id = STATUS + count;
				tbl.tBodies[0].rows[i].myRow.two.id = NAME + count;
				tbl.tBodies[0].rows[i].myRow.three.id = DESTNETWORK + count;
				tbl.tBodies[0].rows[i].myRow.four.id = PREFIXLEN + count;
				tbl.tBodies[0].rows[i].myRow.five.id = GATEWAY + count;
				tbl.tBodies[0].rows[i].myRow.six.id = MATRIX + count;
				tbl.tBodies[0].rows[i].myRow.seven.id = INTERFACE + count;
				changeRowIndex(count);
				count++;
			}
		}
	}
}
function check_TotalRule(Limit_TotalStaticRouteRules, Total_StaticRouteRules)
{
	var IsFull = Limit_TotalStaticRouteRules - Total_StaticRouteRules;
	document.getElementById("RemainingRules").innerHTML = IsFull;
	
	if (IsFull == 0)
	{
		document.getElementById("createButton").disabled = true;
	}
	else
	{
		document.getElementById("createButton").disabled = false;
	}
}