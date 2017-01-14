<?

include_once "../../config.php";

$team = '../../trainers/'.$_SESSION['id'].'/team.json';
$pokes = json_decode(file_get_contents($team), true);

if($_POST[trainerPoke] == 'grabFirst'){

	foreach($pokes as $k => $v){
		if($v[HP] > 0){
			$get = $k;
			break;
		}
	}

	$pokes[$get]['team_no'] = $get;
	echo json_encode($pokes[$get]);
}
elseif($_POST[trainerPoke] == 'showTeam'){
	echo json_encode($pokes);
}
elseif($_POST[trainerPoke] != ''){
	$pokes[$_POST[trainerPoke]]['team_no'] = $_POST[trainerPoke];
	echo json_encode($pokes[$_POST[trainerPoke]]);
}

?>