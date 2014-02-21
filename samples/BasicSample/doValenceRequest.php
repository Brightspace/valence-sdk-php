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
        CURLOPT_CUSTOMREQUEST  => $verb,
        CURLOPT_URL            => $uri,
        
        /* Explicitly turn this on because some versions of cURL (and therefore PHP installations) do
           not default this to true. This verifies the SSL certs that are used to communicate with an
           LMS via HTTPS. It is vitally important that you always do this in production. */
        CURLOPT_VERIFYPEER => true,
        
        /* CURLOPT_CAINFO points to a liste of trusted certificates to use for verifying (see above).
           If you are on some platforms (e.g. possibly Windows) you may need to explicitly provide these.
           Your platform may not require this as it may provide a system-wide setting (which you should prefer).
           If you need to explicitly set this please get an updated file from http://curl.haxx.se/docs/caextract.html
           and preferably set this up in your php.ini globally. For more information, see
           http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/ */
        CURLOPT_CAINFO         => getcwd() . '/cacert.pem'
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
