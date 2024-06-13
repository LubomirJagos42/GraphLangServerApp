<?php
include_once("ModelLogin.php");

#Model Schematic Nodes Class
class ModelSchematicNodes{
	private $db_conn;
	private $modelLogin;
	
	function __construct($db_conn){
		$this->db_conn = $db_conn;
		$this->modelLogin = new ModelLogin($db_conn);
	}
	
	function getProjectNodesClassNames(){
		$currUserId = $this->modelLogin->getCurrentUserId();
		$currUserProjectId = $this->modelLogin->getCurrentUserProjectId();
		
		$result = $this->db_conn->query("SELECT node_class_name, node_class_parent FROM storage_schematic_blocks WHERE node_owner=$currUserId AND node_project=$currUserProjectId;");
		$classNames = array();
		foreach ($result as $row) {
			array_push($classNames, $row['node_class_name']);
		}
		
		#
		#	HERE MUST BE ORDERING NODES BASED HOW THEY EXTEND EACH OTHERS
		#
		
		return $classNames;
	}
	
	/**
	 *	Returns if node is already stored for user and project.
	 *	There can be cases when node with same class name is different, it depends:
	 *		- same nodes class names but for different languages (ie. one for C++, one for python another for C|python)
	 */
	function isNodeDefined($userOwner, $projectId, $className){
		$isDefined = false;
		
		$queryStr = "";
		$queryStr .= "SELECT internal_id FROM storage_schematic_blocks WHERE";
		$queryStr .= " node_owner=$userOwner";
		$queryStr .= " AND node_project=$projectId";
		$queryStr .= " AND node_class_name='$className'";
		$queryStr .= ";";

		$result = $this->db_conn->query($queryStr);

		if ($result->num_rows > 0){
			$row = $result->fetch_row();
			$isDefined = $row[0];
		};
		
		#return false or if node is in DB then return its internal id
		return $isDefined;
	}
	
	/**
	 *	Store node into DB for user and project, if node is already defined it will do UPDATE instead of INSERT
	 */
	function saveNode($userOwner, $projectId, $nodeClassName, $nodeClassContent, $nodeClassParent = "", $nodeLanguage = "", $nodeDir = "", $nodeDisplayName = ""){
		$outputStr = "";
		$isNodeDefined = $this->isNodeDefined($userOwner, $projectId, $nodeClassName, $nodeLanguage);

		$nodeInternalId = -1;
		if (is_array($isNodeDefined)) $nodeInternalId = $isNodeDefined[0];
		else $nodeInternalId = $isNodeDefined;

		#
		#	Extract node parent from it's code using regular expression
		#		- what is strange to make regex running properly it needs to be wrap with some delimiter therefore there at start and end delimiter ~
		#		- stackoverflow issue: https://stackoverflow.com/questions/20705399/warning-preg-replace-unknown-modifier
		#
		$regexPattern = '~[\/\s\n]*([a-zA-Z0-9\.\-\_]+)[\s]*=[\s]*([a-zA-Z0-9\.\-]+)\.extend~';	
		$codeStr = hex2bin($nodeClassContent);
		$regexMatchGroups = array();
		preg_match($regexPattern, $codeStr, $regexMatchGroups);
		
		if (count($regexMatchGroups) > 0){
			$nodeClassName = $regexMatchGroups[1];	#get class name from content
			$nodeClassParent = $regexMatchGroups[2];	#get class parent from content
			$outputStr .= "regex group found, name: ". $nodeClassName ."\n";
			$outputStr .= "regex group found, parent: ". $nodeClassParent ."\n";
		}else{
			echo("regex NOT FOUND using parent from POST\n");
		}

		$queryStr = "";
		if ($isNodeDefined){
			$queryStr .= "UPDATE storage_schematic_blocks SET";
			$queryStr .= " node_class_name='$nodeClassName'";
			$queryStr .= ", node_class_parent='$nodeClassParent'";
			$queryStr .= ", node_content_code=UNHEX('$nodeClassContent')";
			$queryStr .= ", node_directory='$nodeDir'";
			$queryStr .= ", node_language='$nodeLanguage'";
			$queryStr .= ", node_display_name='$nodeDisplayName'";
			$queryStr .= " WHERE";
			//$queryStr .= "  node_owner=$userOwner";
			//$queryStr .= "  AND node_project=$projectId";
			//$queryStr .= "  AND node_class_name='$nodeClassName'";
			$queryStr .= " internal_id=$nodeInternalId";
			$queryStr .= ";";
			
			$outputStr .= "saveNode() - UPDATE is used, node internal id: $nodeInternalId\n";
		}else{
			$queryStr .= "INSERT INTO storage_schematic_blocks (node_owner, node_project, node_display_name, node_class_name, node_class_parent, node_content_code, node_directory, node_language) VALUES";
			$queryStr .= " ($userOwner, $projectId, '$nodeDisplayName', '$nodeClassName', '$nodeClassParent', UNHEX('$nodeClassContent'), '$nodeDir', '$nodeLanguage');";

			$outputStr .= "saveNode() - INSERT is used\n";			
		}
		$result = $this->db_conn->query($queryStr);
		$affected_rows = $this->db_conn->affected_rows;

		$outputStr .= "Query result: $result\n";
		$outputStr .= "\tAffected rows: $affected_rows\n";

		return $outputStr;
	}
	
