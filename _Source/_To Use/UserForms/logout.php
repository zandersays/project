<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/php/site.php');

session_destroy();

$_SESSION['user'] = new User();
$_SESSION['user']->clearLoginCookies();

header('Location: /');
?>