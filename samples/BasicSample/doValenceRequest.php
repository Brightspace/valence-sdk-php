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

require_once('config.php');

/*
    This file performs the actual API call. In your application you may wish to rewrite this.
    It uses cURL to send the request.
*/
function doValenceRequest($verb, $route) {
    global $config;

    session_start();
    if (!isset($_SESSION['userId']) || !isset($_SESSION['userKey'])) {
        throw new Exception('This function should only be called once the user is logged in.' .
                            'It expects the userId and userKey to be stored in $_SESSION.');
    }
    $userId = $_SESSION['userId'];
    $userKey = $_SESSION['userKey'];
    session_write_close();

    // Create authContext
    $authContextFactory = new D2LAppContextFactory();
    $authContext = $authContextFactory->createSecurityContext($config['appId'], $config['appKey']);

    // Create userContext
    $hostSpec = new D2LHostSpec($config['host'], $config['port'], $config['scheme']);
    $userContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);

    // Create url for API call
    $uri = $userContext->createAuthenticatedUri($route, $verb);

    // Setup cURL
    $ch = curl_init();
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CAINFO         => getcwd() . '/cacert.pem',
        CURLOPT_CUSTOMREQUEST  => $verb,
        CURLOPT_URL            => $uri
    );
    curl_setopt_array($ch, $options);

    // Do call
    $response = curl_exec($ch);

    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $responseCode = $userContext->handleResult($response, $httpCode, $contentType);

    if ($responseCode == D2LUserContext::RESULT_OKAY) {
        return json_decode($response, true);
    }

    // TODO handle time skew errors

    curl_close($ch);
    throw new Exception("Valence API call failed: $httpCode: $response");
}
