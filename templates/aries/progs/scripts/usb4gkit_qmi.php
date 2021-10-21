<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

if ($SLOT != "USB-1")
{
	/* This board has only one USB which we can support USB 4G adapter. */
	echo "echo USB4GKIT: We DO NOT support slot - ".$SLOT." !!! > /dev/console\n";
	echo "exit 9\n";
}
else
{
	if ($ACTION=="addttydataport")
	{
		if ($DEVNUM=="")
		{
			echo "echo USB4GKIT: No DEVNUM !!! > /dev/console\n";
			echo "exit 9\n";
		}
		else
		{
			$tty = XNODE_getpathbytarget("/runtime/tty", "entry", "devnum", $DEVNUM, 1);
			anchor($tty);
			set("slot",		$SLOT);
			set("vid",		$VID);
			set("pid",		$PID);
			set("devname",	$DEVNAME);
			set("devpath",	$DEVPATH);
			set("infnum",	$INFNUM);
			set("generation","GENERATION_4_QMI");

			event("TTY.ATTACH");
		}
	}
	else if ($ACTION=="addttycmdport")
	{
		if ($DEVNUM=="")
		{
			echo "echo USB4GKIT: No DEVNUM !!! > /dev/console\n";
			echo "exit 9\n";
		}
		else
		{
			$tty = XNODE_getpathbytarget("/runtime/tty", "entry", "devnum", $DEVNUM, 1);
			anchor($tty);
			set("cmdport/devname",	$DEVNAME);
			set("cmdport/devpath",	$DEVPATH);
			set("cmdport/infnum",	$INFNUM);

//			setattr("rssi",     "get", "usb3gkit -c rssi -v 0x".$VID." -p 0x".$PID." -n ".$DEVNAME);
//			setattr("connection", "get", "usb3gkit -c connection -v 0x".$VID." -p 0x".$PID." -n ".$DEVNAME);
//			setattr("operator",  "get", "usb3gkit -c operator -v 0x".$VID." -p 0x".$PID." -n ".$DEVNAME);
		}

	}
	else if ($ACTION=="addtty")
	{
		if ($DEVNUM=="")
		{
			echo "echo USB4GKIT: No DEVNUM !!! > /dev/console\n";
			echo "exit 9\n";
		}
		else
		{
			$tty = XNODE_getpathbytarget("/runtime/tty", "entry", "devnum", $DEVNUM, 1);
			$inf = XNODE_getpathbytarget($tty, "inf", "devpath", $DEVPATH, 1);
			anchor($inf);
			set("devname",	$DEVNAME);
			set("devpath",	$DEVPATH);
			set("infnum",	$INFNUM);
		}
	}
	else if ($ACTION=="deltty")
	{
		$path = "";
		foreach ("/runtime/tty/entry")
		{
			TRACE_debug("usb4gkit.php: devpath=".query("devpath").", DEVPATH=".$DEVPATH);
			if (query("devpath")==$DEVPATH) $path = "/runtime/tty/entry:".$InDeX;
		}
		if ($path != "")
		{
//			del($path);
			del("/runtime/tty");
			del("/runtime/auto_config");
			
			event("TTY.DETTACH");
		}
	}
}
echo "exit 0\n";
?>
