<?php
// Make sure you only use a tmpfs. Don't put a / at the end of the variable path. 
$TMPFS = '/dev/shm';
// If you use several instances of mN you need to set different memory files for each of them
// live things
$LIVEMEMORY  = $TMPFS . '/mN_LIVEMEMORY.json';
$ILIVEMEMORY = $TMPFS . '/mN_ILIVEMEMORY.json';
// 5minflag + total counters + mail Q
$MEMORY      = $TMPFS . '/mN_MEMORY.json';
?>
