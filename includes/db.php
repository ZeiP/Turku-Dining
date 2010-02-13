<?php

try {
	$db = new PDO('sqlite:/var/www/WWW/menu/includes/menut.sqlite');
} catch(PDOException $e)
{
	echo $e->getMessage();
}
$db->setAttribute( PDO::ATTR_ERRMODE,  PDO::ERRMODE_WARNING  );
