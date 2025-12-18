<?php
// Edit these values to match your MySQL setup
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'pdms';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if(!$conn){
    die("Database Connection Failed: ".mysqli_connect_error());
}
?>