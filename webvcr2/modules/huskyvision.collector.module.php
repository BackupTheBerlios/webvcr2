<?php
 // $Id: huskyvision.collector.module.php,v 1.1 2002/02/09 17:45:23 waldb Exp $
 // $Author: waldb $

if (!defined("__HUSKYVISION_COLLECTOR_MODULE_PHP__")) {

define ('__HUSKYVISION_COLLECTOR_MODULE_PHP__', true);

class huskyvisionCollector extends collectorModule {

	var $MODULE_NAME = "HuskyVision Collector";
	var $MODULE_VERSION = "0.1";

	function huskyvisionCollector () {
		$this->collectorModule();
	} // end constructor huskyvisionCollector

	function view () {
		foreach ($GLOBALS as $k => $v) global $$k;

		$fd = fopen("http://www.sp.uconn.edu/~wwwnews/huskyvision","r");
		if ($fd) {

			print_header_open();
			print_title ("HuskyVision");
			print_header_close();
	
			print "Deleting station and program settings.<br>\n";
			$collectorid = getcollectorid("huskyvision");
			$query = "DELETE FROM station WHERE collectorid='".addslashes($collectorid)."'";
			$sql->query($query);
			$query = "DELETE FROM program";
			$sql->query($query);
	
			echo "<p>Scanning HuskyVision...<br>\n";
			echo "Stations found: <br>\n";
			print "
				<FORM ACTION=\"".$this->page_name."\" METHOD=POST>
				<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
				<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
					<TR BGCOLOR=\"#ccccff\">
						<TD><B>Name</B></TD>
						<TD><B>Add</B></TD>
					</TR>
			";
			$i = 0;
			while (!feof($fd)) {
				$lijn = fgets($fd, 1024);
				list ($channel_id, $channel_name) = explode (",", $lijn); 
				if (!empty($channel_name)) {
					$query = $sql->insert_query (
						"station",
						array (
							"sid"			=>	NULL,
							"sname"			=>	$channel_name,
							"collectorid"	=>	"2",
							"rname"			=>	$channel_name,
							"channel"		=>	$channel_id
						)
					);
					$result = $sql->query($query);
				 	$sid = $sql->last_record($result);    
					print "
						<TR>
							<TD>".prepare($channel_name)."</TD>
							<TD>
								<INPUT TYPE=CHECKBOX NAME=\"T".htmlentities($sid).
								"\" VALUE=\"1\" CHECKED>
								<INPUT TYPE=HIDDEN NAME=\"N".htmlentities($sid).
								"\" VALUE=\"".prepare($channel_name)."\">
								<INPUT TYPE=HIDDEN NAME=\"C".htmlentities($sid).
								"\" VALUE=\"".prepare($channel_id)."\">
							</TD>
						</TR>
					";
					$i++;
				} // end checking for valid name
			} // end while
			fclose($fd);
			print "</table>\n";
			print "<p><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Choose\"></p>\n";
			print "</form>\n";
			print_page_close();
		} // end if fd
	} // end function huskyvisionCollector->view

} // end class huskyvisionCollector

register_module("huskyvisionCollector");

} // end if not defined

?>
