<?php
 // $Id: module_loader.php,v 1.1 2002/02/09 17:45:18 waldb Exp $
 // $Author: waldb $

include ("lib/functions.php");
include ("lib/module.php");

// module loaders
include "lib/module_collector.php";

// get list of modules
$module_list = new module_list ("WebVCR");

// check for module
if (!$module_list->check_for($module)) {
	DIE("module \"$module\" not found");
} // end of checking for module

// load module
execute_module ($module);

?>
