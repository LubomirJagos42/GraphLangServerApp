<?php
$EXECUTION_START_TIME = microtime(true);
include_once("application/config_database.php");
include_once("application/ControllerDefault.php");

	$controller = new ControllerDefault($db_conn);

	$GENERATE_SCRIPT_EXECUTION_TIME = true;

	$q = "";
	if (isset($_GET['q'])) $q = $_GET['q'];
	
	#
	#	Basic routing based on 'q' param from url
	#
	if ($q == "experiment"){
		$controller->doDefaultRouting();
	}else if($q == "ide"){
		$controller->doGraphLangIDE();	
	}else if($q == "loadNodesFromServer"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doLoadNodesFromServer();	
	}else if($q == "uploadNodesToServer"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doUploadNodesToServer();	
	}else if($q == "experimentGetOrderedNodes"){
		$controller->doExperimentGetOrderedNodes();
	}else if($q == "experimentGetJavascriptForNodes"){
		$GENERATE_SCRIPT_EXECUTION_TIME = false;
		$controller->doExperimentGetJavascriptForNodes();
	}else if($q == "doExperimentDebug"){
		$controller->doExperimentDebug();
	}else{
		$controller->doNotFound();			
	}
	
mysqli_close($db_conn);	
$EXECUTION_TOTAL_TIME_SECONDS = microtime(true) - $EXECUTION_START_TIME;
if ($GENERATE_SCRIPT_EXECUTION_TIME) echo("<!-- Total execution time: " . $EXECUTION_TOTAL_TIME_SECONDS . "s -->\n");
?>
