function fixMinDate() {
	let elt = document.getElementById('datedebut');
	document.getElementById('datefin').min = elt.value;
}


function displayAddModal() {
	var modal = document.getElementById('add_event_form');
	modal.style.display = 'block';
	window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
		}
	}
}


function refreshPage() {
	window.location = self.location;
	document.location.reload(true);
}
