<?php
 // $Id: eurotv.php,v 1.1 2002/02/09 17:45:18 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

if ($SUBMIT == "Update") {	
	while(list($name,$value) = each($HTTP_GET_VARS)) {
		if (preg_match("/^T/",$name)) {
			$sid = preg_replace("/T/","",$name);
			$query = ("insert into station values('NULL','$value','2',' set rname='$value' where sid=$sid");
			$sql->query($query);
		} // end checking for match
	}

	print_header_open();
	print_title ("Eurotv");
	print_header_close();

	print "<p>updated.</p>";
	print_page_close();
	
	exit;
}

if ($SUBMIT == "Choose") {
    print_header_open();
	print_title ("Eurotv");
    print_header_close();


	print "
		<FORM ACTION=\"".$PHP_SELF."\" METHOD=POST>
		<TABLE>
			<tr>
				<td>Name</td>
				<td>Your name in xawtv</td>
			</tr>
	";
	while(list($name,$value)=each($HTTP_GET_VARS))
	{
		if ($value == 1) {
			$sid = preg_replace("/T/","",$name);
			$query = "SELECT sname FROM station WHERE sid='".addslashes($sid)."'";
			$result = $sql->query($query);
			while($blah = $sql->fetch_array($result)) {
				$name = $blah[0];
			}
			print "<tr><td>$name</td><td><input type=TEXT name=T$sid value=\"$name\"></td></tr>\n";	
		}
	}
	print "</table>\n";
	print "<p><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Update\"></p>\n";
	print "</form>\n";
	$collectorid = getcollectorid("eurotv");
	$query = "DELETE FROM station WHERE collectorid='".addslashes($collectorid)."'";
	$sql->query($query);
	print_page_close();   

	exit;
} // end if action is Choose

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
		<FORM ACTION=\"".$PHP_SELF."\" METHOD=POST>
		<TABLE BORDER=0 CELLSPACING=0>
			<tr BGCOLOR=\"#ccccff\">
				<td><B>Name</B></td>
				<td><B>Add</B></td>
			</tr>
	";
	$i=0;
	while (!feof($fd)) {
		$lijn=fgets($fd,1024);
		if (preg_match("/A HREF=\"\/sl.*\/a/",$lijn))
		{
			$lijn = preg_replace("//"," ",$lijn);
			$lijn = preg_replace("/\n/"," ",$lijn);
			//(/HREF=\"(.*)?\"\>(.*)\<\/a\>/)
			$lijn = preg_replace("/.*HREF=\"\/(.*)\.htm\"\>(.*)?\<\/a\>.*/","\\1##\\2",$lijn);
			list($url,$name) = split("##",$lijn);
			$url = preg_replace("/^sl/","",$url);
			$query = "INSERT into station values ('NULL','$name','2','$url','$name')";
			$result = $sql->query($query);
	 		$sid = $sql->last_record($result);    
			print "<tr><td>$name</td><td><input type=CHECKBOX name=\"T$sid\" value=\"1\" checked></td></tr>\n";
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

?>
