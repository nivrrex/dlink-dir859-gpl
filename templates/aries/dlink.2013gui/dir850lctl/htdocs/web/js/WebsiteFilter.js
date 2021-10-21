// Coding : Timmy Hsieh 2013/03/13

var ROW_BASE = 1; // first number (for display)
var IsLoaded = false;
var TABLE_NAME = 'tblWebsiteFilter';
var TABLE_NAME2 = 'tblWebsiteFilter2';
var URLS = 'urls_'
var DELETE_ICON = 'delicon_';

var a;

// Pop Window Get Temp Information
var tmp_Urls;
var IsLoaded = true;

function GetHNAPInformation(Url)
{	
	a = Url;
}
function myRowObject(one, two)
{
	this.one = one;			// input Url
	this.two = two;			// input Delete icon
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
		
		// Name
		var cell_0 = row.insertCell(0);
		var Input1 = document.createElement('input');
		Input1.setAttribute('type', 'text');
		Input1.setAttribute('id', URLS + iteration);
		Input1.setAttribute('size', '30');
		Input1.onkeydown = function () { save_button_changed(); };
		Input1.value = a;
		cell_0.appendChild(Input1);

		// Delete Icon
		var cell_1 = row.insertCell(1);
		var Input2 = document.createElement('img');
		Input2.setAttribute('src', 'image/trash.gif');
		Input2.setAttribute('width', '41');
		Input2.setAttribute('height', '41');
		Input2.setAttribute('id', DELETE_ICON + iteration);
		Input2.setAttribute('style', 'cursor:pointer');
		Input2.onclick = function () { deleteCurrentRow(this) };
		cell_1.appendChild(Input2);

		row.myRow = new myRowObject(Input1, Input2);
		
		a = null;
	}
}
function AddRowToIndex(num)
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
		
		// Name
		var cell_0 = row.insertCell(0);
		var Input1 = document.createElement('input');
		Input1.setAttribute('type', 'text');
		Input1.setAttribute('id', URLS + iteration);
		Input1.setAttribute('size', '30');
		cell_0.appendChild(Input1);

		// Delete Icon
		var cell_1 = row.insertCell(1);
		var Input2 = document.createElement('img');
		Input2.setAttribute('src', 'image/trash.gif');
		Input2.setAttribute('width', '41');
		Input2.setAttribute('height', '41');
		Input2.setAttribute('id', DELETE_ICON + iteration);
		Input2.setAttribute('style', 'cursor:pointer');
		Input2.onclick = function () { deleteCurrentRow(this) };
		cell_1.appendChild(Input2);

		row.myRow = new myRowObject(Input1, Input2);
		
		Total_FilterRules += 1;
		check_TotalRule(Limit_TotalFilterRules, Total_FilterRules);
	}
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
		
		Total_FilterRules -= 1;
		check_TotalRule(Limit_TotalFilterRules, Total_FilterRules);
		save_button_changed();
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
				tbl.tBodies[0].rows[i].myRow.one.id = URLS + count;
				count++;
			}
		}
	}
}
function check_TotalRule(Limit_TotalFilterRules, Total_FilterRules)
{
	var IsFull = Limit_TotalFilterRules - Total_FilterRules;
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