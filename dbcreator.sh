#!/bin/sh

USERNAME  = "developer"
DATABASE  = "test_db"
HOST	  = "localhost"
PASSWORD  = "SVSUd3v3lop3r"
JOB_TABLE = "jobs"
SUCCESS_JOBS_TABLE = "jobs_completed"
FAIL_JOBS_TABLE    = "jobs_failed"

# status is an enum value; 1 = "pending"
getJobsQuery = "SELECT * FROM $DATABASE.$JOB_TABLE WHERE status=1"

