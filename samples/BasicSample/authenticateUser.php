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

function getPageURL() {
    $portString = '';
    if (($_SERVER['HTTPS'] == 'on' && $_SERVER['SERVER_PORT'] != 443)
     || ($_SERVER['HTTPS'] == 'off' && $_SERVER['SERVER_PORT'] != 80)) {
            $portString = ':' . $_SERVER['SERVER_PORT'];
    }
    return "http" . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . $portString . $_SERVER['REQUEST_URI'];
}

if ($_GET['authBtn'] == 'Deauthenticate') {
    session_start();
    unset($_SESSION['userId']);
    unset($_SESSION['userKey']);
    session_write_close();
    header("location: index.php");
} else {
    $redirectPage = str_replace('authenticateUser.php', 'postLogin.php', getPageURL());
    $authContextFactory = new D2LAppContextFactory();
    $authContext = $authContextFactory->createSecurityContext($config['appId'], $config['appKey']);
    $hostSpec = new D2LHostSpec($config['host'], $config['port'], $config['scheme']);
    $url = $authContext->createUrlForAuthenticationFromHostSpec($hostSpec, $redirectPage);
    header("Location: $url");
}
