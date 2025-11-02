<?php
#   This class interact with operation system and is running python websocket servers for:
#       - debugging generated code
#       - python server to compile code on remote host if php server running on remote like raspberrypi zero or other
#
class ModelOsCommands{
    private $db_conn;
    private $OperatingSystem;

    function __construct($db_conn){
        $this->db_conn = $db_conn;

        $this->OperatingSystem = php_uname('s');
        if ($this->OperatingSystem == "") $this->OperatingSystem = PHP_OS;
    }

    function runCommand($runPath, $startDir){
        $result = array("status" => 0, "errorMsg" => "", "message" => "");
        $pid = -1;

        //Get Operating System

        if($this->isOsWindows()) {
            //Works only for PHP 4 and above. proc_get_status() does not return correct PID so
            //work around is used as shown below..
            $descriptorspec = array (
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w")
            );

            //proc_open — Execute a command
            //'start /b' runs command in the background
            $prog = proc_open("start /b " . $runPath, $descriptorspec, $pipes, $startDir, NULL);
            if (is_resource($prog))
            {
                //Get Parent process Id
                $ppid = proc_get_status($prog);
                $pid=$ppid['pid'];

                /*
                 *  There is problem to get output from stdout and stderr in non-blocking mode, if used proposed function from internet stream_get_content(...)
                 *  they are blocking PHP response since they are waiting till python script ends.
                 */

            }else{
                $result["errorMsg"] = "Failed to execute!";
                return $result;
            }

            $output = array_filter(explode(" ", shell_exec("wmic process get parentprocessid,processid | find \"$pid\"")));
            array_pop($output);

            //Process Id is
            $pid = end($output);
        }
        else if($this->isOsLinux()) {

            //Works only for PHP 4 and above. proc_get_status() does not return correct PID so
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
        $result = "";
        if ($this->isOsLinux()){
            //posix_kill($pid);     //this just send kill signal, not useful
            $result = exec("kill -9 $pid");     //this sends SIGKILL signal to process
        }
        if ($this->isOsWindows()){
            //proc_terminate($resource);
            $result = exec('taskkill /F /PID '.$pid);
        }

        return $result;
    }

    function checkIfProcessIsRunning($pid = -1){
        $result = array("isRunning" => false, "commandOutput" => "");

        if ($this->isOsLinux()){
            $pidCheckResult = exec("if ps -p $pid > /dev/null; then echo \"true\"; else echo \"false\"; fi");
            $result["isRunning"] = $pidCheckResult == "true";
            $result["commandOutput"] = exec("ps --no-headers -p $pid");
        }
        if ($this->isOsWindows()){
            $pidCheckResult = exec('tasklist /FI "PID eq '.$pid.'"');
            $result["isRunning"] = !str_contains($pidCheckResult, "No tasks are running");
            $result["commandOutput"] = $pidCheckResult;
        }

        return $result;
    }

    function isOsWindows(){
        return in_array($this->OperatingSystem, ["Windows NT", "WINNT"]);
    }

    function isOsLinux(){
        return in_array($this->OperatingSystem, ["Linux"]);
    }
}
?>

