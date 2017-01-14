<?

include_once '../../config.php';


class wildEncounter {

	public $return_data;

	public function build($pokeStats, $level, $nature, &$mysqli){
		///DETERMINE ABILITY
		$abilities = explode('/', $pokeStats['abilities']);
		$rand = rand(0, 100);
		if($rand <= 40)
			$ability = $abilities[0];
		elseif($rand <= 80)
			$ability = $abilities[1];
		elseif($rand <= 100)
			$ability = $abilities[2];

		///CALCULATE NATURE
		$natures = [
			1 => 'Attack',
			2 => 'Defense',
			3 => 'Sp. Attack',
			4 => 'Sp. Defense',
			5 => 'Speed',
		];

		///RANDOMIZE IVS
		$ivs = [
			0 => rand(0,31),	//	HP
			1 => rand(0,31),	//	ATK
			2 => rand(0,31),	//	DEF
			3 => rand(0,31),	//	S ATK
			4 => rand(0,31),	//	S DEF
			5 => rand(0,31),	//	SPD
		];

		///GENERATE STATS
		$baseStats = explode('/',$pokeStats['stats']);
		foreach($baseStats as $k => $v){
			if($k == 0)
				$totals[$k] = round(((2*$v + $iv[$k] + $ev[$k]/4 + 100) * $level) / 100 + 10);	//HP
			elseif($natures[$k] == $nature['increase'])
				$totals[$k] = round((((2*$v + $iv[$k] + $ev[$k]/4) * $level) / 100 + 5) * 1.1);	//Positive Nature
			elseif($natures[$k] == $nature['decrease'])
				$totals[$k] = round((((2*$v + $iv[$k] + $ev[$k]/4) * $level) / 100 + 5) * 0.9);	//Negative Nature
			else
				$totals[$k] = round((((2*$v + $iv[$k] + $ev[$k]/4) * $level) / 100 + 5));		//Neutral
		}

		///SELECT MOVES
		foreach(json_decode($pokeStats[movelist], true) as $k => $v){
			if($k > $level)
				break;
			if(stristr($v, ', ')){
				$set = explode(', ', $v);
				foreach ($set as $vv) {
					$potential_moveset[] = $vv;
				}
			}
			else
				$potential_moveset[] = $v;
		}
		$len = count($potential_moveset);
		if($len > 4){
			for ($i=0; $i < 4; $i++) { 
				$movelist[] = $potential_moveset[mt_rand(0, $len-1)];
			}
		}
		else
			$movelist = $potential_moveset;

		///RANDOMIZE GENDER
		$gender = explode('/', $pokeStats[gender]);
		$gender = (rand(1,100) <= $gender[0]) ? 'male' : 'female';

		///BUILD DATA ARRAY
		$uuid = 'encounter_'.date(U).'_'.rand(1,10000);
		$results = [
			'uuid' => $uuid,
			'name' => $pokeStats['name'],
			'number' => $pokeStats['id'],
			'level' => $level,
			'type' => $pokeStats['type'],
			'totals' => $totals,
			'iv' => $ivs,
			'ev' => $pokeStats['ev'],
			'nature' => $nature['name'],
			'ability' => $ability,
			'moves' => $movelist,
			'shiny' => '',
			'gender' => $gender,
			'HP' => $totals[0],
			'base_exp' => $pokeStats['exp'],
			'catch_rate' => $pokeStats['catch_rate'],
			//DEV
			// 'HP' => 1,
		];

		$this->return_data = [
			'uuid' => $uuid,
			'name' => $pokeStats['name'],
			'number' => $pokeStats['id'],
			'level' => $level,
			'shiny' => '',
			'gender' => $results[gender],
			'type' => $pokeStats['type'],
			'HP' => $results[HP],
			// 'HP_pct' => round(($results[HP]/$results[totals][0]) * 100),
			'HP_pct' => 100,
		];

		//BattleLog / EXP
		$battleLogFile = '../../trainers/'.$_SESSION[id].'/battleLog.json';
		$fp = fopen($battleLogFile, 'w+');
		fwrite($fp, '');
		fclose($fp);

		//Encounter
		$file = '../../trainers/'.$_SESSION[id].'/encounter.json';
		$fp = fopen($file, 'w+');
		fwrite($fp, json_encode($results));
		fclose($fp);
	}

}

if($_POST['route'] != ''){

	$mysqli->select_db('odyssey_staticdata');
	///INITIAL ENCOUNTER
	$pokeEncountered = ['001','004','007','010','013','016'];

	$length = count($pokeEncountered) - 1;
	$pokeEncountered = $pokeEncountered[rand(0, $length)];
	// $level = rand(21,23);
	$level = rand(1,3);

	///GET POKE DATA
	$query = "SELECT * FROM pokemon WHERE id='$pokeEncountered'";
	$get = $mysqli->query($query);
	$pokeStats = $get->fetch_assoc();

	$query = "SELECT name, increase, decrease FROM nature ORDER BY RAND() LIMIT 1";
	$get = $mysqli->query($query);
	$nature = $get->fetch_assoc();

	///BUILD ENCOUNTER AND RETURN JSON OF POKE
	$wildEncounter = new wildEncounter;
	$wildEncounter->build($pokeStats, $level, $nature);
	echo json_encode($wildEncounter->return_data);

};


?>