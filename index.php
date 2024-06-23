<?php
$EXECUTION_START_TIME = microtime(true);

session_start();

include_once("application/config_database.php");
include_once("application/ControllerDefault.php");

	$controller = new ControllerDefault($db_conn);

	$GENERATE_SCRIPT_EXECUTION_TIME = true;

	$q = "";
	$usertoken = "";
	if (isset($_GET['q'])) $q = $_GET['q'];
	if (isset($_GET['token'])) $usertoken = $_GET['token'];

	#
	#	Basic routing based on 'q' param from url
	#
	if ($q == "experiment"){
		$controller->doDefaultRouting();
	}else if($q == "ide"){
		$controller->doGraphLangIDE();	
	}else if($q == "shapeDesigner"){
		$controller->doGraphLangShapeDesigner();
	}else if($q == "loadNodesFromServer"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doLoadNodesFromServer();	
	}else if($q == "uploadNodesToServer"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doUploadNodesToServer();	
	}else if($q == "getOrderedNodes"){
		$controller->doGetOrderedNodes();
	}else if($q == "getNodeJavascriptCode"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doGetNodeJavascriptCode();
	}else if($q == "updateNodeJavascriptCode"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doUpdateNodeJavascriptCode();
	}else if($q == "getJavascriptForNodes"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doGetJavascriptForNodes();
	}else if($q == "doExperimentDebug"){
		$controller->doExperimentDebug();
	}else if($q == "userLogin"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doUserLogin();
	}else if($q == "userLoginForm"){
		$controller->doUserLoginForm();
	}else if($q == "logout"){
		$controller->doUserLogout();
	}else if($q == "userProjectList"){
		$controller->doUserProjectList();
	}else if($q == "createProject"){
		$controller->doCreateProject();
	}else if($q == "isUserLogged"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doIsUserLogged();
	}else if($q == "downloadIde"){
		$controller->doDownloadIde();
	}else if($q == "deleteProject"){
		$controller->doDeleteProject();
	}else{
		$controller->doNotFound();			
	}
	
mysqli_close($db_conn);	
$EXECUTION_TOTAL_TIME_SECONDS = microtime(true) - $EXECUTION_START_TIME;
if ($GENERATE_SCRIPT_EXECUTION_TIME) echo("<!-- Total execution time: " . $EXECUTION_TOTAL_TIME_SECONDS . "s -->\n");
?>
