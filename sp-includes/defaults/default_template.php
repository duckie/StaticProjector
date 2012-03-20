<?php class default_template extends sp_base_template { 
/****************************************************/	

/**** MAIN TEMPLATE ****/
public function main($iData) { ?>
<!DOCTYPE html>
<html>
<head>
<?php sp_insert_chunk("head", $iData); ?>
</head>
<body>
<div id="header">
	<div id="banner"><?php sp_insert_chunk("banner", $iData);?></div>
	<div id="menu"><?php sp_insert_chunk("menu", $iData);?></div>
</div>

<div id="content"><?php sp_insert_chunk("content", $iData);?></div>

<div id="footer"><?php sp_insert_chunk("footer", $iData);?></div>
</body>
</html>
<?php }




/**** HTTP HEADER ****/
public function head($iData) { ?>
<title><?php echo(sp_config_value("title"));?></title>
<link href="web-data/styles/style.css" rel="stylesheet" type="text/css" />
<?php }


/**** BANNER ****/
public function banner($iData) { ?> <!--  Empty --> <?php }


/**** MENU ****/
public function menu($iData) { ?> <!--  Empty --> <?php }


/**** CONTENT ****/
public function content($iData) { ?>

<?php if("markdown" == $iData["type"]):?>
<?php echo(sp_markdown($iData["content"])); ?>
<?php endif;?>

<?php }




/**** FOOTER ****/
public function footer($iData) { ?> <!--  Empty --> <?php }

/****************************************************/
}?>