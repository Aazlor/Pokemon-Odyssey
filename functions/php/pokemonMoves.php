<?

require_once "../../config.php";


function getPokeData(){
	$trainer = '../../trainers/'.$_SESSION[id].'/team.json';
	$pokes = json_decode(file_get_contents($trainer), true);
	return $pokes;
}

$mysqli->select_db('odyssey_staticdata');

if($_POST[data][type] == 'learnMove'){

	$pokes = getPokeData();

	foreach($pokes as $key => $value) {
		if(in_array($_POST[data][poke], $value)){
			$poke = $value;
			$poke_team_no = $key;
			break;
		}
	}

	$query = "SELECT movelist FROM pokemon WHERE id = '$poke[number]'";
	$getMoveList = json_decode($mysqli->query($query)->fetch_object()->movelist, true);

	$new_moves = explode(', ',$getMoveList[$_POST[data][level]]);

	foreach($new_moves as $v){
		if(!in_array($v, $poke[moveset])){
			$poke[moveset][] = $v;
		}		
	}


	$query = "SELECT * FROM moves WHERE name IN ('".implode('\',\'', array_merge($new_moves, $poke[moveset]))."')";
	$get = $mysqli->query($query);
	while($z = $get->fetch_assoc()){
		$moves[$z[name]] = $z;
	}

	if( count($poke[moveset]) <= 4 ){
		foreach($new_moves as $v){
			$returnData['function'] = 'insert';
			$returnData['display'][] = ['type' => 'text', 'text' => $poke[nickname].' learned <i>'.$v.'</i>.'];
		}
	}
	else{
		$returnData['text'] = $poke[nickname].' wants to learn a new move but knows too many.';

		foreach($poke[moveset] as $key => $v){
			$move = $moves[$v];
			$moveSet .= '<div class="Move '.$move[type].'" data-id="'.$move[name].'" data-type="'.$move[type].'" data-category="'.$move[category].'" data-power="'.$move[power].'" data-accuracy="'.$move[accuracy].'" data-desc="'.$move[flavor].'">'.$move[name].'</div>';
		}
		$returnMoveSet = '<div id="MoveSelector"><div class="Explanation">Select a move to forget.</div><div class="Desc"></div><div class="List">'.$moveSet.'</div><div class="Clear"></div></div>';

		$returnData['function'] = 'select';
		$returnData['display'] = ['type' => 'movelist', 'object' => $returnMoveSet];
	}

	$pokes[$poke_team_no] = $poke;

	//SAVE RESULTS
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$fp = fopen($trainerFile, 'w+');
	fwrite($fp, json_encode($pokes));
	fclose($fp);

	//RETURN DATA
	echo json_encode($returnData);
}
else{
	$pokes = getPokeData();

	foreach($pokes as $key => $value) {
		if(in_array($_POST[poke_info][poke], $value)){
			$poke = $value;
			$poke_team_no = $key;
			break;
		}
	}

	$returnData['text'] = $poke[nickname].' forgot the move <i>'.$_POST[move].'</i>.';

	if(($key = array_search($_POST[move], $poke[moveset])) !== false) {
		unset($poke[moveset][$key]);
	}

	$poke[moveset] = array_values($poke[moveset]);

	$pokes[$poke_team_no] = $poke;

	//SAVE RESULTS
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$fp = fopen($trainerFile, 'w+');
	fwrite($fp, json_encode($pokes));
	fclose($fp);

	//RETURN DATA
	echo json_encode($returnData);
}


?>