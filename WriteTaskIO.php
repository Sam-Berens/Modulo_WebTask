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

// Unpack all the inputs:
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);

$FieldSize = $Input['FieldSize'];
$FieldSize = mysqli_real_escape_string($Conn,$FieldSize);

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

// Generate DateTime_Write:
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

// Generate the AttemptId:
$AttemptId = $SubjectId.'_'.sprintf('%03d',$SessionId).'_'.sprintf('%03d',$TrialId).'_'.sprintf('%01d',$AttemptNum).'_'.$DateTime_Write;
$AttemptId = md5($AttemptId);

// Create the SQL request
$Sql = "CALL RecordTaskIO('$AttemptId','$SubjectId',$FieldSize,$SessionId,$TrialId,$PairId,'$TrialType',$OppId,$FieldIdx_A,$FieldIdx_B,$FieldIdx_C,$AttemptNum,$FieldIdx_R,$Correct,$RT,'$DateTime_Write')";

// Run the query:
if (!($Conn->query($Sql))) {
    $Conn->close();
    die('Query Sql failed to execute successfully;');
}

$Conn->close();
?>