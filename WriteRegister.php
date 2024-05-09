<?php
header('Content-Type: application/json');

// Connect to the database:
$Servername = "localhost";
$Username = "sophie";
$Password = "dragonbrain";
$Dbname = "b01_DataStore";
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

$Input = json_decode(file_get_contents('php://input'), true);
if (!$Input) {
	$Input = $_POST; // Only used when testing via MATLAB's webwrite function.
} 

// Unpack all the inputs:
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);

$Large2Small = $Input['Large2Small'];
$Large2Small = mysqli_real_escape_string($Conn,$Large2Small);

$FieldPerms = $Input['FieldPerms'];
$FieldPerms = mysqli_real_escape_string($Conn,$FieldPerms);

$TaskSets = $Input['TaskSets'];
$TaskSets = mysqli_real_escape_string($Conn,$TaskSets);

// Create the SQL request
$Sql = "CALL RecordRegister('$SubjectId',$Large2Small,'$FieldPerms','$TaskSets')";

// Run the query:
if (!($Conn->query($Sql))) {
    $Conn->close();
    die('Query Sql failed to execute successfully;');
}

$Conn->close();
?>