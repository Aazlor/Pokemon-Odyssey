(function( combat, $, undefined ) {
	//Private Property
	var activePoke = '';
	var backButton = '<div id="back" data-button="back"><span>back</span></div>';
	// var isHot = true;

	//Public Property
	// skillet.ingredient = "Bacon Strips";

	//Public Method
	combat.start = function(poke){

		//Get Trainer Pokemon
		combat.getPoke('grabFirst');
		//Start Combat
		$('#Combat').fadeIn('slow');
		//Text
		var initalText = '<p class="text">Wild <b>'+poke.name+'</b> appeared!</p><div class="text_arrow"></div>';

		$('#Combat .box').html(initalText);
		$(function() {blinker = window.setInterval("$('.text_arrow').toggle();",500);});
		//Petals
		// 	$('#Combat').sakura();			// Currently requires way too much overhead

		//Display Enemy Pokemon
		var enemy_poke = '<img src="sprites/pokemon/battle/'+poke.number+'/'+poke.shiny+'front.gif" /><div class="Shadow"></div>';
		$('.Enemy').fadeOut('300');
		$('.Enemy .Poke').html(enemy_poke);
		
		var genderSym = (poke.gender == 'male') ? '&#9794;' : '&#9792;';
		poke.nickname = (typeof poke.nickname === 'undefined' || !poke.nickname) ? '' : '<div class="Nickname">'+poke.nickname+'</div>';
		var details = poke.nickname+'<div class="HP"><div class="Name">'+poke.name+'</div><div class="bar"></div><div class="Number"><span>'+poke.HP_pct+'</span>%/'+poke.HP_pct+'%</div></div><div class="Lvl">Lv '+poke.level+' '+genderSym+'</div>';

		$('.Enemy .Details').html(details);
		$('.Enemy .Details').addClass(poke.uuid);
		$('.Enemy').fadeIn('300');

		combatStep = 'waitForPlayer';
	},
	combat.getPoke = function(poke){
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/choosePoke.php",
			data: {trainerPoke: poke},
		}).done(function(data) {
			combat.choosePoke(data);
		});
	},
	combat.choosePoke = function(poke){
		poke_data = poke;
		activePoke = poke;
		var trainer_poke = '<img src="sprites/pokemon/battle/'+poke.number+'/'+poke.shiny+'back.gif" /><div class="Shadow"></div>';
		$('.Trainer .Poke').html(trainer_poke);

		var genderSym = (poke.gender == 'male') ? '&#9794;' : '&#9792;';
		poke.nickname = (typeof poke.nickname === 'undefined' || !poke.nickname) ? '' : '<div class="Nickname">'+poke.nickname+'</div>';
		var details = poke.nickname+'<div class="HP"><div class="Name">'+poke.name+'</div><div class="bar"></div><div class="Number"><span class="currHP">'+poke.HP+'</span>/<span class="totalHP">'+poke.totals[0]+'</span></div></div><div class="EXP"><div class="Lvl">Lv <span>'+poke.level+'</span> '+genderSym+'</div><div class="bar"></div></div>';

		$('.Trainer .Details').html(details);
		$('.Trainer .Details').addClass(poke.uuid);
		$('.Trainer .Details').data('poke',poke.team_no);
		$('.Trainer').find('.EXP .bar').css('width', ((parseInt(poke.exp)/parseInt(poke.exp_to_lvl))*100) +'%');

		combat.draw(poke);

		$('.Trainer').fadeIn('300');
	},
	combat.continue = function(){
		$('#Combat .box p:first-child').fadeOut(200).remove();
		window.clearInterval(blinker);
		if($('#Combat .box').find('p').length == 0){
			$('.text_arrow').css('display','none');
		}
		combat.mainMenu();
	},
	combat.mainMenu = function(){
		var menu = '<div id="MainMenu" data-menu="Home"><div data-button="menu" data-function="moveList"><span>Fight</span></div><div data-button="menu" data-function="bagOpen"><span>Bag</span></div><div data-button="menu" data-function="changePokemon"><span>Pok&eacute;mon</span></div><div data-button="menu" data-function="runAway" data-poke="'+activePoke.team_no+'"><span>Run</span></div></div>';
		$('#Combat .box').html(menu);
		combatStep = 'mainMenu';
	},
	combat.moveList = function(){
		var menu = '';
		for (var i = 0; i < activePoke.moveset.length; i++) {
			menu = menu + '<div data-button="move" data-move="'+activePoke.moveset[i]+'" data-poke="'+activePoke.team_no+'" data-numBtn="'+i+'"><span>'+activePoke.moveset[i]+'</span></div>';
		}
		$('#Combat .box').html('<div id="MainMenu" data-menu="Select">'+menu+backButton+'</div>');
	}
	combat.resolveAttacks = function(data, step){

		combatStep = 'resolvingAttacks';
		if(step == 0){
			resolvingAttacks.data = data;
			resolvingAttacks.step = 0;
		}

		if(resolvingAttacks.step < resolvingAttacks.data.length){

			console.log(resolvingAttacks.data[resolvingAttacks.step]);

			if(resolvingAttacks.data[resolvingAttacks.step].action == 'recall'){
				$('.Trainer').fadeOut('300');
			}
			if(resolvingAttacks.data[resolvingAttacks.step].action == 'sendOut'){
				combat.choosePoke(resolvingAttacks.data[resolvingAttacks.step].poke);
			}
			switch(resolvingAttacks.data[resolvingAttacks.step].type) {
				case 'text':
					var displayText = '<p class="text">'+resolvingAttacks.data[resolvingAttacks.step].text+'</p><div class="text_arrow"></div>';
					$('#Combat .box').html(displayText);
					resolvingAttacks.step++;
					break;
				case 'draw':
					var draw = resolvingAttacks.data[resolvingAttacks.step].draw;
					combat.draw(draw);
					resolvingAttacks.step++;
					combat.resolveAttacks();
					break;
				case 'drawEXP':
					var draw = resolvingAttacks.data[resolvingAttacks.step].drawEXP;
					combat.drawEXP(resolvingAttacks.data[resolvingAttacks.step]);
					resolvingAttacks.step++;
					combat.resolveAttacks();
					break;
				case 'endBattle':
					combatStep = 0;
					inCombat = false;
					// $('#Combat').sakura('stop');		/// TOO MUCH OVERHEAD FOR NOW
					$('#Combat').fadeOut('slow');
					break;
				case 'swapOut':
					var poke = resolvingAttacks.data[resolvingAttacks.step].poke;
					resolvingAttacks.step++;
					combat.resolveAttacks();
					break;
				case 'fainted':
					combat.fainted();
					return false;
				case 'KO':
					$('#ViewPort div').fadeOut('750', function(){
						location.reload();
					});
					return false;
				case 'victory':
					$('#Combat .Enemy').fadeOut('3000');
					resolvingAttacks.step++;
					combat.resolveAttacks();
					break;
				case 'learnMove':
					combatStep = 'waitForPlayer';
					stats.learnMove(resolvingAttacks.data[resolvingAttacks.step]);
					return false;
				case 'evolve':
					stats.evolve(resolvingAttacks.data[resolvingAttacks.step]);
					return false;
				default:
					console.log('Something fell through...  ||  resolveAttacks(switch->default)');
					combat.mainMenu();
					break;
			}
		}
		else{
			combat.mainMenu();
		}

		team.update();
	}
	combat.draw = function(poke){
		if(poke.hp === undefined)
			poke.hp = parseInt(poke.HP);
		if(poke.to === undefined)
			poke.to = (parseInt(poke.hp)/parseInt(poke.totals[0])) * 100;

		$('.' + poke.uuid + '').find('.HP .bar').animate({
			'width': (poke.to)+'%',
		}, 333);
		if(poke.to > 50)	$('.' + poke.uuid + '').find('.HP .bar').css({'background-color': '#7CA429',});
		if(poke.to < 50)	$('.' + poke.uuid + '').find('.HP .bar').css({'background-color': '#cbb000',});
		if(poke.to < 15)	$('.' + poke.uuid + '').find('.HP .bar').css({'background-color': '#bb0000',});

		if(poke.totals != undefined)
			animateValue($('.' + poke.uuid + '').find('.Number span.totalHP'), $('.' + poke.uuid + '').find('.Number span.totalHP').text(), poke.totals[0], 333);		
		if(poke.level != undefined)
			animateValue($('.' + poke.uuid + '').find('.Lvl span'), $('.' + poke.uuid + '').find('.Lvl span').text(), poke.level, 333);		
		animateValue($('.' + poke.uuid + '').find('.Number span.currHP'), $('.' + poke.uuid + '').find('.Number span.currHP').text(), poke.hp, 333);		
	}
	combat.drawEXP = function(poke){
		$('.' + poke.uuid + '').find('.EXP .bar').animate({
			'width': poke.bar+'%',
		}, 333);
		animateValue($('.' + poke.uuid + '').find('.EXP .Lvl span'), $('.' + poke.uuid + '').find('.EXP .Lvl span').text(), poke.level, 333);		
	}
	combat.runAway = function(poke){
		var poke = $(poke).data('poke');
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/combat.php",
			data: {poke: poke, action: 'run'},
		}).done(function(data) {
			console.log(data);
			combat.resolveAttacks(data, 0);
		});		
	}
	combat.changePokemon = function(){
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/choosePoke.php",
			data: {trainerPoke: 'showTeam'},
		}).done(function(data) {
			combat.displayTeam(data, 'swapOut');
		});		
	}
	combat.displayTeam = function(team, action){
		var menu = '';
		var b = 0;
		$.each(team, function(key, poke){
			b++;
			if(poke.nickname == null)
				poke.nickname = poke.name;

			if(poke.HP == 0)
				menu += '<div data-button="swapOut" data-poke="'+key+'" class="swapOut fainted" data-numBtn="'+b+'" data-action=""><span><img src="sprites/pokemon/battle/'+poke.number+'/'+poke.shiny+'front.gif" /><p>'+poke.nickname+'<br>Lv '+poke.level+'</p></span></div>';
			else
				menu += '<div data-button="swapOut" data-poke="'+key+'" class="swapOut" data-numBtn="'+b+'" data-action="'+action+'"><span><img src="sprites/pokemon/battle/'+poke.number+'/'+poke.shiny+'front.gif" /><p>'+poke.nickname+'<br>Lv '+poke.level+'</p></span></div>';
		});
		if(action == 'fainted'){
			$('#Combat .box').html('<div id="MainMenu" class="Team" data-menu="Team">'+menu+'</div>');
		}
		else{
			$('#Combat .box').html('<div id="MainMenu" class="Team" data-menu="Team">'+menu+backButton+'</div>');
		}
	}
	combat.fainted = function(){
		combatStep = 'fainted';
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "./functions/php/choosePoke.php",
			data: {trainerPoke: 'showTeam'},
		}).done(function(data) {
			console.log(data);
			combat.displayTeam(data, 'fainted');
		});
	}

	//Private Method
	// function addItem( item ) {
	// 	if ( item !== undefined ) {
	// 		console.log( "Adding " + $.trim(item) );
	// 	}
	// }
}( window.combat = window.combat || {}, jQuery ));

$(function(){
	$('body').on('click', '#MainMenu div', function(){

		if($(this).data('button') == 'menu'){
			var functionName = $(this).data('function');
			combat[functionName](this);
			return false;
		}
		if($(this).data('button') == 'move'){
			var move = $(this).data('move');
			var poke = $(this).data('poke');
			$.ajax({
				type: "POST",
				// dataType: "json",
				url: "./functions/php/combat.php",
				data: {move: move, poke: poke, action: 'attack'},
			}).done(function(data) {
				console.log(data);
				var data = JSON.parse(data);
				combat.resolveAttacks(data, 0);
				return false;
			});
			return false;
		}
		if($(this).data('button') == 'back'){
			combat.mainMenu();
			return false;
		}
		if($(this).data('button') == 'swapOut'){
			var newpoke = $(this).data('poke');
			var action = $(this).data('action');
			var poke = $('.Trainer .Details').data('poke');
			$.ajax({
				type: "POST",
				dataType: "json",
				url: "./functions/php/combat.php",
				data: {poke: poke, newpoke: newpoke, action: action},
			}).done(function(data) {
				console.log(data);
				combat.resolveAttacks(data, 0);
				return false;
			});
			return false;
		}
	});
});