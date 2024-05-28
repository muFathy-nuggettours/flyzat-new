var TiltAnimation = (function(){
	var hero, mouseX, mouseY, options;
	var _init = function(settings){
		options = settings;
		if (!options.speed){ options.speed = 1; }
		if (!options.perspective){ options.perspective = 900; }
		if (!options.xMultiplier){ options.xMultiplier = 5; }
		if (!options.yMultiplier){ options.yMultiplier = 5; }
		mouseX = 0;
		mouseY = 0;
		slider = options.component;
		_addEventHandlers();
	};
	var _addEventHandlers = function(){
		window.addEventListener("mousemove", _getMousePos, false);
		if (window.DeviceMotionEvent != undefined) {
			window.addEventListener("devicemotion", _accelerometerUpdate, false);
		}
	};
	var _accelerometerUpdate = function(e) {
		var aX = event.accelerationIncludingGravity.x * 1;
		var aY = event.accelerationIncludingGravity.y * 1;
		var aZ = event.accelerationIncludingGravity.z * 1;
		var xPosition = Math.atan2(aY, aZ) * 20;
		var yPosition = Math.atan2(aX, aZ) * 20;
		xPosition = Math.round(xPosition * 1000) / 1000;
		yPosition = Math.round(yPosition * 1000) / 1000;
		_animate(yPosition, xPosition);
	};
	var _getMousePos = function(e){
		e = e || window.event;
		mouseX = e.pageX;
		mouseY = e.pageY;
		var xPos = mouseX / window.innerWidth - 0.5;
		var yPos = mouseY / window.innerHeight - 0.5;
		var rotationYValue = options.xMultiplier * xPos;
		var rotationXValue = options.yMultiplier * yPos;
		_animate(rotationYValue, rotationXValue);
	};
	var _animate = function(rotationYValue, rotationXValue) {
		TweenLite.to(slider, options.speed, {
			rotationY: rotationYValue,
			rotationX: rotationXValue,
			ease: Power1.easeOut,
			transformPerspective: options.perspective,
			transformOrigin: "center"
		});
	};
	return {
		init: _init
	};
})();