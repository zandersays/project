<?php
class UserApi extends Api {

    public static $user = false;

    public $commands = array(
        'login' => array(
            'arguments' => array(
                array('name' => 'username', 'required' => true),
                array('name' => 'password', 'required' => true),
                array('name' => 'rememberMe', 'required' => false),
            ),
        ),
        'sendEmailVerificationEmail' => array(
            'arguments' => array(
                array('name' => 'userEmailId', 'required' => true),
            ),
        ),
        'getLoginDialog' => array(
            'arguments' => array(
            ),
        ),
        'getRegisterDialog' => array(
            'arguments' => array(
            ),
        ),
    );
    
    public static function getLoginDialog() {
        //sleep(4);
        return Controller::getHtmlElement('Module:users/dialogs/login');
    }
    
    public static function getRegisterDialog() {
        return Controller::getHtmlElement('Module:users/dialogs/register');
    }

    public static function login($identifier, $password, $rememberMe = false) {
        // Try to login using the database
        if(Module::isActive('Users')) {
            $loginUsingDatabase = self::loginUsingDatabase($identifier, $password);
        }
        else {
            $loginUsingDatabase = array('status' => 'failure', 'response' => 'Users module is not enabled.');
        }

        // If remember me is not true, remove any remember me cookies
        if(!$rememberMe) {
            Cookie::delete('rememberMeKey');
            Cookie::delete('rememberMeId');
        }

        // If the database login does not work, try to login using settings
        if($loginUsingDatabase['status'] == 'failure') {
            $loginUsingSettings = self::loginUsingSettings($identifier, $password);
            if($loginUsingSettings['status'] == 'success' || $loginUsingSettings['response'] == 'Invalid password.') {
                return $loginUsingSettings;
            }
        }

        // If the login is successful, and the authentication method is database, and remember me is true
        if($loginUsingDatabase['status'] == 'success' && self::$user['authenticationMethod'] == 'database' && $rememberMe) {
            $rememberMeSalt = String::random();
            $rememberMeKey = hash('sha512', self::$user['id'].$rememberMeSalt);
            $rememberMeId = hash('md5', self::$user['id'].$rememberMeKey);
            Cookie::set('rememberMeId', $rememberMeId);
            Cookie::set('rememberMeKey', $rememberMeKey);

            $userCookieLogin = new UserCookieLogin();
            $userCookieLogin->setUserId(self::$user['id']);
            $userCookieLogin->setKey($rememberMeKey);
            // Set the expiration time for 60 days
            $userCookieLogin->setTimeExpires(Time::dateTime(Time::nowInSeconds() + (Time::$dayInSeconds * 60)));
            $userCookieLogin->setIpAddedBy(Network::ipV4ToLongInteger($_SERVER['REMOTE_ADDR']));
            $userCookieLogin->setTimeAdded(Time::dateTime(Time::nowInSeconds()));
            $userCookieLogin->save();
        }

        return $loginUsingDatabase;
    }

    public static function loginUsingSettings($identifier, $password) {
        // Check against credentials in settings file
        if((String::lower(Project::getAdministratorUsername()) == String::lower($identifier) || String::lower(Project::getAdministratorEmail()) == String::lower($identifier)) && Project::getAdministratorPassword() == hash('sha512', Project::getAdministratorPasswordSalt().$password)) {
            self::loginSuccessful('settings');
            $response = array('status' => 'success', 'response' => 'Login successful.');
        }
        else if(String::lower(Project::getAdministratorUsername()) == String::lower($identifier) || String::lower(Project::getAdministratorEmail()) == String::lower($identifier)) {
            $response = array('status' => 'failure', 'response' => 'Invalid password.');
        }
        else {
            $response = array('status' => 'failure', 'response' => 'Login attempt failed.');
        }

        return $response;
    }

