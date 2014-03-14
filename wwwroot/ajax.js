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