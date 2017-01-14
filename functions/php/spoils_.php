<?

function levelUp($poke, $poke_no, $redrawEXP){
	global $mysqli, $replyData;
	$query = "SELECT * FROM pokemon WHERE id = '$poke[number]'";
	$get = $mysqli->query($query);
	$pokeStats = $get->fetch_assoc();

	// $poke[level] += 15;
	$poke[level]++;


	$replyData[] = ['type' => 'text', 'text' => $poke[nickname].' has reached level '.$poke[level]];

	$move_list = json_decode($pokeStats[movelist], true);

	if($move_list[$poke[level]] != ''){
		$replyData[] = ['type' => 'learnMove', 'level' => $poke[level], 'poke' => $poke_no];
	}

	//Evolve
	if(stristr($pokeStats[evolve_param], 'Level/')){
		// if($poke[level] >= 1){		#Dev to level after every fight
		if($poke[level] >= str_replace('Level/', '', $pokeStats[evolve_param])){
			$replyData[] = ['type' => 'text', 'text' => $poke[nickname].' is evolving.', 'action' => 'evolve'];
			$replyData[] = ['type' => 'evolve', 'to' => $pokeStats[evolve_to], 'from' => $poke[number], 'poke' => $poke_no];
			
			$query = "SELECT name, movelist FROM pokemon WHERE id = '$pokeStats[evolve_to]'";
			$pokeEvolveName = $mysqli->query($query)->fetch_object()->name;
			$replyData[] = ['type' => 'text', 'text' => $poke[nickname].' evolved into '.$pokeEvolveName.'.'];
			if($poke[name] == $poke[nickname]) $poke[nickname] = $pokeEvolveName;
		}

		/////  ADD LEARN NEW MOVES FROM EVOLUTION HERE
		$pokeEvolveMoves = $mysqli->query($query)->fetch_object()->movelist;
		$move_list = json_decode($pokeEvolveMoves, true);

		if($move_list[$poke[level]] != ''){
			$replyData[] = ['type' => 'learnMove', 'level' => $poke[level], 'poke' => $poke_no];
		}		
	}

	$poke[exp] = $poke[exp] - $poke[exp_to_lvl];

	$query = "SELECT $pokeStats[lvl_rate] AS exp FROM exp WHERE level = '$poke[level]'";
	$poke[exp_to_lvl] = $mysqli->query($query)->fetch_object()->exp;


	if($redrawEXP == 1){
		$pct = ($poke[exp]) / $poke[exp_to_lvl];
		$pct = ($pct > 1) ? 1 : $pct;
		$replyData[] = ['type' => 'drawEXP', 'bar' => round($pct * 100), 'uuid' => $poke[uuid], 'level' => $poke[level]];
	}

	return $poke;
}

function calcExp($pokes, $attackingPoke, $defendingPoke){
	global $replyData;

	// $replyData[] = ['type' => 'text', 'text' => 'Console Echo', 'pokes' => $pokes, 'attackingPoke' => $attackingPoke];

	$exp_vars[a] = 1;							// a = 1 if wild : 1.5 if trainer's poke
	$exp_vars[b] = $defendingPoke[base_exp];	// b = base yeild of pokemon
	$exp_vars[e] = 1;							// e = (lucky egg) ? 1.5 : 1
	$exp_vars[f] = 1;							// f = (affection >= 2 hearts) ? 1.2 : 1
	$exp_vars[L] = $defendingPoke[level];		// L = level of fainted poke
	$exp_vars[Lp] = $attackingPoke[level];		// Lp = Level of victorious pokemon
	$exp_vars[p] = 1;							// p = (!exp point power) ? 1 : 0.5 for ↓↓↓, 0.66 for ↓↓, 0.8 for ↓, 1.2 for ↑, 1.5 for ↑↑, or 2 for ↑↑↑, S, or MAX
	$exp_vars[s] = 1;							// s = (participated) ? 1 : (if exp share is on) 2
	$exp_vars[t] = 1;							// t = (original trainer) ? 1 : 1.5							//1.7 if international trade, maybe something similar for market
	$exp_vars[v] = 1;							// v = (past min evolution level) ? 1.2 : 1

	///FLAT EXP
	$exp_gain = ($exp_vars[a] * $exp_vars[t] * $exp_vars[b] * $exp_vars[e] * $exp_vars[L] * $exp_vars[p] * $exp_vars[f] * $exp_vars[v]) / (7 * $exp_vars[s]);

	///SCALED EXP
	///		$exp_gain = (((a * b * L) / (5 * s)) * ( (((2*L) + 10)^2.5) / ((L + Lp + 10)^2.5) ) + 1) * t * e * p;

	$battleLog = json_decode(file_get_contents($battleLogFile = '../../trainers/'.$_SESSION[id].'/battleLog.json'), true);

	$exp_per = round($exp_gain / count($battleLog[participated]));

	$ii = 0;
	$exp_text;
	foreach ($battleLog[participated] as $key => $value) {
		if($pokes[$key][uuid] == $attackingPoke[uuid]){
			$pct = ($attackingPoke[exp] + $exp_per) / $attackingPoke[exp_to_lvl];
			$pct = ($pct > 1) ? 1 : $pct;
			$replyData[] = ['type' => 'drawEXP', 'bar' => round($pct * 100), 'uuid' => $attackingPoke[uuid], 'level' => $attackingPoke[level]];
			$redrawEXP = 1;
		}
		else
			$redrawEXP = 0;

		$replyData[] = ['type' => 'text', 'text' => $pokes[$key]['nickname'].' gained '.$exp_per.' exp.'];

		$pokes[$key][exp] += $exp_per;
		if($pokes[$key][exp] >= $pokes[$key][exp_to_lvl]){
			$pokes[$key] = levelUp($pokes[$key], $key, $redrawEXP);									/// levelUp();
		}

		unset($pokes[$key][modifiers]);
	}

	return $pokes;
}

