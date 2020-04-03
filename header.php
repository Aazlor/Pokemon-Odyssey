<!DOCTYPE html>
<html>
<head>
	<title>Pokemon Odyssey</title>

	<link rel="stylesheet" type="text/css" href="styles/star_bg.css">
	<link rel="stylesheet" type="text/css" href="styles/basic.css">


	<script type="text/javascript" src="includes/js/jquery-3.1.1.min.js"></script>
	<link rel="stylesheet" type="text/css" href="includes/js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="includes/js/jquery-ui/jquery-ui.structure.min.css">
	<script type="text/javascript" src="includes/js/jquery-ui/jquery-ui.min.js"></script>
	<!-- <link rel="stylesheet" type="text/css" href="includes/js/sakura/jquery-sakura.css"> -->
	<!-- <script type="text/javascript" src="includes/js/sakura/jquery-sakura.js"></script> -->
	<script type="text/javascript" src="includes/js/animateNumber/jquery.animateNumber.min.js"></script>

	<script type="text/javascript">

	//GLOBALS
	tileSize = 8;
	speed = 4;
	inCombat = false;
	route = 1;
	combatStep = 0;
	resolvingAttacks = {};
	poke_data = {};
	
	moveKeys = {};	// store key codes and currently pressed ones
		moveKeys.UP = 38;
		moveKeys.LEFT = 37;
		moveKeys.RIGHT = 39;
		moveKeys.DOWN = 40;
		moveKeys.W = 87;
		moveKeys.A = 65;
		moveKeys.S = 83;
		moveKeys.D = 68;
	
	pokes = <?= file_get_contents('trainers/'.$_SESSION[id].'/team.json') ?>;		//	Currently equiped pokemon
		
	$(function() {

		$(window).resize(function(){location.reload();});

		$('#Trainer').center();
		$('#Wrapper').center();
		TrainerXY = {
			0 : Math.floor($('#Trainer').offset().top),
			1 : Math.floor($('#Trainer').offset().left + $('#Trainer').outerWidth(true)),
			2 : Math.floor($('#Trainer').offset().top + $('#Trainer').outerHeight(true)),
			3 : Math.floor($('#Trainer').offset().left),
		};
		team.update();

		$('#PokeStats .MoveSet .Move').sortable();
	});

	jQuery.fn.centerTrainer = function () {
		this.css("position","absolute");
		this.css("top", Math.max(0, (Math.floor(($('#ViewPort').height() - 32) / 2) + $('#ViewPort').scrollTop())) + "px");
		this.css("left", Math.max(0, (Math.floor(($('#ViewPort').width() - 32) / 2) + $('#ViewPort').scrollLeft())) + "px");
		return this;
	}

	jQuery.fn.center = function () {
		this.css("position","absolute");
		this.css("top", Math.max(0, (Math.floor(($('#ViewPort').height() - $(this).outerHeight()) / 2) + $('#ViewPort').scrollTop())) + "px");
		this.css("left", Math.max(0, (Math.floor(($('#ViewPort').width() - $(this).outerWidth()) / 2) + $('#ViewPort').scrollLeft())) + "px");
		return this;
	}
	</script>

	<script type="text/javascript" src="functions/js/maths.js"></script>
	<script type="text/javascript" src="functions/js/build_objects.js"></script>
	<script type="text/javascript" src="functions/js/event_listener.js"></script>
	<script type="text/javascript" src="functions/js/combat.js"></script>
	<script type="text/javascript" src="functions/js/encounter.js"></script>
	<script type="text/javascript" src="functions/js/collision.js"></script>
	<script type="text/javascript" src="functions/js/movement.js"></script>
	<script type="text/javascript" src="functions/js/pokemon_team.js"></script>
	<script type="text/javascript" src="functions/js/stat_handler.js"></script>

</head>
<body>

<div id='stars'></div>
<div id='stars2'></div>
<div id='stars3'></div>


<div id="ViewPort">