<?php
$settings = array(
    'instances' => array(
        array(
            'projectPath' => substr(dirname(__FILE__), 0, -7),
            'path' => substr(dirname(__FILE__), 0, -7),
            'accessPath' => null,
            'host' => $_SERVER['HTTP_HOST'],
            'id' => 'project',
            'type' => null,
            'setupTime' => 'Not applicable.',
        ),
    ),
    'modules' => array(
        'Routes' => array(
            'routes' => array(
                array(
                    'expression' => '/provision/',
                    'controllerName' => 'Provision',
                    'status' => 'active',
                ),
                array(
                    'status' => 'active',
                    'expression' => '/provision/api/(.*?)/(.*?)/(.*)',
                    'description' => '',
                    'priority' => '',
                    'controllerName' => 'Api',
                    'functionName' => '',
                    'data' => array(
                        array(
                            'key' => 1,
                            'value' => 'apiName',
                        ),
                        array(
                            'key' => 2,
                            'value' => 'commandName',
                        ),
                        array(
                            'key' => 3,
                            'value' => ':hash',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
?>