(function( stats, $, undefined ) {
	//Private Property
		// var isHot = true;

	//Public Property
		// skillet.ingredient = "Bacon Strips";

	//Public Method
	stats.learnMove = function(poke_data){
		window.poke_data = poke_data;
		combatStep = 'selectMove';

		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/pokemonMoves.php",
			data: {data: poke_data},
		}).done(function(data) {
			console.log(data);
			if(data.function == 'select'){
				$('#ViewPort').append(data.display.object);
				$('#ViewPort').find('#MoveSelector').slideDown('600');
				var displayText = '<p class="text">'+data.text+'</p><div class="text_arrow"></div>';
				$('#Combat .box').html(displayText);
			}
			else if(data.function == 'insert'){
				$.each(data.display, function(key, val){
					// console.log(key, val);
					resolvingAttacks.data[resolvingAttacks.step] = data.display[key];
				});
				combat.resolveAttacks(resolvingAttacks.data, resolvingAttacks.step);
			}
		});
	}

	stats.forgetMove = function(data, moveNo){
		$('#ViewPort').find('#MoveSelector .Desc div').fadeOut(300, function(){
			$('#ViewPort').find('#MoveSelector .Desc').html('');
		});
		$('#ViewPort').find('.Move.active').slideUp(300, function(){
			$(this).remove();

			if($('#ViewPort').find('.Move').length <= 4){
				$('#ViewPort').find('#MoveSelector').slideUp('600', function(){
					$(this).remove();
					combat.resolveAttacks(resolvingAttacks.data, resolvingAttacks.step++);
				});
			}
		});

		var displayText = '<p class="text">'+data.text+'</p><div class="text_arrow"></div>';
		$('#Combat .box').html(displayText);
	}

	stats.evolve = function(data){

		combatStep = 'Evolution';

		var html = '<div id="Evolution"><div class="hiddenBG Bug"></div><div class="Poke"><img src="sprites/pokemon/battle/'+data.from+'/front.gif"><div><div>';
		$('body').append(html);
		$('#ViewPort').addClass('fade');

		if($('body').find('#Combat .Trainer .Poke img').attr('src') == 'sprites/pokemon/battle/'+data.from+'/back.gif'){
			var alterCombat = 1;
		}

		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/evolve.php",
			data: {action: 'evolve', poke: data.poke, to: data.to},
		}).done(function(data){
			console.log(data);
			evolvePoke(data, alterCombat);
		});
	}

	//Private Method
	function getmoves(){
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/pokemonmoves.php",
			data: {action: 'get'},
		}).done(function(data) {
			parsemoves(data);
		});
	}

	function evolvePoke(poke, alterCombat){

		if(alterCombat == 1){
			setTimeout(function(){
				$('body').find('#Combat .Trainer .Poke img').addClass('blur');
				$('#PokemonTeam').fadeOut('3000');
				$('body').find('#Evolution').fadeIn('300');
				$('body').find('#Evolution .Poke img, .hiddenBG').addClass('transform');

				setTimeout(function() {
					$('body').find('#Evolution .Poke img').attr('src', 'sprites/pokemon/battle/'+poke.number+'/front.gif');
					$('body').find('#Evolution .Poke img, .hiddenBG').removeClass('transform');
					$('body').find('#Combat .Trainer .Name').fadeOut('2600', function(){
						$(this).html(poke.name).fadeIn('1400');
						poke.hp = poke.totals[0];
						combat.draw(poke);
					});
					$('#PokemonTeam').fadeIn('3000');
				}, 4000);
				setTimeout(function(){
					$('body').find('#Evolution').fadeOut('300', function(){
						$(this).remove();
					});
					$('#ViewPort').removeClass('fade');

					$('body').find('#Combat .Trainer .Poke img').attr('src', 'sprites/pokemon/battle/'+poke.number+'/back.gif');				
					$('body').find('#Combat .Trainer .Poke img').removeClass('blur');

					combat.resolveAttacks(resolvingAttacks.data, resolvingAttacks.step++);
				}, 7200);
			}, 100);
		}
		else{
			setTimeout(function(){
				$('#PokemonTeam').fadeOut('3000');
				$('body').find('#Evolution').fadeIn('300');
				$('body').find('#Evolution .Poke img, .hiddenBG').addClass('transform');

				setTimeout(function() {
					$('body').find('#Evolution .Poke img').attr('src', 'sprites/pokemon/battle/'+poke.number+'/front.gif');
					$('body').find('#Evolution .Poke img, .hiddenBG').removeClass('transform');
					$('#PokemonTeam').fadeIn('3000');
				}, 4000);
				setTimeout(function(){
					$('body').find('#Evolution').fadeOut('300', function(){
						$(this).remove();
					});
					$('#ViewPort').removeClass('fade');

					combat.resolveAttacks(resolvingAttacks.data, resolvingAttacks.step++);
				}, 7200);
			}, 100);
		}
	}

}( window.stats = window.stats || {}, jQuery ));


$(function(){
	$('body').on('click', '#MoveSelector .Move', function(){
		console.log(this);
		$('.Move').removeClass('active');
		$(this).addClass('active');
		var html = '<div class="Type '+$(this).data('type')+'"><img src="sprites/icons/'+$(this).data('type').toLowerCase()+'.png" alt="'+$(this).data('type')+'" title="'+$(this).data('type')+'"><br>'+$(this).data('type')+'</div>';
		html += '<div class="Category"><img src="sprites/icons/'+$(this).data('category').toLowerCase()+'.png"></div>';
		html += '<div><span>Power</span> '+$(this).data('power')+'</div>';
		if(parseFloat($(this).data('accuracy')) > 0){
			var acc = (parseFloat($(this).data('accuracy')) * 100) + '%';
			html += '<div><span>Accuracy</span> '+acc+'</div>';
		}
		html += '<div><p>'+$(this).data('desc')+'</p></div>';
		html += '<div class="Forget" data-id="'+$(this).data('id')+'">Forget This Move</div>';
		$('.Desc').html(html);
	});

	$('body').on('click', '#MoveSelector .Forget', function(){
		var moveNo = $(this).data('id');
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/pokemonmoves.php",
			data: {action: 'forget', poke_info: window.poke_data, move: moveNo},
		}).done(function(data) {
			console.log(data);
			stats.forgetMove(data, moveNo);
		});
	});
})