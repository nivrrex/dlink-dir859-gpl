function initialMenu()
{
	var menu = "<ul>";
	menu 		+= "	<li><a href='javascript:CheckHTMLStatus(\"Home\");'>"+I18N("j", "Home")+"</a></li>";
	menu 		+= "	<li class='parent' onmouseover='this.className=\"parentOn\"' onmouseout='this.className=\"parent\"'><a href='#'>"+I18N("j", "Settings")+"</a>";
	menu 		+= "		<ul>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"WiFi\");' onclick='return confirmExit();'>"+I18N("j", "Wi-Fi")+"</a></li>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"ExtendedWiFi\");' onclick='return confirmExit()'>"+I18N("j", "Extended Wi-Fi")+"</a></li>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"Audio\");' onclick='return confirmExit()'>"+I18N("j", "Audio Renderer")+"</a></li>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"Network\");' onclick='return confirmExit()'>"+I18N("j", "Network")+"</a></li>";
	menu 		+= "		</ul>";
	
	menu 		+= "	<li class='parent' onmouseover='this.className=\"parentOn\"' onmouseout='this.className=\"parent\"'><a href='#'>"+I18N("j", "Management")+"</a>";
	menu 		+= "		<ul>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"Admin\");' onclick='return confirmExit()'>"+I18N("j", "Admin")+"</a></li>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"System\");' onclick='return confirmExit()'>"+I18N("j", "System")+"</a></li>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"UpdateFirmware\");' onclick='return confirmExit()'>"+I18N("j", "Upgrade")+"</a></li>";
	menu 		+= "			<li><a href='javascript:CheckHTMLStatus(\"Statistics\");' onclick='return confirmExit()'>"+I18N("j", "Statistics")+"</a></li>";
	menu 		+= "		</ul>";
	menu 		+= "	</li>";
	menu 		+= "</ul>";
	document.getElementById("menu").innerHTML = menu;
}

function CheckHTMLStatus(URLString)
{
	if (URLString != "")
	{
		$.ajax({
			"cache" : false,
			"url" : URLString + ".html",
			"timeout" : 2000,
			"type" : "GET",
			"error" : function() { document.getElementById("DetectRouterConnection").style.display = "inline"; },
			"success" : function(data) { document.getElementById("DetectRouterConnection").style.display = "none"; self.location.href = URLString + ".html"; }
		});
	}
	else
	{
		$.ajax({
			"cache" : false,
			"url" : "./js/CheckConnection",
			"timeout" : 2000,
			"type" : "GET",
			"error" : function() { document.getElementById("DetectRouterConnection").style.display = "inline"; },
			"success" : function(data) { document.getElementById("DetectRouterConnection").style.display = "none"; }
		});
	}
}