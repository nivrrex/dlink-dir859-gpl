<?php
/*
This is the PHP file which can get all .js file (language) and transform them to csv.

*/


// Configure variables.
$_PATH_PHPEXCEL = "./PHPExcel_1.8.0_doc";
$localization_dir = 'localization/';
$language_ary = array("zh-tw.js", "zh-cn.js", "ru-ru.js","de-de.js", "es-es.js","fr-fr.js","it-it.js","ko-kr.js","pt-br.js");
$xls_filename = "res.xlsx";

/* handle one .js file */
function handle_language($lang_name, $lang)
{
	global $very_large_array;
	echo "Starting processing $lang_name...";
	//var_dump($lang);

	foreach ($lang as $row)
	{
		if (strstr($row, "//") || strstr($row, "{") || strstr($row, "}"))	//skip some lines.
		{ continue; }
		$oneline_ary = explode(":",$row);
		if (count($oneline_ary) != 2)	// Something error in the line. needs to skip it.
		{ continue; }

		$ary_index = trim($oneline_ary[0], " \t\n\r\0\x0B\"");
		$ary_value = trim($oneline_ary[1]," \t\n\r\0\x0B,");	// remove the last ","
		$ary_value = trim($ary_value, " \t\n\r\0\x0B\"");
		$ary_index = stripslashes($ary_index);
		$ary_value = stripslashes($ary_value);

		$very_large_array[$ary_index][$lang_name] = $ary_value;
	}

	echo " done.\n";

}


/* Write the very large array to .csv file */
function write2file($filename)
{
	global $very_large_array, $language_ary;
	$excel_col_index = array("A","B","C","D","E","F","G","H","I","J","K","L", "M","N","O","P","Q");	// support 17 columns now.

	echo "Starting prepare the xlsx file...\n";

	$objPHPExcel = new PHPExcel();

	for ($lang_index=0;$lang_index<count($language_ary);$lang_index++)	// set the 1st row.
	{
		$col_index = sprintf("%s%d",$excel_col_index[$lang_index+1], 1);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_index, $language_ary[$lang_index]);

		// Set format
		$objPHPExcel->getActiveSheet()->getColumnDimension($excel_col_index[$lang_index+1])->setWidth(50);
	}
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(50);

	$str_counter = 0;
	foreach ($very_large_array as $str_index => $lang_ary)
	{
		$col_index = sprintf("%s%d","A", $str_counter + 2);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_index,$str_index);	// write AX
		for ($lang_index=0;$lang_index<count($language_ary);$lang_index++)
		{
			$lang_name = $language_ary[$lang_index];

			if (isset($lang_ary[$lang_name]))
			{
				$value = $lang_ary[$lang_name];
				$col_index = sprintf("%s%d",$excel_col_index[$lang_index + 1], $str_counter + 2);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_index, $value);
			}
		}
		$str_counter ++;
	}

	//Set format.
	$col_index = sprintf("A1:%s%d",$excel_col_index[count($language_ary)], $str_counter + 1);
	$objPHPExcel->getActiveSheet()->getStyle($col_index)->getAlignment()->setWrapText(true);	//wrap text.
	$objPHPExcel->getActiveSheet()->getStyle($col_index)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$objPHPExcel->getActiveSheet()->getStyle($col_index)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	//Freeze Pane
	$objPHPExcel->getActiveSheet()->freezePane('B2');

	echo "Starting write to $filename...";

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save($filename);

	echo " done.\n";
}

// ---------------------------------------- Start Here. ----------------------------------------

if (!is_file($_PATH_PHPEXCEL.'/Classes/PHPExcel.php'))
{
	echo "PHPExcel is not installed. Please download it from https://phpexcel.codeplex.com/\n";
	exit (1);
}

require_once $_PATH_PHPEXCEL.'/Classes/PHPExcel.php';
include $_PATH_PHPEXCEL.'/Classes/PHPExcel/IOFactory.php';

date_default_timezone_set('Asia/Taipei');

// Check the excel file is exist or not.
if (file_exists($xls_filename) == TRUE)
{
	if (unlink($xls_filename) == FALSE)
	{
		echo "Cannot delete $csv_filename\n";
		exit ;
	}
}

// Open the .js folder.

$very_large_array = array();
//$language_index = 1;

foreach ($language_ary as $language_value)
{
	// read the js file to an array.
	$filename = $localization_dir.$language_value;
	if (!file_exists($filename))
	{
		echo "cannot open ".$filename."\n";
		exit(1);
	}
	$lang = file($localization_dir.$language_value, FILE_SKIP_EMPTY_LINES);
	handle_language($language_value, $lang);
}

write2file($xls_filename);


//show memory usage.
function convert($size)
 {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 }
echo "Momory usage: ".convert(memory_get_usage(true)); // 123 kb

?>
