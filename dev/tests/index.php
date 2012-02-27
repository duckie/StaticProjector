<?php 

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


RunAndPrintTest("file_reader_01", array("ref" => "file_reader_dump_01.txt","repo" => __DIR__."/data/repository1"));