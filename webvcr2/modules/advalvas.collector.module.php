<?php
 // $Id: advalvas.collector.module.php,v 1.1 2002/02/09 17:45:23 waldb Exp $

if (!defined("__ADVALVAS_COLLECTOR_MODULE_PHP__")) {

define ('__ADVALVAS_COLLECTOR_MODULE_PHP__', true);

class advalvasCollector extends collectorModule {

	var $MODULE_NAME = "Advalvas Collector";
	var $MODULE_VERSION = "0.1";

	function advalvasCollector () {
		$this->collectorModule();
	} // end constructor advalvasCollector

	function view () {
		foreach ($GLOBALS AS $k => $v) global $$k;

		$fd = fopen("http://tv.advalvas.be/av2/","r");

		if ($fd) {
			print_header_open();
			print_title ("Advalvas");
			print_header_close();
			
			echo "<p>Scanning advalvas...<br>\n";
			echo "Stations found: <br>\n";
			print "
			<FORM ACTION=\"".$this->page_name."\" METHOD=POST>
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
			<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=2>
				<TR BGCOLOR=\"#ccccff\">
					<TD>Name</TD>
					<TD>Add</TD>
				</TR>
			";
			$i = 0;
			while (!feof($fd)) {
				$lijn = fgets($fd,1024);
				if (preg_match("/tv\.dll\?/",$lijn)) {
					$lijn = preg_replace("//"," ",$lijn);
					$lijn = preg_replace("/\n/"," ",$lijn);
					#<TD><A href="scripts/tv.dll?Show?NL&amp;BBC2">BBC2</A></TD>
					$lijn = preg_replace("/.*tv\.dll.*\;(.*?)\"\>(.*)\<\/A.*/","\\1##\\2",$lijn);
					list($url,$naam) = split("##",$lijn);
					print "<tr><td>$naam</td><td><input type=checkbox name=$url value=1 checked></td></tr>\n";
					#print $lijn;
					$i++;
				}
			}
			fclose($fd);
			print "</table>";
			print "<p><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Choose\"></p>";
			print "</form>";
			print_page_close();
		} // end if fd
	} // end function advalvasCollector->view
	
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
		
        while(list($name,$value)=each($HTTP_POST_VARS)) {
                if ($value==1) {
                $suburl = "?NL&".$name;
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
	} // end function advalvasCollector->choose

} // end class advalvasCollector

register_module ("advalvasCollector");

} // end if not defined

?>
