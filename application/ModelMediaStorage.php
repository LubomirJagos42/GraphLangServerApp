<?php
class ModelMediaStorage
{
    private $db_conn;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
    }

    function getProjectLibrary($userId, $projectId, $libraryName = ""){
        $queryStr = "SELECT media_content, media_format, media_compile_parameters FROM storage_media WHERE media_name='$libraryName' AND (project_id=$projectId OR project_id IS NULL) AND media_owner=$userId;";
        $result = $this->db_conn->query($queryStr);

        $outputArray = $result->fetch_assoc();  //now fetch just first row
        return $outputArray;
    }

    function getAllUserMedia($userId){
        //$queryStr = "SELECT internal_id, media_format, media_language, media_version, TO_BASE64(media_content) as media_content, project_id, media_name, media_compile_parameters FROM storage_media WHERE media_owner=$userId;";
        $queryStr = "SELECT internal_id, media_format, media_language, media_version, project_id, media_name, media_compile_parameters FROM storage_media WHERE media_owner=$userId;";
        $result = $this->db_conn->query($queryStr);

        $outputArray = array();
        while ($row = $result->fetch_assoc()) {
            array_push($outputArray, $row);
        }

        return $outputArray;
    }

}
?>