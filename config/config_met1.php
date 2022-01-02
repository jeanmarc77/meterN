<?php
if (!defined('checkaccess')) {
    die('Direct access not permitted');
}

// ### CONFIG FOR METER #1

$METNAME1        = "Conso";
$TYPE1           = 'Elect';
$PROD1           = 2;
$PHASE1          = 1;
$SKIPMONITORING1 = false;
$ID1             = "elect";
$COMMAND1        = "houseenergy -energy";
$UNIT1           = "Wh";
$PRECI1          = 0;
$PASSO1          = 100000;
$COLOR1          = '962629';
$PRICE1          = 0.23;
$LID1            = "elect";
$LIVECOMMAND1    = "houseenergy -power";
$LIVEUNIT1       = "W";
$EMAIL1          = "";
$POAKEY1         = '';
$POUKEY1         = '';
$TLGRTOK1		 = '';
$TLGRCID1		 = '';
$WARNCONSOD1     = 15000;
$NORESPM1        = true;

$cfgver=1580629442;
?>
