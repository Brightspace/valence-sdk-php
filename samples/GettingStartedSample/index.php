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

require_once 'lib/D2LAppContextFactory.php';

session_start();

/**
 * App ID, APP key, user ID and user key should all be unavailable to the end user of the application in real world scenarios
 */
if (isset($_SESSION['appId'])) {
    $appId = $_SESSION['appId'];
} else {
    // default Application ID
    $appId = 'G9nUpvbZQyiPrk3um2YAkQ';
    $_SESSION['appId']= $appId;
}

if (isset($_SESSION['appKey'])) {
    $appKey = $_SESSION['appKey'];
} else {
    // default Application key
    $appKey = 'ybZu7fm_JKJTFwKEHfoZ7Q';
    $_SESSION['appKey'] = $appKey;
}

if (isset($_SESSION['host'])) {
    $host = $_SESSION['host'];
} else {
    $host="valence.desire2learn.com";
}

if (isset($_SESSION['port'])) {
    $port = $_SESSION['port'];
} else {
    $port=443;
}

if (isset($_SESSION['scheme'])) {
    $scheme = $_SESSION['scheme'];
} else {
    $scheme = 'https';
}

$authContextFactory = new D2LAppContextFactory();
$authContext = $authContextFactory->createSecurityContext($appId, $appKey);
$hostSpec = new D2LHostSpec($host, $port, $scheme);
$opContext = $authContext->createUserContextFromHostSpec($hostSpec, null, null, $_SERVER["REQUEST_URI"]);

if ($opContext!=null) {
    $userId = $opContext->getUserId();
    $userKey = $opContext->getUserKey();
    $_SESSION['userId'] = $userId;
    $_SESSION['userKey'] = $userKey;
} elseif (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    if (isset($_SESSION['userKey'])) {
        $userKey = $_SESSION['userKey'];
    } else {
        $userKey = '';
    }
} else {
    $userId = '';
    $userKey = '';
}


session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Desire2Learn Auth SDK Sample</title>
    <style type = "text/css">
        table.plain
        {
          border-color: transparent;
          border-collapse: collapse;
        }

        table td.plain
        {
          padding: 5px;
          border-color: transparent;
        }

        table th.plain
        {
          padding: 6px 5px;
          text-align: left;
          border-color: transparent;
        }

        tr:hover
        {
            background-color: transparent !important;
        }

        .error
        {
            color: #FF0000;
        }
    </style>
    <script src="sample.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type = "text/javascript"></script>
</head>
<body>
    <span id="errorField1" class="error" hidden="true">Error: </span><span id="errorField2"></span>
    <form method="get" action="authenticateUser.php" id="configForm">
    <input type="submit" name="authBtn" value="Load Defaults" id="resetButton" />
    <hr />
    <table>
        <tr>
            <td>
                <b>Host: </b>
            </td>
            <td>
                <input name="hostField" type="text" style="width:20em" value="<?php echo $host; ?>" id="hostField" />
            </td>
            <td>
                <b>Port:</b>
            </td>
            <td>
                <input name="portField" type="text" style="width:20em" value="<?php echo $port; ?>" id="portField" />
            </td>
            <td>
                <input id="schemeField" type="checkbox" name="schemeField" <?php echo $scheme == 'https' ? 'checked="true"' : '';?> />
                HTTPS
            </td>
        </tr>
        <tr>
            <td>
                <b>App ID:</b>
            </td>
            <td>
                <input name="appIDField" type="text" style="width:20em" value="<?php echo $appId; ?>" id="appIDField" />
            </td>
            <td>
                <b>App Key:</b>
            </td>
            <td>
                <input name="appKeyField" type="text" style="width:20em" value="<?php echo $appKey; ?>" id="appKeyField" />
            </td>
        </tr>
    </table>
    <div id="userDiv">
        <br />
        <span>This information is returned by the authentication server and is valid only for this application:</span>
        <table>
            <tr>
                <td>
                    <b>User ID:</b>
                </td>
                <td>
                    <input type="text" name="userIDField" id="userIDField" style="width:20em" value="<?php echo $userId; ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <b>User Key:</b>
                </td>
                <td>
                    <input type="text" name="userKeyField" id="userKeyField" style="width:20em" value="<?php echo $userKey; ?>" />
                </td>
            </tr>
        </table>
        <input type="submit" name="authBtn" value="Deauthenticate" id="deauthBtn">
    </div>
    <span id="authNotice">Note: to authenticate against the test server, you can user username
                          "sampleapiuser" and password "Tisabiiif".
    </span><br />
    <input type="submit" name="authBtn" value="Authenticate" id="authenticateBtn" /><br>
    <input type="button" id="manualBtn" value="Manually set credentials" onclick="setCredentials()" />
    <input type="submit" name="authBtn" value="Save" id="manualAuthBtn" hidden=true />
    </form>

    <hr />
    <table>
        <tr>
            <td>
                <b>Examples:</b>
            </td>
            <td>
                <button type="button" onclick="exampleGetVersions()">
                    Get Versions</button>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="button" onclick="exampleWhoAmI()">
                    WhoAmI</button>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="button" onclick="exampleCreateUser()">
                    Create User</button>
            </td>
        </tr>
    </table>
    <br />
    <b>Method:</b>&nbsp;
    <input value="GET" name="method" type="radio" id="GETField" checked="checked" onclick="hideData()" />GET
    &nbsp;
    <input value="POST" name="method" type="radio" id="POSTField" onclick="showData()" />POST
    &nbsp;
    <input value="PUT" name="method" type="radio" id="PUTField" onclick="showData()" />PUT
    &nbsp;
    <input value="DELETE" name="method" type="radio" id="DELETEField" onclick="hideData()" />DELETE<br />
    <b>Action:</b>&nbsp;<input name="actionField" type="text" id="actionField" style="width:600px;" /><br />
    <b id="dataFieldLabel">Data:</b><br />
    <textarea name="dataField" rows="2" cols="20" id="dataField" style="height:400px;width:600px;">
