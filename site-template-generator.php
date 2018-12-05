<?php

$ARGS_MAP = [
	'VALID_ARGS' => [
		'n' => 'name',
		'name' => 'name',
		'p' => 'path',
		'path' => 'path',
		'd' => 'desc',
		'desc' => 'desc',
		'description' => 'desc',
		'a' => 'auth',
		'auth' => 'auth',
		'author' => 'auth',
		'r' => 'inc-readme',
		'inc-readme' => 'inc-readme',
		'readme' => 'inc-readme',
		'o' => 'output',
		'output' => 'output',
	],
	'ARG_DATA' => [
		'script_path' => 0,
		'name' => 1,
		'path' => 2,
		'desc' => 3,
		'auth' => 4,
		'inc-readme' => 5,
		'output' => 6
	]
];

// Alias for .bashrc: alias sitegen="php /path/to/scripts/script.php" 
/*
php /c/php/scripts/site-template-gen.php "Test Project" /c/Users/dunhaj/github/v2/ "My new project" "Josiah Dunham"
sitegen -name "My New Project" -path "/c/Users/dunhaj/github/v2/" -desc "My new project" -auth "Josiah Dunham" -inc-readme false
*/

$ARG_ARRAY = parseArgs($argv);
print_r($ARG_ARRAY);

$project_name = '';
$project_folder_name = '';
$project_description = '';
$project_author = '';
$screen_output = 'MINIMAL';
$createReadMe = 1;

if(isset($ARG_ARRAY[1]) && $ARG_ARRAY[1] != ''){
	$project_folder_name = strtolower(str_replace(' ', '-', $ARG_ARRAY[1]));
	$project_name = $ARG_ARRAY[1];
} 

else {
	die(chr(10) . 'Error creating new project: No project name defined.');
}


if(isset($ARG_ARRAY[2]) && $ARG_ARRAY[2] != '') {
	$site_root = (realpath($ARG_ARRAY[2]) == true) ? realpath($ARG_ARRAY[2]) : getcwd();
}
else {
	$site_root = getcwd();
}

if(isset($ARG_ARRAY[3])) {
	$project_description = $ARG_ARRAY[3];
}

if(isset($ARG_ARRAY[4])) {
	$project_author = $ARG_ARRAY[4];
}

if(isset($ARG_ARRAY[5])) {
	$createReadMe = $ARG_ARRAY[5];
	
	if($createReadMe === 'false') {
		$createReadMe = 0;
	}
}

if(isset($ARG_ARRAY[6])) {
	switch($ARG_ARRAY[6]) {
		case 'n':
		case 'none':
			$screen_output = 'NONE';
			break;
		case 'v':
		case 'verbose':
			$screen_output = 'VERBOSE';
			break;
		case 'm':
		case 'min':
		case 'minimal':
		default:
			$screen_output = 'MINIMAL';
			break;
	}
}


define('PROJECT_NAME', $project_name);
define('PROJECT_FOLDER', $project_folder_name);
define('PROJECT_DESCRIPTION', $project_description);
define('PROJECT_AUTHOR', $project_author);

define('PROJECT_LOCATION', $site_root.'\\'.$project_folder_name.'\\');

define('GENERATE_README', $createReadMe);
define('SCREEN_OUTPUT', $screen_output);

print_r(GENERATE_README .chr(10));
displayProjectInfo();


if (file_exists(PROJECT_LOCATION)) {
	die(chr(10).'Error creating project: '.PROJECT_LOCATION.' already exists'.chr(10));
}
else {
	if(SCREEN_OUTPUT != 'NONE') {	
		output(chr(10)."Creating project \"". PROJECT_NAME. "\" in " .PROJECT_LOCATION. " ...");
		output('Done!');
	}
	
}
//linebreak();
createProjectDirectory();
//linebreak();
createDirectories();
//linebreak();
createFiles();

if(GENERATE_README) {
	generateREADME();
}



function run_command($s) {
	
	shell_exec($s);
}

function createDirectory($s) {
	$cmd = 'mkdir ' . $s;
	
	switch(SCREEN_OUTPUT) {
		case 'NONE':
			break;
		case 'VERBOSE':
			output('Running command: ' . $cmd);	
		case 'MINIMAL':
			output('Creating new directory (' . $s . ') ...');
	}
	
	run_command($cmd);
	output('Done!');
}

