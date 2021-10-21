<? include "/htdocs/phplib/html.php";
if($Remove_XML_Head_Tail != 1)	{HTML_hnap_200_header();}

include "/htdocs/webinc/config.php";

$result = "OK";

if($FEATURE_NOLAN==1)
{
	$feature_lan = "false";
}
else
{
	$feature_lan = "true";
}

?>
<? if($Remove_XML_Head_Tail != 1)	{HTML_hnap_xml_header();}?>
		<GetDeviceFeatureAlphaResponse xmlns="http://purenetworks.com/HNAP1/">
			<GetDeviceFeatureAlphaResult><?=$result?></GetDeviceFeatureAlphaResult>
			<FeatureLAN><?=$feature_lan?></FeatureLAN>
		</GetDeviceFeatureAlphaResponse>
<? if($Remove_XML_Head_Tail != 1)	{HTML_hnap_xml_tail();}?>