// Coding : Timmy Hsieh 2014/02/05

var ROW_BASE = 1; // first number (for display)
var ROW_ID;
var ROW_NAME;
var GetScheduleTime = "";
var _ReciprocalRebootNumber = 100;
var HNAP = new HNAP_XML();

function DisplayTime()
{
	var DisplayTime = document.createElement("div");
	DisplayTime.className			= "display";
	DisplayTime.style.position		= "absolute";
	DisplayTime.style.top			= "0px";
	DisplayTime.style.left			= "0px";
	DisplayTime.style.width			= "90px";
	DisplayTime.style.height		= "27px";
	DisplayTime.style.lineHeight		= "27px";
	DisplayTime.style.background	= "#FFFA72";
	DisplayTime.style.color			= "#505050";
	DisplayTime.style.zIndex		= "1000";
	return DisplayTime;
}

function SpriteBtn()
{
	var SpriteBtn = document.createElement("div");
	SpriteBtn.className					= "sprite";
	SpriteBtn.style.position			= "absolute";
	SpriteBtn.style.top					= "0px";
	SpriteBtn.style.left				= "0px";
	SpriteBtn.style.width				= "32px";
	SpriteBtn.style.height				= "27px";
	SpriteBtn.style.backgroundImage		= "url('image/closeBtn.png')";
	SpriteBtn.style.backgroundRepeat	= "no-repeat";
	SpriteBtn.style.backgroundPosition	= "center";
	SpriteBtn.style.cursor				= "pointer";
	SpriteBtn.onclick					= function(evt) {
		evt = evt || window.event;
		var target = evt.target || evt.srcElement;
		var aList = new Array();
		var CurVal = parseInt($(target).parent().text());
		var RangeMin = CurVal;
		var RangeMax = CurVal;
		$(".ui-selected", $(this).parent().parent()).each(function()
		{
			var index = $( this ).text();
			aList.push(parseInt(index));
		});

		if (!Array.prototype.indexOf)
		{
			Array.prototype.indexOf = function(elt /*, from*/)
			{
				var len = this.length >>> 0;
				var from = Number(arguments[1]) || 0;
				from = (from < 0) ? Math.ceil(from) : Math.floor(from);
				if (from < 0)	{	from += len;	}

				for (; from < len; from++)
				{
					if (from in this && this[from] === elt)	{	return from;	}
				}
				return -1;
			};
		}

		for (var i = aList.indexOf(RangeMax); i > -1; i --)
		{
			if (aList[i]-1 == aList[i-1])	{	continue;	}
			else		{	RangeMin = aList[i];	break;	}
		}
		var currentLI = $(this).parent().removeClass('ui-selected click-selected-locked ui-unselecting');
		for (var j = RangeMin; j <= RangeMax; j ++)
		{
			currentLI = currentLI.prev().removeClass('ui-selected click-selected-locked ui-unselecting');
			currentLI.prev().children(".display").remove();
		}
		$(this).remove();
	}
	return SpriteBtn;
}
function createSelector()
{
	for (var i = 0; i < 7; i ++)
	{
		var tmpList = "";
		for (var j = 1; j <= 24; j ++)	{	tmpList += "<li class='list' style='position:relative'>" + j + "</li>";	}
		document.getElementById("calendar").children[i].children[0].innerHTML = tmpList;
		document.getElementById("EditCalendar").children[i].children[0].innerHTML = tmpList;
	}
	$(function() {
		$( ".week" ).bind("mousedown", function(evt) {
			evt.metaKey = true;
		}).selectable(
		{
			selecting: function (event, ui)
			{
				Line = 0;
				GetScheduleTime = "";
				$( ".ui-selected", this ).each(function()
				{
					var index = $( this ).text();
					GetScheduleTime = GetScheduleTime += index + ",";
				});
				GetScheduleTime= GetScheduleTime.split(",");
				var ScheduleLine = GetScheduleTime.length;
				var TimeList = new Array();

				for (var i = 0; i < ScheduleLine - 1; i ++)	{	TimeList[i] = parseInt(GetScheduleTime[i]);	}
				for (var j = 0; j < TimeList.length; j ++)	{	if (TimeList[j] + 1 != TimeList[j+1])	{ 	Line ++;	}	}
				if (Line >= 2)
				{
					$(ui.selecting).removeClass('ui-selecting');
					document.getElementById("schedule_Info").innerHTML = "";
					document.getElementById("schedule_EditInfo").innerHTML = "";
					// document.getElementById("schedule_Info").innerHTML = "Maximum two schedule rules per day";
					// document.getElementById("schedule_EditInfo").innerHTML = "Maximum two schedule rules per day";
				}
				else
				{
					document.getElementById("schedule_Info").innerHTML = "";
					document.getElementById("schedule_EditInfo").innerHTML = "";
				}
			},
			selected: function (event, ui)	{	$(ui.selected).addClass("click-selected-locked");	}
		});
	});
}

function Day(d)
{
	var tmpArr = [];	var tmpArr2 = [];
	this.rule = 0;	this.s1 = null;	this.e1 = null;	this.s2 = null;	this.e2 = null;	this.all = null;
	for (var i = 0; i < d.length; i ++)
	{
		if (d[i+1] > d[i]+1)	{	tmpArr.push(d[i]);	this.rule = 1;	}
		else
		{
			if (this.rule == 0)	{	tmpArr.push(d[i]);	}
			else				{	tmpArr2.push(d[i]);	}
		}
	}
	if (tmpArr.length != 0)		{	this.s1 = tmpArr[0]-1;	this.e1 = tmpArr[tmpArr.length - 1];	}
	if (tmpArr2.length != 0)	{	this.s2 = tmpArr2[0]-1;	this.e2 = tmpArr2[tmpArr2.length - 1];	}
	if (d.length == 0)			{	this.rule = null;}
	this.all = [this.rule, this.s1, this.e1, this.s2, this.e2];
}

