<?php

class sp_command_parser_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$sp = new sp_StaticProjector($iParameters["repo"], "");
		$cmd_parser = new sp_Commands($sp);
		
		if($cmd_parser -> parse_request(""))
			$this -> write($cmd_parser -> get_template().json_encode($cmd_parser -> get_command_data())."\n");
		
		if($cmd_parser -> parse_request("/"))
			$this -> write($cmd_parser -> get_template().json_encode($cmd_parser -> get_command_data())."\n");
		
		if($cmd_parser -> parse_request("/images/roger"))
			$this -> write($cmd_parser -> get_template().json_encode($cmd_parser -> get_command_data())."\n");
		
		if($cmd_parser -> parse_request("/gal/roger"))
			$this -> write($cmd_parser -> get_template().json_encode($cmd_parser -> get_command_data())."\n");
		
		return true;
	}
}