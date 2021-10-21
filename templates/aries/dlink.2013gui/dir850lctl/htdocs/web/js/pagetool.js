/* include menu.js */
document.write('<script type="text/javascript" charset="utf-8" src="/js/menu.js' + ini_ver + '"></script>');
/* include DetectRouterConnection.js */
document.write('<script type="text/javascript" charset="utf-8" src="/js/DetectRouterConnection.js' + ini_ver + '"></script>');

function CleanTable(tblID)
{
	table = document.getElementById(tblID);
	var rows = table.getElementsByTagName("tr");
	while (rows.length > 0) table.deleteRow(rows.length - 1);
}

function save_button_changed()
{
	changeFlag = true;
	document.getElementById("Save_Disable_btn").style.display = "none";
	document.getElementById("Save_btn").style.display = "block";
}

function SetCheckBoxEnable(id, init, ckstatus)
{
	var stren = I18N("j", "Enabled");
	var strdis = I18N("j", "Disabled");
	
	SetCheckBox(id, stren, strdis, ckstatus, init);
}

function SetCheckBoxAllow(id, init, ckstatus)
{
	var stren = I18N("j", "Allowed");
	var strdis = I18N("j", "Blocked");
	
	SetCheckBox(id, stren, strdis, ckstatus, init);
}

function SetCheckBox(id, stren, strdis, ckstatus, init)
{
	var checkbox = id+"_ck";
	var now_check;
	var status;
	
	if(init)
	{
		now_check = ckstatus;
		now_check?status=false:status=true;
	}
	else
	{
		now_check = document.getElementById(checkbox).checked;
		now_check?status=true:status=false;
		save_button_changed();
	}
	
	if(status)
	{
		document.getElementById(id).className = "checkbox_off";
		document.getElementById(id).innerHTML = '<input type="checkbox" id="'+checkbox+'" name="'+checkbox+'" checked>'+strdis;
		document.getElementById(checkbox).checked = false;
	}
	else
	{
		document.getElementById(id).className = "checkbox_on";
		document.getElementById(id).innerHTML = '<input type="checkbox" id="'+checkbox+'" name="'+checkbox+'" checked>'+stren;
		document.getElementById(checkbox).checked = true;
	}
}

/////////////////////////////////////////////////////////////////////
function presetCheckBox(id, ck) {
	var targetId = 	document.getElementById(id);
	var checkboxId =  id +'_ck';
	
	if(ck == true) {
		var enable = I18N("j","Enabled");
	//	document.getElementById(checkboxId).checked = true;
		targetId.setAttribute("class", "checkbox_on");
		targetId.setAttribute("className", "checkbox_on");
		targetId.innerHTML='<input type="checkbox" name=' + id + ' id=' + checkboxId + ' checked>'+enable;
		document.getElementById(checkboxId).checked = true;
	}else {	
		var disable = I18N("j","Disabled");
	//	document.getElementById(checkboxId).checked = false;
		targetId.setAttribute("class", "checkbox_off");
		targetId.setAttribute("className", "checkbox_off");
		targetId.innerHTML='<input type="checkbox" name=' + id + ' id=' + checkboxId + ' checked>'+disable;
		document.getElementById(checkboxId).checked = false;
	}	
}
/////////////////////////////////////////////////////////////////////
function internetV4_showGeneral(sel) {
	//alert(sel);
	var staticIP = document.getElementById("generalBlock_staticIP");
	var DynamicIP = document.getElementById("generalBlock_DynamicIP");
	var PPPoE = document.getElementById("generalBlock_PPPoE");
	var PPTP = document.getElementById("generalBlock_PPTP");
	var L2TP = document.getElementById("generalBlock_L2TP");
	var DSLite = document.getElementById("generalBlock_DSLite");
	
	var adv_StaticIP = document.getElementById("advancedBlock_staticIP");
	var adv_DynamicIP = document.getElementById("advancedBlock_DynamicIP");
	var adv_PPPoE = document.getElementById("advancedBlock_PPPoE");
	var adv_PPTP = document.getElementById("advancedBlock_PPTP");
	var adv_L2TP = document.getElementById("advancedBlock_L2TP");
	var adv_DSLite = document.getElementById("advancedBlock_DSLite");
	
	if(sel==0){
		//alert("staticIP");
		staticIP.style.display = "inline";
		PPPoE.style.display = "none";
		PPTP.style.display = "none";
		L2TP.style.display = "none";	
		adv_StaticIP.style.display = "inline";
		adv_DynamicIP.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_PPTP.style.display = "none";
		adv_L2TP.style.display = "none";
		adv_DSLite.style.display = "none";
		
	}
	else if(sel==1){
		//alert("Dynamic IP");
		staticIP.style.display = "none";
		PPPoE.style.display = "none";
		PPTP.style.display = "none";
		L2TP.style.display = "none";
		adv_StaticIP.style.display = "none";
		adv_DynamicIP.style.display = "inline";
		adv_PPPoE.style.display = "none";
		adv_PPTP.style.display = "none";
		adv_L2TP.style.display = "none";
		adv_DSLite.style.display = "none";
	}
	else if(sel==2){
		//alert("PPPoE");
		staticIP.style.display = "none";
		PPPoE.style.display = "inline";
		PPTP.style.display = "none";
		L2TP.style.display = "none";
		adv_StaticIP.style.display = "none";
		adv_DynamicIP.style.display = "none";
		adv_PPPoE.style.display = "inline";
		adv_PPTP.style.display = "none";
		adv_L2TP.style.display = "none";
		adv_DSLite.style.display = "none";
	}
	else if(sel==3){
		//alert("PPTP ");
		staticIP.style.display = "none";
		PPPoE.style.display = "none";
		PPTP.style.display = "inline";
		L2TP.style.display = "none";
		adv_StaticIP.style.display = "none";
		adv_DynamicIP.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_PPTP.style.display = "inline";
		adv_L2TP.style.display = "none";
		adv_DSLite.style.display = "none";
	}
	else if(sel==4){
		//alert("L2TP");
		staticIP.style.display = "none";
		PPPoE.style.display = "none";
		PPTP.style.display = "none";
		L2TP.style.display = "inline";
		adv_StaticIP.style.display = "none";
		adv_DynamicIP.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_PPTP.style.display = "none";
		adv_L2TP.style.display = "inline";
		adv_DSLite.style.display = "none";
	}
	else if(sel==5){
		//alert("DS-Lite");
		staticIP.style.display = "none";
		PPPoE.style.display = "none";
		PPTP.style.display = "none";
		L2TP.style.display = "none";
		adv_StaticIP.style.display = "none";
		adv_DynamicIP.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_PPTP.style.display = "none";
		adv_L2TP.style.display = "none";
		adv_DSLite.style.display = "inline";
	}
	
}

function internetV7_showGeneral(sel) {
	//alert(sel);
	var MTU_StaticIP = document.getElementById("mtu_StaticIP");
	
	if(sel==0) {
		mtu_StaticIP_Manual.style.display = "none";
	}
	else{
		mtu_StaticIP_Manual.style.display = "inline";
	}

}

function showAdv(id) {
	var block = document.getElementById(id);
	//alert("showAdv1");
	if(block.style.display == "none" || block.style.display == "") {
		//alert("block");
		block.style.display = "block";
	} else {
		//alert("none");
		block.style.display = "none";
	}
	//alert("showAdv2");
}

function alwaysShowAdv(id) {
	var block = document.getElementById(id);
	block.style.display = "inline";
}