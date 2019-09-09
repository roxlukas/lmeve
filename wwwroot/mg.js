/* 
    "SOCIAL SPACE" module for LMeve
    Created on : 2019-01-22, 10:31:03
    Author     : Lukas Rox
*/

/************************* draggable windows **************************/

class Window {
    constructor(x, y, width, height, title) {
        this.x = x;
        this.y = y;
        this.width = width;
        this.height = height;
        
        this.visible = true;
    
        this.window_id = this.makeid(20)
        
        this.win = document.createElement('div');
        this.win.setAttribute("class", "neocom-color window-body");
        this.win.setAttribute("id", this.window_id);
        this.win.style.cssText = 'width:'+ this.width + 'px;height:'+ this.height + 'px;top:' + this.y + 'px;left:' + x +'px;';
        
        this.title = document.createElement('div');
        this.title.setAttribute("class", "window-title");
        
        this.content = document.createElement('div');
        this.content.setAttribute("class", "window-content");
        this.content.setAttribute("id", "content_" + this.window_id );
        this.content.style.cssText = 'width:'+ this.width + 'px;height:'+ (this.height - 24) + 'px;';
        
        this.header = document.createElement('div');
        this.header.setAttribute("class", "window-header");
        this.header.setAttribute("id", "header_" + this.window_id );
        
        this.button = document.createElement('span');
        this.button.setAttribute("class", "window-button");
        this.button.setAttribute("id", "btn_" + this.window_id );
        this.button.innerHTML = 'x';
        this.button.addEventListener("click", function(){ wm.hide(this.parentElement.parentElement) });
        
        this.header.appendChild(this.button);
        
        this.win.appendChild(this.header);
        this.win.appendChild(this.title);
        this.win.appendChild(this.content);
        
        
        document.body.appendChild(this.win);
        this.title.innerHTML = title;
        mg_window_drag_element(this.win);
    }
    
    setContent(content) {
        if (this.content) this.content.innerHTML = content;
    }
    
    getContent() {
        if (this.content) return this.content.innerHTML;
        return false;
    }
    
    setHeader(title) {
        if (this.title) this.title.innerHTML = title;
    }
    
    getHeader() {
        if (this.title) return this.title.innerHTML;
        return false;
    }
    
    getId() {
        if (this.window_id) return this.window_id;
    }
    
    getContent() {
        if (this.content) return this.content.innerHTML;
        return false;
    }
    
    setWidth(width) {
        this.width = width;
        if (this.win) this.win.style.width = this.width + 'px;';
    }
    
    getWidth() {
        return this.width;
    }
    
    setHeight(height) {
        this.height = height;
        if (this.win) this.win.style.height = this.height + 'px;';
    }
    
    getHeight() {
        return this.height;
    }
    
    destroy() {
        if (this.title) this.title.remove();
        if (this.content) this.content.remove();
        if (this.win) this.win.remove();
        this.title = null;
        this.content = null;
        this.win = null;
    }
    
    hide() {
        if (this.win) {
            this.visible = false;
            this.win.style.display = "none";
        }
    }
    
    show() {
        if (this.win) {
            this.visible = true;
            this.win.style.display = "block";
        }
    }
    
    toggle() {
        if (this.win) {
            if (this.visible == true) {
                this.hide();
            } else {
                this.show();
            }
        }
    }
    
    makeid(size) {
      var text = "";
      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

      for (var i = 0; i < size; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

      return text;
    }
    
    
}

class Message {
    constructor(message) {
        
    }
}

function mg_window_drag_element(elmnt) {
      var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
      if (document.getElementById("header_" + elmnt.id)) {
        // if present, the header is where you move the DIV from:
        document.getElementById("header_" + elmnt.id).onmousedown = mg_window_drag_mouse_down;
      } else {
        // otherwise, move the DIV from anywhere inside the DIV:
        elmnt.onmousedown = mg_window_drag_mouse_down;
      }

      function mg_window_drag_mouse_down(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = mg_window_close_drag_element;
        // call a function whenever the cursor moves:
        document.onmousemove = mg_window_element_drag;
      }

      function mg_window_element_drag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
      }

