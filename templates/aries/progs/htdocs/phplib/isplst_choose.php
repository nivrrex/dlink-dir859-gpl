<?
include "htdocs/phplib/xnode.php";

function set_country($from, $to, $index)
{
	set($to."/entry:".$index."/country", query($from."/country"));
	set($to."/entry:".$index."/i18n", query($from."/i18n"));
	set($to."/entry:".$index."/mcc", query($from."/mcc"));
	set($to."/entry:".$index."/iso-3166", query($from."/iso-3166"));
	
}

function set_mnc_entry($from, $to, $index)
{
	set($to."/entry:".$index."/mcc", query($from."/mcc"));
	set($to."/entry:".$index."/mnc", query($from."/mnc"));
	set($to."/entry:".$index."/dialno", query($from."/dialno"));
	set($to."/entry:".$index."/apn", query($from."/apn"));
	set($to."/entry:".$index."/username", query($from."/username"));
	set($to."/entry:".$index."/password", query($from."/password"));
	set($to."/entry:".$index."/profilename", query($from."/profilename"));
	set($to."/entry:".$index."/ispi18n", query($from."/ispi18n"));
	if(query($from."/repeat_sign") != "")
		set($to."/entry:".$index."/repeat_sign", query($from."/repeat_sign"));
	if(query($from."/priority") != "")
		set($to."/entry:".$index."/priority", query($from."/priority"));

}

function copy_entry($from, $to, $index)
{
	movc($from, $to."/entry:".$index);

}

anchor("/runtime/services/operator");
$entry = "/runtime/services/operator";
$i=0;
$j=0;
$zone = query("/runtime/device/zone");
if($zone != "")
{
	$entryp_country = XNODE_getpathbytarget("/runtime/services/operator_all", "entry", "iso-3166", $zone, 0);
	if($entryp_country != "")
	{
		$i++;
		set_country($entryp_country, $entry, $i);
		copy_entry($entryp_country, $entry, $i);
	}
	else
	{
		echo "Unknown 3gisp: ".$zone."\n";
	}

}
else
{

	foreach("/runtime/device/zone/entry")
	{
		$iso3166 =  query("iso-3166");
echo "iso3166=".$iso3166."\n";
		$entryp_country = XNODE_getpathbytarget("/runtime/services/operator_all", "entry", "iso-3166", $iso3166, 0);
echo "entryp_country=".$entryp_country."\n";
		if($entryp_country != "")
		{
			$i++;

			set_country($entryp_country, $entry, $i);
			foreach("/runtime/device/zone/entry:".$i."/entry")
			{
				$mnc =  query("mnc");
echo "mnc=".$mnc."\n";
				if($mnc != "")
				{
					$entryp_mnc = XNODE_getpathbytarget($entryp_country, "entry", "mnc", $mnc, 0);
echo "entryp_mnc=".$entryp_mnc."\n";
					if($entryp_mnc != "")
					{
						$j++;
						set_mnc_entry($entryp_mnc, $entry."/entry:".$i, $j);
					}
					else
					{
						echo "Unknown mnc: ".$mnc."\n";

					}
				}
			}

			if($j == 0)	
			{
				copy_entry($entryp_country, $entry, $i);
			}
		}

	}

}

// chose all 3g isplst
if($i == 0)
{
	movc("/runtime/services/operator_all", $entry);
}

?>

