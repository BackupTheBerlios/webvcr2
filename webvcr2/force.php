<?php
 // $Id: force.php,v 1.1 2002/02/09 17:45:18 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

if ( ($SUBMIT == "Add Program") && isset($START) &&
	 isset($STOP) && isset($PROGRAM) ) {
/* We want to record a program, a $PROGRAM name, $START and $STOP has to
   be set to create an entry in the database
*/
	$query = $sql->insert_query(
		"program",
		array (
			"pid"		=>	NULL,
			"sid"		=>	$station,
			"pname"		=>	$PROGRAM,
			"start"		=>	"$STARTYEAR-$STARTMONTH-$STARTDAY $START",
			"stop"		=>	"$STOPYEAR-$STOPMONTH-$STOPDAY $STOP",
			"record"	=>	"1",
			"flag"		=>	"1"
		)
	);
	$sql->query($query);
	header("Location: $PHP_SELF");
} // end if submit=PROGRAM, etc

/* Deleting a show from the database */
if (isset($DELETE)) {
	$query="DELETE FROM program WHERE pid='".addslashes($DELETE)."'";
	$sql->query($query);
	header("Location: index.php");
}

if (isset($DERECORD)) {
	derecord($DERECORD);
	header("Location: index.php");
}

/* Fetching the necessary info from the database and put them in 'unique' 
   global vars  (DB_NAME)
*/
if (isset($EDIT)) {
	$query="SELECT *,DATE_FORMAT(start,\"%Y %m %d %H:%i\"), ".
		"DATE_FORMAT(stop,\"%Y %m %d %H:%i\") FROM program ".
		"WHERE pid='".addslashes($EDIT)."'";
	$result = $sql->query($query);
	while($blah = $sql->fetch_array($result)) {
		$DB_PID = $blah[0]; // PID is the program ID
		$DB_SID = $blah[1]; // SID is the station ID
		$DB_STATION = getstation($DB_SID);
		$DB_PNAME = $blah[2];
		/* We'll have to split on a ' ' cause SQL gives us back 
		   %y %m %d %H:%i (year month day hour:minute)
		*/
		list($DB_STARTYEAR,$DB_STARTMONTH,$DB_STARTDAY,$DB_START)=split(" ",$blah[7]);
		list($DB_STOPYEAR,$DB_STOPMONTH,$DB_STOPDAY,$DB_STOP)=split(" ",$blah[8]);
	} // end looping through results
} // end if isset EDIT

if (isset($ADDSTART)) {
	$sql->query("UPDATE program SET start=start + INTERVAL 1 minute ".
		"WHERE pid='".addslashes($ADDSTART)."'");
	header("Location: index.php");
	exit;
}
if (isset($DELSTART)) {
	$sql->query("UPDATE program SET start=start - INTERVAL 1 minute where pid='".addslashes($DELSTART)."'");
	header("Location: index.php");
	exit;
}

if (isset($ADDSTOP)) {
	$sql->query("UPDATE program SET stop=stop + INTERVAL 1 minute where pid='".addslashes($ADDSTOP)."'");
	header("Location: index.php");
	exit;
}
if (isset($DELSTOP)) {
	$sql->query("UPDATE program SET stop=stop - INTERVAL 1 minute where pid='".addslashes($DELSTOP)."'");
	header("Location: index.php");
	exit;
}

print_header_open();
print_title("Manually Add a Program");
print_header_close();

print "<FORM ACTION=\"force.php\" METHOD=POST>\n";

// fetching some date stuff before we go back to HTML code
$dag = date("d");
$maand = date("m");
$jaar = date("Y");

?>

<br>
<center>
<table border=1><tr bgcolor="#006666">
	<td><center>
		<font color="#FFFFFF">Add New Program</font>
	</center></td>
</tr><tr bgcolor="#E0E0E0"><td>
<table border=0>
<tr>
	<td>Station</td><td>:</td><td>

<?php  
/* if we are editing the program then print the preselected station first
   in the select box 
*/
if ($EDIT) {
	print_select_station("STATION", $DB_SID);
} else {
	print_select_station("STATION", "");
} // end if EDIT
?>
	</td>
</tr>
<tr>
	<td>Program Name</td><td>:</td><td><input type=TEXT name="PROGRAM" 
<?php if ($EDIT) { print "value=\"".prepare($DB_PNAME)."\""; } ?>
	></td>