function trainEV($poke, $defendingPoke){
	$ev_gains = explode(',', $defendingPoke[ev]);
	foreach($ev_gains as $k => $v){
		$poke[evs][$k] += $v;
	}
	$oldMaxHP = $poke[totals][0];

	$poke[totals] = updateTotals($poke);															///	updateTotals();
	$poke[ev_totals]++;

	$poke[HP] += $poke[totals][0] - $oldMaxHP;
	
	return $poke;
}

function updateTotals($poke){
	global $mysqli;
	$query = "SELECT * FROM pokemon WHERE id = '$poke[number]'";
	$get = $mysqli->query($query);
	$pokeStats = $get->fetch_assoc();

	$baseStats = explode('/',$pokeStats['stats']);

	///CALCULATE NATURE
	$natures = [
		1 => 'Attack',
		2 => 'Defense',
		3 => 'Sp. Attack',
		4 => 'Sp. Defense',
		5 => 'Speed',
	];
	$query = "SELECT name, increase, decrease FROM nature WHERE name = '$poke[nature]' LIMIT 1";
	$get = $mysqli->query($query);
	$nature = $get->fetch_assoc();

	foreach($baseStats as $k => $base){
		if($k == 0)
			$totals[$k] = ((((2 * $base) + $poke[ivs][$k] + ($poke[evs][$k]/4)) * $poke[level]) / 100) + $poke[level] + 10;		//HP
		else
			$totals[$k] = ((((2 * $base) + $poke[ivs][$k] + ($poke[evs][$k]/4)) * $poke[level]) / 100) + 5;						//Neutral

		if($natures[$k] == $nature['increase'])
			$totals[$k] = $totals[$k] * 1.1;			//Positive Nature
		elseif($natures[$k] == $nature['decrease'])
			$totals[$k] = $totals[$k] * 0.9;			//Negative Nature

		$totals[$k] = round($totals[$k]);
	}

	return $totals;
}

function spoils($attackingPoke, $defendingPoke){
	global $replyData;
	$pokes = getPokeData();

	$pokes[trainer][$_POST[poke]] = $attackingPoke;


	$replyData[] = ['type' => 'text', 'text' => 'Enemy '.$defendingPoke['nickname'].' has fainted.'];
	$replyData[] = ['type' => 'victory'];
	// $replyData[] = ['type' => 'text', 'text' => 'Good job!<br>Pretend you got stuff.'];

	$pokes[trainer] = calcExp($pokes[trainer], $attackingPoke, $defendingPoke);						///	calcExp();

	$pokes[trainer][$_POST[poke]] = trainEV($pokes[trainer][$_POST[poke]], $defendingPoke);			///	trainEV();



	$replyData[] = ['type' => 'endBattle'];

	//SAVE RESULTS

	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$fp = fopen($trainerFile, 'w+');
	fwrite($fp, json_encode($pokes[trainer]));
	fclose($fp);
}

?>