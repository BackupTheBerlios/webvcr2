<?php
 // $Id: show.php,v 1.1 2002/02/09 17:45:19 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

print_header_open();
$show_station = getstation($station);
print_title ("Program Listing for $show_station");
print_header_close();

if (isset($RECORD)) {
	if (isset($FLAG))
	{
		record($RECORD,$FLAG);
	} else {
		record($RECORD,0); // function in functions.php
	}
}

if (isset($FORCERECORD)) {
	record($FORCERECORD,2);
}
if (isset($DERECORD)) {
	derecord($DERECORD);
}
?>


<center>
<table border=1><tr bgcolor="#006666"><td><center>
	<font color="#FFFFFF">Program Listing for <?php
		//$station = getstation($station);
		if (!$station) $station = "(undefined station)";
		echo $show_station;
	?></font></center></td></tr><tr bgcolor="#E0E0E0"><td>
<table border=0>
<tr>
	<td>Program</td><td>Start</td><td>Stop</td><td>Record</td>
</tr>

<?
/* long query that shows every program of station $station (this shows no
   manual recorded programs (flag=0)
*/
$query = ("SELECT station.sname, program.pname, ".
	"DATE_FORMAT(start,'%H:%i'), DATE_FORMAT(stop,'%H:%i'), ".
	"program.record,program.pid,program.flag FROM program,station ".
	"WHERE program.sid='".addslashes($station)."' AND ".
	"station.sid='".addslashes($station)."' AND (flag!='1') ".
	"ORDER BY pid");
$result = $sql->query($query);
if (!$sql->results($result)) {
	print "
		<tr>
			<td colspan=4 align=center>
				<I>No program listings for this channel.</I>
			</td>
		</tr>
	";
} else {
	while($blah = $sql->fetch_array($result)) {
		print "
			<tr>
				";
				if ($blah[6] == 3) { # flag 3 is set, it's a movie!
					$flag = 3;
	                $url = imdbparse($blah[1]);
	                print "<td><a href=\"$url\">$blah[1]</a></td>";
	            } else {
	                print "<td>$blah[1]</td>";
	            }
	            print "
				<td>$blah[2]</td>
				<td>$blah[3]</td>
			";
		if ($blah[4] == 1) {
			print "
			<td bgcolor=\"green\">
			<a href=\"$PHP_SELF?DERECORD=$blah[5]&station=$station\">derecord</a>
			</td>";
		} else {
		  	print "
			<td bgcolor=\"red\">
			";
			if ($flag == 3) {
				print "<a href=\"$PHP_SELF?RECORD=$blah[5]&station=$station&FLAG=$flag\">record</a>";
			} else {
				print "<a href=\"$PHP_SELF?RECORD=$blah[5]&station=$station\">record</a>";	
			}
			print"
			</td>";
	  	} // end checking flags
		print "</td></tr>";
	} // end of while
} // end of if there are no results

?>
</table>
</table>
</center>
<? include "footer.inc"; ?>

<? print_page_close(); ?>

