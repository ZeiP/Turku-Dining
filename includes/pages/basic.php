<?php

$show_restaurants = array();
if (preg_match('/r\/([^\/]+)/', $_REQUEST['action'], $matches) != 0) {
	$show_restaurants = explode(';', $matches[1]);
}

if (!empty($_SESSION['sessionsettings']['show_date'])) {
	$date = $_SESSION['sessionsettings']['show_date'];
}
else {
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
}

?>
<!DOCTYPE html>
<html lang="fi">
<head>
	<title>Turku Dining</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $obj->url('styles.css'); ?>" media="screen, projection, tty, tv" />
	<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $obj->url('rss'); ?>" />
	<meta name="description" content="Turun opiskelijaruokaloiden ruokalistat yhdellä sivustolla." />
	<meta name="keywords" content="turku,opiskelijaruokailu,opiskelija,yliopisto,ammattikorkeakoulu,opiskelijaruokala,ravintola,ruokalista,menu" />
	<script type="text/javascript" src="<?php echo $obj->url('script.js'); ?>"></script>
</head>
<body>
<h1>Minne mennä?</h1>
<p>Lista turkulaisista opiskelijaravintoloista ja niiden ruokalistoista. Lisää ravintoloita voi ehdottaa sivun alalaidasta löytyvään sähköpostiosoitteeseen.</p>
<h2><?php echo ucfirst(strftime('%Ana %d.%m.%Y', $date)); ?></h2>
<div id="login">
<?php if (empty($_SESSION['usersettings'])) { ?>
	<form method="get" action="<?php echo $obj->url('user'); ?>">
	<input type="hidden" name="goto" value="<?php echo htmlentities($obj->url()); ?>" />
	<p><label for="username">Käyttäjätunnus</label>
		<input type="text" name="username" id="username" maxlength="30" /></p>
	<p><input type="submit" name="login" value="Kirjaudu sisään" /> <input type="submit" name="newuser" value="Uusi käyttäjä" /></p>
	</form>
<?php } else { ?>
	<h2><?php echo $usersettings['username']; ?></h2>
	<p><a href="<?php echo $obj->url('settings'); ?>">Asetukset</a></p>
	<form method="get" action="<?php echo $obj->url('user'); ?>">
	<input type="hidden" name="goto" value="<?php echo htmlentities($obj->url()); ?>" />
	<p><input type="submit" name="logout" value="Kirjaudu ulos" /></p>
	</form>
<?php } ?>
<?php echo $obj->select_date($date); ?>
</div>
<?php

if ($usersettings['showmap'] && !empty($cloudmade_id))
{ // If user has chosen map to be shown, let's show it by requiring the corresponding file.
	require('includes/map.php');
}

// Function that returns the whole menu table (user prefs are in the $obj class already.)
echo $obj->print_menutable($date, $show_restaurants);

?>

<div id="footer">
<p id="credits">Värkin teki <a href="mailto:jyri-petteri.paloposki@iki.fi">ZeiP</a>, palautetta saa lähettää edellämainittuun sähköpostiosoitteeseen.</p>
<p>
<a href="http://validator.w3.org/check?uri=referer">Valid HTML5!</a>
<a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" /></a>
</p>
</div>
<?php if (!empty($google_ua)) { ?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
	var pageTracker = _gat._getTracker("<?php echo $google_ua; ?>");
	pageTracker._trackPageview();
} catch(err) {}</script>
<?php } ?>
</body>
</html>