function Rule(s)
{
	this.rule = parseInt(s[s.length - 5]);	this.s1 = s[s.length - 4];	this.e1 = s[s.length - 3];
	this.s2 = s[s.length - 2];		this.e2 = s[s.length - 1];
	this.all = [this.rule, this.s1, this.e1, this.s2, this.e2];
}

function addRuleButton()	{	document.getElementById("createPop").style.display = "inline";	}
function clearCreateRulePOP()
{
	document.getElementById("createPop").style.display = "none";
	document.getElementById("schedule_name").value = "";
	document.getElementById("schedule_Info").innerHTML = "";
	$("li").removeClass('ui-selected click-selected-locked ui-unselecting');
	$("li .sprite").remove();
	$("li .display").remove();
}

function clearEditRulePOP()
{
	document.getElementById("editPop").style.display = "none";
	document.getElementById("schedule_Editname").value = "";
	document.getElementById("schedule_EditInfo").innerHTML = "";
	$("li").removeClass('ui-selected click-selected-locked ui-unselecting');
	$("li .sprite").remove();
	$("li .display").remove();
}

function checkSum()
{
	var result = closeCreateRulePOP();
	if (result == "Success")
	{
		document.getElementById("schedule_name").value = "";
		document.getElementById("schedule_Info").innerHTML = "";
		document.getElementById("createPop").style.display = "none";
	}
}
function closeCreateRulePOP()
{
	var calendar = document.getElementById("calendar");
	var info = document.getElementById("schedule_Info");
	var checkSum = 0;
	var name = checkRuleName("new");
	
	if (name == "__&illegalChar&__")	{	info.innerHTML = I18N("j", "Text field contains illegal characters.");	return;	}
	
	for (var i = 0; i < 7; i ++)
	{
		eval("var oWeek_" + i + "= [];");
		for (var j = 0; j < 24; j ++)
		{
			if (calendar.children[i].children[0].children[j].className.search("click-selected-locked") != -1)
			{
				eval("oWeek_" + i + ".push(parseInt(calendar.children[i].children[0].children[j].innerHTML));");
				if (checkSum == 0)	{	checkSum = 1;	}
			}
		}
		eval("var oDay" + i + "= new Day (oWeek_" + i + ");");
	}

	if (checkSum == 0)	{	info.innerHTML = I18N("j", "Schedule cannot be null!");	return;	}
	AddRowToTable(null, name, oDay0.all, oDay1.all, oDay2.all, oDay3.all, oDay4.all, oDay5.all, oDay6.all, 1);
	return "Success";
}

function checkEditSum()
{
	var result = closeEditRulePOP();
	if (result == "Success")
	{
		document.getElementById("schedule_Editname").value = "";
		document.getElementById("schedule_EditInfo").innerHTML = "";
		document.getElementById("editPop").style.display = "none";
	}
}

function closeEditRulePOP()
{
	var calendar = document.getElementById("EditCalendar");
	var name = document.getElementById("schedule_Editname").value;
	var info = document.getElementById("schedule_EditInfo");
	var checkSum = 0;
	if (ROW_NAME != name)	{	name = checkRuleName("edit");	}
	
	if (name == "__&illegalChar&__")	{	info.innerHTML = I18N("j", "Text field contains illegal characters.");	return;	}
	
	for (var i = 0; i < 7; i ++)
	{
		eval("var oWeek_" + i + "= [];");
		for (var j = 0; j < 24; j ++)
		{
			if (calendar.children[i].children[0].children[j].className.search("click-selected-locked") != -1)
			{
				eval("oWeek_" + i + ".push(parseInt(calendar.children[i].children[0].children[j].innerHTML));");
				if (checkSum == 0)	{	checkSum = 1;	}
			}
		}
		eval("var oDay" + i + "= new Day (oWeek_" + i + ");");
	}
	if (checkSum == 0)	{	info.innerHTML = I18N("j", "Schedule cannot be null!");	return;	}
	assignRowToIndex(ROW_ID, name, oDay0.all, oDay1.all, oDay2.all, oDay3.all, oDay4.all, oDay5.all, oDay6.all);
	return "Success";
}

function assignRowToIndex(id, name, day1, day2, day3, day4, day5, day6, day7)
{
	var tbl = document.getElementById("tblSchedule");
	var tbl2 = document.getElementById("tblSchedule2");
	tbl.rows[id].childNodes[0].childNodes[0].innerHTML = name;
	tbl.rows[id].childNodes[1].childNodes[0].innerHTML = "";
	// Set Value Default
	for (var i = 0; i <= 13; i ++)	{	tbl2.rows[id].childNodes[i].childNodes[0].innerHTML = '-1';	}

	var b = 0;
	for (var i = 1; i <= 7; i ++)
	{
		eval("var day = day" + i + "[0]");
		var tmpDay = "";
		switch (i)
		{
			case 1:	tmpDay = "Monday";		break;
			case 2:	tmpDay = "Tuesday";		break;
			case 3:	tmpDay = "Wednesday";	break;
			case 4:	tmpDay = "Thursday";	break;
			case 5:	tmpDay = "Friday";		break;
			case 6:	tmpDay = "Saturday";	break;
			case 7:	tmpDay = "Sunday";		break;
		}

		switch (day)
		{
			case null:	b += 2;	break;
			case 0:
				if (eval("day" + i + "[1]") == 0 && eval("day" + i + "[2]") == 24)
				{
					tbl.rows[id].childNodes[1].childNodes[0].innerHTML += I18N("j", tmpDay) + " : " + I18N("j", "All Day") + "</br>";
					tbl2.rows[id].childNodes[parseInt(0+b)].childNodes[0].innerHTML = "0";
					tbl2.rows[id].childNodes[parseInt(1+b)].childNodes[0].innerHTML = "24";
				}
				else
				{
					tbl.rows[id].childNodes[1].childNodes[0].innerHTML += I18N("j", tmpDay) + " : " + eval("day" + i + "[1]") + ":00" + " - " + eval("day" + i + "[2]") + ":00" + "</br>";
					tbl2.rows[id].childNodes[parseInt(0+b)].childNodes[0].innerHTML = eval("day" + i + "[1]");
					tbl2.rows[id].childNodes[parseInt(1+b)].childNodes[0].innerHTML = eval("day" + i + "[2]");
				}
				b += 2;
				break;
			case 1:
				tbl.rows[id].childNodes[1].childNodes[0].innerHTML += I18N("j", tmpDay) + " : " + eval("day" + i + "[1]") + ":00" + " - " + eval("day" + i + "[2]") + ":00" + ", " + eval("day" + i + "[3]") + ":00" + " - " + eval("day" + i + "[4]") + ":00" + "</br>";
				for (var k = 1; k <= 4; k ++)	{	eval("var tmpVal" + k + " = day" + i + "[" + k + "]");	}
				var _valInput1 = tmpVal1 + "," + tmpVal3;
				var _valInput2 = tmpVal2 + "," + tmpVal4;
				tbl2.rows[id].childNodes[parseInt(0+b)].childNodes[0].innerHTML = _valInput1;
				tbl2.rows[id].childNodes[parseInt(1+b)].childNodes[0].innerHTML = _valInput2;
				b += 2;
				break;
		}
	}
	// All Week
	var tmpAllWeek = "";
	tmpAllWeek += I18N("j", "Monday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Tuesday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Wednesday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Thursday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Friday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Saturday") + " : " +I18N("j", "All Day") + "<br>" + I18N("j", "Sunday") + " : " + I18N("j", "All Day") + "<br>";
	if (tbl.rows[id].childNodes[1].childNodes[0].innerHTML == tmpAllWeek)	{	tbl.rows[id].childNodes[1].childNodes[0].innerHTML = I18N("j", "All Week") + "</b>";	}
	document.getElementById("editPop").style.display = "none";
	$("li").removeClass('ui-selected click-selected-locked ui-unselecting');
	$("li .sprite").remove();
	$("li .display").remove();
}

