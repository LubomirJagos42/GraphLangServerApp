<?php
class ModelDirectory
{
    private $db_conn;

    function __construct($db_conn){
        $this->db_conn = $db_conn;
    }

    function getIdeHtmlIncludeDirPrefix($version = ""){
        if ($version != "") {
            return "GraphLang/$version/GraphLang IDE";
        }else{
            return "GraphLang/default/GraphLang IDE";
        }
    }

    function getShapeDesignerHtmlIncludeDirPrefix($version = ""){
        if ($version != "") {
            return "GraphLang/$version/GraphLang_ShapeDesigner";
        }else{
            return "GraphLang/default/GraphLang_ShapeDesigner IDE";
        }
    }

    function getEnvironmentRootDir($version = ""){
        if ($version != "") {
            return "GraphLang/$version";
        }else{
            return "GraphLang/default";
        }
    }

    /**
     * @param $src
     * @param $dst
     * @return void
     * @description Recursive directory copy, copied from github: https://gist.github.com/gserrano/4c9648ec9eb293b9377b
     */
    function recursive_copy($src, $dst, $excludeDirPatterns = array(), $excludeFilesPatterns = array()) {
        $dir = opendir($src);
        @mkdir($dst);
        while(( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {

                    $excludeFlag = false;
                    foreach ($excludeDirPatterns as $pattern){
                        if (preg_match($pattern, $file)){
                            $excludeFlag = true;
                            break;
                        }
                    }
                    if ($excludeFlag == false){
                        $this->recursive_copy(
                            $src .'/'. $file,
                            $dst .'/'. $file,
                            $excludeDirPatterns,
                            $excludeFilesPatterns
                        );
                    }

                }
                else {

                    $excludeFlag = false;
                    foreach ($excludeFilesPatterns as $pattern){
                        if (preg_match($pattern, $file)){
                            $excludeFlag = true;
                            break;
                        }
                    }
                    if ($excludeFlag == false){
                        copy($src .'/'. $file,$dst .'/'. $file);
                    }

                }
            }
        }
        closedir($dir);
    }

    function recurseRmdir($dir) {
        $scandir = scandir($dir);
        $files = array_diff(
            $scandir ? $scandir : array(),
            array('.','..')
        );
        foreach ($files as $file) {
            (is_dir("$dir/$file") && !is_link("$dir/$file")) ? $this->recurseRmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function zipDir($source, $zipfilename){
        $dir = opendir($source);
        $result = ($dir === false ? false : true);

        if ($result !== false) {
            $rootPath = realpath($source);

            // Initialize archive object
            $zip = new ZipArchive();
            $zip->open($zipfilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            // Create recursive directory iterator
            /** @var SplFileInfo[] $files */
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }

            // Zip archive will be created only after closing object
            $zip->close();
        }
    }

}
?>