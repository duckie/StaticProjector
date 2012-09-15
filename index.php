<?php
require_once('sp-includes/StaticProjector.php');
$https_on = isset($_SERVER['HTTPS']) ? ('on' === $_SERVER['HTTPS']) : false;
$base_url = ($https_on ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);
$instance = new sp_StaticProjector(__DIR__, __DIR__, $base_url, isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']:''));
try
{
	$instance -> run();
}
catch (ErrorException $e)
{
	echo($e -> getTraceAsString());
}

?>
