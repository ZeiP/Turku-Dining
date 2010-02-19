<?php

// Defining includes directory based on current file.
define('IDIR', dirname(__FILE__) . '/includes/');

// Initializing the basic stuff: Loading DB and common functions, user-spesific settings etc.
require(IDIR . 'init.php');

// Choosing required action and loading the appropriate page
switch (strtolower($_REQUEST['action'])	) {
	case 'rss':
		require(IDIR . 'pages/rss.php');
		break;
	case 'listall':
		require(IDIR . 'pages/listall.php');
		break;
	case 'settings':
	case 'user':
		require(IDIR . 'pages/settings.php');
		break;
	default:
		require(IDIR . 'pages/basic.php');
}

?>
