<?php
 // $Id: eurotv.collector.module.php,v 1.1 2002/02/09 17:45:23 waldb Exp $

if (!defined("__EUROTV_COLLECTOR_MODULE_PHP__")) {

define ('__EUROTV_COLLECTOR_MODULE_PHP__', true);

class eurotvCollector extends collectorModule {

	var $MODULE_NAME = "EuroTV Collector";
	var $MODULE_VERSION = "0.1";

	function eurotvCollector () {
		$this->collectorModule();
	} // end constructor eurotvCollector

	function view () {
		foreach ($GLOBALS as $k => $v) global $$k;

		$fd = fopen("http://www.eurotv.com/scripts/eutvprog.cfm","r");
		if ($fd) {

			print_header_open();
			print_title ("Eurotv");
			print_header_close();
	
			print "Deleting station and program settings.<br>\n";
			$collectorid = getcollectorid("eurotv");
			$query = "DELETE FROM station WHERE collectorid='".addslashes($collectorid)."'";
			$sql->query($query);
			$query = "DELETE FROM program";
			$sql->query($query);
	
			echo "<p>Scanning eurotv...<br>\n";
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
				$lijn = fgets($fd,1024);
				if (preg_match("/A HREF=\"\/sl.*\/a/",$lijn)) {
					$lijn = preg_replace("//", " ", $lijn);
					$lijn = preg_replace("/\n/", " ", $lijn);
					//(/HREF=\"(.*)?\"\>(.*)\<\/a\>/)
					$lijn = preg_replace("/.*HREF=\"\/(.*)\.htm\"\>(.*)?\<\/a\>.*/","\\1##\\2",$lijn);
					list($url,$name) = split("##",$lijn);
					$url = preg_replace("/^sl/","",$url);
					$query = "INSERT into station values ('NULL','$name','2','$url','$name')";
					$result = $sql->query($query);
			 		$sid = $sql->last_record($result);    
					print "
					<TR>
						<TD>".prepare($name)."</TD>
						<TD>
							<input type=CHECKBOX NAME=\"T".htmlentities($sid).
							"\" VALUE=\"1\" CHECKED>
						</TD>
					</TR>
					";
					//print $lijn;
					$i++;
				} // end if matches link
			} // end while
			fclose($fd);
			print "</table>\n";
			print "<p><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Choose\"></p>\n";
			print "</form>\n";
			print_page_close();
		} // end if fd
	} // end function eurotvCollector->view

} // end class eurotvCollector

register_module("eurotvCollector");

} // end if not defined

?>
