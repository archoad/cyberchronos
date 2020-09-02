const chronosOptions = {
	script_path: '',
	font: 'default',
	default_bg_color: {r: 208, g: 219, b: 229},
	start_at_end: true,
	timenav_height: 250,
	hash_bookmark: true,
	scale_factor: 2,
	timenav_position: 'bottom'
}

let chronos = null;


function loadData() {
	chronos = new TL.Timeline('timeline', './data/data.json', chronosOptions);
	//console.log(chronos._el.storyslider);
}
