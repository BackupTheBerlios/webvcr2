#!/usr/bin/perl
#(c)1997-2001 wim@bofh.be
use LWP;
use LWP::UserAgent;
use DBI();

open(f,"global.inc");
while(<f>) {
	if (!/\?/){ eval $_;}
}
close(f);

# Connect to the database.
my $dbh = DBI->connect("DBI:mysql:database=$sql_db;host=$sql_host","$sql_user", "$sql_pass", {'RaiseError' => 1});

$dbh->do("delete from program where flag=0 or flag=2 or flag=3"); # don't delete manually recorded programs!

my $sth = $dbh->prepare("select collectorid from collector where name='eurotv'");
$sth->execute();
while (my $ref = $sth->fetchrow_hashref()) {
        $collectorid=$ref->{'collectorid'};
}
$sth->finish();
 
# populating %urls from data out of mysql
my $sth = $dbh->prepare("SELECT * FROM station where collectorid='$collectorid'");
$sth->execute();
while (my $ref = $sth->fetchrow_hashref()) {
        $urls{$ref->{'rname'}}=$ref->{'suburl'}
}
$sth->finish();
$host="http://www.eurotv.com:80";
$now=localtime;


################################################################################
# checks what number should be used today (because the number of day differs)  #
################################################################################
sub checkhtm {
  my($url)=@_;
  my $inhoud=();
  my $ua = new LWP::UserAgent;
  $ua->agent("Mozilla/4.5 [en] (Win98; I)");
  # Create a request
  my $fullurl=$host."/slbbc1.htm";
  my $req = new HTTP::Request (GET => $fullurl);
  # Pass request to the user agent and get a response back
  my $res = $ua->request($req);
  # Check the outcome of the response
  if ($res->is_success) {
    $inhoud= $res->content;
  } else { }
  my @temp=split(/ /,$now);
  my $dag=$temp[0];
  my $dagnr=$temp[2];
  my @temp2=split(/\n/,$inhoud);
  foreach $lijn (@temp2) {
    if ($lijn =~ /$dag/)
    {$lijn=~ s/\//g; 
	  if ($lijn =~/\<A HREF=\"(\d).*\>(\w+) (\d+).*\<BR\>/) 
			{
			if ($dagnr eq $3) { return($1); }
			else { die "today not found .. error\n";}
			}
#      $numberofday=$temp3[0]; # now we get something like [0-9]a ; that's being used in the URL
    }
  }
}

################################################################################
# connects to the $host and retrieves the data for each channel                #
################################################################################
sub connecttohttpd {
  my($url)=@_; 
  my $inhoud=();
  $ua = new LWP::UserAgent;
  $ua->agent("Mozilla/4.5 [en] (Win98; I)");
  # Create a request
  my $fullurl=$host."/".$numberofday."a".$url.".htm";
  $req = new HTTP::Request (GET => $fullurl); 
  #Pass request to the user agent and get a response back
  $res = $ua->request($req);
  # Check the outcome of the response
  if ($res->is_success) {
    $inhoud= $res->content;
  } else { }
  return($inhoud);
}

###############################################################################
# takes the retrieved data and gets the most important data ;)                #
###############################################################################
sub parseinhoud{
  my $lijn=();
  my $flag=0;
  my $inhoud=$_[0];
  my @blah=split(/\n/,$inhoud);
  foreach $lijn (@blah) 
  { if ($lijn=~ /^[0-9][0-9]:[0-9][0-9]/) 
    {my @temp=split(/\<B\>/,$lijn);
      $temp[0]=~ s/\//g; 
      $temp[1]=~ s/\//g; 
      $temp[1]=~ s/\<\/B\>//g;
      chop($temp[0]);
      push(@uur,$temp[0]);
      push(@programma,$temp[1]);
    }
	if ($lijn=~/film\.gif/)
	{
		$film[$#programma]=1;
	}
  }
}

###############################################################################
# finally prints the data to stdout                                           #
###############################################################################
sub printit {
my $flag=0;
my $i=1;
my $temp=0;
my @tempje=("0");
  #my $tempje[0]=0;
for ($[ .. $#uur) 
{ #print "$zenders\t";
	if ($_ == $#uur) { #print "$uur[$_]- 99:99\t"; 
    } else { 
 #clumsy hack, fix it someday :)
	@blah=split(/:/,$uur[$_]);
	@blah2=split(/:/,$uur[$_+1]);
	$blah[0]=~s/^0//;
	$blah2[0]=~s/^0//;
	$tempje[$i]=$blah[0];
	$tempje2[$i]=$blah2[0];
	if ($tempje[$i-1] > $tempje[$i]) { $flag=1; } #if the previous hour is bigger then the current we passed midnight
	if ($tempje2[$i-1] > $tempje2[$i]) { $flag=2;} #if the endhour is after midnight
	 $i++;

	($temp,$temp,$temp,$mday,$mon,$year) = localtime(time);
	$year += 1900;
	$mon++;
	my $date=$year."-".$mon."-".$mday;
      
	$beginuur=$date." ".$uur[$_];chop($beginuur);
    $einduur=$date." ".$uur[$_+1];chop($einduur);
	$station=&getstation($zenders);

	  #print "$beginuur - $einduur\n"; 
	if ($film[$_] == 1)
	{
		$dbh->do("INSERT INTO program VALUES (NULL, ?, ?, ?, ?, ?, ?)",undef, $station, "$programma[$_]","$beginuur","$einduur",0,3);
	} else {
		$dbh->do("INSERT INTO program VALUES (NULL, ?, ?, ?, ?, ?, ?)",undef, $station, "$programma[$_]","$beginuur","$einduur",0,0);
	}
	  

	if ($flag == 1) 
	{
		$pid=$dbh->{'mysql_insertid'};
	  	$dbh->do("update program set start=INTERVAL 1 DAY + start,stop=INTERVAL 1 DAY + stop where pid=$pid");
	}
	if ($flag == 2) 
	{
	  	$pid=$dbh->{'mysql_insertid'};
	  	$dbh->do("update program set stop=INTERVAL 1 DAY + stop where pid=$pid");
	}
	if ($film[$_] == 1)
	{
		$pid=$dbh->{'mysql_insertid'};
	  	$dbh->do("update program set flag=3 where pid=$pid");
	}
	}
}
}

sub getstation{
my $station=$_[0];
my $sth = $dbh->prepare("SELECT * FROM station where sname = '$station'");
$sth->execute();
while (my $ref = $sth->fetchrow_hashref()) {
	$station=$ref->{'sid'}
}
$sth->finish();
return ($station)
}

$numberofday=&checkhtm; #checks to find the right number of the day.
foreach $zenders (keys %urls) 
{ @uur=();@programma=();@film=();
  $inhoud=&connecttohttpd($urls{$zenders});
  &parseinhoud($inhoud);
  &printit;
#  print "\n-\n";
}

