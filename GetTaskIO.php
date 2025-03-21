<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Connection failed: " . $Conn->connect_error);
}

// Run the SELECT query
$Sql = "SELECT * FROM TaskIO";
$Result = mysqli_query($Conn,$Sql);
$TaskIO = mysqli_fetch_all($Result,MYSQLI_ASSOC);

$Conn->close();
echo(json_encode($TaskIO));