<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

echo '#!/bin/sh\n';
echo '#PINSTS: "'.$PINSTS.'"\n';

/*XNODE_pri_getpathbytarget();*/
function XNODE_pri_getpathbytarget($base,$node,$target,$value,$create,$pri)
{
    foreach($base."/".$node)
    {
        if (query($target) == $value)
        {
            if($pri != "0")
            {
                if(query("priority") == $pri)
                    return $base."/".$node.":".$InDeX;
            }
            else
                return $base."/".$node.":".$InDeX;
        }
    }

    if ($create > 0)
    {
        $i = query($base."/".$node."#")+1;
        $path = $base."/".$node.":".$i;
        set($path."/".$target, $value);
        return $path;
    }

    return "";

}
/*XNODE_spe_getpathbytarget();*/
function XNODE_spe_getpathbytarget($base,$node,$target,$value,$pid)
{
    foreach($base."/".$node)
    {
        if (query($target) == $value)
        {
            if(query("pid") == $pid)
            {
            	return $base."/".$node.":".$InDeX;
            }
        }
    }
	return "";

}

/*add one function to dispose special dongle ,which can not get mcc/mnc value.And we need to auto dial the dongle.*/
$vid = query("/runtime/tty/entry:1/vid");
$pid = query("/runtime/tty/entry:1/pid");

$filepath = "/runtime/services/special_dongle";	
$node = XNODE_spe_getpathbytarget($filepath, "entry", "vid", $vid, $pid);

if($node != "")
{
	$pri_node = XNODE_getpathbytarget($node, "entry", "priority","1", 0);
	//no support pin code and cimi.
	if($MCC == "" || $MCC == "000")
	{
		$MCC  = ""; 
		$MNC2 = "";
		$MNC3 = "";
		$MCC  = query($pri_node."/mcc");
		$MNC2 = query($pri_node."/mnc2");
		$MNC3 = query($pri_node."/mnc3");		
		$PINSTS = "SPE READY";
		echo '#1 Special PINSTS: "'.$PINSTS.'"\n';
	}
	//support cimi and not support pin code.
	else
	{
		//here dongle can get mcc/mnc ,but the mcc/mnc is repeat ,and the dial shell is not priority.we need to dispose it.
		//such as us cellular um175.because the dial shell is the same as at&t .
		//support cimi and not support cpin.or not support both of cimi and cpin.
		if($vid == "106c" && $pid == "3714")
		{
			$MCC  = "310";
			$MNC2 = "12";
			$MNC3 = "120";
		}
                $PINSTS = "SPE READY";
                echo '#2 Special PINSTS: "'.$PINSTS.'"\n';				
	}
}	

if($MCC !="" && $MCC !="000" )
{
	set("/runtime/device/SIM/MCC", $MCC);
	set("/runtime/device/SIM/MNC2", $MNC2);
	set("/runtime/device/SIM/MNC3", $MNC3);

	$opp = "/runtime/services/operator";
	$country = XNODE_getpathbytarget($opp, "entry", "mcc", $MCC, 0);
	if ($country!="")
	{
		$op = XNODE_getpathbytarget($country, "entry", "mnc", $MNC3, 0);
		$MNC="";
		if ($op!="")
			$MNC = $MNC3;
		else
		{
			$op = XNODE_getpathbytarget($country, "entry", "mnc", $MNC2, 0);
			if ($op!="")
				$MNC = $MNC2;
		}

                //add one function to judge country value ,if not match ,no auto config and dial up.
                $c_mode = query("/runtime/device/zone");	//c_mode means country_mode.
		$c_value = query($country."/country");		//c_value means country_value.
                if($c_mode !="All" && $c_mode != "")
                {
			echo '###c_mode is '.$c_mode."\n";
			echo '###c_value is '.$c_value."\n";
                        if($c_mode == "NA")
			{
				if($c_value != "USA" && $c_value != "Canada")
					exit;
			}
			else
			{
				if($c_value != $c_mode)
					exit;				
			}
                }

		//Follow function will effect dongle,which  Not get MNC/MCC,or can not get MCC/MNC.
/*
		if($MNC == "")
		{
                        $wan3_infp = XNODE_getpathbytarget("","inf","uid","WAN-3",0);
                        $wan3_inetp = XNODE_getpathbytarget("/inet","entry","uid",query($wan3_infp."/inet"),0);
                        $t_mode =query($wan3_inetp."/ppp4/tty/auto_config/mode");
                        if( query($wan3_inetp."/ppp4/tty/auto_config/mode") == "1" )
                        {
                        	set($wan3_inetp."/ppp4/tty/auto_config/mode", "0");
                        	set($wan3_inetp."/ppp4/dialup/mode","manual");
                        }			
			exit;
		}

*/		if ($op!="")
		{
                        $sign = query($op."/repeat_sign");
			$priority = query($op."/priority");
                        if($sign == "1")
                        {			
				if($priority != "1")
				{				
					$op=XNODE_pri_getpathbytarget($country, "entry", "mnc", $MNC, 0,"1");
				}
			}

			$autocfg = "/runtime/device/SIM/autocfg";
			set($autocfg."/apn", query($op."/apn"));
			set($autocfg."/dialno", query($op."/dialno"));
			set($autocfg."/username", query($op."/username"));
			set($autocfg."/password", query($op."/password"));
			set($autocfg."/isp", query($op."/profilename"));
			set($autocfg."/country", query($country."/country"));
/*
			$sign = query($op."/repeat_sign");
			if($sign == "1")
			{
				$wan3_infp = XNODE_getpathbytarget("","inf","uid","WAN-3",0);
				$wan3_inetp = XNODE_getpathbytarget("/inet","entry","uid",query($wan3_infp."/inet"),0);
				$t_mode =query($wan3_inetp."/ppp4/tty/auto_config/mode"); 
				if( query($wan3_inetp."/ppp4/tty/auto_config/mode") == "1" )
				{	
					set($wan3_inetp."/ppp4/tty/auto_config/mode", "0");
					set($wan3_inetp."/ppp4/dialup/mode","manual");
				}
			}
			set($autocfg."/repeat_sign",$sign);
*/
			$auto_config = "/runtime/auto_config";
			set($auto_config."/repeat_sign",$sign);	
			set($auto_config."/apn", query($op."/apn"));
			set($auto_config."/dialno", query($op."/dialno"));
			set($auto_config."/username", query($op."/username"));
			set($auto_config."/password", query($op."/password"));
			set($auto_config."/isp", query($op."/profilename"));
			set($auto_config."/country", query($country."/country"));
			set($auto_config."/mcc", $MCC);
			set($auto_config."/mnc", $MNC);
		}
	}
	set("/runtime/device/SIM/MNC", $MNC);
}
$PINsts = $PINSTS;
$devname = query('/runtime/tty/entry:1/cmdport/devname');
if($devname == "") 
	$devname = query('/runtime/tty/entry:1/devname'); 
