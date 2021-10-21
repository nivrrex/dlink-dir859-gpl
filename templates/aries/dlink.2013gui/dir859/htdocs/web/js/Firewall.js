
//data list
function Datalist()
{
	this.list = new Array();
	this.maxrowid = 0;
}

Datalist.prototype .getData = function(rowid){
	var i;
	var data;
	
	for(i = 0; i < this.list.length; i++)
	{
		data = this.list[i];
		if(data.rowid == rowid)
		{
			break;
		}
	}
	
	//assume data exist
	return data;
}

Datalist.prototype .getRowNum = function(rowid){
	var rowNum = 0;
	for(rowNum = 0; rowNum < this.list.length; rowNum++)
	{
		if(rowid == this.list[rowNum].rowid)
		{
			break;
		}
	}
	return rowNum;
}

Datalist.prototype.editData = function(id, newdata){
	var rowNum = this.getRowNum(id);
	if(this.checkData(newdata, rowNum) == false)
	{
		return false;
	}
	
	newdata.setRowid(id);
	this.list.splice(rowNum,1,newdata);
	
	newdata.setDataToRow($("#tr"+newdata.rowid));
	return true;
}

Datalist.prototype.deleteData = function(id){
	var rowNum = this.getRowNum(id);
	this.list.splice(rowNum, 1);
	
	$("#tr"+id).remove();
}

Datalist.prototype.push = function(data){
	if(this.checkData(data, null) == false)
	{
		return false;
	}

	data.setRowid(this.maxrowid);
	this.list.push(data);
	
	this.maxrowid++;
	
	data.addRowToHTML('tblFirewall');
	
	return true;
}

Datalist.prototype.checkData = function(newdata, rowNum){
	var i;
	
	//check
	var srcIPstart = newdata.srcIPstart;
	var srcIPend = newdata.srcIPend;
	var srcInterface = newdata.srcInterface;

	if(srcInterface == "LAN" || srcInterface == "LAN-2" || srcInterface == "LAN-1")
	{
		if(srcIPstart != "" && srcIPend != ""&& srcIPend != null)
		{
			if(COMM_IPv4NETWORK(srcIPstart, "24") != COMM_IPv4NETWORK(srcIPend, "24"))
			{
				alert(I18N("j","Please enter a valid IP address range. (e.g. 1.1.1.1-1.1.1.2)"));
				return false;
			}
		}
	}
		
	for(i = 0; i < this.list.length; i++)
	{
		if(i == rowNum)
			continue;
	
		if(this.list[i].name == newdata.name)
		{
			alert(I18N("j","Name cannot be the same."));
			return false;
		}
		
		if((this.list[i].srcInterface == newdata.srcInterface) &&
		    (this.list[i].srcIPstart == newdata.srcIPstart) &&  (this.list[i].srcIPend == newdata.srcIPend) &&
			(this.list[i].dstInterface == newdata.dstInterface) &&
			(this.list[i].dstIPstart == newdata.dstIPstart)&& (this.list[i].dstIPend == newdata.dstIPend)&&
			(this.list[i].protocol == newdata.protocol)&&
			(this.list[i].portStart == newdata.portStart)&& (this.list[i].portEnd == newdata.portEnd)&&
			(this.list[i].schedule == newdata.schedule))
		{
			alert(I18N("j","Rule cannot be the same."));
			return false;	
		}		
	}
	return true;
}

Datalist.prototype.length = function(){
	return this.list.length;
}

//constructor
function Data(name, srcInterface, srcIP, dstInterface, dstIP, protocol, port, schedule){
	this.name = name;
	this.protocol = protocol;
	this.schedule = schedule;
	var srcIParray = this.parseRange(srcIP);
	this.srcIPstart = srcIParray[0];
	this.srcIPend = srcIParray[1];

	var xml_GetGuestZoneRouterSettings = HNAP.GetXML("GetGuestZoneRouterSettings");
    var routerguestIP = xml_GetGuestZoneRouterSettings.Get("GetGuestZoneRouterSettingsResponse/IPAddress");

	if(srcInterface == "LAN")
	{
		if(this.srcIPstart != null)
		{
			var srcIPaddr = this.srcIPstart.split(".");
		}
		if(this.srcIPend != null)
		{
			var srcIPaddr2 = this.srcIPend.split(".");
		}
		if(routerguestIP != null)
		{
			var guestaddr = routerguestIP.split(".");
		}
	}
	
	var dstIParray = this.parseRange(dstIP);
	this.dstIPstart = dstIParray[0];
	this.dstIPend = dstIParray[1];
	if(dstInterface == "LAN")
	{
		if(this.dstIPstart != null)
		{
			var dstIPaddr = this.dstIPstart.split(".");
		}
		if(this.dstIPend != null)
		{
			var dstIPaddr2 = this.dstIPend.split(".");
		}
	}
	
	if(((srcIPaddr !=null )&&(guestaddr !=null )&&(srcIPaddr[2] == guestaddr[2])) || ((srcIPaddr2 !=null )&&(guestaddr !=null )&&(srcIPaddr2[2] == guestaddr[2])))
	{
		this.srcInterface = "LAN-2";
	}
	else
	{
		this.srcInterface = srcInterface;	
	}
	if(((dstIPaddr !=null )&&(guestaddr !=null )&&(dstIPaddr[2] == guestaddr[2])) || ((dstIPaddr2 !=null )&&(guestaddr !=null )&&(dstIPaddr2[2] == guestaddr[2])))
	{
		this.dstInterface = "LAN-2";
	}
	else
	{
		this.dstInterface = dstInterface;	
	}
	var portArray = this.parseRange(port);
	this.portStart = portArray[0];
	this.portEnd = portArray[1];
}


Data.prototype = 
{
	//property
	rowid:null,
	name:null,
	srcInterface:null,
	srcIPstart:null,
	srcIPend:null,
	dstInterface:null,
	dstIPstart:null,
	dstIPend:null,
	protocol:null,
	portStart:null,
	portEnd:null,
	schedule:null,

	//method
	setRowid : function(rowid)
	{
		this.rowid = rowid;
	},
	
	parseRange: function(input)
	{
		var output = new Array;
		
		output = input.split('-');
		
		if((output[0] == null) || (output[0] == ""))
		{
			output[0] = output[1];
			output[1] = null;
		}
		return output;
	},
	
	showName: function()
	{
		return HTMLEncode(this.name);
	},
	
	showSchedule : function()
	{
		if((this.schedule == "Always")||(this.schedule == ""))
		{
			return I18N("j", "Always");
		}
		else
		{
			return HTMLEncode(this.schedule);
		}
	},
	
	addRowToHTML : function(table)
	{
		var outputString;
		
		outputString = "<tr id='tr"+ this.rowid + "'></tr>"
		
		var selector = "#"+table+"> tbody";
		$(selector).append(outputString);
		
		this.setDataToRow($("#tr"+this.rowid));
		return;
	},
	
	setDataToRow : function(object)
	{
		var outputString;
	
		outputString = "<td>" + this.showName() + "</td>";
		outputString += "<td>" + this.showSchedule() + "</td>";
		outputString += "<td><img src='image/edit_btn.png' width=28 height=28 style='cursor:pointer' onclick='editData("+this.rowid+")'/></td>";
		outputString += "<td><img src='image/trash.png' width=41 height=41 style='cursor:pointer' onclick='deleteData("+this.rowid+")'/></td>";
	
		object.html(outputString);
		return;
	},
	
	createRange : function(start, end)
	{
		var outputString = start;
	
		if((end != "") && (end != null))
		{
			outputString += "-"+end;
		}
		return outputString;
	}

}