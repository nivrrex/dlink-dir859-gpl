// Coding : Timmy Hsieh 2013/03/19

var ROW_BASE = 1; // first number (for display)
var IsLoaded = false;
var TABLE_NAME = 'tblVirtualServer';
var TABLE_NAME2 = 'tblVirtualServer2';
var TABLE_NAME3 = 'tblVirtualServer3';
var STATUS = 'status_'
var NAME = 'name_';
var EXTERNAL_PORT = 'export_';
var INTERNAL_PORT = 'inport_';
var PROTOCOL = 'protocol_';
var LOCAL_IP = 'localip_';
var SCHEDULE = 'schedule_';
var EDIT_ICON = 'editicon_';
var DELETE_ICON = 'delicon_';

// Value
var a, b, c, d, e, f, g, h;
var Name_CheckList = [];

// Pop Window Get Temp Information
var tmp_Iteration;
var tmp_Name;
var tmp_LocalIP;
var tmp_Protocol;
var tmp_ProtocolNumber;
var tmp_ExternalPort;
var tmp_InternalPort;
var tmp_Schedule;
var IsLoaded = true;

function GetHNAPInformation(Status, Name, LocalIP, Protocol, ProtocolNumber, ExternalPort, InternalPort, Schedule)
{
	a = Status;
	b = Name;
	c = LocalIP;
	d = Protocol;
	e = ProtocolNumber;
	f = ExternalPort;
	g = InternalPort;
	h = Schedule;
}

