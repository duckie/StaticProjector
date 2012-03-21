<?php class default_template extends sp_base_template { 

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


}?>