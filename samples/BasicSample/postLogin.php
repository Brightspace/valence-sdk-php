<?php
if (isset($_GET['x_a']) && isset($_GET['x_b'])) {
	session_start();
	$_SESSION['userId'] = $_GET['x_a'];
	$_SESSION['userKey']= $_GET['x_b'];
	session_write_close();
	header("Location: index.php");
} else {
    throw new Exception('If you are seeing this page you probably navigated here directly. ' .
                        'The LMS redirects the user to this page on succesful login, passing the user credentials in the x_a, x_b query parameters.');
}
