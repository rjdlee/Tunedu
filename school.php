<?php
	require_once 'main.php';

	class School extends Main 
	{
		public function all() 
		{
			$params = array('Province', 'City', 'Address', 'Postal', 'Type', 'TopGrade', 'BottomGrade');
			$return = '<div class="schoolContainer">';

			try {
				$qry = $this->conn->prepare('SELECT * FROM schools WHERE `id` = :id');
				$qry->execute(array('id' => $_GET['id']));
			} catch(PDOException $e) {
				return 'ERROR: ' . $e->getMessage();
			}

			while($data = $qry->fetch())
			{
				$return .= '<h1>'.$this->fixCapital($data['Name']).'</h1>';
				
				foreach($params as $key) {
					$return .= '<b>'.$key.'</b> '.$this->fixCapital($data[$key]).'</br>';
				}

				$return .= '<b>Phone</b> '.$this->formatPhone($data['Phone']).'</br>';
			}

			$return .= '</div>';
			return $return;
		}

		private function formatPhone($phone) 
		{
			return preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $phone);
		}
	}

	$school = new School($dbHost, $dbUsername, $dbName, $dbPassword);
	$nav = new Nav($dbHost, $dbUsername, $dbName, $dbPassword);

	echo '<div id="nav">' . $nav->createForm() . '</div>';
	echo $school->all();
?>