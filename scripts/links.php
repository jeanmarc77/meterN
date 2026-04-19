<?php
/**
 * /srv/http/metern/scripts/links.php
 *
 * @package default
 */

$JSjquery = "https://code.jquery.com/jquery-4.0.0.min.js' integrity='sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=' crossorigin='anonymous";
$JSjqui   = "https://code.jquery.com/ui/1.14.2/jquery-ui.min.js' integrity='sha256-mblSWfbYzaq/f+4akyMhE6XELCou4jbkgPv+JQPER2M=' crossorigin='anonymous";
$JSjquit  = "https://code.jquery.com/ui/1.14.2/themes/south-street/jquery-ui.css";
	
if (true) { // Set to true if you wish to use Highcharts local lib
	$HC     = 'js/highcharts/highcharts.js';
	$HCmore = 'js/highcharts/highcharts-more.js';
	$HCdd   = 'js/highcharts/modules/drilldown.js';
	$HCexp  = 'js/highcharts/modules/exporting.js';
	$HCann  = 'js/highcharts/modules/annotations.js';
} else {

	$HC       = "https://code.highcharts.com/highcharts.js";
	$HCmore   = 'https://code.highcharts.com/highcharts-more.js';
	$HCdd     = 'https://code.highcharts.com/modules/drilldown.js';
	$HCexp    = 'https://code.highcharts.com/modules/exporting.js';
	$HCann    = 'https://code.highcharts.com/modules/annotations.js';
}

?>
