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


include("functions.php");
session_set_cookie_params([
	'lifetime' => $cookie_timeout,
	'path' => '/',
	'domain' => $cookie_domain,
	'secure' => $session_secure,
	'httponly' => $cookie_httponly,
	'samesite' => $cookie_samesite
]);

session_start();
if (isset($_POST['captcha'])) {
	if (validateCaptcha($_POST['captcha'])) {
		headPage();
		computeEvents($_POST);
		genJsonFile();
		displayTimeline();
		addEvent();
		footPage();
	} else {
		destroySession();
	}
} else {
	headPage();
	isTherEventsForThisyear();
	genJsonFile();
	displayTimeline();
	addEvent();
	footPage();
}




?>
