// A few utilities related to sending JSON requests to our own server.
//


function sendPayload_(url, opt_callback, opt_method, opt_payload) {
  var method = opt_method || "GET";

  var xmlhttp = null;
  if (window.XMLHttpRequest) {// code for all new browsers
    xmlhttp = new XMLHttpRequest();
  } else if (window.ActiveXObject) {// code for IE5 and IE6
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (xmlhttp != null) {
    xmlhttp.onreadystatechange = onPayloadResponse_(xmlhttp, opt_callback);
    xmlhttp.open(method, url, true);
  if (opt_method == "POST") {
    xmlhttp.setRequestHeader("Content-type",
                             "application/x-www-form-urlencoded");
  }

    xmlhttp.send(opt_payload);
  }
}


function onPayloadResponse_(xmlhttp, opt_callback, opt_err) {
  return function() {
    if (xmlhttp.readyState == 4) {// 4 = "loaded"
      if (xmlhttp.status == 200) {// 200 = OK
        if (opt_callback) {
          opt_callback(xmlhttp.responseText);
        }
      } else {
        if (opt_err) {
          opt_err(xmlhttp);
        }
      }
    }
  }
}


function log(message) {
  if (window.console) {
    window.console.log(message);
  }
}