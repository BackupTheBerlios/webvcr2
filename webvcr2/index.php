<?php
 // $Id: index.php,v 1.1 2002/02/09 17:45:18 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

print_header_open();
print_title("");
print_header_close();

?>

<h1>Currently Programmed Programs</h1>

<?php

$query = "SELECT * FROM program WHERE ".
	"(flag='0' OR flag='1' OR flag='2' OR flag='3') ".
	"AND record='1'";
$result = $sql->query($query);
if ($sql->results($result)) {
	?>
	<table border=1><tr bgcolor="#006666"><td><center><font color="#FFFFFF">recorded programs</font></center></td></tr><tr bgcolor="#E0E0E0"><td>
	<table border=1 width=100%>
	<tr>
		<td>Station</td>
		<td>Program Name</td>
		<td>Start</td>
		<td>Stop</td>
		<td>Edit</td>
	</tr>
	<?php
	$query = "SELECT *,DATE_FORMAT(start,'%H:%i %d/%m'), ".
		"DATE_FORMAT(stop,'%H:%i %d/%m') FROM program ".
		"WHERE (flag='0' or flag='1' or flag='2' or flag='3') ".
		"AND record='1' ORDER BY start";
	$result = $sql->query($query);

	while($blah = $sql->fetch_array($result)) {
		$station = getstation($blah[1]); // We need a name not a number ;-)
	   	print "
		<tr><td>$station</td>
		 	<td>$blah[2]</td>
	        <td>$blah[7]</td>
			<td>$blah[8]</td>";
#			if ($blah[6] == 0) {
				print "
				<td>
				<a href=force.php?ADDSTART=$blah[0]>+1 start</a>
				<a href=force.php?DELSTART=$blah[0]>-1 start</a><br>
				<a href=force.php?ADDSTOP=$blah[0]>+1 end</a>
				<a href=force.php?DELSTOP=$blah[0]>-1 end</a>
				</td>
				<td><a href=force.php?EDIT=$blah[0]>edit</a></td>
				<td><a href=force.php?DERECORD=$blah[0]>delete</a></td>";
#			} else {
#				print "
#				<td><a href=force.php?EDIT=$blah[0]>edit</a></td>
#				<td><a href=force.php?DELETE=$blah[0]>delete</a></td>";
#			}
		print "</tr>";
	}
	?>
	</table>
	</table>
<?php
} else {
	print "
	<P>
	<CENTER>
		No programs currently entered.
	</CENTER>
	<P>
	";
} // end checking for programs

print_page_close();

?>
