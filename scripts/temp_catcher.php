#!/usr/bin/php
<?php
// Startzeit speichern
$start = date("d.m.Y H:i:s");

// Konfiguration einlesen
$known_dbs = file ( "/var/www/html/scripts/rrdb.txt" );
foreach ( $known_dbs as $k => $db ) {
  $known_dbs [$k] = explode ( ":", $db );
  $known_dbs [$k] [1] = explode ( "|", $known_dbs [$k] [1] );
}

//  Hier interessiert uns nur der 1. Sensor (falls mehrere vorhanden)
$name = $known_dbs [0] [1] [1];
$min = $known_dbs [0] [1] [3];
$max = $known_dbs [0] [1] [4];
$rrdb = $known_dbs [0] [0];


// letztes Datentelegramm in ein Array einlesen
$daten = file("/var/www/html/t_msg.txt");
$werte = explode("|",$daten[0]);

// Schleife vorbereiten
$loop = 0;
$writes = 0;
$loops = 60;

// Datenbank Objekt erzeugen
$rrdFile = "/var/www/html/rrd/temperatur3.rrd";
$updater = new RRDUpdater($rrdFile);

// Schleife starten
for ($i=0;$i<$loops;$i++) {
  // Startzeit der Schleife merken
  $st = microtime(TRUE); 
  
  // aktuelle Temperatur einlesen
  $temp01 = exec('cat /sys/bus/w1/devices/28-021562a3a0ff/w1_slave |grep t='); 
  $temp01 = explode('t=',$temp01);
  $temp01 = $temp01[1] / 1000;
  
  // Temperatur auswerten (min/max Vergleich)
  if ($temp01 < $min ) {
    exec('gpio write 0 0');
    exec('gpio write 2 1');
    exec('gpio write 3 1');
    $status = 'amber';
  } elseif ($temp01 > $max ) {
    exec('gpio write 0 1');
    exec('gpio write 2 1');
    exec('gpio write 3 0');
    $status = 'red';
  } else {
    exec('gpio write 0 1');
    exec('gpio write 2 0');
    exec('gpio write 3 1');
    $status = 'green';
  }
  
  //  Telgramm zusammenstellen
  $werte[0] = $name;
  $werte[1] = $temp01;
  $werte[2] = $status;
  $werte[3] = date("d.m.Y H:i:s");
  $werte[4] = $min;
  $werte[5] = $max;
  
  // Telegramm speichern
  $handle = fopen("/var/www/html/t_msg.txt", "w");
  if ($handle) {
    fwrite($handle, join("|",$werte));
    fclose($handle);
    $writes++;  // Anzahl der Schreibvorgänge aktualisieren
  }
  $loop++;  // Anzahl der Schleifendurchläufe aktualisieren
  if ($loop % 2 === 0 ) {
    $updater->update(array("temperatur" => $temp01), time());
  }
  $cycle= microtime(TRUE) - $st; // Laufzeit eines Durchlaufs festhalten (Mikrosekunden - Auflösung 1/1000000)
}

// $cmd = "sudo /usr/bin/rrdtool update ".$rrdFile." N:$temp01\n";
// exec($cmd);

// Fertig - Meldung ausgeben
echo "von ".$start." bis ".date("d.m.Y H:i:s")." - Durchläufe: ".$loop." - Schreiben: ".$writes." - Zyklus: ".$cycle;
?>


