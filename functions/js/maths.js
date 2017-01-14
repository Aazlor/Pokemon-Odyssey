function randomInt(min, max) {
	min = Math.ceil(min);
	max = Math.floor(max);
	return Math.floor(Math.random() * (max - min + 1)) + min;
}


function randomMovementBG(ele){
	 var p = [0, 0], speed = 10, runMe = function () {
		var angle = Math.random() * 2 * Math.PI;
		var d = [
			speed * Math.cos(angle), 
			speed * Math.sin(angle)
		];

		for (var i = 0; i < 2; i++)
		   p[i] = (p[i] + d[i] > 100 || p[i] + d[i] < 0) ? p[i] - d[i] : p[i] + d[i];

		$(ele).animate({
			backgroundPositionX: p[0] + '%',
			backgroundPositionY: p[1] + '%'
		}, 1000, 'linear', runMe);
	 };
	
	 runMe();
};



function animateValue(id, start, end, duration) {
	start = parseInt(start);
	var range = end - start;
	var current = start;
	var increment = end > start? 1 : -1;
	var stepTime = Math.abs(Math.floor(duration / range));
	var timer = setInterval(function() {
		if (current == end) {
			clearInterval(timer);
		}
		else{
			current += increment;
			$(id).html(current);			
		}
	}, stepTime);
}