<?php

header('Content-type: application/rss+xml; charset=utf-8');

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
<rss version="2.0">
<channel>
<title>Turku Dining</title>
<description>Turun opiskelijaruokaloiden ruokalistat</description>
<link><?php echo $obj->url(); ?></link>
<language>fi</language>
<webMaster>jyri-petteri.paloposki@iki.fi (Jyri-Petteri Paloposki)</webMaster>
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

echo htmlspecialchars($obj->print_menutable($date, $db));

?>
</description>
<pubDate><?php echo date('D, d M Y H') . ':00:01 ' . date('O'); ?></pubDate>
</item>
</channel>
</rss>
