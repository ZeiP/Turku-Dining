<?php

header('Content-type: text/html; charset=utf-8');
setlocale(LC_ALL, 'fi_FI.utf8');

session_start();

require(IDIR . 'db.php');
require(IDIR . 'class.php');

$obj = new TurkuDining($db);
