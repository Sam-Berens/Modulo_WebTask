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

// Get all the inputs:
$SubjectId = $Input['SubjectId'];
mysqli_real_escape_string($Conn,$SubjectId);

// Get FieldSize from the Register table
$Sql1 = "SELECT * FROM Register WHERE SubjectId='$SubjectId';";
$Result = mysqli_query($Conn,$Sql1);
if ($Result === false) {
	$Conn->close();
	die('Query Sql1 failed to execute successfully;');
} else {
	$Result = mysqli_fetch_assoc($Result);

	// Determine which set to send
	$Phase = intval($Result['Phase']);
	$Large2Small = intval($Result['Large2Small']);
	$Large = ($Phase xor $Large2Small);
	if (!$Large) {
		$FieldSize = 5;
	} else {
		$FieldSize = 7;
	}
}

// Get the count for each pair
$Counts = array();
for ($iPair = 0; $iPair < pow($FieldSize,2); $iPair++) {
    // Create the SQL request:
    $Sql2 = "SELECT COUNT(PairId) FROM TaskIO WHERE SubjectId='$SubjectId' AND AttemptNum=0 AND PairId=$iPair;";
    $Result = mysqli_query($Conn,$Sql2);
    if ($Result === false) {
        $Conn->close();
        die('Query Sql2 failed to execute successfully;');
    } else {
        $CC = mysqli_fetch_assoc($Result);
        $CC = intval($CC['COUNT(PairId)']);
        array_push($Counts,$CC);
    }
}

$Conn->close();
echo(json_encode($Counts));
?>