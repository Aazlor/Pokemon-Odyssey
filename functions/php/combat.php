<?

require_once "../../config.php";
$mysqli->select_db('odyssey_staticdata');

function getPokeData(){
	$trainer = '../../trainers/'.$_SESSION[id].'/team.json';
	$pokes[trainer] = json_decode(file_get_contents($trainer), true);
	$enemy = '../../trainers/'.$_SESSION[id].'/encounter.json';
	$pokes[enemy] = json_decode(file_get_contents($enemy), true);	
	return $pokes;
}

$replyData = [];

function replyData($data){
	global $replyData;
	$replyData[] = $data;
}

function endCheck(){
	global $replyData;

	if(preg_match('/"endBattle"/i' , json_encode($replyData))){
		echo json_encode($replyData);
		die();
	}
}

function reply(){
	global $replyData;
	echo json_encode($replyData);
	die();
}

function updateKO($attacker, $aPoke, $dPoke){
	unset($dPoke[modifiers]);
	update($attacker, $aPoke, $dPoke);
}

function update($attacker, $aPoke, $dPoke){
	$trainerFile = '../../trainers/'.$_SESSION[id].'/team.json';
	$wildFile = '../../trainers/'.$_SESSION[id].'/encounter.json';
	$trainerPoke = json_decode(file_get_contents($trainerFile), true);

		//SAVE RESULTS
	$trainerPoke[$_POST[poke]] = ($attacker == 'trainer') ? $aPoke : $dPoke;
	$enemyPoke = ($attacker == 'enemy') ? $aPoke : $dPoke;

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

function endOfCombatRound(){
	/***
		End of Combat Round Check
		--	Apply damage from burn, poison, etc
	***/
}

class actions {

	function run(){
		$pokes = getPokeData();

		if($pokes[trainer][$_POST[poke]][nickname] == '')
			$pokes[trainer][$_POST[poke]][nickname] = $pokes[trainer][$_POST[poke]][name];

		/*****	Auto Run Start *****/

					// if type == ghost or ability in array[]		run = 1;

		/*****	Auto Run End *****/


		/*****	Calc Run 	*****/
				// 	formula: 		f = ((spd * 128 / enemy_spd) + 30) * attempt_no				mod 256 (ie: rand(0, 256) < f, success)
		$f = (($pokes[trainer][$_POST[poke]][totals][5] * 128) / $pokes[enemy][totals][5] + 30 ) * ($pokes[trainer][$_POST[poke]][runCount] + 1);
		$ranAway = (rand(0,255) < $f) ? 1 : 0;

		if($ranAway == 0){
			replyData(['type' => 'text', 'text' => $pokes[trainer][$_POST[poke]][nickname].' failed to run away.']);
		}
		elseif($ranAway == 1){
			replyData(['type' => 'endBattle']);
		}
	}

	function swapOut(){
		$pokes = getPokeData();

		$nickname = ($pokes[trainer][$_POST[poke]][nickname] == '') ? $pokes[trainer][$_POST[poke]][name] : $pokes[trainer][$_POST[poke]][nickname];
		replyData(['type' => 'text', 'action' => 'recall', 'text' => 'Return '.$nickname.'.']);

		$pokes[trainer][$_POST[newpoke]][team_no] = $_POST[newpoke];
		$nickname = ($pokes[trainer][$_POST[newpoke]][nickname] == '') ? $pokes[trainer][$_POST[newpoke]][name] : $pokes[trainer][$_POST[newpoke]][nickname];
		replyData(['type' => 'text', 'action' => 'sendOut', 'text' => $nickname.', I choose you!', 'poke' => $pokes[trainer][$_POST[newpoke]]]);

		$_POST[poke] = $_POST[newpoke];
	}

	function fainted(){
		$this->swapOut();
		reply();
	}

	function item($item){
		/***
			DO STUFF
		***/
	}
}

class calculations {

	public $pokes, $sql;

	function __construct(){
		global $db_user, $db_pass;
		$this->sql = new mysqli("localhost", "$db_user", "$db_pass", 'odyssey_staticdata');
		$this->pokes = getPokeData();
	}

	function determinePriorityOrder(){

		/*****
			Modify the encounter and combat script to allow each poke to have a governing id that references their battle position.
			For a more advanced feature, give each participating pokemon an id, and iterate through those for input.  This will be needed for multiplayer, multi pokemon battles, etc
		*****/

		$query = "SELECT * FROM moves LIMIT 10";
		$get = $this->sql->query($query);

		$calculatedSpeed[trainer] = $this->finalSpeed($this->pokes[trainer][$_POST[poke]]);
		$calculatedSpeed[enemy] = $this->finalSpeed($this->pokes[enemy]);
		arsort($calculatedSpeed);

		if($_POST[action] != 'attack'){
			$trainer = new actions;
			$callFunction = $_POST['action'];
			$trainer->$callFunction();
			endCheck();

			unset($calculatedSpeed[trainer]);
		}

		return $calculatedSpeed;
		// $executeAttacks = new resolveAttack($calculatedSpeed);
		// reply();
	}

	function finalSpeed($poke){
		$spd = $poke[totals][5];
		$spdMod = $poke[modifiers][5];
		$spdModCalc = ($spdMod >= 0) ? (2 + $spdMod) / 2 : 2 / abs(-2 + $spdMod);
		$spdFinal = $spd * $spdModCalc;
		return $spdFinal;
	}

	function damageModifiers($aPoke, $dPoke, $move){
		#STAB (same type attack bonus)
		$aPoke[type] = explode('/', $aPoke[type]);
		$stab = (in_array($move[type], $aPoke[type])) ? 1.5 : 1;

		/***** IF MOVE HAS INCREASED CRITICAL *************************************************/
		include_once "_move_modifier.php";
		$aPoke[modifiers][8] += $attackPreModifiers[8][$move[name]];
		/**************************************************************************************/

		$crit = ($aPoke[modifiers][8] >= 3 || (1 / (16 / max($aPoke[modifiers][8] * 4, 1))) >= (rand(0,10000) / 10000)) ? 1.5 : 1;		// Roll the die
		if($crit > 1)
			replyData(['type' => 'text', 'text' => 'Critical hit!']);				#!!!!!!!!!!!!	Critical Hit

		$type = $this->effectiveness($move, $dPoke[type]);
		if($type > 1)
			replyData(['type' => 'text', 'text' => 'Its super effective!']);		#!!!!!!!!!!!!	Super Effective
		if($type < 1)
			replyData(['type' => 'text', 'text' => 'Its not very effective']);		#!!!!!!!!!!!!	Not Very Effective
		if($type == 0){
			replyData(['type' => 'text', 'text' => 'Its completely ineffective']);	#!!!!!!!!!!!!	Pokemon Immune
			return 0;
		}
		/*****	OTHER DAMAGE CALCULATION GOES HERE 	*******************************************
			// #$other = 1;	//other counts for things like held items, Abilities, field advantages, and whether the battle is a Double Battle or Triple Battle or not.
		**************************************************************************************/
		$rand = rand(8500, 10000) / 10000;
		
		$dmgModifier[Modifier] = ($stab * $type * $crit * $rand);
		$dmgModifier[AtkDef] = $this->AtkDef($aPoke, $dPoke, $crit, $move[category]);

		return $dmgModifier;
	}

	function AtkDef($aPoke, $dPoke, $crit, $category){

		$atk = 1;
		$def = 4;

		($category == 'Special') ? $atk+=2 : $def-=2;

		$Attack = $aPoke[modifiers][$atk];
		$Defense = $dPoke[modifiers][$def];

		if($crit > 1){
			$Attack = max($Attack, 0);
			$Defense = min($Defense, 0);
		}

		$atkModified = ($Attack >= 0) ? (2 + $Attack) / 2 : 2 / abs(-2 + $Attack);
		$defModified = ($Defense >= 0) ? (2 + $Defense) / 2 : 2 / abs(-2 + $Defense);

		$result = ($atkModified * $aPoke[totals][$atk]) / ($defModified * $dPoke[totals][$def]);

		return $result;
	}

	function effectiveness($move, $dPoke_type){
		$effectiveness = 1;

		$query = "SELECT * FROM types WHERE name = '$move[type]'";
		$get = $this->sql->query($query);
		$attackType = $get->fetch_assoc();

		foreach ($attackType as $key => $value) {
			if($value != '')
				$attackType[$key] = explode(', ', $attackType[key]);
		}

		$dPoke_type = explode('/', $dPoke_type);
		foreach($dPoke_type as $v){
			$effectiveness = (!empty($attackType[strong]) && in_array($v, $attackType[strong])) ? $effectiveness * 2 : $effectiveness * 1;
			$effectiveness = (!empty($attackType[weak]) && in_array($v, $attackType[weak])) ? $effectiveness / 2 : $effectiveness * 1;
			$effectiveness = (!empty($attackType[immune]) && in_array($v, $attackType[immune])) ? $effectiveness * 0 : $effectiveness * 1;
		}

		return $effectiveness;
	}

}

class resolveAttack {

	#$apoke = Attacking Pokemon
	#$dpoke = Defending Pokemon
	public $aPoke, $dPoke, $move, $attacker, $sql, $pokes;
	public $encounterType = 'Wild';
	public $modifierNames = [ 0 => 'Health', 1 => 'Attack', 2 => 'Defense', 3 => 'Special Attack', 4 => 'Special Defense', 5 => 'Speed', 6 => 'Accuracy', 7 => 'Evasion', 8 => 'Critical' ];

	function __construct($attacker){
		global $db_user, $db_pass;
		$this->sql = new mysqli("localhost", "$db_user", "$db_pass", 'odyssey_staticdata');
		$this->attacker = $attacker;
	}

	function attackerDefender(){
		($this->attacker == 'trainer') ? $this->resolveTrainer() : $this->resolveEnemy();
	}

	function resolveTrainer(){
		$this->pokes = getPokeData();
		$this->aPoke = $this->pokes[trainer][$_POST[poke]];
		$this->dPoke = $this->pokes[enemy];

		if($this->aPoke[HP] <= 0)
			return false;

		$move = $_POST[move];
		$this->getMove($move);
	}

	function resolveEnemy(){
		$this->pokes = getPokeData();
		$this->aPoke = $this->pokes[enemy];
		$this->dPoke = $this->pokes[trainer][$_POST[poke]];

		if($this->aPoke[HP] <= 0)
			return false;

		$getMove = $this->aPoke[moves][array_rand($this->aPoke[moves])];
		$this->getMove($getMove);
	}

	function getMove($move){
		$query = "SELECT * FROM moves WHERE name = '$move'";
		$get = $this->sql->query($query);
		$this->move = $get->fetch_assoc();
	}

	function executeMove(){

		($this->aPoke[nickname] == '') ? $this->aPoke[nickname] = $this->aPoke[name] : '';
		($this->dPoke[nickname] == '') ? $this->dPoke[nickname] = $this->dPoke[name] : '';

		/** REPLY DATA USED MOVE **/
		($this->attacker == 'trainer') ? replyData(['type' => 'text', 'text' => $this->aPoke[nickname].' used '.$this->move[name]]) : replyData(['type' => 'text', 'text' => $this->encounterType.' '.$this->aPoke[nickname].' used '.$this->move[name]]);
	
		if(!$this->checkStatusCondition())
			return false;

		if(!$this->rollToHit()){
			replyData(['type' => 'text', 'text' => ($this->aPoke[modifiers][6] < $this->dPoke[modifiers][7]) ? $this->dPoke[nickname] .' dodged the '.$this->move[name].'.' : $this->move[name] .' missed.']);
			return false;
		}

		return true;
	}

	function checkStatusCondition(){
		/*** Check if attacking pokemon has status that would prevent it from using a move ***/
		return true;
	}

	function rollToHit(){

		if($this->move[accuracy] != '-'){
			$accuracyModifier =	($this->aPoke[modifiers][6] >= 0) ? (3 + $this->aPoke[modifiers][6]) / 3 : 3 / abs(-3 + $this->aPoke[modifiers][6]);
			$evasionModifier =	($this->dPoke[modifiers][7] >= 0) ? 3 / abs(-3 + $this->dPoke[modifiers][7]) : (3 + $this->dPoke[modifiers][7]) / 3;

			$p = $this->move[accuracy] * (max($accuracyModifier/$evasionModifier, .33));

			$roll = rand(0,10000) / 10000;
			if($roll > $p)
				return false;
		}
		elseif($this->move[accuracy] == 'S'){
			/*****
				S or something unique
				Some moves, like fissure, have a different hit ratio
			*****/
		}
		return true;
	}

	function statusMove($type){

		if($type == 'Status'){
			include "_move_status.php";
			$statusMove = $statusMoves[$this->move[name]];			
		}
		elseif($type == 'Secondary'){
			/** SOMETHING HERE **/
		}

		if($this->move[probability] != '' && $this->move[probability] < 1){
			$roll = rand(0,10000) / 10000;
			echo $roll;
			if($roll > $this->move[probability]){
				replyData(['type' => 'text', 'text' => $this->move[name].' failed.']);		#!!!!!!!!!!!!	Status Effect Failed - RNG
				return false;
			}
		}

		if($statusMove[unique] === 0){
			$user = json_decode($statusMove[user], true);
			$enemy = json_decode($statusMove[opponent], true);
			foreach($user as $k => $v){
				if($v != 0)
					$this->applyModifier('aPoke', $k, $v);
			}
			foreach($enemy as $k => $v){
				if($v != 0)
					$this->applyModifier('dPoke', $k, $v);
			}
		}
		$this->endTurnCheck();		
	}

	function applyModifier($target, $stat, $impact){
		$dir = ($impact > 0) ? 'buffed' : 'nerfed';			////////////	Increase/Decrease Flavor Text

		$this->$target[modifiers][$stat] += $impact;
		if(abs($this->$target[modifiers][$stat]) > 6){
			$desc = $this->modifierNames[$stat].' cannot be '.$dir.' any further.';
			replyData(['type' => 'text', 'text' => (strtolower(substr($this->$target[nickname], -1)) == 's') ? $this->$target[nickname].'\' '.$desc : $this->$target[nickname].'\'s '.$desc]);		#!!!!!!!!!!!!	Modifier Limit Reached
			$this->$target[modifiers][$stat] = ($this->$target[modifiers][$stat] > 0) ? 6 : -6;
		}
		else{
			$desc = $this->modifierNames[$stat].' has been '.$dir.'.';
			replyData(['type' => 'text', 'text' => (strtolower(substr($this->$target[nickname], -1)) == 's') ? $this->$target[nickname].'\' '.$desc : $this->$target[nickname].'\'s '.$desc]);
		}
	}

	function  attackMove(){
		//Figure Damage
		$calc = new calculations();
		$dmgModifier = $calc->damageModifiers($this->aPoke, $this->dPoke, $this->move);

		//Calc HP Remaining
		$hp = $this->dPoke[HP] -= round((((2 * $this->aPoke[level] + 10) / 250) * ($dmgModifier[AtkDef]) * $this->move[power] + 2) * $dmgModifier[Modifier]);
		$hp_pct = round(($hp) / $this->dPoke[totals][0], 2) * 100;

		//Ensure HP doesn't become negative
		if($hp <= 0) $this->dPoke[HP] = $hp_pct = $hp = 0;
		//Set HP display as Number or Percentage
		$hp = ($this->attacker == 'trainer') ? $hp_pct : $hp;
		//Return draw data
		replyData( ['type' => 'draw', 'draw' => ['uuid' => $this->dPoke[uuid], 'hp' => $hp, 'to' => $hp_pct]] );

		//Check if Poke fainted
		if($hp <= 0){
			if($this->attacker == 'trainer'){
				require_once "spoils.php";
				spoils($this->aPoke, $this->dPoke);
				endCheck();
			}
			else{
				replyData(['type' => 'text', 'text' => $this->dPoke['nickname'].' has fainted.']);

				foreach($this->pokes[trainer] as $k => $v){
					if($k != $_POST[poke] && $v[HP] > 0){
						$team_alive = 1;
						break;
					}
				}

				if($team_alive == 1){
					replyData(['type' => 'fainted']);
				}
				else{
					replyData(['type' => 'text', 'text' => 'Welp, that '.$this->aPoke[nickname].' kicked your ass.']);
					replyData(['type' => 'KO']);
				}
			}
			updateKO($this->attacker, $this->aPoke, $this->dPoke);
		}

		/*****************
			CALCULATE SECONDARY EFFECT CHANCE ON MOVE
			// if(!empty($move['effect'])){
			// 	#calculate odds of effect happening
			// }
		******************/

		$this->endTurnCheck();
	}

	function endTurnCheck(){

		/*** Insert any functionality that happens AFTER a pokemon finishes it's own attack ***/

		update($this->attacker, $this->aPoke, $this->dPoke);
	}

}

updateBattleLog();
$steps = new calculations;
$calculatedSpeed = $steps->determinePriorityOrder();

foreach ($calculatedSpeed as $key => $value) {
	$executeAttack = new resolveAttack($key);
	$executeAttack->attackerDefender();
	if($executeAttack->executeMove()){
		if($executeAttack->move[category] == 'Status')
			$executeAttack->statusMove('Status');
		else 		##	RIGHT AROUND HERE -> Double Slap / Multi Hit Calculations
			$executeAttack->attackMove();		
	}
}
endOfCombatRound();

reply();

?>