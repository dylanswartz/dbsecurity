#!/usr/bin/perl
use strict;
use warnings;
# This script queries a table contaning a list of jobs to
# to preform on databases searching for jobs to "create"
# a database. For each create job, the script attempts
# to create the database. If the database is successfully
# created, then the job is marked complete and added to
# a table contaning "completed jobs"; if it fails, the 
# job is marked as "failed" and added to the "failed jobs"
# table.


# Use the DBI module
# The DBI is the standard database interface module for Perl.
# It defines a set of methods, variables and conventions that 
# provide a consistent database interface independent of the 
# actual database being used.
use DBI;

# Database config variables
my($host)      = "localhost";
my($database)  = "test_db";
my($username)  = "developer";
my($password)  = "SVSUd3v3lop3r";
my($dbms)      = "mysql";

# Database table names
my($tablename) 	      = "jobs";
my($successTableName) = "jobs_completed";
my($failTableName)    = "jobs_failed";

# Database field names
my($jobType)   = "job";
my($jobStatus) = "status";

# Get the database driver handle
# Used for database administration (e.g. database creation)
my($drh) = DBI->install_driver($dbms);

# Connnect to the databse 
my($dbh) = DBI->connect("DBI:${dbms}:${database};host=${host}", $username, $password, 
			   { RaiseError => 1 }
	   	       );

# Prepare database query to select jobs
my($select) = $dbh->prepare("SELECT * 
			     FROM  ${tablename} 
			     WHERE ${jobType}  ='create' 
			     AND   ${jobStatus}='pending' 
			     ORDER BY time");

# Other variable declarations
my($result);
my($create);
my($insert);
my($update);
my($errorFlag) 	  = 0; # assume no errors
my($errorMessage) = "An error occured. \n";
my($newStatus)	  = "";
# Execute select qyert
$select->execute() or die $select->errstr;

if ($select->rows < 1 ) {
	print "No jobs to process. \n";
}

while ( $result = $select->fetchrow_hashref() ) 
{
	# Try to complete job
	$create = $drh->func('createdb', $$result{'databaseName'}, 
			     $host, $username, $password, 'admin');

	# Determine if successful
	if ($create) {
		#if successful, insert into $successTableName
		$insert = $dbh->do("INSERT INTO $successTableName(id) 
				    VALUES($$result{'id'})");
		if (!$insert) {
			$errorFlag = 1;
			$errorMessage = "Database $$result{'databaseName'} created. Failed to insert into ${successTableName}";
		}
		print "Successfully created $$result{'databaseName'}\n";	
	} else {
		#if unsuccessful, insert into $failTableName
		$insert = $dbh->do("INSERT INTO $failTableName(id) 
			            VALUES($$result{'id'})");
		
		$errorFlag = 1;
		$errorMessage = "Failed to create $$result{'databaseName'}\n";
	}

	if (!$errorFlag) { # No errors! :D
		$newStatus = "complete";
	} else { 	   # Errors! =(
		$newStatus = "failed";
	}
	
	# Update the status of the job	
	$update = $dbh->do("UPDATE ${tablename}
			    SET ${jobStatus}='${newStatus}'
			    WHERE id = $$result{'id'}");

	if (!$update) {
		$errorFlag = 1;
		$errorMessage = "Failed to update status of job id $$result{'id'}";
	}

	if ($errorFlag) {
		print $errorMessage;
	}
}

$select->finish();
$dbh->disconnect();
