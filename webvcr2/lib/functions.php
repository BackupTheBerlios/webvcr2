<?php
// $Id: functions.php,v 1.4 2002/02/13 01:08:47 waldb Exp $

include("global.inc");

define ('PACKAGE_NAME', "WebVCR");
define ('REQUIRE_WEBTOOLS', "0.2.3");

if (!defined("WEBTOOLS_VERSION") or
		!version_check(WEBTOOLS_VERSION, REQUIRE_WEBTOOLS)) {
	die(PACKAGE_NAME." requires phpwebtools >= ".REQUIRE_WEBTOOLS);
}

include_once ("lib/module.php");
include_once ("lib/module_collector.php");

###############################################################################
# Change this!!! 
###############################################################################
$sql = new sql (SQL_MYSQL, $sql_host, $sql_user, $sql_pass, $sql_db);


############### DO NOT CHANGE BELOW THIS ######################################
function addCollector($name, $mainurl, $baseurl) {
	global $sql;
	$result = $sql->query("SELECT collectorid FROM collector ".
		"WHERE name='".addslashes($name)."'");
	if (!$sql->results($result)) {
		$result = $sql->query($sql->insert_query(
			"collector",
			array (
				"name"		=> $name,
				"mainurl"	=> $mainurl,
				"baseurl"	=> $baseurl
			)
		) );
	} // end checking if we already have an entry
	return true;
} // end function getcollectorid

function getstation($number) {
	global $sql;
	$result = $sql->query("SELECT * FROM station ".
		"WHERE sid='".addslashes($number)."'");
	if (!$sql->results($result)) return false;
	while($blah = $sql->fetch_array($result)) {
		return($blah[1]);
	} // end while
} // end function getstation

function getconfig($name) {
	global $sql;
	$result = $sql->query("SELECT value FROM config ".
		"WHERE name='".addslashes($name)."'");
	if (!$sql->results($result)) return false;
	while($blah = $sql->fetch_array($result)) {
	    return($blah["value"]);
	} // end while
} // end function getconfig

function getcollectorid($name) {
	global $sql;
	$result = $sql->query("SELECT collectorid FROM collector ".
		"WHERE name='".addslashes($name)."'");
	if (!$sql->results($result)) return false;
	while($blah = $sql->fetch_array($result)) {
		return($blah[0]);
	} // end while
} // end function getcollectorid

function getcodecconfig($name) {
	global $sql;
	$codec = getconfig("codec");
	$result = $sql->query("SELECT $name FROM codecconfig ".
		"WHERE cid='".addslashes($codec)."'");
	if (!$sql->results($result)) return false;
	while($blah = $sql->fetch_array($result)) {
	    return($blah["$name"]);
	} // end while
} // end function getcodecconfig

function print_select_station($varname, $channel) {
	global $sql;
	$collectorid = getconfig("collectorid");
	$result = $sql->query("SELECT * FROM station ".
		"WHERE collectorid='".addslashes($collectorid)."' ".
		"ORDER BY sname");
	if (!$sql->results($result)) {
		print "<B>No stations</B>\n";
		return false;
	} // end checking for results
	print "<SELECT NAME=\"".htmlentities($varname)."\">\n";
	while($blah = $sql->fetch_array($result)) {
		print "<OPTION VALUE=\"".prepare($blah["sid"])."\" ".
			( ($blah["sname"]==$channel) ? "SELECTED" : "" ).">".
			htmlentities($blah["sname"])."</OPTION>\n";
	} // end while
	print "</SELECT>\n";
} // end function print_select_station

function print_select_collector($collectorid) {
	global $sql;
	$result = $sql->query("SELECT * FROM collector ".
		"ORDER BY name");
	if (!$sql->results($result)) return false;
	while($blah = $sql->fetch_array($result)) {
		print "<OPTION VALUE=\"".htmlentities($blah["collectorid"])."\" ".
			( ($blah["collectorid"]==$collectorid) ? "SELECTED" : "" ).
			">".htmlentities($blah["name"])."</OPTION>\n";
	} // end while
} // end function print_select_collector

function print_select_codec() {
	global $sql;
	$result = $sql->query("SELECT * FROM codec ORDER BY name");
	while($blah = $sql->fetch_array($result)) {
		print "<OPTION VALUE=\"".htmlentities($blah["cid"])."\">".
			htmlentities($blah["name"])."</OPTION>\n";
	} // end while
} // end function print_select_codec

function derecord($id) {
	global $sql;
	$query = "UPDATE program SET record='0' WHERE pid='".addslashes($id)."'";
	$sql->query($query);
} // end function derecord

