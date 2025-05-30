<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Connection failed: " . $Conn->connect_error);
}

$Input = json_decode(file_get_contents('php://input'), true);
if (!$Input) {
	$Input = $_POST; // Only used when testing via MATLAB's webwrite function.
}

// Unpack the SubjectId:
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// Run the SELECT query
$Sql = "SELECT * FROM TaskIO WHERE SubjectId='$SubjectId'";
$Result = mysqli_query($Conn,$Sql);
$TaskIO = mysqli_fetch_all($Result,MYSQLI_ASSOC);

$Conn->close();
echo(json_encode($TaskIO));