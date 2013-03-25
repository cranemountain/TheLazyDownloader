<?php
$host="localhost";
$user="db_username";
$password="db_password";
$database="rss_download";
$connection = mysql_connect($host, $user, $password) or trigger_error(mysql_error(),E_USER_ERROR);
$select_db = mysql_select_db($database);

$mysqli = new mysqli("localhost", "db_username", "db_password", "rss_download");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
?>
