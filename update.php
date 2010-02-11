<?php

header('Content-type: text/html; charset=utf-8');

require('includes/functions.php');

try {
	$db = new PDO('sqlite:/home/zeip/public_html/includes/menut.sqlite');
} catch(PDOException $e)
{
	echo $e->getMessage();
}
$db->setAttribute( PDO::ATTR_ERRMODE,  PDO::ERRMODE_WARNING  );

$sql = 'SELECT id
	FROM restaurants
	WHERE shortname = \'monttu\'
	ORDER BY shortname';
$res = $db->query($sql);
while ($row = $res->fetch()) {
	$matches = fetch_menu($row['id'], $db);
	echo '<h1>' . $row['name'] . '</h1>';
}

?>
