<?php
/**
 * /srv/http/metern/programs/programlastdays.php
 *
 * @package default
 */

define('checkaccess', TRUE);
include '../config/config_main.php';
include '../config/config_layout.php';
date_default_timezone_set('UTC');;

$dir      = '../data/meters/';
$output = array();
$output   = glob($dir . '*.csv');
$cntmeter = count($output);
sort($output);

$unitlist = array();
$yaxis    = 0;
$cnt      = 0;

for ($metnum = 1; $metnum <= $NUMMETER; $metnum++) {
	include "../config/config_met$metnum.php";
	if (!isset (${'LASTD_MET' . $metnum})) {
		${'LASTD_MET' . $metnum} = false;
	}
	if (${'LASTD_MET' . $metnum}) {
		$cnt++;
	}
}

$datanum=0;
for ($metnum = 1; $metnum <= $NUMMETER; $metnum++) {
	$PRODXDAYS = 15;

	if (${'LASTD_MET' . $metnum}) {
		$SYSlist = array();
		$stack   = array();
		$srch = $metnum .preg_quote(${'METNAME'.$metnum});

		for ($i = 0; $i < $cntmeter; $i++) {
			if (preg_match("/$srch/", $output[$i])) {
				array_push($SYSlist, $output[$i]);
			}
		}

		sort($SYSlist);
		$cnt = count($SYSlist);

		$j       = 0;
		$h       = 1; // which year file to takes
		$day_num = 0;

		while ($day_num < $PRODXDAYS) { // Digging
			if (($cnt - $h) >= 0) { // file exist

				$lines       = file($SYSlist[$cnt - $h]);
				$countalines = count($lines);
				$array       = preg_split("/,/", $lines[$countalines - $j - 1]);
				if (isset($array[1])){
					$year    = substr($SYSlist[$cnt - $h], -8, 4);
					$month   = substr($array[0], 4, 2);
					$day     = substr($array[0], 6, 2);
					$UTCdate = strtotime($year . '-' . $month . '-' . $day);
					$UTCdate *= 1000;
					settype($array[1], 'float');
					if (${'TYPE' . $metnum} == 'Elect') {
						$array[1]/=1000;
					}

					$stack[$day_num] = array(
						$UTCdate,
						$array[1]
					);
					$day_num++;
				}
				$j++;

				if ($countalines == $j) {
					if ($h < $cnt) {
						$h++;
						$lines       = file($SYSlist[$cnt - $h]); //Takes older file
						$countalines = count($lines);
						$j           = 0;
					} else {
						$PRODXDAYS = $day_num; //Stop
					}
				}
			} else {
				$yesterd   = ((strtotime(date('Ymd')) - 86400) * 1000);
				$stack[0]  = array(
					$yesterd,
					0
				);
				$PRODXDAYS = $day_num; //Stop
			} // file exist
		} // digging

		if (!in_array(${'UNIT' . $metnum}, $unitlist)) {
			$unitlist[] = ${'UNIT' . $metnum};
			$yaxis++;
		}

		sort($stack);
		$cntunit = count($unitlist);
		for ($h = 0; $h < $cntunit; $h++) {
			if (${'UNIT' . $metnum} == $unitlist[$h]) {
				$thisyaxis = $h;
			}
		}

		$data[$datanum] = array(
			'name' => "${'METNAME'.$metnum}",
			'color' => "#${'COLOR'.$metnum}",
			'yAxis' => $thisyaxis,
			'data' => $stack
		);
		$datanum++;
	} // if show
} // for each meter

$jsonreturn = array(
	'data' => $data
);

header("Content-type: application/json");
echo json_encode($jsonreturn);
?>