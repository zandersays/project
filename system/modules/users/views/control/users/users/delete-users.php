<?php
if(!isset($userIdArray)) {
    $userIdArray = array();
}

$deleteUsers = new Form('editUser', array(
    'view' => 'Module:users/control/users/users/delete-users',
    'controller' => 'Module:users/UsersAndAccountsForms',
    'function' => 'deleteUsers',
    'submitButtonText' => 'Confirm Deletion',
    'style' => 'width: 600px;',
));

$deleteUsers->addFormSection(
    new FormSection('deleteUsersSection', array(
        'description' => '
            <p>You have specified these users for deletion:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.Arr::implode(', ', $userIdArray).'</li>
            </ul>
        '
    ))
);

$deleteUsers->addFormComponent(
    new FormComponentHidden('userIdArray', Json::encode($userIdArray))
);
?>