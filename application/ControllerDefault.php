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
	
}
?>