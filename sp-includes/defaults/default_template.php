<?php class default_template extends sp_base_template { 

public function main($iData) {
	parent::main($iData);
}
	
public function head($iData) {
	parent::head($iData);
}
	
public function banner($iData) {
	parent::banner($iData);
}
	
public function menu($iData) {
	parent::menu($iData);
}
	
public function content($iData) 
{
?>
<?php if("markdown" == $iData["type"]):?>
<?php echo(sp_markdown($iData["content"])); ?>
<?php else:?>
Hello guys !
<?php endif;?>
<?php 
}

public function footer($iData) {
	parent::footer($iData);
}


}?>