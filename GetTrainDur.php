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
$Sql1 = "SELECT SUM(RT)/3600000 FROM `TaskIO` WHERE SubjectId='$SubjectId' AND TrialType='Sup' AND Correct=1;";
$Sql2 = "SELECT COUNT(AttemptId) FROM `TaskIO` WHERE SubjectId='$SubjectId' AND TrialType='Sup' AND Correct=1;";
$Result1 = mysqli_query($Conn,$Sql1);
$ThinkingTime = mysqli_fetch_assoc($Result1);
$Result2 = mysqli_query($Conn,$Sql2);
$SupCount = mysqli_fetch_assoc($Result2);
$TrainDur = $ThinkingTime['SUM(RT)/3600000'] + $SupCount['COUNT(AttemptId)']*(5/3600);

// Close connection to database:
$Conn->close();
echo($TrainDur);