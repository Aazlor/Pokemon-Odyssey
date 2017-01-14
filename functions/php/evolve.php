<?
require_once "../../config.php";
require_once "spoils.php";


function getPokeData(){
	$trainer = '../../trainers/'.$_SESSION[id].'/team.json';
	$pokes = json_decode(file_get_contents($trainer), true);
	return $pokes;
}

$mysqli->select_db('odyssey_staticdata');


if($_POST[action] == 'evolve'){
	$pokes = getPokeData();

	foreach($pokes as $key => $value) {
		if(in_array($_POST[poke], $value)){
			$poke = $value;
			$poke_team_no = $key;
			break;
		}
	}

	$no = $poke[number];
	$query = "SELECT * FROM pokemon WHERE `id` = '$no' LIMIT 1";
	$defaults = $mysqli->query($query)->fetch_assoc();

	$query = "SELECT * FROM pokemon WHERE `id` = '$_POST[to]' LIMIT 1";
	$evolution = $mysqli->query($query)->fetch_assoc();

	$query = "SELECT $evolution[lvl_rate] as EXP FROM exp WHERE level = '$poke[level]'";
	$exp = $mysqli->query($query)->fetch_object()->EXP;

	///UPDATE Poke XP TO LEVEL
	$poke[exp_to_lvl] = $exp;

	///UPDATE Poke No.
	$poke[number] = $evolution[id];

	///UPDATE Poke Name
	$poke[name] = $evolution[name];

	///UPDATE Poke Type
	$poke[type] = $evolution[type];

	///Check Abilities
	$abilities = explode('/', $defaults[abilities]);
	foreach($abilities as $k => $v){
		if($v == $poke[ability]){
			$ability_no = $k;
			break;
		}
	}
	$abilities = explode('/', $evolution[abilities]);
	$poke[ability] = $abilities[$ability_no];

	///UPDATE STATS
	$poke = updateTotals($poke);
	$poke[HP] = $poke[totals][0];

	$pokes[$poke_team_no] = $poke;

	//SAVE RESULTS
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$fp = fopen($trainerFile, 'w+');
	fwrite($fp, json_encode($pokes));
	fclose($fp);

	echo json_encode($poke);
}


?>