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
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// Get the current Phase and FieldSize for this subject
$Sql1 = "SELECT * FROM Register WHERE SubjectId='$SubjectId';";
$Result = mysqli_query($Conn, $Sql1);
if ($Result === false) {
	$Conn->close();
	die('Query Sql1 failed to execute successfully;');
} else {
	$Result = mysqli_fetch_assoc($Result);

	$Phase = intval($Result['Phase']);
	$Large2Small = intval($Result['Large2Small']);
	$Large = ($Phase xor $Large2Small);
	if (!$Large) {
		$FieldSize = 5;
	} else {
		$FieldSize = 7;
	}
}

// Get the MinSessionId
$Sql2 = "SELECT MIN(SessionId) FROM TaskIO WHERE SubjectId='$SubjectId' AND Phase=$Phase;";
$Result = mysqli_query($Conn, $Sql2);
if ($Result === false) {
	$Conn->close();
	die('Query Sql2 failed to execute successfully;');
} else {
	$MinSessionId = mysqli_fetch_assoc($Result);
	$IsSet = isset($MinSessionId['MIN(SessionId)']);
	if (!$IsSet) {
		$MinSessionId = 0;
	} else {
		$MinSessionId = intval($MinSessionId['MIN(SessionId)']);
	}
}

// Get the MaxSessionId
$Sql3 = "SELECT MAX(SessionId) FROM TaskIO WHERE SubjectId='$SubjectId' AND Phase=$Phase;";
$Result = mysqli_query($Conn, $Sql3);
if ($Result === false) {
	$Conn->close();
	die('Query Sql3 failed to execute successfully;');
} else {
	$MaxSessionId = mysqli_fetch_assoc($Result);
	$IsSet = isset($MaxSessionId['MAX(SessionId)']);
	if (!$IsSet) {
		$MaxSessionId = 0;
	} else {
		$MaxSessionId = intval($MaxSessionId['MAX(SessionId)']);
	}
}

// Create an array of Sessions (LearnStats, Accuracy*) for the current experimental phase;
$LearnStats = array();
$NumTrials = array();
$Accuracy0 = array();
$Accuracy1 = array();
$Accuracy2 = array();

// Loop through each session (indexed by $iS)...
// ... adding an array of trial objects to each element of LearnStats;
for ($iS = $MinSessionId; $iS <= $MaxSessionId; $iS++) {

	// Run a query to select out all Sup attempts for the iS'th Session
	$Sql4 = "SELECT * FROM TaskIO WHERE SubjectId='$SubjectId' AND Phase=$Phase AND SessionId=$iS;"; /////////////////////////////////////////
	$Result = mysqli_query($Conn, $Sql4);

	// Create a new trial array for this (valid) session
	$Trials = array();

	// Loop through all the attempts in this session
	//$iT = -1; // Trial counter /////////////////////////////////////////////////////////////////////////////
	while ($Attempt = mysqli_fetch_assoc($Result)) {
		//$iT++; /////////////////////////////////////////////////////////////////////////////////////////////////////

		// Extract the trial details
		$TrialId = intval($Attempt['TrialId']); //////////////////////////////////////////////////////////////////////////
		$AttemptNum = intval($Attempt['AttemptNum']);
		$C = intval($Attempt['FieldIdx_C']);
		$R = intval($Attempt['FieldIdx_R']);

		// Calculate the angular error and the real projection
		$Theta = (($R - $C) % $FieldSize) * 2 * pi() / $FieldSize;
		$Real = cos($Theta);

		// If this is the first attempt, create a new trial object ...
		// ... and push it onto the Trials array
		if ($AttemptNum === 0) {
			$TrialObject = array(
				'FieldIdx_C' => $C,
				'FieldIdx_R' => array($R),
				'Accuracy' => array($Real)
			);
			array_push($Trials, $TrialObject);

			// Else, if we are dealing with a different attempt number...
			// ... adjust the last trail object in Trials
		} elseif ($AttemptNum < 3) {
			array_push($Trials[$TrialId]['FieldIdx_R'], $R); /////////////////////////////////////////////////////////////
			array_push($Trials[$TrialId]['Accuracy'], $Real); /////////////////////////////////////////////////////////
		}
	}

	// Pad out the Accuracy stats so that they always have elements
	for ($iT = 0; $iT < sizeof($Trials); $iT++) {
		$Trials[$iT]['Accuracy'] = array_pad($Trials[$iT]['Accuracy'], 3, 1);
	}

	// Push all the trails from this session onto LearnStats
	array_push($LearnStats, $Trials);

	// Record the number of trials in this session
	$nT = sizeof($Trials);
	array_push($NumTrials, $nT);

	// Compute the mean accuracy for the first attempt
	array_push(
		$Accuracy0,
		array_reduce($Trials, function ($x, $y) {
			return $x + ($y['Accuracy'][0]); })
	);
	$Accuracy0[$iS] = $Accuracy0[$iS] / $nT;

	// Compute the mean accuracy for the second attempt
	array_push(
		$Accuracy1,
		array_reduce($Trials, function ($x, $y) {
			return $x + ($y['Accuracy'][1]); })
	);
	$Accuracy1[$iS] = $Accuracy1[$iS] / $nT;

	// Compute the mean accuracy for the third attempt
	array_push(
		$Accuracy2,
		array_reduce($Trials, function ($x, $y) {
			return $x + ($y['Accuracy'][2]); })
	);
	$Accuracy2[$iS] = $Accuracy2[$iS] / $nT;

}

// Filter the Accuracy statistics by the number of trails in each session
// https://www.w3schools.com/php/func_array_filter.asp
//////////////////////////////////////////////////////////////////////////////////////////////////////
// Array to be filtered
$arrayToFilter = ["apple", "banana", "cherry", "date", "elderberry"];

// Array containing allowed values
$allowedValues = ["banana", "date", "fig", "grape"];

// Use array_filter to filter based on allowedValues
$filteredArray = array_filter($arrayToFilter, function($value) use ($allowedValues) {
    return in_array($value, $allowedValues);
});

// Reset array keys
$filteredArray = array_values($filteredArray);

// Print the filtered array
print_r($filteredArray);
/////////////////////////////////////////////////////////////////////////////////////////////////////////

// Package up data to return
$DataToSend = array();
$DataToSend['Accuracy0'] = $Accuracy0;
$DataToSend['Accuracy1'] = $Accuracy1;
$DataToSend['Accuracy2'] = $Accuracy2;

echo (json_encode($DataToSend));
$Conn->close();
?>