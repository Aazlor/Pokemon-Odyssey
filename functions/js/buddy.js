var buddy = {}

$(function(){

	buddy.id = '001';
	buddy.size = 32;
	buddy.x = $('#Trainer').position().left - (buddy.size);
	buddy.y = $('#Trainer').position().top - $('#Trainer').outerHeight();
	buddy.speedMultiplier = speed + buddy.size / 100;
	buddy.element = document.getElementById("Buddy");
	buddy.element.style.left = buddy.x + 'px';
	buddy.element.style.top = buddy.y + 'px';


	var sprite = 'url(sprites/pokemon/overworld/'+buddy.id+'/down.png)';

	$('#Buddy').css({
		'background': sprite,
		'height': buddy.size+'px',
		'width': buddy.size+'px',
	});
});

var buddyFollow = function(dx, dy, t){


	if(dy == 1){
		dist = $('#Trainer').position().top - buddy.y;
		if(Math.abs(dist) > 45){
			move = dist - 32;
			buddy.y += move;
			$('#Buddy').stop().animate({
				top: buddy.y,
			}, 1000);
		}
	}
	if(dx == -1){
		dist = $('#Trainer').position().left - buddy.x;
		if(Math.abs(dist) > 45){
			move = dist - 32;
			buddy.x += move;
			$('#Buddy').stop().animate({
				left: buddy.x
			}, 1000);
		}
	}
	if(dy == -1){
		dist = $('#Trainer').position().top - buddy.y;
		if(Math.abs(dist) > 45){
			move = dist - 32;
			buddy.y += move;
			$('#Buddy').stop().animate({
				top: buddy.y + $('#Trainer').outerWidth(),
			}, 1000);
		}
	}
	if(dx == 1){
		dist = $('#Trainer').position().left - buddy.x;
		if(Math.abs(dist) > 45){
			move = dist - 32;
			buddy.x += move;
			$('#Buddy').animate({
				left: buddy.x,
				step: function(now, fx){
					console.log(now);
				},
				easing:'linear'
			}, 1000);
		}
	}


	buddy.x += (dx||0) * t;
	buddy.y += (dy||0) * t;
	buddy.element.style.left = buddy.x + 'px';
	buddy.element.style.top = buddy.y + 'px';

	// var dx = 0;
	// var dy = 0;

	// if(Math.abs(distanceFromTrainerY) >= buddy.size * 1.5){
	// 	if(distanceFromTrainerY < 0){
	// 		dy = -1;
	// 		$('#Buddy').css({
	// 			'background': 'url(sprites/pokemon/overworld/'+buddy.id+'/up.png)',
	// 		});
	// 	}
	// 	else{
	// 		dy = 1;
	// 		$('#Buddy').css({
	// 			'background': 'url(sprites/pokemon/overworld/'+buddy.id+'/down.png)',
	// 		});
	// 	}

	// 	var catchUp = 1;
	// }
	// else if(Math.abs(distanceFromTrainerX) >= buddy.size * 1.5){
	// 	if(distanceFromTrainerX < 0){
	// 		dx = -1;
	// 		$('#Buddy').css({
	// 			'background': 'url(sprites/pokemon/overworld/'+buddy.id+'/left.png)',
	// 		});
	// 	}
	// 	else{
	// 		dx = 1;
	// 		$('#Buddy').css({
	// 			'background': 'url(sprites/pokemon/overworld/'+buddy.id+'/right.png)',
	// 		});
	// 	}
	// 	var catchUp = 1;
	// }

	// if(catchUp == 1){
	// 	buddy.x += distanceFromTrainerX;
	// 	buddy.y += distanceFromTrainerY;
	// 	buddy.element.style.left = buddy.x + 'px';
	// 	buddy.element.style.top = buddy.y + 'px';
	// }
	// else{
	// 	$('#Buddy').css('animation', '');
	// }
}