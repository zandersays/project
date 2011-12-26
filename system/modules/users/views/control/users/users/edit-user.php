<?php
if(!isset($id)) {
    $id = null;
    $username = null;
    $status = null;
    $userEmailInitialValues = null;
}

$editUser = new Form('editUser', array(
    'view' => 'Module:users/control/users/users/edit-user',
    'controller' => 'Module:users/UsersAndAccountsForms',
    'function' => 'editUser',
    'submitButtonText' => 'Save Changes',
    'style' => 'width: 600px;',
    'onSubmitStartClientSide' => "$('#reformedPassword').val(security.hexSha512($('#password').val())); var emptyPassword = ''; var passwordLength = $('#password').val().length; while(emptyPassword.length < passwordLength) emptyPassword += '*'; $('#password').val(emptyPassword);",
    'description' => '<p><a href="../../delete-users/userIdArray:['.$id.']/" class="buttonLink minusSquareGrey">Delete User</a></p>'
));

$editUser->addFormComponentArray(array(
    new FormComponentHidden('userId', $id),
    new FormComponentSingleLineText('username', 'Username:', array(
        'validationOptions' => array('required', 'username'),
        'initialValue' => $username,
    )),
    new FormComponentDropDown('status', 'Status:', array(
        array('label' => 'Active', 'value' => 'active'),
        array('label' => 'Unverified', 'value' => 'unverified'),
    ), array(
        'validationOptions' => array('required'),
        'initialValue' => $status,
    )),
    /*
    new FormComponentSingleLineText('password', 'Change password:', array(
        'validationOptions' => array('required', 'password'),
        'type' => 'password',
    )),
    new FormComponentHidden('reformedPassword', ''),
    new FormComponentSingleLineText('email', 'E-mail address:', array(
        'validationOptions' => array('required', 'email'),
        'instanceOptions' => array(
            'max' => 0,
            'initialValues' => $userEmailInitialValues,
        ),
    )),*/
));
?>