</textarea><br />
    <b id="responseFieldLabel" hidden=true>Response:</b><br />
    <textarea name="responseField" hidden=true rows="2" cols="20" id="responseField" style="height:400px;width:600px;">
</textarea><br />
    <input type="button" name="submitButton" value="Submit" id="submitButton" onclick="doAPIRequest()"/>

</body>
<script type="text/javascript">
    function showData() {
        document.getElementById("dataFieldLabel").hidden = false;
        document.getElementById("dataField").hidden=false;
    }

    function hideData() {
        document.getElementById("dataFieldLabel").hidden = true;
        document.getElementById("dataField").hidden=true;
    }

    function exampleGetVersions() {
        hideData();
        document.getElementById("GETField").checked = true;
        document.getElementById("actionField").value = "/d2l/api/versions/";
    }

    function exampleWhoAmI() {
        hideData();
        document.getElementById("GETField").checked = true;
        document.getElementById("actionField").value = "/d2l/api/lp/1.0/users/whoami";
    }

    function exampleCreateUser() {
        showData();
        document.getElementById("POSTField").checked = true;
        document.getElementById("actionField").value = "/d2l/api/lp/1.0/users/";
        document.getElementById("dataField").value = "{\n  \"OrgDefinedId\": \"<string>\",\n  \"FirstName\": \"<string>\",\n  \"MiddleName\": \"<string>\",\n  \"LastName\": \"<string>\",\n  \"ExternalEmail\": \"<string>|null\",\n  \"UserName\": \"<string>\",\n  \"RoleId\": \"<number>\",\n  \"IsActive\": \"<boolean>\",\n  \"SendCreationEmail\": \"<boolean>\"\n}";
    }

    function setCredentials() {
        document.getElementById("manualAuthBtn").hidden = false;
        document.getElementById("deauthBtn").hidden = true;
        document.getElementById("userDiv").hidden = false;
        document.getElementById("userIDField").hidden = false;
        document.getElementById("userKeyField").hidden = false;
        document.getElementById("manualBtn").hidden = true;
        document.getElementById("authenticateBtn").hidden = true;
        document.getElementById("authNotice").hidden = true;
    }

    hideData();

    if(document.getElementById("userIDField").value != "") {
        document.getElementById("userIDField").disabled = true;
        document.getElementById("userKeyField").disabled = true;
        document.getElementById("manualBtn").hidden = true;
        document.getElementById("authenticateBtn").hidden = true;
        document.getElementById("authNotice").hidden = true;
        document.getElementById("hostField").disabled = true;
        document.getElementById("portField").disabled = true;
        document.getElementById("appKeyField").disabled = true;
        document.getElementById("appIDField").disabled = true;
    } else {
        document.getElementById("userIDField").hidden = true;
        document.getElementById("userKeyField").hidden = true;
        document.getElementById("userDiv").hidden = true;
        document.getElementById("hostField").disabled = false;
        document.getElementById("portField").disabled = false;
        document.getElementById("appKeyField").disabled = false;
        document.getElementById("appIDField").disabled = false;
    }

    $("body").ajaxError(function(e, request) {
        console.log("AJAX error!");
    });
</script>
</html>
