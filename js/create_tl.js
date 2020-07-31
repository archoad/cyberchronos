let chronosOptions = {
	start_at_end: true,
	timenav_height: 250,
	hash_bookmark: true,
	timenav_position: 'bottom'
}

let chronos = new TL.Timeline('timeline', './data/data.json', chronosOptions);
