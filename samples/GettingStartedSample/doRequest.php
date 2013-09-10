<?php
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

require_once 'config.php';
require_once $config['libpath'] . '/D2LAppContextFactory.php';

$errorArray = array(
    D2LUserContext::RESULT_OKAY => "Success",
    D2LUserContext::RESULT_INVALID_SIG => "Invalid signature.",
    D2LUserContext::RESULT_INVALID_TIMESTAMP => "There is a time skew between server and local machine.  Try again.",
    D2LUserContext::RESULT_NO_PERMISSION => "Not authorized to perform this operation.",
    D2LUserContext::RESULT_UNKNOWN => "Unknown error occured"
    );


$host = $_GET['host'];
$port = $_GET['port'];
$scheme = $_GET['scheme'];
$data = $_GET['data'];
$apiMethod = $_GET['apiMethod'];
$appId = $_GET['appId'];
$appKey = $_GET['appKey'];

session_start();

$userId = $_SESSION['userId'];
$userKey = $_SESSION['userKey'];
$_SESSION['host'] = $host;
$_SESSION['port'] = $port;
$_SESSION['scheme'] = $scheme;
$_SESSION['appId'] = $appId;
$_SESSION['appKey'] = $appKey;
session_write_close();

$authContextFactory = new D2LAppContextFactory();
$authContext = $authContextFactory->createSecurityContext($appId, $appKey);
$hostSpec = new D2LHostSpec($host, $port, $scheme);

$opContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);

$ch = curl_init();
$options = array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CAINFO => getcwd().'/cacert.pem',
//    CURLOPT_SSL_VERIFYPEER => false,
    );

curl_setopt_array($ch, $options);

$tryAgain = true;
$numAttempts = 1;
while ($tryAgain && $numAttempts < 5) {
    $uri = $opContext->createAuthenticatedUri($_GET['apiRequest'], $_GET['apiMethod']);

    curl_setopt($ch, CURLOPT_URL, $uri);
    switch($apiMethod) {
        case 'POST':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        case 'GET':
            break;
    }
    $response = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $responseCode = $opContext->handleResult($response, $httpCode, $contentType);

    if ($responseCode == D2LUserContext::RESULT_OKAY) {
        $ret = "$response";
        $tryAgain = false;
    } elseif ($responseCode == D2LUserContext::RESULT_INVALID_TIMESTAMP) {
        // Try again since time skew should now be fixed.
        $tryAgain = true;
    } else {
        if($httpCode == 302) {
            // This usually happens when a call is made non-anonymously prior to logging in.
            // The D2L server will send a redirect to the log in page.
            $ret = "Redirect encountered (need to log in for this API call?) (HTTP status 302)";
        } else {
            $ret = "{$errorArray[$responseCode]} (HTTP status $httpCode)";
        }
        $tryAgain = false;
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . '400 Bad Request');
        $GLOBALS['http_response_code'] = $httpCode;
    }
    $numAttempts++;
}

curl_close($ch);
echo $ret;
?>
