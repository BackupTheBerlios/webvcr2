#!/usr/bin/perl
use DBI();

open(f,"global.inc");
while(<f>) {
    if (!/\?/){ eval $_;}
}
close(f);
# Connect to the database.
my $dbh = DBI->connect("DBI:mysql:database=$sql_db;host=$sql_host", "$sql_user", "$sql_pass", {'RaiseError' => 1});

 
@station=("BRT","KETNET","CANVAS","VTM","VT4","KA2","BBC1","BBC2","NED1","NED2",,"NED3");
$dbh->do("delete from station");
foreach(@station)
{
$dbh->do("INSERT INTO station VALUES (NULL, ?)",undef, "$_");
}
$dbh->disconnect();
