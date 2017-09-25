/*
File: ajax.js
Vers.:0.1
24.11.2015
 */
$.ajaxSetup({
  crossDomain:true,
  type: 'POST',
  timeout: 5000,
  error: function(xhr) {
    if (xhr.status != "200") {
      $('#display_error').html('Error: ' + xhr.status + ' ' + xhr.statusText);
    } else {
      $('#display_error').html('');
      var liste = $('#client_list').html();
    }
  }
});

$(window).load(function() {
  send(100,20); // pis suchen
  window.setTimeout(function(){send(101);}, 500); // initiale Anzeige
  autorefresh = "on";
  send(102);  // aktualisieren
});


function send(button, b_value) {
  var data;
  switch (parseInt(button)) {
  case 100: // lokalen Raspberry Pis setzen
    client ="rpi001";
    data = "request=confirmation";
    data += "&client="+client;
    $.post("http://"+client+"/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break;
  case 101: // Anzeige initial aufbauen (Ganzes Fenster)
    data = "request=initial_setup";
    data += "&clients="+clients;
    data += "&clients_data="+JSON.stringify(clients_data);
    $.post(ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break;
  case 102: // Anzeige aktualisieren (einzelne Elemente)
    for (i = 0; i < noc; i++) {
      data = "request=refresh";
      data += "&client="+clients[i];
      data += "&id="+i;
      $.post("http://"+clients[i]+"/"+ajxFile, data, function(rdata) {
        compute_response(rdata);
      }, 'json');
    }
    break;
  default:
  }
}
function compute_response(rdata) {
  switch (rdata.response) {
  case "initial_setup":
    $('#viewer').html(rdata.viewer);
    break;
  case "refresh":
    $('#tv_'+rdata.id).html(rdata.temperatur);
    $('#mi_'+rdata.id).html('min:<br>'+rdata.min);
    $('#mav_'+rdata.id).html('max:<br>'+rdata.max);
    $('#dv_'+rdata.id).html(rdata.datum);
    $('#led_'+rdata.id).removeClass('led_amber').removeClass('led_green').removeClass('led_red').addClass('led_'+rdata.status);
    switch (rdata.status) {
    case "amber":
      $('#heater_'+rdata.id).removeClass('heater2').addClass('heater');
      break;
    case "red":
      $('#ventilator_'+rdata.id).removeClass('propeller2').addClass('propeller');
      break;
    case "green":
      $('#heater_'+rdata.id).removeClass('heater').addClass('heater2');
      $('#ventilator_'+rdata.id).removeClass('propeller').addClass('propeller2');
      break;
    }
    break;
  case "confirmation":
    noc = clients.push(rdata.client);
    clients_name.push(rdata.name);
    var data = {
      name:rdata.name,
      temperatur:rdata.temperatur,
      status:rdata.status,
      datum:rdata.datum,
      min:rdata.min,
      max:rdata.max
    };
    clients_data.push(data);
    break;
  }
}
