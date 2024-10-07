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


//Get the largest session Id out of all participants:
$Sql = "SELECT MAX(SessionId) FROM `TaskIO`;";
$Result = mysqli_query($Conn,$Sql);
$MaxSess = mysqli_fetch_assoc($Result);
$MaxSess = intval($MaxSess['MAX(SessionId)']);

// Close connection to database:
$Conn->close();
echo(json_encode($MaxSess));