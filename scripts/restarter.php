#!/usr/bin/php
<?php
// Konfiguration einlesen
$known_dbs = file ( "/var/www/html/scripts/rrdb.txt" );
foreach ( $known_dbs as $k => $db ) {
  $known_dbs [$k] = explode ( ":", $db );
  $known_dbs [$k] [1] = explode ( "|", $known_dbs [$k] [1] );
}

// Hier interessiert uns allerdings nur der 1. Sensor (falls mehrere vorhanden)
$name = $known_dbs [0] [1] [1];
$min = $known_dbs [0] [1] [3];
$max = $known_dbs [0] [1] [4];
$on_state = $known_dbs [0] [1] [5];
$rrdb = $known_dbs [0] [0];

if ($on_state == "RESTART") {
  
}

?>


