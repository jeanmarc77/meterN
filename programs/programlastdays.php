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
$unitlist = array();
$SYSlist = array();
$yaxis    = 0;
$cnt      = 0;

for ($metnum = 1; $metnum <= $NUMMETER; $metnum++) {
	include "../config/config_met$metnum.php";
	if (!isset (${'LASTD_MET' . $metnum})) {
		${'LASTD_MET' . $metnum} = false;
	}
	if (${'LASTD_MET' . $metnum}) {
		array_push($SYSlist, $metnum);
		$cnt++;
	}
}
sort($SYSlist);
$cnt = count($SYSlist);

$datanum=0;
$yesterd = ((strtotime(date('Ymd')) - 86400) * 1000);

for ($i = 0; $i < $cnt; $i++) {
	$stack   = array();
	$metnum = $SYSlist[$i];
	$PRODXDAYS = 15;
	$thisyear = date('Y',strtotime('-1 days'));

	$j       = 0;
	$day_num = 0;

		while ($day_num < $PRODXDAYS) { // Digging
			$filename    = $dir . $metnum . ${'METNAME'.$metnum} . $thisyear . '.csv';
			if (file_exists($filename)) { // file exist
				$lines       = file($filename);
				$countalines = count($lines);
				$array       = preg_split("/,/", $lines[$countalines - $j - 1]);
				if (isset($array[1])){
					$year  = substr($array[0], 0, 4);
					$month   = substr($array[0], 4, 2);
					$day     = substr($array[0], 6, 2);
					$UTCdate = ((strtotime($year . '-' . $month . '-' . $day))*1000);
	
					if ($yesterd - $UTCdate < (86400000 * 20)) {
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
				}
				$j++;

				if ($countalines == $j || $j == $PRODXDAYS) {
					if ($thisyear == date('Y')) {
						$thisyear--; //Takes older file
						$j = 0;
					} else {
						$PRODXDAYS = $day_num; //Stop
					}
				}
			} else {
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
} // for each meter

$jsonreturn = array(
	'data' => $data
);

header("Content-type: application/json");
echo json_encode($jsonreturn);
?>