function changePwd($s) {
	output('Changing working directory to: ' . $s . ' ...');
	chdir($s);
}

function createFile($s) {
	$cmd = 'touch "' . $s .'"';
	
	switch(SCREEN_OUTPUT) {
		case 'NONE':
			break;
		case 'VERBOSE':
			output('Running command: ' . $cmd);	
		case 'MINIMAL':
			output('Creating new file (' . $s . ')...');
	}
	
	run_command($cmd);
	output('Done!');
}


function displayProjectInfo() {
	if(SCREEN_OUTPUT != 'NONE') {
		output('PROJECT_NAME: ' . PROJECT_NAME);
		output('PROJECT_LOCATION: ' . PROJECT_LOCATION);
		output('PROJECT_DESCRIPTION: ' . PROJECT_DESCRIPTION);
		output('PROJECT_AUTHOR: ' . PROJECT_AUTHOR);		
	}
}

function createProjectDirectory() {
	createDirectory(PROJECT_LOCATION);
}

function createDirectories() {
	$directories = [
		'lib\\',
		'lib\\css\\',
		'lib\\js\\',
		'lib\\img\\'
	];
	
	foreach($directories as $d) {
		createDirectory(PROJECT_LOCATION . $d);
	}
}

function createFiles() {
	$files = [
		'lib\\css\\styles.css',
		'lib\\js\\site.js',
		'index.php'
	];
	
	foreach($files as $f) {
		createFile(PROJECT_LOCATION . $f);
	}
	
	$indexPHPLines = [
		'<?php ?>',
		'<h1>' . PROJECT_NAME . '</h1>',
		'<h2>Welcome to your New Project!</h2>'
	];
	
	$indexPHP = '';
	foreach($indexPHPLines as $line) {
		$indexPHP .= $line . chr(10);
	}
	
	file_put_contents(PROJECT_LOCATION . '\\index.php', $indexPHP);
}

function generateREADME() {
	echo 'generateReadMeFunction' .chr(10);
	$readmeLines = [
		'# '. PROJECT_NAME,
		'### Date: ' . date("m/d/Y"),
		'### Author: ' . PROJECT_AUTHOR,
		'### Description: ' . PROJECT_DESCRIPTION,
	];
	
	$readme = '';
	foreach($readmeLines as $line) {
		$readme .= $line . chr(10);
	}
	
	createFile(PROJECT_LOCATION . 'README.md');
	file_put_contents(PROJECT_LOCATION . "\\README.md", $readme);
}

function parseArgs($args) {
	$error = false;
	
	$argsMap = $GLOBALS['ARGS_MAP'];
	$argArray = [];
	
	$argNumber = 0;
	
	for($a = 0; $a < count($args); $a++) {
		$arg = $args[$a]; //$arg: -n
		
		$isKey = (substr($arg, 0, 1)) == '-';
		
		if($isKey) {
			$key = substr($arg, 1, strlen($arg) - 1); // $key: n
			
			// If $argsMap['VALID_ARGS']['n'] exists
			
			if(array_key_exists($key, $argsMap['VALID_ARGS'])) {
				$argName = $argsMap['VALID_ARGS'][$key]; // $argName: name
				$argIdx = $argsMap['ARG_DATA'][$argName]; //$argIdx ($argsMap['ARG_DATA']['name']): 1
				$argValue = $args[$a + 1];
				$a++;
			}
			else {
				$error = true;
				$errorCode = '1';
				break;
			}
		}
		else {
			$argIdx = $argNumber;
			$argValue = $arg;
		}
		
		if(!isset($argArray[$argIdx])) {
			$argArray[$argIdx] = $argValue;
		}
		else {
			$error = true;
			$errorCode = '2';
			break;
		}
		
		$argNumber++;
	}
	
	if($error) {
		die('Error (Code: ' . $errorCode . ') - Invalid argument list supplied.' . chr(10));
	}
	
	$argArray = prepareArgs($argArray);
	
	return $argArray;
}

function prepareArgs($args) {
	$argLen = count($GLOBALS['ARGS_MAP']['ARG_DATA']);
	
	for($x = 0; $x < $argLen; $x++) {
		if(!isset($args[$x])) {
			$args[$x] = null;
		}
	}
	
	ksort($args);	
	return $args;
}

function output($s = '') {
	echo $s . chr(10);
}

function linebreak() {
	output();
}

?>