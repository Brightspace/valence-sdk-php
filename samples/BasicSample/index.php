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

require_once 'valenceCredentials.php';
require_once 'doRequest.php';

$authenticated = false;

if (isset($_GET['x_a']) && isset($_GET['x_b'])){
	session_start();
	$_SESSION['userId'] = $_GET['x_a'];
	$_SESSION['userKey']= $_GET['x_b'];
	session_write_close();
	header("Location: index.php");
}else{
	session_start();
	if((isset($_SESSION['userId'])) && (isset($_SESSION['userKey'])) && (isset($_SESSION['hostSpec']))){
		$hostSpec = $_SESSION['hostSpec'];
		$userId = $_SESSION['userId'];
		$userKey = $_SESSION['userKey'];
		$authenticated = True;
	}
	session_write_close();
		
	if ($authenticated){
		$authContextFactory = new D2LAppContextFactory();
		$authContext = $authContextFactory->createSecurityContext($appId, $appKey);
		$opContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);

		$apiRequest = '/d2l/api/lp/1.0/users/whoami';
		$apiMethod = 'GET';
		$uri = $opContext->createAuthenticatedUri($apiRequest, $apiMethod);

		$ch = setCurlOptions($uri);
		$results = runCurlRequest($ch, $apiMethod, $opContext);

		$userData = json_decode($results, true);

		echo "Welcome: ".$userData['UniqueName'];
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head></head>
	<body>
		<title>Desire2Learn Auth SDK Sample</title>
		<form method="get" action="authenticateUser.php" id="configForm">
		    <input type="submit" name="authBtn" value="Authenticate" id="authenticateBtn" /><br>
		    <input type="submit" name="authBtn" value="Deauthenticate" id="deauthBtn">
		</form>
	</body>
</html>