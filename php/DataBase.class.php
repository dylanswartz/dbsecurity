<?php
require_once 'XtacData.class.php';
require_once 'print_nice.php';

class DataBase extends XtacData {

	// ## Class-variables ##############################################
	protected $host;
	protected $database;
	protected $hasConnection;
	protected $connection;
	protected $arrData = Array();
	protected $arrFieldname = Array();

	// WARNING: Fluent Interface Ahead!
	//   This class tries to conform to the fluent-interface paradigm for public
	//   function returns.  See http://devzone.zend.com/article/1362 for an in 
	//   depth explanation.  The gist is that if every public function returns 
	//   $this, then writing driver code that uses this class is ridiculously easy.
	//   
	//   The only gotcha is that if you need to send output somewhere, it has to 
	//   be through a pass-by-reference parameter.
	//
	//   private and protected functions can return values as normal, since they
	//   aren't called explicitely by any driver code.  A purely fluent app would
	//   force these to return $this too, but as far as I can see, there's no
	//   real good reason for it.
	//
	//   Any new public functions must return $this, otherwise the whole idea of
	//   clean driver code goes to pieces.  If you write a public function that
	//   doesn't return $this you force the end-coder to keep track of which
	//   public functions return what...it's a mess.  Don't do it.

	//## Connection Management #################################
	public function connect($inUsername, $inPassword) {
		// Use an already active connection if one exists
		$newMysqlHandler = $this->hasConnection?
			$this->connection:
			new mysqli($this->host, $inUsername, $inPassword, $this->database);

		$this->hasConnection = true;
		$this->connection = $newMysqlHandler;

		return $this;
	}
	public function disconnect() {
		$this->connection->close();
		$this->hasConnection = false;

		return $this;
	}



	//## Parent Methods ########################################
	public function getUser($inID, $inCol, &$outUser){
		// Look for a user with the given id in the xtac table and the users2keep
		// table.  If it exists in either, send it out as $outUser
		$xtacResults = array();
		$users2keepResults = array();
		$xmtdResults = array();
		$results = array();

		$xtacResults = $this->query('xtac', "PersonID = $inID", '', $inCol);
		$users2keepResults = $this->query('users2keep', "ID like $inID", '', 'ID as PersonID, Login');
		$xmtdResults = $this->query('xmtd', "ID like $inID", '', 'ID as PersonID, Login');
		$results = array_merge($xtacResults, $users2keepResults, $xmtdResults);

		foreach ($results[0] as $key => $value)
			$outUser[$key] = parent::translateValue($value);

		return $this;
	}



	//## Distinct methods ######################################
	public function getColumns($inTable, &$outColumns){

		$queryString = '';         // Abfragestring, der an die Datenbank gesendet wird
		$mysqlQuery = false;       // Das Ergebnis der Abfrage
		$mysqlHandler = false;     // Verbindungs-Handler
		$result = Array();         // Die Rueckgabe


		// Verbindung mit der Datenbank aufbauen
		if($this->hasConnection) {

			$queryString = 'show columns from ' . $this->database . '.' . $inTable . ';';

			$mysqlQuery = $this->connection->query($queryString);

			while($mysqlResult = $mysqlQuery->fetch_assoc())
				$result[] = $mysqlResult;
		}
		else
			trigger_error('Not connected to database', E_USER_ERROR);

		$outColumns = $result;

		return $this;
	}
	public function getAttributes(&$outAttributes) {
		// Gets an array of all fields in the xtac database
		$outAttributes = $this->query('fields');

		return $this;
	}
	public function getRole($inLogin, &$outRole) {
		// Retrieve the access role of the given user.
		//
		// Not many people need access to this site, so a list of people that have
		// each role is just stored in the roles table in the xtac database.
		$result = Array();
		$result = $this->query('access_control', "login='$inLogin'");

		$outRole = (array_key_exists(0, $result))?
			$result[0]['role']:
			'library';

		return $this;
	}
	public function getAuthorizedFields($inLogin, &$outMysql, &$outLdap) {
		// Not everyone has access to every field in the database.  Interns,
		// for instance, really don't need to see people's social security number
		// This function checks a user's role, then returns arrays of the 
		// fields in xtac and ldap which that user has access to.

		//## Initialize Variables #############################
		$result = Array();
		$role = '';

		$this->getRole($inLogin, $role);

		//## Perform Query ####################################
		$result = $this->query('roles', "role='$role'");

		//## Process Results ##################################
		$outMysql = $result[0]['MySQLFields'];
		$ldapString = $result[0]['LDAPFields'];
		$outLdap = explode(',',$ldapString);

		return $this;
	}
	public function canResetPassword($inLogin, &$result) {
		// Not every user should be able to reset people's passwords.  This
		// function just checks the roles table to see if the current user
		// has sufficient permission to reset a password.  If so, it displays
		// the password reset button, if not, it does not.

		$query  = 'access_control.login = \'';
		$query .= $_SERVER['PHP_AUTH_USER'];
		$query .= '\' and access_control.role = roles.role';
		$tmpResults = array();

		$tmpResults = $this->query('roles, access_control',$query,'','roles.AllowPasswordReset');

		$result = $tmpResults[0]['AllowPasswordReset'];

		return $this;
	}
	public function checkMSEligibility($inID, &$outResult) {
		// Regular employees have access to a library of Microsoft Software
		// This function checks whether or not a given user id has access
		// to that library.
		//
		// Only the library role uses this function right now.
		$result = array();

		$result = $this->query('xtac', "PersonID like '%$inID'", '', 'RegEmployee');
		$outResult = (@$result[0]['RegEmployee'] === 'Y')?
			true:
			false;

		return $this;
	}


