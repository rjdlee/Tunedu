<?php
	/*
	Included in: index.php
	Description: All search form functionaility.
	*/

	require_once 'main.php';
	
	class Search extends Main
	{
		/*
		Check the form input and find all fields that are filled out. $params are the inputs it iterates through (foreach loop) and
		all the ones with input get added to the $paramsDefined array. If there are no filled inputs, nothing happens (return false).
		Otherwise, it returns or gives $paramsDefined to whatever called this function (at the bottom of this page).
		*/
		public function checkParams()
		{	
			$params = array('name', 'province', 'city', 'postal');
			$paramsDefined = array();
			$filter = false;

			foreach($params as $val)
			{
				if(isset($_GET[$val]) && !empty($_GET[$val]))
				{
					array_push($paramsDefined, $val);
					$filter = true;
				}
			}

			if($filter)
				return $paramsDefined;
			else
				return false;
		}

		/*
		Execute the search.
		*/
		public function prepare($paramsDefined)
		{
			//Create two variables, one ($prepare) for the query and one ($execute) to pass variables to the query.
			$prepare = 'SELECT *, ';
			$prepareRelevance = '';
			$prepareWhere = 'WHERE ';

			$execute = array();

			//Iterate through all the $paramsDefined, refer to the first function, checkParams().
			foreach($paramsDefined as $key => $val) 
			{

				$prepareRelevance .= '(MATCH (`' . $val . '`) AGAINST (:'.$val.'1 IN NATURAL LANGUAGE MODE))';
				$prepareWhere .= '(MATCH (`' . $val . '`) AGAINST (:'.$val.'2 IN NATURAL LANGUAGE MODE))';

				$execute[':' . $val . '1'] = '%' . $_GET[$val] . '%';
				$execute[':' . $val . '2'] = '%' . $_GET[$val] . '%';

				if($key < count($paramsDefined) - 1) 
				{
					$prepareRelevance .= ' + ';
					$prepareWhere .= ' OR ';
				}
			}

			$prepareRelevance .= ' AS relevance';
			$prepare .= $prepareRelevance . ' FROM schools ' . $prepareWhere . ' ORDER BY relevance DESC LIMIT 0,9';
			
			//Finally execute the query, but if it fails, return an error. $qry stores all information from the executed query.
			try {
				$qry = $this->conn->prepare($prepare);
				$qry->execute($execute);
			} catch(PDOException $e) {
				return 'ERROR: ' . $e->getMessage();
			}

			$searchText = '<div id="searchResults"><div class="description">Search Results</div>';

			//Check to see if any results were returned, otherwise write out, 0 Results.
			if($qry->rowCount() > 0) 
			{
				//$qry->fetch() takes each row from the executed query one at a time and saves it to the $data variable.
				while($data = $qry->fetch())
				{
					//The $data variable is actually an array and contains data from each column in the table. Outputting it organizedly here.
					$searchText .= ('<a href="school.php?id='.$data['id'].'"><div class="result"><h3>' .
						$this->fixCapital($data['Name'] . '</h3> ' . 
							$data['Address'] . ', ' . $data['City'] . ', ' . $data['Province']) . 
						'</div></a>');
				}

				$searchText .= '</div>';
				return $searchText;

			} else {
				$searchText .= '0 Results</div>';
				return $searchText;
			}
		}
	}

	//Create a new object of the class, Search.
	$search = new Search($dbHost, $dbUsername, $dbName, $dbPassword);
	$nav = new Nav($dbHost, $dbUsername, $dbName, $dbPassword);

	//assign the $checkParams variable to whatever $conn->checkParams() gives, either false or $paramsDefined.
	$checkParams = $search->checkParams();

	echo '<div id="nav">' . $nav->createForm() . '</div>';

	//If $checkParams is not false, continue to search.
	if($checkParams)
	{
		echo $search->prepare($checkParams);
	}

?>