<?php
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
