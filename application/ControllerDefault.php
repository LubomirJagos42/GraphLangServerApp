<?php
include_once("ModelLogin.php");
include_once("ModelSchematicNodes.php");

#Controller Default Class
class ControllerDefault{
    private $modelLogin;
	private $modelSchematicNodes;

    function __construct($db_conn){
        $this->modelLogin = new ModelLogin($db_conn);
		$this->modelSchematicNodes = new modelSchematicNodes($db_conn);
    }

    function doDefaultRouting(){
		$isUserLogged =	$this->modelLogin->isUserLogged();
		$userId = $this->modelLogin->getCurrentUserId();
		$projectId = $this->modelLogin->getCurrentUserProjectId();
		$userNodesClassNamesArray = $this->modelSchematicNodes->getProjectNodesClassNames();
		
		include("ViewExperiment_1.php");
    }
	
	function doGraphLangIDE(){
		include("GraphLang/GraphLang IDE/GrahpLang IDE Generated 1.php");
	}

	function doNotFound(){
		include("ViewNotFound.php");
	}
	
	function doLoadNodesFromServer(){
		$outputStr = "";
		
		#
		#	HERE WILL BE CHECK IF USER IS LOGGED AND INTO WHICH PROJECT
		#
		
		
		#
		#	HERE WILL BE PRINTED JS NODES FROM DB FOR CURRENT USER AND HIS PROJECT
		#
		
		$outputStr .= <<< 'EOD'
			window.addEventListener('load', (event) => {
				alert('PHP says Hello world.');

				function includeJsToHead(filename)
				{
					var head = document.getElementsByTagName('head')[0];

					var script = document.createElement('script');
					script.src = filename;
					script.type = 'text/javascript';

					head.appendChild(script)
				}

				includeJsToHead("/GraphLangServerApp/javascript/simpleAlert.js");
			});

		EOD;

		echo $outputStr;
	}

	function doUploadNodesToServer(){
		$outputStr = "";
		
		$nodeName = $_POST['nodeName'];
		$nodeContent = $_POST['nodeContent'];
		$nodeOwner = $_POST['nodeOwner'];
		$projectId = $_POST['projectId'];
		$nodeClassParent = $_POST['nodeClassParent'];
		$nodeDir = $_POST['nodeDir'];
		$nodeLanguage = $_POST['nodeLanguage'];
		$nodeDisplayName = $_POST['nodeDisplayName'];
		
		$outputStr .= $this->modelSchematicNodes->saveNode($nodeOwner, $projectId, $nodeName, $nodeContent, $nodeClassParent, $nodeLanguage, $nodeDir, $nodeDisplayName);
		
		echo($outputStr);
	}
	
	function doExperimentGetOrderedNodes(){
		$orderedNodesArray = $this->modelSchematicNodes->getOrderedNodesForProject(2, 47);
		
		$outputStr = "";
		$outputStr .= "<table>\n";
		for($k=0; $k < count($orderedNodesArray); $k++){
			$outputStr .= "<tr>";
			$outputStr .= "<td>". $orderedNodesArray[$k]['node_directory'] ."</td>\n";
			$outputStr .= "<td>". $orderedNodesArray[$k]['node_display_name'] ."</td>\n";
			$outputStr .= "<td>". $orderedNodesArray[$k]['node_class_name'] ."</td>\n";
			$outputStr .= "<td>". $orderedNodesArray[$k]['node_class_parent'] ."</td>\n";
			$outputStr .= "<td>". $orderedNodesArray[$k]['internal_id'] ."</td>\n";
			$outputStr .= "</tr>\n";
		}
		$outputStr .= "</table>\n";
		echo($outputStr);
	}
	
	function doExperimentGetJavascriptForNodes(){
		$orderedNodesArray = $this->modelSchematicNodes->getJavascriptForNodes(2, 47);
		
	}
}
?>