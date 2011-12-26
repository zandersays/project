<?php
$header = HtmlElement::div(array('id' => 'header'));
$header->append('
    <a href="http://www.project.com/" class="projectLogo"><img src="'.Project::getInstanceAccessPath().'project/images/control/project-logo.gif" /></a>
    <h1 style="line-height: .8em;"><a href="'.Project::getInstanceAccessPath().'">'.$siteTitle.'</a> ('.$instanceType.')<br /><span style="font-size: .4em;">powered by <a href="http://www.project.com/" target="_blank">Project</a></span></h1>
    <ul id="navigation">
        <li><a href="'.Project::getInstanceAccessPath().'project/logout/">Logout ('.UserApi::$user['username'].' - '.UserApi::$user['authenticationMethod'].')</a></li>
    </ul>
');
?>