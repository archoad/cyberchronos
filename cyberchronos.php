<?php
/*=========================================================
// File:        cyberchronos.php
// Description: main file of cyberchronos
// Created:     2020-03-02
// Licence:     GPL-3.0-or-later
// Copyright 2020 Michel Dubois

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

Inspired by @x0rz
https://github.com/x0rz/phishing_catcher
=========================================================*/


$server_path = dirname($_SERVER['SCRIPT_FILENAME']);
$cheminDATA = sprintf("%s/data/", $server_path);
$logo = './pictures/logoArchoad.png';


function genNonce($length) {
	$nonce = random_bytes($length);
	$b64 = base64_encode($nonce);
	$url = strtr($b64, '+/', '-_');
	return rtrim($url, '=');
}


function genCaptcha() {
	if(isset($_SESSION['sess_captcha'])) {
		unset($_SESSION['sess_captcha']);
	}
	$imgWidth = 100;
	$imgHeight = 24;
	$nbrLines = 5;
	$img = imagecreatetruecolor($imgWidth, $imgHeight);
	$bg = imagecolorallocate($img, 0, 0, 0);
	imagecolortransparent($img, $bg);
	for($i=0; $i<=$nbrLines; $i++) {
		$lineColor = imagecolorallocate($img, rand(0,255), rand(0,255), rand(0,255));
		imageline($img, rand(1, $imgWidth-$imgHeight), rand(1, $imgHeight), rand(1, $imgWidth+$imgHeight), rand(1, $imgHeight), $lineColor);
	}
	$captchaNumber = ["un", "deux", "trois", "quatre", "cinq"];
	$val1 = rand(1, 5);
	$val2 = rand(1, 5);
	$_SESSION['sess_captcha'] = $val1 * $val2;
	$captchaString = $captchaNumber[$val1-1].'*'.$captchaNumber[$val2-1];
	$textColor = imagecolorallocate($img, 40, 45, 50);
	imagestring($img, 3, 0, 4, $captchaString, $textColor);
	ob_start();
	imagepng($img);
	$rawImageBytes = ob_get_clean();
	imagedestroy($img);
	return(base64_encode($rawImageBytes));
}


