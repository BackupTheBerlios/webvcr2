<?php
 // $Id: module_collector.php,v 1.1 2002/02/09 17:45:23 waldb Exp $
 // $Author: waldb $
 // collector module prototype

if (!defined('__LIB_MODULE_COLLECTOR_PHP__')) {

include_once ("lib/module.php");
define ('__LIB_MODULE_COLLECTOR_PHP__', true);

class collectorModule extends webvcrModule {

	var $CATEGORY_NAME = "Collector";
	var $CATEGORY_VERSION = "1.0";

	function collectorModule () {
		$this->webvcrModule();
	} // end constructor collectorModule

	function execute () {
		global $SUBMIT;
		switch ($SUBMIT) {
			case "Update":
				$this->update();
				break;
			case "Choose":
				$this->choose();
				break;
			default:
				$this->view();
				break;
		} // end switch
	} // end function execute

	function update () {
		foreach ($GLOBALS as $k => $v) global $$k;

 		while(list($name,$value) = each($HTTP_GET_VARS)) {
			if (preg_match("/^T/", $name)) {
				$sid = preg_replace("/^T/", "", $name);
	//"update station set rname='$value' where sid='".addslashes($sid)."'";
				$query = $sql->update_query (
					"station",
					array (
						"rname"		=>	$value,
						"channel"	=>	${"C".$sid}
					),
					array (
						"sid"	=>	$sid
					)
				);
				$sql->query($query);
			} // end if preg_match
		} // end looping through variables

		print_header_open();
		print_title ($this->MODULE_NAME);
		print_header_close();

		print "<p>Updated.</p>\n";
		print_page_close();
	
		exit;
	} // end function collectorModule->update

	function choose () {
		while(list($k, $v) = each($GLOBALS)) global $$k;

		$collectorid = getcollectorid("advalvas");
		$query = "DELETE FROM station ".
			"WHERE collectorid='".addslashes($collectorid)."'";
		$sql->query($query);
		$query = "DELETE FROM program";
		$sql->query($query);

    	print_header_open();
		print_title ("Choose Stations for ".$this->MODULE_NAME);
		print_header_close();

		print "
			<FORM ACTION=\"".page_name()."\" METHOD=POST>
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
			<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
			<TR BGCOLOR=\"#ccccff\">
				<TD><B>Original Name</B></TD>
				<TD><B>xawtv Name</B></TD>
				<TD><B>Channel</B></TD>
			</TR>
		";

		while(list($name, $value) = each($HTTP_POST_VARS)) {
			if (preg_match("/^T/", $name)) {
				// derive sid from this...
				$sid = preg_replace("/^T/", "", $name);

				// grab all sid-related variables
				$channel_name = ${"N".$sid};
				$channel_number = ${"C".$sid};

				// create actual insert query
				$query = $sql->insert_query(
					"station",
					array(
						"sname"			=>	$this->transformName($channel_name),
						"collectorid"	=>	"1",
						"suburl"		=>	$this->transformURL($channel_name),
						"rname"			=>	$this->transformName($channel_name),
						"channel"		=>	$channel_number
					)
				);
				$result = $sql->query($query);
				$old_sid = $sid;
				$sid = $sql->last_record($result);
				${"C".$sid} = ${"C".$old_sid}; 
				global ${"C".$sid};
				print "
				<TR>
					<TD>".prepare($channel_name)."</TD>
					<TD>
						<INPUT TYPE=TEXT NAME=\"T".htmlentities($sid)."\"
						 VALUE=\"".prepare($channel_name)."\">
					</TD>
					<TD>".html_form::text_widget("C".htmlentities($sid))."</TD>
				</TR>
				";	
			} // end if value = 1
		}
		print "</TABLE>\n";
		print "<P><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Update\"></P>\n";
		print "</FORM>\n";
		print_page_close();   

		exit;
	} // end function collectorModule->choose

	function transformName ($original) {
		return $original; // stub returns original
	} // end function collectorModule->transformName

	function transformURL ($original) {
		return $original; // stub returns original
	} // end function collectorModule->transformURL

	function view () {
		// STUB
	} // end function collectorModule->view

} // end class collectorModule

} // end if not defined

?>
