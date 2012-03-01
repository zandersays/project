<?php
$title = HtmlElement::h1();
$message = HtmlElement::p();

$title->text($titleText);
$message->text($messageText);

$verifyEmail = $title.$message;
?>