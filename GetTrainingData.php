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
$Sup['SessionId'] = array();
$Sup['SessionN'] = array();
$Sup['TrialId'] = array();
$Sup['TrialN'] = array();
$Sup['PairId'] = array();
$Sup['c'] = array();
$Sup['r0'] = array();
$Sup['k0'] = array();
$Sup['r1'] = array();
$Sup['k1'] = array();
$Sup['r2'] = array();
$Sup['k2'] = array();

$Uns['SessionId'] = array();
$Uns['SessionN'] = array();
$Uns['TrialId'] = array();
$Uns['TrialN'] = array();
$Uns['PairId'] = array();
$Uns['c'] = array();
$Uns['r'] = array();
$Uns['k'] = array();

// Loop through each session (indexed by $iS)...
// ... adding an array of trial objects to each element of Sessions;
$iS = -1;
for ($iSId = $MinSessionId; $iSId <= $MaxSessionId; $iSId++) {
    $iS = $iS + 1;

    // Run a query to select out all Sup attempts for the iSId'th Session
    $Sql4 = "SELECT * FROM TaskIO WHERE 
		SubjectId='$SubjectId' AND 
		SessionId=$iSId AND 
        TrialType='Sup'
		ORDER BY DateTime_Write ASC;";
    $Result = mysqli_query($Conn, $Sql4);

    // Loop through all the attempts in this session
    $iT = -1; // Trial counter
    while ($Attempt = mysqli_fetch_assoc($Result)) {

        // Extract the trial details
        $AttemptNum = intval($Attempt['AttemptNum']);
        $TrialId = intval($Attempt['TrialId']);
        $PairId = intval($Attempt['PairId']);
        $c = intval($Attempt['FieldIdx_C']);
        $r = intval($Attempt['FieldIdx_R']);
        $Correct = intval($Attempt['Correct']);

        // If this is the first attempt...
        if ($AttemptNum === 0) {
            $iT = $iT + 1;
            array_push($Sup['SessionId'], $iSId);
            array_push($Sup['SessionN'], $iS);
            array_push($Sup['TrialId'], $TrialId);
            array_push($Sup['TrialN'], $iT);
            array_push($Sup['PairId'], $PairId);
            array_push($Sup['c'], $c);
            array_push($Sup['r0'], $r);
            array_push($Sup['k0'], $Correct);
            array_push($Sup['r1'], $c);
            array_push($Sup['k1'], 1);
            array_push($Sup['r2'], $c);
            array_push($Sup['k2'], 1);

        } elseif ($AttemptNum == 1) {
            $Sup['r1'][count($Sup['r1']) - 1] = $r;
            $Sup['k1'][count($Sup['k1']) - 1] = $Correct;
        } else if ($AttemptNum == 2) {
            $Sup['r2'][count($Sup['r2']) - 1] = $r;
            $Sup['k2'][count($Sup['k2']) - 1] = $Correct;
        }

    }

    // ----------------------------------------------------------------

    // Run a query to select out all Uns attempts for the iSId'th Session
    $Sql4 = "SELECT * FROM TaskIO WHERE 
    SubjectId='$SubjectId' AND 
    SessionId=$iSId AND 
    TrialType='Uns'
    ORDER BY DateTime_Write ASC;";
    $Result = mysqli_query($Conn, $Sql4);

    // Loop through all the attempts in this session
    $iT = -1; // Trial counter
    while ($Attempt = mysqli_fetch_assoc($Result)) {

        // Extract the trial details
        $AttemptNum = intval($Attempt['AttemptNum']);
        $TrialId = intval($Attempt['TrialId']);
        $PairId = intval($Attempt['PairId']);
        $c = intval($Attempt['FieldIdx_C']);
        $r = intval($Attempt['FieldIdx_R']);
        $Correct = intval($Attempt['Correct']);

        // If this is the first attempt...
        if ($AttemptNum === 0) {
            $iT = $iT + 1;
            array_push($Uns['SessionId'], $iSId);
            array_push($Uns['SessionN'], $iS);
            array_push($Uns['TrialId'], $TrialId);
            array_push($Uns['TrialN'], $iT);
            array_push($Uns['PairId'], $PairId);
            array_push($Uns['c'], $c);
            array_push($Uns['r'], $r);
            array_push($Uns['k'], $Correct);
        }

    }
}

// Package up data to return
$DataToSend = array();
$DataToSend['Sup'] = $Sup;
$DataToSend['Uns'] = $Uns;
echo(json_encode($DataToSend));
$Conn->close();