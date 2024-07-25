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

// This function must return four JavaScript variables:
//      1) SessionId (an integer)
//      2) FieldSize (an integer)
//      3) ImgPerm (an array of ints)
//      4) TaskSet (an array of objects that can be set to CurrentQuestion in ObjectSpec.js)
$DataToSend = array();

// Get SessionId
$Sql = "SELECT MAX(SessionId) FROM TaskIO WHERE SubjectId='$SubjectId';";
$Result = mysqli_query($Conn,$Sql);
if ($Result === false) {
	$Conn->close();
	die('Query Sql failed to execute successfully;');
} else {
	$LastSessionId = mysqli_fetch_assoc($Result);
	$IsSet = isset($LastSessionId['MAX(SessionId)']);
	if (!$IsSet) {
		$LastSessionId = -1;
	} else {
		$LastSessionId = intval($LastSessionId['MAX(SessionId)']);
	}
	$DataToSend['SessionId']  = $LastSessionId + 1;
}

// Get ImgPerm and TaskSet from the Register table
$Sql = "SELECT * FROM Register WHERE SubjectId='$SubjectId';";
$Result = mysqli_query($Conn,$Sql);
if ($Result === false) {
	$Conn->close();
	die('Query Sql failed to execute successfully;');
} else {
	$Result = mysqli_fetch_assoc($Result);

	// Set the FieldSize
	$FieldSize = 6;

	// Extract the relevant ImgPerm and TaskSet
	$ImgPerm = json_decode($Result['ImgPerm']);
	$TaskSet = json_decode($Result['TaskSet']);

	// Package the result
	$DataToSend['FieldSize'] = $FieldSize;
	$DataToSend['ImgPerm'] = $ImgPerm;
	$DataToSend['TaskSet'] = $TaskSet;
}


echo(json_encode($DataToSend));
$Conn->close();
?>