	public function getUsername($inID, &$outUsername) {
		// Searches for a given id in the xtac and users2keep tables and 
		// returns the username that belongs to that id.
		//
		// outUsername will be false if the id doesn't exist.
		$result = Array();
		$xtacResult = array();
		$users2keepResult = array();
		$xmtdResult = array();

		$xtacResult = $this->query('xtac', "PersonID = $inID", '', 'Login');
		$users2keepResult = $this->query('users2keep', "ID = $inID", '', 'Login');
		$xmtdResult = $this->query('xmtd', "ID = $inID", '', 'Login');

		$result = array_merge($xtacResult, $users2keepResult, $xmtdResult);


		//## Fail silently if no-one has that userID
		$outUsername = (!@$result[0])?
			false:
			$result[0]['Login'];

		return $this;
	}
	public function getCriticalUsers(&$outUsers) {
		// There are a few users in xtac whose passwords shouldn't be able to 
		// be changed by any random intern with an xtac login.  This function
		// Checks the cricitalusers table for a list of those users (called...
		// wait for it...Critical Users).  It returns a single-dimensional array
		// of those users.

		$result = array();

		$result = @$this->query('criticalusers', '', '', 'Login');

		foreach ($result as $key => $value) {
			$outUsers[] = $value['Login'];
		}

		return $this;
	}
	public function getHistory($inID, &$outHistory){
		// This function fetches the support history for a given user id.  It
		// returns a two-dimmensional array of support center logs (timestamp,
		// support staffer who logged the incident and any comments they made)

		$result = Array();
		$result = $this->query('history', "PersonID like '$inID'", 'TimeStamp,StaffMember');

		if (empty($result[0]))
			$outHistory = null;
		else
			$outHistory = $result;

		return $this;
	}
	public function addComment($inID, $inUser, $inComment){
		// Adds a comment to the support history of the given user id.  This is the
		// function that performs the actual query that records the current time,
		// the current support staffer, and any comments in the history table.
		
		$dataToInsert = array($inID, $inUser, 'NOW()', $this->connection->real_escape_string($inComment));
		$this->insert('history', $dataToInsert);

		echo '<div class="dg newComment">',
			'<dt class="timestamp">just now</dt>',
			'<dd class="staff">',$inUser,'</dd>',
			'<dd class="comments">',$inComment,'</dd>',
			'</div>';

		return $this;
	}
	public function addField($inCanonicalName, $inReadableName, $inCategory, $inMysqlField='NULL', $inLdapField='NULL', $inRole){
		$MysqlInsertString = ',' . $inMysqlField;
		$LdapInsertString = ',' . $inLdapField;
		$dataToInsert = array(
			$inCanonicalName,
			$inReadableName,
			'NULL',	// HTML Class - used for data that need special formating
			'NULL',	// HTML Parent ID - used for multi-field data like "name = first + middle + last"
			$inCategory,
			$inLdapField,
			$inMysqlField);
		$this->insert('fields', $dataToInsert);

		$testresult = $this->query('fields', 'htmlid=\''.$inCanonicalName.'\'');
		print_nice($testresult);

		if ($inMysqlField)
			$this->updateVarChar('roles', 'MySQLFields', $MysqlInsertString, true, 'role', $inRole);
		if ($inLdapField)
			$this->updateVarChar('roles', 'LdapFields', $LdapInsertString, true, 'role', $inRole);

		return $this;
	}
	public function searchUsers($inCriteria, &$outResults) {
		// This function takes a string of $inCriteria and searches through
		// xtac and users2keep looking for a user that matches.  It figures out
		// which terms in the database the user is looking for and performs the
		// proper query automatically.
		//
		// For security reasons, these queries should only ever return the last name,
		// first name, user id and username of an individual.
		//
		// This function is primarily called asynchronously by the javascript
		// autocomplete library for amazing on-the-fly searching awesomeness.

		$searchTerms = array();
		$xtacResults = array();
		$users2KeepResults = array();
		$xmtdResults = array();

		// If there's a comma in the search string, the user is probably searching for
		// a name in "last, first" format.
		if (strpos($inCriteria, ',')) {
			$searchTerms = explode(',', $inCriteria);
			$lastName = trim($searchTerms[0]);
			$firstName = trim($searchTerms[1]);

			$Filter = "Lastname like '%$lastName%' and ".
					"NickName like '%$firstName%'";
		}

		// If there's a space in the search string, the user is probably searching for
		// a name in "first last" format.
		elseif (strpos($inCriteria, ' ')) {
			$searchTerms = explode(' ', $inCriteria);

			$Filter = "NickName like '%$searchTerms[0]%' and ".
					"LastName like '$searchTerms[1]%'";
		}

		else {
			$Filter = "Login like '$inCriteria%' or ".
					"LastName like '$inCriteria%' or ".
					"NickName like '$inCriteria%' or ".
					"PersonID like '%$inCriteria%'";
		}


		$SortOrder = 'LastName, NickName';
		$SelectedColumns = 'LastName, NickName, PersonID, Login';

		$xtacResults = $this->query('xtac', $Filter, $SortOrder, $SelectedColumns);
		$users2KeepResults = $this->query('users2keep', "Login like '$inCriteria%'", 'Login', 'ID as PersonID, Login');
		$xmtdResults = $this->query('xmtd', "Login like '$inCriteria%'", 'Login', 'ID as PersonID, Login');

		$outResults = array_merge($xtacResults, $users2KeepResults, $xmtdResults);

		return $this;
	}



