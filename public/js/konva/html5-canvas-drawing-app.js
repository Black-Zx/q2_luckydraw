var stage;
var canvas;
var context;
var canvasWidth = 600;
var canvasHeight = 600; //1080;
var lineWidth = 4;

var clickX = new Array();
var clickY = new Array();
var clickColor = new Array();
var clickTool = new Array();
var clickSize = new Array();
var clickDrag = new Array();
var paint = false;

var colorList = ['#c82262', '#c5883e', '#b45fff', '#c4ab92', '#ffdb4b', '#ff6600', '#ff99c1', '#ffff00', '#e6e6e6', '#dbf8c6', '#cceaff', '#1fa3a3'];
var currentColor = 0;
var curColor;

var toolList = ['crayon'];
var currentTool = 0;
var curTool = toolList[currentTool];

var sizeList = [2];
var currentSize = 1;
var curSize = sizeList[currentSize];

var offsetLeft;
var offsetTop;
var layerRef;

function selectColor(colorIndex) {
	// update color
	currentColor = (colorIndex+1 >= 10) ? (colorIndex+1) : ('0'+(colorIndex+1));
	curColor = colorList[colorIndex];

	// console.log(currentColor);

	// button class control
	$('.color-select .color').removeClass('active');
	$('.color-select .color.color-'+currentColor).addClass('active');

	// auto select pen
	if(curTool == 'eraser') selectTools(0);
}

function selectTools(toolIndex) {
	// update tool
	currentTool = toolIndex;
	curTool = toolList[currentTool];

	// button class control
	$('.tool-wrapper .tool').removeClass('active');
	$('.tool-wrapper .tool.tool-0'+(currentTool+1)).addClass('active');
}

function selectSize(sizeIndex) {
	// update size
	currentSize = sizeIndex;
	curSize = sizeList[sizeIndex];

	// console.log(mouseWhiteCircle);

	if(mouseWhiteCircle){
		var thisSize = curSize;
		if(thisSize > 2){
			thisSize = curSize*0.5;
		}
		mouseWhiteCircle.radius(thisSize);
	};

	// button class control
	$('.size-select .size').removeClass('active');
	$('.size-select .size.size-0'+(currentSize+1)).addClass('active');
}

