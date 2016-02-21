var settings = {};
settings.canvasID = 'wglCanvas';
settings.sofHullName = null;
settings.sofRaceName = null;
settings.sofFactionName = null;
settings.background = null;
settings.categoryID = 6;
settings.volume = 30000;
settings.graphicFile = null;

function checkwglsuprt() {
		  if (!window.WebGLRenderingContext) {
			  //window.alert("Cannot create WebGLRenderingContext. WebGL disabled.");
			  console.log('WebGL is NOT supported! (Cannot create WebGLRenderingContext)');return false;   
		  }
		  var canvas = document.getElementById(settings.canvasID);
		  var experimental = false;
		  try {gl = canvas.getContext("webgl");}
		  catch (x) {gl = null;}
		  
		  if (gl == null) {
				try {gl = canvas.getContext("experimental-webgl");experimental = true;}
				catch (x) {console.log('Experimental WebGL is NOT supported (experimental-webgl)');return false;}
				if (!gl) {
                                        console.log('WebGL is NOT supported! (canvas.getContext==false)');
					return false;
				}
		  }
                  console.log('WebGL is supported');
		  return true;
}
		
var scene = null,
ship = null,
WGLSUPPORT = checkwglsuprt();
	
function loadPreview(settings,skin) {
    if (skin=='default' || !skin || skin=='NULL') skin=settings.sofFactionName;
    var dna=settings.sofHullName+':'+skin+':'+settings.sofRaceName;
    console.log("Loading Standard SKIN "+dna);
    if (scene != null) {
            if (ship != null) scene.removeObject(ship);
            ship = scene.loadShip(dna, undefined);
            return;
    }
    var canvas = document.getElementById(settings.canvasID);
    ccpwgl.initialize(canvas);
    scene = ccpwgl.loadScene(settings.background);
    //sun = scene.loadSun('res:/dx9/model/lensflare/orange.red', undefined);
    var camera = new TestCamera(canvas);
    camera.minDistance = 10;
    camera.maxDistance = 10000;
    camera.fov = 30;
    if (settings.volume==0 && (settings.categoryID==3 || settings.categoryID==2) ) camera.distance=100000;else
    if (settings.volume<=1000) camera.distance=30;else 
    if ((settings.volume>1000) && (settings.volume<6000)) camera.distance=50;else
    if ((settings.volume>=6000) && (settings.volume<29000)) camera.distance=150;else
    if ((settings.volume>=29000) && (settings.volume<50000)) camera.distance=250;else
    if ((settings.volume>=50000) && (settings.volume<120000)) camera.distance=500;else
    if ((settings.volume>=120000) && (settings.volume<600000)) camera.distance=1600;else
    if ((settings.volume>=600000)) camera.distance=2500;
    camera.rotationX = 0.5;
    camera.rotationY = 0.1;
    camera.nearPlane = 1;
    camera.farPlane = 10000000;
    camera.minPitch = -0.5;
    camera.maxPitch = 0.65;
    ccpwgl.setCamera(camera);

    if (settings.categoryID==6 || settings.categoryID==18 || settings.categoryID==11) {
            //if ship, NPC or drone - use loadShip
            //use new SOF data
            ship = ship = scene.loadShip(dna, undefined);

    } else if (settings.categoryID==3 || settings.categoryID==2) {
            ship = scene.loadObject(settings.graphicFile, undefined);
    }

    ccpwgl.onPreRender = function () 
    { 
            /*var shipTransform = ship.getTransform();
            shipTransform[5] = shipTransform[15] = 1.0;
            X = Y * (Math.PI / 180.0);
            Y=Y+.1;
            shipTransform[0]=Math.cos(X);
            shipTransform[2]=Math.sin(X);
            shipTransform[8]=-1 * Math.sin(X);
            shipTransform[10]=Math.cos(X);
            ship.setTransform(shipTransform);*/
    };

}

function loadDesignerSkin(settings,sof) {
    var dna=settings.sofHullName+':'+settings.sofFactionName+':'+settings.sofRaceName+sof;
    console.log("Loading Designer SKIN "+dna);
    if (scene != null) {
        if (ship != null) scene.removeObject(ship);
        ship = scene.loadShip(dna, undefined);
        return;
    } else {
        loadPreview(settings,skin);
        if (scene != null) {
            if (ship != null) scene.removeObject(ship);
            ship = scene.loadShip(dna, undefined);
            return;
        }
    }
}

function getDesignerSkin(skinId, elementId) {
    var uri=ccpwgl_int.resMan.BuildUrl('res:/staticdata/skins.json');
    $.getJSON( uri, function( data, elementId ) {
      $.each( data, function( key, val ) {
          //console.log("key=" + key + " sof="+val.sof);
          if (key == skinId) {
              //console.log("FOUND! key=" + key + " name="+val.name+" sof="+val.sof);
              $('#'+elementId).click(function() {
                toggler_on('3dpreview');
                loadDesignerSkin(settings,val.sof);
              });
          }
      });
    });
}

function loadTurret(index, resource) {
    //console.log('loadTurret('+index+', '+resource+')');
    if (scene != null) {
        if (ship != null) {
            if (ship.isLoaded()) {
                if (index <= ship.getTurretSlotCount()) {
                    ship.mountTurret(index,resource);
                    return true;
                }
            } else {
                console.log('loadTurret('+index+', '+resource+') ship is still loading, will retry in a moment...');
                window.setTimeout(setTimeout(function() {
                    loadTurret(index, resource);
                },1000));
            }
        }
    }
    return false;
}
					
function togglefull() {
        var canvas=document.getElementById(settings.canvasID);
        var button=document.getElementById('buttonFull');
        var skinpanel=document.getElementById('skinpanel');
        if (canvas.style.position=="absolute") {
                //minimize!
                canvas.style.position="static";
                canvas.style.width="720px";
                canvas.style.height="420px";
                button.style.position="relative";
                button.style.left="2px";
                button.style.top="-418px";
                button.value="Fullscreen";
                skinpanel.style.zIndex="auto";
                skinpanel.style.position="static";
                skinpanel.style.right="0px";
        } else {
                //maximize!
                window.scrollTo(0,0);
                canvas.style.position="absolute";
                canvas.style.top="0px";
                canvas.style.left="0px";
                canvas.style.width="100%";
                canvas.style.height="100%";
                button.style.position="absolute";
                button.style.left="2px";
                button.style.top="2px";
                button.value="Minimize";
                skinpanel.style.top="0px";
                skinpanel.style.zIndex=10;
                skinpanel.style.position="absolute";
                skinpanel.style.right="0px";
        }
}