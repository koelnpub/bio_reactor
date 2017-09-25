<?php
/**
 * liest die Datei mit den Informationen über die vorhandenen Sensoren und gibt diese als Array zurück.
 *
 * @return array
 */
function read_known_db_config() {
  $known_dbs = file ( "/var/www/html/scripts/rrdb.txt" );
  foreach ( $known_dbs as $k => $db ) {
    $known_dbs [$k] = explode ( ":", $db );
    $known_dbs [$k] [1] = str_replace ( "\n", "", $known_dbs [$k] [1] );
    $known_dbs [$k] [1] = explode ( "|", $known_dbs [$k] [1] );
  }
  return $known_dbs;
}
/**
 * Erzeugt ein Diagramm aus den Datenbankwerten zwischen Start- und Stoppzeit und gibt einen Link auf die Datei mit dem Diagramm zurück.
 * @param string $c_start
 * @param string $c_stop
 * @param string $client
 * 
 * @return string
 */
function createImage($c_start,$c_stop,$client) {
  $start = strtotime($c_start);
  $stop = strtotime($c_stop);
  $rrdFile = "/var/www/html/rrd/temperatur.rrd";
  $tag = date("YmdHis",time());
  $outputPngFile = "/var/www/html/factory/rrd/image_archive/requested_".$tag.".png";
  $graphObj = new RRDGraph($outputPngFile);
  $graphObj->setOptions(
      array(
          "--start" => $start,
          "--end" => $stop,
          "--vertical-label" => "°C",
          "--title" => $client.": ".$c_start." bis ".$c_stop,
          "--width" => 775,
          "--height" => 200,
          "DEF:mytemperatur=$rrdFile:temperatur:AVERAGE",
          "DEF:myminimum=$rrdFile:temperatur:MIN",
          "DEF:mymaximum=$rrdFile:temperatur:MAX",
          "VDEF:mylast=mytemperatur,LAST",
          "VDEF:mymin=myminimum,MINIMUM",
          "VDEF:mymax=mymaximum,MAXIMUM",
          "VDEF:myave=mytemperatur,AVERAGE",
          "AREA:mytemperatur#AAFFAA",
          "LINE1:mytemperatur#00FF00:Temperatur",
          "GPRINT:mylast:aktuell\: %10.2lf °C",
          "GPRINT:myave:durchschnitt\: %10.2lf °C",
          "GPRINT:mymin:minimum\: %10.2lf °C",
          "GPRINT:mymax:maximum\: %10.2lf °C"
      )
  );
  $graphObj->save();
  return "http://".$client.str_replace("var/www/html/", "", $outputPngFile);
}
/**
 * aktualisiert die Informationen zum 1. Sensor. Zurückgegeben wird der aktuelle Laufzeitstatus oder im Fehlerfall eine entsprechende Nachricht.
 * @param array $known_dbs
 * @return string
 */
function writeKnownDbs($known_dbs) {
  $string = "";
  foreach ( $known_dbs as $k => $db ) {
    $string .= $db[0].":".join("|", $db[1])."\n";
  }
  $handle = fopen ( "/var/www/html/scripts/rrdb.txt", "w" );
  if ($handle) {
    fwrite ( $handle, $string );
    fclose ( $handle );
  } else {
    return "WRITE ERROR";
  }
  return $known_dbs [0] [1] [5];
}
/**
 * Ändern des Laufzeitstatus (RUN,IDLE,STOP) für den 1. Sensor.
 * @param string $value
 * @return string
 */
function changeOnState($value) {
  $known_dbs = read_known_db_config();
  $known_dbs [0] [1] [5] = $value;
  return writeKnownDbs($known_dbs);
}
/**
 * Liefert die Vorgaben (Formularelemente) für das Diagrammauswahl-Fenster.
 * @param array $data
 * @return string
 */
