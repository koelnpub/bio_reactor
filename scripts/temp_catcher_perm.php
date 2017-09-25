#!/usr/bin/php
<?php
$run = true;
$idle_message_written = false;
$stopp_message_written = false;
while ( $run ) {
  // Schleifen-Startzeit speichern
  $start = date ( "d.m.Y H:i:s" );
  
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
  
  if ($on_state == "RUN" or $on_state == "IDLE") {
    // letztes Datentelegramm in ein Array einlesen
    $daten = file ( "/var/www/html/t_msg.txt" );
    $werte = explode ( "|", $daten [0] );
    
    // 2. Schleife vorbereiten
    $loop = 0;
    $writes = 0;
    $loops = 60;
    
    // Datenbank Objekt erzeugen
    $rrdFile = "/var/www/html/rrd/temperatur.rrd";
    $updater = new RRDUpdater ( $rrdFile );
    
    // 2. Schleife starten
    for($i = 0; $i < $loops; $i ++) {
      // Startzeit der 2. Schleife merken
      $st = microtime ( TRUE );
      
      // aktuelle Temperatur einlesen
      $temp01 = exec ( 'cat /sys/bus/w1/devices/' . $rrdb . '/w1_slave |grep t=' );
      $temp01 = explode ( 't=', $temp01 );
      $temp01 = $temp01 [1] / 1000;
      
      if ($on_state == "RUN") {
        $idle_message_written = false;
        $stopp_message_written = false;
        // Temperatur auswerten (min/max Vergleich)
        if ($temp01 < $min) {
          exec ( 'gpio write 0 0' );
          exec ( 'gpio write 2 1' );
          exec ( 'gpio write 3 1' );
          $status = 'amber';
        } elseif ($temp01 > $max) {
          exec ( 'gpio write 0 1' );
          exec ( 'gpio write 2 1' );
          exec ( 'gpio write 3 0' );
          $status = 'red';
        } else {
          exec ( 'gpio write 0 1' );
          exec ( 'gpio write 2 0' );
          exec ( 'gpio write 3 1' );
          $status = 'green';
        }
        // Telgramm zusammenstellen
        $werte [0] = $name;
        $werte [1] = $temp01;
        $werte [2] = $status;
        $werte [3] = date ( "d.m.Y H:i:s" );
        $werte [4] = $min;
        $werte [5] = $max;
        
        // Telegramm speichern
        $handle = fopen ( "/var/www/html/t_msg.txt", "w" );
        if ($handle) {
          fwrite ( $handle, join ( "|", $werte ) );
          fclose ( $handle );
          $writes ++; // Anzahl der Schreibvorgänge aktualisieren
        }
        $loop ++; // Anzahl der Schleifendurchläufe aktualisieren
        if ($loop % 2 === 0) {
          $updater->update ( array (
              "temperatur" => $temp01 
          ), time () );
        }
        $cycle = microtime ( TRUE ) - $st; // Laufzeit eines Durchlaufs festhalten (Mikrosekunden - Auflösung 1/1000000)
      } else {
        if (! $idle_message_written) {
          exec ( 'gpio write 0 1' );
          exec ( 'gpio write 2 1' );
          exec ( 'gpio write 3 1' );
          $idle_message_written = true;
          // IDLE - Telgramm zusammenstellen
          $werte [0] = $name;
          $werte [1] = $temp01;
          $werte [2] = "white";
          $werte [3] = date ( "d.m.Y H:i:s" );
          $werte [4] = $min;
          $werte [5] = $max;
          
          // Telegramm speichern
          $handle = fopen ( "/var/www/html/t_msg.txt", "w" );
          if ($handle) {
            fwrite ( $handle, join ( "|", $werte ) );
            fclose ( $handle );
          }
          // IDLE - Meldung ausgeben
          echo "Anlage durch Leitwarte temporär gestoppt " . $werte [3] . "\n";
        }
      }
    }
    
    // Minuten - Meldung ausgeben
    echo "von " . $start . " bis " . date ( "d.m.Y H:i:s" ) . " - Durchläufe: " . $loop . " - Schreiben: " . $writes . " - Zyklus: " . $cycle . "\n";
  } else {
    // Stopp - Telgramm zusammenstellen
    $idle_message_written = false;
    if (! $stopp_message_written) {
      exec ( 'gpio write 0 1' );
      exec ( 'gpio write 2 1' );
      exec ( 'gpio write 3 1' );
      $stopp_message_written = true;
      $werte [0] = $name;
      $werte [1] = "0.00";
      $werte [2] = "black";
      $werte [3] = date ( "d.m.Y H:i:s" );
      $werte [4] = "0.0";
      $werte [5] = "0.0";
      
      // Telegramm speichern
      $handle = fopen ( "/var/www/html/t_msg.txt", "w" );
      if ($handle) {
        fwrite ( $handle, join ( "|", $werte ) );
        fclose ( $handle );
      }
      // Stopp - Meldung ausgeben
      echo "Anlage durch Leitwarte herunter gefahren " . $werte [3] . "\n";
    }
  }
}
?>


