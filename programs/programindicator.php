<?php
/**
 * /srv/http/metern/programs/programindicator.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/memory.php';

if (file_exists($ILIVEMEMORY)) {
	$data     = file_get_contents($ILIVEMEMORY);
	$array = json_decode($data, true);
	header("Content-type: application/json");
	echo json_encode($array);
}
?>
