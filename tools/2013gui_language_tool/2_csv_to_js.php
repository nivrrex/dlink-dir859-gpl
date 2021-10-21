<?php
/*
Convert the .csv to *.js
*/

//Configure.
$_PATH_PHPEXCEL = "./PHPExcel_1.8.0_doc";
$output_dir = "language";
$xls_filename = "res.xlsx";

function generate_js_header()
{
	$var = "";
	$var = $var."//This java script is generated automatically at ".date("c").".\r\n";
	$var = $var."{\r\n";
	return $var;
}

function generate_js_footer()
{
	$var = "";
	$var = $var."}\r\n";

	return $var;
}

function write_js($file_path, $str_ary)
{
	$handle = fopen($file_path, "w");
	if ($handle == false)
	{
		echo __FILE__.":".__LINE__.":Cannot write $output_dir$current_language\n";
		exit;
	}
	// Write the UTF-8 bom to identify this file needs process with UTF-8
	$utf8_with_bom = chr(239) . chr(187) . chr(191);
	fwrite($handle,$utf8_with_bom);

	fwrite($handle, generate_js_header());

	foreach ($str_ary as $str_index => $str_value)
	{
		if (!isset($str_index) || $str_index == "") { continue; }
		if (!isset($str_value) || $str_value == "") { continue; }
		
		$str = "";
		$str = $str."\t".'"'.addcslashes($str_index,'"').'"'.":".'"'.addcslashes($str_value,'"').'",'."\r\n";

		fwrite($handle, $str);
	}
	// Remove the last ,
	fseek($handle, -3, SEEK_END );
	fwrite($handle, "\r\n");

	fwrite($handle, generate_js_footer());
	fclose($handle);
}

function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
 }

// ---------------------------------------- Start Here.----------------------------------------

require_once $_PATH_PHPEXCEL.'/Classes/PHPExcel.php';
include $_PATH_PHPEXCEL.'/Classes/PHPExcel/IOFactory.php';

date_default_timezone_set('Asia/Taipei');

if (is_dir($output_dir))
{
	echo "The output directory $output_dir existed. Delete it...";
	rrmdir($output_dir);
	echo "done.\r\n";
//	exit (1);
}
mkdir ($output_dir);

if (is_file($xls_filename) == false)
{
	echo "Error: Open ".$xls_filename." failed\n";
	exit(1);
}

echo "Loading ".$xls_filename."...";
$objPHPExcel = PHPExcel_IOFactory::load($xls_filename);
$array_tmp = $objPHPExcel->getActiveSheet()->toArray();
unset ($objPHPExcel);
echo "done.\n";


$lang_ary = $array_tmp[0];
array_shift($lang_ary);
array_shift($array_tmp);

foreach ($lang_ary as $lang_i => $lang_name)
{
	echo "Processing ".$lang_name."...";
	foreach ($array_tmp as $str_i => $str_ary_tmp)
	{
		$str_array[$str_ary_tmp[0]] = $str_ary_tmp[$lang_i+1];
	}

	write_js($output_dir."/".$lang_name, $str_array);
//	var_dump($str_array);
	echo "done.\n";

}

?>
