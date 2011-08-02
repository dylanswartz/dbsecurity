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
use String::Random;

# Database config variables
my($host)      = "localhost";
my($database)  = "test_db";
my($username)  = "developer";
my($password)  = "SVSUd3v3lop3r";
my($dbms)      = "mysql";

# Database table names
my($tablename) 	        = "jobs";
my($successTableName)   = "jobs_completed";
my($failTableName)      = "jobs_failed";
my($databasesTableName) = "database_list";
my($configTableName)    = "config_data";

# Database field names
my($jobType)   = "job";
my($jobStatus) = "status";

# File paths
my($documentRoot) = "/home/dylan/public_html/";

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
my($newPassword)  = "";
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
		#if successful
		# insert into $databaseTableName
		$insert = $dbh->do("INSERT INTO ${databasesTableName}(name, creatorId) 
			            VALUES('$$result{'databaseName'}', $$result{'userId'})");
		if (!$insert) {
			$errorFlag = 1;
			$errorMessage = "Database $$result{'databaseName'} created. ".
					"Failed to insert into ${databasesTableName} table.";
		}

	} else {
		#if unsuccessful, insert into $failTableName
		$insert = $dbh->do("INSERT INTO ${failTableName}(id) 
			            VALUES($$result{'id'})");
		
		$errorFlag = 1;
		$errorMessage = "Failed to create $$result{'databaseName'}\n";
	}

	if (!$errorFlag) {
		#insert into job into $successTableName
		$insert = $dbh->do("INSERT INTO ${successTableName}(id) 
				    VALUES($$result{'id'})");
		if (!$insert) {
			$errorFlag = 1;
			$errorMessage = "Database $$result{'databaseName'} created. ".
					"Failed to insert into ${successTableName} table.\n";
		}
	}

	if (!$errorFlag) {
		# create user & config file for the new databse
		$newPassword = newPassword(16);
		my $create = createUser($dbh, $$result{'databaseName'}, $$result{'databaseName'}, $newPassword);
		if (!$create) {
			$errorFlag = 1;
			$errorMessage = "Database $$result{'databaseName'} created. ".
					"Failed to create user! No config file generated!\n";
		}	
	}

	if (!$errorFlag) {
		# this beast generates the config file and database config entries; 
		# if it fails, an error is set.
		if (!generateConfig($documentRoot.
				    $$result{'databaseName'}, 
				    $$result{'databaseName'}, 
				    $$result{'databaseName'}, 
				    $newPassword, "php",
			    	    $dbh, $configTableName)) {
			$errorFlag = 1;
			$errorMessage = "Database $$result{'databaseName'} created. ".
					"User created. No config file generated!\n";
		}
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
	} else {
		print "Successfully created $$result{'databaseName'}\n";
	}

}

$select->finish();
$dbh->disconnect();

# This function creates the required configuration file for a program
# to access a database. It also stores the config data in the database.
# @input  - pathToFile, databaseName, databaseUser, databaseUserPassword, typeOfFile, databseHandle
# @output - boolean value indicating success or failure
sub createConfigFile {
	my($pathToFile)      = $_[0];
	my($databaseName)    = $_[1];
	my($databaseUser)    = $_[2];
	my($databasePassword = $_[3];
	my($typeOfFile)      = $_[4];
	my($dbh)	     = $_[5];
	my($configTable)     = $_[6];

		
}

# This function creates a user with a all privlidges for
# the specided databse.
# @input  - databaseConnectionHandle, databaseName, username, password
# @output - boolean value indicating success or failure
sub createUser {
	my $dbh      = $_[0];
	my $database = $_[1];
	my $username = $_[2];
	my $password = $_[3];

	# Create user
	my $query = $dbh->do("CREATE USER '$username' IDENTIFIED BY '$password'");

	if (!$query) {
		return 0; # fail
	} else {# if user created
		# grant all privileges on $database
		$query = $dbh->do("GRANT ALL PRIVILEGES ON  `$database` .* TO  '$username';");
	}

	if (!$query) {
		return 0; # fail
	}

	return 1; 	  # success
}


# This function returns an 8 character random string
# NOTE: requires String::Random module
# http://search.cpan.org/~steve/String-Random-0.22/
#sub newPassword {
#	my($pass) = new String::Random;
#	return $pass->randpattern("CCcc!ccn");
#}

# This function returns a string of length n; where
# n is the first and only parameter. If no parameter
# is supplied, n = 8
# @input - [passwordLength]
sub newPassword {
	my $password;
	my $_rand;

	my $password_length = $_[0];
	if (!$password_length) {
		$password_length = 8;
	};
	
	my @chars = split(" ",
		          "a b c d e f g h i j k l m n o
			   p q r s t u v w x y z - _ % # |
			   0 1 2 3 4 5 6 7 8 9");
	srand;

	for (my $i=0; $i < $password_length ;$i++) {
		$_rand = int(rand 41);
		$password .= $chars[$_rand];
	}
	return $password;
}
