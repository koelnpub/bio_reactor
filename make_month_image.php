<?php
$rrdFile = "/var/www/html/rrd/temperatur.rrd";
$tag = date("Ymd",time());
$outputPngFile = "/var/www/html/rrd/image_archive/month_until_".$tag.".png";
$month = strtotime("-1 month");
$graphObj = new RRDGraph($outputPngFile);
$graphObj->setOptions(
    array(
        "--start" => $month,
        "--end" => time(),
        "--vertical-label" => "m/s",
        "--title" => "1 Monat bis einschließlich  ".date("d.m.Y H:i:s",time()),
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
        "LINE1:myminimum#0000ff:Minimum",
        "LINE1:mymaximum#ff0000:Maximim",
        "GPRINT:mylast:aktuell\: %10.2lf m/s",
        "GPRINT:myave:durchschnitt\: %10.2lf m/s",
        "GPRINT:mymin:minimum\: %10.2lf m/s",
        "GPRINT:mymax:maximum\: %10.2lf m/s "
    )
);
$graphObj->save();
$file = str_replace("/var/www/html/", "", $outputPngFile)
?>
<img src="<?php echo $file; ?>">