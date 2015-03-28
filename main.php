<?php
/**
 * Created by PhpStorm.
 * User: guntherdw
 * Date: 27/03/15
 * Time: 21:59
 */

require_once('config.php');
// Configuration done
require_once('bbox3.php');

$bbox3 = new bbox3($ip, $user, $password);
echo "Generating session key/cookie\n";
$bbox3->initSessionKey();
echo "Logging in...\n";
$bbox3->login();

echo "Removing session key/cookie\n";
$bbox3->closeSession();

?>