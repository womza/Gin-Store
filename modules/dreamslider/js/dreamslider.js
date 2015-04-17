$(function(){

	if (typeof(dreamslider_speed) == 'undefined')
		dreamslider_speed = 500;
	if (typeof(dreamslider_pause) == 'undefined')
		dreamslider_pause = 3000;
	if (typeof(dreamslider_loop) == 'undefined')
		dreamslider_loop = true;

	$('#dreamslider').dmSlider({
		infiniteLoop: dreamslider_loop,
		hideControlOnEnd: false,
		pager: true,
		autoHover: true,
		auto: dreamslider_loop,
		speed: dreamslider_speed,
		pause: dreamslider_pause,
		controls: true
	});
});