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

	abstract public function main($iData);
}