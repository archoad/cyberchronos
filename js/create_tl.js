let additionalOptions = {
	start_at_end: true,
	timenav_height: 250,
	hash_bookmark: false
}

window.timeline = new TL.Timeline('timeline', './data/data.json', additionalOptions);
