<?php
include_once("ModelOsCommands.php");

class ModelProject
{
    private $db_conn;
    private $modelOsCommands;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
        $this->modelOsCommands = new ModelOsCommands($db_conn);
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

    function getProjectEmbeddedInfo($projectId = -1){
        $projectId = (int) $projectId;
        $embeddedInfo = array("isEmbedded" => false, "target" => "");

        if ($projectId > -1) {
            $queryStr = "SELECT project_code_template, project_embedded_platform, project_embedded_board FROM user_projects WHERE internal_id=$projectId;";
            $result = $this->db_conn->query($queryStr);

            $row = $result->fetch_assoc();
            if ($row != null){
                $embeddedInfo["isEmbedded"] = $row["project_code_template"] == "embedded";
                $embeddedInfo["platform"] = $row["project_embedded_platform"];
                $embeddedInfo["board"] = $row["project_embedded_board"];
                $embeddedInfo["target"] = $row["project_code_template"];
            }

            return $embeddedInfo;
        }

        return $embeddedInfo;
    }

    function createProject($userOwner, $projectName, $projectDescription, $projectImage, $projectVisibility, $projectCodeTemplate, $projectLanguage, $projectIdeVersion, $projectEmbeddedPlatform, $projectEmbeddedBoard){
        $queryStr = "";
        $queryStr .= "INSERT INTO user_projects";
        $queryStr .= "(project_owner, project_name, project_graphlang_version, project_visibility, project_description, project_image, project_code_template, project_language, project_embedded_platform, project_embedded_board)";
        $queryStr .= " VALUES ($userOwner, '$projectName', '$projectIdeVersion', '$projectVisibility', '$projectDescription', '$projectImage', '$projectCodeTemplate', '$projectLanguage', '$projectEmbeddedPlatform', '$projectEmbeddedBoard')";

        $result = $this->db_conn->query($queryStr);

        $result = $this->db_conn->insert_id;

        return $result;
    }

