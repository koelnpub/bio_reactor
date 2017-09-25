<?php
$rrdFile = "/var/www/html/rrd/temperatur2.rrd";
$tag = date("YmdHis",time());
$outputPngFile = "/var/www/html/rrd/image_archive/hour_".$tag.".png";
$hour = strtotime("-1 hour",strtotime(date("d.m.Y H:i")));
$graphObj = new RRDGraph($outputPngFile);
$graphObj->setOptions(
    array(
        "--start" => $hour,
        "--end" => time(),
        "--vertical-label" => "°C",
        "--title" => "Letzte Stunde bis ".date("d.m.Y H:i:s",time()),
        "--width" => 800,
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
$file = str_replace("/var/www/html/", "", $outputPngFile)
?>
<img src="<?php echo $file; ?>">