function headPage() {
	$_SESSION['nonce'] = genNonce(8);
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: text/html; charset=utf-8');
	header('X-Content-Type-Options: "nosniff"');
	header('X-XSS-Protection: 1; mode=block');
	header('X-Frame-Options: deny');
	printf("<!DOCTYPE html><html lang='fr-FR'><head>");
	printf("<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	printf("<title>Cybersecurity incident timeline</title>");
	printf("<link href='css/style.css' rel='StyleSheet' type='text/css' />");
	printf("<link href='css/timeline.css' rel='StyleSheet' type='text/css' />");
	printf("<script nonce='%s' src='js/timeline.min.js'></script>", $_SESSION['nonce']);
	printf("<script nonce='%s' src='js/app.js'></script>", $_SESSION['nonce']);
	printf("</head><body>");
}


function footPage() {
	printf("</body></html>");
}


function validateCaptcha($captcha) {
	if (strncmp($_SESSION['sess_captcha'], $captcha, 6) === 0) {
		return true;
	} else {
		return false;
	}
}


function destroySession() {
	session_unset();
	session_destroy();
	session_write_close();
	setcookie(session_name(),'',0,'/');
	header('Location: cyberchronos.php');
}


function addEvent() {
	$captcha = genCaptcha();
	$today = date('Y-m-d', time());
	printf("<div id='addevent'>");
	printf("<form method='post' action='cyberchronos.php'>");
	printf("<div class='gridwrapper'>");
	printf("<div class='gridtitle'><input class='addform' type='text' maxlength='120' name='title' id='title' placeholder='Titre' required></div>");
	printf("<div class='gridurl'><input class='addform' type='url' maxlength='120' name='url' id='url' placeholder='URL de la source (https://www.example.com)' pattern='https://.*'></div>");
	printf("<div class='gridinfo'><textarea class='addform' id='story' name='story' placeholder='Détails' required></textarea></div>");
	printf("<div class='griddatedeb1'><p>Date de début</p></div>");
	printf("<div class='griddatedeb2'><input class='addform' type='date' name='datedebut' id='datedebut' required></div>");
	printf("<div class='griddatefin1'><p>Date de fin</p></div>");
	printf("<div class='griddatefin2'><input class='addform' type='date' name='datefin' id='datefin'></div>");
	printf("<div class='gridimgcaptcha'><img class='addform' src='data:image/png;base64,%s' alt='captcha'/></div>", $captcha);
	printf("<div class='gridresultcaptcha'><input class='addform' type='text' size='10' maxlength='10' name='captcha' id='captcha' placeholder='Résultat' required></div>");
	printf("<div class='gridrecord'><input class='addform' type='submit' value='Valider'></div>");
	printf("</div>");
	printf("</form>");
	printf("</div>");
	printf("<script nonce='%s'>document.getElementById('datedebut').addEventListener('change', function() {fixMinDate();});</script>\n", $_SESSION['nonce']);
}


function displayTimeline() {
	printf("<div id='timeline'></div>");
	printf("<script nonce='%s' src='js/create_tl.js'></script>", $_SESSION['nonce']);
}


function getJsonFile($filename) {
	global $cheminDATA;
	$jsonFile = sprintf("%s%s", $cheminDATA, $filename);
	$jsonSource = file_get_contents($jsonFile);
	return json_decode($jsonSource, true);
}


function genTitle() {
	global $logo;
	$year = date('Y');
	$text = [];
	$text['headline'] = 'Incidents de cybersécurité';
	$text['text'] = sprintf("Année %s", $year);
	$media = [];
	$media['url'] = $logo;
	$title = [];
	$title['media'] = $media;
	$title['text'] = $text;
	return $title;
}


function genEras() {
	$year = date('Y');
	$result = [];
	for ($i=1; $i<=12; $i++) {
		$date = sprintf("%s-%d-1", $year, $i);
		$d = new DateTime($date);
		$temp = [];
		$temp['start_date'] = ['year'=>$year, 'month'=>$i, 'day'=>1];
		$temp['end_date'] = ['year'=>$year, 'month'=>$i, 'day'=>$d->format('t')];
		$temp['text'] = ['headline'=>''];
		$result[] = $temp;
	}
	return $result;
}


function genEvent($url, $headline, $text, $start, $end) {
	$start = explode('-', $start);
	$end = explode('-', $end);
	$json = [];
	$json['start_date'] = ['year'=>$start[0], 'month'=>$start[1], 'day'=>$start[2]];
	$json['end_date'] = ['year'=>$end[0], 'month'=>$end[1], 'day'=>$end[2]];
	$json['media'] = ['url'=>$url];
	$json['text'] = ['headline'=>$headline, 'text'=>$text];
	$json['group'] = 'Interne';
	//$json['background'] = ['color'=>#2244aa];
	return $json;
}


function writeJsonEvent($json) {
	global $cheminDATA;
	$date = date('Y-m-d');
	$rand = md5(microtime());
	$jsonFile = sprintf("%s%s-%s.json", $cheminDATA, $date, $rand);
	$fp = fopen($jsonFile, 'w');
	fwrite($fp, json_encode($json, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
	fclose($fp);
}


function isCurrentYearJsonFile($filename) {
	$result = false;
	$year = date('Y');
	if ((!is_dir($filename)) || (pathinfo($filename,  PATHINFO_EXTENSION) === 'json')) {
		if (explode('-', $filename)[0] === $year) {
			$result =  true;
		}
	}
	return $result;
}


function isTherEventsForThisyear() {
	global $cheminDATA;
	$nbrEvent = 0;
	$handle = opendir($cheminDATA);
	while (false !== ($entry = readdir($handle))) {
		if (isCurrentYearJsonFile($entry)) {
			$nbrEvent++;
		}
	}
	closedir($handle);
	if (!$nbrEvent) { // Create an empty event
		genFirstEvent();
	}
}


function genFirstEvent() {
	$year = date('Y');
	$title = "Timeline des incidents cyber";
	$text = "Cyberchronos permet de réaliser un suivi des incidents internes et externes. Vous pouvez rajouter des incidents en remplissant le formulaire ci-dessous.";
	$start = sprintf("%s-01-01", $year);
	$end = sprintf("%s-01-01", $year);
	$json = genEvent("", $title, $text, $start, $end);
	writeJsonEvent($json);
}


function genJsonFile() {
	global $cheminDATA;

	$jsonFile = sprintf("%s%s", $cheminDATA, 'data.json');
	$json = [];
	$json['title'] = genTitle();
	$json['eras'] = genEras();
	$json['events'] = [];
	$handle = opendir($cheminDATA);
	while (false !== ($entry = readdir($handle))) {
		if (isCurrentYearJsonFile($entry)) {
			$json['events'][] = getJsonFile($entry);
		}
	}
	closedir($handle);
	$fp = fopen($jsonFile, 'w');
	fwrite($fp, json_encode($json, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
	fclose($fp);
}


function computeEvents($data) {
	$title = filter_var(trim($_POST['title']), FILTER_SANITIZE_STRING|FILTER_SANITIZE_ENCODED);
	$story = filter_var(trim($_POST['story']), FILTER_SANITIZE_STRING|FILTER_SANITIZE_ENCODED);
	$url = filter_var(trim($_POST['url']), FILTER_SANITIZE_URL);
	$datedebut = trim($_POST['datedebut']);
	if ($_POST['datefin']==="") { $datefin = $datedebut; } else { $datefin = trim($_POST['datefin']); }
	$json = genEvent($url, $title, $story, $datedebut, $datefin);
	writeJsonEvent($json);
}


session_start();
if (isset($_POST['captcha'])) {
	if (validateCaptcha($_POST['captcha'])) {
		computeEvents($_POST);
		genJsonFile();
		headPage();
		displayTimeline();
		addEvent();
		footPage();
	} else {
		destroySession();
	}
} else {
	isTherEventsForThisyear();
	genJsonFile();
	headPage();
	displayTimeline();
	addEvent();
	footPage();
}



?>
