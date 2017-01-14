$(function(){

	$.each($('.solid, .depth'), function(){
		$(this).css('z-index', $(this).position().top);
	});

});