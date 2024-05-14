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

// Unpack the SubjectId:
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);

// This function must return three JavaScript variables:
//      1) SessionId (an integer)
//      2) Phase (an integer)
//      3) FieldSize (an integer)
//      4) ImgPerm (an array of ints)
//      5) TaskSet (an array of objects that can be set to CurrentQuestion in ObjectSpec.js)
$DataToSend = array();

// Get SessionId
$Sql = "SELECT MAX(SessionId) FROM TaskIO WHERE SubjectId='$SubjectId';";
$Result = mysqli_query($Conn,$Sql);
if ($Result === false) {
	$Conn->close();
	die('Query Sql failed to execute successfully;');
} else {
	$LastSessionId = mysqli_fetch_assoc($Result);
	$LastSessionId = intval($LastSessionId['MAX(SessionId)']);
	$DataToSend['SessionId']  = $LastSessionId + 1;
}

// Get Phase, Large2Small, ImgPerms, and TaskSets from the Register table
$Sql = "SELECT * FROM Register WHERE SubjectId='$SubjectId';";
$Result = mysqli_query($Conn,$Sql);
if ($Result === false) {
	$Conn->close();
	die('Query Sql failed to execute successfully;');
} else {
	$Result = mysqli_fetch_assoc($Result);

	// Determine which set to send
	$Phase = $Result['Phase'];
	$Large2Small = $Result['Large2Small'];
	$Large = $Phase xor $Large2Small;
	if (!$Large) {
		$FieldSize = 5;
	} else {
		$FieldSize = 7;
	}

	// Extract the relevant ImgPerm and TaskSet
	$ImgPerms = get_object_vars(json_decode($Result['ImgPerms']));
	$ImgPerm = $ImgPerms[sprintf('S%02d',$FieldSize)];
	$TaskSets = get_object_vars(json_decode($Result['TaskSets']));
	$TaskSet = $TaskSets[sprintf('S%02d',$FieldSize)];

	// Package the result
	$DataToSend['Phase'] = $Phase;
	$DataToSend['FieldSize'] = $FieldSize;
	$DataToSend['ImgPerm'] = $ImgPerm;
	$DataToSend['TaskSet'] = $TaskSet;
}


echo(json_encode($DataToSend));
$Conn->close();
?>