/**
 * Copyright (c) 2012 Desire2Learn Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the license at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/******************************************************************************
 * Javascript functions for the sample HTML file                              *
 ******************************************************************************/
if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g, '');
  };
}

 function doAPIRequest() {
	$('#responseField').val("");
	document.getElementById('errorField1').hidden = true;
	document.getElementById("errorField2").innerHTML = "";
	document.getElementById("responseField").hidden = true;
	document.getElementById("responseFieldLabel").hidden = true;
	$('#responseField').val("");

	var host = $('#hostField').val().trim();
	var port = $('#portField').val().trim();
	var scheme = $('#schemeField').is(':checked') ? 'https' : 'http';
	var req = $('#actionField').val().trim();
	var method = $('#GETField').is(':checked') ? "GET" :
				 $('#POSTField').is(':checked') ? "POST" :
				 $('#PUTField').is(':checked') ? "PUT" : "DELETE";
	var data = $('#dataField').val();
	var anon = $('#anonymousField').is(':checked');
    var appId = $('#appIDField').val().trim();
    var appKey = $('#appKeyField').val().trim();
	$.ajax({
				url: "doRequest.php",
				data: {
					host: host,
					port: port,
					scheme: scheme,
					anon: anon,
					apiRequest: req,
					apiMethod: method,
					data: data,
                    appId: appId,
                    appKey: appKey
				},
				success: function(data) {
					var output;
					if(data == '') {
						output = 'Success!';
						return;
					} else {
						try {
							output = JSON.stringify(JSON.parse(data), null, 4);
						} catch(e) {
							output = "Unexpected non-JSON response from the server: " + data;
						}
					}
					$('#responseField').val(output);
					document.getElementById("responseField").hidden = false;
					document.getElementById("responseFieldLabel").hidden = false;
				},
				error: function(jqXHR, textStatus, errorThrown) {
					document.getElementById('errorField1').hidden = false;
					document.getElementById("errorField2").innerHTML = jqXHR.responseText;
				},
			});
 }
