function leading_zero(liczba) {
    if (liczba<10) {
	liczba='0'+liczba;
    }
    return liczba;
}

function insert_now(edit_box) {
    var Today = new Date();
    var Month = leading_zero(Today.getMonth()+1);
    var Day = leading_zero(Today.getDate());
    var Year = Today.getYear();
    var Godz = Today.getHours();
    var Minuty = leading_zero(Today.getMinutes());
    if(Year <= 1900)
    Year += 1900;
    var _now = Day + "." + Month + "." + Year + " " + Godz + ":" + Minuty;
    var formularz = document.getElementById(edit_box);
    formularz.value=_now;
}

function insert_day() {
    var Today = new Date();
    var Month = leading_zero(Today.getMonth()+1);
    var Day = leading_zero(Today.getDate());
    var Year = Today.getYear();
    if(Year <= 1900)
    Year += 1900;
    var _now = Day + "." + Month + "." + Year;
    var formularz = document.getElementById("pole_daty");
    formularz.value=_now;
}

function insert_blank(where) {
    var f = document.getElementById(where);
    f.value="";
}

function checkradio(nr) {
    var radio1 = document.getElementById("zlec_link1");
    var radio2 = document.getElementById("zlec_link2");

    var select1 = document.getElementById("zlec_ap");
    var select2 = document.getElementById("zlec_error");

    if (nr==1) {
		radio1.checked=1;
		radio2.checked=0;
		select1.disabled=1;
		select2.disabled=0;
    }
    if (nr==2) {
		radio1.checked=0;
		radio2.checked=1;
		select1.disabled=0;
		select2.disabled=0;
    }
}

function checkradio2(nr) {
    var radio1 = document.getElementById("zlec_link1");
    var radio2 = document.getElementById("zlec_link2");
    var bnext = document.getElementById("bnext");
    if (nr==1) {
		radio1.checked=1;
		radio2.checked=0;
    }
    if (nr==2) {
		radio1.checked=0;
		radio2.checked=1;
    }
	bnext.disabled=0;
}


function empty_ip(edit_box) {
    var formularz = document.getElementById(edit_box);
    formularz.value="Nie dotyczy";
}
