<?

require_once "../../config.php";
require_once "attackPreModifiers.php";
require_once "attackEffect.php";
require_once "attackSecondaryEffect.php";
require_once "spoils.php";

$mysqli->select_db('odyssey_staticdata');


/*****	GLOBAL VARS *****/
$modifierNames = [
	0 => 'Health',
	1 => 'Attack',
	2 => 'Defense',
	3 => 'Special Attack',
	4 => 'Special Defense',
	5 => 'Speed',
	6 => 'Accuracy',
	7 => 'Evasion',
	8 => 'Critical',
];

/*****	FUNCTIONS *****/
function getPokeData(){
	$trainer = '../../trainers/'.$_SESSION[id].'/team.json';
	$pokes[trainer] = json_decode(file_get_contents($trainer), true);
	$enemy = '../../trainers/'.$_SESSION[id].'/encounter.json';
	$pokes[enemy] = json_decode(file_get_contents($enemy), true);	
	return $pokes;
}

function calcEffectiveness($move, $pokeTypes){
	global $mysqli;

	$effectiveness = 1;

	$query = "SELECT * FROM types WHERE name = '$move[type]'";
	$get = $mysqli->query($query);
	$attackType = $get->fetch_assoc();

	foreach ($attackType as $key => $value) {
		if($value != '')
			$attackType[$key] = explode(', ', $attackType[key]);
	}

	$pokeTypes = explode('/', $pokeTypes);
	foreach($pokeTypes as $v){
		$effectiveness = (!empty($attackType[strong]) && in_array($v, $attackType[strong])) ? $effectiveness * 2 : $effectiveness * 1;
		$effectiveness = (!empty($attackType[weak]) && in_array($v, $attackType[weak])) ? $effectiveness / 2 : $effectiveness * 1;
		$effectiveness = (!empty($attackType[immune]) && in_array($v, $attackType[immune])) ? $effectiveness * 0 : $effectiveness * 1;
	}

	return $effectiveness;
}

function rollToHit($attackingPoke, $defendingPoke, $selectedMove){
	$accuracyModifier =	($attackingPoke[modifiers][6] >= 0) ? (3 + $attackingPoke[modifiers][6]) / 3 : 3 / abs(-3 + $attackingPoke[modifiers][6]);
	$evasionModifier =	($defendingPoke[modifiers][7] >= 0) ? 3 / abs(-3 + $defendingPoke[modifiers][7]) : (3 + $defendingPoke[modifiers][7]) / 3;

	$p = $selectedMove[accuracy] * (max($accuracyModifier/$evasionModifier, .33));

	$roll = rand(0,10000) / 10000;
	if($roll > $p)
		return false;
	return true;
}

function calcModifier($attackingPoke, $defendingPoke, $selectedMove){
	global $replyData, $attackPreModifiers;
	#STAB (same type attack bonus)
	$attackingPoke[type] = explode('/', $attackingPoke[type]);
	$stab = (in_array($selectedMove[type], $attackingPoke[type])) ? 1.5 : 1;

	/***** IF MOVE HAS INCREASED CRITICAL *************************************************/
	$attackingPoke[modifiers][8] += $attackPreModifiers[8][$selectedMove[name]];
	/**************************************************************************************/

	$crit = ($attackingPoke[modifiers][8] >= 3 || (1 / (16 / max($attackingPoke[modifiers][8] * 4, 1))) >= (rand(0,10000) / 10000)) ? 1.5 : 1;		// Roll the die
	if($crit > 1)
		$replyData[] = ['type' => 'text', 'text' => 'Critical hit!'];							#!!!!!!!!!!!!	Critical Hit

	$type = calcEffectiveness($selectedMove, $defendingPoke[type]);
	if($type > 1)
		$replyData[] = ['type' => 'text', 'text' => 'Its super effective!'];		#!!!!!!!!!!!!	Super Effective
	if($type < 1)
		$replyData[] = ['type' => 'text', 'text' => 'Its not very effective'];		#!!!!!!!!!!!!	Not Very Effective
	if($type == 0){
		$replyData[] = ['type' => 'text', 'text' => 'Its completely ineffective'];	#!!!!!!!!!!!!	Pokemon Immune
		return 0;
	}
	/*****	OTHER DAMAGE CALCULATION GOES HERE 	*******************************************
		// #$other = 1;	//other counts for things like held items, Abilities, field advantages, and whether the battle is a Double Battle or Triple Battle or not.
	**************************************************************************************/
	$rand = rand(8500, 10000) / 10000;
	
	$modifier = ($stab * $type * $crit * $rand);

	return $modifier;
}

