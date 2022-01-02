<?php
function isvalid($id, $datareturn) // json
{
	$json = json_decode($datareturn, true);
	if (json_last_error() == JSON_ERROR_NONE) {
		if (isset($json[$id])) {
			$datareturn = $json[$id];
		} else {
			$datareturn = null;
		}
	} else {
		$datareturn = null;
	}
	return $datareturn;
}
?>
