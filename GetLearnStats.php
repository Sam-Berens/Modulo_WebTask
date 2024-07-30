<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Connect to the database:
$Servername = "localhost";
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

// Set the FieldSize
$FieldSize = 6;

// Get the MinSessionId
$Sql2 = "SELECT MIN(SessionId) FROM TaskIO WHERE SubjectId='$SubjectId';";
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
$Sql3 = "SELECT MAX(SessionId) FROM TaskIO WHERE SubjectId='$SubjectId';";
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

// Create an array of Sessions (Sessions, Accuracy*);
$Sessions = array();
$NumTrials = array();
$Accuracy0 = array();
$Accuracy1 = array();
$Accuracy2 = array();

// Loop through each session (indexed by $iS)...
// ... adding an array of trial objects to each element of Sessions;
for ($iS = $MinSessionId; $iS <= $MaxSessionId; $iS++) {

	// Run a query to select out all Sup attempts for the iS'th Session
	$Sql4 = "SELECT * FROM TaskIO WHERE 
		SubjectId='$SubjectId' AND 
		SessionId=$iS AND 
		TrialType='Sup'
		ORDER BY DateTime_Write ASC;";
	$Result = mysqli_query($Conn, $Sql4);

	// Create a new trial array for this (valid) session
	$Trials = array();

	// Loop through all the attempts in this session
	$iT = -1; // Trial counter
	while ($Attempt = mysqli_fetch_assoc($Result)) {

		// Extract the trial details
		$AttemptNum = intval($Attempt['AttemptNum']);
		$C = intval($Attempt['FieldIdx_C']);
		$R = intval($Attempt['FieldIdx_R']);

		// Calculate the angular error and the real projection
		$Theta = (($R - $C) % $FieldSize) * 2 * pi() / $FieldSize;
		$Real = cos($Theta);

		// If this is the first attempt, create a new trial object ...
		// ... and push it onto the Trials array
		if ($AttemptNum === 0) {
			$iT++;
			$TrialObject = array(
				'FieldIdx_C' => $C,
				'FieldIdx_R' => array($R),
				'Accuracy' => array($Real)
			);
			array_push($Trials, $TrialObject);

			// Else, if we are dealing with a different attempt number...
			// ... adjust the last trail object in Trials
		} elseif ($AttemptNum < 3) {
			array_push($Trials[$iT]['FieldIdx_R'], $R);
			array_push($Trials[$iT]['Accuracy'], $Real);
		}
	}

	// Pad out the Accuracy stats so that they always have 3 elements
	for ($iT = 0; $iT < sizeof($Trials); $iT++) {
		$Trials[$iT]['Accuracy'] = array_pad($Trials[$iT]['Accuracy'], 3, 1);
	}

	// Push all the trails from this session onto Sessions
	array_push($Sessions, $Trials);

	// Record the number of trials in this session
	$nT = sizeof($Trials);
	array_push($NumTrials, $nT);

	// Compute the mean accuracy for the first attempt
	array_push(
		$Accuracy0,
		array_reduce($Trials, function ($x, $y) {
			return $x + ($y['Accuracy'][0]);
		})
	);
	$Accuracy0[$iS] = $Accuracy0[$iS] / $nT;

	// Compute the mean accuracy for the second attempt
	array_push(
		$Accuracy1,
		array_reduce($Trials, function ($x, $y) {
			return $x + ($y['Accuracy'][1]);
		})
	);
	$Accuracy1[$iS] = $Accuracy1[$iS] / $nT;

	// Compute the mean accuracy for the third attempt
	array_push(
		$Accuracy2,
		array_reduce($Trials, function ($x, $y) {
			return $x + ($y['Accuracy'][2]);
		})
	);
	$Accuracy2[$iS] = $Accuracy2[$iS] / $nT;
}

// Filtering out sessions with less than 9 supervised trials
function FilterFunc($ii)
{
	return ($GLOBALS['NumTrials'][$ii] > 8);
}
$Accuracy0 = array_filter($Accuracy0, 'FilterFunc', ARRAY_FILTER_USE_KEY);
$Accuracy0 = array_values($Accuracy0);
$Accuracy1 = array_filter($Accuracy1, 'FilterFunc', ARRAY_FILTER_USE_KEY);
$Accuracy1 = array_values($Accuracy1);
$Accuracy2 = array_filter($Accuracy2, 'FilterFunc', ARRAY_FILTER_USE_KEY);
$Accuracy2 = array_values($Accuracy2);

// Transform accuracy values
function MapValues($x)
{
	$beta = 2;
	$y = (exp($x*$beta)-1) / (exp($beta)-1);
	//$z = max($y, 0);
	return $y;
}
$Accuracy0 = array_map('MapValues', $Accuracy0);
$Accuracy1 = array_map('MapValues', $Accuracy1);
$Accuracy2 = array_map('MapValues', $Accuracy2);

// Create a domain for all the accuracy values
$SessionN = range(0, sizeof($Accuracy0));

// Add a zero at beginning of each Accuracy array
array_unshift($Accuracy0, 0);
array_unshift($Accuracy1, 0);
array_unshift($Accuracy2, 0);

// Package up data to return
$DataToSend = array();
$DataToSend['SessionN'] = $SessionN;
$DataToSend['Accuracy0'] = $Accuracy0;
$DataToSend['Accuracy1'] = $Accuracy1;
$DataToSend['Accuracy2'] = $Accuracy2;

echo (json_encode($DataToSend));
$Conn->close();