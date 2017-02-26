<?php

function formatBytes($bytes) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	// Uncomment one of the following alternatives
	$bytes /= pow(1024, $pow);
	// $bytes /= (1 << (10 * $pow));

	$precision = 2;
	if($bytes >= 10){
		$precision = 1;
	}
	if($bytes >= 100){
		$precision = 0;
	}

	return round($bytes, $precision) . ' ' . $units[$pow];
}

?>