	//## Generic Query ###################################################
	private function query($inTable, $inFilter = '', $inOrder = '', $inCol = '*'){
		// Perform a query on a given table, using an optional filter string, an
		// optional orderby string and an optional list of columns to return

		$queryString = '';         // Abfragestring, der an die Datenbank gesendet wird
		$mysqlQuery = false;       // Das Ergebnis der Abfrage
		$mysqlHandler = false;     // Verbindungs-Handler
		$result = Array();         // Rueckgabe


		// Verbindung mit der Datenbank aufbauen
		if($this->hasConnection){
			// Abfrage starten
			$queryString = 'select ' . $inCol
				. ' from ' . $inTable;
			if($inFilter != '')
				$queryString .= ' where ' . $inFilter;
			if($inOrder != '')
				$queryString .= ' order by ' . $inOrder;
			$queryString .= ';';

			if (!$mysqlQuery = $this->connection->query($queryString))
				trigger_error("Query: " . $queryString . "\n", E_USER_NOTICE);

			$result = Array();
			while($mysqlResult = $mysqlQuery->fetch_assoc())
				$result[] = $mysqlResult;
		}
		else {
			trigger_error('Not connected to database', E_USER_ERROR);
			$result = false;
		}

		return $result;
	}

	private function insert($inTable, $inData){
		$queryString = '';
		$firstItem = true;

		if($this->hasConnection){
			$queryString = 'insert into ' . $inTable . ' values(';
					foreach ($inData as $value) {
					if ($firstItem === false)
					$queryString .= ', ';
					else 
					$firstItem = false;

					$queryString .= (substr($value, -2) === '()')?
					$value:
					"'$value'";
					}

					$queryString .= ');';

			return $this->connection->query($queryString);
		}
	}
	private function updateVarChar($inTable, $inField, $inData, $append=false, $conditionField, $conditionValue){
		// Only works for strings right now
		$queryString = '';
		$firstItem = true;

		if ($this->hasConnection){
			$queryString = 'update ' . $inTable . ' set ' . $inField . '=';
			$queryString = ($append)?
				$queryString . 'concat(' . $inField . ',\'' . $inData . '\')':
				$queryString . '\'' . $inData . '\'';
			$queryString =  $queryString . 'where ' . $conditionField . '=\'' . $conditionValue . '\';';

			echo $queryString;

			return $this->connection->query($queryString);
		}
	}

	//## Testing Suite ###################################################
	public function connected(){
		echo 'hasConnection says ', $this->hasConnection;
		echo '<br />';
		echo 'connection says ', print_r($this->connection);
		echo '<br />';
		echo '<br />';

		return $this;
	}
	public function role() {
		$temprole = '';
		$this->getRole($_SERVER['PHP_AUTH_USER'], $temprole);
		echo '<h1>Role: ',$temprole,'</h1>';
		echo '<br />';
		echo '<br />';
		
		return $this;
		
	}

	
}
?>
