<?php
 // $Id: config.php,v 1.2 2002/02/13 01:08:47 waldb Exp $
 // $Author: waldb $

include("lib/functions.php");

/* under construction */
if ($SUBMIT == "Save") {
	if (isset($savedir)) {
		$query="update config set value='".addslashes($savedir)."' where name='savedir'";
		$sql->query($query);
	}
	$sql->query("UPDATE config SET value='".addslashes($timetostart)."' ".
		"WHERE name='timetostart'");
	$sql->query("UPDATE config SET value='".addslashes($timetoend)."' ".
		"WHERE name='timetoend'");
	$sql->query("UPDATE config SET value='".addslashes($vcrprog)."' ".
		"WHERE name='vcrprog'");
	$sql->query("UPDATE config SET value='".addslashes($quality)."' ".
		"WHERE name='quality'");
	$sql->query("UPDATE config SET value='".addslashes($keyframes)."' ".
		"WHERE name='keyframes'");
	$sql->query("UPDATE config SET value='".addslashes($audiobitrate)."' ".
		"WHERE name='audiobitrate'");
	$sql->query("UPDATE config SET value='".addslashes($recordsource)."' ".
		"WHERE name='recordsource'");
	$sql->query("UPDATE config SET value='".addslashes($collectorid)."' ".
		"WHERE name='collectorid'");
		// updating the codec config and the codec
	$sql->query("UPDATE config SET value='".addslashes($codec)."' ".
		"WHERE name='codec'");
	$sql->query("UPDATE codecconfig SET bitrate='".addslashes($bitrate)."',".
		"crispness='".addslashes($crispness)."' ".
		"WHERE cid='".addslashes($codec)."'");
}

print_header_open();
print_title ("config");
print_header_close();

?>

<FORM ACTION="config.php" METHOD=POST>
<center>
<table border=1><tr bgcolor="#006666">
<td><center><font color="#FFFFFF">WebVCR 2 Configuration</font></center></td>
</tr>
<tr bgcolor="#E0E0E0"><td>
<table border=0>
<tr>
	<td>Directory to Store Video</td><td>:</td>
<? $savedir=getconfig("savedir"); 
print "<td>".html_form::text_widget("savedir")."</td>
</tr>
<tr>
	<td>Location of VCR binary</td><td>:</td>";
$vcrprog=getconfig("vcrprog");
print "
	<td>".html_form::text_widget("vcrprog")."</td>
</tr>
<tr>
	<td>Recording Quality (max 100)</td><td>:</td>";
$quality=getconfig("quality");
print "
	<td>".html_form::text_widget("quality")."</td>
</tr>
<tr>
	<td>Key frames (max 30)</td><td>:</td>";
$keyframes=getconfig("keyframes");
print "
	<td>".html_form::text_widget("keyframes")."</td>
</tr>
<tr>
	<td>Audio Bitrate</td><td>:</td>";
$audiobitrate=getconfig("audiobitrate");
print "
	<td>".html_form::text_widget("audiobitrate")."</td>
</tr>
<tr>
	<td>Recording Source</td><td>:</td>";
$recordsource=getconfig("recordsource");
print "
	<td>".html_form::text_widget("recordsource")."</td>
</tr>
<tr>
	<td>Codec</td><td>:</td><td><select name=\"codec\">";
print_select_codec();
print "</select></td></tr>
<tr>
	<td>Bitrate</td><td>:</td>";
$bitrate=getcodecconfig("bitrate");
print "
	<td>".html_form::text_widget("bitrate")."</td>
</tr>
<tr>
	<td>Crispness</td><td>:</td>";
$crispness=getcodecconfig("crispness");
print "
	<td>".html_form::text_widget("crispness")."</td>
</tr>
<tr>
	<td>Station Collector to Use</td><td>:</td><td><select name=\"collectorid\">";
print_select_collector(getconfig("collectorid"));
print "</select></td></tr>";
?>
  <tr><td>Minutes to Start Record Before and After</td><td>:</td>
<?php
    $timetostart=getconfig("timetostart");
print "<td>".html_form::text_widget("timetostart")."</td>\n";
?>
<tr><td>Minutes to End Recording After the Program</td><td>:</td>
<?php
    $timetoend=getconfig("timetoend");
	print "<td>".html_form::text_widget("timetoend")."</td>\n";
?>
</td>
<tr>
<td colspan=3 align=center>
	<a href="collector.php">Channel Collector Configuration</a>
</td>
</tr>
 
  <TD COLSPAN=3 ALIGN=CENTER><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Save"></TD>
     </TR>
</TABLE>
</table>
</center>
</FORM>
<?php
print_page_close();
?>

