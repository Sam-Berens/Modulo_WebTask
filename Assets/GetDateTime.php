<?php
header('Content-Type: application/json');
$Result = array();
$Now = new DateTime('now', new DateTimeZone('Europe/London'));
$Result['DateTime'] = $Now -> format("Ymd_His");
echo json_encode($Result);
?>