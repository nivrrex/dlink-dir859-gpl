function LOCALIZE(){}
LOCALIZE.prototype =
{
	//localize object
	localize: {},
	
	GetLangfile: function(langCode)
	{
		//auto detect browser language
		if(langCode === "auto")
		{
			var autoDetect = (navigator.browserLanguage || navigator.language);
			langCode = autoDetect.toLowerCase();
		}
		var ajaxObj = GetAjaxObj("get_langfile");
		ajaxObj.createRequest();
		ajaxObj.onCallback = function (json)
		{
			localize = eval('('+json+')'); //parse to JSON data
		}
		ajaxObj.setHeader("Content-Type", "text/xml");
		ajaxObj.requestMethod = "GET";
		ajaxObj.requestAsyn = false;
		ajaxObj.returnXml = false; //return JSON data
		ajaxObj.sendRequest("/js/localization/"+ langCode + ".js");
	},
	
	LangReplace: function(args, string)
	{
		try 
		{
			var pattern = (args.length > 0) ? new RegExp('\\$([1-' + args.length.toString() + '])', 'g') : null;
			var str = localize.hasOwnProperty(string) ? localize[string] : string;
			var result = String(str).replace(pattern, 
				function (match, index) 
				{ 
					index++;
					return args[index]; 
				});
			return result;
		} 
		catch (e)
		{
			//console.log(e);
			return string;
		}
	}
};

var LANG = new LOCALIZE();

function InitLANG(lang)
{
	var langPack = sessionStorage.getItem('langPack');
	 
	if(langPack == null)
	{
		
		LANG.GetLangfile(lang);
	}
	else
	{
		LANG.localize = JSON.parse(langPack);
	}
}

function I18N(type, string)
{
	var args = arguments;
	var res;
	
	string = string.toString() || '';
	res = LANG.LangReplace(args, string);
	
	if (type === 'h') { document.write(res); }
	else if (type === 'j') { return res; }
}