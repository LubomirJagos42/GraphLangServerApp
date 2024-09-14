<?php
include_once("ControllerParent.php");

#Controller Parent - functions common for all controllers
class ControllerParent{
    function __construct(){
    }

    public function getVariableFromPost($varname, $defaultVal = null){
        if (isset($_POST[$varname])) return $_POST[$varname];
        return $defaultVal;
    }

    public function getVariableFromGet($varname, $defaultVal = null){
        if (isset($_GET[$varname])) return $_GET[$varname];
        return $defaultVal;
    }

}
?>