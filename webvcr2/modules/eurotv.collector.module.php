<?php
 // $Id: eurotv.collector.module.php,v 1.2 2002/02/12 23:27:44 waldb Exp $

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
			/*	
			print "Deleting station and program settings.<br>\n";
			$collectorid = getcollectorid("eurotv");
			$query = "DELETE FROM station WHERE collectorid='".addslashes($collectorid)."'";
			$sql->query($query);
			$query = "DELETE FROM program";
			$sql->query($query);
			*/
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
					print "<tr><td>$name</td><td><input type=checkbox name=$url value=1></td></tr>\n";
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

	function choose () {
		while(list($k, $v) = each($GLOBALS)) global $$k;

		$collectorid = getcollectorid("eurotv");
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
		
        while(list($name,$value)=each($HTTP_POST_VARS)) {
                if ($value==1) {
                $suburl = $name;
                $channel_name = preg_replace("/_/"," ",$name);
                $channel_name = preg_replace("/\|/","+",$channel_name);
				$query=$sql->insert_query(
					"station",
					array(
						"sname"	=> $this->transformName($channel_name),
						"collectorid" => $collectorid,
						"suburl" => $suburl,
						"rname" => $this->transformName($channel_name),
						"channel" => "0"
						)
				);
				$result = $sql->query($query);
				$sid = $sql->last_record($result);
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
                }
        }
		print "</TABLE>\n";
		print "<P><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Update\"></P>\n";
		print "</FORM>\n";
		print_page_close();
		
		exit;
	} // end function eurotv->choose


} // end class eurotvCollector

register_module("eurotvCollector");

} // end if not defined

?>