      function mg_window_close_drag_element() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
      }
    }

class WindowManager {
    constructor() {
        this.windows = [];
    }
    
    createWindow(x, y, width, height, title) {
        var l = this.windows.length;
        this.windows[l] = new Window(x, y, width, height, title);
        return this.windows[l];
    }
    
    destroyWindow(id) {
        this.windows[id].destroy();
        this.windows.splice(id,1);
    }
    
    findByTitle(title) {
        for (var i in this.windows) {
            if (this.windows[i].getHeader() == title) return this.windows[i];
        }
        return false;
    }
    
    findById(id) {
        for (var i in this.windows) {
            if (this.windows[i].getId() == id) return this.windows[i];
        }
        return false;
    }
    
    hide(elem) {
        var win = this.findById(elem.getAttribute('id'));
        //console.log(elem.getAttribute('id'))
        if (win) win.hide();
    }
    
    show(elem) {
        var win = this.findById(elem.getAttribute('id'));
        //console.log(elem.getAttribute('id'))
        if (win) win.show();
    }
    
    toggle(elem) {
        var win = this.findById(elem.getAttribute('id'));
        //console.log(elem.getAttribute('id'))
        if (win) win.toggle();
    }
}

var wm = new WindowManager();

/********************************* MG ***************************************/

var state = undefined;
var previous = undefined;
var ship_state = '';
var stationID = null;
var camera, scene, ship;
var chat_local_trigger = null;
var chat_local_timestamp = 0;

function mg_parse_state(state, mg) {
    window.state = state;
    console.log("mg_parse_state(" + state.server_time +")");
    
    //local chat
    if (window.chat_local_timestamp == 0) {
        window.chat_local_timestamp = state.server_time;
    }
    if (window.chat_local_trigger == null) {
        window.chat_local_trigger = window.setInterval(function() { mg_local_get_messages(); }, 3000);
    }
    
    //ship state
    if (window.ship_state != state.ship_state) {
        if (state.ship_state == 'docked') {
            mg_station_layout(mg, state.typeID, state.stationTypeID, state.parentItemID, state.regionID);
        }
        if (state.ship_state == 'inspace') {
            mg_in_space_layout(mg);
        }
        window.ship_state = state.ship_state;
    }
    //if (state.ship_state == 'docked' && window.stationID != state.parentItemID) mg_load_station_interior_webgl(state.typeID, state.parentItemID, state.stationTypeID);
    if (state.ship_state == 'docked' && (window.previous == null || window.previous.parentItemID != state.parentItemID)) { 
        mg_load_station_exterior_webgl(state.stationTypeID, state.parentItemID, state.regionID);
        $('#nearest-object').html(state.stationName);
        $('#station-owner-logo').html('<img src="https://imageserver.eveonline.com/Corporation/' + state.corporationID + '_128.png" />');
        mg_local_append('Switched to ' + state.solarSystemName + ' local channel.');
    }
    if (state.security < 0.0) state.security = 0.0;
    $('#system-name').html(state.solarSystemName);
    $('#constellation-name').html(state.constellationName);
    $('#region-name').html(state.regionName);
    $('#faction-info').html('<img src="' + state.iconFile + '" width="32"> ' + state.factionName);
    $('#system-security').html(parseFloat(Math.round(state.security * 100000)/100000).toFixed(1));
    var sec_color = mg_security_status_color(parseFloat(Math.round(state.security * 100000)/100000).toFixed(1));
    //console.log("mg_call_state(" + state.updated  +") security color = " + sec_color);
    $('#system-security').css('color', sec_color);
    
    if (state.ship_state == 'docked') {
        $('#nearest-object').html(state.stationName);
        $('#station-owner-logo').html('<img src="https://imageserver.eveonline.com/Corporation/' + state.corporationID + '_128.png" />');
    }
    if (state.ship_state == 'inspace') {
        $('#nearest-object').html(state.nearestObjectName);
    }
    //local
    mg_local_update_list(state.local);
    
    window.previous = state;
}

