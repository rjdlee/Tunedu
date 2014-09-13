<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>School and Parents</title>

		<link rel="stylesheet" type="text/css" href="main.css"/>
	</head>
	<body>
	</body>
</html>

<?php
	//Include in all files which require a database connection.
	include_once "dbLogin.php";

	class Main {

		protected $conn;
		protected $ADone;

		function __construct($dbHost, $dbUsername, $dbName, $dbPassword) 
		{

			if(empty($this->conn)) 
			{	
				date_default_timezone_set('America/Toronto');

				try {
			   		$this->conn = new PDO('mysql:host='.$dbHost.';dbname='.$dbName, $dbUsername, $dbPassword);

					$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			   		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			   		return $this->conn;
				} catch(PDOException $e) {
			    	return 'ERROR: ' . $e->getMessage();
				}
			}
		}

		static function isDefined($val) {
			return isset($val) && !empty($val);
		}

		static function fixCapital($string) {
			$string =ucwords(strtolower($string));

		    foreach (array('-', '/', '"', '(', '.') as $delimiter) {
		      if (strpos($string, $delimiter)!==false) {
		        $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
		      }
		    }
		    return $string;
		}
	}

	class Nav extends Main
	{
		/*
		Ask what country the user is from, either Canada or the US.
		*/
		public function checkCountry() 
		{
			if(!isset($_GET['country'])) 
			{
				return false;
			} else {
				return true;
			}
		}

		/*
		Returns the search form with provinces or states depending on country.
		*/
		public function createForm() 
		{
			$values = array('name' => '', 'province' => 'Any', 'city' => '', 'postal' => '');

			foreach($values as $key => $val) 
			{
				if(isset($_GET[$key]) && !empty($_GET[$key]))
				{
					$values[$key] = $_GET[$key];
				}
			}

			$form = "<form action='/school/' method='get'>
						<input name='name' type='text' placeholder='School Name' value='" . $values['name'] . "'/>
						<div class='selectDiv'><div class='selectStyle'><select name='province'><optgroup>
							<option value=''>" . 'Any' . "</option>";
						
			foreach($this->getProvinces() as $val)
			{
				$selected = '';

				if($values['province'] == $val)
					$selected = "selected='selected'";

				$form .= "<option value='" . $val . "' " . $selected . ">" . $val . "</option>";
			}

			$form .= "	</optgroup></select></div><div class='selectArrow'></div></div>
						<input name='city' type='text' placeholder='City' value='" . $values['city'] . "'/>
						<input name='postal' type='text' placeholder='Zip or Postal Code' maxlength=7 value='" . $values['postal'] . "'/>
						<input type='submit' value='Search'/>
					</form>";

			return $form;
		}

		/*
		Returns all unique provinces and states in the database.
		*/
		private function getProvinces() {
			try {
				$qry = $this->conn->prepare('SELECT `Province` FROM schools GROUP BY `Province`');
				$qry->execute();
			} catch(PDOException $e) {
				return 'ERROR: ' . $e->getMessage();
			}

			$provinces = array();

			while($data = $qry->fetch()) 
			{
				array_push($provinces, $data['Province']);
			}

			return $provinces;
		}
	}
?>