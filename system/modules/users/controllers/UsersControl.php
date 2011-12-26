<?php
class UsersControl extends Controller {
    
    function index($data) {
        if(empty($data['function'])) {
            $data['function'] = 'users';
        }

        return $this->$data['function']($data);
    }

    function settingsUsers($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

    function settingsUsersUsers($data) {
        //echo 'Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']);
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }
    function settingsUsersUsersRegistrationEmail($data) {
        //echo 'Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']);
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

    //function users($data) {
    //    return $this->getHtmlElement('Module:users/control/users');
    //}

    function users($data) {
        //print_r($data); exit();

        // Get the start offset
        if(isset($data['pathArguments']['offset'])) {
            $usersStartOffset = $data['pathArguments']['offset'];
        }
        else {
            $usersStartOffset = 1;
        }

        // Optional search
        if(isset($data['pathArguments']['search'])) {
            $usersSearch = $data['pathArguments']['search'];
        }
        else {
            $usersSearch = false;
        }

        $usersPerPage = 10;

        if(!$usersSearch) {
            $userList = ModelList::read('User')->limit($usersStartOffset - 1, $usersPerPage)->execute();
        }
        else {
            $userList = ModelList::read('User')->where('username LIKE '.Database::escapeString('%'.$usersSearch.'%'))->limit($usersStartOffset - 1, $usersPerPage)->execute();
        }
        
        $userCount = Database::query('SELECT COUNT(*) as count FROM user');
        $userCount = $userCount[0]['count'];

        return $this->getView('Module:users/'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), array(
            'userList' => $userList->modelList,
            'userCount' => $userCount,
            'usersPerPage' => $usersPerPage,
            'usersStartOffset' => $usersStartOffset,
            'usersSearch' => $usersSearch,
        ));
    }

    function usersAddAUser($data) {
        return Controller::getHtmlElement('Module:users/'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function usersEditUser($data) {
        $user = User::readById($data['pathArguments']['userId'])->execute();
        $userEmailList = $user->readUserEmailModelList()->execute();
        $userEmailInitialValues = array();
        foreach($userEmailList->modelList as $userEmail) {
            $userEmailInitialValues[] = $userEmail->getEmail();
        }
        //print_r($userEmailList); exit();

        return $this->getHtmlElement('Module:users/control/users/users/edit-user', array(
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'status' => $user->getStatus(),
            'userEmailInitialValues' => $userEmailInitialValues,
        ));
    }

    function usersDeleteUsers($data) {
        //print_r($data['pathArguments']);

        return $this->getHtmlElement('Module:users/control/users/users/delete-users', array(
            'userIdArray' => $data['pathArguments']['userIdArray'],
        ));
    }

    function accounts($data) {
        return $this->getHtmlElement('Module:users/'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function accountsAddAnAccount($data) {
        return $this->getHtmlElement('Module:users/'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

}
?>