function mg_security_status_color(security) {
    if (security <= 0.1) return('#D73000');
    if (security <= 0.2) return('#F04800');
    if (security <= 0.3) return('#F06000');
    if (security <= 0.4) return('#D77700');
    if (security <= 0.5) return('#EFEF00');
    if (security <= 0.6) return('#8FEF2F');
    if (security <= 0.7) return('#00F000');
    if (security <= 0.8) return('#00EF47');
    if (security <= 0.9) return('#48F0C0');
    if (security <= 1.0) return('#2FEFEF');
}

function mg_call_state(mg) {
    //var updated = Math.floor(Date.now() / 1000);
    console.log("mg_call_state() updating...");
    $.ajax({
    url:'ajax.php?act=MG_CALL_STATE',
    type:'GET',
    success: function( json ) {
        console.log("mg_call_state() got state: " + json.server_time +"");
        mg_parse_state(json, mg);
    },
    error: function() {
        console.log("mg_call_state() failed to get server state!");
    }
    });
}

function mg_get_region_backdrop(regionID) {    
    console.log("mg_get_region_backdrop(" + regionID + ")");
    var resource = 'res:/dx9/scene/Universe/c01_cube.red';
    $.ajax({
    url:'api.php?endpoint=MAPREGIONS&regionID=' + regionID ,
    type:'GET',
    async: false,
    success: function( json ) {
        console.log("mg_get_region_backdrop(" + regionID + ") found " + json[0].graphicFile);
        resource = json[0].graphicFile;
    },
    });
    console.log("mg_get_region_backdrop(" + regionID + ") returns " + resource);
    return resource;
}

function mg_station_layout_resize() {
    var t = document.getElementById('tab-main');
    var h = parseInt(t.style.getPropertyValue('height'));
    var sa = document.getElementById('station-area');
    var su = document.getElementById('station-ui');
    sa.style.setProperty('height', h + 'px');
    su.style.setProperty('height', h + 'px');
}

function mg_station_layout(mg, shipTypeID, stationTypeID, stationID,  regionID) {
    console.log("mg_station_layout()");
    $.ajax({
    url:'ajax.php?act=MG_STATION_LAYOUT',
    type:'GET',
    success: function( json ) {
        $('#' + mg).html(json);
        mg_station_layout_resize();
        window.addEventListener("resize", function(){ mg_station_layout_resize(); });
        mg_create_local_window();
        mg_call_state(mg);
    }
    });
}

function mg_create_local_window() {
    var t = document.getElementById('tab-main');
    var h = parseInt(t.style.getPropertyValue('height'));
    var y = 106;
    var h2 = 360;
    var w = 360;
    var y2 = (h+y)-h2;
    //console.log( 'y=' + y + ' h=' + h + ' h2=' + h2 + ' y2=' + y2 );
    //console.log('wm.createWindow(34,' + y2 + ',320,' + h2 + ')');
    var local_window = wm.createWindow(34,y2,w,h2,'Local');
    local_window.setContent('<div id="chat-local-content" class="col-8 window-content chat-70pct"></div><div id="chat-local-list" class="col-4 window-content chat-70pct"></div><div id="chat-local-entry" class="col-12 window-content chat-30pct"><textarea name="local-entry" id="local-entry"></textarea></div>');
    var textbox = document.getElementById('local-entry');
    textbox.onkeypress = mg_local_submit;
}

function mg_in_space_layout(mg) {
    console.log("mg_in_space_layout()");
    $.ajax({
    url:'ajax.php?act=MG_IN_SPACE_LAYOUT',
    type:'GET',
    success: function( json ) {
        $('#' + mg).html(json);
        mg_call_state(mg);
    }
    });
}

function mg_gm_teleport(shipItemID, stationID) {
    console.log("mg_gm_teleport()");
    $.ajax({
    url:'ajax.php?act=MG_GM_TELEPORT&itemID=' + shipItemID + '&stationID=' + stationID,
    type:'GET',
    success: function( json ) {
        return true;
    }
    });
}