</tr>
</table>
<br>
<table>
<tr>
	<td>Start Time (e.g. 14:32)</td><td>:</td>
	<td><input type=TEXT name="START" size=5 
<?php if ($EDIT) { print "value=\"".prepare($DB_START)."\""; } ?>
	></td>

	<td>Day</td><td>:</td>
	<td><select name="STARTDAY">
<?php /* If we're editing put the selected day first, otherwise fill the
      rest of the <select> with 31 days *FIXME*
   */
	if ($EDIT) {
		print "<option>".prepare($DB_STARTDAY)."</option>\n";
	} else {
		print "<option>".prepare($dag)."</option>\n";
	}
   	$i = 1;
   	while ($i <= 31) {
    	if ($i < 10) {
			print "<option>".prepare("0$i")."</option>";
		} else {
        	print "<option>$i</option>";
		} // end if i < 10
        $i++;
   	} // end while
?>
	</select></td>

	<td>Month</td><td>:</td>
	<td><select name="STARTMONTH">
<?php
  	if ($EDIT) {
		print "<option>".prepare($DB_STARTMONTH)."</option>\n";
	} else {
		print "<option>".prepare($maand)."</option>\n";
	}
  	$i = 1;
  	while ($i <= 12) {
    	if ($i < 10) {
			print "<option>".prepare("0$i")."</option>\n";
		} else {
        	print "<option>".prepare($i)."</option>";
		}
    $i++;
   	} 
?>
	</select></td>
	<td>Year</td><td>:</td>
	<td><select name="STARTYEAR">
<?php
	if ($EDIT) {
		print "<option>".prepare($DB_STARTYEAR)."</option>\n";
	} else {
		print "<option>".prepare($jaar)."</option>\n";
	}
	$i = 1;
	while ($i <= 3) {
		print "<option>".prepare($jaar - 2 + $i)."</option>";
		$i++;
	}
?>
	</select></td>
</tr>

<tr>
	<td>End Time (e.g. 22:15)</td><td>:</td>
	<td><input type=TEXT name="STOP" size=5 
<?php if ($EDIT) { print "value=\"$DB_STOP\""; } ?>
	></td>
	
	<td>Day</td><td>:</td><td><select name="STOPDAY">
<?php
  	if ($EDIT) {
		print "<option>".prepare($DB_STOPDAY)."</option>\n";
	} else {
		print "<option>".prepare($dag)."</option>\n";
	}
  	$i = 1;
   	while ($i <= 31) {
    	if ($i < 10) {
			print "<option>".prepare("0$i")."</option>\n";
		} else {
        	print "<option>".prepare($i)."</option>\n";
		}
    $i++;
    }
?>
	</select></td>

<td>Month</td><td>:</td><td><select name="STOPMONTH">
<?php
	if ($EDIT) {
		print "<option>".prepare($DB_STOPMONTH)."</option>\n";
  	} else {
		print "<option>".prepare($maand)."</option>\n";
	}
  	$i = 1;
  	while ($i <= 12) {
        if ($i < 10) {
			print "<option>".prepare("0$i")."</option>\n";
		} else {
     		print "<option>".prepare($i)."</option>\n";
		} // end if i < 10
	    $i++;
    } // end of while
?>
	</select></td>
	<td>Year</td><td>:</td>
	<td><select name="STOPYEAR">
<?php
	if ($EDIT) {
		print "<option>".prepare($DB_STOPYEAR)."</option>\n";
	} else {
		print "<option>".prepare($jaar)."</option>\n";
	}
	$i = 1;
	while ($i <= 3) {
		print "<option>".prepare($jaar - 2 + $i)."</option>";
		$i++;
	}
?>
	</select></td>
</tr>
<tr>
  <td COLSPAN=4 ALIGN=CENTER>
<?php print "<a href=\"$PHP_SELF\">New Entry</a>"; ?>
  </td>
  <TD COLSPAN=4 ALIGN=CENTER><INPUT TYPE=SUBMIT NAME="SUBMIT"
<?php 
	if ($EDIT) { 
		print "VALUE=\"Update Program\">";
	} else { 
		print "VALUE=\"Add Program\">";
	}
?>
	</td>
</tr>
</table>
</table>
</center>
</FORM>
<?php

print_page_close();

?>

