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
	header("cache-control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Content-type: text/html; charset=utf-8");
	header('X-Content-Type-Options: "nosniff"');
	header("X-XSS-Protection: 1; mode=block");
	header("X-Frame-Options: deny");
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
	printf("<form method='post' action='cyberchronos.php'><table>");
	printf("<tr><td colspan='2'><input class='addform' type='text' size='100' maxlength='100' name='title' id='title' placeholder='Titre' required></td></tr>");
	printf("<tr><td colspan='2'><textarea class='addform' id='story' name='story' placeholder='Détails' required></textarea></td></tr>");
	printf("<tr><td colspan='2'><input class='addform' type='url' name='url' id='url' size='100' placeholder='URL de la source' pattern='https://.*' required></td></tr>");
	printf("<tr><td><p class='addform'>Date de début&nbsp;<input class='addform' type='date' name='datedebut' id='datedebut' required></p></td>");
	printf("<td><p class='addform'>Date de fin&nbsp;<input class='addform' type='date' name='datefin' id='datefin'></p></td></tr>");
	printf("<tr><td><img src='data:image/png;base64,%s' alt='captcha'/>&nbsp;", $captcha);
	printf("<input class='addform' type='text' size='10' maxlength='10' name='captcha' id='captcha' placeholder='Résultat' required></td>");
	printf("<td><input class='button' type='submit' value='Valider'></td></tr>");
	printf("</table></form>");
	printf("</div>");
	printf("<script nonce='%s'>document.getElementById('datedebut').addEventListener('change', function() {fixMinDate();});</script>\n", $_SESSION['nonce']);
}


function displayTimeline() {
	printf("<div id='timeline'></div>");
	printf("<script nonce='%s' src='js/create_tl.js'></script>", $_SESSION['nonce']);
}


function computeEvents($data) {
	print_r($data);
}


session_start();
if (isset($_POST['captcha'])) {
	if (validateCaptcha($_POST['captcha'])) {
		headPage();
		displayTimeline();
		computeEvents($_POST);
		addEvent();
		footPage();
	} else {
		destroySession();
	}
} else {
	headPage();
	displayTimeline();
	addEvent();
	footPage();
}



?>
