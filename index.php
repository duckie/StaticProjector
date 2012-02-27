<?php

require_once(__DIR__ . "/sp-includes/StaticProjector.php");
$instance = new sp_StaticProjector(__DIR__, isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "");
$instance -> run();

?>