<?php class default_template extends sp_base_template { 
/****************************************************/	

public function main($iData) { ?>
<html>
<head>
<?php sp_insert_chunk("head", $iData); ?>
</head>
<body>
<?php sp_insert_chunk("body", $iData); ?>
</body>
</html>
<?php }

public function head($iData) { ?>
<title><?php echo(sp_config_value("title"));?></title>
<?php }


public function body($iData) { ?>
<div><?php echo($iData); ?></div>
<?php }

/****************************************************/
}?>