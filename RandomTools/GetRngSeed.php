<?php
$Random = mt_rand() / mt_getrandmax();
$Seed = round($Random*(pow(2,64)-1));
echo(json_encode($Seed));
?>