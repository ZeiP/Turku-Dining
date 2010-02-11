<?php

header('Content-type: text/html; charset=utf-8');
setlocale(LC_ALL, 'fi_FI.utf8');

session_start();

try {
	$db = new PDO('sqlite:/var/www/WWW/menu/includes/menut.sqlite');
} catch(PDOException $e)
{
	echo $e->getMessage();
}
$db->setAttribute( PDO::ATTR_ERRMODE,  PDO::ERRMODE_WARNING  );

if (!empty($_REQUEST['username']) && isset($_REQUEST['newuser'])) {
	$sql = 'SELECT *
		FROM users
		WHERE LOWER(username) = LOWER(:username)
		LIMIT 1';
	$qry = $db->prepare($sql);
	$qry->execute(array($_REQUEST['username']));
	if ($qry->rowCount() == 0) {
		$sql = 'INSERT INTO users
			(username)
			VALUES(:username)';
		$qry = $db->prepare($sql);
		$qry->execute(array($_REQUEST['username']));
	}
}
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
elseif (!empty($_SESSION['usersettings']) && isset($_REQUEST['save'])) {
	$_SESSION['usersettings']['showmap'] = (($_REQUEST['showmap'] == 'on') ? 1 : 0);
	$_SESSION['usersettings']['studentprice'] = (($_REQUEST['studentprice'] == 'on') ? 1 : 0);
	$sql = 'UPDATE users
		SET showmap = :showmap,
			studentprice = :studentprice
		WHERE LOWER(username) = LOWER(:username)';
	$qry = $db->prepare($sql);
	$qry->execute(array(
		$_SESSION['usersettings']['showmap'], 
		$_SESSION['usersettings']['studentprice'], 
		$_SESSION['usersettings']['username']));
}

if (!empty($_SESSION['usersettings'])) {
	$usersettings = $_SESSION['usersettings'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>
';

if (strftime('%H') >= 16 || strftime('%u') == 7) { // Kello 17 jälkeen tai sunnuntaisin seuraava päivä
	$datestr = '+1 day';
}
else {
	$datestr = 'now';
}

$date = strtotime($datestr);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">
<head>
	<title>Turku Dining</title>
	<link rel="stylesheet" type="text/css" href="styles.css" media="screen, projection, tty, tv" />
	<link rel="alternate" type="application/rss+xml" title="RSS" href="rss">
</head>
<body>
<h1>Minne mennä?</h1>
<h2><?php echo ucfirst(strftime('%Ana %d.%m.%Y', $date)); ?></h2>
<div id="login">
<?php if (empty($_SESSION['usersettings'])) { ?>
	<form method="get" action="/menu">
	<p><label for="username">Käyttäjätunnus</label>
		<input type="text" name="username" id="username" maxlength="30" /></p>

	<p><input type="submit" name="login" value="Kirjaudu sisään" /> <input type="submit" name="newuser" value="Uusi käyttäjä" /></p>
	</form>
<?php } else { ?>
	<h2><?php echo $usersettings['username']; ?></h2>
	<form method="get" action="/menu">
	<p><input type="checkbox" name="showmap" id="showmap" <?php if ($usersettings['showmap']) echo 'checked="checked" '; ?>/>
	<label for="showmap">Näytä kartta</label></p>
	<p><input type="checkbox" name="studentprice" id="studentprice" <?php if ($usersettings['studentprice']) echo 'checked="checked" '; ?>/>
	<label for="studentprice">Näytä vain opiskelijahinta</label></p>
	<p id="disclaimer">Huomaathan, että kaikki tiedot ovat vain viitteellisiä!</p>
	<p><input type="submit" name="save" value="Tallenna" /> <input type="submit" name="logout" value="Kirjaudu ulos" /></p>
	</form>
<?php } ?>
</div>
<?php
if ($usersettings['showmap']) {
	require('includes/map.php');
}

require('includes/menutable.php');
echo print_menutable($date, $db, $usersettings);

?>

<div id="footer">Värkin teki <a href="mailto:jyri-petteri.paloposki@iki.fi">ZeiP</a>, palautetta saa lähettää edellämainittuun sähköpostiosoitteeseen.</div>
</body>
</html>
