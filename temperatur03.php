#!/usr/bin/php
<?php
$start = date("d.m.Y H:i:s");
$min = 24.0;
$max = 26.0;
$temp01 = exec('cat /sys/bus/w1/devices/28-021562a3a0ff/w1_slave |grep t=');
$temp01 = explode('t=',$temp01);
$temp01 = $temp01[1] / 1000;
if ($temp01 < $min ) {
  $status = 'gelb';
} elseif ($temp01 > $max ) {
  $status = 'rot';
} else {
  $status = 'grün';
}

exec('gpio write 0 1');
exec('gpio write 2 1');
exec('gpio write 3 1');
exec('gpio mode 0 out');
exec('gpio mode 2 out');
exec('gpio mode 3 out');

$daten = file("/var/www/html/t_msg.txt");
$werte = explode("|",$daten[0]);
$loop = 0;
$writes = 0;
for ($i=0;$i<59;$i++) {
  $st = time();
  $temp01 = exec('cat /sys/bus/w1/devices/28-021562a3a0ff/w1_slave |grep t=');
  $temp01 = explode('t=',$temp01);
  $temp01 = $temp01[1] / 1000;
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
  $werte[1] = $temp01;
  $werte[2] = $status;
  $werte[3] = date("d.m.Y H:i:s");
  $handle = fopen("/var/www/html/t_msg.txt", "w");
  if ($handle) {
    fwrite($handle, join("|",$werte));
    fclose($handle);
    $writes++;
  }
  $loop++;
  $cycle= time() - $st;
}
echo "von ".$start." bis ".date("d.m.Y H:i:s")." - Durchläufe: ".$loop." - Schreiben: ".$writes." - Zyklus: ".$cycle;
?>


