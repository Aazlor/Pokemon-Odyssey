$(function(){
	/// store reference to wrapper's position and element
	var wrapper = {
		x: $('#Wrapper').position().left,
		y: $('#Wrapper').position().top,
		speedMultiplier: speed,
		element: document.getElementById("Wrapper")
	};

	/// wrapper movement update
	var moveWrapper = function(dx, dy, t){
		if(!t){
			t = wrapper.speedMultiplier;
		}

		wrapper.x += (dx||0) * t;
		wrapper.y += (dy||0) * t;	
		wrapper.element.style.left = wrapper.x + 'px';
		wrapper.element.style.top = wrapper.y + 'px';

		checkEncounter();
	};

	/// wrapper control
	var detectWrapperMovement = function(){
		if(inCombat === false){
			//moving bg, not char, so flipped x/y
			if ( moveKeys[moveKeys.UP] || moveKeys[moveKeys.W] ){
				$('#Trainer .Sprite').css({
					'transform': 'scaleX(1)',
					'filter': 'none',
					'background-image': 'url(sprites/trainer-up.png)',
				});
				var collision = collisionCheck(0);
				if(collision > 0){
					moveWrapper(0, 1, collision);
					var moving = 1;
				}
			}
			if ( moveKeys[moveKeys.RIGHT] || moveKeys[moveKeys.D] ){
				$('#Trainer .Sprite').css({
					'transform': 'scaleX(-1)',
					'filter': 'FlipH',
					'background-image': 'url(sprites/trainer-left.png)',
				});
				var collision = collisionCheck(1);
				if(collision > 0){
					moveWrapper(-1, 0, collision);
					var moving = 1;
				}
			}
			if ( moveKeys[moveKeys.DOWN] || moveKeys[moveKeys.S] ){
				$('#Trainer .Sprite').css({
					'transform': 'scaleX(1)',
					'filter': 'none',
					'background-image': 'url(sprites/trainer-down.png)',
				});
				var collision = collisionCheck(2);
				if(collision > 0){
					moveWrapper(0, -1, collision);
					var moving = 1;
				}
			}
			if ( moveKeys[moveKeys.LEFT] || moveKeys[moveKeys.A] ){
				$('#Trainer .Sprite').css({
					'transform': 'scaleX(1)',
					'filter': 'none',
					'background-image': 'url(sprites/trainer-left.png)'
				});
				var collision = collisionCheck(3);
				if(collision > 0){
					moveWrapper(1, 0, collision);
					var moving = 1;
				}
			}

			if(moving == 1){
				$('#Trainer .Sprite').css('animation', 'TrainerWalking 0.5s steps(4) infinite');
				$('#Trainer').css('z-index', $('#Trainer').position().top - wrapper.y);
			}
			else{
				$('#Trainer .Sprite').css("animation", '' );
			}
		}
		else{
			$('#Trainer .Sprite').css("animation", '' );
		}
	};

	/// update current position on screen
	moveWrapper();

	/// game loop
	$moveInterval = setInterval(function(){
		detectWrapperMovement();
	}, 1000/24);
});