<?php

header('Content-type: application/rss+xml; charset=utf-8');
setlocale(LC_ALL, 'fi_FI.utf8');

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
<rss version="2.0">
<channel>
<title>Turku Dining</title>
<description>Turun opiskelijaruokaloiden ruokalistat</description>
<link>http://www.asteriski.fi/~zeip/menu</link>
<language>fi</language>
<webMaster>jyri-petteri.paloposki@iki.fi</webMaster>
<lastBuildDate><?php echo date('D, d M Y H:i:s O'); ?></lastBuildDate>
<pubDate><?php echo date('D, d M Y') . ' 00:00:01 ' . date('O'); ?></pubDate>
<docs>http://www.rssboard.org/rss-specification</docs>
<skipHours>
<?php
for ($i = 0; $i <= 23; $i++) {
	echo '<hour>' . $i . '</hour>';
}
?>
</skipHours>
<item>
<guid>TurkuDining<?php echo date('D, d M Y H:i:s O'); ?></guid>
<title><?php echo ucfirst(strftime('%Ana %d.%m.%Y', $date)); ?></title>
<description>
<?php

require('includes/menutable.php');
echo htmlspecialchars(print_menutable($date, $db));

?>
</description>
<pubDate><?php echo date('D, d M Y') . ' 00:00:01 ' . date('O'); ?></pubDate>
</item>
</channel>
</rss>