function mg_load_station_exterior_webgl(typeID, stationID, regionID) {
    console.log("mg_load_station_exterior_webgl()");
    var graphicFile = mg_get_region_backdrop(regionID);
    window.stationID = stationID;
    $.ajax({
    url:'api.php?endpoint=INVTYPES&typeID=' + typeID ,
    type:'GET',
    success: function( json ) {
        var dna = json.sofDNA;
        var settings = {};
        settings.canvasID = 'station-canvas';
        settings.background = graphicFile;
        loadObject(settings,dna);
    }
    });   
}

function mg_load_station_interior_webgl(typeID, stationID, stationTypeID) {
    console.log("mg_load_station_interior_webgl()");
    window.stationID = stationID;
    var raceID = null;
    
    //load station data from LMeve API
    $.ajax({
        url:'api.php?endpoint=INVTYPES&typeID=' + stationTypeID ,
        type:'GET',
        async: false,
        success: function( station ) {
            raceID = station.raceID;
        }
    }); 
    
    //load ship data from LMeve API
    $.ajax({
        url:'api.php?endpoint=INVTYPES&typeID=' + typeID ,
        type:'GET',
        async: false,
        success: function( ship ) {
            var settings = {};
            settings.canvasID = 'station-canvas';
            //settings.background = graphicFile;
            dna = ship.sofDNA;
            loadShipInHangar(settings, raceID, dna);
        }
    });
}

function mg_local_append(message) {
    $('#chat-local-content').append('<div class="chat-message">' + message + '</div>');
}

function mg_get_local_list() {
    console.log("mg_get_local_list() updating local...");
    $.ajax({
    url:'ajax.php?act=MG_LOCAL_LIST',
    type:'GET',
    success: function( json ) {
        //console.log(JSON.stringify(json));
        mg_local_update_list(json);
        return true;
    },
    error: function(  ) {
        mg_local_update_list("Cannot connect to server.");
        return true;
    }
    });
}

function mg_local_update_list(json) {
    var list = "";
    for (var i=0; i < json.length; i++) {
        var ch = json[i];
        //console.log(JSON.stringify(ch));
        if (ch.characterID && ch.characterName) {
            list += '<div class="mg-character-list" onclick="mg_character_click(' + ch.characterID + ')"><img src="https://imageserver.eveonline.com/character/' + ch.characterID + '_32.jpg" alt="" />' + ch.characterName + '</div>';
        }
    }
    $('#chat-local-list').html(list);
}

function mg_character_click(characterID) {
    console.log('mg_character_click(' + characterID + ')');
}

function mg_local_submit(e) {
    e = e || window.event;
    
    //console.log('mg_local_submit('+ JSON.stringify(e) +')');
    
    var key = e.keyCode;

    // If the user has pressed enter
    if (key === 13) {
        console.log('mg_local_submit() sending POST...');
        // information to be sent to the server
        var entry = $('#local-entry').val();
        $('#local-entry').val('');
        $.ajax({
            type: "POST",
            url:'ajax.php?act=MG_LOCAL_ENTRY',
            data: {text: entry},
            success: function(e) {
                
                //mg_local_get_messages();
            }
        });
        return false;
    }
    else {
        return true;
    }
}

function mg_local_get_messages() {
    t = window.chat_local_timestamp;
    console.log("mg_local_get_messages("+ t +") getting messages...");
    $.ajax({
    url:'ajax.php?act=MG_LOCAL_GET_MESSAGES&last_timestamp=' + window.chat_local_timestamp,
    type:'GET',
    success: function( json ) {
        if ( json.length > 0 ) console.log(JSON.stringify(json));
        for (var i=0; i < json.length; i++) {
            var ch = json[i];
            //console.log(JSON.stringify(ch));
            if (ch.characterID && ch.characterName) {
                mg_local_append('<div class="mg-character-list" onclick="mg_character_click(' + ch.characterID + ')"><img src="https://imageserver.eveonline.com/character/' + ch.characterID + '_32.jpg" alt="" /><strong>' + ch.characterName + '</strong> &gt; ' + ch.text + '</div>');
                var objDiv = document.getElementById("chat-local-content");
                objDiv.scrollTop = objDiv.scrollHeight;
                if (ch.timestamp > window.chat_local_timestamp) window.chat_local_timestamp = ch.timestamp;
            }
        }
        return true;
    },
    error: function(  ) {
        
        return true;
    }
    });
}