$LOCKsts = query('/runtime/device/SIM/LOCKsts');
if ($PINsts == "SPE READY")
{
	echo '# SIM SPE READY: do nothing\n';
	echo 'xmldbc -s /runtime/device/SIM/PINsts NOSUPPORT\n';
        if($MODE == "normal")
        {
                echo '  service INET.WAN-3 restart\n';
        }
	else
	{	
                echo '  service WAN restart\n';
	}
}

else if ($PINsts == " READY" || $PINsts == "READY")
{
	echo '# SIM READY: do nothing\n';
	if($LOCKsts == "READY")
		echo 'xmldbc -s /runtime/device/SIM/PINsts READY\n';
	else
		echo 'xmldbc -s /runtime/device/SIM/PINsts UNLOCKED\n';
        if($MODE == "normal")
        {
                echo '  service INET.WAN-3 restart\n';
        }
	else
	{	
                echo '  service WAN restart\n';
	}
}
else if ($PINsts == " SIM PIN" || $PINsts == "SIM PIN")
{
	$i=1;
	$SIMPIN="";
	while ($i>0)
	{
		$inf = query('/inf:'.$i.'/uid');
		if ($inf == "") { $i=0; break; }
		$infp	= XNODE_getpathbytarget("", "inf", "uid", $inf, 0);
		$inetp	= XNODE_getpathbytarget("/inet", "entry", "uid", query($infp."/inet"), 0);
		$over	= query($inetp.'/ppp4/over');
		if ($over == "tty")
		{
			$SIMPIN = query($inetp.'/ppp4/tty/simpin');
			$i = 0;
			break;
		}
		$i++;
	}
	if ($SIMPIN != "")
	{
		if ($devname != "")
		{
			if($PINsts == " SIM PIN")
			{
				echo 'pinsts=`chat -e -D '.$devname.' OK-AT+CPIN="'.$SIMPIN.'"-OK | grep "OK"`\n';
			}
			else
			{
				echo 'pinsts=`chat -e -D '.$devname.' OK-AT+CPIN=\\\"'.$SIMPIN.'\\\"-OK | grep "OK"`\n';
			}
			echo 'if [ "$pinsts" = "OK" ]; then\n';
			echo '	xmldbc -s /runtime/device/SIM/PINsts READY\n';
			echo '	sh /etc/events/ttyplugoff.sh\n';
			echo '	xmldbc -s /runtime/device/SIM/LOCKsts READY\n';
			echo '	sh /etc/events/ttyplugin.sh\n';
//			echo '	service SIM.CHK restart\n';
			echo 'else\n';
			echo '	xmldbc -s /runtime/device/SIM/PINsts ERROR\n';
			echo 'fi\n';
		}
	}
	else
	{
		echo 'xmldbc -s /runtime/device/SIM/PINsts LOCKED\n';
	}
}
else if ($PINsts == " SIM failure" || $PINsts == "SIM failure")
{
	echo '#Please check SIM card and then reboot.\n';
	echo 'xmldbc -s /runtime/device/SIM/PINsts failure\n';
}
else if ($PINsts == " SIM not inserted" || $PINsts == "SIM not inserted")
{
	echo '#Please sure that SIM is inserted.\n';
	echo 'xmldbc -s /runtime/device/SIM/PINsts NotInserted\n';
}
else if ($PINsts == " SIM PUK" || $PINsts == "SIM PUK")
{
	echo '#PUK is needed.\n';
	echo 'xmldbc -s /runtime/device/SIM/PINsts PUK\n';
}
else if ($PINsts == "" && $MCC !="" && $MCC != "000")
{
        echo '# get PINsts is null,but MCC is not null!\n';
        echo 'xmldbc -s /runtime/device/SIM/PINsts UNLOCKED\n';
        if($MODE == "normal")
        {
                echo '  service INET.WAN-3 restart\n';
        }
	else
	{	
                echo '  service WAN restart\n';
	}
}
else
{
	echo '#Unknown SIM status. Please check 3G dongle and then reboot.\n';
	echo 'xmldbc -s /runtime/device/SIM/PINsts Unknown\n';
	//here need to service WAN start,because if not add ,will cause that some dongle cannot get pin code,and not in special list,connect to 3G network fail.	
        if($MODE == "normal")
        {
                echo '  service INET.WAN-3 restart\n';
        }
	else
	{	
                echo '  service WAN restart\n';
	}
}
echo 'exit 0\n';
?>
