<?php

try {
	$db = new PDO('sqlite:' . IDIR . '/db/menut.sqlite');
} catch(PDOException $e)
{
	echo $e->getMessage();
}
$db->setAttribute( PDO::ATTR_ERRMODE,  PDO::ERRMODE_WARNING  );
