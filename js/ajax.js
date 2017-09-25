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
function send(button, b_value) {
  var data;
  switch (parseInt(button)) {
  case 100:
    if ($("#switch_" + b_value).prop("checked")) {
      var sw_status = 'on';
    } else {
      var sw_status = 'off';
    }
    if (parseInt(b_value) > 0) {
      data = "request=control_switch";
      data += "&sw_id=" + b_value;
      data += "&sw_status=" + sw_status
      $.post(ajxFile, data, function(rdata) {
        compute_response(rdata);
      }, 'json');
    } else {
      if ($('#auto').val() == "off") {
        $('#auto').val('on');
        autorefresh = "on";
        counter = 0;
      } else {
        $('#auto').val('off');
        autorefresh = "off";
        counter = 0;
      }

    }
    break;
  case 200:
    counter++;
    if (counter > 60) {
      counter = 0;
      $('#auto').val('off');
      $('#switch_0').prop("checked", false).change();
      autorefresh = "off";
      break;
    }
    data = "request=all_values";
    data += "&l_0_c="+$('#l_0_c').val();
    $.post(ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break;
  case 210:
    data = "request=auto_refresh";
    if ($('#auto').val() == "off") {
      $('#auto').val('on');
      $('#auto').removeClass('btn-default').addClass('btn-primary').html(
          'Auto-Refresh Enabled');
      ;
      autorefresh = "on";
      counter = 0;
    } else {
      $('#auto').val('off');
      $('#auto').removeClass('btn-primary').addClass('btn-default').html(
          'Request Auto-Refresh');
      autorefresh = "off";
      counter = 0;
    }
    break;
  case 500:
    client ="rpi00"+b_value;
    data = "request=confirmation";
    data += "&client="+client;
    $.post("http://"+client+"/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break;
  case 501:
    client ="aquarium";
    data = "request=confirmation";
    data += "&client="+client;
    $.post("http://"+client+"/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break;
  case 502:
    for (var i = 1;i<=parseInt(b_value);i++) {
      client ="rpi0";
      if (i < 10) {
        client += "0"+i;
      } else {
        client += i;
      }
      data = "request=confirmation";
      data += "&client="+client;
      $.post("http://"+client+"/"+ajxFile, data, function(rdata) {
        compute_response(rdata);
      }, 'json');
    }
    client ="aquarium";
    data = "request=confirmation";
    data += "&client="+client;
    $.post("http://"+client+"/"+ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break;
  case 503:
//    var html = '<table class="table"><thead><tr><th>Host</th><th>Name</th></th></tr><tbody>';
//    for(var i=0;i<noc;i++) {
//      html += '<tr><td>'+clients[i]+'</td><td>'+clients_name[i]+'</td></tr>';
//    }
//    html += "</tbody></table>";
//    $('#client_list').html("Gefunden: "+noc);
//    $('#client_data').html(html);
    data = "request=initial_setup";
    data += "&clients="+clients;
    data += "&clients_data="+JSON.stringify(clients_data);
    $.post(ajxFile, data, function(rdata) {
      compute_response(rdata);
    }, 'json');
    break;
  case 504:
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
//    $('#client_list').html(liste);
//    $('#client_data').html('<table class="table"><tr><td>Name</td><td>'+rdata.name+'</td></tr><tr><td>Temperatur</td><td>'+rdata.temperatur+'</td></tr><tr><td>Status</td><td>'+rdata.status+'</td></tr><tr><td>Datum</td><td>'+rdata.datum+'</td></tr></table>');
    break;
  case "all_values":
    $('#led_0').removeClass('led_red').removeClass('led_green').removeClass('led_amber').addClass('led_'+rdata.status);
    for (i = 0; i < parseInt(rdata.count); i++) {
      $('#v' + i).html(
          rdata.temps[i][0] + "<span>" + rdata.temps[i][1] + "</span>");
    }
    if (rdata.status =="amber") {
      $('#ventilator').removeClass('propeller').addClass('propeller2');
      $('#heater').removeClass('heater2').addClass('heater');
    } else if (rdata.status =="red"){
      $('#ventilator').removeClass('propeller2').addClass('propeller');
      $('#heater').removeClass('heater').addClass('heater2');
    } else {
      $('#ventilator').removeClass('propeller').addClass('propeller2');
      $('#heater').removeClass('heater').addClass('heater2');
    }
    break;
  case "all_images":
    var i;
    for (i = 1; i < parseInt(rdata.images); i++) {
      $('#img' + i).html("");
      $('#img' + i).html(rdata.img[i]);
    }
    $("#img")
  }
}