function myRowObject(one, two, three, four, five, six, seven, eight, nine)
{
	this.one = one;			// input Status
	this.two = two;			// input Name
	this.three = three;		// input Local IP
	this.four = four;		// input Protocol & Protocol Number
	this.five = five;		// input External Port
	this.six = six;			// input Internal Port
	this.seven = seven;		// input Schedule
	this.eight = eight;		// input Edit icon
	this.night = nine;		// input Delete icon
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
		}
		
		cell_0.appendChild(Input1);
		
		// Name
		var cell_1 = row.insertCell(1);
		var Input2 = document.createElement('label');
		Input2.setAttribute('id', NAME + iteration);
		Input2.setAttribute('size', '10');
		Input2.innerHTML = b;
		cell_1.appendChild(Input2);
		
		// Local IP
		var cell_2 = row.insertCell(2);
		var Input3 = document.createElement('label');
		Input3.setAttribute('id', LOCAL_IP + iteration);
		Input3.setAttribute('size', '10');
		Input3.innerHTML = c;
		cell_2.appendChild(Input3);
		
		// Protocol
		var cell_3 = row.insertCell(3);
		var Input4 = document.createElement('label');
		Input4.setAttribute('id', PROTOCOL + iteration);
		Input4.setAttribute('size', '10');
		
		switch(d)
		{
			case "TCP":
				Input4.innerHTML = d;
				break;
			case "UDP":
				Input4.innerHTML = d;
				break;
			case "Both":
				Input4.innerHTML = d;
				break;
			case "Other":
				Input4.innerHTML = d + " : " + e;
				break;
			default:
				break;
		}
		
		cell_3.appendChild(Input4);
		
		// External Port
		var cell_4 = row.insertCell(4);
		var Input5 = document.createElement('label');
		Input5.setAttribute('id', EXTERNAL_PORT + iteration);
		Input5.setAttribute('size', '10');
		
		switch (d)
		{
			case "TCP":
			case "UDP":
			case "Both":
				Input5.innerHTML = f;
				break;
			case "Other":
			default:
				Input5.innerHTML = "N/A";
				break;
		}
		
		cell_4.appendChild(Input5);
		
		// Internal Port
		var cell_5 = row.insertCell(5);
		var Input6 = document.createElement('label');
		Input6.setAttribute('id', INTERNAL_PORT + iteration);
		Input6.setAttribute('size', '10');
		
	switch (d)
		{
			case "TCP":
			case "UDP":
			case "Both":
				Input6.innerHTML = g;
				break;
			case "Other":
			default:
				Input6.innerHTML = "N/A";
				break;
		}
		
		cell_5.appendChild(Input6);

		// Schedule
		var cell_6 = row.insertCell(6);
		var Input7 = document.createElement('label');
		Input7.setAttribute('id', SCHEDULE + iteration);
		Input7.setAttribute('size', '10');
		if (h == "Always")
		{
			h = I18N("j", "Always");
		}
		Input7.innerHTML = h;
		cell_6.appendChild(Input7);
		
		
		// Input Edit Icon
		var cell_7 = row.insertCell(7);
		var Input8 = document.createElement('img');
		Input8.setAttribute('src', 'image/edit_btn.gif');
		Input8.setAttribute('width', '28');
		Input8.setAttribute('height', '28');
		Input8.setAttribute('id', EDIT_ICON + iteration);
		Input8.setAttribute('style', 'cursor:pointer');
		Input8.onclick = function () { editData(iteration) };
		cell_7.appendChild(Input8);
		
		// Input Delete Icon
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
		h = null;
	}
}
function AddRowToIndex()
{
	var tbl = document.getElementById(TABLE_NAME);
	tmp_Name = document.getElementById("vs_Name").value;
	
	// Clear all name
	for (var _clearName = 0; _clearName < Name_CheckList.length; _clearName ++)
	{
		Name_CheckList[_clearName] = null;
	}
	
	// Save all name
	var getName_Row = 0;
	for (var _getName = 1; _getName <= Total_VirtualServerRules; _getName ++)
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
	
	var getScheduleStatus = document.getElementById("vs_Schedule");
	a = "true";
	b = document.getElementById("vs_Name").value;
	c = document.getElementById("vs_LocalIP").value;
	d = document.getElementById("vs_Protocol").value;
	e = document.getElementById("vs_ProtocolNumber").value;
	f = document.getElementById("vs_ExternalPort").value;	
	g = document.getElementById("vs_InternalPort").value;
	h = getScheduleStatus.options[getScheduleStatus.selectedIndex].text;
	
	// Protocol Type & Protocol Number
	switch (d)
	{
		case "1":
			d = "TCP";
			e = "6";
			break;
		case "2":
			d = "UDP";
			e = "17";
			break;
		case "3":
			d = "Both";
			e = "256";
			break;
		case "4":
			d = "Other";
			break;
		default:
			alert("Bad Request - vs_Protocol");
			break;
	}

	switch (d)
	{
		case "TCP":
		case "UDP":
		case "Both":
			if (b == '' || c == '' || f == '' || g == '')
			{
				alert("Error! Value cannot be null.");
			}
			else
			{
				addRowToTable(null);
				Total_VirtualServerRules += 1;
				check_TotalRule(Limit_VirtualServerRules, Total_VirtualServerRules);
			}
			break;
		case "Other":
			if (b == '' || c == '' || e == '')
			{
				alert("Error! Value cannot be null.");
			}
			else
			{
				addRowToTable(null);
				Total_VirtualServerRules += 1;
				check_TotalRule(Limit_VirtualServerRules, Total_VirtualServerRules);
			}
			break;
		default:
			alert("Error! so do nothing...");
			break;
	}
	return "Success";
}
function editDataGet(id)
{
	var tbl = document.getElementById(TABLE_NAME);
	var tmp_editName = tbl.rows[id].childNodes[1].childNodes[0].innerHTML;
	var tmp_editLocalIP = tbl.rows[id].childNodes[2].childNodes[0].innerHTML;
	var tmp_editProtocol = tbl.rows[id].childNodes[3].childNodes[0].innerHTML;
	var tmp_editProtocolNumber;
	var tmp_editExternalPort = tbl.rows[id].childNodes[4].childNodes[0].innerHTML;
	var tmp_editInternalPort = tbl.rows[id].childNodes[5].childNodes[0].innerHTML;
	var tmp_editSchedule = tbl.rows[id].childNodes[6].childNodes[0].innerHTML;
	
	document.getElementById("edit_vs_Name").value = tmp_editName;
	document.getElementById("vs_EditExternalPort").value = tmp_editExternalPort;
	document.getElementById("vs_EditInternalPort").value = tmp_editInternalPort;
	
	switch (tmp_editProtocol)
	{
		case "TCP":
			document.getElementById("edit_ProtocolNumber").style.display = "none";
			document.getElementById("edit_ExternalPort").style.display = "table-row";
			document.getElementById("edit_InternalPort").style.display = "table-row";
			$("#vs_EditProtocol").selectbox('detach');
			$("#vs_EditProtocol").val('1');
			$("#vs_EditProtocol").selectbox('attach');
			// document.getElementById("vs_EditProtocol").value = "1";
			tmp_editProtocol = "1";
			tmp_editProtocolNumber = "6";
			break;
		case "UDP":
			document.getElementById("edit_ProtocolNumber").style.display = "none";
			document.getElementById("edit_ExternalPort").style.display = "table-row";
			document.getElementById("edit_InternalPort").style.display = "table-row";
			$("#vs_EditProtocol").selectbox('detach');
			$("#vs_EditProtocol").val('2');
			$("#vs_EditProtocol").selectbox('attach');
			// document.getElementById("vs_EditProtocol").value = "2";
			tmp_Protocol = "2";
			tmp_editProtocolNumber = "17";
			break;
		case "Both":
			document.getElementById("edit_ProtocolNumber").style.display = "none";
			document.getElementById("edit_ExternalPort").style.display = "table-row";
			document.getElementById("edit_InternalPort").style.display = "table-row";
			$("#vs_EditProtocol").selectbox('detach');
			$("#vs_EditProtocol").val('3');
			$("#vs_EditProtocol").selectbox('attach');
			// document.getElementById("vs_EditProtocol").value = "3";
			tmp_Protocol = "3";
			tmp_editProtocolNumber = "256";
			break;
		case "Other":
			var split_editProtocol = tmp_editProtocol.split(/[\s:]+/);
			var split_editProtocolString = split_editProtocol[split_editProtocol.length - 1];
			
			document.getElementById("edit_ProtocolNumber").style.display = "table-row";
			document.getElementById("edit_ExternalPort").style.display = "none";
			document.getElementById("edit_InternalPort").style.display = "none";
			$("#vs_EditProtocol").selectbox('detach');
			$("#vs_EditProtocol").val('4');
			$("#vs_EditProtocol").selectbox('attach');
			// document.getElementById("vs_EditProtocol").value = "4";
			document.getElementById("vs_EditProtocolNumber").value = split_editProtocolString;
			tmp_editExternalPort = "";
			tmp_editInternalPort = "";
			tmp_Protocol = "4";
			break;
		default:
			var split_editProtocol = tmp_editProtocol.split(/[\s:]+/);
			var split_editProtocolString = split_editProtocol[split_editProtocol.length - 1];
			
			document.getElementById("edit_ProtocolNumber").style.display = "table-row";
			document.getElementById("edit_ExternalPort").style.display = "none";
			document.getElementById("edit_InternalPort").style.display = "none";
			$("#vs_EditProtocol").selectbox('detach');
			$("#vs_EditProtocol").val('4');
			$("#vs_EditProtocol").selectbox('attach');
			// document.getElementById("vs_EditProtocol").value = "4";
			document.getElementById("vs_EditProtocolNumber").value = split_editProtocolString;
			tmp_editProtocolNumber = split_editProtocolString;
			tmp_editExternalPort = "";
			tmp_editInternalPort = "";
			tmp_Protocol = "4";
			break;
	}
	
	document.getElementById("vs_EditLocalIP").value = tmp_editLocalIP;
	
	var getScheduleStatus = document.getElementById("vs_EditSchedule");
	
	for (var i = 0; i <= Total_ScheduleRules; i ++)
	{
		if(getScheduleStatus.options[i].text === tmp_editSchedule)
		{
			$("#vs_EditSchedule").selectbox('detach');
			$("#vs_EditSchedule").val(i);
			$("#vs_EditSchedule").selectbox('attach');
			break;
		}
	}
	
	tmp_Name = tmp_editName;
	tmp_LocalIP = tmp_editLocalIP;
	tmp_Protocol = tmp_editProtocol;
	tmp_ProtocolNumber = tmp_editProtocolNumber;
	tmp_ExternalPort = tmp_editExternalPort;
	tmp_InternalPort = tmp_editInternalPort;
	tmp_Schedule = tmp_editSchedule;

	setIteration(id);
}
function assignRowToIndex(id)
{
	var tbl = document.getElementById(TABLE_NAME);
	var edit_Name = document.getElementById("edit_vs_Name").value;
	
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
		for (var _getName = 1; _getName <= Total_VirtualServerRules; _getName ++)
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
		
		tbl.rows[id].childNodes[0].childNodes[0].innerHTML = document.getElementById("edit_vs_Name").value;
	}
	
	var get_EditName = document.getElementById("edit_vs_Name").value;
	var get_EditLocalIP = document.getElementById("vs_EditLocalIP").value;
	var get_EditExternalPortValue = document.getElementById("vs_EditExternalPort").value;
	var get_EditInternalPortValue = document.getElementById("vs_EditInternalPort").value;
	var get_EditProtocolNumber = document.getElementById("vs_EditProtocolNumber").value
	var get_EditSchedule = document.getElementById("vs_EditSchedule");
	var get_ScheduleStatus = get_EditSchedule.options[get_EditSchedule.selectedIndex].text;
	
	if (get_ScheduleStatus == "Always")	{ get_ScheduleStatus = I18N("j", "Always");	}
	if (tmp_Protocol == "TCP" || tmp_Protocol == "UDP" || tmp_Protocol == "Both" || tmp_Protocol == "1" || tmp_Protocol == "2" || tmp_Protocol == "3")
	{
		if (tmp_ExternalPort == "" || tmp_InternalPort == "" || get_EditExternalPortValue == "" || get_EditInternalPortValue == "" || get_EditName == "" || get_EditLocalIP == "")
		{
			alert("Error! Value cannot be null.");
			return "Error";
		}
		else
		{
			var tbl = document.getElementById(TABLE_NAME);
			tbl.rows[id].childNodes[1].childNodes[0].innerHTML = tmp_Name;
			//tbl.rows[id].childNodes[2].childNodes[0].innerHTML = tmp_LocalIP;
			tbl.rows[id].childNodes[2].childNodes[0].innerHTML = get_EditLocalIP;
			tbl.rows[id].childNodes[6].childNodes[0].innerHTML = get_ScheduleStatus;
			
			switch(tmp_Protocol)
			{
				case "1":
				case "TCP":
					tbl.rows[id].childNodes[3].childNodes[0].innerHTML = "TCP";
					tbl.rows[id].childNodes[4].childNodes[0].innerHTML = get_EditExternalPortValue;
					tbl.rows[id].childNodes[5].childNodes[0].innerHTML = get_EditInternalPortValue;
					break;
				case "2":
				case "UDP":
					tbl.rows[id].childNodes[3].childNodes[0].innerHTML = "UDP";
					tbl.rows[id].childNodes[4].childNodes[0].innerHTML = get_EditExternalPortValue;
					tbl.rows[id].childNodes[5].childNodes[0].innerHTML = get_EditInternalPortValue;
					break;
				case "3":
				case "Both":
					tbl.rows[id].childNodes[3].childNodes[0].innerHTML = "Both";
					tbl.rows[id].childNodes[4].childNodes[0].innerHTML = get_EditExternalPortValue;
					tbl.rows[id].childNodes[5].childNodes[0].innerHTML = get_EditInternalPortValue;
					break;
				default:
					tbl.rows[id].childNodes[3].childNodes[0].innerHTML = "Other" + " : " + tmp_ProtocolNumber;
					tbl.rows[id].childNodes[4].childNodes[0].innerHTML = "";
					tbl.rows[id].childNodes[5].childNodes[0].innerHTML = "";
					break;
			}	
		}
	}
	else
	{
		if (tmp_ProtocolNumber == "" || get_EditProtocolNumber == "" || get_EditName == "" || get_EditLocalIP == "")
		{
			alert("Error! Value cannot be null.");
			return "Error";
		}
		else
		{
			var tbl = document.getElementById(TABLE_NAME);
			tbl.rows[id].childNodes[1].childNodes[0].innerHTML = tmp_Name;
			tbl.rows[id].childNodes[2].childNodes[0].innerHTML = tmp_LocalIP;
			tbl.rows[id].childNodes[3].childNodes[0].innerHTML = "Other" + " : " + tmp_ProtocolNumber;
			tbl.rows[id].childNodes[4].childNodes[0].innerHTML = "N/A";
			tbl.rows[id].childNodes[5].childNodes[0].innerHTML = "N/A";
		}
	}
	
	check_TotalRule(Limit_VirtualServerRules, Total_VirtualServerRules);
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
		
		Total_VirtualServerRules -= 1;
		check_TotalRule(Limit_VirtualServerRules, Total_VirtualServerRules);
		save_button_changed();
	}
}
function OnChangeName(num)
{
	tmp_Name = num;
}
function OnChangeExternalPort(num)
{
	tmp_ExternalPort = num;
}
function OnChangeInternalPort(num)
{
	tmp_InternalPort = num;
}
function OnChangeProtocol(num)
{
	tmp_Protocol = num;

	switch (tmp_Protocol)
	{
		case "1":
			document.getElementById("edit_ProtocolNumber").style.display = "none";
			document.getElementById("edit_ExternalPort").style.display = "table-row";
			document.getElementById("edit_InternalPort").style.display = "table-row";
			document.getElementById("vs_EditExternalPort").value = "";
			document.getElementById("vs_EditInternalPort").value = "";
			document.getElementById("vs_EditProtocolNumber").value = "6";
			break;
		case "2":
			document.getElementById("edit_ProtocolNumber").style.display = "none";
			document.getElementById("edit_ExternalPort").style.display = "table-row";
			document.getElementById("edit_InternalPort").style.display = "table-row";
			document.getElementById("vs_EditExternalPort").value = "";
			document.getElementById("vs_EditInternalPort").value = "";
			document.getElementById("vs_EditProtocolNumber").value = "17";
			break;
		case "3":
			document.getElementById("edit_ProtocolNumber").style.display = "none";
			document.getElementById("edit_ExternalPort").style.display = "table-row";
			document.getElementById("edit_InternalPort").style.display = "table-row";
			document.getElementById("vs_EditExternalPort").value = "";
			document.getElementById("vs_EditInternalPort").value = "";
			document.getElementById("vs_EditProtocolNumber").value = "256";
			break;
		case "4":
			document.getElementById("edit_ProtocolNumber").style.display = "table-row";
			document.getElementById("edit_ExternalPort").style.display = "none";
			document.getElementById("edit_InternalPort").style.display = "none";
			document.getElementById("vs_EditExternalPort").value = "";
			document.getElementById("vs_EditInternalPort").value = "";
			document.getElementById("vs_EditProtocolNumber").value = "";
			tmp_editExternalPort = "";
			tmp_editInternalPort = "";
			break;
	}
}
function OnChangePortocolNumber(num)
{
	tmp_ProtocolNumber = num;
}
function OnChangeLocalIP(num)
{
	tmp_LocalIP = num;
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
				tbl.tBodies[0].rows[i].myRow.three.id = LOCAL_IP + count;
				tbl.tBodies[0].rows[i].myRow.four.id = PROTOCOL + count;
				tbl.tBodies[0].rows[i].myRow.five.id = EXTERNAL_PORT + count;
				tbl.tBodies[0].rows[i].myRow.seven.id = INTERNAL_PORT + count;
				tbl.tBodies[0].rows[i].myRow.eight.id = SCHEDULE + count;
				changeRowIndex(count);
				count++;
			}
		}
	}
}
function check_TotalRule(Limit_VirtualServerRules, Total_VirtualServerRules)
{
	var IsFull = Limit_VirtualServerRules - Total_VirtualServerRules;
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