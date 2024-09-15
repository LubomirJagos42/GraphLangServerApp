<?php
#   This class interact with operation system and is running python websocket servers for:
#       - debugging generated code
#       - python server to compile code on remote host if php server running on remote like raspberrypi zero or other
#
class ModelOsCommands{
    private $db_conn;
    private $OperatinSystem;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
        $this->OperatingSystem = php_uname('s');
    }

    function windowsRunCommand($runPath, $startDir){
        $result = array("status" => 0, "errorMsg" => "", "message" => "");
        $pid = -1;

        //Get Operating System

        if($this->OperatingSystem == "Windows NT") {
            //**Works only for PHP 4 and above. proc_get_status() does not return correct PID so
            //work around is used as shown below..
            $descriptorspec = array (
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
            );

            //proc_open — Execute a command
            //'start /b' runs command in the background
            if ( is_resource( $prog = proc_open("start /b " . $runPath, $descriptorspec, $pipes, $startDir, NULL) ) )
            {
                //Get Parent process Id
                $ppid = proc_get_status($prog);
                $pid=$ppid['pid'];
            }
            else
            {
                $result["errorMsg"] = "Failed to execute!";
                return $result;
            }
            $output = array_filter(explode(" ", shell_exec("wmic process get parentprocessid,processid | find \"$pid\"")));
            array_pop($output);

            //Process Id is
            $pid = end($output);
        }
        else if($this->OperatingSystem == "Linux") {

            //**Works only for PHP 4 and above. proc_get_status() does not return correct PID so
            //work around is used as shown below..
            $descriptorspec = array (
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
            );

            //proc_open — Execute a command
            //'nohup' command line-utility will allow you to run command/process or shell script that can continue running in the background
            if (is_resource($prog = proc_open("nohup " . $runPath, $descriptorspec, $pipes, $startDir, NULL) ) )
            {
                //Get Parent process Id
                $ppid = proc_get_status($prog);
                $pid=$ppid['pid'];

                //Process Id is
                $pid=$pid+1;

            }
            else
            {
                $result["errorMsg"] = "Failed to execute!";
                return $result;
            }
        }

        $result["pid"] = $pid;
        $result["resource"] = $prog;

        return $result;
    }

    function killProcess($pid, $resource = null){
        if ($this->OperatingSystem == "Linux"){
            posix_kill($pid);
        }
        if ($this->OperatingSystem == "Windows NT"){
            /*
             *  THIS NOT RUNNING NEED TO BE REPAIRED
             */
            //proc_terminate($resource);
            exec('taskkill /F /PID $pid');
        }
    }

}
?>

