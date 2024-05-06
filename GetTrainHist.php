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

// Get all the inputs:
$SubjectId = $Input['SubjectId'];
mysqli_real_escape_string($Conn,$SubjectId);

$Counts = array();
for ($iPair = 0; $iPair < 49; $iPair++) {
    // Create the SQL request:
    $Sql = "SELECT COUNT(PairId) FROM TaskIO WHERE SubjectId='$SubjectId' AND AttemptNum=0 AND PairId=$iPair;";
    $Result = mysqli_query($Conn,$Sql);
    if ($Result === false) {
        $Conn->close();
        die('Query Sql failed to execute successfully;');
    } else {
        $CC = mysqli_fetch_assoc($Result);
        $CC = intval($CC['COUNT(PairId)']);
        array_push($Counts,$CC);
    }
}

$Conn->close();
echo(json_encode($Counts));
?>