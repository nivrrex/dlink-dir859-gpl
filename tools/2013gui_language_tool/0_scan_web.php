<?php
// copy from http://stackoverflow.com/questions/3826963/php-list-all-files-in-directory
function directoryToArray($directory, $recursive=false) 
{
    $array_items = array();
    if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (is_dir($directory. "/" . $file)) {
                    if($recursive) {
                        $array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
                    }
                    $file = $directory . "/" . $file;
                    $array_items[] = preg_replace("/\/\//si", "/", $file);
                } else {
                    $file = $directory . "/" . $file;
                    $array_items[] = preg_replace("/\/\//si", "/", $file);
                }
            }
        }
        closedir($handle);
    }
    return $array_items;
}

function file_needed_to_phase($filepath)
{
	if (preg_match('/i18n[.]js$/', $filepath) == true)
	{
		return false;
	}
	$patterns = array('/[.]js$/', '/[.]html$/', '/[.]htm$/', '/[.]php$/');
	foreach ($patterns as &$pattern)
	{
		if (preg_match($pattern, $filepath) == true)
		{ return true; }
	}
	return false;
}

function getparameter($str)
{	
	$pattern = '/"(((\\\")|([^"]))+)"/'; 
	preg_match_all ($pattern, $str, $matches);
	return trim('"'.$matches[1][1].'"');
	//Get the parameter.
	/*$var = strstr($str, ",");
	$var = substr($var, 1);*/
	//return trim($var);
}

function add_variable_to_large_array($str, $filename)
{
	global $very_large_array, $var_file_path;
	if (substr($str,0,1) != '"')
	{
		echo "This might be a variable: [$str]\n";
		return ;
	}
	if (in_array($str, $very_large_array) === false)
	{
		$index = count($very_large_array);
		$very_large_array[$index] = $str;
		$var_file_path[$index] = $filename;
	}
}

function scan_file($filename)
{
	echo "Start parsing $filename...";
	$handle = fopen($filename, "r");
	if ($handle == false)
	{
		echo "[ERROR] cannot open $filename";
		exit(1);
	}
	while(false !== ($str = fgets($handle)))
	{
		$str = trim($str);

		$variable = preg_split  ('/I18N[(]{1}([^I]+[^1]+[^8]+[^N]+)[)]{1}/', $str, null, PREG_SPLIT_DELIM_CAPTURE);

		if (count($variable) == 1 && $variable[0] === $str)
		{
			continue;
		}

		for ($i=1;$i<count($variable);$i+=2)
		{
			$word = getparameter($variable[$i]);
			if ($word === null)
			{
				echo "Error happened ".__file__.":".__LINE__."\n";
			}
			add_variable_to_large_array($word, $filename);
		}
		
	}

	fclose($handle);
	echo "done.\n";
}

function generate_js_header()
{
	$var = "";
	$var = $var."//This java script is generated automatically.\r\n";
	$var = $var."{\r\n";
	
	return $var;
}

function generate_js_footer()
{
	$var = "";
//	$var = $var."//This java script is generate automatically.\r\n";
	$var = $var."}\r\n";
	
	return $var;
}

function write_to_js()
{
	global $very_large_array, $var_file_path;

	$localization_path = 'web/js/localization/0_index.js';	// This file will be created to be used in creating csv file.
	$handle = fopen($localization_path,"w");
	if ($handle == false)
	{
		echo __FILE__.":".__LINE__.":Cannot write $output_dir$current_language\n";
		exit;
	}

	$utf8_with_bom = chr(239) . chr(187) . chr(191);
	fwrite($handle,$utf8_with_bom);

	fwrite($handle, generate_js_header());

	for ($i=0;$i<count($very_large_array);$i++)
	{
		$index = $very_large_array[$i];
		$value = $var_file_path[$i];
		$str = "";
		$str = $str.$index.":\"".$value."\"";
		if ($i != count($very_large_array) -1 )
		{
			$str = $str.",";
		}
		$str = $str."\r\n";
		fwrite($handle, $str);
	}

	fwrite($handle, generate_js_footer());
	fclose($handle);
}

$web_dir = "web/";
if (!is_dir($web_dir))
{
	echo "[Error] The web dir is not exist";
	exit(1);
}

//declare the main array.
$very_large_array = array();
$var_file_path = array();

if (false)
{
	scan_file("web/Network.html");
	var_dump($very_large_array);
}

if (true)
{
	$file_list = directoryToArray($web_dir,true);
	foreach ($file_list as &$filepath)
	{
		if (!is_file($filepath))
		{ continue; }

		if (file_needed_to_phase($filepath) === false)
		{ continue; }
		
		
		scan_file($filepath);
	}

	write_to_js();
//	var_dump($very_large_array);
//	var_dump($var_file_path);
}
?>