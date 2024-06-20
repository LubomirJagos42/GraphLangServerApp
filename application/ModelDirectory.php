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

    /**
     * @param $projectId
     * @return string
     * @description Return directory based on graphlang project version.
     */
    function getIdeDirectory($projectId){
        $version = $this->getProjectVersion();

        //here will be some decision based on project
        if ($version == "0v1") return "GraphLangServerApp/GraphLang/0v1/";

        //default value
        return "GraphLangServerApp/GraphLang/";
    }

    function getIdeHtmlIncludeDirPrefix($projectId = -1){
        return "GraphLang/0v1/GraphLang IDE";
    }
}
?>