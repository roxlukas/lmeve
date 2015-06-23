function drawPolygon(ctx,points,color) {
 ctx.beginPath();
 ctx.lineWidth = 1;
 ctx.lineCap = 'round';
 ctx.strokeStyle = color;
 for (var i = 0; i < points.length; i++) {
	ctx.lineTo(points[i].x, points[i].y);
 }
 ctx.lineTo(points[0].x, points[0].y);
 ctx.fillStyle = color;
 ctx.closePath();
 ctx.fill();
 ctx.stroke();
}

function setPixel(ctx,coords,size,color) {
 ctx.fillStyle = color;
 ctx.fillRect( coords.x, coords.y, size, size);
}


function drawPixels(ctx,points,size,color) {
 for (var i = 0; i < points.length; i++) {
	setPixel(ctx,points[i],size,color);
 }
}

function drawSkinIcon(canvasID,colors) {
 var hull = [
	{ x: 16, y: 5 },
	{ x: 47, y: 5 },
	{ x: 63, y: 31 },
	{ x: 47, y: 57 },
	{ x: 16, y: 57 },
	{ x: 1, y: 31 }
 ];

 var secondary = [
	{ x: 5, y: 25 },
	{ x: 16, y: 5 },
	{ x: 47, y: 5 },
	{ x: 54, y: 17 },
	{ x: 35, y: 17 },
	{ x: 26, y: 28 },
	{ x: 18, y: 28 },
	{ x: 14, y: 25 }
 ];

 var primary1 = [
	{ x: 9, y: 45 },
	{ x: 16, y: 57 },
	{ x: 47, y: 57 },
	{ x: 63, y: 31 },
	{ x: 56, y: 20 },
	{ x: 48, y: 20 },
	{ x: 38, y: 30 },
	{ x: 38, y: 35 },
	{ x: 27, y: 45 }
 ];

 var primary2 = [
	{ x: 4, y: 27 },
	{ x: 13, y: 27 },
	{ x: 18, y: 30 },
	{ x: 25, y: 30 },
	{ x: 25, y: 34 },
	{ x: 15, y: 42 },
	{ x: 8, y: 42 },
	{ x: 1, y: 31 }
 ];

 var windows = [
	{ x: 19, y: 41 },
    	{ x: 21, y: 43 },
	{ x: 23, y: 43 },
	{ x: 25, y: 43 },
	{ x: 27, y: 33 },
	{ x: 30, y: 33 },
	{ x: 30, y: 37 },
    	{ x: 28, y: 37 },
	{ x: 33, y: 25 },
	{ x: 32, y: 36 },
	{ x: 42, y: 20 },
    	{ x: 40, y: 20 },
	{ x: 38, y: 22 }
 ];

 var background = [
	{ x: 0, y: 0 },
	{ x: 63, y: 0 },
	{ x: 63, y: 63 },
	{ x: 0, y: 63 }
 ];

 var canvas = document.getElementById(canvasID),
     ctx = canvas.getContext('2d'),
     scalex = 1.0 * canvas.width / 64.0,
     scaley = 1.0 * canvas.height / 64.0;

 ctx.scale(scalex,scaley);

 //background
 //drawPolygon(ctx,background,"black");
 //hull
 drawPolygon(ctx,hull,colors.hull);
 //windows
 drawPixels(ctx,windows,2,colors.window);
 //primary
 drawPolygon(ctx,primary1,colors.primary);
 drawPolygon(ctx,primary2,colors.primary);
 //secondary
 drawPolygon(ctx,secondary,colors.secondary);
 ctx.scale(1/scalex,1/scaley);
}

/*sample colors:
skinMaterialID 	material 	displayNameID 	colorWindow 	colorPrimary 	colorSecondary 	colorHull
	1 	ardishapur 	505346 		e4d4b0 		dfd8c5 		717e5c	 	181818
	2 	kador 		505347 		d6f7f9 		6d98c9 		a9aaad 		181818
	3 	quafe 		505345 		b8d4cb 		6c7276 		3b719d 		222222
	4 	khanid 		505348 		b4cddb 		393939 		b8b8b8 		181818
	5 	sarum 		505349 		e4d4b0 		6d312c 		c2a468 		181818

//usage

 var colors = {
     "window": "#e4d4b0",
     "primary": "#6d312c",
     "secondary": "#c2a468",
     "hull": "#181818"
 };

drawSkinIcon('canvas',colors);
*/