function AddRowToTable(num, name, day1, day2, day3, day4, day5, day6, day7, userData)
{
	var tbl = document.getElementById("tblSchedule");
	var tbl2 = document.getElementById("tblSchedule2");
	var nextRow = tbl.tBodies[0].rows.length;
	var iteration = nextRow + ROW_BASE;
	if (num == null)	{	num = nextRow;	}
	else	{	iteration = num + ROW_BASE;	}
	var row = tbl.tBodies[0].insertRow(num);
	var row2 = tbl2.tBodies[0].insertRow(num);
	// Name
	var cell_0 = row.insertCell(0);
	var Input1 = document.createElement('label');
	Input1.setAttribute('id', "Name_" + iteration);
	Input1.setAttribute('size', '15');
	Input1.innerHTML = name;
	cell_0.appendChild(Input1);

	// Time
	var cell_1 = row.insertCell(1);
	var Input2 = document.createElement('label');
	Input2.setAttribute('id', "Time_" + iteration);
	Input2.setAttribute('size', '15');
	Input2.setAttribute('align', 'right');
	Input2.innerHTML = "";

	// Edit
	var cell_2 = row.insertCell(2);
	var Input3 = document.createElement('img');
	Input3.setAttribute('src', 'image/edit_btn.gif');
	Input3.setAttribute('width', '28');
	Input3.setAttribute('height', '28');
	Input3.setAttribute('id', "E_Icon" + iteration);
	Input3.setAttribute('style', 'cursor:pointer');
	Input3.onclick = function () { editDataGet(iteration) };
	cell_2.appendChild(Input3);

	// Delete Icon
	var cell_3 = row.insertCell(3);
	var Input4 = document.createElement('img');
	Input4.setAttribute('src', 'image/trash.gif');
	Input4.setAttribute('width', '41');
	Input4.setAttribute('height', '41');
	Input4.setAttribute('id', "D_Icon" + iteration);
	Input4.setAttribute('style', 'cursor:pointer');
	Input4.onclick = function () { deleteCurrentRow(this) };
	cell_3.appendChild(Input4);

	// Other Value, Invisible!
	var a = 0;
	for (var i = 4; i <= 17; i ++)
	{
		eval("var cell_" + i + " = row2.insertCell(" + a + ");");	a ++;
		eval("var Input" + parseInt(i+1) + " = document.createElement('label');");
	}

	Input5.setAttribute('id', "Mon_S" + iteration);
	Input6.setAttribute('id', "Mon_E" + iteration);
	Input7.setAttribute('id', "Tue_S" + iteration);
	Input8.setAttribute('id', "Tue_E" + iteration);
	Input9.setAttribute('id', "Wed_S" + iteration);
	Input10.setAttribute('id', "Wed_E" + iteration);
	Input11.setAttribute('id', "Thu_S" + iteration);
	Input12.setAttribute('id', "Thu_E" + iteration);
	Input13.setAttribute('id', "Fri_S" + iteration);
	Input14.setAttribute('id', "Fri_E" + iteration);
	Input15.setAttribute('id', "Sat_S" + iteration);
	Input16.setAttribute('id', "Sat_E" + iteration);
	Input17.setAttribute('id', "Sun_S" + iteration);
	Input18.setAttribute('id', "Sun_E" + iteration);

	// Set Value Default
	for (var i = 5; i <= 18; i ++)	{	eval("Input" + i + ".innerHTML = '-1'");	}

	var b = 0;
	for (var i = 1; i <= 7; i ++)
	{
		eval("var day = day" + i + "[0]");
		var tmpDay = "";
		switch (i)
		{
			case 1:	tmpDay = "Monday";		break;
			case 2:	tmpDay = "Tuesday";		break;
			case 3:	tmpDay = "Wednesday";	break;
			case 4:	tmpDay = "Thursday";	break;
			case 5:	tmpDay = "Friday";		break;
			case 6:	tmpDay = "Saturday";	break;
			case 7:	tmpDay = "Sunday";		break;
		}

		switch (day)
		{
			case null:	break;
			case 0:
				if (eval("day" + i + "[1]") == 0 && eval("day" + i + "[2]") == 24)
				{
					Input2.innerHTML += I18N("j", tmpDay) + " : " + I18N("j", "All Day") + "</br>";
					eval("Input" + parseInt(5+b) + ".innerHTML = '0';");
					eval("Input" + parseInt(6+b) + ".innerHTML = '24';");
				}
				else
				{
					Input2.innerHTML += I18N("j", tmpDay) + " : " + eval("day" + i + "[1]") + ":00" + " - " + eval("day" + i + "[2]") + ":00" + "</br>";
					eval("Input" + parseInt(5+b) + ".innerHTML = day" + i + "[1];");
					eval("Input" + parseInt(6+b) + ".innerHTML = day" + i + "[2];");
				}
				break;
			case 1:
				Input2.innerHTML += I18N("j", tmpDay) + " : " + eval("day" + i + "[1]") + ":00" + " - " + eval("day" + i + "[2]") + ":00" + ", " + eval("day" + i + "[3]") + ":00" + " - " + eval("day" + i + "[4]") + ":00" + "</br>";
				for (var k = 1; k <= 4; k ++)	{	eval("var tmpVal" + k + " = day" + i + "[" + k + "]");	}
				var _valInput1 = tmpVal1 + "," + tmpVal3;
				var _valInput2 = tmpVal2 + "," + tmpVal4;
				eval("Input" + parseInt(5+b) + ".innerHTML = _valInput1");
				eval("Input" + parseInt(6+b) + ".innerHTML = _valInput2");
				break;
		}
		if (tmpDay != "Sunday")	{	for (var j = parseInt(7+b); j <= 18; j ++)	{	eval("Input" + j + ".innerHTML = '-1'");	}	b += 2;	}
	}

	// All Week
	var tmpAllWeek = "";
	tmpAllWeek += I18N("j", "Monday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Tuesday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Wednesday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Thursday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Friday") + " : " + I18N("j", "All Day") + "<br>" + I18N("j", "Saturday") + " : " +I18N("j", "All Day") + "<br>" + I18N("j", "Sunday") + " : " + I18N("j", "All Day") + "<br>";
	if (Input2.innerHTML == tmpAllWeek)	{	Input2.innerHTML = I18N("j", "All Week");	}
	cell_1.appendChild(Input2);

	var c = 5;
	for (var i = 4; i <= 17; i ++)	{	eval("cell_" + i + ".appendChild(Input" + c + ");");	c++;	}
	if (userData == 1)	{	Total_Rules ++;	}
	check_TotalRule(Limit_Rules, Total_Rules);
	$("li").removeClass('ui-selected click-selected-locked ui-unselecting');
	$("li .sprite").remove();
	$("li .display").remove();
}

