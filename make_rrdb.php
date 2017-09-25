<?php
$rrdFile = "/var/www/html/rrd/temperatur.rrd";

$creator = new RRDCreator($rrdFile, time(), 2);
$creator->addDataSource("temperatur:GAUGE:6:U:U");
$creator->addArchive("AVERAGE:0.5:1:3600");
$creator->addArchive("AVERAGE:0.5:30:1440");
$creator->addArchive("AVERAGE:0.5:150:336");
$creator->addArchive("AVERAGE:0.5:240:248");
$creator->addArchive("MAX:0.5:1:3600");
$creator->addArchive("MIN:0.5:30:1440");
$creator->addArchive("MIN:0.5:150:336");
$creator->addArchive("MIN:0.5:240:248");
$creator->addArchive("MAX:0.5:1:3600");
$creator->addArchive("MAX:0.5:30:1440");
$creator->addArchive("MAX:0.5:150:336");
$creator->addArchive("MAX:0.5:240:248");
$creator->save();
?>