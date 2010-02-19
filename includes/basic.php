<?php

if (strftime('%H') >= 16 || strftime('%u') == 7)
{ // On week days after 16 o'clock choosing the next day...
	$datestr = '+1 day';
}
elseif (strftime('%u') == 6 && strftime('%H') >= 16)
{ // On Saturdays skipping to Monday after 16 o'clock
	$datestr = '+2 days';
}
else
{ // Otherwise we'll settle with today's menus...
	$datestr = 'now';
}

// Converting the previously-chosen date string to a date.
$date = strtotime($datestr);

// Can't be printed outside PHP code because of the stupid PHP short tags (<?)
echo '<?xml version="1.0" encoding="UTF-8"?>
';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">
<head>
	<title>Turku Dining</title>
	<link rel="stylesheet" type="text/css" href="styles.css" media="screen, projection, tty, tv" />
	<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $obj->url('rss'); ?>" />
	<meta name="description" content="Turun opiskelijaruokaloiden ruokalistat yhdellä sivustolla." />
	<meta name="keywords" content="turku,opiskelijaruokailu,opiskelija,yliopisto,ammattikorkeakoulu,opiskelijaruokala,ravintola,ruokalista,menu" />
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
if ($usersettings['showmap'])
{ // If user has chosen map to be shown, let's show it by requiring the corresponding file.
	require('includes/map.php');
}

// Function, that returns the whole menu table (user prefs are in the $obj class already.)
echo $obj->print_menutable($date);

?>

<div id="footer">Värkin teki <a href="mailto:jyri-petteri.paloposki@iki.fi">ZeiP</a>, palautetta saa lähettää edellämainittuun sähköpostiosoitteeseen.</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-13105388-1");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
