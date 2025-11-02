<?php
include_once("ControllerParent.php");
include_once("ModelLogin.php");
include_once("ModelSchematicNodes.php");
include_once("ModelDirectory.php");
include_once("ModelProject.php");
include_once("ModelOsCommands.php");

#Controller Default Class
class ControllerDefault extends ControllerParent{
    private $modelLogin;
	private $modelSchematicNodes;
	private $modelDirectory;

    function __construct($db_conn){
        $this->modelLogin = new ModelLogin($db_conn);
		$this->modelSchematicNodes = new ModelSchematicNodes($db_conn);
		$this->modelDirectory = new ModelDirectory($db_conn);
		$this->modelProject = new ModelProject($db_conn);
		$this->modelOsCommands = new ModelOsCommands($db_conn);
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

        $loginInfo = $this->modelLogin->isUserLogged(
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

            /*
             *  Get node ID and class name from url params from request
             */
            $nodeId = $this->getVariableFromGet("nodeId", -1);
            $nodeClassName = $this->getVariableFromGet("nodeClassName", "");

            /*
             *  Try to find node data from DB and put them into variables for view
             */
            $currentNodeInfo = $this->modelSchematicNodes->getNode($nodeId, $currentUser, $currentProject, $nodeClassName, true);
            if ($currentNodeInfo) {
                $nodeClassName = $currentNodeInfo["node_class_name"];
                $nodeDisplayName = $currentNodeInfo["node_display_name"];
                $nodeClassParent = $currentNodeInfo["node_class_parent"];
                $nodeCodeContent = $currentNodeInfo["node_content_code"];
            }else{
                $nodeClassName = "";
                $nodeDisplayName = "";
                $nodeClassParent = "";
                $nodeCodeContent = "";
            }

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
            $emptyCategories = $this->modelSchematicNodes->getEmptyCategoriesForProject($currentProject);
            $userDefinedNodesClassNames = $this->modelSchematicNodes->getUserDefinedNodesClassNames($currentUser, $currentProject);

            $nodeClassName = isset($_GET['nodeClassName']) ? $_GET['nodeClassName'] : null;

            $ideVersion = $this->modelProject->getProjectVersion($currentProject);
            $htmlIncludeDirPrefix = $this->modelDirectory->getShapeDesignerHtmlIncludeDirPrefix($ideVersion);
            if ($ideVersion != ""){
                include($htmlIncludeDirPrefix ."/index.php");
            }else{
                include("ViewNotFound.php");
            }

        }else if ($loginInfo['isLogged'] == 1 && $currentProject == -1) {
            $this->doNotFound();
        }else{
            $this->doUserLoginForm();
        }
    }

