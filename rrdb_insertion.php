<?php
// RRDB aktualisieren
$temp01 = 23.44;
$start =strtotime("01.08.2016 13:00");
echo $temp01."<br>";
$rrdFile = "/var/www/html/rrd/temperatur.rrd";
echo $rrdFile."<br>";
$updater = new RRDUpdater($rrdFile);
for($i = 0;$i<55;$i++) {
  $updater->update(array("temperatur" => $temp+$i*0.3, $start+$i*60));
}

// echo "<pre>".print_r($updater,TRUE)."<pre>";
// if ($updater->update(array("temperatur" => $temp01))) {
//   echo "gespeichert<br>";
// } else {
//   echo "Fehler";
// }
?>