    public static function loginUsingDatabase($identifier, $password) {
        $loginSuccessful = false;

        // Read the user by username or e-mail address
        try {
            $user = User::read()
                ->filterBy(User::Username, $identifier, Comparator::Equal, FilterByFlags::CaseInsensitive)
                ->withRelation(UserEmail::Model, UserEmail::UserId)
                    ->orWith()
                    ->filterBy(UserEmail::Email, $identifier, Comparator::Equal, FilterByFlags::CaseInsensitive)
                ->select()->execute()->getFirst();
        }
        catch(Exception $exc) {
            //print_r($exc);
        }
        //print_r($user);

        // Check to see if the provided password hashes with the salt to form the hashed password
        if($user !== null && $user->getPassword() == hash('sha512', $user->getPasswordSalt().hash('sha512', $password))) {
            $loginSuccessful = true;
            $response = array('status' => 'success', 'response' => 'Database login successful.', 'userId' => $user->getId());
        }
        // If there was a user but the password was wrong
        else if($user) {
            $response = array('status' => 'failure', 'response' => 'Invalid password.');
        }
        // If there is no username or e-mail address
        else {
            $response = array('status' => 'failure', 'response' => 'No such user.');
        }
        
        // Status checks
        if($user !== null && $user->getStatus() == 'banned') {
            $response = array('status' => 'failure', 'response' => 'User banned.');
            $loginSuccessful = false;
        }
        else if($user !== null && $user->getStatus() == 'deactivated') {
            $response = array('status' => 'failure', 'response' => 'User deactivated.');
            $loginSuccessful = false;
        }
        // TODO: Add a settings based status check that will prevent login if user account is unverified

        if($loginSuccessful) {
            self::loginSuccessful('database', $user);
        }

        return $response;
    }

