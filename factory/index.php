<!DOCTYPE html>
<html lang="en">
<head>
<title>Bio-Factory</title>
<link href="styles/bootstrap.css" rel="stylesheet" />
<link href="styles/bootstrap-toggle.min.css" rel="stylesheet" />
<link href="styles/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
var ajxFile = "ajax_interface.php";
var autorefresh = "off";
var clients = [];
var clients_name = [];
var clients_data = [];
var noc = 0;
var client = '';
var updateView = function ()  {
  if (autorefresh == "on") {
    send(102);
  }
  myTimer = window.setTimeout(updateView, 5000);
}
var myTimer = window.setTimeout(updateView, 5000);
</script>
</head>
<body>  
  <div><span id="client_list"></span></div>
  <div id="client_data"></div>
  <div id="viewer"></div>
  
  <div id='graphic' class='modal fade' role='dialog'>
    <div class='modal-dialog modal-sm'>
      <div class='modal-content'>
        <div class='modal-header'>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="hl">Auswertung</h4>
        </div>
        <div class='modal-body'>
        </div>
        <div class='modal-footer'>
        </div>
      </div>
    </div>
  </div>
  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-toggle.min.js"></script>
  <script src="js/ajax.js"></script>
</body>
</html>