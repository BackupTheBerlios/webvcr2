<?php
 // $Id: advalvas.php,v 1.1 2002/02/09 17:45:17 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

if ($SUBMIT == "Update") {	
	while(list($name,$value) = each($HTTP_GET_VARS))
	{
		if (preg_match("/^T/",$name)) {
			$sid = preg_replace("/T/","",$name);
			$query="update station set rname='$value' where sid='".addslashes($sid)."'";
			$sql->query($query);
		} // end if preg_match
	}

	print_header_open();
	print_title ("Advalvas");
	print_header_close();

	print "<p>Updated.</p>\n";
	print_page_close();
	
	exit;
} // end SUBMIT = "Update"

if ($SUBMIT == "Choose") {
	$collectorid = getcollectorid("advalvas");
	$query = "DELETE FROM station WHERE collectorid=$collectorid";
	$sql->query($query);
	$query=("delete from program");
	$sql->query($query);

    print_header_open();
	print_title ("Advalvas");
    print_header_close();

	print "<FORM ACTION=\"".page_name()."\" METHOD=POST>
		<table border=0 cellspacing=0 cellpadding=2>
		<tr bgcolor=\"#ccccff\">
			<td><B>Name</B></td>
			<td><B>Your Name in xawtv</B></td>
		</tr>
	";
	while(list($name,$value)=each($HTTP_GET_VARS)) {
		if ($value == 1) {
			$orgname = "?NL&".$name;
			$name = preg_replace("/_/"," ",$name);
			$name = preg_replace("/\|/","+",$name);
			$query = $sql->insert_query(
				"station",
				array(
					"sid"			=>	NULL,
					"sname"			=>	$name,
					"collectorid"	=>	"1",
					"suburl"		=>	$orgname,
					"rname"			=>	$name
				)
			);
			$result = $sql->query($query);
			$sid = $sql->last_record($result);
			print "<tr><td>$name</td><td><input type=text name=T$sid value=\"$name\"></td></tr>\n";	
		} // end if value = 1
		
	}
	print "</table>";
	print "<p><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Update\"></p>";
	print "</form>";
	print_page_close();   

	exit;
}

$fd = fopen("http://tv.advalvas.be/av2/","r");

if ($fd) {
	print_header_open();
	print_title ("Advalvas");
	print_header_close();
	
	echo "<p>scanning advalvas...<br>\n";
	echo "stations found: <br>\n";
	print "<form><table><tr><td>naam</td><td>add</td></tr>\n";
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

//print $blah
//cat advalvastv |perl -n -e 'if (/tv\.dll.*\>(.*)\<\/A.*/){print "$1\n";}'|less

?>
