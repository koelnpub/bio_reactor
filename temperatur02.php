<?php
$min = 24.0;
$max = 26.0;
$temp01 = exec('cat /sys/bus/w1/devices/28-021562a3a0ff/w1_slave |grep t=');
$temp01 = explode('t=',$temp01);
$temp01 = $temp01[1] / 1000;
if ($temp01 < $min ) {
  $status = 'gelb';
  $a_status = 'gelb';
} elseif ($temp01 > $max ) {
  $status = 'rot';
  $a_status = 'rot';
} else {
  $status = 'grün';
  $a_status = 'grün';
}
$temp01 = number_format($temp01,1,",",".")."°C";
echo date("H:i:s")." Start-Temperatur ".$temp01." Status: " .$status."<br>";

exec('gpio write 0 1');
exec('gpio write 2 1');
exec('gpio write 3 1');
exec('gpio mode 0 out');
exec('gpio mode 2 out');
exec('gpio mode 3 out');

for ($i=0;$i<50;$i++) {
  $temp01 = exec('cat /sys/bus/w1/devices/28-021562a3a0ff/w1_slave |grep t=');
  $temp01 = explode('t=',$temp01);
  $temp01 = $temp01[1] / 1000;
  if ($temp01 < $min ) {
    exec('gpio write 0 0');
    exec('gpio write 2 1');
    exec('gpio write 3 1');
    $status = 'gelb';
  } elseif ($temp01 > $max ) {
    exec('gpio write 0 1');
    exec('gpio write 2 1');
    exec('gpio write 3 0');
    $status = 'rot';
  } else {
    exec('gpio write 0 1');
    exec('gpio write 2 0');
    exec('gpio write 3 1');
    $status = 'grün';
  }
  if ($status != $a_status) {
    $temp01 = number_format($temp01,1,",",".")."°C";
    echo date("H:i:s").". aktuelle Temperatur ".$temp01." Status: " .$status."<br>";
    $a_status = $status;
  }
}
$temp01 = number_format($temp01,1,",",".")."°C";
echo date("H:i:s").". Programmende Temperatur ".$temp01." Status: " .$status."<br>";

?>


