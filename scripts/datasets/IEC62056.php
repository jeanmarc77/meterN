<?php
function isvalid($id, $datareturn) //  IEC 62056 data set structure
{
	$regexp = "/^$id\(.+\*.+\)$/i"; //ID(VALUE*UNIT)
	if (preg_match($regexp, $datareturn)) {
		$datareturn = preg_replace("/^$id\(/i", '', $datareturn, 1); // VALUE*UNIT)
		$datareturn = preg_replace("/\*.+\)$/i", '', $datareturn, 1); // VALUE
	} else {
		$datareturn = null;
	}
	return $datareturn;
}
?>
