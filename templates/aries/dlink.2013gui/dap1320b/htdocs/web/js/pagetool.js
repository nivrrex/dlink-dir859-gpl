/* include menu.js */
document.write('<script type="text/javascript" charset="utf-8" src="/js/menu.js"></script>');
/* include DetectRouterConnection.js */
document.write('<script type="text/javascript" charset="utf-8" src="/js/DetectRouterConnection.js"></script>');

/*nita add + for bridge, redirect ip address to hostname*/
function iOS_check(){
	if (/ip(hone|od|ad)|Mac/i.test(navigator.userAgent || navigator.vendor || window.opera)){
		return true;
	}else{
		return false;
	}
}

function Andriod_check(){
	if(navigator.userAgent.match(/Android/i)) {
		return true;
	}else{
		return false;
	}
}

function redirectToHostname()
{
	var HNAP = new HNAP_XML();
	var xml_GetDeviceSettings = HNAP.GetXML("GetDeviceSettings");
	var getHostName = xml_GetDeviceSettings.Get("GetDeviceSettingsResponse/DeviceName");
	var getRedirect = xml_GetDeviceSettings.Get("GetDeviceSettingsResponse/Redirect");
	var url = window.location.toString();
	var redirect_url = "";
	
	if(url.indexOf(getHostName)<0)
	{
		if(url.indexOf("html")!=-1)
		{
			var para = url.split("/");
			for(var i=0; i<para.length; i++)
			{
				if(para[i].indexOf("html")!=-1)
					redirect_url = para[i];
			}
		}
		
		/*add get redirect node for debug easy.*/
		if(getRedirect=="" && getHostName!="" && Andriod_check()==false)
		{
			if(iOS_check()==true)
				window.location.replace("http://" + getHostName + ".local/" + redirect_url);
			else
				window.location.replace("http://" + getHostName + "/" + redirect_url);
		}
	}
}
redirectToHostname();