/**
* Creates a canvas element, loads images, adds events, and draws the canvas for the first time.
*/
function prepareCanvas(canvasRef, offsetL, offsetT, layer, stageRef) {
	stage = stageRef;
	canvas = canvasRef;
	// recalculate actual width and height
	var tempWidth = $('#canvasDiv').width();
	var tempHeight = tempWidth*canvasHeight/canvasWidth;
	// assign new width and height
	canvasWidth = tempWidth;
	canvasHeight = tempHeight;
	// console.log(canvasWidth);
	// console.log(canvasHeight);

	canvas.setAttribute('width', canvasWidth);
	canvas.setAttribute('height', canvasHeight);

	selectColor(0);

	context = canvas.getContext("2d"); // Grab the 2d canvas context

	// drawing property
	context.strokeStyle = curColor; //clickColor[clickColor.length-1];
	context.lineJoin = "round";
	context.lineWidth = curSize; //clickSize[clickSize.length-1];
	
	offsetLeft = offsetL;
	offsetTop = offsetT;
	layerRef = layer;

	var lastPointerPosition;
	
	// Add mouse events
	$('#canvas').on('mousedown touchstart', function(e){
		lastPointerPosition = stage.getPointerPosition();
		paint = true;
		/*
		console.log(e);
		console.log(window.isDraw);
		console.log(stage.getPointerPosition());

		// var rect = canvas.getBoundingClientRect();

		if(window.isDraw){
			/*
			// Mouse down location
			if(e.type == 'touchstart' || e.type == 'touchmove' || e.type == 'touchend' || e.type == 'touchcancel'){
				var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
				var mouseX = touch.clientX - rect.left; //touch.pageX - offsetLeft;
				var mouseY = touch.clientY - rect.top; //touch.pageY - offsetTop;
			} else if (e.type == 'mousedown' || e.type == 'mouseup' || e.type == 'mousemove' || e.type == 'mouseover'|| e.type=='mouseout' || e.type=='mouseenter' || e.type=='mouseleave') {
				mouseX = e.clientX - rect.left; //e.pageX - offsetLeft;
				mouseY = e.clientX - rect.top; //e.pageY - offsetTop;
			}*/
			/*
			console.log(stage);
			var mousePos = stage.getPointerPosition();
			if(!mousePos) {
				// probably is touch event
				mousePos = stage.setPointersPositions(e);
				mousePos = stage.getPointerPosition();
			};
			mouseX = mousePos.x;
			mouseY = mousePos.y;

			console.log('x: ' + mouseX + ', y: ' + mouseY);

			paint = true;
			addClick(mouseX, mouseY, false);
			console.log('touchstart');
			// redraw();
		};
		*/
	});
	
	$('#canvas').on('mousemove touchmove', function(e){
		if(paint == true){
			context.globalCompositeOperation = 'source-over';
	        context.beginPath();

	        var localPos = {
	        	x: lastPointerPosition.x,
	        	y: lastPointerPosition.y,
	        };
	        context.moveTo(localPos.x, localPos.y);
	        var pos = stage.getPointerPosition();
	        localPos = {
	        	x: pos.x,
	        	y: pos.y,
	        };
	        context.lineTo(localPos.x, localPos.y);
	        context.closePath();
	        context.stroke();

	        lastPointerPosition = pos;
	        // update Konva layer
			layerRef.draw();

			/*
			if(e.type == 'touchstart' || e.type == 'touchmove' || e.type == 'touchend' || e.type == 'touchcancel'){
				var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
				addClick(touch.pageX - offsetLeft, touch.pageY - offsetTop, true);
			} else if (e.type == 'mousedown' || e.type == 'mouseup' || e.type == 'mousemove' || e.type == 'mouseover'|| e.type=='mouseout' || e.type=='mouseenter' || e.type=='mouseleave') {
				addClick(e.pageX - offsetLeft, e.pageY - offsetTop, true);
			}*//*
			var mousePos = stage.getPointerPosition();
			mouseX = mousePos.x;
			mouseY = mousePos.y;
			addClick(mouseX, mouseY, true);
			console.log('touchmove');
			redraw();*/
		}
	});
	
	$('#canvas').on('mouseup touchend', function(e){
		paint = false;
		console.log('touchend');
		// addClick(mouseX, mouseY, false);
	  	// redraw();
	});
	
	$('#canvas').on('mouseleave touchend', function(e){
		paint = false;
		console.log('touchend');
		// addClick(mouseX, mouseY, false);
	});
}

function addClick(x, y, dragging) {
	// console.log(x+', '+y);
	clickX.push(x);
	clickY.push(y);
	clickTool.push(curTool);
	clickColor.push(curColor);
	clickSize.push(curSize);
	clickDrag.push(dragging);
}

function clearCanvas() {
	context.clearRect(0, 0, canvasWidth, canvasHeight);
}

function redraw() {	
	var locX;
	var locY;
	var radius;
	var i = 0;

	if(clickX.length){
		// brush size
		radius = clickSize[clickSize.length-1];
		
		context.beginPath();
		if(clickDrag.length && clickDrag[clickDrag.length-1]){
			console.log("!");
			context.moveTo(clickX[clickX.length-2], clickY[clickY.length-2]);
		}else{
			console.log("!! " + clickX[clickX.length-1]+', '+clickY[clickY.length-1]);
			context.moveTo(clickX[clickX.length-1], clickY[clickY.length-1]);
		}
		console.log("line to: " + clickX[clickX.length-1]+', '+clickY[clickY.length-1]);
		context.lineTo(clickX[clickX.length-1], clickY[clickY.length-1]);
		context.closePath();
		
		if(clickTool[clickTool.length-1] == "eraser"){
			context.globalCompositeOperation = "destination-out"; // To erase instead of draw over with white
			context.strokeStyle = 'white';
		}else{
			context.globalCompositeOperation = "source-over";	// To erase instead of draw over with white
			context.strokeStyle = clickColor[clickColor.length-1];
		}
		context.lineJoin = "round";
		context.lineWidth = radius;
		context.stroke();
	};

	context.restore();

	// update Konva layer
	layerRef.draw();
}