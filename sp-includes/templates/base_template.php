<?php 

abstract class sp_base_template
{
	public function render_chunk($iChunkName, $iData)
	{
		// @todo : add a check with a log for the user
		sp_StaticRegister::push_object("tmpl", $this);
		$this -> $iChunkName($iData);
		sp_StaticRegister::pop_object("tmpl");
	}
	
	/**** MAIN TEMPLATE ****/
	public function main($iData) { 
?><!DOCTYPE html>
<html>
<head>
<?php sp_insert_chunk("head", $iData); ?>
</head>
<body>
<div id="body">
<div id="header">
<div id="banner"><?php sp_insert_chunk("banner", $iData);?></div>
<div id="menu"><?php sp_insert_chunk("menu", $iData["menu"]);?></div>
</div>

<div id="content">
<?php sp_insert_chunk("content", $iData);?>
</div>

<div id="footer"><?php sp_insert_chunk("footer", $iData);?></div>
</div>
</body>
</html>
<?php
	}
		
	/**** HTTP HEADER ****/
	public function head($iData)
	{
?><title><?php echo(sp_config_value("title"));?></title>
<link href="<?php echo(sp_resource_url("web-data/styles/style.css"))?>" rel="stylesheet" type="text/css" /> <?php
	}
	
	
	/**** BANNER ****/
	public function banner($iData) {}
	
	
	/**** MENU ****/
	public function menu($iData)
	{
		if(null != $iData)
		{
			echo("\n<ul id=\"nav\">\n");
			$this -> print_menu($iData);
			echo("</ul>\n");
		}
	}
	
	private function print_menu(&$current_tab,$iRecurseLevel = 3)
	{
		if($iRecurseLevel == 0) return;
		foreach($current_tab as $item)
		{
			echo("<li>");
			if(empty($item["link"]))
				echo("<a href=\"#\">".trim($item["name"])."</a>");
			else
				echo("<a href=\"".sp_url(trim($item["link"]))."\">".trim($item["name"])."</a>");
				
			if(count($item["children"]))
			{
				echo("\n<ul>\n");
				$this -> print_menu($item["children"], $iRecurseLevel - 1);
				echo("</ul>\n");
			}
				
			echo("<li>\n");
		}
	}
	
	/**** FOOTER ****/
	public function footer($iData) {}
}