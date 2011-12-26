<?php

/**
 * Sets up a Project environment so that
 * all modules can be tested.
 *
 * @author Kam Sheffield
 * @version 08/21/2011
 */

// include the main bootstrap file
require_once dirname(__FILE__).'/project-bootstrap.php';

// get all of the bootstraps
$bootstraps = Dir::read(dirname(__FILE__));

// load all the bootstraps
foreach($bootstraps as $bootstrap) {
    if(!String::contains('project-bootstrap.php', $bootstrap)) {
        require_once($bootstrap);
    }
}

?>