    public static function loginUsingCookie() {
        // Get the necessary cookies
        $rememberMeId = Cookie::get('rememberMeId');
        $rememberMeKey = Cookie::get('rememberMeKey');

        // If the necessary cookies exist, see if they are valid
        if(!empty($rememberMeId) && !empty($rememberMeKey)) {
            // Check to see if the cookie login is valid
            try {
                // If this does not throw an exception, the cookie login is valid
                $userCookieLogin = UserCookieLogin::read()
                    ->where('
                        MD5(CONCAT(`user_id`, `key`)) = \''.mysql_escape_string($rememberMeId).'\' AND
                        `key` = \''.mysql_escape_string($rememberMeKey).'\' AND
                        NOW() < `time_expires`
                    ')
                    ->selectFirstModel();

                //$userCookieLogin = UserCookieLogin::read()
                //->filterBy(':MD5(CONCAT(`user_id, `key`))', $rememberMeId)
                //->filterBy('key', $rememberMeKey)
                //->filterBy('time_expires', ':NOW()', Comparator::GreaterThan)
                //->execute();

                // Read the correct user
                $user = User::read()
                    ->filterBy(User::Id, $userCookieLogin->getUserId())
                    ->selectFirstModel();

                // Process the successful login and set the authentication method to cookie
                self::loginSuccessful('cookie', $user);
                // TODO: Handle status checks for users who may be banned or deactivated

                // TODO: Randomly garbage collect expired cookie logins

                $response = array('status' => 'success', 'response' => 'Cookie login successful.', 'userId' => $user->getId());
            }
            catch(Exception $exception) {
                // Remove the existing invalid cookies
                Cookie::delete('rememberMeKey');
                Cookie::delete('rememberMeId');

                // TODO: Log a potential attack if the cookie pair does not exist
                // TODO: Login throttling

                $response = array('status' => 'failure', 'response' => 'Invalid or expired cookie login.');
            }
        }
        else {
            $response = array('status' => 'failure', 'response' => 'No remember me cookie set.');
        }

        return $response;
    }

    public static function loginSuccessful($authenticationMethod, $user = null) {
        // Create a shell user for a settings login
        if($authenticationMethod == 'settings') {
            $user = array('username' => Project::getAdministratorUsername(), 'id' => null);
        }
        else if(Object::methodExists('getUsername', $user)) {
            $user = array('username' => $user->getUsername(), 'id' => $user->getId());
        }
        else if(isset($user['username'])) {
            if(isset($user['token'])) {
                $user['token'] = $user['token'];
            }
            
            $user['id'] = $user['id'];
            $user['username'] = $user['username'];
        }

        // Set the authentication method
        $user['authenticationMethod'] = $authenticationMethod;

        // Store the user in the session and add a static reference to it from the user class
        Session::set('user', $user);
        self::$user =& Session::get('user');
    }

    public static function logout($redirect = null) {
        // Set the default redirect
        if($redirect === null) {
            $redirect = Project::getInstanceAccessPath();
        }

        Cookie::delete('rememberMeKey');
        Cookie::delete('rememberMeId');
        Session::destroy();
        Router::redirect($redirect);
    }

    public static function register($username, $email, $passwordSha512, $status = null) {
        if($status == null) {
            $status = 'unverified';
        }

        // Make sure the username is present
        //if(empty($username)) {
        //    return array('status' => 'failure', 'response' => 'Must provide a username.', 'code' => 'usernameRequired');
        //}
        // Force an empty username to be the e-mail address
        if(empty($username)) {
            $username = $email;
        }

        // Make sure the email is present
        if(empty($email)) {
            return array('status' => 'failure', 'response' => 'Must provide an e-mail address.', 'code' => 'emailRequired');
        }

        // Make sure the password is present
        if(empty($passwordSha512)) {
            return array('status' => 'failure', 'response' => 'Must provide a password.', 'code' => 'passwordRequired');
        }

        // Check if the username is already in use
        $user = User::read()
            ->filterBy(User::Username, $username)
            ->select()->execute()->asModelList()->getFirst();
        if($user !== null) {
            return array('status' => 'failure', 'response' => 'Username '.$username.' is already in use.', 'code' => 'usernameTaken');
        }
            
        // Check if the e-mail is already in use
        $userEmail = UserEmail::read()
            ->filterBy(UserEmail::Email, $email)
            ->select()->execute()->asModelList()->getFirst();
        if($userEmail !== null) {
            return array('status' => 'failure', 'response' => 'The e-mail address '.$email.' is already in use.', 'code' => 'emailTaken');
        }

        // Create the new user
        $user = new User();
        $user->setStatus($status);
        $user->setUsername(String::lower($username));
        // Create a password salt
        $passwordSalt = Security::generateBase64Salt();
        $user->setPassword(hash('sha512', $passwordSalt.$passwordSha512));
        $user->setPasswordSalt($passwordSalt);
        $user->setTimeAdded(Time::dateTime(Time::nowInSeconds()));
        $user->setIpAddedBy(Network::ipV4ToLongInteger());
        try {
            $user->save(true);
        }
        catch(Exception $exception) {
            print_r($exception);
        }

        // TODO: If the login on registration setting is enabled
        self::loginSuccessful('database', $user);

        // Create the e-mail
        $userEmail = new UserEmail();
        $userEmail->setUserId($user->getId());
        // TO DO: Set status based on settings for users
        $userEmail->setStatus($status == 'active' ? 'verified' : 'unverified');
        $userEmail->setEmail($email);
        $userEmail->setTimeAdded(Time::dateTime(Time::nowInSeconds()));
        $userEmail->setIpAddedBy(Network::ipV4ToLongInteger());
        $userEmail->save(true);

        if($status == 'unverified') {
            self::sendEmailVerificationEmail($userEmail->getId());
        }

        return array('status' => 'success', 'response' => 'Registration successful.', 'user' => $user, 'username' => $username, 'email' => $email);
    }
    
    public static function sendEmailVerificationEmail($userEmailId) {
        $userEmail = UserEmail::read()
            ->filterBy(UserEmail::Id, $userEmailId)
            ->select()->execute()->asModelList()->getFirst();
        if(!$userEmail) {
            return array('status' => 'failure', 'response' => 'Could not find user e-mail ID.');
        }
        $user = User::read()
            ->filterBy(User::Id, $userEmail->getUserId())
            ->select()->execute()->asModelList()->getFirst();
        
        // Create the e-mail verification key
        $userEmailVerification = new UserEmailVerification();
        $userEmailVerification->setUserEmailId($userEmail->getId());
        $userEmailVerification->setStatus('available');
        $userEmailVerificationKey = Security::md5($user->getId().String::random(32));
        $userEmailVerification->setKey($userEmailVerificationKey);
        $userEmailVerification->setTimeAdded(Time::dateTime(Time::nowInSeconds()));
        $userEmailVerification->setIpAddedBy(Network::ipV4ToLongInteger());
        $userEmailVerification->save();

        // Send an e-mail if e-mail verification is on
        $userEmailVerificationEmail = new Email();

        // Settings
        $usersSettings = Project::getModuleSettings('Users');
        //print_r($usersSettings['users']['registrationEmail']['emailOptions']);
        if(isset($usersSettings['users']['registrationEmail']['emailOptions'])) {
            //echo 'Special options set for email.<br />';
            $userEmailVerificationEmail->initialize($usersSettings['users']['registrationEmail']['emailOptions']);
        }
        else {
            //echo 'No options for registration e-mail.<br />';
        }
        //print_r($userEmailVerificationEmail);

        $emailDomain = String::replace('www.', '', Project::getInstanceHost());
        $emailDomain = String::replace('dev.', '', $emailDomain);
        
        $emailVerification = Controller::getHtmlElement('Module:users/emails/email-verification', array(
            'username' => $user->getUsername(),
            'verificationLink' => 'http://'.Project::getInstanceHost().Project::getInstanceAccessPath().'user/verify-email/'.$userEmailVerificationKey.'/',
            'emailDomain' => $emailDomain,
        ));
        $userEmailVerificationEmail->to($userEmail->getEmail());
        $userEmailVerificationEmail->from('support@'.$emailDomain, Project::getSiteTitle().' Accounts');
        $userEmailVerificationEmail->subject($emailVerification['subject']);
        $userEmailVerificationEmail->message($emailVerification['message']);

        if(!$userEmailVerificationEmail->send()) {
            return array('status' => 'failure', 'response' => 'There was a problem sending the e-mail verification to your e-mail address.');
        }
        else {
            return array('status' => 'success', 'response' => 'E-mail verification request successfully sent.');
        }
    }

    public static function verifyEmail($key) {
        // Check to see if the key exists
        $userEmailVerification = UserEmailVerification::read()->where("`key` = '".mysql_escape_string($key)."'")->execute();

        // If the key does not exist
        if(!$userEmailVerification) {
            return array(
                'status' => 'failure',
                'response' => array(
                    'title' => 'Invalid E-mail Verification',
                    'message' => 'There was a problem verifying your e-mail address. Please check your e-mail copy and paste the link into your browser.',
                )

            );
        }
        // If the key exists and has already been used
        else if($userEmailVerification->getStatus() == 'consumed') {
            return array(
                'status' => 'failure',
                'response' => array(
                    'title' => 'Invalid E-mail Verification',
                    'message' => 'This e-mail verification has already been used.',
                )

            );
        }
        // If the key exists and has not been used
        else if($userEmailVerification->getStatus() == 'available') {
            $userEmailVerification->setStatus('consumed');
            $userEmailVerification->setTimeConsumed(Time::dateTime());
            $userEmailVerification->save();

            $userEmail = UserEmail::readById($userEmailVerification->getUserEmailId())->execute();
            $userEmail->setTimeVerified(Time::dateTime());
            $userEmail->setStatus('verified');
            $userEmail->save();

            // Check to see if the account is unverified and set it to verified
            $user = User::readById($userEmail->getUserId())->execute();
            if($user->getStatus() == 'unverified') {
                $user->setStatus('active');
                $user->save();
            }

            return array(
                'status' => 'success',
                'response' => array(
                    'title' => 'E-mail Verification Successful',
                    'message' => 'The e-mail address '.$userEmail->getEmail().' was successfully verified.',
                )

            );
        }        
    }

}
?>