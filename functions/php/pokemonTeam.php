<?
require_once "../../config.php";

if($_POST[action] == 'get'){
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$team = file_get_contents($trainerFile);

	echo $team;
}
elseif($_POST[action] == 'viewStats'){
	$mysqli->select_db('odyssey_staticdata');

	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$team = json_decode(file_get_contents($trainerFile), true);

	$poke = $team[$_POST[poke]];

	$types = explode('/', $poke[type]);
	foreach($types as $v){
		$showTypes .= ' <img src="./sprites/icons/'.strtolower($v).'.png" alt="'.$v.'" title="'.$v.'">';
	}
	
	$query = "SELECT * FROM moves WHERE name IN ('".implode("','", $poke[moveset])."')";
	$get = $mysqli->query($query) or die($mysqli->error);
	while($z = $get->fetch_assoc()){
		$moves[$z[name]] = $z;
	}

	if(empty($poke[evs]))
		$poke[evs] = [0,0,0,0,0,0];

	$data_set = [
		'Pok&eacute;dex' => $poke[number],
		'Type' => $showTypes,
		'Nature' => $poke[nature],
		'Ability' => $poke[ability],
		'HP' => $poke[HP].' / '.$poke[totals][0].' <b>('.$poke[ivs][0].')</b> <i>('.$poke[evs][0].')</i>',
		'Atk' => $poke[totals][1].' <b>('.$poke[ivs][1].')</b> <i>('.$poke[evs][1].')</i>',
		'Def' => $poke[totals][2].' <b>('.$poke[ivs][2].')</b> <i>('.$poke[evs][2].')</i>',
		'Spec. Atk' => $poke[totals][3].' <b>('.$poke[ivs][3].')</b> <i>('.$poke[evs][3].')</i>',
		'Spec. Def' => $poke[totals][4].' <b>('.$poke[ivs][4].')</b> <i>('.$poke[evs][4].')</i>',
		'Speed' => $poke[totals][5].' <b>('.$poke[ivs][5].')</b> <i>('.$poke[evs][5].')</i>',
		'Happiness' => $poke[happiness],
		'Exp' => $poke[exp].' /'.$poke[exp_to_lvl],
		'OT' => $poke[caught_by],
	];

	$return = '
	<div id="PokeStats" data-id="'.$_POST[poke].'">

		<div class="Pokeball"><img src="./sprites/icons/pokeball_open.png"><div class="Shadow"></div></div>

		<div class="Left">
			<div class="Poke">
				<div class="ShadowBox">
					<img src="./sprites/pokemon/battle/'.$poke[number].'/front.gif">
					<div class="Shadow"></div>
				</div>
			</div>';
			
			if($poke[nickname] != '' && $poke[nickname] != $poke[name]){
				$return .= '<div class="Nickname">'.$poke[nickname].'</div>';
			}
			$return .= '
			<div class="Name Nickname">Lv '.$poke[level].' '.$poke[name].'</div>

			<div class="MoveSet">
				';
				foreach ($poke[moveset] as $k => $v) {
					$value = $moves[$v];
					// $value[accuracy] = ($value[accuracy] > 0) ? $value[accuracy] * 100 .'%' : $value[accuracy];
					$value[accuracy] = ($value[accuracy] > 0) ? $value[accuracy] .'%' : $value[accuracy];
					$return .= '<div class="Move '.$value[type].'" data-key="'.$key.'"><img src="./sprites/icons/'.strtolower($value[type]).'.png" class="type" />'.$value[name].'<img src="./sprites/icons/'.strtolower($value[category]).'.png" alt="'.$value[category].'" title="'.$value[category].'"><div class="Desc"><div><span>Power</span> '.$value[power].'</div><div><span>Accuracy</span> '. $value[accuracy] .'</div><div><p>'.$value[flavor].'</p></div></div></div>';
				}
				$return .= '
			</div>

		</div>

		<div class="Right">
		';
		foreach($data_set as $k => $v){
			$rand = rand(0, count($types)-1);
			$return .= '
			<div>
				<span class="Label '.$types[$rand].'">'.$k.'</span>
				<span class="Value">'.$v.'</span>
			</div>
			';
		}


		$return .= '
		<div class="Legend IV">Individual Value</div>
		<div class="Legend EV">Effort Value</div>
		</div>
	</div>';

	$json[data] = $return;
	echo json_encode($json);
}
elseif($_POST[action] == 'sort'){
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$get_team = json_decode(file_get_contents($trainerFile), true);

	foreach($_POST['order'] as $key => $value){
		$team[$key] = $get_team[$value];
	}

	$fp = fopen($trainerFile, 'w+');
	fwrite($fp, json_encode($team));
	fclose($fp);
}
elseif($_POST[action] == 'sortMoves'){
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$team = json_decode(file_get_contents($trainerFile), true);

	foreach ($_POST[order] as $k => $v) {
		$order[$k] = rtrim($v);
	}

	$team[$_POST[poke]][moveset] = $order;

	pre($team);
	$fp = fopen($trainerFile, 'w+');
	fwrite($fp, json_encode($team));
	fclose($fp);
}

?>