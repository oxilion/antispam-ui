function release(nr, key) {
	$("#release-"+ nr).dialog('open');
}
function remove(nr, key) {
	var answer = confirm("Weet u zeker dat u deze e-mail wilt verwijderen?");
	if (answer){
		$("#respons").load("?action=delete&id="+ nr + "&key="+ key +" #responsBA");
		$("#"+ nr).hide(1000);
	}
}
function send(nr) {
	alert(email);
	if (email.length > 6) {

	}
}