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
require_once 'doValenceRequest.php';

session_start();
$authenticated = isset($_SESSION['userId']) && isset($_SESSION['userKey']);
session_write_close();
?>

<!doctype html>
<meta charset="utf-8">

<title>Desire2Learn Basic SDK Sample</title>

<form method="get" action="authenticateUser.php" id="configForm">
<?php if($authenticated) { ?>
    <input type="submit" name="authBtn" value="Deauthenticate">
<?php } else { ?>
    <input type="submit" name="authBtn" value="Authenticate">
<?php } ?>
</form>


<?php
if ($authenticated) {
    try {
        $userData = doValenceRequest('GET', '/d2l/api/lp/' . $config['LP_Version'] . '/users/whoami');
    } catch (Exception $e) {
        echo '<span>Caught exception:</span><pre>' . $e->getMessage() . '</pre>';
        exit;
    }
    echo '<span>Welcome, ' . $userData['UniqueName'] . '.</span>';
}
