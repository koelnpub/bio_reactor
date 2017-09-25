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
var clients = [];
var clients_name = [];
var clients_data = [];
var noc = 0;
var client = '';
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
  <button class="btn btn-primary" onclick="send(500,1)">RPI001 Testen</button>
  <button class="btn btn-primary" onclick="send(501,1)">Aquarium Testen</button>
  <button class="btn btn-primary" onclick="send(502,20)">RPIs Suchen (1-20)</button>
  <button class="btn btn-primary" onclick="send(503)">Ergebnis zeigen</button>
  <button class="btn btn-primary" onclick="send(504)">Ergebnis aktualisieren</button>
  
  <div><span id="client_list"></span></div>
  <div id="client_data"></div>
  <div id="viewer"></div>
  
  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-toggle.min.js"></script>
  <script src="js/ajax.js"></script>
</body>
</html>