function calcAtkDef($attackingPoke, $defendingPoke, $crit, $category){

	$atk = 1;
	$def = 4;

	($category == 'Special') ? $atk+=2 : $def-=2;

	$Attack = $attackingPoke[modifiers][$atk];
	$Defense = $defendingPoke[modifiers][$def];

	if($crit > 1){
		$Attack = max($Attack, 0);
		$Defense = min($Defense, 0);
	}

	$atkModified = ($Attack >= 0) ? (2 + $Attack) / 2 : 2 / abs(-2 + $Attack);
	$defModified = ($Defense >= 0) ? (2 + $Defense) / 2 : 2 / abs(-2 + $Defense);

	$result = $atkModified / $defModified;

	return $result;
}

function calcSpeedMod($poke){
	$spd = $poke[totals][5];
	$spdMod = $poke[modifiers][5];
	$spdModCalc = ($spdMod >= 0) ? (2 + $spdMod) / 2 : 2 / abs(-2 + $spdMod);
	$spdFinal = $spd * $spdModCalc;
	return $spdFinal;
}

function updateKO($playerAttacking, $attackingPoke, $defendingPoke){
	unset($attackingPoke[modifiers]);
	unset($defendingPoke[modifiers]);
	update($playerAttacking, $attackingPoke, $defendingPoke);
}

function update($playerAttacking, $attackingPoke, $defendingPoke){
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$wildFile = '../../trainers/'.$_SESSION[id].'/encounter.json';
	$trainerPoke = json_decode(file_get_contents($trainerFile), true);

		//SAVE RESULTS
	$trainerPoke[$_POST[poke]] = ($playerAttacking == 'trainer') ? $attackingPoke : $defendingPoke;
	$enemyPoke = ($playerAttacking == 'enemy') ? $attackingPoke : $defendingPoke;

	$fp = fopen($trainerFile, 'w+');
	fwrite($fp, json_encode($trainerPoke));
	fclose($fp);
	$fp = fopen($wildFile, 'w+');
	fwrite($fp, json_encode($enemyPoke));
	fclose($fp);
}

function updateBattleLog(){
	$battleLogFile = '../../trainers/'.$_SESSION[id].'/battleLog.json';
	$battleLog = json_decode(file_get_contents($battleLogFile), true);

		//SAVE RESULTS
	$battleLog[participated][$_POST[poke]] = 1;

	$fp = fopen($battleLogFile, 'w+');
	fwrite($fp, json_encode($battleLog));
	fclose($fp);
}

///Calculate Modifiers at Start of Battle
/*
	Intimidate, keen eye, what the fuck ever
*/


