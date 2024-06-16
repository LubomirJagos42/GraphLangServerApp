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

    private function getVariableFromPost($varname, $defaultVal = null){
        if (isset($_POST[$varname])) return $_POST[$varname];
        return $defaultVal;
    }

    private function getVariableFromGet($varname, $defaultVal = null){
        if (isset($_GET[$varname])) return $_GET[$varname];
        return $defaultVal;
    }

    function doDefaultRouting(){
        $userId = $this->modelLogin->getCurrentUserId();
        $passwordMD5 = isset($_SESSION["password"]) ? $_SESSION["password"] : "";
        $projectId = $this->modelLogin->getCurrentUserProjectId();

		$isUserLogged =	$this->modelLogin->isUserLogged(
            $this->modelLogin->getCurrentUsername(),
            "",
            md5($passwordMD5 . $this->modelLogin->getCurrentUserToken())
        );

		$userNodesClassNamesArray = $this->modelSchematicNodes->getProjectNodesClassNames($userId, $projectId);
		
		include("ViewExperiment_1.php");
    }
	
	function doGraphLangIDE(){
        $currentUser = $this->modelLogin->getCurrentUserId();
        $currentProject = $this->modelLogin->getCurrentUserProjectId();
        $passwordMD5 = isset($_SESSION["password"]) ? $_SESSION["password"] : "";

        $loginInfo =	$this->modelLogin->isUserLogged(
            $this->modelLogin->getCurrentUsername(),
            "",
            md5($passwordMD5 . $this->modelLogin->getCurrentUserToken())
        );

        if ($loginInfo['isLogged'] == 1 && $currentProject > -1){
            $nodeDefaultTreeDefinition = $this->modelSchematicNodes->getJavascriptObjectsInitDefinitionForProject($currentUser, $currentProject);
            $nodesNamesWithCategories = $this->modelSchematicNodes->getNodesWithCategories($currentUser, $currentProject);
            $userDefinedNodesClassNames = $this->modelSchematicNodes->getUserDefinedNodesClassNames($currentUser, $currentProject);

            include("GraphLang/GraphLang IDE/GrahpLang IDE Generated 1.php");
        }else if ($loginInfo['isLogged'] == 1 && $currentProject == -1) {
            $this->doNotFound();
        }else{
            $this->doUserLoginForm();
        }
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
        #
        #   username from:
        #       1. get
        #       2. post
        #       3. session
        #
        $username = $this->getVariableFromGet("username", "");
        if (!$username) $username = $this->getVariableFromPost("username", "");
        if (!$username) $username = isset($_SESSION["username"]) ? $_SESSION["username"] : "";

        #
        #   password from:
        #       1. post
        #
        $password = $this->getVariableFromPost("password", "");

        #
        #   token from:
        #       1. get
        #       2. post
        #       3. session generate one for login MD5(MD5(password raw string) + TOKEN)
        #
        $token = $this->getVariableFromGet("token", "");
        if (!$token) $token = $this->getVariableFromPost("token", "");
        if (!$token){
            $passwordMD5 = isset($_SESSION["password"]) ? $_SESSION["password"] : "";
            $token = md5($passwordMD5 . $this->modelLogin->getCurrentUserToken());
        }

        $loginInfo = $this->modelLogin->isUserLogged($username, $password, $token);
        if ($loginInfo['isLogged'] == 0){
            echo("user not logged!\n");
            echo("username: $username\n");
            echo("password: $password\n");
            echo("token: $token\n");
            return;
        }


		$outputStr = "";
		
        $nodeName = $this->getVariableFromPost("nodeName", "");
        $nodeContent = $this->getVariableFromPost("nodeContent", "");
        $nodeOwner = $this->modelLogin->getCurrentUserId();
        $projectId = $this->getVariableFromPost("projectId", "");
        $nodeClassParent = $this->getVariableFromPost("nodeClassParent", "");
        $nodeDir = $this->getVariableFromPost("nodeDir", "");
        $nodeLanguage = $this->getVariableFromPost("nodeLanguage", "");
        $nodeDisplayName = $this->getVariableFromPost("nodeDisplayName", "");
        $nodeIsHidden =  $this->getVariableFromPost("nodeIsHidden", "");
        $nodeCategoryName =  $this->getVariableFromPost("nodeCategory", "");

		$outputStr .= $this->modelSchematicNodes->saveNode(
            $nodeOwner,
            $projectId,
            $nodeName,
            $nodeContent,
            $nodeClassParent,
            $nodeLanguage,
            $nodeDir,
            $nodeDisplayName,
            $nodeIsHidden,
            $nodeCategoryName
        );

        $usertoken = $this->modelLogin->getCurrentUserToken();
        $outputStr .= "\nusertoken:'$usertoken'\n";

		echo($outputStr);
	}
	
	function doExperimentGetOrderedNodes(){
		$userOwner = $this->modelLogin->getCurrentUserId();
        $projectId = $this->getVariableFromGet("projectId", "-1");

        if (!$userOwner){
            echo("Current user: $userOwner<br/>\n");
            echo("projectId: $projectId<br/>\n");
            echo("User not specified. Try to <a href='?q=userLoginForm'>log in</a>");
            return;
        }

        $orderedNodesArray = $this->modelSchematicNodes->getOrderedNodesForProject($userOwner, $projectId);
		
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
		#string output is now happening in model but must be here to have rihgt decoupling here in this php application
        $orderedNodesArray = $this->modelSchematicNodes->getJavascriptForNodes(2, 47);
	}

    function doExperimentDebug(){
        $nodesNamesWithCategories = $this->modelSchematicNodes->getNodesWithCategories(2,47);
        print_r($nodesNamesWithCategories);
    }

    function doUserLogin(){
        $username = $this->getVariableFromPost("username", "");
        $password = $this->getVariableFromPost("password", "");
        $token = $this->getVariableFromPost("token", "");

        $outputArray = $this->modelLogin->isUserLogged($username, $password, $token);
        //print_r(outputArray);

        $response = "";
        foreach ($outputArray as $key => $val) $response .= "$key=$val&";
        echo(trim($response, '&'));
    }

    function doUserLoginForm(){
        #
        #   username from:
        #       1. get
        #       2. post
        #       3. session
        #
        $username = $this->getVariableFromGet("username", "");
        if (!$username) $username = $this->getVariableFromPost("username", "");
        if (!$username) $username = isset($_SESSION["username"]) ? $_SESSION["username"] : "";

        #
        #   password from:
        #       1. post
        #
        $password = $this->getVariableFromPost("password", "");

        #
        #   token from:
        #       1. get
        #       2. post
        #       3. session generate one for login MD5(MD5(password raw string) + TOKEN)
        #
        $token = $this->getVariableFromGet("token", "");
        if (!$token) $token = $this->getVariableFromPost("token", "");
        if (!$token){
            $passwordMD5 = isset($_SESSION["password"]) ? $_SESSION["password"] : "";
            $token = md5($passwordMD5 . $this->modelLogin->getCurrentUserToken());
        }

        $outputArray = $this->modelLogin->isUserLogged($username, $password, $token);
        $isLogged = $outputArray['isLogged'];
        $usertoken = $outputArray['token'];

        #
        #   Login form:
        #       1. user not logged - display form
        #       2. user is logged - display some info
        #
        include("ViewLoginForm.php");
    }
}
?>