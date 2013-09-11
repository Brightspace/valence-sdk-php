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
