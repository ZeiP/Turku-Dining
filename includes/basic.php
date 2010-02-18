<?php

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
	<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $obj->url('rss'); ?>">
<script type="text/javascript">
function toggleDisplayNode(obj) {
    if (obj.style.display != "none") {
        obj.style.display = "none";
    }
    else {
        obj.style.display = "";
    }
}
</script>
</head>
<body>
<h1>Minne mennä?</h1>
<h2><?php echo ucfirst(strftime('%Ana %d.%m.%Y', $date)); ?></h2>
<div id="login">
<?php if (empty($_SESSION['usersettings'])) { ?>
	<form method="get" action="<?php echo $obj->url(); ?>">
	<p><label for="username">Käyttäjätunnus</label>
		<input type="text" name="username" id="username" maxlength="30" /></p>

	<p><input type="submit" name="login" value="Kirjaudu sisään" /> <input type="submit" name="newuser" value="Uusi käyttäjä" /></p>
	</form>
<?php } else { ?>
	<h2><?php echo $usersettings['username']; ?></h2>
	<p><a href="<?php echo $obj->url('settings'); ?>">Asetukset</a></p>
	<form method="get" action="<?php echo $obj->url(); ?>">
	<p><input type="submit" name="logout" value="Kirjaudu ulos" /></p>
	</form>
<?php } ?>
</div>
<?php
if ($usersettings['showmap']) {
	require('includes/map.php');
}

echo $obj->print_menutable($date);

?>

<div id="footer">Värkin teki <a href="mailto:jyri-petteri.paloposki@iki.fi">ZeiP</a>, palautetta saa lähettää edellämainittuun sähköpostiosoitteeseen.</div>
</body>
</html>
