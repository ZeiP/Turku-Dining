<?php

require('includes/functions.php');

if ($_REQUEST['action'] == 'rss') {
	require('includes/rss.php');
}
else {
	require('includes/basic.php');
}

?>
