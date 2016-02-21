/**********************************************************************************
								LM Framework v3
								
	A simple PHP based application framework.
	
	Contact: pozniak.lukasz@gmail.com
	
	Copyright (c) 2005-2013, �ukasz Po�niak
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification,
	are permitted provided that the following conditions are met:
	
	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
	BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
	OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	OF THE POSSIBILITY OF SUCH DAMAGE.

**********************************************************************************/

function haha(id){
    var myselect = document.getElementById(id);
    eval("location='"+myselect.options[myselect.selectedIndex].value+"'");
}



function move_what(what,ile,typ) {
	inactive="tab";
	active="tab-act";
	if ((selected==what)||(selected==-1)||((selected>-1) && (last!=typ))) {
		if (selected==what) {
			selected=-1;
			id="tbl" + what;
			tabela=document.getElementById(id);
			tabela.setAttribute("class",inactive);
		} else {
			if (selected>-1) {
				id="tbl" + selected;
				tabela=document.getElementById(id);
				tabela.setAttribute("class",inactive);
			}
			id="tbl" + what;
			tabela=document.getElementById(id);
			tabela.setAttribute("class",active);
			selected=what;
			q=ile;
			last=typ;
		}
	} else {
		if (q > 1) {
			pytanie = "How many to move (max. " + q + ")";
			ile = prompt(pytanie,"");
			if ((ile<1)||(ile>q)) {
				alert("Wrong amount!");
				return;
			}
			url = "?id=6&id2=0&what=" + what + "&from=" + selected + "&q=" + ile;
		} else {
			url = "?id=6&id2=0&what=" + what + "&from=" + selected + "&q=1";
		}
		window.location=url;
	}
}

function move_where(what,where) {
	if (q>1) {
		pytanie = "How many to move (max. " + q + ")";
		ile = prompt(pytanie,"");
		if ((ile<1)||(ile>q)) {
			alert("Wrong amount!");
			return;
		}
		url = "?id=6&id2=0&what=" + what + "&where=" + where + "&q=" + ile;
	} else {
		url = "?id=6&id2=0&what=" + what + "&where=" + where;
	}
	if (selected!=-1) window.location=url;
}

function move_trash(what) {
	url = "?id=6&id2=4&id3=1&nr=" + what;
	if (selected!=-1) window.location=url;
}

function edit_what(what) {
	url = "?id=6&id2=2&id3=1&nr=" + what;
	if (selected!=-1) window.location=url;
}

function add(editbox) {
    value=Math.round(editbox.getAttribute("value"));
    editbox.setAttribute("value", value+1);
}

