<?

require_once "../../config.php";

if(!function_exists('getPokeData')){
	function getPokeData(){
		$trainer = '../../trainers/'.$_SESSION[id].'/team.json';
		$pokes[trainer] = json_decode(file_get_contents($trainer), true);
		$enemy = '../../trainers/'.$_SESSION[id].'/encounter.json';
		$pokes[enemy] = json_decode(file_get_contents($enemy), true);	
		return $pokes;
	}
}

function run(){
	global $replyData, $mysqli;
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
		$replyData[] = ['type' => 'text', 'text' => $pokes[trainer][$_POST[poke]][nickname].' failed to run away.'];
		require_once "wild_attack.php";
		attackResolution('enemy');

	}
	elseif($ranAway == 1){
		$replyData[] = ['type' => 'endBattle'];
	}

	return $replyData;
}

if($_POST['action'] == 'run'){
	$replyData = run();
	echo json_encode($replyData);
}


?>