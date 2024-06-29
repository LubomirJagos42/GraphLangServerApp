<?php
include_once("ModelLogin.php");
include_once("ModelSchematicNodes.php");
include_once("ModelDirectory.php");
include_once("ModelProject.php");

#Controller Default Class
class ControllerDefault{
    private $modelLogin;
	private $modelSchematicNodes;
	private $modelDirectory;

    function __construct($db_conn){
        $this->modelLogin = new ModelLogin($db_conn);
		$this->modelSchematicNodes = new modelSchematicNodes($db_conn);
		$this->modelDirectory = new modelDirectory($db_conn);
		$this->modelProject = new modelProject($db_conn);
    }

    private function getVariableFromPost($varname, $defaultVal = null){
        if (isset($_POST[$varname])) return $_POST[$varname];
        return $defaultVal;
    }

    private function getVariableFromGet($varname, $defaultVal = null){
        if (isset($_GET[$varname])) return $_GET[$varname];
        return $defaultVal;
    }

    private function getCurrentUserLoginVariables(){
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

        return array(
            "username" => $username,
            "password" => $password,
            "token" => $token
        );
    }

    private function getLoginInfo(){
        $passwordMD5 = isset($_SESSION["password"]) ? $_SESSION["password"] : "";

        $loginInfo =	$this->modelLogin->isUserLogged(
            $this->modelLogin->getCurrentUsername(),
            "",
            md5($passwordMD5 . $this->modelLogin->getCurrentUserToken())
        );

        return $loginInfo;
    }

    function doDefaultRouting(){
        $userId = $this->modelLogin->getCurrentUserId();
        $passwordMD5 = isset($_SESSION["password"]) ? $_SESSION["password"] : "";
        $projectId = $this->modelLogin->getCurrentUserProjectId();

		$loginInfo = $this->modelLogin->isUserLogged(
            $this->modelLogin->getCurrentUsername(),
            "",
            md5($passwordMD5 . $this->modelLogin->getCurrentUserToken())
        );

        if ($loginInfo["isLogged"] == 1){
            $userNodesClassNamesArray = $this->modelSchematicNodes->getProjectNodesClassNames($userId, $projectId);
            include("ViewExperiment_1.php");
        }else{
            echo("experiment: user not logged!<br/>\n");
            echo("<a href='?'>Home</a><br/>\n");
        }

    }
	
	function doGraphLangIDE(){
        $currentUser = $this->modelLogin->getCurrentUserId();
        $currentProject = $this->modelLogin->getCurrentUserProjectId();
        $loginInfo = $this->getLoginInfo();

        if ($loginInfo['isLogged'] == 1 && $currentProject > -1){
            $nodeDefaultTreeDefinition = $this->modelSchematicNodes->getJavascriptObjectsInitDefinitionForProject($currentUser, $currentProject);
            $nodesNamesWithCategories = $this->modelSchematicNodes->getNodesWithCategories($currentUser, $currentProject);
            $emptyCategories = $this->modelSchematicNodes->getEmptyCategoriesForProject($currentProject);
            $userDefinedNodesClassNames = $this->modelSchematicNodes->getUserDefinedNodesClassNames($currentUser, $currentProject);

            $ideVersion = $this->modelProject->getProjectVersion($currentProject);
            $htmlIncludeDirPrefix = $this->modelDirectory->getIdeHtmlIncludeDirPrefix($ideVersion);
            if ($ideVersion != ""){
                include($htmlIncludeDirPrefix ."/GrahpLang IDE Generated 1.php");
            }else{
                include("ViewNotFound.php");
            }

        }else if ($loginInfo['isLogged'] == 1 && $currentProject == -1) {
            $this->doNotFound();
        }else{
            $this->doUserLoginForm();
        }
	}

