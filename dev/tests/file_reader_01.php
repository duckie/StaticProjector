<?php

$status = false;
echo("Test begin <br />\n");
require_once(__DIR__."/../../sp-includes/StaticProjector.php");

class SimpleVisitor extends FileReaderVisitor
{
	public function __construct()
	{
		$this -> basedir = __DIR__."/testdata/repository1/";
	}
	
	public function process(FileInfo $iReader)
	{
		echo($iReader -> absolute_path);
		echo("<br />");
	}
}

$info1 = FileReader::get_file_info(__DIR__."/testdata/repository1/images/list1/image_01.jpg", true);
$test = $info1 -> as_array();

$vis = new SimpleVisitor();
$reader = new FileReader($vis);
$reader -> run();
$status = true;

echo("<br />\n Test ended with status ".($status?"OK":"FAILED")."<br />\n");