    function updateProject($userId, $projectId, $projectName, $projectDescription, $projectImage, $projectVisibility, $projectCodeTemplate, $projectLanguage, $projectIdeVersion, $projectEmbeddedPlatform, $projectEmbeddedBoard){
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
        $queryStr .= " project_embedded_platform = '$projectEmbeddedPlatform'";
        $queryStr .= " project_embedded_board = '$projectEmbeddedBoard'";
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
        $queryStr .= "DELETE FROM project_deployments WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["project_deployments"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM nodes_to_category_assignment WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["nodes_to_category_assignment"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

        $queryStr = "";
        $queryStr .= "DELETE FROM category_to_category_assignment WHERE project_id=$projectId;";
        $result = $this->db_conn->query($queryStr);
        $resultStatus["category_to_category_assignment"] = ($result == true ? "OK, removed ".$this->db_conn->affected_rows : "FAIL");

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
        $queryStr .= "SELECT project_name, project_graphlang_version, project_visibility, project_image, project_description, project_code_template, project_language, project_embedded_platform, project_embedded_board";
        $queryStr .= " FROM user_projects";
        $queryStr .= " WHERE project_owner=$userOwner AND internal_id=$projectId";

        $outputArray = array(
            "status" => 0,
            "errorMsg" => "",
            "project_name" => "",
            "project_graphlang_version" => "",
            "project_visibility" => "",
            "project_image" => "",
            "project_description" => "",
            "project_code_template" => "",
            "project_language" => "",
            "project_embedded_platform" => "",
            "project_embedded_board" => "",
        );

        try {
            $result = $this->db_conn->query($queryStr);
        }catch (Exception $e){
            $outputArray["status"] = 0;
            $outputArray["errorMsg"] = $this->db_conn->error;
            return $outputArray;
        }

        if($result){
            $row = $result->fetch_assoc();

            if ($row == false){
                $outputArray["status"] = -1;
                $outputArray["errorMsg"] = "no row ind DB for project id: $projectId";
                return $outputArray;
            }

            $outputArray["status"] = 1;
            $outputArray["project_name"] = $row["project_name"];
            $outputArray["project_graphlang_version"] = $row["project_graphlang_version"];
            $outputArray["project_visibility"] = $row["project_visibility"];
            $outputArray["project_image"] = $row["project_image"];
            $outputArray["project_description"] = $row["project_description"];
            $outputArray["project_code_template"] = $row["project_code_template"];
            $outputArray["project_language"] = $row["project_language"];
            $outputArray["project_embedded_platform"] = $row["project_embedded_platform"];
            $outputArray["project_embedded_board"] = $row["project_embedded_board"];
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

    function getProjectLibrary($userId, $projectId, $libraryName = ""){
        $queryStr = "SELECT media_content, media_format, media_compile_parameters FROM storage_media WHERE media_name='$libraryName' AND (project_id=$projectId OR project_id IS NULL) AND media_owner=$userId;";
        $result = $this->db_conn->query($queryStr);

        $outputArray = $result->fetch_assoc();  //now fetch just first row
        return $outputArray;
    }

    function compileProjectCpp($codeStr, $projectOutputDir, $outputFileName, $librariesList, $userId, $projectId){
        $result = array("status" => 0, "errorMsg" => "", "message" => "");

        #
        #   Create project output directory
        #
        if (!$projectOutputDir) {
            $result = array("status" => 0, "errorMsg" => "Unable to create user project temp dir");
            echo($result);
            return;
        }

        #
        #   Erase everything from project build directory
        #
        $compileOutputDir = $projectOutputDir;
        @mkdir($compileOutputDir);
        $result["compileOutputDir"] = $compileOutputDir.DIRECTORY_SEPARATOR."build";

        if (strlen($codeStr) > 0){
            $fileToCompile = $compileOutputDir.DIRECTORY_SEPARATOR."main.cpp";      #name hardcoded since node code is generated into one file
            $outFile = fopen($fileToCompile, "w+");
            fwrite($outFile, $codeStr);
            fclose($outFile);

            #
            #   WRITE ADDITIONAL LIBRARIES FROM DB TO DRIVE to folder libraries
            #
            $projectLibDir = $compileOutputDir.DIRECTORY_SEPARATOR."libraries";
            @mkdir($projectLibDir);
            foreach ($librariesList as $libraryName){
                $mediaObj = $this->getProjectLibrary($userId, $projectId, $libraryName);
                if ($mediaObj == null) break;

                $libDir = $projectLibDir.DIRECTORY_SEPARATOR.$libraryName;
                @mkdir($libDir);
                $media_output_file = $libDir.DIRECTORY_SEPARATOR.$libraryName.".".$mediaObj["media_format"];
                file_put_contents($media_output_file, $mediaObj["media_content"]);
                if ($mediaObj["media_format"] == "zip"){
                    $zip = new ZipArchive();
                    $zip->open($media_output_file);
                    $zip->extractTo($libDir);
                    $zip->close();
                }
            }

            #
            #   TODO: This is experimental, write compile command into .sh file which could be run through msys
            #
            //@mkdir($compileOutputDir.DIRECTORY_SEPARATOR."build");  //create build/ directory
            $compileFileContent = "";
            $compileFileContent .= "mkdir -p build #create build directory, do nothing if exists\n";
            $compileFileContent .= "g++ main.cpp -g -o build/main.exe \\\n";
            $wasExternalLibUsed = false;
            foreach ($librariesList as $libraryName){
                $mediaObj = $this->getProjectLibrary($userId, $projectId, $libraryName);
                if ($mediaObj == null) break;

                if ($mediaObj["media_compile_parameters"] == "" || $mediaObj["media_compile_parameters"] == null){
                    $compileFileContent .= "    -Ilibraries/$libraryName/include \\\n";
                    $compileFileContent .= "    -Llibraries/$libraryName \\\n";
                    $compileFileContent .= "    -l$libraryName \\\n";                    //here is used library name as -l for C++ compiler what means that library must have same name as is named in C++ when installed on system
                    $wasExternalLibUsed = true;
                }
            }
            $compileFileContent .= "2>&1    #redirect error output to stdout\n";
            $bashCompilationScriptFilepath = $compileOutputDir.DIRECTORY_SEPARATOR."compile.sh";
            file_put_contents($bashCompilationScriptFilepath, $compileFileContent);
            chmod($bashCompilationScriptFilepath, 0755);

            #
            #   ALTERNATIVE CREATE CMakeLists.txt FOR USE cmake
            #       - simple cmake file, assume there is main.cpp
            #       - libraries folders are named as written in DB media_name field
            #
            $CMakeListsStr = "";
            $CMakeListsStr .= "cmake_minimum_required(VERSION 3.15)\n";
            $CMakeListsStr .= "project(GraphLangGeneratedApp LANGUAGES CXX)\n";
            $CMakeListsStr .= "\n";
            $CMakeListsStr .= "# Require C++17 (adjust if you want C++20/23)\n";
            $CMakeListsStr .= "set(CMAKE_CXX_STANDARD 17)\n";
            $CMakeListsStr .= "set(CMAKE_CXX_STANDARD_REQUIRED ON)\n";
            $CMakeListsStr .= "\n";
//            $CMakeListsStr .= "# Set include debug symbols in both Debug and Release mode\n";
//            $CMakeListsStr .= "set(CMAKE_CXX_FLAGS_DEBUG_INIT \"-Wall\")\n";
//            $CMakeListsStr .= "set(CMAKE_CXX_FLAGS_RELEASE_INIT \"-Wall\")\n";
//            $CMakeListsStr .= "\n";


            #
            #   gcc diagnostic options documented at: https://gcc.gnu.org/onlinedocs/gcc/Diagnostic-Message-Formatting-Options.html
            #
            $CMakeListsStr .= "# Enable JSON diagnostics when using GCC\n";
            $CMakeListsStr .= "if (CMAKE_CXX_COMPILER_ID STREQUAL \"GNU\")\n";
            $CMakeListsStr .= "    add_compile_options(-fdiagnostics-format=json -fdiagnostics-plain-output)\n";
//            $CMakeListsStr .= "    target_compile_options(GraphLangGeneratedApp PRIVATE -fdiagnostics-format=json-pretty -fno-diagnostics-show-caret)\n";
            $CMakeListsStr .= "endif()\n";
            $CMakeListsStr .= "\n";


            $CMakeListsStr .= "# Directory for logs\n";
            $CMakeListsStr .= 'set(DIAG_DIR "${CMAKE_BINARY_DIR}/diagnostics")'."\n";
            $CMakeListsStr .= 'file(MAKE_DIRECTORY "${DIAG_DIR}")'."\n";
            $CMakeListsStr .= "# For all compiler commands, redirect output to a file\n";
            if ($this->modelOsCommands->isOsWindows()){
                $CMakeListsStr .= 'set_property(GLOBAL PROPERTY RULE_LAUNCH_COMPILE "${CMAKE_COMMAND} -E env DIAG_DIR=${DIAG_DIR} bash ${CMAKE_CURRENT_SOURCE_DIR}/capture_diag.sh")'."\n";
            }elseif ($this->modelOsCommands->isOsWindows()){
                $CMakeListsStr .= 'set_property(GLOBAL PROPERTY RULE_LAUNCH_COMPILE "${CMAKE_COMMAND} -E env DIAG_DIR=${DIAG_DIR} ${CMAKE_CURRENT_SOURCE_DIR}/capture_diag.sh")'."\n";
            }
            $CMakeListsStr .= "\n";


            $CMakeListsStr .= "# Add executable from your main.cpp\n";
            $CMakeListsStr .= "add_executable(main main.cpp)\n";
            $CMakeListsStr .= "\n";

            #
            #   Add directories with header files (.h)
            #       - check if folder exists
            #
            $CMakeListsStr .= "# Tell CMake where to find headers\n";
            foreach ($librariesList as $libraryName){
                $CMakeListsStr .= "set(LIBRARY_DIR \"\${CMAKE_CURRENT_SOURCE_DIR}/libraries/$libraryName\")\n";
                $CMakeListsStr .= "target_include_directories(main PRIVATE \"\${LIBRARY_DIR}\")\n";
                $CMakeListsStr .= "if (EXISTS \"\${LIBRARY_DIR}/include\")\n";
                $CMakeListsStr .= "    target_include_directories(main PRIVATE \${LIBRARY_DIR}/include)\n";
                $CMakeListsStr .= "endif()\n";
                $CMakeListsStr .= "\n";
            }
            $CMakeListsStr .= "\n";

            #
            #   Add compiled dynamic libraries to cmake (Linux -> .a, .so Windows -> .dll)
            #       - here must be check if inside these folder are really compiled libraries
            #
            foreach ($librariesList as $libraryName){
                $CMakeListsStr .= "# Link $libraryName - Tell CMake where to find the $libraryName binary (if there is binary file)\n";
                #
                #   1. original way
                #$CMakeListsStr .= "target_link_directories(main PRIVATE \${CMAKE_CURRENT_SOURCE_DIR}/libraries/$libraryName)\n";
                #
                #   2. more flexible way using find
                $CMakeListsStr .= "set(LIBRARY_DIR \"\${CMAKE_CURRENT_SOURCE_DIR}/libraries/$libraryName\")\n";
                $CMakeListsStr .= "find_library(FIND_LIBRARY_FILE_PATH NAMES $libraryName PATHS \"\${LIBRARY_DIR}\")\n";
                $CMakeListsStr .= "if (FIND_LIBRARY_FILE_PATH)\n";
                $CMakeListsStr .= "    target_link_libraries(main PRIVATE \"\${FIND_LIBRARY_FILE_PATH}\")\n";
                $CMakeListsStr .= "    message(STATUS \"Library $libraryName found\")\n";
                $CMakeListsStr .= "else()\n";
                $CMakeListsStr .= "    message(STATUS \"Could not find $libraryName inside libraries/\")\n";
                $CMakeListsStr .= "endif()\n";
                $CMakeListsStr .= "\n";
            }

            #
            #   CREATE SHELL FILE TO CAPTURE DIAGNOSTIC INTO FILE like build/Debug/diagnostics/main_20251221_035321.txt
            #
            $captureFileContent = '';
            $captureFileContent .= '#!/bin/bash' . "\n";
            $captureFileContent .= '# capture_diag.sh - Capture compiler diagnostics' . "\n";
            $captureFileContent .= "\n";
            $captureFileContent .= '# Get the diagnostic output directory from environment' . "\n";
            $captureFileContent .= 'DIAG_DIR="${DIAG_DIR:-./diagnostics}"' . "\n";
            $captureFileContent .= "\n";
            $captureFileContent .= '# Create directory if it doesn\'t exist' . "\n";
            $captureFileContent .= 'mkdir -p "$DIAG_DIR"' . "\n";
            $captureFileContent .= "\n";
            $captureFileContent .= '# Generate unique filename based on source file' . "\n";
            $captureFileContent .= 'SOURCE_FILE="$@"' . "\n";
            $captureFileContent .= '# Extract just the filename without path and extension' . "\n";
            $captureFileContent .= 'BASENAME=$(basename "${SOURCE_FILE%.*}")' . "\n";
            $captureFileContent .= 'TIMESTAMP=$(date +%Y%m%d_%H%M%S)' . "\n";
            $captureFileContent .= 'DIAG_FILE="${DIAG_DIR}/${BASENAME}_${TIMESTAMP}.txt"' . "\n";
            $captureFileContent .= "\n";
            $captureFileContent .= '# Execute the actual compilation command and capture output' . "\n";
            $captureFileContent .= '"$@" 2>&1 | tee "$DIAG_FILE"' . "\n";
            $captureFileContent .= "\n";
            $captureFileContent .= '# Return the compilation exit code' . "\n";
            $captureFileContent .= 'exit ${PIPESTATUS[0]}' . "\n";

            $captureDiagFilepath = $compileOutputDir.DIRECTORY_SEPARATOR."capture_diag.sh";
            file_put_contents($captureDiagFilepath, $captureFileContent);

            #
            #   THIS WILL BE PART OF PREVIOUS foreach
            #
            #foreach ($librariesList as $libraryName){
            #    $CMakeListsStr .= "target_link_libraries(main PRIVATE $libraryName)\n";
            #}

            $cmakeFilepath = $compileOutputDir.DIRECTORY_SEPARATOR."CMakeLists.txt";
            file_put_contents($cmakeFilepath, $CMakeListsStr);


            #
            #   Create compile shell file for Debug mode (this is for now, for debugging in gdb)
            #
            $compileCMakeFileContent = "";
            $compileCMakeFileContent .= "cmake -D CMAKE_BUILD_TYPE=Debug -G \"Unix Makefiles\" -S . -B build/Debug\n";
            $compileCMakeFileContent .= "cmake --build build/Debug\n";

            $cmakeCompileScriptFilepath = $compileOutputDir.DIRECTORY_SEPARATOR."compile_cmake.sh";
            file_put_contents($cmakeCompileScriptFilepath, $compileCMakeFileContent);
            chmod($cmakeCompileScriptFilepath, 0755);

            #
            #   Run compilation python script from IDE directory
            #       - using absolute paths to be sure
            #       - used dirname(__FILE__, 2) since we are at directory of this php script so tested need goint to parent dir and then one more up, that is 2nd param 2
            #
            #   TODO GraphLang IDE version is hardwired need to be replaced by obtaining from DB
            #
            $fileToCompileAbsolutePath = dirname(__FILE__, 2).DIRECTORY_SEPARATOR.$fileToCompile;
            $compileFileOutputAbsolutePath = dirname(__FILE__, 2).DIRECTORY_SEPARATOR.$compileOutputDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR."Debug".DIRECTORY_SEPARATOR.$outputFileName;


            // //WAY 1 - THIS IS RUNNING, not using external libs
            // //USING PYTHON SCRIPT TO COMPILE CODE USING g++ - RUNNING - not using external C++ libraries
            //$compileCommand = "";
            //$compileCommand .= "python";
            //$compileCommand .= ' "'.dirname(__FILE__, 2).DIRECTORY_SEPARATOR.$this->modelDirectory->getIdeHtmlIncludeDirPrefix($ideVersion).DIRECTORY_SEPARATOR."python_tools".DIRECTORY_SEPARATOR.'compileCppCode.py"';
            //$compileCommand .= ' "'.$fileToCompileAbsolutePath.'"';
            //$compileCommand .= ' "'.$compileFileOutputAbsolutePath.'"';
            //$compileCommand = str_replace('\\', '/', $compileCommand);  #even Windows is OK with this when / is used instead of \
            // //USING BASH SCRIPT
            //$compileCommand = 'bash -lc "$(cygpath -u \'%cd%\')/'.$bashCompilationScriptFilepath.'"';
            //$compileCommand = str_replace('\\', '/', $compileCommand);  #even Windows is OK with this when / is used instead of \

            // WAY 2 - bash script to compile using external libs in msys
            //$compileOutputDir = str_replace('\\', '/', $compileOutputDir);  #even Windows is OK with this when / is used instead of
            //$compileCommand = 'bash -lc "cd $(cygpath -u \'%cd%\')/'.$compileOutputDir.' && ./compile.sh 2>&1; echo $?"';

            // WAY 3 - bash script to compile using cmake
            $compileOutputDir = str_replace('\\', '/', $compileOutputDir);  #even Windows is OK with this when / is used instead of
            $compileCommand = "";
            if ($this->modelOsCommands->isOsWindows()){
                $compileCommand = 'bash -lc "cd $(cygpath -u \'%cd%\')/'.$compileOutputDir.' && ./compile_cmake.sh 2>&1; echo $?"';
            }else if($this->modelOsCommands->isOsLinux()){
                $compileCommand = 'cd $(pwd)/'.$compileOutputDir.' && ./compile_cmake.sh 2>&1; echo $?';
            }

            #
            #   RUN COMPILATION AND FILL RESULT ARRAY
            #
            $result["outputFileAbsolutePath"] = $compileFileOutputAbsolutePath;
            $result["compileCommand"] = $compileCommand;

            // WAY 1 - python script to compile
            //$result["compileCommandOutput"] = shell_exec($compileCommand);  //<------------- COMPILATION TRIGGERED, for python script

            // WAY 2 - using bash script in msys
            //this output must be JSON: {"status": string, "message": string, "errorMessage": string}
            $compileOutputShellExecResult = shell_exec($compileCommand);

            /*
             *  Read compilation status from file, this is done using some auxiliary shell script which writes gcc json output to text file on drive
             */
            $compilationDianosticDir = dirname(__FILE__, 2).DIRECTORY_SEPARATOR.$compileOutputDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR."Debug".DIRECTORY_SEPARATOR."diagnostics";
            $compilationDianosticDir = str_replace('\\', '/', $compilationDianosticDir);  #even Windows is OK with this when / is used instead of
            $diagnosticsFiles = scandir($compilationDianosticDir, SCANDIR_SORT_DESCENDING);
            $diagnosticFilePath = $compilationDianosticDir."/".$diagnosticsFiles[0];
            $diagnosticJsonResult = @file_get_contents($diagnosticFilePath);

            $result["compileCommandOutput"] = json_encode(array(
                "status" => str_starts_with($diagnosticJsonResult, "[]") ? 0 : 1,
                "message" => $compileOutputShellExecResult,
                "errorMsg" => $diagnosticJsonResult
            ));

            #
            #   WRITE COMPILATION RESULT
            #
            $result["status"] = 1;
            $result["message"] .= "Project compilation finished, check compilation output.\n";
        }else{
            $result["status"] = 0;
            $result["message"] .= "No source code, string parameter with source code is empty!\n";
        }

        return $result;
    }

    function compileProjectCppEmbedded($codeStr, $embeddedPlatform, $embeddedBoard, $projectOutputDir, $outputFileName, $librariesList, $userId, $projectId){
        $result = array("status" => 0, "errorMsg" => "", "message" => "");

        #
        #   Create project output directory
        #
        if (!$projectOutputDir) {
            $result = array("status" => 0, "errorMsg" => "Unable to create user project temp dir", "compileCommandOutput" => "");
            echo($result);
            return;
        }

        #
        #   Erase everything from project build directory
        #
        $compileOutputDir = $projectOutputDir;
        @mkdir($compileOutputDir);
        $result["compileOutputDir"] = $compileOutputDir;

        if (strlen($codeStr) > 0){

            #
            #   Init platformio project
            #
            $createProjectCommandStr = "pio project init --board $embeddedBoard -d $compileOutputDir";
//            $this->modelOsCommands->runCommand($createProjectCommandStr, $compileOutputDir);
            $initProjectOutputShellExecResult = shell_exec($createProjectCommandStr);

            $fileToCompile = $compileOutputDir.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."main.cpp";      #name hardcoded since node code is generated into one file
            $outFile = fopen($fileToCompile, "w+");
            fwrite($outFile, $codeStr);
            fclose($outFile);

            #
            #   Platformio build project in debug mode
            #   At first it change current directory to project directory and run local command to enter debug mode for project.
            #
            #       !!!IMPORTANT: using bash from msys2 in windows case to compile to redirect error output to standard output, otherwise compilation errors are not visible in command line
            #                     redirection is done by this: pio debug 2>&1;
            #
            $compileOutputDir = str_replace('\\', '/', $compileOutputDir);  #even Windows is OK with this when / is used instead of
            $buildProjectCommandStr = "";
            if ($this->modelOsCommands->isOsWindows()){
                $buildProjectCommandStr = 'bash -lc "cd $(cygpath -u \'%cd%\')/'.$compileOutputDir.' && pio debug 2>&1; echo $?"';
            }else if($this->modelOsCommands->isOsLinux()){
                $buildProjectCommandStr = 'cd $(pwd)/'.$compileOutputDir.' && pio debug 2>&1; echo $?';
            }
            $buildProjectOutputShellExecResult = shell_exec($buildProjectCommandStr);

            $projectBuildShellResult = "";
            $projectBuildShellResult .= $initProjectOutputShellExecResult;
            $projectBuildShellResult .= "\n";
            $projectBuildShellResult .= $buildProjectOutputShellExecResult;

            $result["compileCommandOutput"] = json_encode(array(
                "status" => str_ends_with($buildProjectOutputShellExecResult, "0\n") ? 0 : 1,   //result code is written as last line in output and there is newline symbol (\n) at the end
                "message" => $projectBuildShellResult,
                "errorMsg" => "",
                "compileCommand" => $buildProjectCommandStr
            ));

            #
            #   WRITE COMPILATION RESULT
            #
            $result["status"] = 1;
            $result["message"] .= "Project compilation finished (embedded), check compilation output.\n";
        }else{
            $result["status"] = 0;
            $result["message"] .= "No source code, string parameter with source code is empty!\n";
        }

        return $result;
    }

}
?>