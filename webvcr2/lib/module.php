<?php
 // $Id: module.php,v 1.1 2002/02/09 17:45:23 waldb Exp $
 // $Author: waldb $
 // module prototype for webvcr

if (!defined("__LIB_MODULE_PHP__")) {

define ('__LIB_MODULE_PHP__', true);

// class webvcrModule extends module
class webvcrModule extends module {

	// override variables
	var $PACKAGE_NAME = "WebVCR";
	var $PACKAGE_VERSION = VERSION;
	var $MODULE_AUTHOR = "jeff b (jeff@univrel.pr.uconn.edu)";
	var $MODULE_DESCRIPTION = "No description.";
	var $MODULE_VENDOR = "Stock Module";

	// all modules use this one loader
	var $page_name = "module_loader.php";

	// contructor method
	function webvcrModule () {
		// call parent constructor
		$this->module();
	} // end constructor webvcrModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module))
		{
			 trigger_error("No Module Defined", E_ERROR);
		}
		return true;
	} // end function check_vars

	// override header method
	function header ($nullvar = "") {
		// STUB
	} // end function header

	// override footer method
	function footer ($nullvar = "") {
		// STUB
	} // end function footer

} // end class webvcrModule

} // end if not defined

?>
