<?php
/**
 * reads the configuration data of the allready known sensors
 * and their db relations.
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
function row_wrap($lc,$string) {
  $pieces [] =  '  <div id="row_'.$lc.'" class="row text-center">';
  $pieces [] = $string;
  $pieces [] =  '    </div>';
  return join("\n", $pieces);
}
function col_wrap($id,$temp,$status,$pc,$hc,$name,$datum,$min,$max) {
  $pieces [] =  '    <div class="col-sm-3">';
  $pieces [] =  '      <h3>'.$name.'</h3>';
  $pieces [] =  '      <div class="des">';
  $pieces [] =  '        <div id="vbody">';
  $pieces [] =  '          <div id="ventilator_'.$id.'" class="'.$pc.'"></div>';
  $pieces [] =  '        </div>';
  $pieces [] =  '        <div id="hbody">';
  $pieces [] =  '          <div id="heater_'.$id.'" class="'.$hc.'"></div>';
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
