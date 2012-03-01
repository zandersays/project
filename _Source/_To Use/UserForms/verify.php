<?php
$verificationResponse = $_SESSION['user']->verifyEmail($_GET['username'], $_GET['passphrase']);
echo $verificationResponse['response'];
?>