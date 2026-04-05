<?php
/**
 * /srv/http/metern/scripts/links.php
 *
 * @package default
 */

if (false) { // Set to true if you wish to use local lib
	$HC     = 'js/highcharts/highcharts.js';
	$HCmore = 'js/highcharts/highcharts-more.js';
	$HCdd   = 'js/highcharts/modules/drilldown.js';
	$HCexp  = 'js/highcharts/modules/exporting.js';
	$HCann  = 'js/highcharts/annotations.js';
	$JSjquery = 'js/jquery/jquery-3.6.0.min.js';
	$JSjqui   = 'js/jquery/jquery-ui.min.js';
	$JSjquit  = 'js/jquery/jquery-ui.css';
} else {
	$JSjquery = "https://code.jquery.com/jquery-3.7.1.min.js' integrity='sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=' crossorigin='anonymous";
	$JSjqui   = "https://code.jquery.com/ui/1.14.1/jquery-ui.min.js' integrity='sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=' crossorigin='anonymous";
	$JSjquit  = "https://code.jquery.com/ui/1.14.1/themes/south-street/jquery-ui.css";
	$HC       = "https://code.highcharts.com/highcharts.js";
	$HCmore   = 'https://code.highcharts.com/highcharts-more.js';
	$HCdd     = 'https://code.highcharts.com/modules/drilldown.js';
	$HCexp    = 'https://code.highcharts.com/modules/exporting.js';
	$HCann    = 'https://code.highcharts.com/modules/annotations.js';
}

?>
