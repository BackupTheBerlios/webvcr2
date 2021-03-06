<?php
 // $Id: advalvas-slurp.php,v 1.1 2002/02/09 17:45:17 waldb Exp $
 // $Author: waldb $
 // (c)1997-2001 wim@bofh.be

include("lib/functions.php");

 // don't delete manually recorded programs!
$sql->query("DELETE FROM program WHERE flag=0 or flag=2 or flag=3");

 // populating %urls from data out of SQL
$result = $sql->query("SELECT collectorid FROM collector WHERE name='advalvas'");
while($blah = $sql->fetch_array($result)) {
	$collectorid = $blah[0];
} // end while

$result = $sql->query("SELECT rname,suburl FROM station where collectorid='".addslashes($collectorid)."'");
while($blah = $sql->fetch_array($result)) {
	$urls[$blah[0]] = $blah[1];
} // end while

$host="http://tv.advalvas.be/av2/scripts/tv.dll?Show"; 

################################################################################
# connects to the $host and retrieves the data for each channel                #
################################################################################
function connecttohttpd ($url,$host) {
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $host.$url);
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.5 [en] (Win98; I)");
	curl_setopt ($ch, CURLOPT_HEADER, 0);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec ($ch);
	curl_close ($ch);
	return $result;
} // end function connecttohttpd

###############################################################################
# takes the retrieved data and gets the most important data ;)                #
###############################################################################
function parsecontent ($content,$uur,$programma,$film) {
	$inhoud = array();
	$inhoud = split("\n",$content);
	reset ($inhoud);
	$inhoud = array_reverse($inhoud);
	while($lijn = array_pop($inhoud))
	{
		// print "$lijn\n";
		if (preg_match("/^\d+:\d+/",$lijn)) {
			$lijn = preg_replace("/^(\d+:\d+).*/","\\1",$lijn);
		  	array_push($uur,$lijn);
		}
		if (preg_match("/<b\>(.*)\<\/b\>/",$lijn)) {
			$lijn=preg_replace("/.*?<b\>(.*?)\<\/b\>.*/","\\1",$lijn);
			if (!preg_match("/href/",$lijn) && (!preg_match("/programma\'s/",$lijn) )) {
				$lijn = addslashes($lijn);
				array_push($programma,$lijn);
			} // end if inner preg_match
		}
		if (preg_match("/\>movie\</",$lijn)) {
			$max = sizeof($programma)-1; 	
			$film[$max] = 1;
		} 
	}
	$array = array($programma,$uur,$film);
	return($array);
} // end function parsecontent

function getstationnumber($station) {
	global $sql;
	$result = $sql->query("SELECT sid FROM station where rname = '".addslashes($station)."'");
	while ($blah = $sql->fetch_array($result)) {
	        $station = $blah[0];
	} // end while
	return ($station);
} // end function getstationnumber


###############################################################################
# finally prints the data to stdout                                           #
###############################################################################
function printit($programma,$uur,$film,$zender) {
	global $sql;
	$flag = 0;
	$i = 1;
	$count = 0;
	reset($uur);
	$max = sizeof($uur);
	while($count < $max-1) { 
        if ($count == $max) {
			// print "$uur[$_]- 99:99\t"; 
		} else {
	 		// clumsy hack, fix it someday :)
			$blah = split(":",$uur[$count]);
			$blah2 = split(":",$uur[$count+1]);
			$blah[0] = preg_replace("/^0/","",$blah[0]);
			$blah2[0] = preg_replace("/^0/","",$blah2[0]);
			$tempje[$i] = $blah[0];
			$tempje2[$i] = $blah2[0];
			if ($tempje[$i-1] > $tempje[$i]) { $flag=1; } #if the previous hour is bigger then the current we passed midnight
			if ($tempje2[$i-1] > $tempje2[$i]) { $flag=2;} #if the endhour is after midnight
		 	$i++;
			list($temp,$temp,$temp,$mday,$mon,$year) = localtime();
			$year += 1900;
			$mon++;
			$date = $year."-".$mon."-".$mday;
			$beginuur = $date." ".$uur[$count];
   		 	$einduur = $date." ".$uur[$count+1];
			// end clumsy hack
			$station = getstationnumber($zender);
	
			if ($film[$count] == 1) {	
				$result = $sql->query("INSERT INTO program VALUES ('NULL', $station, '$programma[$count]','$beginuur','$einduur',0,3)");
			} else {
				$result = $sql->query("INSERT INTO program VALUES ('NULL', $station, '$programma[$count]','$beginuur','$einduur',0,0)");
			}
		
			if ($flag == 1) {
				$pid = $sql->last_record($result);
		  		$sql->query("update program set start=INTERVAL 1 DAY + start,stop=INTERVAL 1 DAY + stop where pid=$pid");
			}
			if ($flag == 2) {
				$pid = $sql->last_record($result);
		  		$sql->query("update program set stop=INTERVAL 1 DAY + stop where pid=$pid");
			}
		}
		$count++;
	} // end while
} // end function printit

if ($WEB) {
	print_header_open(); 
	print_title("Collecting ...");
	print_header_close(); 
}

while(list($zender,$url) = each($urls)) {
    $uur = array(); $programma = array();
	$inhoud = array();
	if ($WEB) {
		print "Slurping $zender...<br>\n";
	}
  	$inhoud = connecttohttpd($url,$host);
  	list($programma,$uur,$film) = parsecontent($inhoud,$programma,$uur,$film);
	printit($programma,$uur,$film,$zender);
}

if ($WEB) {
	print "done<br>";
	print_page_close();
}

?>
