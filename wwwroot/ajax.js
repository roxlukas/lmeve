function ajax_get(URL,hookID) {
	if(typeof(hookID)==='undefined') hookID = 'NULL';

	var xmlhttp;
	
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				if (hookID!='NULL') document.getElementById(hookID).innerHTML=xmlhttp.responseText;
				return;
			}
	}
	xmlhttp.open("GET",URL,true);
	xmlhttp.send();
	return;
}

function get_next(URL,tableRef,loaderRef,callback,offset,rowcount,length) {
    //console.log('get_next() offset='+offset);
    loaderRef.style.display = "block";
    loaderRef.innerHTML = "<em><img src=\"img/loader.png\" /> Loading "+offset+" of "+length+"...</em>";
    $.ajax({
        url: URL+'&offset='+offset,
        success: function(data) {
            tableRef.innerHTML+=data;
        },
        complete: function() {
            if (offset < length) {
                get_next(URL,tableRef,loaderRef,callback,parseInt(offset,10)+parseInt(rowcount,10),rowcount,length);
            } else {
                callback();
                loaderRef.style.display = "none";
            }
        }
    });
}

function ajax_append_table(URL, tableID, loaderID, callback) {
        if(typeof(tableID)==='undefined') tableID = 'NULL';

        var tableRef = document.getElementById(tableID).getElementsByTagName('tbody')[0];
        var loaderRef = document.getElementById(loaderID);
        
        var length=0;var rowcount=0;
        
        $.ajax({
            url: URL+'&getlength=true',
            success: function(data) {
                length=data;
                console.log('length='+length);
            },
            complete: function() {
                $.ajax({
                    url: URL+'&getrowcount=true',
                    success: function(data) {
                        rowcount=data;
                        console.log('rowcount='+rowcount);
                    },
                    complete: function() {
                        get_next(URL,tableRef,loaderRef,callback,0,rowcount,length);
                    }
                });
            }
        });
	
	return;
}

function isScrolledIntoView(elem)
{
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}

function ajax_save(URL, itemID, itemlabelID) {
	var xmlhttp;
	var item=document.getElementById(itemID);
	var itemlabel=document.getElementById(itemlabelID);
	item.disabled=true;
	itemlabel.innerHTML="Saving...";
	
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				var item=document.getElementById(itemID);
				item.disabled=false;
				itemlabel.innerHTML="";
				return;
			}
	}
	xmlhttp.open("GET",URL,true);
	xmlhttp.send();
	return;
}

function regionSelect(id) {
    $.ajax({
    url:'ajax.php?act=GET_REGIONS',
    type:'GET',
    //data: 'act=GET_REGIONS',
    //dataType: 'json',
    success: function( json ) {
        $.each(json, function(i, value) {
            $('#'+id).append($('<option>').text(value.regionName).attr('value', value.regionID));
        });
    }
});
}

function systemSelect(id,regionID) {
    $.ajax({
    url:'ajax.php?act=GET_SOLARSYSTEMS&regionID='+regionID,
    type:'GET',
    success: function( json ) {
        $('#'+id).empty();
        $.each(json, function(i, value) {
            $('#'+id).append($('<option>').text(value.solarSystemName).attr('value', value.solarSystemID));
        });
    }
    });
}

function pollerRealTime(idDate,idFile,idMessage,idActive) {
    var tmpkey='';
    $.ajax({
    url:'ajax.php?act=GET_POLLERMESSAGE',
    type:'GET',
    success: function( json ) {
        //{"errorID":146,"keyID":"0","fileName":"CREST \/industry\/systems\/","date":"2015-03-23 20:25:20","errorCode":0,"errorCount":0,"errorMessage":"OK"}
        if (json.keyID!=0) tmpkey='[KeyID:'+json.keyID+'] ';
        $('#'+idDate).html(json.date);
        $('#'+idFile).html(tmpkey+json.fileName);
        $('#'+idMessage).html(json.errorMessage);
        if (json.pollerActive==true) $('#'+idActive).html('<span style="color: #00A000; font-weight:bold;">YES</span>');
        if (json.pollerActive!=true) $('#'+idActive).html('<span style="color: #808080;">NO</span>');
    }
    });
}