<?php
$emailVerification = array();
$emailVerification['subject'] = '['.Project::getSiteTitle().'] Please Verify Your E-mail Address';
$emailVerification['message'] = 'Hi '.$username.',

Thanks for registering an account on '.Project::getSiteTitle().'. We look forward to seeing you on the site.

To complete your registration, you need to verify your e-mail address by visiting the link below:

'.$verificationLink.'

If clicking the link doesn\'t work, just copy and paste the entire link into your web browser. If you are still having problems, try logging in at http://'.Project::getInstanceHost().Project::getInstanceAccessPath().' with your username and password or reply to this e-mail and we will do our best to help you.

Welcome to '.Project::getSiteTitle().'. Have a great day!

'.Project::getSiteTitle().' Accounts
support@'.$emailDomain.'
';
?>