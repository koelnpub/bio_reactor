<?php
session_start ();
require_once 'functions_inc.php';
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
$min = 24.0;
$max = 26.0;
$ajxData = $_POST;
$request = $_POST ['request'];
$answer = array();
switch ($request) {
  case "save_maintenance":
    $answer ['response'] = $request;
    
    break;
  case "change_on_state":
    $answer ['response'] = $request;
    $answer ['msg'] = changeOnState($_POST ['status']);
    break;
  case "g_button":
    $answer ['response'] = $request;
    $answer ['client'] = $_POST['client'];
    $image = createImage($_POST['start'],$_POST['stop'],$_POST['client']);
    $answer ['body'] = "<img src='".$image."'>";
    break;
  case "m_button":
    $answer ['response'] = $request;
    $answer ['client'] = $_POST['client'];
    $answer ['header'] = $_POST['name'];
    $answer ['body'] = getMaintenanceEditor($_POST);
    break;
  case "button":
    $answer ['response'] = $request;
    $answer ['client'] = $_POST['client'];
    $answer ['header'] = $_POST['name'];
    $answer ['body'] = getParameter($_POST);
    break;
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
    $daten = file("/var/www/html/t_msg.txt");
    if (count($daten)) {
      $werte = explode("|",$daten[0]);
      $answer ['response'] = $request;
      $answer ['client'] = $_POST['client'];
      $answer['name'] = $werte[0];
      $answer['temperatur'] = str_replace(".", ",", $werte[1])."°C";
      $answer['status'] = $werte[2];
      $answer['datum'] = $werte[3];
      $answer['min'] = str_replace(".", ",", $werte[4])."°C";
      $answer['max'] = str_replace(".", ",", $werte[5])."°C";
      $answer['id'] = $_POST['id'];
    } else {
      $answer ['response'] = "nothing";
    }
    break;
  default :
    echo "<pre>" . print_r ( $ajxData, TRUE ) . "</pre>";
}
echo json_encode ( $answer );
?>