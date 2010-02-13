<?php

define('IDIR', dirname(__FILE__) . '/includes/');

require(IDIR . 'init.php');

if ($_REQUEST['action'] == 'rss') {
	require('includes/rss.php');
}
else {
	require('includes/basic.php');
}

?>
