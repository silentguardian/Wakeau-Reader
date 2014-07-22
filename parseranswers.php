<?php

$text = '';

preg_match_all('~(\d+) ([abcd])~', $text, $matches);

$keys = array();
foreach ($matches[0] as $key => $dummy)
	$keys[$matches[1][$key]] = $matches[2][$key];
ksort($keys);

echo count($keys);

echo '<pre>';
foreach ($keys as $k => $v)
	echo $k . ' ' . $v . "\n";
echo '</pre>';

exit();