function getParameter($data) {
  $pieces = array();
  $pieces [] = "<input type='hidden' id='name' value='".$data['name']."'>";
  $pieces [] = "<input type='hidden' id='client' value='".$data['client']."'>";
  $pieces [] = "<input type='hidden' id='id' value='".$data['id']."'>";
  $pieces [] = "<div id='editor'>";
  $pieces [] =  input_wrap("start", date("d.m.Y H:i:s",strtotime("-2 hour")), "Start (-zeit)");
  $pieces [] = "<br>";
  $pieces [] =  input_wrap("stop", date("d.m.Y H:i:s"),"Ende (-zeit)");
  $pieces [] = "<br><button class='btn btn-primary' onclick='send(104,".$data['id'].")'><span class = 'glyphicon glyphicon-stats'></span></button>";
  $pieces [] = "</div>";
  $pieces [] = "<div id ='g_viewer'></div>";
  return join("\n", $pieces);
}
/**
 * Liefert die Vorgaben (Formularelemente) für das Editor-Fenster zur Bearbeitung der Sensordaten (1. Sensor).
 * @param array $data
 * @return string
 */
function getMaintenanceEditor($data) {
  $known_dbs = read_known_db_config();
  $name = $known_dbs [0] [1] [1];
  $min = str_replace(".",",",$known_dbs [0] [1] [3]);
  $max = str_replace(".",",",$known_dbs [0] [1] [4]);
  $on_state = $known_dbs [0] [1] [5];
  $sensor = $known_dbs [0] [0];
  $pieces [] = "<input type='hidden' id='name' value='".$data['name']."'>";
  $pieces [] = "<input type='hidden' id='client' value='".$data['client']."'>";
  $pieces [] = "<input type='hidden' id='id' value='".$data['id']."'>";
  $pieces [] = "<div id='editor'>";
  $pieces [] =  input_wrap("Sensor", $sensor, "Sensor-ID");
  $pieces [] = "<br>";
  $pieces [] =  input_wrap("Name", $name, "Name der Anlage");
  $pieces [] = "<br>";
  $pieces [] =  input_wrap("min", $min, "Minimaltemperatur","°C","text-right");
  $pieces [] = "<br>";
  $pieces [] =  input_wrap("max", $max,"Maximaltemperatur","°C","text-right");
  $pieces [] =  '</div>';
  $pieces [] = "<br><button class='btn btn-success' onclick='send(106,".$data['id'].")'><span class = 'glyphicon glyphicon-file'></span> Sichern</button><br><hr>";
  $pieces [] =  '<div id="on_state_radio">';
  $pieces [] =  'Anlage:';
  $pieces [] =  '<div class="radio-inline">';
  if ($on_state == "RUN") {
    $pieces [] =  '<label><input type="radio" name="optradio" value="RUN" onclick="send(107,'.$data['id'].')" checked>Ein</label>';
  } else {
    $pieces [] =  '<label><input type="radio" name="optradio" value="RUN" onclick="send(107,'.$data['id'].')">Ein</label>';
  }
  $pieces [] =  '</div>';
  $pieces [] =  '<div class="radio-inline">';
  if ($on_state == "IDLE") {
    $pieces [] =  '<label><input type="radio" name="optradio" value="IDLE" onclick="send(107,'.$data['id'].')" checked>Angehalten</label>';
  } else {
    $pieces [] =  '<label><input type="radio" name="optradio" value="IDLE" onclick="send(107,'.$data['id'].')">Angehalten</label>';
  }
  $pieces [] =  '</div>';
  $pieces [] =  '<div class="radio-inline">';
  if ($on_state == "STOP") {
    $pieces [] =  '<label><input type="radio" name="optradio" value="STOP" onclick="send(107,'.$data['id'].')" checked>Aus</label>';
  } else {
    $pieces [] =  '<label><input type="radio" name="optradio" value="STOP" onclick="send(107,'.$data['id'].')">Aus</label>';
  }
  $pieces [] =  '</div>';
  $pieces [] =  '</div>';
  return join("\n", $pieces);
}
/**
 * Gibt eine Bootstrap Zeile zurück
 * @param string $lc
 * @param string $string
 */
function row_wrap($lc,$string) {
  $pieces [] =  '  <div id="row_'.$lc.'" class="row text-center">';
  $pieces [] = $string;
  $pieces [] =  '    </div>';
  return join("\n", $pieces);
}
/**
 * Gibt ein bootstrap-konformes Eingabefeld zurück.
 * @param string $name
 * @param string $value
 * @param string $placeholder
 * @param string $dim
 * @param string $ori
 */
