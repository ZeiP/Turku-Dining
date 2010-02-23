<?php

header('Content-type: text/html; charset=utf-8');
setlocale(LC_ALL, 'fi_FI.utf8');

session_start();

require(IDIR . 'db.php');
require(IDIR . 'class.php');

if (!empty($_REQUEST['show_date'])) {
	if (!is_array($_SESSION['sessionsettings'])) {
		$_SESSION['sessionsettings'] = array();
	}
	$_SESSION['sessionsettings']['show_date'] = $_REQUEST['show_date'];
}

if ((!empty($_SESSION['usersettings']) || !empty($_SESSION['sessionsettings'])) && isset($_REQUEST['logout'])) {
	unset($_SESSION['usersettings']);
	unset($_SESSION['sessionsettings']);
}
elseif (!empty($_SESSION['usersettings']['username']) || !empty($_REQUEST['username']) && (isset($_REQUEST['login']) || isset($_REQUEST['newuser']))) {
	$sql = 'SELECT *
		FROM users
		WHERE LOWER(username) = LOWER(:username)
		LIMIT 1';
	$qry = $db->prepare($sql);
	$qry->execute(array(
		((!empty($_REQUEST['username'])) ? $_REQUEST['username'] : $_SESSION['usersettings']['username']),
		));
	$row = $qry->fetch(PDO::FETCH_ASSOC);
	$_SESSION['usersettings'] = $row;
}

if (!empty($_SESSION['usersettings'])) {
	$usersettings = $_SESSION['usersettings'];
	$usersettings['exclude_restaurants'] = explode(',', $usersettings['exclude_restaurants']);
	if (empty($usersettings['exclude_restaurants'])) {
		$usersettings['exclude_restaurants'] = array();
	}
}

$obj = new TurkuDining($db, $usersettings);
