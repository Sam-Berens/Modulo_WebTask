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
$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);

$SessionId = $Input['SessionId'];
$SessionId = mysqli_real_escape_string($Conn,$SessionId);

$TrialId = $Input['TrialId'];
$TrialId = mysqli_real_escape_string($Conn,$TrialId);

$PairId = $Input['PairId'];
$PairId = mysqli_real_escape_string($Conn,$PairId);

$TrialType = $Input['TrialType'];
$TrialType = mysqli_real_escape_string($Conn,$TrialType);

$OppId = $Input['OppId'];
$OppId = mysqli_real_escape_string($Conn,$OppId);

$FieldIdx_A = $Input['FieldIdx_A'];
$FieldIdx_A = mysqli_real_escape_string($Conn,$FieldIdx_A);

$FieldIdx_B = $Input['FieldIdx_B'];
$FieldIdx_B = mysqli_real_escape_string($Conn,$FieldIdx_B);

$FieldIdx_C = $Input['FieldIdx_C'];
$FieldIdx_C = mysqli_real_escape_string($Conn,$FieldIdx_C);

$AttemptNum = $Input['AttemptNum'];
$AttemptNum = mysqli_real_escape_string($Conn,$AttemptNum);

$FieldIdx_R = $Input['FieldIdx_R'];
$FieldIdx_R = mysqli_real_escape_string($Conn,$FieldIdx_R);

$Correct = $Input['Correct'];
$Correct = mysqli_real_escape_string($Conn,$Correct);

$RT = $Input['RT'];
$RT = mysqli_real_escape_string($Conn,$RT);

// Generate the AttemptId:
$AttemptId = $SubjectId.'_'.sprintf('%03d',$SessionId).'_'.sprintf('%03d',$TrialId).'_'.sprintf('%01d',$AttemptNum);

// Generate DateTime_Write:
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

// Create the SQL request
$Sql = "CALL RecordTaskIO('$AttemptId','$SubjectId',$SessionId,$TrialId,$PairId,'$TrialType',$OppId,$FieldIdx_A,$FieldIdx_B,$FieldIdx_C,$AttemptNum,$FieldIdx_R,$Correct,$RT,'$DateTime_Write')";
//error_log($Sql);

// Run the query:
if (!($Conn->query($Sql))) {
    $Conn->close();
    die('Query Sql failed to execute successfully;');
}

$Conn->close();
?>