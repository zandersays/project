<?php
require_once($_SERVER['DOCUMENT_ROOT']."/php/site.php");

class UserApi extends Api {
    var $commands = array(
        'checkUsernameAvailability' => array('arguments' => array('value'), 'requiredArguments' => array('value')),
        'manageFollowing' => array('arguments' => array('userId', 'actionType'), 'requiredArguments' => array('userId', 'actionType')),
    );
    var $database = 'wallspotting';

    function  __construct() {
    }

    function checkUsernameAvailability($username) {
        $usernameQuery = Database::getRecordCount($this->database, 'user', 'WHERE `username` = \''.mysql_escape_string($username).'\'');

        if($usernameQuery['response'] == 0) {
            $response = array('status' => 'success', 'response' => array('Username "'.$username.'" is available.'));
        }
        else {
            $response = array('status' => 'failure', 'response' => array('Username "'.$username.'" is not available.'));
        }

        return $response;
    }

    public function manageFollowing($userId, $actionType) {
        $followingUser = new User();
        $followingUser->read($_SESSION['user']->id);
        $followingUser->manageFollowing($userId, $actionType);

        $user = new User();
        $user->read($userId);

        return array('status' => 'success', 'response' => $user->getFollowLink());
    }
}
?>