#!/usr/bin/perl 
# $Id: vcr-cron.pl,v 1.2 2002/02/10 10:23:06 waldb Exp $
#

use DBI();

open (f, "global.inc");

while(<f>) {
    if (!/\?/){ eval $_;}
}
close(f);

my $sid="";
my $pname="";
my $pid="";
my $start="";

# Connect to the database.
my $dbh = DBI->connect("DBI:mysql:database=$sql_db;host=$sql_host","$sql_user","$sql_pass", {'RaiseError' => 1});


sub codeclookup {
	my($codec) = @_;
	my $sth = $dbh->prepare("select * from codec where cid='$codec'");
	$sth->execute();
	while (my $ref = $sth->fetchrow_hashref()) {
	    return($ref->{'name'});
	}
	$sth->finish();
} # end sub codeclookup

my $sth = $dbh->prepare("select * from program where ( ( UNIX_TIMESTAMP(start)-UNIX_TIMESTAMP(NOW()) ) >= 0 ) and ( (UNIX_TIMESTAMP(start)-UNIX_TIMESTAMP(NOW()) ) <= 120) and record=1");

# flag 2 are programs that are forced to record (i.e. programs that are running now)
$sth->execute();
# $numRows = $sth->rows; 
while (my $ref = $sth->fetchrow_hashref()) {
	$sid = $ref->{'sid'};
	$pname = $ref->{'pname'};
	$pid = $ref->{'pid'};
	$start = $ref->{'start'};
	#$flag = $ref->{'flag'};
}
$sth->finish();

my $sth = $dbh->prepare("select * from program where ( UNIX_TIMESTAMP(start) <= UNIX_TIMESTAMP(NOW()) ) and ( UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(stop) ) and flag=2");
$sth->execute();

while (my $ref = $sth->fetchrow_hashref()) {
    $sid = $ref->{'sid'};
    $pname = $ref->{'pname'};
    $pid = $ref->{'pid'};
    $start = $ref->{'start'};
    $flag = $ref->{'flag'};
}
$sth->finish();

if ($sid ne '') {
	# flag 2 are programs that are forced to record (i.e. programs that are running now)
	if ($flag == 2) {
		$sth = $dbh->prepare("select station.rname,UNIX_TIMESTAMP(stop)-UNIX_TIMESTAMP(NOW()) from program,station,config where pid=$pid and station.sid=program.sid order by flag desc");
	} else { 
		$sth = $dbh->prepare("select station.rname,UNIX_TIMESTAMP(stop)-UNIX_TIMESTAMP(start) from program,station,config where pid=$pid and station.sid=program.sid order by flag desc");
	}
	$sth->execute();
	while (my $ref = $sth->fetchrow_hashref()) {
		if ($flag == 2)
		{
			$seconds = $ref->{'UNIX_TIMESTAMP(stop)-UNIX_TIMESTAMP(NOW())'}+120;
		} else {
			$seconds = $ref->{'UNIX_TIMESTAMP(stop)-UNIX_TIMESTAMP(start)'}+240;
		}
		$station = $ref->{'rname'};
	}
	$sth->finish();

	$sth = $dbh->prepare("select * from config");
	$sth->execute();
	while (my $ref = $sth->fetchrow_hashref()) {
		if ($ref->{'name'} eq "savedir") { $savedir = $ref->{'value'}; };
		if ($ref->{'name'} eq "vcrprog") { $vcrprog = $ref->{'value'}; };
		if ($ref->{'name'} eq "quality") { $quality = $ref->{'value'}; };
		if ($ref->{'name'} eq "keyframes") { $keyframes = $ref->{'value'}; };
		if ($ref->{'name'} eq "audiobitrate") { $audiobitrate = $ref->{'value'}; };
		if ($ref->{'name'} eq "codec") { $cid = $ref->{'value'}; $codec = &codeclookup($cid); };
		if ($ref->{'name'} eq "recordsource") { $recordsource = $ref->{'value'}; };
		
	}
	$sth->finish();

	$sth = $dbh->prepare("select * from codecconfig where cid=$cid");
	$sth->execute();
	while (my $ref = $sth->fetchrow_hashref()) {
		$bitrate = $ref->{'bitrate'};
		$crispness = $ref->{'crispness'};
	}
	$sth->finish();

	if ($flag == 2) { $sth = $dbh->prepare("update program set flag=0 where pid=$pid"); $sth->execute(); }
	print "$pname runs for $seconds seconds en starts @ $start\n";
	`/usr/bin/aumix -L`;
	$vcrargs = " --codec \"$codec\" -a 'Bitrate=$bitrate,Crispness=$crispness' --source \"$recordsource\" --quality $quality --audiobitrate $audiobitrate ";
	$command = $vcrprog.$vcrargs."-p \"$station\" --rectime $seconds"."s \"$savedir/$pname.avi\"";
	system("$command");
}

$dbh->disconnect();     

