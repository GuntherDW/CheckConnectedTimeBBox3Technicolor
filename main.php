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
require_once('DLM.php');
require_once('math.php');

$bbox3 = new bbox3($ip, $user, $password);
echo "Generating session key/cookie\n";
$bbox3->initSessionKey();
echo "Logging in...\n";
$bbox3->login();

echo "Getting main page...\n";
$bbox3->getMainPage();

echo "Getting device info page...\n";
$bbox3->getDeviceInfo();

echo "Removing session key/cookie\n";
$bbox3->closeSession();


echo "We are connected for " . $bbox3->getConnectedSeconds() . " seconds!\n";


$math = new math();
echo "Prettified : ".$math->sectoday($bbox3->getConnectedSeconds())."\n";

$dlm = new DLM();
$DLM_CHECK_TIME = $dlm->getEstimateToNextCheck($bbox3->getConnectedSeconds());

$secondsToDLM = $DLM_CHECK_TIME - $bbox3->getConnectedSeconds();

echo "Seconds to new DLM check : " . $secondsToDLM . "\n";
echo "Prettified : ".$math->sectoday($secondsToDLM)."\n";


?>