<?php
include_once 'config.php';
include_once 'data/load.php';




$debug = true;
?>

<?php include_once 'header.php'; ?>

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

<?php include_once 'footer.php'; ?>
