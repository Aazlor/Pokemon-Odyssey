(function( team, $, undefined ) {
	//Private Property
		// var isHot = true;

	//Public Property
		// skillet.ingredient = "Bacon Strips";

	//Public Method
	team.update = function(poke){
		getTeam();
	}

	team.showStats = function(poke){

		var up = $('body').find('#PokeStats');
		if((up.length > 0 && $(up).data('id') == $(poke).data('slot')) || $(poke).hasClass('Pokeball')){
			$(up).fadeOut(300, function(){
				$(this).remove();
			});
		}
		else {
			$(up).fadeOut(300, function(){
				$(this).remove();
			});
			$.ajax({
				type: "POST",
				dataType: "json",
				url: "./functions/php/pokemonTeam.php",
				data: {action: 'viewStats', poke: $(poke).data('slot')},
			}).done(function(data) {
				// console.log(data);
				$(data.data).appendTo('#ViewPort').fadeIn(300);
				$('#PokeStats .MoveSet').sortable({
					helper: 'clone',
					placeholder: 'blank',
					// revert: true,
					stop:function(event,ui){
						var new_order = [];
						var poke = $('#PokeStats').data('id');
						$('#PokeStats .Move').each(function(key, value){
							var move = $(value)
								.clone()    //clone the element
								.children() //select all the children
								.remove()   //remove all the children
								.end()  //again go back to selected element
								.text();
							new_order[key] = move;
						});

						$.ajax({
							type: "POST",
							url: "./functions/php/pokemonTeam.php",
							data: {action: 'sortMoves', order: new_order, poke: poke},
						}).done(function(data) {
							console.log(data);
						});
					}
				}).disableSelection();
			});
		}
	}

	//Private Method
	function getTeam(){
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/pokemonTeam.php",
			data: {action: 'get'},
		}).done(function(data) {
			parseTeam(data);
		});
	}

	function parseTeam(team){
		var teamHTML = '';
		$('#PokemonTeam').html('');
		$.each(team, function(key, poke){
			var genderSym = (poke.gender == 'male') ? '&#9794;' : '&#9792;';

			if(poke.nickname === null)
				poke.nickname = poke.name;

			teamHTML = '<div class="Poke" data-id="'+poke.uuid+'" data-slot="'+key+'"><div class="img"><img src="sprites/pokemon/battle/'+poke.number+'/'+poke.shiny+'front.gif" /></div><div class="Nickname">'+poke.nickname+'</div><div class="HP"><div class="Number"><span>'+poke.HP+'</span>/'+poke.totals[0]+'</div><div class="bar"></div></div><div class="EXP">'+poke.exp+'/'+poke.exp_to_lvl+'<div class="bar"></div></div><div class="Lvl">Lv '+poke.level+' <span class="'+poke.gender+'">'+genderSym+'</span></div></div>';
			$('#PokemonTeam').append(teamHTML);
			$('#PokemonTeam .Poke:last .HP .bar').css({
				width: ((poke.HP/poke.totals[0]) * 100)+'%',
			});

			$('#PokemonTeam .Poke:last .EXP .bar').css({
				width: ((parseInt(poke.exp)/parseInt(poke.exp_to_lvl)) * 100)+'%',
			});
		});

		$('#PokemonTeam').sortable({
			placeholder: 'blank',
			helper: 'clone',
			revert:true,
			stop:function(event,ui){
				var new_order = [];
				$('#PokemonTeam .Poke').each(function(key, value){
					var slot = $(value).data('slot');
					new_order[key] = slot;
				});
				$.ajax({
					type: "POST",
					url: "./functions/php/pokemonTeam.php",
					data: {action: 'sort', order: new_order},
				}).done(function(data) {
					getTeam();
				});
			}
		}).disableSelection();
	}
}( window.team = window.team || {}, jQuery ));


$(function(){
	// game loop
	team.update();

	// $checkTeam = setInterval(function(){
	// 	team.update();
	// }, 3000);

	$('body').on('click', '#PokemonTeam .Poke, #PokeStats .Pokeball', function(){
		team.showStats($(this));
	});

	$('body').on('click','#PokeStats .Move', function(){
		if($('body').find('#PokeStats .active').length > 0 && $(this).hasClass('active') === false){
			$('body').find('#PokeStats .active .Desc').slideToggle(300);
			$('body').find('#PokeStats .active').toggleClass('active');
		}
		$(this).toggleClass('active');
		$(this).find('.Desc').slideToggle(300);
	});
});