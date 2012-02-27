<?php 

require_once(__DIR__."/../../sp-includes/StaticProjector.php");
require_once(__DIR__."/test.php");

function RunTest($iName,$iParams)
{
	require_once(__DIR__."/".$iName.".php");
	$class = "sp_".$iName;
	$local_test = new $class();
	$success = $local_test -> run($iParams);
	return $success;
}

function RunAndPrintTest($iName,$iParams)
{
	echo($iName.": ");
	$success = RunTest($iName,$iParams);
	echo($success ? "OK" : "FAIL");
	echo("<br />");
}

$data_in = __DIR__."/data";
$data_out = __DIR__."/output";

RunAndPrintTest("file_reader_01", array("ref" => "file_reader_dump_01.txt","repo" => "$data_in/repository1"));
RunAndPrintTest("file_reader_02", array("ref" => "file_reader_dump_02.txt","dir" => "$data_in/repository1/images/list1"));
RunAndPrintTest("file_reader_03", array("src" => "$data_in/repository1","dst" => "$data_out/repository1"));