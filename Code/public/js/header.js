// // What's this for?
// var supportedFlag = $.keyframe.isSupported();

// $.keyframe.define([{
// 	name: 'flippy',
//     '0%':   {-webkit-transform: rotateX(70deg), transform: rotateX(70deg), -webkit-transform-origin: top, transform-origin: top, opacity: 0},
//     '100%': {-webkit-transform: rotateX(0deg), transform: rotateX(0deg), -webkit-transform-origin: top, transform-origin: top, opacity: 1}
// }]);

// Right, jQuery is a thing. 
$(document).ready(function(){ 

	$("#logInButton").click(function(event){ // Show/hide dialog if you click on the log in button. 
		document.getElementById('logInHidden').classList.toggle('swing-in-top-bck');
		// $('#logInHidden').playKeyframe({
		// 	name: 'flippy',
		// 	duration: 2000
		// });
	});

});




// $(selector).playKeyframe({
// name: 'flippy', // name of the keyframe you want to bind to the selected element
// duration: 90, // [optional, default: 0, in ms] how long you want it to last in     milliseconds
// timingFunction: 'linear', // [optional, default: ease] specifies the speed curve of the animation
// delay: 0, //[optional, default: 0, in ms]  how long you want to wait before the animation starts in milliseconds, default value is 0
// repeat: 'infinite', //[optional, default:1]  how many times you want the animation to repeat, default value is 1
// direction: 'alternate', //[optional, default: 'normal']  which direction you want the frames to flow, default value is normal
// fillMode: 'running', //[optional, default: 'forward']  how to apply the styles outside the animation time, default value is forwards
// complete: function(){} //[optional]  Function fired after the animation is complete. If repeat is infinite, the function will be fired every time the animation is restarted.
// });
