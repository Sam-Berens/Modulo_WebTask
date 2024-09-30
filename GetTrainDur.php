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

//Get the train time:
$Sql = "SELECT SUM(RT)/3600000 FROM `TaskIO` WHERE SubjectId='$SubjectId' AND Correct=1;";
$Result = mysqli_query($Conn,$Sql);
$TrainDur = mysqli_fetch_assoc($Result);
$TrainDur = $TrainDur['SUM(RT)/3600000'];

// Close connection to database:
$Conn->close();
echo($TrainDur);