<?php
require_once 'XtacData.class.php';

class LDAP extends XtacData {
	// ## Class-variables ######################################
	protected $host;
	protected $database;
	protected $hasConnection;
	protected $connection;
	protected $lastBindDn;
	protected $lastBindPw;

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
	//   clean driver code goes to pieces.  Non $this public functions force the
	//   end-coder to keep track of which public functions return what...it's a
	//   mess.  Don't do it.

	//## Connection Management #################################
	//  connect() : connect and bind to the ldap server using the instance host and
	//              a given username and password
	public function connect($inUserName, $inPassword){
		$this->hasConnection = true;
		$this->connection = ldap_connect($this->host);

		$this->bind($inUserName, $inPassword);

		$this->connection;

		return $this;
	}
	public function disconnect() {
		ldap_unbind($this->connection);

		return $this;
	}


	//## Parent Methods ########################################
	public function getUser($inUsername, $inCol, &$outUser){
		$ldapArray = array();
		$tempResult = array();
		$ldapResults = null;

		$ldapResults = $this->search('o=svsu', "(cn=$inUsername)", $inCol);

		if ($ldapResults !== null && array_key_exists(0, $ldapResults)){
			foreach ($ldapResults[0] as $key => $value)
				if (!is_numeric($key)) {
					$ldapArray[$key] = is_array($value)?
						$value[0]:
						$value;
					$ldapArray[$key] = parent::translateValue($ldapArray[$key]);
				}

			$outUser = $ldapArray;
		}

		return $this;
	}

	public function resetPassword($inUsername) {
		$info = null;
		$newPass = '';
		$update = array();
		$userdn = '';
		$FileHandle = null;
		$LogMessage = '';

		$info = $this->search('o=svsu', "(cn=$inUsername)");

		if ($info['count'] === 1) {
			$userdn = $info[0]['dn'];
			$newPass = $this->generatePassword();

			// Prepare updates:
			$update['userPassword'] = $newPass;
			$update['loginGraceLimit'] = '20';
			$update['loginGraceRemaining'] = '5';
			$update['passwordExpirationTime'] = '20080223132322Z';

			// Fail with an error message if the update is unsuccesful
			if (!$this->update($userdn, $update)) {
				return false;
			}

			//Log String
			$LogMessage = @date('r') . ' - ' . $_SERVER['PHP_AUTH_USER'] . ' reset the password on ' . $userdn . "\n";
			file_put_contents('/var/log/passreset.log', $LogMessage, FILE_APPEND);

			echo '<div class="dg noborder">',
				'<dt>New Password:</dt>',
				'<dd>',$newPass,'</dd>',
				'</div>';
		}

		return $this;
	}
	public function addGraceLogins($inUserName) {
		$info = null;
		$update = array();
		$userdn = '';

		$info = $this->search('o=svsu', "(cn=$inUserName)");

		if ($info['count'] === 1) {
			$userdn = $info[0]['dn'];
			
			$update['loginGraceRemaining'] = '2';

			// Fail with an error message if the update is unsuccesful
			if (!$this->update($userdn, $update))
				return false;
			
			echo '<div class="dg noborder">',
				'<dt>New Grace Logins:</dt>',
				'<dd>2</dd>',
				'</div>';
		}

		return $this;
	}


	//## Distinct methods ######################################
	public function rebind() {
		$this->bind($this->lastBindDn, $this->lastBindPw);

		return $this;
	}


	//## Private Methods #######################################
	private function bind($inDn, $inPassword) {
		//Set Variables in case we need to reconnect
		$this->lastBindDn = $inDn;
		$this->lastBindPw = $inPassword;

		ldap_bind($this->connection, $inDn, $inPassword);

		return true;
	}
	private function search($inBase, $inFilter, $inArrAttribute = null) {

		$ldapSearch = false;       // Such-Handler
		$arrResult = Array();      // Suchergebnisse

		// Fail if there is no open connection to LDAP
		if (!$this->hasConnection())
			return false;

		$ldapSearch = (is_array($inArrAttribute))? 
			ldap_search($this->connection, $inBase, $inFilter, $inArrAttribute):
			ldap_search($this->connection, $inBase, $inFilter);

		return ldap_get_entries($this->connection, $ldapSearch);
	}

	private function update($inUserName, $inArrUpdate) {
		$tempArray = array();
		$success = false;

		foreach ($inArrUpdate as $key => $value) {
			$tempArray[$key] = $value;
			$success = (ldap_modify($this->connection, $inUserName, $tempArray))?
				true:
				false;
			$tempArray = array();
		}
		return $success;
	}
	

	private function generatePassword($length = 8) {
		// start with a blank password
		$password = "";

		// define possible characters
		$possible = "0123456789bcdfghjkmnpqrstvwxyz"; 

		// set up a counter
		$i = 0; 

		// add random characters to $password until $length is reached
		while ($i < $length) { 

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

			// we don't want this character if it's already in the password
			if (!strstr($password, $char)) { 
				$password .= $char;
				$i++;
			}

		}

		// done!
		return $password;

	}
}
?>
