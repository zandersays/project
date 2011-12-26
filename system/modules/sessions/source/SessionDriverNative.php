<?php
class SessionDriverNative extends SessionDriver {

    public function SessionDriverNative($settings) {
        // Set the lifetime of the cookie
        session_set_cookie_params($settings['expiration']);
        ini_set('session.gc_maxlifetime', $settings['expiration']);
        if($settings['garbageCollectionProbability'] == 0) {
            ini_set('session.gc_probability', 0);
        }
        else {
            ini_set('session.gc_probability', 1);
        }
        ini_set('session.gc_divisor', $settings['garbageCollectionProbability']);

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

    public function start($id = '') {
        // Only start a session if a session does not already exist
        $sessionId = session_id();
        if(empty($sessionId)) {
            // Name the session
            session_name('sessionId');

            // Start the session
            session_start();

            // Store a reference to the global session variable in $data
            $this->data =& $_SESSION;
        }
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

    public function destroy($id = '') {
        // Initialize the session
        $sessionId = session_id();
        if(empty($sessionId)) {
            session_start();
        }

        // Unset all of the session variables
        $this->data = array();

        // This will destroy the session, and not just the session data
        if(ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        // Finally, destroy the session
        session_destroy();
    }

}
?>