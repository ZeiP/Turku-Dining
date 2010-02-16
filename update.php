<?php

define('IDIR', dirname(__FILE__) . '/includes/');

require('includes/init.php');

$sql = 'SELECT id
	FROM restaurants
	ORDER BY shortname';
$res = $db->query($sql);
while ($row = $res->fetch()) {
	$matches = $obj->fetch_menu($row['id']);
	echo '<h1>' . $row['name'] . '</h1>';
}

?>
