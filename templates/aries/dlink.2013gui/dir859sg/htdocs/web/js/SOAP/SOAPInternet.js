/**
 * @constructor
 */
function SOAPVlanSettings_lanport()
{
	this.lan1_pppoe = "";
	this.lan2_pppoe = "";
	this.lan3_pppoe = "";
	this.lan4_pppoe = "";
	this.lan1_dhcp = "";
	this.lan2_dhcp = "";
	this.lan3_dhcp = "";
	this.lan4_dhcp = "";
};

/**
 * @constructor
 */
function SOAPVlanSettings_wlanport()
{
	this.wlan01_pppoe = "";
	this.wlan02_pppoe = "";
	this.wlan11_pppoe = "";
	this.wlan12_pppoe = "";
	this.wlan01_dhcp = "";
	this.wlan02_dhcp = "";
	this.wlan11_dhcp = "";
	this.wlan12_dhcp = "";
};

/**
 * @constructor
 */
function SOAPGetVlanSettingsResponse()
{
	this.devmode = "";
	this.wantype = "";
	this.active = 0;
	this.interid_pppoe = "";
	this.voipid_pppoe = "";
	this.iptvid_pppoe = "";
	this.interid_dhcp = "";
	this.voipid_dhcp = "";
	this.iptvid_dhcp = "";
	this.lanport = new SOAPVlanSettings_lanport();
	this.wlanport = new SOAPVlanSettings_wlanport();

};

// @prototype
SOAPGetVlanSettingsResponse.prototype = 
{

}

/**
 * @constructor
 */
function SOAPGetNATSettingsResponse()
{
	this.Disable = "";
}

// @prototype
SOAPGetNATSettingsResponse.prototype = 
{

}
