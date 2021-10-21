<?

//do we have storage?
$storage_count = get("x", "/runtime/device/storage/count");

if($storage_count > 0)
{
	//yes, we have, turn the USB LED on
	echo "usockc /var/gpio_ctrl USB_LED_ON\n";
}
else
{
	//no, we don't, turn the USB LED off
	echo "usockc /var/gpio_ctrl USB_LED_OFF\n";
}

?>
