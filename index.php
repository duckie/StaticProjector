<?php
require_once(__DIR__ . "/sp-includes/StaticProjector.php");
$https_on = isset($_SERVER["HTTPS"]) ? ("on" == $_SERVER["HTTPS"]) : false;
$base_url = ($https_on ? "https://" : "http://").$_SERVER["HTTP_HOST"].dirname($_SERVER["SCRIPT_NAME"]);
$instance = new sp_StaticProjector(__DIR__, $base_url, isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "");
$instance -> run();
?>