function editDataGet(id)
{
	document.getElementById("editPop").style.display = "inline";
	var tbl = document.getElementById("tblSchedule");
	var tbl2 = document.getElementById("tblSchedule2");
	var ruleName = tbl.rows[id].childNodes[0].childNodes[0].innerHTML;
	ROW_NAME = ruleName;
	document.getElementById("schedule_Editname").value = ruleName;
	// Get Value
	var Mon_s = tbl2.rows[id].childNodes[0].childNodes[0].innerHTML;
	var Mon_e = tbl2.rows[id].childNodes[1].childNodes[0].innerHTML;
	var Tue_s = tbl2.rows[id].childNodes[2].childNodes[0].innerHTML;
	var Tue_e = tbl2.rows[id].childNodes[3].childNodes[0].innerHTML;
	var Wed_s = tbl2.rows[id].childNodes[4].childNodes[0].innerHTML;
	var Wed_e = tbl2.rows[id].childNodes[5].childNodes[0].innerHTML;
	var Thu_s = tbl2.rows[id].childNodes[6].childNodes[0].innerHTML;
	var Thu_e = tbl2.rows[id].childNodes[7].childNodes[0].innerHTML;
	var Fri_s = tbl2.rows[id].childNodes[8].childNodes[0].innerHTML;
	var Fri_e = tbl2.rows[id].childNodes[9].childNodes[0].innerHTML;
	var Sat_s = tbl2.rows[id].childNodes[10].childNodes[0].innerHTML;
	var Sat_e = tbl2.rows[id].childNodes[11].childNodes[0].innerHTML;
	var Sun_s = tbl2.rows[id].childNodes[12].childNodes[0].innerHTML;
	var Sun_e = tbl2.rows[id].childNodes[13].childNodes[0].innerHTML;
	// Search and Combine Rules
	if (Mon_s.indexOf(',') > -1)
	{
		Mon_s = Mon_s.split(/[\s,]+/);	Mon_e = Mon_e.split(/[\s,]+/);
		var s1 = Mon_s[Mon_s.length - 2];	var s2 = Mon_s[Mon_s.length - 1];
		var e1 = Mon_e[Mon_e.length - 2];	var e2 = Mon_e[Mon_e.length - 1];
		for (var i = parseInt(s1); i < parseInt(e1); i ++)	{	$("#EditMonday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
		for (var j = parseInt(s2); j < parseInt(e2); j ++)	{	$("#EditMonday .week li").eq(j).addClass('ui-selected click-selected-locked');	}
		$("#EditMonday .week li").eq(e1-1).append(new SpriteBtn());
		$("#EditMonday .week li").eq(e2-1).append(new SpriteBtn());
		if (e1 - s1 > 3)
		{
			var display1 = new DisplayTime();
			$("#EditMonday .week li").eq(s1).append(display1);
			display1.innerHTML = (s1) + ":00 - " + e1 + ":00";
		}
		if (e2 - s2 > 3)
		{
			var display2 = new DisplayTime();
			$("#EditMonday .week li").eq(s2).append(display2);
			display2.innerHTML = (s2) + ":00 - " + e2 + ":00";
		}
	}
	else
	{
		if (Mon_s != -1)
		{
			for (var i = parseInt(Mon_s); i < parseInt(Mon_e); i ++)	{	$("#EditMonday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
			$("#EditMonday .week li").eq(Mon_e-1).append(new SpriteBtn());
			if (Mon_e-Mon_s > 3)
			{
				var display1 = new DisplayTime();
				$("#EditMonday .week li").eq(Mon_s).append(display1);
				display1.innerHTML = (Mon_s) + ":00 - " + Mon_e + ":00";
			}
		}
	}

	if (Tue_s.indexOf(',') > -1)
	{
		Tue_s = Tue_s.split(/[\s,]+/);	Tue_e = Tue_e.split(/[\s,]+/);
		var s1 = Tue_s[Tue_s.length - 2];	var s2 = Tue_s[Tue_s.length - 1];
		var e1 = Tue_e[Tue_e.length - 2];	var e2 = Tue_e[Tue_e.length - 1];
		for (var i = parseInt(s1); i < parseInt(e1); i ++)	{	$("#EditTuesday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
		for (var j = parseInt(s2); j < parseInt(e2); j ++)	{	$("#EditTuesday .week li").eq(j).addClass('ui-selected click-selected-locked');	}
		$("#EditTuesday .week li").eq(e1-1).append(new SpriteBtn());
		$("#EditTuesday .week li").eq(e2-1).append(new SpriteBtn());
		if (e1 - s1 > 3)
		{
			var display3 = new DisplayTime();
			$("#EditTuesday .week li").eq(s1).append(display3);
			display3.innerHTML = (s1) + ":00 - " + e1 + ":00";
		}
		if (e2 - s2 > 3)
		{
			var display4 = new DisplayTime();
			$("#EditTuesday .week li").eq(s2).append(display4);
			display4.innerHTML = (s2) + ":00 - " + e2 + ":00";
		}
	}
	else
	{
		if (Tue_s != -1)
		{
			for (var i = parseInt(Tue_s); i < parseInt(Tue_e); i ++)	{	$("#EditTuesday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
			$("#EditTuesday .week li").eq(Tue_e-1).append(new SpriteBtn());
			if (Tue_e-Tue_s > 3)
			{
				var display3 = new DisplayTime();
				$("#EditTuesday .week li").eq(Tue_s).append(display3);
				display3.innerHTML = (Tue_s) + ":00 - " + Tue_e + ":00";
			}
		}
	}

	if (Wed_s.indexOf(',') > -1)
	{
		Wed_s = Wed_s.split(/[\s,]+/);	Wed_e = Wed_e.split(/[\s,]+/);
		var s1 = Wed_s[Wed_s.length - 2];	var s2 = Wed_s[Wed_s.length - 1];
		var e1 = Wed_e[Wed_e.length - 2];	var e2 = Wed_e[Wed_e.length - 1];
		for (var i = parseInt(s1); i < parseInt(e1); i ++)	{	$("#EditWednesday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
		for (var j = parseInt(s2); j < parseInt(e2); j ++)	{	$("#EditWednesday .week li").eq(j).addClass('ui-selected click-selected-locked');	}
		$("#EditWednesday .week li").eq(e1-1).append(new SpriteBtn());
		$("#EditWednesday .week li").eq(e2-1).append(new SpriteBtn());
		if (e1 - s1 > 3)
		{
			var display5 = new DisplayTime();
			$("#EditWednesday .week li").eq(s1).append(display5);
			display5.innerHTML = (s1) + ":00 - " + e1 + ":00";
		}
		if (e2 - s2 > 3)
		{
			var display6 = new DisplayTime();
			$("#EditWednesday .week li").eq(s2).append(display6);
			display6.innerHTML = (s2) + ":00 - " + e2 + ":00";
		}
	}
	else
	{
		if (Wed_s != -1)
		{
			for (var i = parseInt(Wed_s); i < parseInt(Wed_e); i ++)	{	$("#EditWednesday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
			$("#EditWednesday .week li").eq(Wed_e-1).append(new SpriteBtn());
			if (Wed_e-Wed_s > 3)
			{
				var display5 = new DisplayTime();
				$("#EditWednesday .week li").eq(Wed_s).append(display5);
				display5.innerHTML = (Wed_s) + ":00 - " + Wed_e + ":00";
			}
		}
	}

	if (Thu_s.indexOf(',') > -1)
	{
		Thu_s = Thu_s.split(/[\s,]+/);	Thu_e = Thu_e.split(/[\s,]+/);
		var s1 = Thu_s[Thu_s.length - 2];	var s2 = Thu_s[Thu_s.length - 1];
		var e1 = Thu_e[Thu_e.length - 2];	var e2 = Thu_e[Thu_e.length - 1];
		for (var i = parseInt(s1); i < parseInt(e1); i ++)	{	$("#EditThursday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
		for (var j = parseInt(s2); j < parseInt(e2); j ++)	{	$("#EditThursday .week li").eq(j).addClass('ui-selected click-selected-locked');	}
		$("#EditThursday .week li").eq(e1-1).append(new SpriteBtn());
		$("#EditThursday .week li").eq(e2-1).append(new SpriteBtn());
		if (e1 - s1 > 3)
		{
			var display7 = new DisplayTime();
			$("#EditThursday .week li").eq(s1).append(display7);
			display7.innerHTML = (s1) + ":00 - " + e1 + ":00";
		}
		if (e2 - s2 > 3)
		{
			var display8 = new DisplayTime();
			$("#EditThursday .week li").eq(s2).append(display8);
			display8.innerHTML = (s2) + ":00 - " + e2 + ":00";
		}
	}
	else
	{
		if (Thu_s != -1)
		{
			for (var i = parseInt(Thu_s); i < parseInt(Thu_e); i ++)	{	$("#EditThursday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
			$("#EditThursday .week li").eq(Thu_e-1).append(new SpriteBtn());
			if (Thu_e-Thu_s > 3)
			{
				var display7 = new DisplayTime();
				$("#EditThursday .week li").eq(Thu_s).append(display7);
				display7.innerHTML = (Thu_s) + ":00 - " + Thu_e + ":00";
			}
		}
	}

	if (Fri_s.indexOf(',') > -1)
	{
		Fri_s = Fri_s.split(/[\s,]+/);	Fri_e = Fri_e.split(/[\s,]+/);
		var s1 = Fri_s[Fri_s.length - 2];	var s2 = Fri_s[Fri_s.length - 1];
		var e1 = Fri_e[Fri_e.length - 2];	var e2 = Fri_e[Fri_e.length - 1];
		for (var i = parseInt(s1); i < parseInt(e1); i ++)	{	$("#EditFriday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
		for (var j = parseInt(s2); j < parseInt(e2); j ++)	{	$("#EditFriday .week li").eq(j).addClass('ui-selected click-selected-locked');	}
		$("#EditFriday .week li").eq(e1-1).append(new SpriteBtn());
		$("#EditFriday .week li").eq(e2-1).append(new SpriteBtn());
		if (e1 - s1 > 3)
		{
			var display9 = new DisplayTime();
			$("#EditFriday .week li").eq(s1).append(display9);
			display9.innerHTML = (s1) + ":00 - " + e1 + ":00";
		}
		if (e2 - s2 > 3)
		{
			var display10 = new DisplayTime();
			$("#EditFriday .week li").eq(s2).append(display10);
			display10.innerHTML = (s2) + ":00 - " + e2 + ":00";
		}
	}
	else
	{
		if (Fri_s != -1)
		{
			for (var i = parseInt(Fri_s); i < parseInt(Fri_e); i ++)	{	$("#EditFriday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
			$("#EditFriday .week li").eq(Fri_e-1).append(new SpriteBtn());
			if (Fri_e-Fri_s > 3)
			{
				var display9 = new DisplayTime();
				$("#EditFriday .week li").eq(Fri_s).append(display9);
				display9.innerHTML = (Fri_s) + ":00 - " + Fri_e + ":00";
			}
		}
	}

	if (Sat_s.indexOf(',') > -1)
	{
		Sat_s = Sat_s.split(/[\s,]+/);	Sat_e = Sat_e.split(/[\s,]+/);
		var s1 = Sat_s[Sat_s.length - 2];	var s2 = Sat_s[Sat_s.length - 1];
		var e1 = Sat_e[Sat_e.length - 2];	var e2 = Sat_e[Sat_e.length - 1];
		for (var i = parseInt(s1); i < parseInt(e1); i ++)	{	$("#EditSaturday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
		for (var j = parseInt(s2); j < parseInt(e2); j ++)	{	$("#EditSaturday .week li").eq(j).addClass('ui-selected click-selected-locked');	}
		$("#EditSaturday .week li").eq(e1-1).append(new SpriteBtn());
		$("#EditSaturday .week li").eq(e2-1).append(new SpriteBtn());
		if (e1 - s1 > 3)
		{
			var display11 = new DisplayTime();
			$("#EditSaturday .week li").eq(s1).append(display11);
			display11.innerHTML = (s1) + ":00 - " + e1 + ":00";
		}
		if (e2 - s2 > 3)
		{
			var display12 = new DisplayTime();
			$("#EditSaturday .week li").eq(s2).append(display12);
			display12.innerHTML = (s2) + ":00 - " + e2 + ":00";
		}
	}
	else
	{
		if (Sat_s != -1)
		{
			for (var i = parseInt(Sat_s); i < parseInt(Sat_e); i ++)	{	$("#EditSaturday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
			$("#EditSaturday .week li").eq(Sat_e-1).append(new SpriteBtn());
			if (Sat_e-Sat_s > 3)
			{
				var display11 = new DisplayTime();
				$("#EditSaturday .week li").eq(Sat_s).append(display11);
				display11.innerHTML = (Sat_s) + ":00 - " + Sat_e + ":00";
			}
		}
	}

	if (Sun_s.indexOf(',') > -1)
	{
		Sun_s = Sun_s.split(/[\s,]+/);	Sun_e = Sun_e.split(/[\s,]+/);
		var s1 = Sun_s[Sun_s.length - 2];	var s2 = Sun_s[Sun_s.length - 1];
		var e1 = Sun_e[Sun_e.length - 2];	var e2 = Sun_e[Sun_e.length - 1];
		for (var i = parseInt(s1); i < parseInt(e1); i ++)	{	$("#EditSunday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
		for (var j = parseInt(s2); j < parseInt(e2); j ++)	{	$("#EditSunday .week li").eq(j).addClass('ui-selected click-selected-locked');	}
		$("#EditSunday .week li").eq(e1-1).append(new SpriteBtn());
		$("#EditSunday .week li").eq(e2-1).append(new SpriteBtn());
		if (e1 - s1 > 3)
		{
			var display13 = new DisplayTime();
			$("#EditSunday .week li").eq(s1).append(display13);
			display13.innerHTML = (s1) + ":00 - " + e1 + ":00";
		}
		if (e2 - s2 > 3)
		{
			var display14 = new DisplayTime();
			$("#EditSunday .week li").eq(s2).append(display14);
			display14.innerHTML = (s2) + ":00 - " + e2 + ":00";
		}
	}
	else
	{
		if (Sun_s != -1)
		{
			for (var i = parseInt(Sun_s); i < parseInt(Sun_e); i ++)	{	$("#EditSunday .week li").eq(i).addClass('ui-selected click-selected-locked');	}
			$("#EditSunday .week li").eq(Sun_e-1).append(new SpriteBtn());
			if (Sun_e-Sun_s > 3)
			{
				var display13 = new DisplayTime();
				$("#EditSunday .week li").eq(Sun_s).append(display13);
				display13.innerHTML = (Sun_s) + ":00 - " + Sun_e + ":00";
			}
		}
	}
	ROW_ID = id;
}

function deleteCurrentRow(obj)
{
	var delRow = obj.parentNode.parentNode;
	var tbl = delRow.parentNode.parentNode;
	var rIndex = delRow.sectionRowIndex;
	document.getElementById("tblSchedule2").deleteRow(rIndex + 1);
	var rowArray = new Array(delRow);
	deleteRows(rowArray);
	reorderRows(tbl, rIndex);
	Total_Rules -= 1;
	check_TotalRule(Limit_Rules, Total_Rules);
	save_button_changed();
}

function deleteRows(rowObjArray)
{
	for (var i = 0; i < rowObjArray.length; i ++)
	{
		var rIndex = rowObjArray[i].sectionRowIndex;
		rowObjArray[i].parentNode.deleteRow(rIndex);
	}
}

function changeRowIndex(count)
{
	var tbl = document.getElementById("tblSchedule");
	tbl.rows[count].childNodes[2].childNodes[0].onclick = function() { editDataGet(count) };
}

function reorderRows(tbl, startingIndex)
{
	var tbl = document.getElementById("tblSchedule");
	if (tbl.tBodies[0].rows[startingIndex])
	{
		var count = startingIndex + ROW_BASE;
		for (var i = startingIndex; i < tbl.tBodies[0].rows.length; i ++)
		{
			tbl.tBodies[0].rows[i].id = "Name_" + count;
			tbl.tBodies[0].rows[i].id = "Time_" + count;
			changeRowIndex(count);
			count++;
		}
	}
}

function check_TotalRule(Limit_Rules, Total_Rules)
{
	var IsFull = Limit_Rules - Total_Rules;
	document.getElementById("RemainingRules").innerHTML = IsFull;
	(IsFull == 0) ? (document.getElementById("createButton").disabled = true) : (document.getElementById("createButton").disabled = false);
}

function CheckConnectionStatus()
{
	$.ajax({
		cache : false,
		url : "./js/CheckConnection",
		timeout : 5000,
		type : "GET",
		success : function(data) { SetXML(); },
		error : function() { document.getElementById("DetectRouterConnection").style.display = "inline"; }
	});
}

	
function SetXML()
{
	document.getElementById("CreatePopAlertMessage").style.display = "inline";
	document.getElementById("waitSettingFinish").style.display = "inline";
	HNAP.GetXMLAsync("SetScheduleSettings", null, "GetXML", function(xml)	{	SetResult_1st(xml)});
}

function SetResult_1st(result_xml)
{
	if (result_xml != null)
	{
		var tbl = document.getElementById("tblSchedule");
		var tbl2 = document.getElementById("tblSchedule2");
		var count = 1, list = 1, rule = 1, n = 0;
		for (var i = 1; i <= Total_Rules; i ++)
		{
			var name = tbl.rows[count].childNodes[0].childNodes[0].innerHTML;
			result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleName", name);
			for (var k = 0; k < 7; k ++)
			{
				var s = tbl2.rows[count].childNodes[parseInt(0+n)].childNodes[0].innerHTML;
				var e = tbl2.rows[count].childNodes[parseInt(1+n)].childNodes[0].innerHTML;
				if (s.indexOf(',') > -1)
				{
					s = s.split(/[\s,]+/);	e = e.split(/[\s,]+/);
					for (var j = 2; j >= 1; j--)
					{
						var sn = s[s.length - j];	var en = e[e.length - j];
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleDate", k+1);
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleAllDay", "false");
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleTimeFormat", "true");
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleStartTimeInfo/TimeHourValue", sn);
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleEndTimeInfo/TimeHourValue", en);
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleStartTimeInfo/TimeMinuteValue", "00");
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleEndTimeInfo/TimeMinuteValue", "00");
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleStartTimeInfo/TimeMidDateValue", "false");
						result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleEndTimeInfo/TimeMidDateValue", "false");
						list ++;
					}
				}
				else if (s != "-1")
				{
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleName", name);
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleDate", k+1);
					if (s == '0' && e == '24')	{	result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleAllDay", "true");	}
					else						{	result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleAllDay", "false");	}
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleTimeFormat", "true");
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleStartTimeInfo/TimeHourValue", s);
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleEndTimeInfo/TimeHourValue", e);
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleStartTimeInfo/TimeMinuteValue", "00");
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleEndTimeInfo/TimeMinuteValue", "00");
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleStartTimeInfo/TimeMidDateValue", "false");
					result_xml.Set("SetScheduleSettings/ScheduleInfoLists:" + rule + "/ScheduleInfo:" + list + "/ScheduleEndTimeInfo/TimeMidDateValue", "false");
					list ++;
				}
				n += 2;
			}
			count ++;	rule ++;	n = 0;	list = 1;
		}
		// Send HNAP to DUT
		var HNAP = new HNAP_XML();
		HNAP.SetXMLAsync("SetScheduleSettings", result_xml, function(xml)	{	SetResult_2nd(xml);	});
	}
	else	{	if (DebugMode == 1)	{	alert("[!!SetXML Error!!] Function: SetResult_1st");	}	document.getElementById("CreatePopAlertMessage").style.display = "none";	}
}

function SetResult_2nd(result_xml)
{
	var SetResult_2nd = result_xml.Get("SetScheduleSettingsResponse/SetScheduleSettingsResult");
	if (SetResult_2nd == "OK")		{	HNAP.GetXMLAsync("Reboot", null, "GetXML", function(xml)	{	SetResult_3th(xml)	});	}
	if (SetResult_2nd == "ERROR")	{	if (DebugMode == 1)	{	alert("[!!SetXML Error!!] Function: SetResult_2nd");	}	window.location.reload();	}
}
function SetResult_3th(result_xml)
	{
		if (result_xml != null)	{	HNAP.SetXMLAsync("Reboot", result_xml, function(xml)	{	SetResult_4th(xml);	});}
		else	{	alert("An error occurred!");	}
	}
	function SetResult_4th(result_xml)
	{
		var SetResult_4th = result_xml.Get("RebootResponse/RebootResult");
		if (SetResult_4th == "OK" || SetResult_4th == "REBOOT")
		{
			document.getElementById("waitSettingFinish").style.display = "none";
			document.getElementById("REBOOT").style.display = "block";
			Start_reciprocal_Number_Reboot();
		}
		if (SetResult_4th == "ERROR")	{	if (DebugMode == 1)	{	alert("[!!SetXML Error!!] Function: SetResult_4th");	}	setTimeout("waitSettingFinished()", 1000);	}
	}
	function Start_reciprocal_Number_Reboot()
	{
		if (_ReciprocalRebootNumber >= 0)
		{
			document.getElementById("reciprocal_Number_Reboot").innerHTML = _ReciprocalRebootNumber + " " + I18N("j", "Sec");
			_ReciprocalRebootNumber --;
			setTimeout("Start_reciprocal_Number_Reboot()", 1000);
		}
		else
		{
			var Host_Name = localStorage.getItem('RedirectUrl');
			self.location.href = Host_Name;
		}
	}
function waitSettingFinished()	{	window.location.reload();	}

function GetXML()
{
	var HNAP = new HNAP_XML();
	HNAP.GetXMLAsync("GetScheduleSettings", null, "GetValue", function(xml)	{	GetResult_1st(xml)	});
}

function GetResult_1st(result_xml)
{
	var calendar = document.getElementById("calendar");
	var GetResult_1st = result_xml.Get("GetScheduleSettingsResponse/GetScheduleSettingsResult");
	if (GetResult_1st == "OK")
	{
		SC_ListNumber = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists#");
		Total_Rules = SC_ListNumber;
		if (Total_Rules != 0)
		{
			for (var i = 1; i <= SC_ListNumber; i ++)
			{
				var SC_Name = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleName");
				var SC_List = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo#");
				var SC_Date = [];
				if (!Array.prototype.indexOf)
				{
					Array.prototype.indexOf = function(elt /*, from*/)
					{
						var len = this.length >>> 0;
						var from = Number(arguments[1]) || 0;
						from = (from < 0) ? Math.ceil(from) : Math.floor(from);
						if (from < 0)	{	from += len;	}

						for (; from < len; from++)
						{
							if (from in this && this[from] === elt)	{	return from;	}
						}
						return -1;
					};
				}
				for (var j = 1; j <= SC_List; j ++)	{	SC_Date.push(parseInt(result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + j + "/ScheduleDate")));	}

				var k = 0;
				for (var j = 0; j < 7; j ++)
				{
					var data = [];
					eval("var oList_" + j + "= [];");
					if (SC_Date.indexOf(j+1) == -1)
					{
						data[0] = null;	data[1] = null;	data[2] = null;	data[3] = null;	data[4] = null;
						for (var o = 0; o <= 4; o ++)	{	eval("oList_" + j + ".push(data["+ o + "])");	}
						eval("var oRule" + j + "= new Rule (oList_" + j + ");");
					}
					else
					{
						if (SC_Date[k] == SC_Date[k+1])
						{
							data[0] = "1";
							data[1] = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + (k+1) + "/ScheduleStartTimeInfo/TimeHourValue");
							data[2] = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + (k+1) + "/ScheduleEndTimeInfo/TimeHourValue");
							data[3] = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + (k+2) + "/ScheduleStartTimeInfo/TimeHourValue");
							data[4] = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + (k+2) + "/ScheduleEndTimeInfo/TimeHourValue");
							for (var o = 0; o <= 4; o ++)	{	eval("oList_" + j + ".push(data["+ o + "])");	}
							eval("var oRule" + j + "= new Rule (oList_" + j + ");");
							k += 2;
						}
						else
						{
							data[0] = "0";
							var SC_AllDay = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + (k+1)  + "/ScheduleAllDay");
							if (SC_AllDay == "true")	{	data[1] = "0";	data[2] = "24";	data[3] = null;	data[4] = null;	}
							else
							{
								data[1] = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + (k+1) + "/ScheduleStartTimeInfo/TimeHourValue");
								data[2] = result_xml.Get("GetScheduleSettingsResponse/ScheduleInfoLists:" + i + "/ScheduleInfo:" + (k+1) + "/ScheduleEndTimeInfo/TimeHourValue");
								data[3] = null;	data[4] = null;
							}
							for (var o = 0; o <= 4; o ++)	{	eval("oList_" + j + ".push(data["+ o + "])");	}
							eval("var oRule" + j + "= new Rule (oList_" + j + ");");
							k ++;
						}
					}
				}
				AddRowToTable(null, SC_Name, oRule0.all, oRule1.all, oRule2.all, oRule3.all, oRule4.all, oRule5.all, oRule6.all, 0);
			}
		}
	}
}

function checkRuleName(pos)
{
	var tbl = document.getElementById("tblSchedule");
	var Name_CheckList = new Array();
	var ValidScheduleRegex = /(^[A-Za-z0-9_-]+$)/;
	var PreRuleName = "NewRule#";
	var checkName = "";
	var tmpValue = 1;

	switch (pos)
	{
		case "new":	checkName = document.getElementById("schedule_name").value;	var info = document.getElementById("schedule_Info");	break;
		case "edit":	checkName = document.getElementById("schedule_Editname").value;	var info = document.getElementById("schedule_EditInfo");	break;
	}
	info.innerHTML = "";
	checkName = checkName.replace(/\s/g, "");
	if (!ValidScheduleRegex.test(checkName) && checkName.length > 0)	{	return "__&illegalChar&__";	}
	if (checkName == "Always" || checkName == "NewRule" || checkName.length == 0)		{	checkName = PreRuleName + tmpValue;	}
	for (var _clearName = 0; _clearName < Name_CheckList.length; _clearName ++)	{	Name_CheckList[_clearName] = null;	}
	var getName_Row = 0;
	for (var _getName = 1; _getName <= Total_Rules; _getName ++)	{	Name_CheckList[getName_Row] = tbl.rows[_getName].cells[0].childNodes[0].innerText;	getName_Row ++;	}
	for (var _getRluleValue = 0; _getRluleValue < Total_Rules; _getRluleValue ++)	{	for (var _checkName = 0; _checkName < Name_CheckList.length; _checkName ++)	{	if (checkName == Name_CheckList[_checkName])	{	tmpValue ++;	checkName = PreRuleName + tmpValue;	}	}	}
	return checkName;
}