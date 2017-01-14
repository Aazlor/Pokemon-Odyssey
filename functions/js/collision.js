var collisionCheck = function(dir) {
	var dirCheck = [];
	dirCheck[dir] = 0 + speed;
	var distance = parseInt(speed);

	objXY = {};

	$('.solid').each(function(index){
		objXY[index] = {
			2 : Math.floor( Math.abs($('#Wrapper').offset().top + $(this).position().top) - TrainerXY[2]),								//Obj North Edge
			3 : Math.floor( Math.abs($('#Wrapper').offset().left + $(this).position().left + $(this).outerWidth(true)) - TrainerXY[3]),	//Obj East Edge
			0 : Math.floor( Math.abs($('#Wrapper').offset().top + $(this).position().top + $(this).outerHeight(true)) - TrainerXY[0]),	//Obj South Edge
			1 : Math.floor( Math.abs($('#Wrapper').offset().left + $(this).position().left) - TrainerXY[1]),							//Obj West Edge
		};

		if(dirCheck[dir] && Math.abs(objXY[index][dir]) <= speed){
			if(dirCheck[0] || dirCheck[2]){
				if(objXY[index][1] < 0 && objXY[index][3] > 0){
					distance = Math.abs(objXY[index][dir]);
				}
			}
			if(dirCheck[1] || dirCheck[3]){
				if(objXY[index][0] > 0 && objXY[index][2] < 0){
					distance = Math.abs(objXY[index][dir]);
				}
			}
		}

	});
	return parseInt(distance);
}