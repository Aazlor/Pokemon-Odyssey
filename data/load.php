<?

include_once 'config.php';

$mysqli->select_db('odyssey_trainers');

$file = './trainers/'.$_SESSION[id].'/team.json';
$team = json_decode(file_get_contents($file));


// if($debug === true)
	unset($team);



if(empty($team)){
	$trainer = 'player_'.$_SESSION[id];											///////	REPLACE WITH TRAINER AUTHENTICATED ID
	$query = "SELECT * FROM $trainer WHERE location = 0 LIMIT 6";
	$get = $mysqli->query($query);
	while($a = $get->fetch_assoc()){
		if($a[shiny] == 0)
			$a[shiny] = '';
		else
			$a[shiny] = 's';
		$a[movelist] = json_decode($a[movelist], true);
		$a[totals] = json_decode($a[totals], true);
		$a[ivs] = json_decode($a[ivs], true);
		$a[evs] = json_decode($a[evs], true);
		$a[movelist] = json_decode($a[movelist], true);
		$a[moveset] = json_decode($a[moveset], true);
		$a[HP] = $a[totals][0];

		$team[] = $a;
	}

	$fp = fopen($file, 'w+');
	fwrite($fp, json_encode($team));
	fclose($fp);
}

?>