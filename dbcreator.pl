#!/usr/bin/perl
use strict;
use warnings;
use DBI;



# MYSQL CONFIG VARIABLES
my($host)  = "localhost";
my($database)  = "test_db";
my($tablename) = "jobs";
my($username)  = "developer";
my($password)  = "SVSUd3v3lop3r";

my($dbh) = DBI->connect("DBI:mysql:${database};host=${host}", $username, $password, 
			   { RaiseError => 1 }
	   	       );

my($sth) = $dbh->prepare("SELECT * FROM ${tablename} WHERE status = 'pending'");
$sth->execute() or die $sth->errstr;
my($ref);
while ( $ref = $sth->fetchrow_hashref() ) 
{
	print "$$ref{'id'} \t $$ref{'databaseName'} \n";
}