	##
	#	Returns node ordered to be able generate javascript to be included in html without any errors that some class are not defined when class extends some toher class.
	#
	function getOrderedNodesForProject($userOwner, $projectId){
		$queryStr = "";
		//$queryStr .= "SELECT * FROM storage_schematic_blocks WHERE node_owner=$userOwner AND node_project=$projectId;";
		$queryStr .= "SELECT node_directory, node_display_name, node_class_name, node_class_parent, internal_id FROM storage_schematic_blocks WHERE node_owner=$userOwner AND node_project=$projectId;";
		
		$result = $this->db_conn->query($queryStr);

		$nodesArray = array();
		while ($row = $result->fetch_row()) {
			$nodeItem = array();
			$nodeItem['node_directory'] = $row[0];
			$nodeItem['node_display_name'] = $row[1];
			$nodeItem['node_class_name'] = $row[2];
			$nodeItem['node_class_parent'] = $row[3];
			$nodeItem['internal_id'] = $row[4];

			array_push($nodesArray, $nodeItem);
		}		

		#ordering node array to have it in right order for include in html in javascript to include first classes which are in later classes extended
		$k = 0;
		while (true){
				for ($j = 0; $j < $k; $j++){
					if ($nodesArray[$k]['node_class_name'] == $nodesArray[$j]['node_class_parent']){
						#swap elements
						$temp = $nodesArray[$j];
						$nodesArray[$j] = $nodesArray[$k];
						$nodesArray[$k] = $temp;

						#set k to 0 to start ordering from beginning
						$k = 0;
						break;
					}
				}
				$k += 1;
				if ($k >= count($nodesArray)) break;
		}
		
		return $nodesArray;
	}
	
	function getJavascriptForNodes($userOwner, $projectId){
		$orderedNodesList = $this->getOrderedNodesForProject($userOwner, $projectId);
		
		$nodeCounter = 0;
		foreach ($orderedNodesList as $node){
			$queryStr = "SELECT node_content_code FROM storage_schematic_blocks WHERE internal_id=". $node['internal_id'] ." AND node_owner=$userOwner AND node_project=$projectId;";
			$result = $this->db_conn->query($queryStr);
			foreach ($result as $row) {
				echo($row['node_content_code']);
				echo("\n");

				/*
				if ($nodeCounter == 75){
					echo($row['node_content_code'] .'"');
				}
				*/
			}
			
			$nodeCounter++;
			//if ($nodeCounter == 74) break;
		}
		echo("\n");
		
	}
	
}
?>