function record($id, $flag) {
	global $sql;
	$result = $sql->query("SELECT start,stop FROM program ".
		"WHERE pid='".addslashes($id)."'");
	while($blah = $sql->fetch_array($result)) {
		$start = $blah["start"];
		$stop = $blah["stop"];
	} // end of grabbing start/stop
	$result = $sql->query("SELECT * FROM program WHERE record='1' ".
		"AND ((start <= '".addslashes($start)."' AND ".
		"'".addslashes($start)."' <= stop) OR ".
		"(start <= '".addslashes($stop)."' AND ".
		"'".addslashes($stop)."' <= stop))");
	$num_rows = $sql->num_rows($result); 
	if ($num_rows == 0) {
		$query="UPDATE program SET record='1', flag='".addslashes($flag)."' ".
			"WHERE pid='".addslashes($id)."'";
		$sql->query($query);
	} else {
		while($blah = $sql->fetch_array($result)) {
			$station = getstation($blah[1]);
			print "<b>$blah[2] on $station @ $blah[3] is conflicting, not recorded.</b><br>";
		}
	} // end if num_rows = 0
} // end function record

function imdbparse($url) {
	$date = "";
	preg_match("/( \(\d+\))$/", $url, $date);
	$url = preg_replace("/^(The|A|An|Les) (.*)/","\\2, \\1", $url);
	$url = "http://www.imdb.com/M/title-substring?title=".urlencode($url);
	// replaced by urlencode: $url = preg_replace("/ /","+",$url);
	return ($url);
} // end function imdbparse

function version() {
	include ("version.inc");
	return ($version);
} // end function version

function print_title($title) {
	include ("version.inc");
	if ( $title == "") {
		print "<TITLE>WebVCR ($version)</TITLE>\n";
	} else {
		print "<TITLE>WebVCR ($version): $title</TITLE>\n";
	}
} // end function print_title

function print_header_open() {

	include ("version.inc");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<HTML><HEAD>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META NAME="description" CONTENT="WebVCR <?php print $version; ?>">
<?php
} // end function print_header_open

function print_header_close() {
	include ("version.inc");

	?></head>
<body bgcolor=#FFFFFF>
<table border=0 width=100%>
	<tr>
		<td width=15% valign=top>
			<p align=center>
		<a href="http://webvcr.sourceforge.net"><img 
src="images/webvcr_small.png" alt="WebVCR <?php echo $version; ?>" border=0></a> </p>
			<p align=center>
			<?php $today = date("j F Y, H:i"); print $today;  ?>
			</p>
			<p><?php free_space() ?></p>

			<FORM ACTION="show.php" METHOD=POST>
			<center>
			<table border=1>
				<tr bgcolor="#006666">
					<td><center><font color="#FFFFFF">New Program</font></center></td>
				</tr>
				<tr bgcolor="#E0E0E0">
					<td>
						<table border=0>
							<tr>
								<td>station:
								  <?php print_select_station("station", ""); ?> 

							<TR>
								<TD ALIGN=CENTER><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Choose">
								</TD>
							</TR>
							<tr>
								<TD ALIGN=CENTER><a href="force.php">Add Program Manually</a></td>
							</tr>
						</TABLE>
				<tr bgcolor="#006666">
					<td><center><font color="#FFFFFF">Real Time Information</font></center></td>
				</tr>
                                <tr bgcolor="#E0E0E0">  
					<td>
						<table border=0 width=100%>
							<tr>
								<td ALIGN=CENTER><a href="now.php">Now on TV</a></td>
							</tr>
							<tr>
								<td ALIGN=CENTER><a href="now.php?MOVIE=1">Movies Running Now</a></td>
							</tr>
							<tr>
								<td ALIGN=CENTER><a href="now.php?MOVIE=2">Movies Running Today</a></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr bgcolor="#006666">
					<td><center><font color="#FFFFFF">Configuration Section</font></center></td>
				</tr>
                                <tr bgcolor="#E0E0E0">  
					<td>
						<TABLE BORDER="0" WIDTH="100%">
							<TR>
								<TD ALIGN=CENTER><a href="config.php">Configuration</a></td>
							</TR><TR>
								<TD ALIGN=CENTER><a href="generate_vcrrc.php">Refresh ~/.vcrrc</a></td>
							</TR>
						</TABLE>
					</td>
				</tr>
			</table>
			</center>
			</FORM>




		</td><td width=5%></td>
		<td valign=top width=80% align=center>
		<p align=center><a href="index.php">Return to Main Menu</a></p>
<?php
} // end function print_header_close

function print_page_close() {
?>
		<p align=right><small>Copyright wim@bofh.be (c) 2001 - <a 
href="http://webvcr.sourceforge.net">http://webvcr.sourceforge.net</a></small></p>
		</td>
	</tr>
</table>
</body>
</html>
<?php
} // end function print_page_close

