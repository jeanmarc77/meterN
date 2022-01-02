<?php
if(!defined('checkaccess')){die('Direct access not permitted');}
// Manage com. apps daemon as 'http' user if needed
if (is_null($PID)) { // Stop Daemon
//$out = exec("com_daemon -stop");
} else { //Start
//$out = exec("com_daemon -start");
}
?>