function sub(editbox) {
    value=Math.round(editbox.getAttribute("value"));
    editbox.setAttribute("value", value-1);
}

 function t(){
 //na wzor ogame
                v=new Date();
                var czas=document.getElementById('czas');
                n=new Date();
                ss=pp;
                s=ss-Math.round((n.getTime()-v.getTime())/1000.);
                m=0;h=0;d=0;
                if(s<0){
                  czas.innerHTML="<b><img src=img/error.gif> 72 hours due.</b><br>"
                }else{
                  if(s>59){
                    m=Math.floor(s/60);
                    s=s-m*60
                  }
                  if(m>59){
                    h=Math.floor(m/60);
                    m=m-h*60
                  }
		  if(h>24){
		    d=Math.floor(h/24);
		    h=h-d*24
		  }
                  if(s<10){
                    s="0"+s
                  }
                  if(m<10){
                    m="0"+m
                  }
		  if (d>0) {
		    czas.innerHTML="<img src=img/time.gif> Time left to complete: "+d+" dni "+h+"h "+m+"m "+s+"s<br>"	  
		  } else {
		    czas.innerHTML="<img src=img/time.gif> Time left to complete: "+h+"h "+m+"m "+s+"s<br>"	  
		  }
                }
                pp=pp-1;
                window.setTimeout("t();",999);
              }
	      
	function reloader(url,timer) {
		timer2 = 1000 * timer;
		window.setTimeout("window.location.reload()",timer2);
		document.write('<img src="img/info.gif" alt="i"> Page reloads every '+timer+' seconds.<br>');
	}

	function gohref(url) {
		window.location=url;
	}
	
	function select_all(obj) {
		//var obj=document.getElementById(qtyID);
		obj.focus();
		obj.select();
	}
        
        //LMeve!
	
        //cookies are used to remember which groups are toggled open
	function createCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		} else expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}

	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
            return null;
	}

	function eraseCookie(name) {
		createCookie(name,"",-1);
	}
        
    //toggler can be used to toggle a group in <div> on and off
    function toggler(what) {
		var element=document.getElementById(what);
		if ((element.style.display=="") || (element.style.display=="none")) {
			element.style.display="block";
                        createCookie(what,"1");
		} else {
			element.style.display="none";
                        eraseCookie(what);
		}
	}
	
	//toggler_on can be used to toggle a group in <div> on if it is off, and does nothing if it's already on
    function toggler_on(what) {
		var element=document.getElementById(what);
		if ((element.style.display=="") || (element.style.display=="none")) {
			element.style.display="block";
                        createCookie(what,"1");
		}
	}
	
    //table_toggler can be used to toggle a group in <table> on and off
    function table_toggler(what) {
		var element=document.getElementById(what);
		if ((element.style.display=="") || (element.style.display=="none")) {
			element.style.display="table";
                        //$( '#' + what ).slideDown( 1000 );
                        createCookie(what,"1");
		} else {
			element.style.display="none";
                        //$( '#' + what ).slideUp( 500 );
                        eraseCookie(what);
		}
	}
        
	//this function restores visibility based on cookies
	function rememberToggleTable(what) {
		var cookie=readCookie(what);
		if ( cookie != null ) {
			var element=document.getElementById(what);
			element.style.display="table";
		}
	}
        //div_toggler can be used to toggle a group in <div> on and off (animates!)
        function div_toggler(what) {
		var element=document.getElementById(what);
                //alert(element.style.display);
		if ((element.style.display=="none")) {
                        $( '#' + what ).slideDown( 400 );
                        createCookie(what,"1");
		} else {
                        $( '#' + what ).slideUp( 400 );
                        eraseCookie(what);
		}
	}
        //this function restores visibility based on cookies
	function rememberToggleDiv(what) {
		var cookie=readCookie(what);
		if ( cookie != null ) {
			var element=document.getElementById(what);
			element.style.display="block";
		}
	}
        
        //sample usage in HTML
        /*
        <div class="rozwijane">
        <a href="javascript:toggler('boks1');">O nas</a>
        </div>

        <div class="rozwiniete" id="boks1">
        <script type="text/javascript">rememberToggle('boks1');</script>
        */    
       
        function getKit(rowID,spanID,typeID,activityID,runs) {
            //toggle the kit row
            var element=document.getElementById(rowID);
		if ((element.style.display=="") || (element.style.display=="none")) {
                    element.style.display="table-row";
                    //$( '#' + rowID ).slideDown( 800 );    
                    //make an AJAX call to load kit data
                    ajax_get('ajax.php?act=GET_KIT&typeID='+typeID.toString()+'&runs='+runs.toString()+'&activityID='+activityID.toString(),spanID);
		} else {
                    element.style.display="none";
                    //$( '#' + rowID ).slideUp( 600 );
		}   
        }
        function getKit2(rowID,spanID,taskID,runs) {
            //toggle the kit row
            var element=document.getElementById(rowID);
		if ((element.style.display=="") || (element.style.display=="none")) {
                    element.style.display="table-row";
                    //$( '#' + rowID ).slideDown( 800 );    
                    //make an AJAX call to load kit data
                    ajax_get('ajax.php?act=GET_KIT2&taskID='+taskID.toString()+'&runs='+runs.toString(),spanID);
		} else {
                    element.style.display="none";
                    //$( '#' + rowID ).slideUp( 600 );
		}   
        }
        
        // Hide all paragraphs using a slide up animation over 0.8 seconds
        //$( "p" ).slideUp( 800 );
        // Show all hidden divs using a slide down animation over 0.6 seconds
        //$( "div.hidden" ).slideDown( 600 );

        function getStockAmount(id) {
            var element=document.getElementById(id);
            var tmp=element.value;
            return tmp;
        }
        
        function addTSCustomParsers() {
            //add ISK parser for sorter
            $.tablesorter.addParser({ 
                id: 'isk', 
                is: function(s) { 
                    return false; 
                }, 
                format: function(s) { 
                    return $.tablesorter.formatFloat(s.replace(new RegExp(/\sisk/g), "").replace(/,/g,""));
                }, 
                type: 'numeric' 
            }); 

            //numeric with thousand separator
            $.tablesorter.addParser({ 
                id: 'numsep', 
                is: function(s) { 
                    return false; 
                }, 
                format: function(s) { 
                    return $.tablesorter.formatFloat(s.replace(/,/g,""));
                }, 
                type: 'numeric' 
            }); 
        }
        
        function showEvetime(id) {
            //console.log("id="+id);
            var evetimeDiv = document.getElementById(id);
            var now = new Date(); 
            var hh = now.getUTCHours();
            var mm = now.getUTCMinutes();
            var yc = now.getUTCFullYear()-1898;
            var mo = now.getUTCMonth()+1;
            var dd = now.getUTCDate();
            if (hh<10) hh="0"+hh;
            if (mm<10) mm="0"+mm;
            if (mo<10) mo="0"+mo;
            if (dd<10) dd="0"+dd;
            var evetime = "<span title=\"YC " + yc + "." + mo + "."+ dd + "\">" + hh + ":" + mm + "</span>";
            //console.log("evetime="+evetime);
            evetimeDiv.innerHTML=evetime;
        }