/*****	Attack Phase	*****/
//Determine Attack Execution Order
function attackResolution($playerAttacking){
	global $replyData, $mysqli, $selectedMove, $modifierNames, $statusMoves;
	$pokes = getPokeData();

	if($playerAttacking == 'trainer'){
		$attackingPoke = $pokes[trainer][$_POST[poke]];
		$defendingPoke = $pokes[enemy];
	}
	else{
		$defendingPoke = $pokes[trainer][$_POST[poke]];
		$attackingPoke = $pokes[enemy];
	}

	if($attackingPoke[HP] <= 0){
		return false;
	}

	($attackingPoke[nickname] == '') ? $attackingPoke[nickname] = $attackingPoke[name] : '';
	($defendingPoke[nickname] == '') ? $defendingPoke[nickname] = $defendingPoke[name] : '';

	//GET DATA
	if($playerAttacking == 'trainer')
		$selectedMove = $_POST[move];
	else
		$selectedMove = $attackingPoke[moves][array_rand($attackingPoke[moves])];

	$query = "SELECT * FROM moves WHERE name = '$selectedMove'";
	$get = $mysqli->query($query);
	$selectedMove = $get->fetch_assoc();

	if($playerAttacking == 'trainer')
		$replyData[] = ['type' => 'text', 'text' => $attackingPoke[nickname].' used '.$selectedMove[name]];		#!!!!!!!!!!!!	POKEMON used ATTACK on DEFENDER
	elseif($playerAttacking == 'enemy')
		$replyData[] = ['type' => 'text', 'text' => 'Enemy '.$attackingPoke[nickname].' used '.$selectedMove[name]];		#!!!!!!!!!!!!	POKEMON used ATTACK on DEFENDER



	//Check Status Conditions
								// if($pokemonIsDisabled){
								// 	$responseText = 'Your bitch is busy [sleeping],[being frozen in a block of ice], [being retarded]';
								// 	continue;
								// }
								// if($volitaile){
								// 	//Roll dice
								// 	#	Confused, 50/50
								// 	#	Something else
								// 	#	Maybe this?
								// 	#####	Does it do what it's told, or did it flake out?  ie:  continue; or go roll to hit?
								// }
	//Roll to Hit
	if(!rollToHit($attackingPoke, $defendingPoke, $selectedMove)){
		$replyData[] = ['type' => 'text', 'text' => ($attackingPoke[modifiers][6] < $defendingPoke[modifiers][7]) ? $defendingPoke[nickname] .' dodged the '.$selectedMove[name].'.' : $selectedMove[name] .' missed.'];
		return false;
	}

	if($selectedMove[category] == 'Status'){
		$statusMove = $statusMoves[$selectedMove[name]][effect];
		$dir = ($defendingPoke[modifiers][$statusMove[stat]] > 0) ? 'buffed' : 'nerfed';		////////////	Increase/Decrease Flavor Text
		if((rand(0, ($statusMove[chance] * 100)) / 100) <= $statusMove[chance]){
			$stat = $modifierNames[$statusMove[stat]];
			if($statusMove[target] == 'enemy'){
				if(abs($defendingPoke[modifiers][$statusMove[stat]]) >= 6){
					$desc = $stat.' cannot be '.$dir.' any further.';
					$replyData[] = ['type' => 'text', 'text' => (strtolower(substr($defendingPoke[name], -1)) == 's') ? $defendingPoke[nickname].'\' '.$desc : $defendingPoke[nickname].'\'s '.$desc];		#!!!!!!!!!!!!	Modifier Limit Reached
				}
				else{
					$desc = $stat.' has been '.$dir.'.';
					$replyData[] = ['type' => 'text', 'text' => (strtolower(substr($defendingPoke[name], -1)) == 's') ? $defendingPoke[nickname].'\' '.$desc : $defendingPoke[nickname].'\'s '.$desc];
					$defendingPoke[modifiers][$statusMove[stat]] += $statusMove[impact];
				}
			}
		}
		else{
			$replyData[] = ['type' => 'text', 'text' => $selectedMove[name].' failed.'];		#!!!!!!!!!!!!	Status Effect Failed - RNG
		}
	}
	else{
		//Figure Damage
		$modifier = calcModifier($attackingPoke, $defendingPoke, $selectedMove);

		$AtkDef = calcAtkDef($attackingPoke[modifiers], $defendingPoke[modifiers], $crit, $selectedMove[category]);

		//Calc HP Remaining
		$hp = $defendingPoke[HP] -= round((((2 * $attackingPoke[level] + 10) / 250) * ($AtkDef) * $selectedMove[power] + 2) * $modifier);
		$hp_pct = round(($hp) / $defendingPoke[totals][0], 2) * 100;

		//Ensure HP doesn't become negative
		if($hp <= 0) $defendingPoke[HP] = $hp_pct = $hp = 0;
		//Set HP display as Number or Percentage
		$hp = ($playerAttacking == 'trainer') ? $hp_pct : $hp;
		//Return draw data
		$replyData[] = ['type' => 'draw', 'draw' =>  [
			'uuid' => $defendingPoke[uuid],
			'hp' => $hp,
			'to' => $hp_pct,
			],
		];

		//Check if Poke fainted
		if($hp <= 0){
			if($playerAttacking == 'trainer'){
				update($playerAttacking, $attackingPoke, $defendingPoke);
				spoils($attackingPoke, $defendingPoke);
				echo json_encode($replyData);
				die();
			}
			else{
				$replyData[] = ['type' => 'text', 'text' => $defendingPoke['nickname'].' has fainted.'];

				foreach($pokes[trainer] as $k => $v){
					if($k != $_POST[poke] && $v[HP] > 0){
						$team_alive = 1;
						break;
					}
				}

				if($team_alive == 1){
					$replyData[] = ['type' => 'fainted'];
				}
				else{
					$replyData[] = ['type' => 'text', 'text' => 'Welp, that '.$attackingPoke[nickname].' kicked your ass.'];
					$replyData[] = ['type' => 'KO'];
				}
			}
			updateKO($playerAttacking, $attackingPoke, $defendingPoke);
			return false;
		}

		/*****************
				CALCULATE ADDITIONAL EFFECT CHANCE ON MOVE
			// if(!empty($move['effect'])){
			// 	#calculate odds of effect happening
			// }
		******************/
	}

	update($playerAttacking, $attackingPoke, $defendingPoke);
}

