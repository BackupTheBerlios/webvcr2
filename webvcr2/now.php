<?php
 // $Id: now.php,v 1.1 2002/02/09 17:45:18 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

if (isset($RECORD)) {
	record($RECORD, 0); // function in functions.php
}
if (isset($FORCERECORD)) {
	record($FORCERECORD, 2);
}
if (isset($DERECORD)) {
	derecord($DERECORD);
}

print_header_open();
print_title("Now on TV");
print_header_close();

?>

<center>
<table border=1><tr bgcolor="#006666"><td><center>
	<font color="#FFFFFF">Program Listing</font>
</center></td></tr><tr bgcolor="#E0E0E0"><td>
<table border=0>
<tr>
	<td>Station</td><td>Program</td><td>Start</td><td>Stop</td><td>Record</td>
</tr>

<?
/* long query that shows every program of station $station (this shows no
   manual recorded programs (flag=0)
*/
$collectorid = getconfig("collectorid");
$query = "select station.sname,program.pname,DATE_FORMAT(start,'%H:%i'),DATE_FORMAT(stop,'%H:%i'),program.record,program.pid,program.sid,program.flag from program,station where (UNIX_TIMESTAMP(start) <= UNIX_TIMESTAMP(NOW())) and (UNIX_TIMESTAMP(NOW()) < UNIX_TIMESTAMP(stop)) and program.sid=station.sid and station.collectorid=$collectorid ORDER BY station.sname";

if ($MOVIE == 1) {
	$query="select station.sname,program.pname,DATE_FORMAT(start,'%H:%i'),DATE_FORMAT(stop,'%H:%i'),program.record,program.pid,program.sid,program.flag from program,station where (UNIX_TIMESTAMP(start) <= UNIX_TIMESTAMP(NOW())) and (UNIX_TIMESTAMP(NOW()) < UNIX_TIMESTAMP(stop)) and program.sid=station.sid and flag=3 and station.collectorid=$collectorid order by station.sname";
}

if ($MOVIE == 2) {
	$query="select station.sname,program.pname,DATE_FORMAT(start,'%H:%i'),DATE_FORMAT(stop,'%H:%i'),program.record,program.pid,program.sid,program.flag from program,station where flag=3 and program.sid=station.sid and station.collectorid=$collectorid order by station.sname,pid";
}

$result = $sql->query($query);
if (!$sql->results($result)) {
	print "
		<tr>
			<td colspan=4 align=center>
				<I>No programs currently listed.</I>
			</td>
		</tr>
	";
} else {
	while($blah = $sql->fetch_array($result))
	{
		print "
			<tr>
				<td><a href=\"show.php?station=$blah[6]\">$blah[0]</a></td>
				";
				if ($blah[7] == 3) 
				{ # flag 3 is set, it's a movie! 
					$url=imdbparse($blah[1]);
					print "<td><a href=\"$url\">$blah[1]</a></td>";
				} else {
					print "<td>$blah[1]</td>";
				}
				print "
				<td>$blah[2]</td>
				<td>$blah[3]</td>
			";
		if ($blah[4] == 1)
		{
			print "
			<td bgcolor=\"green\">
			<a href=\"$PHP_SELF?DERECORD=$blah[5]&station=$blah[6]\">derecord</a>
			</td>";
		} else {
		  	print "
			<td bgcolor=\"red\">
			<a href=\"$PHP_SELF?RECORD=$blah[5]&station=$blah[6]\">record</a>
			<a href=\"$PHP_SELF?FORCERECORD=$blah[5]&station=$blah[6]\">force</a>
			</td>";
	  	}
		print "</td></tr>";
	} // end while

} // end if there are no results

?>
</table>
</table>
</center>
<?php print_page_close(); ?>

