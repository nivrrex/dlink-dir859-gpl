<?
function output($result)	{echo $result;}
function exec_by_type($type,$path,$value)
{
	if($type == "GET")
		output(query($path));
	else if($type == "SET")
		set($path,$value);
	else if($type == "GET_PATH")
		output($path);
	else if($type == "DEL")
		del($path);
}
?>