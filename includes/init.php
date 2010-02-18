<?php

header('Content-type: text/html; charset=utf-8');
setlocale(LC_ALL, 'fi_FI.utf8');

session_start();

require(IDIR . 'db.php');
require(IDIR . 'class.php');

$obj = new TurkuDining($db);

if (!empty($_REQUEST['username']) && (isset($_REQUEST['login']) || isset($_REQUEST['newuser']))) {
	$sql = 'SELECT *
		FROM users
		WHERE LOWER(username) = LOWER(:username)
		LIMIT 1';
	$qry = $db->prepare($sql);
	$qry->execute(array($_REQUEST['username']));
	$row = $qry->fetch();
	$_SESSION['usersettings'] = $row;
}
elseif (!empty($_SESSION['usersettings']) && isset($_REQUEST['logout'])) {
	unset($_SESSION['usersettings']);
}

if (!empty($_SESSION['usersettings'])) {
	$usersettings = $_SESSION['usersettings'];
}
