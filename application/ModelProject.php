<?php
class ModelProject
{
    private $db_conn;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
    }

    function getProjectVersion($projectId = -1){
        $projectId = (int) $projectId;

        if ($projectId > -1) {
            $queryStr = "SELECT project_graphlang_version FROM user_projects WHERE internal_id=$projectId;";
            $result = $this->db_conn->query($queryStr);

            $version = "";

            $row = $result->fetch_row();
            if ($row != null) $version = $row[0];

            return $version;
        }

        return "";
    }

    function createProject($userOwner, $projectName, $projectDescription, $projectImage, $projectVisibility, $projectCodeTemplate, $projectLanguage, $projectIdeVersion){
        $queryStr = "";
        $queryStr .= "INSERT INTO user_projects";
        $queryStr .= "(project_owner, project_name, project_graphlang_version, project_visibility, project_description, project_image, project_code_template, project_language)";
        $queryStr .= " VALUES ($userOwner, '$projectName', '$projectIdeVersion', '$projectVisibility', '$projectDescription', '$projectImage', '$projectCodeTemplate', '$projectLanguage')";

        $result = $this->db_conn->query($queryStr);
        return $result;
    }

    function deleteProject($userOwner, $projectId){
        $projectId = (int) $projectId;

        $resultStatus = array();

        $queryStr = "";
        $queryStr .= "DELETE FROM user_projects WHERE internal_id=$projectId AND project_owner=$userOwner;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["user_projects"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM project_categories WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["project_categories"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM nodes_to_category_assignment WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["nodes_to_category_assignment"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM media_to_project_assignment WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["media_to_project_assignment"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM storage_schematic_blocks WHERE node_project=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["storage_schematic_blocks"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        return $resultStatus;
    }

}
?>