function input_wrap($name, $value, $placeholder,$dim = "",$ori="text-left") {
  $pieces = array();
  $pieces [] =  '<div class="input-group">';
  $pieces [] =  '<span class="input-group-addon" id="'.$name.'">'.$name.'</span>';
  $pieces [] =  '<input id="i_'.$name.'" type="text" class="form-control '.$ori.'" placeholder="'.$placeholder.'" value="'.$value.'">';
  if ($dim) {
    $pieces [] =  '<span class="input-group-addon" id="dim_'.$name.'">'.$dim.'</span>';
  }
  $pieces [] =  '</div>';
  
  return join("\n", $pieces);
}
/**
 * Gibt eine bootstrap-konforme Bildschirm-Spaltengruppe mit der schematischen Darstellung der Anlage zurück. Bootstrap teilt den Bildschirm in 12 gleichgoße Spalten.
 * Hier werden drei Spalten also 1/4 der Bildschirmbreite genutzt.
 * @param string $id
 * @param string $temp
 * @param string $status
 * @param string $pc
 * @param string $hc
 * @param string $name
 * @param string $datum
 * @param string $min
 * @param string $max
 * 
 * @return string
 */
function col_wrap($id,$temp,$status,$pc,$hc,$name,$datum,$min,$max) {
  $pieces = array();
  $pieces [] =  '    <div class="col-sm-3">';
  $pieces [] =  '      <input type="hidden" id="name_'.$id.'" value="'.$name.'">';
  $pieces [] =  '      <h3>'.$name.'</h3>';
  $pieces [] =  '      <div class="des">';
  $pieces [] =  '        <div id="vbody">';
  $pieces [] =  '          <div id="ventilator_'.$id.'" class="'.$pc.'"></div>';
  $pieces [] =  '        </div>';
  $pieces [] =  '        <div id="hbody">';
  $pieces [] =  '          <div id="heater_'.$id.'" class="'.$hc.'"></div>';
  $pieces [] =  '        </div>';
  $pieces [] =  '        <div id="abody">';
  $pieces [] =  '          <button class="btn btn-primary" onclick="send(103,'.$id.')"><span class="glyphicon glyphicon-search"></span></button>';
  $pieces [] =  '          <button id="maintenance" class="btn btn-success" onclick="send(105,'.$id.')"><span class="glyphicon glyphicon-cog"></span></button>';
  $pieces [] =  '        </div>';
  $pieces [] =  '        <div id="table">';
  $pieces [] =  '        <table class="table">';
  $pieces [] =  '          <tr>';
  $pieces [] =  '            <td>';
  $pieces [] =  'Temperatur';
  $pieces [] =  '            </td>';
  $pieces [] =  '            <td><span id="tv_'.$id.'">';
  $pieces [] =  $temp;
  $pieces [] =  '            </span></td>';
  $pieces [] =  '          </tr>';
  $pieces [] =  '          <tr>';
  $pieces [] =  '            <td>';
  $pieces [] =  'Stand';
  $pieces [] =  '            </td>';
  $pieces [] =  '            <td><span id="dv_'.$id.'">';
  $pieces [] =  $datum;
  $pieces [] =  '            </span></td>';
  $pieces [] =  '          </tr>';
  $pieces [] =  '          <tr>';
  $pieces [] =  '            <td>';
  $pieces [] =  'Status';
  $pieces [] =  '            </td>';
  $pieces [] =  '            <td>';
  $pieces [] =  '<div id="led_'.$id.'" class="led_'.$status.'"></div>';
  $pieces [] =  '            </td>';
  $pieces [] =  '          </tr>';
  $pieces [] =  '          <tr>';
  $pieces [] =  '            <td><span id="mi_'.$id.'">';
  $pieces [] =  'min:<br>'.$min;
  $pieces [] =  '            </span></td>';
  $pieces [] =  '            <td><span id="ma_'.$id.'">';
  $pieces [] =  'max:<br>'.$max;
  $pieces [] =  '            </span></td>';
  $pieces [] =  '          </tr>';
  $pieces [] =  '        </table>';
  $pieces [] =  '      </div>';
  $pieces [] =  '      </div>';
  return join("\n", $pieces);
}
?>
