<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

date_default_timezone_set('America/Detroit');

if(!function_exists(pre)){
	function pre($array){
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
}

if(!function_exists(parse)){
	function parse($string){
		$x = explode('{{}}', $string);
		foreach($x as $v){
			if($v != ''){
				$s = explode('(())', $v);
				if($s != ''){
					$parsed_data[$s[0]] = $s[1];
				}
			}
		}
		return($parsed_data);
	}
}

if(!function_exists(rutime)){
	function rutime($ru, $rus, $index) {
		return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
			-  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
	}
}

if(!function_exists(getIndex)){
	function getIndex($array, $key, $value){
		foreach($array as $k => $v){
			if(is_array($v) && $v[$key] == $value){
				return $k;
			}
		}
		return null;
	}
}

/***************************************************************************************************************************************************************************************************/
/* UPDATE ME */
$hostname = 'localhost';
$db_name = '';
/***************************************************************************************************************************************************************************************************/

$db_user = '';
$db_pass = '';

$timeout = 5;  /* thirty seconds for timeout */
$mysqli = mysqli_init();
$mysqli->options( MYSQLI_OPT_CONNECT_TIMEOUT, $timeout );
$mysqli->real_connect("localhost", "$db_user", "$db_pass");

// $db_select = $mysqli->select_db($db_name);

session_start();
$_SESSION['id'] = 1;


$debug = true;

?>