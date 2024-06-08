<?php
$DB_SERVER = "localhost";
$DB_USER = "root";
$DB_PORT = "3306";
$DB_PASSWORD = "";
$DB_NAME = "graphlang_local_develop";

$db_conn = new mysqli($DB_SERVER, $DB_USER, $DB_PASSWORD);
if ($db_conn->connect_error) {
  die("Connection failed: " . $db_conn->connect_error);
}
$db_conn->select_db($DB_NAME);

?>
