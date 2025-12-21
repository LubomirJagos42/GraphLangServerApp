<?php
include_once("ControllerParent.php");
include_once("ModelOsCommands.php");
include_once("ModelLogin.php");
include_once("ModelDirectory.php");
include_once("ModelProject.php");

#Controller Login Class
class ControllerOsCommands extends ControllerParent{
    private $modelOsCommands;
    private $modelLogin;
    private $modelDirectory;

    function __construct($db_conn){
        $this->modelOsCommands = new ModelOsCommands($db_conn);
        $this->modelLogin = new ModelLogin($db_conn);
        $this->modelDirectory = new modelDirectory($db_conn);
        $this->modelProject = new modelProject($db_conn);
    }

    function doRunPythonCppDebugServer(){
        $result = array("status" => 0, "errorMsg" => "", "warningMsg" => "", "message" => "", "debuggerLayerMode" => "");

        $currentProject = $this->modelLogin->getCurrentUserProjectId();
        $ideVersion = $this->modelProject->getProjectVersion($currentProject);
        $embeddedInfo = $this->modelProject->getProjectEmbeddedInfo($currentProject);

        $startDir = dirname(__FILE__, 2).DIRECTORY_SEPARATOR.$this->modelDirectory->getIdeHtmlIncludeDirPrefix($ideVersion).DIRECTORY_SEPARATOR."python_tools";
        $startDir = str_replace('\\', '/', $startDir);  #even Windows is OK with this when / is used instead of \

        $processResult = array(
            "status" => -1,
            "pid" => -1,
            "stdout" => "",
            "stdin" => ""
        );
        $osCommandToStartDebuggerLayer = "";
        if ($embeddedInfo["isEmbedded"] == false) {
            $result["debuggerLayerMode"] = "desktop";
            $osCommandToStartDebuggerLayer = 'python3 DebuggerCppBrowserInterface.py --logging'; #add argument --logging if need log if something is crashing in tmp/gdb_websocket.log
        }else{
            $result["debuggerLayerMode"] = "embedded";
            $osCommandToStartDebuggerLayer = 'python3 DebuggerCppBrowserInterface.py  --logging --embedded --projectDir '.$this->modelDirectory->getCurrentUserProjectTempDir($currentProject);
        }
        $processResult = $this->modelOsCommands->runCommand($osCommandToStartDebuggerLayer, $startDir);

        //        sleep(5);
        //        $this->modelOsCommands->killProcess($processResult['pid'], $processResult['resource']);

        $result["processResult"] = array();
        $result["processResult"]["startDir"] = $startDir;
        $result["processResult"]["osCommand"] = $osCommandToStartDebuggerLayer;
        $result["processResult"]["pid"] = $processResult["pid"];
        $result["processResult"]["status"] = $processResult["status"];
        if (array_key_exists("stdout", $processResult)) {$result["processResult"]["stdout"] = $processResult["stdout"];}
        if (array_key_exists("stderr", $processResult)) {$result["processResult"]["stderr"] = $processResult["stderr"];}

        //        var_dump($result);
        echo(json_encode($result));
    }

}
?>