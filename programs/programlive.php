<?php
/**
 * /srv/http/metern/programs/programlive.php
 *
 * @package default
 */

$LIVEMEMORY  = '/dev/shm/mN_LIVEMEMORY.json';
$ILIVEMEMORY = '/dev/shm/mN_ILIVEMEMORY.json';

define('checkaccess', TRUE);
include '../config/config_main.php';
include '../config/memory.php';
date_default_timezone_set($DTZ);

if (file_exists($LIVEMEMORY)) {
	$data     = file_get_contents($LIVEMEMORY);
	$array = json_decode($data, true);
	$array['stamp'] = date("H:i:s");
	if (isset($array['UTC'])) {
		$nowutc = strtotime(date('Ymd H:i:s'));
		if ($nowutc - $array['UTC'] > 15) {
			for ($i = 1; $i <= $NUMMETER; $i++) {
				include "../config/config_met$i.php";
				$array["${'METNAME'.$i}$i"] = 0;
			}
			$array['stamp'] .= ' - communication lost';
		}
		header("Content-type: application/json");
		echo json_encode($array);
	}
}
?>