    function doGraphLangShapeDesigner(){
        $currentUser = $this->modelLogin->getCurrentUserId();
        $currentProject = $this->modelLogin->getCurrentUserProjectId();
        $loginInfo = $this->getLoginInfo();

        if ($loginInfo['isLogged'] == 1 && $currentProject > -1){
            $nodeDefaultTreeDefinition = $this->modelSchematicNodes->getJavascriptObjectsInitDefinitionForProject($currentUser, $currentProject);
            $nodesNamesWithCategories = $this->modelSchematicNodes->getNodesWithCategories($currentUser, $currentProject);
            $userDefinedNodesClassNames = $this->modelSchematicNodes->getUserDefinedNodesClassNames($currentUser, $currentProject);

            $ideVersion = $this->modelProject->getProjectVersion($currentProject);
            $htmlIncludeDirPrefix = $this->modelDirectory->getEnvironmentRootDir($ideVersion);
            if ($ideVersion != ""){
                include($htmlIncludeDirPrefix ."/GraphLang_ShapeDesigner/index.php");
            }else{
                include("ViewNotFound.php");
            }

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
        $loginInfo = $this->getCurrentUserLoginVariables();
        $username = $loginInfo['username'];
        $password = $loginInfo['password'];
        $token = $loginInfo['token'];

        #
        #   Here are data expected coming from python script therefore expected input is:
        #       username: user email like john.doe@somedomain.com
        #       password: ""
        #       token:    token as it should be md5(md5(raw password) + token from server)
        #
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
	
	function doGetOrderedNodes(){
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
		if (count($orderedNodesArray)) {
            $outputStr .= "<html>\n";
            $outputStr .= "\t\t<head>\n";
            $outputStr .= "\t\t\t\t<style type='text/css'>\n";
            $outputStr .= "\t\t\t\t\t\ttable{border-collapse: collapse; border: 1px solid black;}\n";
            $outputStr .= "\t\t\t\t\t\ttable td{padding: 5px; border-collapse: collapse; border: 1px solid black;}\n";
            $outputStr .= "\t\t\t\t</style>\n";
            $outputStr .= "\t\t</head>\n";
            $outputStr .= "\t\t<body>\n";
            $outputStr .= "\t\t<a href='?q=userProjectList'>Back to project list</a><br /><br />\n";
            $outputStr .= "\t\t<table>\n";
            for ($k = 0; $k < count($orderedNodesArray); $k++) {
                $outputStr .= "\t\t\t\t<tr>\n";
                $outputStr .= "\t\t\t\t\t\t<td>" . $orderedNodesArray[$k]['node_directory'] . "</td>\n";
                $outputStr .= "\t\t\t\t\t\t<td>" . $orderedNodesArray[$k]['node_display_name'] . "</td>\n";
                $outputStr .= "\t\t\t\t\t\t<td>" . $orderedNodesArray[$k]['node_class_name'] . "</td>\n";
                $outputStr .= "\t\t\t\t\t\t<td>" . $orderedNodesArray[$k]['node_class_parent'] . "</td>\n";
                $outputStr .= "\t\t\t\t\t\t<td>" . $orderedNodesArray[$k]['internal_id'] . "</td>\n";
                $outputStr .= "\t\t\t\t</tr>\n";
            }
            $outputStr .= "\t\t</table>\n";
            $outputStr .= "\t\t</body>\n";
            $outputStr .= "\t\t</html>\n";
        }else{
            $outputStr .= "No ordered nodes found!\n";
            $outputStr .= "<br /><br /><a href='?q=userProjectList'>Back to project list</a>\n";
        }

		echo($outputStr);
	}
	
	function doGetJavascriptForNodes(){
        $loginInfo = $this->getCurrentUserLoginVariables();
        $username = $loginInfo['username'];
        $password = $loginInfo['password'];
        $token = $loginInfo['token'];

        $loginInfo = $this->modelLogin->isUserLogged($username, $password, $token);
        if ($loginInfo['isLogged'] == 1) {
            $orderedNodesArray = $this->modelSchematicNodes->getJavascriptForNodes(
                $this->modelLogin->getCurrentUserId(),
                $this->modelLogin->getCurrentUserProjectId()
            );
        }else{
            $projectId = $this->modelLogin->getCurrentUserProjectId();
            echo("alert('javascript nodes from server: user not logged!\nprojectId: $projectId');\n");
        }
	}

    function doProjectCategoriesNodesEditor(){
        $loginInfo = $this->getCurrentUserLoginVariables();
        $username = $loginInfo['username'];
        $password = $loginInfo['password'];
        $token = $loginInfo['token'];

        #
        #   Here are data expected coming from python script therefore expected input is:
        #       username: user email like john.doe@somedomain.com
        #       password: ""
        #       token:    token as it should be md5(md5(raw password) + token from server)
        #
        $loginInfo = $this->modelLogin->isUserLogged($username, $password, $token);
        if ($loginInfo['isLogged'] == 1) {
            $currentUserId = $this->modelLogin->getCurrentUserId();
            $currentProjectId = $this->modelLogin->getCurrentUserProjectId();

            $nodesNamesWithCategories = $this->modelSchematicNodes->getNodesWithCategories(
                $currentUserId,
                $currentProjectId
            );
            $emptyCategories = $this->modelSchematicNodes->getEmptyCategoriesForProject($currentProjectId);
            $categoriesIdNamesList = $this->modelSchematicNodes->getAllProjectCategories($currentProjectId);
            $viewType = isset($_GET["viewType"]) ? $_GET["viewType"] : null;

            if ($viewType == "1") include("ViewProjectCategories_2.php");
            else include("ViewProjectCategories_1.php");
        }else{
            echo("user not logged!<br /><br />\n");
            echo("<a href='?'>Home</a>");
        }
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
        $loginInfo = $this->getCurrentUserLoginVariables();
        $username = $loginInfo['username'];
        $password = $loginInfo['password'];
        $token = $loginInfo['token'];

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

    function doUserLogout(){
        $_SESSION['username'] = "";
        $_SESSION['password'] = "";
        $_SESSION['token'] = "";

        echo("user logout<br /><br />\n");
        echo("<a href='?'>Home</a>\n");
    }

    function doUserProjectList(){
        $currentUser = $this->modelLogin->getCurrentUserId();
        $passwordMD5 = isset($_SESSION["password"]) ? $_SESSION["password"] : "";

        $loginInfo = $this->modelLogin->isUserLogged(
            $this->modelLogin->getCurrentUsername(),
            "",
            md5($passwordMD5 . $this->modelLogin->getCurrentUserToken())
        );

        if ($loginInfo['isLogged'] == 1){
            $debugMode = isset($_GET['debugMode']) ? $_GET['debugMode'] : 0;

            $projectList = $this->modelSchematicNodes->getUserProjectList($currentUser);
            $othersProjectList = $this->modelSchematicNodes->getOthersPublicProjects($currentUser);

            include("ViewUserProjectList.php");
        }else{
            $this->doUserLoginForm();
        }
    }

    function doCreateProject(){
        $currentUser = $this->modelLogin->getCurrentUserId();
        $loginInfo = $this->getLoginInfo();

        if ($loginInfo['isLogged'] == 1){

            $projectName = isset($_POST["name"]) ? $_POST["name"] : "";
            $projectDescription = isset($_POST["description"]) ? $_POST["description"] : "";
            $projectVisibility = isset($_POST["visibility"]) ? $_POST["visibility"] : "";
            $projectCodeTemplate = isset($_POST["codeTemplate"]) ? $_POST["codeTemplate"] : "";
            $projectLanguage = isset($_POST["language"]) ? $_POST["language"] : "";
            $projectIdeVersion = isset($_POST["ideVersion"]) ? $_POST["ideVersion"] : "";

            if ($projectName != ""){
                $check = getimagesize($_FILES["image"]["tmp_name"]);
                if($check !== false) {
                    $projectImage = file_get_contents($_FILES["image"]["tmp_name"]);
                    $imageType = $_FILES["image"]["type"];
                    $projectImageEncoded = "data:$imageType;base64,". base64_encode($projectImage);
                }

                $result = $this->modelProject->createProject(
                    $currentUser,
                    $projectName,
                    $projectDescription,
                    $projectImageEncoded,
                    $projectVisibility,
                    $projectCodeTemplate,
                    $projectLanguage,
                    $projectIdeVersion
                );

                echo("project create result: $result<br />\n");
                echo("<br />\n");
                echo("<a href='?q=userProjectList'>Back to project list</a><br />\n");

            }else{
                include("ViewCreateProject.php");
            }

        }else{
            $this->doUserLoginForm();
        }
    }

    function doIsUserLogged(){
        $loginInfo = $this->getLoginInfo();

        $isLogged = $loginInfo["isLogged"];
        $token = $loginInfo['token'];
        echo("{\"isLogged\":$isLogged, \"token\":\"$token\"}");
    }

    function doDownloadIde(){
        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1){
            $currentUser = $this->modelLogin->getCurrentUserProjectId();
            $currentProject = $this->modelLogin->getCurrentUserProjectId();

            $rootDir = "_temp";
            $fileBaseName = "GraphLangIDE_user_".$currentUser."_project_".$currentProject;
            $tempDir = $rootDir.DIRECTORY_SEPARATOR.$fileBaseName;
            $zipFileName = $rootDir.DIRECTORY_SEPARATOR.$fileBaseName.".zip";
            $file_url = "/GraphLangServerApp/_temp/".$fileBaseName.".zip";

            //remove all previous files for this user to not fill temp directory if called too many times
            foreach (glob($rootDir.DIRECTORY_SEPARATOR."*_user_$currentUser_*") as $filename) unlink($filename);

            @mkdir($rootDir);   //just to be sue there will be temporary dir created, if already exists this do nothing, warnings are supressed
            @$this->modelDirectory->recurseRmdir($tempDir);
            @mkdir($tempDir);

            echo("temp dir created: $tempDir<br/>\n");

            $environmentDir = $this->modelDirectory->getEnvironmentRootDir($this->modelProject->getProjectVersion($currentProject));

            $this->modelDirectory->recursive_copy(
                $environmentDir,
                $tempDir,
                array(
                    "/^\..*$/",               //exclude dirs starts with '.' (they are hidden)
                    "/^__pycache__$/",        //exclude dirs __pycache__
                    "/^_temp$/",              //exclude dirs _temp
                    "/^LibraryBlocks$/"       //exclude LibraryBlocks which is from original IDE when working on local PC from local drive
                ),
                array(
                    "/^.*\.php$/",            //exclude all .php files
                    "/^\..*$/"                //exclude files which starts with '.' (that are hidden files), this to remove files like .gitignore, .idea and so
                )
            );
            echo("files copied<br/>\n");
            echo("starts packing dir to .zip<br/>\n");

            $this->modelDirectory->zipDir(
                $tempDir,
                $zipFileName
            );
            echo(".zip created<br />\n");

            @$this->modelDirectory->recurseRmdir($tempDir);
            echo("temp dir removed<br />\n");

            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");
            readfile($file_url);
        }
    }

    function doDeleteProject(){
        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1){
            $userId = $this->modelLogin->getCurrentUserId();
            $projectId = $this->modelLogin->getCurrentUserProjectId();
            $result = $this->modelProject->deleteProject($userId, $projectId);

            echo("<h2>Delete project id:$projectId result</h2>\n");
            echo("<table>\n");
            foreach ($result as $key => $value){
                echo("<tr><td>delete items in table $key</td><td>$value</td></tr>\n");
            }
            echo("</table>\n");
            echo("<br /><br /><a href='?q=userProjectList'>Back to project list</a>\n");
        }else{
            $this->doUserLoginForm();
        }
    }

    function doGetNodeJavascriptCode(){
        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1) {
            $userOwner = $this->modelLogin->getCurrentUserId();
            $projectId = isset($_GET["projectId"]) ? $_GET["projectId"] : "";
            $nodeClassName = isset($_GET["nodeClassName"]) ? $_GET["nodeClassName"] : "";
            $nodeInfo = $this->modelSchematicNodes->getNodeCodeContent($userOwner, $projectId, $nodeClassName, true);

            if (!empty($nodeInfo)){
                echo('{"error": "OK", "nodeContent": "'. $nodeInfo["nodeContentCode"] .'", "nodeClassName": "'. $nodeInfo["nodeClassName"] .'", "nodeClassParent": "'. $nodeInfo["nodeClassParent"] .'"}');
            }else{
                echo('{"error": "No node was returned!", "nodeContent": "", "nodeClassName": "", "nodeClassParent": ""}');
            }
        }else{
            echo('{"error": "User not logged!", "nodeContent": "", "nodeClassName": "", "nodeClassParent": ""}');
        }
    }

