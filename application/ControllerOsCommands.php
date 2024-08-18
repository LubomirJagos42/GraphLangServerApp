<?php
include_once("ControllerParent.php");
include_once("ModelOsCommands.php");
include_once("ModelLogin.php");

#Controller Login Class
class ControllerOsCommands extends ControllerParent{
    private $modelOsCommands;
    private $modelLogin;

    function __construct($db_conn){
        $this->modelOsCommands = new ModelOsCommands($db_conn);
        $this->modelLogin = new ModelLogin($db_conn);
    }

    function doRunPythonCppDebugServer(){
        $startDir = getcwd().'\GraphLang\0v1\GraphLang IDE\python_tools';
        $processResult = $this->modelOsCommands->windowsRunCommand(
            'python3 DebuggerCppBrowserInterface.py',
            $startDir
        );
        echo("windows running command pid: ".$processResult['pid']." <br />\n");
        echo("current path: ".$startDir."<br />\n");
        echo("<br />\n");
        var_dump($processResult);
//        sleep(5);
//        $this->modelOsCommands->killProcess($processResult['pid'], $processResult['resource']);
    }

}
?>