<?php
abstract class XtacData {
	//## Variables ############################
	protected $host;
	protected $database;
	protected $hasConnection;
	protected $connection;


	//## Constructor ###########################################
	public function __construct($inHost, $inDatabase=null) {
		$this->host = $inHost;

		if ($inDatabase !== null)
			$this->database = $inDatabase;

		$hasConnection = false;
	}


	//## Connection Management #################################
	abstract public function connect($inUsername, $inPassword);
	abstract public function disconnect();
	public function hasConnection(){
		return $this->hasConnection;
	}


	//## Fetching Operations ###################################
	abstract public function getUser($inID, $inCol, &$outUser);

	protected function translateValue($inValue) {
		if (strlen($inValue) !== 0 && $inValue[strlen($inValue) - 1] === 'Z' && is_numeric($inValue[0])){
			$time = mktime(
				/* Hours */	0,
				/* Minutes */	0,
				/* Seconds */	0,
				/* Month */	substr($inValue, 4,2),
				/* Day */		substr($inValue, 6,2),
				/* Year */	substr($inValue, 0,4)
			);

			return date('F j, Y', $time);
		}

		elseif (strlen($inValue) !== 0 && $inValue[0] === 'c' && $inValue[1] == 'n') {
			$temparray = array();
			$temparray = explode(',', $inValue);
			$inValue = implode(', ', $temparray);

			return $inValue;
		}

		else {
			switch ($inValue) {
				case '':
					return null;
					break;
				case 'TRUE':
				case 'true':
					return 'Y';
					break;
				case 'FALSE':
				case 'false':
					return 'N';
					break;
				case 'OFF':
					return 'N';
					break;
				case 'ON':
					return 'Y';
					break;
				default:
					return $inValue;
			}
		}
	}
}
?>