    function doUpdateNodeJavascriptCode(){
        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1) {
            $userOwner = $this->modelLogin->getCurrentUserId();
            $projectId = isset($_GET["projectId"]) ? $_GET["projectId"] : "";
            $nodeClassName = isset($_GET["nodeClassName"]) ? $_GET["nodeClassName"] : "";
            $newNodeClassContent = isset($_POST["nodeClassContent"]) ? $_POST["nodeClassContent"] : "";

            $result = $this->modelSchematicNodes->updateNodeCodeContent($userOwner, $projectId, $nodeClassName, $newNodeClassContent, true);

            echo('{"error": "OK", "numberOfUpdatedRows": "'.$result.'"}');
        }else{
            echo('{"error": "User not logged!", "numberOfUpdatedRows": "0"}');
        }
    }

    function doReplaceNodeSchematicJsonDocument(){
        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1){
            $userOwner = $this->modelLogin->getCurrentUserId();
            $projectId = isset($_GET["projectId"]) ? $_GET["projectId"] : "";
            $nodeClassName = isset($_GET["nodeClassName"]) ? $_GET["nodeClassName"] : "";
            $newNodeClassContent = isset($_POST["nodeClassContent"]) ? $_POST["nodeClassContent"] : "";

            $result = $this->modelSchematicNodes->replaceNodeSchematicJsonDocument($userOwner, $projectId, $nodeClassName, $newNodeClassContent, true);
            echo('{"error": "OK", "numberOfUpdatedRows": "'.$result.'"}');
        }else{
            echo('{"error": "User not logged!", "numberOfUpdatedRows": "0"}');
        }
    }

    function doCategoryOperation(){
        $result = array("status" => 0, "errorMsg" => "unknown error");

        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1){
            /*
             *  Get current logged user information.
             */
            $userOwner = $this->modelLogin->getCurrentUserId();
            $projectId = $this->modelLogin->getCurrentUserProjectId();

            /*
             *  Get parameters for category operation.
             */
            $operation = isset($_POST['operation']) ? $_POST['operation'] : "";
            $categoryId = isset($_POST['categoryId']) ? $_POST['categoryId'] : -1;
            $nodeId = isset($_POST['nodeId']) ? $_POST['nodeId'] : -1;
            $categoryName = isset($_POST['categoryName']) ? $_POST['categoryName'] : "Name Unknown";

            $categoryId = $categoryId ? $categoryId : -1;
            $nodeId = $nodeId ? $nodeId : -1;

            /*
             *  Check conditions if user is owner of category or project before doing operations over DB to really do that stuff.
             */
            //CHECK - user is owner of category for these operations
            if (in_array($operation, array("deleteNodeFromCategory","deleteCategory","renameCategory"))){
                if ($this->modelSchematicNodes->isUserOwnerOfCategory($userOwner, $categoryId) == false){
                    $result["errorMsg"] = "User $userOwner is not owner of category $categoryName";
                    return $result;
                }
            }
            //CHECK - user is owner of project for these operations
            if (in_array($operation, array("addCategory"))){
                if ($this->modelSchematicNodes->isUserOwnerOfProject($userOwner, $projectId) == false){
                    $result["errorMsg"] = "User $userOwner is not owner of project $projectId, category $categoryId cannot be renamed to '$categoryName'";
                    return $result;
                }
            }

            /*
             *  User is allowed to do operation, here it's performed.
             */
            if ($operation == "deleteNodeFromCategory"){
                $result = $this->modelSchematicNodes->deleteNodeFromCategory($nodeId, $categoryId);
            }else if ($operation == "addNodeToCategory"){
                $result = $this->modelSchematicNodes->addNodeToCategory($nodeId, $projectId, $categoryId);
            }else if ($operation == "deleteCategory"){
                $result = $this->modelSchematicNodes->deleteCategory($categoryId);
            }else if ($operation == "addCategory"){
                $result = $this->modelSchematicNodes->addCategory($projectId, $categoryName);
            }else if ($operation == "renameCategory"){
                $result = $this->modelSchematicNodes->renameCategory($categoryId, $categoryName);
            }else{
                $result["errorMsg"] = "category operation not recognized";
            }

            echo json_encode($result);
        }else{
            $result["errorMsg"] = "User not logged!";
            echo json_encode($result);
        }

    }

}
?>