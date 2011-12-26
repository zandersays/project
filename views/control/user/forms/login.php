<?php
$login = Controller::getHtmlElement('Module:users/forms/login');
$login->set('title', '<h1><img src="../project/images/control/project.png" alt="Project" ></h1>');
$login->select('login-page1')->set('title', '<h1>Login to '.Project::getSiteTitle().'</h1>');
if(empty($login->select('loginRedirect')->value)) {
    $redirect = Project::getInstanceAccessPath().'project/';
    $redirect = Url::encode($redirect);
    $login->select('loginRedirect')->set('value', $redirect);
}

?>