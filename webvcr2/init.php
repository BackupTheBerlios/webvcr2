<?php
 // $Id: init.php,v 1.4 2002/02/15 23:51:01 waldb Exp $

include("global.inc");

print $PASS;
if ( isset($PASS) && ($PASS != "") ) {
	$sql = new sql (SQL_MYSQL, $HOSTNAME, "root", "$PASS", "mysql");

	$sql->query("drop database ".addslashes($DATABASE));
	
	$sql->query("create database ".addslashes($DATABASE));

	//$sql->create_db("$DATABASE");
	$sql = new sql (SQL_MYSQL, $HOSTNAME, "root", "$PASS", "$DATABASE");
	//$sql->select_db("$DATABASE");

	$query="create table collector(
		name varchar(100),
		mainurl varchar(100),
		baseurl varchar(255),
		collectorid int not null auto_increment,
		primary key(collectorid))";
	print "creating $DATABASE.collector .. <br>";
	$sql->query($query);

	$query="insert into collector values ('advalvas','http://www.advalvas.be/av2','http://tv.advalvas.be/av2/scripts/tv.dll?Show?',NULL)";
	print "creating first collector (advalvas) in $DATABASE.collector ..<br>";
	$sql->query($query);

	$query="insert into collector values ('eurotv','http://www.eurotv.com/scripts/eutvprog.cfm','http://www.eurotv.com',NULL)";
	print "creating second collector (eurotv) in $DATABASE.collector ..<br>";
	$sql->query($query);

	$query = "create table station(
		sid int not null auto_increment,
		sname varchar(100),
		collectorid int,
		suburl varchar(100),
		rname varchar(100),
		channel int,
		primary key(sid),
		key(collectorid))";
	print "creating $DATABASE.station .. <br>";
	$sql->query($query);

	$query = "create table program(
		pid int not null auto_increment,
		sid int not null,
		pname varchar(100),
		start datetime,
		stop datetime,
		record int,
		flag int,
		primary key(pid))";
	print "creating $DATABASE.program .. <br>";
	$sql->query($query);

	$query="create table specs(
		pid int not null,
		bitrate int,
		framerate int,
		height int,
		width int,
		id int not null auto_increment,
	primary key(id))";
	print "creating $DATABASE.specs .. <br>";
	$sql->query($query);

	$query = "create table codec(
		name varchar(100),
		cid int not null auto_increment,
		primary key(cid))";
	print "creating $DATABASE.codec ..<br>";
	$sql->query($query);

	print "populating $DATABASE.codec ..<br>";
	$sql->query("INSERT INTO codec VALUES ('divx ;-) low-motion', NULL)");

	$query="create table codecconfig(
		cid int,
		bitrate int,
		crispness int)";
	print "creating $DATABASE.codecconfig ..<br>";
	$sql->query($query);
	print "populating $DATABASE.codecconfig ..<br>";
	$sql->query("insert into codecconfig values(1,4000,100)");


	$query="create table config(
		name varchar(255),
		value varchar(255),
		id int not null auto_increment,
	primary key(id))";
	print "creating $DATABASE.config .. <br>";
	$sql->query($query);

	$sql->query("insert into config values ('savedir','/mnt',NULL)");
	$sql->query("insert into config values ('vcrprog','/usr/bin/vcr',NULL)");
	$sql->query("insert into config values ('timetostart','2',NULL)");
	$sql->query("insert into config values ('timetoend','2',NULL)");
	$sql->query("insert into config values ('codec','1',NULL)");
	$sql->query("insert into config values ('collectorid','2',NULL)");
	$sql->query("insert into config values ('keyframes','15',NULL)");
	$sql->query("insert into config values ('quality','100',NULL)");
	$sql->query("insert into config values ('recordsource','television',NULL)");
	$sql->query("insert into config values ('audiobitrate','128',NULL)");
	print "populating $DATABASE.config .. <br>";

	$query="GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP
           ON $DATABASE.*
           TO $USERNAME@localhost
		   IDENTIFIED BY '$PASSWORD'";
	print "creating $USERNAME and giving .. <br>";
	$sql->query($query);

	$query="FLUSH PRIVILEGES";
	$sql->query($query);
	print "reloading priviliges .. <br>";

	print "<h1><font color=\"red\">Important</font></h1>
	<b>cut and paste these lines to global.inc in the directory where
	you installed webvcr-2 (usally /var/www/webvcr2)<b><br>";
	print '<pre>
	&lt?
	$sql_host="localhost";
	$sql_user="'.$USERNAME.'";
	$sql_pass="'.$PASSWORD.'";
	$sql_db="'.$DATABASE.'";
	?&gt
	</pre>';
	exit;
}
?>

<html>
<FORM ACTION="init.php" METHOD=POST>
<table>
<tr><td>
Root Password of MySQL</td><td>:</td>
	<td><input type=PASSWORD name=PASS></td>
</tr><tr>
<td>MySQL Server Hostname</td><td>:</td>
	<td><input type=text name=HOSTNAME value="localhost"></td></tr>
<td>Database Name</td><td>:</td>
	<td><input type=text name=DATABASE value="tv"></td></tr>
<tr>
<td>User Name</td><td>:</td>
	<td><input type=text name=USERNAME value="tvuser"></td></tr>
</tr>
<td>Password</td><td>:</td>
	<td><input type=PASSWORD name=PASSWORD value=""></td></tr>
</table>
	<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Create">
</form>
</html>

