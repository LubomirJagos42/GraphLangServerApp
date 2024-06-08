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
	
}
?>