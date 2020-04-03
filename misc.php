<?php

$dir = 'sprites/icons/';
$files = scandir($dir);

// print_r($files);

foreach ($files as $key => $value) {
	if(stristr($value, 'icon')){
		// $new = str_replace('_.*', '', $value);
		$new = preg_replace('/\_.*/', '', $value);
		echo strtolower($new).'<br>';
		rename($dir.$value, $dir.$new.'.png');
	}
}


?>