function free_space() {
	$savedir = getconfig("savedir");
	if (!$savedir) $savedir = "/home";
	$df = diskfreespace($savedir);
	$size = intval($df / (1024*1024));
	$recordtime = intval($size/350);
	print "$size MB free =~ $recordtime hours free\n";
} // end function free_space

// ********************** ~/.vcrrc manipulation functions *****************

class vcrrc {

	var $home; // this is the users' home directory
	var $config; // this is the configuration
	var $fp; // file pointer (internal use only)

	function vcrrc ( $homedir = "~" ) {
		global $sql;
		$this->home = ( ($homedir == "~") ? $HTTP_ENV_VARS["HOME"] :
			$this->home = $homedir );

		// extract to $config[]
		$result = $sql->query ( "SELECT * FROM config" );
		while ($r = $sql->fetch_array($result)) {
			extract ($r);
			$this->config["$name"] = trim ( stripslashes ( $value ) );
		} // end while

		// make copy of codec
		$codec_id = $this->config['codec'];

		// put replace config[codec] with actual codec name
		$codec_result = $sql->query (
			"SELECT * FROM codec WHERE cid='".
			addslashes($codec_id)."'");
		extract ( $sql->fetch_array($codec_result) );
		$this->config['codec'] = trim ( stripslashes ( $name ) );

		// grab codecconfig options
		$codecconfig_result = $sql->query (
			"SELECT * FROM codecconfig WHERE cid='".
			addslashes($codec_id)."'");
		if ($sql->results($codecconfig_result))
			extract ( $sql->fetch_array($codecconfig_result) );
		$this->config['bitrate'] = trim ( $bitrate );
		$this->config['crispness'] = trim ( $crispness );

	} // end constructor vcrrc

	// function generate
	// - generates vcrrc file from SQL tables
	function generate () {
		global $sql;

		// open file
		$this->fp = fopen ( $this->home . "/.vcrrc", "w" );
		if (!$this->fp) return false; // die if can't write

		// add header
		fwrite ( $this->fp, "# ~/.vcrrc\n" );
		fwrite ( $this->fp, "#\n" );
		fwrite ( $this->fp, "# Generated by WebVCR ".$version."\n\n" );

		// drop general config information first
		fwrite ( $this->fp, "[defaults]\n" );	

		// source = Television [recordsource]
		$this->config_option ( "source", "recordsource" );

		// freqtab = europe-west
		// norm = pal
		// verbose = 1 // TODO ! TODO !
		fwrite ( $this->fp, "verbose = 0\n" );

		// init-sound=/dev/mixer:Line:90
		// audiobitrate = 128
		$this->config_option ( "audiobitrate" );

		// audiomode = stereo // TODO ! TODO !
		$this->config['audiomode'] = "mono";
		$this->config_option ( "audiomode" );

		// audiofrequency = 22 // TODO ! TODO !
		$this->config['audiofrequency'] = "22";
		$this->config_option ( "audiofrequency" );

		// rectime = -1 // unlimited record time

		// codec = DivX ;-) low-motion
		// (need to get supplimentary codec information)
		$this->config_option ( "codec" );

		// attributes = BitRate=7500,Crispness=100
		// attributes = BitRate=900,Crispness=100
		if (isset($this->config['bitrate']) and
				isset($this->config['crispness']))
			fwrite ( $this->fp, "attributes = BitRate=".
				$this->config['bitrate'].",Crispness=".
				$this->config['crispness']."\n" );

		// keyframes = 10 (hi), 15 (lo)
		$this->config_option ( "keyframes" );

		// quality = 100
		$this->config_option ( "quality" );

		// -- end of defaults section --
		fwrite ( $this->fp, "\n" );

		// get all channels
		$result = $sql->query ( "SELECT * FROM station GROUP BY sname ORDER BY sname" );
		if ($sql->results($result)) {
			while ($r = $sql->fetch_array($result)) {
				// get all portions
				extract ($r);

				// drop to the file
				fwrite ( $this->fp, "[".trim($sname)."]\n");
				fwrite ( $this->fp, "channel = ".trim($channel)."\n\n");
			} // end while there are still parts to be fetched
		} // end if there are results

		// close file
		if ($this->fp) fclose ( $this->fp );
			else return false;

		// return true
		return true;
	} // end function vcrrc->generate

	function config_option ( $name, $value="" ) {
		fwrite ( $this->fp, $name." = ".(
			($value=="") ? $config["$name"] : $config["$value"]
			)."\n" );
	} // end function vcrrc->config_option

} // end class vcrrc

?>