    function doGraphLangCodeEditor(){
        $currentUser = $this->modelLogin->getCurrentUserId();
        $loginInfo = $this->getLoginInfo();

        if ($loginInfo['isLogged'] == 1){
            $projectId = $this->getVariableFromGet("projectId", -1);
            if ($projectId == -1) $projectId = $this->getVariableFromPost("projectId", -1);
            $nodeId = $this->getVariableFromGet("nodeId", "");
            if ($nodeId == -1) $nodeId = $this->getVariableFromPost("nodeId", -1);
            $nodeClassName = $this->getVariableFromGet("nodeClassName", "");
            if ($nodeClassName == "") $nodeClassName = $this->getVariableFromPost("nodeClassName", "");

            $nodeInfo = $this->modelSchematicNodes->getNode($nodeId, $currentUser, $projectId, $nodeClassName);

            $nodeDisplayName = $nodeInfo["node_display_name"];

            include("ViewCodeEditor.php");

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

		$saveResult = $this->modelSchematicNodes->saveNode(
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

        $outputStr .= $saveResult["message"];

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
            $outputStr .= "\t\t<a href='?q=userProjectList&debugMode=1'>Back to project list</a><br /><br />\n";
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
            $outputStr .= "<br /><br /><a href='?q=userProjectList&debugMode=1'>Back to project list</a>\n";
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
            //echo(addslashes($orderedNodesArray));   //this will broke loading JS code, add slashes in front of ',",\ to not be interpreted by echo as special chars
            echo($orderedNodesArray);
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

            $includeHiddenNodes = $this->getVariableFromGet("includeHiddenNodes", false);
            $includeHiddenNodes = in_array(strtolower($includeHiddenNodes), ["t", "true", "1"]) ? true : false;

            $nodesNamesWithCategories = $this->modelSchematicNodes->getNodesWithCategories(
                $currentUserId,
                $currentProjectId,
                $includeHiddenNodes
            );
            $emptyCategories = $this->modelSchematicNodes->getEmptyCategoriesForProject($currentProjectId);
            $categoriesIdNamesList = $this->modelSchematicNodes->getAllProjectCategories($currentProjectId);
            $viewType = isset($_GET["viewType"]) ? $_GET["viewType"] : null;

            $currentProject = $this->modelLogin->getCurrentUserProjectId();
            $ideVersion = $this->modelProject->getProjectVersion($currentProject);
            $htmlIncludeDirPrefix = $this->modelDirectory->getIdeHtmlIncludeDirPrefix($ideVersion);

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
            $projectNoImage = isset($_POST["noImage"]) ? $_POST["noImage"] : "";

            if ($projectName != ""){
                $projectImageEncoded = "";
                if(
                    ($projectNoImage == false || $projectNoImage == "") &&
                    isset($_FILES["image"]["tmp_name"]) &&
                    $_FILES["image"]["tmp_name"] != "" &&
                    getimagesize($_FILES["image"]["tmp_name"])
                ) {
                    $projectImage = $_FILES["image"]["tmp_name"] != "" ? file_get_contents($_FILES["image"]["tmp_name"]) : "";
                    $imageType = $_FILES["image"]["type"];
                    $projectImageEncoded = "data:$imageType;base64,". base64_encode($projectImage);
                }
                if($projectNoImage == true) $projectImageEncoded = "";

                $newProjectId = $this->modelProject->createProject(
                    $currentUser,
                    $projectName,
                    $projectDescription,
                    $projectImageEncoded,
                    $projectVisibility,
                    $projectCodeTemplate,
                    $projectLanguage,
                    $projectIdeVersion
                );

                echo("new project ID: $newProjectId<br />\n");
                echo("<br />\n");

                $templateProjectInfo = $this->modelProject->getTemplateProject();
                $this->modelProject->copyNodesWithCategoriesFromToProject(
                    $templateProjectInfo['internal_id'],
                    $newProjectId
                );

                echo("<a href='?q=userProjectList'>Back to project list</a><br />\n");

            }else{
                include("ViewCreateProject.php");
            }

        }else{
            $this->doUserLoginForm();
        }
    }

    function doUpdateProjectDetails(){
        $currentUser = $this->modelLogin->getCurrentUserId();
        $currentProject = $this->modelLogin->getCurrentUserProjectId();
        $loginInfo = $this->getLoginInfo();

        if ($loginInfo['isLogged'] == 1){

            $projectUpdate = isset($_GET['doUpdate']) && $_GET['doUpdate'] == 1 ? true : false;

            if ($projectUpdate == false) {
                $projectInfo = $this->modelProject->getProject($currentUser, $currentProject);

                $projectName = $projectInfo["project_name"];
                $projectDescription = $projectInfo["project_description"];
                $projectVisibility = $projectInfo["project_visibility"];
                $projectCodeTemplate = $projectInfo["project_code_template"];
                $projectLanguage = $projectInfo["project_language"];
                $projectIdeVersion = $projectInfo["project_graphlang_version"];
                $projectImage = $projectInfo["project_image"];
            }else{
                $projectName = isset($_POST["name"]) ? $_POST["name"] : "";
                $projectDescription = isset($_POST["description"]) ? $_POST["description"] : "";
                $projectVisibility = isset($_POST["visibility"]) ? $_POST["visibility"] : "";
                $projectCodeTemplate = isset($_POST["codeTemplate"]) ? $_POST["codeTemplate"] : "";
                $projectLanguage = isset($_POST["language"]) ? $_POST["language"] : "";
                $projectIdeVersion = isset($_POST["ideVersion"]) ? $_POST["ideVersion"] : "";
                $projectNoImage = isset($_POST["noImage"]) ? $_POST["noImage"] : "";
            }

            if ($projectUpdate){
                $projectImageEncoded = "";
                if (isset($_FILES["image"]["tmp_name"]) && $_FILES["image"]["tmp_name"] != "") $check = getimagesize($_FILES["image"]["tmp_name"]);
                else $check = false;
                if($check !== false) {
                    $projectImage = file_get_contents($_FILES["image"]["tmp_name"]);
                    $imageType = $_FILES["image"]["type"];
                    $projectImageEncoded = "data:$imageType;base64,". base64_encode($projectImage);
                }
                if($projectNoImage == true) $projectImageEncoded = null;

                $result = $this->modelProject->updateProject(
                    $currentUser,
                    $currentProject,
                    $projectName,
                    $projectDescription,
                    $projectImageEncoded,
                    $projectVisibility,
                    $projectCodeTemplate,
                    $projectLanguage,
                    $projectIdeVersion
                );

                echo("project UPDATE result: ". $result['status'] ."<br />\n");
                if ($result["status"] == 0) echo("Error: ". $result['errorMsg'] ."<br />\n");
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
            $currentUser = $this->modelLogin->getCurrentUserId();
            $currentProject = $this->modelLogin->getCurrentUserProjectId();
            $projectOwnerId = $this->modelProject->getProjectOwnerId($currentProject);

            $rootDir = "_temp";
            $fileBaseName = "GraphLangIDE_user_".$currentUser."_project_".$currentProject;
            $tempDir = $rootDir.DIRECTORY_SEPARATOR.$fileBaseName;
            $zipFileName = $rootDir.DIRECTORY_SEPARATOR.$fileBaseName.".zip";
            $file_url = "_temp/".$fileBaseName.".zip";

            //remove all previous files for this user to not fill temp directory if called too many times
            foreach (glob($rootDir.DIRECTORY_SEPARATOR."*_user_".$currentUser."_*") as $filename) unlink($filename);

            @mkdir($rootDir);   //just to be sure there will be temporary dir created, if already exists this do nothing, warnings are suppressed
            @$this->modelDirectory->recurseRmdir($tempDir);
            @mkdir($tempDir);

            echo("temp dir created: $tempDir<br/>\n");

            $environmentDir = $this->modelDirectory->getEnvironmentRootDir($this->modelProject->getProjectVersion($currentProject));

            echo("START file copying into temporary dir<br/>\n");
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
            echo("END files copied into temporary dir<br/>\n");

            echo("START modifying HTML file for IDE");
                $outputHtmlIdeFile = $tempDir.DIRECTORY_SEPARATOR."GraphLang IDE".DIRECTORY_SEPARATOR."GrahpLang IDE Generated 1.html";

                $nodeDefaultTreeDefinition = $this->modelSchematicNodes->getJavascriptObjectsInitDefinitionForProject($currentUser, $currentProject);
                $nodesNamesWithCategories = $this->modelSchematicNodes->getNodesWithCategories($currentUser, $currentProject);
                $emptyCategories = $this->modelSchematicNodes->getEmptyCategoriesForProject($currentProject);
                $userDefinedNodesClassNames = $this->modelSchematicNodes->getUserDefinedNodesClassNames($currentUser, $currentProject);

                $orderedNodesList = $this->modelSchematicNodes->getOrderedNodesForProject($currentUser, $currentProject);
                $nodesListWithCateories = $this->modelSchematicNodes->getAllNodesWithCategories($currentUser, $currentProject);

                $ideVersion = $this->modelProject->getProjectVersion($currentProject);
                $htmlIncludeDirPrefix = '.';


                ob_start();
                include($this->modelDirectory->getIdeHtmlIncludeDirPrefix($ideVersion) ."/GrahpLang IDE Generated Downloaded.php");
                $outputHtmlIdeFileContent = ob_get_contents();
                file_put_contents($outputHtmlIdeFile, $outputHtmlIdeFileContent);
                ob_get_clean();
            echo("END modifying HTML file for IDE");

            echo("START copying blocks files<br />\n");
                echo("&nbsp;&nbsp;&nbsp;&nbsp;call \$this->modelSchematicNodes->getNodesWithCategories($currentUser, $currentProject);<br />\n");
                $categoriesWithNodes = $this->modelSchematicNodes->getNodesWithCategories($projectOwnerId, $currentProject);
                $libraryBlocksDir = $tempDir.DIRECTORY_SEPARATOR."GraphLang IDE".DIRECTORY_SEPARATOR."LibraryBlocks";

                echo("&nbsp;&nbsp;&nbsp;&nbsp;creating dir $libraryBlocksDir<br />\n");
                @mkdir($libraryBlocksDir);

                /*
                 *  SAVE NORMAL NODES INTO CATEGORY DIRECTORY
                 */
                foreach ($categoriesWithNodes as $categoryName => $nodeList){
                    $categoryDir = $libraryBlocksDir.DIRECTORY_SEPARATOR.($categoryName == 0 ? "" : $categoryName);
                    echo("&nbsp;&nbsp;&nbsp;&nbsp;creating dir $categoryDir<br />\n");
                    @mkdir($categoryDir);
                    ob_start();
                    foreach ($nodeList as $node){
                        $nodeInfo = $this->modelSchematicNodes->getNode($node["id"]);
                        $outputFile = $categoryDir.DIRECTORY_SEPARATOR.$nodeInfo["node_display_name"].".js";
                        echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;writing file ". $outputFile ."<br />\n");
                        $nodefile = fopen($outputFile, "w");
                        fwrite($nodefile, $nodeInfo['node_content_code']);
                        fclose($nodefile);
                    }
                    ob_get_clean();
                }
            echo("END copying blocks files<br />\n");

            echo("START copying hidden nodes into files");
            /*
             *  SAVE HIDDEN NODES INTO CATEGORY DIRECTORY
             */
            $categoryDir = $libraryBlocksDir.DIRECTORY_SEPARATOR."_hidden";
            echo("&nbsp;&nbsp;&nbsp;&nbsp;creating dir $categoryDir<br />\n");
            @mkdir($categoryDir);
            foreach ($this->modelSchematicNodes->getProjectHiddenNodes($currentProject) as $nodeInfo){
                ob_start();
                    $outputFile = $categoryDir.DIRECTORY_SEPARATOR.$nodeInfo["node_display_name"].".js";
                    echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;writing file ". $outputFile ."<br />\n");
                    $nodefile = fopen($outputFile, "w");
                    fwrite($nodefile, $nodeInfo['node_content_code']);
                    fclose($nodefile);
                ob_get_clean();
            }
            echo("END copying hidden nodes into files<br />\n");

            echo("START create webpage file generate<br/>\n");
                $params = array();
                $params["blocksRootDir"] = "LibraryBlocks";
                $params["excludeFromHtmlBlockPatterns"] = array(
                    "_hidden"
                );
                $params["blocksToTabsAssignment"] = array();
                foreach ($categoriesWithNodes as $categoryName => $nodeList){
                    /*
                     *  this will add category names and their paths for script in form:
                     *      array(
                     *          "some/path/categoryName1" => "categoryName1",
                     *          "some/path/categoryName1" => "categoryName2",
                     *          ...
                     *          "some/path/categoryNameLast" => "categoryNameLast"
                     *      );
                     *
                     *  for 'others' nodes n category is generated as they are sitting under library root directory directly
                     */
                    if ($categoryName != "0") $params["blocksToTabsAssignment"][$categoryName] = $categoryName;
                }

                /*
                 *  This will include createWebPage.php file as php script and evaluate it as php script, inside there is python script and
                 *  on some places there are php tags to dynamically add content specific for downloading project.
                 */
                $createWebpageFilePath = "GraphLang".DIRECTORY_SEPARATOR.$this->modelProject->getProjectVersion($currentProject).DIRECTORY_SEPARATOR."GraphLang IDE".DIRECTORY_SEPARATOR."createWebPage.php";
                echo("&nbsp;&nbsp;&nbsp;&nbsp;createWebpPageFile path: $createWebpageFilePath<br/>\n");
                ob_start();
                include($createWebpageFilePath);
                $createWebpageScript = ob_get_clean();

                $outputCreateWebpageFilePath = $tempDir.DIRECTORY_SEPARATOR."GraphLang IDE".DIRECTORY_SEPARATOR."createWebPage.py";
                $outputCreateWebpageFile = fopen($outputCreateWebpageFilePath, "w");
                fwrite($outputCreateWebpageFile, $createWebpageScript);
                fclose($outputCreateWebpageFile);
            echo("END create webpage file generate<br/>\n");

            echo("START packing dir to .zip<br/>\n");
                $this->modelDirectory->zipDir(
                    $tempDir,
                    $zipFileName
                );
            echo("END .zip created<br />\n");

            @$this->modelDirectory->recurseRmdir($tempDir);
            echo("temp dir removed<br />\n");

            ob_get_clean();     //clean all echo into output buffer
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
                echo('{"error": "OK", "nodeContent": "'. $nodeInfo["nodeContentCode"] .'", "nodeClassName": "'. $nodeInfo["nodeClassName"] .'", "nodeClassParent": "'. $nodeInfo["nodeClassParent"] .'", "nodeDisplayName": "'. $nodeInfo["nodeDisplayName"] .'"}');
            }else{
                echo('{"error": "No node was returned!", "nodeContent": "", "nodeClassName": "", "nodeClassParent": "", "nodeDisplayName": ""}');
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
        $result = array("status" => 0, "errorMsg" => "");

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
            $operation = $this->getVariableFromPost("operation", "");
            $categoryId = $this->getVariableFromPost("categoryId", -1);
            $nodeId = $this->getVariableFromPost("nodeId", -1);
            $categoryName = $this->getVariableFromPost("categoryName", "Name Unknown");

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

    function doNodeOperation(){
        $result = array("status" => 0, "errorMsg" => "");

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
        if ($loginInfo["isLogged"] == 1){
            /*
             *  Get current logged user information.
             */
            $userOwner = $this->modelLogin->getCurrentUserId();
            $projectId = $this->modelLogin->getCurrentUserProjectId();

            /*
             *  Check if user is owner of node, if not do not continue
             */
            $operation = $this->getVariableFromGet("operation", "");
            if ($operation == "") $this->getVariableFromPost("operation", "");
            $nodeClassName = $this->getVariableFromGet("nodeClassName", "");
            if ($nodeClassName == "") $this->getVariableFromPost("nodeClassName", "");

            $nodeNewDisplayName = $this->getVariableFromPost("nodeNewDisplayName", "");
            $nodeNewClassName = $this->getVariableFromPost("nodeNewClassName", "");
            $nodeNewIsHidden = $this->getVariableFromPost("nodeNewIsHidden", -1);
            $nodeNewCodeContent = $this->getVariableFromPost("nodeNewCodeContent", "");
            $nodeNewLanguage = $this->getVariableFromPost("nodeNewLanguage", "");
            $nodeNewParent = $this->getVariableFromPost("nodeNewParent", "");

            $nodeNewIsHidden = in_array($nodeNewIsHidden, array("T", "True", "TRUE", "true", "1", 1)) ? 1 : 0;

            /*
             *  Trying to obtain nodeId from:
             *      - GET
             *      - POST
             *  if node id is specified than get current node details from DB and overwrite parameters for project id and class name even if they are send
             *  nodeId if provided it overwrites other params
             */
            $nodeId = $this->getVariableFromGet("nodeId", -1);
            if ($nodeId == -1) $nodeId = $this->getVariableFromPost("nodeId", -1);
            if ($nodeId > -1){
                $nodeInfo = $this->modelSchematicNodes->getNode($nodeId);
                $nodeClassName = $nodeInfo['node_class_name'];
                $projectId = $nodeInfo['node_project'];
            }

            if ($this->modelSchematicNodes->isUserOwnerOfNode($userOwner, $nodeId, $projectId, $nodeClassName) == false){
                $result = array("status" => 0, "errorMsg" => "user is not node owner node details change not allowed");
                echo json_encode($result);
                return;
            }

            $result = array("status"=>0, "errorMsg"=>"");
            /*
             *  User is allowed to do operation, here it's performed.
             */
            if ($operation == "changeNodeDisplayName"){
                $result = $this->modelSchematicNodes->updateNodeDisplayName($nodeNewDisplayName, $nodeId, $userOwner, $projectId, $nodeClassName);
            }else if ($operation == "changeNodeClassName"){
                $result = $this->modelSchematicNodes->updateNodeClassName($nodeNewClassName, $userOwner, $projectId, $nodeClassName);
            }else if ($operation == "changeNodeLanguage"){
                $result = $this->modelSchematicNodes->updateNodeLanguage($nodeNewLanguage, $nodeId, $userOwner, $projectId, $nodeClassName);
            }else if ($operation == "changeNodeIsHidden"){
                $result = $this->modelSchematicNodes->updateNodeIsHidden($nodeNewIsHidden, $nodeId, $userOwner, $projectId, $nodeClassName);
            }else if ($operation == "getNodeInfo"){
                $result["status"] = 1;
                $result["output"] = $this->modelSchematicNodes->getNode($nodeId, $userOwner, $projectId, $nodeClassName, true);
            }else if ($operation == "changeNodeCodeContent"){
                $result = $this->modelSchematicNodes->updateNodeCodeContent($userOwner, $projectId, $nodeClassName, $nodeNewCodeContent, true);
            }else if ($operation == "deleteNode"){
                $result = $this->modelSchematicNodes->deleteNode($userOwner, $projectId, $nodeId, $nodeClassName);
            }else{
                $result["errorMsg"] = "node operation not recognized";
            }

            $result['token'] = $loginInfo['token'];
            echo json_encode($result);
        }else{
            $result["errorMsg"] = "User not logged!";
            echo json_encode($result);
        }
    }

    /*
     *  TODO this function is in implementation progress
     *      14th September
     *          - it's done and tested, there could be errors but seems to be working when called from Chrome using console and sending ajax requests
     *          - I am exhausted from work and it's 3am so I am OK with how it's running now
     */
    function doCompileProject($printResultUsingEcho = true){
        $result = array("status" => 0, "errorMsg" => "", "message" => "");

        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1) {
            $currentUser = $this->modelLogin->getCurrentUserId();
            $currentProject = $this->modelLogin->getCurrentUserProjectId();
            $ideVersion = $this->modelProject->getProjectVersion($currentProject);

            $nodeCodeContent = $this->getVariableFromPost("nodeCodeContent", "");
            $codeStr = hex2bin($nodeCodeContent);
            $projectOutputDir = $this->modelDirectory->createCurrentUserProjectTempDir($currentUser, $currentProject);
            $outputFileName = $this->getVariableFromPost("outputFileName", "main");
            $librariesList = $this->getVariableFromPost("nodeCodeAdditionalLibraries", "");
            $librariesList = explode(',', $librariesList);

            $result = $this->modelProject->compileProjectCpp($codeStr, $projectOutputDir, $outputFileName, $librariesList, $currentUser, $currentProject);
        }else{
            $result["errorMsg"] .= "User not logged!\n";
        }

        if ($printResultUsingEcho == true){
            echo(json_encode($result));
        }

        return $result;
    }

    /*
     *  TODO this function is in implementation progress
     *      14th September
     *          - I've done it using if/else really nested and seems to be working
     *          - there is many combinations what can happen I put there checking
     *          - it could be maybe done simpler but now I am exhausted after work and so and this was wrote just dull and tested from Chrome at least it's running and oing checks right
     *          - if reimplemented just implement another method like doNodeUpload2() or something, left this one :)
     */
    function doNodeUpload(){
        $result = array("status" => 0, "errorMsg" => "", "warningMsg" => "", "message" => "");

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
        if ($loginInfo["isLogged"] == 1){
            /*
             *  Get current logged user information.
             */
            $userOwner = $this->modelLogin->getCurrentUserId();
            $projectId = $this->modelLogin->getCurrentUserProjectId();
            $nodeId = $this->getVariableFromPost("nodeId", -1);
            $nodeClassName = $this->getVariableFromPost("nodeClassName", "");
            $nodeDisplayName = $this->getVariableFromPost("nodeDisplayName", "");
            $nodeClassParentName = $this->getVariableFromPost("nodeClassParentName", "");
            $nodeCodeContent = $this->getVariableFromPost("nodeCodeContent", "");

            ###
            #   Here first need to be checked:
            #       1. check if ID, className from DB, className from codeContent are ok, ie. if node is going to be:
            #           a) update - just update code content
            #           b) create - whole new node is going to be create
            #           c) rename and update - first rename then update code content
            #

            ###
            #   1. look into DB if node exists based on its ID or class name
            #
            $nodeInfo  = null;
            if ($nodeId != -1 || $nodeClassName != "") $nodeInfo = $this->modelSchematicNodes->getNode($nodeId, $userOwner, $projectId, $nodeClassName);

            ###
            #   2. Going to figure out what to do a), b) or c)
            #
            if ($nodeId != -1 && $nodeInfo){
                $nodeNameAndParentFromCode = $this->modelSchematicNodes->extractClassNameAndParent($nodeCodeContent);

                if ($nodeClassName != "" && ($nodeInfo['node_class_name'] != $nodeClassName || $nodeNameAndParentFromCode["nodeClassName"] != $nodeClassName)){
                    #
                    #   option c)
                    #
                    if ($nodeNameAndParentFromCode["nodeClassName"] != $nodeClassName) $result["warningMsg"] = "Node name in code [".$nodeNameAndParentFromCode["nodeClassName"]."] and in post parameter [".$nodeClassName."] are different, using name from code";

                    $result["renameResult"] = $this->modelSchematicNodes->updateNodeClassName($nodeNameAndParentFromCode["nodeClassName"], $userOwner, $projectId, $nodeInfo['node_class_name'], true);

                    $affectedRows = $this->modelSchematicNodes->updateNodeCodeContent($userOwner, $projectId, $nodeNameAndParentFromCode["nodeClassName"], $nodeCodeContent, true);
                    $result["updateNodeCodeContentResult"] = array(
                        "affectedRows" => $affectedRows
                    );

                    #
                    #   For now just check if rename was sucessful, there is no check for updated node code content since code could stay same and affected rows therefore are 0
                    #
                    if ($result["renameResult"]["status"]){
                        $result["status"] = 1;
                        $result["message"] .= "Node upload - based on nodeId - node name [".$nodeNameAndParentFromCode["nodeClassName"]."] - OK\n";
                    }
                }else{
                    #
                    #   option a)
                    #

                    if (strlen($nodeCodeContent) > 0){
                        $affectedRows = $this->modelSchematicNodes->updateNodeCodeContent($userOwner, $projectId, $nodeInfo["node_class_name"], $nodeCodeContent, true, $nodeId);

                        //if content is same as before 0 node is updated this is DB thing
                        $result["status"] = 1;
                        $result["message"] .= "Node upload - UPDATE based on nodeId - ".$nodeInfo["node_class_name"]." - update $affectedRows node - OK\n";
                    }else{
                        $result["errorMsg"] .= "Node upload - UPDATE based on nodeId - received node content is empty\n";
                    }
                }

                #
                #   If node parent in code is different than one in DB update it
                #
                if ($nodeNameAndParentFromCode["nodeClassParent"] != $nodeInfo['node_class_parent']){
                    $affectedRows = $this->modelSchematicNodes->updateNodeClassParent($nodeNameAndParentFromCode["nodeClassParent"], $userOwner, $projectId, "", $nodeId);
                    if ($affectedRows > 0) $result["message"] .= "Node upload - UPDATE based on nodeId - node id ".$nodeId." - update class parent on $affectedRows row\n";
                }

                #
                #   Change display name if available
                #
                if ($nodeDisplayName != ""){
                    $changeDisplayNameResult = $this->modelSchematicNodes->updateNodeDisplayName($nodeDisplayName, $nodeId, $userOwner, $projectId);
                    $result["changeDisplayNameResult"] = $changeDisplayNameResult;
                }

            }else if ($nodeClassName){
                #
                #   Perform check if node class name and parent are same, for case that user try to push there some suspicious code
                #       THIS IS REALLY ERROR CASE BECAUSE USER WANT TO UPLOAD SOME CLASS BY NAME BUT SENDING DIFFERENT NODE CLASS NAME IN CODE!!!
                #
                $nodeNameAndParentFromCode = $this->modelSchematicNodes->extractClassNameAndParent($nodeCodeContent);
                if ($nodeNameAndParentFromCode["nodeClassName"] != $nodeClassName){
                    $result["errorMsg"] .= "Node upload - UPDATE based on className - different class name in POST parameters [".$nodeClassName."] and inside code [".$nodeNameAndParentFromCode["nodeClassName"]."] !\n";
                    echo json_encode($result);
                    return;
                }

                if ($nodeInfo){
                    #
                    #   option a)
                    #

                    if (strlen($nodeCodeContent) > 0){
                        $affectedRows = $this->modelSchematicNodes->updateNodeCodeContent($userOwner, $projectId, $nodeInfo["node_class_name"], $nodeCodeContent, true);

                        #
                        #   If node parent in code is different than one in DB update it USING NODE CLASS NAME
                        #
                        if ($nodeNameAndParentFromCode["nodeClassParent"] != $nodeInfo['node_class_parent']){
                            $affectedRows = $this->modelSchematicNodes->updateNodeClassParent($nodeNameAndParentFromCode["nodeClassParent"], $userOwner, $projectId, $nodeClassName);
                            if ($affectedRows > 0) $result["message"] .= "Node upload - ".$nodeInfo["node_class_name"]." - update class parent on $affectedRows row\n";
                        }

                        //if content is same as before 0 node is updated this is DB thing
                        $result["status"] = 1;
                        $result["message"] .= "Node upload - UPDATE based on className - ".$nodeInfo["node_class_name"]." - update $affectedRows node - OK\n";
                    }else{
                        $result["errorMsg"] .= "Node upload - UPDATE based on className - received node content is empty\n";
                    }

                }else{
                    #
                    #   option b)
                    #

                    $saveResult = $this->modelSchematicNodes->saveNode(
                        $userOwner,
                        $projectId,
                        $nodeClassName,
                        $nodeCodeContent,
                        "",
                        "",
                        "",
                        $nodeDisplayName ? $nodeDisplayName : $nodeClassName
                    );

                    //TODO need to do evaluation of JSON from save node, for now suppose that something happen
                    $result["message"] = $saveResult["message"];
                    $result["status"] = 1;
                }
            }else{
                $result["errorMsg"] .= "Node upload - no nodeId [$nodeId] or nodeClassName [$nodeClassName] provided!\n";
            }

        }else{
            $result["errorMsg"] .= "User not logged!\n";
        }

        #
        #   PRINT RESULT JSON TO OUTPUT
        #
        echo json_encode($result);
    }

    function doRegisterUserViaEmail(){
        $username = $this->getVariableFromPost("username", "");
        $password = $this->getVariableFromPost("password", "");
        $passwordConfirmation = $this->getVariableFromPost("passwordConfirmation", "");
        $email = $this->getVariableFromPost("email", "");

        //this is used when user confirm registration after mail was send with token
        $token = $this->getVariableFromGet("token", "");

        if ($token != "") {
            /*
             *  Registration confirmation
             */
            $result = $this->modelLogin->confirmRegistration($token);

            echo("Registration confirmation output<br />");
            echo("<br />\n");
            var_dump($result);
            echo("<br />\n");
            echo("<a href='?q=home'>Back to home</a>\n");
        }else if ($username == "" || $password == "" || $email == ""){
            /*
             *  Default registration form - POST data weren't sent then it means user just want to register new one, therefore display form to do it.
             */
            include("ViewRegisterForm.php");
        }else{
            /*
             *  Sending mail - POST data were sent, therefore try to process them and send email with confirmation token to mail address.
             */
            $token = md5($username.$email.$password);
            $resultNewRegistration = $this->modelLogin->addNewUserRegistration($username, md5($password), $email, $token);
            if ($resultNewRegistration["status"] == 1){
                $registrationLink = $_SERVER['REQUEST_URI']."&token=$token";

                $receiver = $email;
                $subject = "GraphLang, confirm registration";
                $body = "Please confirm your registration on following <br />";
                $body .= "<a href='".$registrationLink."'>---> link <---</a><br />";
                $body .= "<br />";
                $body .= "Link will be active for 24 hours.<br />";
                $body .= "<br />";

                $this->modelLogin->sendMail($receiver, $subject, $body);
                echo("<br />\n");
                echo("<a href='?q=home'>Back to home</a>\n");
            }else{
                echo("ERROR DURING REGISTRATION\n");
                echo("<br />");
                echo("<br />");
                echo($resultNewRegistration["error"]);
                echo("<br />");
                echo("<br />");
            }

        }
    }

    function doCheckIfProcessIsRunning(){
        $result = array("status" => 0, "errorMsg" => "", "message" => "");

        $loginInfo = $this->getLoginInfo();
        if ($loginInfo["isLogged"] == 1) {
            $pidToBeChecked = $this->getVariableFromGet("pid", -1);
            #
            #   WRITE COMPILATION RESULT
            #
            $checkProcessResult = $this->modelOsCommands->checkIfProcessIsRunning($pidToBeChecked);
            $result["status"] =  $checkProcessResult["isRunning"];
            $result["message"] .= $checkProcessResult["commandOutput"];
        }else{
            $result["errorMsg"] .= "User not logged!\n";
        }

        echo(json_encode($result));
    }

    /*
     *  Execute compiled program and write PID of its process into file in same folder.
     *  These steps will be performed:
     *      1. generate C++ code
     *      2. compile code
     *      3. if program is already running kill it (there is file in dir with last executed PID, so it check if process exists)
     *      4. execute program
     *      5. write pid of current process to file like .current_pid
     *
     *  TODO: There is needed to add compilation parameters for g++ like when include ie. ZeroMQ library to use -I<path to folder with .h files> -L<somepath to folder with lib> -lzmq
     *        otherwise project will not compile
     */
    function doRunProject(){
        $result = array("status" => 0, "errorMsg" => "", "message" => "");

        //first compile project, it requires that code must be sent over POST to be compiled
        $projectCompilationResult = $this->doCompileProject(false);

        if ($projectCompilationResult["status"] == 1){
            /*
             *  compilation was OK, run program and
             */
            $compilationDirectory = rtrim(dirname($projectCompilationResult["outputFileAbsolutePath"]), '/');
            $currentProgramPidFile = $compilationDirectory.DIRECTORY_SEPARATOR.".current_running_pid";
            $currentProgramPid = -1;

            //first check if current program is running already, if yes kill it?
            if (file_exists($currentProgramPidFile)){
                $result["killedProcesses"] = array();
                $currentProgramProcessPidList = explode("\n", file_get_contents($currentProgramPidFile));
                foreach($currentProgramProcessPidList as $currentProgramProcessPid){
                    $currentProgramProcessPid = trim($currentProgramProcessPid);
                    $currentProcessStatus = $this->modelOsCommands->checkIfProcessIsRunning($currentProgramProcessPid);
                    if (
                        $currentProcessStatus["isRunning"] == true
                        && str_contains($currentProcessStatus["commandOutput"], "main")             //suppose that program is named main.cpp therefore process will be main.exe
                    ){
                        $killingProcessResult = $this->modelOsCommands->killProcess($currentProgramProcessPid);  //kill program if already running
                        array_push($result["killedProcesses"], array($currentProgramProcessPid => $killingProcessResult));
                    }
                }
                unlink($currentProgramPidFile);                                                    //remove file with pid
            }

            //this will run compiled program in Windows environment
            //$runCompiledProgramResult = $this->modelOsCommands->runCommand($projectCompilationResult["outputFileAbsolutePath"], $compilationDirectory);
            //$currentProgramPid = $runCompiledProgramResult["pid"];

            /*
             *  This will run program in msys environment, there is used on some example zeromq and it's simpler to use it from msys, msys2/usr/bin folder must be added to environment path variable!
             *  MSYS2 is using its internal PID but when used ps -W there is column WINPID which is real windows native PID, extracted by using awk
             *     1.) cd "$(cygpath -u '%cd%')" → moves bash into the Windows current directory.
             *     2.) ./main.exe & → starts your program in the background.
             *     3.) sleep 1 → gives it a moment to appear in ps. Changed to 1ms, THIS CAN CAUSE PROBLEMS BUT NOW SEEMS FINE.
             *     4.) ps -W | grep main.exe | awk '{print $4}' > outputFileName → extracts the Windows PID.
             */
            $cmdStr = "";
            if ($this->modelOsCommands->isOsWindows()){
                $cmdStr = "bash -lc \"cd \\\"$(cygpath -u '%cd%')\\\" && ./main.exe & sleep 0.7; ps -W | grep main | awk '{print $4}' > \\\"$(cygpath -u '%cd%')\\\"/.current_running_pid\"";
            }
            if ($this->modelOsCommands->isOsLinux()){
                $cmdStr = "./main.exe & sleep 2; ps -aux | grep main.exe | awk '{print $2}' > .current_running_pid";
            }

            $runCompiledProgramResult = $this->modelOsCommands->runCommand($cmdStr, $compilationDirectory);
            for($i = 0; $i < 10; $i++){
                if(file_exists($currentProgramPidFile)){
                    $currentProgramPid = file_get_contents($currentProgramPidFile);     //read pid from file to write it to answer
                    break;
                }
                sleep(1); // if not found wait one second before continue looping, using this to be sure it's working properly to give it time
                //usleep(300000);   //sleep in microseconds, this is extreme
            }

            if (intval($currentProgramPid) != -1){
                file_put_contents($currentProgramPidFile, $currentProgramPid);
                $result["status"] = 1;
                $result["message"] = "Program was executed with pid ".$currentProgramPid;
            }else{
                //PROGRAM NOT RUNNING
                $result["errorMsg"] = "There was problem to execute compiled program. PID: ".$currentProgramPid;
                $result["message"] = "FILE: ".$currentProgramPidFile.", PID: ".$currentProgramPid;
            }
        }else{
            //COMPILATION FAILED
            $result["errorMsg"] = "There was problem to compile program.";
            $result["message"] = $projectCompilationResult["message"];
        }

        echo(json_encode($result));
    }

}
?>