function calcExecutionOrder($pokes){
	$calculatedSpeed[trainer] = calcSpeedMod($pokes[trainer][$_POST[poke]]);
	$calculatedSpeed[enemy] = calcSpeedMod($pokes[enemy]);
	arsort($calculatedSpeed);
	return $calculatedSpeed;
}

function attackAll(){
	global $replyData;
	$pokes = getPokeData();
	$calculatedSpeed = calcExecutionOrder($pokes);
	foreach ($calculatedSpeed as $playerAttacking => $speed) {
		attackResolution($playerAttacking);	
	}
}

function swapOut($pokes){
	global $replyData;
	$nickname = ($pokes[trainer][$_POST[poke]][nickname] == '') ? $pokes[trainer][$_POST[poke]][name] : $pokes[trainer][$_POST[poke]][nickname];
	$replyData[] = ['type' => 'text', 'action' => 'recall', 'text' => 'Return '.$nickname.'.'];		#!!!!!!!!!!!!	Status Effect Failed - RNG

	$pokes[trainer][$_POST[newpoke]][team_no] = $_POST[newpoke];
	$nickname = ($pokes[trainer][$_POST[newpoke]][nickname] == '') ? $pokes[trainer][$_POST[newpoke]][name] : $pokes[trainer][$_POST[newpoke]][nickname];
	$replyData[] = ['type' => 'text', 'action' => 'sendOut', 'text' => $nickname.', I choose you!', 'poke' => $pokes[trainer][$_POST[newpoke]]];		#!!!!!!!!!!!!	Status Effect Failed - RNG

	$_POST[poke] = $_POST[newpoke];
}

updateBattleLog();

if($_POST['action'] == 'attack'){
	$pokes = getPokeData();
	attackAll();
	echo json_encode($replyData);
}
elseif($_POST['action'] == 'swapOut'){
	$pokes = getPokeData();
	$calculatedSpeed = calcExecutionOrder($pokes);
	foreach ($calculatedSpeed as $playerAttacking => $speed) {
		if($playerAttacking == 'trainer')
			swapOut($pokes);
		else
			attackResolution($playerAttacking);	
	}
	echo json_encode($replyData);
}
elseif($_POST['action'] == 'fainted'){
	$pokes = getPokeData();
	swapOut($pokes);
	echo json_encode($replyData);
}

?>