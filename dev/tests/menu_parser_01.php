<?php

class sp_menu_parser_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$dump = $this -> create_ref_to_check();
		$menu = sp_ArrayUtils::parse_menu($iParameters["file"]);
		sp_ArrayUtils::store_array($menu,$dump);
		return true;
	}
}