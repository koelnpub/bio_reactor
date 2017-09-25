<?php
$rrdFile = dirname(__FILE__) . "/rrd/temperatur.rrd";
$tag = date("YmdHis",time());
$outputPngFile1 = "/var/www/html/rrd/image_archive/day_".$tag.".png";
$outputPngFile2 = "/var/www/html/rrd/image_archive/week_until_".$tag.".png";
$outputPngFile3 = "/var/www/html/rrd/image_archive/month_until_".$tag.".png";
$start = strtotime("-1 month");
$day = strtotime("-1 day");
$jetzt = time() - 20;
$week = strtotime("-7 day");
$week2 = strtotime("-14 day");
$week3 = strtotime("-21 day");
$week4 = strtotime("-28 day");
$temp = 20;

$creator = new RRDCreator($rrdFile, $start, 60);
$creator->addDataSource("temperatur:GAUGE:120:U:U");
$creator->addArchive("AVERAGE:0.5:1:1440");
$creator->addArchive("AVERAGE:0.5:30:336");
$creator->addArchive("AVERAGE:0.5:180:186");
$creator->addArchive("MIN:0.5:1:1440");
$creator->addArchive("MIN:0.5:30:336");
$creator->addArchive("MIN:0.5:180:186");
$creator->addArchive("MAX:0.5:1:1440");
$creator->addArchive("MAX:0.5:30:336");
$creator->addArchive("MAX:0.5:180:186");
$creator->save();

$updater = new RRDUpdater($rrdFile);
// for($i = 1;$i<10080;$i++) {
//   $updater->update(array("temperatur" => $temp+rand(0,100)/10), $week4+$i*60);
// }
// for($i = 1;$i<10080;$i++) {
//   $updater->update(array("temperatur" => $temp+rand(0,100)/10), $week3+$i*60);
// }
// for($i = 1;$i<10080;$i++) {
//   $updater->update(array("temperatur" => $temp+rand(0,100)/10), $week2+$i*60);
// }
for($i = 1;$i<10080;$i++) {
  $updater->update(array("temperatur" => $temp+rand(0,100)/10), $week+$i*60);
}

$title_post = "bis einschließlich ".date("d.m.Y H:i:s");
$graphObj = new RRDGraph($outputPngFile1);
$graphObj->setOptions(
    array(
        "--start" => $day,
        "--end" => time(),
        "--vertical-label" => "°C",
        "--title" => "24 Stunden ".$title_post,
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
        "GPRINT:mymax:maximum\: %10.2lf °C "
    )
);
$graphObj->save();
$graphObj = new RRDGraph($outputPngFile2);
$graphObj->setOptions(
    array(
        "--start" => $week,
        "--end" => time(),
        "--vertical-label" => "°C",
        "--title" => "7 Tage ".$title_post,
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
        "GPRINT:mylast:aktuell\: %10.2lf °C",
        "GPRINT:myave:durchschnitt\: %10.2lf °C",
        "GPRINT:mymin:minimum\: %10.2lf °C",
        "GPRINT:mymax:maximum\: %10.2lf °C "
    )
);
$graphObj->save();
$graphObj = new RRDGraph($outputPngFile3);
$graphObj->setOptions(
    array(
        "--start" => $start,
        "--end" => time(),
        "--vertical-label" => "°C",
        "--title" => "Ein Monat ".$title_post,
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
        "GPRINT:mylast:aktuell\: %10.2lf °C",
        "GPRINT:myave:durchschnitt\: %10.2lf °C",
        "GPRINT:mymin:minimum\: %10.2lf °C",
        "GPRINT:mymax:maximum\: %10.2lf °C "
    )
);
$graphObj->save();
$file1 = str_replace("/var/www/html/", "", $outputPngFile1);
$file2 = str_replace("/var/www/html/", "", $outputPngFile2);
$file3 = str_replace("/var/www/html/", "", $outputPngFile3);

echo $jetzt."<br>".($week+10079*60)."<br><br>";
?>
<img src="<?php echo $file1; ?>"><br><img src="<?php echo $file2; ?>"><br><img src="<?php echo $file3; ?>">