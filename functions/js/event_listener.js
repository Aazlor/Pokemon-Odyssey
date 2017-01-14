$(function(){

	// Move 
	$('body').on('keydown keyup click', function( e ) {
		var kc = e.keyCode || e.which;

		///OUT OF COMBAT
		if(inCombat === false)
			if($.inArray(moveKeys, kc)){
				moveKeys[kc] = e.type == 'keydown';
		}

	});
	// Move 
	$('body').on('keydown click', function( e ) {
		e.stopPropagation();
		e.preventDefault();
		var kc = e.keyCode || e.which;
		
		///IN COMBAT
		if(inCombat === true){
			if(kc == 32 || kc == 13 || e.type == 'click'){
				if(combatStep == 'waitForPlayer')
					combat.continue();
				if(combatStep == 'resolvingAttacks')
					combat.resolveAttacks();
				// console.log(e);
				return false;
			}
			if(kc == 27){
				if($('#Combat').find('#MainMenu').data('menu') == 'Home'){
					var poke = $('#Combat').find('div[data-poke]');
					combat.runAway(poke);
				}
				else
					combat.mainMenu();
				return false;
			}
			if(kc == 49 || kc == 50 || kc == 51 || kc == 52 || kc == 53 || kc == 54){

				return false;

				if($('#Combat').find('#MainMenu').data('menu') == 'Home'){
					var numBtn = kc - 49;
					var el = $('#Combat').find('div[data-button="menu"]').eq(numBtn);
					if(el !== undefined){
						var functionName = $(el).data('function');
						combat[functionName](el);
					}
					return false;
				}
				if($('#Combat').find('#MainMenu').data('menu') == 'Select'){
					var numBtn = kc - 48;
					var el = $('#Combat').find('div[data-numBtn="'+numBtn+'"]')
					var move = $(el).data('move');
					var poke = $(el).data('poke');
					$.ajax({
						type: "POST",
						dataType: "json",
						url: "./functions/php/wild_attack.php",
						data: {move: move, poke: poke, action: 'attack'},
					}).done(function(data) {
						// console.log(data);
						combat.resolveAttacks(data, 0);
					});
					return false;
				}
				if($('#Combat').find('#MainMenu').data('menu') == 'Team'){
					var numBtn = kc - 48;
					var el = $('#Combat').find('div[data-numBtn="'+numBtn+'"]')
					var poke = $(el).data('poke');
					// $.ajax({
					// 	type: "POST",
					// 	dataType: "json",
					// 	url: "./functions/php/wild_attack.php",
					// 	data: {move: move, poke: poke, action: 'attack'},
					// }).done(function(data) {
					// 	// console.log(data);
					// 	combat.resolveAttacks(data, 0);
					// });
					combat.getPoke();
					return false;
				}
			}
		}

		// console.log(kc);
    });

});