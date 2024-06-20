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
	
	function getProjectNodesClassNames($currUserId, $currUserProjectId){
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
	function saveNode($userOwner, $projectId, $nodeClassName, $nodeClassContent, $nodeClassParent = "", $nodeLanguage = "", $nodeDir = "", $nodeDisplayName = "", $nodeIsHidden = false, $nodeCategoryName = ""){
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
			$queryStr .= ", node_isHidden=$nodeIsHidden";
			$queryStr .= " WHERE";
			//$queryStr .= "  node_owner=$userOwner";
			//$queryStr .= "  AND node_project=$projectId";
			//$queryStr .= "  AND node_class_name='$nodeClassName'";
			$queryStr .= " internal_id=$nodeInternalId";
			$queryStr .= ";";

            $result = $this->db_conn->query($queryStr);

			$outputStr .= "saveNode() - UPDATE is used, node internal id: $nodeInternalId\n";
		}else{
			$queryStr .= "INSERT INTO storage_schematic_blocks (";
            $queryStr .= "node_owner";
            $queryStr .= ", node_project";
            $queryStr .= ", node_display_name";
            $queryStr .= ", node_class_name";
            $queryStr .= ", node_class_parent";
            $queryStr .= ", node_content_code";
            $queryStr .= ", node_directory";
            $queryStr .= ", node_language";
            $queryStr .= ", node_isHidden";
            $queryStr .= ") VALUES (";
			$queryStr .= "$userOwner";
            $queryStr .= ", $projectId";
            $queryStr .= ", '$nodeDisplayName'";
            $queryStr .= ", '$nodeClassName'";
            $queryStr .= ", '$nodeClassParent'";
            $queryStr .= ", UNHEX('$nodeClassContent')";
            $queryStr .= ", '$nodeDir'";
            $queryStr .= ", '$nodeLanguage'";
            $queryStr .= ", $nodeIsHidden";
            $queryStr .= ");";

			$outputStr .= "saveNode() - INSERT is used\n";

            $result = $this->db_conn->query($queryStr);
            if ($result === TRUE) {
                $nodeInternalId = $this->db_conn->insert_id;
            } else {
                $outputStr .= "ERROR: ". $conn->error ."\n";
            }
		}
		$affected_rows = $this->db_conn->affected_rows;

		$outputStr .= "Query result: $result\n";
		$outputStr .= "\tAffected rows: $affected_rows\n";

        #
        #   Save CATEGORY for node
        #
        $categoryId = -1;
        if ($nodeInternalId > -1 && $nodeCategoryName != "" && !($nodeIsHidden == "" || $nodeIsHidden == false || $nodeIsHidden == 0)){
            $queryStr = "SELECT * FROM project_categories WHERE project_id=$projectId AND category_name='$nodeCategoryName';";
            $result = $this->db_conn->query($queryStr);

            if ($result->num_rows > 0){
                $row = $result->fetch_row();
                $categoryId = $row[0];
            }else{
                $queryStr = "INSERT INTO project_categories (category_name, project_id) VALUES ('$nodeCategoryName', $projectId);";
                $result = $this->db_conn->query($queryStr);
                if ($result === TRUE){
                    $categoryId = $this->db_conn->insert_id;
                }
            }
        }
        if ($nodeInternalId > -1 && $categoryId > -1){
            $queryStr = "INSERT INTO nodes_to_category_assignment (category_id, node_id, project_id) VALUES ($categoryId, $nodeInternalId, $projectId);";
            try{
                $result = $this->db_conn->query($queryStr);
            }catch (Exception $e){}

            if ($result === TRUE){
                $outputStr .= "Node $nodeDisplayName ($nodeInternalId, $nodeClassName) added to category '$nodeCategoryName' ($categoryId)\n";
            }else{
                $outputStr .= "ERROR: CATEGORY: ". $this->db_conn->error ."\n";
            }
        }else{
            $outputStr .= "No category defined for node.\n";
        }

		return $outputStr;
	}
	
	##
	#	Returns node ordered to be able generate javascript to be included in html without any errors that some class are not defined when class extends some toher class.
	#
	function getOrderedNodesForProject($userOwner, $projectId){
        $userOwner = (int) $userOwner;
        $projectId = (int) $projectId;

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

    /**
     * @param $userOwner
     * @param $projectId
     * @return void
     * @description This returns full javascript definitions of all nodes for user project in right ordeer ie. if there is some node which is
     * extension of some other node is inserted later to have this parent node to be defined.
     */
    function getJavascriptForNodes($userOwner, $projectId){
        $userOwner = (int) $userOwner;
        $projectId = (int) $projectId;

		$orderedNodesList = $this->getOrderedNodesForProject($userOwner, $projectId);
		
		foreach ($orderedNodesList as $node){
			$queryStr = "SELECT node_content_code FROM storage_schematic_blocks WHERE internal_id=". $node['internal_id'] ." AND node_owner=$userOwner AND node_project=$projectId;";
			$result = $this->db_conn->query($queryStr);
			foreach ($result as $row) {
				echo($row['node_content_code']);
				echo("\n");
			}
		}
		echo("\n");
	}

    /**
     * @param $userOwner
     * @param $projectId
     * @return void
     * @description This will return string javascript object for library block initialization. Since every schematic blocs is part of something
     * similar to GraphLang.LibraryBlocks.SomeCategory... therefore these objects must be first initializied like raphLang = {},
     * then GraphLang.LibraryBlocks = {} and so to overcome javascript error that these variables are not defined.
     */
    function getJavascriptObjectsInitDefinitionForProject($userOwner, $projectId){
        $userOwner = (int) $userOwner;
        $projectId = (int) $projectId;

        $queryStr = "SELECT node_class_name FROM `storage_schematic_blocks` WHERE node_owner=$userOwner AND node_project=$projectId AND node_class_name NOT LIKE 'draw2d%' GROUP BY node_class_name;";
        $result = $this->db_conn->query($queryStr);

        $objectClassArray = array();
        $k = 0;
        foreach ($result as $row) {
            array_push($objectClassArray, array());
            $objectClassArray[$k] = explode('.', $row['node_class_name']);
            $k++;
        }

        $alreadyDefinedObjects = array();
        foreach ($objectClassArray as $objectTreeItemArray){
            $objectToBeDefined = "";
            $k = 0;
            foreach ($objectTreeItemArray as $objectName){
                #don't do object definition for last element
                if ($k != count($objectTreeItemArray)-1){
                    if ($objectToBeDefined != "") $objectToBeDefined .= '.';
                    $objectToBeDefined .= $objectName;

                    #object definition is already done
                    if (!in_array($objectToBeDefined, $alreadyDefinedObjects)){
                        array_push($alreadyDefinedObjects, $objectToBeDefined);
                    }
                }
                $k++;
            }
        }
        return $alreadyDefinedObjects;
    }

    function getNodesWithCategories($userOwner, $projectId){
        $userOwner = (int) $userOwner;
        $projectId = (int) $projectId;

        $queryStr = "";

        $queryStr .= "SELECT";
        $queryStr .= "    project_categories.category_name,";
        $queryStr .= "    storage_schematic_blocks.node_class_name,";
        $queryStr .= "    storage_schematic_blocks.node_display_name";
        $queryStr .= " FROM `storage_schematic_blocks`";
        $queryStr .= " LEFT JOIN nodes_to_category_assignment";
        $queryStr .= " ON";
        $queryStr .= "    storage_schematic_blocks.node_project = nodes_to_category_assignment.project_id AND";
        $queryStr .= "    storage_schematic_blocks.internal_id = nodes_to_category_assignment.node_id";
        $queryStr .= " LEFT JOIN project_categories";
        $queryStr .= " ON";
        $queryStr .= "    nodes_to_category_assignment.project_id = project_categories.project_id AND";
        $queryStr .= "    nodes_to_category_assignment.category_id = project_categories.internal_id";
        $queryStr .= " WHERE";
        $queryStr .= "    storage_schematic_blocks.node_owner=$userOwner AND";
        $queryStr .= "    storage_schematic_blocks.node_project=$projectId AND";
        $queryStr .= "    storage_schematic_blocks.node_isHidden=false";
        $queryStr .= " ORDER BY";
        $queryStr .= "	  project_categories.category_name,";
        $queryStr .= "    storage_schematic_blocks.node_display_name;";

        $result = $this->db_conn->query($queryStr);

        $nodesByCategories = array();
        $nodesByCategories[0] = array();    #default category for nodes with no category
        while ($row = $result->fetch_row()){
            $categoryName = $row[0] ? $row[0] : 0;
            if (!array_key_exists($categoryName, $nodesByCategories)) $nodesByCategories[$categoryName] = array();
            array_push($nodesByCategories[$categoryName], array($row[1], $row[2]));
        }

        return $nodesByCategories;
    }

    function getUserProjectList($userOwner){
        $userOwner = (int) $userOwner;

        $queryStr = "SELECT internal_id, project_graphlang_version, project_name, project_visibility, project_image, project_description, project_code_template FROM user_projects WHERE project_owner=$userOwner;";
        $result = $this->db_conn->query($queryStr);

        $projectsList = array();
        foreach ($result as $row) {
            array_push($projectsList, array(
                "id" => $row["internal_id"],
                "ideVersion" => $row["project_graphlang_version"],
                "name" => $row["project_name"],
                "visibility" => $row["project_visibility"],
                "image" => $row["project_image"],
                "description" => $row["project_description"],
                "codeTemplate" => $row["project_code_template"]
            ));
        }

        return $projectsList;
    }

    function getUserDefinedNodesClassNames($userOwner, $projectId){
        $userOwner = (int) $userOwner;
        $projectId = (int) $projectId;

        $queryStr = "SELECT node_class_name FROM storage_schematic_blocks WHERE node_class_parent LIKE '%UserDefinedNode%' AND node_owner=$userOwner AND node_project=$projectId;";
        $result = $this->db_conn->query($queryStr);

        $userDefinedNodes = array();
        foreach ($result as $row) {
            array_push($userDefinedNodes, $row["node_class_name"]);
        }
        return $userDefinedNodes;
    }

}
?>