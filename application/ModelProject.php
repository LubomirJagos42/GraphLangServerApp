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

        $result = $this->db_conn->insert_id;

        return $result;
    }

    function updateProject($userId, $projectId, $projectName, $projectDescription, $projectImage, $projectVisibility, $projectCodeTemplate, $projectLanguage, $projectIdeVersion){
        $outputArray = array("status" => 0, "errorMsg" => "");

        $queryStr = "";
        $queryStr .= "UPDATE user_projects SET";
        $queryStr .= " project_name = '$projectName',";
        $queryStr .= " project_graphlang_version = '$projectIdeVersion',";
        $queryStr .= " project_visibility = '$projectVisibility',";
        if ($projectImage === null) $queryStr .= " project_image = '',";
        else if ($projectImage !== "") $queryStr .= " project_image = '$projectImage',";
        $queryStr .= " project_language = '$projectLanguage',";
        $queryStr .= " project_description = '$projectDescription',";
        $queryStr .= " project_code_template = '$projectCodeTemplate'";
        $queryStr .= " WHERE internal_id=$projectId AND project_owner=$userId;";

        try {
            $result = $this->db_conn->query($queryStr);
        }catch (Exception $e){
            $outputArray["errorMsg"] = $this->db_conn->error;
            return $outputArray;
        }

        if ($this->db_conn->affected_rows == 0){
            $outputArray["status"] = 0;
            $outputArray["errorMsg"] = "No rows were changed.<br />\nquery: $queryStr";
            return $outputArray;
        }

        $outputArray["status"] = 1;
        return $outputArray;
    }

    function deleteProject($userOwner, $projectId){
        $projectId = (int) $projectId;

        $resultStatus = array();

        //there are some foreign keys defined over assignement tables therefore deletion must be performed in right table order, some tables must be erased at start

        $queryStr = "";
        $queryStr .= "DELETE FROM nodes_to_category_assignment WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["nodes_to_category_assignment"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM media_to_project_assignment WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["media_to_project_assignment"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM project_categories WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["project_categories"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM storage_schematic_blocks WHERE node_project=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["storage_schematic_blocks"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM user_projects WHERE internal_id=$projectId AND project_owner=$userOwner;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["user_projects"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        return $resultStatus;
    }

    function getProject($userOwner, $projectId){
        $queryStr = "";
        $queryStr .= "SELECT project_name, project_graphlang_version, project_visibility, project_image, project_description, project_code_template, project_language";
        $queryStr .= " FROM user_projects";
        $queryStr .= " WHERE project_owner=$userOwner AND internal_id=$projectId";

        $outputArray = array("status" => 0, "errorMsg" => "");

        try {
            $result = $this->db_conn->query($queryStr);
        }catch (Exception $e){
            $outputArray["status"] = 0;
            $outputArray["errorMsg"] = $this->db_conn->error;
            return $outputArray;
        }

        if($result){
            $row = $result->fetch_assoc();
            $outputArray["status"] = 1;
            $outputArray["project_name"] = $row["project_name"];
            $outputArray["project_graphlang_version"] = $row["project_graphlang_version"];
            $outputArray["project_visibility"] = $row["project_visibility"];
            $outputArray["project_image"] = $row["project_image"];
            $outputArray["project_description"] = $row["project_description"];
            $outputArray["project_code_template"] = $row["project_code_template"];
            $outputArray["project_language"] = $row["project_language"];
        }

        return $outputArray;
    }

    function getProjectOwnerId($projectId){
        $queryStr = "";
        $queryStr .= "SELECT project_owner FROM user_projects WHERE internal_id=$projectId;";

        $result = $this->db_conn->query($queryStr);

        $row = $result->fetch_assoc();
        return $row["project_owner"];
    }

    function getTemplateProject(){
        $queryStr = "SELECT internal_id, project_owner, project_graphlang_version, project_name, project_visibility, project_image, project_description, project_code_template, project_language FROM user_projects WHERE project_isTemplate=1;";
        $result = $this->db_conn->query($queryStr);

        $outputArray = $result->fetch_assoc();  //now fetch just first row
        return $outputArray;
    }
    function copyNodesWithCategoriesFromToProject($sourceProjectId, $targetProjectId){
        $queryStr = "SELECT project_owner FROM user_projects WHERE internal_id=$sourceProjectId;";
        $result = $this->db_conn->query($queryStr);
        $sourceProjectOwner = $result->fetch_row()[0];

        $queryStr = "SELECT project_owner FROM user_projects WHERE internal_id=$targetProjectId;";
        $result = $this->db_conn->query($queryStr);
        $targetProjectOwner = $result->fetch_row()[0];

        // COPY NODES
        $queryStr = "";
        $queryStr .= "INSERT INTO storage_schematic_blocks (";
        $queryStr .= "node_display_name,";
        $queryStr .= " node_class_name,";
        $queryStr .= " node_class_parent,";
        $queryStr .= " node_content_code,";
        $queryStr .= " node_language,";
        $queryStr .= " node_isHidden,";
        $queryStr .= " node_directory,";
        $queryStr .= " node_owner,";
        $queryStr .= " node_project";
        $queryStr .= ")";
        $queryStr .= " SELECT";
        $queryStr .= " node_display_name,";
        $queryStr .= " node_class_name,";
        $queryStr .= " node_class_parent,";
        $queryStr .= " node_content_code,";
        $queryStr .= " node_language,";
        $queryStr .= " node_isHidden,";
        $queryStr .= " node_directory,";
        $queryStr .= " $targetProjectOwner,";
        $queryStr .= " $targetProjectId";
        $queryStr .= " FROM storage_schematic_blocks";
        $queryStr .= " WHERE node_project=$sourceProjectId";
        $queryStr .= ";";
        echo("<br /><br />\n".$queryStr."<br /><br />\n");
        $result = $this->db_conn->query($queryStr);

            //GET array for new nodes, their IDs and class names to can create later nodes to categories assignement
            $queryStr = "";
            $queryStr .= "SELECT internal_id, node_class_name FROM storage_schematic_blocks WHERE node_project=$targetProjectId;";
            $result = $this->db_conn->query($queryStr);
            $newNodesList = array();
            while ($row = $result->fetch_assoc()) $newNodesList[$row["node_class_name"]] = $row["internal_id"];

        // COPY CATEGORIES
        $queryStr = "";
        $queryStr .= "INSERT INTO project_categories (category_name, project_id)";
        $queryStr .= " SELECT category_name, $targetProjectId  FROM project_categories WHERE project_id=$sourceProjectId";
        $queryStr .= ";";
        echo("<br /><br />\n".$queryStr."<br /><br />\n");
        $result = $this->db_conn->query($queryStr);

        //
        // CREATE NEW NODES TO CATEGORIES ASSIGNEMENT
        //

            // GET array for new categories to have their IDs and names to be able rerecreate nodes to categories assignement
            $queryStr = "";
            $queryStr .= "SELECT internal_id, category_name FROM project_categories WHERE project_id=$targetProjectId;";
            echo("<br /><br />\n".$queryStr."<br /><br />\n");
            $result = $this->db_conn->query($queryStr);

            $newCategoryNameToId = array();
            while($row = $result->fetch_assoc()) $newCategoryNameToId[$row['category_name']] = $row['internal_id'];

        //GET assignement node_class_name to category
        $queryStr = "";
        $queryStr .= "SELECT";
        $queryStr .= " project_categories.category_name AS category_name,";
        $queryStr .= " storage_schematic_blocks.node_class_name AS node_class_name";
        $queryStr .= " FROM nodes_to_category_assignment";
        $queryStr .= " JOIN project_categories ON nodes_to_category_assignment.category_id = project_categories.internal_id";
        $queryStr .= " JOIN storage_schematic_blocks ON nodes_to_category_assignment.node_id = storage_schematic_blocks.internal_id";
        $queryStr .= "  WHERE nodes_to_category_assignment.project_id = $sourceProjectId;";
        echo("<br /><br />\n".$queryStr."<br /><br />\n");
        $result = $this->db_conn->query($queryStr);
        $originalNodeCategoryAssignement = array();
        while($row = $result->fetch_assoc()) $originalNodeCategoryAssignement[$row['node_class_name']] = $row['category_name'];

        echo("<br /><br />\n");
        print_r($newCategoryNameToId);

        echo("<br /><br />\n");
        print_r($newNodesList);

        echo("<br /><br />\n");
        print_r($originalNodeCategoryAssignement);

        foreach ($originalNodeCategoryAssignement as $node_class_name => $category_name){
            $queryStr = "INSERT INTO nodes_to_category_assignment (category_id, node_id, project_id) VALUES (".$newCategoryNameToId[$category_name].",".$newNodesList[$node_class_name].",$targetProjectId);";
            $result = $this->db_conn->query($queryStr);
        }

        echo("<br /><br />\n");
    }

}
?>