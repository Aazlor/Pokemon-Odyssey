<?php
include_once 'config.php';
include_once 'data/load.php';




$debug = true;
?>

<!DOCTYPE html>
<html>
<head>
	<title>Pokemon Odyssey</title>

	<link rel="stylesheet" type="text/css" href="styles/star_bg.css">
	<link rel="stylesheet" type="text/css" href="styles/basic.css">


	<style type="text/css">

	#DevNotes {
		position: absolute;
		top: 20%;
		left: 3em;
		background: rgba(255,255,255,.75);
		border-radius: 20px;
		max-height: 50%;
		max-width: 200px;
		overflow: auto;
		padding: 2em;
		font-family: arial;
		font-size: 10px;
	}

	#DevNotes h2 {
		margin-top: 1em;
		margin-bottom: .25em;
	}

	#DevNotes ul {
		list-style-position: outside;
		padding-left: 2em;
	}

	#DevNotes ul li {
		padding: .25em 0;
	}

	#DevNotes::-webkit-scrollbar {
		width: 0;
	}

	</style>


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

	<div id="Trainer">
		<div class="Sprite">
			<div class="Shadow"></div>
		</div>
	</div>

	<div id="Wrapper">
		<div id="Background"></div>
		<div class="tinyTree solid"></div>
		<div class="grassyRock solid"></div>

		<?

			$tallGrass[0] = [
				'x' => 450,
				'y' => 200,
				'w' => 200,
				'h' => 150
			];

			foreach($tallGrass as $k => $a){
				for ($i=0; $i < $a[h]; $i+=16) { 
					$offsetTop = $a[y] + $i;
					echo '<div class="depth" style="top: '.$offsetTop.'px; left: '.$a[x].'px;">';
						for ($ii=0; $ii < $a[w]; $ii+=16) { 
							echo '<div class="tallGrass encounter"></div>';
						}
					echo'</div>';
				}
			}
		?>
	</div>

	<div id="Combat">

		<div class="Trainer">
			<div class="Platform">
				<div class="Poke"></div>
				<div class="Shadow"></div>
			</div>
			<div class="Details"></div>
		</div>
		<div class="Enemy">
			<div class="Details"></div>
			<div class="Platform">
				<div class="Poke"></div>
				<div class="Shadow"></div>
			</div>
		</div>
		

		<div class="box"></div>
	</div>

	<div id="PokemonTeam"></div>
</div>

<div id="DevNotes">
	<h1>Dev Notes</h1>
	<h2>In Progress</h2>
	<ul>
		<li>Programming Move Status Effects and Secondary Effects</li>
		<li>Adding all Gen 1 Moves</li>
		<li>Pokemon Sprites Currently Added 001-025 (Arbok)</li>
	</ul>


	<h2>Pipeline</h2>
	<ul>
		<li>Finish Gen 1 Pokes</li>
		<li>Non-level based evolutions</li>
		<li>Nicknames (adding and changing)</li>
		<li>Items</li>
		<li>Catching Pokemon</li>
		<li>Abilities: In-combat</li>
		<li>Abilities: Out of Combat</li>
		<li>Moves: Out of Combat</li>
		<!-- <li></li> -->
	</ul>

	<h2>Completed</h2>
	<ul class="Complete">
		<li>Pokemon learning default moves when evolving</li>
		<li>Pokemon move order now sortable</li>
		<li>Team order now sortable</li>
		<li>Pokemon Stats Update during combat, not on a timer</li>
		<li>EXP in-combat Bar update on victory</li>
		<li>Viewing Pokemon Stats</li>
		<li>Evolution at Req lvl</li>
		<li>Learning/Forgetting Moves</li>
		<li>Rewarding EXP</li>
		<li>Level Up</li>
		<li>Combat: Swapping Pokes</li>
		<li>Combat: Fainting</li>
		<li>Combat: Run</li>
		<li>Combat: Attacking</li>
		<li>Wild Encounters</li>
		<li>Movement & Collision</li>
		<!-- <li></li> -->
	</ul>

</body>
</html>