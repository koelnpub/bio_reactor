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
  case 100: // Raspberry Pis suchen
    for (var i = 1;i<=parseInt(b_value);i++) {
      client ="rpi0";
      if (i < 10) {
        client += "0"+i;
      } else {
        client += i;
      }
      data = "request=confirmation";
      data += "&client="+client;
      $.post("http://"+client+"/factory/"+ajxFile, data, function(rdata) {
        compute_response(rdata);
      }, 'json');
    }
    client ="aquarium";
    data = "request=confirmation";
    data += "&client="+client;
    $.post("http://"+client+"/factory/"+ajxFile, data, function(rdata) {
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
      $.post("http://"+clients[i]+"/factory/"+ajxFile, data, function(rdata) {
        compute_response(rdata);
      }, 'json');
    }
    break;  
  case 103: // Button wurde betätigt
    data = "request=button";
    data += "&client="+clients[parseInt(b_value)];
    data += "&name="+$("#name_"+b_value).val();
    data += "&id="+b_value;
    $.post("http://"+clients[parseInt(b_value)]+"/factory/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break; 
  case 104: // Grafik Button wurde betätigt
    data = "request=g_button";
    data += "&client="+clients[parseInt(b_value)];
    data += "&name="+$("#name_"+b_value).val();
    data += "&id="+b_value;
    data += "&start="+$('#i_start').val();
    data += "&stop="+$('#i_stop').val();
    $.post("http://"+clients[parseInt(b_value)]+"/factory/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break; 
  case 105: // Wartungs Button wurde betätigt
    data = "request=m_button";
    data += "&client="+clients[parseInt(b_value)];
    data += "&name="+$("#name_"+b_value).val();
    data += "&id="+b_value;
    $.post("http://"+clients[parseInt(b_value)]+"/factory/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break; 
  case 106: // Wartungs Fenster speichern
    data = "request=save_maintenance";
    data += "&client="+clients[parseInt(b_value)];
    data += "&name="+$("#name_"+b_value).val();
    data += "&i_sensor="+$("#i_Sensor").val();
    data += "&i_name="+$("#i_Name").val();
    data += "&i_min="+$("#i_min").val();
    data += "&i_max="+$("#i_max").val();
    data += "&status="+$("input[name='optradio']:checked").val();
    data += "&id="+b_value;
    data += ""
    $.post("http://"+clients[parseInt(b_value)]+"/factory/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break; 
  case 107: // Laufzeit Status ändern
    $("input[name='optradio']").prop('disabled',true);
    data = "request=change_on_state";
    data += "&client="+clients[parseInt(b_value)];
    data += "&name="+$("#name_"+b_value).val();
    data += "&id="+b_value;
    data += "&status="+$("input[name='optradio']:checked").val();
    $.post("http://"+clients[parseInt(b_value)]+"/factory/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break; 
  default:
  }
}
function compute_response(rdata) {
  switch (rdata.response) {
  case "change_on_state":
    $('#on_state_radio').html(rdata.msg);
    switch (rdata.msg) {
    case "RUN":
      $('#maintenance').removeClass('btn-default').removeClass('btn-danger').removeClass('btn-warning').addClass('btn-success');
      break;
    case "IDLE":
      $('#maintenance').removeClass('btn-default').removeClass('btn-danger').removeClass('btn-success').addClass('btn-warning');
      break;
    case "STOP":
      $('#maintenance').removeClass('btn-default').removeClass('btn-success').removeClass('btn-warning').addClass('btn-danger');
      break;
    case "WRITE ERROR":
      $('#maintenance').removeClass('btn-success').removeClass('btn-danger').removeClass('btn-warning').addClass('btn-default');
      break;
    }
    break;
  case "save_maintenance":
    
    break;
  case "m_button":
    $("#graphic .modal-dialog").removeClass('modal-lg').addClass('modal-sm');
    $("#graphic .modal-header .hl").html(rdata.header + " - Wartung");
    $("#graphic .modal-body").html(rdata.body);
    $('#graphic').modal('show');
    break;
  case "g_button":
    $("#graphic .modal-body #editor").html("");
    $("#graphic .modal-body #g_viewer").html(rdata.body);
    $("#graphic .modal-dialog").removeClass('modal-sm').addClass('modal-lg');
    break;
  case "button":
    $("#graphic .modal-dialog").removeClass('modal-lg').addClass('modal-sm');
    $("#graphic .modal-header .hl").html(rdata.header + " - Auswertung");
    $("#graphic .modal-body").html(rdata.body);
    $('#graphic').modal('show');
    break;
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
