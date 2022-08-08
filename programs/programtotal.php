<?php
/**
 * /srv/http/metern/programs/programtotal.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config_main.php';
include '../config/memory.php';

if (file_exists($MEMORY)) {
	$memdata  = file_get_contents($MEMORY);
	$memarray = json_decode($memdata, true);

	for ($i = 1; $i <= $NUMMETER; $i++) {
		include "../config/config_met$i.php";

		if (${'TYPE' . $i} != 'Sensor' && !empty($memarray["First$i"]) && !empty($memarray["Last$i"])) { // meter
			// total
			if (${'TYPE' . $i} == 'Elect') {
				if ($memarray["Last$i"] <= 1000) {
					$prefix  = '';
					$val_tot = number_format($memarray["Last$i"], 0, $DPOINT, $THSEP);
				} elseif ($memarray["Last$i"] > 1000) {
					$val_tot = $memarray["Last$i"] / 1000;
					$prefix  = 'k';
					$val_tot = number_format($val_tot, 3, $DPOINT, $THSEP);
				}
			} else {
				$val_tot = number_format($memarray["Last$i"], ${'PRECI' . $i}, $DPOINT, $THSEP);
				$prefix  = '';
			}
			$data["Totalcounter$i"] = $val_tot.$prefix;
			// daily
			if ($memarray["First$i"] <= $memarray["Last$i"]) {
				$val_last = $memarray["Last$i"] - $memarray["First$i"];
			} else { // counter pass over
				$val_last = $memarray["Last$i"] + ${'PASSO' . $i} - $memarray["First$i"];
			}

			if (${'TYPE' . $i} == 'Elect') {
				if ($val_last <= 1000) {
					$prefix   = '';
					$val_last = number_format($val_last, 0, $DPOINT, $THSEP);
				} elseif ($val_last > 1000) {
					$val_last /= 1000;
					$prefix   = 'k';
					$val_last = number_format($val_last, 1, $DPOINT, $THSEP);
				}
			} else {
				$val_last = number_format($val_last, ${'PRECI' . $i}, $DPOINT, $THSEP);
				$prefix   = '';
			}
			$data["Dailycounter$i"] = $val_last.$prefix;
		} else {
			$data["Totalcounter$i"] = '--';
			$data["Dailycounter$i"] = '--';
		}
	}
} else {
	for ($i = 1; $i <= $NUMMETER; $i++) {
		$data["Lastcounter$i"]  = '--';
		$data["Dailycounter$i"] = '--';
	}
}
header("Content-type: application/json");
echo json_encode($data);
?>
