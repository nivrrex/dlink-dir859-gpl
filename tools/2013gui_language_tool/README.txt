This is a tool for 2013 GUI language. Author: huanyao_kang@alphanetworks.com

Prepare:
	1. Install the regular PHP in PC, not xmldbc PHP. The recommanded PHP for Windows is EasyPHP. It can be downloaded from "http://www.easyphp.org/". After installing, rebooting computer may be required.
	2. Copy rootfs\htdocs\web\js\localization to the directory of the php file.
	3. Download PHPEXCEL from https://phpexcel.codeplex.com/ and unzip to the directory of the php file.

Capture all strings that call I18N function in *.html and *.js (Optional)
	1. Execute "php 0_scan_web.php" and it will scan all .html and .js files, and put the caputred string to "web/js/localization/0_index.js". 

Convert the all language js files and the scanned string to excel file
	1. Configure 1_js_to_csv.php.
		1.1 "language_ary" : modify the list to language which is needed to transfer.
		1.2 "localization_dir" : modify to the location of .js folder.
		1.3 _PATH_PHPEXCEL : the folder PHPEXCEL installed.
		1.4 xls_filename : filename of the excel file. It will be overwrited when existed.
	2. Execute the PHP file.
		2.1 Execute "php 1_js_to_csv.php" and it will scan all .js file in "web/js/localization" and merge the language strings to "res.xlsx". 
		2.2 If the res.xlsx existed, it will be overwrited.
	3. Result.
	 3.1 Open the "res.xlsx" with Excel. Here is the all language strings. You may edit this excel file to change the translation in easy way.
	
Convert Excel to .js file
	1. Configure 2_csv_to_js.php.
		1.1 _PATH_PHPEXCEL : the folder PHPEXCEL installed.
		1.2 output_dir : folder of js files. the directory will be deleted when existed.
		1.3 xls_filename : filename of the excel file.
	2. Execute the PHP file.
		2.1 Execute "php 2_csv_to_js.php" and it will read xls_filename and output the language string to output_dir.
	3. Result.
		3.1 Open output_dir and view the result.
	