/////////////////////////////////////////////////////////////////////
function presetCheckBox(id, ck) {
	var targetId = 	document.getElementById(id);
	var checkboxId =  id +'_ck';
	
	if(ck == true) {
		var enable;
		if(checkboxId == "enableAccess_ck")
		{
			enable = I18N("j","Allowed");
		}
		else
		{
			enable = I18N("j","Enabled");
		}
	//	document.getElementById(checkboxId).checked = true;
		targetId.setAttribute("class", "checkbox_on");
		targetId.setAttribute("className", "checkbox_on");
		targetId.innerHTML='<input type="checkbox" name=' + id + ' id=' + checkboxId + ' checked>'+enable;
		document.getElementById(checkboxId).checked = true;
	}else {	
		var disable;
		if(checkboxId == "enableAccess_ck")
		{
			disable = I18N("j","Blocked");
		}
		else
		{
			disable = I18N("j","Disabled");
		}
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

function internetV6_showGeneral(sel) {
	//alert(sel);
	var Auto_Detection = document.getElementById("generalBlock_AutoDetection");
	var StaticIPv6 = document.getElementById("generalBlock_StaticIPv6");
	var Auto_Configuration = document.getElementById("generalBlock_AutoConfiguration");
	var PPPoE = document.getElementById("generalBlock_PPPoE");
	var IPv6inIPv4Tunnel = document.getElementById("generalBlock_IPv6inIPv4Tunnel");
	var g_6to4 = document.getElementById("generalBlock_6to4");
	var g_6rd = document.getElementById("generalBlock_6rd");
	var LocalConnectivityOnly = document.getElementById("generalBlock_LocalConnectivityOnly");

	var adv_AutoDetection = document.getElementById("advancedBlock_AutoDetection");
	var adv_StaticIPv6 = document.getElementById("advancedBlock_StaticIPv6");
	var adv_AutoConfiguration = document.getElementById("advancedBlock_AutoConfiguration");
	var adv_PPPoE = document.getElementById("advancedBlock_PPPoE");
	var adv_IPv6inIPv4Tunnel = document.getElementById("advancedBlock_IPv6inIPv4Tunnel");
	var adv_6to4 = document.getElementById("advancedBlock_6to4");
	var adv_6rd = document.getElementById("advancedBlock_6rd");
	var adv_LocalConnectivityOnly = document.getElementById("advancedBlock_LocalConnectivityOnly");
	
	
	if(sel==0) {
		//alert("AutoDetection");
		Auto_Detection.style.display = "inline";
		StaticIPv6.style.display = "none";
		Auto_Configuration.style.display = "none";
		IPv6inIPv4Tunnel.style.display = "none";
		g_6to4.style.display = "none";
		g_6rd.style.display = "none";
		PPPoE.style.display = "none";
		adv_AutoDetection.style.display = "inline";
		adv_StaticIPv6.style.display = "none";
		adv_AutoConfiguration.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_IPv6inIPv4Tunnel.style.display = "none";
		adv_6to4.style.display = "none";
		adv_6rd.style.display = "none";
		adv_LocalConnectivityOnly.style.display = "none";
	}
	else if(sel==1) {
		//alert("StaticIPv6");
		Auto_Detection.style.display = "none";
		StaticIPv6.style.display = "inline";
		Auto_Configuration.style.display = "none";
		IPv6inIPv4Tunnel.style.display = "none";
		g_6to4.style.display = "none";
		g_6rd.style.display = "none";
		PPPoE.style.display = "none";
		adv_AutoDetection.style.display = "none";
		adv_StaticIPv6.style.display = "inline";
		adv_AutoConfiguration.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_IPv6inIPv4Tunnel.style.display = "none";
		adv_6to4.style.display = "none";
		adv_6rd.style.display = "none";
		adv_LocalConnectivityOnly.style.display = "none";
	}
	else if(sel==2) {
		//alert("AutoConfiguration");
		Auto_Detection.style.display = "none";
		StaticIPv6.style.display = "none";
		Auto_Configuration.style.display = "inline";
		IPv6inIPv4Tunnel.style.display = "none";
		g_6to4.style.display = "none";
		g_6rd.style.display = "none";
		PPPoE.style.display = "none";
		adv_AutoDetection.style.display = "none";
		adv_StaticIPv6.style.display = "none";
		adv_AutoConfiguration.style.display = "inline";
		adv_PPPoE.style.display = "none";
		adv_IPv6inIPv4Tunnel.style.display = "none";
		adv_6to4.style.display = "none";
		adv_6rd.style.display = "none";
		adv_LocalConnectivityOnly.style.display = "none";
	}
	else if(sel==3) {
		//alert("PPPoE");
		Auto_Detection.style.display = "none";
		StaticIPv6.style.display = "none";
		Auto_Configuration.style.display = "none";
		IPv6inIPv4Tunnel.style.display = "none";
		g_6to4.style.display = "none";
		g_6rd.style.display = "none";
		PPPoE.style.display = "inline";
		adv_AutoDetection.style.display = "none";
		adv_StaticIPv6.style.display = "none";
		adv_AutoConfiguration.style.display = "none";
		adv_PPPoE.style.display = "inline";
		adv_IPv6inIPv4Tunnel.style.display = "none";
		adv_6to4.style.display = "none";
		adv_6rd.style.display = "none";
		adv_LocalConnectivityOnly.style.display = "none";
	}
	else if(sel==4) {
		//alert("IPv6inIPv4Tunnel");
		Auto_Detection.style.display = "none";
		StaticIPv6.style.display = "none";
		Auto_Configuration.style.display = "none";
		IPv6inIPv4Tunnel.style.display = "inline";
		g_6to4.style.display = "none";
		g_6rd.style.display = "none";
		PPPoE.style.display = "none";
		adv_AutoDetection.style.display = "none";
		adv_StaticIPv6.style.display = "none";
		adv_AutoConfiguration.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_IPv6inIPv4Tunnel.style.display = "inline";
		adv_6to4.style.display = "none";
		adv_6rd.style.display = "none";
		adv_LocalConnectivityOnly.style.display = "none";
	}
	else if(sel==5) {
		//alert("6to4");
		Auto_Detection.style.display = "none";
		StaticIPv6.style.display = "none";
		Auto_Configuration.style.display = "none";
		IPv6inIPv4Tunnel.style.display = "none";
		g_6to4.style.display = "inline";
		g_6rd.style.display = "none";
		PPPoE.style.display = "none";
		adv_AutoDetection.style.display = "none";
		adv_StaticIPv6.style.display = "none";
		adv_AutoConfiguration.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_IPv6inIPv4Tunnel.style.display = "none";
		adv_6to4.style.display = "inline";
		adv_6rd.style.display = "none";
		adv_LocalConnectivityOnly.style.display = "none";
	}
	else if(sel==6) {
		//alert("6rd");
		Auto_Detection.style.display = "none";
		StaticIPv6.style.display = "none";
		Auto_Configuration.style.display = "none";
		IPv6inIPv4Tunnel.style.display = "none";
		g_6to4.style.display = "none";
		g_6rd.style.display = "inline";
		PPPoE.style.display = "none";
		adv_AutoDetection.style.display = "none";
		adv_StaticIPv6.style.display = "none";
		adv_AutoConfiguration.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_IPv6inIPv4Tunnel.style.display = "none";
		adv_6to4.style.display = "none";
		adv_6rd.style.display = "inline";
		adv_LocalConnectivityOnly.style.display = "none";
	}
	else if(sel==7) {
		//alert("LocalConnectivityOnly");
		Auto_Detection.style.display = "none";
		StaticIPv6.style.display = "none";
		Auto_Configuration.style.display = "none";
		IPv6inIPv4Tunnel.style.display = "none";
		g_6to4.style.display = "none";
		g_6rd.style.display = "none";
		PPPoE.style.display = "none";
		adv_AutoDetection.style.display = "none";
		adv_StaticIPv6.style.display = "none";
		adv_AutoConfiguration.style.display = "none";
		adv_PPPoE.style.display = "none";
		adv_IPv6inIPv4Tunnel.style.display = "none";
		adv_6to4.style.display = "none";
		adv_6rd.style.display = "none";
		adv_LocalConnectivityOnly.style.display = "inline";
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
		//alert("inline");
		block.style.display = "inline";
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