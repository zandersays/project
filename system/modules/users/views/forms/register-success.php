<?php
$registerSuccess = array();

$siteJavaScriptObjectName = String::titleToCamelCase(Project::getSiteTitle(), true);

$registerSuccess['successPageHtml'] = '
    <p>
        Your account has been created with the password you have chosen. We\'ve already logged you in, too!
    </p>
    <p>
        Some features on the site won\'t work until we have verified the e-mail address you provided.
        We sent an e-mail to <b>'.$email.'</b> with a link for you to follow in order to verify your account.
        You should receive the e-mail within the next few minutes.
    </p>
    <ul class="formControl" style="margin-top: 1.5em;"><li><button onclick="'.$siteJavaScriptObjectName.'.registerDialog.destroy();">Okay</button></li></ul>
';

$registerSuccess['successJs'] = "
    $('#register .formTitle h1').html('<span>Welcome</span> to ".Project::getSiteTitle().",<br />".$username."!');
    $('#register .formAfterControl').remove();
    ".$siteJavaScriptObjectName.".registerDialog.options.onBeforeClose = function() {
        $('#register .formControl button').text('Reloading...')
    }
    ".$siteJavaScriptObjectName.".registerDialog.options.reloadOnClose = true;
";
?>