<?php

define('IDIR', dirname(__FILE__) . '/includes/');

require(IDIR . 'init.php');

switch (strtolower($_REQUEST['action'])	) {
	case 'rss':
		require('includes/rss.php');
		break;
	case 'listall':
		require('includes/listall.php');
		break;
	default:
		require('includes/basic.php');
}

?>
