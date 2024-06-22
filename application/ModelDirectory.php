<?php
class ModelDirectory
{
    private $db_conn;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
    }

    function getProjectVersion($projectId){
        return "0v1";   #TODO: neeed implement this using reading from DB, now static value
    }

    function getIdeHtmlIncludeDirPrefix($version = ""){
        if ($version != "") {
            return "GraphLang/$version/GraphLang IDE";
        }else{
            return "GraphLang/default/GraphLang IDE";
        }

    }
}
?>