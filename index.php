<!DOCTYPE html>
<html lang="en">
<head>
<title>Anlagensteuerung mit dem Raspberry Pi</title>
<link href="styles/bootstrap.css" rel="stylesheet" />
<link href="styles/bootstrap-toggle.min.css" rel="stylesheet" />
<link href="styles/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript"> 
    var ajxFile = "ajax_interface.php";
    var autorefresh = "off";
    var counter = 0;
    var updateView = function ()  {
      if (autorefresh == "on") {
      	  send(200);
      } 
  	  myTimer = window.setTimeout(updateView, 5000);
  	}
    var myTimer = window.setTimeout(updateView, 5000);
  </script>
</head>
<body>

<?php
// Laden weiterer benötigter Programme *******************************************
require_once 'functions_inc.php';

// gpio Ports initialisieren *****************************************************
exec('gpio write 0 1');
exec('gpio write 2 1');
exec('gpio write 3 1');
exec('gpio mode 0 out');
exec('gpio mode 2 out');
exec('gpio mode 3 out');

// Variablen initialisieren ******************************************************
$pieces = $pieces1 = $pieces2 = $pieces3 = $pieces4 = array ();
$temps = array ();
$debug = FALSE;
$min = 24.0;
$max = 26.0;

// Lesen und bearbeiten der Konfigurationsdatei **********************************
$known_dbs = read_known_db_config();
foreach ( $known_dbs as $k => $rrd ) {
  $temp01 = exec ( 'cat /sys/bus/w1/devices/' . $rrd [0] . '/w1_slave |grep t=' );
  $temp01 = explode ( 't=', $temp01 );
  $temp01 = $temp01 [1] / 1000;
  if ($temp01 < $min ) {
    exec('gpio write 0 0');
    exec('gpio write 2 1');
    exec('gpio write 3 1');
    $status = 'amber';
    $a_status = 'amber';
    $pc="propeller2";
    $hc="heater";
  } elseif ($temp01 > $max ) {
    exec('gpio write 0 1');
    exec('gpio write 2 1');
    exec('gpio write 3 0');
    $status = 'red';
    $a_status = 'red';
    $pc="propeller";
    $hc="heater2";
  } else {
    exec('gpio write 0 1');
    exec('gpio write 2 0');
    exec('gpio write 3 1');
    $status = 'green';
    $a_status = 'green';
    $pc="propeller2";
    $hc="heater2";
  }
  $temp01 = number_format($temp01,1,".","");
  $temps [] = explode ( ".", $temp01 );
}
//  Ausgabe vorbereiten **********************************************************

$pieces [] =  '  <div class="row text-center">';
$pieces [] =  '    <div class="col-sm-3">';
$pieces [] =  '      <h3>Anlage</h3>';
$pieces [] =  '      <div class="des">';
$pieces [] =  '        <table class="table">';
$pieces [] =  '          <tr>';
$pieces [] =  '            <td>';
$pieces [] =  'Refresh';
$pieces [] =  '            </td>';
$pieces [] =  '            <td>';
$pieces [] =  '<input type="checkbox" id="switch_0" data-toggle="toggle" data-size="mini" onchange="send(100,0)" >';
$pieces [] =  '            </td>';
$pieces [] =  '          </tr>';
$pieces [] =  '          <tr>';
$pieces [] =  '            <td>';
$pieces [] =  'Temperatur Status';
$pieces [] =  '            </td>';
$pieces [] =  '            <td>';
$pieces [] =  '<div id="led_0" class="led_'.$status.'"></div>';
$pieces [] =  '            </td>';
$pieces [] =  '          </tr>';
$pieces [] =  '        </table>';
$pieces [] =  '        <div id="vbody">';
$pieces [] =  '          <div id="ventilator" class="'.$pc.'"></div>';
$pieces [] =  '        </div>';  
$pieces [] =  '        <div id="hbody">';
$pieces [] =  '          <div id="heater" class="'.$hc.'"></div>';
$pieces [] =  '        </div>';
$pieces [] =  '      </div>';
$pieces [] =  '    </div>';

$pieces1 [] = '    <div class="col-sm-3">';
$pieces1 [] = '      <h3>' . $rrd [1] [1] . '</h3>';
$pieces1 [] = '      <div class="de">';
$pieces1 [] = '        <div class="den">';
$pieces1 [] = '          <div class="dene">';
$pieces1 [] = '            <div class="denem">';

$pieces2 [] = '              <div id="v'.$k.'" class="deneme">';
$pieces2 [] = $temps [$k] [0] . "<span>" . $temps [$k] [1] . "</span>";
$pieces2 [] = '              </div>';

$pieces3 [] = '            </div>';
$pieces3 [] = '          </div>';
$pieces3 [] = '        </div>';
$pieces3 [] = '      </div>';
$pieces3 [] = '      <span class="dim"> °C</span>';
$pieces3 [] = '    </div>';

$pieces4 [] = '    <input id="auto" type="hidden" value="off">';
$pieces4 [] = '  </div>';

// Ausgeben **************************************************************************
echo join ( "\n", $pieces );
foreach ( $known_dbs as $k => $rrd ) {
  echo join ( "\n", $pieces1 );
  echo join ( "\n", $pieces2 );
  echo join ( "\n", $pieces3 );
}
echo join ( "\n", $pieces4 );
?>

  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-toggle.min.js"></script>
  <script src="js/ajax.js"></script>
</body>
</html>