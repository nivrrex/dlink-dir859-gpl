/*
 * Translated default messages for the jQuery validation plugin.
 * Locale: en-us
 * Region: US
 */
(function ($) {
	$.extend($.validator.messages, {
		required: "Please enter an IP address.",
		email: "Please enter an e-mail address.",
		email_AccountName: "Please enter your e-mail account name.",
		email_Password: "Please enter your e-mail password.",
		ssid: "Please enter a Wi-Fi Name(SSID).",
		ip: "Please enter an IP address.",
		subnet_Mask: "Please enter a subnet mask.",
		device_Name: "Please enter a device name.",
		dlna_Name: "Please enter a name for your DLNA Media Server.",
		port: "Please enter a port number.",
		rule_Name: "Please enter a name for this rule.",
		ip_Range: "Please enter an IP address range.",
		port_Range: "Please enter a port range.",
		address_CheckRange: "Please enter a valid IP address range. (e.g. 1.1.1.1-1.1.1.2)",
		port_CheckRange: "Please enter a valid port range. (e.g. 1000-1001)",
		port_Check: "Please enter a valid port number.",
		netmask: "Please enter a netmask.",
		gateway_Address: "Please enter a gateway address.",
		matrix: "Please enter a matrix.",
		netmask_Check: "Please enter a valid netmask. (e.g. 255.255.255.0)",
		gateway_AddressCheck: "Please enter a valid gateway address. (e.g. 192.168.0.1)",
		destinationIP: "Please enter a destination network IP address.",
		prefix_Length: "Please enter a prefix length.",
		ipv6address_Check: "Please enter a valid IPv6 address. (e.g. 2001::1)",
		smtp: "Please enter an SMTP server address.",
		address_Check: "Please enter a valid IP address. (e.g. 192.168.0.1)",
		address_CheckAllRange: "Please enter a valid IP address. (e.g. 255.255.255.0)",
		check_UserName: "Please enter a username.",
		check_Password: "Please enter a password.",
		email_Check: "Please enter a valid e-mail.",
		host_Name: "Please enter a host name.",
		sourceIP: "Please enter a source network IP address.",
		ipv6address_CheckRange: "Please enter a valid IPv6 address range (e.g., 2001::1-2001::2)",
		password_WEPCheck: "Your password must be 5 or 10 characters length",
		password_WEPCheck128: "Your password must be 13 or 26 characters length",
		password_WEPCheck_H: "Your password must be 10 hex length",
		password_WEPCheck128_H: "Your password must be 26 hex length",
		password_WPACheck: "Your password must be between 8-63 characters length",
		check_DeviceAdminPassword: "Your password must be between 6-15 characters length",
		check_IllegalChar: "Text field contains illegal characters.",
		range: $.validator.format("Please enter a value between {0} and {1}"),
		hex_Range: "Please enter a value between 0 and FF",
		range_Compare: "End range must be greater than the start range",
		number: "Please enter a value",
		ipv6: "Please enter an IPv6 address."
	});
}(jQuery));