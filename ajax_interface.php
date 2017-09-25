<?php
session_start ();
require_once '/var/www/html/functions_inc.php';
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
$min = 24.0;
$max = 26.0;
$ajxData = $_POST;
$request = $_POST ['request'];
$answer = array();
switch ($request) {
  case "confirmation":
    $answer ['response'] = $request;
    $answer ['client'] = $_POST['client'];
    $daten = file("/var/www/html/t_msg.txt");
    $werte = explode("|",$daten[0]);
    $answer['name'] = $werte[0];
    $answer['temperatur'] = str_replace(".", ",", $werte[1])."°C";
    $answer['status'] = $werte[2];
    $answer['datum'] = $werte[3];
    $answer['min'] = str_replace(".", ",", $werte[4])."°C";
    $answer['max'] = str_replace(".", ",", $werte[5])."°C";
    break;
  case "initial_setup":
    $viewer = array();
    $data = json_decode($_POST['clients_data'],true);
    $answer ['response'] = $request;
//     $answer ['viewer'] = "<pre>".print_r($data,true)."</pre>";
    foreach($data as $k=>$v) {
      $pc ="propeller2";
      $hc ="heater2";
      switch($v['status']) {
        case "amber":
          $hc ="heater";
          break;
        case "red":
          $pc ="propeller";
          break;
      }
      $string = col_wrap($k, $v['temperatur'], $v['status'], $pc, $hc, $v['name'], $v['datum'], $v['min'], $v['max']);
      $viewer[] = row_wrap($k, $string);
    }
    $answer ['viewer'] = join("\n",$viewer);
    break;
  case "refresh":
    
    
    
    
    $answer ['response'] = $request;
    $answer ['client'] = $_POST['client'];
    $daten = file("/var/www/html/t_msg.txt");
    $werte = explode("|",$daten[0]);
    $answer['name'] = $werte[0];
    $answer['temperatur'] = str_replace(".", ",", $werte[1])."°C";
    $answer['status'] = $werte[2];
    $answer['datum'] = $werte[3];
    $answer['min'] = str_replace(".", ",", $werte[4])."°C";
    $answer['max'] = str_replace(".", ",", $werte[5])."°C";
    $answer['id'] = $_POST['id'];
    break;
  case "all_values" :
    $answer ['response'] = $request;
    $temps = array ();
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
      } elseif ($temp01 > $max ) {
        exec('gpio write 0 1');
        exec('gpio write 2 1');
        exec('gpio write 3 0');
        $status = 'red';
        $a_status = 'red';
      } else {
        exec('gpio write 0 1');
        exec('gpio write 2 0');
        exec('gpio write 3 1');
        $status = 'green';
        $a_status = 'green';
      }
      $temp01 = number_format($temp01,1,".",",");
      $temps [$k] = explode ( ".", $temp01 );
      $answer ['t'.$k.'_0'] = $temps[$k][0];
      $answer ['t'.$k.'_1'] = $temps[$k][1];
    }
    $answer ['count'] = count($temps);
    $answer ['temps'] = $temps;
    $answer ['status'] = $status;
    $answer['lc']= $_POST ['l_0_c'];
    break;
  default :
    echo "<pre>" . print_r ( $ajxData, TRUE ) . "</pre>";
}
echo json_encode ( $answer );
?>