<?
include "/etc/services/PHYINF/phywifi.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

$countrycode = query("/runtime/devdata/countrycode");

if($countrycode == "GB" || $countrycode == "EU")
{
	echo "cp /etc/SingleSKU/SingleSKU_CE.dat /var/run/SingleSKU.dat\n";
}
else if ($countrycode == "CA")
{
	echo "cp /etc/SingleSKU/SingleSKU_IC.dat /var/run/SingleSKU.dat\n";
}
else if ($countrycode == "SG")
{
	echo "cp /etc/SingleSKU/SingleSKU_CE_FCC.dat /var/run/SingleSKU.dat\n";
}
else
{
	echo "cp /etc/SingleSKU/SingleSKU_FCC.dat /var/run/SingleSKU.dat\n";
}

echo "insmod /lib/modules/rlt_wifi.ko\n";

?>
