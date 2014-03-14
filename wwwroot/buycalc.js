function calc_total(fields,totalID) {
	var fieldtotal=document.getElementById(totalID);
	var total=0.0;
	for (var i=0;i<fields.length;i++) {
		var tmpval=document.getElementById(fields[i][1]).innerHTML;
		//console.log(tmpval);
		total+=parseFloat(tmpval);
	}
	fieldtotal.innerHTML=parseFloat(Math.round(total * 100) / 100).toFixed(2) + ' ISK';
}

function calc_row(qtyID,ppuID,valueID,totalID,fields,e) {
	//console.log("calc_row() called\n\r");
	var qty=document.getElementById(qtyID);
	var ppu=document.getElementById(ppuID);
	var value=document.getElementById(valueID);
	
	value.innerHTML=parseFloat(Math.round(qty.value * ppu.innerHTML * 100) / 100).toFixed(2);
	calc_total(fields,totalID);
	//Enter detection
	var charCode = (typeof e.which === "number") ? e.which : e.keyCode;
	//console.log("charCode="+charCode+"\n\r");
	if ( charCode == 13 ) {
		//console.log("Enter detected, finding next tabindex.\n\r");
		for (i = 0; i < qty.form.elements.length; i++) {
			if (qty.form.elements[i].tabIndex == qty.tabIndex+1) {
				qty.form.elements[i].focus();
				if (qty.form.elements[i].type == "text")
				qty.form.elements[i].select();
				break;
			}
		}
		return false;
	}
}

function form_reset(fields,totalID) {
	var fieldtotal=document.getElementById(totalID);
	for (var i=0;i<fields.length;i++) {
		document.getElementById(fields[i][1]).innerHTML='0.00';
	}
	fieldtotal.innerHTML='0.00 ISK';
}

function form_submit(form) {
	//if (confirm('Are you sure you want to submit this order?')) {
		form.submit();
	//}
}