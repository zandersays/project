<?php
class SessionDriverDatabase extends SessionDriver {

    public function SessionDriverDatabase($settings) {
        // Set the lifetime of the cookie
        session_set_cookie_params($settings['expiration']);
        ini_set('session.gc_maxlifetime', $settings['expiration']);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', $settings['garbageCollectionProbability']);

        // Overwrite the session save handler
        session_set_save_handler(
            array(&$this, 'open'),
            array(&$this, 'close'),
            array(&$this, 'read'),
            array(&$this, 'write'),
            array(&$this, 'destroy'),
            array(&$this, 'garbageCollect')
        );

        // Start the session
        $this->start();

        // Update the cookie expiration
        if(Cookie::get('sessionId', false) != null) {
            Cookie::set('sessionId', Cookie::get('sessionId', false), array(
                'expiration' => $settings['expiration'],
                'signing' => false,
            ));
        }

        // Regenerate the session ID on every request for enhanced security
        if($settings['regenerate'] === true) {
            $this->regenerate();
        }
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        // Set an empty response
        $data = array();

        // Set the expiration time to the time specified in settings, or to the default of 24 minutes
        $sessionsSettings = Project::getModuleSettings('Sessions');
        $expiration = isset($sessionsSettings['expiration']) ? $sessionsSettings['expiration'] : 1440;
        $expiration = time() + $expiration;

        // Retrieve the session
        try {
            $session = UserSession::readByClause('`session_id` = \''.mysql_escape_string($sessionId).'\' AND `time_expires` < \''.Time::dateTime($expiration).'\'');
            $data = $session->getData();
        }
        catch(Exception $exception) {
            $data = array();
        }

        return $data;
    }

    function write($sessionId, $data) {
        //print_r(debug_backtrace()); //exit();

        // Get the current session if it exists
        try {
            $session = UserSession::readByClause('`session_id` = \''.mysql_escape_string($sessionId).'\' AND `time_expires` > \''.Time::dateTime($expiration).'\'');
        }
        // If the session does not exist, create a new one
        catch(Exception $exception) {
            $session = new UserSession();
        }

        // Set the session ID
        $session->setSessionId($sessionId);
        $session->setIpAddedBy(Network::ipV4ToLongInteger($_SERVER['REMOTE_ADDR']));
        $session->setTimeAdded(Time::dateTime(Time::nowInSeconds()));

        // Set the expiration time to the time specified in settings, or to the default of 24 minutes
        $sessionsSettings = Project::getModuleSettings('Sessions');
        $expiration = isset($sessionsSettings['expiration']) ? $sessionsSettings['expiration'] : 1440;
        $expiration = time() + $expiration;
        $session->setTimeExpires(Time::dateTime($expiration));

        // Set the session data
        $session->setData($data);

        $session->save();

        return true;
    }

    function garbageCollect() {

        // Garbage Collection
        // Build DELETE query.  Delete all records who have passed the expiration time
        //$sql = 'DELETE FROM `sessions` WHERE `expires` < UNIX_TIMESTAMP();';

        // Always return TRUE
        return true;
    }

    public function start($id = '') {
        // Name the session
        session_name('sessionId');

        // Start the session
        session_start();

        // Store a reference to the global session variable in $data
        $this->data =& $_SESSION;
    }

    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    public function delete($keys) {
        if(!Arr::is($keys)) {
            $keys = array($keys);
        }

        foreach($keys as $key) {
            unset($this->data[$key]);
        }
    }

    public function regenerate() {
        session_regenerate_id();

        return session_id();
    }

    public function destroy($sessionId = '') {
        $sessionId = session_id();
        if(empty($sessionId)) {
            session_start();
        }

        $session = UserSession::readByClause('`session_id` = \''.mysql_escape_string($sessionId).'\'');
        $session->delete();

        // Unset all of the session variables
        $this->data = array();

        // This will destroy the session, and not just the session data
        if(ini_get('session.use_cookies')) {
            $cookieParameters = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $cookieParameters['path'], $cookieParameters['domain'],
                $cookieParameters['secure'], $cookieParameters['httponly']
            );
        }
    }

}
?>