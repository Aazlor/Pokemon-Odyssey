var AABB = {
	collide: function (el1, el2) {
		var rect1 = document.getElementById(el1).getBoundingClientRect();
		var rect2 = el2.getBoundingClientRect();
		return !(
			rect1.top > rect2.bottom ||
			rect1.right < rect2.left ||
			rect1.bottom < rect2.top ||
			rect1.left > rect2.right
		);
	},

	inside: function (el1, el2) {
		var rect1 = document.getElementById(el1).getBoundingClientRect();
		var rect2 = el2.getBoundingClientRect();

		return (
			((rect2.top <= rect1.top) && (rect1.top <= rect2.bottom)) &&
			((rect2.top <= rect1.bottom) && (rect1.bottom <= rect2.bottom)) &&
			((rect2.left <= rect1.left) && (rect1.left <= rect2.right)) &&
			((rect2.left <= rect1.right) && (rect1.right <= rect2.right))
		);
	}
};


var checkEncounter = function(){
	$.each($('.encounter'), function(index){
		if((AABB.collide('Trainer', this, index) || AABB.inside('Trainer', this) ) && window.inCombat != 1){
			// var int = randomInt(0, 1000);	//	Production
			var int = randomInt(0, 1);		//	Development
			// var int = randomInt(0, 100);		//	Psuedo-Testing

			if(int == 0){
				inCombat = true;
				$.each(moveKeys, function(k, v){
					if(v === true)
						moveKeys[k] = false;
				});

				$.ajax({
					type: "POST",
					dataType: "json",
					url: "./functions/php/wild_encounter.php",
					data: {playerID: 1, route: route},
				}).done(function(msg) {
					// console.log(msg);
					combat.start(msg);
				});
			}
		}
	});

}